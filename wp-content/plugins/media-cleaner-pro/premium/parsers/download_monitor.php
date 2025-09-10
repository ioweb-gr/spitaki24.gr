<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_download_monitor', 10, 2 );

function wpmc_scan_postmeta_download_monitor( $id ) {
  $type = get_post_type( $id );
  if ( $type !== 'dlm_download' ) {
    return;
  }

  global $wpdb, $wpmc;
  try {
    $api = download_monitor()->service( 'download_repository' )->retrieve_single( $id );
    //error_log( print_r( $api->version_ids, 1 ) );
  }
  catch ( Exception $exception ) {

  }
  
  //foreach ( $files as $file ) {
    // $attachment = unserialize( $attachment );
    // $pathinfo = pathinfo( $attachment['file'] );
    // $dirname = $pathinfo['dirname'];
    // //error_log( print_r( 'DIRNAME' . $dirname, 1 ) );
    // foreach ( $attachment['sizes'] as $size ) {
    //   $file = $dirname . '/' . $size['file'];
    //   //error_log( $file );
    //   $wpmc->add_reference_url( $file, 'GEODIRECTORY (URL)' );
    // } 
  //}
}

?>