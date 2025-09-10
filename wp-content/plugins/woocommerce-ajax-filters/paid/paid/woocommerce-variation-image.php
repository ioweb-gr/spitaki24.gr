<?php
class BeRocket_AAPF_compat_woocommerce_variation_image {
    function __construct() {
        add_filter('woocommerce_product_get_image', array(__CLASS__, 'replace_variation_image'), 10, 5);
        add_filter('post_thumbnail_html', array(__CLASS__, 'post_thumbnail_html'), 10, 5);
        add_filter('wc_product_table_data_image', array(__CLASS__, 'wc_product_table_data_image'), 10, 2);
        include_once('woocommerce-variation-functions.php');
    }
	public static function post_thumbnail_html($html, $post_ID, $post_thumbnail_id, $size, $attr) {
		global $product;
		if( isset($product) && is_a($product, 'WC_Product') ) {
			remove_filter('post_thumbnail_html', array(__CLASS__, 'post_thumbnail_html'), 10, 5);
			if( $product->get_id() == $post_ID ) {
				$html = self::replace_variation_image($html, $product, $size, array(), $product->get_title());
			}
			add_filter('post_thumbnail_html', array(__CLASS__, 'post_thumbnail_html'), 10, 5);
		}
		return $html;
	}
    public static function wc_product_table_data_image($image, $product) {
        remove_filter('wc_product_table_data_image', array(__CLASS__, 'wc_product_table_data_image'), 10, 2);
        $image = self::replace_variation_image($image, $product, 'large', array(), $product->get_title());
        add_filter('wc_product_table_data_image', array(__CLASS__, 'wc_product_table_data_image'), 10, 2);
        return $image;
    } 
    public static function replace_variation_image($image, $product, $size, $attr, $placeholder) {
        remove_filter('woocommerce_product_get_image', array(__CLASS__, 'replace_variation_image'), 10, 5);
        if ( empty( $product ) ) return $image;

        global $berocket_variable_to_variation_list;
        if( is_array($berocket_variable_to_variation_list) && array_key_exists($product->get_id(), $berocket_variable_to_variation_list) ) {
            $parent_product = $berocket_variable_to_variation_list[$product->get_id()];
            $parent_product = array_pop($parent_product);
            $parent_product = wc_get_product($parent_product);
            $image = $parent_product->get_image($size, $attr, $placeholder);
        }
        add_filter('woocommerce_product_get_image', array(__CLASS__, 'replace_variation_image'), 10, 5);
        return $image;
    }
}
new BeRocket_AAPF_compat_woocommerce_variation_image();
