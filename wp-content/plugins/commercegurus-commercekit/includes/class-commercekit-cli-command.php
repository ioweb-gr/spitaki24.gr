<?php
/**
 *
 * CommerceKit CLI Command
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly....
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Commercekit_Clear_Cache_Command Class
	 */
	class Commercekit_CLI_Command extends WP_CLI_Command {

		/**
		 * Success message.
		 *
		 * @var message
		 */
		protected $message = '';

		/**
		 * Commercekit_Clear_Cache_Command Constructor
		 */
		public function __construct() {
			$this->message = esc_html__( 'Error on cg-commercekit CLI command.', 'commercegurus-commercekit' );
		}

		/**
		 * Commercekit_Clear_Cache_Command invoke
		 *
		 * @param mixed $args of command.
		 */
		public function __invoke( $args ) {
			$modules = array(
				'ajax-search'        => array(
					'id'      => 'ajax_search',
					'options' => array(
						'ajs-orderby-oos'   => 'ajs_orderby_oos',
						'ajs-other-results' => 'ajs_other_results',
						'ajs-fast-search'   => 'ajs_fast_search',
						'ajs-index-logger'  => 'ajs_index_logger',
					),
				),
				'attributes-gallery' => array(
					'id'      => 'pdp_attributes_gallery',
					'options' => array(),
				),
				'attribute-swatches' => array(
					'id'      => 'attribute_swatches',
					'options' => array(
						'display-tooltips'    => 'as_enable_tooltips',
						'pdp-swatches'        => 'attribute_swatches_pdp',
						'plp-swatches'        => 'attribute_swatches_plp',
						'plp-quick-atc'       => 'as_activate_atc',
						'hide-loading-facade' => 'as_disable_facade',
						'hide-pdp-related'    => 'as_disable_pdp',
					),
				),
				'countdown-timer'    => array(
					'id'      => 'countdown_timer',
					'options' => array(
						'checkout-countdown' => 'countdown][checkout][active',
						'product-shortcode'  => 'widget_pos_countdown',
						'checkout-shortcode' => 'widget_pos_countdown2',
					),
				),
				'free-shipping'      => array(
					'id'      => 'none',
					'options' => array(
						'fsn-cart-page'        => 'fsn_cart_page',
						'fsn-mini-cart'        => 'fsn_mini_cart',
						'display-notification' => 'fsn_before_ship',
						'fsn-shortcode'        => 'widget_pos_fsn',
					),
				),
				'order-bump'         => array(
					'id'      => 'none',
					'options' => array(
						'order-bump-mini-cart' => 'order_bump_mini',
						'multiple-mini-cart'   => 'multiple_obp_mini',
						'order-bump-checkout'  => 'order_bump',
						'multiple-checkout'    => 'multiple_obp',
						'order-bump-shortcode' => 'widget_pos_obp',
					),
				),
				'product-badges'     => array(
					'id'      => 'store_badge',
					'options' => array(
						'new-display-catalog' => 'badge][new][catalog',
						'new-display-product' => 'badge][new][product',
					),
				),
				'product-gallery'    => array(
					'id'      => 'pdp_gallery',
					'options' => array(
						'pdp-lightbox'        => 'pdp_lightbox',
						'pdp-video-autoplay'  => 'pdp_video_autoplay',
						'pdp-thumb-arrows'    => 'pdp_thumb_arrows',
						'pdp-featured-review' => 'pdp_featured_review',
						'gallery-shortcode'   => 'widget_pos_pdp_gallery',
					),
				),
				'size-guide'         => array(
					'id'      => 'size_guide',
					'options' => array(
						'display-search-result' => 'size_guide_search',
						'size-guide-shortcode'  => 'widget_pos_sizeguide',
					),
				),
				'sticky-atc'         => array(
					'id'      => 'none',
					'options' => array(
						'sticky-atc-desktop' => 'sticky_atc_desktop',
						'sticky-atc-mobile'  => 'sticky_atc_mobile',
						'sticky-atc-tabs'    => 'sticky_atc_tabs',
					),
				),
				'stock-meter'        => array(
					'id'      => 'inventory_display',
					'options' => array(
						'stock-meter-shortcode' => 'widget_pos_stockmeter',
					),
				),
				'waitlist'           => array(
					'id'      => 'waitlist',
					'options' => array(
						'hide-oos-variation'  => 'wtl_show_oos',
						'force-email-name'    => 'wtl_force_email_name',
						'waitlist-auto-mail'  => 'waitlist_auto_mail',
						'wtl-not-stock-limit' => 'wtl_not_stock_limit',
						'waitlist-admin-mail' => 'waitlist_admin_mail',
						'waitlist-user-mail'  => 'waitlist_user_mail',
					),
				),
				'wishlist'           => array(
					'id'      => 'wishlist',
					'options' => array(
						'wishlist-shortcode' => 'widget_pos_wishlist',
					),
				),
				'settings'           => array(
					'id'      => 'none',
					'options' => array(
						'cache-logger' => 'as_logger',
					),
				),
				'import-export'      => array(
					'id'      => 'none',
					'options' => array(
						'import-export-logger' => 'export_import_logger',
					),
				),
			);

			$helpers = array(
				'ajax-search'           => 'Ajax Search: Enable Ajax Search in the main search bar',
				'ajs-orderby-oos'       => 'Ajax Search: Display out of stock items at the end of the search results',
				'ajs-other-results'     => 'Ajax Search: Display other results such as posts and pages',
				'ajs-fast-search'       => 'Ajax Search: Enable Lightning Fast Results (excludes prices)',
				'ajs-index-logger'      => 'Ajax Search: Enable Product Ajax Search index rebuilding logger',
				'attributes-gallery'    => 'Attributes Gallery: Enable Attributes Gallery',
				'attribute-swatches'    => 'Attribute Swatches: Enable Attribute Swatches',
				'display-tooltips'      => 'Attribute Swatches: Display tooltips on color and image swatches',
				'pdp-swatches'          => 'Attribute Swatches: Attribute Swatches on Product Details Pages',
				'plp-swatches'          => 'Attribute Swatches: Attribute Swatches on Listing Pages',
				'plp-quick-atc'         => 'Attribute Swatches: Activate Quick add to cart',
				'hide-loading-facade'   => 'Attribute Swatches: Disable loading facade on Product Listings Pages',
				'hide-pdp-related'      => 'Attribute Swatches: Switch off swatches for related products and within the menu on PDPs',
				'countdown-timer'       => 'Countdown Timers: Enable countdowns on single product and checkout pages',
				'checkout-countdown'    => 'Countdown Timers: Enable countdown timer on checkout page',
				'product-shortcode'     => 'Countdown Timers: Enable Product Countdown shortcode',
				'checkout-shortcode'    => 'Countdown Timers: Enable Checkout Countdown shortcode',
				'fsn-cart-page'         => 'Free Shipping Notification: Display on the cart page',
				'fsn-mini-cart'         => 'Free Shipping Notification: Display on the mini cart',
				'display-notification'  => 'Free Shipping Notification: Display notification before entering the shipping address',
				'fsn-shortcode'         => 'Free Shipping Notification: Enable Free Shipping Notification shortcode',
				'order-bump-mini-cart'  => 'Order Bump: Enable order bumps within the mini cart',
				'multiple-mini-cart'    => 'Order Bump: Allow multiple order bumps within the mini cart',
				'order-bump-checkout'   => 'Order Bump: Enable order bumps on checkout page',
				'multiple-checkout'     => 'Order Bump: Allow multiple order bumps on checkout page',
				'order-bump-shortcode'  => 'Order Bump: Enable Checkout shortcode',
				'product-badges'        => 'Product Badges: Enable Product Badges',
				'new-display-catalog'   => 'Product Badges: New Product Badge - Show on catalog',
				'new-display-product'   => 'Product Badges: New Product Badge - Show on product pages',
				'product-gallery'       => 'Product Gallery: Enable Product Gallery',
				'pdp-lightbox'          => 'Product Gallery: Display images in a lightbox when clicked on',
				'pdp-video-autoplay'    => 'Product Gallery: Enable video auto play',
				'pdp-thumb-arrows'      => 'Product Gallery: Enable thumbnail previous / next arrows',
				'pdp-featured-review'   => 'Product Gallery: Display featured review',
				'gallery-shortcode'     => 'Product Gallery: Enable Product Gallery shortcode',
				'size-guide'            => 'Size Guides: Enable size guides',
				'display-search-result' => 'Size Guides: Make size guide pages findable within search results',
				'size-guide-shortcode'  => 'Size Guides: Enable Size Guide shortcode',
				'sticky-atc-desktop'    => 'Sticky Add to Cart: Enable on desktop',
				'sticky-atc-mobile'     => 'Sticky Add to Cart: Enable on mobile',
				'sticky-atc-tabs'       => 'Sticky Add to Cart: Expand tabs',
				'stock-meter'           => 'Stock Meter: Show Stock Meter on the single product page',
				'stock-meter-shortcode' => 'Stock Meter: Enable Stock Meter shortcode',
				'waitlist'              => 'Waitlist: Enable waitlist for out of stock products',
				'hide-oos-variation'    => 'Waitlist: Display hidden out of stock variations on Single Product page',
				'force-email-name'      => 'Waitlist: Force from email and from name',
				'waitlist-auto-mail'    => 'Waitlist: Enable automatic emails when the item is back in stock',
				'wtl-not-stock-limit'   => 'Waitlist: Send Waitlist emails even if the number of recipients exceeds the stock amount.',
				'waitlist-admin-mail'   => 'Waitlist: Enable emails to the store owner when a customer signs up to the waitlist',
				'waitlist-user-mail'    => 'Waitlist: Enable email to the customer when they sign up to a waitlist',
				'wishlist'              => 'Wishlist: Enable wishlist functionality',
				'wishlist-shortcode'    => 'Wishlist: Enable Wishlist shortcode',
				'cache-logger'          => 'Settings: Enable CommerceKit cache rebuilding logger',
				'import-export-logger'  => 'Import / Export: Display additional details within: WooCommerce > Status > Logs',
			);

			$cgkit_options   = get_option( 'commercekit', array() );
			$option_updated  = false;
			$success_message = 'Invalid parameters';
			$this->message   = $success_message;
			if ( isset( $args[0] ) && in_array( $args[0], array( 'activate', 'deactivate' ), true ) ) {
				if ( isset( $args[1] ) && array_key_exists( $args[1], $modules ) ) {
					$option_value = 0;
					if ( 'activate' === $args[0] ) {
						$option_value = 1;
					}
					if ( isset( $modules[ $args[1] ]['id'] ) && 'none' !== $modules[ $args[1] ]['id'] ) {
						$cgkit_options[ $modules[ $args[1] ]['id'] ] = $option_value;

						$option_updated  = true;
						$success_message = $args[1] . ' module has been ' . $args[0] . 'd';
					}
				}
			} elseif ( isset( $args[0] ) && array_key_exists( $args[0], $modules ) ) {
				if ( isset( $args[1] ) && in_array( $args[1], array( 'activate', 'deactivate' ), true ) ) {
					if ( isset( $args[2] ) && array_key_exists( $args[2], $modules[ $args[0] ]['options'] ) ) {
						$option_value = 0;
						if ( 'activate' === $args[1] ) {
							$option_value = 1;
						}
						$option_key = $modules[ $args[0] ]['options'][ $args[2] ];
						if ( false !== strpos( $option_key, '][' ) ) {
							$keys  = explode( '][', $option_key );
							$total = count( $keys );
							if ( 3 === $total ) {
								$cgkit_options[ $keys[0] ][ $keys[1] ][ $keys[2] ] = $option_value;
							} elseif ( 2 === $total ) {
								$cgkit_options[ $keys[0] ][ $keys[1] ] = $option_value;
							} elseif ( 1 === $total ) {
								$cgkit_options[ $keys[0] ] = $option_value;
							}
						} else {
							$cgkit_options[ $option_key ] = $option_value;
						}
						$option_updated  = true;
						$success_message = $args[2] . ' option of ' . $args[0] . ' module has been ' . $args[1] . 'd';
					}
				}
			} elseif ( isset( $args[0] ) && 'info' === $args[0] ) {
				$info = 'List of available commands:';
				foreach ( $modules as $key => $value ) {
					if ( 'none' !== $value['id'] ) {
						$info .= "\n" . 'wp cg-commercekit [activate|deactivate] ' . $key . ( isset( $helpers[ $key ] ) ? '  /* ' . $helpers[ $key ] . ' */' : '' );
					}
					if ( isset( $value['options'] ) && count( $value['options'] ) ) {
						foreach ( $value['options'] as $key2 => $value2 ) {
							$info .= "\n" . 'wp cg-commercekit ' . $key . ' [activate|deactivate] ' . $key2 . ( isset( $helpers[ $key2 ] ) ? '  /* ' . $helpers[ $key2 ] . ' */' : '' );
						}
					}
				}
				$this->message = $info;
			}

			if ( $option_updated ) {
				update_option( 'commercekit', $cgkit_options, false );
				$this->message = $success_message;
			}

			WP_CLI::success( $this->message );
		}
	}

	WP_CLI::add_command( 'cg-commercekit', 'Commercekit_CLI_Command' );
}
