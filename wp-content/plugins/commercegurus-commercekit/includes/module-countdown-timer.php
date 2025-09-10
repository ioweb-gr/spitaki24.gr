<?php
/**
 *
 * Countdown Timer module
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

/**
 * Display Countdown timer template
 *
 * @param  string $title of countdown timer.
 * @param  string $timer hours, minutes and seconds.
 * @param  string $mode of countdown timer.
 * @param  string $restart of countdown timer when complete.
 * @param  string $session of countdown timer for restart.
 * @param  string $location of countdown timer, either product page or checkout page.
 * @param  string $message of countdown timer for display after complete.
 * @param  string $index of countdown timer.
 * @param  string $return return output.
 */
function commercekit_countdown_timer_template( $title, $timer, $mode, $restart, $session, $location, $message, $index, $return = false ) {
	global $cgkit_ctd_script;
	$class         = 'product';
	$days_label    = esc_html__( 'DAYS', 'commercegurus-commercekit' );
	$hours_label   = esc_html__( 'HRS', 'commercegurus-commercekit' );
	$minutes_label = esc_html__( 'MINS', 'commercegurus-commercekit' );
	$seconds_label = esc_html__( 'SECS', 'commercegurus-commercekit' );
	if ( 'product' !== $location ) {
		$class         = 'non-product ' . $location;
		$days_label    = esc_html__( 'days', 'commercegurus-commercekit' );
		$hours_label   = esc_html__( 'hours', 'commercegurus-commercekit' );
		$minutes_label = esc_html__( 'minutes', 'commercegurus-commercekit' );
		$seconds_label = esc_html__( 'seconds', 'commercegurus-commercekit' );
	}

	if ( isset( $timer['days_label'] ) && ! empty( $timer['days_label'] ) ) {
		$days_label = $timer['days_label'];
	}
	if ( isset( $timer['hours_label'] ) && ! empty( $timer['hours_label'] ) ) {
		$hours_label = $timer['hours_label'];
	}
	if ( isset( $timer['minutes_label'] ) && ! empty( $timer['minutes_label'] ) ) {
		$minutes_label = $timer['minutes_label'];
	}
	if ( isset( $timer['seconds_label'] ) && ! empty( $timer['seconds_label'] ) ) {
		$seconds_label = $timer['seconds_label'];
	}
	$options   = get_option( 'commercekit', array() );
	$shortcode = false;
	if ( 'product' === $location ) {
		$shortcode = isset( $options['widget_pos_countdown'] ) && 1 === (int) $options['widget_pos_countdown'] ? true : false;
	}
	if ( 'checkout' === $location ) {
		$shortcode = isset( $options['widget_pos_countdown2'] ) && 1 === (int) $options['widget_pos_countdown2'] ? true : false;
	}
	$html = '
<div id="commercekit-timer-wrap" ' . ( true === $shortcode ? 'class="cgkit-timer-' . $location . '-shortcode"' : '' ) . '>
<div id="commercekit-timer" class="' . esc_html( $class ) . '" data-timer="' . esc_html( $timer['total'] ) . '" data-mode="' . esc_html( $mode ) . '" data-restart="' . esc_html( $restart ) . '" data-key="' . esc_html( $session ) . '" data-location="' . esc_html( $location ) . '" data-index="' . esc_html( $index ) . '" data-hide_timer="' . ( isset( $timer['hide_timer'] ) ? esc_html( $timer['hide_timer'] ) : 0 ) . '" ' . ( true === $shortcode ? '' : 'style="visibility: hidden;"' ) . '>
	<div class="commercekit-timer-title">' . esc_html( stripslashes_deep( $title ) ) . '</div>
	<div class="commercekit-timer-blocks">
		<div class="commercekit-timer-block">
			<div class="commercekit-timer-digit" id="days">' . esc_html( $timer['days'] ) . '</div>
			<div class="commercekit-timer-label">' . esc_html( $days_label ) . '</div>
		</div>
		<div class="commercekit-timer-sep">:</div>
		<div class="commercekit-timer-block">
			<div class="commercekit-timer-digit" id="hours">' . esc_html( $timer['hours'] ) . '</div>
			<div class="commercekit-timer-label">' . esc_html( $hours_label ) . '</div>
		</div>
		<div class="commercekit-timer-sep">:</div>
		<div class="commercekit-timer-block">
			<div class="commercekit-timer-digit" id="minutes">' . esc_html( $timer['minutes'] ) . '</div>
			<div class="commercekit-timer-label">' . esc_html( $minutes_label ) . '</div>
		</div>
		<div class="commercekit-timer-sep">:</div>
		<div class="commercekit-timer-block">
			<div class="commercekit-timer-digit" id="seconds">' . esc_html( $timer['seconds'] ) . '</div>
			<div class="commercekit-timer-label">' . esc_html( $seconds_label ) . '</div>
		</div>
	</div>
</div>
<div id="commercekit-timer-message" class="' . esc_html( $class ) . '" style="display:none;">' . esc_html( stripslashes_deep( $message ) ) . '</div>
</div>';

	$cgkit_ctd_script = true;
	if ( $return ) {
		return $html;
	} else {
		echo $html; // phpcs:ignore
	}
}

/**
 * Print countdown timer script
 */
function commercekit_countdown_timer_script() {
	global $cgkit_ctd_script;
	if ( ! isset( $cgkit_ctd_script ) || true !== $cgkit_ctd_script ) {
		return;
	}
	$options   = get_option( 'commercekit', array() );
	$shortcode = isset( $options['widget_pos_countdown'] ) && 1 === (int) $options['widget_pos_countdown'] ? true : false;
	$shortcode = isset( $options['widget_pos_countdown2'] ) && 1 === (int) $options['widget_pos_countdown2'] ? true : $shortcode;
	?>

<script>
function setCKITCookie(cname, cvalue, exdays){
	var d = new Date();
	d.setTime( d.getTime() + ( exdays * 24 * 60 * 60 * 1000 ) );
	var expires = "expires=" + d.toGMTString() + "; ";
	if( ! exdays ) expires = "";
	document.cookie = cname + "=" + cvalue + "; " + expires + "path=/";
} 
function getCKITCookie(cname){
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i=0; i<ca.length; i++) {
		var c = ca[i].trim();
		if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
	}
	return "";
}
if( document.querySelector('#commercekit-timer') ){
	var cgkit_this =  document.querySelector('#commercekit-timer');
	var cgkit_timer = cgkit_this.getAttribute('data-timer');
	var cgkit_mode = cgkit_this.getAttribute('data-mode');
	var cgkit_restart = cgkit_this.getAttribute('data-restart');
	var cgkit_key = cgkit_this.getAttribute('data-key');
	var cgkit_location = cgkit_this.getAttribute('data-location');
	var cgkit_index = cgkit_this.getAttribute('data-index');
	var cgkit_hide_timer = cgkit_this.getAttribute('data-hide_timer');
	var cgkit_loaded_total = false;

	var cgkit_otimer = cgkit_timer;
	var cgkit_ntimer = getCKITCookie(cgkit_key);
	if( cgkit_ntimer != '' ) cgkit_timer = cgkit_ntimer;
	if( cgkit_key == '' ){
		var cgkit_formData = new FormData();
		cgkit_formData.append('index', cgkit_index);
		fetch( commercekit_ajs.ajax_url + '=commercekit_get_countdown_total', {
			method: 'POST',
			body: cgkit_formData,
		}).then(response => response.json()).then( json => {
			cgkit_timer = json.total;
			cgkit_loaded_total = true;
		});
	}
	var cgkit_time = new Date().getTime();
	var cgkit_count_date = cgkit_time + ( cgkit_timer * 1000 );

	var cgkit_x = setInterval(function() {
		if( cgkit_key == '' && !cgkit_loaded_total ){
			return;
		}
		var now = new Date().getTime();
		var distance = cgkit_count_date - now;

		var days = Math.floor(distance / (1000 * 60 * 60 * 24));
		var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
		var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
		var seconds = Math.floor((distance % (1000 * 60)) / 1000);

		if( days <= 0 ) days = 0;
		if( hours <= 0 ) hours = 0;
		if( minutes <= 0 ) minutes = 0;
		if( seconds <= 0 ) seconds = 0;

		if( cgkit_location != 'product' ){
			if( days <= 0 ) 
				document.querySelector('#days').parentNode.style.display = 'none';
			else
				document.querySelector('#days').parentNode.style.display = 'inline-flex';
			if( hours <= 0 ) 
				document.querySelector('#hours').parentNode.style.display = 'none';
			else
				document.querySelector('#hours').parentNode.style.display = 'inline-flex';
			if( minutes <= 0 )
				document.querySelector('#minutes').parentNode.style.display = 'none';
			else
				document.querySelector('#minutes').parentNode.style.display = 'inline-flex';
		}

		var cgkit_ctimer = days * 60 * 60 * 24;
		cgkit_ctimer = cgkit_ctimer + ( hours * 60 * 60 );
		cgkit_ctimer = cgkit_ctimer + ( minutes * 60 );
		cgkit_ctimer = cgkit_ctimer + seconds;
		if( cgkit_location != 'product' ){
			setCKITCookie(cgkit_key, cgkit_ctimer, 0);
		} else {
			setCKITCookie(cgkit_key, cgkit_ctimer, 30);
		}

		days = ("00"+days).slice(-2);
		hours = ("00"+hours).slice(-2);
		if( cgkit_location == 'product' ){
			minutes = ("00"+minutes).slice(-2);
		}
		seconds = ("00"+seconds).slice(-2);

		document.querySelector('#days').innerHTML = days;
		document.querySelector('#hours').innerHTML = hours;
		document.querySelector('#minutes').innerHTML = minutes;
		document.querySelector('#seconds').innerHTML = seconds;
		<?php if ( ! $shortcode ) { ?>
		document.querySelector('#commercekit-timer').style.visibility = 'visible';
		<?php } ?>
		if (cgkit_ctimer <= 0) {
			if( cgkit_mode == 'regular' || ( cgkit_mode != 'regular' && cgkit_restart == 'none' ) ){
				clearInterval(cgkit_x);
				if( cgkit_location != 'product' || cgkit_hide_timer == 1 ){
					document.querySelector('#commercekit-timer').style.display = 'none';
					document.querySelector('#commercekit-timer-message').style.display = 'block';
				}
			} else {
				if( cgkit_restart == 'immediate' ){
					var cgkit_ntime = new Date().getTime();
					cgkit_count_date = cgkit_ntime + ( cgkit_otimer * 1000 );
				} else {
					clearInterval(cgkit_x);
					if( cgkit_location != 'product' ){
						document.querySelector('#commercekit-timer').style.display = 'none';
						document.querySelector('#commercekit-timer-message').style.display = 'block';
						setCKITCookie(cgkit_key, cgkit_otimer, 0);
					} else {
						setCKITCookie(cgkit_key, cgkit_otimer, 30);
						if( cgkit_hide_timer == 1 ){
							document.querySelector('#commercekit-timer').style.display = 'none';
							document.querySelector('#commercekit-timer-message').style.display = 'block';
						}
					}
				}
			}
		}
	}, 1000);
}
var vinput = document.querySelector('.summary form.cart:not(.cgkit-swatch-form):not(.commercekit_sticky-atc) input.variation_id');
if( vinput ){
	vinput_observer = new MutationObserver((changes) => {
		changes.forEach(change => {
			if(change.attributeName.includes('value')){
				setTimeout(function(){
					var cinput_val = vinput.value;
					if( vinput_val != cinput_val ){
						updateCountDownTimerDisplay(cinput_val);
					}
				}, 500);
			}
		});
	});
	vinput_observer.observe(vinput, {attributes : true});

	document.addEventListener('change', function(e){
		setTimeout(function(){
			var cinput_val = vinput.value;
			if( vinput_val != cinput_val ){
				updateCountDownTimerDisplay(cinput_val);
			}
		}, 300);
	});
	document.addEventListener('click', function(e){
		var input = e.target;
		if( input.classList.contains('reset_variations') ){
			setTimeout(function(){
				var cinput_val = vinput.value;
				if( vinput_val != cinput_val ){
					updateCountDownTimerDisplay(cinput_val);
				}
			}, 300);
		}
	});
	setTimeout(function(){
		var cinput_val = vinput.value;
		if( vinput_val != cinput_val ){
			updateCountDownTimerDisplay(cinput_val);
		}
	}, 300);
}
var vinput_val = '0';
function updateCountDownTimerDisplay(cinput_val){
	var timer = document.querySelector('#commercekit-timer-wrap');
	var btn_disabled = document.querySelector('.summary form.cart:not(.cgkit-swatch-form):not(.commercekit_sticky-atc) .single_add_to_cart_button.disabled');
	if( cinput_val == '' || cinput_val == '0' ){
		timer.style.display = 'block';
	} else if( btn_disabled ) {
		timer.style.display = 'none';
	} else {
		timer.style.display = 'block';
	}
	vinput_val = cinput_val;
}
</script>
	<?php
}
add_action( 'wp_footer', 'commercekit_countdown_timer_script', 0 );

/**
 * Get timer by location
 *
 * @param  string $location of countdown timer for display.
 * @param  string $return return output.
 * @param  string $id     countdown ID.
 * @return string
 */
function commercekit_countdown_timer_by_location( $location, $return = false, $id = 0 ) {

	if ( 'product' === $location ) {
		global $product;
		if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
			return;
		}
		$product_id = $product ? $product->get_id() : 0;
		if ( $product_id ) {
			$disable_cgkit_countdown = (int) get_post_meta( $product_id, 'commercekit_disable_countdown', true );
			if ( 1 === $disable_cgkit_countdown ) {
				return;
			}
		}
		if ( $product->is_type( 'simple' ) || $product->is_type( 'bundle' ) || $product->is_type( 'grouped' ) || $product->is_type( 'composite' ) || $product->is_type( 'woosg' ) || $product->is_type( 'woosb' ) || $product->is_type( 'yith_bundle' ) ) {
			if ( ! $product->backorders_allowed() && ( ( $product->managing_stock() && 0 === (int) $product->get_stock_quantity() ) || 'outofstock' === $product->get_stock_status() ) ) {
				return;
			}
		}
		if ( $product->is_type( 'variable' ) ) {
			$outofstocks = 0;
			$variations  = commercekit_get_available_variations( $product );
			if ( is_array( $variations ) && count( $variations ) ) {
				foreach ( $variations as $variation ) {
					if ( isset( $variation['backorders_allowed'] ) && 1 === (int) $variation['backorders_allowed'] ) {
						continue;
					}
					if ( ! isset( $variation['is_in_stock'] ) || 1 !== (int) $variation['is_in_stock'] ) {
						$outofstocks++;
					}
				}
				if ( count( $variations ) === $outofstocks ) {
					return;
				}
			} else {
				return;
			}
		}

		$categories = array();
		$terms      = get_the_terms( $product->get_id(), 'product_cat' );
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
	}

	$commercekit_options = get_option( 'commercekit', array() );
	$commercekit_flags   = commercekit_feature_flags()->get_flags();
	$countdown           = isset( $commercekit_options['countdown'] ) ? $commercekit_options['countdown'] : array();
	$enable_ctd_timer    = isset( $commercekit_flags['countdown_timer'] ) && 1 === (int) $commercekit_flags['countdown_timer'] ? 1 : 0;
	if ( ! $enable_ctd_timer ) {
		return;
	}

	$title          = '';
	$timer          = array();
	$timer['total'] = 0;
	$mode           = 'regular';
	$restart        = 'none';
	$message        = '';
	$ctd_type       = 0;
	$index          = 0;

	if ( 'checkout' === $location ) {
		if ( isset( $countdown['checkout']['active'] ) && 1 === (int) $countdown['checkout']['active'] ) {
			$title            = isset( $countdown['checkout']['title'] ) ? commercekit_get_multilingual_string( $countdown['checkout']['title'] ) : '';
			$message          = isset( $countdown['checkout']['expiry_message'] ) ? commercekit_get_multilingual_string( $countdown['checkout']['expiry_message'] ) : '';
			$timer['total']   = isset( $countdown['checkout']['minutes'] ) ? ( (int) $countdown['checkout']['minutes'] ) * 60 : 0;
			$timer['total']  += isset( $countdown['checkout']['seconds'] ) ? (int) $countdown['checkout']['seconds'] : 0;
			$timer['days']    = 0;
			$timer['hours']   = 0;
			$timer['minutes'] = isset( $countdown['checkout']['minutes'] ) ? (int) $countdown['checkout']['minutes'] : 0;
			$timer['seconds'] = isset( $countdown['checkout']['seconds'] ) ? (int) $countdown['checkout']['seconds'] : 0;
		} else {
			return;
		}
	}

	if ( 'product' === $location ) {
		$product_ids = array( (int) $product->get_id() );

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

		$id = intval( $id );

		if ( isset( $countdown['product']['title'] ) && count( $countdown['product']['title'] ) > 0 ) {
			foreach ( $countdown['product']['title'] as $k => $title ) {
				if ( empty( $title ) ) {
					continue;
				}
				$kid = (int) $k + 1;
				if ( $id && $id !== $kid ) {
					continue;
				}
				if ( isset( $countdown['product']['active'][ $k ] ) && 1 === (int) $countdown['product']['active'][ $k ] ) {
					$can_display = false;
					$ctd_type    = isset( $countdown['product']['type'][ $k ] ) ? (int) $countdown['product']['type'][ $k ] : 0;
					$condition   = isset( $countdown['product']['condition'][ $k ] ) ? $countdown['product']['condition'][ $k ] : 'all';
					$pids        = isset( $countdown['product']['pids'][ $k ] ) ? explode( ',', $countdown['product']['pids'][ $k ] ) : array();
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
					}
					if ( $can_display && 2 === $ctd_type ) {
						$now_time  = time();
						$from_time = 0;
						$to_time   = 0;
						if ( isset( $countdown['product']['from_date'][ $k ] ) && ! empty( $countdown['product']['from_date'][ $k ] ) ) {
							$from_time = strtotime( $countdown['product']['from_date'][ $k ] . ' ' . $countdown['product']['from_date_h'][ $k ] . ':' . $countdown['product']['from_date_m'][ $k ] . ':00' );
						}
						if ( isset( $countdown['product']['to_date'][ $k ] ) && ! empty( $countdown['product']['to_date'][ $k ] ) ) {
							$to_time = strtotime( $countdown['product']['to_date'][ $k ] . ' ' . $countdown['product']['to_date_h'][ $k ] . ':' . $countdown['product']['to_date_m'][ $k ] . ':00' );
						}
						if ( $from_time && $to_time && $from_time < $now_time && $to_time > $now_time ) {
							$total_time     = $to_time - $now_time;
							$timer['total'] = $total_time;

							$timer['days'] = floor( $total_time / DAY_IN_SECONDS );
							$total_time    = ( $total_time % DAY_IN_SECONDS );

							$timer['hours'] = floor( $total_time / HOUR_IN_SECONDS );
							$total_time     = ( $total_time % HOUR_IN_SECONDS );

							$timer['minutes'] = floor( $total_time / MINUTE_IN_SECONDS );
							$total_time       = ( $total_time % MINUTE_IN_SECONDS );

							$timer['seconds'] = floor( $total_time );
						} else {
							continue;
						}
					}
					if ( $can_display ) {
						$title   = commercekit_get_multilingual_string( $title );
						$restart = 'none';
						$index   = $k;
						$mode    = 1 === $ctd_type ? 'evergreen' : 'regular';
						if ( 'evergreen' === $mode ) {
							$restart = 'nextvisit';
						}
						if ( 2 !== $ctd_type ) {
							$timer['total']   = ( (int) $countdown['product']['days'][ $k ] ) * 60 * 60 * 24;
							$timer['total']  += ( (int) $countdown['product']['hours'][ $k ] ) * 60 * 60;
							$timer['total']  += ( (int) $countdown['product']['minutes'][ $k ] ) * 60;
							$timer['total']  += (int) $countdown['product']['seconds'][ $k ];
							$timer['days']    = (int) $countdown['product']['days'][ $k ];
							$timer['hours']   = (int) $countdown['product']['hours'][ $k ];
							$timer['minutes'] = (int) $countdown['product']['minutes'][ $k ];
							$timer['seconds'] = (int) $countdown['product']['seconds'][ $k ];
						}

						$timer['days_label']    = commercekit_get_multilingual_string( $countdown['product']['days_label'][ $k ] );
						$timer['hours_label']   = commercekit_get_multilingual_string( $countdown['product']['hours_label'][ $k ] );
						$timer['minutes_label'] = commercekit_get_multilingual_string( $countdown['product']['minutes_label'][ $k ] );
						$timer['seconds_label'] = commercekit_get_multilingual_string( $countdown['product']['seconds_label'][ $k ] );

						$timer['hide_timer'] = 0;
						$message             = '';
						if ( isset( $countdown['product']['hide_timer'][ $k ] ) && 1 === (int) $countdown['product']['hide_timer'][ $k ] ) {
							$timer['hide_timer'] = 1;
							$message             = isset( $countdown['product']['custom_msg'][ $k ] ) && ! empty( $countdown['product']['custom_msg'][ $k ] ) ? commercekit_get_multilingual_string( $countdown['product']['custom_msg'][ $k ] ) : '';
						}

						break;
					}
				}
			}
		}
	}

	if ( $timer['total'] ) {
		$session = 'ckit_' . md5( $timer['total'] . '-' . $mode );
		if ( 'product' === $location ) {
			if ( 2 === $ctd_type ) {
				$session = '';
			}
			if ( $product->is_type( 'simple' ) || $product->is_type( 'variable' ) || $product->is_type( 'bundle' ) || $product->is_type( 'grouped' ) || $product->is_type( 'composite' ) || $product->is_type( 'woosg' ) || $product->is_type( 'woosb' ) || $product->is_type( 'yith_bundle' ) ) {
				if ( $return ) {
					return commercekit_countdown_timer_template( $title, $timer, $mode, $restart, $session, $location, $message, $index, $return );
				} else {
					commercekit_countdown_timer_template( $title, $timer, $mode, $restart, $session, $location, $message, $index );
				}
			}
		} else {
			if ( $return ) {
				return commercekit_countdown_timer_template( $title, $timer, $mode, $restart, $session, $location, $message, $index, $return );
			} else {
				commercekit_countdown_timer_template( $title, $timer, $mode, $restart, $session, $location, $message, $index );
			}
		}
	}
}

/**
 * Single Product Page - Display Countdown timer
 */
function commercekit_product_countdown_timer() {
	commercekit_countdown_timer_by_location( 'product' );
}

/**
 * Single Product Page - Display Countdown timer shortcode
 *
 * @param array $atts Shortcode attributes.
 */
function commercekit_product_countdown_timer_shortcode( $atts ) {
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

	$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'commercekit_product_countdown' );
	$id   = isset( $atts['id'] ) ? intval( $atts['id'] ) : 0;

	$html = commercekit_countdown_timer_by_location( 'product', true, $id );

	return $html;
}

/**
 * Product Countdown elementor widget
 *
 * @param  string $widgets_manager widgets manager object.
 */
function commercekit_product_countdown_elementor_widget( $widgets_manager ) {
	require_once CGKIT_BASE_PATH . 'includes/elementor/class-commercekit-countdown-elementor.php';
	$widgets_manager->register( new Commercekit_Countdown_Elementor() );
}

$commercekit_options  = get_option( 'commercekit', array() );
$widget_pos_countdown = isset( $commercekit_options['widget_pos_countdown'] ) && 1 === (int) $commercekit_options['widget_pos_countdown'] ? true : false;
if ( $widget_pos_countdown ) {
	add_shortcode( 'commercekit_product_countdown', 'commercekit_product_countdown_timer_shortcode' );
	add_action( 'elementor/widgets/register', 'commercekit_product_countdown_elementor_widget' );
} else {
	add_action( 'woocommerce_single_product_summary', 'commercekit_product_countdown_timer', 39 );
}

/**
 * Checkout Page - Display Countdown timer
 */
function commercekit_checkout_countdown_timer() {
	commercekit_countdown_timer_by_location( 'checkout' );
}

/**
 * Checkout Page - Display Countdown timer shortcode
 */
function commercekit_checkout_countdown_timer_shortcode() {
	$html = commercekit_countdown_timer_by_location( 'checkout', true );

	return $html;
}

/**
 * Checkout Countdown elementor widget
 *
 * @param  string $widgets_manager widgets manager object.
 */
function commercekit_checkout_countdown_elementor_widget( $widgets_manager ) {
	require_once CGKIT_BASE_PATH . 'includes/elementor/class-commercekit-countdown2-elementor.php';
	$widgets_manager->register( new Commercekit_Countdown2_Elementor() );
}

$widget_pos_countdown2 = isset( $commercekit_options['widget_pos_countdown2'] ) && 1 === (int) $commercekit_options['widget_pos_countdown2'] ? true : false;
if ( $widget_pos_countdown2 ) {
	add_shortcode( 'commercekit_checkout_countdown', 'commercekit_checkout_countdown_timer_shortcode' );
	add_action( 'elementor/widgets/register', 'commercekit_checkout_countdown_elementor_widget' );
} else {
	add_action( 'woocommerce_before_checkout_form', 'commercekit_checkout_countdown_timer', 1 );
}

/**
 * Reset checkout countdown timer
 */
function commercekit_reset_checkout_countdown_timer() {
	if ( WC()->cart->is_empty() ) {
		$options   = get_option( 'commercekit', array() );
		$countdown = isset( $options['countdown'] ) ? $options['countdown'] : array();
		if ( isset( $countdown['checkout']['active'] ) && 1 === (int) $countdown['checkout']['active'] ) {
			$total  = 0;
			$mode   = 'regular';
			$total  = isset( $countdown['checkout']['minutes'] ) ? ( (int) $countdown['checkout']['minutes'] ) * 60 : 0;
			$total += isset( $countdown['checkout']['seconds'] ) ? (int) $countdown['checkout']['seconds'] : 0;
			if ( $total ) {
				$session = 'ckit_' . md5( $total . '-' . $mode );
				setcookie( $session, $total, time() - ( 24 * 3600 ), '/' );
			}
		}
	}
}

add_action( 'woocommerce_cart_item_removed', 'commercekit_reset_checkout_countdown_timer', 10, 0 );

/**
 * Get countdown total
 */
function commercekit_get_countdown_total() {
	$ajax      = array();
	$options   = get_option( 'commercekit', array() );
	$countdown = isset( $options['countdown'] ) ? $options['countdown'] : array();

	$ajax['status'] = 0;
	$ajax['total']  = 0;

	$index     = isset( $_POST['index'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['index'] ) ) : 0; // phpcs:ignore
	$now_time  = time();
	$from_time = 0;
	$to_time   = 0;
	if ( isset( $countdown['product']['from_date'][ $index ] ) && ! empty( $countdown['product']['from_date'][ $index ] ) ) {
		$from_time = strtotime( $countdown['product']['from_date'][ $index ] . ' ' . $countdown['product']['from_date_h'][ $index ] . ':' . $countdown['product']['from_date_m'][ $index ] . ':00' );
	}
	if ( isset( $countdown['product']['to_date'][ $index ] ) && ! empty( $countdown['product']['to_date'][ $index ] ) ) {
		$to_time = strtotime( $countdown['product']['to_date'][ $index ] . ' ' . $countdown['product']['to_date_h'][ $index ] . ':' . $countdown['product']['to_date_m'][ $index ] . ':00' );
	}
	if ( $from_time && $to_time && $from_time < $now_time && $to_time > $now_time ) {
		$ajax['total']  = $to_time - $now_time;
		$ajax['status'] = 1;
	}

	wp_send_json( $ajax );
}
add_action( 'wp_ajax_commercekit_get_countdown_total', 'commercekit_get_countdown_total', 10, 1 );
add_action( 'wp_ajax_nopriv_commercekit_get_countdown_total', 'commercekit_get_countdown_total' );
