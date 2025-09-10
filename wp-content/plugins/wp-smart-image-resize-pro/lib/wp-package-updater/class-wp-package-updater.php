<?php

/**
 * WP Package Updater
 * Plugins and themes update library to enable with WP Plugin Update Server
 * (Edited)
 * @author Alexandre Froger
 * @version 1.4.0
 * @see https://github.com/froger-me/wp-package-updater
 * @copyright Alexandre Froger - https://www.froger.me
 */
if ( ! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('WP_Package_Updater_SIR')) {

    class WP_Package_Updater_SIR
    {

        const VERSION = '1.0.3';

        private $license_server_url;
        private $package_slug;
        private $update_server_url;
        private $package_path;
        private $package_url;
        private $update_checker;
        private $type = 'Plugin';
        private $package_id;
        private $use_license = true;

        public function __construct(
            $update_server_url,
            $package_file_path,
            $package_path,
            $package_slug
        ) {
            $this->package_path = trailingslashit($package_path);
            $this->package_id        = plugin_basename($package_file_path);
            $this->update_server_url = trailingslashit($update_server_url) . 'wppus-update-api/';
            $this->package_slug      = $package_slug;

            if ( ! class_exists('Puc_v4_Factory') ) {
                require $this->package_path . 'lib/plugin-update-checker/plugin-update-checker.php';
            }

            $metadata_url = trailingslashit($this->update_server_url) . '?action=get_metadata&package_id=';
            $metadata_url .= rawurlencode($this->package_slug);

            $this->update_checker = Puc_v4_Factory::buildUpdateChecker($metadata_url, $package_file_path, $this->package_slug);

            

            $this->package_url = plugin_dir_url($package_file_path);

            $this->update_checker->addQueryArgFilter(array($this, 'filter_update_checks'));

            if ($this->use_license) {
                
                $this->license_server_url = trailingslashit($update_server_url) . 'wppus-license-api/';
                $this->update_checker->addResultFilter(array($this, 'set_license_error_notice_content'));
             
                add_action('wp_ajax_wppu_' . $this->package_id . '_activate_license', array($this, 'activate_license'),10, 0);
                add_action('wp_ajax_wppu_' . $this->package_id . '_deactivate_license', array($this, 'deactivate_license'), 10, 0);
                add_action('admin_enqueue_scripts', array($this, 'add_admin_scripts'), 99, 1);
                add_action('admin_notices', array($this, 'show_license_error_notice'), 10, 0);
                add_action('init', array($this, 'load_textdomain'), 10, 0);
            }
        }

        public function load_textdomain()
        {
            $i10n_path = trailingslashit(basename($this->package_path)) . 'lib/wp-update-migrate/languages';
            load_plugin_textdomain('wp-package-updater', false, $i10n_path);
        }


        public function add_admin_scripts($hook)
        {   
            $condition = strpos( $hook, 'wp-smart-image-resize' ) !== false;
            $condition = $condition && ! wp_script_is('wp-package-updater-script-sir');
            
            $js_relative_path = 'lib/wp-package-updater/js/main.js';

            if ( $condition ) {
                $ver_js = filemtime( trailingslashit($this->package_path) . $js_relative_path);
                $params = array(
                    'action_prefix' => 'wppu_' . $this->package_id,
                    'ajax_url'      => admin_url('admin-ajax.php'),
                    'package_slug'=>  $this->package_slug,
                    'nonce'=> wp_create_nonce($this->package_id)
                );

                wp_enqueue_script('wp-package-updater-script-sir',
                 trailingslashit($this->package_url). $js_relative_path,
                 array('jquery'),
                 $ver_js,
                true);
                     

                wp_localize_script('wp-package-updater-script-sir', 'WP_Package_Updater_SIR', $params);
            }
        }

        public function filter_update_checks($query_args)
        {

            if ($this->use_license) {
                $license           = $this->get_current_license_key();
                $license_signature = $this->get_current_license_signature();

                if ($license) {
                    $query_args[ 'update_license_key' ]       = rawurlencode($license);
                    $query_args[ 'update_license_signature' ] = rawurlencode($license_signature);
                }
            }

            $query_args[ 'update_type' ] = $this->type;

            return $query_args;
        }

        public function activate_license()
        {
            $license_data = $this->do_query_license('activate');

            if( is_wp_error( $license_data ) ){
                wp_send_json_error($license_data, 400);
            }

            if ( isset( $license_data->package_slug, $license_data->license_key ) ) {
                update_option('license_key_' . $license_data->package_slug, $license_data->license_key);
            }

            if ( isset( $license_data->license_signature ) ) {
                update_option('license_signature_' . $license_data->package_slug, $license_data->license_signature);
            } else {
                delete_option('license_signature_' . $license_data->package_slug);
            }

            delete_option('wppu_' . $this->package_slug . '_license_error');

            $license_key = $this->hidden_license($license_data->license_key);
            
            wp_send_json_success(['license_key' => $license_key, 'message'=> 'Congratulations! This site is now receiving automatic updates.']);
        }
    
        public function deactivate_license()
        {
            $license_data = $this->do_query_license('deactivate');

            if(is_wp_error($license_data)){
                wp_send_json_error($license_data, 400);
            }
            if (isset($license_data->package_slug, $license_data->license_key)) {
                delete_option('license_key_' . $license_data->package_slug);

                if (isset($license_data->license_signature)) {
                    delete_option('license_signature_' . $license_data->package_slug);
                } else {
                    delete_option('license_signature_' . $license_data->package_slug);
                }
            } else {
                    delete_option('license_signature_' . $this->package_slug);
                    delete_option('license_key_' . $this->package_slug);
            }

            $license_data->license_key = '';

            wp_send_json_success(['license_key' => '', 'message' => 'License key deactivated from this site successfully.']);
        }

        public function set_license_error_notice_content($package_info, $result)
        {

            if (isset($package_info->license_error) && ! empty($package_info->license_error)) {

               $license_data = $this->handle_license_errors($package_info->license_error);

                 update_option('wppu_' . $this->package_slug . '_license_error',
                    $package_info->name . ': ' . $license_data->message);
            } else {
                delete_option('wppu_' . $this->package_slug . '_license_error');
            }

            return $package_info;
        }

        public function show_license_error_notice()
        {
            $error = get_option('wppu_' . $this->package_slug . '_license_error');

            if ($error) {
                $class = 'license-error-' . $this->package_slug . ' notice notice-error is-dismissible';
                printf('<div class="%1$s"><p>%2$s</p></div>', $class, $error); // @codingStandardsIgnoreLine
            }
        }

        protected function do_query_license($query_type)
        {

            check_ajax_referer($this->package_id, 'nonce');

            // Get package slug.
            
            if( $query_type === 'activate' ){
                $license_key = trim(sanitize_text_field(filter_input(INPUT_POST, 'license_key')));
            }else{
                $license_key = $this->get_current_license_key();

                // We'll attempt to use the submitted license key in case the current one was deleted for some reason.
                if( empty( $license_key ) ){
                    $license_key = trim(sanitize_text_field(filter_input(INPUT_POST, 'license_key')));
                }
            }

            // License key not provided. 
            if ( empty( $license_key ) ) {
                $error = new WP_Error('License', 'A license key is required.');
                wp_send_json_error($error, 400);
            }

            // Built query.
            $api_params = array(
                'action'          => $query_type,
                'license_key'     => $license_key,
                'allowed_domains' => $this->get_current_domain(),
                'package_slug'    => rawurlencode($this->package_slug),
            );


            $query    = esc_url_raw(add_query_arg($api_params, $this->license_server_url));

            $response = wp_remote_get($query, array( 'timeout'   => 30));
            
            // Unknown error.
            if (is_wp_error($response)) {
                return new WP_Error('License', $response->get_error_message());
            }

            $license_data = json_decode(wp_remote_retrieve_body($response));
            
            // Malformed response error.
            if (JSON_ERROR_NONE !== json_last_error()) {
                return new WP_Error('License', 'Unexpected Error! The query to retrieve the license data returned a malformed response.');
            }

            if( isset($license_data->result) && $license_data->result === 'error' ){

                $license_data->message = $this->normalize_error_message($query_type, $license_data);
            
                return new WP_Error('License', $license_data->message);
            }
          
            return $license_data;
        }

        protected function normalize_error_message($query_type, $data){

            switch($data->error_code){
                case 40:
                    if( $query_type === 'activate'){
                        // TODO: Switch to deactivate form. 
                        return __('The license is already in use for this domain.', 'wp-package-updater');
                    }
                    else{
                        // TODO: Switch to activate form.
                        return  __('The license is already inactive for this domain.', 'wp-package-updater');
                    }
                case 60:
                    return __('The provided license key is invalid. Please use another license key.', 'wp-package-updater');
                    
                case 110:
                   return  __('The license has reached the maximum number of activations and cannot be activated for this domain.', 'wp-package-updater');
                default: 
                    return $data->message;
            }
            
        }
       
        protected function handle_license_errors($license_data, $query_type = null)
        {
            $license_data->clear_key = false;
            if ('activate' === $query_type) {

                if (isset($license_data->allowed_domains)) {
                    $license_data->message = __('The license is already in use for this domain.', 'wp-package-updater');
                } elseif (isset($license_data->max_allowed_domains)) {
                    $license_data->clear_key = true;
                    $license_data->message   = __('The license has reached the maximum number of activations and cannot be activated for this domain.',
                        'wp-package-updater');
                }
            } elseif ('deactivate' === $query_type) {

                if (isset($license_data->allowed_domains)) {
                    $license_data->clear_key = true;
                    $license_data->message   = __('The license is already inactive for this domain.',
                        'wp-package-updater');
                }
            }

            if (
                isset($license_data->status) &&
                'expired' === $license_data->status
            ) {
                if (isset($license_data->date_expiry)) {
                    $license_data->message = sprintf(
                    // translators: the license expiry date
                        __('The license expired on %s and needs to be renewed to be updated.', 'wp-package-updater'),
                        date_i18n(get_option('date_format'), $license_data->date_expiry)
                    );
                } else {
                    $license_data->message = __('The license expired and needs to be renewed to be updated.',
                        'wp-package-updater');
                }
            } elseif (
                isset($license_data->status) &&
                'blocked' === $license_data->status
            ) {
                $license_data->message = __('The license is blocked and cannot be updated anymore. Please use another license key.',
                    'wp-package-updater');
            } elseif (
                isset($license_data->status) &&
                'pending' === $license_data->status
            ) {
                $license_data->clear_key = true;
                $license_data->message   = __('The license has not been activated and its status is stil pending. Please try again or use another license key.',
                    'wp-package-updater');
            } elseif (
                isset($license_data->status) &&
                'invalid' === $license_data->status
            ) {
                $license_data->clear_key = true;
                $license_data->message   = __('The provided license key is invalid. Please use another license key.',
                    'wp-package-updater');
            } elseif (isset($license_data->license_key)) {
                $license_data->clear_key = true;
                $license_data->message   = __('The provided license key does not appear to be valid. Please use another license key.',
                    'wp-package-updater');
            } elseif (1 === count((array)$license_data)) {

                if ('Plugin' === $this->type) {
                    $license_data->message = 'An active license is required to update the plugin. Please provide a valid license key in ' . 
                    ( class_exists('woocommerce') ? 'WooCommerce' : 'Settings' )
                    . ' > Smart Image Resize > Manage License.';
                } else {
                    $license_data->message = __('An active license is required to update the theme. Please provide a valid license key in Appearence > Theme License.',
                        'wp-package-updater');
                }
            } elseif ( ! isset($license_data->message) || empty($license_data->message)) {
                $license_data->clear_key = true;

                if ('Plugin' === $this->type) {
                    $license_data->message = __('An unexpected error has occured. Please try again. If the problem persists, please contact the author of the plugin.',
                        'wp-package-updater');
                } else {
                    $license_data->message = __('An unexpected error has occured. Please try again. If the problem persists, please contact the author of the theme.',
                        'wp-package-updater');
                }
            }

            return $license_data;
        }

        /**
         * Return the activation form html.
         * 
         * @return string
         */
        public function show_license_form()
        {
            $license_key  = $this->hidden_license($this->get_current_license_key());
            $package_id   = $this->package_id;
            $package_slug = $this->package_slug;
            $license_error = $this->get_current_license_error_message();

            $license_status = '';
            if(!empty($license_key) && empty($license_error)){
                $license_status = 'active';
            }
            else if (!empty($license_key) && !empty($license_error) && strpos($license_error, 'expired') !== false) {
                $license_status = 'expired';
            }
            else if (!empty($license_key) && !empty($license_error) && strpos($license_error, 'invalid') !== false) {
                $license_status = 'invalid';
            }

            include_once $this->package_path . 'lib/wp-package-updater/templates/license-form.php';

        }
        
        public function get_current_license_error_message(){
            return get_option('wppu_' . $this->package_slug . '_license_error');
        }

        public function __pb(){
            return $this->use_license && strpos($this->get_current_license_error_message(), 'blocked') !== false;
        }

        /**
         * Return the active license key on the client side.
         */
        private function get_current_license_key(){
            return get_option('license_key_' . $this->package_slug);
        }

        private function get_current_license_signature(){
            return get_option('license_signature_' . $this->package_slug);
        }

        protected static function is_plugin_file($absolute_path)
        {
            $plugin_dir    = wp_normalize_path(WP_PLUGIN_DIR);
            $mu_plugin_dir = wp_normalize_path(WPMU_PLUGIN_DIR);

            if ((0 === strpos($absolute_path, $plugin_dir)) || (0 === strpos($absolute_path, $mu_plugin_dir))) {

                return true;
            }

            if ( ! is_file($absolute_path)) {
                return false;
            }

            if (function_exists('get_file_data')) {
                $headers = get_file_data($absolute_path, array('Name' => 'Plugin Name'), 'plugin');

                return ! empty($headers[ 'Name' ]);
            }

            return false;
        }

        /**
         * Returns current website host.
         * 
         * @return string
         */
        protected function get_current_domain()
        {
                $url = esc_url( network_home_url() );
            
                if( ! parse_url( $url, PHP_URL_SCHEME ) ) {
                    $url = 'http://' . $url ;
                }
                
                $defaults = [ 'host' => 'localhost', 'path' => '' ];

                $url_parts = wp_parse_args( parse_url( $url ), $defaults );
                
                return $url_parts[ 'host' ]  . untrailingslashit( $url_parts['path'] );
        }
       

    /**
     * Asterisk license key.
     * @param string $license
     * 
     * @return string
    */
    private function hidden_license( $license ) {
            $key_length = strlen( $license );
            return str_pad(substr( $license, 0, (int) ($key_length / 2) ), $key_length, '*');
    }
}
}

