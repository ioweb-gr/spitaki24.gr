<?php

namespace WPStaging\Pro\Backup\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Pro\Backup\Job\Jobs\JobExport;
use WPStaging\Pro\Backup\Job\Jobs\JobImport;
use WPStaging\Core\WPStaging;

class Status extends AbstractTemplateComponent
{
    const TYPE_IMPORT = 'restore';

    public function render()
    {
        if (! $this->canRenderAjax()) {
            return;
        }

        $job = $this->getJob();
        $job->prepare();

        wp_send_json($job->getJobDataDto());
    }

    /**
     * @return JobExport|JobImport
     */
    private function getJob()
    {
        if (!empty($_GET['process']) && $_GET['process'] === self::TYPE_IMPORT) {
            return WPStaging::getInstance()->get(JobImport::class);
        } else {
            return WPStaging::getInstance()->get(JobExport::class);
        }
    }
}
