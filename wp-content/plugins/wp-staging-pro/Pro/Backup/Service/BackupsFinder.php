<?php

namespace WPStaging\Pro\Backup\Service;

use SplFileInfo;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\DirectoryListing;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Pro\Backup\Exceptions\BackupRuntimeException;

/**
 * Class BackupsFinder
 *
 * Finds the .wsptg backups in the filesystem.
 *
 * @package WPStaging\Pro\Backup
 */
class BackupsFinder
{
    private $directory;
    private $filesystem;
    private $filteredBackupsDirectory;
    private $directoryListing;

    public function __construct(Directory $directory, Filesystem $filesystem, DirectoryListing $directoryListing)
    {
        $this->directory = $directory;
        $this->filesystem = $filesystem;
        $this->directoryListing = $directoryListing;
    }

    public function getBackupsDirectory($refresh = false)
    {
        if ($refresh || $this->filteredBackupsDirectory === null) {
            $defaultBackupUploadsDirectory = $this->directory->getPluginUploadsDirectory() . Compressor::EXPORT_DIR_NAME;

            /**
             * Allows filtering the path to the directory Backups will be written to and read from.
             *
             * Note: changing this directory while there are Backups in the previous location will, in
             * fact, hide those Backups from the plugin. The task of managing the Backups left in the previous
             * location(s) is left to the user.
             *
             * @param string $defaultBackupUploadsDirectory The default path to the directory Backups will be read from and
             *                                              written to.
             */
            $directory = apply_filters('wpstg.backup.directory', $defaultBackupUploadsDirectory);

            $directory = trailingslashit(wp_normalize_path($directory));

            if (!$this->filesystem->mkdir($directory, true)) {
                throw BackupRuntimeException::cannotCreateBackupsDirectory($directory);
            }

            if (!is_readable($directory)) {
                throw BackupRuntimeException::backupsDirectoryNotReadable($directory);
            }

            if (!is_writeable($directory)) {
                throw BackupRuntimeException::backupsDirectoryNotWriteable($directory);
            }

            $this->directoryListing->maybeUpdateOldHtaccessWebConfig($directory);

            $this->filteredBackupsDirectory = $directory;
        }

        return $this->filteredBackupsDirectory;
    }

    /**
     * @return \DirectoryIterator A DirectoryIterator that points to the backups folder.
     */
    public function getBackupDirectories()
    {
        // Backups that the user uploaded.
        $backupsDirectory = $this->getBackupsDirectory();

        try {
            return new \DirectoryIterator($backupsDirectory);
        } catch (\Exception $e) {
            if (defined('WPSTG_DEBUG') && WPSTG_DEBUG) {
                error_log("Could not create iterator because directory $backupsDirectory does not exist.");
            }
        }
    }

    /**
     * @return \array<\SplFileInfo> An array of SplFileInfo objects of .wpstg backup files.
     */
    public function findBackups()
    {
        $it = $this->getBackupDirectories();

        $backups = [];

        /** @var SplFileInfo $file */
        foreach ($it as $file) {
            if (($file->getExtension() === 'wpstg' || $file->getExtension() === 'sql') && !$file->isLink()) {
                $backups[] = clone $file;
            }
        }

        return $backups;
    }

    /**
     * @param $md5
     *
     * @return \SplFileInfo
     */
    public function findBackupByMd5Hash($md5)
    {
        $backup = array_filter($this->findBackups(), function ($splFileInfo) use ($md5) {
            return md5($splFileInfo->getBasename()) === $md5;
        });

        if (empty($backup)) {
            throw new \UnexpectedValueException('Backup not found.');
        }

        return array_shift($backup);
    }
}
