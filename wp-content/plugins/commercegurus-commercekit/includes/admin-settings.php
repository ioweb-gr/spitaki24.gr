<?php
/**
 *
 * Admin Settings
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

/**
 * Adding admin settings page
 */
function commercekit_admin_page() {
	// The icon in Base64 format.
	$icon_base64 = 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDI2LjAuMSwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCAyMDAgMjAwIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAyMDAgMjAwOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+Cgkuc3Qwe2ZpbGw6I0ZGRkZGRjt9Cjwvc3R5bGU+CjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xMDEuNSw5MC4yTDMyLjEsNTUuM3Y5MS45bDY5LjQsMzQuOWw2OS43LTM0LjlWNTUuM0wxMDEuNSw5MC4yeiBNNDUuOSw3Ni42bDQ4LjcsMjQuNXY2My4xbC00OC43LTI0LjVWNzYuNnoKCSBNMTU3LjQsMTM5LjhsLTQ5LDI0LjV2LTYzLjJsNDktMjQuNVYxMzkuOHogTTE2NS41LDUxLjRsLTE0LjIsNy4xbC00Mi45LTIyLjJ2NDEuNGwtNi4zLDMuMWgtMS4ybC0wLjYtMC4zbC01LjgtMi45VjM2LjYKCUw1My41LDU5LjNsLTE0LTdsNjEuOC0zNC4yTDE2NS41LDUxLjR6Ii8+Cjwvc3ZnPgo=';

	// The icon in the data URI scheme.
	$icon_data_uri = 'data:image/svg+xml;base64,' . $icon_base64;

	add_menu_page(
		'CommerceKit Settings',
		'CommerceKit',
		'manage_options',
		'commercekit',
		'commercekit_admin_page_html',
		$icon_data_uri,
		'99.41861'
	);
}
add_action( 'admin_menu', 'commercekit_admin_page' );

/**
 * Adding admin setting page update
 */
function commercekit_admin_page_update() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return false;
	}

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		return false;
	}

	$tab     = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'dashboard';
	$section = isset( $_REQUEST['section'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['section'] ) ) : 'list';
	if ( isset( $_POST['commercekit'] ) ) {
		$commercekit_options = get_option( 'commercekit', array() );
		if ( 'dashboard' === $tab ) {
			if ( ! isset( $_POST['commercekit']['countdown_timer'] ) ) {
				$_POST['commercekit']['countdown_timer'] = 0;

				$countdown = isset( $commercekit_options['countdown'] ) ? $commercekit_options['countdown'] : array();

				$_POST['commercekit']['countdown'] = $countdown;
				if ( isset( $countdown['product']['active'] ) && count( $countdown['product']['active'] ) > 0 ) {
					foreach ( $countdown['product']['active'] as $k => $v ) {
						$_POST['commercekit']['countdown']['product']['active'][ $k ] = 0;
					}
				}
				$_POST['commercekit']['countdown']['checkout']['active'] = 0;
			} else {
				$countdown = isset( $commercekit_options['countdown'] ) ? $commercekit_options['countdown'] : array();

				$_POST['commercekit']['countdown'] = $countdown;
				if ( isset( $countdown['product']['active'] ) && count( $countdown['product']['active'] ) > 0 ) {
					foreach ( $countdown['product']['active'] as $k => $v ) {
						$_POST['commercekit']['countdown']['product']['active'][ $k ] = isset( $countdown['product']['activeo'][ $k ] ) ? $countdown['product']['activeo'][ $k ] : 0;
					}
				}
				$_POST['commercekit']['countdown']['checkout']['active'] = 1;
			}
			if ( ! isset( $_POST['commercekit']['inventory_display'] ) ) {
				$_POST['commercekit']['inventory_display'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['pdp_triggers'] ) ) {
				$_POST['commercekit']['pdp_triggers'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['order_bump'] ) ) {
				$_POST['commercekit']['order_bump'] = 0;

				$order_bump_product = isset( $commercekit_options['order_bump_product'] ) ? $commercekit_options['order_bump_product'] : array();

				$_POST['commercekit']['order_bump_product'] = $order_bump_product;
				if ( isset( $order_bump_product['product']['active'] ) && count( $order_bump_product['product']['active'] ) > 0 ) {
					foreach ( $order_bump_product['product']['active'] as $k => $v ) {
						$_POST['commercekit']['order_bump_product']['product']['active'][ $k ] = 0;
					}
				}
			} else {
				$order_bump_product = isset( $commercekit_options['order_bump_product'] ) ? $commercekit_options['order_bump_product'] : array();

				$_POST['commercekit']['order_bump_product'] = $order_bump_product;
				if ( isset( $order_bump_product['product']['active'] ) && count( $order_bump_product['product']['active'] ) > 0 ) {
					foreach ( $order_bump_product['product']['active'] as $k => $v ) {
						$_POST['commercekit']['order_bump_product']['product']['active'][ $k ] = isset( $order_bump_product['product']['activeo'][ $k ] ) ? $order_bump_product['product']['activeo'][ $k ] : 0;
					}
				}
			}
			if ( ! isset( $_POST['commercekit']['pdp_gallery'] ) ) {
				$_POST['commercekit']['pdp_gallery'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['pdp_attributes_gallery'] ) ) {
				$_POST['commercekit']['pdp_attributes_gallery'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['attribute_swatches'] ) ) {
				$_POST['commercekit']['attribute_swatches'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['ajax_search'] ) ) {
				$_POST['commercekit']['ajax_search'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['wishlist'] ) ) {
				$_POST['commercekit']['wishlist'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['waitlist'] ) ) {
				$_POST['commercekit']['waitlist'] = 0;
			}
		}
		if ( 'inventory-bar' === $tab ) {
			if ( ! isset( $_POST['commercekit']['inventory_display'] ) ) {
				$_POST['commercekit']['inventory_display'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['widget_pos_stockmeter'] ) ) {
				$_POST['commercekit']['widget_pos_stockmeter'] = 0;
			}
		}
		if ( 'pdp-triggers' === $tab ) {
			if ( ! isset( $_POST['commercekit']['pdp_triggers'] ) ) {
				$_POST['commercekit']['pdp_triggers'] = 0;
			}
		}
		if ( 'countdown-timer' === $tab ) {
			if ( ! isset( $_POST['commercekit']['countdown_timer'] ) ) {
				$_POST['commercekit']['countdown_timer'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['countdown']['checkout']['active'] ) ) {
				$_POST['commercekit']['countdown']['checkout']['active'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['widget_pos_countdown'] ) ) {
				$_POST['commercekit']['widget_pos_countdown'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['widget_pos_countdown2'] ) ) {
				$_POST['commercekit']['widget_pos_countdown2'] = 0;
			}
		}
		if ( 'order-bump' === $tab ) {
			if ( ! isset( $_POST['commercekit']['order_bump'] ) ) {
				$_POST['commercekit']['order_bump'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['order_bump_product'] ) ) {
				$_POST['commercekit']['order_bump_product'] = array();
			}
			if ( ! isset( $_POST['commercekit']['multiple_obp'] ) ) {
				$_POST['commercekit']['multiple_obp'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['order_bump_mini'] ) ) {
				$_POST['commercekit']['order_bump_mini'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['order_bump_minicart'] ) ) {
				$_POST['commercekit']['order_bump_minicart'] = array();
			}
			if ( ! isset( $_POST['commercekit']['multiple_obp_mini'] ) ) {
				$_POST['commercekit']['multiple_obp_mini'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['widget_pos_obp'] ) ) {
				$_POST['commercekit']['widget_pos_obp'] = 0;
			}
		}
		if ( 'pdp-gallery' === $tab ) {
			if ( ! isset( $_POST['commercekit']['pdp_gallery'] ) ) {
				$_POST['commercekit']['pdp_gallery'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['pdp_lightbox'] ) ) {
				$_POST['commercekit']['pdp_lightbox'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['pdp_lightbox_cap'] ) ) {
				$_POST['commercekit']['pdp_lightbox_cap'] = 0;
			}
			$pdp_thumbnails = isset( $_POST['commercekit']['pdp_thumbnails'] ) ? sanitize_text_field( wp_unslash( (int) $_POST['commercekit']['pdp_thumbnails'] ) ) : 4;
			if ( $pdp_thumbnails < 3 || $pdp_thumbnails > 8 ) {
				$_POST['commercekit']['pdp_thumbnails'] = 4;
			}
			if ( ! isset( $_POST['commercekit']['pdp_video_autoplay'] ) ) {
				$_POST['commercekit']['pdp_video_autoplay'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['pdp_mobile_optimized'] ) ) {
				$_POST['commercekit']['pdp_mobile_optimized'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['pdp_thumb_arrows'] ) ) {
				$_POST['commercekit']['pdp_thumb_arrows'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['pdp_featured_review'] ) ) {
				$_POST['commercekit']['pdp_featured_review'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['widget_pos_pdp_gallery'] ) ) {
				$_POST['commercekit']['widget_pos_pdp_gallery'] = 0;
			}
		}
		if ( 'pdp-attributes-gallery' === $tab ) {
			if ( ! isset( $_POST['commercekit']['pdp_attributes_gallery'] ) ) {
				$_POST['commercekit']['pdp_attributes_gallery'] = 0;
			}
		}
		if ( 'attribute-swatches' === $tab ) {
			if ( ! isset( $_POST['commercekit']['attribute_swatches'] ) ) {
				$_POST['commercekit']['attribute_swatches'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['attribute_swatches_pdp'] ) ) {
				$_POST['commercekit']['attribute_swatches_pdp'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['attribute_swatches_plp'] ) ) {
				$_POST['commercekit']['attribute_swatches_plp'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['as_activate_atc'] ) ) {
				$_POST['commercekit']['as_activate_atc'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['as_enable_tooltips'] ) ) {
				$_POST['commercekit']['as_enable_tooltips'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['as_disable_facade'] ) ) {
				$_POST['commercekit']['as_disable_facade'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['as_disable_pdp'] ) ) {
				$_POST['commercekit']['as_disable_pdp'] = 0;
			}
		}
		if ( 'sticky-atc-bar' === $tab ) {
			if ( ! isset( $_POST['commercekit']['sticky_atc_desktop'] ) ) {
				$_POST['commercekit']['sticky_atc_desktop'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['sticky_atc_mobile'] ) ) {
				$_POST['commercekit']['sticky_atc_mobile'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['sticky_atc_tabs'] ) ) {
				$_POST['commercekit']['sticky_atc_tabs'] = 0;
			}
			wc_clear_template_cache();
		}
		if ( 'free-shipping-notification' === $tab ) {
			if ( ! isset( $_POST['commercekit']['fsn_cart_page'] ) ) {
				$_POST['commercekit']['fsn_cart_page'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['fsn_mini_cart'] ) ) {
				$_POST['commercekit']['fsn_mini_cart'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['fsn_before_ship'] ) ) {
				$_POST['commercekit']['fsn_before_ship'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['fsn_exclude_class'] ) ) {
				$_POST['commercekit']['fsn_exclude_class'] = array();
			}
			if ( ! isset( $_POST['commercekit']['widget_pos_fsn'] ) ) {
				$_POST['commercekit']['widget_pos_fsn'] = 0;
			}
		}
		if ( 'size-guide' === $tab ) {
			if ( ! isset( $_POST['commercekit']['size_guide'] ) ) {
				$_POST['commercekit']['size_guide'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['size_guide_search'] ) ) {
				$_POST['commercekit']['size_guide_search'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['widget_pos_sizeguide'] ) ) {
				$_POST['commercekit']['widget_pos_sizeguide'] = 0;
			}
		}
		if ( 'badge' === $tab ) {
			if ( ! isset( $_POST['commercekit']['store_badge'] ) ) {
				$_POST['commercekit']['store_badge'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['badge']['new']['catalog'] ) ) {
				$_POST['commercekit']['badge']['new']['catalog'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['badge']['new']['product'] ) ) {
				$_POST['commercekit']['badge']['new']['product'] = 0;
			}
		}
		if ( 'ajax-search' === $tab ) {
			if ( ! isset( $_POST['commercekit']['ajax_search'] ) ) {
				$_POST['commercekit']['ajax_search'] = 0;
				if ( isset( $commercekit_options['generating_ajs'] ) && 1 === (int) $commercekit_options['generating_ajs'] ) {
					$_POST['commercekit']['interrupt_ajs']  = 1;
					$_POST['commercekit']['generating_ajs'] = 0;

					$data = array(
						'interrupt_ajs' => 1,
						'cancelled_ajs' => 0,
					);
					commercekit_ajs_temporary_options( 'SET', $data );

					$as_store = ActionScheduler::store();
					$as_store->cancel_actions_by_hook( 'commercegurus_ajs_run_wc_product_index' );
					commercegurus_ajs_log( 'The indexing process has been interrupted.' );
				}
			}
			if ( ! isset( $_POST['commercekit']['ajs_tabbed'] ) ) {
				$_POST['commercekit']['ajs_tabbed'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['ajs_pre_tab'] ) ) {
				$_POST['commercekit']['ajs_pre_tab'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['ajs_orderby_oos'] ) ) {
				$_POST['commercekit']['ajs_orderby_oos'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['ajs_hidevar'] ) ) {
				$_POST['commercekit']['ajs_hidevar'] = 0;
			}
			$ajs_product_count = isset( $_POST['commercekit']['ajs_product_count'] ) ? (int) $_POST['commercekit']['ajs_product_count'] : 0;
			if ( $ajs_product_count > 5 || $ajs_product_count < 1 ) {
				$_POST['commercekit']['ajs_product_count'] = commercekit_get_default_settings( 'ajs_product_count' );
			}
			if ( ! isset( $_POST['commercekit']['ajs_index_logger'] ) ) {
				$_POST['commercekit']['ajs_index_logger'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['ajs_other_results'] ) ) {
				$_POST['commercekit']['ajs_other_results'] = 0;
			}
			$ajs_other_count = isset( $_POST['commercekit']['ajs_other_count'] ) ? (int) $_POST['commercekit']['ajs_other_count'] : 0;
			if ( $ajs_other_count > 5 || $ajs_other_count < 1 ) {
				$_POST['commercekit']['ajs_other_count'] = commercekit_get_default_settings( 'ajs_other_count' );
			}
			if ( ! isset( $_POST['commercekit']['ajs_fast_search'] ) ) {
				$_POST['commercekit']['ajs_fast_search'] = 0;
			}
		}
		if ( 'waitlist' === $tab ) {
			$bulk_action = isset( $_POST['bulk_action'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_action'] ) ) : '';
			$bulk_apply  = isset( $_POST['bulk_apply'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_apply'] ) ) : 0;
			if ( 1 === (int) $bulk_apply ) {
				return commercekit_admin_waitlist_bulk_action( $bulk_action );
			}
			if ( 'settings' === $section ) {
				if ( ! isset( $_POST['commercekit']['waitlist'] ) ) {
					$_POST['commercekit']['waitlist'] = 0;
				}
				if ( ! isset( $_POST['commercekit']['wtl_show_oos'] ) ) {
					$_POST['commercekit']['wtl_show_oos'] = 0;
				}
			} elseif ( 'emails' === $section ) {
				if ( ! isset( $_POST['commercekit']['wtl_force_email_name'] ) ) {
					$_POST['commercekit']['wtl_force_email_name'] = 0;
				}
				if ( ! isset( $_POST['commercekit']['waitlist_auto_mail'] ) ) {
					$_POST['commercekit']['waitlist_auto_mail'] = 0;
				}
				if ( ! isset( $_POST['commercekit']['wtl_not_stock_limit'] ) ) {
					$_POST['commercekit']['wtl_not_stock_limit'] = 0;
				}
				if ( ! isset( $_POST['commercekit']['waitlist_admin_mail'] ) ) {
					$_POST['commercekit']['waitlist_admin_mail'] = 0;
				}
				if ( ! isset( $_POST['commercekit']['waitlist_user_mail'] ) ) {
					$_POST['commercekit']['waitlist_user_mail'] = 0;
				}
			} elseif ( 'integrations' === $section ) {
				if ( ! isset( $_POST['commercekit']['wtl_esp_klaviyo'] ) ) {
					$_POST['commercekit']['wtl_esp_klaviyo'] = 0;
				}
				if ( ! isset( $_POST['commercekit']['esp_klaviyo']['main_call'] ) ) {
					$_POST['commercekit']['esp_klaviyo']['main_call'] = 0;
				}
				if ( ! isset( $_POST['commercekit']['esp_klaviyo']['oos_message'] ) ) {
					$_POST['commercekit']['esp_klaviyo']['oos_message'] = 0;
				}
				if ( ! isset( $_POST['commercekit']['esp_klaviyo']['stock_message'] ) ) {
					$_POST['commercekit']['esp_klaviyo']['stock_message'] = 0;
				}
				if ( ! isset( $_POST['commercekit']['esp_klaviyo']['show_form'] ) ) {
					$_POST['commercekit']['esp_klaviyo']['show_form'] = 0;
				}
				if ( ! isset( $_POST['commercekit']['esp_klaviyo']['force_display'] ) ) {
					$_POST['commercekit']['esp_klaviyo']['force_display'] = 0;
				}
			} else {
				return false;
			}
		}
		if ( 'wishlist' === $tab ) {
			if ( ! isset( $_POST['commercekit']['wishlist'] ) ) {
				$_POST['commercekit']['wishlist'] = 0;
			}
			if ( ! isset( $_POST['commercekit']['widget_pos_wishlist'] ) ) {
				$_POST['commercekit']['widget_pos_wishlist'] = 0;
			}
		}
		if ( 'settings' === $tab ) {
			if ( ! isset( $_POST['commercekit']['as_logger'] ) ) {
				$_POST['commercekit']['as_logger'] = 0;
			}
			if ( isset( $_POST['commercekit']['clear_as_cache'] ) && 1 === (int) $_POST['commercekit']['clear_as_cache'] ) {
				return commercekit_as_clear_all_cache();
			}
		}
		if ( 'exporter' === $tab ) {
			if ( ! isset( $_POST['commercekit']['export_import_logger'] ) ) {
				$_POST['commercekit']['export_import_logger'] = 0;
			}
		}
		if ( 'support' === $tab ) {
			$fname    = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
			$email    = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
			$url      = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
			$title    = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			$question = isset( $_POST['question'] ) ? sanitize_textarea_field( wp_unslash( $_POST['question'] ) ) : '';
			$width    = isset( $_POST['screen_width'] ) ? sanitize_text_field( wp_unslash( $_POST['screen_width'] ) ) : '';
			$height   = isset( $_POST['screen_height'] ) ? sanitize_text_field( wp_unslash( $_POST['screen_height'] ) ) : '';
			$to_mail  = 'support@commercegurus.com';
			if ( ! empty( $email ) && ! empty( $url ) && ! empty( $title ) && ! empty( $question ) ) {
				global $wp_version, $woocommerce;
				$version       = explode( '.', phpversion() );
				$theme         = wp_get_theme();
				$template      = $theme->get_template();
				$email_headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $email, 'Reply-To: ' . $email );
				$email_subject = $title;
				$email_body    = '
<p>' . nl2br( $question ) . '</p>
<p>&nbsp;</p>
<hr/>
<p>First name: <br />' . $fname . '</p>
<p>URL: <br />' . $url . '</p>
<p>Subscription number: <br />#' . commercekit_get_subscription_number() . '</p>
<p>Commercekit version: <br />' . CGKIT_CSS_JS_VER . '</p>
<p>Active theme: <br />' . $theme->get( 'Name' ) . ' (Version: ' . $theme->get( 'Version' ) . ')</p>
<p>WordPress version: <br />' . esc_html( $wp_version ) . '</p>
<p>WooCommerce version: <br />' . ( isset( $woocommerce->version ) ? esc_html( $woocommerce->version ) : '' ) . '</p>
<p>Using a child theme?<br />' . ( isset( $template ) && false !== stripos( $template, '-child' ) ? 'Yes' : 'No' ) . '</p>
<p>PHP version: <br />' . $version[0] . '.' . $version[1] . '.' . $version[2] . '</p>
<p>OS Platform: <br />' . commercekit_admin_get_os() . '</p>
<p>Browser: <br />' . commercekit_admin_get_browser() . '</p>
<p>Screen Width: <br />' . $width . '</p>
<p>Screen Height: <br />' . $height . '</p>
<p>Site URL: <br />' . home_url( '/' ) . '</p>';

				$success = wp_mail( $to_mail, $email_subject, $email_body, $email_headers );
				if ( $success ) {
					return esc_html__( 'Your email has been sent to our support team.', 'commercegurus-commercekit' );
				} else {
					return esc_html__( 'Error on sending email to support team.', 'commercegurus-commercekit' );
				}
			} else {
				return false;
			}
		}

		$commercekit = map_deep( wp_unslash( $_POST['commercekit'] ), 'sanitize_textarea_field' );
		foreach ( $commercekit as $key => $value ) {
			$commercekit_options[ $key ] = $value;
		}
		$editor_keys = array( 'wtl_auto_content', 'wtl_auto_footer', 'wtl_admin_content', 'wtl_user_content', 'wtl_intro', 'wtl_consent_text', 'wtl_success_text', 'fsn_initial_text', 'fsn_progress_text', 'fsn_success_text' );
		foreach ( $editor_keys as $ekey ) {
			if ( isset( $_POST['commercekit'][ $ekey ] ) ) {
				$commercekit_options[ $ekey ] = wp_kses_post( wp_unslash( $_POST['commercekit'][ $ekey ] ) );
			}
		}
		if ( isset( $_POST['commercekit']['size_guide_icon_html'] ) ) {
			$commercekit_options['size_guide_icon_html'] = wp_unslash( $_POST['commercekit']['size_guide_icon_html'] ); // phpcs:ignore
		}

		update_option( 'commercekit', $commercekit_options, false );
	}

	return true;
}

/**
 * Adding admin setting page HTML
 */
function commercekit_admin_page_html() {
	global $commerce_gurus_commercekit, $wp_scripts;

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! empty( $commercekit_nonce ) && ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery-ui-style' );
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_media();

	$success = commercekit_admin_page_update();
	$notice  = '';
	$tab     = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'dashboard'; // phpcs:ignore
	$section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : 'list'; // phpcs:ignore

	if ( true === $success ) {
		$notice = esc_html__( 'Settings have been saved.', 'commercegurus-commercekit' );
	} elseif ( false !== $success ) {
		$notice = $success;
	} else {
		$notice = '';
	}
	$commercekit_options = get_option( 'commercekit', array() );
	$domain_connected    = commercekit_is_domain_connected();
	$environment_warning = $commerce_gurus_commercekit->get_environment_warning();
	?>
	<div class="wrap">
		<?php if ( ! empty( $notice ) && 'support' !== $tab && 'exporter' !== $tab ) { ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php } ?>
		<?php if ( ! $environment_warning ) { ?>
		<form action="" method="post" id="commercekit-form" enctype="multipart/form-data" class="form-horizontal">
		<div id="ajax-loading-mask"><div class="ajax-loading"></div></div>
		<h1 style="display: none;">&nbsp;</h1>
		<div class="commercekit-admin-header">
			<div class="commercekit-logo--wrapper">
				<a href="?page=commercekit"><img src="<?php echo esc_url( CKIT_URI ); ?>assets/images/logo.png" alt="CommerceKit Logo" class="commercekit-logo" /></a>

				<div class="commercekit-admin-header--links">
					<nav>
						<div><?php esc_html_e( 'Version', 'commercegurus-commercekit' ); ?> <?php echo esc_attr( CGKIT_CSS_JS_VER ); ?></div>
						<a href="https://www.commercegurus.com/shoptimizer-changelog/" target="_blank"><?php esc_html_e( 'Changelog', 'commercegurus-commercekit' ); ?></a>
						<a href="https://www.commercegurus.com/docs/commercekit/" target="_blank"><?php esc_html_e( 'Documentation', 'commercegurus-commercekit' ); ?></a>
						<a href="?page=commercekit&amp;tab=settings" class="button-primary"><?php esc_html_e( 'Clear CommerceKit cache', 'commercegurus-commercekit' ); ?></a>
					</nav>
				</div>
			</div>
			<p class="intro"><?php esc_html_e( 'Conversion-boosting, performance-focused eCommerce features which work together seamlessly. From', 'commercegurus-commercekit' ); ?> <a href="https://www.commercegurus.com/commercekit/" target="_blank"><?php esc_html_e( 'CommerceGurus', 'commercegurus-commercekit' ); ?></a>.</p>
		</div>
		<div class="ckit-admin-wrapper">
		<nav class="nav-tab-wrapper" id="settings-tabs">
			<a href="?page=commercekit" data-tab="dashboard" class="nav-item <?php echo 'dashboard' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Dashboard', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=ajax-search" data-tab="ajax-search" class="nav-item <?php echo 'ajax-search' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Ajax Search', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=pdp-attributes-gallery" data-tab="pdp-attributes-gallery" class="nav-item <?php echo 'pdp-attributes-gallery' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Attributes Gallery', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=attribute-swatches" data-tab="attribute-swatches" class="nav-item <?php echo 'attribute-swatches' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Attribute Swatches', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=countdown-timer" data-tab="countdown-timer" class="nav-item <?php echo 'countdown-timer' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Countdowns', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=free-shipping-notification" data-tab="free-shipping-notification" class="nav-item <?php echo 'free-shipping-notification' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Free Shipping Notification', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=order-bump" data-tab="order-bump" class="nav-item <?php echo 'order-bump' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Order Bump', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=badge" data-tab="badge" class="nav-item <?php echo 'badge' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Product Badges', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=pdp-gallery" data-tab="pdp-gallery" class="nav-item <?php echo 'pdp-gallery' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Product Gallery', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=size-guide" data-tab="size-guide" class="nav-item <?php echo 'size-guide' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Size Guides', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=sticky-atc-bar" data-tab="sticky-atc-bar" class="nav-item <?php echo 'sticky-atc-bar' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Sticky Add to Cart', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=inventory-bar" data-tab="inventory-bar" class="nav-item <?php echo 'inventory-bar' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Stock Meter', 'commercegurus-commercekit' ); ?></a>
			<a style="display: none;" href="?page=commercekit&tab=pdp-triggers" data-tab="pdp-triggers" class="nav-item <?php echo 'pdp-triggers' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'PDP Triggers', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=waitlist" data-tab="waitlist" class="nav-item <?php echo 'waitlist' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Waitlist', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=wishlist" data-tab="wishlist" class="nav-item <?php echo 'wishlist' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Wishlist', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=settings" data-tab="settings" class="nav-item <?php echo 'settings' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Settings', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=exporter" data-tab="exporter" class="nav-item <?php echo 'exporter' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Import / Export', 'commercegurus-commercekit' ); ?></a>
			<a href="?page=commercekit&tab=support" data-tab="waitlist" class="nav-item-support nav-item <?php echo 'support' === $tab ? 'nav-item-active' : ''; ?>"><?php esc_html_e( 'Support', 'commercegurus-commercekit' ); ?></a>
		</nav>

		<div class="tab-content">
			<?php
			switch ( $tab ) {
				case 'dashboard':
					require_once dirname( __FILE__ ) . '/admin-dashboard.php';
					break;
				case 'countdown-timer':
					require_once dirname( __FILE__ ) . '/admin-countdown-timer.php';
					break;
				case 'inventory-bar':
					require_once dirname( __FILE__ ) . '/admin-inventory-bar.php';
					break;
				case 'pdp-triggers':
					require_once dirname( __FILE__ ) . '/admin-pdp-triggers.php';
					break;
				case 'order-bump':
					require_once dirname( __FILE__ ) . '/admin-order-bump.php';
					break;
				case 'pdp-gallery':
					require_once dirname( __FILE__ ) . '/admin-pdp-gallery.php';
					break;
				case 'pdp-attributes-gallery':
					require_once dirname( __FILE__ ) . '/admin-pdp-attributes-gallery.php';
					break;
				case 'attribute-swatches':
					require_once dirname( __FILE__ ) . '/admin-attribute-swatches-settings.php';
					break;
				case 'sticky-atc-bar':
					require_once dirname( __FILE__ ) . '/admin-sticky-atc-bar.php';
					break;
				case 'free-shipping-notification':
					require_once dirname( __FILE__ ) . '/admin-free-shipping-notification.php';
					break;
				case 'size-guide':
					require_once dirname( __FILE__ ) . '/admin-size-guide.php';
					break;
				case 'badge':
					require_once dirname( __FILE__ ) . '/admin-badge.php';
					break;
				case 'ajax-search':
					require_once dirname( __FILE__ ) . '/admin-ajax-search.php';
					break;
				case 'wishlist':
					require_once dirname( __FILE__ ) . '/admin-wishlist.php';
					break;
				case 'waitlist':
					require_once dirname( __FILE__ ) . '/admin-waitlist.php';
					break;
				case 'settings':
					require_once dirname( __FILE__ ) . '/admin-modules-settings.php';
					break;
				case 'exporter':
					require_once dirname( __FILE__ ) . '/admin-import-export.php';
					break;
				case 'support':
					require_once dirname( __FILE__ ) . '/admin-support.php';
					break;
			}
			?>
		<div class="submit-button">
			<input type="hidden" name="commercekit[settings]" value="1" />
			<?php wp_nonce_field( 'commercekit_settings', 'commercekit_nonce' ); ?>
			<?php if ( 'dashboard' !== $tab && 'support' !== $tab && ! ( 'waitlist' === $tab && ( 'list' === $section || 'products' === $section || 'statistics' === $section ) ) && ! ( 'ajax-search' === $tab && 'reports' === $section ) && ! ( 'wishlist' === $tab && ( 'reports' === $section || 'statistics' === $section ) ) && 'settings' !== $tab && 'exporter' !== $tab ) { ?>
				<input type="submit" name="btn-submit" id="btn-submit" class="button button-primary" value="Save Changes">
			<?php } ?>
		</div>

		</div>

		</div><!--/ckit-admin-wrap -->
		</form>
		<?php } ?>
	</div>
	<?php
}

/**
 * Get products or categories IDs
 */
function commercekit_get_pcids() {
	global $cgkit_asku_search;
	$return            = array();
	$commercekit_nonce = isset( $_GET['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json( $return );
	}

	$type              = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : 'products';
	$tab               = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
	$mode              = isset( $_GET['mode'] ) ? sanitize_text_field( wp_unslash( $_GET['mode'] ) ) : '';
	$cgkit_asku_search = false;

	if ( 'products' === $type ) {
		if ( 'order-bump' === $tab && 'full' === $mode ) {
			$post_types        = array( 'product', 'product_variation' );
			$cgkit_asku_search = true;
		} else {
			$post_types = array( 'product' );
		}
		$query = ! empty( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$args  = array(
			's'              => $query,
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'post_type'      => $post_types,
		);
		if ( is_numeric( $query ) ) {
			unset( $args['s'] );
			$args['post__in'] = array( $query );
		}
		$all_types = false;
		if ( 'badge' === $tab ) {
			$all_types = true;
		}

		$search_results = new WP_Query( $args );

		if ( $search_results->have_posts() ) {
			while ( $search_results->have_posts() ) {
				$search_results->the_post();
				if ( 'product' === $search_results->post->post_type ) {
					$product = wc_get_product( $search_results->post->ID );
					if ( ! $product || ( ! $product->is_type( array( 'simple', 'variable', 'subscription', 'variable-subscription' ) ) && ! $all_types ) ) {
						continue;
					}
				}
				$title    = commercekit_limit_title( $search_results->post->post_title );
				$title    = '#' . $search_results->post->ID . ' - ' . $title;
				$return[] = array( $search_results->post->ID, $title );
			}
		}
	} elseif ( 'pages' === $type ) {
		$query = ! empty( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$args  = array(
			's'              => $query,
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'post_type'      => 'page',
		);
		if ( is_numeric( $query ) ) {
			unset( $args['s'] );
			$args['post__in'] = array( $query );
		}

		$search_results = new WP_Query( $args );

		if ( $search_results->have_posts() ) {
			while ( $search_results->have_posts() ) {
				$search_results->the_post();
				$title    = ( mb_strlen( $search_results->post->post_title ) > 80 ) ? mb_substr( $search_results->post->post_title, 0, 79 ) . '...' : $search_results->post->post_title;
				$title    = '#' . $search_results->post->ID . ' - ' . $title;
				$return[] = array( $search_results->post->ID, $title );
			}
		}
	} elseif ( 'tags' === $type ) {
		$query = ! empty( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$args  = array(
			'name__like' => $query,
			'hide_empty' => true,
			'number'     => 20,
		);
		if ( is_numeric( $query ) ) {
			$terms = array( get_term( $query, 'product_tag' ) );
		} else {
			$terms = get_terms( 'product_tag', $args );
		}
		if ( is_array( $terms ) && count( $terms ) > 0 ) {
			foreach ( $terms as $term ) {
				if ( isset( $term->name ) ) {
					$term->name = '#' . $term->term_id . ' - ' . $term->name;
					$return[]   = array( $term->term_id, $term->name );
				}
			}
		}
	} elseif ( 'brands' === $type && taxonomy_exists( 'product_brand' ) ) {
		$query = ! empty( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$args  = array(
			'name__like' => $query,
			'hide_empty' => true,
			'number'     => 20,
		);
		if ( is_numeric( $query ) ) {
			$terms = array( get_term( $query, 'product_brand' ) );
		} else {
			$terms = get_terms( 'product_brand', $args );
		}
		if ( is_array( $terms ) && count( $terms ) > 0 ) {
			foreach ( $terms as $term ) {
				if ( isset( $term->name ) ) {
					$term->name = '#' . $term->term_id . ' - ' . $term->name;
					$return[]   = array( $term->term_id, $term->name );
				}
			}
		}
	} else {
		$query = ! empty( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		$args  = array(
			'name__like' => $query,
			'hide_empty' => true,
			'number'     => 20,
		);
		if ( is_numeric( $query ) ) {
			$terms = array( get_term( $query, 'product_cat' ) );
		} else {
			$terms = get_terms( 'product_cat', $args );
		}
		if ( is_array( $terms ) && count( $terms ) > 0 ) {
			foreach ( $terms as $term ) {
				if ( isset( $term->name ) ) {
					$term->name = '#' . $term->term_id . ' - ' . $term->name;
					$return[]   = array( $term->term_id, $term->name );
				}
			}
		}
	}

	wp_send_json( $return );
}

add_action( 'wp_ajax_commercekit_get_pcids', 'commercekit_get_pcids' );

/**
 * Admin ajax save settings
 */
function commercekit_ajax_save_settings() {
	$ajax            = array();
	$ajax['status']  = 0;
	$ajax['message'] = esc_html__( 'Error on saving settings.', 'commercegurus-commercekit' );

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json( $ajax );
	}

	$return = commercekit_admin_page_update();
	if ( $return ) {
		$ajax['status']  = 1;
		$ajax['message'] = esc_html__( 'Settings have been saved.', 'commercegurus-commercekit' );
	}

	wp_send_json( $ajax );
}

add_action( 'wp_ajax_commercekit_save_settings', 'commercekit_ajax_save_settings' );

/**
 * Admin waitlist bulk action
 *
 * @param  string $bulk_action of waitlist.
 * @return string
 */
function commercekit_admin_waitlist_bulk_action( $bulk_action ) {
	global $wpdb;

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		return false;
	}

	if ( 'export' === $bulk_action ) {
		$rows = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'commercekit_waitlist ORDER BY created', ARRAY_A ); // db call ok; no-cache ok.
		if ( is_array( $rows ) && count( $rows ) ) {
			return false;
		} else {
			return esc_html__( 'There are no waitlists to export.', 'commercegurus-commercekit' );
		}
	} elseif ( 'delete' === $bulk_action ) {
		$wtl_ids = isset( $_POST['wtl_ids'] ) ? map_deep( wp_unslash( $_POST['wtl_ids'] ), 'sanitize_text_field' ) : array();
		if ( is_array( $wtl_ids ) && count( $wtl_ids ) ) {
			foreach ( $wtl_ids as $wtl_id ) {
				$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'commercekit_waitlist WHERE id IN (%s)', $wtl_id ) ); // db call ok; no-cache ok.
			}
			return esc_html__( 'Selected waitlist has been deleted.', 'commercegurus-commercekit' );
		} else {
			return esc_html__( 'Please select at least one waitlist to delete.', 'commercegurus-commercekit' );
		}
	} elseif ( 'delete-product' === $bulk_action ) {
		$wtl_ids = isset( $_POST['wtl_ids'] ) ? map_deep( wp_unslash( $_POST['wtl_ids'] ), 'sanitize_text_field' ) : array();
		if ( is_array( $wtl_ids ) && count( $wtl_ids ) ) {
			foreach ( $wtl_ids as $wtl_id ) {
				$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'commercekit_waitlist WHERE product_id IN (%s)', $wtl_id ) ); // db call ok; no-cache ok.
			}
			return esc_html__( 'Selected product waitlist has been deleted.', 'commercegurus-commercekit' );
		} else {
			return esc_html__( 'Please select at least one product waitlist to delete.', 'commercegurus-commercekit' );
		}
	}

	return false;
}

/**
 *  Admin waitlist bulk export
 */
function commercekit_admin_waitlist_export() {
	global $wpdb;

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		return false;
	}

	$tab         = isset( $_POST['tab'] ) ? sanitize_text_field( wp_unslash( $_POST['tab'] ) ) : '';
	$bulk_action = isset( $_POST['bulk_action'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_action'] ) ) : '';
	$bulk_apply  = isset( $_POST['bulk_apply'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_apply'] ) ) : 0;
	if ( 'waitlist' === $tab && 'export' === $bulk_action && 1 === (int) $bulk_apply ) {
		$rows = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'commercekit_waitlist ORDER BY created', ARRAY_A ); // db call ok; no-cache ok.
		if ( is_array( $rows ) && count( $rows ) ) {
			$output = fopen( 'php://output', 'w' );
			header( 'Content-Type: text/csv; charset=UTF-8' );
			header( 'Content-Transfer-Encoding: Binary' );
			header( 'Content-Disposition: attachment; filename="Waitlist.csv"' );
			$headers = array( 'Email', 'Product', 'Date added', 'Mail sent', 'Product ID', 'Product SKU' );
			$yes_lbl = esc_html__( 'Yes', 'commercegurus-commercekit' );
			$no_lbl  = esc_html__( 'No', 'commercegurus-commercekit' );
			fputcsv( $output, $headers );
			if ( count( $rows ) ) {
				foreach ( $rows as $row ) {
					$tmp   = array();
					$tmp[] = $row['email'];
					$tmp[] = get_the_title( $row['product_id'] );
					$tmp[] = gmdate( 'j F Y', $row['created'] );
					$tmp[] = isset( $row['mail_sent'] ) && 1 === (int) $row['mail_sent'] ? $yes_lbl : $no_lbl;
					$tmp[] = $row['product_id'];
					$tmp[] = get_post_meta( $row['product_id'], '_sku', true );
					fputcsv( $output, $tmp );
				}
			}
			fclose( $output ); // phpcs:ignore
			exit();
		}
	}
}
add_action( 'admin_init', 'commercekit_admin_waitlist_export' );

/**
 *  Get browser OS
 */
function commercekit_admin_get_os() {
	$user_agent  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	$os_platform = 'Unknown OS Platform';

	$os_array = array(
		'/windows nt 10/i'      => 'Windows 10',
		'/windows nt 6.3/i'     => 'Windows 8.1',
		'/windows nt 6.2/i'     => 'Windows 8',
		'/windows nt 6.1/i'     => 'Windows 7',
		'/windows nt 6.0/i'     => 'Windows Vista',
		'/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
		'/windows nt 5.1/i'     => 'Windows XP',
		'/windows xp/i'         => 'Windows XP',
		'/windows nt 5.0/i'     => 'Windows 2000',
		'/windows me/i'         => 'Windows ME',
		'/win98/i'              => 'Windows 98',
		'/win95/i'              => 'Windows 95',
		'/win16/i'              => 'Windows 3.11',
		'/macintosh|mac os x/i' => 'Mac OS X',
		'/mac_powerpc/i'        => 'Mac OS 9',
		'/linux/i'              => 'Linux',
		'/ubuntu/i'             => 'Ubuntu',
		'/iphone/i'             => 'iPhone',
		'/ipod/i'               => 'iPod',
		'/ipad/i'               => 'iPad',
		'/android/i'            => 'Android',
		'/blackberry/i'         => 'BlackBerry',
		'/webos/i'              => 'Mobile',
	);

	foreach ( $os_array as $index => $value ) {
		if ( preg_match( $index, $user_agent ) ) {
			$os_platform = $value;
		}
	}
	return $os_platform;
}

/**
 *  Get browser name
 */
function commercekit_admin_get_browser() {
	$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	$browser    = 'Unknown Browser';

	$browsers = array(
		'/msie/i'      => 'Internet Explorer',
		'/firefox/i'   => 'Firefox',
		'/safari/i'    => 'Safari',
		'/chrome/i'    => 'Chrome',
		'/edge/i'      => 'Edge',
		'/opera/i'     => 'Opera',
		'/netscape/i'  => 'Netscape',
		'/maxthon/i'   => 'Maxthon',
		'/konqueror/i' => 'Konqueror',
		'/mobile/i'    => 'Handheld Browser',
	);

	foreach ( $browsers as $index => $value ) {
		if ( preg_match( $index, $user_agent ) ) {
			$browser = $value;
		}
	}

	return $browser;
}

/**
 *  Get domain connected status
 */
function commercekit_is_domain_connected() {
	if ( ! class_exists( 'CG_Helper' ) ) {
		return false;
	}

	$whitelisted = CG_Helper::maybe_whitelisted();
	if ( isset( $whitelisted['domain_auth'] ) && '1' === $whitelisted['domain_auth'] ) {
		return true;
	}

	if ( ! isset( $subscriptions ) ) {
		$subscriptions = CG_Helper::get_subscriptions();
	}

	if ( empty( $subscriptions ) ) {
		return false;
	}

	$theme    = wp_get_theme();
	$template = $theme->get_template();
	if ( false === stripos( $template, 'shoptimizer' ) ) {
		return false;
	}

	$theme  = wp_get_theme( 'shoptimizer' );
	$header = $theme->get( 'CGMeta' );

	if ( empty( $header ) ) {
		return false;
	}

	list( $product_id, $file_id ) = explode( ':', $header );
	if ( empty( $product_id ) || empty( $file_id ) ) {
		return false;
	}

	foreach ( $subscriptions as $subscription ) {
		if ( (string) $subscription['product_id'] !== (string) $product_id ) {
			continue;
		}

		if ( 'active' === $subscription['sub_status'] || 'pending-cancel' === $subscription['sub_status'] ) {
			return true;
		}
	}

	return false;
}

/**
 *  Get subscription number
 */
function commercekit_get_subscription_number() {
	if ( ! class_exists( 'CG_Helper' ) ) {
		return 0;
	}

	if ( ! isset( $subscriptions ) ) {
		$subscriptions = CG_Helper::get_subscriptions();
	}

	if ( empty( $subscriptions ) ) {
		return 0;
	}

	$theme  = wp_get_theme( 'shoptimizer' );
	$header = $theme->get( 'CGMeta' );

	if ( empty( $header ) ) {
		return 0;
	}

	list( $product_id, $file_id ) = explode( ':', $header );
	if ( empty( $product_id ) || empty( $file_id ) ) {
		return 0;
	}

	foreach ( $subscriptions as $subscription ) {
		if ( (string) $subscription['product_id'] !== (string) $product_id ) {
			continue;
		}

		if ( 'active' === $subscription['sub_status'] || 'pending-cancel' === $subscription['sub_status'] ) {
			return $subscription['sub_id'];
		}
	}

	return 0;
}

/**
 * Get limit title
 *
 * @param  string $title_text of limit output.
 *
 * @return  string
 */
function commercekit_limit_title( $title_text ) {
	$title_text = ( mb_strlen( $title_text ) > 80 ) ? mb_substr( $title_text, 0, 79 ) . '...' : $title_text;
	return $title_text;
}

/**
 *  Make multilingual strings
 */
function commercekit_make_multilingual_strings() {
	global $wpdb;
	$options = get_option( 'commercekit', array() );
	$keys    = array( 'ajs_placeholder', 'ajs_other_text', 'ajs_no_text', 'ajs_all_text', 'ajs_no_other_text', 'ajs_other_all_text', 'as_quickadd_txt', 'as_more_opt_txt', 'sticky_atc_label', 'inventory_text', 'inventory_text_31', 'inventory_text_100', 'wtl_intro', 'wtl_email_text', 'wtl_button_text', 'wtl_consent_text', 'wtl_success_text', 'wtl_readmore_text', 'wtl_auto_subject', 'wtl_auto_content', 'wtl_auto_footer', 'wtl_admin_subject', 'wtl_admin_content', 'wtl_user_subject', 'wtl_user_content', 'wsl_adtext', 'wsl_pdtext', 'wsl_brtext', 'fsn_initial_text', 'fsn_progress_text', 'fsn_success_text', 'size_guide_label', 'multiple_obp_label', 'multiple_obp_mini_lbl' );

	$editor_keys = array( 'wtl_auto_content', 'wtl_auto_footer', 'wtl_admin_content', 'wtl_user_content', 'wtl_intro', 'wtl_consent_text', 'wtl_success_text', 'fsn_initial_text', 'fsn_progress_text', 'fsn_success_text' );

	$plugin_type = '';
	if ( function_exists( 'pll_register_string' ) ) {
		$plugin_type = 'polylang';
		foreach ( $keys as $key ) {
			if ( isset( $options[ $key ] ) && ! empty( $options[ $key ] ) ) {
				$multiline = false;
				if ( in_array( $key, $editor_keys, true ) ) {
					$multiline = true;
				}
				$pll_slug = str_replace( '_', '-', $key );
				pll_register_string( $pll_slug, $options[ $key ], 'commercegurus-commercekit', $multiline );
			}
		}
	} elseif ( has_action( 'wpml_register_single_string' ) ) {
		$plugin_type = 'wpml-hook';
		foreach ( $keys as $key ) {
			if ( isset( $options[ $key ] ) && ! empty( $options[ $key ] ) ) {
				do_action( 'wpml_register_single_string', 'commercegurus-commercekit', $options[ $key ], $options[ $key ] );
			}
		}
	} elseif ( function_exists( 'icl_register_string' ) ) {
		$plugin_type = 'wpml';
		foreach ( $keys as $key ) {
			if ( isset( $options[ $key ] ) && ! empty( $options[ $key ] ) ) {
				icl_register_string( 'commercegurus-commercekit', $options[ $key ], $options[ $key ] );
			}
		}
	}

	if ( isset( $options['countdown']['product']['title'] ) ) {
		commercekit_make_array_multilingual_strings( $options['countdown']['product']['title'], 'countdown-product-title', $plugin_type );
		commercekit_make_array_multilingual_strings( $options['countdown']['product']['days_label'], 'countdown-product-days-label', $plugin_type );
		commercekit_make_array_multilingual_strings( $options['countdown']['product']['hours_label'], 'countdown-product-hours-label', $plugin_type );
		commercekit_make_array_multilingual_strings( $options['countdown']['product']['minutes_label'], 'countdown-product-minutes-label', $plugin_type );
		commercekit_make_array_multilingual_strings( $options['countdown']['product']['seconds_label'], 'countdown-product-seconds-label', $plugin_type );
		if ( isset( $options['countdown']['product']['custom_msg'] ) ) {
			commercekit_make_array_multilingual_strings( $options['countdown']['product']['custom_msg'], 'countdown-product-custom-msg', $plugin_type );
		}
	}
	if ( isset( $options['countdown']['checkout']['title'] ) ) {
		commercekit_make_array_multilingual_strings( $options['countdown']['checkout']['title'], 'countdown-checkout-title', $plugin_type );
		commercekit_make_array_multilingual_strings( $options['countdown']['checkout']['expiry_message'], 'countdown-checkout-expiry-message', $plugin_type );
	}
	if ( isset( $options['order_bump_product']['product']['title'] ) ) {
		commercekit_make_array_multilingual_strings( $options['order_bump_product']['product']['title'], 'order-bump-product-title', $plugin_type );
		commercekit_make_array_multilingual_strings( $options['order_bump_product']['product']['button_text'], 'order-bump-product-button-text', $plugin_type );
	}
	if ( isset( $options['order_bump_minicart']['product']['title'] ) ) {
		commercekit_make_array_multilingual_strings( $options['order_bump_minicart']['product']['title'], 'order-bump-minicart-title', $plugin_type );
		commercekit_make_array_multilingual_strings( $options['order_bump_minicart']['product']['button_text'], 'order-bump-minicart-button-text', $plugin_type );
	}
	if ( isset( $options['badge']['product']['title'] ) ) {
		commercekit_make_array_multilingual_strings( $options['badge']['product']['title'], 'badge-product-title', $plugin_type );
	}
	if ( isset( $options['badge']['new']['title'] ) ) {
		commercekit_make_array_multilingual_strings( $options['badge']['new']['title'], 'badge-new-title', $plugin_type );
	}
	if ( isset( $options['esp_klaviyo']['main_call_txt'] ) ) {
		commercekit_make_array_multilingual_strings( $options['esp_klaviyo']['main_call_txt'], 'esp-klaviyo-main-call', $plugin_type );
	}
	if ( isset( $options['esp_klaviyo']['oos_message_txt'] ) ) {
		commercekit_make_array_multilingual_strings( $options['esp_klaviyo']['oos_message_txt'], 'esp-klaviyo-oos-message-txt', $plugin_type );
	}
	if ( isset( $options['esp_klaviyo']['stock_message_txt'] ) ) {
		commercekit_make_array_multilingual_strings( $options['esp_klaviyo']['stock_message_txt'], 'esp-klaviyo-stock-message-txt', $plugin_type );
	}
}
add_action( 'init', 'commercekit_make_multilingual_strings' );

/**
 *  Make array of multilingual strings
 *
 * @param mixed  $array of multilingual array.
 * @param string $key of multilingual key.
 * @param string $type of multilingual type.
 * @param string $multiline of multiline textarea.
 */
function commercekit_make_array_multilingual_strings( $array, $key, $type, $multiline = false ) {
	if ( is_array( $array ) ) {
		foreach ( $array as $k => $v ) {
			if ( 'polylang' === $type ) {
				pll_register_string( $key . '-' . $k, $v, 'commercegurus-commercekit', $multiline );
			} elseif ( 'wpml-hook' === $type ) {
				do_action( 'wpml_register_single_string', 'commercegurus-commercekit', $v, $v );
			} elseif ( 'wpml' === $type ) {
				icl_register_string( 'commercegurus-commercekit', $v, $v );
			}
		}
	} else {
		if ( 'polylang' === $type ) {
			pll_register_string( $key, $array, 'commercegurus-commercekit', $multiline );
		} elseif ( 'wpml-hook' === $type ) {
			do_action( 'wpml_register_single_string', 'commercegurus-commercekit', $array, $array );
		} elseif ( 'wpml' === $type ) {
			icl_register_string( 'commercegurus-commercekit', $array, $array );
		}
	}
}

/**
 *  Get multilingual string
 *
 * @param string $text of multilingual string.
 */
function commercekit_get_multilingual_string( $text ) {
	if ( function_exists( 'pll__' ) ) {
		$text = pll__( $text );
	} elseif ( has_filter( 'wpml_translate_single_string' ) ) {
		$text = apply_filters( 'wpml_translate_single_string', $text, 'commercegurus-commercekit', $text );
	} elseif ( function_exists( 'icl_translate' ) ) {
		$text = icl_translate( 'commercegurus-commercekit', $text );
	}
	return $text;
}

/**
 *  Get editor allowed html
 *
 * @return $allowed_html mixed allowed html.
 */
function commercekit_editor_allowed_html() {
	$allowed_html = array(
		'p'      => array(
			'class' => array(),
			'style' => array(),
		),
		'span'   => array(
			'class' => array(),
			'style' => array(),
		),
		'a'      => array(
			'href' => array(),
		),
		'br'     => array(),
		'strong' => array(),
		'em'     => array(),
		'u'      => array(),
		'ul'     => array(),
		'ol'     => array(),
		'li'     => array(),
		'del'    => array(),
	);

	return $allowed_html;
}

/**
 * Custom admin SKU search query
 *
 * @param  string $query of search.
 */
function commercekit_admin_sku_pre_get_posts( $query ) {
	global $cgkit_asku_search;
	if ( isset( $cgkit_asku_search ) && $cgkit_asku_search ) {
		add_filter( 'posts_join', 'commercekit_admin_sku_search_join', 99, 1 );
		add_filter( 'posts_where', 'commercekit_admin_sku_search_where', 99, 1 );
		add_filter( 'posts_groupby', 'commercekit_admin_sku_search_groupby', 99, 1 );
	}
}
add_action( 'pre_get_posts', 'commercekit_admin_sku_pre_get_posts', 99, 1 );

/**
 * Custom admin SKU search join
 *
 * @param  string $join of join.
 */
function commercekit_admin_sku_search_join( $join ) {
	global $wpdb;
	$join .= " LEFT JOIN $wpdb->postmeta sku_meta ON ( " . $wpdb->posts . ".ID = sku_meta.post_id AND sku_meta.meta_key='_sku' ) LEFT JOIN {$wpdb->posts} parents ON ( " . $wpdb->posts . '.post_parent = parents.ID AND ' . $wpdb->posts . ".post_parent != '0' )";

	return $join;
}

/**
 * Custom admin SKU search where
 *
 * @param  string $where of where.
 */
function commercekit_admin_sku_search_where( $where ) {
	global $wpdb;
	$where = preg_replace(
		"/\(\s*{$wpdb->posts}.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
		"({$wpdb->posts}.post_title LIKE $1) OR (sku_meta.meta_value LIKE $1)",
		$where
	);

	return $where . ' AND ( ' . $wpdb->posts . ".post_parent = '0' OR parents.post_status = 'publish' ) ";
}

/**
 * Custom admin SKU search groupby
 *
 * @param  string $groupby of groupby.
 */
function commercekit_admin_sku_search_groupby( $groupby ) {
	global $wpdb;
	$mygroupby = "{$wpdb->posts}.ID";
	if ( preg_match( "/$mygroupby/", $groupby ) ) {
		return $groupby;
	}
	if ( ! strlen( trim( $groupby ) ) ) {
		return $mygroupby;
	}

	return $groupby . ', ' . $mygroupby;
}

/**
 * Clear attribute swatches all cache
 */
function commercekit_as_clear_all_cache() {
	global $wpdb;
	$options = get_option( 'commercekit', array() );
	$enabled = true;
	if ( $enabled ) {
		$args = array(
			'hook'     => 'commercekit_attribute_swatch_build_cache',
			'per_page' => -1,
			'group'    => 'commercekit',
			'status'   => ActionScheduler_Store::STATUS_PENDING,
		);

		$action_ids = as_get_scheduled_actions( $args, 'ids' );
		$building   = 0 < count( $action_ids ) ? true : false;
		if ( $building ) {
			return esc_html__( 'Attribute swatches cache is building in the background. You can clear or start rebuilding when this background process has completed.', 'commercegurus-commercekit' );
		}

		commercekit_as_log( 'CLEAR SWATCHES CACHE TRIGGERED: About to clear and rebuild all swatches transients.' );
		$sql = 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "%_cgkit_swatch_loop_form_%"';
		$wpdb->query( $sql ); // phpcs:ignore
		$sql3 = 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "%_cgkit_swatch_loop_full_%"';
		$wpdb->query( $sql3 ); // phpcs:ignore
		commercekit_as_log( 'cgkit_swatch_loop_form transients purge complete.' );

		$table = $wpdb->prefix . 'commercekit_swatches_cache_count';
		$sql2  = 'DELETE FROM ' . $table . ' WHERE 1 = 1';
		$wpdb->query( $sql2 ); // phpcs:ignore
		commercekit_as_log( 'Database table ' . $table . ' truncate complete.' );

		$upload_dir = wp_upload_dir();
		$json_path  = $upload_dir['basedir'] . '/commercekit-json';
		if ( file_exists( $json_path ) ) {
			$counter  = 0;
			$json_dir = opendir( $json_path );
			if ( $json_dir ) {
				while ( false !== ( $json_file = readdir( $json_dir ) ) ) { // phpcs:ignore
					$json_file = (string) $json_file;
					if ( '.' === $json_file || '..' === $json_file ) {
						continue;
					}
					$file_path = $json_path . '/' . $json_file;
					if ( is_file( $file_path ) ) {
						unlink( $file_path );
						$counter++;
					}
				}
				closedir( $json_dir );
			}
			if ( $counter ) {
				commercekit_as_log( $counter . ' JSON files has been cleared.', 'commercekit-attribute-gallery' );
			}
		}

		$options['commercekit_as_scheduled']        = 0;
		$options['commercekit_as_scheduled_status'] = 'shortly';
		$options['commercekit_as_scheduled_clear']  = time();
		update_option( 'commercekit', $options, false );
		commercekit_as_log( 'commercekit_as_scheduled is 0. proceeding with creating events... checking commercekit_attribute_swatch_build_cache_list' );

		return esc_html__( 'Attribute swatches cache has been cleared.', 'commercegurus-commercekit' );
	}
}

/**
 * Admin ajax get attribute swatches build status
 */
function commercekit_ajax_get_as_build_status() {
	global $wpdb;
	$ajax    = array();
	$options = get_option( 'commercekit', array() );

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json( $ajax );
	}

	$args = array(
		'hook'     => 'commercekit_attribute_swatch_build_cache',
		'per_page' => -1,
		'group'    => 'commercekit',
		'status'   => ActionScheduler_Store::STATUS_PENDING,
	);

	$action_ids = as_get_scheduled_actions( $args, 'ids' );
	$as_status  = isset( $options['commercekit_as_scheduled_status'] ) ? $options['commercekit_as_scheduled_status'] : '';
	$building   = 0 < count( $action_ids ) ? 1 : 0;
	if ( ! $building && 'processing' === $as_status ) {
		$options['commercekit_as_scheduled_status'] = 'completed';
		update_option( 'commercekit', $options, false );
		$as_status = 'completed';
	}

	$enabled        = true;
	$build_done     = isset( $options['commercekit_as_scheduled_done'] ) ? gmdate( 'M j H:i:s', $options['commercekit_as_scheduled_done'] ) : '';
	$build_clear    = isset( $options['commercekit_as_scheduled_clear'] ) ? $options['commercekit_as_scheduled_clear'] : 0;
	$ajax['status'] = 1 === $enabled ? $building : 0;

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'order'          => 'DESC',
		'orderby'        => 'ID',
		'fields'         => 'ids',
		'tax_query'      => array( // phpcs:ignore
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => 'variable',
			),
		),
	);

	$query   = new WP_Query( $args );
	$p_total = (int) $query->found_posts;
	$table   = $wpdb->prefix . 'commercekit_swatches_cache_count';
	$sql     = 'SELECT COUNT(*) FROM ' . $table;
	$c_total = (int) $wpdb->get_var( $sql ); // phpcs:ignore
	$c_total = (int) min( $c_total, $p_total );
	$n_start = 0 < $build_clear && ( ( $build_clear + 600 ) < time() ) ? true : false;

	$alert_id = '';
	if ( $enabled && 'shortly' === $as_status && $n_start && 0 === count( $action_ids ) ) {
		$alert_id       = 'event-failed';
		$ajax['status'] = 0;
	} elseif ( $enabled && 'shortly' === $as_status ) {
		$alert_id       = 'event-shortly';
		$c_total        = 0;
		$ajax['status'] = 1;
	} elseif ( $enabled && 'created' === $as_status ) {
		$alert_id       = 'event-created';
		$c_total        = 0;
		$ajax['status'] = 1;
	} elseif ( $enabled && 'processing' === $as_status ) {
		$alert_id       = 'event-processing';
		$ajax['status'] = 1;
	} elseif ( $enabled && 'cancelled' === $as_status ) {
		$alert_id       = 'event-cancelled';
		$ajax['status'] = 0;
	} elseif ( $enabled && 'completed' === $as_status ) {
		$alert_id       = 'event-completed';
		$ajax['status'] = 0;
		if ( '' === $build_done ) {
			$alert_id = '';
		}
	}

	if ( 0 === (int) $query->found_posts ) {
		$alert_id       = 'ajax-stop';
		$c_total        = 0;
		$ajax['status'] = 0;
	}

	$ajax['c_total'] = $c_total;
	$ajax['p_total'] = $p_total;

	$ajax['c_percent']  = $p_total > 0 ? (int) ( ( $c_total * 100 ) / $p_total ) : 0;
	$ajax['build_done'] = $build_done;
	$ajax['alert_id']   = $alert_id;
	$ajax['is_stuck']   = 0;
	if ( 0 === $ajax['c_percent'] && 'event-created' === $alert_id ) {
		$last_created = isset( $options['commercekit_as_actions_created'] ) ? (int) $options['commercekit_as_actions_created'] : 0;
		if ( $last_created && $last_created <= ( time() - 90 ) ) {
			$ajax['is_stuck'] = 1;
		}
	}

	wp_send_json( $ajax );
}

add_action( 'wp_ajax_commercekit_get_as_build_status', 'commercekit_ajax_get_as_build_status' );

/**
 * Product CommerceKit options meta box
 */
function commercegurus_product_cgkit_options_meta_box() {
	$options = get_option( 'commercekit', array() );

	$enable_inventory_bar   = isset( $options['inventory_display'] ) && 1 === (int) $options['inventory_display'] ? true : false;
	$enable_countdown_timer = isset( $options['countdown_timer'] ) && 1 === (int) $options['countdown_timer'] ? true : false;
	$enable_pdp_gallery     = isset( $options['pdp_gallery'] ) && 1 === (int) $options['pdp_gallery'] ? true : false;
	$enable_pdpa_gallery    = isset( $options['pdp_attributes_gallery'] ) && 1 === (int) $options['pdp_attributes_gallery'] && $enable_pdp_gallery ? true : false;
	$enable_waitlist        = isset( $options['waitlist'] ) && 1 === (int) $options['waitlist'] ? true : false;
	if ( $enable_inventory_bar || $enable_countdown_timer || $enable_pdpa_gallery || $enable_pdp_gallery || $enable_waitlist ) {
		add_meta_box( 'commercegurus-product-cgkit-options', esc_html__( 'CommerceKit options', 'commercegurus-commercekit' ), 'commercegurus_product_cgkit_options_meta', 'product', 'side', 'low' );
	}
}
add_action( 'admin_init', 'commercegurus_product_cgkit_options_meta_box' );

/**
 * Product gallery layout meta
 */
function commercegurus_product_cgkit_options_meta() {
	global $post;
	$options = get_option( 'commercekit', array() );

	$enable_inventory_bar   = isset( $options['inventory_display'] ) && 1 === (int) $options['inventory_display'] ? true : false;
	$enable_countdown_timer = isset( $options['countdown_timer'] ) && 1 === (int) $options['countdown_timer'] ? true : false;
	$enable_pdp_gallery     = isset( $options['pdp_gallery'] ) && 1 === (int) $options['pdp_gallery'] ? true : false;
	$enable_pdpa_gallery    = isset( $options['pdp_attributes_gallery'] ) && 1 === (int) $options['pdp_attributes_gallery'] && $enable_pdp_gallery ? true : false;
	$sticky_atc_desktop     = isset( $options['sticky_atc_desktop'] ) && 1 === (int) $options['sticky_atc_desktop'] ? true : false;
	$sticky_atc_mobile      = isset( $options['sticky_atc_mobile'] ) && 1 === (int) $options['sticky_atc_mobile'] ? true : false;
	$sticky_atc_tabs        = isset( $options['sticky_atc_tabs'] ) && 1 === (int) $options['sticky_atc_tabs'] ? true : false;
	$enable_sticky_atc      = $sticky_atc_desktop || $sticky_atc_mobile || $sticky_atc_tabs ? true : false;
	$enable_waitlist        = isset( $options['waitlist'] ) && 1 === (int) $options['waitlist'] ? true : false;
	if ( isset( $post->ID ) && $post->ID && ( $enable_pdpa_gallery || $enable_pdp_gallery ) ) {
		$cgkit_gallery_layout = get_post_meta( $post->ID, 'commercekit_gallery_layout', true );
		?>
<p>
	<label><?php esc_html_e( 'Product gallery layout', 'commercegurus-commercekit' ); ?></label>
	<select name="commercekit_gallery_layout" id="commercekit_gallery_layout">
		<option value=""><?php esc_html_e( 'Global default', 'commercegurus-commercekit' ); ?></option>
		<option value="horizontal" <?php echo 'horizontal' === $cgkit_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Horizontal', 'commercegurus-commercekit' ); ?></option>
		<option value="vertical-left" <?php echo 'vertical-left' === $cgkit_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Vertical left', 'commercegurus-commercekit' ); ?></option>
		<option value="vertical-right" <?php echo 'vertical-right' === $cgkit_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Vertical right', 'commercegurus-commercekit' ); ?></option>
		<option value="grid-2-4" <?php echo 'grid-2-4' === $cgkit_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Grid: 2 cols x 4 rows', 'commercegurus-commercekit' ); ?></option>
		<option value="grid-3-1-2" <?php echo 'grid-3-1-2' === $cgkit_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Grid: 3 cols, 1 col, 2 cols', 'commercegurus-commercekit' ); ?></option>
		<option value="grid-1-2-2" <?php echo 'grid-1-2-2' === $cgkit_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Grid: 1 col, 2 cols, 2 cols', 'commercegurus-commercekit' ); ?></option>
		<option value="vertical-scroll" <?php echo 'vertical-scroll' === $cgkit_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Vertical scroll', 'commercegurus-commercekit' ); ?></option>
		<option value="simple-scroll" <?php echo 'simple-scroll' === $cgkit_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Simple scroll', 'commercegurus-commercekit' ); ?></option>
		<option value="core-gallery" <?php echo 'core-gallery' === $cgkit_gallery_layout ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Core WooCommerce Gallery', 'commercegurus-commercekit' ); ?></option>
	</select>
</p>
		<?php
	}
	if ( isset( $post->ID ) && $post->ID && ( $enable_inventory_bar || $enable_countdown_timer || $enable_sticky_atc || $enable_waitlist ) ) {
		$disable_cgkit_inventory = (int) get_post_meta( $post->ID, 'commercekit_disable_inventory', true );
		$disable_cgkit_countdown = (int) get_post_meta( $post->ID, 'commercekit_disable_countdown', true );
		$disable_sticky_atc      = (int) get_post_meta( $post->ID, 'commercekit_disable_sticky_atc', true );
		$disable_cgkit_waitlist  = (int) get_post_meta( $post->ID, 'commercekit_disable_waitlist', true );
		?>
		<p>
			<label><strong><?php esc_html_e( 'Disable sections', 'commercegurus-commercekit' ); ?></strong></label>
		</p>
		<p>
		<?php if ( $enable_countdown_timer ) { ?>
			<label><input type="checkbox" name="commercekit_disable_countdown" id="commercekit_disable_countdown" value="1" <?php echo 1 === $disable_cgkit_countdown ? 'checked="checked"' : ''; ?>/>&nbsp;<?php esc_html_e( 'Disable product countdown', 'commercegurus-commercekit' ); ?></label><br />
		<?php } ?>
		<?php if ( $enable_inventory_bar ) { ?>
			<label><input type="checkbox" name="commercekit_disable_inventory" id="commercekit_disable_inventory" value="1" <?php echo 1 === $disable_cgkit_inventory ? 'checked="checked"' : ''; ?>/>&nbsp;<?php esc_html_e( 'Disable inventory bar', 'commercegurus-commercekit' ); ?></label><br />
		<?php } ?>
		<?php if ( $enable_sticky_atc ) { ?>
			<label><input type="checkbox" name="commercekit_disable_sticky_atc" id="commercekit_disable_sticky_atc" value="1" <?php echo 1 === $disable_sticky_atc ? 'checked="checked"' : ''; ?>/>&nbsp;<?php esc_html_e( 'Disable sticky add to cart bar', 'commercegurus-commercekit' ); ?></label><br />
		<?php } ?>
		<?php if ( $enable_waitlist ) { ?>
			<label><input type="checkbox" name="commercekit_disable_waitlist" id="commercekit_disable_waitlist" value="1" <?php echo 1 === $disable_cgkit_waitlist ? 'checked="checked"' : ''; ?>/>&nbsp;<?php esc_html_e( 'Disable waitlist', 'commercegurus-commercekit' ); ?></label><br />
		<?php } ?>
		</p>
		<?php
	}
	echo '<input type="hidden" name="cgkit_options_nonce" id="cgkit_options_nonce" value="' . esc_html( wp_create_nonce( 'cgkit_options_nonce' ) ) . '" />';
}

/**
 * Save product CommerceKit options
 *
 * @param string $post_id post ID.
 * @param string $post post.
 */
function commercegurus_save_product_cgkit_options( $post_id, $post ) {
	if ( 'product' !== $post->post_type ) {
		return;
	}
	$options = get_option( 'commercekit', array() );

	$enable_inventory_bar   = isset( $options['inventory_display'] ) && 1 === (int) $options['inventory_display'] ? true : false;
	$enable_countdown_timer = isset( $options['countdown_timer'] ) && 1 === (int) $options['countdown_timer'] ? true : false;
	$enable_pdp_gallery     = isset( $options['pdp_gallery'] ) && 1 === (int) $options['pdp_gallery'] ? true : false;
	$enable_pdpa_gallery    = isset( $options['pdp_attributes_gallery'] ) && 1 === (int) $options['pdp_attributes_gallery'] && $enable_pdp_gallery ? true : false;
	$sticky_atc_desktop     = isset( $options['sticky_atc_desktop'] ) && 1 === (int) $options['sticky_atc_desktop'] ? true : false;
	$sticky_atc_mobile      = isset( $options['sticky_atc_mobile'] ) && 1 === (int) $options['sticky_atc_mobile'] ? true : false;
	$sticky_atc_tabs        = isset( $options['sticky_atc_tabs'] ) && 1 === (int) $options['sticky_atc_tabs'] ? true : false;
	$enable_sticky_atc      = $sticky_atc_desktop || $sticky_atc_mobile || $sticky_atc_tabs ? true : false;
	$enable_waitlist        = isset( $options['waitlist'] ) && 1 === (int) $options['waitlist'] ? true : false;

	$cgkit_options_nonce = isset( $_POST['cgkit_options_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['cgkit_options_nonce'] ) ) : '';
	if ( $cgkit_options_nonce && wp_verify_nonce( $cgkit_options_nonce, 'cgkit_options_nonce' ) ) {
		if ( $post_id && ( $enable_pdpa_gallery || $enable_pdp_gallery ) ) {
			$cgkit_gallery_layout = isset( $_POST['commercekit_gallery_layout'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_gallery_layout'] ) ) : '';
			update_post_meta( $post_id, 'commercekit_gallery_layout', $cgkit_gallery_layout );
		}
		if ( $post_id && $enable_countdown_timer ) {
			$disable_cgkit_countdown = isset( $_POST['commercekit_disable_countdown'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['commercekit_disable_countdown'] ) ) : 0;
			update_post_meta( $post_id, 'commercekit_disable_countdown', $disable_cgkit_countdown );
		}
		if ( $post_id && $enable_inventory_bar ) {
			$disable_cgkit_inventory = isset( $_POST['commercekit_disable_inventory'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['commercekit_disable_inventory'] ) ) : 0;
			update_post_meta( $post_id, 'commercekit_disable_inventory', $disable_cgkit_inventory );
		}
		if ( $post_id && $enable_sticky_atc ) {
			$disable_sticky_atc = isset( $_POST['commercekit_disable_sticky_atc'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['commercekit_disable_sticky_atc'] ) ) : 0;
			update_post_meta( $post_id, 'commercekit_disable_sticky_atc', $disable_sticky_atc );
		}
		if ( $post_id && $enable_waitlist ) {
			$disable_cgkit_waitlist = isset( $_POST['commercekit_disable_waitlist'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['commercekit_disable_waitlist'] ) ) : 0;
			update_post_meta( $post_id, 'commercekit_disable_waitlist', $disable_cgkit_waitlist );
		}
	}
}
add_action( 'woocommerce_process_product_meta', 'commercegurus_save_product_cgkit_options', 10, 2 );

/**
 * Attribute Swatches logger
 *
 * @param  string $message log message.
 * @param  string $source source of log.
 */
function commercekit_as_log( $message, $source = 'commercekit-attribute-swatches' ) {
	$options = get_option( 'commercekit', array() );
	$enabled = isset( $options['as_logger'] ) && 1 === (int) $options['as_logger'] ? true : false;
	if ( $enabled ) {
		$logger = wc_get_logger();
		$logger->info( $message, array( 'source' => $source ) );
	}
}

/**
 * Get attribute swatches totals log message
 */
function commercekit_get_as_totals_log_message() {
	global $wpdb;
	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'order'          => 'DESC',
		'orderby'        => 'ID',
		'fields'         => 'ids',
		'tax_query'      => array( // phpcs:ignore
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => 'variable',
			),
		),
	);

	$query   = new WP_Query( $args );
	$p_total = (int) $query->found_posts;
	$table   = $wpdb->prefix . 'commercekit_swatches_cache_count';
	$sql     = 'SELECT COUNT(*) FROM ' . $table;
	$c_total = (int) $wpdb->get_var( $sql ); // phpcs:ignore
	$c_total = (int) min( $c_total, $p_total );

	return 'Total number of variable products cached is ' . $c_total . ' / ' . $p_total;
}

/**
 * Get time hours minutes dropdowns
 *
 * @param  string $limit options limit.
 * @param  string $selected selected option.
 */
function commercekit_get_hours_minutes_options( $limit, $selected ) {
	$output = '';
	for ( $i = 0; $i <= $limit; $i++ ) {
		$pi  = str_pad( $i, 2, '0', STR_PAD_LEFT );
		$sel = '';
		if ( $pi == $selected ) { // phpcs:ignore
			$sel = ' selected="selected"';
		}
		$output .= '<option value="' . $pi . '"' . $sel . '>' . $pi . '</option>';
	}
	echo $output; // phpcs:ignore
}

/**
 * Product ajs index logger
 *
 * @param  string $message log message.
 */
function commercegurus_ajs_log( $message ) {
	$options = get_option( 'commercekit', array() );
	$enabled = isset( $options['ajs_index_logger'] ) && 1 === (int) $options['ajs_index_logger'] ? true : false;
	if ( $enabled ) {
		$logger = wc_get_logger();
		$logger->info( $message, array( 'source' => 'commercekit-ajax-search-index' ) );
	}
}
/**
 * Generate AJS product index
 */
function commercekit_ajs_generate_wc_product_index() {
	global $wpdb;
	$options        = get_option( 'commercekit', array() );
	$ajax           = array();
	$ajax['status'] = 0;
	$ajax['total']  = 0;
	$enable_ajs     = isset( $options['ajax_search'] ) && 1 === (int) $options['ajax_search'] ? true : false;
	$interrupt_ajs  = isset( $options['interrupt_ajs'] ) && 1 === (int) $options['interrupt_ajs'] ? 1 : 0;
	$cancelled_ajs  = isset( $options['cancelled_ajs'] ) && 1 === (int) $options['cancelled_ajs'] ? 1 : 0;
	$data           = commercekit_ajs_temporary_options( 'GET' );
	$interrupt_ajs  = isset( $data['interrupt_ajs'] ) && 1 === (int) $data['interrupt_ajs'] ? 1 : $interrupt_ajs;
	$cancelled_ajs  = isset( $data['cancelled_ajs'] ) && 1 === (int) $data['cancelled_ajs'] ? 1 : $cancelled_ajs;

	$ajax['complete']       = 0;
	$ajax['percent']        = 0;
	$ajax['generating_ajs'] = 0;
	$ajax['interrupt_ajs']  = $interrupt_ajs;
	$ajax['cancelled_ajs']  = $cancelled_ajs;
	$query_template         = "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_parent = '0' AND post_status = 'publish' AND ID > %d ";
	$total_query            = $wpdb->prepare( $query_template, 0 ); // phpcs:ignore
	$ajax['total']          = (int) $wpdb->get_var( $total_query ); // phpcs:ignore
	$generate_ajs           = isset( $_POST['generate_ajs'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['generate_ajs'] ) ) : 0; // phpcs:ignore

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json( $ajax );
	}

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json( $ajax );
	}
	$ajax['status'] = 1;

	if ( 1 === $generate_ajs ) {
		if ( $enable_ajs ) {
			commercegurus_ajs_log( 'CLEAR INDEX TRIGGERED: About to clear and rebuild all products ajax search index.' );
		} else {
			commercegurus_ajs_log( 'CLEAR INDEX TRIGGERED: About to clear all products ajax search index.' );
		}

		$table = $wpdb->prefix . 'commercekit_ajs_product_index';
		$wpdb->query( 'TRUNCATE TABLE ' . $table ); // phpcs:ignore
		commercegurus_ajs_log( 'Database table ' . $table . ' truncate complete.' );
		$options['generating_ajs_id']   = 0;
		$options['generating_ajs']      = 0;
		$options['interrupt_ajs']       = 0;
		$options['cancelled_ajs']       = 0;
		$options['generating_ajs_done'] = 0;
		update_option( 'commercekit', $options, false );
		$ajax['generating_ajs'] = 0;
		$ajax['interrupt_ajs']  = 0;
		$ajax['cancelled_ajs']  = 0;

		if ( current_user_can( 'manage_options' ) && $enable_ajs ) {
			as_schedule_single_action( time() + 5, 'commercegurus_ajs_run_wc_product_index', array( 'ajs_product_id' => 0 ), 'commercekit' );
			commercegurus_ajs_log( 'REBUILDING INDEX: creating action for commercegurus_ajs_run_wc_product_index hook with product_id = 0' );
			$options['generating_ajs']      = 1;
			$options['interrupt_ajs']       = 0;
			$options['cancelled_ajs']       = 0;
			$options['generating_ajs_done'] = 0;
			commercegurus_ajs_log( 'updating generating_ajs_id to 0, generating_ajs to 1, interrupt_ajs to 0, cancelled_ajs to 0, generating_ajs_done to 0' );
			update_option( 'commercekit', $options, false );
			$ajax['generating_ajs'] = 1;
			$ajax['interrupt_ajs']  = 0;
			$ajax['cancelled_ajs']  = 0;
		}

		$data = array(
			'interrupt_ajs' => 0,
			'cancelled_ajs' => 0,
		);
		commercekit_ajs_temporary_options( 'SET', $data );
	} else {
		$ajax['generating_ajs'] = isset( $options['generating_ajs'] ) && 1 === (int) $options['generating_ajs'] ? 1 : 0;
	}

	$generating_cache = false;
	if ( 1 === (int) $ajax['generating_ajs'] && $enable_ajs ) {
		$cgkit_wc_ajs      = new CommerceKit_AJS_Index();
		$ajax['mem_limit'] = $cgkit_wc_ajs->get_memory_limit();
		$ajax['exec_time'] = $cgkit_wc_ajs->get_execution_time();
		$ajax['mem_usage'] = memory_get_usage( true );
		$generating_cache  = true;
	}

	if ( 1 === (int) $ajax['cancelled_ajs'] ) {
		$ajax['generating_ajs'] = 0;
	}

	if ( 1 === (int) $ajax['interrupt_ajs'] ) {
		$ajax['generating_ajs'] = 0;
	}

	$generating_ajs_id  = isset( $options['generating_ajs_id'] ) ? (int) $options['generating_ajs_id'] : 0;
	$pending_query      = $wpdb->prepare( $query_template, $generating_ajs_id ); // phpcs:ignore
	$complete_total     = $ajax['total'] - (int) $wpdb->get_var( $pending_query ); // phpcs:ignore
	$ajax['complete']   = $complete_total >= 0 ? $complete_total : 0;
	$ajax['percent']    = $ajax['total'] > 0 ? (int) ( ( $ajax['complete'] * 100 ) / $ajax['total'] ) : 0;
	$ajax['build_done'] = isset( $options['generate_ajs_time'] ) && ! empty( $options['generate_ajs_time'] ) ? gmdate( 'M j H:i:s', $options['generate_ajs_time'] ) : '';

	if ( ! $generating_cache && $ajax['complete'] && $ajax['complete'] !== $ajax['total'] ) {
		$new_ajs_id = (int) $wpdb->get_var( 'SELECT MAX(product_id) FROM ' . $wpdb->prefix . 'commercekit_ajs_product_index' ); // phpcs:ignore
		if ( $new_ajs_id !== (int) $generating_ajs_id ) {
			$generating_ajs_id = $new_ajs_id;
			$pending_query     = $wpdb->prepare( $query_template, $generating_ajs_id ); // phpcs:ignore
			$complete_total    = $ajax['total'] - (int) $wpdb->get_var( $pending_query ); // phpcs:ignore
			$ajax['complete']  = $complete_total >= 0 ? $complete_total : 0;

			$options['generating_ajs_id'] = $generating_ajs_id;
			update_option( 'commercekit', $options, false );
		}
	}

	wp_send_json( $ajax );
}
add_action( 'wp_ajax_commercekit_ajs_generate_wc_product_index', 'commercekit_ajs_generate_wc_product_index' );

/**
 * Run action scheduler for cancel ajax search indexing process
 */
function commercekit_ajs_generate_cancel_handle() {
	$ajax    = array();
	$options = get_option( 'commercekit', array() );

	$ajax['status']  = 0;
	$ajax['message'] = '';

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json( $ajax );
	}

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json( $ajax );
	}

	$options['interrupt_ajs'] = 0;
	$options['cancelled_ajs'] = 1;
	update_option( 'commercekit', $options, false );
	$data = array(
		'interrupt_ajs' => 0,
		'cancelled_ajs' => 1,
	);
	commercekit_ajs_temporary_options( 'SET', $data );

	$as_store = ActionScheduler::store();
	$as_store->cancel_actions_by_hook( 'commercegurus_ajs_run_wc_product_index' );
	commercegurus_ajs_log( 'The indexing process has been cancelled.' );

	$ajax['status']  = 1;
	$ajax['message'] = esc_html__( 'The indexing process has been cancelled.', 'commercegurus-commercekit' );

	wp_send_json( $ajax );
}
add_action( 'wp_ajax_commercekit_ajs_generate_cancel', 'commercekit_ajs_generate_cancel_handle', 10 );

/**
 * Ajax search indexing process temporary options
 *
 * @param  string $method  method.
 * @param  string $options options.
 */
function commercekit_ajs_temporary_options( $method, $options = array() ) {
	$upload_dir = wp_upload_dir();
	$txt_file   = $upload_dir['basedir'] . '/commercekit-ajs.txt';
	if ( 'GET' === $method ) {
		if ( ! file_exists( $txt_file ) ) {
			return array();
		}
		$fp = fopen( $txt_file, 'r' ); // phpcs:ignore
		if ( $fp ) {
			$options = fgets( $fp ); // phpcs:ignore
			if ( ! empty( $options ) ) {
				return json_decode( $options, true );
			}
			fclose( $fp ); // phpcs:ignore 
		}
		return array();
	} elseif ( 'SET' === $method ) {
		$fp = fopen( $txt_file, 'w' ); // phpcs:ignore
		if ( $fp ) {
			fwrite( $fp, wp_json_encode( $options ) ); // phpcs:ignore
			fclose( $fp ); // phpcs:ignore 
		}
	} elseif ( 'DEL' === $method ) {
		if ( file_exists( $txt_file ) ) {
			unlink( $txt_file );
		}
	}
}

/**
 * Reset zero results
 */
function commercekit_ajs_reset_zero_results() {
	global $wpdb;
	$ajax            = array();
	$ajax['success'] = 0;
	$ajax['html']    = '<tr><th class="left">' . esc_html__( 'Term', 'commercegurus-commercekit' ) . '</th><th class="right">' . esc_html__( 'Count', 'commercegurus-commercekit' ) . '</th></tr><tr><td class="center" colspan="2">' . esc_html__( 'No terms', 'commercegurus-commercekit' ) . '</td></tr>';
	$ajax['percent'] = '0%';

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json( $ajax );
	}

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json( $ajax );
	}

	$table_name = $wpdb->prefix . 'commercekit_searches';
	$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET `no_result_count` = %d WHERE 1=1", 0 ) ); // phpcs:ignore
	delete_transient( 'commercekit_search_reports' );
	$ajax['success'] = 1;

	wp_send_json( $ajax );
}
add_action( 'wp_ajax_commercekit_ajs_reset_zero_results', 'commercekit_ajs_reset_zero_results' );

/**
 * Reset ajax search statistics
 */
function commercekit_ajs_reset_statistics() {
	global $wpdb;
	$ajax                    = array();
	$ajax['success']         = 0;
	$ajax['frequent_search'] = '<tr><th class="left">' . esc_html__( 'Term', 'commercegurus-commercekit' ) . '</th><th class="right">' . esc_html__( 'Count', 'commercegurus-commercekit' ) . '</th></tr><tr><td class="center" colspan="2">' . esc_html__( 'No terms', 'commercegurus-commercekit' ) . '</td></tr>';
	$ajax['total_search']    = '0';
	$ajax['click_rate']      = '0%';
	$ajax['zero_results']    = $ajax['frequent_search'];
	$ajax['zero_percent']    = '0%';

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json( $ajax );
	}

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json( $ajax );
	}

	$table_name = $wpdb->prefix . 'commercekit_searches';
	$wpdb->query( "DELETE FROM $table_name WHERE 1=1" ); // phpcs:ignore
	delete_transient( 'commercekit_search_reports' );
	$ajax['success'] = 1;

	wp_send_json( $ajax );
}
add_action( 'wp_ajax_commercekit_ajs_reset_statistics', 'commercekit_ajs_reset_statistics' );

/**
 * Reset order bump statistics
 */
function commercekit_reset_obp_statistics() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json_error();
	}

	update_option( 'commercekit_obp_views', 0, false );
	update_option( 'commercekit_obp_clicks', 0, false );
	update_option( 'commercekit_obp_sales', 0, false );
	update_option( 'commercekit_obp_sales_revenue', 0, false );

	wp_send_json_success();
}
add_action( 'wp_ajax_commercekit_reset_obp_statistics', 'commercekit_reset_obp_statistics' );

/**
 * Reset waitlist statistics
 */
function commercekit_reset_waitlist_statistics() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json_error();
	}

	update_option( 'commercekit_wtls_reset', 0, false );
	update_option( 'commercekit_wtls_total', 0, false );
	update_option( 'commercekit_wtls_sales', 0, false );
	update_option( 'commercekit_wtls_sales_revenue', 0, false );

	wp_send_json_success();
}
add_action( 'wp_ajax_commercekit_reset_waitlist_statistics', 'commercekit_reset_waitlist_statistics' );

/**
 * Reset wishlist statistics
 */
function commercekit_reset_wishlist_statistics() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json_error();
	}

	update_option( 'commercekit_wsls_reset', 0, false );
	update_option( 'commercekit_wsls_total', 0, false );
	update_option( 'commercekit_wsls_sales', 0, false );
	update_option( 'commercekit_wsls_sales_revenue', 0, false );

	wp_send_json_success();
}
add_action( 'wp_ajax_commercekit_reset_wishlist_statistics', 'commercekit_reset_wishlist_statistics' );
