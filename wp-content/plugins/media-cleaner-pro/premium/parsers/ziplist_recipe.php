<?php

add_action( 'wpmc_scan_post', 'wpmc_scan_html_ziplist_recipe', 10, 2 );

function wpmc_scan_html_ziplist_recipe( $html, $id ) {
	global $wpmc;
	$html = amd_zlrecipe_convert_to_recipe( $html );
	$new_urls = $wpmc->get_urls_from_html( $html );
	$wpmc->add_reference_url( $new_urls, 'CONTENT (URL)' );
}

?>