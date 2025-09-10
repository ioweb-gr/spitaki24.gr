<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobImport;

use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Pro\Backup\Task\FileImportTask;

class ImportPluginsTask extends FileImportTask
{
    public static function getTaskName()
    {
        return 'backup_restore_plugins';
    }

    public static function getTaskTitle()
    {
        return 'Restoring Plugins';
    }

    protected function buildQueue()
    {
        try {
            $pluginsToImport = $this->getPluginsToImport();
        } catch (\Exception $e) {
            // Folder does not exist. Likely there are no plugins to import.
            return;
        }

        $destDir = $this->directory->getPluginsDirectory();

        try {
            $existingPlugins  = $this->getExistingPlugins();
        } catch (\Exception $e) {
            $this->logger->critical(__(sprintf('Destination plugins folder could not be found not created at "%s"', (string)apply_filters('wpstg.import.plugins.destDir', $destDir))));

            return;
        }

        foreach ($pluginsToImport as $pluginSlug => $pluginPath) {
            /*
             * Scenario: Importing a plugin that already exists
             * 1. Backup old plugin
             * 2. Import new plugin
             * 3. Delete backup
             */
            if (array_key_exists($pluginSlug, $existingPlugins)) {
                $this->enqueueMove($existingPlugins[$pluginSlug], "{$destDir}{$pluginSlug}{$this->getOriginalSuffix()}");
                $this->enqueueMove($pluginsToImport[$pluginSlug], "{$destDir}{$pluginSlug}");
                $this->enqueueDelete("{$destDir}{$pluginSlug}{$this->getOriginalSuffix()}");
                continue;
            }

            /*
             * Scenario 2: Importing a plugin that does not yet exist
             */
            $this->enqueueMove($pluginsToImport[$pluginSlug], "{$destDir}{$pluginSlug}");
        }
    }

    /**
     * @return array An array of paths of plugins to import.
     */
    private function getPluginsToImport()
    {
        $tmpDir = $this->jobDataDto->getTmpDirectory() . PathIdentifier::IDENTIFIER_PLUGINS;

        return $this->findPluginsInDir($tmpDir);
    }

    /**
     * @return array An array of paths of existing plugins.
     */
    private function getExistingPlugins()
    {
        $destDir = $this->directory->getPluginsDirectory();
        $destDir = (string)apply_filters('wpstg.import.plugins.destDir', $destDir);
        $this->filesystem->mkdir($destDir);

        return $this->findPluginsInDir($destDir);
    }

    /**
     * @param string $path Folder to look for plugins, eg: '/var/www/wp-content/plugins'
     *
     * @example [
     *              'foo' => '/var/www/wp-content/plugins/foo',
     *              'foo.php' => '/var/www/wp-content/plugins/foo.php',
     *          ]
     *
     * @return array An array of paths of plugins found in the root of given directory,
     *               where the index is the name of the plugin, and the value it's path.
     */
    private function findPluginsInDir($path)
    {
        $it = new \DirectoryIterator($path);

        $plugins = [];

        /** @var \DirectoryIterator $fileInfo */
        foreach ($it as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isLink()) {
                continue;
            }

            // wp-content/plugins/foo | Skip the current active wp staging plugin slug e.g wp-staging-pro, wp-staging-dev, wp-staging-pro_1, etc.
            if ($fileInfo->isDir() && $fileInfo->getFilename() !== WPSTG_PLUGIN_SLUG) {
                $plugins[$fileInfo->getBasename()] = $fileInfo->getPathname();

                continue;
            }

            // wp-content/plugins/foo.php
            if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php' && $fileInfo->getBasename() !== 'index.php') {
                $plugins[$fileInfo->getBasename()] = $fileInfo->getPathname();

                continue;
            }
        }

        return $plugins;
    }
}
