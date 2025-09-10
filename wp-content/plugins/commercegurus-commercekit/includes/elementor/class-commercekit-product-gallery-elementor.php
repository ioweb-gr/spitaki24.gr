<?php
/**
 * CommerceGurus Product Gallery Elementor Widget
 *
 * @author   CommerceGurus
 * @package  Commercekit_Product_Gallery_Elementor
 * @since    2.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly....
}

/**
 * Product Gallery Elementor widget.
 */
class Commercekit_Product_Gallery_Elementor extends \Elementor\Widget_Base {

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
		return 'commercekit-product-gallery';
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
		return esc_html__( 'CommerceKit Product Gallery', 'commercegurus-commercekit' );
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
		return 'eicon-gallery-grid';
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
		if ( class_exists( 'CommerceGurus_Attributes_Gallery' ) ) {
			$gallery_obj = new CommerceGurus_Attributes_Gallery();
			echo $gallery_obj->commercegurus_load_pdp_attributes_gallery(); // phpcs:ignore
		} elseif ( class_exists( 'CommerceGurus_Gallery' ) ) {
			$gallery_obj = new CommerceGurus_Gallery();
			echo $gallery_obj->commercegurus_load_pdp_gallery(); // phpcs:ignore
		}
	}
}
