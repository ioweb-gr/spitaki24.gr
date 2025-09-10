<?php

/**
 * The API provided by any Task implementation, part of Job.
 *
 * @package WPStaging\Pro\BackgroundProcessing\Interfaces
 */

namespace WPStaging\Pro\BackgroundProcessing\Interfaces;

use WPStaging\Pro\BackgroundProcessing\Exceptions\TaskException;

/**
 * Interface TaskInterface
 *
 * @package WPStaging\Pro\BackgroundProcessing\Interfaces
 */
interface TaskInterface
{
    /**
     * Enqueues the Task to run as soon as possible.
     *
     * If the task cannot be scheduled, then the method MUST throw a TaskException.
     *
     * @param string $jobClass  Teh fully-qualified name of the Job class that is starting
     *                          this Task.
     * @param int    $taskIndex The 0-based index, in the context of the Job, of the Task.
     *
     * @return int The Action ID, in the format used by the Action Scheduler library
     *             to identify scheduled actions.
     *
     * @throws TaskException If the Task cannot be scheduled.
     * @see as_enqueue_async_action()
     */
    public function runAsync($jobClass, $taskIndex);
}
