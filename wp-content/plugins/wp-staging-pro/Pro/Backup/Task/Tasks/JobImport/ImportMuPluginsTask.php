<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobImport;

use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Pro\Backup\Task\FileImportTask;

class ImportMuPluginsTask extends FileImportTask
{
    public static function getTaskName()
    {
        return 'backup_restore_muplugins';
    }

    public static function getTaskTitle()
    {
        return 'Restoring Mu-Plugins';
    }

    protected function buildQueue()
    {
        try {
            $muPluginsToImport = $this->getMuPluginsToImport();
        } catch (\Exception $e) {
            // Folder does not exist. Likely there are no mu-plugins to import.
            return;
        }

        $destDir = $this->directory->getMuPluginsDirectory();

        try {
            $existingMuPlugins = $this->getExistingMuPlugins();
        } catch (\Exception $e) {
            $this->logger->critical(__(sprintf('Destination mu-plugins folder could not be found nor created at "%s"', (string)apply_filters('wpstg.import.muPlugins.destDir', $destDir))));

            return;
        }

        foreach ($muPluginsToImport as $muPluginSlug => $muPluginPath) {
            /*
             * Scenario: Importing a mu-plugin that already exists
             * 1. Backup old mu-plugin
             * 2. Import new mu-plugin
             * 3. Delete backup
             */
            if (array_key_exists($muPluginSlug, $existingMuPlugins)) {
                $this->enqueueMove($existingMuPlugins[$muPluginSlug], "{$destDir}{$muPluginSlug}{$this->getOriginalSuffix()}");
                $this->enqueueMove($muPluginsToImport[$muPluginSlug], "{$destDir}{$muPluginSlug}");
                $this->enqueueDelete("{$destDir}{$muPluginSlug}{$this->getOriginalSuffix()}");
                continue;
            }

            /*
             * Scenario 2: Importing a plugin that does not yet exist
             */
            $this->enqueueMove($muPluginsToImport[$muPluginSlug], "$destDir$muPluginSlug");
        }
    }

    /**
     * @return array An array of paths of mu-plugins to import.
     */
    private function getMuPluginsToImport()
    {
        $tmpDir = $this->jobDataDto->getTmpDirectory() . PathIdentifier::IDENTIFIER_MUPLUGINS;

        return $this->findMuPluginsInDir($tmpDir);
    }

    /**
     * @return array An array of paths of existing mu-plugins.
     */
    private function getExistingMuPlugins()
    {
        $destDir = $this->directory->getMuPluginsDirectory();
        $destDir = (string)apply_filters('wpstg.import.muPlugins.destDir', $destDir);
        $this->filesystem->mkdir($destDir);

        return $this->findMuPluginsInDir($destDir);
    }

    /**
     * @param string $path Folder to look for mu-plugins, eg: '/var/www/wp-content/mu-plugins'
     *
     * @example [
     *              'foo' => '/var/www/wp-content/mu-plugins/foo',
     *              'foo.php' => '/var/www/wp-content/mu-plugins/foo.php',
     *          ]
     *
     * @return array An array of paths of mu-plugins found in the root of given directory,
     *               where the index is the name of the mu-plugin, and the value it's path.
     */
    private function findMuPluginsInDir($path)
    {
        $it = new \DirectoryIterator($path);

        $muPluginsDirs = [];
        $muPluginsFiles = [];

        /** @var \DirectoryIterator $fileInfo */
        foreach ($it as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isLink()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                // wp-content/plugins/foo
                $muPluginsDirs[$fileInfo->getBasename()] = $fileInfo->getPathname();

                continue;
            } elseif ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
                // wp-content/plugins/foo.php
                $muPluginsFiles[$fileInfo->getBasename()] = $fileInfo->getPathname();
            }
        }

        /*
         * We need to handle the order of mu-plugins importing explicitly,
         * starting by the folders, and only then by the files.
         *
         * This will avoid a mu-plugin requiring a file in a folder that
         * has not been imported yet.
         */

        return array_merge($muPluginsDirs, $muPluginsFiles);
    }
}
