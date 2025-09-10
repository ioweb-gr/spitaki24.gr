<?php
if( ! class_exists('BeRocket_AAPF_Template_Style_rating_stars_inline') ) {
    class BeRocket_AAPF_Template_Style_rating_stars_inline extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'rating_stars_inline',
                'template'      => 'checkbox',
                'name'          => 'Rating Stars Inline',
                'file'          => __FILE__,
                'style_file'    => '/css/rating_stars_inline.css',
                'script_file'   => '/js/rating_stars_inline.js',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/rating_stars_inline.png',
                'version'       => '1.0',
                'sort_pos'      => '1',
                'name_price'    => 'Rating Stars Inline',
                'image_price'   => plugin_dir_url( BeRocket_AJAX_filters_file ) . 'paid/template_styles/images/rating_stars_inline.png',
            );
            parent::__construct();
            add_filter('BeRocket_AAPF_template_single_item', array($this, 'products_count'), 1001, 4);
            add_filter('BeRocket_AAPF_template_full_content', array($this, 'full_content'), 100001, 4);
            add_filter('berocket_aapf_get_terms_args', array($this, 'get_terms_args'), 100001, 3);
            add_filter('berocket_aapf_get_terms_additional', array($this, 'get_terms_additional'), 100001, 4);
        }
        function get_terms_args($get_terms_args, $instance, $args) {
            if ( $instance['new_style']['slug'] == $this->data['slug'] ) {
                $get_terms_args['hide_empty'] = false;
            }
            return $get_terms_args;
        }
        function get_terms_additional($get_terms_advanced, $instance, $args, $get_terms_args) {
            if ( $instance['new_style']['slug'] == $this->data['slug'] ) {
                $get_terms_advanced['disable_hide_empty'] = true;
            }
            return $get_terms_advanced;
        }
        function full_content($template, $terms, $berocket_query_var_title) {
            if ( $berocket_query_var_title['new_style']['slug'] == $this->data['slug'] && count($terms) > 0 ) {
                foreach($template['template']['content']['filter']['content']['list']['content'] as $i => &$element) {
                    if( isset($element['attributes']['class']['hiden']) ) {
                        unset($element['attributes']['class']['hiden']);
                    }
                }
                if( isset($template['template']['content']['filter']['content']['show_hide']) ) {
                    unset($template['template']['content']['filter']['content']['show_hide']);
                }
            }
            return $template;
        }
        function template_full($template, $terms, $berocket_query_var_title) {
            if( count($terms) > 0 ) {
                $order_asc = true;
                if( $terms[0]->slug == 'rated-1' ) {
                    $template['template']['attributes']['class'][] = 'bapf_rtnstrs_asc';
                } else {
                    $template['template']['attributes']['class'][] = 'bapf_rtnstrs_desc';
                    $order_asc = false;
                }
                $checked = false;
                foreach($template['template']['content']['filter']['content']['list']['content'] as $i => $element) {
                    if( ! empty($element['content']['checkbox']['attributes']['checked']) ) {
                        $checked = $i;
                    }
                }
                if( $checked !== false ) {
                    foreach($template['template']['content']['filter']['content']['list']['content'] as $i => &$element) {
                        if( $order_asc && $i < $checked ) {
                            $element['content']['label']['content']['name'] = '<i class="fa fa-star"></i>';
                            $element['content']['checkbox']['attributes']['checked'] = 'checked';
                        } elseif( ! $order_asc && $i > $checked ) {
                            $element['content']['label']['content']['name'] = '<i class="fa fa-star"></i>';
                            $element['content']['checkbox']['attributes']['checked'] = 'checked';
                        }
                    }
                }
            }
            $template = parent::template_full($template, $terms, $berocket_query_var_title);
            $template['template']['attributes']['class'][] = 'bapf_rtnstrs';
            return $template;
        }

        function products_count( $element, $term, $i, $berocket_query_var_title ) {
            if ( $berocket_query_var_title['new_style']['slug'] == $this->data['slug'] ) {
                if( ! empty($element['content']['checkbox']['attributes']['checked']) ) {
                    $element['content']['label']['content']['name'] = '<i class="fa fa-star"></i>';
                } else {
                    $element['content']['label']['content']['name'] = '<i class="fa fa-star-o"></i>';
                }
                if( $berocket_query_var_title['show_product_count_per_attr'] ) {
                    $element['content']['qty']['content'] = array( $term->count );
                }
                if( isset($element['attributes']['class']['hiden']) ) {
                    unset($element['attributes']['class']['hiden']);
                }
            }

            return $element;
        }
    }
    new BeRocket_AAPF_Template_Style_rating_stars_inline();
}