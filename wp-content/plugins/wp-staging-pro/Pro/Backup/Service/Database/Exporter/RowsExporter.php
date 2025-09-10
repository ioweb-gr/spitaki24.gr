<?php

namespace WPStaging\Pro\Backup\Service\Database\Exporter;

use Exception;
use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Database\SearchReplace;
use WPStaging\Framework\Traits\DatabaseSearchReplaceTrait;
use WPStaging\Framework\Traits\MySQLRowsGeneratorTrait;
use WPStaging\Pro\Backup\Dto\Job\JobExportDataDto;
use WPStaging\Pro\Backup\Dto\JobDataDto;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class RowsExporter extends AbstractExporter
{
    use MySQLRowsGeneratorTrait;
    use DatabaseSearchReplaceTrait;

    protected $tableIndex = 0;

    protected $tableRowsOffset = 0;

    protected $totalRowsExported;

    protected $totalRowsInCurrentTable;

    protected $tables = [];

    protected $prefixedValues = [];

    protected $queriesToInsert = '';

    protected $jobDataDto;

    protected $logger;

    protected $writeToFileCount = 0;

    protected $searchReplaceForPrefix;


    const NULL_FLAG = "{WPSTG_NULL}";


    const BINARY_FLAG = "{WPSTG_BINARY}";

    public function __construct(Database $database, JobDataDto $jobDataDto)
    {
        parent::__construct($database);

        $this->jobDataDto = $jobDataDto;

        $this->prefixedValues = array_flip(array_map(function ($unprefixedValue) {
            return $this->database->getPrefix() . $unprefixedValue;
        }, ['user_roles', 'capabilities', 'user_level', 'dashboard_quick_press_last_post_id', 'user-settings', 'user-settings-time']));
    }

    public function getTableRowsOffset()
    {
        return (int)$this->tableRowsOffset;
    }

    public function setTableRowsOffset($tableRowsOffset)
    {
        $this->tableRowsOffset = $tableRowsOffset;
    }

    public function getTableIndex()
    {
        return (int)$this->tableIndex;
    }

    public function setTableIndex($tableIndex)
    {
        $this->tableIndex = $tableIndex;
    }

    public function setTotalRowsExported($totalRowsExported)
    {
        $this->totalRowsExported = $totalRowsExported;
    }

    public function setTotalRowsInCurrentTable($totalRows)
    {
        $this->totalRowsInCurrentTable = $totalRows;
    }

    public function getTotalRowsExported()
    {
        return (int)$this->totalRowsExported;
    }

    public function getTableBeingExported()
    {
        return array_key_exists($this->tableIndex, $this->tables) ? $this->tables[$this->tableIndex] : '';
    }

    public function countTotalRows()
    {
        if (!array_key_exists($this->tableIndex, $this->tables)) {
            throw new \RuntimeException('Table not found.');
        }

        $tableName = $this->tables[$this->tableIndex];

        return (int)$this->client->query("SELECT COUNT(*) as `totalRows` FROM $tableName")->fetch_object()->totalRows;
    }

    protected function setupExportSearchReplace()
    {
        global $wpdb;
        $this->searchReplaceForPrefix = new SearchReplace([$wpdb->base_prefix], ['{WPSTG_FINAL_PREFIX}'], true, []);
    }

    public function export($jobId, LoggerInterface $logger)
    {
        $this->client->query("SET SESSION sql_mode = ''");

        $tableName = $this->tables[$this->tableIndex];

        $prefixedTableName = $this->getPrefixedTableName($tableName);
        $tableColumns = $this->getColumnTypes($tableName);
        $databaseName = $this->database->getWpdba()->getClient()->__get('dbname');

        try {
            $numericPrimaryKey = $this->getPrimaryKey($databaseName, $tableName);
        } catch (Exception $e) {
            $numericPrimaryKey = null;
            if ($this->jobDataDto->getTableRowsOffset() === 0 && $this->jobDataDto->getTotalRowsOfTableBeingExported() > 300000) {
                $logger->info("The table $tableName does not have a compatible primary key, so it will get slower the more rows it exports...");
            }
        }

        $this->setupExportSearchReplace();

        do {
            $data = $this->rowsGenerator($databaseName, $tableName, $numericPrimaryKey, $this->tableRowsOffset, "rowsExporter_$jobId", $this->client, $this->jobDataDto);

            foreach ($data as $row) {
                $this->writeQueryInsert($row, $prefixedTableName, $tableColumns);

                $this->writeToFileCount++;
                $this->totalRowsExported++;

                if (!empty($numericPrimaryKey)) {
                    $this->tableRowsOffset = $row[$numericPrimaryKey];
                } else {
                    $this->tableRowsOffset++;
                }

                if ($this->writeToFileCount >= 10) {
                    $this->file->fwrite($this->queriesToInsert);
                    $this->queriesToInsert = '';
                    $this->writeToFileCount = 0;
                }
            }

            $rowsLeftToProcess = max($this->totalRowsInCurrentTable - $this->totalRowsExported, 0);
        } while (!$this->isThreshold() && $rowsLeftToProcess > 0);

        if (!empty($this->queriesToInsert)) {
            $this->file->fwrite($this->queriesToInsert);
            $this->queriesToInsert = '';
        }

        return $rowsLeftToProcess;
    }

    public function setTables(array $tables = [])
    {
        $this->tables = $tables;
    }

    private function getColumnTypes($tableName)
    {
        $column_types = [];

        $result = $this->client->query("SHOW COLUMNS FROM `{$tableName}`");
        while ($row = $this->client->fetchAssoc($result)) {
            if (isset($row['Field'])) {
                $column_types[strtolower($row['Field'])] = strtolower($row['Type']);
            }
        }

        $this->client->freeResult($result);

        return $column_types;
    }

    private function getPrimaryKey($databaseName, $tableName)
    {
        $query = "SELECT COLUMN_NAME 
                  FROM INFORMATION_SCHEMA.COLUMNS
                  WHERE TABLE_NAME = '$tableName'
                  AND TABLE_SCHEMA = '$databaseName'
                  AND IS_NULLABLE = 'NO'
                  AND DATA_TYPE IN ('int', 'bigint', 'smallint', 'mediumint')
                  AND COLUMN_KEY = 'PRI'
                  AND EXTRA like '%auto_increment%';";

        $result = $this->client->query($query);

        $primaryKey = $this->client->fetchObject($result);

        $this->client->freeResult($result);

        if (!is_object($primaryKey)) {
            throw new \UnexpectedValueException();
        }

        if (!property_exists($primaryKey, 'COLUMN_NAME')) {
            throw new \UnexpectedValueException();
        }

        if (empty($primaryKey->COLUMN_NAME)) {
            throw new \UnexpectedValueException();
        }

        return $primaryKey->COLUMN_NAME;
    }

    private function isTableCorrupt()
    {
        return $this->client->errno() === 1194;
    }

    private function writeQueryInsert($row, $prefixedTableName, $tableColumns)
    {
        try {
            foreach ($row as $column => &$value) {
                if (is_null($value)) {
                    $nullFlag = self::NULL_FLAG;
                    $value = "'$nullFlag'";
                    continue;
                }

                if (
                                    strpos($tableColumns[strtolower($column)], 'binary') !== false ||
                                    strpos($tableColumns[strtolower($column)], 'blob') !== false
                ) {
                    $value = bin2hex($value);
                    $binaryFlag = static::BINARY_FLAG;
                    $value = "'$binaryFlag$value'";
                    continue;
                }

                if ($prefixedTableName === '{WPSTG_TMP_PREFIX}options' && $column === 'option_name') {
                    if (substr($value, 0, 1) === '_') {
                        foreach (['_transient_', '_site_transient_', '_wc_session_'] as $excludedOption) {
                            if (strpos($value, $excludedOption) === 0) {
                                throw new \OutOfBoundsException();
                            }
                        }
                    }

                    if (isset($this->prefixedValues[$value])) {
                        $value = $this->searchReplaceForPrefix->replace($value);
                    }
                }

                if ($prefixedTableName === '{WPSTG_TMP_PREFIX}usermeta' && $column === 'meta_key') {
                    if (isset($this->prefixedValues[$value])) {
                        $value = $this->searchReplaceForPrefix->replace($value);
                    }
                }

                $value = "'{$this->client->escape($value)}'";
            }

            $this->queriesToInsert .= "INSERT INTO `{$prefixedTableName}` VALUES (" . implode(',', $row) . ");\n";
        } catch (Exception $e) {
        }
    }
}
