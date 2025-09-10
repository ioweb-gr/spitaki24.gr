<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types && type-hints

namespace WPStaging\Pro\Backup\Dto\Service;

use WPStaging\Pro\Backup\Entity\BackupMetadata;

class CompressorDto
{
    /** @var string */
    private $filePath;

    /** @var int */
    private $writtenBytesTotal = 0;

    /** @var int */
    private $fileSize;

    /** @var bool */
    private $indexPositionCreated = false;

    /** @var BackupMetadata */
    private $backupMetadata;

    public function appendWrittenBytes($bytes)
    {
        $this->writtenBytesTotal += (int) $bytes;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->fileSize <= $this->writtenBytesTotal;
    }

    public function resetIfFinished()
    {
        if ($this->isFinished()) {
            $this->reset();
        }
    }

    public function reset()
    {
        $this->setFileSize(null);
        $this->setFilePath(null);
        $this->setWrittenBytesTotal(0);
        $this->setIndexPositionCreated(false);
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = wp_normalize_path($filePath);
    }

    /**
     * @return int
     */
    public function getWrittenBytesTotal()
    {
        /** @noinspection UnnecessaryCastingInspection */
        return (int) $this->writtenBytesTotal;
    }

    /**
     * @param int $writtenBytesTotal
     */
    public function setWrittenBytesTotal($writtenBytesTotal)
    {
        $this->writtenBytesTotal = $writtenBytesTotal;
    }

    /**
     * @return int
     */
    public function getFileSize()
    {
        return $this->fileSize;
    }

    /**
     * @param int $fileSize
     */
    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;
    }

    /**
     * @return bool
     */
    public function isIndexPositionCreated()
    {
        return (bool)$this->indexPositionCreated;
    }

    /**
     * @param bool $indexPositionCreated
     */
    public function setIndexPositionCreated($indexPositionCreated)
    {
        $this->indexPositionCreated = (bool)$indexPositionCreated;
    }

    /**
     * @return BackupMetadata
     */
    public function getBackupMetadata()
    {
        if (!$this->backupMetadata) {
            $this->backupMetadata = new BackupMetadata();
        }
        return $this->backupMetadata;
    }

    /**
     * @param BackupMetadata $backupMetadata
     */
    public function setBackupMetadata(BackupMetadata $backupMetadata)
    {
        $this->backupMetadata = $backupMetadata;
    }
}
