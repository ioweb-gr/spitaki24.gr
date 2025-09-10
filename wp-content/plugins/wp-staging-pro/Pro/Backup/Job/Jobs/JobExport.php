<?php

namespace WPStaging\Pro\Backup\Job\Jobs;

use WPStaging\Pro\Backup\Dto\Job\JobExportDataDto;
use WPStaging\Pro\Backup\Job\AbstractJob;
use WPStaging\Pro\Backup\Task\Tasks\JobExport\DatabaseExportTask;
use WPStaging\Pro\Backup\Task\Tasks\JobExport\CombineExportTask;
use WPStaging\Pro\Backup\Task\Tasks\JobExport\FilesystemScannerTask;
use WPStaging\Pro\Backup\Task\Tasks\JobExport\IncludeDatabaseTask;
use WPStaging\Pro\Backup\Task\Tasks\JobExport\FileExportTask;
use WPStaging\Pro\Backup\Task\Tasks\JobExport\ExportRequirementsCheckTask;

class JobExport extends AbstractJob
{
    /** @var JobExportDataDto $jobDataDto */
    protected $jobDataDto;

    /** @var array The array of tasks to execute for this job. Populated at init(). */
    private $tasks = [];

    public static function getJobName()
    {
        return 'backup_export';
    }

    protected function getJobTasks()
    {
        return $this->tasks;
    }

    protected function execute()
    {
        $this->startBenchmark();

        try {
            $response = $this->getResponse($this->currentTask->execute());
        } catch (\Exception $e) {
            $this->currentTask->getLogger()->critical($e->getMessage());
            $response = $this->getResponse($this->currentTask->generateResponse(false));
        }

        $this->finishBenchmark(get_class($this->currentTask));

        return $response;
    }

    protected function init()
    {
        $this->tasks[] = ExportRequirementsCheckTask::class;
        $this->tasks[] = FilesystemScannerTask::class;
        $this->tasks[] = FileExportTask::class;
        if ($this->jobDataDto->getIsExportingDatabase()) {
            $this->tasks[] = DatabaseExportTask::class;
            $this->tasks[] = IncludeDatabaseTask::class;
        }
        $this->tasks[] = CombineExportTask::class;
    }
}
