<?php
/**
 *
 * Some wrappers for theme mods/options and their defaults
 *
 * @package CommerceGurus
 * @subpackage shoptimizer
 */

// Set sensible defaults.
require_once get_template_directory() . '/inc/customizer/defaults.php';

$shoptmizer_typography2_enabled = shoptimizer_typography2_enabled();
if ( $shoptmizer_typography2_enabled ) {
	require_once get_template_directory() . '/inc/customizer/defaults-extended.php';
}

if ( ! function_exists( 'shoptimizer_get_option' ) ) {
	/**
	 * Main function used to call them options
	 *
	 * @param int $key The theme option argument.
	 */
	function shoptimizer_get_option( $key ) {
		$shoptimizer_options = shoptimizer_get_options();
		$shoptimizer_option  = get_theme_mod( $key, $shoptimizer_options[ $key ] );
		return $shoptimizer_option;
	}
}

if ( ! function_exists( 'shoptimizer_get_options' ) ) {

	/**
	 * Get theme option defaults
	 */
	function shoptimizer_get_options() {
		return wp_parse_args(
			get_theme_mods(),
			shoptimizer_get_option_defaults()
		);
	}
}

if ( ! function_exists( 'shoptimizer_get_post_meta' ) ) {

	/**
	 * Get option meta
	 */
	function shoptimizer_get_post_meta( $option_key, $meta_key_only = false, $post_id = '' ) {
		$post_id = ( '' != $post_id ) ? $post_id : shoptimizer_get_post_id();
		$val = shoptimizer_get_option( $option_key );
		// Get value from option 'post-meta'.
		if ( is_singular() || is_product() || ( is_home() && ! is_front_page() ) ) {

			$val = get_post_meta( $post_id, $option_key, true );

			if ( empty( $val ) || 'default' == $val ) {

				if ( true === $meta_key_only ) {
					return false;
				}

				$val = shoptimizer_get_option( $option_key );
			}
		}

		return $val;
	}
}

if ( ! function_exists( 'shoptimizer_get_post_id' ) ) {
	/**
	 * Get post ID.
	 *
	 * @return number                   Post ID.
	 */
	function shoptimizer_get_post_id() {
		global $post;
		$post_id = 0;
		if ( is_home() ) {
			$post_id = get_option( 'page_for_posts' );
		} elseif ( is_archive() ) {
			global $wp_query;
			$post_id = $wp_query->get_queried_object_id();
		} elseif ( isset( $post->ID ) && ! is_search() && ! is_category() ) {
			$post_id = $post->ID;
		}

		return $post_id;
	}
}