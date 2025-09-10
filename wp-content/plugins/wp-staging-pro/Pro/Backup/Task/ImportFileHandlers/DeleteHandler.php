<?php

namespace WPStaging\Pro\Backup\Task\ImportFileHandlers;

class DeleteHandler extends ImportFileHandler
{
    public function handle($source, $destination)
    {
        $this->lock($destination);
        try {
            $deleted = $this->filesystem
                ->setRecursive(true)
                ->setShouldStop(function () {
                    return $this->fileImportTask->isThreshold();
                })
                ->delete($destination, true, true);
        } catch (\Exception $e) {
            $this->unlock();
            $this->logger->warning(sprintf(
                __('%s: PHP does not have permission to delete %s! This folder might still be in your filesystem, please clear it manually.', 'wp-staging'),
                call_user_func([$this->fileImportTask, 'getTaskTitle']),
                $destination
            ));

            return;
        }

        $this->unlock();

        if (!$deleted) {
            $this->fileImportTask->enqueueDelete($destination);
            $this->logger->debug(sprintf(
                __('%s: %s could not be entirely deleted in this request. Enqueuing it again for retry...', 'wp-staging'),
                call_user_func([$this->fileImportTask, 'getTaskTitle']),
                $destination
            ));
        }
    }
}
