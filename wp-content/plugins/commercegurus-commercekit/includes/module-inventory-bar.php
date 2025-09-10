<?php
/**
 *
 * Inventory Bar module
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

/**
 * Get round stock quantity
 *
 * @param  string $commercekit_stock_quantity of inventory bar.
 * @return string $commercekit_stock_quantity of inventory bar.
 */
function commercekit_get_round_stock_quantity( $commercekit_stock_quantity ) {
	if ( $commercekit_stock_quantity > 30 && $commercekit_stock_quantity <= 40 ) {
		$commercekit_stock_quantity = 40;
	} elseif ( $commercekit_stock_quantity > 40 && $commercekit_stock_quantity <= 50 ) {
		$commercekit_stock_quantity = 50;
	} elseif ( $commercekit_stock_quantity > 50 && $commercekit_stock_quantity <= 60 ) {
		$commercekit_stock_quantity = 60;
	} elseif ( $commercekit_stock_quantity > 60 && $commercekit_stock_quantity <= 70 ) {
		$commercekit_stock_quantity = 70;
	} elseif ( $commercekit_stock_quantity > 70 && $commercekit_stock_quantity <= 80 ) {
		$commercekit_stock_quantity = 80;
	} elseif ( $commercekit_stock_quantity > 80 && $commercekit_stock_quantity <= 90 ) {
		$commercekit_stock_quantity = 90;
	} elseif ( $commercekit_stock_quantity > 90 && $commercekit_stock_quantity <= 100 ) {
		$commercekit_stock_quantity = 100;
	}
	return $commercekit_stock_quantity;
}

/**
 * Get percent stock quantity
 *
 * @param  string $commercekit_stock_quantity of inventory bar.
 * @param  string $final_threshold final threshold.
 * @return string $stock_percent of inventory bar.
 */
function commercekit_get_percent_stock_quantity( $commercekit_stock_quantity, $final_threshold ) {
	$final_threshold = max( $commercekit_stock_quantity, $final_threshold );
	$stock_percent   = (int) ( ( $commercekit_stock_quantity / $final_threshold ) * 100 );
	if ( $stock_percent < 5 ) {
		$stock_percent = 5;
	}
	return $stock_percent;
}
/**
 * Single Product Page - Inventory Bar creation
 *
 * @param  string $display_text of inventory bar.
 * @param  string $display_text_31 of inventory bar.
 * @param  string $display_text_100 of inventory bar.
 */
function commercekit_inventory_number( $display_text, $display_text_31, $display_text_100 ) {
	global $post, $product, $cgkit_ibar_script;
	if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
		return;
	}
	$product_id = $product ? $product->get_id() : 0;
	if ( $product_id ) {
		$disable_cgkit_inventory = (int) get_post_meta( $product_id, 'commercekit_disable_inventory', true );
		if ( 1 === $disable_cgkit_inventory ) {
			return;
		}
	}
	$commercekit_stock_quantity = $product->get_stock_quantity();
	if ( 'outofstock' === $product->get_stock_status() ) {
		$commercekit_stock_quantity = 0;
	}
	if ( $product->is_type( 'simple' ) && 0 >= $commercekit_stock_quantity ) {
		return;
	}

	$product_ids = array( (int) $product_id );
	$categories  = array();
	$cat_terms   = get_the_terms( $product_id, 'product_cat' );
	if ( is_array( $cat_terms ) && count( $cat_terms ) ) {
		foreach ( $cat_terms as $cat_term ) {
			$categories[] = (int) $cat_term->term_id;
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
		}
	}

	$options   = get_option( 'commercekit', array() );
	$condition = isset( $options['inventory_condition'] ) ? $options['inventory_condition'] : 'all';
	$pids      = isset( $options['inventory_pids'] ) ? explode( ',', $options['inventory_pids'] ) : array();
	$pids      = array_map( 'intval', $pids );

	$low_threshold   = isset( $options['inventory_threshold'] ) && (int) $options['inventory_threshold'] ? (int) $options['inventory_threshold'] : (int) commercekit_get_default_settings( 'inventory_threshold' );
	$regr_threshold  = isset( $options['inventory_threshold31'] ) && (int) $options['inventory_threshold31'] ? (int) $options['inventory_threshold31'] : (int) commercekit_get_default_settings( 'inventory_threshold31' );
	$high_threshold  = isset( $options['inventory_threshold100'] ) && (int) $options['inventory_threshold100'] ? (int) $options['inventory_threshold100'] : (int) commercekit_get_default_settings( 'inventory_threshold100' );
	$final_threshold = max( $low_threshold, $regr_threshold, $high_threshold );

	$can_display = false;
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
	}

	if ( ! $can_display ) {
		return;
	}

	$stock_quantities            = array();
	$low_stock_amounts           = array();
	$stock_quantities['default'] = $commercekit_stock_quantity;
	if ( $product->is_type( 'variable' ) ) {
		$outofstocks = 0;
		$variations  = commercekit_get_available_variations( $product );
		if ( is_array( $variations ) && count( $variations ) ) {
			foreach ( $variations as $variation ) {
				if ( ! isset( $variation['is_in_stock'] ) || 1 !== (int) $variation['is_in_stock'] ) {
					$outofstocks++;
				} else {
					$stock_quantities[ $variation['variation_id'] ]  = isset( $variation['cgkit_stock_quantity'] ) ? (int) $variation['cgkit_stock_quantity'] : 0;
					$low_stock_amounts[ $variation['variation_id'] ] = isset( $variation['cgkit_low_stock_amount'] ) ? (int) $variation['cgkit_low_stock_amount'] : 0;
					if ( ! isset( $variation['cgkit_stock_quantity'] ) || ! isset( $variation['cgkit_low_stock_amount'] ) ) {
						$vproduct = wc_get_product( $variation['variation_id'] );
						if ( $vproduct ) {
							$stock_quantities[ $variation['variation_id'] ]  = (int) $vproduct->get_stock_quantity();
							$low_stock_amounts[ $variation['variation_id'] ] = (int) $vproduct->get_low_stock_amount();
						}
					}
				}
			}
			if ( count( $variations ) === $outofstocks && 0 >= $commercekit_stock_quantity ) {
				return;
			}
		} else {
			return;
		}
	}

	$shortcode = isset( $options['widget_pos_stockmeter'] ) && 1 === (int) $options['widget_pos_stockmeter'] ? true : false;
	$html      = '';

	$cgkit_ibar_script = false;
	if ( $product->is_type( 'simple' ) && $commercekit_stock_quantity ) {
		$commercekit_stock_percent = commercekit_get_percent_stock_quantity( $commercekit_stock_quantity, $final_threshold );
		$final_display_text        = $display_text;
		$low_stock_class           = 'high-stock';
		if ( $commercekit_stock_quantity > $low_threshold && $commercekit_stock_quantity <= $regr_threshold ) {
			$final_display_text = $display_text_31;
			$low_stock_class    = 'regular-stock';
		}
		if ( $commercekit_stock_quantity > $regr_threshold ) {
			$final_display_text = $display_text_100;
		}
		$low_stock_amount = (int) $product->get_low_stock_amount();
		if ( $low_stock_amount && $commercekit_stock_quantity <= $low_stock_amount ) {
			$low_stock_class    = 'low-stock';
			$final_display_text = $display_text;
		} elseif ( ! $low_stock_amount && $commercekit_stock_quantity < $low_threshold ) {
			$low_stock_class = 'low-stock';
		}
		if ( strpos( $final_display_text, '%' ) !== false && strpos( $final_display_text, '%s' ) === false ) {
			$final_display_text = str_replace( '%', '', $final_display_text );
		}
		$html .= '<div class="commercekit-inventory ' . ( true === $shortcode ? 'cgkit-inventory-shortcode' : '' ) . '"><span class="title ' . esc_html( $low_stock_class ) . '">' . esc_html( sprintf( $final_display_text, $commercekit_stock_quantity ) ) . '</span><div class="progress-bar full-bar active ' . esc_html( $low_stock_class ) . '-bar"><span style="width: ' . esc_html( $commercekit_stock_percent ) . '%;"></span></div></div>';

		$cgkit_ibar_script = true;
	}

	if ( $product->is_type( 'variable' ) && count( $stock_quantities ) ) {
		$html .= '<div class="commercekit-inventory ' . ( true === $shortcode ? 'cgkit-inventory-shortcode' : '' ) . '">';
		foreach ( $stock_quantities as $stock_key => $stock_value ) {
			if ( 0 >= $stock_value ) {
				continue;
			}
			$stock_percent = commercekit_get_percent_stock_quantity( $stock_value, $final_threshold );
			if ( ! $stock_value ) {
				continue;
			}
			$final_display_text = $display_text;
			$low_stock_class    = 'high-stock';
			if ( $stock_value > $low_threshold && $stock_value <= $regr_threshold ) {
				$final_display_text = $display_text_31;
				$low_stock_class    = 'regular-stock';
			}
			if ( $stock_value > $regr_threshold ) {
				$final_display_text = $display_text_100;
			}
			$low_stock_amount = isset( $low_stock_amounts[ $stock_key ] ) ? $low_stock_amounts[ $stock_key ] : 0;
			if ( $low_stock_amount && $stock_value <= $low_stock_amount ) {
				$low_stock_class    = 'low-stock';
				$final_display_text = $display_text;
			} elseif ( ! $low_stock_amount && $stock_value < $low_threshold ) {
				$low_stock_class = 'low-stock';
			}
			if ( strpos( $final_display_text, '%' ) !== false && strpos( $final_display_text, '%s' ) === false ) {
				$final_display_text = str_replace( '%', '', $final_display_text );
			}
			if ( 'default' === $stock_key ) {
				$html .= '<div class="cki-variation cki-variation-' . esc_html( $stock_key ) . '">';
			} else {
				$html .= '<div class="cki-variation cki-variation-' . esc_html( $stock_key ) . '" style="display: none;">';
			}
			$html .= '<span class="title ' . esc_html( $low_stock_class ) . '">' . esc_html( sprintf( $final_display_text, $stock_value ) ) . '</span> <div class="progress-bar full-bar ' . ( 'default' === $stock_key ? 'active' : '' ) . ' ' . esc_html( $low_stock_class ) . '-bar"><span style="width: ' . esc_html( $stock_percent ) . '%;"></span></div></div>';

			$cgkit_ibar_script = true;
		}
		$html .= '</div>';
	}

	if ( $shortcode ) {
		return $html;
	} else {
		echo $html; // phpcs:ignore
	}
}

/**
 * Print inventory bar script
 */
function commercekit_inventory_number_script() {
	global $cgkit_ibar_script;
	if ( ! isset( $cgkit_ibar_script ) || true !== $cgkit_ibar_script ) {
		return;
	}
	$options          = get_option( 'commercekit', array() );
	$low_stock_color  = isset( $options['inventory_lsb_color'] ) && ! empty( $options['inventory_lsb_color'] ) ? $options['inventory_lsb_color'] : commercekit_get_default_settings( 'inventory_lsb_color' );
	$rglr_stock_color = isset( $options['inventory_rsb_color'] ) && ! empty( $options['inventory_rsb_color'] ) ? $options['inventory_rsb_color'] : commercekit_get_default_settings( 'inventory_rsb_color' );
	$high_stock_color = isset( $options['inventory_hsb_color'] ) && ! empty( $options['inventory_hsb_color'] ) ? $options['inventory_hsb_color'] : commercekit_get_default_settings( 'inventory_hsb_color' );
	?>
<style>
.commercekit-inventory .progress-bar.low-stock-bar span { background: <?php echo esc_attr( $low_stock_color ); ?>; }
.commercekit-inventory .progress-bar.regular-stock-bar span { background: <?php echo esc_attr( $rglr_stock_color ); ?>; }
.commercekit-inventory .progress-bar.high-stock-bar span { background: <?php echo esc_attr( $high_stock_color ); ?>; }
</style>
<script>
function isInCKITViewport(element){
	var rect = element.getBoundingClientRect();
	return (
		rect.top >= 0 &&
		rect.left >= 0 &&
		rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
		rect.right <= (window.innerWidth || document.documentElement.clientWidth)
	);
}
function animateInventoryBar(){
	var bar = document.querySelector('.commercekit-inventory .progress-bar.active');
	if( bar ) {
		if( isInCKITViewport(bar) ){
			var y = setTimeout(function() {
				bar.classList.remove('full-bar');
			}, 100);
		}
	}
}
function animateInventoryHandler(entries, observer) {
	for( entry of entries ){
		if( entry.isIntersecting && entry.target.classList.contains('progress-bar') ){
			var bar = document.querySelector('.commercekit-inventory .progress-bar.active');
			if( bar )
				bar.classList.remove('full-bar');
		}
	}
}
var cgi_observer = new IntersectionObserver(animateInventoryHandler);
if( document.querySelector('.commercekit-inventory') ){
	var $cgkit_cdt = document.querySelector('#commercekit-timer');
	if( $cgkit_cdt ){
		$cgkit_cdt.classList.add('has-cg-inventory');
	}
	animateInventoryBar();
	window.onresize = animateInventoryBar;
	cgi_observer.observe(document.querySelector('.commercekit-inventory .progress-bar'));
	var vinput2 = document.querySelector('.summary form.cart:not(.cgkit-swatch-form):not(.commercekit_sticky-atc) input.variation_id');
	if( vinput2 ){
		vinput2_observer = new MutationObserver((changes) => {
			changes.forEach(change => {
				if(change.attributeName.includes('value')){
					setTimeout(function(){
						var cinput_val2 = vinput2.value;
						if( vinput_val2 != cinput_val2 && cinput_val2 != '' ){
							updateStockInventoryDisplay(cinput_val2);
						}
					}, 500);
				}
			});
		});
		vinput2_observer.observe(vinput2, {attributes : true});

		document.addEventListener('click', function(e){
			var input = e.target;
			var inputp = input.closest('.swatch');
			if( input.classList.contains('reset_variations') || input.classList.contains('swatch') || inputp ){
				var clear_var = false;
				if( input.classList.contains('reset_variations') ){
					clear_var = true;
				}
				setTimeout(function(){
					if( inputp ){
						input = inputp;
					}
					if( !input.classList.contains('selected') ){
						clear_var = true;
					}
					var cinput_val2 = vinput2.value;
					if( vinput_val2 != cinput_val2 && ( cinput_val2 != '' || clear_var ) ){
						updateStockInventoryDisplay(cinput_val2);
					}
				}, 500);
			}
		});
		setTimeout(function(){
			var cinput_val2 = vinput2.value;
			if( vinput_val2 != cinput_val2 && cinput_val2 != '' ){
				updateStockInventoryDisplay(cinput_val2);
			}
		}, 500);
	}
}
var vinput_val2 = '0';
function updateStockInventoryDisplay(cinput_val2){
	var btn_disabled = document.querySelector('.summary form.cart:not(.cgkit-swatch-form):not(.commercekit_sticky-atc) .single_add_to_cart_button.disabled');
	var display_class = '.cki-variation-'+cinput_val2;
	if( cinput_val2 == '' || cinput_val2 == '0' ){
		display_class = '.cki-variation-default';
	} else if( btn_disabled ) {
		display_class = '';
	} else {
		display_class = '.cki-variation-'+cinput_val2;
	}
	document.querySelector('.commercekit-inventory').style.display = 'none';
	var cki_vars = document.querySelectorAll('.cki-variation');
	cki_vars.forEach(function(cki_var){
		cki_var.style.display = 'none';
		var bar = cki_var.querySelector('.progress-bar');
		if( bar ){
			bar.classList.remove('active');
			bar.classList.add('full-bar');
		}
	});
	if( display_class != '' ){
		var cki_var = document.querySelector(display_class);
		if( cki_var ){
			cki_var.style.display = 'block';
			document.querySelector('.commercekit-inventory').style.display = '';
			var bar = cki_var.querySelector('.progress-bar');
			if( bar ){
				bar.classList.add('active');
			}
		}
	}
	vinput_val2 = cinput_val2;
	var bar = document.querySelector('.commercekit-inventory .progress-bar.active');
	if( bar )
		cgi_observer.observe(bar);
}
</script>
	<?php
}
add_action( 'wp_footer', 'commercekit_inventory_number_script', 0 );

/**
 * Single Product Page - Display Inventory Bar
 */
function commercekit_display_inventory_counter() {
	global $product;
	if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
		return;
	}
	$commercekit_inventory_display = false;
	$commercekit_stock_quantity    = $product->get_stock_quantity();
	$commercekit_options           = get_option( 'commercekit', array() );
	$commercekit_flags             = commercekit_feature_flags()->get_flags();
	if ( isset( $commercekit_flags['inventory_display'] ) && 1 === (int) $commercekit_flags['inventory_display'] ) {
		$commercekit_inventory_display = true;
	}
	/* translators: %s: stock counter. */
	$display_text = isset( $commercekit_options['inventory_text'] ) && ! empty( $commercekit_options['inventory_text'] ) ? commercekit_get_multilingual_string( $commercekit_options['inventory_text'] ) : commercekit_get_default_settings( 'inventory_text' );

	/* translators: %s: stock counter. */
	$display_text_31 = isset( $commercekit_options['inventory_text_31'] ) && ! empty( $commercekit_options['inventory_text_31'] ) ? commercekit_get_multilingual_string( $commercekit_options['inventory_text_31'] ) : commercekit_get_default_settings( 'inventory_text_31' );

	$display_text_100 = isset( $commercekit_options['inventory_text_100'] ) && ! empty( $commercekit_options['inventory_text_100'] ) ? commercekit_get_multilingual_string( $commercekit_options['inventory_text_100'] ) : commercekit_get_default_settings( 'inventory_text_100' );

	$shortcode = isset( $commercekit_options['widget_pos_stockmeter'] ) && 1 === (int) $commercekit_options['widget_pos_stockmeter'] ? true : false;

	if ( true === $commercekit_inventory_display ) {
		if ( $product->is_type( 'simple' ) || $product->is_type( 'variable' ) ) {
			if ( $shortcode ) {
				return commercekit_inventory_number( $display_text, $display_text_31, $display_text_100 );
			} else {
				commercekit_inventory_number( $display_text, $display_text_31, $display_text_100 );
			}
		}
	}
}

/**
 * Single Product Page - Display Inventory Bar shortcode
 */
function commercekit_display_inventory_counter_shortcode() {
	global $product, $post;
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return '';
	}
	if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
		if ( isset( $post->ID ) && $post->ID ) {
			$product = wc_get_product( $post->ID );
			if ( ! $product ) {
				return '';
			}
		}
	}

	$html = commercekit_display_inventory_counter();

	return $html;
}

/**
 * Stock meter elementor widget
 *
 * @param  string $widgets_manager widgets manager object.
 */
function commercekit_stockmeter_elementor_widget( $widgets_manager ) {
	require_once CGKIT_BASE_PATH . 'includes/elementor/class-commercekit-stockmeter-elementor.php';
	$widgets_manager->register( new Commercekit_Stockmeter_Elementor() );
}

$commercekit_options   = get_option( 'commercekit', array() );
$widget_pos_stockmeter = isset( $commercekit_options['widget_pos_stockmeter'] ) && 1 === (int) $commercekit_options['widget_pos_stockmeter'] ? true : false;
if ( $widget_pos_stockmeter ) {
	add_shortcode( 'commercekit_stockmeter', 'commercekit_display_inventory_counter_shortcode' );
	add_action( 'elementor/widgets/register', 'commercekit_stockmeter_elementor_widget' );
} else {
	add_action( 'woocommerce_single_product_summary', 'commercekit_display_inventory_counter', 40 );
}
