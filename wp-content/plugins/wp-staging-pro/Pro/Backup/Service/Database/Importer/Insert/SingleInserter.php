<?php

namespace WPStaging\Pro\Backup\Service\Database\Importer\Insert;

class SingleInserter extends TransactionInserter
{
    public function processQuery(&$insertQuery)
    {
        if (strlen($insertQuery) > $this->maxAllowedPacket) {
            throw new \OutOfRangeException(sprintf(
                'A query was skipped because it exceeded the maximum size. Query size: %s | max_allowed_packet: %s',
                size_format(strlen($insertQuery)),
                size_format($this->maxAllowedPacket)
            ));
        }

        $this->maybeStartTransaction();

        if (!$this->exec($insertQuery)) {
            throw new \RuntimeException(sprintf(
                'Failed to insert single query. Reason Code: %s Reason Message: %s',
                $this->client->errno(),
                $this->client->error()
            ));
        }

        $this->currentTransactionSize += strlen($insertQuery);

        $this->maybeCommit();
    }
}
