<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Email_General.
 *
 * @since 4.0
 */
class TLWP_Common {

	/**
	 * Get TLWP Option
	 *
	 * @param $option
	 * @param null   $default
	 *
	 * @return mixed|void|null
	 *
	 * @since 4.0.15
	 */
	public static function get_tlwp_option( $option, $default = null ) {

		if ( empty( $option ) ) {
			return null;
		}

		$option_prefix = 'tlwp_';

		return get_option( $option_prefix . $option, $default );

	}

	/**
	 * Set tlwp option
	 *
	 * @param $option
	 * @param $value
	 *
	 * @return bool|null
	 *
	 * @since 4.0.15
	 */
	public static function set_tlwp_option( $option, $value ) {

		if ( empty( $option ) ) {
			return null;
		}

		$option_prefix = 'tlwp_';

		return update_option( $option_prefix . $option, $value, false );

	}

	/**
	 * Delete tlwp options
	 *
	 * @param string $option
	 *
	 * @return bool|null
	 *
	 * @since 4.0.15
	 */
	public static function delete_tlwp_option( $option = null ) {
		if ( empty( $option ) ) {
			return null;
		}

		$option_prefix = 'tlwp_';

		return delete_option( $option_prefix . $option );
	}	

}
