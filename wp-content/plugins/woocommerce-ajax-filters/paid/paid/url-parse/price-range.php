<?php
if( ! class_exists('BeRocket_url_parse_page_price_range') ) {
    class BeRocket_url_parse_page_price_range {
        public $main_class;
        function __construct() {
            add_action('bapf_class_ready', array($this, 'init'), 10, 1);
            add_filter('bapf_uparse_func_check_attribute_values', array($this, 'values_price'), 90, 6);
            add_filter('bapf_uparse_generate_custom_query_each', array($this, 'custom_query'), 90, 6);
            add_filter('bapf_uparse_modify_data_each_precheck', array( $this, 'modify_data' ), 90, 5 );
            add_filter('br_is_term_selected_taxonomy', array($this, 'price_range_taxonomy'));
            add_filter( 'berocket_widget_attribute_type_terms', array($this, 'widget_attribute_type_terms'), 10, 4 );
            add_filter( 'bapf_uparse_generate_filter_link_each', array($this, 'generate_filter_link'), 10, 4 );
        }
        public function init($BeRocket_AAPF) {
            $this->main_class = $BeRocket_AAPF;
            $options = $this->main_class->get_option();
            if( ! empty($options['filter_price_variation']) ) {
                add_filter('berocket_aapf_wcvariation_filtering_total_query-add_table', array($this, 'variation_product'), 1000, 4);
                add_filter('berocket_variation_cache_key', array($this, 'variation_cache'), 1000, 1);
            }
        }
        public function values_price($result, $instance, $values_line, $taxonomy, $filter, $args) {
            if( $result === null && isset($filter['type']) && $filter['type'] == 'price' && strpos($values_line, '*') !== FALSE && strpos($values_line, '_') === FALSE ) {
                $error = array(
                    'error' => new WP_Error( 'bapf_uparse', __('Incorrect data for price: ', 'BeRocket_AJAX_domain').$values_line )
                );
                $operator = '-';
                $values_regex = $instance->get_regex('values');
                preg_match_all($values_regex, $values_line, $values);
                if( count($values) > 0 && count($values[0]) > 0 ) {
                    $result_values = array();
                    $result_ids = array();
                    foreach($values[0] as $value) {
                        $value_list = explode('*', $value);
                        if( count($value_list) == 2 ) {
                            $value_list[0] = floatval($value_list[0]);
                            $value_list[1] = floatval($value_list[1]);
                            $result_values[$value] = array('from' => min($value_list), 'to' => max($value_list));
                            $result_ids[min($value_list).'*'.max($value_list)] = min($value_list).'*'.max($value_list);
                        } else {
                            $operator = $value;
                        }
                    }
                    $result = array(
                        'values'    => $result_values,
                        'value_ids' => $result_ids,
                        'operator'  => $instance->func_delimiter_to_operator($operator)
                    );
                } else { return $error; }
            }
            return $result;
        }
        public function custom_query($result, $instance, $filter, $data) {
            if( $result === null && isset($filter['type']) && $filter['type'] == 'price' && ! isset($filter['val_arr']['from']) && ! isset($filter['val_arr']['to']) ) {
                $val_arr = $filter['val_arr'];
                if( isset($val_arr['op']) ) unset($val_arr['op']);
                $custom_query_line = 'pricerange:';
                foreach($val_arr as $price_range ) {
                    if( ! isset($price_range['from']) || ! isset($price_range['to']) ) {
                        return $result;
                    }
                    $custom_query_line .= $price_range['from'].'-'.$price_range['to'].';';
                }
                $result = $filter;
                $result['custom_query'] = array($this, 'post_clauses');
                $result['custom_query_line'] = $custom_query_line;
            }
            return $result;
        }
        public function post_clauses($args, $filter) {
            return $this->add_price_to_post_clauses($args, $filter);
        }
        public function add_price_to_post_clauses($args, $filter = false) {
            global $berocket_parse_page_obj;
            if( ! empty($filter['val_arr']) && count($filter['val_arr']) > 0 ) {
                $options = $this->main_class->get_option();
                if( empty($options['filter_price_variation']) ) {
                    $args = $this->wc_price_to_post_clauses($args, $filter);
                } else {
                    $args = $this->advanced_price_to_post_clauses($args, $filter);
                }
            }
            return $args;
        }
        public function wc_price_to_post_clauses($args, $filter) {
            global $wpdb;
            if ( ! strstr( $args['join'], 'wc_product_meta_lookup' ) ) {
                $args['join'] .= " LEFT JOIN {$wpdb->wc_product_meta_lookup} as wc_product_meta_lookup ON {$wpdb->posts}.ID = wc_product_meta_lookup.product_id ";
            }
            $prices = array();
            foreach($filter['val_arr'] as $price_val) {
                if( isset($price_val['from']) && isset($price_val['to']) ) {
                    $prices[] = $wpdb->prepare(
                        'wc_product_meta_lookup.min_price >= %f AND wc_product_meta_lookup.max_price < %f',
                        ($price_val['from'] - 1),
                        $price_val['to']
                    );
                }
            }
            if( count($prices) > 0 ) {
                $args['where'] .= ' AND ('.implode(' OR ', $prices).') ';
            }
            return $args;
        }
        public function advanced_price_to_post_clauses($args, $filter) {
            $where = $this->get_advanced_where_price_query_part($filter);
            if( $where !== FALSE ) {
                $args['join'] .= $this->get_advanced_price_temp_table($where);
            }
            return $args;
        }
        public function get_advanced_where_price_query_part($filter) {
            global $wpdb;
            $prices = array();
            foreach($filter['val_arr'] as $price_val) {
                if( isset($price_val['from']) && isset($price_val['to']) ) {
                    $prices[] = $wpdb->prepare(
                        'bapf_price_lookup.min_price >= %f AND bapf_price_lookup.max_price < %f',
                        ($price_val['from'] - 1),
                        $price_val['to']
                    );
                }
            }
            $where = FALSE;
            if( count($prices) > 0 ) {
                $where = '('.implode(' OR ', $prices).')';
            }
            return $where;
        }
        public function get_advanced_price_temp_table ($where) {
            global $wpdb;
            $query_price = array(
                'select'    => "SELECT IF(bapf_price_post.post_parent = 0, bapf_price_post.ID, bapf_price_post.post_parent) as product_id from {$wpdb->posts} as bapf_price_post",
                'join'      => "JOIN {$wpdb->wc_product_meta_lookup} as bapf_price_lookup ON bapf_price_post.ID = bapf_price_lookup.product_id",
                'where'     => "WHERE (" . $where . ")"
            );
            $query_price = apply_filters('berocket_aapf_get_advanced_price_temp_table', $query_price, $where);
            $query_price = implode(' ', $query_price);
            $table = " JOIN ({$query_price}) as bapf_custom_price ON {$wpdb->posts}.ID = bapf_custom_price.product_id ";
            
            return $table;
        }
        public function modify_data($result, $instance, $value, $args, $data) {
            if( $result === null ) {
                $price_name = apply_filters('bapf_uparse_price_taxonomy', 'price');
                if( $value['taxonomy'] == $price_name && strpos($value['value'], '*') !== FALSE ) {
                    $type = $args['type'];
                    $val_arr = explode('*', $value['value']);
                    $val_arr = array('from' => $val_arr[0], 'to' => $val_arr[1]);
                    $added = false;
                    foreach($data['filters'] as $filter_i => &$filter ) {
                        if( $filter['taxonomy'] == 'bapf_price' ) {
                            $added = true;
                            $position = false;
                            if( isset($filter['val_arr']) && is_array($filter['val_arr']) ) {
                                $position = array_search($value['value'], $filter['val_arr']);
                            }
                            if( ($type == 'add' || $type == 'revert') && ! isset($filter['val_arr'][$value['value']]) ) {
                                if( ! isset($filter['val_arr']) || ! is_array($filter['val_arr']) ) {
                                    $filter['val_arr'] = array();
                                }
                                if( ! isset($filter['val_ids']) || ! is_array($filter['val_ids']) ) {
                                    $filter['val_ids'] = array();
                                }
                                $filter['val_arr'][$value['value']] = $val_arr;
                                $filter['val_ids'][$value['value']] = $value['value'];
                            } elseif( ($type == 'remove' || $type == 'revert') && isset($filter['val_arr'][$value['value']]) ) {
                                unset($filter['val_arr'][$value['value']]);
                                if( isset($filter['val_ids'][$value['value']]) ) {
                                    unset($filter['val_ids'][$value['value']]);
                                }
                                if(count($filter['val_arr']) == 0 || (count($filter['val_arr']) == 1 && ! empty($filter['val_arr']['op'])) ) {
                                    unset($data['filters'][$filter_i]);
                                }
                            }
                            $filter = $instance->back_generate($filter, $data, $args);
                        }
                    }
                    if(($type == 'revert' || $type == 'add') && ! $added) {
                        $filter_arr = array(
                            'val_arr'  => array($value['value'] => $val_arr),
                            'val_ids'  => array($value['value'] => $value['value']),
                            'attr'     => $price_name,
                            'taxonomy' => 'bapf_price',
                        );
                        $filter_arr = $instance->back_generate($filter_arr, $data, $args);
                        $data['filters'][] = $filter_arr;
                    }
                    return $data;
                }
            }
            return $result;
        }
        public function price_range_taxonomy($taxonomy) {
            if($taxonomy == 'price') {
                $taxonomy = 'bapf_price';
            }
            return $taxonomy;
        }
        public function widget_attribute_type_terms($vars, $attr_type, $attr_filter_type, $instance) {
            
            extract($instance);

            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $br_options    = $BeRocket_AAPF->get_option();

            list( $terms_error_return, $terms_ready, $terms, $type ) = $vars;

            if ( $attr_filter_type == 'attribute' ) {
                if ( (berocket_isset($type) == 'ranges' || in_array(berocket_isset($new_template), array('checkbox', 'select'))) && $attr_type == 'price' ) {
                    $terms_ready = true;
                    if( empty($ranges_label) || ! is_array($ranges_label) ) {
                        $ranges_label = array();
                    }
                    if(is_array($ranges)) {
                        $prev_range = -1;
                        foreach($ranges as $i => $range) {
                            $start = $i - 1;
                            if( $start < 0 ) {
                                $start = 0;
                            }
                            if(strlen($range) == 0) {
                                unset($ranges[$i]);
                                if( isset($ranges_label[$start]) ) {
                                    unset($ranges_label[$start]);
                                }
                                continue;
                            }
                            if(floatval($range) <= $prev_range) {
                                unset($ranges[$i]);
                                if( isset($ranges_label[$start]) ) {
                                    unset($ranges_label[$start]);
                                }
                                continue;
                            }
                            $prev_range = floatval($range);
                        }
                    }
                    $ranges = array_values($ranges);
                    $ranges_label = array_values($ranges_label);

                    if ( ! is_array($ranges) || count( $ranges ) < 2 ) {
                        $terms_error_return = 'ranges < 2';
                        $terms = $ranges;
                        return array($terms_error_return, $terms_ready, $terms, $type);
                    }
                    $terms = array();
                    $ranges[0]--;

                    $price_ranges = array();
                    for ( $i = 1; $i < count( $ranges ); $i++ ) {
                        $range = apply_filters('berocket_min_max_filter_range', array($ranges[ $i - 1 ], $ranges[ $i ]));
                        $real_range = $range;
                        if( $i == 1 ) {
                            $real_range[0]++;
                        }
                        $price_ranges[] = array('from' => $range[0], 'to' => $range[1], 'data' => FALSE, 'real_from' => $real_range[0], 'real_to' => $real_range[1], 'lable' => empty($ranges_label[$i - 1]) ? '' : $ranges_label[$i - 1] );
                    }
                    $price_ranges_data = BeRocket_AAPF_Widget_functions::get_price_ranges($price_ranges);
                    $remove_index = array();
                    $first_exist = false;
                    if( is_array($price_ranges_data) ) {
                        foreach($price_ranges as $i => &$price_range_single) {
                            foreach($price_ranges_data as $price_range_data) {
                                if($price_range_data->price_range == $price_range_single['from'].'-'.$price_range_single['to']) { 
                                    $price_range_single['data'] = $price_range_data;
                                    if( $first_exist ) {
                                        if( $price_range_data->product_count == 0 ) {
                                            $remove_index[] = $i;
                                        } else {
                                            $remove_index = array();
                                        }
                                    } else {
                                        if( $price_range_data->product_count == 0 ) {
                                            unset($price_ranges[$i]);
                                        } else {
                                            $first_exist = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    foreach($price_ranges as $i => $price_range) {
                        if( $price_range['data'] === FALSE || in_array($i, $remove_index) ) {
                            continue;
                        }
                        $range_from = intval( apply_filters( 'berocket_price_filter_widget_min_amount', apply_filters( 'woocommerce_price_filter_widget_min_amount', $price_range['from'] ), $price_range['from'] ) ) + 1;
                        if( ! empty( $show_last_to_infinity ) && (count($price_ranges) - 1) == $i ) {
                            $range_to = (empty($to_infinity_text) ? '&#8734;' : $to_infinity_text);
                            $index = 'infinity';
                        } else {
                            $range_to = intval( apply_filters( 'berocket_price_filter_widget_max_amount', apply_filters( 'woocommerce_price_filter_widget_max_amount', $price_range['to'] ), $price_range['to'] ) );
                            $index = ($i + 1);
                        }
                        $t_id       = ( $price_range['real_from'] + 1 ) . '*' . $price_range['real_to'];
                        $t_name = $this->ranges_name_generate('', $index, $range_from, $range_to, $instance);
                        $term       = array( 'term_id'  => $t_id,
                                             'slug'     => $t_id,
                                             'value'    => $t_id,
                                             'name'     => empty($price_range['lable']) ? $t_name : $price_range['lable'],
                                             'count'    => ($price_range['data'] == FALSE || ! isset($price_range['data']->product_count) ? 0 : $price_range['data']->product_count),
                                             'taxonomy' => $attribute
                        );
                        $term       = (object) $term;
                        $terms[] = $term;
                    }
                }
            }
            return array( $terms_error_return, $terms_ready, $terms, $type );
        }
        function ranges_name_generate($name, $i, $start_value, $end_value, $instance) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $br_options    = $BeRocket_AAPF->get_option();
            $range_display_type = br_get_value_from_array($instance, array('range_display_type'));
            if( $range_display_type == 'same' ) {
                $range_from = intval($start_value);
                if( $i != 1 ) {
                    $range_from = $range_from -1;
                }
                if( $i != 'infinity' ) {
                    $range_to   = intval($end_value);
                }
            } elseif( $range_display_type == 'decimal' ) {
                $range_from = intval($start_value);
                if( $i != 1 ) {
                    $range_from = $range_from -1;
                }
                if( $i != 'infinity' ) {
                    $range_to   = intval($end_value) - 0.01;
                }
            } else {
                $range_from = intval($start_value);
                if( $i != 'infinity' ) {
                    $range_to   = intval($end_value);
                }
            }
            $price_args = array();
            if(! empty($instance['number_style']) ) {
                $price_args['decimal_separator'] = br_get_value_from_array($instance, array('number_style_decimal_separate'));
                $price_args['thousand_separator'] = br_get_value_from_array($instance, array('number_style_thousand_separate'));
                $price_args['decimals'] = intval(br_get_value_from_array($instance, array('number_style_decimal_number')));
            }
            if( ! empty($instance['custom_price_ranges']) && ! empty($instance['custom_price_ranges']) ) {
                $price_args['price_format'] = '';
            }
            $range_from = $this->wc_price( $range_from, $price_args );
            if( $i == 'infinity' ) {
                $range_to = $end_value;
            } else {
                $range_to = $this->wc_price( $range_to, $price_args );
            }
            if( ! empty($instance['custom_price_ranges']) && ! empty($instance['custom_price_ranges_text']) ) {
                $args_price = apply_filters(
                    'bapf_wc_price_args',
                    array(
                        'currency'           => '',
                        'decimal_separator'  => wc_get_price_decimal_separator(),
                        'thousand_separator' => wc_get_price_thousand_separator(),
                        'decimals'           => wc_get_price_decimals(),
                        'price_format'       => get_woocommerce_price_format(),
                    )
                );
                $cur_slug = $args_price['currency'];
                if( empty($args_price['currency']) ) {
                    $cur_slug = get_woocommerce_currency();
                }
                $cur_symbol = get_woocommerce_currency_symbol($cur_slug);
                $t_name = str_replace(array('%from%', '%to%', '%cur_symbol%', '%cur_slug%'), array($range_from, $range_to, $cur_symbol, $cur_slug), $instance['custom_price_ranges_text']);
            } else {
                if($start_value == $end_value) {
                    $t_name = $range_from;
                } else {
                    $t_name = $range_from . apply_filters('bapf_price_ranges_separate', ' - ', $range_from ) . $range_to;
                }
            }
            return apply_filters('bapf_price_ranges_name_generated', $t_name, $name, $i, $start_value, $end_value, $instance, $range_from, $range_to);
        }
        function wc_price( $price, $args = array() ) {
            $args = apply_filters(
                'bapf_wc_price_args',
                wp_parse_args(
                    $args,
                    array(
                        'currency'           => '',
                        'decimal_separator'  => wc_get_price_decimal_separator(),
                        'thousand_separator' => wc_get_price_thousand_separator(),
                        'decimals'           => wc_get_price_decimals(),
                        'price_format'       => get_woocommerce_price_format(),
                    )
                )
            );

            $unformatted_price = $price;
            $negative          = $price < 0;
            $price             = apply_filters( 'bapf_raw_woocommerce_price', floatval( $negative ? $price * -1 : $price ) );
            $price             = apply_filters( 'bapf_formatted_woocommerce_price', number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] );

            if ( apply_filters( 'bapf_woocommerce_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
                $price = wc_trim_zeros( $price );
            }

            if( empty($args['price_format']) ) {
                $formatted_price = ( $negative ? '-' : '' ) . $price;
            } else {
                $formatted_price = ( $negative ? '-' : '' ) . sprintf( $args['price_format'], get_woocommerce_currency_symbol( $args['currency'] ), $price );
            }

            /**
             * Filters the string of price markup.
             *
             * @param string $return            Price HTML markup.
             * @param string $price             Formatted price.
             * @param array  $args              Pass on the args.
             * @param float  $unformatted_price Price as float to allow plugins custom formatting. Since 3.2.0.
             */
            return apply_filters( 'bapf_wc_price', $formatted_price, $price, $args, $unformatted_price );
        }
        function generate_filter_link($result, $instance, $filter, $data) {
            if( $result === null && $filter['taxonomy'] == 'bapf_price' ) {
                $price_name = apply_filters('bapf_uparse_price_taxonomy', 'price');
                $link_elements = apply_filters('bapf_uparse_generate_filter_link_each_taxval_delimiters', array(
                    'before_values'  => '[',
                    'after_values'   => ']',
                ), $instance, $filter, $data);
                $values_lines = array();
                $delimiter = '-';
                if( isset($filter['val_arr']['op']) ) {
                    $delimiter = $instance->func_operator_to_delimiter($filter['val_arr']['op']);
                }
                $values = array();
                $val_ids = $filter['val_ids'];
                sort($val_ids);
                if( ! empty($val_ids) ) {
                    foreach($val_ids as $val_id ) {
                        $values[] = $val_id;
                    }
                }
                $filter_line = apply_filters('bapf_url_parse_page_price_range_implode_values', implode($delimiter, $values), $instance, $values, $filter, $data);
                if( ! empty($filter_line) ) {
                    $values_line = $price_name . $link_elements['before_values'] . $filter_line . $link_elements['after_values'];
                    $values_lines[] = apply_filters('bapf_uparse_generate_filter_link_each_values_line', $values_line, $this, $filter, $data, $link_elements, array(
                        'taxonomy_name' => $price_name,
                        'filter_line'   => $filter_line
                    ));
                }
                return $values_lines;
            }
            return $result;
        }
        function variation_product($query, $data, $current_attributes, $current_terms) {
            global $berocket_parse_page_obj;
            $data = $berocket_parse_page_obj->get_current();
            if( ! empty($data['filters']) && count($data['filters']) > 0 ) {
                foreach($data['filters'] as $filter) {
                    if( isset($filter['type']) && $filter['type'] == 'price' && ! isset($filter['val_arr']['from']) && ! isset($filter['val_arr']['to'])) {
                        $where = $this->get_advanced_where_price_query_part($filter);
                        global $wpdb;
                        $query_price = array(
                            'select'    => "SELECT bapf_price_post.ID as product_id from {$wpdb->posts} as bapf_price_post",
                            'join'      => "JOIN {$wpdb->wc_product_meta_lookup} as bapf_price_lookup ON bapf_price_post.ID = bapf_price_lookup.product_id",
                            'where'     => "WHERE (" . $where . ") AND bapf_price_post.post_parent != 0"
                        );
                        $query_price = apply_filters('berocket_aapf_get_advanced_price_temp_table', $query_price, $where);
                        $query_price = implode(' ', $query_price);
                        $table = " LEFT JOIN ({$query_price}) as bapf_custom_price ON filtered_post.post_id = bapf_custom_price.product_id";
                        $query['subquery']['join_close_1'] .= $table;
                        $query['subquery']['select'] .= ',IF(min(bapf_custom_price.product_id) IS NULL, 1, 0) as not_in_price';
                        $query['select'] .= ',min(filtered_post.not_in_price) as not_in_price';
                        $query['having'] .= ' OR not_in_price = 1';
                    }
                }
            }
            
            return $query;
        }
        function variation_cache($key) {
            global $berocket_parse_page_obj;
            $data = $berocket_parse_page_obj->get_current();
            if( ! empty($data['filters']) && count($data['filters']) > 0 ) {
                foreach($data['filters'] as $filter) {
                    if( isset($filter['type']) && $filter['type'] == 'price' && ! isset($filter['val_arr']['from']) && ! isset($filter['val_arr']['to'])) {
                        $where = $this->get_advanced_where_price_query_part($filter);
                        $key .= md5($where);
                    }
                }
            }
            return $key;
        }
    }
    new BeRocket_url_parse_page_price_range();
}