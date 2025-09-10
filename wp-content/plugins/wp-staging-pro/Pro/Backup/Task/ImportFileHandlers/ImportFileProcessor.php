<?php

namespace WPStaging\Pro\Backup\Task\ImportFileHandlers;

use WPStaging\Pro\Backup\Task\FileImportTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Class ImportFileProcessor
 *
 * This class applies the Chain of Responsibility pattern.
 *
 * @package WPStaging\Pro\Backup\Abstracts\Task\ImportFileHandlers
 */
class ImportFileProcessor
{
    private $moveHandler;
    private $deleteHandler;

    public function __construct(MoveHandler $moveHandler, DeleteHandler $deleteHandler)
    {
        $this->moveHandler   = $moveHandler;
        $this->deleteHandler = $deleteHandler;
    }

    public function handle($action, $source, $destination, FileImportTask $fileImportTask, LoggerInterface $logger)
    {
        $this->moveHandler->setContext($fileImportTask, $logger);
        $this->deleteHandler->setContext($fileImportTask, $logger);

        switch ($action) {
            case 'move':
                $this->moveHandler->handle($source, $destination);
                break;
            case 'delete':
                $this->deleteHandler->handle($source, $destination);
                break;
        }
    }
}
