<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_elementor', 10, 1 );

function wpmc_scan_postmeta_elementor( $id ) {
	global $wpmc;
	$postmeta_images_ids = array();
	$postmeta_images_urls = array();
  $data = get_post_meta( $id, '_elementor_data' );
	if ( isset( $data[0] ) ) {
    $decoded = json_decode( $data[0] );
		$wpmc->get_from_meta( $decoded, array( 'url' ), $postmeta_images_ids, $postmeta_images_urls );
	}
	$wpmc->add_reference_id( $postmeta_images_ids, 'PAGE BUILDER META (ID)' );
	$wpmc->add_reference_url( $postmeta_images_urls, 'PAGE BUILDER META (URL)' );
}

?>