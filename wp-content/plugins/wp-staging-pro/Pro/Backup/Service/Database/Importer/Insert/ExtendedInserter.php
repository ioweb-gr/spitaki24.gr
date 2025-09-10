<?php

namespace WPStaging\Pro\Backup\Service\Database\Importer\Insert;

use WPStaging\Core\WPStaging;

class ExtendedInserter extends TransactionInserter
{
    protected $extendedQuery = '';

    public function processQuery(&$queryToInsert)
    {
        $lengthQueryToInsert = strlen($queryToInsert);
        if ($lengthQueryToInsert > $this->maxAllowedPacket) {
            throw new \OutOfRangeException(sprintf(
                'A query was skipped because it exceeded the maximum size. Query size: %s | max_allowed_packet: %s',
                size_format(strlen($queryToInsert)),
                size_format($this->maxAllowedPacket)
            ));
        }

        if (false && $lengthQueryToInsert > 1 * MB_IN_BYTES) {
            $this->execExtendedQuery();
            $this->commit();

            WPStaging::getInstance()->getContainer()->make(SingleInserter::class)->exec($queryToInsert);

            return;
        }

        $this->maybeStartTransaction();

        $this->extendInsert($queryToInsert);

        if (strlen($this->extendedQuery) > $this->maxAllowedPacket) {
            $this->execExtendedQuery();
        }

        $this->maybeCommit();
    }

    public function commit()
    {
        if (empty($this->extendedQuery)) {
            return;
        }

        $this->maybeStartTransaction();

        $this->execExtendedQuery(true);

        parent::commit();
    }

    public function execExtendedQuery($isCommitting = false)
    {
        if (empty($this->extendedQuery)) {
            return;
        }

        $this->extendedQuery .= ';';

        $success = $this->exec($this->extendedQuery);

        if ($success) {
            $this->currentTransactionSize += strlen($this->extendedQuery);

            $this->extendedQuery = '';
            $this->jobImportDataDto->setInsertingInto('');
        } else {
            if (defined('WPSTG_DEBUG') && WPSTG_DEBUG) {
                $query = substr($this->extendedQuery, 0, 1000);
                error_log("Failed Query: {$query}");
            }

            $this->extendedQuery = '';
            $this->jobImportDataDto->setInsertingInto('');

            if (!$isCommitting) {
                $this->commit();
            }

            throw new \RuntimeException(sprintf(
                'Failed to insert extended query. Query: %s Reason Code: %s Reason Message: %s',
                $this->extendedQuery,
                $this->client->errno(),
                $this->client->error()
            ));
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
                $this->commit();
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
}
