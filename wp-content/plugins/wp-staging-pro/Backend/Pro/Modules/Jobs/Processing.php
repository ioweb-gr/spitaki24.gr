<?php

namespace WPStaging\Backend\Pro\Modules\Jobs;

use WPStaging\Framework\Security\AccessToken;
use WPStaging\Core\WPStaging;
use RuntimeException;
use WPStaging\Backend\Modules\Jobs\Job;
use WPStaging\Pro\Backup\Ajax\Export\PrepareExport;
use WPStaging\Pro\Backup\BackupServiceProvider;
use WPStaging\Pro\Backup\Dto\TaskResponseDto;
use WPStaging\Framework\Utils\WpDefaultDirectories;
use WPStaging\Pro\Backup\Job\Jobs\JobExport;

/**
 * Class Processing
 * Collect clone and job data and delegate all further separate job modules
 * @package WPStaging\Backend\Pro\Modules\Jobs
 */
class Processing extends Job
{
    use BackupTrait;

    /**
     * Start the cloning job
     */
    public function start()
    {
        // Save default job settings to cache file
        $this->init();

        $methodName = $this->options->currentJob;

        if (!method_exists($this, $methodName)) {
            // If method not exists, start over with default action
            $methodName = 'jobFinish';
            $this->log("Processing: Force method '{$methodName}'");
            $this->cache->delete("clone_options");
            $this->cache->delete("files_to_copy");
            // Save default job settings and create clone_options with default settings
            $this->init();
        }

        // Call the job
        return $this->{$methodName}();
    }

    /**
     * Save processing default settings
     * @return bool
     */
    private function init()
    {
        // Make sure this runs one time only on start of processing
        if (!isset($_POST) || !isset($_POST["clone"]) || !empty($this->options->currentJob)) {
            return false;
        }

        // Delete old job files initially
        $this->cache->delete('clone_options');
        $this->cache->delete('files_to_copy');

        // Basic Options
        $this->options->root = str_replace(["\\", '/'], DIRECTORY_SEPARATOR, ABSPATH);
        $this->options->existingClones = get_option("wpstg_existing_clones_beta", []);

        if (isset($_POST["clone"]) && array_key_exists($_POST["clone"], $this->options->existingClones)) {
            $this->options->current = $_POST["clone"];
            $this->options->databaseUser = $this->options->existingClones[strtolower($this->options->current)]['databaseUser'];
            $this->options->databasePassword = $this->options->existingClones[strtolower($this->options->current)]['databasePassword'];
            $this->options->databaseDatabase = $this->options->existingClones[strtolower($this->options->current)]['databaseDatabase'];
            $this->options->databaseServer = $this->options->existingClones[strtolower($this->options->current)]['databaseServer'];
            $this->options->databasePrefix = $this->options->existingClones[strtolower($this->options->current)]['databasePrefix'];
            $this->options->url = $this->options->existingClones[strtolower($this->options->current)]['url'];
            $this->options->path = wpstg_replace_windows_directory_separator(trailingslashit($this->options->existingClones[strtolower($this->options->current)]['path']));
            $this->options->uploadsSymlinked = isset($this->options->existingClones[strtolower($this->options->current)]['uploadsSymlinked']) ? $this->options->existingClones[strtolower($this->options->current)]['uploadsSymlinked'] : false;
        }

        // Clone
        $this->options->clone = $_POST["clone"];
        $this->options->cloneDirectoryName = preg_replace("#\W+#", '-', strtolower($this->options->clone));
        $this->options->cloneNumber = $this->options->existingClones[strtolower($this->options->clone)]['number'];
        $this->options->prefix = $this->getPrefix();
        $this->options->mainJob = 'pushing';

        $this->options->excludedTables = [];
        $this->options->clonedTables = [];

        // Files
        $this->options->totalFiles = 0;
        $this->options->copiedFiles = 0;

        // Directories
        $this->options->includedDirectories = [];
        $this->options->excludedDirectories = [];
        $this->options->extraDirectories = [];
        $this->options->directoriesToCopy = [];
        $this->options->scannedDirectories = [];

        // TODO REF: Job Queue; FIFO
        // Job

        if (is_multisite()) {
            // We don't support backup of multisite as of now
            $this->options->currentJob = 'jobFileScanning';
        } else {
            $this->options->currentJob = 'jobPrepareBackup';
        }
        $this->options->currentStep = 0;
        $this->options->totalSteps = 0;


        // Create new Job object
        $this->options->job = new \stdClass();


        // Excluded Tables POST
        if (isset($_POST["excludedTables"]) && is_array($_POST["excludedTables"])) {
            $this->options->excludedTables = $_POST["excludedTables"];
        } else {
            $this->options->excludedTables = [];
        }

        // Excluded Directories POST
        if (isset($_POST["excludedDirectories"]) && is_array($_POST["excludedDirectories"])) {
            $this->options->excludedDirectories = $_POST["excludedDirectories"];
        }


        // Included Directories POST
        if (isset($_POST["includedDirectories"]) && is_array($_POST["includedDirectories"])) {
            $this->options->includedDirectories = $_POST["includedDirectories"];
        }

        // Extra Directories POST
        if (isset($_POST["extraDirectories"]) && !empty($_POST["extraDirectories"])) {
            $this->options->extraDirectories = array_map('trim', $_POST["extraDirectories"]);
        }

        // Never copy these folders
        $excludedDirectories = [
            $this->options->path . 'wp-content/plugins/wp-staging-pro',
            $this->options->path . 'wp-content/plugins/wp-staging-pro-1',
            $this->options->path . 'wp-content/plugins/wp-staging',
            $this->options->path . 'wp-content/plugins/wp-staging-1',
            $this->options->path . 'wp-content/uploads/wp-staging',
        ];

        // Add upload folder to list of excluded directories for push if symlink option is enabled
        if ($this->options->uploadsSymlinked) {
            $wpUploadsFolder = $this->options->path . (new WpDefaultDirectories())->getRelativeUploadPath();
            $excludedDirectories[] = rtrim($wpUploadsFolder, '/\\');
        }

        // Delete uploads folder before pushing
        $this->options->deleteUploadsFolder = !$this->options->uploadsSymlinked && isset($_POST['deleteUploadsBeforePushing']) && $_POST['deleteUploadsBeforePushing'] === 'true';
        // backup uploads folder before deleting
        $this->options->backupUploadsFolder = $this->options->deleteUploadsFolder && isset($_POST['backupUploadsBeforePushing']) && $_POST['backupUploadsBeforePushing'] === 'true';
        // Delete all plugins and themes not used in staging site
        $this->options->deletePluginsAndThemes = isset($_POST['deletePluginsAndThemes']) && $_POST['deletePluginsAndThemes'] === 'true';
        // Set default statuses for backup of uploads dir and cleaning of uploads, themes and plugins dirs
        $this->options->statusBackupUploadsDir = 'pending';
        $this->options->statusContentCleaner = 'pending';

        $this->options->excludedDirectories = array_merge($excludedDirectories, $this->options->excludedDirectories);

        // Excluded Files
        $this->options->excludedFiles = apply_filters('wpstg_push_excluded_files', [
            '.htaccess',
            '.DS_Store',
            '*.git',
            '*.svn',
            '*.tmp',
            'desktop.ini',
            '.gitignore',
            '*.log',
            'wp-staging-optimizer.php',
            '.wp-staging',
        ]);

        // Directories to Copy Total
        $this->options->directoriesToCopy = array_merge(
            $this->options->includedDirectories,
            $this->options->extraDirectories
        );

        $this->options->createBackupBeforePushing = filter_var(
            $_POST["createBackupBeforePushing"],
            FILTER_VALIDATE_BOOLEAN
        );


        // Save settings
        $this->saveExcludedDirectories();
        $this->saveExcludedTables();

        return $this->saveOptions();
    }

    /**
     * Save excluded directories
     * @return boolean
     */
    private function saveExcludedDirectories()
    {
        if (empty($this->options->existingClones[$this->options->clone])) {
            return false;
        }

        $this->options->existingClones[$this->options->clone]['excludedDirs'] = $this->options->excludedDirectories;

        if (update_option("wpstg_existing_clones_beta", $this->options->existingClones) === false) {
            return false;
        }

        return true;
    }

    /**
     * Save excluded tables
     * @return boolean
     */
    private function saveExcludedTables()
    {
        if (empty($this->options->existingClones[$this->options->clone])) {
            return false;
        }

        $this->options->existingClones[$this->options->clone]['excludedTables'] = $this->options->excludedTables;

        if (update_option("wpstg_existing_clones_beta", $this->options->existingClones) === false) {
            return false;
        }

        return true;
    }

    /**
     * Get prefix of staging site
     * @return string
     */
    private function getPrefix()
    {
        $prefix = 'tmp_';

        if ($this->isExternalDatabase() && isset($this->options->existingClones[$this->options->current]['databasePrefix'])) {
            $prefix = $this->options->existingClones[$this->options->current]['databasePrefix'];
        }

        if (isset($this->options->existingClones[$this->options->clone]['prefix'])) {
            $prefix = $this->options->existingClones[$this->options->clone]['prefix'];
        }

        return $prefix;
    }

    /**
     * @todo Response should be a DTO (we have it now; cloneDTO, perhaps needs extension)
     * @param object $response
     * @param string $nextJob
     *
     * @return object
     */
    private function handleJobResponse($response, $nextJob)
    {
        /*
         * This only fires When creating an automatic database-only backup.
         */
        if ($response instanceof TaskResponseDto) {
            $this->options->currentStep = $response->getStep();
            $this->options->totalSteps = $response->getTotal();
            $this->saveOptions();
            $response = json_decode(json_encode($response), false);
        }

        /**
         * @todo Normalize $response to an object.
         * @see \WPStaging\Backend\Pro\Modules\Jobs\Finish::start
         */
        if (is_array($response) && array_key_exists('status', $response)) {
            return $response;
        }

        if (!isset($response->status)) {
            $response->error = true;
            $response->message = "Response does not have status, therefore we can't detect whether it finished or not.";

            return $response;
        }

        // Job is not done. Status true means the process is finished
        // TODO Ref: $response->isFinished instead of $response->status; self explanatory hence no comment like above
        if ($response->status !== true) {
            return $response;
        }

        $this->options->currentJob = $nextJob;
        $this->options->currentStep = 0;
        $this->options->totalSteps = 0;

        // Save options
        $this->saveOptions();

        return $response;
    }

    public function jobPrepareBackup()
    {
        // Early bail: Not doing a backup. Skipping...
        if (!$this->options->createBackupBeforePushing) {
            return $this->jobFileScanning();
        }

        /** @var PrepareExport $prepare */
        $prepare  = WPStaging::getInstance()->getContainer()->make(PrepareExport::class);
        $prepared = $prepare->prepare([
            'isExportingDatabase' => true,
            'isAutomatedBackup' => true,
            'Name' => __('Database Backup', 'wp-staging'),
        ]);

        if (is_wp_error($prepared)) {
            throw new \UnexpectedValueException($prepared->get_error_message(), $prepared->get_error_code());
        }

        $response = new TaskResponseDto();
        $response->setStatus(true);

        return $this->handleJobResponse($response, 'jobBackup');
    }

    /**
     * Step 1
     * Take a backup of the production database
     */
    public function jobBackup()
    {
        $job = WPStaging::getInstance()->getContainer()->make(JobExport::class);

        if (!$job) {
            throw new RuntimeException('Failed to get Job Site Export');
        }

        return $this->handleJobResponse($job->prepareAndExecute(), 'jobFileScanning');
    }

    /**
     * Step 2
     * Scan folders for files to copy
     * @return object
     */
    public function jobFileScanning()
    {
        $directories = new ScanDirectories();

        return $this->handleJobResponse($directories->start(), 'jobCopy');
    }

    /**
     * Step 3
     * Copy Files
     * @return object
     */
    public function jobCopy()
    {
        $files = new Files();

        return $this->handleJobResponse($files->start(), 'jobCopyDatabaseTmp');
    }

    /**
     * Step 4
     * Copy Database tables to tmp tables
     * @return object
     */
    public function jobCopyDatabaseTmp()
    {
        $database = new DatabaseTmp();

        return $this->handleJobResponse($database->start(), 'jobSearchReplace');
    }

    /**
     * Step 5
     * Search & Replace
     * @return object
     */
    public function jobSearchReplace()
    {
        $searchReplace = new SearchReplace();

        return $this->handleJobResponse($searchReplace->start(), 'jobData');
    }

    /**
     * Step 6
     * So some data operations
     * @return object
     */
    public function jobData()
    {
        return $this->handleJobResponse((new Data())->start(), 'jobDatabaseRename');
    }

    /**
     * Step 7
     * Switch live and tmp tables
     * @return object
     */
    public function jobDatabaseRename()
    {
        $databaseBackup = new \WPStaging\Backend\Pro\Modules\Jobs\DatabaseTmpRename();

        return $this->handleJobResponse($databaseBackup->start(), 'jobFinish');
    }

    /**
     * Step 8
     * Finish Job
     * @return object
     */
    public function jobFinish()
    {
        $finish = new \WPStaging\Backend\Pro\Modules\Jobs\Finish();

        // Re-generate the token when the Push is complete.
        // Todo: Consider adding a do_action() on jobFinish to hook here.
        // Todo: Inject using DI.
        $accessToken = new AccessToken();
        $accessToken->generateNewToken();

        return $this->handleJobResponse($finish->start(), '');
    }
}
