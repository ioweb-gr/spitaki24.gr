<?php
if( ! class_exists('BeRocket_url_parse_page_sliders') ) {
    class BeRocket_url_parse_page_sliders {
        public $main_class;
        function __construct() {
            add_action('bapf_class_ready', array($this, 'init'), 10, 1);
            add_filter('bapf_uparse_func_check_attribute_values_modify', array($this, 'values'), 100, 6);
            add_filter('bapf_uparse_func_generate_tq_single', array($this, 'generate_tq_single'), 100, 6);
            add_filter('BeRocket_AAPF_template_full_content', array($this, 'slider_selected'), 10, 4);
        }
        public function init($BeRocket_AAPF) {
            $this->main_class = $BeRocket_AAPF;
        }
        public function values($result, $instance, $values_line, $taxonomy, $filter, $args) {
            if( isset($result['error']) && (empty($result['value_ids']) || $result['operator'] == 'SLIDER') ) {
                $values = explode('_', $values_line);
                if(count($values) == 2) {
                    $result['values']   = $values;
                    $result['operator'] = 'SLIDER';
                    unset($result['error']);
                }
            }
            $is_slider = $result['operator'] == 'SLIDER' && ! empty($filter['type']) && in_array($filter['type'], array('taxonomy', 'attribute'));
            if( apply_filters('bapf_uparse_paid_attr_slider_apply', $is_slider, $result, $instance, $values_line, $taxonomy, $filter, $args) ) {
                $taxonomy = apply_filters('bapf_uparse_paid_attr_slider_taxonomy', $filter['taxonomy'], $filter) ;
                $from = urldecode( $result['values'][0] );
                $to   = urldecode( $result['values'][1] );
                $terms_slug_id = $instance->func_get_terms_slug_id($filter['taxonomy']);
                $terms_slug_id = array_flip($terms_slug_id);
                $all_terms_name = array();
                $all_terms_slug = array();
                $braapf_sliders = apply_filters('braapf_slider_data', array());
                if( is_array($braapf_sliders) && isset($braapf_sliders[$taxonomy]) && isset($braapf_sliders[$taxonomy]['get_terms_args']) && isset($braapf_sliders[$taxonomy]['get_terms_advanced']) ) {
                    $get_terms_args = $braapf_sliders[$taxonomy]['get_terms_args'];
                    $get_terms_advanced = $braapf_sliders[$taxonomy]['get_terms_advanced'];
                    $get_terms_advanced['disable_recount'] = true;
                    $full_terms = berocket_aapf_get_terms( $get_terms_args, $get_terms_advanced );
                    $taxonomy_terms = wp_list_pluck($full_terms, 'slug', 'term_id');
                    $search = array_values($taxonomy_terms);
                    if( in_array(br_get_value_from_array($braapf_sliders, array($taxonomy, 'get_terms_advanced', 'orderby')), array('name_numeric_full', 'slug_num') ) ) {
                        $from = intval($from);
                        $to = intval($to);
                        $search_new = array();
                        foreach($full_terms as $search_term) {
                            if( in_array(br_get_value_from_array($braapf_sliders, array($taxonomy, 'get_terms_advanced', 'orderby')), array('name_numeric_full') ) ) {
                                $name_num = floatval($search_term->name);
                            } else {
                                $name_num = floatval($search_term->slug);
                            }
                            if( $name_num >= $from && $name_num <= $to ) {
                                $search_new[] = $search_term->slug;
                            }
                        }
                        $search = $search_new;
                    } else {
                        $search2 = $search;
                        foreach($search2 as &$search_val) {
                            $search_val = urldecode($search_val);
                        }
                        if( isset($search_val) ) {
                            unset($search_val);
                        }
                        $start_terms    = array_search( $from, $search2 );
                        $end_terms      = array_search( $to, $search2 );
                        $search = array_slice( $search, $start_terms, ( $end_terms - $start_terms + 1 ) );
                    }
                } else {
                    $terms = $instance->get_terms( array('taxonomy' => $taxonomy, 'hide_empty' => false) );
                    $wc_order_by = wc_attribute_orderby( $taxonomy );
                    BeRocket_AAPF_Widget_functions::sort_terms( $terms, array(
                        "wc_order_by"     => $wc_order_by,
                        "order_values_by" => '',
                        "filter_type"     => 'attribute',
                        "order_values_type"=> SORT_ASC
                    ) );
                    $is_numeric = true;
                    $is_with_string = false;
                    if( is_wp_error ( $all_terms_name ) ) {
                        BeRocket_updater::$error_log[] = $all_terms_name->errors;
                    }
                    if( ! is_numeric($from) || ! is_numeric($to) ) {
                        $is_with_string = true;
                    }
                    foreach ( $terms as $term ) {
                        if( ! is_numeric( substr( $term->name[0], 0, 1 ) ) ) {
                            $is_numeric = false;
                        }
                        if( ! is_numeric( $term->name ) ) {
                            $is_with_string = true;
                        }
                        array_push( $all_terms_name, $term->slug );
                        array_push( $all_terms_slug, $term->name );
                    }
                    if( $is_numeric ) {
                        array_multisort( $all_terms_slug, SORT_NUMERIC, $all_terms_name, $all_terms_slug );
                    } else {
                        //array_multisort( $all_terms_name, $all_terms_name, $all_terms_slug );
                    }
                    $taxonomy_terms = $instance->get_terms(array('fields' => 'id=>slug', 'taxonomy' => $taxonomy));
                    if( $is_with_string ) {
                        $start_terms    = array_search( $from, $all_terms_name );
                        $end_terms      = array_search( $to, $all_terms_name );
                        $all_terms_name = array_slice( $all_terms_name, $start_terms, ( $end_terms - $start_terms + 1 ) );
                        $search = $all_terms_name;
                    } else {
                        $start_terms = false;
                        $end_terms = false;
                        $previous_pos = false;
                        $search = array();
                        foreach($all_terms_slug as $term_pos => $term) {
                            if( $term >= $from && $start_terms === false ) {
                                $start_terms = $term_pos;
                            }
                            if( $end_terms === false ) {
                                if( $term > $to ) {
                                    if( $previous_pos !== false ) {
                                        $end_terms = $previous_pos;
                                    }
                                } elseif( $term == $to ) {
                                    $end_terms = $term_pos;
                                }
                            }
                            $previous_pos = $term_pos;
                        }
                        if( $start_terms > $end_terms ) {
                            $search = array();
                        } elseif( $from > $to ) {
                            $search = array();
                        } else {
                            $search = array_slice( $all_terms_name, $start_terms, ( $end_terms - $start_terms + 1 ) );
                        }
                    }
                }
                $value_ids = array();
                if( ! empty($search) && is_array($search) ) {
                    foreach($search as $serach_slug) {
                        if( isset($terms_slug_id[$serach_slug]) ) {
                            $value_ids[$serach_slug] = $terms_slug_id[$serach_slug];
                        }
                    }
                }
                $result = array(
                    'values'    => array('from' => $from, 'to' => $to),
                    'value_ids' => $value_ids,
                    'operator'  => $instance->func_delimiter_to_operator('_')
                );
            }
            /*if( $result === null && isset($filter['type']) && $filter['type'] == 'price' ) {
                $error = array(
                    'error' => new WP_Error( 'bapf_uparse', __('Incorrect data for price: ', 'BeRocket_AJAX_domain').$values_line )
                );
                $values = explode('_', $values_line);
                if( count($values) == 2 ) {
                    $values[0] = floatval($values[0]);
                    $values[1] = floatval($values[1]);
                    if( $values[0] >= 0 && $values[1] >= 0 && $values[0] <= $values[1] ) {
                        $result = array(
                            'values'    => array('from' => $values[0], 'to' => $values[1]),
                            'operator'  => $instance->func_delimiter_to_operator('_')
                        );
                    } else { return $error; }
                } else { return $error; }
            }*/
            return $result;
        }
        public function generate_tq_single($result, $instance, $val_arr, $filter) {
            if($result === null && ! empty($filter['val_arr']['op']) && $filter['val_arr']['op'] == 'SLIDER' ) {
                $term_ids = array();
                foreach($filter['terms'] as $term) {
                    $term_ids[] = $term->term_id;
                }
                if( empty($term_ids) ) $term_ids = array(0);
                $result = array(
                    'taxonomy'  => $filter['taxonomy'],
                    'field'     => 'id',
                    'terms'     => $term_ids,
                    'operator'  => 'IN'
                );
            }
            return $result;
        }
        function slider_selected($template_content, $terms, $berocket_query_var_title) {
            if( in_array($berocket_query_var_title['new_template'], array('slider', 'new_slider')) ) {
                foreach($terms as $term){break;}
                if( count($terms) > 1 ) {
                    global $berocket_parse_page_obj;
                    $filter_data = $berocket_parse_page_obj->get_current();
                    foreach($filter_data['filters'] as $filter) {
                        if(( $filter['type'] == $term->taxonomy || $filter['taxonomy'] == $term->taxonomy ) && ! empty($filter['val_arr']['op']) && $filter['val_arr']['op'] == 'SLIDER') {
                            $terms_numeric = array_values($terms);
                            foreach($terms_numeric as $position => $term) {
                                if( berocket_isset($filter['val_arr']['from']) == urldecode($term->value) ) {
                                    $template_content['template']['content']['filter']['content']['slider_all']['content']['slider']['attributes']['data-start'] = $position;
                                }
                                if( berocket_isset($filter['val_arr']['to']) == urldecode($term->value) ) {
                                    $template_content['template']['content']['filter']['content']['slider_all']['content']['slider']['attributes']['data-end'] = $position;
                                }
                            }
                            break;
                        }
                    }
                }
            }
            return $template_content;
        }
    }
    new BeRocket_url_parse_page_sliders();
}