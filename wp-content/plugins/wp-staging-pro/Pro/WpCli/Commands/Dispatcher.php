<?php

/**
 * Provides information about the plugin integration with wp-cli.
 *
 * @package WPStaging\Pro\WpCli\Commands
 */

namespace WPStaging\Pro\WpCli\Commands;

use WPStaging\Core\WPStaging;
use WPStaging\Pro\BackgroundProcessing\Interfaces\JobInterface;
use WPStaging\Pro\BackgroundProcessing\Jobs\CloneCreateJob;
use WPStaging\Pro\BackgroundProcessing\Jobs\NullJob;

/**
 * Class Dispatcher
 *
 * @package WPStaging\Pro\WpCli\Commands
 */
class Dispatcher
{
    /**
     * Whether the dispatcher did set up or not.
     *
     * @var bool
     */
    protected static $setUp = false;

    /**
     * {@inheritdoc}
     */
    public static function registrationArgs()
    {
        return [
            'shortdesc' => 'Manages WPStaging PRO cloning and pushing operations.'
        ];
    }

    /**
     * Creates a staging site.
     *
     * ## OPTIONS
     *
     * [--background]
     * : Whether to run the command in background or not.
     *
     * @subcommand create
     */
    public function create(array $args = [], array $assocArgs = [])
    {
        /** @var CommandInterface $subCommand */
        $subCommand = static::getSubcommand('create');
        return $subCommand($args, $assocArgs);
    }

    /**
     * Returns the sub-command mapped to the sub-command slug..
     *
     * @param string $subCommand The sub-command slug; e.g. `create`.
     *
     * @return CommandInterface A command instance reference.
     */
    public static function getSubcommand($subCommand)
    {
        $subCommandMap = self::getSubCommandMap();

        static::setupCommandToJobMapping();

        $commandClass = isset($subCommandMap[$subCommand]) ? $subCommandMap[$subCommand] : static::class;

        if ($commandClass === false) {
            // If we're here, and the sub-command is not mapped, then this is a developer error: report it.
            throw new \LogicException("No command class is mapped to the {$subCommand} sub-command!");
        }

        if (!class_implements($commandClass, CommandInterface::class)) {
            throw new \LogicException(
                "The class {$commandClass} MUST implement the " . CommandInterface::class . ' interface.'
            );
        }

        $container = WPStaging::getInstance()->getContainer();

        return $container->make($commandClass);
    }

    /**
     * Returns the map from the sub-command slugs to the class implementing them.
     *
     *
     * @return array<string,string> A map from the sub-command slugs to the classes implementing them.
     */
    protected static function getSubCommandMap()
    {
        $subCommandMap = [
            'create' => CloneCreateCommand::class
        ];

        /**
         * Allows filtering the map from command slugs to the classes implementing them.
         *
         * @param array<string,string> $subCommandMap A map from the sub-command slugs to
         *                                            the classes implementing them.
         */
        $subCommandMap = apply_filters('wpstg_wpcli_subcommand_map', $subCommandMap);

        return $subCommandMap;
    }

    /**
     * Sets up the command to Job mapping the Container should use to provide
     * Commands with their target Job.
     *
     * @return void The method does not return any value.
     */
    protected static function setupCommandToJobMapping()
    {
        if (static::$setUp) {
            return;
        }

        /** @var \tad_DI52_Container $container */
        $container = WPStaging::getInstance()->getContainer();

        // What Job should each Command execute?
        $commandToJobMap = [
            CloneCreateCommand::class => CloneCreateJob::class,
        ];

        // By default a command should work and do nothing.
        $container->singleton(JobInterface::class, NullJob::class);

        foreach ($commandToJobMap as $command => $Job) {
            $container->when($command)->needs(JobInterface::class)->give($Job);
        }

        static::$setUp = true;
    }
}
