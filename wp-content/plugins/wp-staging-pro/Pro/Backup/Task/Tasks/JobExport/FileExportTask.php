<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Backup\Task\Tasks\JobExport;

use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Pro\Backup\Dto\Service\CompressorDto;
use WPStaging\Pro\Backup\Dto\StepsDto;
use WPStaging\Pro\Backup\Exceptions\DiskNotWritableException;
use WPStaging\Pro\Backup\Task\ExportTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Service\Compressor;

class FileExportTask extends ExportTask
{
    /** @var Compressor */
    private $compressor;

    /** @var int If a file couldn't be processed in a single request, this will be populated */
    private $bigFileBeingProcessed;

    protected $filesystem;

    protected $start;

    public function __construct(Compressor $compressor, LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, Filesystem $filesystem)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->compressor = $compressor;
        $this->filesystem = $filesystem;
    }

    public static function getTaskName()
    {
        return 'backup_export_file_export';
    }

    public static function getTaskTitle()
    {
        return 'Adding Files to Backup';
    }

    public function execute()
    {
        $this->prepareFileExportTask();
        $this->start = microtime(true);

        while (!$this->isThreshold() && !$this->stepsDto->isFinished()) {
            try {
                $this->export();
            } catch (FinishedQueueException $exception) {
                $this->stepsDto->finish();

                $this->logger->info(sprintf('Added %d/%d files to backup (%s)', $this->stepsDto->getCurrent(), $this->stepsDto->getTotal(), $this->getExportSpeed()));

                return $this->generateResponse(false);
            } catch (DiskNotWritableException $exception) {
                // Probably disk full. No-op, as this is handled elsewhere.
            }
        }

        if ($this->bigFileBeingProcessed instanceof CompressorDto) {
            $relativePathForLogging = str_replace($this->filesystem->normalizePath(ABSPATH), '', $this->filesystem->normalizePath($this->bigFileBeingProcessed->getFilePath()));
            $percentProcessed = ceil(($this->bigFileBeingProcessed->getWrittenBytesTotal() / $this->bigFileBeingProcessed->getFileSize()) * 100);
            $this->logger->info(sprintf('Adding big file: %s - %s/%s (%s%%) (%s)', $relativePathForLogging, size_format($this->bigFileBeingProcessed->getWrittenBytesTotal(), 2), size_format($this->bigFileBeingProcessed->getFileSize(), 2), $percentProcessed, $this->getExportSpeed()));
        } else {
            $this->logger->info(sprintf('Added %d/%d files to backup (%s)', $this->stepsDto->getCurrent(), $this->stepsDto->getTotal(), $this->getExportSpeed()));
        }

        return $this->generateResponse(false);
    }

    protected function getExportSpeed()
    {
        $elapsed = microtime(true) - $this->start;
        $bytesPerSecond = min(10 * GB_IN_BYTES, absint($this->compressor->getBytesWrittenInThisRequest() / $elapsed));

        if ($bytesPerSecond === 10 * GB_IN_BYTES) {
            return '10GB/s+';
        }

        return size_format($bytesPerSecond) . '/s';
    }

    /**
     * @throws DiskNotWritableException
     */
    public function export()
    {
        $compressorDto = $this->compressor->getDto();
        $compressorDto->setWrittenBytesTotal($this->jobDataDto->getFileBeingExportedWrittenBytes());

        if ($compressorDto->getWrittenBytesTotal() !== 0) {
            $compressorDto->setIndexPositionCreated(true);
        }

        $path = $this->taskQueue->dequeue();

        if (is_null($path)) {
            throw new FinishedQueueException();
        }

        if (empty($path)) {
            return;
        }

        try {
            $status = $this->compressor->appendFileToBackup($path);
        } catch (\RuntimeException $e) {
            $status = null;
        }

        // Done processing this file
        if ($status === true) {
            $this->jobDataDto->setFileBeingExportedWrittenBytes(0);
            $this->stepsDto->incrementCurrentStep();

            return;
        } elseif ($status === null) {
            // Invalid file
            $this->logger->warning("Could not add file to backup: $path");
            $this->jobDataDto->setFileBeingExportedWrittenBytes(0);
            $this->stepsDto->incrementCurrentStep();

            return;
        } elseif ($status === false) {
            // Processing a file that could not be finished in this request
            $this->jobDataDto->setFileBeingExportedWrittenBytes($compressorDto->getWrittenBytesTotal());
            $this->taskQueue->retry(false);

            if ($compressorDto->getWrittenBytesTotal() < $compressorDto->getFileSize() && $compressorDto->getFileSize() > 10 * MB_IN_BYTES) {
                $this->bigFileBeingProcessed = $compressorDto;
            }
        }
    }

    private function prepareFileExportTask()
    {
        if ($this->stepsDto->getTotal() > 0) {
            return;
        }
        $this->stepsDto->setTotal($this->jobDataDto->getDiscoveredFiles());
    }
}
