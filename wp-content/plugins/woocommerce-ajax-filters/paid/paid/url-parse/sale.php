<?php
if( ! class_exists('BeRocket_url_parse_page_sale') ) {
    class BeRocket_url_parse_page_sale {
        public $main_class;
        function __construct() {
            add_action('bapf_class_ready', array($this, 'init'), 10, 1);
            add_filter('bapf_uparse_func_check_attribute_name', array($this, 'name'), 100, 3);
            add_filter('bapf_uparse_func_check_attribute_values_terms', array($this, 'ids_slug'), 100, 6);
            add_filter('bapf_uparse_add_terms_to_data_each_terms', array($this, 'terms'), 100, 4);
            add_filter('bapf_widget_get_terms_for_filter', array($this, 'terms_incexc'), 100, 3);
            add_filter('bapf_uparse_generate_custom_query_each', array($this, 'custom_query'), 100, 4);
            add_filter('bapf_uparse_get_terms', array($this, 'get_terms_custom'), 100, 3);
            add_filter( 'bapf_uparse_generate_filter_link_each', array($this, 'generate_filter_link'), 10, 5 );
        }
        public function init($BeRocket_AAPF) {
            $this->main_class = $BeRocket_AAPF;
        }
        public function name($result, $instance, $attribute_name) {
            $stock_status_taxonomy = apply_filters('bapf_uparse_stock_status_taxonomy', '_sale');
            if( $result === null && $attribute_name == $stock_status_taxonomy ) {
                $result = array(
                    'taxonomy' => '_sale',
                    'type'     => 'sale'
                );
            }
            return $result;
        }
        public function ids_slug($result, $instance, $values_line, $taxonomy, $filter, $args) {
            if( $result === null && isset($filter['type']) && $filter['type'] == 'sale' ) {
                $terms = $this->get_terms();
                $result = array();
                foreach($terms as $term) {
                    $result[$term->term_id] = $term->slug;
                }
            }
            return $result;
        }
        public function terms($result, $instance, $filter, $data) {
            if( $result === null && isset($filter['type']) && $filter['type'] == 'sale' ) {
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
            if( $terms === false && isset($br_filter['filter_type']) && $br_filter['filter_type'] == '_sale' ) {
                $terms = $this->get_terms();
            }
            return $terms;
        }
        public function custom_query($result, $instance, $filter, $data) {
            if( $result === null && isset($filter['type']) && $filter['type'] == 'sale' ) {
                $result = $filter;
                $result['custom_query'] = array($this, 'post_clauses');
                $result['custom_query_line'] = 'sale:'.$filter['val'];
            }
            return $result;
        }
        public function post_clauses($args, $filter) {
            return $this->add_sale_to_post_clauses($args, $filter);
        }
        public function add_sale_to_post_clauses($args, $filter = false) {
            global $wpdb;
            $status = 'none';
            foreach($filter['terms'] as $filter_term) {
                if($status == 'none' ) {
                    $status = $filter_term->slug;
                } else {
                    $status = 'both';
                }
            }
            if( $status != 'none' && $status != 'both' ) {
                $sale_products = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
                $args['where'] .= " AND {$wpdb->posts}.ID ".($status == 'sale' ? 'IN' : 'NOT IN').' ('.implode(',', $sale_products).')' ;
            }
            return $args;
        }
        public function get_terms_custom($result, $instance, $args) {
            if( $result === null && ! empty($args['taxonomy']) && $args['taxonomy'] == '_sale' ) {
                return $this->get_terms();
            }
            return $result;
        }
        public function get_terms() {
            $terms       = array();
            array_push( $terms, (object) array( 'term_id'           => '1',
                                                'term_taxonomy_id'  => '1',
                                                'name'              => __( 'On sale', 'BeRocket_AJAX_domain' ),
                                                'slug'              => 'sale',
                                                'value'             => ( empty($br_options['slug_urls']) ? '1' : 'sale' ),
                                                'taxonomy'          => '_sale',
                                                'count'             => 1
            ) );
            array_push( $terms, (object) array( 'term_id'           => '2',
                                                'term_taxonomy_id'  => '2',
                                                'name'              => __( 'Not on sale', 'BeRocket_AJAX_domain' ),
                                                'slug'              => 'notsale',
                                                'value'             => ( empty($br_options['slug_urls']) ? '2' : 'notsale' ),
                                                'taxonomy'          => '_sale',
                                                'count'             => 1
            ) );
            return $terms;
        }
        function generate_filter_link($result, $instance, $filter, $data, $args = array()) {
            if( $result === null && $filter['type'] == 'sale' ) {
                return $instance->generate_filter_link_each_without_check($filter, $data, $args);
            }
            return $result;
        }
	}
	new BeRocket_url_parse_page_sale();
}