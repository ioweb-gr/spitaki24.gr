<?php

if ( function_exists( 'auxshp_get_product_thumbnail' ) ) {
    remove_action( 'woocommerce_before_shop_loop_item_title', 'auxshp_get_product_thumbnail', 11 );
    add_action( 'woocommerce_before_shop_loop_item_title',   'woocommerce_template_loop_product_thumbnail', 11);
}

// Phlox theme not using the registered image sizes
// Cons of Phlox theme not using registered image sizes:
// 2. Potential performance issues due to unnecessary image resizing
// 3. Reduced compatibility with other plugins that rely on standard image sizes
// 4. Difficulty in maintaining uniform product image displays
// 5. Possible increased server load from custom image generation

if ( ! function_exists( 'auxin_get_the_resized_attachment_src' ) ) {
    function auxin_get_the_resized_attachment_src( $attach_ids = null, $width = null, $height = null ) {
        
        if ( empty( $attach_ids ) ) {
            return false;
        }

        $is_array = is_array( $attach_ids );
        $attach_ids = (array) $attach_ids;

        $srcs = [];
        
        foreach($attach_ids as $attach_id){

        if ( ! wp_attachment_is( 'image', $attach_id ) ) {
            continue;
        }

        // Default size
        $size = 'thumbnail';
        
        // Valid size
        if ( null !== $width && null !== $height ) {
            $size = array( $width, $height );
        }

        // WooCommerce product
        $post_type = get_post_type( wp_get_post_parent_id( $attach_id ) );
        
        if ( in_array( $post_type, array( 'product', 'product_variation' ), true ) ) {
            $size = 'woocommerce_thumbnail';
        }

        // Get the image source
        $image_src = wp_get_attachment_image_src( $attach_id, $size );
        $srcs[] =  $image_src ? $image_src[0] : false;
    }

    if( $is_array ){
        return $srcs;
    }

    return $srcs[0];
}
}