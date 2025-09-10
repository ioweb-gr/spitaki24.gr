<?php

namespace WPStaging\Pro\Backup\Task\ImportFileHandlers;

class MoveHandler extends ImportFileHandler
{
    public function handle($source, $destination)
    {
        $parentDirectory = dirname($destination);

        if (!is_dir($parentDirectory)) {
            $parentDirectoryCreated = wp_mkdir_p($parentDirectory);

            if (!$parentDirectoryCreated) {
                $this->logger->warning(sprintf(
                    __('%s: Parent directory of destination did not exist and could not be created, skipping! Parent directory: %s File that was skipped: %s', 'wp-staging'),
                    call_user_func([$this->fileImportTask, 'getTaskTitle']),
                    $parentDirectory,
                    $destination
                ));

                return;
            }
        }

        $this->lock($source);
        $moved = @rename($source, $destination);
        $this->unlock();

        if (!$moved) {
            $relativeSourcePathForLogging = str_replace($this->filesystem->normalizePath(ABSPATH), '', $source);
            $relativeDestinationPathForLogging = str_replace($this->filesystem->normalizePath(ABSPATH), '', $destination);

            $this->logger->warning(sprintf(
                __('%s: Could not move "%s" to "%s". May be a file permission issue?', 'wp-staging'),
                call_user_func([$this->fileImportTask, 'getTaskTitle']),
                $relativeSourcePathForLogging,
                $relativeDestinationPathForLogging
            ));
        }
    }
}
