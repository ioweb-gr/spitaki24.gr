<?php
if ( ! class_exists('BeRocket_AAPF_Template_Style_divi_checkbox') ) {
    class BeRocket_AAPF_Template_Style_divi_checkbox extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'divi_checkbox',
                'template'      => 'checkbox',
                'name'          => 'Divi',
                'file'          => __FILE__,
                'style_file'    => 'css/divi.css',
                'script_file'   => '',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/divi_checkbox.png',
                'sort_pos'      => '950',
                'version'       => '1.0',
                'name_price'    => 'Price Ranges Divi',
                'image_price'   => plugin_dir_url( __FILE__ ) . 'images/divi_checkbox-price.png',
            );

            parent::__construct();

            add_filter('BeRocket_AAPF_template_single_item', array($this, 'products_count'), 1001, 4);
        }

        function template_single_item( $template, $term, $i, $berocket_query_var_title ) {
            $this->array_set( $template, array('attributes', 'class') );
            $template['attributes']['class'][] = 'divi_checkbox_class_item';

            return $template;
        }

        function template_full( $template, $terms, $berocket_query_var_title ) {
            $this->array_set( $template, array('template', 'attributes', 'class') );
            $template['template']['attributes']['class'][] = 'divi_checkbox_class';

            if ( in_array( $berocket_query_var_title['attribute'], array('_stock_status', '_sale') ) and isset( $template['template']['content']['filter']['content']['list']['content'] ) ) {
                foreach ( $template['template']['content']['filter']['content']['list']['content'] as $key => $element ) {
                    if ( $element['content']['checkbox']['attributes']['value'] == 2 ) {
                        unset( $template['template']['content']['filter']['content']['list']['content'][ $key ] );
                    } elseif ( ! $berocket_query_var_title['title'] ) {
                        unset( $template['template']['content']['header'] );
                    }
                }
            }

            //echo "</div></div></div>";
            //bd(1);

            return $template;
        }

        function products_count( $element, $term, $i, $berocket_query_var_title ) {
            if ( $berocket_query_var_title['show_product_count_per_attr'] and $berocket_query_var_title['new_style']['slug'] == 'divi_checkbox' ) {
                $element['content']['qty']['content'] = array( $term->count );
            }

            return $element;
        }
    }

    new BeRocket_AAPF_Template_Style_divi_checkbox();
}

if ( ! class_exists('BeRocket_AAPF_Template_Style_divi_color') ) {
    class BeRocket_AAPF_Template_Style_divi_color extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'divi_color',
                'template'      => 'checkbox',
                'name'          => 'Divi',
                'file'          => __FILE__,
                'style_file'    => 'css/divi.css',
                'script_file'   => '',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/divi_color.png',
                'specific'      => 'color',
                'sort_pos'      => '950',
                'version'       => '1.0'
            );

            parent::__construct();

            add_filter('BeRocket_AAPF_template_single_item', array($this, 'products_count'), 1001, 4);
        }

        function template_full( $template_content, $terms, $berocket_query_var_title ) {
            $this->array_set( $template_content, array('template', 'attributes', 'class') );
            $template_content['template']['attributes']['class'][] = 'divi_color_class';
            $template_content['template']['attributes']['class']['style_type'] = 'bapf_stylecolor';
            $template_content['template']['attributes']['class']['inline_color'] = 'bapf_colorinline';
            return $template_content;
        }

        function template_single_item( $template, $term, $i, $berocket_query_var_title ) {
            $this->array_set( $template, array('attributes', 'class') );
            $template['attributes']['class'][] = 'divi_color_class_item';
            if( ! empty($berocket_query_var_title['clrimg_use_attrval']) ) {
                $meta_color = $term->name;
            } else {
                $berocket_term                 = berocket_term_get_metadata($term, 'color');
                $meta_color                    = br_get_value_from_array( $berocket_term, 0, '' );
            }
            $meta_color                        = str_replace( '#', '', $meta_color );
            $template['content']['checkbox']   = BeRocket_AAPF_dynamic_data_template::create_element_arrays( $template['content']['checkbox'], array('attributes', 'style') );
            $template['content']['checkbox']['attributes']['style']['display'] = 'display:none;';
            $template['content']['label']['content'] = array(
                'color' => array(
                    'type'          => 'tag',
                    'tag'           => 'span',
                    'attributes'    => array(
                        'class'         => array(
                            'main'          => 'bapf_clr_span',
                        ),
                        'style'         => array(
                            'bg-color'      => 'background-color: #' . $meta_color . ';'
                        ),
                    ),
                    'content'       => array(
                        'span'          => array(
                            'type'          => 'tag',
                            'tag'           => 'span',
                            'attributes'    => array(
                                'class'         => array(
                                    'main'          => 'bapf_clr_span_abslt',
                                ),
                            ),
                        )
                    )
                )
            );

            return $template;
        }

        function products_count( $element, $term, $i, $berocket_query_var_title ) {
            if ( $berocket_query_var_title['show_product_count_per_attr'] and $berocket_query_var_title['new_style']['slug'] == 'divi_color' ) {
                $element['content']['qty'] = array(
                    'type'          => 'tag',
                    'tag'           => 'span',
                    'attributes'    => array(
                        'class'         => array(
                            'main'          => 'pcs',
                        ),
                    ),
                    'content'       => array( $term->count )
                );

            }

            return $element;
        }
    }

    new BeRocket_AAPF_Template_Style_divi_color();
}

if ( ! class_exists('BeRocket_AAPF_Template_Style_divi_image') ) {
    class BeRocket_AAPF_Template_Style_divi_image extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'divi_image',
                'template'      => 'checkbox',
                'name'          => 'Divi',
                'file'          => __FILE__,
                'style_file'    => '/css/divi.css',
                'script_file'   => '',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/divi_image.png',
                'version'       => '1.0',
                'specific'      => 'image',
                'sort_pos'      => '950',
            );
            parent::__construct();

            add_filter('BeRocket_AAPF_template_single_item', array($this, 'products_count'), 1001, 4);
        }

        function template_full( $template_content, $terms, $berocket_query_var_title ) {
            $this->array_set( $template_content, array('template', 'attributes', 'class') );
            $template_content['template']['attributes']['class'][]               = 'divi_image_class';
            $template_content['template']['attributes']['class']['style_type']   = 'bapf_styleimage';
            $template_content['template']['attributes']['class']['inline_color'] = 'bapf_colorinline';
            return $template_content;
        }

        function template_single_item( $template, $term, $i, $berocket_query_var_title ) {
            $this->array_set( $template, array('attributes', 'class') );
            $template['attributes']['class'][] = 'divi_image_class_item';
            $berocket_term = berocket_term_get_metadata($term, 'image');
            if( ! empty($berocket_query_var_title['clrimg_use_attrval']) ) {
                $meta_image = $term->name;
            } else {
                $berocket_term = berocket_term_get_metadata($term, 'image');
                $meta_image = br_get_value_from_array($berocket_term, 0, '');
            }
            $template['content']['checkbox'] = BeRocket_AAPF_dynamic_data_template::create_element_arrays( $template['content']['checkbox'], array('attributes', 'style') );
            $template['content']['checkbox']['attributes']['style']['display'] = 'display:none;';
            $template['content']['label']['content'] = array(
                'color' => array(
                    'type'          => 'tag',
                    'tag'           => 'span',
                    'attributes'    => array(
                        'class'         => array(
                            'main'          => 'bapf_img_span',
                        ),
                        'style'         => array(),
                    ),
                    'content'       => array(
                        'span'          => array(
                            'type'          => 'tag',
                            'tag'           => 'span',
                            'attributes'    => array(
                                'class'         => array(
                                    'main'          => 'bapf_img_span_abslt',
                                ),
                            ),
                        )
                    )
                )
            );

            if ( substr( $meta_image, 0, 3 ) == 'fa-' ) {
                $template['content']['label']['content']['color']['content']['icon'] = array(
                    'type'          => 'tag',
                    'tag'           => 'i',
                    'attributes'    => array(
                        'class'         => array(
                            'main'          => 'fa',
                            'icon'          => $meta_image
                        ),
                        'style'         => array()
                    ),
                );
            } else {
                $template['content']['label']['content']['color']['attributes']['style']['bg-color'] = 'background: url(' . $meta_image . ') no-repeat scroll 50% 50% rgba(0, 0, 0, 0);background-size: cover;';
            }

            return $template;
        }

        function products_count( $element, $term, $i, $berocket_query_var_title ) {
            if ( $berocket_query_var_title['show_product_count_per_attr'] and $berocket_query_var_title['new_style']['slug'] == 'divi_image' ) {
                $element['content']['qty'] = array(
                    'type'          => 'tag',
                    'tag'           => 'span',
                    'attributes'    => array(
                        'class'         => array(
                            'main'          => 'pcs',
                        ),
                    ),
                    'content'       => array( $term->count )
                );

            }

            return $element;
        }
    }

    new BeRocket_AAPF_Template_Style_divi_image();
}

if ( ! class_exists('BeRocket_AAPF_Template_Style_divi_slider') ) {
    class BeRocket_AAPF_Template_Style_divi_slider extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'divi_slider',
                'template'      => 'slider',
                'name'          => 'Divi',
                'file'          => __FILE__,
                'style_file'    => '/css/divi.css',
                'script_file'   => '/../js/slider.js',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/divi_slider.png',
                'version'       => '1.0',
                'sort_pos'      => '950',
            );
            parent::__construct();
        }
        function enqueue_all() {
            wp_enqueue_script( 'jquery-ui-slider' );
            BeRocket_AAPF::wp_enqueue_script( 'berocket_aapf_jquery-slider-fix');
            parent::enqueue_all();
        }

        function template_full( $template, $terms, $berocket_query_var_title ) {
            $this->array_set( $template, array('template', 'attributes', 'class') );
            $template['template']['attributes']['class'][] = 'divi_slider_class';
            return $template;
        }
    }

    new BeRocket_AAPF_Template_Style_divi_slider();
}

if ( ! class_exists('BeRocket_AAPF_Elemets_Style_divi_button') ) {
    class BeRocket_AAPF_Elemets_Style_divi_button extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'divi_button',
                'template'      => 'button',
                'name'          => 'Divi',
                'file'          => __FILE__,
                'style_file'    => '/css/divi.css',
                'script_file'   => '',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/divi_button.png',
                'version'       => '1.0',
                'specific'      => 'elements',
                'sort_pos'      => '950',
            );
            parent::__construct();
        }

        function filters( $action = 'add' ) {
            parent::filters( $action );

            $filter_func = 'add_filter';
            if ( $action != 'add' ) {
                $filter_func = 'remove_filter';
            }

            $filter_func('BeRocket_AAPF_template_full_element_content', array( $this, 'template_element_full' ), 10, 2);
        }

        function template_element_full( $template, $berocket_query_var_title ) {
            $template['template']['attributes']['class']['inline'] = 'bapf_divi_button';

            return $template;
        }
    }

    new BeRocket_AAPF_Elemets_Style_divi_button();
}

if ( ! class_exists('BeRocket_AAPF_Elemets_Style_divi_sfa') ) {
    class BeRocket_AAPF_Elemets_Style_divi_sfa extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'divi_sfa',
                'template'      => 'selected_filters',
                'name'          => 'Divi',
                'file'          => __FILE__,
                'style_file'    => '/css/divi.css',
                'script_file'   => '',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/divi_selected_filters_area.png',
                'version'       => '1.0',
                'specific'      => 'elements',
                'sort_pos'      => '950',
            );

            parent::__construct();
        }

        function filters( $action = 'add' ) {
            parent::filters( $action );

            $filter_func = 'add_filter';
            if ( $action != 'add' ) $filter_func = 'remove_filter';

            $filter_func('BeRocket_AAPF_template_full_element_content', array( $this, 'template_element_full' ), 10, 2);
        }

        function template_element_full( $template, $berocket_query_var_title ) {
            $template['template']['attributes']['class']['inline'] = 'bapf_divi_sfa';

            return $template;
        }
    }

    new BeRocket_AAPF_Elemets_Style_divi_sfa();
}