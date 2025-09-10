<?php

/**
 * The common parent for most Jobs to store and implement logic common to most Jobs.
 *
 * @package WPStaging\Pro\BackgroundProcessing\Jobs
 */

namespace WPStaging\Pro\BackgroundProcessing\Jobs;

use WPStaging\Core\WPStaging;
use WPStaging\Pro\BackgroundProcessing\Interfaces\JobInterface;
use WPStaging\Pro\BackgroundProcessing\Interfaces\TaskInterface;
use WPStaging\Pro\BackgroundProcessing\JobArguments;
use WPStaging\Pro\BackgroundProcessing\JobStatus;

/**
 * Class AbstractJob
 *
 * @package WPStaging\Pro\BackgroundProcessing\Jobs
 */
abstract class AbstractJob implements JobInterface
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
        try {
            $firstTask = $this->getTask(0);
            /** @var TaskInterface $task */
            $task = WPStaging::getInstance()->getContainer()->make($firstTask);
            $actionId = $task->runAsync(static::class, 0);
        } catch (\Exception $e) {
            return JobStatus::fail(
                [
                    'currentTask' => isset($firstTask) ? $firstTask : null,
                    'message' => $e->getMessage()
                ]
            );
        }

        return JobStatus::success(
            [
                'succeeded' => null,
                'currentTask' => $this->getTask(0),
                'actionId' => $actionId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTask($index)
    {
        $tasks = $this->getTasks();

        return isset($tasks[$index]) ? $tasks[$index] : null;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getTasks();
}
