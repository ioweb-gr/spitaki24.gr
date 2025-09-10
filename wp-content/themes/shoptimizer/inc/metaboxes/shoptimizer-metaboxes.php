<?php
/**
 * Main controller for Shoptimizer Meta Boxes.
 *
 * @package Shoptimizer
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_action( 'add_meta_boxes', 'shoptimizer_register_settings_meta_box' );
/**
 * Bootstrap our custom meta boxes
 */
function shoptimizer_register_settings_meta_box() {
	if ( ! current_user_can( apply_filters( 'shoptimizer_metabox_capability', 'edit_theme_options' ) ) ) {
		return;
	}
	global $post;
	$current_blog_page_id = get_option( 'page_for_posts' );

	// Don't display Shoptimizer settings Meta Box on Blog page.
	if ( isset( $post->ID ) && $current_blog_page_id && (int) $current_blog_page_id === (int) $post->ID ) {
		return;
	}

	$current_post_types = array( 'product' );

	$meta_box_name = esc_html__( 'Shoptimizer settings', 'shoptimizer' );

	foreach ( $current_post_types as $type ) {
		if ( 'attachment' !== $type ) {
			add_meta_box(
				'shoptimizer_settings_meta_box',                        // ID.
				$meta_box_name,                                         // Title.
				'shoptimizer_init_settings_meta_box',                   // Callback.
				$type,                                                  // Post type.
				'side',                                                 // Context.
				'default'                                              // Priority.
			);
		}
	}
}

/**
 * Render the Shoptimizer Settings Meta Box.
 *
 * @param object $post WP Post object.
 */
function shoptimizer_init_settings_meta_box( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'shoptimizer_settings_nonce' );
	$current_post_meta              = array();
	$shoptimizer_settings_post_meta = (array) get_post_meta( $post->ID );

	if ( is_array( $shoptimizer_settings_post_meta ) ) {

		// Set stored and override defaults.
		foreach ( $shoptimizer_settings_post_meta as $key => $value ) {
			$current_post_meta[ $key ]['default'] = ( isset( $shoptimizer_settings_post_meta[ $key ][0] ) ) ? $shoptimizer_settings_post_meta[ $key ][0] : '';
		}
	}

	/**
	 * Vars for metas
	 */
	$product_short_desc_position       = ( isset( $current_post_meta['shoptimizer_layout_pdp_short_description_position']['default'] ) ) ? $current_post_meta['shoptimizer_layout_pdp_short_description_position']['default'] : 'default';
	$product_disable_pdp_custom_widget = ( isset( $current_post_meta['shoptimizer-disable-pdp-custom-widget']['default'] ) ) ? $current_post_meta['shoptimizer-disable-pdp-custom-widget']['default'] : '';

	?>

	<div class="shoptimizer-meta-box">
		<p class="post-attributes-label-wrapper" >
			<strong> <?php esc_html_e( 'Product short description position', 'shoptimizer' ); ?> </strong>
		</p>
		<select name="shoptimizer_layout_pdp_short_description_position" id="shoptimizer_layout_pdp_short_description_position" class="components-select-control__input">
			<option value="default" <?php selected( $product_short_desc_position, 'default' ); ?> > <?php esc_html_e( 'Customizer setting', 'shoptimizer' ); ?></option>
			<option value="top" <?php selected( $product_short_desc_position, 'top' ); ?> > <?php esc_html_e( 'Top', 'shoptimizer' ); ?></option>
			<option value="bottom" <?php selected( $product_short_desc_position, 'bottom' ); ?> > <?php esc_html_e( 'Bottom', 'shoptimizer' ); ?></option>
		</select>
	</div>

	<div class="shoptimizer-meta-box">
		<p class="post-attributes-label-wrapper" >
			<strong> <?php esc_html_e( 'Disable sections', 'shoptimizer' ); ?> </strong>
		</p>
		<div class="shoptimizer-meta-box__disable">
			<div class="shoptimizer-meta-box__disable-checkbox">
				<input type="checkbox" id="shoptimizer-disable-pdp-custom-widget" name="shoptimizer-disable-pdp-custom-widget" value="disabled" <?php checked( $product_disable_pdp_custom_widget, 'disabled' ); ?> />
				<label for="shoptimizer-disable-pdp-custom-widget"><?php esc_html_e( 'Disable Single Product Custom Area Widget', 'shoptimizer' ); ?></label> <br />
			</div>
		</div>
	</div>
	<?php
}

add_action( 'save_post', 'shoptimizer_save_settings_meta_data' );
/**
 * Saves Shoptimizer settings meta data for a given post.
 *
 * @param int $post_id Post ID.
 */
function shoptimizer_save_settings_meta_data( $post_id ) {
	$is_autosave    = wp_is_post_autosave( $post_id );
	$is_revision    = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST['shoptimizer_settings_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['shoptimizer_settings_nonce'] ), basename( __FILE__ ) ) ) ? true : false;

	if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	$product_shortdesc_pos_key   = 'shoptimizer_layout_pdp_short_description_position';
	$product_shortdesc_pos_value = isset( $_POST[ $product_shortdesc_pos_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $product_shortdesc_pos_key ] ) ) : '';
	if ( ! empty( $product_shortdesc_pos_value ) ) {
		update_post_meta( $post_id, $product_shortdesc_pos_key, $product_shortdesc_pos_value );
	} else {
		delete_post_meta( $post_id, $product_shortdesc_pos_key );
	}

	$product_disable_custom_widget_key   = 'shoptimizer-disable-pdp-custom-widget';
	$product_disable_custom_widget_value = isset( $_POST[ $product_disable_custom_widget_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $product_disable_custom_widget_key ] ) ) : '';
	if ( ! empty( $product_disable_custom_widget_value ) ) {
		update_post_meta( $post_id, $product_disable_custom_widget_key, $product_disable_custom_widget_value );
	} else {
		delete_post_meta( $post_id, $product_disable_custom_widget_key );
	}
}



add_action( 'admin_enqueue_scripts', 'shoptimizer_enqueue_meta_box_admin_scripts' );
/**
 * A small sprinkle of css for meta boxes
 *
 * @param string $hook Current wp-admin page.
 */
function shoptimizer_enqueue_meta_box_admin_scripts( $hook ) {
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
		$current_post_types = get_post_types( array( 'public' => true ) );
		$screen             = get_current_screen();
		$post_type          = $screen->id;

		if ( in_array( $post_type, (array) $current_post_types ) ) {
			wp_enqueue_style( 'shoptimizer-metabox-style', get_template_directory_uri() . '/assets/css/admin/metaboxes.css', array(), SHOPTIMIZER_VERSION );
		}
	}
}
