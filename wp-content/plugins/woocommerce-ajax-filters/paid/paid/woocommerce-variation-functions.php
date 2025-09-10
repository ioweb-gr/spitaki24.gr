<?php
if( ! class_exists('BeRocket_AAPF_compat_woocommerce_variation_functions') ) {
    class BeRocket_AAPF_compat_woocommerce_variation_functions {
        function __construct() {
            add_filter('bapf_uparse_parse_line_modify', array(__CLASS__, 'save_data'), 900000, 2);
            add_filter('woocommerce_variation_price_custom_query', array(__CLASS__, 'price_query'), 10, 4);
            add_filter('woocommerce_before_product_object_save', array(__CLASS__, 'save_meta_box'), 10, 2);
        }
        public static function save_meta_box($product, $data_store) {
            if( 'variable' == $product->get_type() ) {
                $variations = $product->get_children();
                if( ! empty($variations) && is_array($variations) ) {
                    $changes = $product->get_changes();
                    if( ! empty($changes['attributes']) && is_array($changes['attributes']) ) {
                        $attributes = array();
                        foreach($changes['attributes'] as $attribute_name => $attribute) {
                            if( ! empty($attribute) && method_exists($attribute, 'get_variation') && ! $attribute->get_variation() ) {
                                $attributes[] = 'attribute_'.$attribute_name;
                            }
                        }
                        if( count($attributes) > 0 ) {
                            global $wpdb;
                            $sql = "DELETE FROM {$wpdb->postmeta} WHERE post_id IN (" . implode(',', $variations) . ") AND meta_key IN ('" . implode("','", $attributes) . "')";
                            $wpdb->query($sql);
                        }
                    }
                }
            }
        }
        public static function save_data($data, $link = false) {
            global $berocket_variable_to_variation_list;
            if( $link == false && ! isset($berocket_variable_to_variation_list) ) {
                global $wpdb;
                $custom_query = self::get_query_from_data($data);
                if( empty($custom_query) ) {
                    return $data;
                }
                $variable_products = $wpdb->get_results( $custom_query, ARRAY_N );
                $berocket_variable_to_variation_list = array();
                if( is_array($variable_products) ) {
                    foreach($variable_products as $variable_product) {
                        if( is_array($variable_product) && count($variable_product) >= 2 ) {
                            if( ! isset($berocket_variable_to_variation_list[$variable_product[1]]) || ! is_array($berocket_variable_to_variation_list[$variable_product[1]]) ) {
                                $berocket_variable_to_variation_list[$variable_product[1]] = array();
                            }
                            $berocket_variable_to_variation_list[$variable_product[1]][] = $variable_product[0];
                        }
                    }
                }
            }
            return $data;
        }
        public static function get_query_from_data($data) {
            global $wpdb;
            $custom_query = '';
            if( ! empty($data) && ! empty($data['filters']) && is_array($data['filters']) ) {
                $current_terms = array();
                $current_attributes = array();
                foreach($data['filters'] as $filter) {
                    if( $filter['type'] == 'attribute' && isset($filter['terms']) && is_array($filter['terms']) ) {
                        $current_attributes[] = sanitize_title('attribute_' . $filter['taxonomy']);
                        foreach($filter['terms'] as $term) {
                            $current_terms[] = urlencode(urldecode($term->slug));
                        }
                    }
                }
                if( count($current_terms) ) {
                    $current_terms = array_unique($current_terms);
                    $current_attributes = array_unique($current_attributes);
                    $current_terms = implode('", "', $current_terms);
                    $current_attributes = implode('", "', $current_attributes);
                    $custom_query = sprintf( '
                        SELECT filtered_post.var_id, filtered_post.ID FROM
                            (SELECT %1$s.id as var_id, %1$s.post_parent as ID, COUNT(%1$s.id) as meta_count FROM %1$s
                            INNER JOIN %2$s AS pf1 ON (%1$s.ID = pf1.post_id)
                            WHERE %1$s.post_type = "product_variation"
                            AND %1$s.post_status != "trash"
                            '.(empty($current_attributes) ? '' : 'AND pf1.meta_key IN ("%3$s") AND pf1.meta_value IN ("%4$s")').'
                            GROUP BY %1$s.id) as filtered_post
                            INNER JOIN (SELECT ID, MAX(meta_count) as max_meta_count FROM (
                                SELECT %1$s.id as var_id, %1$s.post_parent as ID, COUNT(%1$s.id) as meta_count FROM %1$s
                                INNER JOIN %2$s AS pf1 ON (%1$s.ID = pf1.post_id)
                                WHERE %1$s.post_type = "product_variation"
                                '.(empty($current_attributes) ? '' : 'AND pf1.meta_key IN ("%3$s") AND pf1.meta_value IN ("%4$s")').'
                                GROUP BY %1$s.id
                            ) as max_filtered_post GROUP BY ID
                        ) as max_filtered_post ON max_filtered_post.ID = filtered_post.ID AND max_filtered_post.max_meta_count = filtered_post.meta_count
                    ', $wpdb->posts, $wpdb->postmeta, $current_attributes, $current_terms );
                    $custom_query = apply_filters( 'woocommerce_variation_price_custom_query', $custom_query, $current_attributes, $current_terms, $data);
                }
            }
            return $custom_query;
        }
        public static function price_query($custom_query, $current_attributes, $current_terms, $data) {
            $price_ranges = false;
            $price = false;
            foreach($data['filters'] as $filter) {
                if( $filter['type'] == 'price' ) {
                    if( isset($filter['val_arr']['from']) && isset($filter['val_arr']['to']) ) {
                        $price = array('from' => floatval($filter['val_arr']['from']), 'to' => floatval($filter['val_arr']['to']));
                    } elseif( is_array($filter['val_arr']) ) {
                        $price_ranges = array();
                        foreach($filter['val_arr'] as $val_arr) {
                            if( is_array($val_arr) && isset($val_arr['from']) && isset($val_arr['to']) ) {
                                $price_ranges[] = array('from' => floatval($val_arr['from']), 'to' => floatval($val_arr['to']));
                            }
                        }
                        if( count($price_ranges) == 0 ) {
                            $price_ranges = false;
                        }
                    }
                }
            }
            if ( $price_ranges != false || $price != false ) {
                global $wpdb;
                $custom_query .= " JOIN {$wpdb->postmeta} as br_price ON filtered_post.var_id = br_price.post_id 
                AND br_price.meta_key = '".apply_filters('berocket_price_filter_meta_key', '_price', 'variation_functions_79')."'";
                if( $price != false ) {
                    $custom_query .= " AND (br_price.meta_value BETWEEN {$price['from']} AND {$price['to']})";
                }
                if( $price_ranges != false ) {
                    $price_ranges_query = array();
                    foreach ( $price_ranges as $range ) {
                        $price_ranges_query[] = "( br_price.meta_value BETWEEN {$range['from']} AND {$range['to']} )";
                    }
                    $price_ranges_query = implode(' OR ', $price_ranges_query);
                    $custom_query .= ' AND (' . $price_ranges_query . ')';
                }
            }
            return $custom_query;
        }
    }
    new BeRocket_AAPF_compat_woocommerce_variation_functions();
}
