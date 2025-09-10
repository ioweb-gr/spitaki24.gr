<?php

/**
 * The purpose of the pre-bootstrap process is to make sure the environment is able to run
 * the plugin without any errors, such as making sure there are no other WPSTAGING instances
 * active at the same time.
 *
 * It works at a low level, without the autoloader, using anonymous callbacks and local variables
 * to make sure we always use and execute the expected code.
 *
 * Since it uses closures, you can't dequeue those actions, but this is expected.
 *
 * @var string $pluginFilePath The absolute path to the main file of this plugin.
 */

add_action('plugins_loaded', function () use ($pluginFilePath) {
    try {
        require __DIR__ . '/runtimeRequirements.php';
        require_once __DIR__ . '/bootstrap.php';
    } catch (Exception $e) {
        if (defined('WPSTG_DEBUG') && WPSTG_DEBUG) {
            error_log('WPSTAGING: ' . $e->getMessage());
        }
    }

    // Show notice when user activates Pro with Free active
    if (is_admin() && get_site_transient('wpstgUpgradingFreeToPro')) {
        delete_site_transient('wpstgUpgradingFreeToPro');
        add_action(is_network_admin() ? 'network_admin_notices' : 'admin_notices', function () {
            echo '<div class="notice-success wpstg-welcome-notice notice">';
            echo '<p style="font-weight: bold;">' . esc_html__('Welcome to WP STAGING Pro!') . '</p>';
            echo '<p>' . wp_kses_post(__(sprintf('Congratulations on upgrading from WP STAGING Free to WP STAGING Pro! Enjoy the new features. If you need support, you can reach us through <a href="https://wp-staging.com/support/" target="_blank">https://wp-staging.com/support/</a>.'), 'wp-staging')) . '</p>';
            echo '<p>' . wp_kses_post(__(sprintf('To get started, please enter your license key <a href="%s">here</a>. You can find your license key in your <a href="%s" target="_blank">account page</a>.', esc_url(self_admin_url('admin.php?page=wpstg-license')), esc_url('https://wp-staging.com/your-account/')), 'wp-staging')) . '</p>';
            echo '</div>';
        });
    }

    // Show notice when user activates Free with Pro active
    if (is_admin() && get_site_transient('wpstgActivatingFreeWhileProIsActive')) {
        delete_site_transient('wpstgActivatingFreeWhileProIsActive');
        add_action(is_network_admin() ? 'network_admin_notices' : 'admin_notices', function () {
            echo '<div class="notice-warning notice wpstg-pro-already-active-notice is-dismissible">';
            echo '<p style="font-weight: bold;">' . esc_html__('WP STAGING Pro Already Active') . '</p>';
            echo '<p>' . esc_html__('WPSTAGING Pro is active, therefore WP STAGING free was automatically disabled.', 'wp-staging') . '</p>';
            echo '</div>';
        });
    }
}, 11, 0); // The priority of this hook must be larger than 10 for the runtime requirement check to detect older versions of WPSTAGING.

register_activation_hook($pluginFilePath, function () use ($pluginFilePath) {
    try {
        require __DIR__ . '/runtimeRequirements.php';
        require_once __DIR__ . '/bootstrap.php';
        require_once __DIR__ . '/install.php';
    } catch (Exception $e) {
        if (defined('WPSTG_DEBUG') && WPSTG_DEBUG) {
            error_log('WPSTAGING: ' . $e->getMessage());
        }
    }

    add_filter('wpstg.deactivation_hook.skip_mu_delete', function ($value) {
        return true;
    });

    // Deactivate WPSTAGING Free when activating Pro
    delete_site_transient('wpstgUpgradingFreeToPro');
    delete_site_transient('wpstgDisableLicenseNotice');

    // Deactivate free plugin on network site
    if (is_multisite()) {
        foreach (wp_get_active_network_plugins() as $networkwidePlugin) {
            if (strpos($networkwidePlugin, 'wp-staging.php') !== false) {
                set_site_transient('wpstgUpgradingFreeToPro', true, 1 * HOUR_IN_SECONDS);
                set_site_transient('wpstgDisableLicenseNotice', true, 1 * HOUR_IN_SECONDS);

                if (!function_exists('deactivate_plugins')) {
                    require_once(trailingslashit(ABSPATH) . 'wp-admin/includes/plugin.php');
                }
                deactivate_plugins(plugin_basename($networkwidePlugin), null, true);
            }
        }
    }
    foreach (wp_get_active_and_valid_plugins() as $sitewidePlugin) {
        if (strpos($sitewidePlugin, 'wp-staging.php') !== false) {
            set_site_transient('wpstgUpgradingFreeToPro', true, 1 * HOUR_IN_SECONDS);
            set_site_transient('wpstgDisableLicenseNotice', true, 1 * HOUR_IN_SECONDS);

            if (!function_exists('deactivate_plugins')) {
                require_once(trailingslashit(ABSPATH) . 'wp-admin/includes/plugin.php');
            }
            deactivate_plugins(plugin_basename($sitewidePlugin), null, false);
        }
    }
});

register_deactivation_hook($pluginFilePath, function () use ($pluginFilePath) {
    require_once __DIR__ . '/Deactivate.php';
    new WPStaging\Deactivate($pluginFilePath);
});
