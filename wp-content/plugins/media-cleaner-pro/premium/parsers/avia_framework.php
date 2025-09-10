<?php

add_action( 'wpmc_scan_post', 'wpmc_scan_html_avia_framework', 10, 2 );

function wpmc_scan_html_avia_framework( $html, $id ) {
	global $wpmc;

	$posts_images_urls = array();
	$galleries_images_et = array();

	// Modules with URL
	preg_match_all( "/url=[\"']((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(jpe?g|gif|png|ico|tif?f|bmp|mp3))[\"']/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) ) {
		foreach ( $res[1] as $url ) {
			array_push( $posts_images_urls, $wpmc->clean_url( $url ) );
		}
	}

	// Modules with SRC
	preg_match_all( "/src=[\"']((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(jpe?g|gif|png|ico|tif?f|bmp|mp3))[\"']/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) ) {
		foreach ( $res[1] as $url ) {
			array_push( $posts_images_urls, $wpmc->clean_url( $url ) );
		}
	}

	// Galleries
	preg_match_all( "/ids=[\"']([0-9,]+)[\"']/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) ) {
		foreach ( $res[1] as $r ) {
			$ids = explode( ',', $r );
			$galleries_images_et = array_merge( $galleries_images_et, $ids );
		}
	}

	$wpmc->add_reference_url( $posts_images_urls, 'CONTENT (URL)' );
	$wpmc->add_reference_id( $galleries_images_et, 'PAGE BUILDER (ID)' );
}

?>