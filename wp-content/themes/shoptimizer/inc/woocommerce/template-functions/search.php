<?php
/**
 * Search specific template functions and hooks
 *
 * @package shoptimizer
 * 
 * Function Index:
 * - shoptimizer_product_search() - Displays product search form based on theme settings
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Search Form Functions
 * --------------------
 */

if ( ! function_exists( 'shoptimizer_product_search' ) ) {
	/**
	 * Display Product Search form based on theme settings.
	 * Supports multiple search types including default WooCommerce search,
	 * AJAX Search for WooCommerce, Advanced Woo Search, Smart Search Pro,
	 * and YITH WooCommerce AJAX Search.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function shoptimizer_product_search(): void {
		// Get theme options
		$search_display = shoptimizer_get_option( 'shoptimizer_layout_search_display' );
		$search_display_type = shoptimizer_get_option( 'shoptimizer_layout_search_display_type' );

		// Early return if WooCommerce is not active
		if ( ! shoptimizer_is_woocommerce_activated() ) {
			return;
		}

		// Determine search wrapper class based on display type
		$wrapper_class = 'outline' === $search_display_type ? 'site-search type-outline' : 'site-search';

		// Display appropriate search form based on settings
		switch ( $search_display ) {
			case 'enable':
				printf(
					'<div class="%s" role="search" aria-label="%s">',
					esc_attr($wrapper_class),
					esc_attr__('Product Search', 'shoptimizer')
				);
				the_widget( 'WC_Widget_Product_Search', 'title=' );
				echo '</div>';
				break;

			case 'ajax-search-wc':
				printf(
					'<div class="%s" role="search" aria-label="%s">',
					esc_attr($wrapper_class),
					esc_attr__('Product Search', 'shoptimizer')
				);
				echo do_shortcode( '[fibosearch]' );
				echo '</div>';
				break;

			case 'advanced-woo-search':
				printf(
					'<div class="%s" role="search" aria-label="%s">',
					esc_attr($wrapper_class),
					esc_attr__('Product Search', 'shoptimizer')
				);
				echo do_shortcode( '[aws_search_form]' );
				echo '</div>';
				break;

			case 'smart-search-pro':
				printf(
					'<div class="%s" role="search" aria-label="%s">',
					esc_attr($wrapper_class),
					esc_attr__('Product Search', 'shoptimizer')
				);
				echo do_shortcode( '[smart_search id="1"]' );
				echo '</div>';
				break;

			case 'yith-search':
				printf(
					'<div class="%s" role="search" aria-label="%s">',
					esc_attr($wrapper_class),
					esc_attr__('Product Search', 'shoptimizer')
				);
				echo do_shortcode( '[yith_woocommerce_ajax_search]' );
				echo '</div>';
				break;

			case 'regular':
				printf(
					'<div class="%s" role="search" aria-label="%s">',
					esc_attr($wrapper_class),
					esc_attr__('Product Search', 'shoptimizer')
				);
				get_search_form();
				echo '</div>';
				break;
		}
	}
}
