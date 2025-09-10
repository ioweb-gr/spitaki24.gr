<?php
/**
 * Uninstall plugin
 *
 * @package CommerceKit
 * @subpackage Shoptimizer
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
global $wpdb;

/*
 * Only remove ALL data if CGKIT_REMOVE_ALL_DATA constant is set to true in user's
 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
 * and to ensure only the site owner can perform this action.
 */
if ( defined( 'CGKIT_REMOVE_ALL_DATA' ) && true === CGKIT_REMOVE_ALL_DATA ) {
	$table_waitlist       = 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'commercekit_waitlist';
	$table_wishlist       = 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'commercekit_wishlist';
	$table_wishlist_items = 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'commercekit_wishlist_items';

	$wpdb->query( $table_waitlist ); // phpcs:ignore
	$wpdb->query( $table_wishlist ); // phpcs:ignore
	$wpdb->query( $table_wishlist_items ); // phpcs:ignore

	delete_option( 'commercekit' );
	delete_option( 'commercekit_db_version' );
	delete_option( 'commercekit_obp_views' );
	delete_option( 'commercekit_obp_clicks' );
	delete_option( 'commercekit_obp_sales' );
	delete_option( 'commercekit_obp_sales_revenue' );
	delete_option( 'commercekit_cgkit_wishlist' );
	delete_option( 'commercekit_import_images' );
	delete_option( 'commercekit_sg_converted' );

	delete_option( 'commercekit_wtls_reset' );
	delete_option( 'commercekit_wtls_total' );
	delete_option( 'commercekit_wtls_sales' );
	delete_option( 'commercekit_wtls_sales_revenue' );
	delete_option( 'commercekit_wsls_reset' );
	delete_option( 'commercekit_wsls_total' );
	delete_option( 'commercekit_wsls_sales' );
	delete_option( 'commercekit_wsls_sales_revenue' );
}
