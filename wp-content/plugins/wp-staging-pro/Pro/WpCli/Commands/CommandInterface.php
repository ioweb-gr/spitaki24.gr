<?php

/**
 * The API provided by any class implementing a WPStaging PRO WP-CLI Command.
 *
 * @package WPStaging\Pro\WpCli\Commands
 */

namespace WPStaging\Pro\WpCli\Commands;

use WPStaging\Pro\BackgroundProcessing\Interfaces\JobInterface;

/**
 * Interface CommandInterface
 *
 * @package WPStaging\Pro\WpCli\Commands
 */
interface CommandInterface
{
    /**
     * Commands should all be invokable and implement this method as their main entry point.
     *
     * @param array<mixed>        $args      The list of positional arguments for the command.
     * @param array<string,mixed> $assocArgs The map of associative arguments for the command.
     *
     * @return bool Whether the command completed correctly or not.
     */
    public function __invoke(array $args = [], array $assocArgs = []);

    /**
     * Returns a reference to the Job this command will execute.
     *
     * @return JobInterface A reference to the Job instance the command
     *                       will execute.
     */
    public function getJob();
}
