<?php

/**
 * Models the input arguments for a Job.
 *
 * @package WPStaging\Pro\BackgroundProcessing
 */

namespace WPStaging\Pro\BackgroundProcessing;

use WPStaging\Framework\Traits\PropertyConstructor;

/**
 * Class JobArguments
 *
 * @package WPStaging\Pro\BackgroundProcessing
 */
class JobArguments
{
    use PropertyConstructor;

    /**
     * Whether the Job should run in the background or not.
     *
     * @var bool
     */
    protected $runInBackground = false;

    /**
     * Returns whether the current Job should be executed in background or not.
     *
     * @return bool Whether the current Job should be executed in background or not.
     */
    public function runInBackground()
    {
        return $this->runInBackground === true;
    }
}
