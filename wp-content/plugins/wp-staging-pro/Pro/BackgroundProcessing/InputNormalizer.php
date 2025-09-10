<?php

/**
 * Normalizes a generic input source to the format used by background processing Jobs.
 *
 * @package WPStaging\Pro\BackgroundProcessing
 */

namespace WPStaging\Pro\BackgroundProcessing;

/**
 * Class InputNormalizer
 *
 * @package WPStaging\Pro\BackgroundProcessing
 */
class InputNormalizer
{

    /**
     * Normalizes wp-cli input arguments to the argument format used by Jobs.
     *
     * @param array<mixed> $args The input wp-cli positional arguments.
     * @param array<string,mixed> $assocArgs The input wp-cli associative arguments.
     *
     * @return JobArguments A reference to the normalized Job arguments.
     */
    public function normalizeWpCliArgs(array $args = [], array $assocArgs = [])
    {
        $translatedArgs = [
            'runInBackground' => isset($assocArgs['background'])
        ];

        return new JobArguments($translatedArgs);
    }
}
