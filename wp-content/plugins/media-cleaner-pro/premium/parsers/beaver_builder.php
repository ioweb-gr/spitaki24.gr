<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_beaverbuilder', 10, 1 );

function wpmc_scan_postmeta_beaverbuilder( $id ) {
	global $wpmc;
	$postmeta_images_ids = array();
	$postmeta_images_urls = array();
	$data = get_post_meta( $id, '_fl_builder_data' );
	if ( !empty( $data ) ) {
		$wpmc->get_from_meta( $data, array( 'id', 'bg_image_src', 'photo_src' ), $postmeta_images_ids, $postmeta_images_urls );
	}
	$wpmc->add_reference_id( $postmeta_images_ids, 'PAGE BUILDER META (ID)' );
	$wpmc->add_reference_url( $postmeta_images_urls, 'PAGE BUILDER META (URL)' );
}

?>