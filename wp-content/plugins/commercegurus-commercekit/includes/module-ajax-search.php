<?php
/**
 *
 * Ajax Search module
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

/**
 * Ajax search options
 *
 * @return string
 */
function commercekit_ajs_options() {
	$commercekit_options            = get_option( 'commercekit', array() );
	$commercekit_flags              = commercekit_feature_flags()->get_flags();
	$cgkit_fast_ajax_search         = isset( $commercekit_options['ajs_fast_search'] ) && 1 === (int) $commercekit_options['ajs_fast_search'] ? true : false;
	$cgkit_ajs                      = array();
	$cgkit_ajs['ajax_url']          = COMMERCEKIT_AJAX::get_endpoint();
	$cgkit_ajs['ajax_search']       = isset( $commercekit_flags['ajax_search'] ) && 1 === (int) $commercekit_flags['ajax_search'] ? 1 : 0;
	$cgkit_ajs['char_count']        = 3;
	$cgkit_ajs['action']            = 'commercekit_ajax_search';
	$cgkit_ajs['loader_icon']       = CKIT_URI . 'assets/images/loader2.gif';
	$cgkit_ajs['no_results_text']   = isset( $commercekit_options['ajs_no_text'] ) && ! empty( $commercekit_options['ajs_no_text'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['ajs_no_text'] ) ) : commercekit_get_default_settings( 'ajs_no_text' );
	$cgkit_ajs['placeholder_text']  = isset( $commercekit_options['ajs_placeholder'] ) && ! empty( $commercekit_options['ajs_placeholder'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['ajs_placeholder'] ) ) : commercekit_get_default_settings( 'ajs_placeholder' );
	$cgkit_ajs['other_result_text'] = isset( $commercekit_options['ajs_other_text'] ) && ! empty( $commercekit_options['ajs_other_text'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['ajs_other_text'] ) ) : commercekit_get_default_settings( 'ajs_other_text' );
	$cgkit_ajs['view_all_text']     = isset( $commercekit_options['ajs_all_text'] ) && ! empty( $commercekit_options['ajs_all_text'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['ajs_all_text'] ) ) : commercekit_get_default_settings( 'ajs_all_text' );
	$cgkit_ajs['no_other_text']     = isset( $commercekit_options['ajs_no_other_text'] ) && ! empty( $commercekit_options['ajs_no_other_text'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['ajs_no_other_text'] ) ) : commercekit_get_default_settings( 'ajs_no_other_text' );
	$cgkit_ajs['other_all_text']    = isset( $commercekit_options['ajs_other_all_text'] ) && ! empty( $commercekit_options['ajs_other_all_text'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['ajs_other_all_text'] ) ) : commercekit_get_default_settings( 'ajs_other_all_text' );
	$cgkit_ajs['ajax_url_product']  = $cgkit_fast_ajax_search ? commercekit_ajs_get_fast_url( 'product' ) : commercekit_ajs_get_slow_url( 'cgkit_ajax_search_product' ); // phpcs:ignore;
	$cgkit_ajs['ajax_url_post']     = $cgkit_fast_ajax_search ? commercekit_ajs_get_fast_url( 'post' ) : commercekit_ajs_get_slow_url( 'cgkit_ajax_search_post' ); // phpcs:ignore;
	$cgkit_ajs['fast_ajax_search']  = $cgkit_fast_ajax_search ? 1 : 0;
	$cgkit_ajs['ajs_other_results'] = ( isset( $commercekit_options['ajs_other_results'] ) && 1 === (int) $commercekit_options['ajs_other_results'] ) || ! isset( $commercekit_options['ajs_other_results'] ) ? 1 : 0;
	$cgkit_ajs['layout']            = 'product';

	$all_post_types = get_post_types( array( 'exclude_from_search' => false ) );
	if ( is_array( $all_post_types ) && in_array( 'product', $all_post_types, true ) ) {
		unset( $all_post_types['product'] );
		unset( $all_post_types['product_variation'] );
	}
	$all_post_types   = array_filter( $all_post_types );
	$saved_post_types = isset( $commercekit_options['ajs_other_post_types'] ) ? $commercekit_options['ajs_other_post_types'] : array();
	if ( count( $all_post_types ) !== count( array_intersect( $all_post_types, $saved_post_types ) ) ) {
		$commercekit_options['ajs_other_post_types'] = $all_post_types;
		update_option( 'commercekit', $commercekit_options, false );
	}

	return $cgkit_ajs;
}

/**
 * Ajax search prepare.
 */
function commercekit_prepare_ajax_search() {
	$commercekit_nonce = isset( $_GET['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_nonce' ) ) {
		wp_send_json(
			array(
				'suggestions'   => array(),
				'view_all_link' => '',
				'result_total'  => 0,
			)
		);
	}
	commercekit_ajax_do_search();
}
add_action( 'wp_ajax_commercekit_ajax_search', 'commercekit_prepare_ajax_search' );
add_action( 'wp_ajax_nopriv_commercekit_ajax_search', 'commercekit_prepare_ajax_search' );

/**
 * Ajax search get fast URL
 *
 * @param  string $search_type type of URL.
 */
function commercekit_ajs_get_fast_url( $search_type = 'product' ) {
	$search_lang = '';
	$lang_plug   = '';
	if ( defined( 'ICL_SITEPRESS_VERSION' ) && has_filter( 'wpml_current_language' ) ) {
		$search_lang = apply_filters( 'wpml_current_language', null );
		$lang_plug   = 'wpml';
	} elseif ( function_exists( 'pll_current_language' ) ) {
		$search_lang = pll_current_language();
		$lang_plug   = 'polylang';
	}
	if ( isset( $_GET['lang'] ) && ! empty( $_GET['lang'] ) ) { // phpcs:ignore
		$get_lang = sanitize_text_field( wp_unslash( $_GET['lang'] ) ); // phpcs:ignore
		if ( $search_lang !== $get_lang ) {
			$search_lang = $get_lang;
		}
	}
	$url = CKIT_URI . 'cgkit-search-api.php';
	$url = add_query_arg( 'search_type', $search_type, $url );
	if ( ! empty( $search_lang ) ) {
		$url = add_query_arg( 'lang', $search_lang, $url );
	}
	if ( ! empty( $lang_plug ) ) {
		$url = add_query_arg( 'plug', $lang_plug, $url );
	}

	return $url;
}

/**
 * Ajax search get slow URL
 *
 * @param  string $filter_key filter key value.
 */
function commercekit_ajs_get_slow_url( $filter_key ) {
	$search_lang = '';

	$url = home_url( '/' );
	if ( defined( 'ICL_SITEPRESS_VERSION' ) && has_filter( 'wpml_current_language' ) ) {
		$search_lang  = apply_filters( 'wpml_current_language', null );
		$default_lang = apply_filters( 'wpml_default_language', null );
		if ( $search_lang === $default_lang ) {
			$search_lang = '';
		}
	} elseif ( function_exists( 'pll_current_language' ) ) {
		$search_lang = pll_current_language();
	}
	if ( isset( $_GET['lang'] ) && ! empty( $_GET['lang'] ) ) { // phpcs:ignore
		$get_lang = sanitize_text_field( wp_unslash( $_GET['lang'] ) ); // phpcs:ignore
		if ( $search_lang !== $get_lang ) {
			$search_lang = $get_lang;
		}
	}
	$url = add_query_arg( $filter_key, 1, $url );
	if ( ! empty( $search_lang ) ) {
		$url = add_query_arg( 'lang', $search_lang, $url );
	}

	return $url;
}

/**
 * Ajax search custom query
 *
 * @param  string $vars of form.
 */
function commercekit_ajax_search_custom_query_var( $vars ) {
	$vars[] = 'cgkit_ajax_search_product';
	$vars[] = 'cgkit_ajax_search_post';

	return $vars;
}
add_filter( 'query_vars', 'commercekit_ajax_search_custom_query_var' );

/**
 * Ajax search custom query handle
 */
function commercekit_ajax_search_custom_query_var_handle() {
	$is_cgkit_ajs = (int) get_query_var( 'cgkit_ajax_search_product' );
	$default_lang = '';
	if ( defined( 'ICL_SITEPRESS_VERSION' ) && has_filter( 'wpml_default_language' ) ) {
		$default_lang = apply_filters( 'wpml_default_language', null );
	}
	if ( 1 === $is_cgkit_ajs || ( isset( $_GET['cgkit_ajax_search_product'] ) && 1 === (int) $_GET['cgkit_ajax_search_product'] ) ) { // phpcs:ignore
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}
		if ( ! defined( 'WC_DOING_AJAX' ) ) {
			define( 'WC_DOING_AJAX', true );
		}
		$commercekit_nonce = isset( $_GET['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['commercekit_nonce'] ) ) : '';
		if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_nonce' ) ) {
			wp_send_json(
				array(
					'suggestions'   => array(),
					'view_all_link' => '',
					'result_total'  => 0,
				)
			);
		}
		commercekit_ajax_do_search( 'product', $default_lang );
	}
	$is_cgkit_ajs_post = (int) get_query_var( 'cgkit_ajax_search_post' );
	if ( 1 === $is_cgkit_ajs_post || ( isset( $_GET['cgkit_ajax_search_post'] ) && 1 === (int) $_GET['cgkit_ajax_search_post'] ) ) { // phpcs:ignore
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}
		if ( ! defined( 'WC_DOING_AJAX' ) ) {
			define( 'WC_DOING_AJAX', true );
		}
		$commercekit_nonce = isset( $_GET['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['commercekit_nonce'] ) ) : '';
		if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_nonce' ) ) {
			wp_send_json(
				array(
					'suggestions'   => array(),
					'view_all_link' => '',
					'result_total'  => 0,
				)
			);
		}
		commercekit_ajax_do_search( 'post', $default_lang );
	}
}
add_action( 'wp_loaded', 'commercekit_ajax_search_custom_query_var_handle', 999 );

/**
 * Ajax search form html
 *
 * @param  string $html of form.
 */
function commercekit_ajax_search_form( $html ) {
	$commercekit_options = get_option( 'commercekit', array() );
	$placeholder_text    = isset( $commercekit_options['ajs_placeholder'] ) && ! empty( $commercekit_options['ajs_placeholder'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['ajs_placeholder'] ) ) : commercekit_get_default_settings( 'ajs_placeholder' );

	$html = preg_replace( '/placeholder=\"([^"]*)\"/i', 'placeholder="' . $placeholder_text . '"', $html );

	return $html;
}
add_filter( 'get_search_form', 'commercekit_ajax_search_form', 99 );
add_filter( 'get_product_search_form', 'commercekit_ajax_search_form', 99 );

/**
 * Custom search template
 *
 * @param  string $template of search.
 */
function commercekit_custom_search_template( $template ) {
	global $wp_query, $cgkit_ajs_tabbed;
	$options     = get_option( 'commercekit', array() );
	$ajs_tabbed  = false;
	$ajs_display = false;

	$cgkit_ajs_tabbed = false;
	if ( $wp_query->is_search && $ajs_tabbed && $ajs_display ) {
		$cgkit_ajs_tabbed = true;
		return dirname( __FILE__ ) . '/templates/search.php';
	} else {
		return $template;
	}
}
add_filter( 'template_include', 'commercekit_custom_search_template' );

/**
 * Custom search query
 *
 * @param  string $query of search.
 */
function commercekit_custom_search_query( $query ) {
	global $commercekit_ajs_index, $commercekit_ajs_s;
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$get_post_type = isset( $_GET['cgkit_post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['cgkit_post_type'] ) ) : ''; // phpcs:ignore
	if ( empty( $get_post_type ) ) {
		$get_post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore
	}

	if ( 'product' === $get_post_type && $query->is_search() ) {
		$search_txt  = $query->get( 's' );
		$search_lang = '';
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && has_filter( 'wpml_current_language' ) ) {
			$search_lang = apply_filters( 'wpml_current_language', null );
		} elseif ( function_exists( 'pll_current_language' ) ) {
			$search_lang = pll_current_language();
		}
		if ( isset( $_GET['lang'] ) && ! empty( $_GET['lang'] ) ) { // phpcs:ignore
			$get_lang = sanitize_text_field( wp_unslash( $_GET['lang'] ) ); // phpcs:ignore
			if ( $search_lang !== $get_lang ) {
				$search_lang = $get_lang;
			}
		}

		$return_data = $commercekit_ajs_index->get_search_product_ids( $search_txt, true, $search_lang );
		$product_ids = isset( $return_data['ids'] ) ? $return_data['ids'] : array();
		if ( count( $product_ids ) ) {
			$commercekit_ajs_s = $search_txt;
			$query->set( 's', '' );
			$query->set( 'post__in', $product_ids );
		}
	} elseif ( 'cgkit-post' === $get_post_type && $query->is_search() ) {
		$options        = get_option( 'commercekit', array() );
		$excludes_other = isset( $options['ajs_excludes_other'] ) ? explode( ',', $options['ajs_excludes_other'] ) : array();
		$excludes_other = array_filter( $excludes_other );
		$all_post_types = get_post_types( array( 'exclude_from_search' => false ) );
		if ( is_array( $all_post_types ) && in_array( 'product', $all_post_types, true ) ) {
			unset( $all_post_types['product'] );
			unset( $all_post_types['product_variation'] );
		}
		$query->set( 'post_type', $all_post_types );
		$query->set( 'post_status', 'publish' );
		if ( count( $excludes_other ) ) {
			$query->set( 'post__not_in', $excludes_other );
		}
	}
}
add_action( 'pre_get_posts', 'commercekit_custom_search_query', 999, 1 );

/**
 * Custom search post filter
 *
 * @param  string $posts list of posts.
 * @param  string $query of search.
 */
function commercekit_custom_search_posts( $posts, $query ) {
	global $commercekit_ajs_s;
	if ( is_admin() || ! $query->is_main_query() ) {
		return $posts;
	}

	$get_post_type = isset( $_GET['cgkit_post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['cgkit_post_type'] ) ) : ''; // phpcs:ignore
	if ( empty( $get_post_type ) ) {
		$get_post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore
	}

	if ( 'product' === $get_post_type && $query->is_search() && isset( $commercekit_ajs_s ) && ! empty( $commercekit_ajs_s ) ) {
		$commercekit_ajs_s = str_replace( '\\', '', $commercekit_ajs_s );
		$query->set( 's', $commercekit_ajs_s );
	}

	return $posts;
}
add_filter( 'the_posts', 'commercekit_custom_search_posts', 10, 2 );

/**
 * Custom order by search query
 *
 * @param  string $query of search.
 */
function commercekit_ajs_order_by_pre_get_posts( $query ) {
	global $commercekit_ajs_s;
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$get_post_type = isset( $_GET['cgkit_post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['cgkit_post_type'] ) ) : ''; // phpcs:ignore
	if ( empty( $get_post_type ) ) {
		$get_post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore
	}

	if ( 'product' === $get_post_type && $query->is_search() ) {
		$commercekit_ajs_s = $query->get( 's' );
		add_filter( 'posts_clauses', 'commercekit_ajs_order_by_stock_status', 999, 1 );
	}
}
add_action( 'pre_get_posts', 'commercekit_ajs_order_by_pre_get_posts', 99, 1 );

/**
 * Custom order by stock status
 *
 * @param  string $posts_clauses posts clauses.
 */
function commercekit_ajs_order_by_stock_status( $posts_clauses ) {
	global $wpdb, $commercekit_ajs_index, $commercekit_ajs_s;
	$options     = get_option( 'commercekit', array() );
	$outofstock  = isset( $options['ajs_outofstock'] ) && 1 === (int) $options['ajs_outofstock'] ? false : true;
	$orderby_oos = isset( $options['ajs_orderby_oos'] ) && 1 === (int) $options['ajs_orderby_oos'] ? true : false;
	$order_terms = $commercekit_ajs_index->get_search_parsed_words( $commercekit_ajs_s );
	$order_by    = $commercekit_ajs_index->get_order_by_string( $commercekit_ajs_s, $order_terms, "$wpdb->posts.post_title", "$wpdb->posts.post_content" );
	$get_orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['orderby'] ) ) : sanitize_text_field( get_query_var( 'orderby' ) ); // phpcs:ignore
	if ( 'relevance' === $get_orderby || empty( $get_orderby ) ) {
		$posts_clauses['orderby'] = ( ! empty( $order_by ) ? $order_by . ', ' : '' ) . $posts_clauses['orderby'];
	}
	if ( $outofstock && $orderby_oos ) {
		$posts_clauses['join']   .= " LEFT JOIN {$wpdb->postmeta} ck_ajs_stock ON ( {$wpdb->posts}.ID = ck_ajs_stock.post_id AND ck_ajs_stock.meta_key = '_stock_status' ) ";
		$posts_clauses['orderby'] = ' ck_ajs_stock.meta_value ASC, ' . $posts_clauses['orderby'];
	} elseif ( ! $outofstock ) {
		$posts_clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} ck_ajs_stock ON ( {$wpdb->posts}.ID = ck_ajs_stock.post_id AND ck_ajs_stock.meta_key = '_stock_status' ) ";
		$posts_clauses['where'] = " AND ( ck_ajs_stock.meta_value NOT IN ( 'outofstock' ) OR ck_ajs_stock.meta_value IS NULL ) " . $posts_clauses['where'];
	}

	return $posts_clauses;
}

/**
 * Ajax search counts.
 */
function commercekit_ajax_search_counts() {
	global $wpdb;
	$ajax            = array();
	$ajax['status']  = 0;
	$ajax['message'] = '';

	$commercekit_nonce = isset( $_GET['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_nonce' ) ) {
		wp_send_json( $ajax );
	}

	$query     = isset( $_GET['query'] ) ? sanitize_text_field( wp_unslash( $_GET['query'] ) ) : '';
	$no_result = isset( $_GET['no_result'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['no_result'] ) ) : 0;
	$table     = $wpdb->prefix . 'commercekit_searches';
	$query     = str_replace( array( '"', '\'', '\\' ), '', $query );
	if ( $query ) {
		$search_ids = isset( $_COOKIE['commercekit_search_ids'] ) && ! empty( $_COOKIE['commercekit_search_ids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['commercekit_search_ids'] ) ) ) : array();
		$search_ids = array_map( 'intval', $search_ids );
		$row        = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE search_term = %s', $query ) ); // phpcs:ignore
		$search_id  = 0;
		if ( $row ) {
			if ( ! in_array( (int) $row->id, $search_ids, true ) ) {
				$data   = array(
					'search_count'    => $row->search_count + 1,
					'no_result_count' => 1 === $no_result ? $row->no_result_count + 1 : $row->no_result_count,
				);
				$where  = array(
					'id' => $row->id,
				);
				$format = array( '%d', '%d' );
				$wpdb->update( $table, $data, $where, $format ); // db call ok; no-cache ok.
			}
			$search_id = $row->id;
		} else {
			$data   = array(
				'search_term'     => $query,
				'search_count'    => 1,
				'no_result_count' => 1 === $no_result ? 1 : 0,
			);
			$format = array( '%s', '%d', '%d' );
			$wpdb->insert( $table, $data, $format ); // db call ok; no-cache ok.
			$search_id = $wpdb->insert_id;
		}
		$search_ids[] = $search_id;
		setcookie( 'commercekit_search_ids', implode( ',', array_unique( $search_ids ) ), time() + ( 48 * 3600 ), '/' );
		$ajax['status'] = 1;
	}

	wp_send_json( $ajax );
}

add_action( 'wp_ajax_commercekit_search_counts', 'commercekit_ajax_search_counts' );
add_action( 'wp_ajax_nopriv_commercekit_search_counts', 'commercekit_ajax_search_counts' );

/**
 * Add wishlist endpoint.
 */
function commercekit_add_search_click_count() {
	global $wpdb;
	$cgkit_search_word = isset( $_GET['cgkit_search_word'] ) ? sanitize_text_field( wp_unslash( $_GET['cgkit_search_word'] ) ) : ''; // phpcs:ignore
	$cgkit_search_word = str_replace( array( '"', '\'', '\\' ), '', $cgkit_search_word );
	if ( $cgkit_search_word ) {
		$table = $wpdb->prefix . 'commercekit_searches';
		$row   = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE search_term = %s', $cgkit_search_word ) ); // phpcs:ignore
		if ( $row ) {
			$search_ids = isset( $_COOKIE['commercekit_search_ids'] ) && ! empty( $_COOKIE['commercekit_search_ids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['commercekit_search_ids'] ) ) ) : array();
			$search_ids = array_map( 'intval', $search_ids );

			$search_cids = isset( $_COOKIE['commercekit_search_cids'] ) && ! empty( $_COOKIE['commercekit_search_cids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['commercekit_search_cids'] ) ) ) : array();
			$search_cids = array_map( 'intval', $search_cids );
			if ( in_array( (int) $row->id, $search_ids, true ) && ! in_array( (int) $row->id, $search_cids, true ) ) {
				$data   = array(
					'click_count' => $row->click_count + 1,
				);
				$where  = array(
					'id' => $row->id,
				);
				$format = array( '%d' );
				$wpdb->update( $table, $data, $where, $format ); // db call ok; no-cache ok.

				$search_cids[] = $row->id;
				setcookie( 'commercekit_search_cids', implode( ',', array_unique( $search_cids ) ), time() + ( 48 * 3600 ), '/' );
			}
		}
	}
}
add_action( 'init', 'commercekit_add_search_click_count' );
require_once dirname( __FILE__ ) . '/module-fast-ajax-search.php';
