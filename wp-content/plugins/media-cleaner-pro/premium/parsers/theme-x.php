<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_x_theme', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_x_theme', 10, 2 );


function wpmc_scan_once_x_theme() {
	global $wpmc, $wpdb;
	$logo = get_option( 'x_logo' );
	if ( $wpmc->is_url( $logo ) ) {
		$wpmc->add_reference_url( $wpmc->clean_url( $logo ), 'SITE ICON' );
	}
}

function wpmc_scan_html_x_theme( $html, $id ) {
	global $wpmc;
	$posts_images_urls = array();
	$final_html = do_shortcode( str_replace( "[cs_content]", "[cs_content _p=\"{$id}\" no_wrap=true ]", $html ) );
	$new_urls = $wpmc->get_urls_from_html( $final_html );
	$posts_images_urls = array_merge( $posts_images_urls, $new_urls );
	
	$cornerstone = get_post_meta( $id, '_cornerstone_settings' );
	if ( !empty( $cornerstone ) ) {
		if ( is_array( $cornerstone ) ) {
			foreach ( $cornerstone as $stone ) {
				$new_urls = $wpmc->get_urls_from_html( $stone );
				$posts_images_urls = array_merge( $posts_images_urls, $new_urls );
			}
		}
		else {
			error_log( 'Cleaner: Cornerstore does not give an array.' );
		}
	}

	$wpmc->add_reference_url( $posts_images_urls, 'CONTENT (URL)' );
}

?>