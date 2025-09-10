<?php
/**
 *
 * Badge module
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

/**
 * Display badge on catalog
 */
function commercekit_badge_on_catalog() {
	global $product;
	if ( ! $product ) {
		return;
	}
	commercekit_badge_show_badges( $product, 'catalog' );
}
add_action( 'woocommerce_before_shop_loop_item_title', 'commercekit_badge_on_catalog', 8 );

/**
 * Display badge on product page
 */
function commercekit_badge_on_product_page() {
	global $product;
	if ( ! $product ) {
		return;
	}
	commercekit_badge_show_badges( $product );
}
$cgkit_options      = get_option( 'commercekit', array() );
$cgkit_flags        = commercekit_feature_flags()->get_flags();
$cgkit_pdp_gallery  = isset( $cgkit_flags['pdp_gallery'] ) && 1 === (int) $cgkit_flags['pdp_gallery'] ? true : false;
$cgkit_pdpa_gallery = isset( $cgkit_flags['pdp_attributes_gallery'] ) && 1 === (int) $cgkit_flags['pdp_attributes_gallery'] && $cgkit_pdp_gallery ? true : false;
if ( $cgkit_pdpa_gallery || $cgkit_pdp_gallery ) {
	$cgkit_shortcode_gallery = isset( $cgkit_options['widget_pos_pdp_gallery'] ) && 1 === (int) $cgkit_options['widget_pos_pdp_gallery'] ? true : false;
	if ( $cgkit_shortcode_gallery ) {
		add_action( 'woocommerce_single_product_summary', 'commercekit_badge_on_product_page', 2 );
	} else {
		add_action( 'commercekit_before_gallery', 'commercekit_badge_on_product_page', 45 );
	}
} else {
	add_action( 'woocommerce_single_product_summary', 'commercekit_badge_on_product_page', 2 );
}

/**
 * Display badges on either catalog or product page
 *
 * @param  string $product object of product.
 * @param  string $type type of page.
 */
function commercekit_badge_show_badges( $product, $type = 'product' ) {
	$options = get_option( 'commercekit', array() );
	$badge   = isset( $options['badge'] ) ? $options['badge'] : array();
	$badges  = array();

	if ( $product && method_exists( $product, 'get_type' ) && 'variation' === $product->get_type() ) {
		$product_id = $product->get_parent_id();
		if ( $product_id ) {
			$product = wc_get_product( $product_id );
		}
	}

	$new_days     = isset( $badge['new']['days'] ) && (int) $badge['new']['days'] ? (int) $badge['new']['days'] : (int) commercekit_get_default_settings( 'badge_new_days' );
	$new_label    = isset( $badge['new']['title'] ) && ! empty( $badge['new']['title'] ) ? commercekit_get_multilingual_string( $badge['new']['title'] ) : commercekit_get_default_settings( 'badge_new_label' );
	$new_bg_color = isset( $badge['new']['bg_color'] ) ? $badge['new']['bg_color'] : commercekit_get_default_settings( 'badge_bg_color' );
	$new_color    = isset( $badge['new']['color'] ) ? $badge['new']['color'] : commercekit_get_default_settings( 'badge_color' );
	$new_catelog  = isset( $badge['new']['catalog'] ) && 1 === (int) $badge['new']['catalog'] ? true : false;
	$new_product  = isset( $badge['new']['product'] ) && 1 === (int) $badge['new']['product'] ? true : false;
	$date_created = strtotime( $product->get_date_created() );
	$newly_added  = ( time() - ( 60 * 60 * 24 * $new_days ) ) < $date_created ? true : false;
	if ( ( 'catalog' === $type && $new_catelog && $newly_added ) || ( 'product' === $type && $new_product && $newly_added ) ) {
		$badges[] = '<span class="ckit-badge" style="background-color: ' . esc_attr( $new_bg_color ) . '; color: ' . esc_attr( $new_color ) . ';">' . esc_attr( $new_label ) . '</span>';
	}

	$product_ids = array( (int) $product->get_id() );
	$categories  = array();
	$terms       = get_the_terms( $product->get_id(), 'product_cat' );
	if ( is_array( $terms ) && count( $terms ) ) {
		foreach ( $terms as $term ) {
			$categories[] = (int) $term->term_id;
		}
	}
	$tags      = array();
	$terms_new = get_the_terms( $product->get_id(), 'product_tag' );
	if ( is_array( $terms_new ) && count( $terms_new ) ) {
		foreach ( $terms_new as $term_new ) {
			$tags[] = (int) $term_new->term_id;
		}
	}

	$brands       = array();
	$brands_exist = taxonomy_exists( 'product_brand' );
	if ( $brands_exist ) {
		$terms_brd = get_the_terms( $product->get_id(), 'product_brand' );
		if ( is_array( $terms_brd ) && count( $terms_brd ) ) {
			foreach ( $terms_brd as $term_brd ) {
				$brands[] = (int) $term_brd->term_id;
			}
		}
	}

	if ( has_filter( 'wpml_default_language' ) && has_filter( 'wpml_object_id' ) ) {
		$default_lang = apply_filters( 'wpml_default_language', null );
		$current_lang = apply_filters( 'wpml_current_language', null );
		if ( $current_lang !== $default_lang ) {
			if ( count( $product_ids ) ) {
				$default_ids = $product_ids;
				foreach ( $default_ids as $default_id ) {
					$item_id = (int) apply_filters( 'wpml_object_id', $default_id, 'product', true, $default_lang );
					if ( $item_id && $default_id !== $item_id ) {
						$product_ids[] = $item_id;
					}
				}
			}
			if ( count( $categories ) ) {
				$default_ids = $categories;
				foreach ( $default_ids as $default_id ) {
					$item_id = (int) apply_filters( 'wpml_object_id', $default_id, 'product_cat', true, $default_lang );
					if ( $item_id && $default_id !== $item_id ) {
						$categories[] = $item_id;
					}
				}
			}
			if ( count( $tags ) ) {
				$default_ids = $tags;
				foreach ( $default_ids as $default_id ) {
					$item_id = (int) apply_filters( 'wpml_object_id', $default_id, 'product_tag', true, $default_lang );
					if ( $item_id && $default_id !== $item_id ) {
						$tags[] = $item_id;
					}
				}
			}
			if ( $brands_exist && count( $brands ) ) {
				$default_ids = $brands;
				foreach ( $default_ids as $default_id ) {
					$item_id = (int) apply_filters( 'wpml_object_id', $default_id, 'product_brand', true, $default_lang );
					if ( $item_id && $default_id !== $item_id ) {
						$brands[] = $item_id;
					}
				}
			}
		}
	}

	if ( isset( $badge['product']['title'] ) && count( $badge['product']['title'] ) > 0 ) {
		foreach ( $badge['product']['title'] as $k => $title ) {
			if ( empty( $title ) ) {
				continue;
			}

			if ( ( 'catalog' === $type && isset( $badge['product']['catalog'][ $k ] ) && 1 === (int) $badge['product']['catalog'][ $k ] ) || ( 'product' === $type && isset( $badge['product']['product'][ $k ] ) && 1 === (int) $badge['product']['product'][ $k ] ) ) {
				$can_display = false;
				$condition   = isset( $badge['product']['condition'][ $k ] ) ? $badge['product']['condition'][ $k ] : 'all';
				$pids        = isset( $badge['product']['pids'][ $k ] ) ? explode( ',', $badge['product']['pids'][ $k ] ) : array();
				$pids        = array_map( 'intval', $pids );
				$product_id  = (int) $product->get_id();
				if ( 'all' === $condition ) {
					$can_display = true;
				} elseif ( 'products' === $condition ) {
					if ( count( array_intersect( $product_ids, $pids ) ) ) {
						$can_display = true;
					}
				} elseif ( 'non-products' === $condition ) {
					if ( ! count( array_intersect( $product_ids, $pids ) ) ) {
						$can_display = true;
					}
				} elseif ( 'categories' === $condition ) {
					if ( count( array_intersect( $categories, $pids ) ) ) {
						$can_display = true;
					}
				} elseif ( 'non-categories' === $condition ) {
					if ( ! count( array_intersect( $categories, $pids ) ) ) {
						$can_display = true;
					}
				} elseif ( 'tags' === $condition ) {
					if ( count( array_intersect( $tags, $pids ) ) ) {
						$can_display = true;
					}
				} elseif ( 'non-tags' === $condition ) {
					if ( ! count( array_intersect( $tags, $pids ) ) ) {
						$can_display = true;
					}
				} elseif ( $brands_exist && 'brands' === $condition ) {
					if ( count( array_intersect( $brands, $pids ) ) ) {
						$can_display = true;
					}
				} elseif ( $brands_exist && 'non-brands' === $condition ) {
					if ( ! count( array_intersect( $brands, $pids ) ) ) {
						$can_display = true;
					}
				}
				if ( $can_display ) {
					$badge_label    = commercekit_get_multilingual_string( $title );
					$badge_bg_color = isset( $badge['product']['bg_color'][ $k ] ) ? $badge['product']['bg_color'][ $k ] : commercekit_get_default_settings( 'badge_bg_color' );
					$badge_color    = isset( $badge['product']['color'][ $k ] ) ? $badge['product']['color'][ $k ] : commercekit_get_default_settings( 'badge_color' );

					$badges[] = '<span class="ckit-badge" style="background-color: ' . esc_attr( $badge_bg_color ) . '; color: ' . esc_attr( $badge_color ) . ';">' . esc_attr( $badge_label ) . '</span>';
				}
			}
		}
	}

	$cgkit_flags  = commercekit_feature_flags()->get_flags();
	$pdp_gallery  = isset( $cgkit_flags['pdp_gallery'] ) && 1 === (int) $cgkit_flags['pdp_gallery'] ? true : false;
	$pdpa_gallery = isset( $cgkit_flags['pdp_attributes_gallery'] ) && 1 === (int) $cgkit_flags['pdp_attributes_gallery'] && $pdp_gallery ? true : false;
	$wrap_class   = '';
	if ( 'product' === $type && ! $pdpa_gallery && ! $pdp_gallery ) {
		$wrap_class = 'ckit-badge-summary';
	}

	$shortcode_gallery = isset( $options['widget_pos_pdp_gallery'] ) && 1 === (int) $options['widget_pos_pdp_gallery'] ? true : false;
	if ( 'product' === $type && $shortcode_gallery && ( $pdpa_gallery || $pdp_gallery ) ) {
		$wrap_class = 'ckit-badge-summary';
	}

	if ( count( $badges ) ) {
		echo '<div class="ckit-badge_wrapper ' . $wrap_class . '">' . implode( '', $badges ) . '</div>'; // phpcs:ignore
	}
}
/**
 * Display badge style on header
 */
function commercekit_badge_styles() {
	?>
<style type="text/css">
.ckit-badge_wrapper { font-size: 11px; position: absolute; z-index: 1; left: 10px; top: 10px; display: flex; flex-direction: column; align-items: flex-start; }
div.product .ckit-badge_wrapper { font-size: 12px; }
.ckit-badge_wrapper.ckit-badge-summary { position: unset; }
.sale-item.product-label + .ckit-badge_wrapper, .onsale + .ckit-badge_wrapper { top: 36px; }
.ckit-badge { padding: 3px 9px; margin-bottom: 5px; line-height: 15px; text-align: center; border-radius: 3px; opacity: 0.8; pointer-events: none; background: #e24ad3; color: #fff; }
#commercegurus-pdp-gallery-wrapper { position: relative; }
#commercegurus-pdp-gallery-wrapper .ckit-badge_wrapper { z-index: 2; }

/* -- Shoptimizer -- */
.theme-shoptimizer .woocommerce-image__wrapper .product-label.type-circle { left: 10px; }
.theme-shoptimizer .sale-item.product-label.type-circle + .ckit-badge_wrapper { top: 50px; }
/* -- RTL -- */
.rtl .ckit-badge_wrapper {left: auto;right: 10px;}
@media (min-width: 771px) {
	.rtl .cgkit-gallery-vertical-scroll .ckit-badge_wrapper {margin-right: 65px;margin-left: 0;}
	.cgkit-gallery-vertical-left .ckit-badge_wrapper { margin-left: 100px; }
}
@media only screen and (max-width: 992px) and (min-width: 771px) {
	.rtl .cgkit-gallery-vertical-scroll .ckit-badge_wrapper {margin-right: 40px;margin-left: 0;}
}
</style>
	<?php
}
add_action( 'wp_head', 'commercekit_badge_styles' );
