<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_advancedads', 10, 2 );

function wpmc_scan_postmeta_advancedads( $id ) {
  $type = get_post_type( $id );
  if ( $type !== 'advanced_ads' ) {
    return;
  }
  $options = get_post_meta( $id, 'advanced_ads_ad_options', true );

  global $wpmc;
  if ( isset( $options['output'] ) && isset( $options['output']['image_id'] ) ) {
    $image_id = $options['output']['image_id'];
    //error_log( print_r( $options['output']['image_id'], 1 ) );
    $wpmc->add_reference_id( $image_id, 'ADVANCED ADS (ID)' );
  }

  // global $wpdb, $wpmc;
  // $geodir_gd_place_detail = $wpdb->prefix . "geodir_gd_place_detail";
  // $geodir_attachments = $wpdb->prefix . "geodir_attachments";
  // $featured = $wpdb->get_var( $wpdb->prepare( "SELECT featured_image FROM $geodir_gd_place_detail WHERE post_id = %d", $id ) );
  // $featured = trim( $featured, '/' );
  // $wpmc->add_reference_url( $featured, 'GEODIRECTORY (URL)' );
  // $attachments = $wpdb->get_col( $wpdb->prepare( "SELECT metadata FROM $geodir_attachments WHERE post_id=%d", $id ) );

  // foreach ( $attachments as $attachment ) {
  //   $attachment = unserialize( $attachment );
  //   $pathinfo = pathinfo( $attachment['file'] );
  //   $dirname = $pathinfo['dirname'];
  //   //error_log( print_r( 'DIRNAME' . $dirname, 1 ) );
  //   foreach ( $attachment['sizes'] as $size ) {
  //     $file = $dirname . '/' . $size['file'];
  //     //error_log( $file );
  //     $wpmc->add_reference_url( $file, 'GEODIRECTORY (URL)' );
  //   } 
  // }
}

?>