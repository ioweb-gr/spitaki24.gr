<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'get_tlwp_db_version' ) ) {
	/**
	 * Get current db version
	 *
	 * @since 4.0.0
	 */
	function get_tlwp_db_version() {

		$option = get_option( 'tlwp_db_version', '1.0.0' );
		
		return $option;

	}
}

if ( ! function_exists( 'tlwp_maybe_define_constant' ) ) {
	/**
	 * Define constant
	 *
	 * @param $name
	 * @param $value
	 *
	 * @since 4.0.0
	 */
	function tlwp_maybe_define_constant( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
}


if ( ! function_exists( 'tlwp_get_current_date_time' ) ) {
	/**
	 * Get current date time
	 *
	 * @return false|string
	 */
	function tlwp_get_current_date_time() {
		return gmdate( 'Y-m-d H:i:s' );
	}
}

if ( ! function_exists( 'tlwp_increase_memory_limit' ) ) {

	/**
	 * Return memory limit required for ES heavy operations
	 *
	 * @return string
	 *
	 * @since 4.5.4
	 */
	function tlwp_increase_memory_limit() {

		return '512M';
	}
}