<?php
/**
 * WooCommerce Template Functions.
 *
 * @package shoptimizer
 */

if ( ! function_exists( 'shoptimizer_woo_cart_available' ) ) {
	/**
	 * Validates whether the Woo Cart instance is available in the request
	 *
	 * @since 2.6.0
	 * @return bool
	 */
	function shoptimizer_woo_cart_available() {
		$woo = WC();
		return $woo instanceof \WooCommerce && $woo->cart instanceof \WC_Cart;
	}
}

add_filter( 'woocommerce_upsell_display_args', 'shoptimizer_woocommerce_upsell_display_args' );

/**
 * Single Product Page - Upsells value via the customizer.
 */
function shoptimizer_woocommerce_upsell_display_args( $args ) {

	$shoptimizer_layout_upsells_amount = '';
	$shoptimizer_layout_upsells_amount = shoptimizer_get_option( 'shoptimizer_layout_upsells_amount' );

	$args['posts_per_page'] = $shoptimizer_layout_upsells_amount;
	$args['columns']        = $shoptimizer_layout_upsells_amount;
	return $args;
}


/**
 * Single Product Page - Change upsells title.
 */

add_filter( 'woocommerce_product_upsells_products_heading', 'shoptimizer_upsells_title' );

function shoptimizer_upsells_title() {

	$shoptimizer_upsells_title_text = shoptimizer_get_option( 'shoptimizer_upsells_title_text' );
	return $shoptimizer_upsells_title_text;
}

/**
 * Single Product Page - Display upsells before related.
 */
add_action( 'after_setup_theme', 'cg_upsells_related', 99 );

function cg_upsells_related() {

	$shoptimizer_layout_woocommerce_upsells_first = '';
	$shoptimizer_layout_woocommerce_upsells_first = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_upsells_first' );

	if ( true === $shoptimizer_layout_woocommerce_upsells_first ) {

		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 25 );
		add_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 18 );

	}
}

/**
 * Single Product Page - Related number via the customizer.
 */
add_filter( 'woocommerce_output_related_products_args', 'shoptimizer_related_products', 99, 3 );

function shoptimizer_related_products( $args ) {

	$shoptimizer_layout_related_amount = '';
	$shoptimizer_layout_related_amount = shoptimizer_get_option( 'shoptimizer_layout_related_amount' );

	$args['posts_per_page'] = $shoptimizer_layout_related_amount;
	$args['columns']        = $shoptimizer_layout_related_amount;
	return $args;
}

/**
 * Cart Page - Change Cross Sells number of columns via the customizer.
 */
function shoptimizer_cross_sells_columns( $columns ) {
	$shoptimizer_layout_cross_sells_amount = '';
	$shoptimizer_layout_cross_sells_amount = shoptimizer_get_option( 'shoptimizer_layout_cross_sells_amount' );
	return $shoptimizer_layout_cross_sells_amount;
}

add_filter( 'woocommerce_cross_sells_total', 'shoptimizer_cross_sells_number' );

/**
 * Cart Page - Change Cross Sells number via the customizer.
 */
function shoptimizer_cross_sells_number( $columns ) {
	$shoptimizer_layout_cross_sells_amount = '';
	$shoptimizer_layout_cross_sells_amount = shoptimizer_get_option( 'shoptimizer_layout_cross_sells_amount' );
	return $shoptimizer_layout_cross_sells_amount;
}

/**
 * Remove default WooCommerce product link open
 *
 * @see get_the_permalink()
 */
function shoptimizer_remove_woocommerce_template_loop_product_link_open() {
	remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
}
add_action( 'wp_head', 'shoptimizer_remove_woocommerce_template_loop_product_link_open' );


/**
 * Remove default WooCommerce product link close
 *
 * @see get_the_permalink()
 */
function shoptimizer_remove_woocommerce_template_loop_product_link_close() {
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
}
add_action( 'wp_head', 'shoptimizer_remove_woocommerce_template_loop_product_link_close' );


/**
 * Open link before the product thumbnail image
 *
 * @see get_the_permalink()
 */
function shoptimizer_template_loop_image_link_open() {
	echo '<a href="' . get_the_permalink() . '" title="' . get_the_title() . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_template_loop_image_link_open', 5 );


/**
 * Close link after the product thumbnail image
 *
 * @see get_the_permalink()
 */
function shoptimizer_template_loop_image_link_close() {
	echo '</a>';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_template_loop_image_link_close', 20 );








/**
 * Register details product_cat meta.
 */
function shoptimizer_product_cat_register_meta() {
    register_meta( 'term', 'below_category_content', 'shoptimizer_sanitize_details' );
}
add_action( 'init', 'shoptimizer_product_cat_register_meta' );

/**
 * Sanitize the details custom meta field.
 *
 * @param  string $details The existing details field.
 * @return string          The sanitized details field
 */
function shoptimizer_sanitize_details( $details ) {
    return is_array( $details ) ? '' : wp_kses_post( $details );
}

/**
 * Add a details metabox to the Add New Product Category page.
 */
function shoptimizer_product_cat_add_details_meta() {
    wp_nonce_field( basename( __FILE__ ), 'shoptimizer_product_cat_details_nonce' );
    ?>
    <div class="form-field">
        <label for="shoptimizer-product-cat-details"><?php esc_html_e( 'Below Category Content', 'shoptimizer' ); ?></label>
        <textarea name="shoptimizer-product-cat-details" id="shoptimizer-product-cat-details" rows="5" cols="40"></textarea>
        <p class="description"><?php esc_html_e( 'Category information which appears below the product list', 'shoptimizer' ); ?></p>
    </div>
    <?php
}
add_action( 'product_cat_add_form_fields', 'shoptimizer_product_cat_add_details_meta' );

/**
 * Add a details metabox to the Edit Product Category page.
 *
 * @param  object $term The existing term object.
 */
function shoptimizer_product_cat_edit_details_meta( $term ) {
    $product_cat_details = get_term_meta( $term->term_id, 'below_category_content', true );
    
    // Backward compatibility check
    if ( empty( $product_cat_details ) ) {
        $product_cat_details_deprecated_obj = get_option( 'taxonomy_' . $term->term_id );
        $product_cat_details = ! empty( $product_cat_details_deprecated_obj['custom_term_meta'] ) 
            ? $product_cat_details_deprecated_obj['custom_term_meta'] 
            : '';
    }

    $settings = array( 
        'textarea_name' => 'shoptimizer-product-cat-details',
        'editor_height' => 200
    );
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="shoptimizer-product-cat-details"><?php esc_html_e( 'Below Category Content', 'shoptimizer' ); ?></label></th>
        <td>
            <?php wp_nonce_field( basename( __FILE__ ), 'shoptimizer_product_cat_details_nonce' ); ?>
            <?php 
            wp_editor( 
                wp_kses_post( $product_cat_details ), 
                'product_cat_details', 
                $settings 
            ); 
            ?>
            <p class="description"><?php esc_html_e( 'Category information which appears below the product list', 'shoptimizer' ); ?></p>
        </td>
    </tr>
    <?php
}
add_action( 'product_cat_edit_form_fields', 'shoptimizer_product_cat_edit_details_meta' );

/**
 * Save Product Category details meta.
 *
 * @param  int $term_id The term ID of the term to update.
 */
function shoptimizer_product_cat_details_meta_save( $term_id ) {
    if ( 
        ! isset( $_POST['shoptimizer_product_cat_details_nonce'] ) || 
        ! wp_verify_nonce( $_POST['shoptimizer_product_cat_details_nonce'], basename( __FILE__ ) ) 
    ) {
        return;
    }

    $old_details = get_term_meta( $term_id, 'below_category_content', true );
    $new_details = isset( $_POST['shoptimizer-product-cat-details'] ) 
        ? $_POST['shoptimizer-product-cat-details'] 
        : '';

    if ( $old_details && '' === $new_details ) {
        delete_term_meta( $term_id, 'below_category_content' );
    } elseif ( $old_details !== $new_details ) {
        update_term_meta(
            $term_id,
            'below_category_content',
            wp_kses_post( $new_details )
        );
    }
}
add_action( 'create_product_cat', 'shoptimizer_product_cat_details_meta_save' );
add_action( 'edit_product_cat', 'shoptimizer_product_cat_details_meta_save' );

/**
 * Display below category content on Product Category archives.
 */
function shoptimizer_product_cat_display_details_meta() {
    if ( ! is_tax( 'product_cat' ) ) {
        return;
    }
    
    $t_id = get_queried_object()->term_id;
    $details = get_term_meta( $t_id, 'below_category_content', true );

    if ( '' !== $details ) {
        ?>
        <div class="below-woocommerce-category">
            <?php
            global $wp_embed;
            add_filter( 'shoptimizer_content_filter', array( $wp_embed, 'autoembed' ) );
            echo apply_filters( 'shoptimizer_content_filter', wp_kses_post( $details ) );
            ?>
        </div>
        <?php
    }
}
add_action( 'woocommerce_after_shop_loop', 'shoptimizer_product_cat_display_details_meta', 40 );

/**
 * Add similar functionality for Product Tags and Brands
 */
function shoptimizer_taxonomy_meta_setup() {
    // Product Tag Setup
    register_meta( 'term', 'below_tag_content', 'shoptimizer_sanitize_details' );
    
    // Product Tag - Add Form
    add_action( 'product_tag_add_form_fields', function() {
        wp_nonce_field( basename( __FILE__ ), 'shoptimizer_product_tag_details_nonce' );
        ?>
        <div class="form-field">
            <label for="shoptimizer-product-tag-details"><?php esc_html_e( 'Below Tag Content', 'shoptimizer' ); ?></label>
            <textarea name="shoptimizer-product-tag-details" id="shoptimizer-product-tag-details" rows="5" cols="40"></textarea>
            <p class="description"><?php esc_html_e( 'Tag information which appears below the product list', 'shoptimizer' ); ?></p>
        </div>
        <?php
    });

    // Product Tag - Edit Form
    add_action( 'product_tag_edit_form_fields', function( $term ) {
        $product_tag_details = get_term_meta( $term->term_id, 'below_tag_content', true );
        $settings = array( 
            'textarea_name' => 'shoptimizer-product-tag-details',
            'editor_height' => 200
        );
        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="shoptimizer-product-tag-details"><?php esc_html_e( 'Below Tag Content', 'shoptimizer' ); ?></label>
            </th>
            <td>
                <?php wp_nonce_field( basename( __FILE__ ), 'shoptimizer_product_tag_details_nonce' ); ?>
                <?php 
                wp_editor( 
                    wp_kses_post( $product_tag_details ), 
                    'product_tag_details', 
                    $settings 
                ); 
                ?>
                <p class="description"><?php esc_html_e( 'Tag information which appears below the product list', 'shoptimizer' ); ?></p>
            </td>
        </tr>
        <?php
    });

    // Product Tag - Save Meta
    $tag_save_callback = function( $term_id ) {
        if ( 
            ! isset( $_POST['shoptimizer_product_tag_details_nonce'] ) || 
            ! wp_verify_nonce( $_POST['shoptimizer_product_tag_details_nonce'], basename( __FILE__ ) ) 
        ) {
            return;
        }

        $new_details = isset( $_POST['shoptimizer-product-tag-details'] ) 
            ? $_POST['shoptimizer-product-tag-details'] 
            : '';

        update_term_meta(
            $term_id,
            'below_tag_content',
            wp_kses_post( $new_details )
        );
    };
    add_action( 'create_product_tag', $tag_save_callback );
    add_action( 'edit_product_tag', $tag_save_callback );

    // Product Tag - Display Meta
    add_action( 'woocommerce_after_shop_loop', function() {
        if ( ! is_tax( 'product_tag' ) ) {
            return;
        }
        
        $t_id = get_queried_object()->term_id;
        $details = get_term_meta( $t_id, 'below_tag_content', true );

        if ( '' !== $details ) {
            ?>
            <div class="below-woocommerce-category">
                <?php
                global $wp_embed;
                add_filter( 'shoptimizer_content_filter', array( $wp_embed, 'autoembed' ) );
                echo apply_filters( 'shoptimizer_content_filter', wp_kses_post( $details ) );
                ?>
            </div>
            <?php
        }
    }, 40 );
}
add_action( 'init', 'shoptimizer_taxonomy_meta_setup' );

/* Brands */

// Product Brand - Setup
register_meta( 'term', 'below_brand_content', 'shoptimizer_sanitize_details' );

// Product Brand - Add Form
add_action( 'product_brand_add_form_fields', function() {
    wp_nonce_field( basename( __FILE__ ), 'shoptimizer_product_brand_details_nonce' );
    ?>
    <div class="form-field">
        <label for="shoptimizer-product-brand-details"><?php esc_html_e( 'Below Brand Content', 'shoptimizer' ); ?></label>
        <textarea name="shoptimizer-product-brand-details" id="shoptimizer-product-brand-details" rows="5" cols="40"></textarea>
        <p class="description"><?php esc_html_e( 'Brand information which appears below the product list', 'shoptimizer' ); ?></p>
    </div>
    <?php
});

// Product Brand - Edit Form
add_action( 'product_brand_edit_form_fields', function( $term ) {
    $product_brand_details = get_term_meta( $term->term_id, 'below_brand_content', true );
    $settings = array( 
        'textarea_name' => 'shoptimizer-product-brand-details',
        'editor_height' => 200
    );
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="shoptimizer-product-brand-details"><?php esc_html_e( 'Below Brand Content', 'shoptimizer' ); ?></label>
        </th>
        <td>
            <?php wp_nonce_field( basename( __FILE__ ), 'shoptimizer_product_brand_details_nonce' ); ?>
            <?php 
            wp_editor( 
                wp_kses_post( $product_brand_details ), 
                'product_brand_details', 
                $settings 
            ); 
            ?>
            <p class="description"><?php esc_html_e( 'Brand information which appears below the product list', 'shoptimizer' ); ?></p>
        </td>
    </tr>
    <?php
});

// Product Brand - Save Meta
$brand_save_callback = function( $term_id ) {
    if ( 
        ! isset( $_POST['shoptimizer_product_brand_details_nonce'] ) || 
        ! wp_verify_nonce( $_POST['shoptimizer_product_brand_details_nonce'], basename( __FILE__ ) ) 
    ) {
        return;
    }

    $new_details = isset( $_POST['shoptimizer-product-brand-details'] ) 
        ? $_POST['shoptimizer-product-brand-details'] 
        : '';

    update_term_meta(
        $term_id,
        'below_brand_content',
        wp_kses_post( $new_details )
    );
};
add_action( 'create_product_brand', $brand_save_callback );
add_action( 'edit_product_brand', $brand_save_callback );

// Product Brand - Display Meta
add_action( 'woocommerce_after_shop_loop', function() {
    if ( ! is_tax( 'product_brand' ) ) {
        return;
    }
    
    $t_id = get_queried_object()->term_id;
    $details = get_term_meta( $t_id, 'below_brand_content', true );

    if ( '' !== $details ) {
        ?>
        <div class="below-woocommerce-category">
            <?php
            global $wp_embed;
            add_filter( 'shoptimizer_content_filter', array( $wp_embed, 'autoembed' ) );
            echo apply_filters( 'shoptimizer_content_filter', wp_kses_post( $details ) );
            ?>
        </div>
        <?php
    }
}, 40 );



if ( ! function_exists( 'shoptimizer_content_filter' ) ) {
	/**
	 * Default content filter
	 *
	 * @param  string $details Post content.
	 * @return string          Post content.
	 */
	function shoptimizer_content_filter( $details ) {
		return $details;
	}
}

/**
 * Adds custom filter that filters the content and is used instead of 'the_content' filter.
 */

global $wp_embed;
add_filter( 'shoptimizer_content_filter', array( $wp_embed, 'run_shortcode' ), 8 );
add_filter( 'shoptimizer_content_filter', array( $wp_embed, 'autoembed'     ), 8 );
add_filter( 'shoptimizer_content_filter', 'wptexturize' );
add_filter( 'shoptimizer_content_filter', 'convert_chars' );
add_filter( 'shoptimizer_content_filter', 'wpautop' );
add_filter( 'shoptimizer_content_filter', 'shortcode_unautop' );
add_filter( 'shoptimizer_content_filter', 'do_shortcode' );

add_filter('shoptimizer_content_filter', 'shoptimizer_remove_empty_tags');
function shoptimizer_remove_empty_tags($content) {
    $pattern = '#<p>(\s|&nbsp;|</?\s?br\s?/?>)*</?p>#';
    $content = preg_replace( $pattern, '', $content );
    return $content;
}

/**
* Checks if ACF is active.
*
* @return boolean
*/
if ( ! function_exists( 'shoptimizer_is_acf_activated' ) ) {
	/**
	 * Query ACF activation.
	 */
	function shoptimizer_is_acf_activated() {
		return class_exists( 'acf' ) ? true : false;
	}
}

/**
 * Before WooCommerce Shop Loop
 * Adds an ACF category banner full width field
 *
 * @since   1.0.0
 * @return  void
 */
add_action( 'shoptimizer_before_content', 'shoptimizer_product_cat_banner', 15 );

if ( ! function_exists( 'shoptimizer_product_cat_banner' ) ) {

	function shoptimizer_product_cat_banner() {

		if ( is_product_category() || is_product_tag() || is_tax( 'product_brand' ) ) {

			$shoptimizer_layout_woocommerce_category_position = '';
			$shoptimizer_layout_woocommerce_category_position = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_category_position' );

			if ( 'below-header' === $shoptimizer_layout_woocommerce_category_position ) {

				$term = get_queried_object();

				if ( shoptimizer_is_acf_activated() ) {
					$categorybanner = get_field( 'category_banner', $term );
				}

				remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
				remove_action( 'woocommerce_archive_description', 'shoptimizer_woocommerce_taxonomy_archive_description' );
				remove_action( 'woocommerce_archive_description', 'shoptimizer_category_image', 20 );
				remove_action( 'woocommerce_before_main_content', 'shoptimizer_archives_title', 20 );

				?>

				<?php if ( ! empty( $categorybanner ) ) : ?>
			<style>
			.shoptimizer-category-banner, .shoptimizer-category-banner.visible {
				background-image: url('<?php echo shoptimizer_safe_html( $categorybanner ); ?>');
			}	
			</style>
			<?php endif; ?>

				<?php if ( ! empty( $categorybanner ) ) { ?>
			<div class="shoptimizer-category-banner lazy-background">
			<?php } else { ?>

			<div class="shoptimizer-category-banner">
			<?php } ?>
				<div class="col-full">
					<h1><?php single_cat_title(); ?></h1>
					<?php the_archive_description( '<div class="taxonomy-description">', '</div>' ); ?>
				</div>
			</div>
				<?php
			}
		}
	}
}


/**
 * Product Archives - Gallery flip image hook
 */
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_gallery_image', 10 );


/**
 * Product Archives - Gallery flip image
 */
function shoptimizer_gallery_image() {

	global $product;
	$attachment_ids = $product->get_gallery_image_ids();

	$shoptimizer_layout_woocommerce_flip_image = '';
	$shoptimizer_layout_woocommerce_flip_image = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_flip_image' );

	if ( true === $shoptimizer_layout_woocommerce_flip_image ) {
		if ( isset( $attachment_ids[0] ) ) {
			echo shoptimizer_safe_html( wp_get_attachment_image( ( $attachment_ids[0] ), 'woocommerce_thumbnail', '', array( 'loading' => 'lazy', 'class' => 'gallery-image' ) ) );
		}
	}
	?>
			
	<?php
}

add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_loop_product_image_wrapper_open', 4 );

function shoptimizer_loop_product_image_wrapper_open() {
	echo '<div class="woocommerce-image__wrapper">';
}

add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_loop_product_image_wrapper_close', 60 );

function shoptimizer_loop_product_image_wrapper_close() {
	echo '</div>';
}

if ( ! function_exists( 'shoptimizer_before_content' ) ) {
	/**
	 * Before Content
	 * Wraps all WooCommerce content in wrappers which match the theme markup
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_before_content() {
		?>
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
		<?php
	}
}

if ( ! function_exists( 'shoptimizer_after_content' ) ) {
	/**
	 * After Content
	 * Closes the wrapping divs
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_after_content() {
		?>
			</main><!-- #main -->
		</div><!-- #primary -->

		<?php
		do_action( 'shoptimizer_sidebar' );
	}
}

if ( ! function_exists( 'shoptimizer_cart_link_fragment' ) ) {
	/**
	 * Cart Fragments
	 * Ensure cart contents update when products are added to the cart via AJAX
	 *
	 * @param  array $fragments Fragments to refresh via AJAX.
	 * @return array            Fragments to refresh via AJAX
	 */
	function shoptimizer_cart_link_fragment( $fragments ) {
		global $woocommerce;

		ob_start();
		shoptimizer_cart_link();
		$fragments['div.shoptimizer-cart'] = ob_get_clean();

		return $fragments;
	}
}


if ( ! function_exists( 'shoptimizer_cart_link' ) ) {
	/**
	 * Cart Link
	 * Displayed a link to the cart including the number of items present and the cart total
	 *
	 * @return void
	 * @since  1.0.0
	 */
	function shoptimizer_cart_link() {

		$shoptimizer_layout_woocommerce_enable_sidebar_cart = '';
		$shoptimizer_layout_woocommerce_enable_sidebar_cart = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_enable_sidebar_cart' );

		$shoptimizer_layout_woocommerce_cart_icon = '';
		$shoptimizer_layout_woocommerce_cart_icon = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_cart_icon' );

		if ( ! shoptimizer_woo_cart_available() ) {
			return;
		}

		?>
			
	<div class="shoptimizer-cart">
		<?php if ( true === $shoptimizer_layout_woocommerce_enable_sidebar_cart ) { ?>
			<a class="cart-contents" role="button" href="#" title="<?php esc_attr_e( 'View your shopping cart', 'shoptimizer' ); ?>">
		<?php } else { ?>
			<a class="cart-contents" role="button" href="<?php echo wc_get_cart_url(); ?>" title="<?php esc_attr_e( 'View your shopping cart', 'shoptimizer' ); ?>">
		<?php } ?>

		<span class="amount"><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span>

		<?php if ( 'basket' === $shoptimizer_layout_woocommerce_cart_icon ) { ?>

		<span class="count"><?php echo wp_kses_post( /* translators: cart count */ sprintf( _n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'shoptimizer' ), WC()->cart->get_cart_contents_count() ) ); ?></span>
		<?php } ?>

		<?php if ( 'cart' === $shoptimizer_layout_woocommerce_cart_icon ) { ?>
		<span class="shoptimizer-cart-icon">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
  				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
			</svg>
			<span class="mini-count"><?php echo wp_kses_data( /* translators: cart count */ sprintf( _n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'shoptimizer' ), WC()->cart->get_cart_contents_count() ) ); ?></span>
		</span>
		<?php } ?>

		<?php if ( 'bag' === $shoptimizer_layout_woocommerce_cart_icon ) { ?>
		<span class="shoptimizer-cart-icon">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
  				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
			</svg>
			<span class="mini-count"><?php echo wp_kses_data( /* translators: cart count */ sprintf( _n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'shoptimizer' ), WC()->cart->get_cart_contents_count() ) ); ?></span>
		</span>
		<?php } ?>

		</a>
	</div>	
		<?php
	}
}


if ( ! function_exists( 'shoptimizer_cart_link_shortcode' ) ) {
	/**
	 * Cart Link Shortcode
	 * Shortcode displayed a link to the cart including the number of items present and the cart total
	 *
	 * @return string
	 * @since  2.7.7
	 */
	function shoptimizer_cart_link_shortcode() {

		$shoptimizer_layout_woocommerce_enable_sidebar_cart = '';
		$shoptimizer_layout_woocommerce_enable_sidebar_cart = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_enable_sidebar_cart' );

		$shoptimizer_layout_woocommerce_cart_icon = '';
		$shoptimizer_layout_woocommerce_cart_icon = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_cart_icon' );

		if ( ! shoptimizer_woo_cart_available() ) {
			return '';
		}

		$html = '<div class="shoptimizer-cart-shortcode"><div class="shoptimizer-cart">';

		if ( true === $shoptimizer_layout_woocommerce_enable_sidebar_cart ) {
			$html .= '<a class="cart-contents" role="button" href="#" title="' . esc_attr__( 'View your shopping cart', 'shoptimizer' ) . '">';
		} else {
			$html .= '<a class="cart-contents" role="button" href="' . wc_get_cart_url() . '" title="' . esc_attr__( 'View your shopping cart', 'shoptimizer' ) . '">';
		}

		$html .= '<span class="amount">' . wp_kses_post( WC()->cart->get_cart_subtotal() ) . '</span>';

		if ( 'basket' === $shoptimizer_layout_woocommerce_cart_icon ) {
			$html .= '<span class="count">' . wp_kses_post( /* translators: cart count */ sprintf( _n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'shoptimizer' ), WC()->cart->get_cart_contents_count() ) ) . '</span>';
		}

		if ( 'cart' === $shoptimizer_layout_woocommerce_cart_icon ) {
			$html .= '<span class="shoptimizer-cart-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg><span class="mini-count">' . wp_kses_data( /* translators: cart count */ sprintf( _n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'shoptimizer' ), WC()->cart->get_cart_contents_count() ) ) . '</span></span>';
		}

		if ( 'bag' === $shoptimizer_layout_woocommerce_cart_icon ) {
			$html .= '<span class="shoptimizer-cart-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg><span class="mini-count">' . wp_kses_data( /* translators: cart count */ sprintf( _n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'shoptimizer' ), WC()->cart->get_cart_contents_count() ) ) . '</span></span>';
		}

		$html .= '</a></div></div>';

		return $html;
	}

	add_shortcode( 'shoptimizer_cart', 'shoptimizer_cart_link_shortcode' );
	add_action( 'hfe_header', 'shoptimizer_header_cart_drawer', 5 ); /* Elementor Header & Footer Builder Plugin */

	/**
	 * Shoptimizer cart register elementor widget
	 * Elementor widget register
	 *
	 * @return void
	 * @since  2.7.7
	 */
	function shoptimizer_cart_register_elementor_widget( $widgets_manager ) {
		require_once get_template_directory() . '/inc/compatibility/elementor-pro/class-shoptimizer-cart-elementor-widget.php';
		$widgets_manager->register( new Shoptimizer_Cart_Elementor_Widget() );
	}

	add_action( 'elementor/widgets/register', 'shoptimizer_cart_register_elementor_widget' );
}


if ( ! function_exists( 'shoptimizer_product_search' ) ) {
	/**
	 * Display Product Search
	 *
	 * @since  1.0.0
	 * @uses  shoptimizer_is_woocommerce_activated() check if WooCommerce is activated
	 * @return void
	 */
	function shoptimizer_product_search() {

			$shoptimizer_layout_search_display = '';
			$shoptimizer_layout_search_display = shoptimizer_get_option( 'shoptimizer_layout_search_display' );

			$shoptimizer_layout_search_display_type = '';
			$shoptimizer_layout_search_display_type = shoptimizer_get_option( 'shoptimizer_layout_search_display_type' );

		if ( isset( $_GET['header'] ) ) {
				$shoptimizer_header_layout = $_GET['header'];
		}

		?>
			
				<?php if ( shoptimizer_is_woocommerce_activated() ) { ?>
					<?php 
					if ( 'enable' === $shoptimizer_layout_search_display ) { ?>

						<?php if ( 'outline' === $shoptimizer_layout_search_display_type ) { ?>
						<div class="site-search type-outline">
						<?php } else { ?>
						<div class="site-search">
						<?php } ?>

							<?php the_widget( 'WC_Widget_Product_Search', 'title=' ); ?>
						</div>
						<?php
					}
					if ( 'ajax-search-wc' === $shoptimizer_layout_search_display ) {
						?>
						<?php if ( 'outline' === $shoptimizer_layout_search_display_type ) { ?>
						<div class="site-search type-outline">
						<?php } else { ?>
						<div class="site-search">
						<?php } ?>
							<?php echo do_shortcode( '[fibosearch]' ); ?>
						</div>
						<?php
					}
					if ( 'advanced-woo-search' === $shoptimizer_layout_search_display ) {
						?>
						<?php if ( 'outline' === $shoptimizer_layout_search_display_type ) { ?>
						<div class="site-search type-outline">
						<?php } else { ?>
						<div class="site-search">
						<?php } ?>
							<?php echo do_shortcode( '[aws_search_form]' ); ?>
						</div>
						<?php
					}
					if ( 'smart-search-pro' === $shoptimizer_layout_search_display ) {
						?>
						<?php if ( 'outline' === $shoptimizer_layout_search_display_type ) { ?>
						<div class="site-search type-outline">
						<?php } else { ?>
						<div class="site-search">
						<?php } ?>
							<?php echo do_shortcode( '[smart_search id="1"]' ); ?>
						</div>
						<?php
					}
					if ( 'yith-search' === $shoptimizer_layout_search_display ) {
						?>
						<?php if ( 'outline' === $shoptimizer_layout_search_display_type ) { ?>
						<div class="site-search type-outline">
						<?php } else { ?>
						<div class="site-search">
						<?php } ?>
							<?php echo do_shortcode( '[yith_woocommerce_ajax_search]' ); ?>
						</div>
						<?php
					}
				}
				if ( 'regular' === $shoptimizer_layout_search_display ) { ?>
					<?php if ( 'outline' === $shoptimizer_layout_search_display_type ) { ?>
						<div class="site-search type-outline">
						<?php } else { ?>
						<div class="site-search">
					<?php } ?>
						<?php get_search_form(); ?>
					</div>
					<?php
				}
				?>
			<?php

	}
}

if ( ! function_exists( 'shoptimizer_header_cart' ) ) {
	/**
	 * Display Header Cart
	 *
	 * @since  1.0.0
	 * @uses  shoptimizer_is_woocommerce_activated() check if WooCommerce is activated
	 * @return void
	 */
	function shoptimizer_header_cart() {
		$shoptimizer_layout_woocommerce_display_cart = '';
		$shoptimizer_layout_woocommerce_display_cart = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_cart' );

		$shoptimizer_layout_search_display = '';
		$shoptimizer_layout_search_display = shoptimizer_get_option( 'shoptimizer_layout_search_display' );

		if ( shoptimizer_is_woocommerce_activated() ) {
			if ( is_cart() ) {
				$class = 'current-menu-item';
			} else {
				$class = '';
			}
			?>
			<?php if ( true === $shoptimizer_layout_woocommerce_display_cart ) { ?>
		
			<nav class="site-header-cart menu" aria-label="Cart contents">
				<?php shoptimizer_cart_link(); ?>
			</nav>
		
		<?php } ?>
			<?php
		}
	}
}

if ( ! function_exists( 'shoptimizer_sidebar_cart_below_text' ) ) {
	/**
	 * Display Below text area Cart Drawer
	 *
	 * @since  1.0.0
	 * @uses  shoptimizer_is_woocommerce_activated() check if WooCommerce is activated
	 * @return void
	 */

	function shoptimizer_sidebar_cart_below_text() {
		$shoptimizer_cart_below_text = shoptimizer_get_option( 'shoptimizer_cart_below_text' );

		if ( $shoptimizer_cart_below_text !== '' ) {
			echo '<div class="cart-drawer-below">';
			echo shoptimizer_safe_html( $shoptimizer_cart_below_text );
			echo '</div>';
		}

	}
}

add_action( 'woocommerce_widget_shopping_cart_after_buttons', 'shoptimizer_sidebar_cart_below_text', 10, 0 );


if ( ! function_exists( 'shoptimizer_header_cart_drawer' ) ) {
	/**
	 * Display Header Cart Drawer
	 *
	 * @since  1.0.0
	 * @uses  shoptimizer_is_woocommerce_activated() check if WooCommerce is activated
	 * @return void
	 */
	function shoptimizer_header_cart_drawer() {
		static $cart_drawer_script = false;

		$shoptimizer_cart_title = shoptimizer_get_option( 'shoptimizer_cart_title' );

		if ( shoptimizer_is_woocommerce_activated() ) {
			if ( is_cart() ) {
				$class = 'current-menu-item';
			} else {
				$class = '';
			}
			?>
		<div tabindex="-1" id="shoptimizerCartDrawer" class="shoptimizer-mini-cart-wrap" role="dialog" aria-label="Cart drawer">
			<div id="ajax-loading">
				<div class="shoptimizer-loader">
					<div class="spinner">
					<div class="bounce1"></div>
					<div class="bounce2"></div>
					<div class="bounce3"></div>
					</div>
				</div>
			</div>
			<div class="cart-drawer-heading"><?php echo shoptimizer_safe_html( $shoptimizer_cart_title ); ?></div>
			<button type="button" aria-label="Close drawer" class="close-drawer">
				<span aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
				</span>
			</button>

				<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>

			</div>

			<?php
				$shoptimizer_cart_drawer_js  = '';
				$shoptimizer_cart_drawer_js .= "
				jQuery( document ).ready( function( $ ) {
					$( 'body' ).on( 'added_to_cart', function( event, fragments, cart_hash ) {
						if ( ! $( 'body' ).hasClass( 'elementor-editor-active' ) ) {
							$( 'body' ).addClass( 'drawer-open' );
							$( '#shoptimizerCartDrawer').focus();
						}
					} );				
				} );
				document.addEventListener( 'DOMContentLoaded', function() {
					document.addEventListener( 'click', function( event ) {
						var is_inner = event.target.closest( '.shoptimizer-mini-cart-wrap' );
						if ( ! event.target.classList.contains( 'shoptimizer-mini-cart-wrap' ) && ! is_inner ) {
							document.querySelector( 'body' ).classList.remove( 'drawer-open' );
						}
						var is_inner2 = event.target.closest( '.shoptimizer-cart' );
						if ( event.target.classList.contains( 'shoptimizer-cart' ) || is_inner2 ) {
							var is_header = event.target.closest( '.site-header-cart' );
							var is_shortcode = event.target.closest( '.shoptimizer-cart-shortcode' );
							if ( is_header || is_shortcode ) {
								event.preventDefault();
								document.querySelector( 'body' ).classList.toggle( 'drawer-open' );
								document.getElementById('shoptimizerCartDrawer').focus();
							}
						}
						if ( event.target.classList.contains( 'close-drawer' ) ) {
							document.querySelector( 'body' ).classList.remove( 'drawer-open' );
						}
					} );
				} );

				// Mini Cart Ajax state.
				document.addEventListener( 'DOMContentLoaded', function() {
				    document.querySelector( '#ajax-loading' ).style.display = 'none';
				} );

				;
				( function( $ ) {
					'use strict';

				    var events_to_monitor = [
				        'wc-ajax=get_refreshed_fragments',
				        'wc-ajax=remove_from_cart',
				    ];

				    function handle_mini_cart_ajax_events( settings, show_loading ) {
				        if ( events_to_monitor.some( function( event ) {
				            return settings.url.indexOf( event ) !== -1;
				        })) {
				            if ( show_loading ) {
				                $( '#ajax-loading' ).css( 'display', 'block' );
				            } else {
				                $( '#ajax-loading' ).css( 'display', 'none' );
				            }
				        }
				    }

				    events_to_monitor.forEach( function( event ) {
				        $( document ).ajaxSend( function( event, jqXHR, settings ) {
				            handle_mini_cart_ajax_events( settings, true ); 
				        });
				        $( document ).ajaxComplete( function( event, jqXHR, settings ) {
				            handle_mini_cart_ajax_events( settings, false );
				        });
				    });
				    
				// Close anon function.
				}( jQuery ) );
				";

			if ( ! $cart_drawer_script ) {
				wp_add_inline_script( 'shoptimizer-main', $shoptimizer_cart_drawer_js );
			}
			$cart_drawer_script = true;
		}
	}
}

if ( ! function_exists( 'shoptimizer_empty_mini_cart' ) ) {
	/**
	 * Empty mini cart
	 * Display the empty mini cart widget area if there are no items in the cart.
	 *
	 * @since   2.5.4
	 * @return  void
	 * @uses    woocommerce_after_mini_cart()
	 */
	function shoptimizer_empty_mini_cart() {
		if ( WC()->cart->is_empty() ) {

			if ( is_active_sidebar( 'empty-mini-cart' ) ) :
				echo '<div class="shoptimizer-empty-mini-cart">';
				dynamic_sidebar( 'empty-mini-cart' );
				echo '</div>';
			endif;

		}
	}
}
add_action( 'woocommerce_before_mini_cart', 'shoptimizer_empty_mini_cart', 20 );


/**
 * Remove view cart button from mini cart.
 */
function shoptimizer_remove_view_cart_minicart() {

	$shoptimizer_sidebar_hide_cart_link = '';
	$shoptimizer_sidebar_hide_cart_link = shoptimizer_get_option( 'shoptimizer_sidebar_hide_cart_link' );

		if ( true === $shoptimizer_sidebar_hide_cart_link ) {
			remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
		}

}
add_action( 'woocommerce_widget_shopping_cart_buttons', 'shoptimizer_remove_view_cart_minicart', 1 );

/**
 * Display/Remove cross-sells from cart page.
 */
function shoptimizer_display_cross_sells() {

	$shoptimizer_display_cross_sells = '';
	$shoptimizer_display_cross_sells = shoptimizer_get_option( 'shoptimizer_display_cross_sells' );

		if ( false === $shoptimizer_display_cross_sells ) {
			remove_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );
		}

}
add_action( 'wp', 'shoptimizer_display_cross_sells' );


if ( ! function_exists( 'shoptimizer_upsell_display' ) ) {
	/**
	 * Upsells
	 * Replace the default upsell function with our own which displays the correct number product columns
	 *
	 * @since   1.0.0
	 * @return  void
	 * @uses    woocommerce_upsell_display()
	 */
	function shoptimizer_upsell_display() {
		$columns = apply_filters( 'shoptimizer_upsells_columns', 4 );
		woocommerce_upsell_display( -1, $columns );
	}
}

if ( ! function_exists( 'shoptimizer_sorting_wrapper' ) ) {
	/**
	 * Sorting wrapper
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_sorting_wrapper() {
		echo '<div class="shoptimizer-sorting">';
	}
}

if ( ! function_exists( 'shoptimizer_sorting_wrapper_end' ) ) {
	/**
	 * Sorting wrapper
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_sorting_wrapper_end() {
		echo '<div class="shoptimizer-sorting sorting-end">';
	}
}

if ( ! function_exists( 'shoptimizer_sorting_wrapper_close' ) ) {
	/**
	 * Sorting wrapper close
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_sorting_wrapper_close() {
		echo '</div>';
	}
}

if ( ! function_exists( 'shoptimizer_product_columns_wrapper' ) ) {
	/**
	 * Product columns wrapper
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_product_columns_wrapper() {
		$columns = shoptimizer_loop_columns();
		echo '<div class="columns-' . absint( $columns ) . '">';
	}
}

if ( ! function_exists( 'shoptimizer_loop_columns' ) ) {
	/**
	 * Default loop columns on product archives
	 *
	 * @return integer products per row
	 * @since  1.0.0
	 */
	function shoptimizer_loop_columns() {
		$columns = 3;

		if ( function_exists( 'wc_get_default_products_per_row' ) ) {
			$columns = wc_get_default_products_per_row();
		}

		return apply_filters( 'shoptimizer_loop_columns', $columns );
	}
}

if ( ! function_exists( 'shoptimizer_product_columns_wrapper_close' ) ) {
	/**
	 * Product columns wrapper close
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_product_columns_wrapper_close() {
		echo '</div>';
	}
}

/**
 * Sets body classes depending on which product alignment has been selected.
 */
function shoptimizer_woocommerce_product_alignment_class( $classes ) {

	$shoptimizer_layout_woocommerce_text_alignment = '';
	$shoptimizer_layout_woocommerce_text_alignment = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_text_alignment' );

	$classes[] = $shoptimizer_layout_woocommerce_text_alignment;
	return $classes;
}

add_filter( 'body_class', 'shoptimizer_woocommerce_product_alignment_class' );

/**
 * Disable Jetpack's Related Posts on Products.
 */
function shoptimizer_exclude_jetpack_related_from_products( $options ) {
	if ( is_product() ) {
		$options['enabled'] = false;
	}

	return $options;
}

add_filter( 'jetpack_relatedposts_filter_options', 'shoptimizer_exclude_jetpack_related_from_products' );


/**
 * Adds a body class if the minimal checkout has been selected.
 */
function shoptimizer_minimal_checkout_body_class( $classes ) {

	$shoptimizer_layout_woocommerce_simple_checkout = '';
	$shoptimizer_layout_woocommerce_simple_checkout = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_simple_checkout' );

	if ( true === $shoptimizer_layout_woocommerce_simple_checkout ) {
		if ( is_checkout() ) {
			$classes[] = 'min-ck';
		}
	}
	return $classes;
}

add_filter( 'body_class', 'shoptimizer_minimal_checkout_body_class' );

/**
 * Adds a body class if the mobile cart page has been selected.
 */
function shoptimizer_mobile_cart_body_class( $classes ) {

	$shoptimizer_layout_woocommerce_mobile_cart_page = '';
	$shoptimizer_layout_woocommerce_mobile_cart_page = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_mobile_cart_page' );

	if ( true === $shoptimizer_layout_woocommerce_mobile_cart_page ) {
		if ( is_cart() ) {
			$classes[] = 'm-cart';
		}
	}
	return $classes;
}

add_filter( 'body_class', 'shoptimizer_mobile_cart_body_class' );


if ( class_exists( 'WooCommerce' ) ) {
	/**
	 * Adds a body class to just the Shop landing page.
	 */
	function shoptimizer_shop_body_class( $classes ) {
		if ( is_shop() ) {
			$classes[] = 'shop';
		}
		return $classes;
	}

	add_filter( 'body_class', 'shoptimizer_shop_body_class' );
}


if ( ! function_exists( 'shoptimizer_breadcrumbs' ) ) {
	/**
	 * Breadcrumbs
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_breadcrumbs() {
		$shoptimizer_layout_woocommerce_display_breadcrumbs = '';
		$shoptimizer_layout_woocommerce_display_breadcrumbs = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_breadcrumbs' );

		$shoptimizer_layout_woocommerce_breadcrumbs_type = '';
		$shoptimizer_layout_woocommerce_breadcrumbs_type = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_breadcrumbs_type' );

		if ( true === $shoptimizer_layout_woocommerce_display_breadcrumbs ) {

			if ( 'default' === $shoptimizer_layout_woocommerce_breadcrumbs_type ) {
				if ( ! is_page_template( 'template-fullwidth-no-heading.php' ) && ! is_page_template( 'template-blank-canvas.php' ) && ! is_page_template( 'template-canvas.php' ) ) {
					add_action( 'shoptimizer_content_top', 'woocommerce_breadcrumb', 10 );
				}
			}

			if ( 'aioseo' === $shoptimizer_layout_woocommerce_breadcrumbs_type ) {

				if ( function_exists( 'aioseo_breadcrumbs' ) ) {
					if ( ! is_page_template( 'template-fullwidth-no-heading.php' ) && ! is_page_template( 'template-blank-canvas.php' ) && ! is_page_template( 'template-canvas.php' ) ) {
						echo '<div class="aioseo woocommerce-breadcrumb">';
						aioseo_breadcrumbs();
						echo '</div>';
					}
				}
			}

			if ( 'rankmath' === $shoptimizer_layout_woocommerce_breadcrumbs_type ) {

				if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
					if ( ! is_page_template( 'template-fullwidth-no-heading.php' ) && ! is_page_template( 'template-blank-canvas.php' ) && ! is_page_template( 'template-canvas.php' ) ) {
						echo '<div class="rankmath woocommerce-breadcrumb">';
						rank_math_the_breadcrumbs();
						echo '</div>';
					}
				}
			}

			if ( 'seoframework' === $shoptimizer_layout_woocommerce_breadcrumbs_type ) {

				if ( ! is_page_template( 'template-fullwidth-no-heading.php' ) && ! is_page_template( 'template-blank-canvas.php' ) && ! is_page_template( 'template-canvas.php' ) ) {
					echo '<div class="seoframework woocommerce-breadcrumb">';
					echo do_shortcode('[tsf_breadcrumb]');
					echo '</div>';
				}
			}

			if ( 'seopress' === $shoptimizer_layout_woocommerce_breadcrumbs_type ) {

				if ( function_exists( 'seopress_display_breadcrumbs' ) ) {
					if ( ! is_page_template( 'template-fullwidth-no-heading.php' ) && ! is_page_template( 'template-blank-canvas.php' ) && ! is_page_template( 'template-canvas.php' ) ) {
						echo '<div class="seopress woocommerce-breadcrumb">';
						seopress_display_breadcrumbs();
						echo '</div>';
					}
				}
			}

			if ( 'yoast' === $shoptimizer_layout_woocommerce_breadcrumbs_type ) {
				if ( function_exists( 'yoast_breadcrumb' ) ) {
					if ( ! is_page_template( 'template-fullwidth-no-heading.php' ) && ! is_page_template( 'template-blank-canvas.php' ) && ! is_page_template( 'template-canvas.php' ) ) {
						yoast_breadcrumb( '<nav class="yoast woocommerce-breadcrumb">', '</nav>' );
					}
				}
			}
		}

	}
}

add_filter(
	'wpseo_breadcrumb_separator',
	function( $separator ) {
		return '<span class="breadcrumb-separator">' . $separator . '</span>';
	}
);


if ( ! function_exists( 'shoptimizer_shop_messages' ) ) {
	/**
	 * Shoptimizer shop messages
	 *
	 * @since   1.0.0
	 */
	function shoptimizer_shop_messages() {
		if ( ! is_checkout() ) {
			echo wp_kses_post( shoptimizer_do_shortcode( 'woocommerce_messages' ) );
		}
	}
}

if ( ! function_exists( 'shoptimizer_woocommerce_pagination' ) ) {
	/**
	 * Shoptimizer WooCommerce Pagination
	 *
	 * @since 1.0.0
	 */
	function shoptimizer_woocommerce_pagination() {
		if ( woocommerce_products_will_display() ) {
			woocommerce_pagination();
		}
	}
}

/**
 * Shop page - show H1 page title for SEO and hide it with CSS.
 */
add_filter( 'woocommerce_show_page_title', 'shoptimizer_show_shop_title' );
function shoptimizer_show_shop_title() {
	if ( is_shop() ) {
		return true;
	}
}


/**
 * Shop page - adds a body class if shop H1 should be visible.
 */
function shoptimizer_shop_heading_class( $classes ) {

	$shoptimizer_layout_shop_title = '';
	$shoptimizer_layout_shop_title = shoptimizer_get_option( 'shoptimizer_layout_shop_title' );

	if ( true === $shoptimizer_layout_shop_title ) {
		if ( is_shop() ) {
			$classes[] = 'shop-heading';
		}
	}
	return $classes;
}

add_filter( 'body_class', 'shoptimizer_shop_heading_class' );


/**
 * Display reviews count on catalog pages.
 */

 $shoptimizer_layout_catalog_reviews_count = '';
 $shoptimizer_layout_catalog_reviews_count = shoptimizer_get_option( 'shoptimizer_layout_catalog_reviews_count' );
 
 if ( true === $shoptimizer_layout_catalog_reviews_count ) {
 
    function shoptimizer_loop_product_get_rating_html( $html, $rating, $count ) {
        if ( 0 < $rating && ! is_product() ) {
            global $product;
            
            // Check if product exists before trying to access its methods
            if ( !is_null($product) && is_object($product) ) {
                $count_html = '';
                $actual_count = $count;
                if ( $product && is_object( $product ) && method_exists( $product, 'get_review_count' ) ) {
                    // Get the actual review count from the product
                    $actual_count = $product->get_review_count();
                
                    $count_html = '<div class="shoptimizer_ratingCount">(' . $actual_count .')</div>';
                }

                $html = '<div class="shoptimizer_ratingContainer"><div class="star-rating">';
                $html .= wc_get_star_rating_html( $rating, $actual_count );
                $html .= '</div>' . $count_html . '</div>';
            }
        }
        return $html;
    }

    add_filter( 'woocommerce_product_get_rating_html', 'shoptimizer_loop_product_get_rating_html', 20, 3 );
}


/**
 * Change Reviews tab title.
 */
function shoptimizer_woocommerce_reviews_tab_title( $title ) {
	$title = strtr(
		$title,
		array(
			'(' => '<span>',
			')' => '</span>',
		)
	);

	return $title;
}
add_filter( 'woocommerce_product_reviews_tab_title', 'shoptimizer_woocommerce_reviews_tab_title' );


/**
 * Display discounted % on product loop.
 */
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_change_displayed_sale_price_html', 7 );
add_action( 'woocommerce_single_product_summary', 'shoptimizer_change_displayed_sale_price_html', 10 );
add_action( 'woocommerce_single_product_summary', 'shoptimizer_clear_product_price', 11 );

if ( ! function_exists( 'shoptimizer_clear_product_price' ) ) {
	/**
	 * Clear product price
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function shoptimizer_clear_product_price() {
		echo '<div class="clear"></div>';
	}
}

/**
 * Shop page - Out of Stock
 */
if ( ! function_exists( 'shoptimizer_shop_out_of_stock' ) ) :
	/**
	 * Add Out of Stock to the Shop page
	 *
	 * @hooked woocommerce_before_shop_loop_item_title - 8
	 *
	 * @since 1.8.5
	 */
	function shoptimizer_shop_out_of_stock() {
		$out_of_stock        = get_post_meta( get_the_ID(), '_stock_status', true );
		$out_of_stock_string = apply_filters( 'shoptimizer_shop_out_of_stock_string', __( 'Out of stock', 'shoptimizer' ) );
		if ( 'outofstock' === $out_of_stock && ! empty( $out_of_stock_string ) ) {
			?>
			<span class="product-out-of-stock"><em><?php echo esc_html( $out_of_stock_string ); ?></em></span>
			<?php
		}
	}

endif;

function shoptimizer_change_displayed_sale_price_html() {

	global $product, $price;
	$shoptimizer_sale_badge = '';

	$shoptimizer_layout_woocommerce_display_badge = '';
	$shoptimizer_layout_woocommerce_display_badge = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_badge' );

	$shoptimizer_layout_woocommerce_display_badge_type = '';
	$shoptimizer_layout_woocommerce_display_badge_type = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_badge_type' );

	if ( $product->is_on_sale() && ! $product->is_type( 'grouped' ) && ! $product->is_type( 'bundle' ) ) {

		if ( $product->is_type( 'variable' ) ) {
			$percentages = array();

			// Get all variation prices.
			$prices = $product->get_variation_prices();

			// Loop through variation prices.
			foreach ( $prices['price'] as $key => $price ) {
				// Only on sale variations.
				if ( $prices['regular_price'][ $key ] !== $price && $prices['regular_price'][ $key ] > 0.005) {
					// Calculate and set in the array the percentage for each variation on sale.
					$percentages[] = round( 100 - ( $prices['sale_price'][ $key ] / $prices['regular_price'][ $key ] * 100 ) );
				}
			}
			// Keep the highest value.
			if ( ! empty( $percentages ) ) {
				$percentage = max( $percentages ); // Keep as number
			}
		} else {
			$percentage = 0;
			$regular_price = (float) $product->get_regular_price();
			if ( $regular_price > 0.005 ) {
				$sale_price    = (float) $product->get_price();
				$percentage = round( 100 - ( $sale_price / $regular_price * 100 ), 0 ); // Remove '%' here
			}
		}

		if ( isset( $percentage ) && $percentage > 0 ) {
			// Add the '%' symbol here instead
			$percentage_display = $percentage . '%';
			
			if ( 'bubble' === $shoptimizer_layout_woocommerce_display_badge_type ) {
				$shoptimizer_sale_badge .= sprintf( __( '<span class="sale-item product-label type-bubble">-%s</span>', 'shoptimizer' ), $percentage_display );
			}
			else {
				$shoptimizer_sale_badge .= sprintf( __( '<span class="sale-item product-label type-circle">-%s</span>', 'shoptimizer' ), $percentage_display );
			}
		}
	}

	if ( true === $shoptimizer_layout_woocommerce_display_badge ) {
		$shoptimizer_sale_badge = apply_filters(
			'shoptimizer_sale_badge_html',
			$shoptimizer_sale_badge,
			$product
		);
		echo shoptimizer_safe_html( $shoptimizer_sale_badge );
	}

}

/**
 * Ajax get variable product sale label prices.
 */
function shoptimizer_get_sale_prices() {
	$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : 0;
	$ajax       = array();
	$percents   = array();
	if ( $product_id ) {
		$_product = wc_get_product( $product_id );
		if ( $_product && $_product->is_type( 'variable' ) ) {
			$prices = $_product->get_variation_prices();
			if ( count( $prices ) ) {
				foreach ( $prices['price'] as $variation_id => $price ) {
					$sale_price    = $prices['sale_price'][ $variation_id ];
					$regular_price = $prices['regular_price'][ $variation_id ];
					if ( $regular_price !== $price ) {
						$percentage = round( 100 - ( $sale_price / $regular_price * 100 ) );
						if ( $percentage ) {
							$percents[ $variation_id ] = '-' . $percentage . '%';
						}
					}
				}
			}
		}
	}
	$ajax['percents'] = $percents;

	wp_send_json( $ajax );
}
add_action( 'wp_ajax_shoptimizer_get_sale_prices', 'shoptimizer_get_sale_prices' );
add_action( 'wp_ajax_nopriv_shoptimizer_get_sale_prices', 'shoptimizer_get_sale_prices' );

/**
 * Get variable product sale label prices script.
 */
function shoptimizer_get_sale_prices_script(){
	global $product;
	if ( ! is_product() ) {
		return;
	}
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}
	if ( ! $product->is_type( 'variable' ) ) {
		return;
	}
	if ( ! $product->is_on_sale() ) {
		return;
	}
	$shoptimizer_layout_woocommerce_display_badge = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_badge' );
	if ( true !== $shoptimizer_layout_woocommerce_display_badge ) {
		return;
	}
	?>
<script type="text/javascript">
var shoptimizer_sales = null;
jQuery( document ).ready( function( $ ) {
	var shoptimizer_sale_lbl = $( '.summary .sale-item.product-label' );
	shoptimizer_sale_lbl.css( 'visibility', 'hidden' );
	$.ajax( {
		type: 'POST',
		url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
		data: { 'action': 'shoptimizer_get_sale_prices', 'product_id': <?php echo esc_attr( $product->get_id() ); ?> },
		success: function( json ) {
			shoptimizer_sales = json.percents;
			shoptimizer_update_variable_sale_badge();
		}
	} );
	$( '.summary input.variation_id' ).change( function() {
		shoptimizer_update_variable_sale_badge();
	} );
	function shoptimizer_update_variable_sale_badge() {
		var variation_id = $( '.summary input.variation_id' ).val();
		if ( '' != variation_id && shoptimizer_sales && shoptimizer_sales.hasOwnProperty( variation_id ) ) {
			shoptimizer_sale_lbl.html( shoptimizer_sales[variation_id] ).css( 'visibility', 'visible' );	
		} else {
			shoptimizer_sale_lbl.css( 'visibility', 'hidden' );
		}
	}
} );
</script>
	<?php
}
add_action( 'wp_footer', 'shoptimizer_get_sale_prices_script', 999 );

add_action( 'woocommerce_shop_loop_item_title', 'shoptimizer_loop_product_content_header_open', 5 );

function shoptimizer_loop_product_content_header_open() {
	echo '<div class="woocommerce-card__header">';
}

add_action( 'woocommerce_after_shop_loop_item', 'shoptimizer_loop_product_content_header_close', 60 );

function shoptimizer_loop_product_content_header_close() {
	echo '</div>';
}

/**
 * Within Product Loop - remove title hook and create a new one with the category displayed above it.
 */
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
add_action( 'woocommerce_shop_loop_item_title', 'shoptimizer_loop_product_title', 10 );

function shoptimizer_loop_product_title() {

	global $post;

	$shoptimizer_layout_woocommerce_display_category = '';
	$shoptimizer_layout_woocommerce_display_category = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_category' );
	?>
		<?php if ( true === $shoptimizer_layout_woocommerce_display_category ) { ?>
			<?php echo '<p class="product__categories">' . wc_get_product_category_list( get_the_id(), ', ', '', '' ) . '</p>'; ?>
		<?php } ?>
		<?php
		echo '<div class="woocommerce-loop-product__title"><a tabindex="0" href="' . get_the_permalink() . '" aria-label="' . get_the_title() . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">' . get_the_title() . '</a></div>';
}

/**
 * Single Product Page - Previous/Next hover feature.
 */
add_action( 'woocommerce_single_product_summary', 'shoptimizer_prev_next_product', 0 );

function get_adjacent_in_stock_product($in_same_term = false, $excluded_terms = '', $previous = true, $taxonomy = 'product_cat') {
	global $post;

	$args = array(
		'post_type' => 'product',
		'posts_per_page' => 1,
		'post_status' => 'publish',
		'meta_query' => array(
			array(
				'key' => '_stock_status',
				'value' => 'instock'
			)
		),
		'date_query' => array(
			array(
				$previous ? 'before' : 'after' => $post->post_date
			)
		),
		'order' => $previous ? 'DESC' : 'ASC',
		'orderby' => 'date'
	);

	if ($in_same_term && !empty($taxonomy)) {
		$terms = get_the_terms($post->ID, $taxonomy);
		if ($terms && !is_wp_error($terms)) {
			$term_ids = wp_list_pluck($terms, 'term_id');
			$args['tax_query'] = array(
				array(
					'taxonomy' => $taxonomy,
					'field' => 'term_id',
					'terms' => $term_ids,
					'include_children' => false
				)
			);
		}
	}

	$query = new WP_Query($args);
	
	if ($query->have_posts()) {
		return wc_get_product($query->posts[0]->ID);
	}

	return false;
}

function shoptimizer_prev_next_product() {
    global $post;

    $shoptimizer_layout_woocommerce_prev_next_display = '';
    $shoptimizer_layout_woocommerce_prev_next_display = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_prev_next_display' );

    if ( true === $shoptimizer_layout_woocommerce_prev_next_display ) {
        // Get adjacent products
        $prev_product = get_adjacent_in_stock_product(true, '', true, 'product_cat');
        $next_product = get_adjacent_in_stock_product(true, '', false, 'product_cat');

        // Only output if we have products to show
        if ($prev_product || $next_product) {
            ?>
            <div class="shoptimizer-product-prevnext">
                <?php if ($prev_product) : ?>
                    <a href="<?php echo esc_url($prev_product->get_permalink()); ?>" 
                       aria-label="<?php echo esc_attr($prev_product->get_name()); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                  d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                        </svg>
                        <div class="tooltip">
                            <?php echo wp_kses_post($prev_product->get_image()); ?>
                            <span class="title"><?php echo esc_html($prev_product->get_name()); ?></span>
                            <span class="prevnext_price"><?php echo wp_kses_post($prev_product->get_price_html()); ?></span>
                        </div>
                    </a>
                <?php endif; ?>

                <?php if ($next_product) : ?>
                    <a href="<?php echo esc_url($next_product->get_permalink()); ?>" 
                       aria-label="<?php echo esc_attr($next_product->get_name()); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                  d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="tooltip">
                            <?php echo wp_kses_post($next_product->get_image()); ?>
                            <span class="title"><?php echo esc_html($next_product->get_name()); ?></span>
                            <span class="prevnext_price"><?php echo wp_kses_post($next_product->get_price_html()); ?></span>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
            <?php
        }
    }
}


/**
 * Single Product Page - Call me back trigger.
 */
add_action( 'woocommerce_single_product_summary', 'shoptimizer_call_back_trigger', 79 );

if ( ! function_exists( 'shoptimizer_call_back_trigger' ) ) {
	function shoptimizer_call_back_trigger() {

		$shoptimizer_layout_floating_button_display = '';
		$shoptimizer_layout_floating_button_display = shoptimizer_get_option( 'shoptimizer_layout_floating_button_display' );

		$shoptimizer_layout_floating_button_text = shoptimizer_get_option( 'shoptimizer_layout_floating_button_text' );

		if ( true === $shoptimizer_layout_floating_button_display ) {
			echo '
			<div class="call-back-feature">
				<button data-trigger="callBack">';
				echo shoptimizer_safe_html( $shoptimizer_layout_floating_button_text );
				echo '</button>
			</div>';
		}
		?>

		<?php
	}
}

/**
 * Single Product Page - Call me back modal.
 */
add_action( 'woocommerce_single_product_summary', 'shoptimizer_call_back_modal', 80 );

if ( ! function_exists( 'shoptimizer_call_back_modal' ) ) {
	function shoptimizer_call_back_modal() {

		$shoptimizer_layout_floating_button_display = '';
		$shoptimizer_layout_floating_button_display = shoptimizer_get_option( 'shoptimizer_layout_floating_button_display' );

		global $woocommerce, $product;
		$id = $product->get_id();

		if ( true === $shoptimizer_layout_floating_button_display ) { ?>
		
		<dialog class="shoptimizer-modal" data-shoptimizermodal-id="callBack" aria-label="Request a call back modal">
            <div class="shoptimizer-modal--container">
	            <form method="dialog">
	                <button aria-label="Close modal" class="shoptimizer-modal--button_close" data-dismiss="modal">
	                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
	                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
	                    </svg>                      
	                </button>
	            </form>

                <div class="shoptimizer-modal--content">
                    <div class="callback-product_wrapper">
			  		<?php echo wp_kses_post( woocommerce_get_product_thumbnail( "woocommerce_gallery_thumbnail" ) ); ?>
			  			<div class="callback-product_content">
				  			<div class="callback-product_title">
				  				<?php the_title(); ?>
				  			</div>

							<?php
								$count = $product->get_review_count();
								if ( $count && wc_review_ratings_enabled() ) {
									echo '<div class="callback-product_rating">';
									echo wc_get_rating_html( $product->get_average_rating() );
									echo '</div>';
								}
							?>
									
							<div class="callback-product_price">
								<?php echo shoptimizer_safe_html( $product->get_price_html() ); ?>
							</div>
						</div>
					</div>
						  
					<?php dynamic_sidebar( "floating-button-content" ); ?>
           		</div>
        	</div>
        </dialog>	

		<?php
	} }
}


/**
 * Single Product - exclude from Jetpack's Lazy Load.
 */
function is_lazyload_activated() {
	$condition = is_product();
	if ( $condition ) {
		return false;
	} return true;
}

add_filter( 'lazyload_is_enabled', 'is_lazyload_activated', 10, 3 );


/**
 * Variation selected highlight
 *
 * @since 1.6.1
 */
add_action( 'woocommerce_before_add_to_cart_quantity', 'shoptimizer_highlight_selected_variation' );

function shoptimizer_highlight_selected_variation() {

	global $product;

	if ( $product->is_type( 'variable' ) ) {

		?>
	 <script>
document.addEventListener( 'DOMContentLoaded', function() {
	var vari_labels = document.querySelectorAll('.variations .label label');
	vari_labels.forEach( function( vari_label ) {
		vari_label.innerHTML = '<span>' + vari_label.innerHTML + '</span>';
	} );

	var vari_values = document.querySelectorAll('.value');
	vari_values.forEach( function( vari_value ) {
		vari_value.addEventListener( 'change', function( event ) {
			var $this = event.target;
			if ( $this.selectedIndex != 0 ) {
				$this.closest( 'tr' ).classList.add( 'selected-variation' );
			} else {
				$this.closest( 'tr' ).classList.remove( 'selected-variation' );
			}
		} );
	} );

	document.addEventListener('click', function( event ){
		if ( event.target.classList.contains( 'reset_variations' ) ) {
			var vari_classs = document.querySelectorAll('.variations tr.selected-variation');
			vari_classs.forEach( function( vari_class ) {
				vari_class.classList.remove( 'selected-variation' );
			} );
		}
	} );
} );
</script>
		<?php

	}

}

/**
 * Product Archives - move title.
 */
function shoptimizer_archives_title() {

	if ( is_product_category() || is_product_tag() || is_tax( 'product_brand' ) || is_product_taxonomy() ) {
		echo '<h1 class="woocommerce-products-header__title page-title">';
		woocommerce_page_title();
		echo '</h1>';
	}

}

/**
 * Display WooCommerce product category description on all category archive pages.
 */
function shoptimizer_woocommerce_taxonomy_archive_description() {

	$shoptimizer_layout_woocommerce_category_description = '';
	$shoptimizer_layout_woocommerce_category_description = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_category_description' );

		if ( true === $shoptimizer_layout_woocommerce_category_description ) {

			if ( is_tax( array( 'product_cat', 'product_tag' ) ) && get_query_var( 'paged' ) !== 0 ) {
				$description = wc_format_content( term_description() );
				if ( $description ) {
					echo '<div class="term-description">' . $description . '</div>';
				}
			}
		}

}
add_action( 'woocommerce_archive_description', 'shoptimizer_woocommerce_taxonomy_archive_description' );

/**
 * Removes default WooCommerce category description.
 *
 * @since   1.0.0
 * @return  void
 */
add_action( 'after_setup_theme', 'shoptimizer_remove_archive_description', 99 );
function shoptimizer_remove_archive_description() {

	$shoptimizer_layout_woocommerce_category_description = '';
	$shoptimizer_layout_woocommerce_category_description = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_category_description' );

	if ( false === $shoptimizer_layout_woocommerce_category_description ) {
		remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
		remove_action( 'woocommerce_archive_description', 'shoptimizer_woocommerce_taxonomy_archive_description' );
	}
}

/**
 * Show cart widget on all pages.
 */
add_filter( 'woocommerce_widget_cart_is_hidden', 'shoptimizer_always_show_cart', 40, 0 );

/**
 * Function to always show cart.
 */
function shoptimizer_always_show_cart() {
	return false;
}


/**
 * Checks if the current page is a product archive
 *
 * @return boolean
 */
function shoptimizer_is_product_archive() {
	if ( shoptimizer_is_woocommerce_activated() ) {
		if ( is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag() ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/**
 * Product Archives - Mobile filters
 */
add_action( 'woocommerce_before_shop_loop', 'shoptimizer_mobile_filters', 5 );

add_action( 'shoptimizer_woocommerce_archives_template_before', 'shoptimizer_mobile_filters', 5 );

function shoptimizer_mobile_filters() {
	if ( is_active_sidebar( 'sidebar-1' ) ) {

		$shoptimizer_layout_woocommerce_sidebar = '';
		$shoptimizer_layout_woocommerce_sidebar = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_sidebar' );

		if ( 'no-woocommerce-sidebar' !== $shoptimizer_layout_woocommerce_sidebar ) {

			echo '<button class="mobile-filter shoptimizer-mobile-toggle"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
</svg>';
			?>
			<?php esc_html_e( 'Show Filters', 'shoptimizer' ); ?>
			<?php
			echo '</button>';

		}
		?>
		<?php
	}
}


if ( class_exists( 'WooCommerce' ) ) {
	add_action( 'get_header', 'shoptimizer_remove_product_sidebar' );

	/**
	 * Remove sidebar on a single product page.
	 */
	function shoptimizer_remove_product_sidebar() {
		if ( is_product() ) {
			remove_action( 'shoptimizer_sidebar', 'shoptimizer_get_sidebar', 10 );
		}
	}
}

/**
 * Product Archives - Reorder default sale badge hook position if active.
 */
$shoptimizer_layout_woocommerce_display_badge = '';
$shoptimizer_layout_woocommerce_display_badge = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_badge' );

if ( false === $shoptimizer_layout_woocommerce_display_badge ) {
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
	add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 7 );
}

/**
 * Single Product Page - Add a section wrapper start.
 */
add_action( 'woocommerce_before_single_product_summary', 'shoptimizer_product_content_wrapper_start', 5 );
function shoptimizer_product_content_wrapper_start() {
	echo '<div class="product-details-wrapper">';
}

/**
 * Single Product Page - Add a section wrapper end.
 */
add_action( 'woocommerce_single_product_summary', 'shoptimizer_product_content_wrapper_end', 120 );
function shoptimizer_product_content_wrapper_end() {
	echo '</div><!--/product-details-wrapper-end-->';
}

/**
 * Single Product - Display custom content below Buy Now Button
 */
add_action( 'woocommerce_single_product_summary', 'shoptimizer_product_custom_content', 45 );

/**
 * Custom markup around single product field - if in stock.
 */
function shoptimizer_product_custom_content() {
	$shoptimizer_disable_pdp_custom_widget = get_post_meta( get_the_ID(), 'shoptimizer-disable-pdp-custom-widget', true );

	if ( 'disabled' !== $shoptimizer_disable_pdp_custom_widget ) {
		if ( is_active_sidebar( 'single-product-field' ) ) :
			echo '<div class="product-widget">';
			dynamic_sidebar( 'single-product-field' );
			echo '</div>';
		endif;
	}
}

add_action( 'woocommerce_after_single_product_summary', 'shoptimizer_related_content_wrapper_start', 10 );
add_action( 'woocommerce_after_single_product_summary', 'shoptimizer_related_content_wrapper_end', 60 );

/**
 * Single Product Page - Related products section wrapper start.
 */
function shoptimizer_related_content_wrapper_start() {
	echo '<section class="related-wrapper">';
}

/**
 * Single Product Page - Reorder Upsells position.
 */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
add_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 25 );

/**
 * Single Product Page - Related products section wrapper end.
 */
function shoptimizer_related_content_wrapper_end() {
	echo '</section>';
}

/**
 * Single Product Page - Reorder Rating position.
 */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 20 );

/**
 * Single Product Page - Added to cart message.
 */
/*function shoptimizer_add_to_cart_message( $message, $products, $show_qty ) {
	return '<a href="'.esc_url( wc_get_checkout_url() ).'" tabindex="1" class="button checkout wc-forward">'.esc_html__( 'Checkout', 'woocommerce' ).'</a> '. $message;
}
add_filter('wc_add_to_cart_message_html', 'shoptimizer_add_to_cart_message', 10, 3);*/


/**
 * Single Product Page - Added to cart message.
 */
add_filter( 'wc_add_to_cart_message_html', 'shoptimizer_add_to_cart_message_filter', 10, 2 );

function shoptimizer_add_to_cart_message_filter( $message ) {

    // Check if the current page is a single product page
    //if ( is_product() ) {
        $shoptimizer_message = sprintf(
            '<div class="message-inner"><div class="message-content">%s </div><div class="buttons-wrapper"><a href="%s" class="button checkout"><span>%s</span></a> <a href="%s" class="button cart"><span>%s</span></a></div></div>',
            shoptimizer_safe_html( $message ),
            esc_url( wc_get_page_permalink( 'checkout' ) ),
            esc_html__( 'Checkout', 'shoptimizer' ),
            esc_url( wc_get_page_permalink( 'cart' ) ),
            esc_html__( 'View Cart', 'shoptimizer' )
        );

        return $shoptimizer_message;
    //}

    // Return the default message if not on a single product page
    return $message;
}



if ( ! function_exists( 'shoptimizer_pdp_ajax_atc' ) ) {
	/**
	 * PDP/Single product ajax add to cart.
	 */
	function shoptimizer_pdp_ajax_atc() {
		$sku = '';
		if ( isset( $_POST['variation_id'] ) ) {
			$sku = $_POST['variation_id'];
		}
		$product_id = $_POST['add-to-cart'];
		if ( empty( $sku ) ) {
			$sku = $product_id;
		}

		ob_start();
		wc_print_notices();
		$notices = ob_get_clean();
		ob_start();
		woocommerce_mini_cart();
		$shoptimizer_mini_cart = ob_get_clean();
		$shoptimizer_atc_data  = array(
			'notices'   => $notices,
			'fragments' => apply_filters(
				'woocommerce_add_to_cart_fragments',
				array(
					'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $shoptimizer_mini_cart . '</div>',
				)
			),
			'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
		);
		// if GA Pro is installed, send an atc event.
		//if ( class_exists( 'WC_Google_Analytics_Pro' ) ) {
		//	wc_google_analytics_pro()->get_integration()->ajax_added_to_cart( $sku );
		//}
		do_action( 'woocommerce_ajax_added_to_cart', $sku );
		
		wp_send_json( $shoptimizer_atc_data );
		die();
	}
}

$shoptimizer_layout_woocommerce_single_product_ajax = '';
$shoptimizer_layout_woocommerce_single_product_ajax = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_single_product_ajax' );
if ( true === $shoptimizer_layout_woocommerce_single_product_ajax ) {
	add_action( 'wc_ajax_shoptimizer_pdp_ajax_atc', 'shoptimizer_pdp_ajax_atc' );
	add_action( 'wc_ajax_nopriv_shoptimizer_pdp_ajax_atc', 'shoptimizer_pdp_ajax_atc' );
}

if ( ! function_exists( 'shoptimizer_pdp_ajax_atc_enqueue' ) ) {

	/**
	 * Enqueue assets for PDP/Single product ajax add to cart.
	 */
	function shoptimizer_pdp_ajax_atc_enqueue() {
		global $shoptimizer_version;

		if ( is_product() ) {

			wp_enqueue_script( 'shoptimizer-ajax-script', get_template_directory_uri() . '/assets/js/single-product-ajax.js', array(), $shoptimizer_version, true );
			wp_localize_script(
				'shoptimizer-ajax-script',
				'shoptimizer_ajax_obj',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'ajax-nonce' ),
				)
			);
		}
	}
}

$shoptimizer_layout_woocommerce_single_product_ajax = '';
$shoptimizer_layout_woocommerce_single_product_ajax = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_single_product_ajax' );

if ( true === $shoptimizer_layout_woocommerce_single_product_ajax ) {
	add_action( 'wp_enqueue_scripts', 'shoptimizer_pdp_ajax_atc_enqueue' );
}


/**
 * Enable block editor on PDPs.
 */
$shoptimizer_layout_pdp_block_editor = '';
$shoptimizer_layout_pdp_block_editor = shoptimizer_get_option( 'shoptimizer_layout_pdp_block_editor' );

if ( true === $shoptimizer_layout_pdp_block_editor ) {
	
	// Enable Gutenberg editor for WooCommerce
	function shoptimizer_activate_gutenberg_product( $can_edit, $post_type ) {
	 if ( $post_type == 'product' ) {
	        $can_edit = true;
	    }
	    return $can_edit;
	}
	add_filter( 'use_block_editor_for_post_type', 'shoptimizer_activate_gutenberg_product', 10, 2 );

}


/**
 * PDP Short description position. Hook in at p9 so we can override woocommerce_template_single_excerpt via metaboxes if needed.
 */
add_action( 'woocommerce_single_product_summary', 'shoptimizer_pdp_short_description_position', 9 );

function shoptimizer_pdp_short_description_position() {
	global $post;
	$shoptimizer_layout_pdp_short_description_position = '';
	$shoptimizer_layout_pdp_short_description_position = shoptimizer_get_post_meta( 'shoptimizer_layout_pdp_short_description_position' );

	if ( 'bottom' === $shoptimizer_layout_pdp_short_description_position ) {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 50 );
	}
}


/**
 * Disable Block Editor for Widgets.
 */
$shoptimizer_widgets_disable_block_editor = '';
$shoptimizer_widgets_disable_block_editor = shoptimizer_get_option( 'shoptimizer_widgets_disable_block_editor' );

if ( true === $shoptimizer_widgets_disable_block_editor ) {

	function shoptimizer_widgets_disable_block_editor() {
	    remove_theme_support( 'widgets-block-editor' );
	}
	add_action( 'after_setup_theme', 'shoptimizer_widgets_disable_block_editor' );
}


/**
 * Cart Page - Custom widget below the primary button.
 */
add_action( 'woocommerce_after_cart_totals', 'shoptimizer_cart_custom_field', 15 );


/**
 * Custom markup around cart field.
 */
function shoptimizer_cart_custom_field() {

	if ( is_active_sidebar( 'cart-field' ) ) :
		echo '<div class="cart-custom-field">';
		dynamic_sidebar( 'cart-field' );
		echo '</div>';
	endif;

}


/**
 * Cart Page - Custom widget below the summary.
 */
add_action( 'woocommerce_after_cart_table', 'shoptimizer_cart_custom_summary', 50 );

/**
 * Custom markup around cart field.
 */
function shoptimizer_cart_custom_summary() {

	if ( is_active_sidebar( 'cart-summary' ) ) :
		echo '<div class="cart-summary">';
		dynamic_sidebar( 'cart-summary' );
		echo '</div>';
	endif;

}


/**
 * Cart, Checkout, My Account - Remove sidebar.
 */
add_action( 'wp', 'shoptimizer_remove_woo_sidebar', 20 );

function shoptimizer_remove_woo_sidebar() {
	if ( is_cart() || is_checkout() || is_account_page() ) {
		remove_action( 'shoptimizer_page_sidebar', 'shoptimizer_pages_sidebar', 10 );
	}
}


/**
 * Cart wrapper open.
 */
function shoptimizer_cart_wrapper_open() {
	echo '<section class="shoptimizer-cart-wrapper">';
}

/**
 * Cart wrapper close.
 */

function shoptimizer_cart_wrapper_close() {
	echo '</section>';
}

add_action( 'woocommerce_before_cart', 'shoptimizer_cart_wrapper_open', 20 );
add_action( 'woocommerce_after_cart', 'shoptimizer_cart_wrapper_close', 10 );


/**
 * Add Progress Bar to the Cart and Checkout pages.
 */
add_action( 'woocommerce_before_cart', 'shoptimizer_cart_progress' );
add_action( 'woocommerce_before_checkout_form', 'shoptimizer_cart_progress', 5 );

if ( ! function_exists( 'shoptimizer_cart_progress' ) ) {

	/**
	 * More product info
	 * Link to product
	 *
	 * @return void
	 * @since  1.0.0
	 */
	function shoptimizer_cart_progress() {

		$shoptimizer_layout_progress_bar_display = '';
		$shoptimizer_layout_progress_bar_display = shoptimizer_get_option( 'shoptimizer_layout_progress_bar_display' );

		if ( true === $shoptimizer_layout_progress_bar_display ) {
			?>

			<div class="checkout-wrap">
			<ul class="checkout-bar">
				<li class="active first"><span>
				<a href="<?php echo get_permalink( wc_get_page_id( 'cart' ) ); ?>"><?php esc_html_e( 'Shopping Cart', 'shoptimizer' ); ?></a></span>
				</li>
				<li class="next">
				<span>
				<a href="<?php echo get_permalink( wc_get_page_id( 'checkout' ) ); ?>"><?php esc_html_e( 'Shipping and Checkout', 'shoptimizer' ); ?></a></span></li>
				<li><span><?php esc_html_e( 'Confirmation', 'shoptimizer' ); ?></span></li>
				
			</ul>
			</div>
			<?php

		}
		?>
		<?php

	}
}// End if().


add_action( 'woocommerce_review_order_after_payment', 'shoptimizer_checkout_custom_field', 15 );

/**
 * Checkout Page - Custom widget below the primary button.
 */
function shoptimizer_checkout_custom_field() {

	if ( is_active_sidebar( 'checkout-field' ) ) :
		echo '<div class="cart-custom-field">';
		dynamic_sidebar( 'checkout-field' );
		echo '</div>';
	endif;

}


/**
 * Custom coupon code start markup.
 */
function shoptimizer_coupon_wrapper_start() {
	echo '<section class="coupon-wrapper">';
}


/**
 * Custom coupon code end markup.
 */

function shoptimizer_coupon_wrapper_end() {
	echo '</section>';
}


/**
 * Single Product Page - Reorder sale message.
 */
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_show_product_sale_flash', 3 );

add_filter( 'shoptimizer_product_thumbnail_columns', 'shoptimizer_gallery_columns' );

/**
 * Single Product Page - Change gallery thumbnails to one column.
 */
function shoptimizer_gallery_columns() {
	return 1;
}


add_filter( 'woocommerce_single_product_carousel_options', 'shoptimizer_flexslider_options' );
/**
 * Single Product Page - Include navigation arrows within the core PDP slider.
 */
function shoptimizer_flexslider_options( $options ) {
	$options['directionNav'] = true;
	return $options;
}

add_action( 'woocommerce_archive_description', 'shoptimizer_category_image', 20 );


/**
 * Display Category image on Category archive.
 */
function shoptimizer_category_image() {

$shoptimizer_layout_woocommerce_category_image = '';
$shoptimizer_layout_woocommerce_category_image = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_category_image' );

	if ( true === $shoptimizer_layout_woocommerce_category_image ) {

		if ( is_product_category() || is_tax( 'product_brand' ) ) {
			global $wp_query;
			$cat              = $wp_query->get_queried_object();
			$thumbnail_id     = get_term_meta( $cat->term_id, 'thumbnail_id', true );
			$image            = wp_get_attachment_url( $thumbnail_id );
			$image_attributes = wp_get_attachment_image_src( $thumbnail_id, 'full' );
			$alt_txt          = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
			if ( empty( $alt_txt ) ) {
				$alt_txt = $cat->name;
			}
			if ( $image ) {
				echo '<img class="cg-cat-image" src="' . $image . '" alt="' . $alt_txt . '" width="' . $image_attributes[1] . '" height="' . $image_attributes[2] . '" />';
			}
		}
	}
}


/**
 * Include product thumbnails in Checkout Summary table.
 */
add_filter( 'woocommerce_cart_item_name', 'shoptimizer_product_thumbnail_in_checkout', 20, 3 );
function shoptimizer_product_thumbnail_in_checkout( $product_name, $cart_item, $cart_item_key ) {
	if ( is_checkout() ) {
		$thumbnail      = $cart_item['data']->get_image('woocommerce_gallery_thumbnail', array('class' => 'skip-lazy'));
		$image_html     = '<div class="product-item-thumbnail">' . $thumbnail . '</div> ';
		$name_html_open = '<div class="cg-checkout-table-product-name">';
		$product_name   = $image_html . $name_html_open . $product_name;
	}
	return $product_name;
}

/**
 * Change the markup for the cart table on checkout page.
 */
add_filter( 'woocommerce_checkout_cart_item_quantity', 'shoptimizer_woocommerce_checkout_cart_item_quantity', 10, 3 );
function shoptimizer_woocommerce_checkout_cart_item_quantity( $strong_class_product_quantity_sprintf_times_s_cart_item_quantity_strong, $cart_item, $cart_item_key ) {

	$quantity_html_close       = '<div class="clear"></div></div>';
	$shoptimizer_cart_quantity = $strong_class_product_quantity_sprintf_times_s_cart_item_quantity_strong . $quantity_html_close;
	return $shoptimizer_cart_quantity;
}


/**
 * Cross Sells (Cart) Rearrange.
 */
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );
add_filter( 'woocommerce_cross_sells_columns', 'shoptimizer_cross_sells_columns' );


/**
 * WooCommerce Shop/Category/Tag sidebar body class.
 */
add_filter( 'body_class', 'shoptimizer_woocommerce_sidebar_body_class' );
function shoptimizer_woocommerce_sidebar_body_class( $classes ) {
	$shoptimizer_layout_woocommerce_sidebar = '';
	$shoptimizer_layout_woocommerce_sidebar = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_sidebar' );

	if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() || ( is_page_template( 'template-woocommerce-archives.php' ) ) ) {
		$classes[] = $shoptimizer_layout_woocommerce_sidebar;
	}
	return $classes;
}


/**
 * Minimal checkout template - remove several hooks.
 */
function shoptimizer_minimal_checkout() {

	$shoptimizer_layout_woocommerce_simple_checkout = '';
	$shoptimizer_layout_woocommerce_simple_checkout = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_simple_checkout' );

	$shoptimizer_header_layout = '';
	$shoptimizer_header_layout = shoptimizer_get_option( 'shoptimizer_header_layout' );

	if ( true === $shoptimizer_layout_woocommerce_simple_checkout ) {

		if ( class_exists( 'WooCommerce' ) ) {
			if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
				remove_action( 'shoptimizer_topbar', 'shoptimizer_top_bar', 10 );
				remove_action( 'shoptimizer_before_site', 'shoptimizer_top_bar', 10 );
				remove_action( 'shoptimizer_header', 'shoptimizer_primary_navigation', 99 );
				remove_action( 'shoptimizer_header', 'shoptimizer_secondary_navigation', 30 );

				remove_action( 'shoptimizer_before_content', 'shoptimizer_header_widget_region', 10 );
				add_action( 'shoptimizer_header', 'shoptimizer_checkout_heading', 30 );

				remove_action( 'shoptimizer_navigation', 'shoptimizer_primary_navigation', 50 );

				remove_action( 'shoptimizer_navigation', 'shoptimizer_primary_navigation_wrapper', 42 );
				remove_action( 'shoptimizer_navigation', 'shoptimizer_header_cart', 60 );
				remove_action( 'shoptimizer_navigation', 'shoptimizer_primary_navigation_wrapper_close', 68 );

				if ( ! function_exists( 'shoptimizer_checkout_heading' ) ) {
					function shoptimizer_checkout_heading() {
						the_title( '<h1>', '</h1>' );
					}
				}

				remove_action( 'shoptimizer_header', 'shoptimizer_header_cart', 50 );
				remove_action( 'shoptimizer_header', 'shoptimizer_header_cart', 60 );
				remove_action( 'shoptimizer_header', 'shoptimizer_product_search', 25 );
				remove_action( 'shoptimizer_page_start', 'shoptimizer_page_header', 10 );
				remove_action( 'shoptimizer_before_footer', 'shoptimizer_below_content', 10 );
				remove_action( 'shoptimizer_footer', 'shoptimizer_footer_widgets', 20 );
				remove_action( 'shoptimizer_footer', 'shoptimizer_footer_copyright', 30 );

			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'shoptimizer_minimal_checkout' );

add_action( 'template_redirect', 'shoptimizer_remove_title', 10 );


/**
 * Appearance > Widgets > Custom Thank You Area. Loads at the bottom of the thank you page after an order has been placed.
 */
add_action( 'woocommerce_thankyou', 'shoptimizer_custom_thankyou_section' );

function shoptimizer_custom_thankyou_section() {
	echo '<div class="thankyou-custom-field">';
	dynamic_sidebar( 'thankyou-field' );
	echo '</div>';
}


/**
 * Add a div with an ID before the variations form, so that the sticky select options button can scroll up to it.
 */
add_action( 'woocommerce_before_add_to_cart_form', 'shoptimizer_sticky_variations_anchor' );

function shoptimizer_sticky_variations_anchor() {
	echo '<div id="shoptimizer-sticky-anchor"></div>';
}


/**
 * PDP Modal wrapper - open.
 */
add_action( 'woocommerce_single_product_summary', 'shoptimizer_pdp_modal_wrapper_open', 36 );

function shoptimizer_pdp_modal_wrapper_open() {
	echo '<div id="shoptimizer-modals-wrapper">';
}


/**
 * PDP Modal wrapper - close.
 */
add_action( 'woocommerce_single_product_summary', 'shoptimizer_pdp_modal_wrapper_close', 38 );

function shoptimizer_pdp_modal_wrapper_close() {
	echo '</div>';
}


/**
 * If the single product shortcode is present, also load the following.
 */
function shoptimizer_single_product_shortcode_styles() {
	global $post;
	global $shoptimizer_version;
	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'product_page' ) ) {
		wp_enqueue_style( 'shoptimizer-product', get_template_directory_uri() . '/assets/css/main/product.css', '', $shoptimizer_version );
		wp_enqueue_style( 'shoptimizer-modal', get_template_directory_uri() . '/assets/css/main/modal.css', '', $shoptimizer_version );
		wp_enqueue_script( 'shoptimizer-quantity', get_template_directory_uri() . '/assets/js/quantity.min.js', array(), '1.1.1', true );
	}
}
add_action( 'wp_enqueue_scripts', 'shoptimizer_single_product_shortcode_styles' );

function shoptimizer_single_product_shortcode_ajax_scripts() {
	$shoptimizer_layout_woocommerce_single_product_ajax = '';
	$shoptimizer_layout_woocommerce_single_product_ajax = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_single_product_ajax' );

	global $post;
	global $shoptimizer_version;
	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'product_page' ) ) {

		if ( true === $shoptimizer_layout_woocommerce_single_product_ajax ) {
			wp_enqueue_script( 'shoptimizer-ajax-script', get_template_directory_uri() . '/assets/js/single-product-ajax.js', array(), $shoptimizer_version, true );

			wp_localize_script(
				'shoptimizer-ajax-script',
				'shoptimizer_ajax_obj',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'ajax-nonce' ),
				)
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'shoptimizer_single_product_shortcode_ajax_scripts' );

function shoptimizer_pdp_shortcode_body_class( $shoptimizer_pdp_shortcode ) {

	global $post;

	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'product_page' ) ) {
		$shoptimizer_pdp_shortcode[] = 'pdp-shortcode';
	}
	return $shoptimizer_pdp_shortcode;
}
add_filter( 'body_class', 'shoptimizer_pdp_shortcode_body_class' );


/**
 * Wrap the category image in a span.
 *
 * @since 2.6.6
 */
add_action( 'woocommerce_before_subcategory_title', function(){
    echo '<span class="cat-image-wrapper">';
}, 9 );

add_action( 'woocommerce_before_subcategory_title', function(){
    echo '</span>';
}, 11 );


/**
 *  Quantity selectors for Shoptimizer mini cart
 *
 * @package Shoptimizer
 *
 * Description: Adds quantity buttons for the Shoptimizer mini cart
 * Version: 1.0
 * Author: CommerceGurus
 */


/**
 * Add minicart quantity fields
 *
 * @param  string $html          cart html.
 * @param  string $cart_item     cart item.
 * @param  string $cart_item_key cart item key.
 */
function add_minicart_quantity_fields( $html, $cart_item, $cart_item_key ) {

	$product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $cart_item['data'] ), $cart_item, $cart_item_key );
	$_product      = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
	$max_qty       = $_product->get_max_purchase_quantity();

	if ( $_product->is_sold_individually() ) {
		return $product_price;
	}

	$out = '<div class="shoptimizer-custom-quantity-mini-cart_container">
				<div class="shoptimizer-custom-quantity-mini-cart">
				<span tabindex="0" role="button" aria-label="Reduce quantity" class="shoptimizer-custom-quantity-mini-cart_button quantity-down">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
					  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
					</svg>
				</span>
				<input aria-label="' . esc_attr( __( 'Quantity input', 'shoptimizer' ) ) . '" class="shoptimizer-custom-quantity-mini-cart_input" data-cart_item_key="' . $cart_item_key . '" type="number" min="1" ' . ( -1 !== $max_qty ? 'max="' . $max_qty . '"' : '' ) . ' step="1" value="' . $cart_item['quantity'] . '">
				<span tabindex="0" role="button" aria-label="Increase quantity" class="shoptimizer-custom-quantity-mini-cart_button quantity-up">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-6 h-6">
  						<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
					</svg>
				</span>
			</div></div>';

	return sprintf(
		'%2$s %1$s',
		$out,
		$product_price
	);

	}

$shoptimizer_minicart_quantity = '';
$shoptimizer_minicart_quantity = shoptimizer_get_option( 'shoptimizer_minicart_quantity' );
if ( true === $shoptimizer_minicart_quantity ) {
	add_filter( 'woocommerce_widget_cart_item_quantity', 'add_minicart_quantity_fields', 10, 3 );
}

if ( ! function_exists( 'minicart_shoptimizer_update_mini_cart' ) ) {

	$shoptimizer_minicart_quantity = '';
	$shoptimizer_minicart_quantity = shoptimizer_get_option( 'shoptimizer_minicart_quantity' );

	if ( true === $shoptimizer_minicart_quantity ) {
		/**
		 * Minicart shoptimizer update mini cart.
		 */
		function minicart_shoptimizer_update_mini_cart() {

			$data = $_POST['data']; // phpcs:ignore
			if ( ! WC()->cart->is_empty() ) {
				foreach ( $data as $item_key => $item_qty ) {
					$_cart_item = WC()->cart->get_cart_item( $item_key );
					if ( ! empty( $_cart_item ) ) {
						$_product = apply_filters( 'woocommerce_cart_item_product', $_cart_item['data'], $_cart_item, $item_key );
						$max_qty  = $_product->get_max_purchase_quantity();
						if ( -1 !== $max_qty && $item_qty > $max_qty ) {
							$item_qty = $max_qty;
						}
						if ( $item_qty > 0 ) {
							WC()->cart->set_quantity( $item_key, $item_qty, true );
						}
					}
				}
			}
			wp_send_json_success();
		}
	}
}

add_action( 'wp_ajax_cg_shoptimizer_update_mini_cart', 'minicart_shoptimizer_update_mini_cart' );
add_action( 'wp_ajax_nopriv_cg_shoptimizer_update_mini_cart', 'minicart_shoptimizer_update_mini_cart' );


if ( ! function_exists( 'minicart_shoptimizer_get_styles' ) ) {
	/**
	 * Enqueue scripts
	 */
	function minicart_shoptimizer_get_scripts() {
		$shoptimizer_minicart_quantity = '';
		$shoptimizer_minicart_quantity = shoptimizer_get_option( 'shoptimizer_minicart_quantity' );

		if ( true === $shoptimizer_minicart_quantity ) {
			wp_enqueue_script( 'custom-shoptimizer-quantity-js', get_theme_file_uri( '/assets/js/minicart-quantity.js' ), array( 'jquery' ), time(), true );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'minicart_shoptimizer_get_scripts', 30 );


/**
* Remove "Description" heading from WooCommerce tabs.
*
* @since 1.0.0
*/
add_filter( 'woocommerce_product_description_heading', '__return_null' );


/**
* Option to automatically update the cart page quantity without clicking "Update".
*
* @since 2.6.6
*/
add_action( 'wp_footer', 'shoptimizer_cart_ajax_update_quantity' ); 
 
function shoptimizer_cart_ajax_update_quantity() {

	$shoptimizer_ajaxcart_quantity = '';
	$shoptimizer_ajaxcart_quantity = shoptimizer_get_option( 'shoptimizer_ajaxcart_quantity' );

	if ( true === $shoptimizer_ajaxcart_quantity ) {
		if ( is_cart() || ( is_cart() && is_checkout() ) ) {
	    	wc_enqueue_js('
				var timeout;
				jQuery("div.woocommerce").on("change keyup mouseup", "input.qty, select.qty", function(){
					if (timeout != undefined) clearTimeout(timeout);
					if (jQuery(this).val() == "") return;
					timeout = setTimeout(function() {
						jQuery("[name=\"update_cart\"]").trigger("click");
					}, 600 );
				});
				
			');
		}
	}
}


add_filter( 'body_class', 'shoptimizer_cart_ajax_update_quantity_class');
function shoptimizer_cart_ajax_update_quantity_class( $classes ) {

	$shoptimizer_ajaxcart_quantity = '';
	$shoptimizer_ajaxcart_quantity = shoptimizer_get_option( 'shoptimizer_ajaxcart_quantity' );

	if ( true === $shoptimizer_ajaxcart_quantity ) {
	    if ( is_cart() || ( is_cart() && is_checkout() ) ) {
	          $classes[] = 'shoptimizer-ajax-cart';
		}
	}
    return $classes; 
}


/**
* Fix for: Site doesn't include support for the "woocommerce/product-tab" block
*
* @since 2.6.6
*/
function shoptimizer_reset_product_template( $post_type_args ) {
	if ( array_key_exists( 'template', $post_type_args ) ) {
		unset( $post_type_args['template'] );
	}

	return $post_type_args;
}
add_filter( 'woocommerce_register_post_type_product', 'shoptimizer_reset_product_template' );


/**
* Allow custom "No search results page".
*
* @since 2.6.7
*/
add_action( 'after_setup_theme', 'shoptimizer_no_search_results' );
function shoptimizer_no_search_results() {
	add_action( 'woocommerce_no_products_found', 'shoptimizer_no_search_results_content' );			
}

function shoptimizer_no_search_results_content() {
	
	$noSearchResults = '';
	$query = new WP_Query(
	    array(
	        'post_type'              => 'wp_block',
	        'title'                  => 'No Search Results',
	        'post_status'            => 'publish',
	        'posts_per_page'         => 1
	    )
	);

	if ( $query->have_posts() == NULL ) {
		$noSearchResults = false;
	} else {
		
	echo '<section>';

	$object = $query->post;
	echo apply_filters('the_content', $object->post_content);
	echo '</section>';
	wp_reset_postdata();	

	}
		
}


/**
* PLP mobile image swipe instead of hover flip.
*
* @since 2.8.0
*/
if ( ! function_exists( 'shoptimizer_loop_product_gallery_pagination' ) ) { 

    function shoptimizer_loop_product_gallery_pagination()  {
	
    	global $product;
        $attachment_ids = $product->get_gallery_image_ids();
		$shoptimizer_layout_woocommerce_flip_image = '';
		$shoptimizer_layout_woocommerce_flip_image = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_flip_image' );	
 
        if ( true === $shoptimizer_layout_woocommerce_flip_image ) {
			if ( isset( $attachment_ids[0] ) ) {
            echo '<div class="shoptimizer-plp-carousel--pagination">
      			<span class="shoptimizer-plp-carousel--dot active"></span>
      			<span class="shoptimizer-plp-carousel--dot"></span>
    		</div>';
			}
		}
	
	}
}
																		 
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_loop_product_gallery_pagination', 58 );

/**
* PLP mobile image swipe instead of hover flip JS.
*
* @since 2.8.0
*/
if ( ! function_exists( 'shoptimizer_loop_product_gallery_pagination_js' ) ) { 

    function shoptimizer_loop_product_gallery_pagination_js()  {
	
		$shoptimizer_plp_flip_js  = '';
		$shoptimizer_plp_flip_js .= "
			
			document.querySelectorAll('.woocommerce-image__wrapper').forEach(carousel => {
		    const carouselInner = carousel.querySelector('.shoptimizer-plp-image-wrapper');
		    const dots = carousel.querySelectorAll('.shoptimizer-plp-carousel--dot');
		    const carouselContainer = carousel.querySelector('.shoptimizer-plp-carousel-container');

		    // Check if the carousel container exists
		    if (carouselContainer) {
		        function updateDots() {
		            const scrollLeft = carouselContainer.scrollLeft;
		            const viewportWidth = carouselContainer.clientWidth;
		            const index = Math.round(scrollLeft / viewportWidth);

		            dots.forEach((dot, i) => {
		                dot.classList.toggle('active', i === index);
		            });
		        }

		        let isScrolling;
		        carouselContainer.addEventListener('scroll', () => {
		            clearTimeout(isScrolling);
		            isScrolling = setTimeout(() => {
		                updateDots();
		            }, 50);
		        });

		        // Initialize the dots
		        updateDots();
		    } else {
		        console.warn('Carousel container not found:', carousel);
		    }
		});

		";

		wp_add_inline_script( 'shoptimizer-main', $shoptimizer_plp_flip_js );
	
	}
}
																		 
add_action( 'wp_head', 'shoptimizer_loop_product_gallery_pagination_js', 100 );

/**
 * Open link before the product thumbnail image
 *
 * @see get_the_permalink()
 */
function shoptimizer_template_loop_carousel_open() {
	echo '<div class="shoptimizer-plp-carousel-container"><div class="shoptimizer-plp-image-wrapper">';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_template_loop_carousel_open', 9 );


/**
 * Close link after the product thumbnail image
 *
 * @see get_the_permalink()
 */
function shoptimizer_template_loop_carousel_close() {
	echo '</div></div>';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_template_loop_carousel_close', 18 );


/**
* Add expand/collapse toggle to the core WooCommerce Product Categories widget.
*
* @since 2.8.0
*/
if ( ! function_exists( 'shoptimizer_wc_category_widget_toggle' ) ) {

    function shoptimizer_wc_category_widget_toggle() {

    	$shoptimizer_wc_product_category_widget_toggle = '';
		$shoptimizer_wc_product_category_widget_toggle = shoptimizer_get_option( 'shoptimizer_wc_product_category_widget_toggle' );

		if ( 'enable' === $shoptimizer_wc_product_category_widget_toggle ) {
	
			$shoptimizer_wc_product_categories_widget_toggle_js  = '';
			$shoptimizer_wc_product_categories_widget_toggle_js .= "
				document.addEventListener('DOMContentLoaded', function() {
	            const productCategories = document.querySelector('.product-categories');

	            function createArrow() {
	                const arrow = document.createElement('span');
	                arrow.classList.add('shoptimizer-wc-cat-widget--toggle');
	                arrow.setAttribute('aria-hidden', 'true'); 
	                return arrow;
	            }

	            const parents = document.querySelectorAll('.cat-parent');
	            function updateActiveClass() {
	                const isActive = Array.from(parents).some(parent => parent.classList.contains('shoptimizer-wc-cat-widget--expanded'));
	                	productCategories.classList.toggle('active', isActive);
	            }

	            parents.forEach(parent => {
	                const link = parent.querySelector('a');
	                const arrow = createArrow();
	                link.appendChild(arrow);
	                parent.setAttribute('aria-expanded', 'false');
	                link.setAttribute('aria-label', 'Expand');

	                arrow.addEventListener('click', function(event) {
	                    event.preventDefault();
	                    event.stopPropagation();
	                    const isExpanded = parent.classList.toggle('shoptimizer-wc-cat-widget--expanded');
	                    parent.setAttribute('aria-expanded', isExpanded);
	                    link.setAttribute('aria-label', isExpanded ? 'Collapse' : 'Expand');
	                    updateActiveClass();
	                });

	                link.addEventListener('click', function(event) {
	                    if (event.target.classList.contains('shoptimizer-wc-cat-widget--toggle')) {
	                        event.preventDefault();
	                    }
	                });
	            });
	            updateActiveClass();
        	});
			";

			wp_add_inline_script( 'shoptimizer-main', $shoptimizer_wc_product_categories_widget_toggle_js );

		}
	
	}
}
add_action( 'woocommerce_after_shop_loop', 'shoptimizer_wc_category_widget_toggle', 90 );


function shoptimizer_tinyslider_js() {

	$shoptimizer_cross_sells_carousel = '';
	$shoptimizer_cross_sells_carousel = shoptimizer_get_option( 'shoptimizer_cross_sells_carousel' );

	if ( true === $shoptimizer_cross_sells_carousel ) {
		if ( is_product() ) {
			// Enqueue tiny-slider.js.
			wp_enqueue_script( 'tiny-slider-js', get_template_directory_uri() . '/assets/js/tiny-slider.min.js', array(), null, true );
			// Enqueue custom script to initialize tiny-slider.js.
			wp_enqueue_script( 'tiny-slider-init', get_template_directory_uri() . '/assets/js/tiny-slider-init.js', array( 'tiny-slider-js' ), null, true );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'shoptimizer_tinyslider_js' );


function shoptimizer_pdp_cross_sells_carousel() {
    // Add these checks at the start of the function
    if (!function_exists('WC')) {
        return;
    }

    if (!is_product() || !get_the_ID()) {
        return;
    }

    // Get the product before proceeding
    $product = wc_get_product();
    if (!$product || !($product instanceof WC_Product)) {
        return;
    }

    // Check if the option exists and is properly set
    $shoptimizer_cross_sells_carousel = shoptimizer_get_option('shoptimizer_cross_sells_carousel');
    if (empty($shoptimizer_cross_sells_carousel)) {
        return;
    }

    // Get product before any output to prevent partial content
    $product = wc_get_product(get_the_ID());
    if (!$product || !is_a($product, 'WC_Product')) {
        return;
    }

    // Get cross-sells before any output
    $shoptimizer_cross_sells = $product->get_cross_sell_ids();
    if (empty($shoptimizer_cross_sells)) {
        return;
    }

    // Only get heading if we're sure we'll display content
    $shoptimizer_cross_sells_carousel_heading = shoptimizer_get_option('shoptimizer_cross_sells_carousel_heading');

    // Start output only after all checks pass
    echo '<div class="pdp-complementary-carousel">';
    echo '<div class="pdp-complementary--header">';
    echo '<div class="pdp-complementary--heading">';
    echo shoptimizer_safe_html($shoptimizer_cross_sells_carousel_heading);
    echo '</div>';
    echo '<div class="pdp-complementary--nav">';
    echo '<button class="tns-prev pdp-complementary--nav-prev" aria-label="Previous"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg></button>';
    echo '<button class="tns-next pdp-complementary--nav-next" aria-label="Next"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg></button>';
    echo '</div>';
    echo '</div>';
    echo '<div class="tns-carousel">';
    echo '<div class="pdp-complementary--container">';

    foreach ($shoptimizer_cross_sells as $cross_sell_id) {
        // Get fresh instance of the product using wc_get_product instead of direct instantiation
        $fresh_product = wc_get_product($cross_sell_id);

        // Check if the product exists
        if (!$fresh_product || !$fresh_product->is_visible()) {
            continue;
        }

        // Force refresh meta data if needed
        $fresh_product->read_meta_data(true);
        
        $shoptimizer_cross_sell_link = get_permalink($cross_sell_id);
        $shoptimizer_cross_sell_title = $fresh_product->get_name();
        $shoptimizer_cross_sell_img = $fresh_product->get_image('woocommerce_thumbnail');
        $shoptimizer_cross_sell_price = $fresh_product->get_price_html();

        echo '<div class="pdp-complementary-item">';
        echo '<div class="pdp-complementary--single">';
        echo '<a href="' . esc_url($shoptimizer_cross_sell_link) . '" aria-label="Product thumbnail">';
        echo $shoptimizer_cross_sell_img;
        echo '</a>';
        echo '<div class="pdp-complementary--content">';
        echo '<span class="pdp-complementary--title"><a href="' . esc_url($shoptimizer_cross_sell_link) . '">' . esc_html($shoptimizer_cross_sell_title) . '</a></span>';
        echo '<span class="price">' . $shoptimizer_cross_sell_price . '</span>';

        // Add to cart button
        if ($fresh_product->is_type('simple')) {
            echo '<div class="pdp-complementary--add-to-cart">';
            echo sprintf('<a href="%s" data-quantity="1" class="product_type_simple add_to_cart_button ajax_add_to_cart" data-product_id="%d" data-product_sku="%s" rel="nofollow">%s</a>',
                esc_url($fresh_product->add_to_cart_url()),
                esc_attr($fresh_product->get_id()),
                esc_attr($fresh_product->get_sku()),
                esc_html($fresh_product->add_to_cart_text())
            );
            echo '</div>';
        } else {
            echo '<div class="pdp-complementary--add-to-cart">';
            echo sprintf('<a href="%s" data-quantity="1" class="product_type_%s" data-product_id="%d" data-product_sku="%s" rel="nofollow">%s</a>',
                esc_url($fresh_product->add_to_cart_url()),
                esc_attr($fresh_product->get_type()),
                esc_attr($fresh_product->get_id()),
                esc_attr($fresh_product->get_sku()),
                esc_html($fresh_product->add_to_cart_text())
            );
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    echo '</div>'; // Close carousel div
    echo '</div>'; // Close tns container div
    echo '</div>'; // Close pdp-complementary-carousel div
}
add_action('woocommerce_single_product_summary', 'shoptimizer_pdp_cross_sells_carousel', 90);



if ( ! function_exists( 'shoptimizer_sticky_single_add_to_cart' ) ) {
	/**
	 * Sticky Add to Cart
	 *
	 * @since 1.0.0
	 */
	function shoptimizer_sticky_single_add_to_cart() {

		$shoptimizer_layout_woocommerce_sticky_cart_display = '';
		$shoptimizer_layout_woocommerce_sticky_cart_display = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_sticky_cart_display' );

		$shoptimizer_layout_woocommerce_single_product_ajax = '';
		$shoptimizer_layout_woocommerce_single_product_ajax = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_single_product_ajax' );

		global $woocommerce, $product;

		$id = $product->get_id();

		?>
			
			<?php if ( true === $shoptimizer_layout_woocommerce_sticky_cart_display ) { ?>

				<?php if ( $product->is_in_stock() ) { ?>

					<?php
					$shoptimizer_sticky_addtocart_js  = '';
					$shoptimizer_sticky_addtocart_js .= "

					var stickycontainer = document.getElementsByClassName('shoptimizer-sticky-add-to-cart')[0];

					function add_class_on_scroll() {
					    stickycontainer.classList.add('visible');
					}

					function remove_class_on_scroll() {
					    stickycontainer.classList.remove('visible');
					}
				
					window.addEventListener('scroll', function(){ 
					    scrollpos = window.scrollY;

					    if(scrollpos > 150){
					        add_class_on_scroll();
					    }
					    else {
					        remove_class_on_scroll();
					    }
					});

					window.addEventListener('scroll', function(e) {
				    	if (window.innerHeight + window.pageYOffset === document.documentElement.offsetHeight) {
				      		remove_class_on_scroll();
				    	}
				  	});

				  	window.onscroll = function(e) {
					    if ((window.innerHeight + window.scrollY) >= document.body.scrollHeight) {
					        remove_class_on_scroll();
					    }
					};

					";

					wp_add_inline_script( 'shoptimizer-main', $shoptimizer_sticky_addtocart_js );

				}

				?>

				<?php if ( $product->is_in_stock() ) { ?>

			<section class="shoptimizer-sticky-add-to-cart">
				<div class="col-full">
					<div class="shoptimizer-sticky-add-to-cart__content">
						<?php echo wp_kses_post( woocommerce_get_product_thumbnail( 'woocommerce_gallery_thumbnail' ) ); ?>
						<div class="shoptimizer-sticky-add-to-cart__content-product-info">
							<span class="shoptimizer-sticky-add-to-cart__content-title"><?php the_title(); ?></span>	
							<?php
								$count = $product->get_review_count();
							if ( $count && wc_review_ratings_enabled() ) {
								echo wc_get_rating_html( $product->get_average_rating() );
							}
							?>
							
						</div>

						<div class="shoptimizer-sticky-add-to-cart__content-button">
							<span class="shoptimizer-sticky-add-to-cart__content-price"><?php echo shoptimizer_safe_html( $product->get_price_html() ); ?></span>

						<?php if ( $product->is_type( 'variable' ) || $product->is_type( 'composite' ) || $product->is_type( 'bundle' ) || $product->is_type( 'grouped' ) ) { ?>
							<a href="#shoptimizer-sticky-anchor" class="variable-grouped-sticky button">
								<?php echo esc_attr__( 'Select options', 'shoptimizer' ); ?>
							</a>
							
						<?php } else { ?>

							<?php if ( false === $shoptimizer_layout_woocommerce_single_product_ajax ) { ?>								
							
							
							<a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="ajax_add_to_cart add_to_cart_button single_add_to_cart_button button" rel="nofollow">							
								<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
							</a>

						<?php } else { ?>

								<?php if ( $product->is_type( 'external' ) ) { ?>

							<a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="ajax_add_to_cart add_to_cart_button single_add_to_cart_button button" rel="nofollow">							
									<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
							</a>

							<?php } else { ?>

							<a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-quantity="1" 
								data-product_id="<?php echo shoptimizer_safe_html( $id ); ?>" data-product_sku="" class="add_to_cart_button ajax_add_to_cart button" rel="nofollow"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></a>
						
								<?php
							}
						}
						}
						?>
						</div>
					</div>
				</div>
			</section>

					<?php
				}
			}// End if().
			?>
		<?php
	}
}// End if().
