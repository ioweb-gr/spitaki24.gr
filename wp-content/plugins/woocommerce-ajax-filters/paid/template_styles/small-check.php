<?php
if( ! class_exists('BeRocket_AAPF_Template_Style_small_check') ) {
    class BeRocket_AAPF_Template_Style_small_check extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'small-check',
                'template'      => 'checkbox',
                'name'          => 'Small Check',
                'file'          => __FILE__,
                'style_file'    => 'css/small-check.css',
                'script_file'   => '',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/small-check.png',
                'version'       => '1.0',
                'name_price'    => 'Price Ranges Small Check',
                'image_price'   => plugin_dir_url( BeRocket_AJAX_filters_file ) . 'paid/template_styles/images/small-check-price.png',
            );
            parent::__construct();
        }
        function template_full($template, $terms, $berocket_query_var_title) {
            $this->array_set($template, array('template', 'attributes', 'class'));
            $template['template']['attributes']['class'][] = 'bapf_ckbox_smlchck';
            return $template;
        }
    }
    new BeRocket_AAPF_Template_Style_small_check();
}
