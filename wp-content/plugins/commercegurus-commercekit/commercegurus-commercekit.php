<?php
/**
 * CommerceKit by CommerceGurus
 *
 * @link              https://www.commercegurus.com
 * @since             1.0.0
 * @package           CommerceGurus_Commercekit
 *
 * @wordpress-plugin
 * Plugin Name:       CommerceGurus CommerceKit
 * Plugin URI:        https://www.commercegurus.com
 * Version:           2.4.1
 * Description:       Conversion-boosting, performance-focused eCommerce features which work together seamlessly. From CommerceGurus.
 * Author:            CommerceGurus
 * Author URI:        https://www.commercegurus.com
 * Requires at least: 5.6
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl.html
 * Text Domain:       commercegurus-commercekit
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly....
}

/**
 * Required minimums and constants
 */
define( 'CGKIT_MIN_WC_VER', '4.0' );
define( 'CGKIT_CSS_JS_VER', '2.4.1' );
define( 'CGKIT_BASE_PATH', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'CKIT_URI' ) ) {
	define( 'CKIT_URI', plugin_dir_url( __FILE__ ) );
}
global $commercekit_db_version;
$commercekit_db_version = '1.4.8';

require_once __DIR__ . '/includes/class-commercegurus-commercekit.php';

/**
 * Add settings link to plugin page.
 *
 * @param string $links of menu links.
 */
function commercekit_add_settings_link( $links ) {
	$settings_link = '<a href="admin.php?page=commercekit" title="' . esc_html__( 'Change plugin settings', 'commercegurus-commercekit' ) . '">' . esc_html__( 'Settings', 'commercegurus-commercekit' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
$cgkit_plugin = plugin_basename( __FILE__ );
add_filter( 'plugin_action_links_' . $cgkit_plugin, 'commercekit_add_settings_link' );

/**
 * Commercekit admin notices
 */
function commercekit_admin_notices() {
	global $commerce_gurus_commercekit, $wpdb;
	$commerce_gurus_commercekit->check_environment();
	$commerce_gurus_commercekit->admin_notices();

	$show_error  = false;
	$table_names = array( 'commercekit_waitlist', 'commercekit_wishlist', 'commercekit_wishlist_items', 'commercekit_searches', 'commercekit_swatches_cache_count', 'commercekit_ajs_product_index' );
	foreach ( $table_names as $table_name ) {
		$cgw_table = $wpdb->prefix . $table_name;
		$get_table = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $cgw_table ) ); // db call ok; no-cache ok.
		if ( $cgw_table !== $get_table ) {
			$show_error = true;
			break;
		}
	}
	if ( $show_error ) {
		echo '<div class="error notice"><p>';
		echo esc_html__( 'CommerceKit Error: You are running a version of MySQL which does not support FULLTEXT indexes.', 'commercegurus-commercekit' );
		echo '</p></div>';
	}
}
add_action( 'admin_notices', 'commercekit_admin_notices' );

require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	return;
}

require_once __DIR__ . '/includes/class-commercekit-ajax.php';
require_once __DIR__ . '/includes/class-commercekit-feature-flags.php';
require_once __DIR__ . '/includes/admin-settings.php';
require_once __DIR__ . '/includes/modules.php';

/**
 * Add site CSS and JS scripts
 */
function commercekit_scripts() {
	global $post;
	$commercekit_flags = commercekit_feature_flags()->get_flags();
	wp_enqueue_script( 'js-cookie', plugins_url( 'assets/js/js.cookie.min.js', __FILE__ ), array(), '3.0.5', true );
	if ( isset( $commercekit_flags['wishlist'] ) && 1 === (int) $commercekit_flags['wishlist'] ) {
		wp_enqueue_style( 'commercekit-wishlist-css', plugins_url( 'assets/css/wishlist.css', __FILE__ ), array(), CGKIT_CSS_JS_VER );
		wp_enqueue_script( 'commercekit-wishlist', plugins_url( 'assets/js/wishlist.js', __FILE__ ), array(), CGKIT_CSS_JS_VER, true );
	}

	if ( isset( $commercekit_flags['ajax_search'] ) && 1 === (int) $commercekit_flags['ajax_search'] ) {
		wp_enqueue_style( 'commercekit-ajax-search-css', plugins_url( 'assets/css/ajax-search.css', __FILE__ ), array(), CGKIT_CSS_JS_VER );
		wp_enqueue_script( 'commercekit-ajax-search', plugins_url( 'assets/js/ajax-search.js', __FILE__ ), array(), CGKIT_CSS_JS_VER, true );
	}

	$enable_attr_swatches = defined( 'COMMERCEKIT_ATTRIBUTE_SWATCHES_VISIBLE' ) ? ( COMMERCEKIT_ATTRIBUTE_SWATCHES_VISIBLE ? 1 : 0 ) : ( isset( $commercekit_flags['attribute_swatches'] ) && 1 === (int) $commercekit_flags['attribute_swatches'] ? 1 : 0 );
	$enable_attr_swatches = apply_filters( 'commercekit_module_attribute_swatches_visible', $enable_attr_swatches );
	if ( $enable_attr_swatches ) {
		if ( function_exists( 'is_product' ) && is_product() && $post ) {
			$attribute_swatches = get_post_meta( $post->ID, 'commercekit_attribute_swatches', true );
			if ( ! isset( $attribute_swatches['enable_product'] ) || 1 === (int) $attribute_swatches['enable_product'] ) {
				wp_enqueue_style( 'commercekit-attribute-swatches-css', plugins_url( 'assets/css/commercegurus-attribute-swatches.css', __FILE__ ), array(), CGKIT_CSS_JS_VER );
			}
		}
		if ( isset( $commercekit_flags['attribute_swatches_plp'] ) && 1 === (int) $commercekit_flags['attribute_swatches_plp'] ) {
			wp_enqueue_style( 'commercekit-attribute-swatches-plp-css', plugins_url( 'assets/css/commercegurus-attribute-swatches-plp.css', __FILE__ ), array(), CGKIT_CSS_JS_VER );
		}
		if ( ( isset( $commercekit_flags['as_enable_tooltips'] ) && 1 === (int) $commercekit_flags['as_enable_tooltips'] ) || ! isset( $commercekit_flags['as_enable_tooltips'] ) ) {
			wp_enqueue_style( 'commercekit-as-tooltip-css', plugins_url( 'assets/css/commercegurus-as-tooltip.css', __FILE__ ), array(), CGKIT_CSS_JS_VER );
		}
		wp_enqueue_script( 'commercekit-attribute-swatches-js', plugins_url( 'assets/js/commercegurus-attribute-swatches.js', __FILE__ ), array( 'wc-add-to-cart-variation' ), CGKIT_CSS_JS_VER, true );
	}

	if ( isset( $commercekit_flags['countdown_timer'] ) && 1 === (int) $commercekit_flags['countdown_timer'] ) {
		wp_enqueue_style( 'commercekit-countdown-css', plugins_url( 'assets/css/countdown.css', __FILE__ ), array(), CGKIT_CSS_JS_VER );
	}

	$sticky_atc_desktop = defined( 'COMMERCEKIT_STICKY_ATC_DESKTOP_VISIBLE' ) ? COMMERCEKIT_STICKY_ATC_DESKTOP_VISIBLE : ( isset( $commercekit_flags['sticky_atc_desktop'] ) && 1 === (int) $commercekit_flags['sticky_atc_desktop'] ? 1 : 0 );
	$sticky_atc_desktop = apply_filters( 'commercekit_module_sticky_atc_desktop_visible', $sticky_atc_desktop );
	$sticky_atc_mobile  = defined( 'COMMERCEKIT_STICKY_ATC_MOBILE_VISIBLE' ) ? COMMERCEKIT_STICKY_ATC_MOBILE_VISIBLE : ( isset( $commercekit_flags['sticky_atc_mobile'] ) && 1 === (int) $commercekit_flags['sticky_atc_mobile'] ? 1 : 0 );
	$sticky_atc_mobile  = apply_filters( 'commercekit_module_sticky_atc_mobile_visible', $sticky_atc_mobile );
	$sticky_atc_tabs    = defined( 'COMMERCEKIT_STICKY_ATC_TABS_VISIBLE' ) ? COMMERCEKIT_STICKY_ATC_TABS_VISIBLE : ( isset( $commercekit_flags['sticky_atc_tabs'] ) && 1 === (int) $commercekit_flags['sticky_atc_tabs'] ? 1 : 0 );
	$sticky_atc_tabs    = apply_filters( 'commercekit_module_sticky_atc_tabs_visible', $sticky_atc_tabs );

	if ( ( $sticky_atc_desktop || $sticky_atc_mobile || $sticky_atc_tabs ) && is_product() ) {
		wp_enqueue_style( 'commercekit-sticky-atc-css', plugins_url( 'assets/css/commercekit-sticky-atc.css', __FILE__ ), array(), CGKIT_CSS_JS_VER );
	}

	if ( isset( $commercekit_flags['inventory_display'] ) && 1 === (int) $commercekit_flags['inventory_display'] ) {
		wp_enqueue_style( 'commercekit-stockmeter-css', plugins_url( 'assets/css/stockmeter.css', __FILE__ ), array(), CGKIT_CSS_JS_VER );
	}
}

add_action( 'wp_enqueue_scripts', 'commercekit_scripts' );

/**
 * Commercekit js variables
 */
function commercekit_js_variables() {
	$options         = get_option( 'commercekit', array() );
	$ajax_url        = COMMERCEKIT_AJAX::get_endpoint();
	$commercekit_ajs = array( 'ajax_url' => $ajax_url );
	if ( function_exists( 'commercekit_ajs_options' ) ) {
		$commercekit_ajs = commercekit_ajs_options();
	}
	$commercekit_pdp = array();
	if ( function_exists( 'commercekit_get_gallery_options' ) ) {
		$commercekit_pdp = commercekit_get_gallery_options( $options );
	}
	$commercekit_as = array();
	if ( function_exists( 'commercekit_get_as_options' ) ) {
		$commercekit_as = commercekit_get_as_options( $options );
	}
	$commercekit_ajs['ajax_nonce'] = is_user_logged_in() ? 1 : 0;
	$commercekit_ajs['ajax_url']   = preg_replace( '/(commercekit-ajax).*$/', '$1', $commercekit_ajs['ajax_url'] );
	?>
	<script type="text/javascript"> var commercekit_ajs = <?php echo wp_json_encode( $commercekit_ajs ); ?>; var commercekit_pdp = <?php echo wp_json_encode( $commercekit_pdp ); ?>; var commercekit_as = <?php echo wp_json_encode( $commercekit_as ); ?>; </script>
	<?php
}
add_action( 'wp_head', 'commercekit_js_variables' );

/**
 * Add admin CSS and JS scripts
 */
function commercekit_admin_scripts() {
	$screen = get_current_screen();
	if ( 'toplevel_page_commercekit' === $screen->base ) {
		wp_enqueue_style( 'woocommerce-select2-styles', WC()->plugin_url() . '/assets/css/select2.css', array(), WC()->version );
		wp_enqueue_style( 'commercekit-admin-style', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), CGKIT_CSS_JS_VER );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'commercekit-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'wp-color-picker' ), CGKIT_CSS_JS_VER, true );
	}
}

add_action( 'admin_enqueue_scripts', 'commercekit_admin_scripts' );

/**
 * Commercekit create plugin tables
 */
function commercekit_create_plugin_tables() {
	global $wpdb, $commercekit_db_version;
	$installed_version = (string) get_option( 'commercekit_db_version' );
	if ( $installed_version === $commercekit_db_version ) {
		return;
	}

	$table_name = 'commercekit_waitlist';
	$cgw_table  = $wpdb->prefix . $table_name;
	$get_table  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $cgw_table ) ); // db call ok; no-cache ok.
	if ( $cgw_table !== $get_table ) {
		$sql  = 'CREATE TABLE IF NOT EXISTS `' . $cgw_table . '` ( ';
		$sql .= '`id` INT(11) NOT NULL AUTO_INCREMENT, ';
		$sql .= '`email` VARCHAR(255) NOT NULL, ';
		$sql .= '`product_id` BIGINT(20) NOT NULL, ';
		$sql .= '`mail_sent` TINYINT(1) NOT NULL DEFAULT \'0\', ';
		$sql .= '`created` BIGINT(20) NOT NULL, ';
		$sql .= '`updated` BIGINT(20) NOT NULL DEFAULT \'0\', ';
		$sql .= '`tracked` TINYINT(1) NOT NULL DEFAULT \'0\', ';
		$sql .= 'PRIMARY KEY (`id`) ';
		$sql .= '); ';
		require_once ABSPATH . '/wp-admin/includes/upgrade.php';
		dbDelta( $sql ); // db call ok; no-cache ok.
	} else {
		$field_cols = $wpdb->get_col( 'SHOW COLUMNS FROM `' . $cgw_table . '`' ); // phpcs:ignore
		if ( ! in_array( 'mail_sent', $field_cols, true ) ) {
			$sql = 'ALTER TABLE `' . $cgw_table . '` ADD `mail_sent` TINYINT(1) NOT NULL DEFAULT \'0\' AFTER `product_id`';
			$wpdb->query( $sql ); // phpcs:ignore
		}
		if ( ! in_array( 'updated', $field_cols, true ) ) {
			$sql = 'ALTER TABLE `' . $cgw_table . '` ADD `updated` BIGINT(20) NOT NULL DEFAULT \'0\' AFTER `created`';
			$wpdb->query( $sql ); // phpcs:ignore
		}
		if ( ! in_array( 'tracked', $field_cols, true ) ) {
			$sql = 'ALTER TABLE `' . $cgw_table . '` ADD `tracked` TINYINT(1) NOT NULL DEFAULT \'0\' AFTER `updated`';
			$wpdb->query( $sql ); // phpcs:ignore
		}

		$field_rows = $wpdb->get_results( 'SHOW COLUMNS FROM `' . $cgw_table . '`' ); // phpcs:ignore
		if ( count( $field_rows ) ) {
			foreach ( $field_rows as $field_row ) {
				if ( 'product_id' === $field_row->Field && 'int(11)' === strtolower( $field_row->Type ) ) { // phpcs:ignore
					$sql = 'ALTER TABLE `' . $cgw_table . '` MODIFY `product_id` BIGINT(20) NOT NULL';
					$wpdb->query( $sql ); // phpcs:ignore
				}
			}
		}
	}

	$table_name = 'commercekit_wishlist';
	$cgw_table  = $wpdb->prefix . $table_name;
	$get_table  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $cgw_table ) ); // db call ok; no-cache ok.
	if ( $cgw_table !== $get_table ) {
		$sql  = 'CREATE TABLE IF NOT EXISTS `' . $cgw_table . '` ( ';
		$sql .= '`id` INT(11) NOT NULL AUTO_INCREMENT, ';
		$sql .= '`session_key` VARCHAR(100) NOT NULL, ';
		$sql .= 'PRIMARY KEY (`id`) ';
		$sql .= '); ';
		require_once ABSPATH . '/wp-admin/includes/upgrade.php';
		dbDelta( $sql ); // db call ok; no-cache ok.
	}

	$table_name = 'commercekit_wishlist_items';
	$cgw_table  = $wpdb->prefix . $table_name;
	$get_table  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $cgw_table ) ); // db call ok; no-cache ok.
	if ( $cgw_table !== $get_table ) {
		$sql  = 'CREATE TABLE IF NOT EXISTS `' . $cgw_table . '` ( ';
		$sql .= '`id` INT(11) NOT NULL AUTO_INCREMENT, ';
		$sql .= '`user_id` INT(11) NOT NULL, ';
		$sql .= '`list_id` INT(11) NOT NULL, ';
		$sql .= '`product_id` BIGINT(20) NOT NULL, ';
		$sql .= '`created` BIGINT(20) NOT NULL, ';
		$sql .= '`tracked` TINYINT(1) NOT NULL DEFAULT \'0\', ';
		$sql .= 'PRIMARY KEY (`id`) ';
		$sql .= '); ';
		require_once ABSPATH . '/wp-admin/includes/upgrade.php';
		dbDelta( $sql ); // db call ok; no-cache ok.
	} else {
		$field_rows = $wpdb->get_results( 'SHOW COLUMNS FROM `' . $cgw_table . '`' ); // phpcs:ignore
		if ( count( $field_rows ) ) {
			foreach ( $field_rows as $field_row ) {
				if ( 'product_id' === $field_row->Field && 'int(11)' === strtolower( $field_row->Type ) ) { // phpcs:ignore
					$sql = 'ALTER TABLE `' . $cgw_table . '` MODIFY `product_id` BIGINT(20) NOT NULL';
					$wpdb->query( $sql ); // phpcs:ignore
				}
			}
		}
		$field_cols = $wpdb->get_col( 'SHOW COLUMNS FROM `' . $cgw_table . '`' ); // phpcs:ignore
		if ( ! in_array( 'tracked', $field_cols, true ) ) {
			$sql = 'ALTER TABLE `' . $cgw_table . '` ADD `tracked` TINYINT(1) NOT NULL DEFAULT \'0\' AFTER `created`';
			$wpdb->query( $sql ); // phpcs:ignore
		}
	}

	$table_name = 'commercekit_searches';
	$cgw_table  = $wpdb->prefix . $table_name;
	$get_table  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $cgw_table ) ); // db call ok; no-cache ok.
	if ( $cgw_table !== $get_table ) {
		$sql  = 'CREATE TABLE IF NOT EXISTS `' . $cgw_table . '` ( ';
		$sql .= '`id` INT(11) NOT NULL AUTO_INCREMENT, ';
		$sql .= '`search_term` VARCHAR(100) NOT NULL, ';
		$sql .= '`search_count` INT(11) NOT NULL DEFAULT \'0\', ';
		$sql .= '`click_count` INT(11) NOT NULL DEFAULT \'0\', ';
		$sql .= '`no_result_count` INT(11) NOT NULL DEFAULT \'0\', ';
		$sql .= 'PRIMARY KEY (`id`) ';
		$sql .= '); ';
		require_once ABSPATH . '/wp-admin/includes/upgrade.php';
		dbDelta( $sql ); // db call ok; no-cache ok.
	}

	$table_name = 'commercekit_swatches_cache_count';
	$cgw_table  = $wpdb->prefix . $table_name;
	$get_table  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $cgw_table ) ); // db call ok; no-cache ok.
	if ( $cgw_table !== $get_table ) {
		$sql  = 'CREATE TABLE IF NOT EXISTS `' . $cgw_table . '` ( ';
		$sql .= '`product_id` BIGINT(20) NOT NULL, ';
		$sql .= '`cached` TINYINT(1) NOT NULL DEFAULT \'0\', ';
		$sql .= '`updated` BIGINT(20) NOT NULL ';
		$sql .= '); ';
		require_once ABSPATH . '/wp-admin/includes/upgrade.php';
		dbDelta( $sql ); // db call ok; no-cache ok.
	}

	$table_name = 'commercekit_ajs_product_index';
	$cgw_table  = $wpdb->prefix . $table_name;
	$get_table  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $cgw_table ) ); // db call ok; no-cache ok.
	if ( $cgw_table !== $get_table ) {
		$sql  = 'CREATE TABLE IF NOT EXISTS `' . $cgw_table . '` ( ';
		$sql .= '`id` BIGINT(20) NOT NULL AUTO_INCREMENT, ';
		$sql .= '`product_id` BIGINT(20) NOT NULL, ';
		$sql .= '`title` TEXT NOT NULL, ';
		$sql .= '`description` TEXT NOT NULL, ';
		$sql .= '`short_description` TEXT NOT NULL, ';
		$sql .= '`product_sku` VARCHAR(100) NOT NULL, ';
		$sql .= '`variation_sku` TEXT NOT NULL, ';
		$sql .= '`product_gtin` VARCHAR(100) NOT NULL, ';
		$sql .= '`variation_gtin` TEXT NOT NULL, ';
		$sql .= '`attributes` TEXT NOT NULL, ';
		$sql .= '`product_url` VARCHAR(255) NOT NULL, ';
		$sql .= '`product_img` TEXT NOT NULL, ';
		$sql .= '`in_stock` TINYINT(1) NOT NULL DEFAULT \'1\', ';
		$sql .= '`is_visible` TINYINT(1) NOT NULL DEFAULT \'1\', ';
		$sql .= '`status` VARCHAR(100) NOT NULL DEFAULT \'publish\', ';
		$sql .= '`lang` VARCHAR(50) NOT NULL, ';
		$sql .= '`other_lang` TEXT NOT NULL, ';
		$sql .= '`other_urls` TEXT NOT NULL, ';
		$sql .= 'PRIMARY KEY (`id`), ';
		$sql .= 'UNIQUE KEY `cgkit_ajs_product_id_index` (`product_id`), ';
		$sql .= 'FULLTEXT KEY `cgkit_ajs_title_desc_index` (`title`,`description`,`short_description`), ';
		$sql .= 'FULLTEXT KEY `cgkit_ajs_title_index` (`title`), ';
		$sql .= 'FULLTEXT KEY `cgkit_ajs_description_index` (`description`), ';
		$sql .= 'FULLTEXT KEY `cgkit_ajs_short_desc_index` (`short_description`), ';
		$sql .= 'FULLTEXT KEY `cgkit_ajs_product_sku_index` (`product_sku`), ';
		$sql .= 'FULLTEXT KEY `cgkit_ajs_variation_sku_index` (`variation_sku`), ';
		$sql .= 'FULLTEXT KEY `cgkit_ajs_product_gtin_index` (`product_gtin`), ';
		$sql .= 'FULLTEXT KEY `cgkit_ajs_variation_gtin_index` (`variation_gtin`), ';
		$sql .= 'FULLTEXT KEY `cgkit_ajs_attributes_index` (`attributes`), ';
		$sql .= 'FULLTEXT KEY `cgkit_ajs_title_desc_product_sku_index` (`title`,`description`,`short_description`,`product_sku`,`variation_sku`,`attributes`) ';
		$sql .= ') DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; ';
		require_once ABSPATH . '/wp-admin/includes/upgrade.php';
		dbDelta( $sql ); // db call ok; no-cache ok.
	} else {
		$field_cols = $wpdb->get_col( 'SHOW COLUMNS FROM `' . $cgw_table . '`' ); // phpcs:ignore
		if ( ! in_array( 'product_gtin', $field_cols, true ) ) {
			$sql = 'ALTER TABLE `' . $cgw_table . '` ADD `product_gtin` VARCHAR(100) NOT NULL AFTER `variation_sku`';
			$wpdb->query( $sql ); // phpcs:ignore
			$sql = 'ALTER TABLE `' . $cgw_table . '` ADD `variation_gtin` TEXT NOT NULL AFTER `product_gtin`';
			$wpdb->query( $sql ); // phpcs:ignore
		}
		$field_indexes = $wpdb->get_results( 'SHOW INDEXES FROM `' . $cgw_table . '`' ); // phpcs:ignore
		$added_indexes = array();
		$new_indexes   = array(
			'cgkit_ajs_product_sku_index'            => '(`product_sku`)',
			'cgkit_ajs_variation_sku_index'          => '(`variation_sku`)',
			'cgkit_ajs_product_gtin_index'           => '(`product_gtin`)',
			'cgkit_ajs_variation_gtin_index'         => '(`variation_gtin`)',
			'cgkit_ajs_attributes_index'             => '(`attributes`)',
			'cgkit_ajs_title_desc_product_sku_index' => '(`title`,`description`,`short_description`,`product_sku`,`variation_sku`,`attributes`)',
		);
		if ( count( $field_indexes ) ) {
			foreach ( $field_indexes as $field_index ) {
				$added_indexes[] = $field_index->Key_name; // phpcs:ignore
			}
		}
		foreach ( $new_indexes as $new_key => $new_index ) {
			if ( ! in_array( $new_key, $added_indexes, true ) ) {
				$sql = 'ALTER TABLE `' . $cgw_table . '` ADD FULLTEXT KEY `' . $new_key . '` ' . $new_index;
				$wpdb->query( $sql ); // phpcs:ignore
			}
		}
		if ( ! in_array( 'lang', $field_cols, true ) ) {
			$sql = 'ALTER TABLE `' . $cgw_table . '` ADD `lang` VARCHAR(50) NOT NULL AFTER `status`';
			$wpdb->query( $sql ); // phpcs:ignore
		}
		if ( ! in_array( 'other_lang', $field_cols, true ) ) {
			$sql = 'ALTER TABLE `' . $cgw_table . '` ADD `other_lang` TEXT NOT NULL AFTER `lang`';
			$wpdb->query( $sql ); // phpcs:ignore
		}
		if ( ! in_array( 'other_urls', $field_cols, true ) ) {
			$sql = 'ALTER TABLE `' . $cgw_table . '` ADD `other_urls` TEXT NOT NULL AFTER `other_lang`';
			$wpdb->query( $sql ); // phpcs:ignore
		}
	}

	$table_name = 'commercekit_sg_post_meta';
	$cgw_table  = $wpdb->prefix . $table_name;
	$get_table  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $cgw_table ) ); // db call ok; no-cache ok.
	if ( $cgw_table !== $get_table ) {
		$sql  = 'CREATE TABLE IF NOT EXISTS `' . $cgw_table . '` ( ';
		$sql .= '`id` BIGINT(20) NOT NULL AUTO_INCREMENT, ';
		$sql .= '`post_id` BIGINT(20) NOT NULL, ';
		$sql .= '`active` TINYINT(1) NOT NULL DEFAULT \'0\', ';
		$sql .= '`sg_prod` BIGINT(20) NOT NULL, ';
		$sql .= '`sg_cat` BIGINT(20) NOT NULL, ';
		$sql .= '`sg_tag` BIGINT(20) NOT NULL, ';
		$sql .= '`sg_attr` VARCHAR(255) NOT NULL, ';
		$sql .= 'PRIMARY KEY (`id`) ';
		$sql .= ') DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; ';
		require_once ABSPATH . '/wp-admin/includes/upgrade.php';
		dbDelta( $sql ); // db call ok; no-cache ok.

		commercekit_sg_convert_post_meta();
	}

	$options = get_option( 'commercekit', array() );
	$db_keys = array(
		'at'   => 'active',
		'ato'  => 'activeo',
		'tt'   => 'title',
		'd'    => 'days',
		'h'    => 'hours',
		'm'    => 'minutes',
		's'    => 'seconds',
		'dl'   => 'days_label',
		'hl'   => 'hours_label',
		'ml'   => 'minutes_label',
		'sl'   => 'seconds_label',
		'tp'   => 'type',
		'cnd'  => 'condition',
		'pids' => 'pids',
	);
	foreach ( $db_keys as $old_key => $new_key ) {
		if ( isset( $options['ctd']['pdt'][ $old_key ] ) ) {
			$options['countdown']['product'][ $new_key ] = $options['ctd']['pdt'][ $old_key ];
			unset( $options['ctd']['pdt'][ $old_key ] );
		}
	}
	$db_keys = array(
		'at' => 'active',
		'tt' => 'title',
		'em' => 'expiry_message',
		'm'  => 'minutes',
		's'  => 'seconds',
	);
	foreach ( $db_keys as $old_key => $new_key ) {
		if ( isset( $options['ctd']['ckt'][ $old_key ] ) ) {
			$options['countdown']['checkout'][ $new_key ] = $options['ctd']['ckt'][ $old_key ];
			unset( $options['ctd']['ckt'][ $old_key ] );
		}
	}
	if ( isset( $options['ctd'] ) ) {
		unset( $options['ctd'] );
	}

	$db_keys = array(
		'at'   => 'active',
		'ato'  => 'activeo',
		'id'   => 'id',
		'tt'   => 'title',
		'bt'   => 'button_text',
		'ba'   => 'button_added',
		'cnd'  => 'condition',
		'pids' => 'pids',
	);
	foreach ( $db_keys as $old_key => $new_key ) {
		if ( isset( $options['obp']['pdt'][ $old_key ] ) ) {
			$options['order_bump_product']['product'][ $new_key ] = $options['obp']['pdt'][ $old_key ];
			unset( $options['obp']['pdt'][ $old_key ] );
		}
	}
	if ( isset( $options['obp'] ) ) {
		unset( $options['obp'] );
	}
	if ( isset( $options['ajs_success_text'] ) ) {
		$options['wtl_success_text'] = $options['ajs_success_text'];
		unset( $options['ajs_success_text'] );
	}
	$options = commercekit_get_default_settings( '', $options );

	update_option( 'commercekit', $options, false );
	update_option( 'commercekit_db_version', $commercekit_db_version, false );

	$is_flushed = (int) get_option( 'commercekit_cgkit_wishlist' );
	update_option( 'commercekit_cgkit_wishlist', $is_flushed, false );
}
register_activation_hook( __FILE__, 'commercekit_create_plugin_tables' );

/**
 * Commercekit update db check.
 */
function commercekit_update_db_check() {
	global $commercekit_db_version;
	if ( (string) get_option( 'commercekit_db_version' ) !== $commercekit_db_version ) {
		commercekit_create_plugin_tables();
	}
}
add_action( 'plugins_loaded', 'commercekit_update_db_check' );

/**
 * CommerceKit compatible with WooCommerce HPOS.
 */
function commercekit_before_woocommerce_init() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}
add_action( 'before_woocommerce_init', 'commercekit_before_woocommerce_init' );

/**
 * Commercekit load gallery features.
 */
function commercekit_load_gallery_features() {
	$commercekit_flags   = commercekit_feature_flags()->get_flags();
	$enable_pdpa_gallery = isset( $commercekit_flags['pdp_attributes_gallery'] ) && 1 === (int) $commercekit_flags['pdp_attributes_gallery'] ? 1 : 0;
	$enable_pdpa_gallery = apply_filters( 'commercekit_module_attributes_gallery_visible', $enable_pdpa_gallery );
	$enable_pdp_gallery  = isset( $commercekit_flags['pdp_gallery'] ) && 1 === (int) $commercekit_flags['pdp_gallery'] ? 1 : 0;
	$enable_pdp_gallery  = apply_filters( 'commercekit_module_pdp_gallery_visible', $enable_pdp_gallery );
	if ( $enable_pdpa_gallery && $enable_pdp_gallery ) {
		require_once __DIR__ . '/class-commercegurus-attributes-gallery.php';
	} elseif ( $enable_pdp_gallery ) {
		require_once __DIR__ . '/class-commercegurus-gallery.php';
	}
}
add_action( 'plugins_loaded', 'commercekit_load_gallery_features', 998 );
