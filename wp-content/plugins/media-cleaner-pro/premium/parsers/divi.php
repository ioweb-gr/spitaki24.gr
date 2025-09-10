<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_divi', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_divi', 10, 2 );

function wpmc_scan_once_divi() {
  global $wpmc;

  $et_divi = get_option( 'et_divi', '' );
  if ( !empty( $et_divi ) ) {
    $postmeta_images_ids = array();
	  $postmeta_images_urls = array();
    $wpmc->get_from_meta( $et_divi, array( 'divi_logo' ), $postmeta_images_ids, $postmeta_images_urls );
    $wpmc->add_reference_id( $postmeta_images_ids, 'THEME (ID)' );
	  $wpmc->add_reference_url( $postmeta_images_urls, 'THEME (URL)' );
  }
}	

function wpmc_scan_html_divi( $html, $id ) {
	global $wpmc;
	$posts_images_urls = array();
	$galleries_images_et = array();

	// Single Image
	preg_match_all( "/src=\"((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(" . $wpmc->types . "))\"/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) && count( $res[1] ) > 0 ) {
		foreach ( $res[1] as $url ) {
			if ( !preg_match('/(elegantthemesimages\.com)|(elegantthemes\.com)/', $url ) )
				array_push( $posts_images_urls, $wpmc->clean_url( $url ) );
		}
	}

	// Background Image
	preg_match_all( "/background_image=\"((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(" . $wpmc->types . "))\"/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) && count( $res[1] ) > 0 ) {
		foreach ( $res[1] as $url ) {
			if ( !preg_match('/(elegantthemesimages\.com)|(elegantthemes\.com)/', $url ) )
				array_push( $posts_images_urls, $wpmc->clean_url( $url ) );
		}
	}

	// Modules with URL (like the Person module)
	preg_match_all( "/url=\"((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(" . $wpmc->types . "))\"/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) ) {
		foreach ( $res[1] as $url ) {
			if ( !preg_match('/(elegantthemesimages\.com)|(elegantthemes\.com)/', $url ) )
				array_push( $posts_images_urls, $wpmc->clean_url( $url ) );
		}
	}

	// Galleries
	preg_match_all( "/gallery_ids=\"([0-9,]+)/", $html, $res );
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