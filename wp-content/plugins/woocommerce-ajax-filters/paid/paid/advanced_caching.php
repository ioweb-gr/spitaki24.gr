<?php
if( ! class_exists('BeRocket_AAPF_paid_advanced_caching') ) {
    class BeRocket_AAPF_paid_advanced_caching {
        public $plugin_name = 'ajax_filters';
        public $defaults, $cache_type, $cache_recount;
        function __construct() {
            $this->defaults = array(
                'object_cache'              => '',
                'object_cache_recount'      => '',
            );
            add_filter('brfr_data_' . $this->plugin_name, array($this, 'settings_page'), 100);
            add_filter('brfr_plugin_defaults_value_'.$this->plugin_name, array($this, 'default_values'), 50, 2);
            add_filter( 'br_get_cache', array($this, 'br_get_cache'), 10, 3 );
            add_filter( 'br_set_cache', array($this, 'br_set_cache'), 10, 5 );
            add_action('bapf_class_ready', array($this, 'init_cache'));
        }
        public function default_values($defaults, $object) {
            if( ! is_array($this->defaults) ) {
                $this->defaults = array();
            }
            if( is_array($defaults) ) {
                $defaults = array_merge($this->defaults, $defaults);
            } else {
                $defaults = $this->defaults;
            }
            return $defaults;
        }
        public function init_cache() {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $option = $BeRocket_AAPF->get_option();
            $this->cache_recount = empty($option['object_cache_recount']) ? '' : $option['object_cache_recount'];
            if(! empty($this->cache_recount) ) {
                add_filter('berocket_recount_cache_use', array($this, 'recount_caching'));
                add_filter('berocket_recount_price_cache_use', array($this, 'recount_caching'));
            }
        }
        function price_caching() {
            return true;
        }
        function recount_caching($recount) {
            if($this->cache_recount == 'full') {
                return true;
            } elseif( ! apply_filters( 'berocket_aapf_is_filtered_page_check', ! empty($_GET['filters']), 'recount_caching' ) ) {
                return true;
            }
            return $recount;
        }
        function settings_page($data) {
            $data['Advanced'] = berocket_insert_to_array(
                $data['Advanced'],
                'header_part_tools',
                array(
                    'header_object_cache' => array(
                        'section'  => 'header_part',
                        "value"    => __('Filter Cache', 'BeRocket_AJAX_domain'),
                    ),
                    'object_cache' => array(
                        "label"    => __( 'Data cache', "BeRocket_AJAX_domain" ),
                        "name"     => "object_cache",
                        "class"    => "bapf_object_cache",
                        "type"     => "selectbox",
                        "options"  => array(
                            array('value' => '', 'text' => __('Disabled', 'BeRocket_AJAX_domain')),
                            array('value' => 'wordpress', 'text' => __('WordPress Cache', 'BeRocket_AJAX_domain')),
                            array('value' => 'persistent', 'text' => __('Persistent Cache Plugins', 'BeRocket_AJAX_domain')),
                        ),
                        "value"    => '',
                    ),
                    'object_cache_recount' => array(
                        "label"     => __( 'Cache for filter recount', "BeRocket_AJAX_domain" ),
                        "name"      => "object_cache_recount",   
                        "type"      => "selectbox",
                        "tr_class"  => "bapf_object_cache_recount",
                        "options"   => array(
                            array('value' => '', 'text' => __('Disabled', 'BeRocket_AJAX_domain')),
                            array('value' => 'first', 'text' => __('Without filters', 'BeRocket_AJAX_domain')),
                            array('value' => 'full', 'text' => __('Full (can use a lot of memory)', 'BeRocket_AJAX_domain')),
                        ),
                        "value"     => '',
                    ),
                ),
                true
            );
            add_action('admin_footer', array($this, 'hide_options'));
            return $data;
        }
        function get_cache_type() {
            if( isset($this->cache_type) ) return $this->cache_type;
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $option = $BeRocket_AAPF->get_option();
            $this->cache_type = $option['object_cache'];
            return $this->cache_type;
        }
        function br_get_cache( $return, $key, $group ){
            $cache_type = $this->get_cache_type();
            $language = br_get_current_language_code();
            $group = $group.$language;
            switch($cache_type) {
                case 'wordpress':
                    $return = get_site_transient( md5($group.$key) );
                    break;
                case '':
                    $return = wp_cache_get( $key, $group );
                    break;
            }
            return $return;
        }
        function br_set_cache( $return, $key, $value, $group, $expire ){
            $cache_type = $this->get_cache_type();
            $language = br_get_current_language_code();
            $group = $group.$language;
            switch($cache_type) {
                case 'wordpress':
                    set_site_transient( md5($group.$key), $value, $expire );
                    break;
                case '':
                    wp_cache_add( $key, $value, $group, $expire );
                    break;
            }
            return $return;
        }
        function hide_options() {
            echo '<script>
            function bapf_cache_hide() {
                if( jQuery(".bapf_object_cache").val() == "" ) {
                    jQuery(".bapf_object_cache_recount").hide();
                } else {
                    jQuery(".bapf_object_cache_recount").show();
                }
            }
            bapf_cache_hide();
            jQuery(document).on("change", ".bapf_object_cache", bapf_cache_hide);
            </script>';
        }
    }
    new BeRocket_AAPF_paid_advanced_caching();
}