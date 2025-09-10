<?php

/**
 * The API implemented by anything that models a group of actions required to perform a Job
 * like creating a staging site or a backup.
 *
 * @package WPStaging\Pro\BackgroundProcessing\Interfaces
 */

namespace WPStaging\Pro\BackgroundProcessing\Interfaces;

use WPStaging\Pro\BackgroundProcessing\JobArguments;
use WPStaging\Pro\BackgroundProcessing\JobStatus;

/**
 * Interface JobInterface
 *
 * @package WPStaging\Pro\BackgroundProcessing\Interfaces
 */
interface JobInterface
{
    /**
     * Runs the Job in foreground, whatever that means in the context of the Job.
     *
     * The Job MUST run immediately in synchronous and blocking mode.
     *
     * @param JobArguments $args A reference to the set of Job input arguments.
     *
     * @return JobStatus A reference to the object representing the final Job status.
     */
    public function run(JobArguments $args);

    /**
     * Runs the Job in foreground, whatever that means in the context of the Job.
     *
     * The Job MUST start as soon as possible and run asynchronously without blocking
     * the current request.
     *
     * @param JobArguments $args A reference to the set of Job input arguments.
     *
     * @return JobStatus A reference to the object representing the initial Job status.
     */
    public function start(JobArguments $args);

    /**
     * Returns the ordered list of Task classes the Job will execute.
     *
     * @return array<string> An ordered list of Task classes the Job will execute.
     */
    public function getTasks();

    /**
     * Returns the Task class in the nth position of the Job Task chain.
     *
     * @param int $index The position to retrieve the Task for; note the
     *                   position is 0-based: the first Task is at index `0`.
     *
     * @return string The name of the Task class at the specified position,
     *                or `null` if there's no Task in the requested position.
     */
    public function getTask($index);
}
