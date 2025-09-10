<?php

add_action( 'wpmc_scan_post', 'wpmc_scan_html_easyrealestate', 10, 2 );

function wpmc_scan_html_easyrealestate( $html, $id ) {
  global $wpmc;
  $property_images = get_post_meta( $id, 'REAL_HOMES_property_images' );
  if ( !empty( $property_images ) )
	  $wpmc->add_reference_id( $property_images, 'THEME (ID)' );
}

?>