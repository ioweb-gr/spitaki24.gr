<?php


/**
 *
 * @link              https://sirplugin.com
 * @since             1.0.0
 * @package           WP_Smart_Image_Resize
 *
 * @wordpress-plugin
 * Plugin Name: Smart Image Resize PRO
 * Plugin URI: https://sirplugin.com
 * Description: Make WooCommerce products images the same size and uniform without cropping.
 * Version: 1.14.0
 * Author: Nabil Lemsieh
 * Author URI: https://sirplugin.com
 * License: GPLv3

 * License URI: http://www.gnu.org/licenses/gpl.html
 * Text Domain: wp-smart-image-resize
 * Domain Path: /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 10.1
 */



// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if (!(defined('WP_CLI') && WP_CLI) && function_exists('\is_plugin_active') && function_exists('\deactivate_plugins')):


    
    if (defined('WP_SIR_VERSION') && ! defined('WP_SIR_IS_PRO')) {
        if (is_plugin_active('smart-image-resize/plugpix-smart-image-resize.php')) {
            deactivate_plugins('smart-image-resize/plugpix-smart-image-resize.php');
        }

        wp_redirect($_SERVER['REQUEST_URI']);
        exit;
    }
    
    
endif;


define( 'WP_SIR_VERSION', '1.14.0' );
define( 'WP_SIR_NAME', 'wp-smart-image-resize' );
define( 'WP_SIR_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_SIR_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_SIR_BASENAME', plugin_basename( __FILE__ ) );

define('WP_SIR_IS_PRO', '1');


// Declare compatibility with custom order tables for WooCommerce.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);


try {
    include_once WP_SIR_DIR . 'lib/wp-package-updater/class-wp-package-updater.php';

    $__u = new \WP_Package_Updater_SIR(
        'https://updates.nabillemsieh.com',
        wp_normalize_path( __FILE__ ),
        wp_normalize_path( WP_SIR_DIR ),
        'wp-smart-image-resize-pro'
    );

    if( $__u->__pb() ){ return; }

    add_action('wp_sir_manage_license', [ $__u, 'show_license_form' ] );

} catch ( \Exception $e ) {
}



// Activate
if( ! function_exists('\wp_sir_activate') ){

    function wp_sir_activate()
    {
        add_option( 'wp_sir_plugin_version', WP_SIR_VERSION );
    }
    
}

// Load
register_activation_hook( __FILE__, 'wp_sir_activate' );

include_once WP_SIR_DIR . 'src/Plugin.php';

// Run the plugin.
add_action( 'plugins_loaded', [\WP_Smart_Image_Resize\Plugin::get_instance(), 'run']);

if(apply_filters('wp_sir_allow_background_processing', true)){
    include_once(WP_SIR_DIR . '/libraries/action-scheduler/action-scheduler.php');
}
