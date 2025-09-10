<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types && type-hints
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Backup\Service;

use RuntimeException;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Pro\Backup\Dto\JobDataDto;
use WPStaging\Pro\Backup\Dto\Service\CompressorDto;
use WPStaging\Framework\Utils\Cache\BufferedCache;
use WPStaging\Pro\Backup\Exceptions\DiskNotWritableException;

class Compressor
{
    const EXPORT_DIR_NAME = 'backups';

    /** @var BufferedCache */
    private $tempBackupIndex;

    /** @var BufferedCache */
    private $tempBackup;

    /** @var CompressorDto */
    private $compressorDto;

    /** @var PathIdentifier */
    private $pathIdentifier;

    /** @var int */
    private $compressedFileSize = 0;

    /** @var JobDataDto */
    private $jobDataDto;

    protected $bytesWrittenInThisRequest = 0;

    // TODO telescoped
    public function __construct(BufferedCache $cacheIndex, BufferedCache $tempBackup, PathIdentifier $pathIdentifier, JobDataDto $jobDataDto, CompressorDto $compressorDto)
    {
        $this->jobDataDto = $jobDataDto;
        $this->compressorDto = $compressorDto;
        $this->tempBackupIndex = $cacheIndex;
        $this->tempBackup = $tempBackup;
        $this->pathIdentifier = $pathIdentifier;

        $this->tempBackupIndex->setFilename('temp_backup_index_' . $this->jobDataDto->getId());
        $this->tempBackupIndex->setLifetime(DAY_IN_SECONDS);

        $this->tempBackup->setFilename('temp_wpstg_backup_' . $this->jobDataDto->getId());
        $this->tempBackup->setLifetime(DAY_IN_SECONDS);
    }

    /**
     * @return CompressorDto
     */
    public function getDto()
    {
        return $this->compressorDto;
    }

    public function getBytesWrittenInThisRequest()
    {
        return $this->bytesWrittenInThisRequest;
    }

    /**
     * @param string $fullFilePath
     *
     * `true` -> finished
     * `false` -> not finished
     * `null` -> skip / didn't do anything
     *
     * @throws DiskNotWritableException
     * @throws RuntimeException
     *
     * @return bool|null
     */
    public function appendFileToBackup($fullFilePath)
    {
        // We can use evil '@' as we don't check is_file || file_exists to speed things up.
        // Since in this case speed > anything else
        // However if @ is not used, depending on if file exists or not this can throw E_WARNING.
        $resource = @fopen($fullFilePath, 'rb');
        if (!$resource) {
            return null;
        }

        $fileStats = fstat($resource);
        $this->initiateDtoByFilePath($fullFilePath, $fileStats);
        $writtenBytesBefore = $this->compressorDto->getWrittenBytesTotal();
        $writtenBytesTotal = $this->appendToCompressedFile($resource, $fullFilePath);
        $this->addIndex($writtenBytesTotal);
        $this->compressorDto->setWrittenBytesTotal($writtenBytesTotal);

        $this->bytesWrittenInThisRequest += $writtenBytesTotal - $writtenBytesBefore;

        $isFinished = $this->compressorDto->isFinished();

        $this->compressorDto->resetIfFinished();

        return $isFinished;
    }

    public function initiateDtoByFilePath($filePath, array $fileStats = [])
    {
        if ($filePath === $this->compressorDto->getFilePath() && $fileStats['size'] === $this->compressorDto->getFileSize()) {
            return;
        }

        $this->compressorDto->setFilePath($filePath);
        $this->compressorDto->setFileSize($fileStats['size']);
    }

    /**
     * Combines index and compressed file, renames / moves it to destination
     *
     * This function is called only once, so performance improvements has no impact here.
     *
     * @return string|null
     */
    public function generateBackupMetadata()
    {
        clearstatcache();
        $indexResource = fopen($this->tempBackupIndex->getFilePath(), 'rb');

        if (!$indexResource) {
            throw new RuntimeException('Index file not found!');
        }

        $indexStats = fstat($indexResource);
        $this->initiateDtoByFilePath($this->tempBackupIndex->getFilePath(), $indexStats);

        if ($this->tempBackup->readLastLine() !== PHP_EOL) {
            $this->tempBackup->append(PHP_EOL);
        }

        clearstatcache();
        $backupSizeBeforeAddingIndex = filesize($this->tempBackup->getFilePath());

        // Write the index to the backup file, regardless of resource limits threshold
        $writtenBytes = $this->appendToCompressedFile($indexResource, $this->tempBackupIndex->getFilePath());
        $this->compressorDto->appendWrittenBytes($writtenBytes);

        clearstatcache();
        $backupSizeAfterAddingIndex = filesize($this->tempBackup->getFilePath());

        $backupMetadata = $this->compressorDto->getBackupMetadata();
        $backupMetadata->setHeaderStart($backupSizeBeforeAddingIndex);
        $backupMetadata->setHeaderEnd($backupSizeAfterAddingIndex);

        $this->tempBackup->append(json_encode($backupMetadata));

        // close the index file handle to make it deleteable for Windows where PHP < 7.3
        fclose($indexResource);
        $this->tempBackupIndex->delete();

        //$this->dto->isFinished();

        return $this->renameExport();
    }

    private function renameExport()
    {
        $fileName = sprintf(
            '%s_%s_%s.%s',
            parse_url(get_home_url())['host'],
            current_time('Ymd-His'),
            $this->jobDataDto->getId(),
            'wpstg'
        );

        $backupsDirectory = WPStaging::make(BackupsFinder::class)->getBackupsDirectory();

        $destination = $backupsDirectory . $fileName;

        if (!rename($this->tempBackup->getFilePath(), $destination)) {
            throw new RuntimeException('Failed to generate destination');
        }

        return $destination;
    }

    /**
     * @param int $writtenBytesTotal
     */
    private function addIndex($writtenBytesTotal)
    {
        clearstatcache();
        if (file_exists($this->tempBackup->getFilePath())) {
            $this->compressedFileSize = filesize($this->tempBackup->getFilePath());
        }
        $start = max($this->compressedFileSize - $writtenBytesTotal, 0);

        if (!$this->compressorDto->isIndexPositionCreated()) {
            $identifiablePath = $this->pathIdentifier->transformPathToIdentifiable($this->compressorDto->getFilePath());
            $info = $identifiablePath . '|' . $start . ':' . $writtenBytesTotal;
            $this->tempBackupIndex->append($info);
            $this->compressorDto->setIndexPositionCreated(true);

            /*
             * We require JobDataDto in the constructor because it is wired in the DI container
             * to the current job DTO instance. However, here we need to make sure this DTO
             * is the jobExportDataDto.
             */
            if (!is_callable([$this->jobDataDto, 'setTotalFiles']) || !is_callable([$this->jobDataDto, 'getTotalFiles'])) {
                throw new \LogicException('This method can only be called from the context of Export');
            }

            $this->jobDataDto->setTotalFiles($this->jobDataDto->getTotalFiles() + 1);

            return;
        }

        $lastLine = $this->tempBackupIndex->readLines(1, null, BufferedCache::POSITION_BOTTOM);
        if (!is_array($lastLine)) {
            throw new RuntimeException('Failed to read backup metadata file index information');
        }

        $lastLine = array_filter($lastLine, function ($item) {
            return !empty($item) && strpos($item, ':') !== false && strpos($item, '|') !== false;
        });

        if (count($lastLine) !== 1) {
            throw new RuntimeException('Failed to read backup metadata file index information');
        }

        $lastLine = array_shift($lastLine);

        // ['wp-content/themes/twentytwentyone/readme.txt', '9378469:4491']
        list($relativePath, $indexPosition) = explode('|', trim($lastLine));

        // ['9378469', '4491']
        list($offsetStart, $writtenPreviously) = explode(':', trim($indexPosition));

        // @todo Should we use mb_strlen($_writtenBytes, '8bit') instead of strlen?
        $this->tempBackupIndex->deleteBottomBytes(strlen($lastLine));

        $identifiablePath = $this->pathIdentifier->transformPathToIdentifiable($this->compressorDto->getFilePath());
        $info = $identifiablePath . '|' . $offsetStart . ':' . $writtenBytesTotal;
        $this->tempBackupIndex->append($info);
        $this->compressorDto->setIndexPositionCreated(true);
    }

    /**
     * @param $resource
     * @param $filePath
     *
     * @return int
     * @throws DiskNotWritableException
     * @throws RuntimeException
     */
    private function appendToCompressedFile($resource, $filePath)
    {
        try {
            return $this->tempBackup->appendFile(
                $resource,
                $this->compressorDto->getWrittenBytesTotal()
            );
        } catch (DiskNotWritableException $e) {
            throw new DiskNotWritableException('Failed to export file ' . $filePath . '. Reason: ' . $e->getMessage());
        }
    }
}
