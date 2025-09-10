<?php

class ET_Builder_Module_br_sidebar_button extends ET_Builder_Module {

	public $slug       = 'et_pb_br_sidebar_button';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => '',
		'author_uri' => '',
	);

	public function init() {
        $this->name             = __( 'BeRocket Sidebar Open Button', 'BeRocket_AJAX_domain' );
		$this->folder_name = 'et_pb_berocket_modules';
		$this->main_css_element = '%%order_class%%';
        
        $this->fields_defaults = array(
            'theme' => array('0'),
            'icon-theme' => array('0'),
            'title' => array('SHOW FILTERS'),
        );

		$this->advanced_fields = array(
			'fonts'           => array(
				'title'   => array(
					'css'          => array(
						'main'      => "{$this->main_css_element} .berocket_ajax_filters_sidebar_toggle",
						'important' => 'plugin_only',
					),
                    'hide_font' => true,
				),
			),
			'link_options'  => false,
			'visibility'    => false,
			'text'          => false,
			'transform'     => false,
			'animation'     => false,
			'background'    => false,
			'borders'       => false,
			'box_shadow'    => false,
			'button'        => false,
			'filters'       => false,
			'margin_padding'=> false,
			'max_width'     => false,
		);
	}

    function get_fields() {
        $query = new WP_Query(array('post_type' => 'br_product_filter', 'nopaging' => true, 'fields' => 'ids'));
        $posts = $query->get_posts();
        if ( is_array($posts) && count($posts) ) {
            $filter_list = array();
            foreach($posts as $post_id) {
                $filter_list[$post_id] = get_the_title($post_id) . ' (ID:' . $post_id . ')';
            }
        } else {
            $filter_list = array('0' => __('--Please create filter--', 'BeRocket_AJAX_domain'));
        }

        $fields = array(
            'theme' => array(
                'label'           => esc_html__( 'Button style', 'BeRocket_AJAX_domain' ),
                'type'            => 'select',
                'options'         => array(
                    'off' => 'Default',
                    '1' => 'Theme 1',
                    '2' => 'Theme 2',
                    '3' => 'Theme 3',
                    '4' => 'Theme 4',
                    '5' => 'Theme 5',
                    '6' => 'Theme 6',
                    '7' => 'Theme 7',
                    '8' => 'Theme 8',
                    '9' => 'Theme 9',
                    '10' => 'Theme 10',
                ),
            ),
            'icon-theme' => array(
                'label'           => esc_html__( 'Button Icon style', 'BeRocket_AJAX_domain' ),
                'type'            => 'select',
                'options'         => array(
                    'off' => 'Default',
                    '1' => 'Theme 1',
                    '2' => 'Theme 2',
                    '3' => 'Theme 3',
                    '4' => 'Theme 4',
                    '5' => 'Theme 5',
                    '6' => 'Theme 6',
                ),
            ),
            'title' => array(
                "label"             => esc_html__( 'Button Text', 'brands-for-woocommerce' ),
                'type'              => 'text',
            ),
        );

        return $fields;
    }

    function render( $atts, $content = null, $function_name = '' ) {
        $atts = $this->props;
        $atts = BAPF_Sidebar_DiviExtension::convert_on_off($atts);
        ob_start();
        if ( is_active_sidebar( 'berocket-ajax-filters' ) ) {
            do_action('braapf_sidebar_button_show', $atts);
        }
        return ob_get_clean();
    }
}

new ET_Builder_Module_br_sidebar_button;
