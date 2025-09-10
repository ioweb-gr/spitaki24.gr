<?php
if( ! class_exists('BeRocket_AAPF_Template_Style_tag_cloud') ) {
    class BeRocket_AAPF_Template_Style_tag_cloud extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'tag_cloud',
                'template'      => 'checkbox',
                'name'          => 'Tag Cloud',
                'file'          => __FILE__,
                'style_file'    => 'css/tag_cloud.css',
                'script_file'   => '',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/tag_cloud.png',
                'version'       => '1.0',
                'name_price'    => 'Price Ranges Cloud',
                'image_price'   => plugin_dir_url( __FILE__ ) . 'images/tag_cloud-price.png',
            );
            parent::__construct();
        }
        function template_full($template, $terms, $berocket_query_var_title) {
            $min_count = $max_count = false;
            foreach($terms as $i => $term) {
                if( $term->count < $min_count || $min_count === false ) {
                    $min_count = $term->count;
                }
                if( $term->count > $max_count || $max_count === false ) {
                    $max_count = $term->count;
                }
            }
            $max_count -= $min_count;
            if( $max_count <= 0 ) {
                $max_count = 1;
            }
            foreach($terms as $i => $term) {
                $percentage = ($term->count - $min_count) / $max_count;
                
                $font_size = intval(10 + 24 * $percentage);
                $template['template']['content']['filter']['content']['list']['content']['element_'.$i]['attributes']['class']['font-size'] = 'bapf_tag_size_'.$font_size;
            }
            $this->array_set($template, array('template', 'attributes', 'class'));
            $template['template']['attributes']['class']['tag_cloud'] = 'bapf_tag_cloud';
            return $template;
        }
    }
    new BeRocket_AAPF_Template_Style_tag_cloud();
}
