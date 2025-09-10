<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobExport;

use RuntimeException;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\DiskWriteCheck;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Dto\StepsDto;
use WPStaging\Pro\Backup\Exceptions\DiskNotWritableException;
use WPStaging\Pro\Backup\Task\ExportTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class ExportRequirementsCheckTask extends ExportTask
{
    /** @var Directory */
    protected $directory;

    /** @var DiskWriteCheck */
    protected $diskWriteCheck;

    public function __construct(
        Directory $directory,
        LoggerInterface $logger,
        Cache $cache,
        StepsDto $stepsDto,
        SeekableQueueInterface $taskQueue,
        DiskWriteCheck $diskWriteCheck
    ) {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->directory = $directory;
        $this->diskWriteCheck = $diskWriteCheck;
    }

    public static function getTaskName()
    {
        return 'backup_site_requirements_check';
    }

    public static function getTaskTitle()
    {
        return 'Requirements Check';
    }

    public function execute()
    {
        if (!$this->stepsDto->getTotal()) {
            $this->stepsDto->setTotal(1);
        }

        try {
            $this->cannotExportEmptyBackup();
            $this->cannotImportIfCantWriteToDisk();
            $this->checkFilesystemPermissions();
        } catch (RuntimeException $e) {
            $this->logger->critical($e->getMessage());

            return $this->generateResponse(false);
        }

        $this->logger->info(__('Requirements check passed...', 'wp-staging'));

        return $this->generateResponse();
    }

    protected function cannotExportEmptyBackup()
    {
        if (
            !$this->jobDataDto->getIsExportingDatabase()
            && !$this->jobDataDto->getIsExportingPlugins()
            && !$this->jobDataDto->getIsExportingUploads()
            && !$this->jobDataDto->getIsExportingMuPlugins()
            && !$this->jobDataDto->getIsExportingThemes()
            && !$this->jobDataDto->getIsExportingOtherWpContentFiles()
        ) {
            throw new RuntimeException(__('You must select at least one item to export.', 'wp-staging'));
        }
    }

    protected function cannotImportIfCantWriteToDisk()
    {
        try {
            $this->diskWriteCheck->testDiskIsWriteable();
        } catch (DiskNotWritableException $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * @throws RuntimeException When PHP does not have enough permission to a required directory.
     */
    protected function checkFilesystemPermissions()
    {
        clearstatcache();

        if (!is_writable($this->directory->getPluginUploadsDirectory())) {
            throw new RuntimeException(__(sprintf('PHP does not have enough permission to write to the WP STAGING data directory: %s', $this->directory->getPluginUploadsDirectory()), 'wp-staging'));
        }

        if ($this->jobDataDto->getIsExportingPlugins()) {
            if (!is_readable($this->directory->getPluginsDirectory())) {
                throw new RuntimeException(__(sprintf('PHP does not have enough permission to read the plugins directory: %s', $this->directory->getPluginsDirectory()), 'wp-staging'));
            }
        }

        if ($this->jobDataDto->getIsExportingThemes()) {
            foreach ($this->directory->getAllThemesDirectories() as $themesDirectory) {
                if (!is_readable($themesDirectory)) {
                    throw new RuntimeException(__(sprintf('PHP does not have enough permission to read a themes directory: %s', $themesDirectory), 'wp-staging'));
                }
            }
        }

        if ($this->jobDataDto->getIsExportingMuPlugins()) {
            if (!is_readable($this->directory->getMuPluginsDirectory()) && !wp_mkdir_p($this->directory->getMuPluginsDirectory())) {
                throw new RuntimeException(__(sprintf('PHP does not have enough permission to read the mu-plugins directory: %s', $this->directory->getMuPluginsDirectory()), 'wp-staging'));
            }
        }

        if ($this->jobDataDto->getIsExportingUploads()) {
            if (!is_readable($this->directory->getUploadsDirectory())) {
                throw new RuntimeException(__(sprintf('PHP does not have enough permission to read the uploads directory: %s', $this->directory->getUploadsDirectory()), 'wp-staging'));
            }
        }

        if ($this->jobDataDto->getIsExportingOtherWpContentFiles()) {
            if (!is_readable($this->directory->getWpContentDirectory())) {
                throw new RuntimeException(__(sprintf('PHP does not have enough permission to read the wp-content directory: %s', $this->directory->getWpContentDirectory()), 'wp-staging'));
            }
        }
    }
}
