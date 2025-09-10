<?php
/**
 * Admin Attributes Swatches
 *
 * @author   CommerceGurus
 * @package  Attributes_Swatches
 * @since    1.0.0
 */

/**
 * Load selected attributes
 *
 * @param string $product admin product.
 */
function commercegurus_attribute_swatches_load_attributes( $product ) {
	$attributes = array();

	if ( $product ) {
		foreach ( $product->get_attributes( 'edit' ) as $attribute ) {
			if ( ! $attribute->get_variation() ) {
				continue;
			}
			$attr_slug = sanitize_title( $attribute->get_name() );
			if ( $attr_slug ) {
				if ( $attribute->is_taxonomy() ) {
					$tax = $attribute->get_taxonomy_object();

					$attributes[ $attr_slug ] = array(
						'id'    => $attribute->get_id(),
						'slug'  => $attr_slug,
						'name'  => $tax ? $tax->attribute_label : '',
						'terms' => $attribute->get_terms(),
						'taxo'  => 1,
					);
				} else {
					$_options  = $attribute->get_options();
					$tax_terms = array();
					if ( count( $_options ) ) {
						foreach ( $_options as $_option ) {
							$tax_terms[] = (object) array(
								'name'    => $_option,
								'slug'    => sanitize_title( $_option ),
								'term_id' => $_option,
							);
						}
					}
					$attributes[ $attr_slug ] = array(
						'id'    => $attr_slug,
						'slug'  => $attr_slug,
						'name'  => $attribute->get_name(),
						'terms' => $tax_terms,
						'taxo'  => 0,
					);
				}
			}
		}
	}

	return $attributes;
}

/**
 * Attribute swatches get loop swatch image.
 *
 * @param string $attachment_id image ID.
 */
function commercekit_as_get_loop_swatch_image( $attachment_id ) {
	$image_size = 'woocommerce_thumbnail';
	$swatch_img = wp_get_attachment_image_src( $attachment_id, $image_size );
	if ( ! $swatch_img ) {
		return false;
	}
	$swatch_image = array();
	$image_srcset = wp_get_attachment_image_srcset( $attachment_id, $image_size );
	$image_sizes  = wp_get_attachment_image_sizes( $attachment_id, $image_size );

	$swatch_image['src']    = isset( $swatch_img[0] ) ? $swatch_img[0] : '';
	$swatch_image['srcset'] = '';
	$swatch_image['sizes']  = '';
	if ( $image_srcset ) {
		$swatch_image['srcset'] = $image_srcset;
	}
	if ( $image_sizes ) {
		$swatch_image['sizes'] = $image_sizes;
	}
	return $swatch_image;
}

/**
 * Attribute swatches options html.
 *
 * @param string $html HTML of dropdowns.
 * @param array  $args other arguments.
 */
function commercekit_attribute_swatches_options_html( $html, $args ) {
	global $product, $cgkit_as_caching;

	if ( commercegurus_as_is_wc_composite_product() ) {
		return $html;
	}

	if ( ! $product || ( method_exists( $product, 'is_type' ) && ! $product->is_type( 'variable' ) ) ) {
		return $html;
	}

	if ( empty( $args['options'] ) ) {
		return $html;
	}

	$arg_product = isset( $args['product'] ) ? $args['product'] : $product;
	$product_id  = $arg_product->get_id();

	$commercekit_options = get_option( 'commercekit', array() );
	$commercekit_flags   = commercekit_feature_flags()->get_flags();
	$attribute_swatches  = get_post_meta( $product_id, 'commercekit_attribute_swatches', true );
	if ( ! is_array( $attribute_swatches ) ) {
		$attribute_swatches = array();
	}

	// Check constants first, then fallback to options.
	$as_enabled = defined( 'COMMERCEKIT_ATTRIBUTE_SWATCHES_VISIBLE' ) ? COMMERCEKIT_ATTRIBUTE_SWATCHES_VISIBLE : ( isset( $commercekit_flags['attribute_swatches'] ) && 1 === (int) $commercekit_flags['attribute_swatches'] ? true : false );
	if ( $as_enabled && isset( $attribute_swatches['enable_product'] ) && 0 === (int) $attribute_swatches['enable_product'] ) {
		return $html;
	}

	if ( ! $as_enabled && ( ! isset( $cgkit_as_caching ) || false === $cgkit_as_caching ) ) {
		return $html;
	}

	$as_enabled_pdp = $as_enabled && ( isset( $commercekit_options['attribute_swatches_pdp'] ) && 0 !== (int) $commercekit_options['attribute_swatches_pdp'] );

	if ( ! $as_enabled_pdp && ( ! isset( $cgkit_as_caching ) || false === $cgkit_as_caching ) ) {
		return $html;
	}

	$attribute_raw  = sanitize_title( $args['attribute'] );
	$attribute_name = commercekit_as_get_attribute_slug( $attribute_raw, true );

	$legend_slug   = wc_attribute_taxonomy_slug( $args['attribute'] );
	$legend_labels = wc_get_attribute_taxonomy_labels();
	$legend_label  = isset( $legend_labels[ $legend_slug ] ) ? $legend_labels[ $legend_slug ] : $legend_slug;
	$legend_label  = esc_html__( 'Select ', 'commercegurus-commercekit' ) . $legend_label;

	$is_taxonomy = true;
	$attr_terms  = wc_get_product_terms(
		$product->get_id(),
		$args['attribute'],
		array(
			'fields' => 'all',
		)
	);
	if ( ! count( $attr_terms ) ) {
		$_options = $args['options'];
		if ( count( $_options ) ) {
			$is_taxonomy = false;
			foreach ( $_options as $_option ) {
				$attr_terms[] = (object) array(
					'name'    => $_option,
					'slug'    => sanitize_title( $_option ),
					'term_id' => $_option,
				);
			}
		}
	}
	if ( ! count( $attr_terms ) ) {
		return $html;
	}

	$attribute_id = $is_taxonomy ? wc_attribute_taxonomy_id_by_name( $args['attribute'] ) : sanitize_title( $args['attribute'] );
	$swatch_type  = isset( $attribute_swatches[ $attribute_id ]['cgkit_type'] ) ? $attribute_swatches[ $attribute_id ]['cgkit_type'] : 'button';
	if ( empty( $swatch_type ) ) {
		return $html;
	}
	$as_quickadd_txt = isset( $commercekit_options['as_quickadd_txt'] ) && ! empty( $commercekit_options['as_quickadd_txt'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['as_quickadd_txt'] ) ) : commercekit_get_default_settings( 'as_quickadd_txt' );
	$as_more_opt_txt = isset( $commercekit_options['as_more_opt_txt'] ) && ! empty( $commercekit_options['as_more_opt_txt'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['as_more_opt_txt'] ) ) : commercekit_get_default_settings( 'as_more_opt_txt' );
	$as_activate_atc = isset( $commercekit_options['as_activate_atc'] ) && 1 === (int) $commercekit_options['as_activate_atc'] ? true : false;
	$as_button_style = isset( $commercekit_options['as_button_style'] ) && 1 === (int) $commercekit_options['as_button_style'] ? true : false;
	$attr_count      = isset( $args['attr_count'] ) ? (int) $args['attr_count'] : 2;
	$attr_index      = isset( $args['attr_index'] ) ? (int) $args['attr_index'] : 1;
	if ( 2 < $attr_count || ! $as_activate_atc ) {
		$as_quickadd_txt = $as_more_opt_txt;
	}

	$single_attribute = false;
	$single_attr_oos  = array();

	$_variations = array();
	$_var_images = array();
	$_gal_images = array();
	$any_attrib  = false;
	$variations  = commercekit_get_available_variations( $product, false, false );
	if ( is_array( $variations ) && count( $variations ) ) {
		foreach ( $variations as $variation ) {
			if ( isset( $variation['attributes'] ) && count( $variation['attributes'] ) ) {
				$variation_img_id = isset( $variation['cgkit_image_id'] ) ? $variation['cgkit_image_id'] : get_post_thumbnail_id( $variation['variation_id'] );
				foreach ( $variation['attributes'] as $a_key => $a_value ) {
					$a_key = str_ireplace( 'attribute_', '', $a_key );

					$_variations[ $a_key ][] = $a_value;
					if ( $variation_img_id ) {
						$_var_images[ $a_key ][ $a_value ] = $variation_img_id;
					}
					if ( '' === $a_value ) {
						$any_attrib = true;
					} else {
						if ( 1 === count( $variation['attributes'] ) ) {
							$single_attribute = true;
							if ( isset( $variation['is_in_stock'] ) && 1 !== (int) $variation['is_in_stock'] ) {
								$single_attr_oos[ $a_key ][ $a_value ] = true;
							}
						}
					}
				}
			}
		}
		$cgkit_image_gallery = get_post_meta( $product_id, 'commercekit_image_gallery', true );
		if ( is_array( $cgkit_image_gallery ) ) {
			$cgkit_image_gallery = array_filter( $cgkit_image_gallery );
		}
		if ( is_array( $cgkit_image_gallery ) && count( $cgkit_image_gallery ) ) {
			foreach ( $cgkit_image_gallery as $slug => $image_gallery ) {
				if ( 'global_gallery' === $slug ) {
					continue;
				}
				$images = explode( ',', trim( $image_gallery ) );
				if ( isset( $images[0] ) && ! empty( $images[0] ) ) {
					$slugs = explode( '_cgkit_', $slug );
					if ( count( $slugs ) ) {
						foreach ( $slugs as $slg ) {
							$_gal_images[ $slg ] = $images[0];
						}
					}
				}
			}
		}
	} else {
		return $html;
	}

	if ( class_exists( 'B2bking' ) || class_exists( 'B2bkingcore' ) ) {
		if ( function_exists( 'is_product' ) && is_product() ) {
			$any_attrib = true;
		}
	}

	$attribute_css  = isset( $args['css_class'] ) && ! empty( $args['css_class'] ) ? $args['css_class'] : 'cgkit-as-wrap';
	$item_class     = '';
	$item_wrp_class = '';
	$item_oos_text  = esc_html__( 'Out of stock', 'commercegurus-commercekit' );
	$swatches_html  = sprintf( '<div class="%s"><span class="cgkit-swatch-title">%s</span><fieldset class="cgkit-attribute-swatches-wrap"><legend class="cgkit-sr-only">%s</legend><ul class="cgkit-attribute-swatches %s" data-attribute="%s" data-no-selection="%s">', $attribute_css, $as_quickadd_txt, $legend_label, $item_wrp_class, $attribute_name, esc_html__( 'No selection', 'commercegurus-commercekit' ) );
	foreach ( $attr_terms as $item ) {
		if ( ! isset( $attribute_swatches[ $attribute_id ] ) ) {
			$attribute_swatches[ $attribute_id ] = array();
		}
		if ( ! isset( $attribute_swatches[ $attribute_id ][ $item->term_id ] ) ) {
			$attribute_swatches[ $attribute_id ][ $item->term_id ]['btn'] = $item->name;
		}
		if ( $is_taxonomy && ! in_array( $item->slug, $args['options'], true ) ) {
			continue;
		}
		if ( $is_taxonomy ) {
			if ( ! $any_attrib && ( ! isset( $_variations[ $attribute_raw ] ) || ! in_array( $item->slug, $_variations[ $attribute_raw ], true ) ) ) {
				continue;
			}
		} else {
			if ( ! $any_attrib && ( ! isset( $_variations[ $attribute_raw ] ) || ! in_array( $item->name, $_variations[ $attribute_raw ], true ) ) ) {
				continue;
			}
		}
		$item_attri_val = $is_taxonomy ? $item->slug : $item->name;
		$selected       = $args['selected'] === $item_attri_val ? 'cgkit-swatch-selected' : '';
		if ( $as_button_style && 'button' === $swatch_type ) {
			$selected .= ' button-fluid';
		}

		$item_term_id = $item->term_id;
		if ( has_filter( 'wpml_current_language' ) && has_filter( 'wpml_object_id' ) ) {
			$item_category = 'category';
			if ( isset( $item->taxonomy ) ) {
				$item_category = $item->taxonomy;
			}
			$wpml_language = apply_filters( 'wpml_current_language', null );
			$item_term_id  = apply_filters( 'wpml_object_id', $item->term_id, $item_category, true, $wpml_language );
			if ( ! isset( $attribute_swatches[ $attribute_id ][ $item_term_id ] ) ) {
				$item_term_id = $item->term_id;
			}
		}

		$swatch_html = commercekit_as_get_swatch_html( $swatch_type, $attribute_swatches[ $attribute_id ][ $item_term_id ], $item );
		$item_title  = 'button' === $swatch_type && isset( $attribute_swatches[ $attribute_id ][ $item->term_id ]['btn'] ) ? $attribute_swatches[ $attribute_id ][ $item->term_id ]['btn'] : $item->name;
		if ( isset( $single_attr_oos[ $attribute_raw ][ $item_attri_val ] ) && true === $single_attr_oos[ $attribute_raw ][ $item_attri_val ] ) {
			$selected  .= ' cgkit-as-outofstock';
			$item_title = $item_title . ' - ' . $item_oos_text;
		}
		if ( $single_attribute ) {
			$selected .= ' cgkit-as-single';
		}
		$item_tooltip = '';
		if ( in_array( $swatch_type, array( 'color', 'image' ), true ) ) {
			$item_tooltip = ' data-cgkit-tooltip="' . $item_title . '"';
		}
		$gal_img_slug   = is_numeric( $item->term_id ) ? $item->term_id : sanitize_title( $item->term_id );
		$item_gimg_id   = isset( $_gal_images[ $gal_img_slug ] ) ? $_gal_images[ $gal_img_slug ] : '';
		$swatches_html .= sprintf( '<li class="cgkit-attribute-swatch cgkit-%s %s" %s><button type="button" data-type="%s" data-attribute-value="%s" data-attribute-text="%s" aria-label="%s" data-oos-text="%s" class="swatch cgkit-swatch %s" data-gimg_id="%s">%s</button></li>', $swatch_type, $item_class, $item_tooltip, $swatch_type, esc_attr( $item_attri_val ), esc_attr( $item->name ), esc_attr( $item_title ), $item_oos_text, $selected, $item_gimg_id, $swatch_html );
	}
	$swatches_html .= '</ul></fieldset></div>';
	if ( isset( $args['css_class'] ) && 'cgkit-as-wrap-plp' === $args['css_class'] ) {
		$html = str_ireplace( ' id="', ' data-id="', $html );
	}
	$swatches_html .= sprintf( '<div style="display: none;">%s</div>', $html );

	return $swatches_html;
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'commercekit_attribute_swatches_options_html', 10, 2 );

/**
 * Attribute swatches attribute label.
 *
 * @param string $label attribute label.
 * @param string $name attribute name.
 */
function commercekit_attribute_swatches_attribute_label( $label, $name ) {
	global $product, $cgkit_as_caching;

	$commercekit_options = get_option( 'commercekit', array() );
	$commercekit_flags   = commercekit_feature_flags()->get_flags();
	$cgkit_as_enabled    = defined( 'COMMERCEKIT_ATTRIBUTE_SWATCHES_VISIBLE' ) ? ( COMMERCEKIT_ATTRIBUTE_SWATCHES_VISIBLE ? 1 : 0 ) : ( isset( $commercekit_flags['attribute_swatches'] ) && 1 === (int) $commercekit_flags['attribute_swatches'] ? 1 : 0 );
	if ( ! $cgkit_as_enabled && ( ! isset( $cgkit_as_caching ) || false === $cgkit_as_caching ) ) {
		return $label;
	}

	if ( $product && method_exists( $product, 'is_type' ) && $product->is_type( 'variable' ) && is_product() ) {
		$attribute_swatches = get_post_meta( $product->get_id(), 'commercekit_attribute_swatches', true );
		if ( isset( $attribute_swatches['enable_product'] ) && 0 === (int) $attribute_swatches['enable_product'] ) {
			return $label;
		}
		$css_class = 'attribute_' . sanitize_title( $name );
		return sprintf( '<strong>%s</strong><span class="ckit-chosen-attribute_semicolon">:</span> <span class="cgkit-chosen-attribute %s no-selection">%s</span>', $label, $css_class, esc_html__( 'No selection', 'commercegurus-commercekit' ) );
	} else {
		return $label;
	}
}
add_filter( 'woocommerce_attribute_label', 'commercekit_attribute_swatches_attribute_label', 102, 2 );

/**
 * Attribute swatches get attribute slug.
 *
 * @param string $slug   slug of attribute.
 * @param bool   $prefix prefix of attribute.
 */
function commercekit_as_get_attribute_slug( $slug, $prefix = false ) {
	if ( ( 'pa_' !== substr( $slug, 0, 3 ) || $prefix ) && false === strpos( $slug, 'attribute_' ) ) {
		$slug = 'attribute_' . sanitize_title( $slug );
	}

	return $slug;
}

/**
 * Attribute swatches get swatch html.
 *
 * @param string $swatch_type type of swatch.
 * @param string $data data of attribute.
 * @param string $item data of term.
 */
function commercekit_as_get_swatch_html( $swatch_type, $data, $item ) {
	$swatch_html = '';

	if ( 'image' === $swatch_type ) {
		$image = null;
		if ( isset( $data['img'] ) && ! empty( $data['img'] ) ) {
			$cgkit_image_swatch = commercekit_as_get_image_swatch_size();
			commercekit_as_generate_attachment_size( $data['img'], $cgkit_image_swatch );
			$image = wp_get_attachment_image_src( $data['img'], $cgkit_image_swatch );
		}
		if ( $image ) {
			$swatch_html = '<span class="cross">&nbsp;</span><img alt="' . esc_attr( $item->name ) . '" width="' . esc_attr( $image[1] ) . '" height="' . esc_attr( $image[2] ) . '" src="' . esc_url( $image[0] ) . '" />';
		} else {
			$swatch_html = '<span class="cross">&nbsp;</span>';
		}
	} elseif ( 'color' === $swatch_type ) {
		if ( isset( $data['clr'] ) && ! empty( $data['clr'] ) ) {
			$bg_color2  = isset( $data['clr2'] ) ? $data['clr2'] : '';
			$bg_type    = isset( $data['ctyp'] ) ? (int) $data['ctyp'] : 1;
			$background = $data['clr'];
			if ( 2 === $bg_type && ! empty( $bg_color2 ) ) {
				$background = 'linear-gradient(135deg, ' . $data['clr'] . ' 50%, ' . $bg_color2 . ' 50%)';
			}
			$swatch_html = '<span class="cross">&nbsp;</span><span class="color-div" style="background: ' . esc_attr( $background ) . ';" data-color="' . esc_attr( $data['clr'] ) . '" aria-hidden="true">&nbsp;' . esc_attr( $item->name ) . '</span>';
		} else {
			$swatch_html = '<span class="cross">&nbsp;</span><span class="color-div" style="" data-color="" aria-hidden="true">&nbsp;' . esc_attr( $item->name ) . '</span>';
		}
	} elseif ( 'button' === $swatch_type ) {
		if ( isset( $data['btn'] ) && strlen( $data['btn'] ) ) {
			$swatch_html = '<span class="cross">&nbsp;</span>' . esc_attr( $data['btn'] );
		} else {
			$swatch_html = '<span class="cross">&nbsp;</span>';
		}
	}

	return $swatch_html;
}

/**
 * Attribute swatches add image size.
 */
function commercekit_as_add_image_size() {
	$cgkit_image_swatch = commercekit_as_get_image_swatch_size();
	if ( 'cgkit_image_swatch' === $cgkit_image_swatch ) {
		add_image_size( $cgkit_image_swatch, 100, 100, true );
	}
}
add_action( 'init', 'commercekit_as_add_image_size' );

/**
 * Attribute swatches filterable image swatch size.
 */
function commercekit_as_get_image_swatch_size() {
	return apply_filters( 'commercekit_as_image_swatch_size', 'cgkit_image_swatch' );
}

/**
 * Attribute swatches generate attachment size if not exist.
 *
 * @param string $attachment_id image ID.
 * @param string $size image size.
 */
function commercekit_as_generate_attachment_size( $attachment_id, $size ) {
	if ( ! function_exists( 'wp_crop_image' ) ) {
		include ABSPATH . 'wp-admin/includes/image.php';
	}

	$old_metadata = wp_get_attachment_metadata( $attachment_id );
	if ( isset( $old_metadata['sizes'][ $size ] ) ) {
		return;
	}

	$fullsizepath = get_attached_file( $attachment_id );
	if ( false === $fullsizepath || is_wp_error( $fullsizepath ) || ! file_exists( $fullsizepath ) ) {
		return;
	}

	$new_metadata = wp_generate_attachment_metadata( $attachment_id, $fullsizepath );
	if ( is_wp_error( $new_metadata ) || empty( $new_metadata ) ) {
		return;
	}

	wp_update_attachment_metadata( $attachment_id, $new_metadata );
}

/**
 * Check whether WooCommerce Composite product or not.
 */
function commercegurus_as_is_wc_composite_product() {
	global $cgkit_as_wc_cp;

	if ( true === $cgkit_as_wc_cp ) {
		return $cgkit_as_wc_cp;
	}

	$cgkit_wccp_actions = array( 'woocommerce_show_composited_product', 'woocommerce_show_component_options' );
	if ( isset( $_GET['wc-ajax'] ) && ! empty( $_GET['wc-ajax'] ) ) { // phpcs:ignore
		if ( isset( $_REQUEST['action'] ) && ! empty( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], $cgkit_wccp_actions, true ) ) { // phpcs:ignore
			$cgkit_as_wc_cp = true;
			return $cgkit_as_wc_cp;
		}
	}

	return false;
}

/**
 * Build product swatch cache
 *
 * @param string $product product object.
 * @param string $return return HTML.
 * @param string $suffix logger suffix text.
 */
function commercekit_as_build_product_swatch_cache( $product, $return = false, $suffix = '' ) {
	global $cgkit_as_cached_keys, $cgkit_as_caching;
	$product_id = $product ? $product->get_id() : 0;
	if ( ! $product_id ) {
		return;
	}
	$cache_key  = 'cgkit_swatch_loop_form_' . $product_id;
	$cache_key2 = 'cgkit_swatch_loop_form_data_' . $product_id;

	if ( isset( $cgkit_as_cached_keys[ $cache_key ] ) && $cgkit_as_cached_keys[ $cache_key ] ) {
		return;
	}

	if ( ! empty( $suffix ) ) {
		$suffix = ' ' . $suffix;
	}

	$cgkit_as_caching    = true;
	$commercekit_options = get_option( 'commercekit', array() );
	$cgkit_variations    = commercekit_get_available_variations( $product, true );
	commercekit_as_log( 'building swatches cache for product id ' . $product_id . $suffix );
	$get_variations       = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
	$available_variations = $get_variations ? $cgkit_variations : false;
	$cgkit_images         = array();
	$images_data          = array();
	if ( is_array( $available_variations ) && count( $available_variations ) ) {
		foreach ( $available_variations as $variation ) {
			if ( isset( $variation['attributes'] ) && count( $variation['attributes'] ) ) {
				$variation_img_id = isset( $variation['cgkit_image_id'] ) ? $variation['cgkit_image_id'] : get_post_thumbnail_id( $variation['variation_id'] );
				if ( $variation_img_id ) {
					$cgkit_images[] = $variation_img_id;
				}
			}
		}
	}

	$attributes      = $product->get_variation_attributes();
	$as_quickadd_txt = isset( $commercekit_options['as_quickadd_txt'] ) && ! empty( $commercekit_options['as_quickadd_txt'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['as_quickadd_txt'] ) ) : commercekit_get_default_settings( 'as_quickadd_txt' );
	$as_more_opt_txt = isset( $commercekit_options['as_more_opt_txt'] ) && ! empty( $commercekit_options['as_more_opt_txt'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['as_more_opt_txt'] ) ) : commercekit_get_default_settings( 'as_more_opt_txt' );
	$as_activate_atc = isset( $commercekit_options['as_activate_atc'] ) && 1 === (int) $commercekit_options['as_activate_atc'] ? true : false;
	if ( 2 < count( $attributes ) || ! $as_activate_atc ) {
		$as_quickadd_txt = $as_more_opt_txt;
	}

	$cgkit_image_gallery = get_post_meta( $product_id, 'commercekit_image_gallery', true );
	if ( is_array( $cgkit_image_gallery ) ) {
		$cgkit_image_gallery = array_filter( $cgkit_image_gallery );
	}
	$attribute_swatches = get_post_meta( $product_id, 'commercekit_attribute_swatches', true );
	if ( is_array( $cgkit_image_gallery ) && count( $cgkit_image_gallery ) ) {
		foreach ( $cgkit_image_gallery as $slug => $image_gallery ) {
			if ( 'global_gallery' === $slug ) {
				continue;
			}
			$images = explode( ',', trim( $image_gallery ) );
			if ( isset( $images[0] ) && ! empty( $images[0] ) ) {
				$cgkit_images[] = $images[0];
			}
		}
	}
	$cgkit_images = array_unique( $cgkit_images );
	if ( count( $cgkit_images ) ) {
		foreach ( $cgkit_images as $image_id ) {
			$image_data = commercekit_as_get_loop_swatch_image( $image_id );
			if ( $image_data ) {
				$images_data[ 'img_' . $image_id ] = $image_data;
			}
		}
	}

	$nattributes = array_map( 'array_filter', $attributes );
	$nattributes = array_filter( $nattributes );
	if ( ! count( $nattributes ) ) {
		commercekit_as_log( 'no attributes founds - skipping setting a transient for product id ' . $product_id . $suffix );
		commercekit_as_log( 'swatch cache complete for product id ' . $product_id . $suffix );
		commercekit_update_swatches_cache_count( $product_id, 0 );
		$message = commercekit_get_as_totals_log_message();
		commercekit_as_log( $message . $suffix );
		$cgkit_as_cached_keys[ $cache_key ] = true;

		$cgkit_as_caching = false;
		return;
	}
	$selected_attributes = $product->get_default_attributes();
	$attribute_keys      = array_keys( $attributes );
	$for_json_data       = array(
		'variations' => false === $available_variations ? false : 'cgkit_cache',
		'images'     => $images_data,
	);
	$data_variations     = 'false';
	$data_images         = '[]';
	$data_form_class     = 'cgkit-no-actions';
	$variations_json     = wp_json_encode( $for_json_data );
	ob_start();
	require dirname( __FILE__ ) . '/templates/product-attribute-swatches.php';
	$swatches_html = ob_get_clean();
	if ( $swatches_html ) {
		set_transient( $cache_key, $swatches_html, 2 * DAY_IN_SECONDS );
		commercekit_as_log( 'setting transient ' . $cache_key . ' for product id ' . $product_id . $suffix );
		set_transient( $cache_key2, $variations_json, 2 * DAY_IN_SECONDS );
		commercekit_as_log( 'setting transient ' . $cache_key2 . ' for product id ' . $product_id . $suffix );
	}

	/* Noajax variations */
	$data_form_class = 'variations_form cart';
	$cache_key3      = 'cgkit_swatch_loop_full_' . $product_id;
	$variations_json = wp_json_encode( $available_variations );
	$data_variations = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
	$images_json     = wp_json_encode( $images_data );
	$data_images     = function_exists( 'wc_esc_json' ) ? wc_esc_json( $images_json ) : _wp_specialchars( $images_json, ENT_QUOTES, 'UTF-8', true );
	ob_start();
	require dirname( __FILE__ ) . '/templates/product-attribute-swatches.php';
	$swatches_html = ob_get_clean();
	if ( $swatches_html ) {
		set_transient( $cache_key3, $swatches_html, 2 * DAY_IN_SECONDS );
		commercekit_as_log( 'setting transient ' . $cache_key3 . ' for product id ' . $product_id . $suffix );
	}
	/* End Noajax variations */

	$cgkit_as_cached_keys[ $cache_key ] = true;
	commercekit_update_swatches_cache_count( $product_id );
	commercekit_as_log( 'swatch cache complete for product id ' . $product_id . $suffix );
	$message = commercekit_get_as_totals_log_message();
	commercekit_as_log( $message . $suffix );
	$cgkit_as_caching = false;

	commercekit_attribute_gallery_generate_single_json( $product, $cgkit_variations );

	if ( $return ) {
		return $swatches_html;
	}
}

/**
 * Update product attribute swatches cache
 *
 * @param string $post_id post ID.
 * @param string $post post.
 */
function commercegurus_update_product_as_data( $post_id, $post ) {
	global $product;
	if ( 'product' !== $post->post_type ) {
		return;
	}
	$product = wc_get_product( $post_id );
	if ( ! $product || ( method_exists( $product, 'is_type' ) && ! $product->is_type( 'variable' ) ) ) {
		return;
	}
	commercekit_as_build_product_swatch_cache( $product, false, 'via update product' );
}
add_action( 'woocommerce_process_product_meta', 'commercegurus_update_product_as_data', 10, 2 );

/**
 * Update product attribute swatches cache on stock, variations updates
 *
 * @param string $product_id product ID.
 */
function commercegurus_update_product_as_cache( $product_id ) {
	global $product;
	$product = wc_get_product( $product_id );
	if ( ! $product || ( method_exists( $product, 'is_type' ) && ! $product->is_type( 'variable' ) ) ) {
		return;
	}
	commercekit_as_build_product_swatch_cache( $product, false, 'via save variations' );
}
add_action( 'woocommerce_updated_product_stock', 'commercegurus_update_product_as_cache', 10, 1 );
add_action( 'woocommerce_save_product_variation', 'commercegurus_update_product_as_cache', 10, 1 );
add_action( 'woocommerce_ajax_save_product_variations', 'commercegurus_update_product_as_cache', 10, 1 );

/**
 * Update product attribute swatches cache on quick edit updates
 *
 * @param string $product_id product ID.
 * @param string $post       post object.
 */
function commercegurus_quick_edit_update_product_as_cache( $product_id, $post ) {
	global $product;
	if ( isset( $post->post_type ) && 'product' === $post->post_type ) {
		$product = wc_get_product( $product_id );
		if ( ! $product || ( method_exists( $product, 'is_type' ) && ! $product->is_type( 'variable' ) ) ) {
			return;
		}
		commercekit_as_build_product_swatch_cache( $product, false, 'via update product' );
	}
}
add_action( 'save_post', 'commercegurus_quick_edit_update_product_as_cache', 10, 2 );

/**
 * Update product attribute swatches cache on stock updates
 *
 * @param string $product_obj product object.
 */
function commercegurus_update_product_as_cache_stock_updates( $product_obj ) {
	global $product;
	if ( $product_obj->is_type( 'variation' ) ) {
		$product_id = $product_obj->get_parent_id();
		$product    = wc_get_product( $product_id );
	} else {
		$product = $product_obj;
	}
	if ( ! $product || ( method_exists( $product, 'is_type' ) && ! $product->is_type( 'variable' ) ) ) {
		return;
	}
	commercekit_as_build_product_swatch_cache( $product, false, 'via stock update' );
}
add_action( 'woocommerce_product_set_stock', 'commercegurus_update_product_as_cache_stock_updates', 10, 1 );
add_action( 'woocommerce_variation_set_stock', 'commercegurus_update_product_as_cache_stock_updates', 10, 1 );

/**
 * Update product attribute swatches cache on delete variation
 *
 * @param string $postid variation ID.
 * @param string $post variation post.
 */
function commercegurus_update_product_as_cache_delete_variation( $postid, $post ) {
	global $product;
	if ( $post && 'product_variation' === $post->post_type ) {
		$product_id = $post->post_parent;
		$product    = wc_get_product( $product_id );
		if ( ! $product || ( method_exists( $product, 'is_type' ) && ! $product->is_type( 'variable' ) ) ) {
			return;
		}
		commercekit_as_build_product_swatch_cache( $product, false, 'via delete variation' );
	}
}
add_action( 'deleted_post', 'commercegurus_update_product_as_cache_delete_variation', 10, 2 );

/**
 * Prepare action scheduler for attribute swatches all cache
 */
function commercekit_as_prepare_action_scheduler() {
	global $wpdb;
	$options = get_option( 'commercekit', array() );

	$as_scheduled = isset( $options['commercekit_as_scheduled'] ) && 1 === (int) $options['commercekit_as_scheduled'] ? true : false;
	if ( $as_scheduled ) {
		return;
	}
	$build_clear = isset( $options['commercekit_as_scheduled_clear'] ) ? $options['commercekit_as_scheduled_clear'] : 0;
	if ( 0 < $build_clear && ( ( $build_clear + 5 ) > time() ) ) {
		return;
	}

	commercekit_as_log( 'running commercekit_as_prepare_action_scheduler - preparing action scheduler for caching' );
	$args = array(
		'hook'     => 'commercekit_attribute_swatch_build_cache_list',
		'per_page' => -1,
		'group'    => 'commercekit',
		'status'   => ActionScheduler_Store::STATUS_PENDING,
	);

	$action_ids = as_get_scheduled_actions( $args, 'ids' );
	if ( ! count( $action_ids ) ) {
		as_schedule_single_action( time() + 5, 'commercekit_attribute_swatch_build_cache_list', array(), 'commercekit' );
		commercekit_as_log( 'REBUILDING CACHE: creating single action for commercekit_attribute_swatch_build_cache_list hook' );
	}

	$args2 = array(
		'hook'     => 'commercekit_attribute_swatch_build_cache_missing',
		'per_page' => -1,
		'group'    => 'commercekit',
	);

	$action_ids2 = as_get_scheduled_actions( $args2, 'ids' );
	if ( ! count( $action_ids2 ) ) {
		as_schedule_recurring_action( time() + 5, 15 * MINUTE_IN_SECONDS, 'commercekit_attribute_swatch_build_cache_missing', array(), 'commercekit' );
	}

	$options['commercekit_as_scheduled'] = 1;
	commercekit_as_log( 'updating commercekit_as_scheduled to 1' );
	$options['commercekit_as_scheduled_status'] = 'created';
	$options['commercekit_as_actions_created']  = time();
	$options['commercekit_as_scheduled_clear']  = 0;
	commercekit_as_log( 'updating commercekit_as_scheduled_clear to 0' );
	update_option( 'commercekit', $options, false );
}
add_action( 'init', 'commercekit_as_prepare_action_scheduler' );

/**
 * Run action scheduler list for attribute swatches cache
 *
 * @param  array $params array of arguments.
 */
function commercekit_as_run_action_scheduler_list( $params = array() ) {
	global $wpdb;
	$options = get_option( 'commercekit', array() );

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

	$query = new WP_Query( $args );
	$total = (int) $query->found_posts;
	if ( $total ) {
		commercekit_as_log( 'CREATING SWATCHES CACHE EVENTS: executing the commercekit_attribute_swatch_build_cache_list hook' );
		$product_ids = wp_parse_id_list( $query->posts );
		foreach ( $product_ids as $product_id ) {
			$args2 = array(
				'hook'   => 'commercekit_attribute_swatch_build_cache',
				'args'   => array( 'product_id' => $product_id ),
				'group'  => 'commercekit',
				'status' => ActionScheduler_Store::STATUS_PENDING,
			);

			$action_ids2 = as_get_scheduled_actions( $args2, 'ids' );
			if ( count( $action_ids2 ) ) {
				commercekit_as_log( 'skip creating single action to create commercekit_attribute_swatch_build_cache for product id ' . $product_id . ' due to pending action' );
				continue;
			} else {
				as_schedule_single_action( time(), 'commercekit_attribute_swatch_build_cache', array( 'product_id' => $product_id ), 'commercekit' );
				commercekit_as_log( 'creating single action to create commercekit_attribute_swatch_build_cache for product id ' . $product_id );
			}
		}

		$options['commercekit_as_scheduled_status'] = 'created';
		$options['commercekit_as_actions_created']  = time();
		update_option( 'commercekit', $options, false );
	}
}
add_action( 'commercekit_attribute_swatch_build_cache_list', 'commercekit_as_run_action_scheduler_list', 10, 1 );

/**
 * Run action scheduler for missing attribute swatches cache
 *
 * @param  array $params array of arguments.
 */
function commercekit_as_run_action_scheduler_missing( $params = array() ) {
	global $wpdb;
	$options   = get_option( 'commercekit', array() );
	$as_status = isset( $options['commercekit_as_scheduled_status'] ) ? $options['commercekit_as_scheduled_status'] : '';
	if ( 'completed' === $as_status ) {
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

		$query = new WP_Query( $args );
		$total = (int) $query->found_posts;
		if ( $total ) {
			$table = $wpdb->prefix . 'commercekit_swatches_cache_count';
			$sql   = 'SELECT COUNT(*) FROM ' . $table;
			$count = (int) $wpdb->get_var( $sql ); // phpcs:ignore
			if ( $total !== $count ) {
				$product_ids  = wp_parse_id_list( $query->posts );
				$sql2         = 'SELECT product_id FROM ' . $table;
				$product_ids2 = $wpdb->get_col( $sql2 ); // phpcs:ignore
				$product_ids3 = array_diff( $product_ids, $product_ids2 );
				if ( count( $product_ids3 ) ) {
					commercekit_as_log( 'CREATING SWATCHES CACHE EVENTS: executing the commercekit_attribute_swatch_build_cache_missing hook' );
					foreach ( $product_ids3 as $product_id ) {
						$args2 = array(
							'hook'   => 'commercekit_attribute_swatch_build_cache',
							'args'   => array( 'product_id' => $product_id ),
							'group'  => 'commercekit',
							'status' => ActionScheduler_Store::STATUS_PENDING,
						);

						$action_ids2 = as_get_scheduled_actions( $args2, 'ids' );
						if ( count( $action_ids2 ) ) {
							commercekit_as_log( 'skip creating single action to create commercekit_attribute_swatch_build_cache for missing product id ' . $product_id . ' due to pending action' );
							continue;
						} else {
							as_schedule_single_action( time(), 'commercekit_attribute_swatch_build_cache', array( 'product_id' => $product_id ), 'commercekit' );
							commercekit_as_log( 'creating single action to create commercekit_attribute_swatch_build_cache for missing product id ' . $product_id );
						}
					}
				}
			}
		}
	}
}
add_action( 'commercekit_attribute_swatch_build_cache_missing', 'commercekit_as_run_action_scheduler_missing', 10, 1 );

/**
 * Run action scheduler for attribute swatches cache
 *
 * @param  array $args array of arguments.
 */
function commercekit_as_run_action_scheduler( $args ) {
	global $wpdb, $product;
	$options    = get_option( 'commercekit', array() );
	$product_id = 0;
	if ( is_numeric( $args ) ) {
		$product_id = (int) $args;
	} elseif ( is_array( $args ) ) {
		if ( isset( $args[0] ) && is_numeric( $args[0] ) ) {
			$product_id = (int) $args[0];
		} elseif ( isset( $args['product_id'] ) && is_numeric( $args['product_id'] ) ) {
			$product_id = (int) $args['product_id'];
		}
	}

	if ( $product_id ) {
		$table = $wpdb->prefix . 'commercekit_swatches_cache_count';
		$sql   = 'SELECT * FROM ' . $table . ' WHERE product_id = \'' . $product_id . '\'';
		$row   = $wpdb->get_row( $sql ); // phpcs:ignore
		if ( $row ) {
			return;
		}

		commercekit_as_log( 'CREATING SWATCHES CACHE EVENT FOR PRODUCT: executing commercekit_attribute_swatch_build_cache hook for product id ' . $product_id );
		$product = wc_get_product( $product_id );
		if ( $product && method_exists( $product, 'is_type' ) && $product->is_type( 'variable' ) ) {
			try {
				commercekit_as_build_product_swatch_cache( $product, false, 'via Action Scheduler' );
			} catch ( Exception $e ) {
				$product = null;
			}
		}

		$options['commercekit_as_scheduled_status'] = 'processing';
		$options['commercekit_as_scheduled_done']   = time();
		update_option( 'commercekit', $options, false );
	}
}
add_action( 'commercekit_attribute_swatch_build_cache', 'commercekit_as_run_action_scheduler', 10, 1 );

/**
 * Run action scheduler cancel for attribute swatches cache
 */
function commercekit_as_run_action_scheduler_cancel() {
	$ajax    = array();
	$options = get_option( 'commercekit', array() );

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json( $ajax );
	}

	$options['commercekit_as_scheduled_status'] = 'cancelled';
	update_option( 'commercekit', $options, false );

	$as_store = ActionScheduler::store();
	$as_store->cancel_actions_by_hook( 'commercekit_attribute_swatch_build_cache' );
	commercekit_as_log( 'The caching process has been cancelled.' );

	$ajax['status']  = 0;
	$ajax['message'] = esc_html__( 'The caching process has been cancelled.', 'commercegurus-commercekit' );

	wp_send_json( $ajax );
}
add_action( 'wp_ajax_commercekit_get_as_build_cancel', 'commercekit_as_run_action_scheduler_cancel', 10, 1 );

/**
 * Update swatches cache count
 *
 * @param string $product_id product ID.
 * @param string $cached whether cached or not.
 */
function commercekit_update_swatches_cache_count( $product_id, $cached = 1 ) {
	global $wpdb;
	$table = $wpdb->prefix . 'commercekit_swatches_cache_count';
	$sql   = 'SELECT * FROM ' . $table . ' WHERE product_id = \'' . $product_id . '\'';
	$row   = $wpdb->get_row( $sql ); // phpcs:ignore
	if ( $row ) {
		$data   = array(
			'cached'  => $cached,
			'updated' => time(),
		);
		$where  = array(
			'product_id' => $product_id,
		);
		$format = array( '%d', '%d' );
		$wpdb->update( $table, $data, $where, $format ); // db call ok; no-cache ok.
	} else {
		$data   = array(
			'product_id' => $product_id,
			'cached'     => $cached,
			'updated'    => time(),
		);
		$format = array( '%s', '%d', '%d' );
		$wpdb->insert( $table, $data, $format ); // db call ok; no-cache ok.
	}
}

/**
 * Prepare available variations
 *
 * @param string $product product object.
 */
function commercekit_prepare_available_variations( $product ) {
	$variation_ids        = $product->get_children();
	$available_variations = array();
	$variation_errors     = array();

	foreach ( $variation_ids as $variation_id ) {
		$variation = wc_get_product( $variation_id );
		if ( ! $variation || ! $variation->exists() ) {
			continue;
		}

		if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $product->get_id(), $variation ) && ! $variation->variation_is_visible() ) {
			continue;
		}

		try {
			$available_variations[] = $product->get_available_variation( $variation );
		} catch ( \Throwable $e ) { // phpcs:ignore
			$variation_errors[] = $e->getMessage();
		}
	}

	$available_variations = array_values( array_filter( $available_variations ) );

	return $available_variations;
}

/**
 * Get available variations
 *
 * @param string $product product object.
 * @param string $rebuild rebuild existing cache.
 * @param string $oos_filter apply out of stock filter.
 */
function commercekit_get_available_variations( $product, $rebuild = false, $oos_filter = true ) {
	global $cgkit_variations_cached;

	if ( isset( $_GET['cgkit-nocache'] ) ) { // phpcs:ignore
		return commercekit_prepare_available_variations( $product );
	}

	$product_id = $product ? $product->get_id() : 0;
	if ( ! $product_id ) {
		return array();
	}

	$suffix = '';
	if ( $oos_filter ) {
		$suffix = '_1';
	}

	$cache_key4 = 'cgkit_swatch_loop_form_variations_' . $product_id;
	$cache_key5 = $cache_key4 . $suffix;
	if ( ! $rebuild && isset( $cgkit_variations_cached[ $cache_key5 ] ) ) {
		return $cgkit_variations_cached[ $cache_key5 ];
	}

	$swatches_html = get_transient( $cache_key4 );
	$variations    = array();
	if ( false !== $swatches_html ) {
		$variations = json_decode( $swatches_html, true );
	}
	if ( $rebuild || false === $swatches_html ) {
		$variations = commercekit_prepare_available_variations( $product );
		set_transient( $cache_key4, wp_json_encode( $variations ), 2 * DAY_IN_SECONDS );
		commercekit_as_log( 'setting transient ' . $cache_key4 . ' for product id ' . $product_id . ' via get_available_variations function' );
	}

	if ( $oos_filter && 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
		foreach ( $variations as $key => $variation ) {
			if ( ! isset( $variation['is_in_stock'] ) || 1 !== (int) $variation['is_in_stock'] ) {
				unset( $variations[ $key ] );
			}
		}
		$variations = array_values( $variations );
	}

	$cgkit_variations_cached[ $cache_key5 ] = $variations;

	return $variations;
}

/**
 * Add extra variation data
 *
 * @param string $array array of variation data.
 * @param string $vproduct variable product object.
 * @param string $variation variation object.
 */
function commercekit_add_extra_variation_data( $array, $vproduct, $variation ) {
	$array['cgkit_stock_quantity']   = (int) $variation->get_stock_quantity();
	$array['cgkit_low_stock_amount'] = (int) $variation->get_low_stock_amount();

	$cgkit_image_id          = get_post_thumbnail_id( $array['variation_id'] );
	$array['cgkit_image_id'] = $cgkit_image_id ? $cgkit_image_id : 0;
	return $array;
}
add_filter( 'woocommerce_available_variation', 'commercekit_add_extra_variation_data', 10, 3 );

/**
 * Dropdown variation attribute options
 *
 * @param string $args array of args.
 */
function commercekit_dropdown_variation_attribute_options_args( $args ) {
	$name  = isset( $args['attribute'] ) ? $args['attribute'] : '';
	$label = $name;
	if ( $name && taxonomy_is_product_attribute( $name ) ) {
		$slug   = wc_attribute_taxonomy_slug( $name );
		$labels = wc_get_attribute_taxonomy_labels();
		$label  = isset( $labels[ $slug ] ) ? $labels[ $slug ] : $slug;
	}
	$args['show_option_none'] = sprintf( esc_html__( 'Choose %s', 'commercegurus-commercekit' ), $label ); // phpcs:ignore

	return $args;
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'commercekit_dropdown_variation_attribute_options_args', 99, 1 );

/**
 * Attribute swatches WPML make duplicate
 *
 * @param string $master_post_id  master post id.
 * @param string $target_lang     target language.
 * @param string $post_array      post array.
 * @param string $target_post_id  target post id.
 */
function commercegurus_attribute_swatches_wpml_make_duplicate( $master_post_id, $target_lang, $post_array, $target_post_id ) {
	global $wpdb;
	if ( 'product' !== get_post_type( $master_post_id ) ) {
		return;
	}
	$product = wc_get_product( $master_post_id );
	if ( ! $product || ( method_exists( $product, 'is_type' ) && ! $product->is_type( 'variable' ) ) ) {
		return;
	}
	$swatches = get_post_meta( $master_post_id, 'commercekit_attribute_swatches', true );
	if ( ! is_array( $swatches ) || ! count( $swatches ) ) {
		return;
	}
	$attributes = commercegurus_attribute_swatches_load_attributes( $product );
	$term_taxes = array();
	$attr_taxes = array();
	if ( count( $attributes ) ) {
		foreach ( $attributes as $attribute ) {
			if ( isset( $attribute['taxo'] ) && 1 === (int) $attribute['taxo'] ) {
				$attr_taxes[] = (int) $attribute['id'];
				if ( is_array( $attribute['terms'] ) && count( $attribute['terms'] ) ) {
					foreach ( $attribute['terms'] as $term_tax ) {
						$term_taxes[ $term_tax->term_id ] = $term_tax->taxonomy;
					}
				}
			}
		}
	}
	$nswatches = array();
	foreach ( $swatches as $attribute_id => $swatch ) {
		$nswatch = array();
		if ( ! is_array( $swatch ) ) {
			$nswatch = $swatch;
		} elseif ( is_numeric( $attribute_id ) && in_array( (int) $attribute_id, $attr_taxes, true ) ) {
			foreach ( $swatch as $key => $value ) {
				$old_key = $key;
				if ( is_numeric( $key ) && array_key_exists( $key, $term_taxes ) ) {
					$key = apply_filters( 'wpml_object_id', $key, $term_taxes[ $key ], true, $target_lang );
					if ( isset( $value['img'] ) && ! empty( $value['img'] ) ) {
						$value['img'] = apply_filters( 'wpml_object_id', $value['img'], 'attachment', true, $target_lang );
					}
					if ( (int) $old_key !== (int) $key ) {
						$term_sql  = 'SELECT t.name FROM ' . $wpdb->prefix . 'terms AS t WHERE t.term_id = ' . (int) $key;
						$term_name = $wpdb->get_var( $term_sql ); // phpcs:ignore
						if ( ! empty( $term_name ) ) {
							$value['btn'] = $term_name;
						}
					}
				}
				$nswatch[ $key ] = $value;
			}
		} else {
			foreach ( $swatch as $key => $value ) {
				if ( isset( $value['img'] ) && ! empty( $value['img'] ) ) {
					$value['img'] = apply_filters( 'wpml_object_id', $value['img'], 'attachment', true, $target_lang );
				}
				$nswatch[ $key ] = $value;
			}
		}
		$nswatches[ $attribute_id ] = $nswatch;
	}
	update_post_meta( $target_post_id, 'commercekit_attribute_swatches', $nswatches );
}
add_action( 'icl_make_duplicate', 'commercegurus_attribute_swatches_wpml_make_duplicate', 10, 4 );


/**
 * Attribute swatches Polylang Pro copy metas
 *
 * @param string $meta_array     array of metadata.
 * @param string $sync           sync or copy.
 * @param string $master_post_id master post ID.
 * @param string $target_post_id target post id.
 * @param string $target_lang    target language code.
 */
function commercegurus_attribute_swatches_pll_copy_post_metas( $meta_array, $sync, $master_post_id, $target_post_id, $target_lang ) {
	global $wpdb;
	if ( 'product' !== get_post_type( $master_post_id ) ) {
		return $meta_array;
	}
	$product = wc_get_product( $master_post_id );
	if ( ! $product || ( method_exists( $product, 'is_type' ) && ! $product->is_type( 'variable' ) ) ) {
		return $meta_array;
	}
	$swatches = get_post_meta( $master_post_id, 'commercekit_attribute_swatches', true );
	if ( ! is_array( $swatches ) || ! count( $swatches ) ) {
		return $meta_array;
	}
	$attributes = commercegurus_attribute_swatches_load_attributes( $product );
	$term_taxes = array();
	$attr_taxes = array();
	if ( count( $attributes ) ) {
		foreach ( $attributes as $attribute ) {
			if ( isset( $attribute['taxo'] ) && 1 === (int) $attribute['taxo'] ) {
				$attr_taxes[] = (int) $attribute['id'];
				if ( is_array( $attribute['terms'] ) && count( $attribute['terms'] ) ) {
					foreach ( $attribute['terms'] as $term_tax ) {
						$term_taxes[ $term_tax->term_id ] = $term_tax->taxonomy;
					}
				}
			}
		}
	}
	$nswatches = array();
	foreach ( $swatches as $attribute_id => $swatch ) {
		$nswatch = array();
		if ( ! is_array( $swatch ) ) {
			$nswatch = $swatch;
		} elseif ( is_numeric( $attribute_id ) && in_array( (int) $attribute_id, $attr_taxes, true ) ) {
			foreach ( $swatch as $key => $value ) {
				$old_key = $key;
				if ( is_numeric( $key ) && array_key_exists( $key, $term_taxes ) ) {
					$key = commercekit_pll_get_term( $key, $target_lang );
					if ( isset( $value['img'] ) && ! empty( $value['img'] ) ) {
						$value['img'] = commercekit_pll_get_post( $value['img'], $target_lang );
					}
					if ( (int) $old_key !== (int) $key ) {
						$term_sql  = 'SELECT t.name FROM ' . $wpdb->prefix . 'terms AS t WHERE t.term_id = ' . (int) $key;
						$term_name = $wpdb->get_var( $term_sql ); // phpcs:ignore
						if ( ! empty( $term_name ) ) {
							$value['btn'] = $term_name;
						}
					}
				}
				$nswatch[ $key ] = $value;
			}
		} else {
			foreach ( $swatch as $key => $value ) {
				if ( isset( $value['img'] ) && ! empty( $value['img'] ) ) {
					$value['img'] = commercekit_pll_get_post( $value['img'], $target_lang );
				}
				$nswatch[ $key ] = $value;
			}
		}
		$nswatches[ $attribute_id ] = $nswatch;
	}
	update_post_meta( $target_post_id, 'commercekit_attribute_swatches', $nswatches );

	$meta_keys = array( 'commercekit_attribute_swatches' );
	foreach ( $meta_array as $key => $meta_key ) {
		if ( in_array( $meta_key, $meta_keys, true ) ) {
			unset( $meta_array[ $key ] );
		}
	}

	return $meta_array;
}
add_filter( 'pll_copy_post_metas', 'commercegurus_attribute_swatches_pll_copy_post_metas', 10, 5 );
