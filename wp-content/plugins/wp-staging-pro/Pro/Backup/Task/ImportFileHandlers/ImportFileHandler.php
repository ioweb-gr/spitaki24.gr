<?php

namespace WPStaging\Pro\Backup\Task\ImportFileHandlers;

use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Pro\Backup\Task\FileImportTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

abstract class ImportFileHandler
{
    /** @var FileImportTask */
    protected $fileImportTask;

    /** @var LoggerInterface */
    protected $logger;

    /** @var resource */
    protected $handle;

    /** @var Filesystem */
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function setContext(FileImportTask $fileImportTask, LoggerInterface $logger)
    {
        $this->fileImportTask = $fileImportTask;
        $this->logger = $logger;
    }

    abstract public function handle($source, $destination);

    protected function lock($filePath)
    {
        $this->handle = @fopen($filePath, is_dir($filePath) ? 'r' : 'rb+');

        if (!$this->handle) {
            $this->logger->debug(sprintf(
                __('%s: Could not open handle for locking file %s', 'wp-staging'),
                call_user_func([$this->fileImportTask, 'getTaskTitle']),
                $filePath
            ));

            return false;
        }

        $locked = flock($this->handle, LOCK_EX | LOCK_NB);

        if (!$locked) {
            $this->logger->debug(sprintf(
                __('%s: Could not open lock file %s', 'wp-staging'),
                call_user_func([$this->fileImportTask, 'getTaskTitle']),
                $filePath
            ));

            return false;
        }

        return true;
    }

    protected function unlock()
    {
        if ($this->handle) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
            unset($this->handle);
        }
    }
}
