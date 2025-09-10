<?php
/**
 * Plugin Name: Ioweb Blacklist
 * Description: Blacklist users from purchasing based on their email or phone number.
 * Version: 1.0
 * Author: IOWEB TECHNOLOGIES
 */

if (!defined('ABSPATH')) {
	exit;
}

// Include the main class
include_once dirname(__FILE__) . '/includes/class-ioweb-blacklist.php';

// Initialize the plugin
function ioweb_blacklist_init() {
	return Ioweb_Blacklist::instance();
}

add_action('plugins_loaded', 'ioweb_blacklist_init');
