<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_wcfm_marketplace', 10, 0 );

function wpmc_scan_once_wcfm_marketplace() {
  global $wpmc, $wpdb;

  $rows = $wpdb->get_col( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = 'wcfmmp_profile_settings'" );
  foreach ( $rows as $row ) {
    $data = maybe_unserialize( $row );
    $postmeta_images_ids = array();
	  $postmeta_images_urls = array();
    $wpmc->get_from_meta( $data, array( 'gravatar', 'banner' ), $postmeta_images_ids, $postmeta_images_urls );
    $wpmc->add_reference_id( $postmeta_images_ids, 'PAGE BUILDER META (ID)' );
	  $wpmc->add_reference_url( $postmeta_images_urls, 'PAGE BUILDER META (URL)' );
  }
}	

?>
