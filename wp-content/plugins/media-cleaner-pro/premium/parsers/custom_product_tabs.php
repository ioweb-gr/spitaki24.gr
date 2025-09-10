<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_custom_product_tabs', 10, 1 );

function wpmc_scan_postmeta_custom_product_tabs( $id ) {
	global $wpmc;
	$tabs = get_post_meta( $id, 'yikes_woo_products_tabs' );
  if ( empty( $tabs ) )
    return;
  $urls = array();
  foreach ( $tabs as $tab ) {
    if ( empty( $tab ) )
      continue;
    // Not sure about how the data is stored exactly.
    foreach ( $tab as $whatever ) {
      foreach ( $whatever as $field => $value ) {
        if ( $field === 'content' ) {
          $urls = array_merge( $wpmc->get_urls_from_html( $value ), $urls );
        }
      }
    }
  }
	$wpmc->add_reference_url( $urls, 'PRODUCT META (URL)' );
}

?>