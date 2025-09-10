<?php
/**
 *
 * CommerceKit Clear Cache Command
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly....
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Commercekit_Clear_Cache_Command Class
	 */
	class Commercekit_Clear_Cache_Command extends WP_CLI_Command {

		/**
		 * Success message.
		 *
		 * @var message
		 */
		protected $message = '';

		/**
		 * Commercekit_Clear_Cache_Command Constructor
		 */
		public function __construct() {
			$this->message = esc_html__( 'Error on commercekit-clear-cache CLI command.', 'commercegurus-commercekit' );
		}

		/**
		 * Commercekit_Clear_Cache_Command invoke
		 *
		 * @param mixed $args of command.
		 */
		public function __invoke( $args ) {
			if ( function_exists( 'commercekit_as_clear_all_cache' ) ) {
				$this->message = commercekit_as_clear_all_cache();
			}

			WP_CLI::success( $this->message );
		}
	}

	WP_CLI::add_command( 'commercekit-clear-cache', 'Commercekit_Clear_Cache_Command' );
}
