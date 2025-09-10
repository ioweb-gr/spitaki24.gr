<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_modula_gallery', 10, 1 );

function wpmc_scan_postmeta_modula_gallery( $id ) {
  $type = get_post_type( $id );
  if ( $type !== 'modula-gallery' ) {
    return;
  }
  global $wpmc;
  $postmeta_images_ids = array();
	$postmeta_images_urls = array();
  $data = get_post_meta( $id, 'modula-images' );
	if ( !empty( $data ) ) {
		$wpmc->get_from_meta( $data, array( 'id' ), $postmeta_images_ids, $postmeta_images_urls );
  }
  $wpmc->add_reference_id( $postmeta_images_ids, 'MODULA GALLERY (ID)' );
	$wpmc->add_reference_url( $postmeta_images_urls, 'MODULA GALLERY (URL)' );
}

?>