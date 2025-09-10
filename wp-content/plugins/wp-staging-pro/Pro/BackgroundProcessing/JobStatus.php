<?php

/**
 * Models the status of a Job.
 *
 * @package WPStaging\Pro\BackgroundProcessing
 */

namespace WPStaging\Pro\BackgroundProcessing;

use LogicException;
use WPStaging\Framework\Traits\PropertyConstructor;

/**
 * Class JobStatus
 *
 * @package WPStaging\Pro\BackgroundProcessing
 */
class JobStatus
{
    use PropertyConstructor;

    /**
     * Whether the Job was correctly dispatched for background processing or not.
     *
     * @var bool|null
     */
    protected $dispatched;

    /**
     * @var bool|null Whether the Job did succeed in the end or not.
     */
    protected $succeeded;

    /**
     * The class of the Task the Job is currently executing.
     *
     * @var string|null
     */
    protected $currentTask;

    /**
     * An optional message attached to the status.
     *
     * @var string|null
     */
    protected $message;

    /**
     * An error message attached to the status, or `null` to indicate there are
     * no error to report.
     *
     * @var string|null
     */
    protected $error;

    /**
     * The Action ID of the Task that is currently running in
     * the context of the Job, or `null` if the Job is not running
     * in the background or no Task is running.
     *
     * @var int|null
     */
    protected $actionId;

    /**
     * A sugar-method builder to return a success status.
     *
     * @param array<string,mixed> $overrides A map of overrides for the default success property
     *                                       set, from property names to their values.
     *
     * @return JobStatus A reference to a successful Job status.
     */
    public static function success(array $overrides = [])
    {
        $args = array_merge(
            static::defaultProps(),
            ['succeeded' => true, 'dispatched' => true, 'message' => '', 'error' => null],
            $overrides
        );

        return new self($args);
    }

    /**
     * Returns a map of the default properties for a Job status.
     *
     * @return array<string,mixed> A map from property names to their default value.
     */
    protected static function defaultProps()
    {
        return [
            'succeeded' => false,
            'dispatched' => false,
            'currentTask' => null,
            'message' => null,
            'error' => null,
            'actionId' => null,
        ];
    }

    /**
     * A sugar-method builder to return a failure status.
     *
     * @param array<string,mixed> $overrides A map of overrides for the default failure property
     *                                       set, from property names to their values.
     *
     * @return JobStatus A reference to a failed Job status.
     *
     * @throws LogicException If an error message is not specified.
     */
    public static function fail(array $overrides = [])
    {
        if (!isset($overrides['error'])) {
            // This is a developer error.
            throw new LogicException('A failed Job MUST define an error message or reason.');
        }

        $args = array_merge(
            static::defaultProps(),
            ['succeeded' => false, 'dispatched' => false],
            $overrides
        );

        return new self($args);
    }

    /**
     * Returns whether a Job succeeded or not.
     *
     * A Job succeeded when it completed successfully.
     *
     * @return bool Whether the Job did succeed in the end or not;
     *              a `null` value indicates the Job has not completed
     *              yet.
     */
    public function succeeded()
    {
        return $this->succeeded;
    }

    /**
     * Returns whether the Job was successfully dispatched or not.
     *
     * Note: a successful start will only indicate the Job was correctly
     * dispatched for background, async, non-blocking processing and it
     * MUST NOT be used as an indication the Job will be started correctly
     * or will complete correctly.
     *
     * @return bool|null Whether the Job was successfully dispatched or not;
     *                   if the Job is not running in the background, then the
     *                   return value will be `null`.
     */
    public function dispatched()
    {
        return $this->dispatched;
    }

    /**
     * Returns the class of the Task that is currently being executed by the Job.
     *
     * @return string|null The class of the Task that is currently being executed
     *                     by the Job, or `null` if the Job is not currently executing
     *                     or going to execute any Task.
     */
    public function currentTask()
    {
        return $this->currentTask;
    }

    /**
     * Returns the error message, if any, attached to the Job Status.
     *
     * If the error message is not `null`, then the assumption can be made
     * an error occurred while executing the Job.
     *
     * @return string|null Either the error message attached to the status
     *                     or `null` to indicate there are no error to report.
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * Returns the Action ID, if any, of the currently running Task
     * part of the Job.
     *
     * @return int|null Either the Action ID, in the format used by the Action Scheduler
     *                  `as_enqueue_async_action` function, or `null` if the Job is not
     *                  running in the background.
     */
    public function actionId()
    {
        return $this->actionId;
    }
}
