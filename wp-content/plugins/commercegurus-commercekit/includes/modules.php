<?php
/**
 *
 * Frontend modules
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

/**
 * Display module output
 *
 * @param  string $display_text of module output.
 */
function commercekit_module_output( $display_text ) {
	$args = array(
		'span'     => array(
			'data-product-id' => array(),
			'data-type'       => array(),
			'data-wpage'      => array(),
			'class'           => array(),
			'aria-label'      => array(),
		),
		'h2'       => array(
			'class' => array(),
		),
		'del'      => array(),
		'ins'      => array(),
		'strong'   => array(),
		'em'       => array(
			'class' => array(),
		),
		'b'        => array(),
		'i'        => array(
			'class' => array(),
		),
		'img'      => array(
			'href'        => array(),
			'alt'         => array(),
			'class'       => array(),
			'scale'       => array(),
			'width'       => array(),
			'height'      => array(),
			'src'         => array(),
			'srcset'      => array(),
			'sizes'       => array(),
			'data-src'    => array(),
			'data-srcset' => array(),
		),
		'br'       => array(),
		'p'        => array(),
		'a'        => array(
			'href'            => array(),
			'data-product-id' => array(),
			'data-type'       => array(),
			'data-wpage'      => array(),
			'class'           => array(),
			'aria-label'      => array(),
			'target'          => array(),
			'title'           => array(),
		),
		'div'      => array(
			'data-product-id' => array(),
			'data-type'       => array(),
			'data-wpage'      => array(),
			'class'           => array(),
			'aria-label'      => array(),
		),
		'noscript' => array(),
	);

	echo wp_kses( $display_text, $args );
}

/**
 * Kses allowed protocols
 *
 * @param  string $protocols protocols.
 */
function commercekit_kses_allowed_protocols( $protocols ) {
	$protocols[] = 'data';
	return $protocols;
}
add_filter( 'kses_allowed_protocols', 'commercekit_kses_allowed_protocols' );

/**
 * Get default settings
 *
 * @param  string $key default settings key.
 * @param  string $options default settings.
 */
function commercekit_get_default_settings( $key = '', $options = array() ) {
	$old_options = get_option( 'commercekit', array() );
	$defaults    = array(

		'ajax_search'            => '0',
		'ajs_placeholder'        => esc_html__( 'Search products...', 'commercegurus-commercekit' ),
		'ajs_display'            => 'all',
		'ajs_tabbed'             => '0',
		'ajs_pre_tab'            => '0',
		'ajs_other_text'         => esc_html__( 'Other results', 'commercegurus-commercekit' ),
		'ajs_no_text'            => esc_html__( 'No product results', 'commercegurus-commercekit' ),
		'ajs_all_text'           => esc_html__( 'View all product results', 'commercegurus-commercekit' ),
		'ajs_outofstock'         => '0',
		'ajs_orderby_oos'        => '0',
		'ajs_hidevar'            => '0',
		'ajs_product_count'      => '3',
		'ajs_index_logger'       => '0',
		'ajs_other_results'      => '1',
		'ajs_no_other_text'      => esc_html__( 'No other results', 'commercegurus-commercekit' ),
		'ajs_other_all_text'     => esc_html__( 'View all other results', 'commercegurus-commercekit' ),
		'ajs_other_count'        => '3',
		'ajs_fast_search'        => '0',

		'countdown_timer'        => '0',

		'order_bump'             => '0',
		'multiple_obp'           => '0',
		'multiple_obp_label'     => '',
		'order_bump_mini'        => '0',
		'multiple_obp_mini'      => '0',
		'multiple_obp_mini_lbl'  => esc_html__( 'Before you go', 'commercegurus-commercekit' ),

		'pdp_gallery'            => '0',
		'pdp_lightbox'           => '1',
		'pdp_lightbox_cap'       => '0',
		'pdp_video_autoplay'     => '1',
		'pdp_mobile_optimized'   => '0',
		'pdp_thumb_arrows'       => '0',
		'pdp_featured_review'    => '0',
		'pdp_gallery_layout'     => 'horizontal',
		'pdp_mobile_layout'      => isset( $old_options['pdp_mobile_optimized'] ) && 1 === (int) $old_options['pdp_mobile_optimized'] ? 'minimal' : 'default',
		'next_slide_percent'     => '10',
		'pdp_desktop_thumbnails' => '4',
		'pdp_mobile_thumbnails'  => '4',

		'pdp_attributes_gallery' => '0',
		'attribute_swatches'     => '0',
		'attribute_swatches_pdp' => '1',
		'attribute_swatches_plp' => '0',
		'as_activate_atc'        => '0',
		'as_quickadd_txt'        => esc_html__( 'Quick add', 'commercegurus-commercekit' ),
		'as_more_opt_txt'        => esc_html__( 'More options', 'commercegurus-commercekit' ),
		'as_swatch_link'         => 'variation',
		'as_button_style'        => '0',
		'as_enable_tooltips'     => '1',
		'as_logger'              => '0',
		'as_disable_facade'      => '0',
		'as_disable_pdp'         => '0',

		'sticky_atc_desktop'     => '0',
		'sticky_atc_mobile'      => '0',
		'sticky_atc_tabs'        => '0',
		'sticky_atc_label'       => esc_html__( 'Gallery', 'commercegurus-commercekit' ),

		'fsn_cart_page'          => '0',
		'fsn_mini_cart'          => '0',
		'fsn_before_ship'        => '0',
		'fsn_initial_text'       => esc_html__( 'Get <strong>free shipping</strong> for orders over {free_shipping_amount}', 'commercegurus-commercekit' ),
		'fsn_progress_text'      => esc_html__( 'You are {remaining} away from free shipping.', 'commercegurus-commercekit' ),
		'fsn_success_text'       => esc_html__( 'Your order qualifies for free shipping!', 'commercegurus-commercekit' ),
		'fsn_bar_color'          => '#3bb54a',
		'fsn_shop_page'          => '0',

		'size_guide'             => '0',
		'size_guide_label'       => esc_html__( 'Size guide', 'commercegurus-commercekit' ),
		'size_guide_search'      => '1',
		'size_guide_icon'        => '0',
		'size_guide_mode'        => '1',

		'store_badge'            => '0',
		'badge_new_label'        => esc_html__( 'New!', 'commercegurus-commercekit' ),
		'badge_new_days'         => '30',
		'badge_bg_color'         => '#e21e1d',
		'badge_color'            => '#ffffff',

		'inventory_display'      => '0',
		'inventory_text'         => esc_html__( 'Only %s items left in stock!', 'commercegurus-commercekit' ), // phpcs:ignore
		'inventory_threshold'    => 20,
		'inventory_lsb_color'    => '#D75852',
		'inventory_text_31'      => esc_html__( 'Less than %s items left!', 'commercegurus-commercekit' ), // phpcs:ignore
		'inventory_threshold31'  => 30,
		'inventory_rsb_color'    => '#60B358',
		'inventory_text_100'     => esc_html__( 'This item is selling fast!', 'commercegurus-commercekit' ),
		'inventory_threshold100' => 100, // phpcs:ignore
		'inventory_hsb_color'    => '#60B358',

		'waitlist'               => '0',
		'wtl_intro'              => esc_html__( 'Notify me when the item is back in stock.', 'commercegurus-commercekit' ),
		'wtl_email_text'         => esc_html__( 'Enter your email address...', 'commercegurus-commercekit' ),
		'wtl_button_text'        => esc_html__( 'Join waiting list', 'commercegurus-commercekit' ),
		'wtl_consent_text'       => esc_html__( 'I consent to being contacted by the store owner', 'commercegurus-commercekit' ),
		'wtl_success_text'       => esc_html__( 'You have been added to the waiting list for this product!', 'commercegurus-commercekit' ),
		'wtl_readmore_text'      => esc_html__( 'Get notified', 'commercegurus-commercekit' ),
		'wtl_show_oos'           => '0',
		'wtl_from_email'         => get_option( 'admin_email' ),
		'wtl_from_name'          => get_option( 'blogname' ),
		'wtl_force_email_name'   => '0',
		'wtl_recipient'          => get_option( 'admin_email' ),
		'wtl_reply_to'           => '0',
		'waitlist_auto_mail'     => '1',
		'wtl_auto_subject'       => esc_html__( 'A product you are waiting for is back in stock!', 'commercegurus-commercekit' ),
		'wtl_auto_content'       => esc_html__( "Hi,\r\n{product_title} is now back in stock on {site_name}.\r\nYou have been sent this email because your email address was registered in a waiting list for this product.\r\nIf you would like to purchase {product_title}, please visit the following link:\r\n{product_link}", 'commercegurus-commercekit' ),
		'wtl_auto_footer'        => esc_html__( 'This email does not guarantee the availability of stock. If the item is out of stock again, you will need to re-add yourself to the waitlist.', 'commercegurus-commercekit' ),
		'wtl_not_stock_limit'    => '0',
		'waitlist_admin_mail'    => '1',
		'wtl_admin_subject'      => esc_html__( 'You have a new waiting list request on {site_name}', 'commercegurus-commercekit' ),
		'wtl_admin_content'      => esc_html__( "Hi,\r\nYou got a waiting list request from {site_name} ({site_url}) for the following:\r\nCustomer email: {customer_email}\r\nProduct Name: {product_title}, SKU: {product_sku}\r\nProduct link: {product_link}", 'commercegurus-commercekit' ),
		'waitlist_user_mail'     => '1',
		'wtl_user_subject'       => esc_html__( 'We have received your waiting list request', 'commercegurus-commercekit' ),
		'wtl_user_content'       => esc_html__( "Hi,\r\nWe have received your waiting list request from {site_name} for the following:\r\nProduct Name: {product_title}, SKU: {product_sku}\r\nProduct link: {product_link}\r\n\r\nWe will send you an email once this item is back in stock.", 'commercegurus-commercekit' ),

		'wishlist'               => '0',
		'wishlist_display'       => '1',
		'wsl_adtext'             => esc_html__( 'Add to wishlist', 'commercegurus-commercekit' ),
		'wsl_pdtext'             => esc_html__( 'Product added', 'commercegurus-commercekit' ),
		'wsl_brtext'             => esc_html__( 'Browse wishlist', 'commercegurus-commercekit' ),
		'wsl_page'               => '0',

		'widget_pos_wishlist'    => '0',
		'widget_pos_sizeguide'   => '0',
		'widget_pos_countdown'   => '0',
		'widget_pos_countdown2'  => '0',
		'widget_pos_stockmeter'  => '0',
		'widget_pos_pdp_gallery' => '0',
		'widget_pos_fsn'         => '0',
	);

	if ( '' !== $key ) {
		if ( isset( $defaults[ $key ] ) ) {
			return $defaults[ $key ];
		} else {
			return '';
		}
	}

	foreach ( $defaults as $dkey => $dvalue ) {
		if ( isset( $options[ $dkey ] ) ) {
			continue;
		} else {
			$options[ $dkey ] = $dvalue;
		}
	}

	return $options;
}

/**
 * Get product custom taxonomies
 */
function commercekit_get_product_custom_taxonomies() {
	global $cgkit_custom_taxonomies;
	if ( isset( $cgkit_custom_taxonomies ) ) {
		return $cgkit_custom_taxonomies;
	}
	$outputs    = array();
	$excludes   = array( 'product_type', 'product_visibility', 'product_cat', 'product_tag', 'product_shipping_class' );
	$taxonomies = get_object_taxonomies( 'product' );
	if ( count( $taxonomies ) ) {
		foreach ( $taxonomies as $tax_value ) {
			if ( in_array( $tax_value, $excludes, true ) ) {
				continue;
			}
			if ( 'pa_' === substr( $tax_value, 0, 3 ) ) {
				continue;
			}
			$taxonomy = get_taxonomy( $tax_value );
			if ( $taxonomy ) {
				$outputs[ $tax_value ] = $taxonomy->label;
			}
		}
	}
	$cgkit_custom_taxonomies = $outputs;
	return $cgkit_custom_taxonomies;
}

/**
 * Ajax get nonce.
 */
function commercekit_ajax_get_nonce() {
	$ajax           = array();
	$ajax['status'] = 1;
	$ajax['nonce']  = wp_create_nonce( 'commercekit_nonce' );
	$ajax['state']  = is_user_logged_in() ? 1 : 0;

	wp_send_json( $ajax );
}
add_action( 'wp_ajax_commercekit_get_nonce', 'commercekit_ajax_get_nonce' );
add_action( 'wp_ajax_nopriv_commercekit_get_nonce', 'commercekit_ajax_get_nonce' );

/**
 * Nonce footer script.
 */
function commercekit_nonce_footer_script() {
	wp_nonce_field( 'commercekit_nonce', 'commercekit_nonce' );
	$user_switched = 0;
	if ( isset( $_GET['user_switched'] ) && 'true' === $_GET['user_switched'] ) { // phpcs:ignore
		$user_switched = 1;
	}
	$nonce_value = isset( $_COOKIE['commercekit-nonce-value'] ) && ! empty( $_COOKIE['commercekit-nonce-value'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['commercekit-nonce-value'] ) ) : '';
	$nonce_state = isset( $_COOKIE['commercekit-nonce-state'] ) ? (int) sanitize_text_field( wp_unslash( $_COOKIE['commercekit-nonce-state'] ) ) : 0;
	if ( 1 === $nonce_state && ! empty( $nonce_value ) ) {
		$user_nonce = wp_create_nonce( 'commercekit_nonce' );
		if ( $nonce_value !== $user_nonce ) {
			$user_switched = 1;
		}
	}
	?>
<script type="text/javascript">
/* <![CDATA[ */
document.addEventListener( 'DOMContentLoaded', function() {
	var cgkit_nonce_ustate = <?php echo is_user_logged_in() ? 1 : 0; ?>;
	var cgkit_nonce_cvalue = Cookies.get( 'commercekit-nonce-value' );
	var cgkit_nonce_cstate = Cookies.get( 'commercekit-nonce-state' );
	var cgkit_user_switched = <?php echo esc_attr( $user_switched ); ?>;
	if ( cgkit_nonce_cvalue == '' || cgkit_nonce_cstate != cgkit_nonce_ustate || cgkit_user_switched == 1 ) {
		var timestamp = new Date().getTime();
		fetch( commercekit_ajs.ajax_url + '=commercekit_get_nonce&v=' + timestamp, {
			method: 'GET',
		} ).then( response => response.json() ).then( json => {
			if ( json.status == 1 ) {
				var twohrs = new Date( new Date().getTime() + 120 * 60 * 1000 );
				if ( window.Cookiebot ) { /* Cookiebot compatible */
					if ( window.Cookiebot.consent.preferences || window.Cookiebot.consent.statistics || window.Cookiebot.consent.marketing ) {
						Cookies.set( 'commercekit-nonce-value', json.nonce, { expires: twohrs } );
						Cookies.set( 'commercekit-nonce-state', json.state, { expires: twohrs } );
					} else {
						Cookies.remove( 'commercekit-nonce-value' );
						Cookies.remove( 'commercekit-nonce-state' );
					}
				} else {
					Cookies.set( 'commercekit-nonce-value', json.nonce, { expires: twohrs } );
					Cookies.set( 'commercekit-nonce-state', json.state, { expires: twohrs } );
				}
				cgkit_nonce_ustate = json.state;
				commercekit_update_nonce( json.nonce );
			}
		} );
	} else {
		commercekit_update_nonce( cgkit_nonce_cvalue );
	}
} );
function commercekit_update_nonce( nonce ) {
	var nonce_input = document.querySelector( '#commercekit_nonce' );
	if ( nonce_input ) {
		nonce_input.value = nonce;
	} else {
		document.body.insertAdjacentHTML( 'beforeend', '<input type="hidden" id="commercekit_nonce" name="commercekit_nonce" value="' + nonce + '" />' );
	}
	commercekit_ajs.ajax_nonce = 1;
	if ( typeof cgkit_update_order_bump_views == 'function' ) {
		cgkit_update_order_bump_views();
	}
}
/* ]]> */
</script>
	<?php
}
add_action( 'wp_footer', 'commercekit_nonce_footer_script' );

/**
 * Add elementor widget categories
 *
 * @param  string $elements_manager elements manager.
 */
function commercekit_add_elementor_widget_categories( $elements_manager ) {
	$elements_manager->add_category(
		'commercekit',
		array(
			'title' => esc_html__( 'CommerceKit', 'commercegurus-commercekit' ),
			'icon'  => 'fa fa-plug',
		)
	);
}
add_action( 'elementor/elements/categories_registered', 'commercekit_add_elementor_widget_categories' );

/**
 * Stock meter elementor widget
 *
 * @param  string $widgets_manager widgets manager object.
 */
function commercekit_product_gallery_elementor_widget( $widgets_manager ) {
	require_once CGKIT_BASE_PATH . 'includes/elementor/class-commercekit-product-gallery-elementor.php';
	$widgets_manager->register( new Commercekit_Product_Gallery_Elementor() );
}

/**
 * Polylang get translated post ID
 *
 * @param  string $post_id post id.
 * @param  string $lang    target language.
 */
function commercekit_pll_get_post( $post_id, $lang ) {
	if ( function_exists( 'pll_get_post' ) ) {
		$npost_id = pll_get_post( $post_id, $lang );
		if ( false !== $npost_id ) {
			$post_id = $npost_id;
		}
	}

	return $post_id;
}

/**
 * Polylang get translated term ID
 *
 * @param  string $term_id term id.
 * @param  string $lang    target language.
 */
function commercekit_pll_get_term( $term_id, $lang ) {
	if ( function_exists( 'pll_get_term' ) ) {
		$nterm_id = pll_get_term( $term_id, $lang );
		if ( false !== $nterm_id ) {
			$term_id = $nterm_id;
		}
	}

	return $term_id;
}

/**
 * WPML Translation with 'Translate' Method
 *
 * @param  string $target_post_id target post id.
 * @param  string $post_array     post array.
 * @param  string $wpml_job       wpml job.
 */
function commercekit_wpml_pro_translation_completed( $target_post_id, $post_array, $wpml_job ) {
	$target_lang    = isset( $wpml_job->language_code ) ? $wpml_job->language_code : '';
	$source_lang    = isset( $wpml_job->source_language_code ) ? $wpml_job->source_language_code : '';
	$master_post_id = apply_filters( 'wpml_object_id', $target_post_id, get_post_type( $target_post_id ), true, $source_lang );
	$post_array     = array();

	if ( (int) $master_post_id === (int) $target_post_id || empty( $target_lang ) || empty( $source_lang ) ) {
		return;
	}
	if ( function_exists( 'commercegurus_attribute_swatches_wpml_make_duplicate' ) ) {
		commercegurus_attribute_swatches_wpml_make_duplicate( $master_post_id, $target_lang, $post_array, $target_post_id );
	}
	if ( function_exists( 'commercegurus_attributes_gallary_wpml_make_duplicate' ) ) {
		commercegurus_attributes_gallary_wpml_make_duplicate( $master_post_id, $target_lang, $post_array, $target_post_id );
	}
	if ( function_exists( 'commercegurus_gallary_wpml_make_duplicate' ) ) {
		commercegurus_gallary_wpml_make_duplicate( $master_post_id, $target_lang, $post_array, $target_post_id );
	}
	if ( function_exists( 'commercekit_sg_wpml_make_duplicate' ) ) {
		commercekit_sg_wpml_make_duplicate( $master_post_id, $target_lang, $post_array, $target_post_id );
	}
}
add_action( 'wpml_pro_translation_completed', 'commercekit_wpml_pro_translation_completed', 10, 3 );

/**
 * Get after and before of mini cart blocks
 */
function commercekit_get_mini_cart_blocks() {
	$flags       = commercekit_feature_flags()->get_flags();
	$before_cart = '';
	$after_cart  = '';

	$enable_fsn_mini_cart = isset( $flags['fsn_mini_cart'] ) && 1 === (int) $flags['fsn_mini_cart'] ? true : false;
	if ( $enable_fsn_mini_cart ) {
		$before_cart = commercekit_free_shipping_notification( 'mini-cart', true );
	}

	$enable_obp_mini_cart = isset( $flags['order_bump_mini'] ) && 1 === (int) $flags['order_bump_mini'] ? true : false;
	if ( $enable_obp_mini_cart ) {
		$after_cart = commercekit_show_order_bumps( 'minicart', true );
	}

	wp_send_json_success(
		array(
			'before_cart' => $before_cart,
			'after_cart'  => $after_cart,
		)
	);
}
add_action( 'wp_ajax_commercekit_mini_cart_blocks', 'commercekit_get_mini_cart_blocks' );
add_action( 'wp_ajax_nopriv_commercekit_mini_cart_blocks', 'commercekit_get_mini_cart_blocks' );

/**
 * After and before of mini cart blocks scripts
 */
function commercekit_mini_cart_blocks_script() {
	$flags      = commercekit_feature_flags()->get_flags();
	$enable_fsn = isset( $flags['fsn_mini_cart'] ) && 1 === (int) $flags['fsn_mini_cart'] ? true : false;
	$enable_obp = isset( $flags['order_bump_mini'] ) && 1 === (int) $flags['order_bump_mini'] ? true : false;
	if ( ! $enable_fsn && ! $enable_obp ) {
		return;
	}
	?>
<script type="text/javascript">
/* <![CDATA[ */
var cgkit_wbmc = document.querySelector( '.wc-block-mini-cart' );
if ( cgkit_wbmc ) {
	var cgkitMCBCurrentRequest = null;
	var cgkitMCBCurrentCancel = null;
	function cgkitLoadMiniCartBlocks() {
		var cgkit_drawer = document.querySelector( '.wc-block-mini-cart__drawer' );
		if ( ! cgkit_drawer ) {
			return;
		}
		clearTimeout( cgkitMCBCurrentRequest );
		cgkitMCBCurrentRequest = setTimeout( function() {
			if ( cgkitMCBCurrentCancel ) {
				cgkitMCBCurrentCancel.abort();
			}
			cgkitMCBCurrentCancel = new AbortController();
			var timestamp = new Date().getTime();
			fetch( commercekit_ajs.ajax_url + '=commercekit_mini_cart_blocks&v=' + timestamp, {
				signal: cgkitMCBCurrentCancel.signal,
				method: 'GET',
			} ).then( response => response.json() ).then( json => {
				if ( json.success ) {
					var cgkit_table = document.querySelector( '.wc-block-mini-cart__drawer .wc-block-mini-cart__items' );
					var cgkit_before = document.querySelector( '#cgkit-before-mini-cart-blocks' );
					if ( cgkit_before ) {
						cgkit_before.innerHTML = json.data.before_cart;
					} else if ( cgkit_table ) {
						cgkit_table.insertAdjacentHTML( 'afterbegin', '<div id="cgkit-before-mini-cart-blocks">' + json.data.before_cart + '</div>' );
					}
					var cgkit_after = document.querySelector( '#cgkit-after-mini-cart-blocks' );
					if ( cgkit_after ) {
						cgkit_after.innerHTML = json.data.after_cart;
					} else if ( cgkit_table ) {
						cgkit_table.insertAdjacentHTML( 'beforeend', '<div id="cgkit-after-mini-cart-blocks">' + json.data.after_cart + '</div>' );
					}
				}
			} ).catch( function( e ) { } );
		}, 100 );
	}
	document.addEventListener( 'click', function( e ) {
		$this = e.target;
		$thisp = $this.closest( '.wc-block-mini-cart' );
		if ( $this.classList.contains( 'wc-block-mini-cart' ) || $thisp ) {
			setTimeout( function() {
				cgkitLoadMiniCartBlocks();
			}, 500 );
		}
	} );
	( function() {
		const originalFetch = window.fetch;
		window.fetch = async function(...args) {
			const response = await originalFetch(...args);
			if ( args[0].includes( '/wc/store/v1/batch' ) || args[0].includes( '/wc/store/v1/cart' ) ) {
				cgkitLoadMiniCartBlocks();
			}
			return response;
		};
	} )();
}
/* ]]> */
</script>
	<?php
}
add_action( 'wp_footer', 'commercekit_mini_cart_blocks_script' );

/**
 * Convert Size Guide post meta into custom table
 */
function commercekit_sg_convert_post_meta() {
	global $wpdb;
	$converted = (int) get_option( 'commercekit_sg_converted', 0 );
	if ( 1 === $converted ) {
		return;
	}
	$sg_sql = 'SELECT p.* FROM ' . $wpdb->prefix . 'posts AS p WHERE p.post_type = \'ckit_size_guide\' ORDER BY p.ID ASC';
	$rows   = $wpdb->get_results( $sg_sql, ARRAY_A ); // phpcs:ignore
	if ( is_array( $rows ) && count( $rows ) ) {
		$table_name = $wpdb->prefix . 'commercekit_sg_post_meta';
		foreach ( $rows as $row ) {
			$post_id    = $row['ID'];
			$active     = 'publish' === $row['post_status'] ? 1 : 0;
			$products   = array_filter( explode( ',', get_post_meta( $post_id, 'commercekit_sg_prod', true ) ) );
			$categories = array_filter( explode( ',', get_post_meta( $post_id, 'commercekit_sg_cat', true ) ) );
			$tags       = array_filter( explode( ',', get_post_meta( $post_id, 'commercekit_sg_tag', true ) ) );
			$attributes = array_filter( explode( ',', get_post_meta( $post_id, 'commercekit_sg_attr', true ) ) );
			$total_rows = max( count( $products ), count( $categories ), count( $tags ), count( $attributes ) );
			if ( $total_rows ) {
				for ( $i = 0; $i < $total_rows; $i++ ) {
					$data   = array(
						'post_id' => $post_id,
						'active'  => $active,
						'sg_prod' => isset( $products[ $i ] ) ? intval( $products[ $i ] ) : 0,
						'sg_cat'  => isset( $categories[ $i ] ) ? intval( $categories[ $i ] ) : 0,
						'sg_tag'  => isset( $tags[ $i ] ) ? intval( $tags[ $i ] ) : 0,
						'sg_attr' => isset( $attributes[ $i ] ) ? $attributes[ $i ] : '',
					);
					$format = array( '%d', '%d', '%d', '%d', '%d', '%s' );
					$wpdb->insert( $table_name, $data, $format ); // db call ok; no-cache ok.
				}
			}
		}
	}
	update_option( 'commercekit_sg_converted', 1, false );
}

require_once dirname( __FILE__ ) . '/commercegurus-attributes-gallery-functions.php';
require_once dirname( __FILE__ ) . '/admin-attribute-swatches.php';

/**
 * Commercekit load all features.
 */
function commercekit_load_all_features() {
	$commercekit_flags      = commercekit_feature_flags()->get_flags();
	$enable_inventory_bar   = isset( $commercekit_flags['inventory_display'] ) && 1 === (int) $commercekit_flags['inventory_display'] ? 1 : 0;
	$enable_countdown_timer = isset( $commercekit_flags['countdown_timer'] ) && 1 === (int) $commercekit_flags['countdown_timer'] ? 1 : 0;
	$enable_ajax_search     = isset( $commercekit_flags['ajax_search'] ) && 1 === (int) $commercekit_flags['ajax_search'] ? 1 : 0;
	$enable_waitlist        = isset( $commercekit_flags['waitlist'] ) && 1 === (int) $commercekit_flags['waitlist'] ? 1 : 0;
	$enable_order_bump      = isset( $commercekit_flags['order_bump'] ) && 1 === (int) $commercekit_flags['order_bump'] ? 1 : 0;
	$enable_order_bump_mini = isset( $commercekit_flags['order_bump_mini'] ) && 1 === (int) $commercekit_flags['order_bump_mini'] ? 1 : 0;
	$enable_wishlist        = isset( $commercekit_flags['wishlist'] ) && 1 === (int) $commercekit_flags['wishlist'] ? 1 : 0;
	$enable_pdp_triggers    = isset( $commercekit_flags['pdp_triggers'] ) && 1 === (int) $commercekit_flags['pdp_triggers'] ? 1 : 0;
	$enable_attr_swatches   = isset( $commercekit_flags['attribute_swatches'] ) && 1 === (int) $commercekit_flags['attribute_swatches'] ? 1 : 0;
	$enable_attr_swatches   = apply_filters( 'commercekit_module_attribute_swatches_visible', $enable_attr_swatches );
	$enable_sticky_atc_desk = defined( 'COMMERCEKIT_STICKY_ATC_DESKTOP_VISIBLE' ) ? COMMERCEKIT_STICKY_ATC_DESKTOP_VISIBLE : ( isset( $commercekit_flags['sticky_atc_desktop'] ) && 1 === (int) $commercekit_flags['sticky_atc_desktop'] ? 1 : 0 );
	$enable_sticky_atc_desk = apply_filters( 'commercekit_module_sticky_atc_desktop_visible', $enable_sticky_atc_desk );
	$enable_sticky_atc_mobi = defined( 'COMMERCEKIT_STICKY_ATC_MOBILE_VISIBLE' ) ? COMMERCEKIT_STICKY_ATC_MOBILE_VISIBLE : ( isset( $commercekit_flags['sticky_atc_mobile'] ) && 1 === (int) $commercekit_flags['sticky_atc_mobile'] ? 1 : 0 );
	$enable_sticky_atc_mobi = apply_filters( 'commercekit_module_sticky_atc_mobile_visible', $enable_sticky_atc_mobi );
	$enable_sticky_atc_tabs = defined( 'COMMERCEKIT_STICKY_ATC_TABS_VISIBLE' ) ? COMMERCEKIT_STICKY_ATC_TABS_VISIBLE : ( isset( $commercekit_flags['sticky_atc_tabs'] ) && 1 === (int) $commercekit_flags['sticky_atc_tabs'] ? 1 : 0 );
	$enable_sticky_atc_tabs = apply_filters( 'commercekit_module_sticky_atc_tabs_visible', $enable_sticky_atc_tabs );
	$enable_fsn_cart_page   = isset( $commercekit_flags['fsn_cart_page'] ) && 1 === (int) $commercekit_flags['fsn_cart_page'] ? 1 : 0;
	$enable_fsn_mini_cart   = isset( $commercekit_flags['fsn_mini_cart'] ) && 1 === (int) $commercekit_flags['fsn_mini_cart'] ? 1 : 0;
	$enable_widget_pos_fsn  = isset( $commercekit_flags['widget_pos_fsn'] ) && 1 === (int) $commercekit_flags['widget_pos_fsn'] ? 1 : 0;
	$enable_size_guide      = isset( $commercekit_flags['size_guide'] ) && 1 === (int) $commercekit_flags['size_guide'] ? 1 : 0;
	$enable_store_badge     = defined( 'COMMERCEKIT_STORE_BADGES_VISIBLE' ) ? COMMERCEKIT_STORE_BADGES_VISIBLE : ( isset( $commercekit_flags['store_badge'] ) && 1 === (int) $commercekit_flags['store_badge'] ? 1 : 0 );
	$enable_store_badge     = apply_filters( 'commercekit_module_store_badges_visible', $enable_store_badge );

	if ( $enable_inventory_bar ) {
		require_once dirname( __FILE__ ) . '/module-inventory-bar.php';
	}
	if ( $enable_countdown_timer ) {
		require_once dirname( __FILE__ ) . '/module-countdown-timer.php';
	}
	if ( $enable_ajax_search ) {
		require_once dirname( __FILE__ ) . '/module-ajax-search.php';
		require_once dirname( __FILE__ ) . '/class-commercekit-ajs-index.php';
	}
	if ( $enable_waitlist ) {
		require_once dirname( __FILE__ ) . '/module-waitlist.php';
	}
	if ( $enable_order_bump || $enable_order_bump_mini ) {
		require_once dirname( __FILE__ ) . '/module-order-bump.php';
	}
	if ( $enable_wishlist ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( is_plugin_active( 'yith-woocommerce-wishlist/init.php' ) ) {
			global $commerce_gurus_commercekit, $pagenow;
			include_once ABSPATH . 'wp-includes/pluggable.php';
			$cpage = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
			if ( 'admin.php' === $pagenow && 'commercekit' === $cpage ) {
				$commerce_gurus_commercekit->add_admin_notice( 'bad_wishlist', 'error', esc_html__( 'You will need to first disable the YITH Wishlist plugin in order to use the CommerceKit Wishlist feature.', 'commercegurus-commercekit' ) );
			}
		} else {
			require_once dirname( __FILE__ ) . '/module-wishlist.php';
		}
	}
	if ( $enable_pdp_triggers ) {
		require_once dirname( __FILE__ ) . '/module-pdp-triggers.php';
	}

	if ( $enable_attr_swatches ) {
		require_once dirname( __FILE__ ) . '/module-attribute-swatches.php';
	}
	if ( $enable_sticky_atc_desk || $enable_sticky_atc_mobi || $enable_sticky_atc_tabs ) {
		require_once dirname( __FILE__ ) . '/module-sticky-atc-bar.php';
	}
	if ( $enable_fsn_cart_page || $enable_fsn_mini_cart || $enable_widget_pos_fsn ) {
		require_once dirname( __FILE__ ) . '/module-free-shipping-notification.php';
	}
	if ( $enable_size_guide ) {
		require_once dirname( __FILE__ ) . '/module-size-guide.php';
	}
	if ( $enable_store_badge ) {
		require_once dirname( __FILE__ ) . '/module-badge.php';
	}
}
add_action( 'plugins_loaded', 'commercekit_load_all_features', 999 );

require_once dirname( __FILE__ ) . '/class-commercekit-exporter.php';
require_once dirname( __FILE__ ) . '/class-commercekit-clear-cache-command.php';
require_once dirname( __FILE__ ) . '/class-commercekit-cli-command.php';
