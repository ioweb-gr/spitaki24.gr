<?php
/**
 * Global WooCommerce Template Functions.
 *
 * This file contains global utility functions used across the WooCommerce implementation.
 * 
 * @package shoptimizer
 * 
 * Function Index:
 * - shoptimizer_woo_cart_available() - Validates WooCommerce cart availability
 * - shoptimizer_content_filter() - Default content filter
 * - shoptimizer_before_content() - Opens main content wrappers
 * - shoptimizer_after_content() - Closes main content wrappers
 * - shoptimizer_is_product_archive() - Checks if current page is product archive
 * - shoptimizer_widgets_disable_block_editor() - Disables Gutenberg for widgets
 * - shoptimizer_remove_woo_sidebar() - Removes sidebar from WooCommerce pages
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Cart Availability Functions
 * --------------------------
 */

if ( ! function_exists( 'shoptimizer_woo_cart_available' ) ) {
	/**
	 * Validates whether the WooCommerce Cart instance is available in the request.
	 *
	 * @since 2.6.0
	 * @return bool True if cart is available, false otherwise.
	 */
	function shoptimizer_woo_cart_available(): bool {
		$woo = WC();
		return $woo instanceof \WooCommerce && $woo->cart instanceof \WC_Cart;
	}
}

/**
 * Content Wrapper Functions
 * ------------------------
 */

if ( ! function_exists( 'shoptimizer_content_filter' ) ) {
	/**
	 * Default content filter for processing post content.
	 *
	 * @param string $details Post content to filter.
	 * @return string Filtered post content.
	 */
	function shoptimizer_content_filter( string $details ): string {
		return $details;
	}
}

if ( ! function_exists( 'shoptimizer_before_content' ) ) {
	/**
	 * Opens the main content wrappers.
	 * Wraps all WooCommerce content in wrappers that match the theme markup.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function shoptimizer_before_content(): void {
		?>
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
		<?php
	}
}

if ( ! function_exists( 'shoptimizer_after_content' ) ) {
	/**
	 * Closes the main content wrappers and adds sidebar.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function shoptimizer_after_content(): void {
		?>
			</main><!-- #main -->
		</div><!-- #primary -->
		<?php
		do_action( 'shoptimizer_sidebar' );
	}
}

/**
 * Page Type Detection Functions
 * ---------------------------
 */

if ( ! function_exists( 'shoptimizer_is_product_archive' ) ) {
	/**
	 * Checks if the current page is a product archive.
	 * Includes shop, product taxonomy, category and tag pages.
	 *
	 * @since 2.0.0
	 * @return boolean True if product archive page, false otherwise.
	 */
	function shoptimizer_is_product_archive(): bool {
		if ( ! shoptimizer_is_woocommerce_activated() ) {
			return false;
		}
		
		return is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag();
	}
}

/**
 * Widget Functions
 * ---------------
 */

// Get widget block editor setting
$shoptimizer_widgets_disable_block_editor = shoptimizer_get_option( 'shoptimizer_widgets_disable_block_editor' );

if ( true === $shoptimizer_widgets_disable_block_editor ) {
	/**
	 * Disables the Gutenberg block editor for widgets.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	function shoptimizer_widgets_disable_block_editor(): void {
		remove_theme_support( 'widgets-block-editor' );
	}
	add_action( 'after_setup_theme', 'shoptimizer_widgets_disable_block_editor' );
}

/**
 * Sidebar Functions
 * ----------------
 */

if ( ! function_exists( 'shoptimizer_remove_woo_sidebar' ) ) {
	/**
	 * Removes sidebar from WooCommerce pages (cart, checkout, my account).
	 *
	 * @since 2.0.0
	 * @return void
	 */
	function shoptimizer_remove_woo_sidebar(): void {
		if ( is_cart() || is_checkout() || is_account_page() ) {
			remove_action( 'shoptimizer_page_sidebar', 'shoptimizer_pages_sidebar', 10 );
		}
	}
}
add_action( 'wp', 'shoptimizer_remove_woo_sidebar', 20 );
