<?php
/**
 * Checkout specific template functions and hooks
 *
 * This file contains functions and hooks specific to the checkout process.
 * 
 * @package shoptimizer
 * 
 * Function Index:
 * - shoptimizer_minimal_checkout_body_class() - Adds minimal checkout body class
 * - shoptimizer_checkout_custom_field() - Adds custom widget area below checkout button
 * - shoptimizer_coupon_wrapper_start() - Adds coupon section wrapper start
 * - shoptimizer_coupon_wrapper_end() - Adds coupon section wrapper end
 * - shoptimizer_product_thumbnail_in_checkout() - Adds product thumbnails to checkout
 * - shoptimizer_woocommerce_checkout_cart_item_quantity() - Modifies cart item quantity display
 * - shoptimizer_minimal_checkout() - Implements minimal checkout template
 * - shoptimizer_custom_thankyou_section() - Adds custom thank you page content
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Checkout Layout Functions
 * ------------------------
 */

if ( ! function_exists( 'shoptimizer_minimal_checkout_body_class' ) ) {
    /**
     * Adds a body class for minimal checkout styling.
     *
     * @since 1.0.0
     * @param array $classes Existing body classes
     * @return array Modified body classes
     */
    function shoptimizer_minimal_checkout_body_class( array $classes ): array {
        $simple_checkout = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_simple_checkout' );

        if ( true === $simple_checkout && is_checkout() ) {
            $classes[] = 'min-ck';
        }
        return $classes;
    }
}
add_filter( 'body_class', 'shoptimizer_minimal_checkout_body_class' );

/**
 * Checkout Widget Areas
 * --------------------
 */

if ( ! function_exists( 'shoptimizer_checkout_custom_field' ) ) {
    /**
     * Displays custom widget area below the checkout button.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_checkout_custom_field(): void {
        if ( is_active_sidebar( 'checkout-field' ) ) {
            echo '<div class="cart-custom-field" 
                       role="complementary" 
                       aria-label="' . esc_attr__('Additional checkout information', 'shoptimizer') . '">';
            dynamic_sidebar( 'checkout-field' );
            echo '</div>';
        }
    }
}
add_action( 'woocommerce_review_order_after_payment', 'shoptimizer_checkout_custom_field', 15 );

/**
 * Coupon Display Functions
 * -----------------------
 */

if ( ! function_exists( 'shoptimizer_coupon_wrapper_start' ) ) {
    /**
     * Adds opening markup for coupon code section.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_coupon_wrapper_start(): void {
        echo '<section class="coupon-wrapper" 
                     aria-label="' . esc_attr__('Coupon code section', 'shoptimizer') . '"
                     role="region">';
    }
}

if ( ! function_exists( 'shoptimizer_coupon_wrapper_end' ) ) {
    /**
     * Adds closing markup for coupon code section.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_coupon_wrapper_end(): void {
        echo '</section>';
    }
}

/**
 * Product Display Functions
 * ------------------------
 */

if ( ! function_exists( 'shoptimizer_product_thumbnail_in_checkout' ) ) {
    /**
     * Adds product thumbnails to the checkout order review table.
     *
     * @since 1.0.0
     * @param string $product_name The product name HTML
     * @param array  $cart_item Cart item data
     * @param string $cart_item_key Cart item key
     * @return string Modified product name HTML with thumbnail
     */
    function shoptimizer_product_thumbnail_in_checkout( string $product_name, array $cart_item, string $cart_item_key ): string {
        if ( is_checkout() ) {
            $product = $cart_item['data'];
            $thumbnail = $product->get_image(
                'woocommerce_gallery_thumbnail',
                array( 
                    'class' => 'skip-lazy',
                    'alt' => sprintf(
                        /* translators: %s: Product name */
                        esc_attr__('Product image for %s', 'shoptimizer'),
                        strip_tags($product->get_name())
                    )
                )
            );
            
            $image_html = '<div class="product-item-thumbnail">' . $thumbnail . '</div> ';
            $name_html_open = '<div class="cg-checkout-table-product-name">';
            $product_name = $image_html . $name_html_open . $product_name;
        }
        return $product_name;
    }
}
add_filter( 'woocommerce_cart_item_name', 'shoptimizer_product_thumbnail_in_checkout', 20, 3 );

if ( ! function_exists( 'shoptimizer_woocommerce_checkout_cart_item_quantity' ) ) {
    /**
     * Modifies the cart item quantity display in checkout.
     *
     * @since 1.0.0
     * @param string $html The quantity HTML
     * @param array  $cart_item Cart item data
     * @param string $cart_item_key Cart item key
     * @return string Modified quantity HTML
     */
    function shoptimizer_woocommerce_checkout_cart_item_quantity( string $html, array $cart_item, string $cart_item_key ): string {
        return $html . '<div class="clear"></div></div>';
    }
}
add_filter( 'woocommerce_checkout_cart_item_quantity', 'shoptimizer_woocommerce_checkout_cart_item_quantity', 10, 3 );

/**
 * Minimal Checkout Template
 * ------------------------
 */

if ( ! function_exists( 'shoptimizer_minimal_checkout' ) ) {
    /**
     * Implements minimal checkout template by removing standard elements.
     * Only applies when simple checkout option is enabled and user is on checkout page.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_minimal_checkout(): void {
        $simple_checkout = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_simple_checkout' );
        
        if ( ! $simple_checkout || ! class_exists( 'WooCommerce' ) ) {
            return;
        }

        // Only modify checkout page, excluding order received page
        if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
            // Remove header elements
            remove_action( 'shoptimizer_topbar', 'shoptimizer_top_bar', 10 );
            remove_action( 'shoptimizer_before_site', 'shoptimizer_top_bar', 10 );
            remove_action( 'shoptimizer_header', 'shoptimizer_primary_navigation', 99 );
            remove_action( 'shoptimizer_header', 'shoptimizer_secondary_navigation', 30 );
            remove_action( 'shoptimizer_before_content', 'shoptimizer_header_widget_region', 10 );

            // Add checkout title
            add_action( 'shoptimizer_header', 'shoptimizer_checkout_heading', 30 );

            // Remove navigation elements
            remove_action( 'shoptimizer_navigation', 'shoptimizer_primary_navigation', 50 );
            remove_action( 'shoptimizer_navigation', 'shoptimizer_primary_navigation_wrapper', 42 );
            remove_action( 'shoptimizer_navigation', 'shoptimizer_header_cart', 60 );
            remove_action( 'shoptimizer_navigation', 'shoptimizer_primary_navigation_wrapper_close', 68 );

            // Remove cart and search
            remove_action( 'shoptimizer_header', 'shoptimizer_header_cart', 50 );
            remove_action( 'shoptimizer_header', 'shoptimizer_header_cart', 60 );
            remove_action( 'shoptimizer_header', 'shoptimizer_product_search', 25 );

            // Remove page elements
            remove_action( 'shoptimizer_page_start', 'shoptimizer_page_header', 10 );
            remove_action( 'shoptimizer_before_footer', 'shoptimizer_below_content', 10 );
            remove_action( 'shoptimizer_footer', 'shoptimizer_footer_widgets', 20 );
            remove_action( 'shoptimizer_footer', 'shoptimizer_footer_copyright', 30 );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'shoptimizer_minimal_checkout' );

if ( ! function_exists( 'shoptimizer_checkout_heading' ) ) {
    /**
     * Displays the checkout page title.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_checkout_heading(): void {
        the_title( '<h1>', '</h1>' );
    }
}

/**
 * Thank You Page Functions
 * -----------------------
 */

if ( ! function_exists( 'shoptimizer_custom_thankyou_section' ) ) {
    /**
     * Displays custom widget area on the order thank you page.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_custom_thankyou_section(): void {
        echo '<div class="thankyou-custom-field" 
                  role="complementary" 
                  aria-label="' . esc_attr__('Order confirmation details', 'shoptimizer') . '"
                  aria-live="polite">';
        dynamic_sidebar( 'thankyou-field' );
        echo '</div>';
    }
}
add_action( 'woocommerce_thankyou', 'shoptimizer_custom_thankyou_section' );

function shoptimizer_minimal_checkout_skip_link(): void {
    if (is_checkout() && !is_wc_endpoint_url('order-received')) {
        echo '<a href="#checkout-content" class="screen-reader-text skip-link">';
        esc_html_e('Skip to checkout form', 'shoptimizer');
        echo '</a>';
    }
}
add_action('shoptimizer_before_content', 'shoptimizer_minimal_checkout_skip_link', 5);