<?php
/**
 *
 * Order Bump module
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

/**
 * Checkout order bump.
 */
function commercekit_checkout_order_bump() {
	$flags   = commercekit_feature_flags()->get_flags();
	$enabled = isset( $flags['order_bump'] ) && 1 === (int) $flags['order_bump'] ? true : false;
	if ( $enabled ) {
		commercekit_show_order_bumps( 'checkout' );
	}
}

/**
 * Checkout Page - Display Order Bump shortcode
 */
function commercekit_checkout_orderbump_shortcode() {
	$html = commercekit_show_order_bumps( 'checkout', true );

	return $html;
}

/**
 * Checkout Orderbump elementor widget
 *
 * @param  string $widgets_manager widgets manager object.
 */
function commercekit_checkout_orderbump_elementor_widget( $widgets_manager ) {
	require_once CGKIT_BASE_PATH . 'includes/elementor/class-commercekit-orderbump-elementor.php';
	$widgets_manager->register( new Commercekit_Orderbump_Elementor() );
}

$commercekit_options = get_option( 'commercekit', array() );
$widget_pos_obp      = isset( $commercekit_options['widget_pos_obp'] ) && 1 === (int) $commercekit_options['widget_pos_obp'] ? true : false;
if ( $widget_pos_obp ) {
	add_shortcode( 'commercekit_orderbump', 'commercekit_checkout_orderbump_shortcode' );
	add_action( 'elementor/widgets/register', 'commercekit_checkout_orderbump_elementor_widget' );
} else {
	add_action( 'woocommerce_review_order_before_submit', 'commercekit_checkout_order_bump', 99 );
}

/**
 * Mini cart order bumps.
 */
function commercekit_minicart_order_bump() {
	$flags   = commercekit_feature_flags()->get_flags();
	$enabled = isset( $flags['order_bump_mini'] ) && 1 === (int) $flags['order_bump_mini'] ? true : false;
	if ( $enabled ) {
		commercekit_show_order_bumps( 'minicart' );
	}
}
add_action( 'woocommerce_mini_cart_contents', 'commercekit_minicart_order_bump', 99 );

/**
 * Show order bumps.
 *
 * @param  string $position of order bumbs.
 * @param  string $return   return html output.
 */
function commercekit_show_order_bumps( $position, $return = false ) {
	global $cgkit_orderbump_script;
	$product_ids = array();
	$categories  = array();
	$tags        = array();

	if ( WC()->cart ) {
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( isset( $item['product_id'] ) && (int) $item['product_id'] ) {
				$product_ids[] = (int) $item['product_id'];
			}
			if ( isset( $item['variation_id'] ) && (int) $item['variation_id'] ) {
				$product_ids[] = (int) $item['variation_id'];
			}
			$terms = get_the_terms( $item['product_id'], 'product_cat' );
			if ( is_array( $terms ) && count( $terms ) ) {
				foreach ( $terms as $term ) {
					$categories[] = (int) $term->term_id;
				}
			}
			$terms_new = get_the_terms( $item['product_id'], 'product_tag' );
			if ( is_array( $terms_new ) && count( $terms_new ) ) {
				foreach ( $terms_new as $term_new ) {
					$tags[] = (int) $term_new->term_id;
				}
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
		}
	}

	$options       = get_option( 'commercekit', array() );
	$product_title = '';
	$button_text   = esc_html__( 'Click to add', 'commercegurus-commercekit' );
	$pid           = 0;
	$order_bumps   = array();
	$cart_total    = WC()->cart ? (float) WC()->cart->get_displayed_subtotal() : 0;

	$order_bump_product  = array();
	$enable_multiple_obp = false;
	if ( 'checkout' === $position ) {
		$order_bump_product  = isset( $options['order_bump_product'] ) ? $options['order_bump_product'] : array();
		$enable_multiple_obp = isset( $options['multiple_obp'] ) && 1 === (int) $options['multiple_obp'] ? true : false;
	}
	if ( 'minicart' === $position ) {
		$order_bump_product  = isset( $options['order_bump_minicart'] ) ? $options['order_bump_minicart'] : array();
		$enable_multiple_obp = isset( $options['multiple_obp_mini'] ) && 1 === (int) $options['multiple_obp_mini'] ? true : false;
	}

	if ( isset( $order_bump_product['product']['title'] ) && count( $order_bump_product['product']['title'] ) > 0 ) {
		foreach ( $order_bump_product['product']['title'] as $k => $product_title ) {
			if ( 'checkout' === $position && empty( $product_title ) ) {
				continue;
			}
			if ( 'minicart' === $position && ( ! isset( $order_bump_product['product']['id'][ $k ] ) || 0 === (int) $order_bump_product['product']['id'][ $k ] ) ) {
				continue;
			}
			if ( isset( $order_bump_product['product']['active'][ $k ] ) && 1 === (int) $order_bump_product['product']['active'][ $k ] ) {
				$can_display = false;
				$condition   = isset( $order_bump_product['product']['condition'][ $k ] ) ? $order_bump_product['product']['condition'][ $k ] : 'all';
				$pids        = isset( $order_bump_product['product']['pids'][ $k ] ) ? explode( ',', $order_bump_product['product']['pids'][ $k ] ) : array();
				$pid         = isset( $order_bump_product['product']['id'][ $k ] ) ? (int) $order_bump_product['product']['id'][ $k ] : 0;
				$button_text = isset( $order_bump_product['product']['button_text'][ $k ] ) ? commercekit_get_multilingual_string( $order_bump_product['product']['button_text'][ $k ] ) : esc_html__( 'Click to add', 'commercegurus-commercekit' );
				$min_total   = isset( $order_bump_product['product']['cart_total_min'][ $k ] ) ? (float) $order_bump_product['product']['cart_total_min'][ $k ] : 0;
				$max_total   = isset( $order_bump_product['product']['cart_total_max'][ $k ] ) ? (float) $order_bump_product['product']['cart_total_max'][ $k ] : 0;

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
				}

				if ( $min_total >= 0 && $max_total > 0 && $max_total >= $min_total ) {
					if ( $can_display && $min_total <= $cart_total && $max_total >= $cart_total ) {
						$can_display = true;
					} else {
						$can_display = false;
					}
				}

				if ( $can_display && $pid && ! in_array( $pid, $product_ids, true ) ) {
					$product_title = commercekit_get_multilingual_string( $product_title );
					$product_id    = $pid;
					$product       = wc_get_product( $pid );
					if ( $product && $product->is_in_stock() && $product->is_purchasable() ) {
						$image = '';
						if ( has_post_thumbnail( $product_id ) ) {
							$image = get_the_post_thumbnail( $product_id, 'thumbnail' );
						} elseif ( $product->is_type( 'variation' ) ) {
							$parent_id = $product->get_parent_id();
							if ( has_post_thumbnail( $parent_id ) ) {
								$image = get_the_post_thumbnail( $parent_id, 'thumbnail' );
							}
						}
						if ( $product->is_type( 'variation' ) && ! $product->variation_is_visible() ) {
							continue;
						}
						if ( $product->has_child() ) {
							$children_ids = $product->get_children();
							$product_id   = reset( $children_ids );
							if ( in_array( (int) $product_id, $product_ids, true ) ) {
								continue;
							}
						}

						$product_id = (int) $product_id;

						$order_bumps[ $product_id ] = array(
							'product_title' => $product_title,
							'image'         => $image,
							'product'       => $product,
							'button_text'   => $button_text,
						);
						if ( $enable_multiple_obp ) {
							continue;
						} else {
							break;
						}
					}
				}
			}
		}
	}
	if ( count( $order_bumps ) ) {
		if ( $return ) {
			$cgkit_orderbump_script = true;
			return commercekit_order_bump_template( $order_bumps, $position );
		} else {
			echo 'minicart' === $position ? '<li>' : '';
			echo commercekit_order_bump_template( $order_bumps, $position ); // phpcs:ignore
			echo 'minicart' === $position ? '</li>' : '';
		}
	}
}

/**
 * Order bump template
 *
 * @param  string $order_bumps list.
 * @param  string $position of order bumps.
 */
function commercekit_order_bump_template( $order_bumps, $position = 'checkout' ) {
	$options   = get_option( 'commercekit', array() );
	$multi_obp = false;
	$obp_label = '';
	$obp_html  = '';
	if ( 'checkout' === $position ) {
		$multi_obp = isset( $options['multiple_obp'] ) && 1 === (int) $options['multiple_obp'] ? true : false;
		$obp_label = isset( $options['multiple_obp_label'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $options['multiple_obp_label'] ) ) : commercekit_get_default_settings( 'multiple_obp_label' );
	}
	if ( 'minicart' === $position ) {
		$multi_obp = isset( $options['multiple_obp_mini'] ) && 1 === (int) $options['multiple_obp_mini'] ? true : false;
		$obp_label = isset( $options['multiple_obp_mini_lbl'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $options['multiple_obp_mini_lbl'] ) ) : commercekit_get_default_settings( 'multiple_obp_mini_lbl' );
	}

	$obp_html .= '<div class="commercekit-order-bump-wrap cgkit-' . esc_html( $position ) . ' ' . ( true === $multi_obp ? 'multiple-order-bumps' : '' ) . ' ' . ( 1 === count( $order_bumps ) ? 'cgkit-single-order-bump' : '' ) . '">';
	if ( $multi_obp && ! empty( $obp_label ) ) {
		$obp_html .= '<div class="ckobp-before-you-go">' . esc_html( $obp_label ) . '</div>';
	}
	$obp_html .= '<div class="commercekit-order-bumps-wrap">';
	$obp_html .= '<div class="commercekit-order-bumps">';
	$counter   = 0;
	foreach ( $order_bumps as $product_id => $order_bump ) {
		$product_title = $order_bump['product_title'];
		$image         = $order_bump['image'];
		$product       = $order_bump['product'];
		$button_text   = $order_bump['button_text'];
		$product_link  = $product->get_permalink();
		$counter++;

		$obp_html .= '<div class="commercekit-order-bump ' . ( 1 === $counter ? 'active' : '' ) . '" data-index="' . esc_attr( $counter ) . '" id="ckobp-' . esc_html( $position ) . '-' . esc_html( $product_id ) . '" data-product-id="' . esc_html( $product_id ) . '">';
		if ( ! empty( $product_title ) ) {
			$obp_html .= '<div class="ckobp-title">' . esc_html( $product_title ) . '</div>';
		}
		$obp_html .= '<div class="ckobp-wrapper">';
		$obp_html .= '<div class="ckobp-item">';
		$obp_html .= '<div class="ckobp-image"><a href="' . esc_url( $product_link ) . '">' . $image . '</a></div>';
		$obp_html .= '<div class="ckobp-product">';
		$obp_html .= '<div class="ckobp-name"><a href="' . esc_url( $product_link ) . '">' . get_the_title( $product_id ) . '</a></div>';
		$obp_html .= '<div class="ckobp-price">' . $product->get_price_html() . '</div>';
		$obp_html .= '</div>'; /* ckobp-product end */
		$obp_html .= '</div>'; /* ckobp-item end */
		$obp_html .= '<div class="ckobp-actions"><div class="ckobp-button"><button type="button" onclick="commercekitOrderBumpAdd(' . esc_html( $product_id ) . ', this, \'' . esc_html( $position ) . '\');">' . esc_html( $button_text ) . '</button></div></div>';
		$obp_html .= '</div>'; /* ckobp-wrapper end */
		$obp_html .= '</div>'; /* commercekit-order-bump end */
	}
	$obp_html .= '</div>'; /* commercekit-order-bumps end */

	if ( $multi_obp && count( $order_bumps ) > 1 ) {
		$counter   = 0;
		$obp_html .= '<div class="ckobp-nav">';
		$obp_html .= '<div class="ckobp-prevnext">';
		$obp_html .= '<div class="ckobp-prev ckobp-disabled" role="button" tabindex="0" aria-label="Previous"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75L3 12m0 0l3.75-3.75M3 12h18" /></svg></div>';
		$obp_html .= '<div class="ckobp-next" role="button" tabindex="0" aria-label="Next"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" /></svg></div>';
		$obp_html .= '</div>'; /* ckobp-prevnext end */
		$obp_html .= '<div class="ckobp-bullets" data-index="1" data-total="' . count( $order_bumps ) . '">';
		foreach ( $order_bumps as $product_id => $order_bump ) {
			$counter++;
			$obp_html .= '<div id="bullet-ckobp-' . esc_html( $position ) . '-' . esc_html( $product_id ) . '" class="ckobp-bullet ' . ( 1 === $counter ? 'active' : '' ) . '" data-index="' . esc_attr( $counter ) . '">&nbsp;</div>';
		}
		$obp_html .= '</div>'; /* ckobp-bullets end */
		$obp_html .= '</div>'; /* ckobp-nav end */
	}

	$obp_html .= '</div>'; /* commercekit-order-bumps-wrap end */
	$obp_html .= '</div>'; /* commercekit-order-bump-wrap end */

	return $obp_html;
}

/**
 * Order bump scripts
 */
function commercekit_order_bump_scripts() {
	?>
<style>
.ckobp-before-you-go { font-size: 15px; color: #111; font-weight: bold; }
.commercekit-order-bump { border: 1px solid #e2e2e2; box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.06); padding: 20px; margin: 8px 0 0 0; border-radius: 6px; }
.commercekit-order-bump .ckobp-title { width: 100%; padding-bottom: 10px; font-weight: bold; font-size: 14px; line-height: 1.4; color: #111; }
.commercekit-order-bump .ckobp-wrapper { display: flex; justify-content: space-between; }
.commercekit-order-bump .ckobp-item { display: flex; }
.commercekit-order-bump .ckobp-actions { display: flex; flex-shrink: 0; }
.commercekit-order-bump .ckobp-image { width: 50px; flex-shrink: 0; }
.commercekit-order-bump .ckobp-image a { display: block; }
.commercekit-order-bump .ckobp-image img { display:block; max-width: 50px; margin: 0; }
.commercekit-order-bump .ckobp-image img:nth-child(2n) { display: none; }
.commercekit-order-bump .ckobp-product { margin: -5px 15px 0 15px; }
.commercekit-order-bump .ckobp-name { color: #111; font-size: 13px; line-height: 1.4; display: inline-flex; }
.commercekit-order-bump .ckobp-name a { color: #111; }
.commercekit-order-bump .ckobp-price { margin-top: 2px; font-size: 12px; }
.commercekit-order-bump .ckobp-price, .commercekit-order-bump .ckobp-price ins { color: #DE9915; }
.commercekit-order-bump .ckobp-price del { margin-right: 5px; color: #999; font-weight: normal; }
.commercekit-order-bump .ckobp-actions button { padding: 7px 10px; font-size: 12px; font-weight: 600; color: #111; border: 1px solid #e2e2e2; background: linear-gradient(180deg, white, #eee 130%) no-repeat; border-radius: 4px; transition: 0.2s all; }
.commercekit-order-bump .ckobp-actions button:hover { border-color: #ccc; }
.ckobp-bullets { min-height: 1px; display: flex;}
.ckobp-bullets.processing { opacity: 0.5; pointer-events: none; }
.ckobp-bullets .ckobp-bullet { display: inline-block; width: 8px; height: 8px; background-color: #ccc; border-radius: 50%; cursor: pointer; margin-right: 7px; }
.ckobp-bullets .ckobp-bullet.active {  background-color: #000; }
@media (max-width: 500px) {
	.commercekit-order-bump .ckobp-wrapper { display: block; }
	.commercekit-order-bump .ckobp-actions { display: block; width: 100%; margin-top: 10px; }
	.commercekit-order-bump .ckobp-actions button { width: 100%; }
	.commercekit-order-bump .ckobp-name, .commercekit-order-bump .ckobp-title, .commercekit-order-bump .ckobp-actions button { font-size: 13px; }
}
.commercekit-order-bumps { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scroll-snap-stop: always; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; position: relative; -ms-overflow-style: none; scrollbar-width: none; width: 100%; }
.commercekit-order-bumps::-webkit-scrollbar { width: 6px; height: 6px; }
.commercekit-order-bumps::-webkit-scrollbar-thumb { background-color:rgba(0,0,0,.2); border-radius: 6px; }
.commercekit-order-bumps::-webkit-scrollbar-track { background: transparent; }
.commercekit-order-bumps::-webkit-scrollbar { display: none; }
.commercekit-order-bumps .commercekit-order-bump { scroll-snap-align: center; flex-shrink: 0; margin-right: 15px; border-radius: 10px; transform-origin: center center; transform: scale(1); transition: transform 0.5s; position: relative; justify-content: center; align-items: center; width: 100%; }
.cgkit-single-order-bump .commercekit-order-bumps::-webkit-scrollbar { width: 0px; height: 0px; }
.product_list_widget li:has(.commercekit-order-bump-wrap) { padding-bottom: 0; }
.commercekit-order-bump-wrap.cgkit-single-order-bump { margin-bottom: 15px; }
.commercekit-order-bumps-wrap { position: relative; }
.commercekit-order-bumps-wrap .ckobp-prev.ckobp-disabled, .commercekit-order-bumps-wrap .ckobp-next.ckobp-disabled { opacity: 0.25; }
.commercekit-order-bumps-wrap .ckobp-prev, .commercekit-order-bumps-wrap .ckobp-next {cursor: pointer; z-index: 2; display: inline-flex; margin-left: 5px;}
.commercekit-order-bumps-wrap .ckobp-prev svg, .commercekit-order-bumps-wrap .ckobp-next svg { width: 18px; height: 18px;}
.commercekit-order-bumps-wrap .ckobp-nav { display: flex; justify-content: space-between; align-items: center; margin: 8px 0 20px 0; }
.commercekit-order-bumps-wrap .ckobp-prevnext { display: flex; order: 2; }
/* RTL */
.rtl .ckobp-bullets .ckobp-bullet { margin-right: 0; margin-left: 7px; }
.rtl .commercekit-order-bumps-wrap .ckobp-prev { order: 1; }
.rtl .commercekit-order-bumps-wrap .ckobp-prev, .rtl .commercekit-order-bumps-wrap .ckobp-next { margin-left: 0; margin-right: 5px; }
</style>
<script>
function commercekitOrderBumpAdd(product_id, obj, position){
	var ajax_nonce = '';
	if( commercekit_ajs.ajax_nonce != 1 ){
		return true;
	} else {
		var nonce_input = document.querySelector( '#commercekit_nonce' );
		if ( nonce_input ) {
			ajax_nonce = nonce_input.value;
		}
	}
	obj.setAttribute('disabled', 'disabled');
	var wrap = obj.closest('.commercekit-order-bump-wrap');
	if( wrap ){
		var bullets = wrap.querySelector('.ckobp-bullets');
		if( bullets ){
			bullets.classList.add('processing');
		}
	}
	var formData = new FormData();
	formData.append('product_id', product_id);
	formData.append('commercekit_nonce', ajax_nonce);
	fetch( commercekit_ajs.ajax_url + '=commercekit_order_bump_add', {
		method: 'POST',
		body: formData,
	}).then(response => response.json()).then( json => {
		var ppp = document.querySelector('.paypalplus-paywall');
		if( ppp ) {
			window.location.reload();
		} else {
			var ucheckout = new Event('update_checkout');
			document.body.dispatchEvent(ucheckout);
			var ufragment = new Event('wc_fragment_refresh');
			document.body.dispatchEvent(ufragment);
			var cgkit_cart_drawer = document.querySelector( '.wc-block-mini-cart__drawer' );
			if ( cgkit_cart_drawer ) {
				jQuery(document.body).trigger('added_to_cart');
				if ( typeof cgkitLoadMiniCartBlocks == 'function' ) {
					cgkitLoadMiniCartBlocks();
				}
			}
		}
	});
}
var ckit_obp_clicked = false;
var ckit_obp_clicked_id = 0; 
document.addEventListener('click', function(e){
	$this = e.target;
	if( $this.classList.contains( 'ckobp-bullet' ) ) {
		e.preventDefault();
		e.stopPropagation();
		ckit_obp_clicked = true;
		ckit_obp_make_active($this, true);
		if( ckit_obp_clicked_id ){
			clearTimeout( ckit_obp_clicked_id );
		}
		ckit_obp_clicked_id = setTimeout(function(){ ckit_obp_clicked = false; ckit_obp_clicked_id = 0; }, 1000);
	}
	$thisp = $this.closest('.ckobp-prev');
	if( $this.classList.contains( 'ckobp-prev' ) || $thisp ) {
		e.preventDefault();
		e.stopPropagation();
		var parent = $this.closest( '.commercekit-order-bump-wrap' );
		var par_divs = parent.querySelector('.ckobp-bullets');
		var $is_rtl = document.querySelector('body.rtl');
		if( par_divs ){
			var $index = parseInt(par_divs.getAttribute('data-index'));
			if( $index == 1 && ! $is_rtl ){
				return true;
			}
			var $nindex = $is_rtl ? $index + 1 : $index - 1;
			var $bullet = parent.querySelector('.ckobp-bullets .ckobp-bullet[data-index="'+$nindex+'"]');
			if( $bullet ){
				$bullet.click();
			}
		}
	}
	$thisp = $this.closest('.ckobp-next');
	if( $this.classList.contains( 'ckobp-next' ) || $thisp ) {
		e.preventDefault();
		e.stopPropagation();
		var parent = $this.closest( '.commercekit-order-bump-wrap' );
		var par_divs = parent.querySelector('.ckobp-bullets');
		var $is_rtl = document.querySelector('body.rtl');
		if( par_divs ){
			var total = parseInt(par_divs.getAttribute('data-total'));
			var $index = parseInt(par_divs.getAttribute('data-index'));
			if( $index == total && ! $is_rtl ){
				return true;
			}
			var $nindex = $is_rtl ? $index - 1 : $index + 1;
			var $bullet = parent.querySelector('.ckobp-bullets .ckobp-bullet[data-index="'+$nindex+'"]');
			if( $bullet ){
				$bullet.click();
			}
		}
	}
});
function ckit_obp_make_active($this, $scroll){
	var parent = $this.closest( '.commercekit-order-bump-wrap' );
	var $id = $this.getAttribute( 'id' ).replace( 'bullet-', '' );
	var $mthis = parent.querySelector( '#' + $id );
	var main_divs = parent.querySelectorAll('.commercekit-order-bumps .commercekit-order-bump');
	$this.classList.add( 'active' );
	$mthis.classList.add( 'active' );
	main_divs.forEach(function(main_div){
		if( main_div !== $mthis ){
			main_div.classList.remove( 'active' );
		}
	});
	var sub_divs = parent.querySelectorAll('.ckobp-bullets .ckobp-bullet');
	sub_divs.forEach(function(sub_divs){
		if( sub_divs !== $this ){
			sub_divs.classList.remove( 'active' );
		}
	});
	var $index = parseInt($mthis.getAttribute('data-index'));
	var par_divs = parent.querySelector('.ckobp-bullets');
	if( par_divs ){
		var total = parseInt(par_divs.getAttribute('data-total'));
		par_divs.setAttribute('data-index', $index);
		ckit_obp_update_prev_next(parent, total, $index);
	}
	if( $scroll ){
		var $width = $mthis.clientWidth;
		var $scroll_left = ( $index - 1 ) * $width;
		var $is_rtl = document.querySelector('body.rtl');
		if( $is_rtl ){
			$scroll_left = -$scroll_left;
		}
		var ckit_obps = parent.querySelector('.commercekit-order-bumps');
		if( ckit_obps ){
			ckit_obps.scroll({
				left: $scroll_left,
				top: 0,
				behavior: 'smooth'
			});
		}
	}
}
document.addEventListener('scroll', function(e){
	var $this = e.target;
	if( $this.classList && $this.classList.contains('commercekit-order-bumps') && !ckit_obp_clicked ){
		var sub_div = $this.querySelector('.commercekit-order-bump:first-child');
		if( sub_div ){
			var parent = $this.closest( '.commercekit-order-bump-wrap' );
			var $width = sub_div.clientWidth;
			var $scroll_left = Math.abs($this.scrollLeft);
			var $index = Math.round( $scroll_left / $width ) + 1;
			var $bullet = parent.querySelector('.ckobp-bullets .ckobp-bullet[data-index="'+$index+'"]');
			if( $bullet ){
				ckit_obp_make_active($bullet, false);
			}
		}
	}
}, true);
function ckit_obp_update_prev_next(parent, total, $index){
	var prev = parent.querySelector('.ckobp-prev');
	var next = parent.querySelector('.ckobp-next');
	if( prev && next ){
		next.classList.remove('ckobp-disabled');
		prev.classList.remove('ckobp-disabled');
		var $is_rtl = document.querySelector('body.rtl');
		if( $is_rtl ){
			if( $index == 1 ) {
				next.classList.add('ckobp-disabled');
			}
			if( $index == total ) {
				prev.classList.add('ckobp-disabled');
			}
		} else {
			if( $index == 1 ) {
				prev.classList.add('ckobp-disabled');
			}
			if( $index == total ) {
				next.classList.add('ckobp-disabled');
			}
		}
	}
}
document.addEventListener('keypress', function(e) {
	var active_elm = document.activeElement;
	if( active_elm && e.key == 'Enter' && ( active_elm.classList.contains('ckobp-prev') || active_elm.classList.contains('ckobp-next') ) ) {
		active_elm.click();
	}
});
var cgkit_updating_obp_views = false;
function cgkit_update_order_bump_views() {
	var product_ids = [];
	var cgkit_obps = document.querySelectorAll( 'div.commercekit-order-bump[data-product-id]' );
	if ( cgkit_obps.length > 0 ) {
		cgkit_obps.forEach( function( cgkit_obp ) {
			product_ids.push( cgkit_obp.getAttribute( 'data-product-id' ) );
		} );
	}
	if ( product_ids.length == 0 ) {
		return;
	}
	if ( cgkit_updating_obp_views ) {
		return;
	}
	var ajax_nonce = '';
	var nonce_input = document.querySelector( '#commercekit_nonce' );
	if ( nonce_input ) {
		ajax_nonce = nonce_input.value;
	}
	var formData = new FormData();
	formData.append( 'product_ids', product_ids );
	formData.append( 'commercekit_nonce', ajax_nonce );
	cgkit_updating_obp_views = true;
	fetch( commercekit_ajs.ajax_url + '=commercekit_orderbump_views', {
		method: 'POST',
		body: formData,
	} ).then( response => response.json() ).then( json => {
		cgkit_updating_obp_views = false;
	} );
}
document.addEventListener( 'DOMContentLoaded', function() {
	if ( jQuery ) {
		jQuery( document ).on( 'wc_fragments_loaded wc_fragments_refreshed', function() {
			cgkit_update_order_bump_views();
		} );
	}
} );
</script>
	<?php
}

/**
 * Footer scripts.
 */
function commercekit_order_bump_footer_scripts() {
	global $cgkit_orderbump_script;
	$commercekit_flags      = commercekit_feature_flags()->get_flags();
	$enable_order_bump      = isset( $commercekit_flags['order_bump'] ) && 1 === (int) $commercekit_flags['order_bump'] ? true : false;
	$enable_order_bump_mini = isset( $commercekit_flags['order_bump_mini'] ) && 1 === (int) $commercekit_flags['order_bump_mini'] ? true : false;
	if ( ( is_checkout() && $enable_order_bump ) || $enable_order_bump_mini || ( isset( $cgkit_orderbump_script ) && true === $cgkit_orderbump_script ) ) {
		commercekit_order_bump_scripts();
	}
}
add_action( 'wp_footer', 'commercekit_order_bump_footer_scripts' );

/**
 * Ajax order bump add.
 */
function commercekit_ajax_order_bump_add() {
	$ajax            = array();
	$ajax['status']  = 0;
	$ajax['message'] = esc_html__( 'Error on adding to cart.', 'commercegurus-commercekit' );

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_nonce' ) ) {
		wp_send_json( $ajax );
	}

	$product_id  = isset( $_POST['product_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0;
	$product_ids = array();
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( isset( $item['product_id'] ) && (int) $item['product_id'] ) {
			$product_ids[] = (int) $item['product_id'];
		}
		if ( isset( $item['variation_id'] ) && (int) $item['variation_id'] ) {
			$product_ids[] = (int) $item['variation_id'];
		}
	}
	if ( ! in_array( $product_id, $product_ids, true ) ) {
		$variation_id = 0;
		if ( 'product_variation' === get_post_type( $product_id ) ) {
			$variation_id = $product_id;
			$product_id   = wp_get_post_parent_id( $variation_id );
		}
		if ( WC()->cart->add_to_cart( $product_id, 1, $variation_id ) ) {
			$ajax['status']  = 1;
			$ajax['message'] = esc_html__( 'Sucessfully added to cart.', 'commercegurus-commercekit' );

			WC()->session->set( 'cgkit_order_bump_added', true );
			$product_id = 0 !== (int) $variation_id ? (int) $variation_id : (int) $product_id;
			$click_ids  = isset( $_COOKIE['commercekit_obp_click_ids'] ) && ! empty( $_COOKIE['commercekit_obp_click_ids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['commercekit_obp_click_ids'] ) ) ) : array();
			$click_ids  = array_map( 'intval', $click_ids );
			if ( ! in_array( $product_id, $click_ids, true ) ) {
				$order_bump_stats_clicks = (int) get_option( 'commercekit_obp_clicks' );
				$order_bump_stats_clicks++;
				update_option( 'commercekit_obp_clicks', $order_bump_stats_clicks, false );

				$click_ids[] = $product_id;
				setcookie( 'commercekit_obp_click_ids', implode( ',', $click_ids ), time() + ( 24 * 3600 ), '/' );
			}
		}
	}

	wp_send_json( $ajax );
}

add_action( 'wp_ajax_commercekit_order_bump_add', 'commercekit_ajax_order_bump_add' );
add_action( 'wp_ajax_nopriv_commercekit_order_bump_add', 'commercekit_ajax_order_bump_add' );

/**
 * Ajax order bump views.
 */
function commercekit_ajax_commercekit_orderbump_views() {
	$ajax            = array();
	$ajax['status']  = 0;
	$ajax['message'] = esc_html__( 'Error on updating order bump views.', 'commercegurus-commercekit' );

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_nonce' ) ) {
		wp_send_json( $ajax );
	}

	$product_ids = isset( $_POST['product_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['product_ids'] ) ) : '';
	$product_ids = explode( ',', $product_ids );
	$product_ids = array_unique( array_filter( $product_ids ) );
	$product_ids = array_map( 'intval', $product_ids );
	if ( count( $product_ids ) ) {
		$view_ids = isset( $_COOKIE['commercekit_obp_view_ids'] ) && ! empty( $_COOKIE['commercekit_obp_view_ids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['commercekit_obp_view_ids'] ) ) ) : array();
		$view_ids = array_map( 'intval', $view_ids );
		$updated  = false;

		$order_bump_stats_views = (int) get_option( 'commercekit_obp_views' );
		foreach ( $product_ids as $product_id ) {
			if ( ! in_array( $product_id, $view_ids, true ) ) {
				$order_bump_stats_views++;
				$view_ids[] = $product_id;
				$updated    = true;
			}
		}

		if ( $updated ) {
			update_option( 'commercekit_obp_views', $order_bump_stats_views, false );
			setcookie( 'commercekit_obp_view_ids', implode( ',', $view_ids ), time() + ( 24 * 3600 ), '/' );
		}

		$ajax['status']  = 1;
		$ajax['message'] = '';
	}

	wp_send_json( $ajax );
}

add_action( 'wp_ajax_commercekit_orderbump_views', 'commercekit_ajax_commercekit_orderbump_views' );
add_action( 'wp_ajax_nopriv_commercekit_orderbump_views', 'commercekit_ajax_commercekit_orderbump_views' );

/**
 * Order bump record sales
 *
 * @param  string $order_id of order.
 */
function commercekit_order_bump_record_sales( $order_id ) {
	$order       = wc_get_order( $order_id );
	$product_ids = array();
	$quantities  = array();
	$click_ids   = isset( $_COOKIE['commercekit_obp_click_ids'] ) && ! empty( $_COOKIE['commercekit_obp_click_ids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['commercekit_obp_click_ids'] ) ) ) : array();
	$matched_ids = array();
	if ( count( $click_ids ) ) {
		foreach ( $order->get_items() as $item_id => $item ) {
			if ( $item['variation_id'] > 0 ) {
				$product_id = $item['variation_id'];
			} else {
				$product_id = $item['product_id'];
			}
			$product_ids[] = $product_id;

			$quantities[ $product_id ] = (int) $item['quantity'];
		}
	} else {
		return;
	}
	if ( count( $product_ids ) ) {
		$matched_ids = array_intersect( $click_ids, $product_ids );
		if ( count( $matched_ids ) ) {
			$order_bump_stats_sales = (int) get_option( 'commercekit_obp_sales' );
			$order_bump_stats_price = (float) get_option( 'commercekit_obp_sales_revenue' );
			foreach ( $matched_ids as $matched_id ) {
				$product = wc_get_product( $matched_id );
				if ( $product ) {
					$order_bump_stats_sales++;
					$order_bump_stats_price += $quantities[ $matched_id ] * (float) $product->get_price();
				}
			}
			update_option( 'commercekit_obp_sales', $order_bump_stats_sales, false );
			update_option( 'commercekit_obp_sales_revenue', number_format( $order_bump_stats_price, 2, '.', '' ), false );
		}
	}

	if ( $order ) {
		$order->update_meta_data( 'commercekit_obp_clicks', $click_ids );
		$order->update_meta_data( 'commercekit_obp_sales', $matched_ids );
		$order->save();
	}

	setcookie( 'commercekit_obp_click_ids', '', time() - ( 24 * 3600 ), '/' );
	setcookie( 'commercekit_obp_view_ids', '', time() - ( 24 * 3600 ), '/' );
}

add_action( 'woocommerce_thankyou', 'commercekit_order_bump_record_sales' );

/**
 * Order bump order review fragments
 *
 * @param  string $fragments of order.
 */
function commercekit_order_bump_order_review_fragments( $fragments ) {
	global $cgkit_obp_scripts;
	$cgkit_order_bump_added = WC()->session->get( 'cgkit_order_bump_added' );
	if ( true === $cgkit_order_bump_added ) {
		if ( isset( $fragments['.woocommerce-checkout-payment'] ) ) {
			unset( $fragments['.woocommerce-checkout-payment'] );
			if ( isset( $fragments['.woocommerce-checkout-review-order-table'] ) ) {
				$fragments['.woocommerce-checkout-review-order-table'] .= '<script> document.querySelectorAll(\'.woocommerce-checkout-payment .blockUI\').forEach(function(div){ div.style.display = \'none\'; }); </script>';
			}
		}
		ob_start();
		$cgkit_obp_scripts = true;
		commercekit_checkout_order_bump();
		$fragments['.commercekit-order-bump-wrap.cgkit-checkout'] = ob_get_clean();
		WC()->session->set( 'cgkit_order_bump_added', false );
	}

	return $fragments;
}

add_filter( 'woocommerce_update_order_review_fragments', 'commercekit_order_bump_order_review_fragments', 99, 1 );

/**
 * Order bump tracking meta box.
 */
function commercekit_order_bump_tracking_meta_box() {
	if ( class_exists( 'WooCommerce' ) ) {
		if ( class_exists( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class ) ) {
			$screen = wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
		} else {
			$screen = 'shop_order';
		}
		add_meta_box( 'commercekit-order-bump-tracking', esc_html__( 'CommerceKit Order Bump', 'commercegurus-commercekit' ), 'commercekit_order_bump_tracking_meta_box_display', $screen, 'side', 'low' );
	}
}
add_action( 'add_meta_boxes', 'commercekit_order_bump_tracking_meta_box' );

/**
 * Order bump tracking meta box display.
 *
 * @param string $order_object order object.
 */
function commercekit_order_bump_tracking_meta_box_display( $order_object ) {
	$order     = ( $order_object instanceof WP_Post ) ? wc_get_order( $order_object->ID ) : $order_object;
	$click_ids = array();
	$sales_ids = array();
	if ( method_exists( $order, 'get_meta' ) ) {
		$cids = $order->get_meta( 'commercekit_obp_clicks', true );
		$sids = $order->get_meta( 'commercekit_obp_sales', true );
		if ( is_array( $cids ) && count( $cids ) ) {
			foreach ( $cids as $cid ) {
				$product = wc_get_product( $cid );
				$title   = $product ? $product->get_name() : '';
				$product = $product && $product->get_parent_id() ? wc_get_product( $product->get_parent_id() ) : $product;
				if ( $product && $title ) {
					$product_link = admin_url( 'post.php?post=' . $product->get_id() . '&action=edit' );
					$click_ids[]  = '<a href="' . esc_url( $product_link ) . '">' . wp_kses_post( $title ) . '</a>';
				}
			}
		}
		if ( is_array( $sids ) && count( $sids ) ) {
			foreach ( $sids as $sid ) {
				$product = wc_get_product( $sid );
				$title   = $product ? $product->get_name() : '';
				$product = $product && $product->get_parent_id() ? wc_get_product( $product->get_parent_id() ) : $product;
				if ( $product && $title ) {
					$product_link = admin_url( 'post.php?post=' . $product->get_id() . '&action=edit' );
					$sales_ids[]  = '<a href="' . esc_url( $product_link ) . '">' . wp_kses_post( $title ) . '</a>';
				}
			}
		}
	}
	if ( ! count( $click_ids ) && ! count( $sales_ids ) ) {
		echo '<style>#commercekit-order-bump-tracking{display:none;}</style>';
	} else {
		if ( count( $click_ids ) ) {
			echo '<strong>' . esc_html__( 'Clicked products:', 'commercegurus-commercekit' ) . '</strong><br />';
			echo implode( '<br />', $click_ids ) . '<br /><br />'; // phpcs:ignore
		}
		if ( count( $sales_ids ) ) {
			echo '<strong>' . esc_html__( 'Purchased products:', 'commercegurus-commercekit' ) . '</strong><br />';
			echo implode( '<br />', $sales_ids ) . '<br />'; // phpcs:ignore
		}
	}
}

/**
 * Order bump filter admin orders.
 *
 * @param string $views which postion.
 */
function commercekit_order_bump_filter_admin_orders( $views ) {
	global $wpdb, $wp_query;
	if ( 'shop_order' !== $wp_query->query['post_type'] ) {
		return $views;
	}
	if ( ! commercekit_obp_screen_option_enabled() ) {
		return $views;
	}
	$query = "SELECT COUNT(ID) FROM {$wpdb->posts} AS posts INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id WHERE posts.post_type = 'shop_order' AND postmeta.meta_key = 'commercekit_obp_sales' AND postmeta.meta_value IS NOT NULL";

	$total_orders = $wpdb->get_var( $query ); // phpcs:ignore

	$class = '';
	if ( isset( $_GET['cgkit-order-bumps'] ) && 1 === (int) $_GET['cgkit-order-bumps'] ) { // phpcs:ignore
		$class = 'current';
	}
	$args = array(
		'post_type'         => 'shop_order',
		'cgkit-order-bumps' => '1',
	);
	$url  = add_query_arg( $args, admin_url( 'edit.php' ) );

	$views['cgkit-order-bumps'] = '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $class ) . '">' . esc_html__( 'Order bumps', 'commercegurus-commercekit' ) . ' <span class="count">(' . $total_orders . ')</span></a>';

	return $views;
}
add_filter( 'views_edit-shop_order', 'commercekit_order_bump_filter_admin_orders', 99, 1 );

/**
 * Order bump admin orders.
 *
 * @param string $query posts query.
 */
function commercekit_order_bump_admin_orders( $query ) {
	if ( ! is_admin() ) {
		return;
	}
	if ( isset( $query->query['post_type'] ) && 'shop_order' === $query->query['post_type'] ) {
		if ( ! commercekit_obp_screen_option_enabled() ) {
			return;
		}
		if ( isset( $_GET['cgkit-order-bumps'] ) && 1 === (int) $_GET['cgkit-order-bumps'] ) { // phpcs:ignore
			$meta_query = array(
				array(
					'key'     => 'commercekit_obp_sales',
					'value'   => null,
					'compare' => '!=',
				),
			);
			$query->set( 'meta_query', $meta_query );
		}
	}
}
add_action( 'pre_get_posts', 'commercekit_order_bump_admin_orders', 99, 1 );

/**
 * Order bump admin orders query args.
 *
 * @param string $args orders query args.
 */
function commercekit_order_bump_admin_orders_args( $args ) {
	if ( ! is_admin() ) {
		return $args;
	}
	if ( isset( $_GET['cgkit-order-bumps'] ) && 1 === (int) $_GET['cgkit-order-bumps'] ) { // phpcs:ignore
		if ( ! commercekit_obp_screen_option_enabled() ) {
			return $args;
		}
		$meta_query = array(
			array(
				'key'     => 'commercekit_obp_sales',
				'value'   => null,
				'compare' => '!=',
			),
		);

		$args['meta_query'] = $meta_query; // phpcs:ignore
	}
	return $args;
}
add_filter( 'woocommerce_order_list_table_prepare_items_query_args', 'commercekit_order_bump_admin_orders_args', 99, 1 );

/**
 * Order bump admin orders script.
 */
function commercekit_order_bump_admin_orders_script() {
	global $wpdb;
	if ( ! isset( $_GET['page'] ) || 'wc-orders' !== $_GET['page'] ) { // phpcs:ignore
		return;
	}
	if ( ! commercekit_obp_screen_option_enabled() ) {
		return;
	}
	$query = "SELECT COUNT(ID) FROM {$wpdb->posts} AS posts INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id WHERE posts.post_type = 'shop_order' AND postmeta.meta_key = 'commercekit_obp_sales' AND postmeta.meta_value IS NOT NULL";

	$total_orders = $wpdb->get_var( $query ); // phpcs:ignore

	$is_active = false;
	if ( isset( $_GET['cgkit-order-bumps'] ) && 1 === (int) $_GET['cgkit-order-bumps'] ) { // phpcs:ignore
		$is_active = true;
	}
	$args = array(
		'cgkit-order-bumps' => '1',
	);
	$url  = add_query_arg( $args, admin_url( 'admin.php?page=wc-orders' ) );
	$link = '<li class="cgkit-order-bumps">| <a href="' . esc_url( $url ) . '" class="' . ( $is_active ? 'current' : '' ) . '">' . esc_html__( 'Order bumps', 'commercegurus-commercekit' ) . ' <span class="count">(' . $total_orders . ')</span></a></li>';
	?>
<script type="text/javascript">
	<?php if ( $is_active ) { ?>
	jQuery( 'ul.subsubsub a' ).removeClass( 'current' );
	<?php } ?>
	jQuery( 'ul.subsubsub' ).append( '<?php echo $link; // phpcs:ignore ?>' );
</script>
	<?php
}
add_action( 'admin_footer', 'commercekit_order_bump_admin_orders_script', 99 );

/**
 * Order bump admin orders add screen option.
 *
 * @param string $settings screen settings.
 */
function commercekit_obp_orders_add_screen_option( $settings ) {
	if ( ( isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] ) || ( isset( $_GET['post_type'] ) && 'shop_order' === $_GET['post_type'] ) ) { // phpcs:ignore
		$user  = wp_get_current_user();
		$value = (int) get_user_meta( $user->ID, 'commercekit_order_bump_hide', true );

		$settings .= '
<fieldset class="metabox-prefs">
	<legend>' . esc_html__( 'Filter', 'commercegurus-commercekit' ) . '</legend>
	<label><input class="commercekit-order-bump-hide" name="commercekit_order_bump_hide" id="commercekit_order_bump_hide" type="checkbox" id="commercekit_order_bump_hide" value="1" ' . ( 1 === $value ? '' : 'checked="checked"' ) . '>' . esc_html__( 'CommerceKit Order Bump', 'commercegurus-commercekit' ) . '</label>' . wp_nonce_field( 'commercekit_settings', 'commercekit_nonce' ) . '
</fieldset>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery("#commercekit_order_bump_hide").on("change", function(){
		var $ckobp_hide = jQuery(this).prop("checked") ? 0 : 1; 
		var $nonce = jQuery("#commercekit_nonce").val();
		jQuery("#screen-options-apply").attr("disabled","disabled");
		jQuery.ajax({
			url: ajaxurl,
			type: "POST",
			dataType: "json",
			data: { commercekit_order_bump_hide: $ckobp_hide, commercekit_nonce: $nonce, action: "commercekit_order_bump_hide" },
			success: function( json ){
				jQuery("#screen-options-apply").removeAttr("disabled");
			}
		});
	});
});
</script>
';
	}

	return $settings;
}
add_filter( 'screen_settings', 'commercekit_obp_orders_add_screen_option', 0, 1 );

/**
 * Order bump admin orders set screen option.
 */
function commercekit_obp_orders_set_screen_option() {
	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
		wp_send_json_error();
	}

	$value = isset( $_POST['commercekit_order_bump_hide'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['commercekit_order_bump_hide'] ) ) : 0;
	$user  = wp_get_current_user();
	update_user_meta( $user->ID, 'commercekit_order_bump_hide', $value );

	wp_send_json_success();
}
add_action( 'wp_ajax_commercekit_order_bump_hide', 'commercekit_obp_orders_set_screen_option' );

/**
 * Order bump admin screen option enabled.
 */
function commercekit_obp_screen_option_enabled() {
	$user  = wp_get_current_user();
	$value = get_user_meta( $user->ID, 'commercekit_order_bump_hide', true );
	if ( 1 === (int) $value ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Order bump admin order show meta box by default.
 *
 * @param string $hidden hidden meta boxes.
 * @param string $screen screen settings.
 */
function commercekit_order_bump_show_meta_box( $hidden, $screen ) {
	if ( in_array( $screen->base, array( 'edit', 'woocommerce_page_wc-orders', 'post' ), true ) && 'shop_order' === $screen->post_type ) {
		$key = array_search( 'commercekit-order-bump-tracking', $hidden, true );
		if ( false !== $key ) {
			unset( $hidden[ $key ] );
		}
	}

	return $hidden;
}
add_filter( 'default_hidden_meta_boxes', 'commercekit_order_bump_show_meta_box', 10, 2 );
