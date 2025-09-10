<?php
/**
 * Ioweb-child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package ioweb-child
 */

add_action( 'wp_enqueue_scripts', 'shoptimizer_parent_theme_enqueue_styles' );

/**
 * Enqueue scripts and styles.
 */
function shoptimizer_parent_theme_enqueue_styles() {
	wp_enqueue_style( 'shoptimizer-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'ioweb-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'shoptimizer-style' )
	);

}


function shoptimizer_loop_product_title_modified() {

	global $post;

	$shoptimizer_layout_woocommerce_display_category = '';
	$shoptimizer_layout_woocommerce_display_category = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_category' );
	?>
	<?php if ( true === $shoptimizer_layout_woocommerce_display_category ) { ?>
		<?php echo '<p class="product__categories">' . wc_get_product_category_list( get_the_id(), ', ', '', '' ) . '</p>'; ?>
	<?php } ?>
	<?php
	echo '<h2 class="woocommerce-loop-product__title"><a href="' . get_the_permalink() . '" title="' . get_the_title() . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">' . get_the_title() . '</a></h2>';
}

function override_shoptimizer(){
	remove_action( 'woocommerce_shop_loop_item_title', 'shoptimizer_loop_product_title', 10);

}

add_action( 'init', 'override_shoptimizer', 1 );
add_action( 'woocommerce_shop_loop_item_title', 'shoptimizer_loop_product_title_modified', 10 );


add_filter(
	'woocommerce_get_image_size_gallery_thumbnail',
	function( $size ) {
		return array(
			'width'  => 150,
			'height' => 150,
			'crop'   => 0,
		);
	}, 100
);
