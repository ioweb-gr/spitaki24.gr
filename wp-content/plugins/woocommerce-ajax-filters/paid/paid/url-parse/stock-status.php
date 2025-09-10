<?php
if( ! class_exists('BeRocket_url_parse_page_stock_status') ) {
    class BeRocket_url_parse_page_stock_status {
        public $main_class;
        function __construct() {
            add_action('bapf_class_ready', array($this, 'init'), 10, 1);
            add_filter('bapf_uparse_func_check_attribute_name', array($this, 'name'), 100, 3);
            add_filter('bapf_uparse_func_check_attribute_values_terms', array($this, 'ids_slug'), 100, 6);
            add_filter('bapf_uparse_add_terms_to_data_each_terms', array($this, 'terms'), 100, 4);
            add_filter('bapf_widget_get_terms_for_filter', array($this, 'terms_incexc'), 100, 3);
            add_filter('bapf_uparse_generate_tax_query_each', array($this, 'tax_query'), 100, 4);
            add_filter('bapf_uparse_get_terms', array($this, 'get_terms_custom'), 100, 3);
            add_filter('bapf_uparse_generate_filter_link_each', array($this, 'generate_filter_link'), 10, 5);
        }
        public function init($BeRocket_AAPF) {
            $this->main_class = $BeRocket_AAPF;
        }
        public function name($result, $instance, $attribute_name) {
            $stock_status_taxonomy = apply_filters('bapf_uparse_stock_status_taxonomy', '_stock_status');
            if( $result === null && $attribute_name == $stock_status_taxonomy ) {
                $result = array(
                    'taxonomy' => '_stock_status',
                    'type'     => 'stock_status'
                );
            }
            return $result;
        }
        public function ids_slug($result, $instance, $values_line, $taxonomy, $filter, $args) {
            if( $result === null && isset($filter['type']) && $filter['type'] == 'stock_status' ) {
                $terms = $this->get_terms();
                $result = array();
                foreach($terms as $term) {
                    $result[$term->term_id] = $term->slug;
                }
            }
            return $result;
        }
        public function terms($result, $instance, $filter, $data) {
            if( $result === null && isset($filter['type']) && $filter['type'] == 'stock_status' ) {
                $terms = $this->get_terms();
                $result = array();
                foreach($terms as $term) {
                    if( array_search($term->term_id, $filter['val_ids']) !== FALSE ) {
                        $result[] = $term;
                    }
                }
            }
            return $result;
        }
        public function terms_incexc($terms, $br_filter, $args) {
            if( $terms === false && isset($br_filter['filter_type']) && $br_filter['filter_type'] == '_stock_status' ) {
                $terms = $this->get_terms();
            }
            return $terms;
        }
        public function tax_query($result, $instance, $filter, $data) {
            if( $result === null && isset($filter['type']) && $filter['type'] == 'stock_status' ) {
                $term = get_term_by('slug', 'outofstock', 'product_visibility');
                $result = $filter;
                $result['tax_query'] = array();
                if( ! empty($term) && ! is_a($term, 'WP_Error') ) {
                    $operator = 'OR';
                    if( isset($filter['val_arr']['op']) ) {
                        $operator = $filter['val_arr']['op'];
                        unset($filter['val_arr']['op']);
                    }
                    $status = 'none';
                    if( ! empty($filter['terms']) && is_array($filter['terms']) ) {
                        foreach($filter['terms'] as $filter_term) {
                            if($status == 'none' ) {
                                $status = $filter_term->slug;
                            } else {
                                $status = 'both';
                            }
                        }
                    }
                    if( $status == 'both' ) {
                        if($operator == 'AND') {
                            $result['tax_query']['relation'] = $operator;
                            $result['tax_query'][] = array(
                                'taxonomy'  => 'product_visibility',
                                'field'     => 'id',
                                'terms'     => array($term->term_id),
                                'operator'  => 'IN'
                            );
                            $result['tax_query'][] = array(
                                'taxonomy'  => 'product_visibility',
                                'field'     => 'id',
                                'terms'     => array($term->term_id),
                                'operator'  => 'NOT IN'
                            );
                        }
                    } else {
                        $result['tax_query']['relation'] = $operator;
                        $result['tax_query'][] = array(
                            'taxonomy'  => 'product_visibility',
                            'field'     => 'id',
                            'terms'     => array($term->term_id),
                            'operator'  => ($status == 'instock' ? 'NOT IN' : 'IN')
                        );
                    }
                }
            }
            return $result;
        }
        public function get_terms_custom($result, $instance, $args) {
            if( $result === null && ! empty($args['taxonomy']) && $args['taxonomy'] == '_stock_status' ) {
                return $this->get_terms();
            }
            return $result;
        }
        public function get_terms() {
            $terms       = array();
            array_push( $terms, (object) array( 'term_id'           => '1',
                                                'term_taxonomy_id'  => '1',
                                                'name'              => __( 'In stock', 'BeRocket_AJAX_domain' ),
                                                'slug'              => 'instock',
                                                'value'             => ( empty($br_options['slug_urls']) ? '1' : 'instock' ),
                                                'taxonomy'          => '_stock_status',
                                                'count'             => 1
            ) );
            array_push( $terms, (object) array( 'term_id'           => '2',
                                                'term_taxonomy_id'  => '2',
                                                'name'              => __( 'Out of stock', 'BeRocket_AJAX_domain' ),
                                                'slug'              => 'outofstock',
                                                'value'             => ( empty($br_options['slug_urls']) ? '2' : 'outofstock' ),
                                                'taxonomy'          => '_stock_status',
                                                'count'             => 1
            ) );
            return $terms;
        }
        function generate_filter_link($result, $instance, $filter, $data, $args = array()) {
            if( $result === null && $filter['type'] == 'stock_status' ) {
                $stock_status_taxonomy = apply_filters('bapf_uparse_stock_status_taxonomy', '_stock_status');
                $filter['taxonomy'] = $stock_status_taxonomy;
                return $instance->generate_filter_link_each_without_check($filter, $data, $args);
            }
            return $result;
        }
	}
	new BeRocket_url_parse_page_stock_status();
}