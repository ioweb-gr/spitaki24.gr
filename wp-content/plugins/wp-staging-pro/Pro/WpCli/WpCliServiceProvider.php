<?php

/**
 * Provides the wp-cli support feature.
 *
 * @package WPStaging\Pro\WpCli
 */

namespace WPStaging\Pro\WpCli;

use WPStaging\Framework\DI\FeatureServiceProvider;
use WPStaging\Pro\WpCli\Commands\Dispatcher;

/**
 * Class WpCliServiceProvider
 *
 * @package WPStaging\Pro\WpCli
 */
class WpCliServiceProvider extends FeatureServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public static function getFeatureTrigger()
    {
        return 'WPSTG_FEATURE_ENABLE_WPCLI_SUPPORT';
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        if (!static::isEnabled()) {
            return false;
        }

        if (!(defined('WP_CLI') && WP_CLI)) {
            // Nothing should be done if the current request is not a wp-cli one.
            return false;
        }

        // @phpstan-ignore-next-line
        \WP_CLI::add_command('wpstg', Dispatcher::class, Dispatcher::registrationArgs());

        return true;
    }
}
