<?php

add_action( 'wpmc_scan_post', 'wpmc_scan_html_justified_image_grid', 10, 2 );

function wpmc_scan_html_justified_image_grid( $html, $id ) {
	global $wpmc;
	$galleries_images_et = array();

  $type = get_post_type( $id );
  if ( $type !== 'justified-image-grid' ) {
    return;
  }

	// Galleries
	preg_match_all( "/ids=([0-9,]+)/", $html, $res );
	if ( !empty( $res ) && isset( $res[1] ) ) {
		foreach ( $res[1] as $r ) {
      $ids = explode( ',', $r );
			$galleries_images_et = array_merge( $galleries_images_et, $ids );
		}
	}

	$wpmc->add_reference_id( $galleries_images_et, 'JUSTIFIED IMAGE GRID (ID)' );
}

?>