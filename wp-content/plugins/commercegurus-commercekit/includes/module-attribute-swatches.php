<?php
/**
 *
 * Attribute swatches module
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

/**
 * Get product attributes swatches admin tab.
 *
 * @param string $tabs admin product tabs.
 */
function commercegurus_get_attribute_swatches_tab( $tabs ) {
	$tabs['commercekit_swatches'] = array(
		'label'    => esc_html__( 'Attribute Swatches', 'commercegurus-commercekit' ),
		'target'   => 'cgkit_attr_swatches',
		'class'    => array( 'commercekit-attributes-swatches', 'show_if_variable' ),
		'priority' => 62,
	);
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'commercegurus_get_attribute_swatches_tab' );

/**
 * Get product attributes swatches admin panel.
 */
function commercegurus_get_attribute_swatches_panel() {
	global $post;
	$product_id = $post->ID;
	$product_id = intval( $product_id );
	$product    = wc_get_product_object( 'variable', $product_id );
	$attributes = commercegurus_attribute_swatches_load_attributes( $product );

	$attribute_swatches = get_post_meta( $product_id, 'commercekit_attribute_swatches', true );
	require_once dirname( __FILE__ ) . '/templates/admin-attribute-swatches.php';
}
add_filter( 'woocommerce_product_data_panels', 'commercegurus_get_attribute_swatches_panel' );

/**
 * Add admin CSS and JS scripts
 */
function commercegurus_attribute_swatches_admin_scripts() {
	$screen = get_current_screen();
	if ( 'product' === $screen->post_type && 'post' === $screen->base ) {
		wp_enqueue_style( 'commercekit-admin-attribute-swatches-style', CKIT_URI . 'assets/css/admin-attribute-swatches.css', array(), CGKIT_CSS_JS_VER );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'commercekit-admin-attribute-swatches-script', CKIT_URI . 'assets/js/admin-attribute-swatches.js', array( 'wp-color-picker' ), CGKIT_CSS_JS_VER, true );
	}
}
add_action( 'admin_enqueue_scripts', 'commercegurus_attribute_swatches_admin_scripts' );

/**
 * Save product attributes gallery
 *
 * @param string $post_id post ID.
 * @param string $post post.
 */
function commercegurus_save_product_attribute_swatches( $post_id, $post ) {
	if ( 'product' !== $post->post_type ) {
		return;
	}
	$cgkit_swatches_nonce = isset( $_POST['cgkit_swatches_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['cgkit_swatches_nonce'] ) ) : '';
	if ( $cgkit_swatches_nonce && wp_verify_nonce( $cgkit_swatches_nonce, 'cgkit_swatches_nonce' ) ) {
		if ( $post_id ) {
			$attribute_swatches = isset( $_POST['commercekit_attribute_swatches'] ) ? map_deep( wp_unslash( $_POST['commercekit_attribute_swatches'] ), 'sanitize_textarea_field' ) : array();
			if ( ! isset( $attribute_swatches['enable_loop'] ) ) {
				$attribute_swatches['enable_loop'] = 0;
			}
			if ( ! isset( $attribute_swatches['enable_product'] ) ) {
				$attribute_swatches['enable_product'] = 0;
			}
			update_post_meta( $post_id, 'commercekit_attribute_swatches', $attribute_swatches );
		}
	}
}
add_action( 'woocommerce_process_product_meta', 'commercegurus_save_product_attribute_swatches', 10, 2 );

/**
 * Get ajax product gallery
 */
function commercegurus_get_ajax_attribute_swatches() {
	$ajax           = array();
	$ajax['status'] = 0;
	$ajax['html']   = '';

	$commercekit_nonce = isset( $_GET['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json( $ajax );
	}

	$product_id = isset( $_GET['product_id'] ) ? (int) $_GET['product_id'] : 0; // phpcs:ignore
	if ( $product_id ) {
		ob_start();
		$product_id   = intval( $product_id );
		$product      = wc_get_product_object( 'variable', $product_id );
		$attributes   = commercegurus_attribute_swatches_load_attributes( $product );
		$without_wrap = true;

		$attribute_swatches = get_post_meta( $product_id, 'commercekit_attribute_swatches', true );
		require_once dirname( __FILE__ ) . '/templates/admin-attribute-swatches.php';

		$ajax['status'] = 1;
		$ajax['html']   = ob_get_contents();
		ob_clean();
	}

	wp_send_json( $ajax );
}
add_action( 'wp_ajax_commercekit_get_ajax_attribute_swatches', 'commercegurus_get_ajax_attribute_swatches' );

/**
 * Update ajax product gallery
 */
function commercegurus_update_ajax_attribute_swatches() {
	$ajax           = array();
	$ajax['status'] = 0;
	$ajax['html']   = '';

	$commercekit_nonce = isset( $_POST['commercekit_nonce_as'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce_as'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json( $ajax );
	}

	$product_id = isset( $_GET['product_id'] ) ? (int) $_GET['product_id'] : 0; // phpcs:ignore
	if ( $product_id ) {
		$post = get_post( $product_id );
		commercegurus_save_product_attribute_swatches( $product_id, $post );
		$ajax['status'] = 1;
	}

	wp_send_json( $ajax );
}
add_action( 'wp_ajax_commercekit_update_ajax_attribute_swatches', 'commercegurus_update_ajax_attribute_swatches' );

if ( ! defined( 'COMMERCEKIT_ATTRIBUTE_SWATCHES_VISIBLE' ) || COMMERCEKIT_ATTRIBUTE_SWATCHES_VISIBLE !== false ) {
	require_once CGKIT_BASE_PATH . 'includes/templates/frontend-attribute-swatches.php';
}
