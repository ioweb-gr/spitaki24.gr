<?php

namespace WPStaging\Pro\Backup\Dto\Job;

use WPStaging\Pro\Backup\Dto\JobDataDto;
use WPStaging\Pro\Backup\Entity\BackupMetadata;

class JobImportDataDto extends JobDataDto
{
    /** @var string */
    private $file;

    /** @var BackupMetadata */
    private $backupMetadata;

    /** @var string */
    protected $tmpDirectory;

    /** @var int Number of extracted files */
    private $extractorFilesExtracted = 0;

    /** @var int Number of written bytes to process the current files */
    private $extractorFileWrittenBytes = 0;

    private $extractorMetadataIndexPosition = 0;

    /** @var string Database table prefix to use while importing the backup */
    private $tmpDatabasePrefix;

    /** @var string Table being inserted during import. */
    private $insertingInto;

    /** @var bool Whether a transaction is started. */
    private $transactionStarted;

    /**
     * @return string The .wpstg backup file being imported.
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Called dynamically
     * @see \WPStaging\Pro\Backup\Ajax\Import\PrepareImport::setupInitialData
     *
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = untrailingslashit(wp_normalize_path($file));
    }

    /**
     * @return BackupMetadata|null
     */
    public function getBackupMetadata()
    {
        return $this->backupMetadata;
    }

    /**
     * @param $backupMetadata
     */
    public function setBackupMetadata($backupMetadata)
    {
        if ($backupMetadata instanceof BackupMetadata) {
            $this->backupMetadata = $backupMetadata;

            return;
        }
        if (is_array($backupMetadata)) {
            try {
                $this->backupMetadata = (new BackupMetadata())->hydrate($backupMetadata);

                return;
            } catch (\Exception $e) {
                $this->backupMetadata = null;

                return;
            }
        }

        $this->backupMetadata = null;
    }

    /**
     * @return string
     */
    public function getTmpDirectory()
    {
        return $this->tmpDirectory;
    }

    /**
     * @param string $tmpPath
     */
    public function setTmpDirectory($tmpPath)
    {
        $this->tmpDirectory = trailingslashit(wp_normalize_path($tmpPath));
    }

    /**
     * @return int
     */
    public function getExtractorFilesExtracted()
    {
        return (int)$this->extractorFilesExtracted;
    }

    public function setExtractorFilesExtracted($extractorFilesExtracted)
    {
        $this->extractorFilesExtracted = (int)$extractorFilesExtracted;
    }

    public function incrementExtractorFilesExtracted()
    {
        $this->extractorFilesExtracted++;
    }

    /**
     * @return int
     */
    public function getExtractorFileWrittenBytes()
    {
        return (int)$this->extractorFileWrittenBytes;
    }

    /**
     * @param int $fileWrittenBytes
     */
    public function setExtractorFileWrittenBytes($fileWrittenBytes)
    {
        $this->extractorFileWrittenBytes = (int)$fileWrittenBytes;
    }

    /**
     * @return int
     */
    public function getExtractorMetadataIndexPosition()
    {
        return (int)$this->extractorMetadataIndexPosition;
    }

    /**
     * @param int $extractorMetadataIndexPosition
     */
    public function setExtractorMetadataIndexPosition($extractorMetadataIndexPosition)
    {
        $this->extractorMetadataIndexPosition = (int)$extractorMetadataIndexPosition;
    }

    /**
     * @return string
     */
    public function getTmpDatabasePrefix()
    {
        return $this->tmpDatabasePrefix;
    }

    /**
     * @param string $tmpDatabasePrefix
     */
    public function setTmpDatabasePrefix($tmpDatabasePrefix)
    {
        $this->tmpDatabasePrefix = $tmpDatabasePrefix;
    }

    /**
     * @return string
     */
    public function getInsertingInto()
    {
        return $this->insertingInto;
    }

    /**
     * @param string $insertingInto
     */
    public function setInsertingInto($insertingInto)
    {
        $this->insertingInto = $insertingInto;
    }

    /**
     * @return bool
     */
    public function getTransactionStarted()
    {
        return $this->transactionStarted;
    }

    /**
     * @param bool $transactionStarted
     */
    public function setTransactionStarted($transactionStarted)
    {
        $this->transactionStarted = $transactionStarted;
    }
}
