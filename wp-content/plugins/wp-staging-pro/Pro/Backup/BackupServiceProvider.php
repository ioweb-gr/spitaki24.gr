<?php

namespace WPStaging\Pro\Backup;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\DI\FeatureServiceProvider;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Filesystem\LogCleanup;
use WPStaging\Framework\Queue\FileSeekableQueue;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Pro\Backup\Ajax\Export\PrepareExport;
use WPStaging\Pro\Backup\Ajax\Import\LoginUrl;
use WPStaging\Pro\Backup\Ajax\Import\PrepareImport;
use WPStaging\Pro\Backup\Ajax\Import\ReadBackupMetadata;
use WPStaging\Pro\Backup\Dto\Job\JobExportDataDto;
use WPStaging\Pro\Backup\Dto\Job\JobImportDataDto;
use WPStaging\Pro\Backup\Dto\JobDataDto;
use WPStaging\Pro\Backup\Ajax\Cancel;
use WPStaging\Pro\Backup\Ajax\Export;
use WPStaging\Pro\Backup\Ajax\Delete;
use WPStaging\Pro\Backup\Ajax\Edit;
use WPStaging\Pro\Backup\Ajax\Listing;
use WPStaging\Pro\Backup\Ajax\Import;
use WPStaging\Pro\Backup\Ajax\FileInfo;
use WPStaging\Pro\Backup\Ajax\FileList;
use WPStaging\Pro\Backup\Ajax\Status;
use WPStaging\Pro\Backup\Ajax\Upload;
use WPStaging\Pro\Backup\Job\Jobs\JobExport;
use WPStaging\Pro\Backup\Job\Jobs\JobImport;
use WPStaging\Pro\Backup\Service\BackupsFinder;
use WPStaging\Pro\Backup\Service\Compressor;
use WPStaging\Pro\Backup\Service\Database\Importer\Insert\ExtendedInserterWithoutTransaction;
use WPStaging\Pro\Backup\Service\Database\Importer\Insert\QueryInserter;
use WPStaging\Pro\Backup\Task\AbstractTask;

class BackupServiceProvider extends FeatureServiceProvider
{
    /**
     * Toggle the experimental backup feature on/off.
     * Used only for developers of WPSTAGING while the backups feature is being developed.
     * Do not turn this on unless you know what you're doing, as it might irreversibly delete
     * files, databases, etc.
     */
    public static function getFeatureTrigger()
    {
        return 'WPSTG_FEATURE_ENABLE_BACKUP';
    }

    protected function registerClasses()
    {
        // @todo: Remove this once this is merged: https://github.com/lucatume/di52/pull/32
        $this->container->bind(JobDataDto::class, function () {
            return new JobDataDto();
        });

        $this->container->bind(SeekableQueueInterface::class, function () {
            return $this->container->make(FileSeekableQueue::class);
        });

        // Jobs
        #$this->container->singleton(JobImport::class);
        #$this->container->singleton(JobExport::class);

        $this->container->when(JobExport::class)
                        ->needs(JobDataDto::class)
                        ->give(JobExportDataDto::class);

        $this->container->when(JobImport::class)
                        ->needs(JobDataDto::class)
                        ->give(JobImportDataDto::class);



        $this->container->when(AbstractTask::class)
                        ->needs(SeekableQueueInterface::class)
                        ->give(FileSeekableQueue::class);

        $this->container->make(BackupDownload::class)->listenDownload();

        $this->hookDatabaseImporterQueryInserter();
    }

    protected function addHooks()
    {
        $this->enqueueAjaxListeners();

        add_action('wpstg_weekly_event', [$this, 'createBackupsDirectory'], 25, 0);
        add_action('wpstg_daily_event', [$this, 'cleanupLogs'], 25, 0);

        add_action('wp_login', $this->container->callback(AfterRestore::class, 'loginAfterRestore'), 10, 2);
    }

    protected function enqueueAjaxListeners()
    {
        add_action('wp_ajax_wpstg--backups--prepare-export', $this->container->callback(PrepareExport::class, 'ajaxPrepare'));
        add_action('wp_ajax_wpstg--backups--export', $this->container->callback(Export::class, 'render'));

        add_action('wp_ajax_wpstg--backups--prepare-import', $this->container->callback(PrepareImport::class, 'ajaxPrepare'));
        add_action('wp_ajax_wpstg--backups--import', $this->container->callback(Import::class, 'render'));

        add_action('wp_ajax_wpstg--backups--read-backup-metadata', $this->container->callback(ReadBackupMetadata::class, 'ajaxPrepare'));

        add_action('wp_ajax_wpstg--backups--listing', $this->container->callback(Listing::class, 'render'));
        add_action('wp_ajax_wpstg--backups--delete', $this->container->callback(Delete::class, 'render'));
        add_action('wp_ajax_wpstg--backups--cancel', $this->container->callback(Cancel::class, 'render'));
        add_action('wp_ajax_wpstg--backups--edit', $this->container->callback(Edit::class, 'render'));
        add_action('wp_ajax_wpstg--backups--status', $this->container->callback(Status::class, 'render'));
        add_action('wp_ajax_wpstg--backups--import--file-list', $this->container->callback(FileList::class, 'render'));
        add_action('wp_ajax_wpstg--backups--import--file-info', $this->container->callback(FileInfo::class, 'render'));
        add_action('wp_ajax_wpstg--backups--import--file-upload', $this->container->callback(Upload::class, 'render'));
        add_action('wp_ajax_wpstg--backups--uploads-delete-unfinished', $this->container->callback(Upload::class, 'deleteIncompleteUploads'));
        add_action('wp_ajax_raw_wpstg--backups--login-url', $this->container->callback(LoginUrl::class, 'getLoginUrl'));

        // Nopriv
        add_action('wp_ajax_nopriv_wpstg--backups--import', $this->container->callback(Import::class, 'render'));
        add_action('wp_ajax_nopriv_wpstg--backups--status', $this->container->callback(Status::class, 'render'));
        add_action('wp_ajax_nopriv_raw_wpstg--backups--login-url', $this->container->callback(LoginUrl::class, 'getLoginUrl'));
    }

    protected function hookDatabaseImporterQueryInserter()
    {
        $this->container->bind(QueryInserter::class, ExtendedInserterWithoutTransaction::class);
    }

    public function createBackupsDirectory()
    {
        $backupsDirectory = $this->container->make(BackupsFinder::class)->getBackupsDirectory();
        $this->container->make(Filesystem::class)->mkdir($backupsDirectory, true);
    }

    public function cleanupLogs()
    {
        $this->container->make(LogCleanup::class)->cleanOldLogs();
    }
}
