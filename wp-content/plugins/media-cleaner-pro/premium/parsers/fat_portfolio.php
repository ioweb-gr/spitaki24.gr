<?php

add_action( 'wpmc_scan_post', 'wpmc_scan_html_fat_portfolio', 10, 2 );

function wpmc_scan_html_fat_portfolio( $html, $id ) {
  global $wpmc;
  $postmeta_images_ids = array();
	$postmeta_images_urls = array();
  $data = get_post_meta( $id, 'fat-meta-box-gallery-type' );
  $wpmc->get_from_meta( $data, array( 'fat_mb_image_gallery' ), $postmeta_images_ids, $postmeta_images_urls );
  $wpmc->add_reference_id( $postmeta_images_ids, 'PORTFOLIO (ID)' );
	$wpmc->add_reference_url( $postmeta_images_urls, 'PORTFOLIO (URL)' );
}

?>