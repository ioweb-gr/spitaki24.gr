<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobImport;

use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Pro\Backup\Task\FileImportTask;

class ImportOtherFilesInWpContentTask extends FileImportTask
{
    public static function getTaskName()
    {
        return 'backup_restore_wp_content';
    }

    public static function getTaskTitle()
    {
        return 'Restoring Other Files in wp-content';
    }

    /**
     * The most critical step because it has to run in one request
     */
    protected function buildQueue()
    {
        try {
            $otherFilesToImport = $this->getOtherFilesToImport();
        } catch (\Exception $e) {
            // Folder does not exist. Likely there are no other files in wp-content to import.
            return;
        }

        $destinationDir = $this->directory->getWpContentDirectory();

        foreach ($otherFilesToImport as $id => $fileInfo) {
            /*
             * Scenario: Importing another file that exists or do not exist
             * 1. Overwrite conflicting files with what's in the backup
             */
            $this->enqueueMove($otherFilesToImport[$id]['path'], $destinationDir . $fileInfo['relativePath']);
        }
    }

    /**
     * @return array An array of paths of other files found in the root of wp-content,
     *               where the index is the relative path, and the value it's absolute path.
     * @example [
     *              'debug.log' => '/var/www/wp-content/debug.log',
     *          ]
     *
     */
    private function getOtherFilesToImport()
    {
        $path = $this->jobDataDto->getTmpDirectory() . PathIdentifier::IDENTIFIER_WP_CONTENT;

        $it = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $it = new \RecursiveIteratorIterator($it);

        $files = [];

        /** @var \SplFileInfo $item */
        foreach ($it as $item) {
            // Early bail: We don't want dots, links or anything that is not a file.
            if (!$item->isFile() || $item->isLink()) {
                continue;
            }

            // Allocate pathname to a variable because we use it multiple times below.
            $pathName = $item->getPathname();

            $relativePath = str_replace($path, '', $pathName);

            $files[] = [
                'path' => $pathName,
                'relativePath' => $relativePath,
            ];
        }

        return $files;
    }
}
