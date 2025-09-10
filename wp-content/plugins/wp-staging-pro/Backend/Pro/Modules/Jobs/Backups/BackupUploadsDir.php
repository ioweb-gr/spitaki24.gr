<?php

namespace WPStaging\Backend\Pro\Modules\Jobs\Backups;

use WPStaging\Framework\Utils\WpDefaultDirectories;
use WPStaging\Framework\Utils\Strings;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Backend\Modules\Jobs\Job;
use WPStaging\Core\Utils\Logger;
use WPStaging\Core\WPStaging;

/**
 * Though the name of this class is backup but actually it renames the wp-content/uploads dir
 * Theoretically with reference to performance "renaming > copying + deleting"
 * Backup/Rename/Move is done in the following steps:
 * 1. Check if already a backup of uploads dir exist, if it exists then delete it first
 * 2. Copy wp-content/uploads/wp-staging dir to backup dir wp-content/uploads/wp-staging.wpstg_backup
 * (We cannot completely delete the uploads dir as wp-staging dir in uploads dir contains data regarding the push/update job
 * so it is better to copy this dir instead of moving/renaming it)
 * 3. Rename/Move all the content in uploads dir to backup uploads dir wp-staging.wpstg_backup (except wp-staging dir)
 *
 * @todo Replace it usage with backups feature once that is ready...
 */
class BackupUploadsDir
{
    /**
     * @var string hook name to delete the uploads backup
     */
    const BACKUP_DELETE_CRON_HOOK_NAME = 'wpstg.cron.delete.uploads_backup';

    /**
     * @var int time to delete the backup after (in seconds)
     */
    const TIME_TO_DELETE_BACKUP_AFTER = 604800; // Means after 1 week

    /**
     * @var string postfix const for appending to backup upload dir during push
     */
    const BACKUP_UPLOADS_DIR_POSTFIX = ".wpstg_backup";

    /**
     * @var array
     */
    private $logs = [];

    /**
     * @var Job
     */
    private $job;

    /**
     * @param Job $job
     */
    public function __construct($job)
    {
        $this->job = $job;
    }

    /**
     * Return logs of this backup process
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Backup upload folder but actually it is renaming the upload dir
     * Faster than doing copying then deleting
     * $directory param used in this method is mainly for mocking purpose but,
     * can also be used to give path of staging site
     * @param string $directory Root directory of target WordPress Installation
     * @return bool
     *
     * @todo update for clone when clone is network after merging that PR
     */
    public function backupWpUploadsDir($directory = ABSPATH)
    {
        $options = $this->job->getOptions();

        if ($options->statusBackupUploadsDir === 'finished' || $options->statusBackupUploadsDir === 'skipped') {
            return true;
        }

        if (!$options->deleteUploadsFolder || !$options->backupUploadsFolder) {
            $options->statusBackupUploadsDir = 'skipped';
            $this->job->saveOptions($options);
            return true;
        }

        if (!is_dir($directory)) {
            $this->logs[] = [
                "msg" => sprintf(__("Files: Error - No such directory exists: %s. Backup Uploads Dir", "wp-staging"), $directory),
                "type" => Logger::TYPE_ERROR
            ];
            return false;
        }

        $directory = trailingslashit($directory);
        // get the relative uploads dir path according to current wp installation to use for given directory
        $relUploadPath = (new WpDefaultDirectories())->getRelativeUploadPath();
        // the absolute uploads dir path according to the given directory
        $uploadPath = trailingslashit($directory . $relUploadPath);
        // the absolute path for the backup uploads dir for the given directory
        $backupUploadPath = trailingslashit(rtrim($uploadPath, '/') . self::BACKUP_UPLOADS_DIR_POSTFIX);

        if (!is_dir($uploadPath)) {
            $this->logs[] = [
                "msg" => sprintf(__("Files: Error - No such directory exists: %s. Backup Uploads Dir", "wp-staging"), $uploadPath),
                "type" => Logger::TYPE_ERROR
            ];
            return false;
        }

        $wpstgContentDir = WPStaging::getContentDir();
        $relWpstgContentDir = str_replace((new Strings())->sanitizeDirectorySeparator(ABSPATH), null, $wpstgContentDir);
        // if given directory is not current installation set wpstgContentDir according to the it
        if ($directory !== ABSPATH) {
            $wpstgContentDir = trailingslashit($directory . trim($relWpstgContentDir, '/'));
        }

        if (!is_dir($wpstgContentDir)) {
            $this->logs[] = [
                "msg" => sprintf(__("Files: Error - No such directory exists: %s. Backup Uploads Dir", "wp-staging"), $wpstgContentDir),
                "type" => Logger::TYPE_ERROR
            ];
            return false;
        }

        if ($options->statusBackupUploadsDir === 'pending') {
            $options->statusBackupUploadsDir = 'cleaning';
            $this->job->saveOptions($options);
            $this->logs[] = [
                "msg" => __("Files: Removing Old Uploads Dir Backup", "wp-staging"),
                "type" => Logger::TYPE_INFO
            ];
        }

        if ($options->statusBackupUploadsDir === 'cleaning') {
            if (!$this->removeBackup($backupUploadPath)) {
                return false;
            }

            $options->statusBackupUploadsDir = 'copying';
            $this->job->saveOptions($options);
            $this->logs[] = [
                "msg" => __("Files: Backing Up WP Staging Content Dir", "wp-staging"),
                "type" => Logger::TYPE_INFO
            ];
            if ($this->job->isOverThreshold()) {
                return false;
            }
        }

        // copy uploads/wp-staging to uploads.wpstg_backup/wp-staging
        if ($options->statusBackupUploadsDir === 'copying') {
            if (!$this->copyWpstgContentDir($wpstgContentDir, $backupUploadPath . 'wp-staging')) {
                return false;
            }

            $options->statusBackupUploadsDir = 'moving';
            $this->job->saveOptions($options);
            $this->logs[] = [
                "msg" => __("Files: Moving Uploads Dir for Backup", "wp-staging"),
                "type" => Logger::TYPE_INFO
            ];
            if ($this->job->isOverThreshold()) {
                return false;
            }
        }

        // move content from uploads to uploads.wpstg_backup except wp-content/uploads/wp-staging
        if ($options->statusBackupUploadsDir === 'moving') {
            if (
                !$this->moveToBackup(rtrim($uploadPath, '/'), rtrim($backupUploadPath, '/'), [
                '/' . untrailingslashit($relWpstgContentDir)
                ], $directory)
            ) {
                return false;
            }

            $options->statusBackupUploadsDir = 'finished';
            $this->job->saveOptions($options);
            $this->logs[] = [
                "msg" => __("Files: Finished Backing Up and Cleaning Uploads Dir", "wp-staging"),
                "type" => Logger::TYPE_INFO
            ];

            return true;
        }

        return false;
    }

    /**
     * Remove Old Backup of Upload dir if exist
     * @param string $backupDir
     * @return bool
     */
    private function removeBackup($backupDir)
    {
        if (is_dir($backupDir)) {
            $fs = (new Filesystem())
                ->setShouldStop([$this->job, 'isOverThreshold'])
                ->setRecursive();
            try {
                if (!$fs->delete($backupDir)) {
                    return false;
                }
            } catch (\RuntimeException $ex) {
                $this->logs[] = [
                    "msg" => sprintf(__("Files: Error - %s. Backing Uploads Dir", "wp-staging"), $ex->getMessage()),
                    "type" => Logger::TYPE_ERROR
                ];
                return false;
            }
        }

        return true;
    }

    /**
     * Move data from upload dir to backup dir except wp-staging content
     * @param string $uploadsDir
     * @param string $backupDir
     * @param array $excludeDirs
     * @param string $wpRootPath
     * @return bool
     */
    private function moveToBackup($uploadsDir, $backupDir, $excludeDirs = [], $wpRootPath = ABSPATH)
    {
        if (is_dir($uploadsDir)) {
            $fs = (new Filesystem())
                ->setRecursive()
                ->setShouldStop([$this->job, 'isOverThreshold'])
                ->setWpRootPath($wpRootPath)
                ->setExcludePaths($excludeDirs);
            try {
                if (!$fs->move($uploadsDir, $backupDir)) {
                    return false;
                }
            } catch (\RuntimeException $ex) {
                $this->logs[] = [
                    "msg" => sprintf(__("Files: Error - %s. Backing Uploads Dir", "wp-staging"), $ex->getMessage()),
                    "type" => Logger::TYPE_ERROR
                ];
            }
        }

        return true;
    }

    /**
     * Copy wpstg content dir to backup dir
     * @param string $wpstgContentDir
     * @param string $backupDir
     * @return bool
     */
    private function copyWpstgContentDir($wpstgContentDir, $backupDir)
    {
        $fs = (new Filesystem())
            ->setRecursive()
            ->setShouldStop([$this->job, 'isOverThreshold']);
        try {
            if (!$fs->copy(rtrim($wpstgContentDir, '/'), $backupDir)) {
                return false;
            }
        } catch (\RuntimeException $ex) {
            $this->logs[] = [
                "msg" => sprintf(__("Files: Error - %s. Backing Uploads Dir", "wp-staging"), $ex->getMessage()),
                "type" => Logger::TYPE_ERROR
            ];
            return false;
        }

        return true;
    }

    /**
     * Schedule the uploads backup to be deleted after one week
     * If any previous schedule was registered delete that and create a new one
     */
    public function scheduleDeleteOfTheBackup()
    {
        $options = $this->job->getOptions();

        // Early bail if the uploads dir isn't back up
        if ($options->statusBackupUploadsDir !== 'finished') {
            return;
        }

        // Remove scheduled event remove backups hook if any
        wp_clear_scheduled_hook(self::BACKUP_DELETE_CRON_HOOK_NAME);

        wp_schedule_single_event(time() + self::TIME_TO_DELETE_BACKUP_AFTER, self::BACKUP_DELETE_CRON_HOOK_NAME);

        $this->logs[] = [
            "msg" => __("Finish: Scheduled uploads backup at wp-content/uploads.wpstg_backup to be deleted after 7 days.", "wp-staging"),
            "type" => Logger::TYPE_INFO
        ];
    }

    /**
     * Remove uploads backup if that exists
     */
    public function removeUploadsBackup()
    {
        $uploadsPath = (new WpDefaultDirectories())->getUploadsPath();
        $backupUploadsPath = trailingslashit(rtrim($uploadsPath, '/') . self::BACKUP_UPLOADS_DIR_POSTFIX);
        if (!is_dir($backupUploadsPath)) {
            return;
        }

        (new Filesystem())->setRecursive()->delete($backupUploadsPath);
    }
}
