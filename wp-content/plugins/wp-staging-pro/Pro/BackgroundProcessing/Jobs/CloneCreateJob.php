<?php

/**
 * Models the Job to create a staging site.
 *
 * @package WPStaging\Pro\BackgroundProcessing\Jobs
 */

namespace WPStaging\Pro\BackgroundProcessing\Jobs;

use WPStaging\Backend\Modules\Jobs\Data;
use WPStaging\Backend\Modules\Jobs\Finish;
use WPStaging\Pro\BackgroundProcessing\Tasks\PreserveDataFirst;

/**
 * Class CloneCreateJob
 *
 * @package WPStaging\Pro\BackgroundProcessing\Jobs
 */
class CloneCreateJob extends AbstractJob
{
    /**
     * {@inheritdoc}
     */
    public function getTasks()
    {
        return [
            Tasks\PreserveDataFirst::class,
            Tasks\Database::class,
            Tasks\SearchReplace::class,
            Tasks\PreserveDataSecond::class,
            Tasks\Directories::class,
            Tasks\Files::class,
            Tasks\Data::class,
            // Finish::class -- @todo still required?
        ];
    }
}
