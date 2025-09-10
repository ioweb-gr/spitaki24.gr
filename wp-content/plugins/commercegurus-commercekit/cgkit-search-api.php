<?php
/**
 *
 * Ajax Fast Search script
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

ini_set( 'html_errors', 0 ); //phpcs:ignore
define( 'CGKIT_WP_PATH', '../../..' );
define( 'DOING_AJAX', true );
define( 'SHORTINIT', true );
define( 'WP_USE_THEMES', false );

$options = array();
if ( ! defined( 'ABSPATH' ) ) {
	require_once CGKIT_WP_PATH . '/wp-load.php';
	$options = get_option( 'commercekit', array() );
}
if ( isset( $options['ajs_fast_search'] ) && 1 === (int) $options['ajs_fast_search'] ) {
	require_once ABSPATH . WPINC . '/default-constants.php';
	require_once ABSPATH . WPINC . '/block-template-utils.php';
	require_once ABSPATH . WPINC . '/link-template.php';
	require_once ABSPATH . WPINC . '/post-thumbnail-template.php';
	require_once ABSPATH . WPINC . '/class-wp-term.php';
	require_once ABSPATH . WPINC . '/class-wp-term-query.php';
	require_once ABSPATH . WPINC . '/class-wp-tax-query.php';
	require_once ABSPATH . WPINC . '/taxonomy.php';
	require_once ABSPATH . WPINC . '/l10n.php';
	require_once ABSPATH . WPINC . '/class-wp-post.php';
	require_once ABSPATH . WPINC . '/class-wp-post-type.php';
	require_once ABSPATH . WPINC . '/post.php';
	require_once ABSPATH . WPINC . '/class-wp-query.php';
	require_once ABSPATH . WPINC . '/query.php';
	require_once ABSPATH . WPINC . '/shortcodes.php';
	require_once ABSPATH . WPINC . '/media.php';
	require_once ABSPATH . WPINC . '/kses.php';
	require_once ABSPATH . WPINC . '/class-wp-rewrite.php';
	require_once ABSPATH . WPINC . '/rewrite.php';
	$GLOBALS['wp_rewrite'] = new WP_Rewrite(); //phpcs:ignore

	require_once dirname( __FILE__ ) . '/includes/module-fast-ajax-search.php';
	require_once dirname( __FILE__ ) . '/includes/class-commercekit-ajs-index.php';

	wp_plugin_directory_constants();
	$search_type = isset( $_GET['search_type'] ) ? sanitize_text_field( wp_unslash( $_GET['search_type'] ) ) : 'product'; // phpcs:ignore
	commercekit_ajax_do_search( $search_type );
}
exit();
