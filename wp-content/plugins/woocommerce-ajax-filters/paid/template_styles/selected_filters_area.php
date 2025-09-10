<?php
if( ! class_exists('BeRocket_AAPF_Elemets_Style_sfa_blue') ) {
    class BeRocket_AAPF_Elemets_Style_sfa_blue extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'sfa_blue',
                'template'      => 'selected_filters',
                'name'          => 'Blue',
                'file'          => __FILE__,
                'style_file'    => 'css/selected_filters_area.css',
                'script_file'   => '',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/selected_filters_area_blue.png',
                'version'       => '1.0',
                'specific'      => 'elements',
                'sort_pos'      => '1',
            );
            parent::__construct();
        }
        function filters($action = 'add') {
            parent::filters($action);
            $filter_func = 'add_filter';
            $action_func = 'add_action';
            if( $action != 'add' ) {
                $filter_func = 'remove_filter';
                $action_func = 'remove_action';
            }
            $filter_func('BeRocket_AAPF_template_full_element_content', array($this, 'template_element_full'), 10, 2);
        }
        function template_element_full($template, $berocket_query_var_title) {
            $template['template']['attributes']['class']['inline'] = 'bapf_sfa_blue';
            return $template;
        }
    }
    new BeRocket_AAPF_Elemets_Style_sfa_blue();
}


if ( ! class_exists( 'BeRocket_AAPF_Elemets_Style_sfa_min_space' ) ) {
	class BeRocket_AAPF_Elemets_Style_sfa_min_space extends BeRocket_AAPF_Template_Style {
		function __construct() {
			$this->data = array(
				'slug'        => 'sfa_min_space',
				'template'    => 'selected_filters',
				'name'        => 'Minimum space',
				'file'        => __FILE__,
				'style_file'  => 'css/selected_filters_area.css',
				'script_file' => '',
				'image'       => plugin_dir_url( __FILE__ ) . 'images/selected_filters_area_min_space.png',
				'version'     => '1.0',
				'specific'    => 'elements',
				'sort_pos'    => '1',
			);
			parent::__construct();
		}

		function filters( $action = 'add' ) {
			parent::filters( $action );
			$filter_func = 'add_filter';
			$action_func = 'add_action';
			if ( $action != 'add' ) {
				$filter_func = 'remove_filter';
				$action_func = 'remove_action';
			}
			$filter_func( 'BeRocket_AAPF_template_full_element_content', array(
				$this,
				'template_element_full'
			), 10, 2 );
		}

		function template_element_full( $template, $berocket_query_var_title ) {
			$template['template']['attributes']['class']['inline'] = 'bapf_sfa_min_space';

			return $template;
		}
	}

	new BeRocket_AAPF_Elemets_Style_sfa_min_space();
}