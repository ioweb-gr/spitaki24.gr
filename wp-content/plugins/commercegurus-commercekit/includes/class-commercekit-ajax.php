<?php
/**
 * CommerceKit AJAX. Frontend AJAX Event Handlers.
 *
 * @class   COMMERCEKIT_AJAX
 * @package CommerceKit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly....
}

/**
 * COMMERCEKIT_AJAX class.
 */
class COMMERCEKIT_AJAX {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		add_action( 'template_redirect', array( __CLASS__, 'do_commercekit_ajax' ), 0 );
	}

	/**
	 * Get CommerceKit Ajax Endpoint.
	 *
	 * @param string $request Optional.
	 *
	 * @return string
	 */
	public static function get_endpoint( $request = '' ) {
		if ( class_exists( 'WC_AJAX' ) ) {
			return str_ireplace( 'wc-ajax=endpoint', 'commercekit-ajax', WC_AJAX::get_endpoint( 'endpoint' ) );
		} else {
			return esc_url_raw( apply_filters( 'commercekit_ajax_get_endpoint', add_query_arg( 'commercekit-ajax', $request, remove_query_arg( array( '_wpnonce' ), home_url( '/', 'relative' ) ) ), $request ) );
		}
	}

	/**
	 * Set CommerceKit AJAX constant and headers.
	 */
	public static function define_ajax() {
		// phpcs:disable
		if ( ! empty( $_GET['commercekit-ajax'] ) ) {
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			if ( ! defined( 'WC_DOING_AJAX' ) ) {
				define( 'WC_DOING_AJAX', true );
			}
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 ); // Turn off display_errors during AJAX events to prevent malformed JSON.
			}
			$GLOBALS['wpdb']->hide_errors();
		}
		// phpcs:enable
	}

	/**
	 * Send headers for CommerceKit Ajax Requests.
	 */
	private static function commercekit_ajax_headers() {
		if ( ! headers_sent() ) {
			send_origin_headers();
			send_nosniff_header();
			header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
			header( 'X-Robots-Tag: noindex' );
			status_header( 200 );
		} elseif ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			headers_sent( $file, $line );
			trigger_error( "commercekit_ajax_headers cannot set headers - headers already sent by {$file} on line {$line}", E_USER_NOTICE ); // phpcs:ignore
		}
	}

	/**
	 * Check for CommerceKit Ajax request and fire action.
	 */
	public static function do_commercekit_ajax() {
		global $wp_query;

		// phpcs:disable
		if ( ! empty( $_GET['commercekit-ajax'] ) ) {
			$wp_query->set( 'commercekit-ajax', sanitize_text_field( wp_unslash( $_GET['commercekit-ajax'] ) ) );
		}

		$action = $wp_query->get( 'commercekit-ajax' );

		if ( $action ) {
			self::commercekit_ajax_headers();
			$action = sanitize_text_field( $action );
			do_action( 'wp_ajax_' . $action );
			wp_die();
		}
		// phpcs:enable
	}
}

COMMERCEKIT_AJAX::init();
