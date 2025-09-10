<?php

namespace WPStaging\Pro\Backup\Task\Tasks;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Pro\Backup\Dto\StepsDto;
use WPStaging\Pro\Backup\Task\AbstractTask;
use WPStaging\Pro\Backup\Task\ImportTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Filesystem\Filesystem;

class CleanupTmpFilesTask extends AbstractTask
{
    private $filesystem;
    private $directory;
    private $pathIdentifier;

    public function __construct(LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, Filesystem $filesystem, Directory $directory, SeekableQueueInterface $taskQueue, PathIdentifier $pathIdentifier)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->filesystem = $filesystem;
        $this->directory = $directory;
        $this->pathIdentifier = $pathIdentifier;
    }

    public static function getTaskName()
    {
        return 'backup_restore_cleanup_files';
    }

    public static function getTaskTitle()
    {
        return 'Cleaning Up Restore Files';
    }

    /**
     * @return \WPStaging\Pro\Backup\Dto\TaskResponseDto
     */
    public function execute()
    {
        $this->prepareCleanupImportTask();

        $tmpImportDir = $this->directory->getTmpDirectory();

        $tmpImportDir = untrailingslashit($tmpImportDir);

        $relativePathForLogging = str_replace($this->filesystem->normalizePath(ABSPATH), '', $this->filesystem->normalizePath($tmpImportDir));

        // Early bail: Path to Clean does not exist
        if (!file_exists($tmpImportDir)) {
            return $this->generateResponse();
        }

        try {
            $deleted = $this->filesystem
                ->setRecursive(true)
                ->setShouldStop(function () {
                    return $this->isThreshold();
                })
                ->delete($tmpImportDir);
        } catch (\Exception $e) {
            $this->logger->warning(sprintf(
                __('%s: Could not cleanup path "%s". May be a permission issue?', 'wp-staging'),
                static::getTaskTitle(),
                $relativePathForLogging
            ));

            return $this->generateResponse();
        }

        if ($deleted) {
            // Successfully deleted
            $this->logger->info(sprintf(
                __('%s: Path "%s" successfully cleaned up.', 'wp-staging'),
                static::getTaskTitle(),
                $relativePathForLogging
            ));

            return $this->generateResponse();
        } else {
            /*
             * Not successfully deleted.
             * This can happen if the folder to delete is too large
             * to be deleted in a single request. We continue
             * deleting it in the next request...
             */
            $response = $this->generateResponse(false);
            $response->setStatus(false);

            $this->logger->info(sprintf(
                __('%s: Re-enqueing path %s for deletion, as it couldn\'t be deleted in a single request without
                    hitting execution limits. If you see this message in a loop, PHP might not be able to delete
                    this directory, so you might want to try to delete it manually.', 'wp-staging'),
                static::getTaskTitle(),
                $relativePathForLogging
            ));

            // Early bail: Response modified for repeating
            return $response;
        }
    }

    public function prepareCleanupImportTask()
    {
        // We only cleanup database file for ImportTask
        if (!$this instanceof ImportTask) {
            return;
        }

        // Early bail: Already prepared
        if ($this->stepsDto->getTotal() === 1) {
            return;
        }

        // Clear the .sql file used during the import, if this backup includes a database.
        $databaseFile = $this->jobDataDto->getBackupMetadata()->getDatabaseFile();

        if ($databaseFile) {
            $databaseFile = $this->pathIdentifier->transformIdentifiableToPath($this->jobDataDto->getBackupMetadata()->getDatabaseFile());

            if (file_exists($databaseFile)) {
                unlink($databaseFile);
            }
        }

        $this->stepsDto->setTotal(1);
    }
}
