<?php

add_action( 'wpmc_scan_post', 'wpmc_scan_html_visualcomposer', 10, 2 );
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_visualcomposer', 10, 1 );

function wpmc_scan_html_visualcomposer( $html, $id ) {
	global $wpmc;
	$posts_images_vc = array();
	$galleries_images_vc = array();

	// Support for Salient Theme
	if ( defined( 'NECTAR_THEME_NAME' ) ) {
		$extra = get_post_meta( $id, '_nectar_portfolio_extra_content', true );
		$urls = $wpmc->get_urls_from_html( $extra );
		$wpmc->add_reference_url( $urls, 'EXTRA CONTENT (URLS)' );
	}

	// Background image by ID
	preg_match_all( "/id\^([0-9]+)\|/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) && count( $res[1] ) > 0 ) {
		//error_log( print_r( $res, 1 ) );
		foreach ( $res[1] as $id ) {
			array_push( $posts_images_vc, $id );
		}
	}
	$wpmc->add_reference_id( $posts_images_vc, 'PAGE BUILDER (ID)' );


	// Single Image
	preg_match_all( "/image=\"([0-9]+)\"/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) && count( $res[1] ) > 0 ) {
		foreach ( $res[1] as $id ) {
			array_push( $posts_images_vc, $id );
		}
	}
	$wpmc->add_reference_id( $posts_images_vc, 'PAGE BUILDER (ID)' );

	// Gallery
	preg_match_all( "/images=\"([0-9,]+)/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) ) {
		foreach ( $res[1] as $r ) {
			$ids = explode( ',', $r );
			$galleries_images_vc = array_merge( $galleries_images_vc, $ids );
		}
	}
	$wpmc->add_reference_id( $galleries_images_vc, 'GALLERY (ID)' );
}

function wpmc_scan_postmeta_visualcomposer( $id ) {
	global $wpmc;
	$postmeta_images_ids = array();
	$postmeta_images_urls = array();

	$wpb_shortcodes_custom_css = get_post_meta( $id, '_wpb_shortcodes_custom_css' );
	if ( is_array( $wpb_shortcodes_custom_css ) ) {
		foreach ( $wpb_shortcodes_custom_css as $d ) {
			$newurls = $wpmc->get_urls_from_html( $d );
			$postmeta_images_urls = array_merge( $postmeta_images_urls, $newurls );
		}
	}

	$vc_post_settings = get_post_meta( $id, '_vc_post_settings' );
	if ( is_array( $vc_post_settings ) ) {
		$wpmc->get_from_meta( $vc_post_settings, array( 'include' ), $postmeta_images_ids, $postmeta_images_urls );
		//error_log( print_r( $vc_post_settings, 1 ) );
	}
	
	$wpmc->add_reference_id( $postmeta_images_ids, 'PAGE BUILDER META (ID)' );
	$wpmc->add_reference_url( $postmeta_images_urls, 'PAGE BUILDER META (URL)' );
}

?>