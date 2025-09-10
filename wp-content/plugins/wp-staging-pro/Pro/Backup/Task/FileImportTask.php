<?php

namespace WPStaging\Pro\Backup\Task;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Filesystem\PathIdentifier;
use WPStaging\Framework\Queue\FinishedQueueException;
use WPStaging\Framework\Queue\SeekableQueueInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Dto\StepsDto;
use WPStaging\Pro\Backup\Task\ImportFileHandlers\ImportFileProcessor;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Class FileImportTask
 *
 * This is an abstract class for the filesystem-based import actions of importing a site,
 * such as plugins, themes, mu-plugins and uploads files.
 *
 * It's main philosophy is to control the individual queue of what needs to be processed
 * from each of the concrete imports. It delegates actual processing of the queue to a separate class.
 *
 * @package WPStaging\Pro\Backup\Abstracts\Task
 */
abstract class FileImportTask extends ImportTask
{
    protected $filesystem;
    protected $directory;

    private $importFileProcessor;

    protected $processedNow;
    protected $pathIdentifier;

    public function __construct(LoggerInterface $logger, Cache $cache, StepsDto $stepsDto, SeekableQueueInterface $taskQueue, Filesystem $filesystem, Directory $directory, ImportFileProcessor $importFileProcessor, PathIdentifier $pathIdentifier)
    {
        parent::__construct($logger, $cache, $stepsDto, $taskQueue);
        $this->filesystem = $filesystem;
        $this->directory = $directory;
        $this->importFileProcessor = $importFileProcessor;
        $this->pathIdentifier = $pathIdentifier;
    }

    public function prepareFileImport()
    {
        if ($this->stepsDto->getTotal() === 0) {
            $this->buildQueue();
            $this->taskQueue->seek(0);

            // Just an arbitrary number, when there are no more items in the Queue we call stepsDto->finish()
            $this->stepsDto->setTotal(100);
        }
    }

    /**
     * @return \WPStaging\Pro\Backup\Dto\TaskResponseDto
     */
    public function execute()
    {
        $this->prepareFileImport();

        try {
            while (!$this->isThreshold()) {
                $this->processNextItemInQueue();
                $this->processedNow++;
            }
        } catch (FinishedQueueException $e) {
            $this->stepsDto->finish();
        }

        $this->logger->info(esc_html__(sprintf('%s (processed %d items)', static::getTaskTitle(), $this->processedNow), 'wp-staging'));

        return $this->generateResponse(false);
    }

    protected function getOriginalSuffix()
    {
        return '_wpstg_tmp';
    }

    /**
     * Concrete classes of the FileImportTask must build
     * the queue once, enqueuing everything that needs
     * to be moved or deleted, using $this->enqueueMove
     * or $this->enqueueDelete.
     *
     * @return void
     */
    abstract protected function buildQueue();

    /**
     * Executes the next item in the queue.
     */
    protected function processNextItemInQueue()
    {
        $nextInQueueRaw = $this->taskQueue->dequeue();

        if (is_null($nextInQueueRaw)) {
            throw new FinishedQueueException();
        }

        // Skip blank lines
        if ($nextInQueueRaw === '') {
            return;
        }

        $nextInQueue = json_decode($nextInQueueRaw, true);

        // Make sure we read expected data from the queue
        if (!is_array($nextInQueue)) {
            $this->logger->warning(sprintf(
                __('%s: An internal error occurred that prevented this item from being imported. Skipping it... (Error Code: INVALID_QUEUE_ITEM)', 'wp-staging'),
                static::getTaskTitle()
            ));
            $this->logger->debug($nextInQueueRaw);

            return;
        }

        // Make sure data is in the expected format
        array_map(function ($requiredKey) use ($nextInQueue, $nextInQueueRaw) {
            if (!array_key_exists($requiredKey, $nextInQueue)) {
                $this->logger->warning(sprintf(
                    __('%s: An internal error occurred that prevented this item from being imported. Skipping it... (Error Code: INVALID_QUEUE_ITEM)', 'wp-staging'),
                    static::getTaskTitle()
                ));
                $this->logger->debug($nextInQueueRaw);

                return;
            }
        }, ['action', 'source', 'destination']);

        $source = $nextInQueue['source'];

        // Make sure destination is within WordPress
        // @todo Test exporting in Windows and importing in Linux and vice-versa
        $destination = $nextInQueue['destination'];
        $destination = wp_normalize_path($destination);

        // Executes the action
        $this->importFileProcessor->handle($nextInQueue['action'], $source, $destination, $this, $this->logger);
    }

    /**
     * @param string $source      Source path to move.
     * @param string $destination Where to move source to.
     */
    public function enqueueMove($source, $destination)
    {
        $this->enqueue([
            'action' => 'move',
            'source' => wp_normalize_path($source),
            'destination' => wp_normalize_path($destination),
        ]);
    }

    /**
     * @param string $path The path to delete. Can be a folder, which will be deleted recursively.
     */
    public function enqueueDelete($path)
    {
        $this->enqueue([
            'action' => 'delete',
            'source' => '',
            'destination' => wp_normalize_path($path),
        ]);
    }

    /**
     * @param array $action An array of actions to perform.
     */
    private function enqueue($action)
    {
        $this->taskQueue->enqueue(json_encode($action));
    }
}
