<?php
/**
 *
 * Wishlist module
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

/**
 * Ajax save wishlist
 */
function commercekit_ajax_save_wishlist() {
	global $wpdb;
	$commercekit_options = get_option( 'commercekit', array() );

	$ajax            = array();
	$ajax['status']  = 0;
	$ajax['message'] = esc_html__( 'Cannot add this item to the wishlist', 'commercegurus-commercekit' );
	$ajax['html']    = '';

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_nonce' ) ) {
		$ajax['message'] = esc_html__( 'Cannot add this item to the wishlist due to nonce error', 'commercegurus-commercekit' );
		wp_send_json( $ajax );
	}

	$table = $wpdb->prefix . 'commercekit_wishlist';
	$pid   = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0;
	$type  = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'page';
	$uid   = (int) get_current_user_id();
	$key   = isset( $_COOKIE['commercekit_wishlist'] ) && ! empty( $_COOKIE['commercekit_wishlist'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['commercekit_wishlist'] ) ) : md5( microtime( true ) );
	$sid   = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_wishlist WHERE session_key = %s', $key ) ); // db call ok; no-cache ok.
	if ( ! $uid && ! $sid ) {
		$table  = $wpdb->prefix . 'commercekit_wishlist';
		$data   = array(
			'session_key' => $key,
		);
		$format = array( '%s' );
		$wpdb->insert( $table, $data, $format ); // db call ok; no-cache ok.
		$sid = $wpdb->insert_id;
		setcookie( 'commercekit_wishlist', $key, time() + ( 365 * 24 * 3600 ), '/' );
	} elseif ( $uid && $sid ) {
		$table  = $wpdb->prefix . 'commercekit_wishlist_items';
		$data   = array(
			'user_id' => $uid,
			'list_id' => 0,
		);
		$where  = array(
			'list_id' => $sid,
		);
		$format = array( '%d', '%d' );
		$wpdb->update( $table, $data, $where, $format ); // db call ok; no-cache ok.

		$table  = $wpdb->prefix . 'commercekit_wishlist';
		$data   = array(
			'id' => $sid,
		);
		$format = array( '%d' );
		$wpdb->delete( $table, $data, $format ); // db call ok; no-cache ok.
		setcookie( 'commercekit_wishlist', $key, time() - ( 365 * 24 * 3600 ), '/' );
		$sid = 0;
	}

	if ( $pid && ( $uid || $sid ) ) {
		$wid = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_wishlist_items WHERE user_id = %d AND list_id = %d AND product_id = %d', $uid, $sid, $pid ) ); // db call ok; no-cache ok.
		if ( ! $wid ) {
			$table  = $wpdb->prefix . 'commercekit_wishlist_items';
			$data   = array(
				'user_id'    => $uid,
				'list_id'    => $sid,
				'product_id' => $pid,
				'created'    => time(),
			);
			$format = array( '%d', '%d', '%d', '%d' );
			$wpdb->insert( $table, $data, $format ); // db call ok; no-cache ok.

			$wsls_reset = (int) get_option( 'commercekit_wsls_reset' );
			if ( 0 === $wsls_reset ) {
				update_option( 'commercekit_wsls_reset', time(), false );
			}
			$wsls_total = (int) get_option( 'commercekit_wsls_total' );
			update_option( 'commercekit_wsls_total', ( $wsls_total + 1 ), false );
		}

		$wsl_adtext     = isset( $commercekit_options['wsl_adtext'] ) && ! empty( $commercekit_options['wsl_adtext'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wsl_adtext'] ) ) : commercekit_get_default_settings( 'wsl_adtext' );
		$wsl_brtext     = isset( $commercekit_options['wsl_brtext'] ) && ! empty( $commercekit_options['wsl_brtext'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wsl_brtext'] ) ) : commercekit_get_default_settings( 'wsl_brtext' );
		$wsl_pdtext     = isset( $commercekit_options['wsl_pdtext'] ) && ! empty( $commercekit_options['wsl_pdtext'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wsl_pdtext'] ) ) : commercekit_get_default_settings( 'wsl_pdtext' );
		$wsl_page       = isset( $commercekit_options['wsl_page'] ) ? esc_attr( $commercekit_options['wsl_page'] ) : 0;
		$wsl_page_link  = get_permalink( $wsl_page );
		$ajax['status'] = 1;
		if ( 'page' === $type ) {
			$ajax['html'] = '<a href="' . $wsl_page_link . '" class="commercekit-browse-wishlist" title="' . esc_html__( 'Wishlist', 'commercegurus-commercekit' ) . '"><em class="cg-wishlist-t"></em><span>' . $wsl_brtext . '</span></a>';
		} else {
			$ajax['html'] = '<a href="' . $wsl_page_link . '" class="commercekit-browse-wishlist" title="' . esc_html__( 'Wishlist', 'commercegurus-commercekit' ) . '"><em class="cg-wishlist-t"></em><span></span></a>';
		}
		$ajax['message'] = $wsl_pdtext . '';
	}

	wp_send_json( $ajax );
}

add_action( 'wp_ajax_commercekit_save_wishlist', 'commercekit_ajax_save_wishlist' );
add_action( 'wp_ajax_nopriv_commercekit_save_wishlist', 'commercekit_ajax_save_wishlist' );

/**
 * Convert saved wishlist
 *
 * @param  string $login of user.
 */
function commercekit_convert_saved_wishlist( $login ) {
	global $wpdb;
	$table = $wpdb->prefix . 'commercekit_wishlist';
	$user  = get_user_by( 'login', $login );
	$uid   = isset( $user->ID ) ? $user->ID : 0;
	$key   = isset( $_COOKIE['commercekit_wishlist'] ) && ! empty( $_COOKIE['commercekit_wishlist'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['commercekit_wishlist'] ) ) : '';
	$sid   = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_wishlist WHERE session_key = %s', $key ) ); // db call ok; no-cache ok.
	if ( $uid && $sid ) {
		$table  = $wpdb->prefix . 'commercekit_wishlist_items';
		$data   = array(
			'user_id' => $uid,
			'list_id' => 0,
		);
		$where  = array(
			'list_id' => $sid,
		);
		$format = array( '%d', '%d' );
		$wpdb->update( $table, $data, $where, $format ); // db call ok; no-cache ok.

		$table  = $wpdb->prefix . 'commercekit_wishlist';
		$data   = array(
			'id' => $sid,
		);
		$format = array( '%d' );
		$wpdb->delete( $table, $data, $format ); // db call ok; no-cache ok.
		setcookie( 'commercekit_wishlist', $key, time() - ( 365 * 24 * 3600 ), '/' );
	}
}
add_action( 'wp_login', 'commercekit_convert_saved_wishlist', 99 );

/**
 * Ajax remove wishlist
 */
function commercekit_ajax_remove_wishlist() {
	global $wpdb;
	$commercekit_options = get_option( 'commercekit', array() );

	$ajax            = array();
	$ajax['status']  = 0;
	$ajax['message'] = esc_html__( 'Cannot remove this item from the wishlist', 'commercegurus-commercekit' );
	$ajax['html']    = '';

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_nonce' ) ) {
		wp_send_json( $ajax );
	}

	$table  = $wpdb->prefix . 'commercekit_wishlist';
	$pid    = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0;
	$wpage  = isset( $_POST['wpage'] ) ? sanitize_text_field( wp_unslash( $_POST['wpage'] ) ) : 1;
	$reload = isset( $_POST['reload'] ) ? sanitize_text_field( wp_unslash( $_POST['reload'] ) ) : 0;
	$uid    = get_current_user_id();
	$key    = isset( $_COOKIE['commercekit_wishlist'] ) && ! empty( $_COOKIE['commercekit_wishlist'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['commercekit_wishlist'] ) ) : md5( microtime( true ) );
	$sid    = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_wishlist WHERE session_key = %s', $key ) ); // db call ok; no-cache ok.

	if ( $pid && $uid ) {
		$table  = $wpdb->prefix . 'commercekit_wishlist_items';
		$data   = array(
			'user_id'    => $uid,
			'product_id' => $pid,
		);
		$format = array( '%d', '%d' );
		$wpdb->delete( $table, $data, $format ); // db call ok; no-cache ok.
	}
	if ( $pid && $sid ) {
		$table  = $wpdb->prefix . 'commercekit_wishlist_items';
		$data   = array(
			'list_id'    => $sid,
			'product_id' => $pid,
		);
		$format = array( '%d', '%d' );
		$wpdb->delete( $table, $data, $format ); // db call ok; no-cache ok.
	}

	$ajax['status'] = 1;
	$ajax['html']   = '<a href="#" role="button" class="commercekit-save-wishlist" data-product-id="' . $pid . '" data-type="list" title="' . esc_html__( 'Wishlist', 'commercegurus-commercekit' ) . '" aria-label="' . esc_html__( 'Wishlist', 'commercegurus-commercekit' ) . '"><em class="cg-wishlist"></em><span></span></a>';

	$ajax['message'] = esc_html__( 'Product removed', 'commercegurus-commercekit' );
	if ( $reload ) {
		$_REQUEST['wpage']  = $wpage;
		$_REQUEST['reload'] = 1;
		$ajax['html']       = do_shortcode( '[commercegurus_wishlist]' );
	}

	wp_send_json( $ajax );
}

add_action( 'wp_ajax_commercekit_remove_wishlist', 'commercekit_ajax_remove_wishlist' );
add_action( 'wp_ajax_nopriv_commercekit_remove_wishlist', 'commercekit_ajax_remove_wishlist' );

/**
 * Single Product Page - Display wishlist
 */
function commercekit_single_product_wishlist() {
	global $wpdb;
	global $product;
	$commercekit_wishlist = false;
	$commercekit_options  = get_option( 'commercekit', array() );
	$commercekit_flags    = commercekit_feature_flags()->get_flags();
	if ( isset( $commercekit_flags['wishlist'] ) && 1 === (int) $commercekit_flags['wishlist'] ) {
		$commercekit_wishlist = true;
	}
	if ( isset( $commercekit_options['wishlist_display'] ) && 2 === (int) $commercekit_options['wishlist_display'] ) {
		return; /* Display on catalog only */
	}
	$widget_pos_wishlist = isset( $commercekit_options['widget_pos_wishlist'] ) && 1 === (int) $commercekit_options['widget_pos_wishlist'] ? 1 : 0;
	if ( $commercekit_wishlist && function_exists( 'is_product' ) && is_product() && $product ) {
		$pid = $product->get_id();
		$uid = get_current_user_id();
		$key = isset( $_COOKIE['commercekit_wishlist'] ) && ! empty( $_COOKIE['commercekit_wishlist'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['commercekit_wishlist'] ) ) : '';
		$wid = 0;
		if ( $uid ) {
			$wid = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_wishlist_items WHERE user_id = %d AND product_id = %d', $uid, $pid ) ); // db call ok; no-cache ok.
		} elseif ( $key ) {
			$sid = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_wishlist WHERE session_key = %s', $key ) ); // db call ok; no-cache ok.
			if ( $sid ) {
				$wid = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_wishlist_items WHERE list_id = %d AND product_id = %d', $sid, $pid ) ); // db call ok; no-cache ok.
			}
		}

		$wrap_class = '';
		if ( 1 === $widget_pos_wishlist ) {
			$wrap_class = 'cgkit-wishlist-shortcode';
		}

		if ( $wid ) {
			$wsl_brtext    = isset( $commercekit_options['wsl_brtext'] ) && ! empty( $commercekit_options['wsl_brtext'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wsl_brtext'] ) ) : commercekit_get_default_settings( 'wsl_brtext' );
			$wsl_page      = isset( $commercekit_options['wsl_page'] ) ? esc_attr( $commercekit_options['wsl_page'] ) : 0;
			$wsl_page_link = get_permalink( $wsl_page );
			$html          = '<div class="commercekit-wishlist full ' . $wrap_class . '" data-product-id="' . $pid . '"><a href="' . $wsl_page_link . '" class="commercekit-browse-wishlist" title="' . esc_html__( 'Wishlist', 'commercegurus-commercekit' ) . '"><em class="cg-wishlist"></em><span>' . $wsl_brtext . '</span></a></div>';
		} else {
			$wsl_adtext = isset( $commercekit_options['wsl_adtext'] ) && ! empty( $commercekit_options['wsl_adtext'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wsl_adtext'] ) ) : commercekit_get_default_settings( 'wsl_adtext' );
			$html       = '<div class="commercekit-wishlist full ' . $wrap_class . '" data-product-id="' . $pid . '"><a href="#" role="button" class="commercekit-save-wishlist" data-product-id="' . $pid . '" data-type="page" title="' . esc_html__( 'Wishlist', 'commercegurus-commercekit' ) . '"><em class="cg-wishlist"></em><span>' . $wsl_adtext . '</span></a></div>';
		}

		if ( 1 === $widget_pos_wishlist ) {
			return $html;
		} else {
			commercekit_module_output( $html );
		}
	}
}

/**
 * Wishlist elementor widget
 *
 * @param  string $widgets_manager widgets manager object.
 */
function commercekit_wishlist_elementor_widget( $widgets_manager ) {
	require_once CGKIT_BASE_PATH . 'includes/elementor/class-commercekit-wishlist-elementor.php';
	$widgets_manager->register( new Commercekit_Wishlist_Elementor() );
}

$commercekit_options = get_option( 'commercekit', array() );
if ( isset( $commercekit_options['widget_pos_wishlist'] ) && 1 === (int) $commercekit_options['widget_pos_wishlist'] ) {
	add_shortcode( 'commercekit_wishlist', 'commercekit_single_product_wishlist' );
	add_action( 'elementor/widgets/register', 'commercekit_wishlist_elementor_widget' );
} else {
	add_action( 'woocommerce_single_product_summary', 'commercekit_single_product_wishlist', 38 );
}

/**
 * Shop Page - Display wishlist
 */
function commercekit_after_shop_loop_item_wishlist() {
	global $wpdb, $product, $cgkit_wishlist_loop, $cgkit_wishlist_page;
	$commercekit_wishlist = false;
	$commercekit_options  = get_option( 'commercekit', array() );
	$commercekit_flags    = commercekit_feature_flags()->get_flags();
	if ( isset( $commercekit_flags['wishlist'] ) && 1 === (int) $commercekit_flags['wishlist'] ) {
		$commercekit_wishlist = true;
	}
	if ( isset( $commercekit_options['wishlist_display'] ) && 3 === (int) $commercekit_options['wishlist_display'] ) {
		return; /* Display on product pages only */
	}
	if ( $commercekit_wishlist ) {
		$pid = $product->get_id();
		$uid = get_current_user_id();
		$key = isset( $_COOKIE['commercekit_wishlist'] ) && ! empty( $_COOKIE['commercekit_wishlist'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['commercekit_wishlist'] ) ) : '';
		$wid = 0;
		if ( $uid ) {
			$wid = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_wishlist_items WHERE user_id = %d AND product_id = %d', $uid, $pid ) ); // db call ok; no-cache ok.
		} elseif ( $key ) {
			$sid = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_wishlist WHERE session_key = %s', $key ) ); // db call ok; no-cache ok.
			if ( $sid ) {
				$wid = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_wishlist_items WHERE list_id = %d AND product_id = %d', $sid, $pid ) ); // db call ok; no-cache ok.
			}
		}
		$wsl_page      = isset( $commercekit_options['wsl_page'] ) ? esc_attr( $commercekit_options['wsl_page'] ) : 0;
		$wsl_page_link = get_permalink( $wsl_page );

		if ( $wid ) {
			if ( isset( $cgkit_wishlist_loop ) && true === $cgkit_wishlist_loop ) {
				$html = '<div class="commercekit-wishlist mini no-wsl-update"><a href="#" class="commercekit-remove-wishlist2 wsl-remove" data-product-id="' . $pid . '" title="' . esc_html__( 'Wishlist', 'commercegurus-commercekit' ) . '" data-wpage="' . ( isset( $cgkit_wishlist_page ) ? esc_attr( $cgkit_wishlist_page ) : 1 ) . '">x</a></div>';
			} else {
				$html = '<div class="commercekit-wishlist mini" data-product-id="' . $pid . '"><a href="' . $wsl_page_link . '" class="commercekit-browse-wishlist" title="' . esc_html__( 'Wishlist', 'commercegurus-commercekit' ) . '"><em class="cg-wishlist-t"></em><span></span></a></div>';
			}
		} else {
			$html = '<div class="commercekit-wishlist mini" data-product-id="' . $pid . '"><a href="#" role="button" class="commercekit-save-wishlist" data-product-id="' . $pid . '" data-type="list" title="' . esc_html__( 'Wishlist', 'commercegurus-commercekit' ) . '" aria-label="' . esc_html__( 'Wishlist', 'commercegurus-commercekit' ) . '"><em class="cg-wishlist"></em><span></span></a></div>';
		}

		commercekit_module_output( $html );
	}
}

add_action( 'woocommerce_before_shop_loop_item', 'commercekit_after_shop_loop_item_wishlist', 0 );

/**
 * Commercegurus wishlist shortcode
 */
function commercekit_shortcode_wishlist() {
	global $wp, $wpdb, $product, $cgkit_wishlist_loop, $cgkit_wishlist_page;
	$commercekit_wishlist = false;
	$commercekit_options  = get_option( 'commercekit', array() );
	$commercekit_flags    = commercekit_feature_flags()->get_flags();
	if ( isset( $commercekit_flags['wishlist'] ) && 1 === (int) $commercekit_flags['wishlist'] ) {
		$commercekit_wishlist = true;
	}
	if ( ! $commercekit_wishlist ) {
		return;
	}
	$uid = get_current_user_id();
	$key = isset( $_COOKIE['commercekit_wishlist'] ) && ! empty( $_COOKIE['commercekit_wishlist'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['commercekit_wishlist'] ) ) : '';
	$sid = -1;
	if ( ! $uid && $key ) {
		$sid = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_wishlist WHERE session_key = %s', $key ) ); // db call ok; no-cache ok.
		if ( $sid ) {
			$uid = -1;
		}
	}
	if ( ! $uid ) {
		$uid = -1;
	}
	if ( ! $sid ) {
		$sid = -1;
	}

	$permalink = get_option( 'permalink_structure' );
	if ( empty( $permalink ) ) {
		$pg_url = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
	} else {
		$pg_url = home_url( $wp->request );
		if ( '/' === substr( $permalink, -1 ) ) {
			$pg_url = rtrim( $pg_url, '/' ) . '/';
		}
	}

	$total  = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'commercekit_wishlist_items WHERE user_id = %d OR list_id = %d', $uid, $sid ) ); // db call ok; no-cache ok.
	$offset = 0;
	$limit  = 24;
	$wpage  = isset( $_REQUEST['wpage'] ) ? sanitize_text_field( (int) $_REQUEST['wpage'] ) : 1; // phpcs:ignore
	$reload = isset( $_REQUEST['reload'] ) ? sanitize_text_field( (int) $_REQUEST['reload'] ) : 0; // phpcs:ignore
	$wpage  = $wpage ? $wpage : 1;
	$wpages = ceil( $total / $limit );
	if ( $wpages && $wpage > $wpages ) {
		$wpage = $wpages;
	}
	$offset = ( $wpage - 1 ) * $limit;
	$rows   = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'commercekit_wishlist_items WHERE user_id = %d OR list_id = %d ORDER BY created DESC LIMIT %d, %d', $uid, $sid, $offset, $limit ), ARRAY_A ); // db call ok; no-cache ok.
	$flink  = '';
	$plink  = '';
	$nlink  = '';
	$llink  = '';
	if ( $wpage > 1 ) {
		$flink = add_query_arg( 'wpage', 1, $pg_url );
		$plink = add_query_arg( 'wpage', $wpage - 1, $pg_url );
	}
	if ( $wpages > 1 && $wpage < $wpages ) {
		$nlink = add_query_arg( 'wpage', $wpage + 1, $pg_url );
		$llink = add_query_arg( 'wpage', $wpages, $pg_url );
	}
	ob_start();
	?>
	<?php if ( ! $reload ) { ?>
	<div id="commercekit-wishlist-shortcode">
	<?php } ?>

	<div class="commercekit-wishlist"></div>
	<ul class="commercekit-wishlist-list products columns-4">
		<?php
		if ( is_array( $rows ) && count( $rows ) ) {
			$cgkit_global_product = $product;
			$cgkit_wishlist_loop  = true;
			$cgkit_wishlist_page  = $wpage;
			$original_post        = $GLOBALS['post'];
			$template_name        = '/templates/frontend-wishlist.php';
			$wishlist_template    = dirname( __FILE__ ) . $template_name;
			$child_theme_template = get_stylesheet_directory() . '/commercekit' . $template_name;
			$main_theme_template  = get_template_directory() . '/commercekit' . $template_name;
			if ( file_exists( $child_theme_template ) ) {
				$wishlist_template = $child_theme_template;
			} elseif ( file_exists( $main_theme_template ) ) {
				$wishlist_template = $main_theme_template;
			}
			foreach ( $rows as $row ) {
				$product = wc_get_product( $row['product_id'] );
				if ( ! $product ) {
					continue;
				}
				$cgkit_wishlist_id = $row['product_id'];
				$GLOBALS['post']   = get_post( $cgkit_wishlist_id ); // phpcs:ignore
				require $wishlist_template;
			}
			$cgkit_wishlist_loop = false;
			$product             = $cgkit_global_product;
			$GLOBALS['post']     = $original_post; // phpcs:ignore
		} else {
			$empty_wishlist = '
			<li class="wsl-no-products">
				<p class="wsl-no-icon">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.31802 6.31802C2.56066 8.07538 2.56066 10.9246 4.31802 12.682L12.0001 20.364L19.682 12.682C21.4393 10.9246 21.4393 8.07538 19.682 6.31802C17.9246 4.56066 15.0754 4.56066 13.318 6.31802L12.0001 7.63609L10.682 6.31802C8.92462 4.56066 6.07538 4.56066 4.31802 6.31802Z" stroke="#EDEDED" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"></path></svg></p>
				<h2 class="wsl-no-title">' . esc_html__( 'This wishlist is empty', 'commercegurus-commercekit' ) . '</h2>
				<p class="wsl-no-desc">' . esc_html__( 'Your wishlist is currently empty. Discover a variety of exciting products on our Shop page!', 'commercegurus-commercekit' ) . '</p>
				<a href="' . esc_url( get_permalink( get_option( 'woocommerce_shop_page_id' ) ) ) . '" class="button wsl-no-shop">' . esc_html__( 'Return to Shop', 'commercegurus-commercekit' ) . '</a>
			</li>';
			echo apply_filters( 'cgkit_wishlist_empty_markup', $empty_wishlist ); // phpcs:ignore
		}
		?>
	</ul> 
	<?php if ( $total ) { ?>
	<div class="tablenav-pages commercekit-wishlist-pages">
		<span class="displaying-num"><?php echo esc_html( $total ); ?> <?php esc_html_e( 'items', 'commercegurus-commercekit' ); ?></span>
			<div>
				<?php if ( $wpages > 1 ) { ?>
					<span class="pagination-links">
					<?php if ( '' !== $flink || '' !== $plink ) { ?>
						<a class="first-page" href="<?php echo esc_url( $flink ); ?>">
							<span aria-hidden="true">«</span>
						</a>
						<a class="first-page" href="<?php echo esc_url( $plink ); ?>">
							<span aria-hidden="true">‹</span>
						</a>
					<?php } else { ?>
						<span class="tablenav-pages-navspan disabled">«</span>
						<span class="tablenav-pages-navspan disabled">‹</span>
					<?php } ?>
					</span>
					<span class="paging-input">
						<?php esc_html_e( 'Page', 'commercegurus-commercekit' ); ?> <?php echo esc_html( $wpage ); ?>
						<span class="tablenav-paging-text"> <?php esc_html_e( 'of', 'commercegurus-commercekit' ); ?> <span class="total-pages"><?php echo esc_html( $wpages ); ?></span></span>
					</span>
					<?php if ( '' !== $nlink || '' !== $llink ) { ?>
						<a class="next-page" href="<?php echo esc_url( $nlink ); ?>">
							<span aria-hidden="true">›</span>
						</a>
						<a class="last-page" href="<?php echo esc_url( $llink ); ?>">
							<span aria-hidden="true">»</span>
						</a>
					<?php } else { ?>
						<span class="tablenav-pages-navspan disabled">›</span>
						<span class="tablenav-pages-navspan disabled">»</span>
					<?php } ?>
				<?php } ?>
			</div>
	</div>
	<?php } ?>
	<?php if ( ! $reload ) { ?>
	</div>
	<?php } ?>
	<?php
	$html = ob_get_contents();
	ob_clean();

	return apply_filters( 'cgkit_wishlist_shortcode_markup', $html ); // phpcs:ignore
}

add_shortcode( 'commercegurus_wishlist', 'commercekit_shortcode_wishlist' );

/**
 * Wishlist product loop class
 *
 * @param array  $classes array of classes.
 * @param string $product product object.
 */
function commercegurus_wishlist_loop_class( $classes, $product ) {
	global $cgkit_wishlist_loop;
	if ( isset( $cgkit_wishlist_loop ) && true === $cgkit_wishlist_loop ) {
		$classes[] = 'cgkit-wishlist-product';
	}

	return $classes;
}

add_filter( 'woocommerce_post_class', 'commercegurus_wishlist_loop_class', 10, 2 );

/**
 * Ajax wishlist add to cart.
 */
function commercekit_ajax_wishlist_addtocart() {
	$ajax            = array();
	$ajax['status']  = 0;
	$ajax['message'] = esc_html__( 'Error on adding to cart.', 'commercegurus-commercekit' );

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_nonce' ) ) {
		wp_send_json( $ajax );
	}

	$product_id   = isset( $_POST['product_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0;
	$variation_id = 0;
	$product      = wc_get_product( $product_id );

	if ( $product && $product->has_child() ) {
		$children_ids = $product->get_children();
		$variation_id = reset( $children_ids );
	}

	if ( WC()->cart->add_to_cart( $product_id, 1, $variation_id ) ) {
		$ajax['status']  = 1;
		$ajax['message'] = esc_html__( 'Sucessfully added to cart.', 'commercegurus-commercekit' );
	}

	wp_send_json( $ajax );
}

add_action( 'wp_ajax_commercekit_wishlist_addtocart', 'commercekit_ajax_wishlist_addtocart' );
add_action( 'wp_ajax_nopriv_commercekit_wishlist_addtocart', 'commercekit_ajax_wishlist_addtocart' );

/**
 * Add wishlist endpoint.
 */
function commercekit_add_wishlist_endpoint() {
	add_rewrite_endpoint( 'cgkit-wishlist', EP_ROOT | EP_PAGES );
	$is_flushed = (int) get_option( 'commercekit_cgkit_wishlist' );
	if ( 1 !== $is_flushed ) {
		flush_rewrite_rules( false );
		update_option( 'commercekit_cgkit_wishlist', 1, false );
	}
}

add_action( 'init', 'commercekit_add_wishlist_endpoint' );

/**
 * Wishlist query vars.
 *
 * @param string $vars of query vars.
 */
function commercekit_cgkit_wishlist_query_vars( $vars ) {
	$vars[] = 'cgkit-wishlist';
	return $vars;
}

add_filter( 'query_vars', 'commercekit_cgkit_wishlist_query_vars', 0 );

/**
 * Add wishlist my account link.
 *
 * @param string $items of menus.
 */
function commercekit_add_cgkit_wishlist_link_my_account( $items ) {
	$new_item = array( 'cgkit-wishlist' => esc_html__( 'My wishlist', 'commercegurus-commercekit' ) );
	if ( isset( $items['customer-logout'] ) ) {
		$old_item = array( 'customer-logout' => $items['customer-logout'] );
		unset( $items['customer-logout'] );
		$items = $items + $new_item + $old_item;
	} else {
		$items = $items + $new_item;
	}
	return $items;
}

add_filter( 'woocommerce_account_menu_items', 'commercekit_add_cgkit_wishlist_link_my_account' );

/**
 * Add wishlist my account link.
 */
function commercekit_cgkit_wishlist_content() {
	echo '<h2>' . esc_html__( 'My wishlist', 'commercegurus-commercekit' ) . '</h1>';
	echo do_shortcode( '[commercegurus_wishlist]' );
}

add_action( 'woocommerce_account_cgkit-wishlist_endpoint', 'commercekit_cgkit_wishlist_content' );

/**
 * Ajax wishlist update.
 */
function commercekit_ajax_wishlist_update() {
	global $wpdb;
	$ajax           = array();
	$ajax['wlists'] = array();

	$wlist_mids = isset( $_POST['wlist_mids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['wlist_mids'] ) ) ) : array(); // phpcs:ignore
	$wlist_fids = isset( $_POST['wlist_fids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['wlist_fids'] ) ) ) : array(); // phpcs:ignore
	$wlist_mids = array_unique( array_filter( $wlist_mids ) );
	$wlist_fids = array_unique( array_filter( $wlist_fids ) );
	$wlist_ids  = array_merge( $wlist_mids, $wlist_fids );
	$wlist_ids  = array_unique( $wlist_ids );

	$table_list = $wpdb->prefix . 'commercekit_wishlist';
	$table_item = $wpdb->prefix . 'commercekit_wishlist_items';

	$uid = (int) get_current_user_id();
	$key = isset( $_COOKIE['commercekit_wishlist'] ) && ! empty( $_COOKIE['commercekit_wishlist'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['commercekit_wishlist'] ) ) : '';
	$sid = ! empty( $key ) ? (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $table_list . ' WHERE session_key = %s', $key ) ) : 0; // phpcs:ignore

	if ( count( $wlist_ids ) && ( $uid || $sid ) ) {
		$wlist_ids = array_map( 'intval', $wlist_ids );
		$wlist_txt = implode( ',', $wlist_ids );
		if ( $uid ) {
			$wids = $wpdb->get_col( $wpdb->prepare( 'SELECT product_id FROM ' . $table_item . ' WHERE user_id = %d AND product_id IN (' . $wlist_txt . ')', $uid ) ); // phpcs:ignore
		} else {
			$wids = $wpdb->get_col( $wpdb->prepare( 'SELECT product_id FROM ' . $table_item . ' WHERE list_id = %d AND product_id IN (' . $wlist_txt . ')', $sid ) ); // phpcs:ignore
		}

		if ( count( $wids ) ) {
			$wlist_mids = array_map( 'intval', $wlist_mids );
			$wlist_fids = array_map( 'intval', $wlist_fids );
			$options    = get_option( 'commercekit', array() );
			$wsl_brtext = isset( $options['wsl_brtext'] ) && ! empty( $options['wsl_brtext'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $options['wsl_brtext'] ) ) : commercekit_get_default_settings( 'wsl_brtext' );
			$wsl_page   = isset( $options['wsl_page'] ) ? esc_attr( $options['wsl_page'] ) : 0;
			$page_link  = get_permalink( $wsl_page );
			$full_html  = '<a href="' . $page_link . '" class="commercekit-browse-wishlist wsl-updated" title="' . esc_html__( 'Wishlist', 'commercegurus-commercekit' ) . '"><em class="cg-wishlist"></em><span>' . $wsl_brtext . '</span></a>';
			$mini_html  = '<a href="' . $page_link . '" class="commercekit-browse-wishlist wsl-updated" title="' . esc_html__( 'Wishlist', 'commercegurus-commercekit' ) . '"><em class="cg-wishlist-t"></em><span></span></a>';
			foreach ( $wids as $wid ) {
				if ( in_array( (int) $wid, $wlist_mids, true ) ) {
					$wlist         = array();
					$wlist['id']   = $wid;
					$wlist['cls']  = '.mini';
					$wlist['html'] = $mini_html;

					$ajax['wlists'][] = $wlist;
				}
				if ( in_array( (int) $wid, $wlist_fids, true ) ) {
					$wlist         = array();
					$wlist['id']   = $wid;
					$wlist['cls']  = '.full';
					$wlist['html'] = $full_html;

					$ajax['wlists'][] = $wlist;
				}
			}
		}
	}

	wp_send_json( $ajax );
}

add_action( 'wp_ajax_commercekit_wishlist_update', 'commercekit_ajax_wishlist_update' );
add_action( 'wp_ajax_nopriv_commercekit_wishlist_update', 'commercekit_ajax_wishlist_update' );

/**
 * Wishlist record sales
 *
 * @param  string $order_id of order.
 */
function commercekit_wishlist_record_sales( $order_id ) {
	global $wpdb;
	$order = wc_get_order( $order_id );
	$uid   = (int) get_current_user_id();
	$key   = isset( $_COOKIE['commercekit_wishlist'] ) && ! empty( $_COOKIE['commercekit_wishlist'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['commercekit_wishlist'] ) ) : md5( microtime( true ) );
	$sid   = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_wishlist WHERE session_key = %s', $key ) ); // db call ok; no-cache ok.
	$reset = (int) get_option( 'commercekit_wsls_reset' );
	$sales = 0;
	$price = 0;
	if ( ! $reset || ( ! $uid && ! $sid ) ) {
		return;
	}
	if ( $uid ) {
		$sid = 0;
	}
	foreach ( $order->get_items() as $item_id => $item ) {
		$product_id = 0;
		if ( isset( $item['product_id'] ) && $item['product_id'] > 0 ) {
			$product_id = $item['product_id'];
		}
		$variation_id = $product_id;
		if ( isset( $item['variation_id'] ) && $item['variation_id'] > 0 ) {
			$variation_id = $item['variation_id'];
		}
		$quantity = (int) $item['quantity'];
		$found_id = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_wishlist_items WHERE user_id = %d AND list_id = %d AND ( product_id = %d OR product_id = %d ) AND created >= %d AND tracked = %d', $uid, $sid, $product_id, $variation_id, $reset, 0 ) ); // db call ok; no-cache ok.
		if ( $found_id && $variation_id ) {
			$product = wc_get_product( $variation_id );
			if ( $product ) {
				$sales++;
				$price += $quantity * (float) $product->get_price();
				$data   = array( 'tracked' => 1 );
				$where  = array( 'id' => $found_id );

				$data_format  = array( '%d' );
				$where_format = array( '%d' );
				$wpdb->update( $wpdb->prefix . 'commercekit_wishlist_items', $data, $where, $data_format, $where_format ); // db call ok; no-cache ok.
			}
		}
	}
	if ( $sales ) {
		$wsls_total = (int) get_option( 'commercekit_wsls_total' );
		$wsls_sales = (int) get_option( 'commercekit_wsls_sales' ) + $sales;
		$wsls_sales = $wsls_sales > $wsls_total ? $wsls_total : $wsls_sales;
		update_option( 'commercekit_wsls_sales', $wsls_sales, false );
	}
	if ( $price ) {
		$wsls_price = (float) get_option( 'commercekit_wsls_sales_revenue' );
		update_option( 'commercekit_wsls_sales_revenue', ( $wsls_price + $price ), false );
	}
}
add_action( 'woocommerce_thankyou', 'commercekit_wishlist_record_sales' );
