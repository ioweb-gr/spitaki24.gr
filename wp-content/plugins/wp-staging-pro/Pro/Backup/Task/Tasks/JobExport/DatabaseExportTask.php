<?php

namespace WPStaging\Pro\Backup\Task\Tasks\JobExport;

use DateTime;
use Exception;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Pro\Backup\Dto\StepsDto;
use WPStaging\Pro\Backup\Service\Database\Exporter\DDLExporter;
use WPStaging\Pro\Backup\Service\Database\Exporter\RowsExporter;
use WPStaging\Pro\Backup\Task\ExportTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Pro\Backup\Dto\TaskResponseDto;
use WPStaging\Framework\Utils\Cache\Cache;

class DatabaseExportTask extends ExportTask
{
    const FILE_FORMAT = 'sql';

    /** @var Directory */
    private $directory;

    public function __construct(Directory $directory, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->directory = $directory;
    }

    public static function getTaskName()
    {
        return 'backup_export_database';
    }

    public static function getTaskTitle()
    {
        return 'Export database';
    }

    /**
     * @return object|TaskResponseDto
     * @throws Exception
     */
    public function execute()
    {
        $this->setupDatabaseFilePathName();

        // First request: Create DDL
        if (!$this->stepsDto->getTotal()) {
            $ddlExporter = WPStaging::getInstance()->getContainer()->make(DDLExporter::class);
            $ddlExporter->setFileName($this->jobDataDto->getDatabaseFile());
            $ddlExporter->exportDDLTablesAndViews();
            $this->jobDataDto->setTablesToExport($ddlExporter->getTables());

            $this->stepsDto->setTotal(count($this->jobDataDto->getTablesToExport()));

            // Early bail: DDL created, do not increase step, so that the next request can start exporting rows from the first table.
            return $this->generateResponse(false);
        }

        // Safety check: Check that the DDL was successfully created
        if (empty($this->jobDataDto->getTablesToExport())) {
            $this->logger->critical('Could not create the tables DDL.');
            throw new Exception('Could not create the tables DDL.');
        }

        // Lazy instantiation
        $rowsExporter = WPStaging::getInstance()->getContainer()->make(RowsExporter::class);
        $rowsExporter->setFileName($this->jobDataDto->getDatabaseFile());
        $rowsExporter->setTables($this->jobDataDto->getTablesToExport());

        do {
            $rowsExporter->setTableIndex($this->stepsDto->getCurrent());
            $rowsExporter->setTableRowsOffset($this->jobDataDto->getTableRowsOffset());
            $rowsExporter->setTotalRowsExported($this->jobDataDto->getTotalRowsExported());

            // Count rows once per table
            if ($this->jobDataDto->getTableRowsOffset() === 0) {
                $this->jobDataDto->setTotalRowsOfTableBeingExported($rowsExporter->countTotalRows());
            }

            $rowsExporter->setTotalRowsInCurrentTable($this->jobDataDto->getTotalRowsOfTableBeingExported());

            try {
                $rowsLeftToExport = $rowsExporter->export($this->jobDataDto->getId(), $this->logger);
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
                throw $e;
            }

            $this->stepsDto->setCurrent($rowsExporter->getTableIndex());
            $this->jobDataDto->setTotalRowsExported($rowsExporter->getTotalRowsExported());
            $this->jobDataDto->setTableRowsOffset($rowsExporter->getTableRowsOffset());

            $this->logger->info(sprintf(
                __('Export database: Table %s. Rows: %s/%s', 'wp-staging'),
                $rowsExporter->getTableBeingExported(),
                number_format_i18n($rowsExporter->getTotalRowsExported()),
                number_format_i18n($this->jobDataDto->getTotalRowsOfTableBeingExported())
            ));

            // Done with this table.
            if ($rowsLeftToExport === 0) {
                $this->jobDataDto->setTotalRowsExported(0);
                $this->jobDataDto->setTableRowsOffset(0);
                $this->jobDataDto->setTableAverageRowLength(0);
                $this->stepsDto->incrementCurrentStep();
            }
        } while (!$this->isThreshold() && !$this->stepsDto->isFinished());

        return $this->generateResponse(false);
    }

    private function setupDatabaseFilePathName()
    {
        // Early bail: Database file already set
        if ($this->jobDataDto->getDatabaseFile()) {
            return;
        }

        global $wpdb;

        $basename = sprintf(
            '%s_%s_%s.%s',
            rtrim($wpdb->base_prefix, '_-'),
            (new DateTime())->format('Y-m-d_H-i-s'),
            md5(mt_rand()),
            self::FILE_FORMAT
        );

        $this->jobDataDto->setDatabaseFile($this->directory->getCacheDirectory() . $basename);
    }
}
