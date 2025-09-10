<?php
class BeRocket_AAPF_compat_woocommerce_variation_price {
    function __construct() {
        add_filter('woocommerce_get_price_html', array(__CLASS__, 'replace_variation_price'), 10, 2);
        include_once('woocommerce-variation-functions.php');
    }
    public static function replace_variation_price($price, $product) {
        if ( empty( $product ) ) return $price;

        global $berocket_variable_to_variation_list;
        if( is_array($berocket_variable_to_variation_list) && array_key_exists($product->get_id(), $berocket_variable_to_variation_list) ) {
            remove_filter('woocommerce_get_price_html', array(__CLASS__, 'replace_variation_price'), 10, 2);
            $parent_products = $berocket_variable_to_variation_list[$product->get_id()];
            $min = $max = false;
            $parent_product_o = false;
            foreach ( $parent_products as $parent_product ) {
                $parent_product_o = wc_get_product( $parent_product );
                if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
                    $price = wc_get_price_including_tax($parent_product_o);
                } else {
                    $price = wc_get_price_excluding_tax($parent_product_o);
                }

                if ( $min === false || $min > $price ) $min = $price;
                if ( $max === false || $max < $price ) $max = $price;
            }

            if ( $min == $max ) {
                if( count($parent_products) == 1 && $parent_product_o !== false ) {
                    $price = $parent_product_o->get_price_html();
                } else {
                    $price = wc_price( $max );
                }
            } else {
                $price = wc_format_price_range( $min, $max );
            }
            add_filter('woocommerce_get_price_html', array(__CLASS__, 'replace_variation_price'), 10, 2);
        }
        return $price;
    }
}
new BeRocket_AAPF_compat_woocommerce_variation_price();
