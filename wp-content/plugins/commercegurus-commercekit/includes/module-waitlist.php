<?php
/**
 *
 * Waitlist module
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

/**
 * Ajax save waitlist
 */
function commercekit_ajax_save_waitlist() {
	global $wpdb;
	$commercekit_options = get_option( 'commercekit', array() );

	$ajax            = array();
	$ajax['status']  = 0;
	$ajax['message'] = esc_html__( 'Error on submitting for waiting list.', 'commercegurus-commercekit' );

	$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
	if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_nonce' ) ) {
		wp_send_json( $ajax );
	}

	$table  = $wpdb->prefix . 'commercekit_waitlist';
	$email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$pid    = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0;
	$data   = array(
		'email'      => $email,
		'product_id' => $pid,
		'created'    => time(),
		'updated'    => time(),
	);
	$format = array( '%s', '%d', '%d', '%d' );
	if ( $email && $pid ) {
		$found = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'commercekit_waitlist WHERE product_id = %d AND email = %s AND mail_sent = %d', $pid, $email, 0 ) ); // db call ok; no-cache ok.
		if ( ! $found ) {
			$wpdb->insert( $table, $data, $format ); // db call ok; no-cache ok.
		}
		$ajax['status']  = 1;
		$ajax['message'] = isset( $commercekit_options['wtl_success_text'] ) && ! empty( $commercekit_options['wtl_success_text'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_success_text'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_success_text' ) );

		$product = wc_get_product( $pid );
		if ( $product ) {
			$finds   = array( '{site_name}', '{site_url}', '{product_title}', '{product_sku}', '{product_link}', '{customer_email}' );
			$replace = array( get_option( 'blogname' ), home_url( '/' ), $product->get_title(), $product->get_sku(), $product->get_permalink(), $email );

			commercekit_remove_wc_email_name_filters();

			$enabled_admin_mail = ( ! isset( $commercekit_options['waitlist_admin_mail'] ) || 1 === (int) $commercekit_options['waitlist_admin_mail'] ) ? true : false;
			if ( $enabled_admin_mail ) {
				$to_mail       = isset( $commercekit_options['wtl_recipient'] ) && ! empty( $commercekit_options['wtl_recipient'] ) ? stripslashes_deep( $commercekit_options['wtl_recipient'] ) : commercekit_get_default_settings( 'wtl_recipient' );
				$from_mail     = isset( $commercekit_options['wtl_from_email'] ) && ! empty( $commercekit_options['wtl_from_email'] ) && isset( $commercekit_options['wtl_force_email_name'] ) && 1 === (int) $commercekit_options['wtl_force_email_name'] ? stripslashes_deep( $commercekit_options['wtl_from_email'] ) : commercekit_get_default_settings( 'wtl_from_email' );
				$reply_to_mail = isset( $commercekit_options['wtl_reply_to'] ) && 1 === (int) $commercekit_options['wtl_reply_to'] ? $from_mail : $email;
				$email_headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $from_mail, 'Reply-To: ' . $reply_to_mail );
				$email_subject = isset( $commercekit_options['wtl_admin_subject'] ) && ! empty( $commercekit_options['wtl_admin_subject'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_admin_subject'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_admin_subject' ) );
				$email_body    = isset( $commercekit_options['wtl_admin_content'] ) && ! empty( $commercekit_options['wtl_admin_content'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_admin_content'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_admin_content' ) );
				$email_subject = str_replace( $finds, $replace, $email_subject );
				$email_body    = str_replace( $finds, $replace, $email_body );
				$email_body    = html_entity_decode( $email_body );
				$email_body    = str_replace( "\r\n", '<br />', $email_body );

				$success = wp_mail( $to_mail, $email_subject, $email_body, $email_headers );
			}

			$enabled_user_mail = ( ! isset( $commercekit_options['waitlist_user_mail'] ) || 1 === (int) $commercekit_options['waitlist_user_mail'] ) ? true : false;
			if ( $enabled_user_mail ) {
				$to_mail       = $email;
				$email         = get_option( 'admin_email' );
				$from_mail     = isset( $commercekit_options['wtl_from_email'] ) && ! empty( $commercekit_options['wtl_from_email'] ) && isset( $commercekit_options['wtl_force_email_name'] ) && 1 === (int) $commercekit_options['wtl_force_email_name'] ? stripslashes_deep( $commercekit_options['wtl_from_email'] ) : commercekit_get_default_settings( 'wtl_from_email' );
				$email_headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $from_mail, 'Reply-To: ' . $from_mail );
				$email_subject = isset( $commercekit_options['wtl_user_subject'] ) && ! empty( $commercekit_options['wtl_user_subject'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_user_subject'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_user_subject' ) );
				$email_body    = isset( $commercekit_options['wtl_user_content'] ) && ! empty( $commercekit_options['wtl_user_content'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_user_content'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_user_content' ) );
				$email_subject = str_replace( $finds, $replace, $email_subject );
				$email_body    = str_replace( $finds, $replace, $email_body );
				$email_body    = html_entity_decode( $email_body );
				$email_body    = str_replace( "\r\n", '<br />', $email_body );

				$success = wp_mail( $to_mail, $email_subject, $email_body, $email_headers );
			}
		}
	}

	wp_send_json( $ajax );
}

add_action( 'wp_ajax_commercekit_save_waitlist', 'commercekit_ajax_save_waitlist' );
add_action( 'wp_ajax_nopriv_commercekit_save_waitlist', 'commercekit_ajax_save_waitlist' );

/**
 * Waitlist form
 */
function commercekit_waitlist_form() {
	global $post;
	$commercekit_options = get_option( 'commercekit', array() );
	$commercekit_flags   = commercekit_feature_flags()->get_flags();
	$enable_waitlist     = isset( $commercekit_flags['waitlist'] ) && 1 === (int) $commercekit_flags['waitlist'] ? 1 : 0;
	$esp_self_hosted     = ( isset( $commercekit_options['waitlist_esp'] ) && 1 === (int) $commercekit_options['waitlist_esp'] ) || ! isset( $commercekit_options['waitlist_esp'] ) ? true : false;
	if ( ! $enable_waitlist ) {
		return;
	}
	if ( 'product' === get_post_type( $post->ID ) && is_product() ) {
		$disable_cgkit_waitlist = (int) get_post_meta( $post->ID, 'commercekit_disable_waitlist', true );
		if ( $disable_cgkit_waitlist ) {
			return;
		}
		$_product = wc_get_product( $post->ID );
		if ( ! $_product ) {
			return;
		}
		if ( $_product->is_type( 'composite' ) || $_product->is_type( 'grouped' ) ) {
			return;
		}
		if ( ! $esp_self_hosted ) {
			$klaviyo_options = commercekit_waitlist_get_klaviyo_options();
			if ( isset( $klaviyo_options['force_display'] ) && 1 === (int) $klaviyo_options['force_display'] ) {
				return;
			}
		}
		add_filter( 'woocommerce_get_stock_html', 'commercekit_waitlist_output_form', 30, 2 );
	}
}
add_action( 'woocommerce_before_single_product', 'commercekit_waitlist_form' );

/**
 * Waitlist ajax form
 *
 * @param string $html of output.
 * @param object $product of output.
 */
function commercekit_waitlist_ajax_form( $html, $product ) {
	global $wp_query, $cgkit_var_post;
	$action = $wp_query->get( 'wc-ajax' );
	if ( 'get_variation' !== $action ) {
		return $html;
	}
	$commercekit_options = get_option( 'commercekit', array() );
	$commercekit_flags   = commercekit_feature_flags()->get_flags();
	$enable_waitlist     = isset( $commercekit_flags['waitlist'] ) && 1 === (int) $commercekit_flags['waitlist'] ? 1 : 0;
	$esp_self_hosted     = ( isset( $commercekit_options['waitlist_esp'] ) && 1 === (int) $commercekit_options['waitlist_esp'] ) || ! isset( $commercekit_options['waitlist_esp'] ) ? true : false;
	if ( ! $enable_waitlist ) {
		return $html;
	}
	if ( ! $product ) {
		return $html;
	}
	$cgkit_var_post = $product;
	if ( ! $esp_self_hosted ) {
		$klaviyo_options = commercekit_waitlist_get_klaviyo_options();
		if ( isset( $klaviyo_options['force_display'] ) && 1 === (int) $klaviyo_options['force_display'] ) {
			return $html;
		}
	}
	$disable_cgkit_waitlist = (int) get_post_meta( $product->get_id(), 'commercekit_disable_waitlist', true );
	if ( $disable_cgkit_waitlist ) {
		return $html;
	}
	if ( $product->is_type( 'variation' ) ) {
		return commercekit_waitlist_output_form( $html, $product );
	}
}
add_filter( 'woocommerce_get_stock_html', 'commercekit_waitlist_ajax_form', 30, 2 );

/**
 * Waitlist output form
 *
 * @param string $html of output.
 * @param object $product of output.
 */
function commercekit_waitlist_output_form( $html, $product ) {
	global $can_show_waitlist_form, $woocommerce_loop, $cgkit_wtls_clone;

	if ( isset( $woocommerce_loop['name'] ) && ! empty( $woocommerce_loop['name'] ) ) {
		return $html;
	}

	$commercekit_options = get_option( 'commercekit', array() );

	$intro   = isset( $commercekit_options['wtl_intro'] ) && ! empty( $commercekit_options['wtl_intro'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_intro'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_intro' ) );
	$pholder = isset( $commercekit_options['wtl_email_text'] ) && ! empty( $commercekit_options['wtl_email_text'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_email_text'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_email_text' ) );
	$blabel  = isset( $commercekit_options['wtl_button_text'] ) && ! empty( $commercekit_options['wtl_button_text'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_button_text'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_button_text' ) );
	$alabel  = isset( $commercekit_options['wtl_consent_text'] ) && ! empty( $commercekit_options['wtl_consent_text'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_consent_text'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_consent_text' ) );
	$rmlabel = isset( $commercekit_options['wtl_readmore_text'] ) && ! empty( $commercekit_options['wtl_readmore_text'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_readmore_text'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_readmore_text' ) );

	$is_outofstock = false;
	$product_id    = $product->get_id();

	$disable_cgkit_waitlist = (int) get_post_meta( $product_id, 'commercekit_disable_waitlist', true );
	if ( $disable_cgkit_waitlist ) {
		return $html;
	}

	if ( $product->is_type( 'bundle' ) ) {
		if ( method_exists( $product, 'get_bundled_items_stock_status' ) && 'outofstock' === $product->get_bundled_items_stock_status() ) {
			$is_outofstock = true;
		}
	}

	if ( ( $product->managing_stock() && 0 === (int) $product->get_stock_quantity() && 'no' === $product->get_backorders() ) || 'outofstock' === $product->get_stock_status() || $is_outofstock ) {
		$can_show_waitlist_form = true;

		$ckwtl_button_css = 'ckwtl-button2';
		$klaviyo_options  = commercekit_waitlist_get_klaviyo_options();
		if ( isset( $klaviyo_options['enable_klaviyo'] ) && 1 === (int) $klaviyo_options['enable_klaviyo'] ) {
			if ( ! isset( $klaviyo_options['force_display'] ) || 0 === (int) $klaviyo_options['force_display'] ) {
				$ckwtl_button_css = 'ckgit-klaviyo-button';
				$main_call_txt    = esc_html__( 'Can\'t find your size?', 'commercegurus-commercekit' );
				$main_call_yes    = isset( $klaviyo_options['main_call'] ) && 1 === (int) $klaviyo_options['main_call'] ? true : false;
				$main_call_txt    = true === $main_call_yes && isset( $klaviyo_options['main_call_txt'] ) && ! empty( $klaviyo_options['main_call_txt'] ) ? commercekit_get_multilingual_string( $klaviyo_options['main_call_txt'] ) : $main_call_txt;
				$rmlabel          = $main_call_txt;
			}
		}

		$user_email = '';
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_email   = $current_user->user_email;
		}

		$product_name     = $product->get_name();
		$product_image_id = $product->get_image_id();
		$product_image    = '';
		$product_price    = $product->get_price_html();
		$product_rating   = '';
		if ( wc_review_ratings_enabled() ) {
			if ( $product->is_type( 'variation' ) ) {
				$parent_product = wc_get_product( $product->get_parent_id() );
				if ( $parent_product && $parent_product->get_review_count() ) {
					$product_rating = wc_get_rating_html( $parent_product->get_average_rating() );
				}
			} elseif ( $product->get_review_count() ) {
				$product_rating = wc_get_rating_html( $product->get_average_rating() );
			}
		}

		if ( $product_image_id ) {
			$image_size    = apply_filters( 'commercegurus_woocommerce_waitlist_thumbnail_size', 'woocommerce_gallery_thumbnail' );
			$product_image = wp_get_attachment_image(
				$product_image_id,
				$image_size,
				false,
				apply_filters(
					'woocommerce_gallery_image_html_attachment_image_params',
					array(
						'title'        => _wp_specialchars( get_post_field( 'post_title', $product_image_id ), ENT_QUOTES, 'UTF-8', true ),
						'data-caption' => _wp_specialchars( get_post_field( 'post_excerpt', $product_image_id ), ENT_QUOTES, 'UTF-8', true ),
						'class'        => 'cgkit-waitlist-image',
					),
					$product_image_id,
					$image_size,
					true
				)
			);
		}
		if ( $product->is_type( 'variation' ) ) {
			$attributes = wc_get_formatted_variation( $product, true, false, true );
			if ( ! empty( $attributes ) ) {
				$product_name .= ' - ' . $attributes;
			}
		}

		$whtml = '
<input type="button" id="ckwtl-button2" class="' . $ckwtl_button_css . '" name="ckwtl_button2" value="' . $rmlabel . '" />
<div id="commercekit-waitlist-popup" style="display: none;">
	<div id="commercekit-waitlist-wrap">
		<div id="commercekit-waitlist-close"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
</svg></div>
		<div class="commercekit-waitlist-head">
			<div class="cgkit-product-image">' . $product_image . '</div>
			<div class="cgkit-product-info">
				<div class="cgkit-product-name">' . $product_name . '</div>
				<div class="cgkit-product-rating">' . $product_rating . '</div>
				<div class="cgkit-product-price">' . $product_price . '</div>
			</div>
		</div>
		<div class="commercekit-waitlist">
			<p>' . $intro . '</p>
			<input type="email" id="ckwtl-email" name="ckwtl_email" placeholder="' . $pholder . '" value="' . $user_email . '" />
			<label><input type="checkbox" id="ckwtl-consent" name="ckwtl_consent" value="1" />&nbsp;&nbsp;' . $alabel . '</label>
			<input type="button" id="ckwtl-button" name="ckwtl_button" value="' . $blabel . '" disabled="disabled" />
			<input type="hidden" id="ckwtl-pid" name="ckwtl_pid" value="' . $product_id . '" />
		</div>
	</div>
</div>';

		$cgkit_wtls_clone = $whtml;

		$html .= $whtml;
	}

	return $html;
}

/**
 * Waitlist output form script
 */
function commercekit_waitlist_output_form_script() {
	global $can_show_waitlist_form, $product, $cgkit_wtls_clone;
	$commercekit_options = get_option( 'commercekit', array() );
	$attribute_names     = array();
	$wtl_readmore_text   = isset( $commercekit_options['wtl_readmore_text'] ) && ! empty( $commercekit_options['wtl_readmore_text'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_readmore_text'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_readmore_text' ) );
	$esp_self_hosted     = ( isset( $commercekit_options['waitlist_esp'] ) && 1 === (int) $commercekit_options['waitlist_esp'] ) || ! isset( $commercekit_options['waitlist_esp'] ) ? true : false;
	if ( $product && method_exists( $product, 'get_id' ) ) {
		$disable_cgkit_waitlist = (int) get_post_meta( $product->get_id(), 'commercekit_disable_waitlist', true );
		if ( $disable_cgkit_waitlist ) {
			return;
		}
	}

	$outofstock_variation = false;
	if ( $product && method_exists( $product, 'is_type' ) && $product->is_type( 'variable' ) ) {
		$variations = commercekit_get_available_variations( $product, false, false );
		if ( is_array( $variations ) && count( $variations ) ) {
			foreach ( $variations as $variation ) {
				if ( ! isset( $variation['is_in_stock'] ) || 1 !== (int) $variation['is_in_stock'] ) {
					$outofstock_variation = true;
					break;
				}
			}
		}
	}

	if ( ( isset( $can_show_waitlist_form ) && true === $can_show_waitlist_form ) || $outofstock_variation ) {
		if ( $product && method_exists( $product, 'is_type' ) && $product->is_type( 'variable' ) ) {
			foreach ( $product->get_attributes() as $attribute ) {
				if ( $attribute->get_variation() ) {
					$attribute_names[] = sanitize_title( $attribute->get_name() );
				}
			}
		}

		$js_function     = 'popupCKITWaitlist();';
		$klaviyo_options = commercekit_waitlist_get_klaviyo_options();
		if ( isset( $klaviyo_options['enable_klaviyo'] ) && 1 === (int) $klaviyo_options['enable_klaviyo'] ) {
			if ( ! isset( $klaviyo_options['force_display'] ) || 0 === (int) $klaviyo_options['force_display'] ) {
				$main_call_txt = esc_html__( 'Can\'t find your size?', 'commercegurus-commercekit' );
				$main_call_yes = isset( $klaviyo_options['main_call'] ) && 1 === (int) $klaviyo_options['main_call'] ? true : false;
				$main_call_txt = true === $main_call_yes && isset( $klaviyo_options['main_call_txt'] ) && ! empty( $klaviyo_options['main_call_txt'] ) ? commercekit_get_multilingual_string( $klaviyo_options['main_call_txt'] ) : $main_call_txt;

				$wtl_readmore_text = $main_call_txt;
				$js_function       = 'showKlaviyoPopupWaitlist();';
			}
		}
		?>
<style>
.commercekit-waitlist { margin: 30px; padding: 25px; background-color: #fff; border: 1px solid #eee; box-shadow: 0 3px 15px -5px rgba(0, 0, 0, 0.08); }
.commercekit-waitlist p { font-weight: 600; margin-bottom: 10px; width: 100%; font-size: 16px; }
.commercekit-waitlist p.error { color: #F00; margin-bottom: 0; line-height: 1.4; font-weight: normal;}
.commercekit-waitlist p.success { margin-bottom: 0; line-height: 1.4; font-weight: normal;}
.commercekit-waitlist #ckwtl-email { width: 100%; background: #fff; margin-bottom: 10px; }
.commercekit-waitlist #ckwtl-email.error { border: 1px solid #F00; }
.commercekit-waitlist .error { border: none; background: transparent; }
.commercekit-waitlist label { width: 100%; margin-bottom: 10px; font-size: 14px; display: block; }
.commercekit-waitlist label.error { color: #F00; padding: 0; }
.commercekit-waitlist label input { position: relative; top: 2px; }
.commercekit-waitlist #ckwtl-button { width: 100%; margin-top: 5px; text-align: center; border-radius: 3px; transition: 0.2s all; }
.commercekit-waitlist #ckwtl-button { width: 100%; text-align: center; }
#ckwtl-button2 { min-width: 200px; width: 100%; margin: 15px 0; padding: 12px 0; text-decoration: none; }
#commercekit-waitlist-popup { position: fixed; width: 100%; height: 100%; max-width: 100%; max-height: 100%; background-color: rgba(0,0,0,0.4); z-index: 9999999; top: 0; left: 0; bottom: 0; right: 0; align-items: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),0 10px 10px -5px rgba(0, 0, 0, 0.04); display: none; }
#commercekit-waitlist-popup .commercekit-waitlist { margin: 0px; }
#commercekit-waitlist-wrap { background-color: #fff; color: #000; overflow: hidden; position: relative; margin: 75px auto; width: 500px; height: auto; max-width: 100%; border-radius: 6px;}
#commercekit-waitlist-wrap .commercekit-waitlist p { font-size: 20px;}
#commercekit-waitlist-close { position: absolute; width: 25px; height: 25px; cursor: pointer; right: 10px; top: 10px; }
#commercekit-waitlist-close svg { width: 22px; height: 22px; }
body.commercekit-stop-scroll { margin: 0; height: 100%; overflow: hidden; }
form.variations_form #ckwtl-button2 { display: none; }
form.variations_form #ckwtl-button3, button.sticky-ckwtl-button3 { display: none; position: relative; background: #43454b; border-color: #43454b; color: #fff; font-size: 16px; font-weight: 600; letter-spacing: 0px; text-transform: none; float: left; width: calc(100% - 95px); height: 52px; margin-left: 40px; padding-top: 0; padding-bottom: 0; border-radius: 4px; outline: 0; line-height: 52px; text-align: center; transition: all .2s;}
form.variations_form .variations label { width: 100%; }
form.variations_form label .ckwtl-os-label { display: none; position: relative; cursor: pointer; font-weight: normal; margin: 2px 0 10px 0;}
form.variations_form label .ckwtl-os-label-text { font-size: 12px;text-decoration: underline; text-transform: none; letter-spacing: 0px; }
form.variations_form label .ckwtl-os-label-text:after { display: none !important; }
form.variations_form label .ckwtl-os-label-tip { display: none; position: absolute; width: 250px; background: white; padding: 10px; left: 0px; bottom: 25px; border: 1px solid #ccc; text-transform: none; font-size: 12px; letter-spacing: 0; line-height: 1.38; transition: all 1s; z-index: 1; box-shadow: 0 5px 5px -5px rgb(0 0 0 / 10%), 0 5px 10px -5px rgb(0 0 0 / 4%);}
form.variations_form label .ckwtl-os-label:hover .ckwtl-os-label-tip { display: block;}
button.sticky-ckwtl-button3 { width: auto; height: auto; line-height: unset; padding: 0.6180469716em 1.41575em; }
form.commercekit_sticky-atc button.sticky-ckwtl-button3 { width: calc(100% - 95px); height: 52px; padding-top: 0; padding-bottom: 0; line-height: 52px; }
.cgkit-sticky-atc-elm-wrap form.commercekit_sticky-atc button.sticky-ckwtl-button3 { width: auto; }
.elementor-add-to-cart form.variations_form #ckwtl-button3, .elementor-add-to-cart button.sticky-ckwtl-button3 { width: auto; height: auto; margin-left: 10px; padding-left: 15px; padding-right: 15px; }
.commercekit-waitlist-head { display: flex; padding: 25px; background: #F8F8F8; align-items: center; }
.commercekit-waitlist-head .cgkit-product-image { width: 100px; min-width: 100px; }
.commercekit-waitlist-head .cgkit-product-image img { margin: 0 auto; max-height: 100px; width: auto; }
.commercekit-waitlist-head .cgkit-product-info { padding-left: 10px; }
.commercekit-waitlist-head .cgkit-product-name { margin-bottom: 5px; font-size: 13px; font-weight: 600; }
.commercekit-waitlist-head .cgkit-product-rating { margin-bottom: 3px; margin-top: -2px; font-size: 13px; }
.commercekit-waitlist-head .cgkit-product-price { margin-bottom: 5px; font-size: 13px; }
#commercekit-waitlist-popup.commercekit-waitlist-popup--active { display: flex; }
@media (min-width: 993px) {
	body:has(#commercekit-waitlist-popup-wrap) .header-4-container { z-index: 2; }
}
/* RTL */
.rtl #commercekit-waitlist-close {left: 5px;right: auto;}
</style>
<?php echo '<div id="cgkit_wtls_clone">' . $cgkit_wtls_clone . '</div>'; // phpcs:ignore ?>
<script>
function validateCKITWaitlistForm(){
	var email = document.querySelector('#ckwtl-email');
	var consent = document.querySelector('#ckwtl-consent');
	var button = document.querySelector('#ckwtl-button');
	var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	var error = false;
	if( !regex.test(email.value) ){
		email.classList.add('error');
		error = true;
	} else {
		email.classList.remove('error');
	}
	if( !consent.checked ){
		consent.parentNode.classList.add('error');
		error = true;
	} else {
		consent.parentNode.classList.remove('error');
	}
	if( !error ){
		button.removeAttribute('disabled');
	} else {
		button.setAttribute('disabled', 'disabled');
	}
}
function submitCKITWaitlist(){
	var pid = document.querySelector('#ckwtl-pid').value;
	var email = document.querySelector('#ckwtl-email').value;
	var button = document.querySelector('#ckwtl-button');
	var container = document.querySelector('.commercekit-waitlist');
	var ajax_nonce = '';
	if( commercekit_ajs.ajax_nonce != 1 ){
		return true;
	} else {
		var nonce_input = document.querySelector( '#commercekit_nonce' );
		if ( nonce_input ) {
			ajax_nonce = nonce_input.value;
		}
	}
	button.setAttribute('disabled', 'disabled');
	var formData = new FormData();
	formData.append('product_id', pid);
	formData.append('email', email);
	formData.append('commercekit_nonce', ajax_nonce);
	fetch( commercekit_ajs.ajax_url + '=commercekit_save_waitlist', {
		method: 'POST',
		body: formData,
	}).then(response => response.json()).then( json => {
		if( json.status == 1 ){
			container.innerHTML = '<p class="success">'+json.message+'</p>';
		} else {
			container.innerHTML = '<p class="error">'+json.message+'</p>';
		}
	});
}
function popupCKITWaitlist(){
	removeDuplicateWaitlistForm();
	var popup = document.querySelector('#commercekit-waitlist-popup');
	if( popup ){
		popup.style.display = '';
		popup.classList.add('commercekit-waitlist-popup--active');
		document.querySelector('body').classList.add('commercekit-stop-scroll');
		document.querySelector('body').classList.remove('sticky-atc-open');
	}
}
function closeCKITWaitlistPopup(){
	var popup = document.querySelector('#commercekit-waitlist-popup');
	if( popup ){
		popup.classList.remove('commercekit-waitlist-popup--active');
		document.querySelector('body').classList.remove('commercekit-stop-scroll');
	}
}
var wtl_attribute_names = <?php echo wp_json_encode( array_unique( $attribute_names ) ); ?>;
function preparePopupCKITWaitlist(input){
	var form = input.closest('form.variations_form');
	var divsum = null;
	if( form ){
		if( form.classList.contains('cgkit-swatch-form') ){
			return true;
		}
		if( form.classList.contains('commercekit_sticky-atc') ){
			removeDuplicateWaitlistForm();
			return true;
		}
		var force_display = document.querySelector('#ckgit-klaviyo');
		if( force_display ){
			return true;
		}
		var btn3 = form.querySelector('#ckwtl-button3');
		var cbtn = form.querySelector('.single_add_to_cart_button');
		if( !btn3 && cbtn ){
			var btn3 = document.createElement('button');
			btn3.setAttribute('type', 'button');
			btn3.setAttribute('name', 'ckwtl-button3');
			btn3.setAttribute('id', 'ckwtl-button3');
			btn3.setAttribute('onclick', '<?php echo esc_attr( $js_function ); ?>');
			btn3.innerHTML = '<?php echo esc_attr( $wtl_readmore_text ); ?>';
			cbtn.parentNode.insertBefore(btn3, cbtn);
			prepareStickyATCWaitlist();
		}
		var ostock = form.querySelector('.stock.out-of-stock');
		var display_label = false;
		if( btn3 && cbtn && ostock && input.value != '' ){
			cbtn.style.display = 'none';
			btn3.style.display = 'block';
			display_label = true;
			updateStickyATCWaitlist(true);
		} else {
			cbtn.style.display = 'block';
			btn3.style.display = 'none';
			updateStickyATCWaitlist(false);
		}
		for( i = 0; i < wtl_attribute_names.length; i++ ){
			updateLabelsCKITWaitlist(form, wtl_attribute_names[i], display_label)
		}
		divsum = form.closest('div.summary, .elementor-widget-container .elementor-add-to-cart');
	} else {
		divsum = input.closest('div.summary, .elementor-widget-container .elementor-add-to-cart');
	}
	if( divsum ){
		var sumprt = divsum.parentNode;
		var new_popup = sumprt.querySelector('#commercekit-waitlist-popup');
		if( new_popup ){
			var wrap = sumprt.querySelector('#commercekit-waitlist-popup-wrap');
			if( wrap ){
				wrap.innerHTML = '';
				wrap.appendChild(new_popup);
			} else {
				var wrap = document.createElement('div');
				wrap.setAttribute('id', 'commercekit-waitlist-popup-wrap');
				wrap.appendChild(new_popup);
				sumprt.appendChild(wrap);
			}
		}
		setTimeout( function(){
			removeDuplicateWaitlistForm();
		}, 200);
	}
}
function removeDuplicateWaitlistForm(){
	var clone_popup = document.querySelector('form.commercekit_sticky-atc #commercekit-waitlist-popup');
	if( clone_popup ){
		clone_popup.remove();
	}
}
function updateLabelsCKITWaitlist(form, attribute, display_label){
	var label = form.querySelector('label[for="'+attribute+'"] .ckwtl-os-label');
	if( !label ){
		var label2 = form.querySelector('label[for="'+attribute+'"]');
		if( label2 ) {
			var label = document.createElement('span');
			label.setAttribute('class', 'ckwtl-os-label');
			label.innerHTML = '<span class="ckwtl-os-label-text"></span><span class="ckwtl-os-label-tip"><?php esc_html_e( 'Select your desired options and click on the "Get notified" button to be alerted when new stock arrives.', 'commercegurus-commercekit' ); ?></span>';
			label2.appendChild(label);
		}
	}
	var sel = form.querySelector('[name="attribute_'+attribute+'"]');
	var sel_text = '';
	if( sel && sel.options ){
		sel_text = sel.options[sel.selectedIndex].text;
		if( sel_text != '' ){
			sel_text = sel_text + ' <?php esc_html_e( 'sold out?', 'commercegurus-commercekit' ); ?>';
		}
	}
	if( label ){
		var label_text = label.querySelector('.ckwtl-os-label-text');
		if( label_text ){
			label_text.innerHTML = sel_text;
		}
		if( display_label ){
			label.style.display = 'table';
		} else {
			label.style.display = 'none';
		}
	}
}
function prepareStickyATCWaitlist(){
	var btns = document.querySelectorAll('button.sticky-atc_button, form.commercekit_sticky-atc .single_add_to_cart_button, button.elm-sticky-atc_button');
	var cntr = 0;
	var force_display = document.querySelector('#ckgit-klaviyo');
	if( force_display ){
		return true;
	}
	btns.forEach(function(btn){
		cntr++
		var parent = btn.parentNode;
		var btn3 = parent.querySelector('.sticky-ckwtl-button3');
		if( !btn3 ){
			btn3 = document.createElement('button');
			btn3.setAttribute('type', 'button');
			btn3.setAttribute('name', 'ckwtl-button3-'+cntr);
			btn3.setAttribute('id', 'ckwtl-button3-'+cntr);
			btn3.setAttribute('class', 'sticky-ckwtl-button3');
			btn3.setAttribute('onclick', '<?php echo esc_attr( $js_function ); ?>');
			btn3.innerHTML = '<?php echo esc_attr( $wtl_readmore_text ); ?>';
			parent.insertBefore(btn3, btn);
		}
	});
}
function updateStickyATCWaitlist(show){
	var btns = document.querySelectorAll('button.sticky-atc_button, form.commercekit_sticky-atc .single_add_to_cart_button, button.elm-sticky-atc_button');
	btns.forEach(function(btn){
		var parent = btn.parentNode;
		var btn3 = parent.querySelector('.sticky-ckwtl-button3');
		if( btn3 ){
			if( show ){
				btn.style.display = 'none';
				btn3.style.display = 'block';
			} else {
				btn.style.display = 'block';
				btn3.style.display = 'none';
			}
		}
	});
}
document.addEventListener('change', function(e){
	if( e.target && ( e.target.id == 'ckwtl-email' || e.target.id == 'ckwtl-consent' ) ){
		validateCKITWaitlistForm();
	}
});
document.addEventListener('keyup', function(e){
	if( e.target && ( e.target.id == 'ckwtl-email' || e.target.id == 'ckwtl-consent' ) ){
		validateCKITWaitlistForm();
	}
});
var var_input = document.querySelector('.summary input.variation_id, .elementor-widget-container .elementor-add-to-cart input.variation_id');
if( var_input ) {
	observer = new MutationObserver((changes) => {
		changes.forEach(change => {
			if(change.attributeName.includes('value')){
				setTimeout(function(){
					preparePopupCKITWaitlist(var_input);
				}, 500);
			}
		});
	});
	observer.observe(var_input, {attributes : true});
} else {
	var smpl_input = document.querySelector('.summary #ckwtl-button2, .elementor-widget-container .elementor-add-to-cart #ckwtl-button2');
	if( smpl_input ){
		preparePopupCKITWaitlist(smpl_input);
	}
}
document.addEventListener('click', function(e){
	if( e.target ){
		if( e.target.id == 'ckwtl-button2' && e.target.classList.contains( 'ckwtl-button2' ) ){
			popupCKITWaitlist();
			e.preventDefault();
			e.stopPropagation();
		} else if( e.target.id == 'ckwtl-button' ){
			submitCKITWaitlist();
			e.preventDefault();
			e.stopPropagation();
		} else if( e.target.id == 'commercekit-waitlist-close' || e.target.closest('#commercekit-waitlist-close') ){
			closeCKITWaitlistPopup();
			e.preventDefault();
			e.stopPropagation();
		} 
	}
});
var $cgkit_wtls_clone = document.querySelector('#cgkit_wtls_clone').innerHTML;
document.querySelector('#cgkit_wtls_clone').innerHTML = '';
var cgkit_bundle = document.querySelector('.summary .bundle_availability, .elementor-widget-container .elementor-add-to-cart .bundle_availability');
if( cgkit_bundle && jQuery ) {
	jQuery('.cart.bundle_data').on('woocommerce-product-bundle-updated', function(){
		var oos_bundle = cgkit_bundle.querySelector('.stock.out-of-stock');
		if( oos_bundle ) {
			cgkit_bundle.innerHTML = cgkit_bundle.innerHTML + $cgkit_wtls_clone;
			var cgkit_bundle_id = document.querySelector('#ckwtl-pid');
			if( cgkit_bundle_id ){
				cgkit_bundle_id.value = this.getAttribute('data-bundle_id');
			}
		}
	});
}
</script>
		<?php
	}
}
add_action( 'wp_footer', 'commercekit_waitlist_output_form_script' );

/**
 * Waitlist automail on stock status
 *
 * @param string $product_id Product ID.
 * @param string $stockstatus Stock status.
 * @param string $product Product Object.
 */
function commercekit_waitlist_automail_on_stock_status( $product_id, $stockstatus, $product ) {
	global $wpdb;
	if ( 'instock' === $stockstatus ) {
		$commercekit_options = get_option( 'commercekit', array() );
		$enabled_auto_mail   = ( ! isset( $commercekit_options['waitlist_auto_mail'] ) || 1 === (int) $commercekit_options['waitlist_auto_mail'] ) ? true : false;
		$wtl_not_stock_limit = isset( $commercekit_options['wtl_not_stock_limit'] ) && 1 === (int) $commercekit_options['wtl_not_stock_limit'] ? false : true;
		if ( $enabled_auto_mail ) {
			$limit = 99999;
			if ( 0 < (int) $product->get_stock_quantity() && $wtl_not_stock_limit ) {
				$limit = (int) $product->get_stock_quantity();
			}
			$rows    = $wpdb->get_results( $wpdb->prepare( 'SELECT DISTINCT email, product_id FROM ' . $wpdb->prefix . 'commercekit_waitlist WHERE product_id = %d AND mail_sent = %d ORDER BY created ASC LIMIT %d, %d', $product_id, 0, 0, $limit ), ARRAY_A ); // db call ok; no-cache ok.
			$finds   = array( '{site_name}', '{site_url}', '{product_title}', '{product_sku}', '{product_link}' );
			$replace = array( get_option( 'blogname' ), home_url( '/' ), $product->get_title(), $product->get_sku(), $product->get_permalink() );

			commercekit_remove_wc_email_name_filters();

			$email         = get_option( 'admin_email' );
			$from_mail     = isset( $commercekit_options['wtl_from_email'] ) && ! empty( $commercekit_options['wtl_from_email'] ) && isset( $commercekit_options['wtl_force_email_name'] ) && 1 === (int) $commercekit_options['wtl_force_email_name'] ? stripslashes_deep( $commercekit_options['wtl_from_email'] ) : commercekit_get_default_settings( 'wtl_from_email' );
			$email_headers = array( 'Content-Type: text/html; charset=UTF-8', 'From: ' . $from_mail, 'Reply-To: ' . $from_mail );
			$email_subject = isset( $commercekit_options['wtl_auto_subject'] ) && ! empty( $commercekit_options['wtl_auto_subject'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_auto_subject'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_auto_subject' ) );
			$email_body    = isset( $commercekit_options['wtl_auto_content'] ) && ! empty( $commercekit_options['wtl_auto_content'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_auto_content'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_auto_content' ) );
			$email_footer  = isset( $commercekit_options['wtl_auto_footer'] ) && ! empty( $commercekit_options['wtl_auto_footer'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $commercekit_options['wtl_auto_footer'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_auto_footer' ) );
			$email_subject = str_replace( $finds, $replace, $email_subject );
			$email_body    = str_replace( $finds, $replace, $email_body );
			$email_body    = html_entity_decode( $email_body );
			$email_body    = str_replace( "\r\n", '<br />', $email_body );
			$email_footer  = str_replace( $finds, $replace, $email_footer );
			$email_footer  = html_entity_decode( $email_footer );
			$email_footer  = str_replace( "\r\n", '<br />', $email_footer );
			$email_body    = $email_body . '<br />' . $email_footer;

			if ( is_array( $rows ) && count( $rows ) ) {
				$wtls_reset = (int) get_option( 'commercekit_wtls_reset' );
				if ( 0 === $wtls_reset ) {
					update_option( 'commercekit_wtls_reset', time(), false );
				}
				$wtls_total = (int) get_option( 'commercekit_wtls_total' );
				foreach ( $rows as $row ) {
					$email_subject2 = str_replace( '{customer_email}', $row['email'], $email_subject );
					$email_body2    = str_replace( '{customer_email}', $row['email'], $email_body );

					$success = wp_mail( $row['email'], $email_subject2, $email_body2, $email_headers );
					$table   = $wpdb->prefix . 'commercekit_waitlist';
					$data    = array(
						'mail_sent' => 1,
						'updated'   => time(),
					);
					$where   = array(
						'email'      => $row['email'],
						'product_id' => $row['product_id'],
					);

					$data_format  = array( '%d', '%d' );
					$where_format = array( '%s', '%d' );
					$wpdb->update( $table, $data, $where, $data_format, $where_format ); // db call ok; no-cache ok.
					$wtls_total++;
				}
				update_option( 'commercekit_wtls_total', $wtls_total, false );
			}
		}
	}
}

add_action( 'woocommerce_product_set_stock_status', 'commercekit_waitlist_automail_on_stock_status', 99, 3 );
add_action( 'woocommerce_variation_set_stock_status', 'commercekit_waitlist_automail_on_stock_status', 99, 3 );

/**
 * Email from name
 *
 * @param  string $from_name from name.
 * @return string $from_name from name.
 */
function commercekit_email_from_name( $from_name ) {
	$options    = get_option( 'commercekit', array() );
	$force_name = isset( $options['wtl_force_email_name'] ) && 1 === (int) $options['wtl_force_email_name'] ? true : false;
	if ( $force_name ) {
		$from_name = isset( $options['wtl_from_name'] ) && ! empty( $options['wtl_from_name'] ) ? stripslashes_deep( $options['wtl_from_name'] ) : commercekit_get_default_settings( 'wtl_from_name' );
	}

	return $from_name;
}
add_filter( 'wp_mail_from_name', 'commercekit_email_from_name', 9, 1 );

/**
 * Email from email
 *
 * @param  string $from_email from email.
 * @return string $from_email from email.
 */
function commercekit_email_from_email( $from_email ) {
	$options     = get_option( 'commercekit', array() );
	$force_email = isset( $options['wtl_force_email_name'] ) && 1 === (int) $options['wtl_force_email_name'] ? true : false;
	if ( $force_email ) {
		$from_email = isset( $options['wtl_from_email'] ) && ! empty( $options['wtl_from_email'] ) ? stripslashes_deep( $options['wtl_from_email'] ) : commercekit_get_default_settings( 'wtl_from_email' );
	}

	return $from_email;
}
add_filter( 'wp_mail_from', 'commercekit_email_from_email', 9, 1 );

/**
 * Remove WooCommerce from email, from name filters.
 */
function commercekit_remove_wc_email_name_filters() {
	remove_filter( 'wp_mail_from', array( 'WC_Email', 'get_from_address' ) );
	remove_filter( 'wp_mail_from_name', array( 'WC_Email', 'get_from_name' ) );
}

/**
 * Waitlist add to cart text
 *
 * @param  string $text add to cart text.
 * @param  string $product product object.
 * @return string $text add to cart text.
 */
function commercekit_waitlist_add_to_cart_text( $text, $product ) {
	$options         = get_option( 'commercekit', array() );
	$flags           = commercekit_feature_flags()->get_flags();
	$enable_waitlist = isset( $flags['waitlist'] ) && 1 === (int) $flags['waitlist'] ? 1 : 0;
	$readmore_label  = isset( $options['wtl_readmore_text'] ) && ! empty( $options['wtl_readmore_text'] ) ? commercekit_get_multilingual_string( stripslashes_deep( $options['wtl_readmore_text'] ) ) : commercekit_get_multilingual_string( commercekit_get_default_settings( 'wtl_readmore_text' ) );
	if ( ! $enable_waitlist ) {
		return $text;
	}
	if ( $product && method_exists( $product, 'is_type' ) && ( $product->is_type( 'composite' ) || $product->is_type( 'grouped' ) ) ) {
		return $text;
	}
	if ( $product && ( ( $product->managing_stock() && 0 === (int) $product->get_stock_quantity() && 'no' === $product->get_backorders() ) || 'outofstock' === $product->get_stock_status() ) ) {
		return $readmore_label;
	}

	return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'commercekit_waitlist_add_to_cart_text', 10, 2 );

/**
 * Show hidden out of stock variations.
 *
 * @param  string $value option value.
 * @param  string $option option name.
 */
function commercekit_waitlist_show_hidden_oos_variations( $value, $option ) {
	global $wp_query;
	if ( isset( $wp_query ) && function_exists( 'is_product' ) && is_product() ) {
		$options      = get_option( 'commercekit', array() );
		$wtl_show_oos = isset( $options['wtl_show_oos'] ) && 1 === (int) $options['wtl_show_oos'] ? true : false;
		if ( $wtl_show_oos ) {
			$value = 'no';
		}
	}

	return $value;
}
add_filter( 'option_woocommerce_hide_out_of_stock_items', 'commercekit_waitlist_show_hidden_oos_variations', 10, 2 );


/**
 * Make empty quatity box of grouped child product out of stock quantity
 *
 * @param  string $value quatity value.
 * @param  string $product grouped product object.
 */
function commercekit_waitlist_grouped_product_oos_quantity( $value, $product ) {
	if ( ! $product->is_in_stock() ) {
		return ' ';
	}

	return $value;
}
add_filter( 'woocommerce_grouped_product_list_column_quantity', 'commercekit_waitlist_grouped_product_oos_quantity', 10, 2 );

/**
 * Admin meta box.
 */
function commercekit_waitlist_klaviyo_meta_box() {
	$options = get_option( 'commercekit', array() );
	if ( isset( $options['waitlist_esp'] ) && 2 === (int) $options['waitlist_esp'] && isset( $options['wtl_esp_klaviyo'] ) && 1 === (int) $options['wtl_esp_klaviyo'] ) {
		add_meta_box( 'commercekit-wtl-klaviyo-meta-box', esc_html__( 'CommerceKit Waitlist: Klaviyo - Basic', 'commercegurus-commercekit' ), 'commercekit_waitlist_admin_klaviyo_display', 'product', 'normal', 'low' );
	}
}
add_action( 'admin_init', 'commercekit_waitlist_klaviyo_meta_box' );

/**
 * Admin categories meta box display.
 *
 * @param string $post post object.
 */
function commercekit_waitlist_admin_klaviyo_display( $post ) {
	$cgkit_esp_klaviyo = array();
	if ( isset( $post->ID ) && $post->ID && 'auto-draft' !== $post->post_status ) {
		$cgkit_esp_klaviyo = (array) get_post_meta( $post->ID, 'cgkit_esp_klaviyo', true );
	}
	$options     = get_option( 'commercekit', array() );
	$esp_klaviyo = isset( $options['esp_klaviyo'] ) ? $options['esp_klaviyo'] : array();
	if ( ! isset( $cgkit_esp_klaviyo['enable_klaviyo'] ) && isset( $options['wtl_esp_klaviyo'] ) ) {
		$cgkit_esp_klaviyo['enable_klaviyo'] = $options['wtl_esp_klaviyo'];
	}
	if ( is_array( $esp_klaviyo ) && count( $esp_klaviyo ) ) {
		foreach ( $esp_klaviyo as $key => $value ) {
			if ( ! isset( $cgkit_esp_klaviyo[ $key ] ) ) {
				$cgkit_esp_klaviyo[ $key ] = $esp_klaviyo[ $key ];
			}
		}
	}
	$form_id_required = false;
	if ( ! isset( $esp_klaviyo['show_form_id'] ) || empty( $esp_klaviyo['show_form_id'] ) ) {
		$form_id_required = true;
	}
	require_once dirname( __FILE__ ) . '/templates/admin-esp-klaviyo.php';
}

/**
 * Admin meta box save.
 *
 * @param string $post_id post id.
 * @param string $post post object.
 */
function commercekit_waitlist_admin_klaviyo_meta_save( $post_id, $post ) {
	if ( 'product' === $post->post_type ) {
		$options = get_option( 'commercekit', array() );
		if ( isset( $options['waitlist_esp'] ) && 2 === (int) $options['waitlist_esp'] && isset( $options['wtl_esp_klaviyo'] ) && 1 === (int) $options['wtl_esp_klaviyo'] ) {
			$commercekit_nonce = isset( $_POST['commercekit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commercekit_nonce'] ) ) : '';
			if ( ! $commercekit_nonce || ! wp_verify_nonce( $commercekit_nonce, 'commercekit_settings' ) ) {
				return;
			}
			if ( ! isset( $_POST['cgkit_esp_klaviyo']['enable_klaviyo'] ) ) {
				$_POST['cgkit_esp_klaviyo']['enable_klaviyo'] = 0;
			}
			if ( ! isset( $_POST['cgkit_esp_klaviyo']['main_call'] ) ) {
				$_POST['cgkit_esp_klaviyo']['main_call'] = 0;
			}
			if ( ! isset( $_POST['cgkit_esp_klaviyo']['oos_message'] ) ) {
				$_POST['cgkit_esp_klaviyo']['oos_message'] = 0;
			}
			if ( ! isset( $_POST['cgkit_esp_klaviyo']['stock_message'] ) ) {
				$_POST['cgkit_esp_klaviyo']['stock_message'] = 0;
			}
			if ( ! isset( $_POST['cgkit_esp_klaviyo']['force_display'] ) ) {
				$_POST['cgkit_esp_klaviyo']['force_display'] = 0;
			}
			$cgkit_esp_klaviyo = isset( $_POST['cgkit_esp_klaviyo'] ) ? map_deep( wp_unslash( $_POST['cgkit_esp_klaviyo'] ), 'wp_kses_post' ) : array(); // phpcs:ignore
			update_post_meta( $post->ID, 'cgkit_esp_klaviyo', $cgkit_esp_klaviyo );
		}
	}
}
add_action( 'save_post', 'commercekit_waitlist_admin_klaviyo_meta_save', 10, 2 );

/**
 * Is Klaviyo Basic enabled.
 */
function commercekit_waitlist_get_klaviyo_options() {
	global $post, $cgkit_klaviyo_options, $wp_query, $cgkit_var_post;

	if ( isset( $cgkit_klaviyo_options ) ) {
		return $cgkit_klaviyo_options;
	}

	$action = $wp_query->get( 'wc-ajax' );
	if ( 'get_variation' === $action ) {
		$product_id = $cgkit_var_post ? $cgkit_var_post->get_id() : 0;
		$parent_id  = $cgkit_var_post ? $cgkit_var_post->get_parent_id() : 0;
		if ( $parent_id ) {
			$product_id = $parent_id;
		}
		$is_product_page = true;
	} else {
		$product_id = isset( $post->ID ) ? $post->ID : 0;
		if ( function_exists( 'is_product' ) && is_product() ) {
			$is_product_page = true;
		} else {
			$is_product_page = false;
		}
	}

	$options = get_option( 'commercekit', array() );
	if ( $is_product_page && $product_id && isset( $options['waitlist_esp'] ) && 2 === (int) $options['waitlist_esp'] && isset( $options['wtl_esp_klaviyo'] ) && 1 === (int) $options['wtl_esp_klaviyo'] ) {
		$cgkit_esp_klaviyo = (array) get_post_meta( $product_id, 'cgkit_esp_klaviyo', true );
		if ( ! isset( $cgkit_esp_klaviyo['enable_klaviyo'] ) || 1 === (int) $cgkit_esp_klaviyo['enable_klaviyo'] ) {
			$esp_klaviyo = isset( $options['esp_klaviyo'] ) ? $options['esp_klaviyo'] : array();
			if ( ! isset( $cgkit_esp_klaviyo['enable_klaviyo'] ) && isset( $options['wtl_esp_klaviyo'] ) ) {
				$cgkit_esp_klaviyo['enable_klaviyo'] = $options['wtl_esp_klaviyo'];
			}

			if ( is_array( $esp_klaviyo ) && count( $esp_klaviyo ) ) {
				foreach ( $esp_klaviyo as $key => $value ) {
					if ( ! isset( $cgkit_esp_klaviyo[ $key ] ) ) {
						$cgkit_esp_klaviyo[ $key ] = $esp_klaviyo[ $key ];
					}
				}
			}

			$cgkit_klaviyo_options = $cgkit_esp_klaviyo;
			return $cgkit_klaviyo_options;
		}
	}

	$cgkit_klaviyo_options = false;
	return $cgkit_klaviyo_options;
}
/**
 * Out of stock Klaviyo form scripts.
 */
function commercekit_waitlist_oos_klaviyo_form_scripts() {
	global $post;
	$options = get_option( 'commercekit', array() );
	$klaviyo = commercekit_waitlist_get_klaviyo_options();
	if ( false !== $klaviyo ) {
		$company_id = isset( $options['esp_klaviyo']['company_id'] ) && ! empty( $options['esp_klaviyo']['company_id'] ) ? $options['esp_klaviyo']['company_id'] : '';
		wp_register_script( 'commercekit-esp-klaviyo-script', esc_url( add_query_arg( 'company_id', $company_id, '//static.klaviyo.com/onsite/js/klaviyo.js' ) ), array(), null, false ); // phpcs:ignore
		wp_enqueue_script( 'commercekit-esp-klaviyo-script' );
	}
}
add_action( 'wp_enqueue_scripts', 'commercekit_waitlist_oos_klaviyo_form_scripts', 10 );

/**
 * Out of stock Klaviyo new message.
 *
 * @param string $text message text.
 * @param string $product product object.
 */
function commercekit_waitlist_new_oos_message( $text, $product ) {
	global $wp_query, $cgkit_var_post;
	$action = $wp_query->get( 'wc-ajax' );
	if ( 'get_variation' === $action ) {
		$cgkit_var_post = $product;
	}
	$options = get_option( 'commercekit', array() );
	$klaviyo = commercekit_waitlist_get_klaviyo_options();
	if ( ! $product->is_in_stock() && false !== $klaviyo ) {
		$oos_message_txt = esc_html__( 'This product is currently out of stock and unavailable', 'commercegurus-commercekit' );
		if ( isset( $klaviyo['oos_message'] ) && 1 === (int) $klaviyo['oos_message'] ) {
			$oos_message_txt = isset( $klaviyo['oos_message_txt'] ) && ! empty( $klaviyo['oos_message_txt'] ) ? commercekit_get_multilingual_string( $klaviyo['oos_message_txt'] ) : '';
			if ( ! empty( $oos_message_txt ) ) {
				return $oos_message_txt;
			}
		}
		return $oos_message_txt;
	}

	return $text;
}
add_filter( 'woocommerce_get_availability_text', 'commercekit_waitlist_new_oos_message', 99, 2 );

/**
 * Klaviyo form data.
 */
function commercekit_waitlist_klaviyo_form() {
	global $product;
	$options = get_option( 'commercekit', array() );
	$klaviyo = commercekit_waitlist_get_klaviyo_options();
	if ( false === $klaviyo || ! $product ) {
		return;
	}
	$force_display = isset( $klaviyo['force_display'] ) && 1 === (int) $klaviyo['force_display'] ? true : false;
	if ( ! $product->is_in_stock() || $force_display || $product->is_type( 'variable' ) ) {
		$main_call_txt = esc_html__( 'Can\'t find your size?', 'commercegurus-commercekit' );
		$main_call_yes = isset( $klaviyo['main_call'] ) && 1 === (int) $klaviyo['main_call'] ? true : false;
		$main_call_txt = true === $main_call_yes && isset( $klaviyo['main_call_txt'] ) && ! empty( $klaviyo['main_call_txt'] ) ? commercekit_get_multilingual_string( $klaviyo['main_call_txt'] ) : $main_call_txt;
		if ( $force_display ) {
			echo '<div id="ckgit-klaviyo" class="ckgit-klaviyo" data-force="' . ( $force_display ? 1 : 0 ) . '" style="' . ( $product->is_type( 'variable' ) && ! $force_display ? 'display: none;' : '' ) . '"> <a class="ckgit-klaviyo__link" href="#ckgit-klaviyo-stock-modal"> <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" class="ckgit-klaviyo__icon" viewBox="0 0 20 20" fill="currentColor"> <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" /> <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" /> </svg> <span class="ckgit-klaviyo__text">' . esc_html( $main_call_txt ) . '</span> </a> </div>';
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'commercekit_waitlist_klaviyo_form', 41 );

/**
 * Klaviyo form script.
 */
function commercekit_waitlist_klaviyo_form_scripts() {
	global $product;
	$options = get_option( 'commercekit', array() );
	$klaviyo = commercekit_waitlist_get_klaviyo_options();
	if ( false === $klaviyo || ! $product ) {
		return;
	}
	$force_display = isset( $klaviyo['force_display'] ) && 1 === (int) $klaviyo['force_display'] ? true : false;
	if ( ! $product->is_in_stock() || $force_display || $product->is_type( 'variable' ) ) {
		$stock_message_yes = isset( $klaviyo['stock_message'] ) && 1 === (int) $klaviyo['stock_message'] ? true : false;
		$stock_message_txt = true === $stock_message_yes && isset( $klaviyo['stock_message_txt'] ) && ! empty( $klaviyo['stock_message_txt'] ) ? commercekit_get_multilingual_string( $klaviyo['stock_message_txt'] ) : '';

		$show_form_id = isset( $klaviyo['show_form_id'] ) && ! empty( $klaviyo['show_form_id'] ) ? $klaviyo['show_form_id'] : '';
		?>
<style>
.ckgit-klaviyo { font-weight: 600; margin-bottom: 10px; clear: both; padding-top: 10px; display: block; font-size: 13px; }
.ckgit-klaviyo__link { display: flex; align-items: center; color: #111; }
.ckgit-klaviyo__icon { margin-right: 10px; }
#ckgit-klaviyo-stock-modal { position: fixed; width: 100%; height: 100%; max-width: 100%; max-height: 100%; background-color: rgba(0,0,0,0.4); z-index: 9999999; top: 0; left: 0; bottom: 0; right: 0; align-items: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),0 10px 10px -5px rgba(0, 0, 0, 0.04); display: none; }
#ckgit-stock-modal-wrap { background-color: #fff; color: #000; overflow: hidden; position: relative; margin: 75px auto; width: 600px; height: auto; max-width: 100%; border-radius: 6px; padding: 10px; }
.ckgit-stock-modal__body { margin-bottom: 15px; }
.ckgit-stock-modal__heading { padding-left: 10px; }
.ckgit-klaviyo__desc { font-size: 14px; padding: 10px; display: block; }
.ckgit-klaviyo__desc p { margin-bottom: 0; }
.ckgit-stock-modal__close { position: absolute; width: 25px; height: 25px; cursor: pointer; right: 5px; top: 10px; }
.ckgit-stock-modal__close svg { width: 22px; height: 22px; }
</style>
<div id="ckgit-klaviyo-stock-modal">
	<div id="ckgit-stock-modal-wrap">
		<div class="ckgit-stock-modal__close"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></div>
		<h2 class="ckgit-stock-modal__heading"><?php esc_html_e( 'Stock information', 'commercegurus-commercekit' ); ?></h2>
		<div class="ckgit-stock-modal__body">
			<div class="ckgit-klaviyo__desc"><?php echo wp_kses_post( $stock_message_txt ); ?></div>
		<?php echo ! empty( $show_form_id ) ? '<div class="klaviyo-form-' . esc_attr( $show_form_id ) . '"></div>' : ''; ?>
		</div>
	</div>
</div>
<script type="text/javascript">
document.addEventListener( 'click', function( e ) {
	var input = e.target;
	var inputp = input.closest( '.ckgit-klaviyo__link' );
	if ( input.classList.contains( 'ckgit-klaviyo__link' ) || inputp || input.classList.contains( 'ckgit-klaviyo-button' ) ) {
		e.preventDefault();
		e.stopPropagation();
		showKlaviyoPopupWaitlist();
	}
	var inputp = input.closest( '.ckgit-stock-modal__close' );
	if ( input.classList.contains( 'ckgit-stock-modal__close' ) || inputp ) {
		e.preventDefault();
		e.stopPropagation();
		var klaviyo_modal = document.querySelector( '#ckgit-klaviyo-stock-modal' );
		if ( klaviyo_modal ) {
			klaviyo_modal.style.display = 'none';
		}
	}
});
var var_input = document.querySelector( '.summary input.variation_id, .elementor-widget-container .elementor-add-to-cart input.variation_id' );
if ( var_input ) {
	observer = new MutationObserver( ( changes ) => {
		changes.forEach( change => {
			if ( change.attributeName.includes( 'value' ) ) {
				setTimeout(function(){
					prepareKlaviyoPopupWaitlist( var_input );
				}, 500);
			}
		});
	} );
	observer.observe( var_input, { attributes : true } );
}
function prepareKlaviyoPopupWaitlist( input ){
	var form = input.closest( 'form.variations_form' );
	if ( form ) {
		if ( form.classList.contains( 'cgkit-swatch-form' ) ) {
			return true;
		}
		var klaviyo = document.querySelector( '#ckgit-klaviyo' );
		if ( klaviyo ) {
			var ostock = form.querySelector( '.stock.out-of-stock' );
			var force = klaviyo.getAttribute( 'data-force' );
			if ( force == 1 || ( ostock && input.value != '' ) ) {
				klaviyo.style.display = 'block';
			} else {
				klaviyo.style.display = 'none';
			}
		}
	}
}
function showKlaviyoPopupWaitlist(){
	var klaviyo_modal = document.querySelector( '#ckgit-klaviyo-stock-modal' );
	if ( klaviyo_modal && ! klaviyo_modal.classList.contains( 'klaviyo-form-closed' ) ) {
		klaviyo_modal.style.display = 'block';
	}
}
window.addEventListener( 'klaviyoForms', function( e ) { 
	if ( e.detail.type == 'close' ) {
		var klaviyo_modal = document.querySelector( '#ckgit-klaviyo-stock-modal' );
		if ( klaviyo_modal ) {
			klaviyo_modal.classList.add( 'klaviyo-form-closed' );
			klaviyo_modal.style.display = 'none';
		}
	}
} );
</script>
		<?php
	}
}
add_action( 'woocommerce_after_single_product', 'commercekit_waitlist_klaviyo_form_scripts' );

/**
 * Waitlist record sales
 *
 * @param  string $order_id of order.
 */
function commercekit_waitlist_record_sales( $order_id ) {
	global $wpdb;
	$order = wc_get_order( $order_id );
	$reset = (int) get_option( 'commercekit_wtls_reset' );
	$email = $order->get_billing_email();
	$sales = 0;
	$price = 0;
	$umail = $email;
	$wpusr = wp_get_current_user();
	if ( $wpusr ) {
		$umail = $wpusr->user_email;
	}
	if ( ! $reset || ( empty( $email ) && empty( $umail ) ) ) {
		return;
	}
	foreach ( $order->get_items() as $item_id => $item ) {
		if ( isset( $item['variation_id'] ) && $item['variation_id'] > 0 ) {
			$product_id = $item['variation_id'];
		} else {
			$product_id = $item['product_id'];
		}
		$quantity = (int) $item['quantity'];
		$found_id = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . $wpdb->prefix . 'commercekit_waitlist WHERE product_id = %d AND ( email = %s OR email = %s ) AND mail_sent = %d AND updated >= %d AND tracked = %d', $product_id, $email, $umail, 1, $reset, 0 ) ); // db call ok; no-cache ok.
		if ( $found_id && $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$sales++;
				$price += $quantity * (float) $product->get_price();
				$data   = array( 'tracked' => 1 );
				$where  = array( 'id' => $found_id );

				$data_format  = array( '%d' );
				$where_format = array( '%d' );
				$wpdb->update( $wpdb->prefix . 'commercekit_waitlist', $data, $where, $data_format, $where_format ); // db call ok; no-cache ok.
			}
		}
	}
	if ( $sales ) {
		$wtls_total = (int) get_option( 'commercekit_wtls_total' );
		$wtls_sales = (int) get_option( 'commercekit_wtls_sales' ) + $sales;
		$wtls_sales = $wtls_sales > $wtls_total ? $wtls_total : $wtls_sales;
		update_option( 'commercekit_wtls_sales', $wtls_sales, false );
	}
	if ( $price ) {
		$wtls_price = (float) get_option( 'commercekit_wtls_sales_revenue' );
		update_option( 'commercekit_wtls_sales_revenue', ( $wtls_price + $price ), false );
	}
}
add_action( 'woocommerce_thankyou', 'commercekit_waitlist_record_sales' );
