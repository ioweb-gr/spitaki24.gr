<?php
if ( ! class_exists('BeRocket_AAPF_Template_Style_square_color') ) {
    bapf_template_styles_preview();
    class BeRocket_AAPF_Template_Style_square_color extends BeRocket_AAPF_styles_preview_color {
        function __construct() {
            parent::__construct();
            $this->data['slug']         = 'square_color';
            $this->data['name']         = 'Color Square';
            $this->data['style_file']   = '/css/color_square.css';
            $this->data['script_file']  = '';
            $this->data['image']        = plugin_dir_url( __FILE__ ) . 'images/color_square.png';
            $this->data['file']         = __FILE__;
        }
        function template_full_custom($template_content, $terms, $berocket_query_var_title) {
            $this->array_set( $template_content, array('template', 'attributes', 'class') );
            $template_content['template']['attributes']['class']['img_square'] = 'bapf_clr_square';
            return $template_content;
        }
        function template_single_item_custom($template, $term, $i, $berocket_query_var_title) {
            return $template;
        }
    }

    new BeRocket_AAPF_Template_Style_square_color();
}
if ( ! class_exists('BeRocket_AAPF_Template_Style_checkbox_color') ) {
    bapf_template_styles_preview();
    class BeRocket_AAPF_Template_Style_checkbox_color extends BeRocket_AAPF_styles_preview_color {
        function __construct() {
            parent::__construct();
            $this->data['slug']         = 'checkbox_color';
            $this->data['name']         = 'Color with Checkbox';
            $this->data['style_file']   = '';
            $this->data['script_file']  = '';
            $this->data['image']        = plugin_dir_url( __FILE__ ) . 'images/color_checkbox.png';
            $this->data['file']         = __FILE__;
        }
        function template_full_custom($template_content, $terms, $berocket_query_var_title) {
            $this->array_set( $template_content, array('template', 'attributes', 'class') );
            $template_content['template']['attributes']['class']['img_square'] = 'bapf_clr_checkbox';
            return $template_content;
        }
        function template_single_item_custom($template, $term, $i, $berocket_query_var_title) {
            if( isset($template['content']['checkbox']['attributes']['style']['display']) ) {
                unset($template['content']['checkbox']['attributes']['style']['display']);
            }
            return $template;
        }
    }

    new BeRocket_AAPF_Template_Style_checkbox_color();
}