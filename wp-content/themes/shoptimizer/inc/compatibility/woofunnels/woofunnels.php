<?php

/**
 * WooFunnels
 *
 * @package Shoptimizer
 * @since Shoptimizer 2.5.3
 */

add_action( 'init', 'shoptimizer_woofunnels_remove_checkout_filters' );
function shoptimizer_woofunnels_remove_checkout_filters() {
   remove_filter( 'woocommerce_cart_item_name', 'shoptimizer_product_thumbnail_in_checkout', 20, 3 );
   remove_filter( 'woocommerce_checkout_cart_item_quantity', 'shoptimizer_woocommerce_checkout_cart_item_quantity', 10, 3 );
}