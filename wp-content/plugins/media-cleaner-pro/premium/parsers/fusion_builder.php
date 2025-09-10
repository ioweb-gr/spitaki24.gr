<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_fusionbuilder', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_fusionbuilder', 10, 2 );
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_fusionbuilder', 10, 2 );

function wpmc_scan_once_fusionbuilder_get_option( $option ) {
	$res = get_option( $option );
	return is_array( $res ) ? $res : array();
}

function wpmc_scan_once_fusionbuilder() {
	global $wpmc;
	$options = array();
	$languages = $wpmc->get_languages();
	$options[] = wpmc_scan_once_fusionbuilder_get_option( 'fusion_options' );
	$options[] = wpmc_scan_once_fusionbuilder_get_option( 'fusion_options_' );
	foreach ( $languages as $language ) {
		$options[] = wpmc_scan_once_fusionbuilder_get_option( 'fusion_options_' . $language );
	}
	foreach ( $options as $option ) {
		$postmeta_images_ids = array();
		$postmeta_images_urls = array();
		$wpmc->get_from_meta( $option, array( 'url', 'thumbnail' ), $postmeta_images_ids, $postmeta_images_urls );
		$wpmc->add_reference_id( $postmeta_images_ids, 'PORTFOLIO (ID)' );
		$wpmc->add_reference_url( $postmeta_images_urls, 'PORTFOLIO (URL)' );
		//error_log( print_r( $postmeta_images_urls ) );
	}
}

function wpmc_scan_postmeta_fusionbuilder( $id ) {
  global $wpmc;
  $postmeta_images_ids = array();
	$postmeta_images_urls = array();
	$data = get_post_meta( $id, '_fusion' );

	// FusionBuilder is doing this horrible thing, not using an array to store the IDs used in the portfolio
	// but named attributes. It's limited to 30 (!?) so let's just look into all this.
	$attributes = array();
	for ( $c = 0; $c < 30; $c++ ) {
		array_push( $attributes, 'kd_featured-image-' . ($c + 1) . '_avada_portfolio_id' );
	}
  $wpmc->get_from_meta( $data, $attributes, $postmeta_images_ids, $postmeta_images_urls );
  $wpmc->add_reference_id( $postmeta_images_ids, 'PORTFOLIO (ID)' );
	$wpmc->add_reference_url( $postmeta_images_urls, 'PORTFOLIO (URL)' );
}

function wpmc_scan_html_fusionbuilder( $html, $id ) {
	global $wpmc;
	$posts_images_urls = array();
	$galleries_images = array();

	// Background Image
	preg_match_all( "/background_image=\"((https?:\/\/)?[^\\&\#\[\] \"\?]+\.(" . $wpmc->types . "))\"/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) && count( $res[1] ) > 0 ) {
		foreach ( $res[1] as $url ) {
			array_push( $posts_images_urls, $wpmc->clean_url( $url ) );
		}
	}

	// Background Image IDs
	preg_match_all( "/background_image_id=\"([0-9,]+)/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) ) {
		foreach ( $res[1] as $r ) {
			$ids = explode( ',', $r );
			$galleries_images = array_merge( $galleries_images, $ids );
		}
	}

	// // Galleries
	preg_match_all( "/image_ids=\"([0-9,]+)/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) ) {
		foreach ( $res[1] as $r ) {
			$ids = explode( ',', $r );
			$galleries_images = array_merge( $galleries_images, $ids );
		}
	}

	$wpmc->add_reference_url( $posts_images_urls, 'FUSION BUILDER (URL)' );
	$wpmc->add_reference_id( $galleries_images, 'FUSION BUILDER (ID)' );
}

?>