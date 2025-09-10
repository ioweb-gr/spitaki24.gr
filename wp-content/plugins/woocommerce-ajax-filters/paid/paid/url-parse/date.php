<?php
if( ! class_exists('BeRocket_url_parse_page_date') ) {
    class BeRocket_url_parse_page_date {
        public $main_class;
        function __construct() {
            add_action('bapf_class_ready', array($this, 'init'), 10, 1);
            add_filter('bapf_uparse_func_check_attribute_name', array($this, 'name'), 100, 3);
            add_filter('bapf_uparse_func_check_attribute_values', array($this, 'values'), 100, 6);
            add_filter('bapf_uparse_query_custom_args_each', array($this, 'custom_args'), 100, 4);
            add_filter('bapf_uparse_generate_filter_link_each', array($this, 'generate_filter_link'), 100, 4);
            add_filter('BeRocket_AAPF_template_full_content', array($this, 'datepicker_selected'), 10, 4);
        }
        public function init($BeRocket_AAPF) {
            $this->main_class = $BeRocket_AAPF;
        }
        public function name($result, $instance, $attribute_name) {
            $stock_status_taxonomy = apply_filters('bapf_uparse_stock_status_taxonomy', '_date');
            if( $result === null && $attribute_name == $stock_status_taxonomy ) {
                $result = array(
                    'taxonomy' => '_date',
                    'type'     => 'date'
                );
            }
            return $result;
        }
        public function values($result, $instance, $values_line, $taxonomy, $filter, $args) {
            if( $result === null && isset($filter['type']) && $filter['type'] == 'date' ) {
                $error = array(
                    'error' => new WP_Error( 'bapf_uparse', __('Incorrect data for date: ', 'BeRocket_AJAX_domain').$values_line )
                );
                $values = explode('_', $values_line);
                if( count($values) == 2 && strlen($values[0]) == 8 && strlen($values[1]) == 8 && $values[0] <= $values[1] ) {
                    $from_date = substr($values[0], 4, 2).'/'.substr($values[0], 6, 2).'/'.substr($values[0], 0, 4);
                    $to_date = substr($values[1], 4, 2).'/'.substr($values[1], 6, 2).'/'.substr($values[1], 0, 4);
                    $from = date('Y-m-d 00:00:00', strtotime($from_date));
                    $to = date('Y-m-d 23:59:59', strtotime($to_date));
                    $result = array(
                        'values'    => array('from' => $from, 'to' => $to),
                        'operator'  => $instance->func_delimiter_to_operator('_')
                    );
                } else { return $error; }
            }
            return $result;
        }
        public function custom_args($result, $instance, $filter, $query_vars) {
            if( $result === null && isset($filter['type']) && $filter['type'] == 'date' && ! empty($filter['val_arr']['from']) && ! empty($filter['val_arr']['to']) ) {
                $result = array(
                    'date_query' => array(
                        'after'  => $filter['val_arr']['from'],
                        'before' => $filter['val_arr']['to'],
                    )
                );
            }
            return $result;
        }
        public function generate_filter_link($result, $instance, $filter, $data) {
            if( $filter['type'] == 'date' && isset($filter['val_arr']['from']) && isset($filter['val_arr']['to']) ) {
                $stock_status_taxonomy = apply_filters('bapf_uparse_stock_status_taxonomy', '_date');
                $link_elements = apply_filters('bapf_uparse_generate_filter_link_each_taxval_delimiters', array(
                    'before_values'  => '[',
                    'after_values'   => ']',
                ), $this, $filter, $data);
                $values_line = $this->generate_filter_link_val_arr($filter['val_arr'], $filter, $instance);
                if( ! empty($values_line) ) {
                    $values_line = $stock_status_taxonomy . $link_elements['before_values'] . $values_line . $link_elements['after_values'];
                    return array(apply_filters('bapf_uparse_generate_filter_link_each_values_line', $values_line, $this, $filter, $data, $link_elements, array(
                        'taxonomy_name' => $stock_status_taxonomy,
                        'filter_line'   => $values_line
                    )));
                }
            }
            return $result;
        }
        public function generate_filter_link_val_arr($val_arr, $filter, $instance) {
            $filter_line = '';
            if( isset($val_arr['from']) && isset($val_arr['to']) ) {
                $delimiter = '_';
                if( isset($val_arr['op']) ) {
                    $delimiter = $instance->func_operator_to_delimiter($val_arr['op']);
                    unset($val_arr['op']);
                }
                $from = new DateTime($val_arr['from']);
                $to = new DateTime($val_arr['to']);
                $filter_line = $from->format('Ymd') . $delimiter . $to->format('Ymd');
            }
            return $filter_line;
        }
        function datepicker_selected($template_content, $terms, $berocket_query_var_title) {
            if( in_array($berocket_query_var_title['new_template'], array('datepicker')) ) {
                foreach($terms as $term){break;}
                global $berocket_parse_page_obj;
                $data = $berocket_parse_page_obj->get_current();
                foreach($data['filters'] as $filter) {
                    if ( ($filter['type'] == 'date' || (! empty($filter['val_arr']['op']) && $filter['val_arr']['op'] == 'SLIDER')) && ($term->taxonomy == $filter['taxonomy'] || $term->taxonomy == $filter['attr']) ) {
                        $from = new DateTime($filter['val_arr']['from']);
                        $to   = new DateTime($filter['val_arr']['to']);
                        $template_content['template']['content']['filter']['content']['datepicker_all']['attributes']['data-start'] = $from->format('Ymd');
                        $template_content['template']['content']['filter']['content']['datepicker_all']['attributes']['data-end']   = $to->format('Ymd');
                    }
                }
            }
            return $template_content;
        }
	}
	new BeRocket_url_parse_page_date();
}