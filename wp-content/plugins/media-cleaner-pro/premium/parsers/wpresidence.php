<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_wpresidence', 10, 1 );

function wpmc_scan_postmeta_wpresidence( $id ) {
  global $wpmc;
  $images = get_post_meta( $id, 'image_to_attach', true );
  $postmeta_images_ids = explode( ',', $images );
  if ( is_array( $postmeta_images_ids ) ) {
    $wpmc->add_reference_id( $postmeta_images_ids, 'THEME GALLERY (ID)' );
  }
}

?>