<?php

namespace WPStaging\Pro\Backup\Ajax\Import;

use WPStaging\Core\WPStaging;
use WPStaging\Pro\Backup\Ajax\PrepareJob;
use WPStaging\Pro\Backup\Dto\Job\JobImportDataDto;
use WPStaging\Pro\Backup\Exceptions\ProcessLockedException;
use WPStaging\Pro\Backup\Job\Jobs\JobImport;

class PrepareImport extends PrepareJob
{
    /** @var JobImportDataDto*/
    private $jobDataDto;
    private $jobImport;

    const TMP_DATABASE_PREFIX = 'wpstgtmp_';

    /*
     * The prefix used when dropping a table. Same length as TMP_DATABASE_PREFIX
     * to avoid extrapolating the limit of 64 characters for a table name.
     */
    const TMP_DATABASE_PREFIX_TO_DROP = 'wpstgbak_';

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

        // Lazy-instantiation to avoid process-lock checks conflicting with running processes.
        $this->jobDataDto = WPStaging::getInstance()->getContainer()->make(JobImportDataDto::class);
        $this->jobImport = WPStaging::getInstance()->getContainer()->make(JobImport::class);

        $response = $this->prepare($data);

        if ($response instanceof \WP_Error) {
            wp_send_json_error($response->get_error_message(), $response->get_error_code());
        } else {
            wp_send_json_success();
        }
    }

    public function prepare($data = null)
    {
        if (empty($data) && array_key_exists('wpstgImportData', $_POST)) {
            $data = $_POST['wpstgImportData'];
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

        $this->jobDataDto->hydrate($sanitizedData);
        $this->jobDataDto->setInit(true);
        $this->jobDataDto->setFinished(false);
        $this->jobDataDto->setTmpDatabasePrefix(self::TMP_DATABASE_PREFIX);

        $this->jobDataDto->setId(substr(md5(mt_rand() . time()), 0, 12));

        $this->jobImport->setJobDataDto($this->jobDataDto);

        return $sanitizedData;
    }

    /**
     * @return array
     */
    private function validateAndSanitizeData($data)
    {
        $expectedKeys = [
            'file',
        ];

        // Make sure data has no keys other than the expected ones.
        $data = array_intersect_key($data, array_flip($expectedKeys));

        // Make sure data has all expected keys.
        foreach ($expectedKeys as $expectedKey) {
            if (!array_key_exists($expectedKey, $data)) {
                throw new \UnexpectedValueException("Invalid request. Missing '$data'.");
            }
        }

        return $data;
    }
}
