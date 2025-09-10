<?php
if( ! class_exists('BeRocket_AAPF_Template_Style_checkbox_square') ) {
    class BeRocket_AAPF_Template_Style_checkbox_square extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'checkbox_square',
                'template'      => 'checkbox',
                'name'          => 'Checkbox Square',
                'file'          => __FILE__,
                'style_file'    => '/css/ckbox_square.css',
                'script_file'   => '',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/ckbox_square.png',
                'version'       => '1.0',
                'sort_pos'      => '1',
                'name_price'    => 'Price Ranges Square',
                'image_price'   => plugin_dir_url( BeRocket_AJAX_filters_file ) . 'paid/template_styles/images/ckbox_square.png',
            );
            parent::__construct();
            add_filter('BeRocket_AAPF_template_single_item', array($this, 'products_count'), 1001, 4);
        }
        function template_full($template, $terms, $berocket_query_var_title) {
            $template = parent::template_full($template, $terms, $berocket_query_var_title);
            $template['template']['attributes']['class'][] = 'bapf_cksquare';
            if( $berocket_query_var_title['filter_type'] == 'attribute' && $berocket_query_var_title['attribute'] == 'price' ) {
                $template['template']['attributes']['class'][] = 'bapf_cksquareprice';
            }
            return $template;
        }

        function products_count( $element, $term, $i, $berocket_query_var_title ) {
            if ( $berocket_query_var_title['show_product_count_per_attr'] and $berocket_query_var_title['new_style']['slug'] == $this->data['slug'] ) {
                $element['content']['qty']['content'] = array( $term->count );
            }

            return $element;
        }
    }
    new BeRocket_AAPF_Template_Style_checkbox_square();
}