<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Backup\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Pro\Backup\Job\Jobs\JobExport;
use WPStaging\Core\WPStaging;

class Export extends AbstractTemplateComponent
{
    public function render()
    {
        if (! $this->canRenderAjax()) {
            return;
        }

        /** @var JobExport $job */
        $job = WPStaging::getInstance()->get(JobExport::class);

        wp_send_json($job->prepareAndExecute());
    }
}
