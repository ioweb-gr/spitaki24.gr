<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobImport;

use RuntimeException;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Database\TableDto;
use WPStaging\Framework\Database\TableService;
use WPStaging\Framework\Filesystem\DiskWriteCheck;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\SiteInfo;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Ajax\Import\PrepareImport;
use WPStaging\Pro\Backup\Dto\Job\JobImportDataDto;
use WPStaging\Pro\Backup\Dto\JobDataDto;
use WPStaging\Pro\Backup\Dto\StepsDto;
use WPStaging\Pro\Backup\Exceptions\DiskNotWritableException;
use WPStaging\Pro\Backup\Exceptions\ThresholdException;
use WPStaging\Pro\Backup\Task\ImportTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class RestoreRequirementsCheckTask extends ImportTask
{
    /** @var TableService */
    protected $tableService;

    /** @var JobImportDataDto $jobDataDto */
    protected $jobDataDto;

    /** @var DiskWriteCheck */
    protected $diskWriteCheck;

    /** @var string A WPSTAGING backup with a version lower than this one is a beta release. */
    const BETA_VERSION_LIMIT = '4';

    public function __construct(
        TableService $tableService,
        JobDataDto $jobDataDto,
        LoggerInterface $logger,
        Cache $cache,
        StepsDto $stepsDto,
        SeekableQueueInterface $taskQueue,
        DiskWriteCheck $diskWriteCheck
    ) {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->tableService = $tableService;
        $this->jobDataDto = $jobDataDto;
        $this->diskWriteCheck = $diskWriteCheck;
    }

    public static function getTaskName()
    {
        return 'backup_restore_requirement_check';
    }

    public static function getTaskTitle()
    {
        return 'Restore Requirements Check';
    }

    public function execute()
    {
        if (!$this->stepsDto->getTotal()) {
            // The only requirement checking that really needs a step is the free disk space one, all other happens instantly.
            $this->stepsDto->setTotal(1);
        }

        try {
            // Warnings
            $this->shouldWarnIfRestoringBackupWithShortOpenTags();
            $this->shouldWarnIfRunning32Bits();
            $this->shouldWarnIfTheresNotEnoughFreeDiskSpace();

            // Errors
            $this->cannotImportIfCantWriteToDisk();
            $this->cannotImportSingleSiteExportIntoMultisiteAndViceVersa();
            $this->cannotHaveConflictingPrefix();
            $this->cannotHaveTableThatWillExceedLength();
            $this->cannotImportIfThereIsNotEnoughFreeDiskSpaceForTheDatabase();
            $this->cannotImportIfBackupGeneratedOnNewerWPStagingVersion();
            $this->cannotImportIfBackupGeneratedOnNewerWPDbVersion();
            $this->cannotImportBackupCreatedBeforeMVP();
            $this->cannotImportIfInvalidSiteOrHomeUrl();
        } catch (ThresholdException $e) {
            $this->logger->info($e->getMessage());

            return $this->generateResponse(false);
        } catch (RuntimeException $e) {
            $this->logger->critical($e->getMessage());

            return $this->generateResponse(false);
        }

        $this->logger->info(__('Requirements check passed...', 'wp-staging'));

        return $this->generateResponse();
    }

    protected function shouldWarnIfRestoringBackupWithShortOpenTags()
    {
        $shortTagsEnabledInBackupBeingRestored = $this->jobDataDto->getBackupMetadata()->getPhpShortOpenTags();

        if ($shortTagsEnabledInBackupBeingRestored) {
            $shortTagsEnabledInThisSite = (new SiteInfo())->isPhpShortTagsEnabled();

            if (!$shortTagsEnabledInThisSite) {
                $this->logger->warning(__('This backup was generated on a server with PHP ini directive "short_open_tags" enabled, which is disabled in this server. This might cause errors after Restore.', 'wp-staging'));
            }
        }
    }

    protected function cannotImportIfCantWriteToDisk()
    {
        try {
            $this->diskWriteCheck->testDiskIsWriteable();
        } catch (DiskNotWritableException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    protected function shouldWarnIfRunning32Bits()
    {
        if (PHP_INT_SIZE === 4) {
            $this->logger->warning(__('You are running a 32-bit version of PHP. That can lead to size detection issues on very large backups. Consider upgrading to 64-bit.', 'wp-staging'));
        }
    }

    protected function shouldWarnIfTheresNotEnoughFreeDiskSpace()
    {
        $fileBeingRestored = $this->jobDataDto->getFile();

        try {
            $file = new \SplFileObject($fileBeingRestored);
        } catch (\Exception $e) {
            $this->logger->warning(__('Could not open the backup file for requirement checking.', 'wp-staging'));
            return;
        }

        try {
            $this->diskWriteCheck->checkPathCanStoreEnoughBytes(WP_CONTENT_DIR, $file->getSize());
        } catch (DiskNotWritableException $e) {
            $this->logger->warning($e->getMessage());
            return;
        } catch (RuntimeException $e) {
            // soft error, no action needed, but log
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @throws RuntimeException When trying to import a .wpstg file generated from a multi-site
     *                          installation into a single-site and vice-versa.
     */
    protected function cannotImportSingleSiteExportIntoMultisiteAndViceVersa()
    {
        if ($this->jobDataDto->getBackupMetadata()->getSingleOrMulti() === 'single' && !is_multisite()) {
            // Early bail: .wpstg file is for "single" site, and we are in single-site.
            return;
        }

        if ($this->jobDataDto->getBackupMetadata()->getSingleOrMulti() === 'multi' && is_multisite()) {
            // Early bail: .wpstg file is for "multi" site, and we are in multi-site.
            return;
        }

        if ($this->jobDataDto->getBackupMetadata()->getSingleOrMulti() === 'single' && is_multisite()) {
            throw new \RuntimeException('This export file was generated from a single-site WordPress installation. This website uses a multi-site WordPress installation, therefore the importer cannot proceed.');
        }

        if ($this->jobDataDto->getBackupMetadata()->getSingleOrMulti() === 'multi' && !is_multisite()) {
            throw new \RuntimeException('This export file was generated from a multi-site WordPress installation. This website uses a single-site WordPress installation, therefore the importer cannot proceed.');
        }

        throw new \RuntimeException('This export is in an unknown format. It was not possible to determine whether it was generated from a single-site WordPress installation, or a multi-site WordPress installation, therefore the importer cannot proceed.');
    }

    protected function cannotHaveConflictingPrefix()
    {
        global $wpdb;

        $basePrefix = $wpdb->base_prefix;

        if (($basePrefix === PrepareImport::TMP_DATABASE_PREFIX || $basePrefix === PrepareImport::TMP_DATABASE_PREFIX_TO_DROP)) {
            throw new \RuntimeException("Can not proceed. The production site database table prefix uses \"$basePrefix\" which is used for temporary tables by WP STAGING. Please, feel free to reach out to WP STAGING support for assistance.");
        }
    }

    protected function cannotHaveTableThatWillExceedLength()
    {
        global $wpdb;

        $prefix = $wpdb->base_prefix;

        $tables = $this->tableService->findTableStatusStartsWith($prefix);

        if (empty($tables)) {
            // This should never happen, as we are running this in the context of a WordPress plugin.
            throw new RuntimeException("We could not find any tables with the prefix \"$prefix\". The importer cannot start. Please, feel free to reach out to WP STAGING support for assistance.");
        }

        /** @var TableDto $table */
        foreach ($tables as $table) {
            if (!$table instanceof TableDto) {
                throw new RuntimeException("We could not read information from tables to determine whether the importer is able to run or not, therefore the importer cannot start. Please, feel free to reach out to WP STAGING support for assistance.");
            }

            $unprefixedName = substr($table->getName(), strpos($table->getName(), $prefix));

            if (strlen($unprefixedName) + strlen(PrepareImport::TMP_DATABASE_PREFIX) > 64) {
                throw new RuntimeException("MySQL has a limit of 64 characters for table names. One of your tables, combined with the temporary prefix used by the importer, would exceed this limit, therefore the importer cannot start. The table with the extra-long name is: \"{$table->getName()}\". Please, feel free to reach out to WP STAGING support for assistance.");
            }
        }

        $maxLengthOfTableBeingImported = $this->jobDataDto->getBackupMetadata()->getMaxTableLength();

        if ($maxLengthOfTableBeingImported + strlen(PrepareImport::TMP_DATABASE_PREFIX) > 64) {
            throw new RuntimeException("MySQL has a limit of 64 characters for table names. One of the tables in the backup being imported, combined with the temporary prefix used by the importer, would exceed this limit, therefore the importer cannot start. Please, feel free to reach out to WP STAGING support for assistance.");
        }

        if ($maxLengthOfTableBeingImported + strlen($prefix) > 64) {
            throw new RuntimeException("MySQL has a limit of 64 characters for table names. One of the tables in the backup being imported, combined with the base prefix of your WordPress installation (\"$prefix\"), would exceed this limit, therefore the importer cannot start. Please, feel free to reach out to WP STAGING support for assistance.");
        }
    }

    /**
     * When importing a backup, we detect and can recover from disk fulls while
     * extracting the .wpstg file to a temporary directory. However, depending
     * on the size of the database in this backup, we might hit disk limits
     * while inserting data into MySQL.
     *
     * We cannot prevent every possible issue, but we can try to catch some.
     *
     * This method tries to write a file the same size as the database being
     * imported to the filesystem. If there is not enough disk space for
     * this operation, there will hardly be enough disk space to import the
     * database.
     *
     * @throws ThresholdException
     */
    protected function cannotImportIfThereIsNotEnoughFreeDiskSpaceForTheDatabase()
    {
        $databaseFileSize = $this->jobDataDto->getBackupMetadata()->getDatabaseFileSize();

        // Early bail: No database in this backup
        if (empty($databaseFileSize)) {
            $this->stepsDto->incrementCurrentStep();

            return;
        }

        /**
         * We estimate we need 110% of the original backup file of free disk space for the import process.
         *
         * wp-content/uploads/wp-staging/tmp/import/wp-content/* (extracted files)
         * Tmp database (in MySQL)
         */
        $estimatedSizeNeeded = (int)($databaseFileSize * 1.1);

        $tmpFile = __DIR__ . '/diskCheck.wpstg';

        if (!file_exists($tmpFile) && !touch($tmpFile)) {
            throw new RuntimeException(__(sprintf('The importer could not write to the temporary file %s.', $tmpFile)));
        }

        $fileObject = new \SplFileObject($tmpFile, 'a');

        $writtenBytes = $this->jobDataDto->getExtractorFileWrittenBytes();
        $timesWritten = 0;
        $fiveMb = str_repeat('a', 5 * MB_IN_BYTES);

        while ($writtenBytes < $estimatedSizeNeeded) {
            $writtenNow = $fileObject->fwrite($fiveMb);

            if ($writtenNow === 0) {
                unlink($fileObject->getPathname());
                throw new RuntimeException(__(sprintf('It seems there is not enough free disk space to import this backup. The importer needs %s of free disk space to proceed, therefore the importer will not continue.', size_format($estimatedSizeNeeded))));
            } else {
                $writtenBytes += $writtenNow;
            }

            // Only check threshold every now and then
            if ($timesWritten++ >= 5) {
                if ($this->isThreshold()) {
                    $this->jobDataDto->setExtractorFileWrittenBytes($fileObject->getSize());
                    $percentage = (int)(($writtenBytes / $estimatedSizeNeeded) * 100);
                    throw ThresholdException::thresholdHit(__(sprintf('Checking if there is enough free disk space to import... (%d%%)', $percentage), 'wp-staging'));
                }
                $timesWritten = 0;
            }
        }

        unlink($fileObject->getPathname());
        $this->jobDataDto->setExtractorFileWrittenBytes(0);
        $this->stepsDto->incrementCurrentStep();
    }

    /*
     * Disallows backups generated in newer versions of WP STAGING to be restored
     * using older versions of WP STAGING.
     */
    protected function cannotImportIfBackupGeneratedOnNewerWPStagingVersion()
    {
        if (defined('WPSTG_DEV') && WPSTG_DEV) {
            $this->logger->warning('Backup generated on newer WP STAGING version. Allowed to continue due to WPSTG_DEV...');
            return;
        }

        if (version_compare($this->jobDataDto->getBackupMetadata()->getVersion(), WPStaging::getVersion(), '>')) {
            throw new RuntimeException(sprintf('This backup was generated on a newer version of WP STAGING. Please upgrade WP STAGING to restore this Backup.'));
        }
    }

    /*
     * Disallow backups that contains database generated in newer versions of WordPress to be restored
     * in older versions of WordPress that has a different database schema.
     */
    protected function cannotImportIfBackupGeneratedOnNewerWPDbVersion()
    {
        if (!$this->jobDataDto->getBackupMetadata()->getIsExportingDatabase()) {
            return;
        }

        /**
         * @var string $wp_version
         * @var int    $wp_db_version
         */
        include ABSPATH . WPINC . '/version.php';

        // This should never happen
        if (!isset($wp_version) || !isset($wp_db_version)) {
            $this->logger->warning('Could not determine the WP DB Schema Version in the Backup. No action is necessary, the backup will proceed...');

            return;
        }

        if (version_compare((int)$this->jobDataDto->getBackupMetadata()->getWpDbVersion(), (int)$wp_db_version, '>')) {
            $this->logger->debug(sprintf(
                __('The backup is using an incompatible database schema version, generated in a newer version of WordPress. Schema version in the backup: %s. Current WordPress Schema version: %s', 'wp-staging'),
                $this->jobDataDto->getBackupMetadata()->getWpDbVersion(),
                $wp_db_version
            ));

            throw new RuntimeException(sprintf(
                __('This backup contains a database generated on WordPress %s, you are running WordPress %s, which has an incompatible database schema version. To restore this Backup, please use a newer version of WordPress.', 'wp-staging'),
                $this->jobDataDto->getBackupMetadata()->getWpVersion(),
                $wp_version
            ));
        }
    }

    /*
     * Disallow backups generated in the MVP to be restored using the newer version of WP STAGING.
     */
    protected function cannotImportBackupCreatedBeforeMVP()
    {
        if (defined('WPSTG_DEV') && WPSTG_DEV) {
            return;
        }

        if (version_compare($this->jobDataDto->getBackupMetadata()->getVersion(), self::BETA_VERSION_LIMIT, '<')) {
            throw new RuntimeException(sprintf('This backup was generated on a beta version of WP STAGING. Create a new Backup using the latest version of WP STAGING. Please feel free to get in touch with our support if you need assistance.'));
        }
    }

    protected function cannotImportIfInvalidSiteOrHomeUrl()
    {
        if (!parse_url($this->jobDataDto->getBackupMetadata()->getSiteUrl(), PHP_URL_HOST)) {
            throw new RuntimeException('This backup contains an invalid Site URL. Please contact support if you need assistance.');
        }

        if (!parse_url($this->jobDataDto->getBackupMetadata()->getHomeUrl(), PHP_URL_HOST)) {
            throw new RuntimeException('This backup contains an invalid Home URL. Please contact support if you need assistance.');
        }
    }
}
