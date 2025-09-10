<?php

/**
 * Defines and groups functionalities common to all the Tasks.
 *
 * @package WPStaging\Pro\BackgroundProcessing\Jobs\Tasks
 */

namespace WPStaging\Pro\BackgroundProcessing\Jobs\Tasks;

use WPStaging\Framework\BackgroundProcessing\Queue;
use WPStaging\Pro\BackgroundProcessing\Interfaces\TaskInterface;

/**
 * Class AbstractTask
 *
 * @package WPStaging\Pro\BackgroundProcessing\Jobs\Tasks
 */
class AbstractTask implements TaskInterface
{
    /**
     * A reference to the Action Scheduler adapter instance.
     *
     * @var Queue
     */
    protected $actionScheduler;

    /**
     * AbstractTask constructor.
     *
     * @param Queue $actionScheduler           A reference to the Action Scheduler
     *                                         adapter instance the Task should use to
     *                                         schedule its Steps.
     */
    public function __construct(Queue $actionScheduler)
    {
        $this->actionScheduler = $actionScheduler;
    }

    /**
     * {@inheritdoc}
     */
    public function runAsync($jobClass, $taskIndex)
    {
        $hook = '';
        $args = [];
        $group = '';
        return $this->actionScheduler->enqueueAction($hook, $args, $group);
    }
}
