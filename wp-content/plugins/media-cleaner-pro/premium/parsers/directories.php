<?php

add_action( 'wpmc_scan_post', 'wpmc_scan_directories', 10, 2 );

function wpmc_scan_directories( $html, $id ) {
  global $wpmc, $wpdb;
  $drts_entity_field_wp_image = $wpdb->prefix . "drts_entity_field_wp_image";
	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT attachment_id FROM {$drts_entity_field_wp_image} WHERE entity_id = %d AND entity_type = 'post'", $id ) );
	foreach ( $ids as $id ) {
    $wpmc->add_reference_id( $id, 'DIRECTORIES (ID)' );
  }
}

?>