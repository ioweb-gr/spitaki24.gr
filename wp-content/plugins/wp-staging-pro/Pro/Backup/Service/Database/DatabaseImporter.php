<?php

namespace WPStaging\Pro\Backup\Service\Database;

use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Pro\Backup\Dto\Job\JobImportDataDto;
use WPStaging\Pro\Backup\Dto\JobDataDto;
use WPStaging\Pro\Backup\Dto\StepsDto;
use WPStaging\Pro\Backup\Exceptions\ThresholdException;
use WPStaging\Pro\Backup\Service\Database\Exporter\RowsExporter;
use WPStaging\Pro\Backup\Service\Database\Importer\Insert\QueryInserter;
use WPStaging\Pro\Backup\Service\Database\Importer\QueryCompatibility;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use RuntimeException;
use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Adapter\Database\InterfaceDatabaseClient;
use WPStaging\Framework\Filesystem\File;
use WPStaging\Framework\Database\SearchReplace;

class DatabaseImporter
{
    use ResourceTrait;

    private $file;

    private $totalLines;

    private $client;

    private $database;

    private $logger;

    private $stepsDto;

    private $searchReplace;

    private $searchReplaceForPrefix;

    private $wpdb;

    private $tmpDatabasePrefix;

    private $jobImportDataDto;

    private $queryInserter;

    private $smallerSearchLength;

    private $binaryFlagLength;

    private $queryCompatibility;

    public function __construct(Database $database, JobDataDto $jobImportDataDto, QueryInserter $queryInserter, QueryCompatibility $queryCompatibility)
    {
        $this->client = $database->getClient();
        $this->wpdb = $database->getWpdba();
        $this->database = $database;
        $this->jobImportDataDto = $jobImportDataDto;

        $this->queryInserter = $queryInserter;
        $this->queryCompatibility = $queryCompatibility;

        $this->binaryFlagLength = strlen(RowsExporter::BINARY_FLAG);
    }

    public function setFile($filePath)
    {
        $this->file = new File($filePath);
        $this->totalLines = $this->file->totalLines();

        return $this;
    }

    public function seekLine($line)
    {
        if (!$this->file) {
            throw new RuntimeException('Restore file is not set');
        }
        $this->file->seek($line);

        return $this;
    }

    public function import($tmpDatabasePrefix)
    {
        $this->tmpDatabasePrefix = $tmpDatabasePrefix;

        $this->setupSearchReplaceForPrefix();

        if (!$this->file) {
            throw new RuntimeException('Restore file is not set');
        }

                $this->exec("SET SESSION sql_mode = ''");

        try {
            while (true) {
                try {
                    $this->execute();
                } catch (\OutOfBoundsException $e) {
                                        $this->logger->debug($e->getMessage());
                }
            }
        } catch (FinishedQueueException $e) {
            $this->stepsDto->finish();
        } catch (ThresholdException $e) {
        } catch (\Exception $e) {
            $this->stepsDto->setCurrent($this->file->key());
            $this->logger->critical(substr($e->getMessage(), 0, 1000));
        }

                $this->queryInserter->commit();

        $this->stepsDto->setCurrent($this->file->key());
    }

    protected function setupSearchReplaceForPrefix()
    {

        $this->searchReplaceForPrefix = new SearchReplace(['{WPSTG_TMP_PREFIX}', '{WPSTG_FINAL_PREFIX}'], [$this->tmpDatabasePrefix, $this->wpdb->getClient()->prefix], true, []);
    }

    public function setup(LoggerInterface $logger, StepsDto $stepsDto)
    {
        $this->logger = $logger;
        $this->stepsDto = $stepsDto;

        $this->queryInserter->initialize($this->database, $this->jobImportDataDto, $logger);

        return $this;
    }

    public function setSearchReplace(SearchReplace $searchReplace)
    {
        $this->searchReplace = $searchReplace;

                $this->smallerSearchLength = min($searchReplace->getSmallerSearchLength(), $this->binaryFlagLength);

        return $this;
    }

    public function getTotalLines()
    {
        return $this->totalLines;
    }

    private function execute()
    {
        if ($this->isThreshold()) {
            throw new ThresholdException();
        }

        $query = $this->findExecutableQuery();

        if (!$query) {
            throw new FinishedQueueException();
        }

        $query = $this->searchReplaceForPrefix->replace($query);
        $this->replaceTableCollations($query);

        if (strpos($query, 'INSERT INTO') === 0) {
            $this->searchReplaceInsertQuery($query);
            try {
                $result = $this->queryInserter->processQuery($query);
            } catch (\Exception $e) {
                                throw $e;
            }
        } else {
                $this->queryInserter->commit();

            $this->queryCompatibility->removeDefiner($query);
            $this->queryCompatibility->removeSqlSecurity($query);
            $this->queryCompatibility->removeAlgorithm($query);

            $result = $this->exec($query);
        }

        if ($result === false) {
            switch ($this->client->errno()) {
                case 1030:
                    $this->queryCompatibility->replaceTableEngineIfUnsupported($query);
                    $result = $this->exec($query);

                    if ($result) {
                        $this->logger->warning(__('Engine changed to InnoDB, as it your MySQL server does not support MyISAM.', 'wp-staging'));
                    }

                    break;
                case 1071:
                case 1709:
                    $this->queryCompatibility->replaceTableRowFormat($query);
                    $result = $this->exec($query);

                    if ($result) {
                        $this->logger->warning(__('Row format changed to DYNAMIC, as it would exceed the maximum length according to your MySQL settings. To not see this message anymore, please upgrade your MySQL version or increase the row format.', 'wp-staging'));
                    }

                    break;
                case 1214:
                    $this->queryCompatibility->removeFullTextIndexes($query);
                    $result = $this->exec($query);

                    if ($result) {
                        $this->logger->warning(__('FULLTEXT removed from query, as your current MySQL version does not support it. To not see this message anymore, please upgrade your MySQL version.', 'wp-staging'));
                    }

                    break;
                case 1226:
                    if (stripos($this->client->error(), 'max_queries_per_hour') !== false) {
                        throw new \RuntimeException(__(sprintf('Your server has reached the maximum allowed queries per hour set by your admin or hosting provider. Please increase MySQL max_queries_per_hour_limit. <a href="https://wp-staging.com/docs/mysql-database-error-codes/" target="_blank">Technical details</a>'), 'wp-staging'));
                    } elseif (stripos($this->client->error(), 'max_updates_per_hour') !== false) {
                        throw new \RuntimeException(__(sprintf('Your server has reached the maximum allowed updates per hour set by your admin or hosting provider. Please increase MySQL max_updates_per_hour. <a href="https://wp-staging.com/docs/mysql-database-error-codes/" target="_blank">Technical details</a>'), 'wp-staging'));
                    } elseif (stripos($this->client->error(), 'max_connections_per_hour') !== false) {
                        throw new \RuntimeException(__(sprintf('Your server has reached the maximum allowed connections per hour set by your admin or hosting provider. Please increase MySQL max_connections_per_hour. <a href="https://wp-staging.com/docs/mysql-database-error-codes/" target="_blank">Technical details</a>'), 'wp-staging'));
                    } elseif (stripos($this->client->error(), 'max_user_connections') !== false) {
                        throw new \RuntimeException(__(sprintf('Your server has reached the maximum allowed connections per hour set by your admin or hosting provider. Please increase MySQL max_user_connections. <a href="https://wp-staging.com/docs/mysql-database-error-codes/" target="_blank">Technical details</a>'), 'wp-staging'));
                    }
                    break;
                case 1813:
                    throw new RuntimeException(__('Could not import the database. MySQL returned the error code 1813, which is related to a tablespace error that WP STAGING can\'t handle. Please contact your hosting company.', 'wp-staging'));
            }

            if (!$result) {
                throw new RuntimeException(__(sprintf('Could not import the database. MySQL has returned the error code %d, with message "%s". If this issue persists, try using the same MySQL version used to create this Backup (%s).', $this->client->errno(), $this->client->error(), $this->jobImportDataDto->getBackupMetadata()->getSqlServerVersion())));
            }
        }
    }

    protected function searchReplaceInsertQuery(&$query)
    {
        if (!$this->searchReplace) {
            throw new RuntimeException('SearchReplace not set');
        }

        preg_match('#^INSERT INTO `(.+?(?=`))` VALUES (\(.+\));$#', $query, $insertIntoExploded);

        if (count($insertIntoExploded) !== 3) {
            error_log($query);
            throw new \OutOfBoundsException('Skipping insert query. The query was logged....');
        }

        $values = $insertIntoExploded[2];

        preg_match_all("#'(?:[^'\\\]++|\\\.)*+'#s", $values, $valueMatches);

        if (count($valueMatches) !== 1) {
            throw new RuntimeException('Value match in query does not match.');
        }

        $valueMatches = $valueMatches[0];

        $query = "INSERT INTO `$insertIntoExploded[1]` VALUES (";

        foreach ($valueMatches as $value) {
            if (empty($value) || $value === "''") {
                $query .= "'', ";
                continue;
            }

            if ($value === "'" . RowsExporter::NULL_FLAG . "'") {
                $query .= "NULL, ";
                continue;
            }

            if (strlen($value) - 2 < $this->smallerSearchLength) {
                $query .= "{$value}, ";
                continue;
            }

            $value = substr($value, 1, -1);

            if (strpos($value, RowsExporter::BINARY_FLAG) === 0) {
                $query .= "UNHEX('" . substr($value, strlen(RowsExporter::BINARY_FLAG)) . "'), ";
                continue;
            }

            if (is_serialized($value)) {
                $this->undoMySqlRealEscape($value);
                $value = $this->searchReplace->replace($value);
                $this->mySqlRealEscape($value);
            } else {
                $value = $this->searchReplace->replace($value);
            }

            $query .= "'{$value}', ";
        }

        $query = rtrim($query, ', ');

        $query .= ');';
    }

    protected function undoMySqlRealEscape(&$query)
    {
        $replacementMap = [
            "\\0" => "\0",
            "\\n" => "\n",
            "\\r" => "\r",
            "\\t" => "\t",
            "\\Z" => chr(26),
            "\\b" => chr(8),
            '\"' => '"',
            "\'" => "'",
            "\_" => '_',
            "\%" => "%",
            '\\\\' => '\\',
        ];

        return strtr($query, $replacementMap);
    }

    protected function mySqlRealEscape(&$query)
    {
        $replacementMap = [
            "\0" => "\\0",
            "\n" => "\\n",
            "\r" => "\\r",
            "\t" => "\\t",
            chr(26) => "\\Z",
            chr(8) => "\\b",
            '"' => '\"',
            "'" => "\'",
            '_' => "\_",
            "%" => "\%",
            '\\' => '\\\\',
        ];

        return strtr($query, $replacementMap);
    }

    private function findExecutableQuery()
    {
        while (!$this->file->eof()) {
            $line = $this->getLine();
            if ($this->isExecutableQuery($line)) {
                return $line;
            }
            $this->file->next();
        }

        return null;
    }

    private function getLine()
    {
        if ($this->file->eof()) {
            return null;
        }

        $line = trim($this->file->fgets());

        return $line;
    }

    private function isExecutableQuery($query = null)
    {
        if (!$query) {
            return false;
        }

                $first2Chars = substr($query, 0, 2);
        if ($first2Chars === '--' || strpos($query, '#') === 0) {
            return false;
        }

        if ($first2Chars === '/*') {
            return false;
        }

        if (stripos($query, 'start transaction;') === 0) {
            return false;
        }

        if (stripos($query, 'commit;') === 0) {
            return false;
        }

        if (substr($query, -strlen(1)) !== ';') {
            $this->logger->debug('Skipping query because it does not end with a semi-colon... The query was logged.');
            error_log($query);

            return false;
        }

        return true;
    }

    private function exec($query)
    {
        $result = $this->client->query($query, true);

        return $result !== false;
    }

    private function replaceTableCollations(&$input)
    {
        static $search = [];
        static $replace = [];

        if (empty($search) || empty($replace)) {
            if (!$this->wpdb->getClient()->has_cap('utf8mb4_520')) {
                if (!$this->wpdb->getClient()->has_cap('utf8mb4')) {
                    $search = ['utf8mb4_0900_ai_ci', 'utf8mb4_unicode_520_ci', 'utf8mb4'];
                    $replace = ['utf8_unicode_ci', 'utf8_unicode_ci', 'utf8'];
                } else {
                    $search = ['utf8mb4_0900_ai_ci', 'utf8mb4_unicode_520_ci'];
                    $replace = ['utf8mb4_unicode_ci', 'utf8mb4_unicode_ci'];
                }
            } else {
                $search = ['utf8mb4_0900_ai_ci'];
                $replace = ['utf8mb4_unicode_520_ci'];
            }
        }

        $input = str_replace($search, $replace, $input);
    }
}
