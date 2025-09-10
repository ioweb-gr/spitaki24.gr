<?php

namespace WPStaging\Pro\Backup\Service\Database\Importer\Insert;

class ExtendedInserterWithoutTransaction extends QueryInserter
{
    protected $extendedQuery = '';

    public function processQuery(&$queryToInsert)
    {
        $this->extendInsert($queryToInsert);

        if (strlen($this->extendedQuery) > $this->maxAllowedPacket) {
            return $this->execExtendedQuery();
        }

        return null;
    }

    public function execExtendedQuery()
    {
        if (empty($this->extendedQuery)) {
            return null;
        }

        $this->extendedQuery .= ';';

        $success = $this->exec($this->extendedQuery);

        if ($success) {
            $this->extendedQuery = '';
            $this->jobImportDataDto->setInsertingInto('');

            return true;
        } else {
            if (defined('WPSTG_DEBUG') && WPSTG_DEBUG) {
                error_log("Failed Query: {$this->extendedQuery}");
            }

            $this->extendedQuery = '';
            $this->jobImportDataDto->setInsertingInto('');

            return false;
        }
    }

    protected function extendInsert(&$insertQuery)
    {
        preg_match('#^INSERT INTO `(.+?(?=`))` VALUES (\(.+\));$#', $insertQuery, $matches);

        if (count($matches) !== 3) {
            throw new \Exception("Skipping INSERT query: $insertQuery");
        }

                $insertingIntoTableName = $matches[1];

        $insertingIntoHeader = "INSERT INTO `$insertingIntoTableName` VALUES ";

        $isFirstValue = false;

        if (empty($this->jobImportDataDto->getInsertingInto())) {
            if (!empty($this->extendedQuery)) {
                throw new \UnexpectedValueException('Query is not empty, cannot proceed.');
            }
            $this->jobImportDataDto->setInsertingInto($insertingIntoTableName);
            $this->extendedQuery .= $insertingIntoHeader;
            $isFirstValue = true;
        } else {
            if ($this->jobImportDataDto->getInsertingInto() !== $insertingIntoTableName) {
                $this->execExtendedQuery();
                if (!empty($this->extendedQuery)) {
                    throw new \UnexpectedValueException('Query is not empty, cannot proceed.');
                }
                $this->jobImportDataDto->setInsertingInto($insertingIntoTableName);
                $this->extendedQuery .= $insertingIntoHeader;
                $isFirstValue = true;
            }
        }

        if ($isFirstValue) {
            $this->extendedQuery .= $matches[2];
        } else {
            $this->extendedQuery .= ",$matches[2]";
        }
    }

    public function commit()
    {
        $this->execExtendedQuery();
    }
}
