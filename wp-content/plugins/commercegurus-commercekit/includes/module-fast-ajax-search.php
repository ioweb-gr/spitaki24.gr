<?php
/**
 *
 * Fast Ajax Search module
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

/**
 * Ajax do search
 *
 * @param  string $search_type of ajax search.
 * @param  string $default_lang default language.
 */
function commercekit_ajax_do_search( $search_type = '', $default_lang = '' ) {
	global $cgkit_ajax_search, $wp_query;
	$commercekit_options = get_option( 'commercekit', array() );
	$cgkit_fast_search   = isset( $commercekit_options['ajs_fast_search'] ) && 1 === (int) $commercekit_options['ajs_fast_search'] ? true : false;
	$search_type         = ! empty( $search_type ) ? $search_type : 'product';
	$enable_ajax_search  = isset( $commercekit_options['ajax_search'] ) && 1 === (int) $commercekit_options['ajax_search'] ? 1 : 0;
	$search_text         = isset( $_GET['query'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['query'] ) ) ) : ''; // phpcs:ignore
	$search_lang         = isset( $_GET['lang'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['lang'] ) ) ) : $default_lang; // phpcs:ignore
	$language_plug       = isset( $_GET['plug'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['plug'] ) ) ) : ''; // phpcs:ignore
	$suggestions         = array();
	$view_all_ext        = 'wpml' === $language_plug ? commercekit_ajs_get_wpml_home_ext( $search_lang, $cgkit_fast_search ) : '';
	$view_all_link       = home_url( '/' . $view_all_ext );
	$query_search_type   = 'product' === $search_type ? 'product' : 'cgkit-post';
	$view_all_link       = add_query_arg( 's', rawurlencode( $search_text ), $view_all_link );
	$cgkit_post_type     = 'product' === $search_type ? 'post_type' : 'cgkit_post_type';
	$view_all_link       = add_query_arg( $cgkit_post_type, $query_search_type, $view_all_link );
	$ajs_excludes        = isset( $commercekit_options['ajs_excludes'] ) ? explode( ',', $commercekit_options['ajs_excludes'] ) : array();
	$ajs_excludes_other  = isset( $commercekit_options['ajs_excludes_other'] ) ? explode( ',', $commercekit_options['ajs_excludes_other'] ) : array();
	$ajs_product_count   = isset( $commercekit_options['ajs_product_count'] ) && (int) $commercekit_options['ajs_product_count'] ? (int) $commercekit_options['ajs_product_count'] : 3;
	$ajs_other_count     = isset( $commercekit_options['ajs_other_count'] ) && (int) $commercekit_options['ajs_other_count'] ? (int) $commercekit_options['ajs_other_count'] : 3;
	$result_total        = 0;
	$cgkit_ajax_search   = true;
	if ( $ajs_product_count < 1 || $ajs_product_count > 5 ) {
		$ajs_product_count = 3;
	}
	if ( $ajs_other_count < 1 || $ajs_other_count > 5 ) {
		$ajs_other_count = 3;
	}
	if ( ! empty( $search_lang ) && $search_lang !== $default_lang ) {
		$view_all_link = add_query_arg( 'lang', $search_lang, $view_all_link );
	}
	if ( $enable_ajax_search && $search_text ) {
		$cgkit_wc_ajs = new CommerceKit_AJS_Index();
		if ( 'product' === $search_type ) {
			$return_data  = $cgkit_wc_ajs->get_search_product_ids( $search_text, false, $search_lang );
			$product_rows = isset( $return_data['products'] ) ? $return_data['products'] : array();
			$result_total = isset( $return_data['total'] ) ? $return_data['total'] : 0;
			$result_count = 0;
			if ( count( $product_rows ) ) {
				foreach ( $product_rows as $product_row ) {
					$post_id     = $product_row->product_id;
					$post_title  = $product_row->title;
					$price_html  = '<span class="product-short-desc">' . $product_row->short_description . '</span>';
					$product_url = $product_row->product_url;
					$product_img = $product_row->product_img;
					if ( ! empty( $search_lang ) && $product_row->lang !== $search_lang && ! empty( $product_row->other_lang ) ) {
						$other_urls = json_decode( $product_row->other_urls, true );
						if ( isset( $other_urls[ $search_lang ] ) && ! empty( $other_urls[ $search_lang ] ) ) {
							$product_url = $other_urls[ $search_lang ];
						}
					}
					if ( ! $cgkit_fast_search ) {
						$product = wc_get_product( $post_id );
						if ( ! $product ) {
							continue;
						}
						$post_title = wp_strip_all_tags( get_the_title( $post_id ) );
						$price_html = $product->get_price_html();

						$product_img = '';
						if ( has_post_thumbnail( $post_id ) ) {
							$product_img = get_the_post_thumbnail( $post_id, 'thumbnail' );
						}
					}
					if ( preg_match( '/' . preg_quote( $search_text, '/' ) . '/i', $post_title, $matches ) ) {
						$post_title = preg_replace( '/' . preg_quote( $search_text, '/' ) . '/i', '<span class="match-text">' . $matches[0] . '</span>', $post_title );
					}
					$output = '<a href="' . esc_url( add_query_arg( 'cgkit_search_word', $search_text, $product_url ) ) . '" class="commercekit-ajs-product">';
					if ( $product_img ) {
						$output .= '<div class="commercekit-ajs-product-image">' . $product_img . '</div>';
					}
					$output .= '<div class="commercekit-ajs-product-desc">';
					$output .= '<div class="commercekit-ajs-product-title">' . $post_title . '</div>';
					$output .= '<div class="commercekit-ajs-product-price">' . $price_html . '</div>';
					$output .= '</div>';
					$output .= '</div>';
					$output .= '</a>';

					$suggestions[] = array(
						'value' => esc_js( $post_title ),
						'data'  => $output,
						'url'   => esc_url( $product_url ),
					);
					$result_count++;
				}
			}
			if ( ! $cgkit_fast_search ) {
				$taxonomies = array( 'product_cat', 'product_tag' );
				$custom_tax = array();
				if ( ! $cgkit_fast_search ) {
					$custom_tax = commercekit_get_product_custom_taxonomies();
				}
				if ( count( $custom_tax ) ) {
					$taxonomies = array_merge( $taxonomies, array_keys( $custom_tax ) );
				}
				$terms = get_terms(
					$taxonomies,
					array(
						'name__like' => $search_text,
						'hide_empty' => true,
						'number'     => 2,
					)
				);
				if ( is_array( $terms ) && count( $terms ) > 0 ) {
					foreach ( $terms as $term ) {
						$term_name = wp_strip_all_tags( $term->name );
						if ( preg_match( '/' . preg_quote( $search_text, '/' ) . '/i', $term_name, $matches ) ) {
							$term_name = preg_replace( '/' . preg_quote( $search_text, '/' ) . '/i', '<span class="match-text">' . $matches[0] . '</span>', $term_name );
						}
						$term_type = $term->taxonomy;
						if ( 'product_cat' === $term_type ) {
							$term_type = commercekit_ajs_esc_html__( 'Product Category', 'commercegurus-commercekit' );
						} elseif ( 'product_tag' === $term_type ) {
							$term_type = commercekit_ajs_esc_html__( 'Product Tag', 'commercegurus-commercekit' );
						} elseif ( array_key_exists( $term_type, $custom_tax ) ) {
							$term_type = $custom_tax[ $term_type ];
						}
						$output  = '<a href="' . esc_url( add_query_arg( 'cgkit_search_word', $search_text, get_term_link( $term ) ) ) . '" class="commercekit-ajs-post">';
						$output .= '<div class="commercekit-ajs-post-title">' . $term_name . '<span class="post-type">' . $term_type . '</span></div>';
						$output .= '</div>';
						$output .= '</a>';

						$suggestions[] = array(
							'value' => esc_js( $term->name ),
							'data'  => $output,
							'url'   => esc_url( get_term_link( $term ) ),
						);
						$result_count++;
					}
				}
			}
		}

		if ( 'post' === $search_type ) {
			if ( ! $cgkit_fast_search ) {
				$all_post_types = get_post_types( array( 'exclude_from_search' => false ) );
				if ( is_array( $all_post_types ) && in_array( 'product', $all_post_types, true ) ) {
					unset( $all_post_types['product'] );
					unset( $all_post_types['product_variation'] );
				}
				$args = array(
					's'              => $search_text,
					'post_status'    => 'publish',
					'posts_per_page' => $ajs_other_count,
					'post_type'      => $all_post_types,
					'post__not_in'   => $ajs_excludes_other,
					'ep_integrate'   => true,
				);
				if ( ! empty( $search_lang ) ) {
					$args['lang'] = $search_lang;
				}
				$posts_search = new WP_Query( $args );
				$result_total = $posts_search->found_posts;

				if ( $posts_search->have_posts() ) {
					while ( $posts_search->have_posts() ) {
						$posts_search->the_post();
						$post_title = esc_html( wp_strip_all_tags( $posts_search->post->post_title ) );
						if ( preg_match( '/' . preg_quote( $search_text, '/' ) . '/i', $post_title, $matches ) ) {
							$post_title = preg_replace( '/' . preg_quote( $search_text, '/' ) . '/i', '<span class="match-text">' . $matches[0] . '</span>', $post_title );
						}
						$post_id   = $posts_search->post->ID;
						$post_type = $posts_search->post->post_type;
						if ( 'post' === $post_type ) {
							$post_type_name = commercekit_ajs_esc_html__( 'Post', 'commercegurus-commercekit' );
						} elseif ( 'page' === $post_type ) {
							$post_type_name = commercekit_ajs_esc_html__( 'Page', 'commercegurus-commercekit' );
						} else {
							$post_type_name = $post_type;
							$post_type_obj  = get_post_type_object( $post_type );
							if ( $post_type_obj && isset( $post_type_obj->labels->singular_name ) ) {
								$post_type_name = $post_type_obj->labels->singular_name;
							}
						}
						$output  = '<a href="' . esc_url( add_query_arg( 'cgkit_search_word', $search_text, get_permalink( $post_id ) ) ) . '" class="commercekit-ajs-post">';
						$output .= '<div class="commercekit-ajs-post-title">' . $post_title . '<span class="post-type">' . $post_type_name . '</span></div>';
						$output .= '</div>';
						$output .= '</a>';

						$suggestions[] = array(
							'value' => esc_js( $post_title ),
							'data'  => $output,
							'url'   => esc_url( get_permalink( $post_id ) ),
						);
					}
				}
			} else {
				$return_data  = $cgkit_wc_ajs->get_search_post_ids( $search_text, false, $search_lang, $language_plug );
				$post_rows    = isset( $return_data['posts'] ) ? $return_data['posts'] : array();
				$result_total = isset( $return_data['total'] ) ? $return_data['total'] : 0;
				$result_count = 0;
				if ( count( $post_rows ) ) {
					foreach ( $post_rows as $post_row ) {
						$post_id    = $post_row->ID;
						$post_title = esc_html( wp_strip_all_tags( $post_row->post_title ) );
						if ( preg_match( '/' . preg_quote( $search_text, '/' ) . '/i', $post_title, $matches ) ) {
							$post_title = preg_replace( '/' . preg_quote( $search_text, '/' ) . '/i', '<span class="match-text">' . $matches[0] . '</span>', $post_title );
						}
						if ( empty( $post_title ) ) {
							continue;
						}
						$post_type = $post_row->post_type;
						if ( 'post' === $post_type ) {
							$post_type_name = commercekit_ajs_esc_html__( 'Post', 'commercegurus-commercekit' );
						} elseif ( 'page' === $post_type ) {
							$post_type_name = commercekit_ajs_esc_html__( 'Page', 'commercegurus-commercekit' );
						} else {
							$post_type_name = $post_type;
							$post_type_obj  = get_post_type_object( $post_type );
							if ( $post_type_obj && isset( $post_type_obj->labels->singular_name ) ) {
								$post_type_name = $post_type_obj->labels->singular_name;
							}
						}
						$output  = '<a href="' . esc_url( add_query_arg( 'cgkit_search_word', $search_text, commercekit_ajs_get_other_permalink( $post_row ) ) ) . '" class="commercekit-ajs-post">';
						$output .= '<div class="commercekit-ajs-post-title">' . $post_title . '<span class="post-type">' . $post_type_name . '</span></div>';
						$output .= '</div>';
						$output .= '</a>';

						$suggestions[] = array(
							'value' => esc_js( $post_title ),
							'data'  => $output,
							'url'   => esc_url( commercekit_ajs_get_other_permalink( $post_row ) ),
						);
					}
				}
			}
		}
	}

	wp_send_json(
		array(
			'suggestions'   => $suggestions,
			'view_all_link' => $view_all_link,
			'result_total'  => $result_total,
		)
	);
}

/**
 * Commercekit ajs esc html
 *
 * @param  string $text translate text.
 * @param  string $domain translate domain.
 */
function commercekit_ajs_esc_html__( $text, $domain ) {
	$options = get_option( 'commercekit', array() );
	if ( isset( $options['ajs_fast_search'] ) && 1 === (int) $options['ajs_fast_search'] ) {
		return $text;
	} else {
		return esc_html__( $text, $domain ); // phpcs:ignore
	}
}

/**
 * Commercekit ajs get other permalink
 *
 * @param  string $post_row post row.
 */
function commercekit_ajs_get_other_permalink( $post_row ) {
	$permalink = get_option( 'permalink_structure' );
	if ( empty( $permalink ) ) {
		return get_permalink( $post_row->ID );
	} else {
		$post_name = $post_row->post_name;
		if ( '/' === substr( $permalink, -1 ) ) {
			$post_name = $post_name . '/';
		}
		return home_url( '/' . $post_name );
	}
}

/**
 * Commercekit ajs get WPML home ext
 *
 * @param  string $search_lang language code.
 * @param  string $fast_search is fast search.
 */
function commercekit_ajs_get_wpml_home_ext( $search_lang, $fast_search ) {
	if ( ! $fast_search || empty( $search_lang ) ) {
		return '';
	} else {
		$wpml_options = get_option( 'icl_sitepress_settings', array() );
		$default_lang = isset( $wpml_options['default_language'] ) && ! empty( $wpml_options['default_language'] ) ? $wpml_options['default_language'] : '';
		$is_dir_type  = isset( $wpml_options['language_negotiation_type'] ) && 1 === (int) $wpml_options['language_negotiation_type'] ? true : false;
		$is_def_dir   = isset( $wpml_options['urls']['directory_for_default_language'] ) && 1 === (int) $wpml_options['urls']['directory_for_default_language'] ? true : false;
		if ( ! $is_dir_type ) {
			return '';
		} else {
			if ( $default_lang !== $search_lang ) {
				return $search_lang . '/';
			} elseif ( $is_def_dir ) {
				return $search_lang . '/';
			}
		}
	}

	return '';
}
