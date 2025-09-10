<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobImport;

use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Pro\Backup\Dto\StepsDto;
use WPStaging\Pro\Backup\Service\Database\DatabaseImporter;
use WPStaging\Pro\Backup\Service\Database\Importer\DatabaseSearchReplacer;
use WPStaging\Pro\Backup\Task\ImportTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Framework\Utils\Cache\Cache;

class ImportDatabaseTask extends ImportTask
{
    /** @var DatabaseImporter */
    private $databaseImport;

    /** @var PathIdentifier */
    private $pathIdentifier;

    /** @var DatabaseSearchReplacer */
    private $databaseSearchReplacer;

    public function __construct(DatabaseImporter $databaseImport, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, PathIdentifier $pathIdentifier, DatabaseSearchReplacer $databaseSearchReplacer)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->databaseImport = $databaseImport;
        $this->databaseImport->setup($this->logger, $stepsDto);
        $this->pathIdentifier = $pathIdentifier;
        $this->databaseSearchReplacer = $databaseSearchReplacer;
    }

    public static function getTaskName()
    {
        return 'backup_restore_database';
    }

    public static function getTaskTitle()
    {
        return 'Restoring Database';
    }

    public function execute()
    {
        $this->prepare();

        $start = microtime(true);
        $before = $this->stepsDto->getCurrent();

        $this->databaseImport->import($this->jobDataDto->getTmpDatabasePrefix());

        $perSecond = ($this->stepsDto->getCurrent() - $before) / (microtime(true) - $start);
        $this->logger->info(sprintf('Executed %s/%s queries (%s queries per second)', number_format_i18n($this->stepsDto->getCurrent()), number_format_i18n($this->stepsDto->getTotal()), number_format_i18n((int)$perSecond)));

        return $this->generateResponse(false);
    }

    /**
     * @see \WPStaging\Pro\Backup\Service\Database\Exporter\RowsExporter::setupExportSearchReplace For Exporter Search/Replace.
     */
    public function prepare()
    {
        $databaseFile = $this->pathIdentifier->transformIdentifiableToPath($this->jobDataDto->getBackupMetadata()->getDatabaseFile());

        if (!file_exists($databaseFile)) {
            throw new \RuntimeException(__('Could not find database file to import.', 'wp-staging'));
        }

        $this->databaseImport->setFile($databaseFile);
        $this->databaseImport->seekLine($this->stepsDto->getCurrent());

        if (!$this->stepsDto->getTotal()) {
            $this->stepsDto->setTotal($this->databaseImport->getTotalLines());
        }

        $this->databaseImport->setSearchReplace($this->databaseSearchReplacer->getSearchAndReplace($this->jobDataDto, get_site_url(), get_home_url()));
    }
}
