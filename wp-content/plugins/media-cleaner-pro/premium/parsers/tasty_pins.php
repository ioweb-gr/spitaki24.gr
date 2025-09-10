<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_tasty_pins', 10, 1 );

function wpmc_scan_postmeta_tasty_pins( $id ) {
  global $wpmc;
  $data = get_post_meta( $id, 'tp_pinterest_hidden_image', true );
  if ( !empty( $data ) ) {
    $ids = explode( ',', $data );
    $wpmc->add_reference_id( $ids, 'POST META (ID)' );
  }
}

?>