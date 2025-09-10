<?php

/**
 * A no-op Job, useful to keep type consistency or for testing purposes.
 *
 * @package WPStaging\Pro\BackgroundProcessing\Jobs
 */

namespace WPStaging\Pro\BackgroundProcessing\Jobs;

use WPStaging\Pro\BackgroundProcessing\Interfaces\JobInterface;
use WPStaging\Pro\BackgroundProcessing\JobArguments;
use WPStaging\Pro\BackgroundProcessing\JobStatus;

/**
 * Class NullJob
 *
 * @package WPStaging\Pro\BackgroundProcessing\Jobs
 */
class NullJob implements JobInterface
{

    /**
     * {@inheritdoc}
     */
    public function run(JobArguments $args)
    {
        return JobStatus::success();
    }

    /**
     * {@inheritdoc}
     */
    public function start(JobArguments $args)
    {
        return JobStatus::success();
    }

    /**
     * {@inheritdoc}
     */
    public function getTasks()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTask($index)
    {
        return null;
    }
}
