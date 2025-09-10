<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Backup\Task\Tasks\JobExport;

use Exception;
use WPStaging\Framework\Filesystem\File;
use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Dto\StepsDto;
use WPStaging\Pro\Backup\Dto\TaskResponseDto;
use WPStaging\Pro\Backup\Dto\Task\Export\Response\CombineExportResponseDto;
use WPStaging\Pro\Backup\Entity\BackupMetadata;
use WPStaging\Pro\Backup\Entity\ListableBackup;
use WPStaging\Pro\Backup\Service\BackupMetadataEditor;
use WPStaging\Pro\Backup\Task\ExportTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Pro\Backup\Service\Compressor;

class CombineExportTask extends ExportTask
{
    /** @var Compressor */
    private $compressor;
    private $wpdb;

    /** @var PathIdentifier */
    protected $pathIdentifier;

    /** @var BackupMetadataEditor */
    protected $backupMetadataEditor;

    public function __construct(Compressor $compressor, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, PathIdentifier $pathIdentifier, BackupMetadataEditor $backupMetadataEditor)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);

        global $wpdb;
        $this->compressor = $compressor;
        $this->wpdb = $wpdb;
        $this->pathIdentifier = $pathIdentifier;
        $this->backupMetadataEditor = $backupMetadataEditor;
    }

    public static function getTaskName()
    {
        return 'backup_export_combine';
    }

    public static function getTaskTitle()
    {
        return 'Finalizing Backup Export';
    }

    public function execute()
    {
        $compressorDto = $this->compressor->getDto();

        $backupMetadata = $compressorDto->getBackupMetadata();
        $backupMetadata->setTotalDirectories($this->jobDataDto->getTotalDirectories());
        $backupMetadata->setTotalFiles($this->jobDataDto->getTotalFiles());
        $backupMetadata->setName($this->jobDataDto->getName());
        $backupMetadata->setIsAutomatedBackup($this->jobDataDto->getIsAutomatedBackup());

        global $wpdb;
        $backupMetadata->setPrefix($wpdb->base_prefix);

        // What the backup exports
        $backupMetadata->setIsExportingPlugins($this->jobDataDto->getIsExportingPlugins());
        $backupMetadata->setIsExportingMuPlugins($this->jobDataDto->getIsExportingMuPlugins());
        $backupMetadata->setIsExportingThemes($this->jobDataDto->getIsExportingThemes());
        $backupMetadata->setIsExportingUploads($this->jobDataDto->getIsExportingUploads());
        $backupMetadata->setIsExportingOtherWpContentFiles($this->jobDataDto->getIsExportingOtherWpContentFiles());
        $backupMetadata->setIsExportingDatabase($this->jobDataDto->getIsExportingDatabase());

        $this->addSystemInfoToBackupMetadata($backupMetadata);

        if ($this->jobDataDto->getIsExportingDatabase()) {
            $backupMetadata->setDatabaseFile($this->pathIdentifier->transformPathToIdentifiable($this->jobDataDto->getDatabaseFile()));
            $backupMetadata->setDatabaseFileSize($this->jobDataDto->getDatabaseFileSize());

            $maxTableLength = 0;
            foreach ($this->jobDataDto->getTablesToExport() as $table) {
                // Get the biggest table name, without the prefix.
                $maxTableLength = max($maxTableLength, strlen(substr($table, strlen($this->wpdb->base_prefix))));
            }

            $backupMetadata->setMaxTableLength($maxTableLength);
        }

        if ($this->jobDataDto->getIsExportingPlugins()) {
            $backupMetadata->setPlugins(array_keys(get_plugins()));
        }

        if ($this->jobDataDto->getIsExportingMuPlugins()) {
            $backupMetadata->setMuPlugins(array_keys(get_mu_plugins()));
        }

        if ($this->jobDataDto->getIsExportingThemes()) {
            $backupMetadata->setThemes(array_keys(search_theme_directories()));
        }

        // Write the Backup metadata
        $exportFilePath = null;
        try {
            $exportFilePath = $this->compressor->generateBackupMetadata();
        } catch (Exception $e) {
            $this->logger->critical('Failed to generate backup file: ' . $e->getMessage());
        }

        // Store the "Size" of the Backup in the metadata, which is something we can only do after the backup is final.
        try {
            $this->signBackup($exportFilePath);
        } catch (Exception $e) {
            $this->logger->critical('The backup file could not be signed for consistency.');

            return $this->generateResponse();
        }

        // Validate the Backup
        if ($exportFilePath) {
            try {
                $this->validateBackup($exportFilePath);
            } catch (Exception $e) {
                $this->logger->critical('The backup file seems to be invalid.');

                return $this->generateResponse();
            }

            $this->stepsDto->finish();

            return $this->overrideGenerateResponse($this->makeListableBackup($exportFilePath));
        }

        $steps = $this->stepsDto;
        $steps->setCurrent($compressorDto->getWrittenBytesTotal());
        $steps->setTotal($compressorDto->getFileSize());

        $this->logger->info(sprintf('Written %d bytes to compressed export', $compressorDto->getWrittenBytesTotal()));

        return $this->overrideGenerateResponse();
    }

    /**
     * Signing the Backup aims to give it an identifier that can be checked for it's consistency.
     *
     * Currently, we use the size of the file. We can use this information later, during Restore or Upload,
     * to check if the Backup file we have is complete and matches the expected one.
     *
     * @param string $exportFilePath
     */
    protected function signBackup($exportFilePath)
    {
        clearstatcache();
        if (!is_file($exportFilePath)) {
            throw new \RuntimeException('The backup file is invalid.');
        }

        $file = new File($exportFilePath, File::MODE_APPEND_AND_READ);
        $backupMetadata = new BackupMetadata();
        $backupMetadata->hydrate($file->readBackupMetadata());

        /*
         * Before: "backupSize": ""
         * After:  "backupSize": 123456
         */
        $backupMetadata->setBackupSize($file->getSize() - 2 + strlen($file->getSize()));

        $this->backupMetadataEditor->setBackupMetadata($file, $backupMetadata);
    }

    /**
     * Check if the backup was successfully validated.
     *
     * @param string $exportFilePath
     */
    protected function validateBackup($exportFilePath)
    {
        clearstatcache();
        if (!is_file($exportFilePath)) {
            throw new \RuntimeException('The backup file is invalid.');
        }

        $file = new File($exportFilePath);

        $backupMetadata = new BackupMetadata();
        $backupMetadata->hydrate($file->readBackupMetadata());

        if ($backupMetadata->getName() !== $this->jobDataDto->getName()) {
            throw new \RuntimeException('The backup file seems to be invalid (Unexpected Name in Metadata).');
        }

        if ($backupMetadata->getBackupSize() !== $file->getSize()) {
            throw new \RuntimeException('The backup file seems to be invalid (Unexpected Size in Metadata).');
        }

        $this->logger->info('The backup was validated successfully.');
    }

    /**
     * @see \wp_version_check
     * @see https://codex.wordpress.org/Converting_Database_Character_Sets
     */
    protected function addSystemInfoToBackupMetadata(BackupMetadata &$backupMetadata)
    {
        /**
         * @var string $wp_version
         * @var int    $wp_db_version
         */
        include ABSPATH . WPINC . '/version.php';

        $mysqlVersion = preg_replace('/[^0-9.].*/', '', $this->wpdb->db_version());

        $backupMetadata->setPhpVersion(phpversion());
        $backupMetadata->setWpVersion($wp_version); /** @phpstan-ignore-line */
        $backupMetadata->setWpDbVersion($wp_db_version); /** @phpstan-ignore-line */
        $backupMetadata->setDbCollate($this->wpdb->collate);
        $backupMetadata->setDbCharset($this->wpdb->charset);
        $backupMetadata->setSqlServerVersion($mysqlVersion);
    }

    /**
     * @param null|ListableBackup $backup
     *
     * @return CombineExportResponseDto|TaskResponseDto
     */
    private function overrideGenerateResponse(ListableBackup $backup = null)
    {
        add_filter('wpstg.task.response', function ($response) use ($backup) {
            if ($response instanceof CombineExportResponseDto) {
                $response->setBackupMd5($backup ? $backup->md5BaseName : null);
                $response->setBackupSize($backup ? size_format($backup->size) : null);
            }

            return $response;
        });

        return $this->generateResponse();
    }

    protected function getResponseDto()
    {
        return new CombineExportResponseDto();
    }

    /**
     * This is used to display the "Download Modal" after the backup completes.
     *
     * @see string src/Backend/public/js/wpstg-admin.js, search for "wpstg--backups--export"
     *
     * @param string $exportFilePath
     *
     * @return ListableBackup
     */
    protected function makeListableBackup($exportFilePath)
    {
        clearstatcache();
        $backup = new ListableBackup();
        $backup->md5BaseName = md5(basename($exportFilePath));
        $backup->size = filesize($exportFilePath);

        return $backup;
    }
}
