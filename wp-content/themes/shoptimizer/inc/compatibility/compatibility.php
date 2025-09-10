<?php

function shoptimizer_compatability_styles() {

	$compatibility_uri = get_template_directory_uri() . '/inc/compatibility';

	if ( class_exists( 'Addify_Request_For_Quote' ) ) {
		wp_enqueue_style( 'shoptimizer-wc-quote-style', $compatibility_uri . '/wc-quote/wc-quote.css' );
	}
	if ( class_exists( 'Addify_Product_Videos' ) ) {
		wp_enqueue_style( 'shoptimizer-product-videos-style', $compatibility_uri . '/woocommerce-product-videos/woocommerce-product-videos.css' );
	}
	if ( class_exists( 'Commercegurus_WC_Product_Filters' ) ) {
		wp_enqueue_style( 'shoptimizer-cgurus-product-filters-style', $compatibility_uri . '/commercegurus-product-filters/commercegurus-product-filters.css' );
	}
	if ( class_exists( 'ElementorPro\Plugin' ) ) {
		wp_enqueue_style( 'shoptimizer-elementor-pro', $compatibility_uri . '/elementor-pro/elementor-pro.css' );
	}
	if ( class_exists( 'Jetpack' ) ) {
		wp_enqueue_style( 'jetpack-style', $compatibility_uri . '/jetpack/jetpack.css' );
	}
	if ( class_exists( 'JudgeMe' ) ) {
		wp_enqueue_style( 'judgeme-style', $compatibility_uri . '/judgeme/judgeme.css' );
	}
	if ( class_exists( 'MC4WP_Container' ) ) {
		wp_enqueue_style( 'mailchimp-wp-style', $compatibility_uri . '/mailchimp-wp/mailchimp-wp.css' );
	}
	if ( class_exists( 'SendyWidgetPRO' ) ) {
		wp_enqueue_style( 'shoptimizer-sendy-pro-style', $compatibility_uri . '/sendy/sendy.css' );
	}
	if ( class_exists( 'WC_Quick_View' ) ) {
		wp_enqueue_style( 'shoptimizer-wc-quickview-style', $compatibility_uri . '/wc-quick-views/wc-quick-view.css' );
	}
	if ( class_exists( 'YITH_WCQV' ) ) {
		wp_enqueue_style( 'shoptimizer-yith-quickview-style', $compatibility_uri . '/yith-quick-view/yith-quick-view.css' );
	}
	if ( class_exists( 'WC_Composite_Products' ) ) {
		wp_enqueue_style( 'shoptimizer-composite-products-style', $compatibility_uri . '/woocommerce-composite-products/woocommerce-composite-products.css' );
	}
	if ( class_exists( 'WC_Bundles' ) ) {
		wp_enqueue_style( 'shoptimizer-wc-bundles-style', $compatibility_uri . '/woocommerce-product-bundles/woocommerce-product-bundles.css' );
	}
	if ( class_exists( 'WC_Subscriptions' ) ) {
		wp_enqueue_style( 'shoptimizer-wc-subscriptions-style', $compatibility_uri . '/woocommerce-subscriptions/woocommerce-subscriptions.css' );
	}
	if ( class_exists( 'WC_360_Image_Display' ) ) {
		wp_enqueue_style( 'shoptimizer-wc-360-style', $compatibility_uri . '/woocommerce-360-image/woocommerce-360-image.css' );
	}
	if ( class_exists( 'SW_WDP\WDP' ) ) {
		wp_enqueue_style( 'shoptimizer-sw-wdp', $compatibility_uri . '/sw-woocommerce-discounts/sw-woocommerce-discounts.css' );
	}
	if ( class_exists( 'WeDevs_Dokan' ) ) {
		wp_enqueue_style( 'shoptimizer-dokan-style', $compatibility_uri . '/dokan/dokan.css' );
	}
	if ( class_exists( 'WooCommerce_Germanized' ) ) {
		wp_enqueue_style( 'shoptimizer-germanized-style', $compatibility_uri . '/germanized/germanized.css' );
	}
	if ( class_exists( 'Woocommerce_German_Market' ) ) {
		wp_enqueue_style( 'shoptimizer-german-market-style', $compatibility_uri . '/german-market/german-market.css' );
	}
	if ( class_exists( 'wpforms' ) ) {
		wp_enqueue_style( 'shoptimizer-wpforms-style', $compatibility_uri . '/wpforms/wpforms.css' );
	}
	if ( class_exists( 'YITH_WCWL_Shortcode' ) ) {
		wp_enqueue_style( 'shoptimizer-yith-wishlist-style', $compatibility_uri . '/yith-wishlist/yith-wishlist.css' );
	}
	if ( class_exists( 'YITH_WCAN' ) ) {
		wp_enqueue_style( 'shoptimizer-yith-ajax-filter', $compatibility_uri . '/yith-filter/yith-filter.css' );
	}
}
add_action( 'wp_enqueue_scripts', 'shoptimizer_compatability_styles', 90 );

// CheckoutWC support.
if ( class_exists( \Objectiv\Plugins\Checkout\Managers\SettingsManager::class ) ) {
	require get_template_directory() . '/inc/compatibility/checkoutwc/checkoutwc.php';
}

// CommerceGurus Product Filters support.
if ( class_exists( 'Commercegurus_WC_Product_Filters' ) && 'horizontal' === Commercegurus_WC_Product_Filters::get_layout() ) {
	require get_template_directory() . '/inc/compatibility/commercegurus-product-filters/commercegurus-product-filters.php';
}

// Dokan support.
if ( class_exists( 'WeDevs_Dokan' ) ) {
	require get_template_directory() . '/inc/compatibility/dokan/dokan.php';
}

// Elementor Pro support.
if ( class_exists( 'ElementorPro\Plugin' ) ) {
	require get_template_directory() . '/inc/compatibility/elementor-pro/elementor-pro.php';
}

// WC Quick View support.
if ( class_exists( 'WC_Quick_View' ) ) {
	require get_template_directory() . '/inc/compatibility/wc-quick-view/wc-quick-view.php';
}

// WooFunnels support.
if ( class_exists( 'WFFN_Core' ) ) {
	require get_template_directory() . '/inc/compatibility/woofunnels/woofunnels.php';
}

// YITH Wishlist support.
if ( class_exists( 'YITH_WCWL_Shortcode' ) ) {
	require get_template_directory() . '/inc/compatibility/yith-wishlist/yith-wishlist.php';
}

// "YITH WooCommerce Ajax Product Filters support.
if ( class_exists( 'YITH_WCAN' ) || defined( 'YITH_WCAN_PREMIUM' ) ) {
    require get_template_directory() . '/inc/compatibility/yith-wcan/yith-wcan.php';
}

// YITH Quick View support.
if ( class_exists( 'YITH_WCQV' ) ) {
	require get_template_directory() . '/inc/compatibility/yith-quick-view/yith-quick-view.php';
}