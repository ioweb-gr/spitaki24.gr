<?php

add_action( 'wpmc_scan_once', 'wpmc_scan_once_yootheme_builder', 10, 0 );
add_action( 'wpmc_scan_post', 'wpmc_scan_html_yootheme_builder', 10, 2 );
//add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_yootheme_builder', 10, 1 );

function wpmc_scan_once_yootheme_builder() {
  global $wpmc;
  $mods = get_theme_mods();
  if ( !empty( $mods ) && isset( $mods['config'] ) ) {
    $json = json_decode( $mods['config'] );
    if ( isset( $json->logo ) && isset( $json->logo->image ) ) {
      $image = $wpmc->clean_url( $json->logo->image );
      if ( !empty( $image ) ) {
        $wpmc->add_reference_url( $image, 'YOOTHEME (URL)' );
      }
    }
  }
}

function wpmc_scan_html_yootheme_builder( $html, $id ) {
  global $wpmc;
  $urls = $wpmc->get_urls_from_html( $html );
  //error_log( print_r( $urls, 1 ) );
  $wpmc->add_reference_url( $urls, 'YOOTHEME (URL)' );
}

// function wpmc_scan_postmeta_yootheme_builder( $id ) {
// }

?>