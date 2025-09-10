<?php

/**
 * CheckoutWC
 *
 * @package Shoptimizer
 * @since Shoptimizer 2.8.7
 */

/**
 * WooCommerce add to cart redirect
 * Adds support for prevent redirect to cart on ajax add to cart action
 *
 * @since   1.0.0
 * @return  void
 */
function shoptimizer_checkoutwc_ajax_add_to_cart_redirect( $url ){
	if ( isset( $_REQUEST['wc-ajax'] ) && 'shoptimizer_pdp_ajax_atc' === $_REQUEST['wc-ajax'] ) {
		return false;
	}

	return $url;
}
add_filter( 'woocommerce_add_to_cart_redirect', 'shoptimizer_checkoutwc_ajax_add_to_cart_redirect', 9999, 1 );
