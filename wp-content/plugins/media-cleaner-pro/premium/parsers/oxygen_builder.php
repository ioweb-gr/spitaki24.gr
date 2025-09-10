<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_oxygen', 10, 0 );
add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_oxygen', 10, 1 );

function wpmc_scan_once_oxygen() {
  global $wpmc;

  $ct_components = get_option( 'ct_components_classes', '' );
  if ( !empty( $ct_components ) ) {
    $postmeta_images_ids = array();
	  $postmeta_images_urls = array();
    $wpmc->get_from_meta( $ct_components, array( 'background-image' ), $postmeta_images_ids, $postmeta_images_urls );
    $wpmc->add_reference_id( $postmeta_images_ids, 'PAGE BUILDER META (ID)' );
	  $wpmc->add_reference_url( $postmeta_images_urls, 'PAGE BUILDER META (URL)' );
  }
}	

function wpmc_scan_postmeta_oxygen( $id ) {
  global $wpmc;
  $data = get_post_meta( $id, 'ct_builder_shortcodes', true );
  
  // Detect the background images
  $regex = '/(http|https):\/\/([\w_-]+(?:(?:\.[\w_-]+)+))([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-])?/';
  preg_match_all( $regex, $data, $results );
  foreach ( $results as $result ) {
    foreach ( $result as $str ) {
      $new = $wpmc->clean_url( $str );
      if ( !empty( $new ) ) 
        $wpmc->add_reference_url( $new, 'PAGE BUILDER META (URL)' );
    }
    //error_log( print_r( $result, 1 ) );
    //error_log( $wpmc->clean_url( $result[0] ) );
  }

  // Detect the content of the shortcodes
  $html = do_shortcode( $data );
  $postmeta_images_urls = $wpmc->get_urls_from_html( $html );
	$wpmc->add_reference_url( $postmeta_images_urls, 'PAGE BUILDER META (URL)' );
}

?>