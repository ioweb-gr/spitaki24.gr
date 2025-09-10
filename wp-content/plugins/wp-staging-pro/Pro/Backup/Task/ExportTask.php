<?php

namespace WPStaging\Pro\Backup\Task;

use WPStaging\Pro\Backup\Dto\Job\JobExportDataDto;
use WPStaging\Pro\Backup\Dto\JobDataDto;

abstract class ExportTask extends AbstractTask
{
    /** @var JobExportDataDto */
    protected $jobDataDto;

    public function setJobDataDto(JobDataDto $jobDataDto)
    {
        /** @var JobExportDataDto $jobDataDto */
        if (
            $jobDataDto->getIsExportingDatabase()
            && !$jobDataDto->getIsExportingMuPlugins()
            && !$jobDataDto->getIsExportingOtherWpContentFiles()
            && !$jobDataDto->getIsExportingPlugins()
            && !$jobDataDto->getIsExportingThemes()
            && !$jobDataDto->getIsExportingUploads()
        ) {
            $jobDataDto->setDatabaseOnlyBackup(true);
        }

        parent::setJobDataDto($jobDataDto);
    }
}
