<?php

namespace WPStaging\Pro\Backup\Ajax;

use Exception;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\Filesystem\DiskWriteCheck;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Backup\Entity\BackupMetadata;
use WPStaging\Pro\Backup\Exceptions\DiskNotWritableException;
use WPStaging\Pro\Backup\Service\BackupsFinder;
use WPStaging\Pro\Backup\Task\Tasks\JobImport\RestoreRequirementsCheckTask;

class Upload extends AbstractTemplateComponent
{
    private $backupsFinder;

    public function __construct(BackupsFinder $backupsFinder, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->backupsFinder = $backupsFinder;
    }

    public function render()
    {
        if (!$this->canRenderAjax()) {
            return;
        }

        try {
            $this->validateRequestData();
        } catch (\Exception $e) {
            wp_send_json_error('Invalid request data', 400);
        }

        /**
         * Example:
         *
         * name = "8xx.290.myftpupload.com_20210521-193355_967950a65d39 (2).wpstg"
         * type = "application/octet-stream"
         * tmp_name = "/tmp/phpYFrjBk"
         * error = {int} 0
         * size = {int} 1048576
         */
        $file = $_FILES['file'];

        $resumableChunkNumber = absint($_GET['resumableChunkNumber']);
        $resumableChunkSize = absint($_GET['resumableChunkSize']);
        $resumableCurrentChunkSize = absint($_GET['resumableCurrentChunkSize']);
        $resumableTotalSize = absint($_GET['resumableTotalSize']);
        $resumableTotalChunks = absint($_GET['resumableTotalChunks']);
        $uniqueIdentifierSuffix = absint($_GET['uniqueIdentifierSuffix']);

        $resumableIdentifier = sanitize_file_name($_GET['resumableIdentifier']);
        $resumableFilename = sanitize_file_name($_GET['resumableFilename']);
        $resumableRelativePath = sanitize_file_name($_GET['resumableRelativePath']);

        $fullPath = $this->backupsFinder->getBackupsDirectory() . $uniqueIdentifierSuffix . $resumableFilename . '.uploading';

        $resumableInternalIdentifier = md5($fullPath);

        // Check free disk space on the first request
        if ($resumableChunkNumber <= 1) {
            try {
                WPStaging::make(DiskWriteCheck::class)->checkPathCanStoreEnoughBytes($this->backupsFinder->getBackupsDirectory(), $resumableTotalSize);
            } catch (DiskNotWritableException $e) {
                wp_send_json_error([
                    'message' => $e->getMessage(),
                    'isDiskFull' => true,
                ], 507);
            } catch (\RuntimeException $e) {
                // no-op
            }
        }

        // Assert chunks are in sequential order
        if ($resumableChunkNumber > 1 && $resumableTotalChunks > 1) {
            $nextExpectedChunk = (int)get_transient("wpstg.upload.nextExpectedChunk.$resumableInternalIdentifier");

            if ($nextExpectedChunk !== $resumableChunkNumber) {
                // 409 would make more sense, but let's throw a 418 in tribute to the only person in the world capable to laugh at this joke.
                wp_send_json_error('', 418);
            }
        }

        update_option('wpstg.backups.doing_upload', true);

        try {
            $result = file_put_contents($fullPath, file_get_contents($file['tmp_name']), FILE_APPEND);

            if (!$result) {
                // Do a disk_free_space() check
                try {
                    WPStaging::make(DiskWriteCheck::class)->checkPathCanStoreEnoughBytes($this->backupsFinder->getBackupsDirectory());
                } catch (\RuntimeException $e) {
                    // no-op
                }

                // If that succeeds or could not be determined, also do a real write check.
                WPStaging::make(DiskWriteCheck::class)->testDiskIsWriteable();
            }
        } catch (DiskNotWritableException $e) {
            delete_option('wpstg.backups.doing_upload');

            wp_send_json_error([
                'message' => $e->getMessage(),
                'isDiskFull' => true,
            ], 507);
        } catch (\Exception $e) {
            delete_option('wpstg.backups.doing_upload');

            wp_send_json_error([
                'message' => $e->getMessage(),
            ], 500);
        }

        // Last chunk?
        if ($resumableChunkNumber === $resumableTotalChunks) {
            try {
                delete_transient("wpstg.upload.nextExpectedChunk.$resumableInternalIdentifier");
                $this->validateBackupFile($fullPath);
                rename($fullPath, $this->backupsFinder->getBackupsDirectory() . $uniqueIdentifierSuffix . $resumableFilename);
            } catch (\Exception $e) {
                if (file_exists($fullPath) && is_file($fullPath)) {
                    unlink($fullPath);
                }
                wp_send_json_error([
                    'message' => $e->getMessage(),
                    'backupFailedValidation' => true,
                ], 500);
            }
        } else {
            // Set the next expected chunk, to avoid scenarios where an erratic network connection could skip chunks or send them in unexpected order, eg:
            // chunk.part.1
            // chunk.part.2
            // chunk.part.4 <-- not what we want!
            // chunk.part.3
            set_transient("wpstg.upload.nextExpectedChunk.$resumableInternalIdentifier", $resumableChunkNumber + 1, 1 * DAY_IN_SECONDS);
        }

        delete_option('wpstg.backups.doing_upload');

        wp_send_json_success();
    }

    public function deleteIncompleteUploads()
    {
        try {
            /** @var \SplFileInfo $splFileInfo */
            foreach (new \DirectoryIterator($this->backupsFinder->getBackupsDirectory()) as $splFileInfo) {
                if ($splFileInfo->isFile() && !$splFileInfo->isLink() && $splFileInfo->getExtension() === 'uploading') {
                    unlink($splFileInfo->getPathname());
                }
            }

            wp_send_json_success();
        } catch (\Exception $e) {
            wp_send_json_error();
        }
    }

    protected function validateBackupFile($fullPath)
    {
        clearstatcache();
        $backupMetadata = new BackupMetadata();
        $metadata = $backupMetadata->hydrateByFilePath($fullPath);

        if (version_compare($metadata->getVersion(), RestoreRequirementsCheckTask::BETA_VERSION_LIMIT, '<')) {
            throw new Exception(__('This backup was generated on a beta version of WP STAGING and can not be used with this version. Please create a new Backup or get in touch with our support if you need assistance.', 'wp-staging'));
        }

        $estimatedSize = $metadata->getBackupSize();
        $realSize = filesize($fullPath);
        $allowedDifferece = 1 * KB_IN_BYTES;

        $smallerThanExpected = $realSize + $allowedDifferece - $estimatedSize < 0;
        $biggerThanExpected = $realSize - $allowedDifferece > $estimatedSize;

        if ($smallerThanExpected || $biggerThanExpected) {
            throw new Exception(__(sprintf('The backup size (%s) is different than expected (%s). If this issue persists, upload the file directly to this folder using FTP: <strong>wp-content/uploads/wp-staging/backups</strong>', size_format($realSize, 2), size_format($estimatedSize)), 'wp-staging'));
        }
    }

    protected function validateRequestData()
    {
        if (empty($_FILES) || !isset($_FILES['file']) || !is_array($_FILES['file'])) {
            throw new Exception();
        }

        switch ((int)$_FILES['file']['error']) {
            case UPLOAD_ERR_OK:
                // Ok, no-op
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
            case UPLOAD_ERR_PARTIAL:
            case UPLOAD_ERR_NO_FILE:
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_EXTENSION:
                throw new Exception();
        }

        if (empty($_FILES['file']['tmp_name']) || !file_exists($_FILES['file']['tmp_name'])) {
            throw new Exception();
        }

        /**
         * Example:
         *
         * resumableChunkNumber = "1"
         * resumableChunkSize = "1048576"
         * resumableCurrentChunkSize = "1048576"
         * resumableTotalSize = "14209912"
         * resumableType = ""
         * resumableIdentifier = "14209912-multitestswp-staginglocal_fcd2fae486dcwpstg"
         * resumableFilename = "multi.tests.wp-staging.local_fcd2fae486dc.wpstg"
         * resumableRelativePath = "multi.tests.wp-staging.local_fcd2fae486dc.wpstg"
         * resumableTotalChunks = "13"
         */
        $requiredValues = [
            'resumableChunkNumber',
            'resumableChunkSize',
            'resumableCurrentChunkSize',
            'resumableTotalSize',
            'resumableIdentifier',
            'resumableFilename',
            'resumableRelativePath',
            'resumableTotalChunks',
        ];

        foreach ($requiredValues as $requiredValue) {
            if (!isset($_GET[$requiredValue])) {
                throw new Exception();
            }
        }

        $numericValues = [
            'resumableChunkNumber',
            'resumableChunkSize',
            'resumableCurrentChunkSize',
            'resumableTotalSize',
            'resumableTotalChunks',
        ];

        foreach ($numericValues as $numericValue) {
            if (!filter_var($_GET[$numericValue], FILTER_VALIDATE_INT)) {
                throw new Exception();
            }
        }

        $nonEmptyValues = [
            'resumableIdentifier',
            'resumableFilename',
            'resumableRelativePath',
        ];

        foreach ($nonEmptyValues as $nonEmptyValue) {
            if (empty($_GET[$nonEmptyValue])) {
                throw new Exception();
            }
        }
    }
}
