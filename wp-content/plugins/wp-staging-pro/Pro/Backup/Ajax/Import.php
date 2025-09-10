<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Backup\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Pro\Backup\Job\Jobs\JobImport;
use WPStaging\Core\WPStaging;

class Import extends AbstractTemplateComponent
{
    public function render()
    {
        if (!$this->canRenderAjax()) {
            return;
        }

        /** @var JobImport $job */
        $job = WPStaging::getInstance()->get(JobImport::class);

        wp_send_json($job->prepareAndExecute());
    }
}
