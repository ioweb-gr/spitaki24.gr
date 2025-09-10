<?php
if( ! class_exists('BeRocket_url_parse_paid_nice_url') ) {
    class BeRocket_url_parse_paid_nice_url {
        public $main_class;
        public $default_permalink = array (
            'variable' => 'filters',
            'value'    => '/values',
            'split'    => '/',
        );
        public $default_nn_permalink = array (
            'variable' => 'filters',
            'value'    => '[values]',
            'split'    => '|',
        );
        function __construct() {
            add_action('bapf_class_ready', array($this, 'init'), 10, 1);
            add_filter('bapf_uparse_parse_get_filter_line', array($this, 'filter_line'), 100, 3);
            add_filter('bapf_uparse_regex', array($this, 'get_regex'), 100, 2);
            add_filter('bapf_uparse_parse1_filter_var', array($this, 'filter_variable'), 100, 3);
            add_filter('bapf_uparse_parse1_filter_nice_url_var', array($this, 'filter_variable'), 100, 3);
            add_filter('bapf_uparse_generate_filter_link_each_taxval_delimiters', array($this, 'taxval_delimiters'), 100, 4);
            add_filter('bapf_uparse_generate_filter_link_delimiter', array($this, 'filters_delimiter'), 100, 4);
            add_filter('bapf_uparse_remove_filters_from_link', array($this, 'remove_filters_from_link'), 100, 3);
            add_filter('bapf_uparse_add_filters_to_link', array($this, 'add_filters_to_link'), 100, 4);
            add_filter('bapf_niceurl_get_permalinks_options', array($this, 'get_permalinks_options'));
        }
        public function init($BeRocket_AAPF) {
            $this->main_class = $BeRocket_AAPF;
        }
        public function filter_variable($var, $instance, $link) {
            $permalink_data = $this->get_permalinks_options();
            if( ! empty($permalink_data['variable']) ) {
                $var = $permalink_data['variable'];
            }
            return $var;
        }
        public function get_regex($regex, $instance) {
            $options = $this->main_class->get_option();
            $permalink_data = $this->get_permalinks_options();
            $values = explode('values', $permalink_data['value']);
            if( $values > 1 ) {
                $regex['filter'] = '/(([\w_-]+)'.$this->escrex($values[0]).'(%val_sym%)'.$this->escrex($values[1]).')(?:$|'.$this->escrex($permalink_data['split']).')/u';
            } else {
                $regex['filter'] = '/(([\w_-]+)'.$this->escrex($values[0]).'(%val_sym%))(?:$|'.$this->escrex($permalink_data['split']).')/u';
            }
            return $regex;
        }
        public function escrex($line) {
            return str_replace('/', '\/', preg_quote($line));
        }
        public function taxval_delimiters($delimiter, $instance, $filter, $data) {
            $permalink_data = $this->get_permalinks_options();
            $values = explode('values', $permalink_data['value']);
            $delimiter = array(
                'before_values'  => $values[0],
                'after_values'   => $values[1],
            );
            return $delimiter;
        }
        public function filters_delimiter($delimiter, $instance, $filter, $data) {
            $permalink_data = $this->get_permalinks_options();
            if( ! empty($permalink_data['split']) ) {
                $delimiter = $permalink_data['split'];
            }
            return $delimiter;
        }
        public function filter_line($result, $instance, $link) {
            if( $result === null ) {
                $options = $this->main_class->get_option();
                if( ! empty($options['nice_urls']) ) {
                    global $wp_the_query, $wp_query, $wp_rewrite;
                    if( $link === FALSE ) {
                        $filter_line = $this->get_filters_from_query();
                    } else {
                        $filter_line = $this->get_filters_from_link($link);
                    }
                    $filter_line = str_replace('+', urlencode('+'), $filter_line);
                    $filter_line = urldecode($filter_line);
                    return $filter_line;
                }
            }
            return $result;
        }
        public function get_filters_from_query() {
            global $wp_the_query, $wp_query, $wp_rewrite;
            $permalink_variable_name = apply_filters('bapf_uparse_parse1_filter_nice_url_var', 'filters', $this, FALSE);
            $permalink_variable = $wp_the_query->get( $permalink_variable_name, '' );
            if( empty($permalink_variable) ) {
                $permalink_variable = $wp_query->get( $permalink_variable_name, '' );
            }
            $filter_line = '';
            if( ! empty($permalink_variable) ) {
                $filter_line = $this->decode_url($permalink_variable);
                $filter_line = $this->remove_pagination($filter_line);
            }
            return $filter_line;
        }
        public function get_filters_from_link($link) {
            $permalink_variable_name = apply_filters('bapf_uparse_parse1_filter_nice_url_var', 'filters', $this, $link);
            $filter_line = '';
            $link_part = explode('?', $link);
            $link_part = $link_part[0];
            if( strpos('/'.$permalink_variable_name.'/', $link_part) === false ) {
                $permalink_variable_name = urlencode($permalink_variable_name);
            }
            $link_part = explode('/'.$permalink_variable_name.'/', $link_part);
            if( count($link_part) > 1 ) {
                $filter_line = $link_part[1];
                $filter_line = $this->decode_url($filter_line);
                $filter_line = $this->remove_pagination($filter_line);
            }
            return $filter_line;
        }
        public function remove_pagination($filter_line) {
            global $wp_rewrite;
            $pagination_base = ( (! empty($wp_rewrite) && is_object($wp_rewrite) && property_exists($wp_rewrite, 'pagination_base')) ? $wp_rewrite->pagination_base : 'page' );
            if( preg_match('#\/'.$pagination_base.'\/(\d+)#', $filter_line, $page_match) ) {
                $filter_line = preg_replace( '#\/'.$pagination_base.'\/(\d+)#', '', $filter_line );
                $_GET['paged'] = $page_match[1];
                set_query_var( 'paged', $page_match[1] );
            }
            return $filter_line;
        }
        public function decode_url($link) {
            $options = $this->main_class->get_option();
            if( empty($options['seo_uri_decode']) ) {
                $link = urlencode($link);
                $link = str_replace('+', urlencode('+'), $link);
                $link = urldecode($link);
            }
            return $link;
        }
        public function get_permalinks_options() {
            $options = $this->main_class->get_option();
            $option_permalink = get_option( (empty($options['nice_urls']) ? 'berocket_nn_permalink_option' : 'berocket_permalink_option') );
            if( ! is_array($option_permalink) ) {
                $option_permalink = array();
            }
            $option_permalink = array_merge( (empty($options['nice_urls']) ? $this->default_nn_permalink : $this->default_permalink), $option_permalink);
            return $option_permalink;
        }
        public function remove_filters_from_link($result, $instance, $link) {
            if( $result === null ) {
                $options = $this->main_class->get_option();
                if( ! empty($options['nice_urls']) ) {
                    $permalink_variable_name = apply_filters('bapf_uparse_parse1_filter_nice_url_var', 'filters', $this, $link);
                    $link_part = explode('?', $link);
                    if( strpos('/'.$permalink_variable_name.'/', $link_part[0]) === false ) {
                        $permalink_variable_name = urlencode($permalink_variable_name);
                    }
                    $link_part[0] = explode('/'.$permalink_variable_name.'/', $link_part[0]);
                    $link_part[0] = $link_part[0][0];
                    $result = implode('?', $link_part);
                }
            }
            return $result;
        }
        public function add_filters_to_link($result, $instance, $link, $filters_line) {
            if( $result === null ) {
                $options = $this->main_class->get_option();
                if( ! empty($options['nice_urls']) ) {
                    $permalink_variable_name = apply_filters('bapf_uparse_parse1_filter_nice_url_var', 'filters', $this, $link);
                    $link_part = explode('?', $link);
                    if( ! empty($filters_line) ) {
                        $trailing_slash = substr($link_part[0], -1) == '/';
                        $link_part[0] .= ($trailing_slash ? '' : '/').$permalink_variable_name.'/'.$filters_line.($trailing_slash ? '/' : '');
                    }
                    $result = implode('?', $link_part);
                }
            }
            return $result;
        }
    }
    new BeRocket_url_parse_paid_nice_url();
}