<?php
/**
 *
 * Sticky Add to Cart
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

/**
 * Sticky Add to Cart Bar wrappers.
 */
function commercekit_sticky_atc_bar_wrappers() {
	global $product, $cgkit_elmpro_sticky_atc;

	$product_id = $product ? $product->get_id() : 0;
	if ( $product_id ) {
		$disable_sticky_atc = (int) get_post_meta( $product_id, 'commercekit_disable_sticky_atc', true );
		if ( 1 === $disable_sticky_atc ) {
			return;
		}
	}

	if ( ! $product || ! commercekit_sticky_atc_is_allowed_product_type( $product ) ) {
		return;
	}
	if ( ! $product->is_in_stock() ) {
		return;
	}
	$options                = get_option( 'commercekit', array() );
	$flags                  = commercekit_feature_flags()->get_flags();
	$classes                = array();
	$enable_sticky_atc_tabs = defined( 'COMMERCEKIT_STICKY_ATC_TABS_VISIBLE' ) ? COMMERCEKIT_STICKY_ATC_TABS_VISIBLE : ( isset( $flags['sticky_atc_tabs'] ) && 1 === (int) $flags['sticky_atc_tabs'] ? true : false );
	if ( $enable_sticky_atc_tabs || ( isset( $cgkit_elmpro_sticky_atc ) && true === $cgkit_elmpro_sticky_atc ) ) {
		return;
	}
	$classes[] = isset( $flags['sticky_atc_desktop'] ) && 1 === (int) $flags['sticky_atc_desktop'] ? '' : 'commercekit-atc-hide-desktop';
	$classes[] = isset( $flags['sticky_atc_mobile'] ) && 1 === (int) $flags['sticky_atc_mobile'] ? '' : 'commercekit-atc-hide-mobile';
	$classes[] = 'cgkit-atc-product-' . $product->get_type();

	$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );
	?>
<section class="commercekit-sticky-add-to-cart <?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<div class="col-full">
		<div class="commercekit-sticky-add-to-cart__content">
			<ul class="commercekit-atc-tab-links">
				<?php foreach ( $product_tabs as $key => $product_tab ) { ?>
					<?php $product_tab['title'] = isset( $product_tab['title'] ) ? $product_tab['title'] : $key; ?>
					<li id="cgkit-tab-title-<?php echo esc_attr( $key ); ?>">
						<a class="commercekit-atc-tab" data-id="#tab-title-<?php echo esc_attr( $key ); ?>" href="#tab-<?php echo esc_attr( $key ); ?>">
							<?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
						</a>
					</li>
				<?php } ?>
			</ul>
			<div class="commercekit-sticky-add-to-cart__content-button">
				<span class="commercekit-sticky-add-to-cart__content-price" style="display: none;"><?php commercekit_module_output( $product->get_price_html() ); ?></span>
				<a href="#" class="sticky-atc_button button">
					<?php if ( $product->is_type( 'external' ) ) { ?>
						<?php echo esc_attr( $product->single_add_to_cart_text() ); ?>
					<?php } elseif ( $product->is_type( 'subscription' ) || $product->is_type( 'variable-subscription' ) ) { ?>
						<?php echo esc_attr__( 'Sign up', 'commercegurus-commercekit' ); ?>
					<?php } else { ?>
						<?php echo esc_attr__( 'Add to cart', 'woocommerce' ); ?>
					<?php } ?>
				</a>
			</div>
		</div>
	</div>
</section>
	<?php
}
add_action( 'woocommerce_before_single_product_summary', 'commercekit_sticky_atc_bar_wrappers', 30 );

/**
 * Sticky Add to Cart Bar before form.
 */
function commercekit_sticky_atc_bar_before_form() {
	global $product, $cgkit_atc_grouped;
	static $atc_before_form = false;

	if ( $atc_before_form ) {
		return;
	}

	if ( ! is_product() ) {
		return;
	}

	$product_id = $product ? $product->get_id() : 0;
	if ( $product_id ) {
		$disable_sticky_atc = (int) get_post_meta( $product_id, 'commercekit_disable_sticky_atc', true );
		if ( 1 === $disable_sticky_atc ) {
			return;
		}
	}

	if ( ! $product || ! commercekit_sticky_atc_is_allowed_product_type( $product ) ) {
		return;
	}
	if ( ! $product->is_in_stock() ) {
		return;
	}
	if ( $product->is_type( 'grouped' ) && true !== $cgkit_atc_grouped ) {
		return;
	}
	?>
<button type="button" class="commercekit-pdp-sticky-inner__close-button" aria-label="Close sticky add to cart">
	<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="commercekit-pdp-sticky-inner__close-icon">
		<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
	</svg>
</button>
<div class="commercekit-pdp-sticky-inner">
	<div class="commercekit-pdp-before-form">
		<div class="commercekit-pdp-before-form_wrapper">
			<div id="commercekit-sticky-atc-title" class="screen-reader-text commercekit-pdp-sticky-atc-title"><?php esc_html_e( 'Add to Cart', 'commercegurus-commercekit' ); ?></div>
			<div class="cgkit-sticky-atc-image">
				<?php echo wp_kses_post( woocommerce_get_product_thumbnail( 'woocommerce_gallery_thumbnail' ) ); ?>
			</div>
			<div class="product-info">
				<span class="content-title"><?php echo esc_html( $product->get_title() ); ?></span>
				<div class="cgkit-as-variation-price"><span class="price"><?php commercekit_module_output( $product->get_price_html() ); ?></span></div>
				<?php
				$count = $product->get_review_count();
				if ( $count && wc_review_ratings_enabled() ) {
					echo wc_get_rating_html( $product->get_average_rating() ); // phpcs:ignore
				}
				?>
			</div>
		</div>			
	</div>
	<?php
	if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) || $product->is_type( 'subscription' ) ) {
		echo '<div class="commercekit-pdp-simple-form">';
	}
	if ( $product->is_type( 'grouped' ) && true === $cgkit_atc_grouped ) {
		echo '{CGKIT-SATC-GROUPED-TABLE}';
		echo '</div>';
	}
	$atc_before_form = true;
}
add_action( 'woocommerce_before_variations_form', 'commercekit_sticky_atc_bar_before_form', 25 );
add_action( 'woocommerce_before_add_to_cart_button', 'commercekit_sticky_atc_bar_before_form', 25 );

/**
 * Sticky Add to Cart Bar before form.
 */
function commercekit_sticky_atc_intersector_open() {
	?>
	<!-- Start sticky Add to Cart Intersector -->
	<div class="commercekit-sticky-atc-intersector">
	<?php

}
add_action( 'woocommerce_before_add_to_cart_button', 'commercekit_sticky_atc_intersector_open', 26 );

/**
 * Sticky Add to Cart Bar before form.
 */
function commercekit_sticky_atc_intersector_close() {
	?>
	</div><!-- End sticky Add to Cart Intersector -->
	<?php

}
add_action( 'woocommerce_after_add_to_cart_button', 'commercekit_sticky_atc_intersector_close', 5 );


/**
 * Sticky Add to Cart Bar after form.
 */
function commercekit_sticky_atc_bar_after_form() {
	global $product;
	static $atc_after_form = false;

	if ( $atc_after_form ) {
		return;
	}

	if ( ! is_product() ) {
		return;
	}

	if ( ! $product || ! commercekit_sticky_atc_is_allowed_product_type( $product ) ) {
		return;
	}

	$product_id = $product ? $product->get_id() : 0;
	if ( $product_id ) {
		$disable_sticky_atc = (int) get_post_meta( $product_id, 'commercekit_disable_sticky_atc', true );
		if ( 1 === $disable_sticky_atc ) {
			return;
		}
	}

	if ( ! $product || ! commercekit_sticky_atc_is_allowed_product_type( $product ) ) {
		return;
	}
	if ( ! $product->is_in_stock() ) {
		return;
	}
	if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) || $product->is_type( 'subscription' ) ) {
		echo '</div>';
	}
	if ( $product->is_type( 'grouped' ) ) {
		return;
	}
	?>
</div>
	<?php
	$atc_after_form = true;
}
add_action( 'woocommerce_after_variations_form', 'commercekit_sticky_atc_bar_after_form', 25 );
add_action( 'woocommerce_after_add_to_cart_button', 'commercekit_sticky_atc_bar_after_form', 25 );

/**
 * Sticky atc before elementor pro add to cart.
 *
 * @param string $widget widget object.
 */
function commercekit_sticky_atc_before_elmpro_add_to_cart( $widget ) {
	global $cgkit_elmpro_sticky_atc, $product;
	if ( 'woocommerce-product-add-to-cart' !== $widget->get_name() && 'woocommerce-product-data-tabs' !== $widget->get_name() ) {
		return;
	}

	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! commercekit_sticky_atc_is_allowed_product_type( $product ) ) {
		return;
	}

	if ( 'woocommerce-product-add-to-cart' === $widget->get_name() ) {
		echo '<div class="cgkit-sticky-atc-elm-wrap">';
	}
	$cgkit_elmpro_sticky_atc = true;
}
add_action( 'elementor/frontend/widget/before_render', 'commercekit_sticky_atc_before_elmpro_add_to_cart', 0, 1 );

/**
 * Sticky atc after elementor pro add to cart.
 *
 * @param string $widget widget object.
 */
function commercekit_sticky_atc_after_elmpro_add_to_cart( $widget ) {
	global $product;
	if ( 'woocommerce-product-add-to-cart' !== $widget->get_name() ) {
		return;
	}

	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! commercekit_sticky_atc_is_allowed_product_type( $product ) ) {
		return;
	}
	?>
	<div class="cgkit-elementor-sticky-add-to-cart-button">
		<div class="elm-sticky-atc_button-wrap">
		<button type="button" class="elm-sticky-atc_button button" aria-expanded="false">
			<?php if ( $product->is_type( 'external' ) ) { ?>
				<?php echo esc_attr( $product->single_add_to_cart_text() ); ?>
			<?php } elseif ( $product->is_type( 'subscription' ) || $product->is_type( 'variable-subscription' ) ) { ?>
				<?php echo esc_attr__( 'Sign up', 'commercegurus-commercekit' ); ?>
			<?php } else { ?>
				<?php echo esc_attr__( 'Add to cart', 'woocommerce' ); ?>
			<?php } ?>
		</button>
		</div>
	</div>
	</div><!--End cgkit widget wrapper-->
	<?php
}
add_action( 'elementor/frontend/widget/after_render', 'commercekit_sticky_atc_after_elmpro_add_to_cart', 99, 1 );

/**
 * Sticky Add to Cart Bar script.
 */
function commercekit_sticky_atc_bar_script() {
	global $product, $cgkit_atc_grouped;

	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$options    = get_option( 'commercekit', array() );
	$flags      = commercekit_feature_flags()->get_flags();
	$product_id = $product ? $product->get_id() : 0;
	if ( $product_id ) {
		$disable_sticky_atc = (int) get_post_meta( $product_id, 'commercekit_disable_sticky_atc', true );
		if ( 1 === $disable_sticky_atc ) {
			return;
		}
	}

	if ( ! $product || ! commercekit_sticky_atc_is_allowed_product_type( $product ) ) {
		return;
	}
	$cgkit_sticky_hdr_class = apply_filters( 'commercekit_sticky_header_css_class', 'body.sticky-m header.site-header' );
	if ( empty( $cgkit_sticky_hdr_class ) ) {
		$cgkit_sticky_hdr_class = 'body.sticky-m header.site-header';
	}
	$enable_desk = defined( 'COMMERCEKIT_STICKY_ATC_DESKTOP_VISIBLE' ) ? COMMERCEKIT_STICKY_ATC_DESKTOP_VISIBLE : ( isset( $flags['sticky_atc_desktop'] ) && 1 === (int) $flags['sticky_atc_desktop'] ? true : false );
	$enable_mobi = defined( 'COMMERCEKIT_STICKY_ATC_MOBILE_VISIBLE' ) ? COMMERCEKIT_STICKY_ATC_MOBILE_VISIBLE : ( isset( $flags['sticky_atc_mobile'] ) && 1 === (int) $flags['sticky_atc_mobile'] ? true : false );

	$enable_clone = $enable_desk || $enable_mobi ? 1 : 0;
	?>
	<?php if ( $product->is_type( 'grouped' ) ) { ?>
	<div id="cgkit-atc-grouped-tmp" style="display: none;">
		<?php
			$cgkit_atc_grouped = true;
			commercekit_sticky_atc_bar_before_form();
		?>
	</div>
	<?php } ?>
<script>
var cgkit_satc_enabled = <?php echo 1 === (int) $enable_clone ? 'true' : 'false'; ?>;
var cgkit_elem = document.querySelector( '.summary form.cart, .cgkit-sticky-atc-elm-wrap form.cart' );
if ( cgkit_elem && cgkit_satc_enabled ) {
	var cgkit_clone = cgkit_elem.cloneNode( true ); 
	cgkit_clone.classList.add( 'commercekit_sticky-atc' );
	cgkit_elem.classList.add( 'commercekit_sticky-atc-origin' );
	if ( cgkit_clone.classList.contains( 'grouped_form' ) ) {
		var cgkit_gtmp = document.querySelector( '#cgkit-atc-grouped-tmp' );
		if ( cgkit_gtmp ) {
			var gtmp_html = cgkit_gtmp.innerHTML;
			var gfrm_html = cgkit_clone.innerHTML;
			gtmp_html = gtmp_html.replace( '{CGKIT-SATC-GROUPED-TABLE}', gfrm_html );
			cgkit_clone.innerHTML = gtmp_html;
		}
	}

	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			if (mutation.addedNodes) {
				mutation.addedNodes.forEach(function(node) {
					if (node.matches && node.matches('.commercekit_sticky-atc')) {
						cgkit_set_a11y_elements();
						observer.disconnect();
					}
				});
			}
		});
	});

	// Start observing
	observer.observe(document.body, {
		childList: true,
		subtree: true
	});

	cgkit_elem.after(cgkit_clone);

	if ( cgkit_clone.classList.contains( 'variations_form' ) ) {
		var var_tbl = cgkit_clone.querySelector('table.variations');
		if ( var_tbl ) {
			var var_tbl_wrp = document.createElement( 'div' );
			var_tbl_wrp.classList.add( 'commercekit-pdp-variation-table' )
			var_tbl.parentNode.insertBefore( var_tbl_wrp, var_tbl );
			var_tbl_wrp.appendChild( var_tbl );
		}
	}

	if ( cgkit_clone.classList.contains( 'grouped_form' ) ) {
		var grp_tbl = cgkit_clone.querySelector('table.group_table');
		if ( grp_tbl ) {
			var grp_tbl_wrp = document.createElement( 'div' );
			grp_tbl_wrp.classList.add( 'commercekit-pdp-grouped-form' )
			grp_tbl.parentNode.insertBefore( grp_tbl_wrp, grp_tbl );
			grp_tbl_wrp.appendChild( grp_tbl );
		}
	}

	if ( cgkit_clone.classList.contains( 'bundle_form' ) ) {
		var bndl_data = cgkit_clone.querySelector( '.bundle_data' );
		if ( bndl_data ) {
			var bndl_id = bndl_data.getAttribute( 'data-bundle_id' );
			if ( bndl_id ) {
				bndl_data.setAttribute( 'data-bundle_id', bndl_id + '2' );
			}
			var bndl_id2 = bndl_data.getAttribute( 'data-bundle-id' );
			if ( bndl_id2 ) {
				bndl_data.setAttribute( 'data-bundle-id', bndl_id2 + '2' );
			}
		}
	}

	var cgkit_image = cgkit_clone.querySelector( '.cgkit-sticky-atc-image img' );
	if ( cgkit_image ) {
		cgkit_image.classList.add( 'wp-post-image-cgas' );
		<?php if ( defined( 'CGKIT_PDP_SINGLE_JSON' ) && true === CGKIT_PDP_SINGLE_JSON ) { ?>
		var cgkit_image_data = '';
		<?php } else { ?>
		var cgkit_image_data = '<?php echo commercekit_sticky_atc_variation_images( $product ); // phpcs:ignore ?>';
		<?php } ?>
		cgkit_clone.setAttribute( 'data-images', cgkit_image_data );
	}
}

const atc_form_clone = document.querySelector( 'form.commercekit_sticky-atc' );
const atc_form_clone_inner = document.querySelector( 'form.commercekit_sticky-atc .commercekit-pdp-sticky-inner' );
const desktop_atc_trigger = document.querySelector( '.commercekit-atc-tab-links .sticky-atc_button, .cgkit-sticky-atc-elm-wrap .elm-sticky-atc_button' );
const mobile_atc_trigger = document.querySelector( '.cgkit-mobile-atc-product-variable .sticky-atc_button, .cgkit-sticky-atc-elm-wrap .elm-sticky-atc_button' );
const sticky_atc_close_button = document.querySelector('form.commercekit_sticky-atc .commercekit-pdp-sticky-inner__close-button');

window.addEventListener( 'click', function( event ) {
	if ( ! cgkit_satc_enabled ) {
		return;
	}
	var elem = event.target;
	var elemp = elem.closest( '.sticky-atc_button, .elm-sticky-atc_button' );
	if ( elem.classList.contains( 'sticky-atc_button' ) || elem.classList.contains( 'elm-sticky-atc_button' ) || elemp ) {
		event.stopPropagation();
		event.preventDefault();
		if ( cgkit_elem.classList.contains( 'bundle_form' ) ) {
			window.scroll( {
				behavior: 'smooth',
				left: 0,
				top: cgkit_get_element_offset_top(cgkit_elem),
			} );
		} else {
			document.querySelector( 'body' ).classList.add( 'sticky-atc-open' );
			cgkit_update_sticky_atc_tabindex( 0 );
			if (window.innerWidth <= 992) {
				mobile_atc_trigger.setAttribute('aria-expanded', 'true');
			} else {
				desktop_atc_trigger.setAttribute('aria-expanded', 'true');
			}
			atc_form_clone_inner.setAttribute('aria-hidden', 'false');
			atc_form_clone.addEventListener('transitionend', function handler(event) {
				if (event.propertyName === 'right' || event.propertyName === 'bottom') {
					sticky_atc_close_button.focus();
					atc_form_clone.removeEventListener('transitionend', handler);
					cgkit_trap_focus(atc_form_clone);
				}
			});
		}
		return;
	}
	if ( typeof makeTouchstartWithClick === 'function' ) {
		makeTouchstartWithClick( event );
	}
	canRunClickFunc = false;
} );

if ( cgkit_elem ) {
	var cgkit_elemp = cgkit_elem.closest( 'form.cart' || cgkit_elemp );
	if ( cgkit_elem.classList.contains( 'cart' ) ) {
		document.querySelector( 'body' ).classList.remove( 'sticky-atc-open' );
		cgkit_update_sticky_atc_tabindex( -1 );
	}
}

var specifiedElement = document.getElementsByClassName('commercekit_sticky-atc')[0];
if ( specifiedElement ) {
	document.addEventListener( 'click', function( event ) {
		var isClickInside = specifiedElement.contains( event.target );
		if ( ! isClickInside && event.screenX && event.screenX != 0 && event.screenY && event.screenY != 0 ) {
			document.querySelector( 'body' ).classList.remove( 'sticky-atc-open' );
			cgkit_update_sticky_atc_tabindex( -1 );
		}
	} );
}

document.addEventListener('DOMContentLoaded', function() {
	document.addEventListener('click', function(event) {
		const closeButton = event.target.closest('.commercekit-pdp-sticky-inner__close-button');
		if (closeButton) {
			cgkit_close_side_cart();
			event.preventDefault();
		}
	});
	document.addEventListener('keydown', function(event) {
		if (event.key === "Escape" || event.keyCode === 27) { 
			if (document.querySelector('body').classList.contains('sticky-atc-open')) {
				cgkit_close_side_cart();
				event.preventDefault();
			}
		}
	});
});

var cgkit_stickycontainer = document.getElementsByClassName('commercekit-sticky-add-to-cart')[0];
if( cgkit_stickycontainer ) {
	function cgkit_add_class_on_scroll() {
		cgkit_stickycontainer.classList.add( 'visible' );
	}
	function cgkit_remove_class_on_scroll() {
		cgkit_stickycontainer.classList.remove( 'visible' );
	}
	window.addEventListener( 'scroll', function() {
		cgkit_scrollpos = window.scrollY;
		if ( cgkit_scrollpos > 150 ) {
			cgkit_add_class_on_scroll();
		} else {
			cgkit_remove_class_on_scroll();
		}
	});
	window.addEventListener( 'scroll', function( e ) {
		if ( window.innerHeight + window.pageYOffset === document.documentElement.offsetHeight ) {
			cgkit_remove_class_on_scroll();
		}
	});
	document.addEventListener( 'scroll', function(e) {
		if ( ( window.innerHeight + window.scrollY ) >= document.body.scrollHeight ) {
			cgkit_remove_class_on_scroll();
		}
	});
}
function cgkit_synchronize_variation_forms( cgkit_select ) {
	var cgkit_form = cgkit_select.closest( 'form' );
	var cgkit_form_clone = null;
	var cgkit_select_clone = null;
	if( cgkit_form.classList.contains( 'commercekit_sticky-atc-origin' ) ) {
		cgkit_select_clone = document.querySelector( 'form.commercekit_sticky-atc select[name="'+cgkit_select.getAttribute('name')+'"]' );
		cgkit_form_clone = cgkit_select_clone.closest( 'form' );
	} else {
		cgkit_select_clone = document.querySelector( 'form.commercekit_sticky-atc-origin select[name="'+cgkit_select.getAttribute('name')+'"]' );
		cgkit_form_clone = cgkit_select_clone.closest( 'form' );
	}
	if ( cgkit_select_clone  ){
		if ( cgkit_select.value == cgkit_select_clone.value ) {
			return;
		} else {
			cgkit_select_clone.value = cgkit_select.value;
			setTimeout( function() {
				if( jQuery ) {
					jQuery( cgkit_select_clone ).change();
				} else {
					cgkit_select_clone.dispatchEvent( new Event('change') );
				}
				if ( typeof cgkitUpdateAvailableAttributes === 'function' ) {
					setTimeout(function(){
						cgkitUpdateAvailableAttributes(cgkit_form_clone);
						var cgkit_swatches = cgkit_form_clone.querySelectorAll('.cgkit-attribute-swatches');
						var is_image_updated = false;
						cgkit_swatches.forEach(function(cgkit_swatch){
							var cgkit_sel_swatche = cgkit_swatch.querySelector('.cgkit-swatch.cgkit-swatch-selected');
							if( cgkit_sel_swatche ){
								cgkitUpdateAttributeSwatch2(cgkit_sel_swatche);
								cgkitUpdateAttributeSwatchImage(cgkit_sel_swatche);
								var var_img = cgkit_sel_swatche.getAttribute( 'data-gimg_id' );
								if ( var_img ) {
									is_image_updated = true;
								}
							} else {
								var no_selection = cgkit_swatch.getAttribute('data-no-selection');
								var text_tr = cgkit_swatch.closest('tr');
								var text_obj = text_tr ? text_tr.querySelector('.cgkit-chosen-attribute') : null;
								if( text_obj ){
									text_obj.innerHTML = no_selection;
									text_obj.classList.add('no-selection');
								}
							}
						});
						var vimg_id = cgkit_form_clone.getAttribute('current-image');
						if( ! is_image_updated && vimg_id != '' ){
							is_image_updated = true;
						}
						if ( ! is_image_updated ) {
							cgkitClearAttributeSwatchImage(cgkit_form_clone);
							cgkitClearAttributeSwatchImage(cgkit_form);
						}
					}, 100);
				}
			}, 100 );
		}
	}
}
function cgkit_synchronize_input_qty( cgkit_input ) {
	var cgkit_form = cgkit_input.closest( 'form' );
	var cgkit_input_clone = null;
	if( cgkit_form.classList.contains( 'commercekit_sticky-atc-origin' ) ) {
		cgkit_input_clone = document.querySelector( 'form.commercekit_sticky-atc input.qty[name="'+cgkit_input.getAttribute('name')+'"]' );
	} else {
		cgkit_input_clone = document.querySelector( 'form.commercekit_sticky-atc-origin input.qty[name="'+cgkit_input.getAttribute('name')+'"]' );
	}
	if ( cgkit_input_clone  ){
		if ( cgkit_input.value == cgkit_input_clone.value ) {
			return;
		} else {
			cgkit_input_clone.value = cgkit_input.value;
			setTimeout( function() {
				if( jQuery ) {
					jQuery( cgkit_input_clone ).change();
				} else {
					cgkit_input_clone.dispatchEvent( new Event('change') );
				}
			}, 100 );
		}
	}
}
function loadQATC(){
	if( jQuery ) {
		jQuery( 'body' ).on( 'change', 'form.commercekit_sticky-atc-origin .variations select, form.commercekit_sticky-atc .variations select', function() {
			cgkit_synchronize_variation_forms( this );
		});
		jQuery( 'body' ).on( 'change', 'form.commercekit_sticky-atc-origin input.qty, form.commercekit_sticky-atc input.qty', function() {
			cgkit_synchronize_input_qty( this );
		});
	}
}
const QATCUserInteractionEvents = ['mouseover', 'keydown', 'touchstart', 'touchmove', 'wheel'];
QATCUserInteractionEvents.forEach(function(event) {
	window.addEventListener(event, triggerQATCScriptLoader, {
		passive: !0
	})
});
function triggerQATCScriptLoader() {
	loadQATCScripts();
	QATCUserInteractionEvents.forEach(function(event) {
		window.removeEventListener(event, triggerQATCScriptLoader, {
			passive: !0
		})
	})
}
function loadQATCScripts() {
	loadQATC();
}
var cgkit_tab_click = false;
var cgkit_tab_click_id = 0; 
window.addEventListener( 'click', function( event ) {
	var elem = event.target;
	var elemp = elem.closest( '.commercekit-atc-tab' );
	if ( elem.classList.contains( 'commercekit-atc-tab' ) || elemp ) {
		event.preventDefault();
		if( elemp ){
			var tid = elemp.getAttribute('data-id');
			var cid = elemp.getAttribute('href');
		} else {
			var tid = elem.getAttribute('data-id');
			var cid = elem.getAttribute('href');
		}
		var wctab = document.querySelector( '.wc-tabs ' + tid + ' > a' );
		if( wctab ){
			wctab.click();
			window.dispatchEvent(new Event('resize'));
		}
		var sticky_tab = document.querySelector( tid + '-title' );
		if( sticky_tab ){
			cgkit_sticky_tabs_activate( sticky_tab );
		}
		cgkit_tab_click = true;
		if ( ! cgkit_tab_click_id ) {
			cgkit_tab_click_id = setTimeout( function(){ cgkit_tab_click = false; cgkit_tab_click_id = 0; }, 1000 );
		} else {
			clearTimeout( cgkit_tab_click_id );
			cgkit_tab_click_id = setTimeout( function(){ cgkit_tab_click = false; cgkit_tab_click_id = 0; }, 1000 );
		}
		if( cid == '#' ){
			window.scroll( {
				behavior: 'smooth',
				left: 0,
				top: 0,
			} );
			return;
		}
		<?php if ( isset( $flags['sticky_atc_tabs'] ) && 1 === (int) $flags['sticky_atc_tabs'] ) { ?>
		var wctabc = document.querySelector(cid);
		<?php } else { ?>
		var wctabc = document.querySelector( '.product .woocommerce-tabs' );
		<?php } ?>
		if( wctabc ){
			window.scroll( {
				behavior: 'smooth',
				left: 0,
				top: cgkit_get_element_offset_top( wctabc ),
			} );
		}
		return;
	}
} );
var cgkit_tablis = document.querySelectorAll( 'ul.wc-tabs li' );
cgkit_tablis.forEach( function( cgkit_tabli ) {
	var cgkit_tabli_obsr = new MutationObserver( ( changes ) => {
		changes.forEach( change => {
			if ( change.attributeName.includes( 'class' ) ) {
				if ( cgkit_tabli.classList.contains( 'active' ) ) {
					var tab_id = cgkit_tabli.getAttribute( 'id' );
					tab_id = '#cgkit-' + tab_id;
					var tab_li = document.querySelector( tab_id );
					if ( tab_li ) {
						var tablis = document.querySelectorAll( 'ul.commercekit-atc-tab-links li' );
						tablis.forEach( function( tabli ) {
							tabli.classList.remove( 'active' );
						} );
						tab_li.classList.add( 'active' );
					}
				}
			}
		} );
	} );
	cgkit_tabli_obsr.observe( cgkit_tabli, { attributes : true } );
} );
var cgkit_body = document.querySelector( 'body' );
var cgkit_body_obsr = new MutationObserver( ( changes ) => {
	changes.forEach( change => {
		if ( change.attributeName.includes( 'class' ) ) {
			if ( cgkit_body.classList.contains( 'drawer-open' ) && cgkit_body.classList.contains( 'sticky-atc-open' ) ) {
				cgkit_body.classList.remove( 'sticky-atc-open' );
				cgkit_update_sticky_atc_tabindex( -1 );
			}
		}
	} );
} );
cgkit_body_obsr.observe( cgkit_body, { attributes : true } );
var first_tab = document.querySelector( 'ul.commercekit-atc-tab-links li:first-child' );
if( first_tab ) {
	first_tab.classList.add( 'active' )
}
var cgkit_sticky_tabs = document.querySelector( '#commercekit-atc-tabs-wrap' );
if ( cgkit_sticky_tabs ) {
	document.addEventListener( 'scroll', function(e) {
		if ( cgkit_tab_click ) {
			return
		}
		var sticky_start = cgkit_get_element_offset_top( cgkit_sticky_tabs );
		var cgkit_gallery = document.querySelector( '#cgkit-tab-commercekit-gallery-title' );
		if ( window.pageYOffset < sticky_start ) {
			if ( cgkit_gallery ) {
				cgkit_sticky_tabs_activate( cgkit_gallery );
				return
			}
		}
		var cgkit_tabs = document.querySelectorAll( '#commercekit-atc-tabs-wrap .woocommerce-Tabs-panel' );
		var cgkit_tab_activated = false;
		cgkit_tabs.forEach( function( cgkit_tab ) {
			var tab_start = cgkit_get_element_offset_top( cgkit_tab );
			var tab_end = cgkit_tab.clientHeight + tab_start;
			var tab_id = '#' + cgkit_tab.getAttribute( 'id' ) + '-title';
			var tab_link = document.querySelector( tab_id );
			if ( ! tab_link ) {
				return;
			}
			if ( window.pageYOffset >= tab_start && window.pageYOffset < tab_end ) {
				cgkit_sticky_tabs_activate( tab_link );
				cgkit_tab_activated = true;
				return;
			}
		} );
		if ( ! cgkit_tab_activated ) {
			cgkit_sticky_tabs_activate( null );
		}
	});
}
function cgkit_sticky_tabs_activate( tab_link ) {
	if ( tab_link ) {
		if ( tab_link.classList.contains( 'active' ) ) {
			return;
		}
	}
	var sticky_tabs = document.querySelectorAll( 'ul.commercekit-atc-tab-links li' );
	sticky_tabs.forEach( function( sticky_tab ) {
		sticky_tab.classList.remove( 'active' )
	} );
	if ( tab_link ) {
		tab_link.classList.add( 'active' );
		tab_link.parentNode.scrollTo( { left: tab_link.offsetLeft, behavior: 'smooth' } );
	}
}
var cgkit_sticky_bar = document.querySelector( '.commercekit-atc-sticky-tabs' );
if ( cgkit_sticky_bar ) {
	document.querySelector( 'body' ).classList.add( 'ckit_stickyatc_active' );
	var cgkit_sticky_pos = cgkit_sticky_bar.offsetTop;
	document.addEventListener( 'scroll', function(e){
	if ( cgkit_sticky_bar.offsetTop != cgkit_sticky_pos ) {
			cgkit_sticky_bar.classList.add( 'commercekit-atc-stuck' );
		} else {
			cgkit_sticky_bar.classList.remove( 'commercekit-atc-stuck' );
		}
		cgkit_sticky_pos = cgkit_sticky_bar.offsetTop;
	});
}
function cgkit_get_element_offset_top( elem ) {
	var offsetTop = 0;
	while ( elem ) {
		offsetTop += elem.offsetTop;
		elem = elem.offsetParent;
	}
	var cgkit_sticky_bar = document.querySelector( '.commercekit-atc-sticky-tabs' );
	if ( cgkit_sticky_bar ) {
		offsetTop = offsetTop - cgkit_sticky_bar.clientHeight;
	}
	if ( window.innerWidth <= 992 ) {
		var cgkit_sticky_hdr = document.querySelector( '<?php echo esc_attr( $cgkit_sticky_hdr_class ); ?>' );
		if ( cgkit_sticky_hdr ) {
			offsetTop = offsetTop - cgkit_sticky_hdr.clientHeight;
		}
	}
	return offsetTop;
}
var cgkit_scroll_bar = document.querySelector( '.commercekit-atc-sticky-tabs .commercekit-atc-tab-links' );
if ( cgkit_scroll_bar ) {
	let cgkitMouseDown = false;
	let cgkitMouseMoveCount = 0;
	let cgkitStartX, cgkitScrollLeft;
	let cgkitStartDragging = function( e ) {
		cgkitMouseDown = true;
		cgkitStartX = e.pageX - cgkit_scroll_bar.offsetLeft;
		cgkitScrollLeft = cgkit_scroll_bar.scrollLeft;
	};
	let cgkitStopDragging = function( e ) {
		cgkitMouseDown = false;
		cgkit_scroll_bar.classList.remove( 'cgkit-dragging' );
		cgkitMouseMoveCount = 0;
	};
	cgkit_scroll_bar.addEventListener( 'mousemove', (e) => {
		e.preventDefault();
		if ( ! cgkitMouseDown ) {
			return;
		}
		const cgkitX = e.pageX - cgkit_scroll_bar.offsetLeft;
		const cgkitScroll = cgkitX - cgkitStartX;
		cgkit_scroll_bar.scrollLeft = cgkitScrollLeft - cgkitScroll;
		if ( cgkitMouseMoveCount >= 10 ) {
			cgkit_scroll_bar.classList.add( 'cgkit-dragging' );
		} else {
			cgkitMouseMoveCount++;
		}
	});
	cgkit_scroll_bar.addEventListener( 'mousedown', cgkitStartDragging, false );
	cgkit_scroll_bar.addEventListener( 'mouseup', cgkitStopDragging, false );
	cgkit_scroll_bar.addEventListener( 'mouseleave', cgkitStopDragging, false );	
}

document.addEventListener( 'DOMContentLoaded' , function() {
	if( window.innerWidth <= 992 ) {
		const cgkit_wc_main_atc = document.querySelector( '.commercekit_sticky-atc-origin .commercekit-sticky-atc-intersector' );
		const cgkit_wc_sticky_atc = document.getElementById( 'cgkit-mobile-commercekit-sticky-atc' );
		if ( !cgkit_wc_main_atc || !cgkit_wc_sticky_atc ) return;
		const observerCallback = ( entries, observer ) => {
			entries.forEach( entry => {
				if( entry.isIntersecting ) {
					document.querySelector( 'body' ).classList.remove( 'cta-off-screen' );
				} else {
					document.querySelector( 'body' ).classList.add( 'cta-off-screen' );
				}
			});
		};
		const observer = new IntersectionObserver( observerCallback );
		observer.observe( cgkit_wc_main_atc );
	}
	cgkit_update_sticky_atc_tabindex( -1 );
});

function cgkit_close_side_cart() {
	if ( ! cgkit_satc_enabled ) {
		return;
	}
	document.querySelector('body').classList.remove('sticky-atc-open');
	cgkit_update_sticky_atc_tabindex( -1 );
	atc_form_clone_inner.setAttribute('aria-hidden', 'true');
	if (window.innerWidth <= 992) {
		atc_form_clone.addEventListener('transitionend', function() {
				document.querySelector('#cgkit-mobile-commercekit-sticky-atc .sticky-atc_button, .cgkit-sticky-atc-elm-wrap .elm-sticky-atc_button').focus();
		});
		mobile_atc_trigger.setAttribute('aria-expanded', 'false');
	} else {
		atc_form_clone.addEventListener('transitionend', function() {
			document.querySelector('.commercekit-atc-sticky-tabs .sticky-atc_button, .cgkit-sticky-atc-elm-wrap .elm-sticky-atc_button').focus();
		});
		desktop_atc_trigger.setAttribute('aria-expanded', 'false');
	}
}

function cgkit_trap_focus(element) {
	const focusableElements = element.querySelectorAll('a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select');
	const firstFocusableElement = focusableElements[0];
	const lastFocusableElement = focusableElements[focusableElements.length - 1];

	firstFocusableElement.focus();

	element.addEventListener('keydown', function(e) {
		let isTabPressed = e.key === 'Tab' || e.keyCode === 9;
		if (!isTabPressed) {
			return;
		}
		if (e.shiftKey) { 
			if (document.activeElement === firstFocusableElement) {
				lastFocusableElement.focus();
				e.preventDefault();
			}
		} else { 
			if (document.activeElement === lastFocusableElement) { 
				firstFocusableElement.focus(); 
				e.preventDefault();
			}
		}
	});
}

function cgkit_set_a11y_elements() {
	var cloned_atc = document.querySelector('.commercekit_sticky-atc');
	if (cloned_atc) {
		var stickyInnerDiv = cloned_atc.querySelector('.commercekit-pdp-sticky-inner');
		if (stickyInnerDiv) {
			stickyInnerDiv.setAttribute('aria-hidden', 'true');
			stickyInnerDiv.setAttribute('role', 'dialog');
			stickyInnerDiv.setAttribute('aria-modal', 'true');
			stickyInnerDiv.setAttribute('tabindex', '-1');
			stickyInnerDiv.setAttribute('aria-labelledby', 'commercekit-sticky-atc-title');

		}
	}
}
function cgkit_update_sticky_atc_tabindex( $index ) {
	var stickyForm = document.querySelector( 'form.cart.commercekit_sticky-atc' );
	if ( stickyForm ) {
		stickyTabElements = Array.from( stickyForm.querySelectorAll( 'a[href], button, textarea, input, select, [tabindex]' ) );
		stickyTabElements.forEach( function( element ) {
			element.setAttribute( 'tabindex', $index );
		} );
	}
}
var cgkit_eatc_button = document.querySelector( '.cgkit-sticky-atc-elm-wrap .single_add_to_cart_button' );
var cgkit_satc_button = document.querySelector( '.cgkit-sticky-atc-elm-wrap .cgkit-elementor-sticky-add-to-cart-button' );
if( cgkit_eatc_button && cgkit_satc_button ){
	document.addEventListener( 'scroll', function(e) {
		var cgkit_hdr_height = 0;
		if ( window.innerWidth <= 992 ) {
			var cgkit_satc_hdr = document.querySelector( '<?php echo esc_attr( $cgkit_sticky_hdr_class ); ?>' );
			if ( cgkit_satc_hdr ) {
				cgkit_hdr_height = cgkit_satc_hdr.offsetHeight;
			}
		}
		var rect = cgkit_eatc_button.getBoundingClientRect();
		if ( rect.top == 0 && rect.bottom == 0 && rect.left == 0 && rect.right == 0 ) {
			var wtls_button = cgkit_eatc_button.parentNode.querySelector( '#ckwtl-button3' );
			if ( wtls_button ) {
				rect = wtls_button.getBoundingClientRect();
			}
		}
		if ( rect.bottom <= cgkit_hdr_height && rect.left >= 0 && rect.right >= 0 ) {
			cgkit_satc_button.style.visibility = 'visible';
		} else {
			cgkit_satc_button.style.visibility = 'hidden';
		}
		var cgkit_elm_ctnr = cgkit_satc_button.closest( '.elementor-section > .elementor-container' );
		var cgkit_btn_wrap = cgkit_satc_button.querySelector( '.elm-sticky-atc_button-wrap' );
		if ( cgkit_elm_ctnr && cgkit_btn_wrap ) {
			cgkit_btn_wrap.style.width = cgkit_elm_ctnr.offsetWidth + 'px';
		}
		var cgkit_adminbar = document.querySelector( '#wpadminbar' );
		if ( cgkit_adminbar ) {
			if( window.innerWidth > 992 ) {
				cgkit_satc_button.style.top = cgkit_adminbar.offsetHeight + 'px';
			} else {
				cgkit_satc_button.style.top = 'unset';
			}
		}
	} );
}
</script>
<style>
.product.product-type-yith_bundle form.commercekit_sticky-atc .quantity-nav { height: 52px; }
	<?php if ( ! $enable_desk ) { ?>
@media (min-width: 993px) {
	.cgkit-elementor-sticky-add-to-cart-button {
		display: none;
	}
}
	<?php } ?>
	<?php if ( ! $enable_mobi ) { ?>
@media (max-width: 992px) {
	.cgkit-elementor-sticky-add-to-cart-button {
		display: none;
	}
}
	<?php } ?>
</style>
	<?php
}
add_action( 'wp_footer', 'commercekit_sticky_atc_bar_script' );

/**
 * Remove shoptimizer quick add to cart.
 */
function commercekit_sticky_atc_bar_remove_shoptimizer_atc() {
	global $product;

	$product_id = $product ? $product->get_id() : 0;
	if ( $product_id ) {
		$disable_sticky_atc = (int) get_post_meta( $product_id, 'commercekit_disable_sticky_atc', true );
		if ( 1 === $disable_sticky_atc ) {
			return;
		}
	}

	if ( ! $product || ! commercekit_sticky_atc_is_allowed_product_type( $product ) ) {
		return;
	}
	if ( ! $product->is_in_stock() ) {
		return;
	}
	$options = get_option( 'commercekit', array() );
	$flags   = commercekit_feature_flags()->get_flags();

	$sticky_atc_desktop = isset( $flags['sticky_atc_desktop'] ) && 1 === (int) $flags['sticky_atc_desktop'] ? 1 : 0;
	$sticky_atc_mobile  = isset( $flags['sticky_atc_mobile'] ) && 1 === (int) $flags['sticky_atc_mobile'] ? 1 : 0;
	$sticky_atc_tabs    = isset( $flags['sticky_atc_tabs'] ) && 1 === (int) $flags['sticky_atc_tabs'] ? 1 : 0;
	if ( $sticky_atc_desktop || $sticky_atc_mobile || $sticky_atc_tabs ) {
		remove_action( 'woocommerce_before_single_product_summary', 'shoptimizer_sticky_single_add_to_cart', 30 );
	}
}
add_action( 'woocommerce_before_single_product', 'commercekit_sticky_atc_bar_remove_shoptimizer_atc', 10 );

/**
 * Is allowed product type.
 *
 * @param string $product product object.
 */
function commercekit_sticky_atc_is_allowed_product_type( $product ) {
	if ( $product->is_type( array( 'simple', 'variable', 'external', 'grouped', 'bundle', 'subscription', 'variable-subscription', 'woosg', 'woosb', 'yith_bundle' ) ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Override WooCommerce tabs template.
 *
 * @param string $template template object.
 * @param string $template_name template name.
 * @param string $template_path template path.
 */
function commercekit_sticky_atc_locate_template( $template, $template_name, $template_path ) {
	global $post;
	$basename = basename( $template );

	if ( is_product() && 'tabs.php' === $basename ) {
		$options    = get_option( 'commercekit', array() );
		$flags      = commercekit_feature_flags()->get_flags();
		$product_id = isset( $post->ID ) ? $post->ID : 0;

		$disable_sticky_atc = 0;
		$product_object     = null;
		if ( $product_id ) {
			$disable_sticky_atc = (int) get_post_meta( $product_id, 'commercekit_disable_sticky_atc', true );
			$product_object     = wc_get_product( $product_id );
		}
		$enable_sticky_atc_tabs = isset( $flags['sticky_atc_tabs'] ) && 1 === (int) $flags['sticky_atc_tabs'] ? true : false;

		if ( $enable_sticky_atc_tabs && 1 !== $disable_sticky_atc && $product_object && commercekit_sticky_atc_is_allowed_product_type( $product_object ) ) {
			$template = dirname( __FILE__ ) . '/templates/commercekit-tabs.php';
		}
	}

	return $template;
}
add_filter( 'woocommerce_locate_template', 'commercekit_sticky_atc_locate_template', 99, 3 );
