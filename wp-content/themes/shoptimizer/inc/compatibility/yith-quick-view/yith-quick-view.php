<?php

/**
 * Quick Views
 *
 * @package Shoptimizer
 * @since Shoptimizer 2.6.6
 */

/**
 * Adds support for various WooCommerce Quick View plugins
 *
 * @since   2.6.6
 * @return  void
 */

/**
 * Enqueue product styling css throughout site.
 */
function shoptimizer_quick_views_styles() {
	wp_enqueue_style( 'shoptimizer-product-min', get_template_directory_uri() . '/assets/css/main/product.min.css' );
	wp_enqueue_script( 'shoptimizer-quantity', get_template_directory_uri() . '/assets/js/quantity.min.js', array(), '1.1.3', true );
}
add_action( 'wp_enqueue_scripts', 'shoptimizer_quick_views_styles' );