<?php
if( ! class_exists('BeRocket_AAPF_paid_variation_link') ) {
    class BeRocket_AAPF_paid_variation_link {
        function __construct() {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $option = $BeRocket_AAPF->get_option();
            if( ! empty($option['select_filter_variation']) && ! is_admin() ) {
                add_action( 'wp', array( $this, 'save_filters' ), 1000 );
                if( strpos($option['select_filter_variation'], 'url') !== FALSE ) {
                    add_filter( 'woocommerce_loop_product_link', array( $this, 'woocommerce_loop_product_link' ), 1, 2 );
                    add_filter( 'post_type_link', array( $this, 'post_link' ), 10, 2 );
                }
                if( strpos($option['select_filter_variation'], 'session') !== FALSE ) {
                    if(!session_id()) {
                        session_start();
                    }
                    add_action( 'wp_head', array( $this, 'wp_head' ), 100 );
                }
            }
        }
        //REPLACE LINK FOR VARIABLE PRODUCTS
        public function post_link($permalink, $post) {
            if( 'product' === $post->post_type ) {
                global $wp_query, $product;
                if( ! empty($product) && is_object($product) && method_exists($product, 'get_ID') && $product->get_ID() == $post->ID && $wp_query->get('bapf_apply') ) {
                    $permalink = $this->woocommerce_loop_product_link($permalink, $product);
                }
            }
            return $permalink;
        }
        public function woocommerce_loop_product_link($link, $product) {
            global $berocket_filters_session;
            if( is_object($product) && method_exists($product, 'is_type') && $product->is_type('variable') && ! empty($berocket_filters_session) ) {
                $filter_attribute = $this->get_attribute_for_variation_link($product, $berocket_filters_session);
                foreach($filter_attribute as $attribute_name => $attribute_val) {
                    $attribute_name = strtolower(urlencode(urlencode($attribute_name)));
                    $attribute_val = strtolower(urlencode(urlencode($attribute_val)));
                    $link = add_query_arg('attribute_'.$attribute_name, $attribute_val, $link);
                }
            }
            return $link;
        }
        public function save_filters() {
            global $berocket_filters_session;
            $berocket_filters_session = $this->get_current_terms();
        }
        public function wp_head() {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $options = $BeRocket_AAPF->get_option();
            if( session_id()) {
                if( is_product() ) {
                    if( ! empty($_SESSION['BeRocket_filters']) ) {
                        $product_id = get_the_ID();
                        $product = wc_get_product($product_id);
                        if( $product->is_type('variable') ) {
                            if( ! empty($_SESSION['BeRocket_filters']) ) {
                                $filter_attribute = $this->get_attribute_for_variation_link($product, $_SESSION['BeRocket_filters']);
                                foreach($filter_attribute as $attribute_name => $attribute_val) {
                                    if( empty($_REQUEST['attribute_'.$attribute_name]) ) {
                                        $_REQUEST['attribute_'.$attribute_name] = $attribute_val;
                                    }
                                }
                            }
                        }
                        if( ! empty($options['select_filter_variation']) && strpos($options['select_filter_variation'], 'url') !== FALSE ) {
                            unset($_SESSION['BeRocket_filters']);
                        }
                    }
                } else {
                    global $berocket_filters_session;
                    $_SESSION['BeRocket_filters'] = $berocket_filters_session;
                }
            }
        }
        public function get_attribute_for_variation_link($product, $filters) {
            $attributes = $product->get_variation_attributes();
            $filter_attribute = array();
            if( ! empty($filters) && is_array($filters) ) {
                foreach($filters as $filter) {
                    if( empty($attributes[$filter['tax']]) || ! empty($filter_attribute[$filter['tax']]) ) continue;
                    foreach($filter['val'] as $term_slug) {
                        if( in_array(strtolower(urlencode(urldecode($term_slug))), $attributes[$filter['tax']]) ) {
                            $filter_attribute[$filter['tax']] = urldecode($term_slug);
                            break;
                        }
                    }
                }
            }
            return $filter_attribute;
        }
        public function get_current_terms() {
            global $berocket_parse_page_obj;
            $data = $berocket_parse_page_obj->get_current();
            $terms = array();
            if( ! empty($data) && ! empty($data['filters']) && is_array($data['filters']) ) {
                foreach($data['filters'] as $filter) {
                    if( $filter['type'] == 'attribute' && is_array($filter['terms']) && count($filter['terms']) > 0 ) {
                        $term = array(
                            'tax' => $filter['taxonomy'],
                            'val' => array()
                        );
                        foreach($filter['terms'] as $term_data) {
                            $term['val'][] = $term_data->slug;
                        }
                        $terms[] = $term;
                    }
                }
            }
            return $terms;
        }
    }
    new BeRocket_AAPF_paid_variation_link();
}