<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_image_map_pro', 10, 0 );

function wpmc_scan_once_image_map_pro() {
  global $wpmc;

  $data = get_option( 'image-map-pro-wordpress-admin-options' );
  if ( !empty( $data ) ) {
    $saves = $data['saves'];
    if ( is_array( $saves ) ) {
      foreach ( $saves as $save ) {
        if ( empty( $save['json'] ) )
          continue;
        $json = json_decode( stripslashes( $save['json'] ) );
        if ( empty( $json ) )
          continue;
        $postmeta_images_ids = array();
        $postmeta_images_urls = array();
        $wpmc->get_from_meta( $json, array( 'url' ), $postmeta_images_ids, $postmeta_images_urls );
        $wpmc->add_reference_id( $postmeta_images_ids, 'IMAGE MAP PRO (ID)' );
        $wpmc->add_reference_url( $postmeta_images_urls, 'IMAGE MAP PRO (URL)' );
      }
    }
  }
}	

?>