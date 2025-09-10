<?php
/**
 * YITH WooCommerce Ajax Product Filter compatibility.
 *
 * @package shoptimizer
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Strip HTML from product titles in WooCommerce loops when YITH filter is active.
 * This prevents layout issues caused by YITH filter injecting markup into titles.
 */
if ( ! function_exists( 'shoptimizer_fix_yith_filter_titles' ) ) {
    function shoptimizer_fix_yith_filter_titles( $title, $post_id ) {
        global $woocommerce_loop;
        
        // Only apply in product loops
        if ( isset( $woocommerce_loop['loop'] ) && is_numeric( $woocommerce_loop['loop'] ) ) {
            return wp_strip_all_tags( $title );
        }
        
        return $title;
    }
    
    // Only add the filters if the function was successfully created
    add_filter( 'the_title', 'shoptimizer_fix_yith_filter_titles', 100, 2 );
    add_filter( 'woocommerce_product_title', 'shoptimizer_fix_yith_filter_titles', 100, 2 );
}