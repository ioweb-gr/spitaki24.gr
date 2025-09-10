<?php
if( ! class_exists('BeRocket_AAPF_Template_Style_datepicker') ) {
    class BeRocket_AAPF_Template_Style_datepicker extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'datepicker',
                'template'      => 'datepicker',
                'name'          => 'Datepicker',
                'file'          => __FILE__,
                'style_file'    => '/css/datepicker.css',
                'script_file'   => '/js/datepicker.js',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/datepicker.png',
                'version'       => '1.0',
                'sort_pos'      => '1',
            );
            parent::__construct();
        }
        function enqueue_all() {
            BeRocket_AAPF::wp_enqueue_style( 'jquery-ui-datepick' );
            BeRocket_AAPF::wp_enqueue_script( 'jquery-ui-datepicker' );
            BeRocket_AAPF::wp_enqueue_script( 'berocket_aapf_jquery-slider-fix');
            parent::enqueue_all();
        }
	    function template_full( $template, $terms, $berocket_query_var_title ) {
		    $this->array_set( $template, array('template', 'attributes', 'class') );
		    $template['template']['attributes']['data-template'][] = 'datepicker';

		    return $template;
	    }
    }
    new BeRocket_AAPF_Template_Style_datepicker();
}

if ( ! class_exists( 'BeRocket_AAPF_Template_Style_datepicker_dark' ) ) {
	class BeRocket_AAPF_Template_Style_datepicker_dark extends BeRocket_AAPF_Template_Style {
		function __construct() {
			$this->data = array(
				'slug'        => 'datepicker_dark',
				'template'    => 'datepicker',
				'name'        => 'Dark',
				'file'        => __FILE__,
				'style_file'  => '/css/datepicker.css',
				'script_file' => '/js/datepicker.js',
				'image'       => plugin_dir_url( __FILE__ ) . 'images/datepicker_dark.png',
				'version'     => '1.0',
				'sort_pos'    => '100',
			);
			parent::__construct();
		}

		function enqueue_all() {
			BeRocket_AAPF::wp_enqueue_style( 'jquery-ui-datepick' );
			BeRocket_AAPF::wp_enqueue_script( 'jquery-ui-datepicker' );
			BeRocket_AAPF::wp_enqueue_script( 'berocket_aapf_jquery-slider-fix' );
			parent::enqueue_all();
		}

		function template_full( $template, $terms, $berocket_query_var_title ) {
			$this->array_set( $template, array('template', 'attributes', 'class') );
			$template['template']['attributes']['class'][] = 'datepicker_dark_class';
			$template['template']['attributes']['data-template'][] = 'datepicker_dark_class';

			return $template;
		}
	}

	new BeRocket_AAPF_Template_Style_datepicker_dark();
}

if ( ! class_exists( 'BeRocket_AAPF_Template_Style_datepicker_orange' ) ) {
	class BeRocket_AAPF_Template_Style_datepicker_orange extends BeRocket_AAPF_Template_Style {
		function __construct() {
			$this->data = array(
				'slug'        => 'datepicker_orange',
				'template'    => 'datepicker',
				'name'        => 'Orange',
				'file'        => __FILE__,
				'style_file'  => '/css/datepicker.css',
				'script_file' => '/js/datepicker.js',
				'image'       => plugin_dir_url( __FILE__ ) . 'images/datepicker_orange.png',
				'version'     => '1.0',
				'sort_pos'    => '100',
			);
			parent::__construct();
		}

		function enqueue_all() {
			BeRocket_AAPF::wp_enqueue_style( 'jquery-ui-datepick' );
			BeRocket_AAPF::wp_enqueue_script( 'jquery-ui-datepicker' );
			BeRocket_AAPF::wp_enqueue_script( 'berocket_aapf_jquery-slider-fix' );
			parent::enqueue_all();
		}

		function template_full( $template, $terms, $berocket_query_var_title ) {
			$this->array_set( $template, array('template', 'attributes', 'class') );
			$template['template']['attributes']['class'][] = 'datepicker_orange_class';
			$template['template']['attributes']['data-template'][] = 'datepicker_orange_class';

			return $template;
		}
	}

	new BeRocket_AAPF_Template_Style_datepicker_orange();
}