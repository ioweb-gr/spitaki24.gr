<?php

add_action( 'wpmc_scan_postmeta', 'wpmc_scan_postmeta_brizy', 10, 1 );

function brizy_get_id_from_brizy_uid( $uid ) {
  global $wpdb;
  $id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta 
    WHERE meta_key='brizy_attachment_uid' 
    AND meta_value='%s'", $uid ) );
  return $id;
}

function brizy_get_from_meta( $meta, $lookFor, &$ids, &$urls, $id ) {
  global $wpmc;
  
  foreach ( $meta as $key => $value ) {
    if ( is_object( $value ) || is_array( $value ) )
      brizy_get_from_meta( $value, $lookFor, $ids, $urls, $id );
    else if ( in_array( $key, $lookFor ) ) {
      if ( empty( $value ) )
        continue;
      else if ( is_numeric( $value ) )
        array_push( $ids, $value );
      else {
        $ext = strtolower( pathinfo( $value, PATHINFO_EXTENSION ) );
        if ( !empty( $ext ) && in_array( $ext, array( 'jpeg', 'jpg', 'png', 'gif' ) ) )
          array_push( $urls, 'brizy/' . $id . '/assets/images/' . $value );
        else if ( strpos( $value, 'wp-' ) !== false ) {
          $media_id = brizy_get_id_from_brizy_uid( $value );
          if ( !empty( $media_id ) )
            array_push( $ids, $media_id );
        }
      }
    }
  }
}

function wpmc_scan_postmeta_brizy( $id ) {
	global $wpmc;
  $data = get_post_meta( $id, 'brizy' );
	if ( !empty( $data ) ) {
    $postmeta_images_ids = array();
	  $postmeta_images_urls = array();
    $post = Brizy_Editor_Post::get( $id );

    // BRIZY VERSION 2
    $post->compile_page();
    $data = $post->get_editor_data();
    $data = json_decode( $data );
    brizy_get_from_meta( $data, array( 'imageSrc', 'bgImageSrc' ), $postmeta_images_ids, $postmeta_images_urls, $id );

    // OLD WAY OF ANALYZING THE CONTENT (MAYBE BRIZY VERSION 1.X ?)
    // $data = $post->storage()->get( Brizy_Editor_Post::BRIZY_POST, false );
    // $json_value = base64_decode( $data['editor_data'] );
    // $json = json_decode( $json_value );
    // brizy_get_from_meta( $json, array( 'imageSrc', 'bgImageSrc' ), $postmeta_images_ids, $postmeta_images_urls, $id );
    
    // DEBUGGING
    // if ( $id == 7 )
    //   error_log( $json_value );
    //error_log( print_r( $postmeta_images_urls, 1 ) );
    
    $wpmc->add_reference_id( $postmeta_images_ids, 'PAGE BUILDER CONTENT (ID)' );
	  $wpmc->add_reference_url( $postmeta_images_urls, 'PAGE BUILDER CONTENT (URL)' );
	}
}

?>