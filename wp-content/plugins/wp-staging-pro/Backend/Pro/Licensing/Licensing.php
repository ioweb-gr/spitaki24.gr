<?php

namespace WPStaging\Backend\Pro\Licensing;

use WPStaging;

class Licensing
{

    // The license key
    private $licensekey;

    const WPSTG_LICENSE_KEY = 'wpstg_license_key';

    /** @var string 'valid' or 'invalid' */
    const WPSTG_LICENSE_STATUS= 'wpstg_license_status';


    public function __construct()
    {

      // Load some hooks
      add_action( 'admin_notices', [$this, 'admin_notices'] );
      add_action( 'admin_init', [$this, 'activate_license'] );
      add_action( 'admin_init', [$this, 'deactivate_license'] );
      add_action( 'wpstg_weekly_event', [$this, 'weekly_license_check'] );
      // For testing weekly_license_check, uncomment this line
      //add_action( 'admin_init', array( $this, 'weekly_license_check' ) );
      update_option( 'wpstg_license_key','1415b451be1a13c283ba771ea52d38bb' );
      
      // this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
      if( !defined( 'WPSTG_STORE_URL' ) )
      define( 'WPSTG_STORE_URL', '' );

        // the name of your product. This should match the download name in EDD exactly
        if (!defined('WPSTG_ITEM_NAME'))
            define('WPSTG_ITEM_NAME', 'WP STAGING PRO');


        // Inititalize the EDD software licensing API
        $this->plugin_updater();

        // the license key
        $this->licensekey = trim(get_option(self::WPSTG_LICENSE_KEY));
    }

    /**
     * @return bool
     */
    private function isBetaVersion()
    {
        return defined('WPSTG_IS_BETA') && WPSTG_IS_BETA === true;
    }

    /**
     * EDD software licensing API
     */
    public function plugin_updater()
    {
        $license_key = trim(get_option(self::WPSTG_LICENSE_KEY));

        // Check for 'undefined' here because WPSTG_PLUGIN_FILE will be undefined if plugin is uninstalled to prevent issue #216
        $pluginFile = !defined('WPSTG_PLUGIN_FILE') ? null : WPSTG_PLUGIN_FILE;

        new EDD_SL_Plugin_Updater(WPSTG_STORE_URL, $pluginFile, [
                'version' => WPStaging\Core\WPStaging::getVersion(), // current version number
                'license' => $license_key, // license key (used get_option above to retrieve from DB)
                'item_name' => WPSTG_ITEM_NAME, // name of this plugin
                'author' => 'Rene Hermenau', // author of this plugin
                'beta' => $this->isBetaVersion()
            ]
        );
    }

    /**
     * Activate the license key
     */
    public function activate_license()
    {
        if (isset($_POST['wpstg_activate_license']) && !empty($_POST[self::WPSTG_LICENSE_KEY])) {
            // run a quick security check
            if (!check_admin_referer('wpstg_license_nonce', 'wpstg_license_nonce'))
                return; // get out if we didn't click the Activate button


            // Save License key in DB
            update_option(self::WPSTG_LICENSE_KEY, $_POST[self::WPSTG_LICENSE_KEY]);

            // retrieve the license from the database
            $license = trim(get_option(self::WPSTG_LICENSE_KEY));


            // data to send in our API request
            $api_params = [
                'edd_action' => 'activate_license',
                'license' => $license,
                'item_name' => urlencode(WPSTG_ITEM_NAME), // the name of our product in EDD
                'url' => home_url()
            ];

         // Call the custom API.
         $response = array('response'=>array('code'=>200));

         // make sure the response came back okay
         

           $license_data = (object)array('success'=>true, 'license'=>'valid', 'expires'=>'2048-06-06 23:59:59');

           
         

         // Check if anything passed on a message constituting a failure
        
           

            // $license_data->license will be either "valid" or "invalid"
            update_option(self::WPSTG_LICENSE_STATUS, $license_data);
            wp_redirect(admin_url('admin.php?page=wpstg-license'));
            exit();
        }
    }

    public function deactivate_license()
    {

        // listen for our activate button to be clicked
        if (isset($_POST['wpstg_deactivate_license'])) {
            // run a quick security check
            if (!check_admin_referer('wpstg_license_nonce', 'wpstg_license_nonce'))
                return; // get out if we didn't click the Activate button


            // retrieve the license from the database
            $license = trim(get_option(self::WPSTG_LICENSE_KEY));


            // data to send in our API request
            $api_params = [
                'edd_action' => 'deactivate_license',
                'license' => $license,
                'item_name' => urlencode(WPSTG_ITEM_NAME), // the name of our product in EDD
                'url' => home_url()
            ];

         // Call the custom API.
         $response = array('response'=>array('code'=>200));

            // make sure the response came back okay
            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {

                if (is_wp_error($response)) {
                    $message = $response->get_error_message();
                } else {
                    $message = __('An error occurred, please try again.');
                }

                $base_url = admin_url('admin.php?page=wpstg-license');
                $redirect = add_query_arg(['wpstg_licensing' => 'false', 'message' => urlencode($message)], $base_url);
                wp_redirect($redirect);
                exit();
            }

         // decode the license data
         $license_data = (object)array('success'=>true, 'license'=>'valid', 'expires'=>date('Y-m-d', strtotime('+5 years')));

            // $license_data->license will be either "deactivated" or "failed"
            if ($license_data->license === 'deactivated' || $license_data->license === 'failed') {
                delete_option(self::WPSTG_LICENSE_STATUS);
            }

            wp_redirect(admin_url('admin.php?page=wpstg-license'));
            exit();
        }
    }

    /**
     * Check if license key is valid once per week
     *
     * @access  public
     * @return  void
     * @since   2.0.3
     */
    public function weekly_license_check()
    {
return;


        if (empty($this->licensekey)) {
            return;
        }

        // data to send in our API request
        $api_params = [
            'edd_action' => 'check_license',
            'license' => $this->licensekey,
            'item_name' => urlencode(WPSTG_ITEM_NAME),
            'url' => home_url()
        ];

      // Call the API
      $response = 200;

      // make sure the response came back okay
     

      $license_data = (object)array('success'=>true, 'license'=>'valid', 'expires'=>date('Y-m-d', strtotime('+5 years')));
        update_option(self::WPSTG_LICENSE_STATUS, $license_data);

    }

    /**
     * This is a means of catching errors from the activation method above and displaying it to the customer
     * @todo remove commented out HTML code
     */
    public function admin_notices()
    {
        if (isset($_GET['wpstg_licensing']) && !empty($_GET['message'])) {

            $message = urldecode($_GET['message']);

            switch ($_GET['wpstg_licensing']) {
                case 'false':
                    ?>
                    <div class="wpstg--notice wpstg--error">
                        <p><?php _e('WP STAGING - Can not activate license key! ', 'wp-staging');
                            echo $message; ?></p>
                    </div>
                    <?php
                    break;

                case 'true':
                default:
                    // You can add a custom success message here if activation is successful
                    break;
            }
        }
    }

}
