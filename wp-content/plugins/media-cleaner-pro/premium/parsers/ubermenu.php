<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_ubermenu', 10, 0 );

function wpmc_scan_once_ubermenu() {
	global $wpmc, $wpdb;
	$ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_ubermenu_settings'" );
	foreach ( $ids as $id ) {
		$meta = get_post_meta( $id, '_ubermenu_settings', true );
		if ( !empty( $meta ) ) {
			$urls = $wpmc->get_urls_from_html( $meta['custom_content'] );
			$wpmc->add_reference_url( $urls, 'MENU (URL)' );
		}
	}
}

?>