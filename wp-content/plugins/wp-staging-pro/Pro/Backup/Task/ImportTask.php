<?php

namespace WPStaging\Pro\Backup\Task;

use WPStaging\Pro\Backup\Dto\Job\JobImportDataDto;
use WPStaging\Pro\Backup\Dto\JobDataDto;

abstract class ImportTask extends AbstractTask
{
    /** @var JobImportDataDto */
    protected $jobDataDto;

    public function setJobDataDto(JobDataDto $jobDataDto)
    {
        /** @var JobImportDataDto $jobDataDto */
        if (
            $jobDataDto->getBackupMetadata()->getIsExportingDatabase()
            && !$jobDataDto->getBackupMetadata()->getIsExportingMuPlugins()
            && !$jobDataDto->getBackupMetadata()->getIsExportingOtherWpContentFiles()
            && !$jobDataDto->getBackupMetadata()->getIsExportingPlugins()
            && !$jobDataDto->getBackupMetadata()->getIsExportingThemes()
            && !$jobDataDto->getBackupMetadata()->getIsExportingUploads()
        ) {
            $jobDataDto->setDatabaseOnlyBackup(true);
        }

        parent::setJobDataDto($jobDataDto);
    }
}
