<?php
/**
 * CommerceKit Feature Flags.
 *
 * @class   Commercekit_Feature_Flags
 * @package CommerceKit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly....
}

/**
 * Commercekit_Feature_Flags class.
 */
class CommerceKit_Feature_Flags {

	/**
	 * Class instance.
	 *
	 * @var instance
	 */
	private static $instance = null;

	/**
	 * Return flags.
	 *
	 * @var flags
	 */
	private $flags = array();

	/**
	 * Commercekit_Feature_Flags Constructor
	 */
	private function __construct() {
		$this->load_flags();
	}

	/**
	 * Commercekit_Feature_Flags get instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Load flags
	 */
	private function load_flags() {
		$options       = get_option( 'commercekit', array() );
		$default_flags = array(
			'ajax_search'            => 0,
			'countdown_timer'        => 0,
			'order_bump'             => 0,
			'order_bump_mini'        => 0,
			'pdp_gallery'            => 0,
			'pdp_attributes_gallery' => 0,
			'attribute_swatches'     => 0,
			'attribute_swatches_plp' => 0,
			'as_enable_tooltips'     => 0,
			'sticky_atc_desktop'     => 0,
			'sticky_atc_mobile'      => 0,
			'sticky_atc_tabs'        => 0,
			'fsn_cart_page'          => 0,
			'fsn_mini_cart'          => 0,
			'widget_pos_fsn'         => 0,
			'size_guide'             => 0,
			'store_badge'            => 0,
			'inventory_display'      => 0,
			'waitlist'               => 0,
			'wishlist'               => 0,
		);
		if ( count( $default_flags ) ) {
			foreach ( $default_flags as $key => $value ) {
				if ( isset( $options[ $key ] ) ) {
					$default_flags[ $key ] = $options[ $key ];
				}
			}
		}

		$flags = apply_filters( 'commercekit_feature_flags', $default_flags );
		if ( count( $flags ) ) {
			foreach ( $flags as $key => $value ) {
				if ( ! array_key_exists( $key, $default_flags ) ) {
					unset( $flags[ $key ] );
				} else {
					$value = apply_filters( 'commercekit_feature_flag_' . $key . '_enabled', $value );
					if ( true === $value || 1 === $value ) {
						$default_flags[ $key ] = 1;
					} elseif ( false === $value || 0 === $value ) {
						$default_flags[ $key ] = 0;
					}
					if ( defined( 'COMMERCEKIT_' . strtoupper( $key ) . '_ENABLED' ) ) {
						$value = constant( 'COMMERCEKIT_' . strtoupper( $key ) . '_ENABLED' );
						if ( true === $value || 1 === $value ) {
							$default_flags[ $key ] = 1;
						} elseif ( false === $value || 0 === $value ) {
							$default_flags[ $key ] = 0;
						}
					}
				}
			}
		}

		$this->flags = $default_flags;
	}

	/**
	 * Is enabled
	 *
	 * @param string $flag_name flag key.
	 */
	public function is_enabled( $flag_name ) {
		if ( isset( $this->flags[ $flag_name ] ) && 1 === (int) $this->flags[ $flag_name ] ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get all flags
	 */
	public function get_flags() {
		return $this->flags;
	}
}

/**
 * Get feature flags instance.
 */
function commercekit_feature_flags() {
	return Commercekit_Feature_Flags::get_instance();
}
