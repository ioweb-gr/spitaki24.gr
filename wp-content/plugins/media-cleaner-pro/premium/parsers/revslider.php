<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_revslider', 10, 0 );

function wpmc_scan_once_revslider() {
  global $wpmc;
  global $wpdb;

  $table = $wpdb->get_blog_prefix() . 'revslider_slides';
  $params = $wpdb->get_col( "SELECT params FROM $table" );
  $layers = $wpdb->get_col( "SELECT layers FROM $table" );
  $sliders = array_merge( $params, $layers );
  foreach ( $sliders as $slider ) {
    $data = json_decode( $slider );
    $postmeta_images_ids = array();
    $postmeta_images_urls = array();
    $wpmc->get_from_meta( $data, array( 'image', 'imageId' ), $postmeta_images_ids, $postmeta_images_urls );
    $wpmc->add_reference_id( $postmeta_images_ids, 'SLIDER (ID)' );
	  $wpmc->add_reference_url( $postmeta_images_urls, 'SLIDER (URL)' );
  }
}

?>