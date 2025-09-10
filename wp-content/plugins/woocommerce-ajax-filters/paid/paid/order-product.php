<?php
class BeRocket_AAPF_order_products_filter {
    function __construct() {
        add_filter( 'berocket_widget_attribute_type_terms', array($this, 'widget_attribute_type_terms'), 10, 4 );
        add_filter('berocket_aapf_seo_meta_filtered_term_continue', array($this, 'seo_meta_filtered_term_continue'), 5, 2);
        add_filter('bapf_uparse_get_terms', array($this, 'get_terms_custom'), 5, 3);
        add_filter('br_is_term_selected_checked', array($this, 'is_checked'), 10, 4);
        add_filter('berocket_widget_widget_type_array', array($this, 'add_sort_by'), 10, 1);
        add_filter('brapf_filter_instance', array($this, 'fix_some_settings'), 10, 1);
    }
    function fix_some_settings($instance) {
        if( ! empty($instance['widget_type']) && $instance['widget_type'] == 'sortby_field' ) {
            $instance['single_selection'] = 1;
            $instance['show_product_count_per_attr'] = 0;
            $default_orderby = wc_get_loop_prop( 'is_search' ) ? 'relevance' : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', '' ) );
            $catalog_orderby_options = $this->get_order_values();
            if( empty($default_orderby) ) {
                if( isset($catalog_orderby_options['menu_order']) ) {
                    $default_orderby = 'menu_order';
                } else {
                    $default_orderby = array_shift(array_keys($catalog_orderby_options));
                }
            }
            $instance['select_first_element_text'] = $catalog_orderby_options[$default_orderby];
        }
        return $instance;
    }
    function add_sort_by($widget_types) {
        $widget_types['sortby_field'] = __('Sort By Field', 'BeRocket_AJAX_domain');
        return $widget_types;
    }
    function is_checked($is_checked, $term_taxonomy, $term) {
        if( $term_taxonomy == 'orderby' ) {
            if( ! empty($_GET['orderby']) && $_GET['orderby'] == $term->slug ) {
                $is_checked = true;
            }
        }
        return $is_checked;
    }
    function widget_attribute_type_terms($vars, $attr_type, $attr_filter_type, $instance) {
        extract($instance);

        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $br_options    = $BeRocket_AAPF->get_option();

        list( $terms_error_return, $terms_ready, $terms, $type ) = $vars;
        if ( $widget_type == 'sortby_field' ) {
            $terms_ready = true;
            $terms = $this->get_terms();
            if( $instance['new_template'] == 'select' ) {
                $default_orderby = wc_get_loop_prop( 'is_search' ) ? 'relevance' : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', '' ) );
                foreach($terms as $i => $term) {
                    if( $term->slug == $default_orderby ) {
                        unset($terms[$i]);
                        break;
                    }
                }
            }
        }

        return array( $terms_error_return, $terms_ready, $terms, $type );
    }
    public function seo_meta_filtered_term_continue($continue, $term_parsed) {
        if( in_array($term_parsed[0], array('products_order')) ) {
            $continue = true;
        }
        return $continue;
    }
    public function get_terms_custom($result, $instance, $args) {
        if( $result === null && ! empty($args['taxonomy']) && $args['taxonomy'] == 'orderby' ) {
            $terms = $this->get_terms();
            return $terms;
        }
        return $result;
    }
    public function get_order_values() {
        $show_default_orderby    = 'menu_order' === apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
        $catalog_orderby_options = apply_filters(
            'woocommerce_catalog_orderby',
            array(
                'menu_order' => __( 'Default sorting', 'woocommerce' ),
                'popularity' => __( 'Sort by popularity', 'woocommerce' ),
                'rating'     => __( 'Sort by average rating', 'woocommerce' ),
                'date'       => __( 'Sort by latest', 'woocommerce' ),
                'price'      => __( 'Sort by price: low to high', 'woocommerce' ),
                'price-desc' => __( 'Sort by price: high to low', 'woocommerce' ),
            )
        );
        if ( wc_get_loop_prop( 'is_search' ) ) {
			$catalog_orderby_options = array_merge( array( 'relevance' => __( 'Relevance', 'woocommerce' ) ), $catalog_orderby_options );

			unset( $catalog_orderby_options['menu_order'] );
		}

		if ( ! $show_default_orderby ) {
			unset( $catalog_orderby_options['menu_order'] );
		}
        return $catalog_orderby_options;
    }
    public function get_terms() {
        $terms       = array();
        $catalog_orderby_options = $this->get_order_values();
        foreach($catalog_orderby_options as $slug => $name) {
            array_push( $terms, (object) array( 'term_id'           => $slug,
                                                'term_taxonomy_id'  => $slug,
                                                'name'              => $name,
                                                'slug'              => $slug,
                                                'value'             => $slug,
                                                'taxonomy'          => 'orderby',
                                                'count'             => 1
            ) );
        }
        return $terms;
    }
}
new BeRocket_AAPF_order_products_filter();