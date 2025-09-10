<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ioweb_Blacklist {
	private static $instance = null;
	private $blacklist_options;

	private function __construct() {
		$this->blacklist_options = get_option( 'ioweb_blacklist_options' );

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'check_blacklist' ) );
	}

	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function add_admin_menu() {
		add_options_page(
			'Ioweb Blacklist',
			'Ioweb Blacklist',
			'manage_options',
			'ioweb-blacklist',
			array( $this, 'options_page' )
		);
	}

	public function settings_init() {
		register_setting( 'ioweb_blacklist_group', 'ioweb_blacklist_options' );

		add_settings_section(
			'ioweb_blacklist_section',
			__( 'Blacklist Settings', 'ioweb-blacklist' ),
			null,
			'ioweb_blacklist_group'
		);

		add_settings_field(
			'blacklist_emails',
			__( 'Blacklisted Emails', 'ioweb-blacklist' ),
			array( $this, 'emails_render' ),
			'ioweb_blacklist_group',
			'ioweb_blacklist_section'
		);

		add_settings_field(
			'blacklist_phones',
			__( 'Blacklisted Phone Numbers', 'ioweb-blacklist' ),
			array( $this, 'phones_render' ),
			'ioweb_blacklist_group',
			'ioweb_blacklist_section'
		);

		add_settings_field(
			'blacklist_message',
			__( 'Error Message', 'ioweb-blacklist' ),
			array( $this, 'message_render' ),
			'ioweb_blacklist_group',
			'ioweb_blacklist_section'
		);

		add_settings_field(
			'blacklist_wp_die',
			__( 'Use wp_die()', 'ioweb-blacklist' ),
			array( $this, 'wp_die_render' ),
			'ioweb_blacklist_group',
			'ioweb_blacklist_section'
		);
	}

	public function emails_render() {
		?>
        <textarea name='ioweb_blacklist_options[blacklist_emails]' rows='5'
                  cols='50'><?php echo esc_textarea( $this->blacklist_options['blacklist_emails'] ); ?></textarea>
        <p class="description"><?php _e( 'Enter emails separated by commas.', 'ioweb-blacklist' ); ?></p>
		<?php
	}

	public function phones_render() {
		?>
        <textarea name='ioweb_blacklist_options[blacklist_phones]' rows='5'
                  cols='50'><?php echo esc_textarea( $this->blacklist_options['blacklist_phones'] ); ?></textarea>
        <p class="description"><?php _e( 'Enter phone numbers separated by commas.', 'ioweb-blacklist' ); ?></p>
		<?php
	}

	public function message_render() {
		?>
        <input type='text' name='ioweb_blacklist_options[blacklist_message]'
               value='<?php echo esc_attr( $this->blacklist_options['blacklist_message'] ); ?>'>
        <p class="description"><?php _e( 'Enter the message to display if a user is blacklisted.', 'ioweb-blacklist' ); ?></p>
		<?php
	}

	public function wp_die_render() {
		?>
        <input type='checkbox'
               name='ioweb_blacklist_options[blacklist_wp_die]' <?php checked( $this->blacklist_options['blacklist_wp_die'], 1 ); ?>
               value='1'>
        <p class="description"><?php _e( 'Check to use wp_die() instead of displaying a custom message.', 'ioweb-blacklist' ); ?></p>
		<?php
	}

	public function options_page() {
		?>
        <form action='options.php' method='post'>
            <h2><?php _e( 'Ioweb Blacklist', 'ioweb-blacklist' ); ?></h2>
			<?php
			settings_fields( 'ioweb_blacklist_group' );
			do_settings_sections( 'ioweb_blacklist_group' );
			submit_button();
			?>
        </form>
		<?php
	}

	public function check_blacklist() {
		$blacklist_emails = array_map('trim', explode(',', $this->blacklist_options['blacklist_emails']));
		$blacklist_phones = array_map('trim', explode(',', $this->blacklist_options['blacklist_phones']));
		$blacklist_die    = (bool) $this->blacklist_options['blacklist_wp_die'];

		$email = WC()->checkout->get_value('billing_email');
		$phone = WC()->checkout->get_value('billing_phone');

		if (in_array($email, $blacklist_emails) || in_array($phone, $blacklist_phones)) {
			if ($blacklist_die) {
				// Trigger a fatal error
				trigger_error("Our website is experiencing technical difficulties. Please try again later.", E_USER_ERROR);
			} else {
				wc_add_notice($this->blacklist_options['blacklist_message'], 'error');
			}
		}
	}



}
