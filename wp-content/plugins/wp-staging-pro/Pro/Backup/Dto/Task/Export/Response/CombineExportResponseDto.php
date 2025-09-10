<?php

/**
 * @noinspection PhpPropertyOnlyWrittenInspection
 * @see          \WPStaging\Framework\Traits\ArrayableTrait::toArray
 */

namespace WPStaging\Pro\Backup\Dto\Task\Export\Response;

use WPStaging\Pro\Backup\Dto\TaskResponseDto;

class CombineExportResponseDto extends TaskResponseDto
{
    /** @var int|null */
    private $backupMd5;

    /** @var int|null */
    private $backupSize;

    /**
     * @param int|null $backupMd5
     */
    public function setBackupMd5($backupMd5)
    {
        $this->backupMd5 = $backupMd5;
    }

    /**
     * @param int|null $backupSize
     */
    public function setBackupSize($backupSize)
    {
        $this->backupSize = $backupSize;
    }
}
