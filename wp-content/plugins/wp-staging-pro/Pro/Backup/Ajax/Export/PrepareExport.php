<?php

namespace WPStaging\Pro\Backup\Ajax\Export;

use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Security\Auth;
use WPStaging\Framework\Utils\Urls;
use WPStaging\Pro\Backup\Ajax\PrepareJob;
use WPStaging\Pro\Backup\BackupProcessLock;
use WPStaging\Pro\Backup\Dto\Job\JobExportDataDto;
use WPStaging\Pro\Backup\Exceptions\ProcessLockedException;
use WPStaging\Pro\Backup\Job\Jobs\JobExport;

class PrepareExport extends PrepareJob
{
    /** @var JobExportDataDto */
    private $jobDataDto;
    private $jobExport;
    private $urls;

    public function __construct(Filesystem $filesystem, Directory $directory, Auth $auth, BackupProcessLock $processLock, Urls $urls)
    {
        parent::__construct($filesystem, $directory, $auth, $processLock);
        $this->urls = $urls;
    }

    public function ajaxPrepare($data)
    {
        if (!$this->auth->isAuthenticatedRequest()) {
            wp_send_json_error(null, 401);
        }

        try {
            $this->processLock->checkProcessLocked();
        } catch (ProcessLockedException $e) {
            wp_send_json_error($e->getMessage(), $e->getCode());
        }

        $response = $this->prepare($data);

        if ($response instanceof \WP_Error) {
            wp_send_json_error($response->get_error_message(), $response->get_error_code());
        } else {
            wp_send_json_success();
        }
    }

    public function prepare($data = null)
    {
        if (empty($data) && array_key_exists('wpstgExportData', $_POST)) {
            $data = $_POST['wpstgExportData'];
        }

        try {
            $sanitizedData = $this->setupInitialData($data);
        } catch (\Exception $e) {
            return new \WP_Error(400, $e->getMessage());
        }

        return $sanitizedData;
    }

    private function setupInitialData($sanitizedData)
    {
        $sanitizedData = $this->validateAndSanitizeData($sanitizedData);
        $this->clearCacheFolder();

        // Lazy-instantiation to avoid process-lock checks conflicting with running processes.
        $this->jobDataDto = WPStaging::getInstance()->getContainer()->make(JobExportDataDto::class);
        $this->jobExport = WPStaging::getInstance()->getContainer()->make(JobExport::class);

        $this->jobDataDto->hydrate($sanitizedData);
        $this->jobDataDto->setInit(true);
        $this->jobDataDto->setFinished(false);

        $this->jobDataDto->setId(substr(md5(mt_rand() . time()), 0, 12));

        $this->jobExport->setJobDataDto($this->jobDataDto);

        return $sanitizedData;
    }

    /**
     * @return array
     */
    private function validateAndSanitizeData($data)
    {
        // Unset any empty value so that we replace them with the defaults.
        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset($data[$key]);
            }
        }

        $defaults = [
            'name' => $this->urls->getBaseUrlWithoutScheme(),
            'isExportingPlugins' => false,
            'isExportingMuPlugins' => false,
            'isExportingThemes' => false,
            'isExportingUploads' => false,
            'isExportingOtherWpContentFiles' => false,
            'isExportingDatabase' => false,
            'isAutomatedBackup' => false,
        ];

        $data = wp_parse_args($data, $defaults);

        // Make sure data has no keys other than the expected ones.
        $data = array_intersect_key($data, $defaults);

        // Make sure data has all expected keys.
        foreach ($defaults as $expectedKey => $value) {
            if (!array_key_exists($expectedKey, $data)) {
                throw new \UnexpectedValueException("Invalid request. Missing '$expectedKey'.");
            }
        }

        // Sanitize data
        $data['name'] = substr(sanitize_text_field(html_entity_decode($data['name'])), 0, 100);

        // Foo\'s Backup => Foo's Backup
        $data['name'] = str_replace('\\\'', '\'', $data['name']);

        // Remove accents and disallow most special characters?
        // $data['name'] = remove_accents($data['name']);
        // $data['name'] = preg_replace('#[^a-zA-Z0-9\' "]#', '', $data['name']);

        $data['isExportingPlugins'] = $this->jsBoolean($data['isExportingPlugins']);
        $data['isExportingMuPlugins'] = $this->jsBoolean($data['isExportingMuPlugins']);
        $data['isExportingThemes'] = $this->jsBoolean($data['isExportingThemes']);
        $data['isExportingUploads'] = $this->jsBoolean($data['isExportingUploads']);
        $data['isExportingOtherWpContentFiles'] = $this->jsBoolean($data['isExportingOtherWpContentFiles']);
        $data['isExportingDatabase'] = $this->jsBoolean($data['isExportingDatabase']);

        return $data;
    }
}
