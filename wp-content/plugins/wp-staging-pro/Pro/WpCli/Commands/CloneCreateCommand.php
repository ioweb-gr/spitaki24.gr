<?php

/**
 * Allow creating staging sites by cloning the main site.
 *
 * @package WPStaging\Pro\WpCli\Commands
 */

namespace WPStaging\Pro\WpCli\Commands;

use WPStaging\Pro\BackgroundProcessing\InputNormalizer;
use WPStaging\Pro\BackgroundProcessing\Interfaces\JobInterface;

/**
 * Class CloneCreateCommand
 *
 * @package WPStaging\Pro\WpCli\Commands
 */
class CloneCreateCommand implements CommandInterface
{
    /**
     * A reference to the Job this command provides a wp-cli interface for.
     *
     * @var JobInterface
     */
    protected $Job;

    /**
     * A reference to the normalizer implementation that should be used to
     * transform the input wp-cli arguments to the arguments used by the
     * Job.
     *
     * @var InputNormalizer
     */
    protected $input;

    /**
     * CloneCreateCommand constructor.
     *
     * @param JobInterface    $Job       A reference to the Job the command will run or start.
     * @param InputNormalizer $normalizer A reference to the object in charge of the input
     *                                    argument normalization.
     */
    public function __construct(JobInterface $Job, InputNormalizer $normalizer)
    {
        $this->Job = $Job;
        $this->input = $normalizer;
    }

    /**
     * Creates a staging site.
     *
     * @param array<mixed>        $args      The list of input positional arguments.
     * @param array<string,mixed> $assocArgs A map from the positional arguments names to their values.
     *
     * @return bool Whether the command correctly executed or not.
     */
    public function __invoke(array $args = [], array $assocArgs = [])
    {
        $normalizedArgs = $this->input->normalizeWpCliArgs($args, $assocArgs);

        if ($normalizedArgs->runInBackground()) {
            $JobStatus = $this->Job->start($normalizedArgs);
            return $JobStatus->dispatched();
        } else {
            $JobStatus = $this->Job->run($normalizedArgs);
            return $JobStatus->succeeded();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getJob()
    {
        return $this->Job;
    }
}
