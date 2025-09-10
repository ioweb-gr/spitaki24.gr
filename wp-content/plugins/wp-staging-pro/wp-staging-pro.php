<?php

/**
 * Plugin Name: WP STAGING PRO
 * Plugin URI: https://wp-staging.com
 * Description: Create a staging clone site for testing & developing
 * Author: WP-STAGING
 * Author URI: https://wordpress.org/plugins/wp-staging
 * Version: 4.0.3
 * Text Domain: wp-staging
 * Domain Path: /languages/
 *
 * @package  WPSTG
 * @category Development, Migrating, Staging
 * @author   WP STAGING
 */

if (!defined("WPINC")) {
    die;
}

/**
 * Welcome to WPSTAGING.
 *
 * If you're reading this, you are a curious person that likes
 * to understand how things works, and that's awesome!
 *
 * The philosophy of this file is to work on all PHP versions.
 *
 * Before PHP can understand conditionals such as "if, else",
 * it has to parse this file and split it into "tokens". This
 * process is called "lexical analysis", and exists in almost
 * all programming languages.
 *
 * This file uses only syntax that works with all PHP versions,
 * so that any PHP version can parse it and run our version check
 * conditional.
 *
 * Then we include other PHP files to be parsed, this time, certain
 * to be executing in a PHP version that is capable of parsing the
 * the syntax we are using.
 */
if (version_compare(phpversion(), '5.5.0', '>=')) {
    // The absolute path to the main file of this plugin.
    $pluginFilePath = __FILE__;
    include dirname(__FILE__) . '/opcacheBootstrap.php';
    include_once dirname(__FILE__) . '/proBootstrap.php';
} else {
    if (!function_exists('wpstg_unsupported_php_version')) {
        function wpstg_unsupported_php_version()
        {
            echo '<div class="notice-warning notice is-dismissible">';
            echo '<p style="font-weight: bold;">' . esc_html__('PHP Version not supported') . '</p>';
            echo '<p>' . esc_html__(sprintf('WP STAGING requires PHP %s or higher. Your site is running an outdated version of PHP (%s), which requires an update.', '5.5', phpversion()), 'wp-staging') . '</p>';
            echo '</div>';
        }
    }
    add_action('admin_notices', 'wpstg_unsupported_php_version');
}
