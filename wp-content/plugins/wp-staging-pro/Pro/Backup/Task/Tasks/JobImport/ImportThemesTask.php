<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobImport;

use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Pro\Backup\Task\FileImportTask;

class ImportThemesTask extends FileImportTask
{
    public static function getTaskName()
    {
        return 'backup_restore_themes';
    }

    public static function getTaskTitle()
    {
        return 'Restoring Themes';
    }

    protected function buildQueue()
    {
        try {
            $themesToImport = $this->getThemesToImport();
        } catch (\Exception $e) {
            // Folder does not exist. Likely there are no themes to import.
            return;
        }

        $destDir = $this->directory->getActiveThemeParentDirectory();

        try {
            $existingThemes = $this->getExistingThemes();
        } catch (\Exception $e) {
            $this->logger->critical(__(sprintf('Destination themes folder could not be found or created at "%s"', (string)apply_filters('wpstg.import.themes.destDir', $destDir))));

            return;
        }

        foreach ($themesToImport as $themeName => $themePath) {
            /*
             * Scenario: Importing a theme that already exists
             * 1. Backup old theme
             * 2. Import new theme
             * 3. Delete backup
             */
            if (array_key_exists($themeName, $existingThemes)) {
                $this->enqueueMove($existingThemes[$themeName], "{$destDir}{$themeName}{$this->getOriginalSuffix()}");
                $this->enqueueMove($themesToImport[$themeName], "{$destDir}{$themeName}");
                $this->enqueueDelete("{$destDir}{$themeName}{$this->getOriginalSuffix()}");
                continue;
            }

            /*
             * Scenario 2: Importing a theme that does not yet exist
             */
            $this->enqueueMove($themesToImport[$themeName], "$destDir$themeName");
        }
    }

    /**
     * @return array An array of paths of themes to import.
     */
    private function getThemesToImport()
    {
        $tmpDir = $this->jobDataDto->getTmpDirectory() . PathIdentifier::IDENTIFIER_THEMES;

        return $this->findThemesInDir($tmpDir);
    }

    /**
     * @return array An array of paths of existing themes.
     */
    private function getExistingThemes()
    {
        $destDir = $this->directory->getActiveThemeParentDirectory();
        $destDir = (string)apply_filters('wpstg.import.themes.destDir', $destDir);
        $this->filesystem->mkdir($destDir);

        return $this->findThemesInDir($destDir);
    }

    /**
     * @param string $path Folder to look for themes, eg: '/var/www/wp-content/themes'
     *
     * @example [
     *              'twentynineteen' => '/var/www/wp-content/themes/twentynineteen',
     *              'twentytwenty' => '/var/www/wp-content/themes/twentytwenty',
     *          ]
     *
     * @return array An array of paths of themes found in the root of given directory,
     *               where the index is the name of the theme, and the value it's path.
     */
    private function findThemesInDir($path)
    {
        $it = new \DirectoryIterator($path);

        $themes = [];

        /** @var \DirectoryIterator $item */
        foreach ($it as $item) {
            if ($item->isDir()) {
                if (file_exists(trailingslashit($item->getPathname()) . 'style.css')) {
                    $themes[$item->getBasename()] = $item->getPathname();
                }
            }
        }

        return $themes;
    }
}
