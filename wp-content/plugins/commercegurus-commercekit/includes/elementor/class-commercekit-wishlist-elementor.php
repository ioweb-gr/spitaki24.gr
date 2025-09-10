<?php
/**
 * CommerceGurus Wishlist Elementor Widget
 *
 * @author   CommerceGurus
 * @package  CommerceGurus_Wishlist
 * @since    2.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly....
}

/**
 * Wishlist Elementor widget.
 */
class Commercekit_Wishlist_Elementor extends \Elementor\Widget_Base {

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
		return 'commercekit-wishlist';
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
		return esc_html__( 'CommerceKit Wishlist', 'commercegurus-commercekit' );
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
		return 'eicon-heart-o';
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
		return array( 'woocommerce', 'product' );
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
		global $product, $post;
		if ( function_exists( 'commercekit_single_product_wishlist' ) ) {
			if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
				if ( isset( $post->ID ) && $post->ID ) {
					$product = wc_get_product( $post->ID );
				}
			}
			echo commercekit_single_product_wishlist(); // phpcs:ignore
		}
	}
}
