<?php
/**
 * CommerceGurus Checkout Countdown Elementor Widget
 *
 * @author   CommerceGurus
 * @package  CommerceGurus_Countdown
 * @since    2.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly....
}

/**
 * Wishlist Elementor widget.
 */
class Commercekit_Countdown2_Elementor extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve button widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'commercekit-countdown2';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve button widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'CommerceKit Checkout Countdown', 'commercegurus-commercekit' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve button widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-countdown';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the button widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'commercekit' );
	}

	/**
	 * Get group name.
	 *
	 * Some widgets need to use group names, this method allows you to create them.
	 * By default it retrieves the regular name.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @return string Unique name.
	 */
	public function get_group_name() {
		return 'woocommerce';
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the widget keywords.
	 *
	 * @since 1.0.10
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return array( 'woocommerce', 'checkout' );
	}

	/**
	 * Render button widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		if ( function_exists( 'commercekit_countdown_timer_by_location' ) ) {
			echo commercekit_countdown_timer_by_location( 'checkout', true ); // phpcs:ignore
		}
	}
}
