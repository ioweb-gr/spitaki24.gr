<?php

class BAPF_Sidebar_DiviExtension extends DiviExtension {
	public $gettext_domain = 'braapf-sidebar-extension';
	public $name = 'braapf-sidebar-extension';
	public $version = '1.0.0';
    public $props = array();
	public function __construct( $name = 'braapf-sidebar-extension', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );

		parent::__construct( $name, $args );
        add_action('wp_ajax_brapf_sidebar_button', array($this, 'sidebar_button'));
	}
    public function sidebar_button() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die();
        }
        $atts = berocket_sanitize_array($_POST);
        $atts = self::convert_on_off($atts);
        do_action('braapf_sidebar_button_show', $atts);
        wp_die();
    }
	public function wp_hook_enqueue_scripts() {
		if ( $this->_debug ) {
			$this->_enqueue_debug_bundles();
		} else {
			$this->_enqueue_bundles();
		}

		if ( et_core_is_fb_enabled() && ! et_builder_bfb_enabled() ) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            add_action( 'wp_enqueue_scripts', array( $BeRocket_AAPF, 'include_all_scripts' ) );
            BeRocket_AAPF::require_all_scripts();
            do_action('br_footer_script');
            BeRocket_AAPF::wp_enqueue_style('berocket_aapf_widget-themes');
			$this->_enqueue_backend_styles();
		}

		// Normalize the extension name to get actual script name. For example from 'divi-custom-modules' to `DiviCustomModules`.
		$extension_name = str_replace( ' ', '', ucwords( str_replace( '-', ' ', $this->name ) ) );

		// Enqueue frontend bundle's data.
		if ( ! empty( $this->_frontend_js_data ) ) {
			wp_localize_script( "{$this->name}-frontend-bundle", "{$extension_name}FrontendData", $this->_frontend_js_data );
		}

		// Enqueue builder bundle's data.
		if ( et_core_is_fb_enabled() && ! empty( $this->_builder_js_data ) ) {
			wp_localize_script( "{$this->name}-builder-bundle", "{$extension_name}BuilderData", $this->_builder_js_data );
		}
	}
    public static function convert_on_off($atts) {
        foreach($atts as &$attr) {
            if( $attr === 'on' || $attr === 'off' ) {
                $attr = ( $attr === 'on' ? TRUE : FALSE );
            }
        }
        return $atts;
    }
}

new BAPF_Sidebar_DiviExtension;
