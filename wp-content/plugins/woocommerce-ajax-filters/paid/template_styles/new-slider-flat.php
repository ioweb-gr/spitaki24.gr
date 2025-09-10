<?php
if( ! class_exists('BeRocket_AAPF_Template_Style_bapf_slider_flat') ) {
    class BeRocket_AAPF_Template_Style_bapf_slider_flat extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'bapf_slider_flat',
                'template'      => 'new_slider',
                'name'          => 'New Slider Flat',
                'file'          => __FILE__,
                'style_file'    => '/../css/ion.rangeSlider.min.css',
                'script_file'   => '/../js/ion.rangeSlider.min.js',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/bapf-slider-flat.png',
                'version'       => '1.0',
                'sort_pos'      => '900',
            );
            parent::__construct();
            $this->data['sort_pos'] = '900';
        }
        function template_full($template, $terms, $berocket_query_var_title) {
            $template['template']['content']['filter']['content']['slider_all']['content']['slider']['attributes']['data-skin'] = 'bapf-flat';
            return $template;
        }
        function enqueue_all() {
            parent::enqueue_all();
            if( file_exists(dirname($this->data['file']).'/css/new-slider-bapfflat.css') ) {
                BeRocket_AAPF::wp_enqueue_style( 'BeRocket_AAPF_script-add-'.sanitize_title('/css/new-slider-bapfflat.css'), plugins_url( '/css/new-slider-bapfflat.css', $this->data['file'] ) );
            }
            if( file_exists(dirname($this->data['file']).'/../js/newSlider.js') ) {
                BeRocket_AAPF::wp_enqueue_script( 'BeRocket_AAPF_script-add-'.sanitize_title('/js/newSlider.js'), plugins_url( '/../js/newSlider.js', $this->data['file'] ), array('jquery'), $this->data['version'], true );
            }
        }
    }
    new BeRocket_AAPF_Template_Style_bapf_slider_flat();
}