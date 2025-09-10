<?php
/**
 * PLP (Product List Page) specific template functions and hooks
 *
 * @package shoptimizer
 * 
 * Function Index:
 * - shoptimizer_product_cat_register_meta() - Registers product category meta
 * - shoptimizer_sanitize_details() - Sanitizes details meta field
 * - shoptimizer_product_cat_add_details_meta() - Adds details metabox to Add New Category
 * - shoptimizer_product_cat_edit_details_meta() - Adds details metabox to Edit Category
 * - shoptimizer_product_cat_details_meta_save() - Saves category meta details
 * - shoptimizer_product_cat_display_details_meta() - Displays category content
 * - shoptimizer_taxonomy_meta_setup() - Sets up taxonomy meta functionality
 * - shoptimizer_remove_empty_tags() - Removes empty paragraph tags from content
 * - shoptimizer_is_acf_activated() - Checks if ACF plugin is active
 * - shoptimizer_product_cat_banner() - Displays category/tag/brand banner
 * - shoptimizer_output_category_banner() - Outputs banner HTML
 * - shoptimizer_sorting_wrapper() - Opens sorting wrapper
 * - shoptimizer_sorting_wrapper_end() - Opens end sorting wrapper
 * - shoptimizer_sorting_wrapper_close() - Closes sorting wrapper
 * - shoptimizer_woocommerce_product_alignment_class() - Adds product alignment body class
 * - shoptimizer_shop_body_class() - Adds shop page body class
 * - shoptimizer_show_shop_title() - Controls shop title visibility
 * - shoptimizer_shop_heading_class() - Adds shop heading body class
 * - shoptimizer_shop_messages() - Displays shop messages
 * - shoptimizer_woocommerce_pagination() - Handles shop pagination
 * - shoptimizer_archives_title() - Displays archive page title
 * - shoptimizer_woocommerce_taxonomy_archive_description() - Displays taxonomy descriptions
 * - shoptimizer_remove_archive_description() - Removes default archive description
 * - shoptimizer_mobile_filters() - Adds mobile filter functionality
 * - shoptimizer_category_image() - Displays category/brand images
 * - shoptimizer_woocommerce_sidebar_body_class() - Adds sidebar-related body classes
 * - shoptimizer_wc_category_widget_toggle() - Adds category widget toggle functionality
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

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
 * @param  mixed $details The existing details field.
 * @return string The sanitized details field
 */
function shoptimizer_sanitize_details($details): string {
    if (is_array($details)) {
        return '';
    }
    return wp_kses_post((string) $details);
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
 * Displays below category content on Product Category archives.
 * This function retrieves and displays custom meta content for product categories,
 * including support for embedded content and shortcodes.
 *
 * @since 2.0.0
 * @return void
 */
function shoptimizer_product_cat_display_details_meta(): void {
    // Early return if not on a product category page
    if (!is_tax('product_cat')) {
        return;
    }
    
    // Get current term object
    $term = get_queried_object();
    if (!$term instanceof WP_Term) {
        return;
    }

    // Get cached meta content
    $cache_key = 'shoptimizer_cat_details_' . $term->term_id;
    $details = wp_cache_get($cache_key);

    if (false === $details) {
        $details = get_term_meta($term->term_id, 'below_category_content', true);
        wp_cache_set($cache_key, $details, '', HOUR_IN_SECONDS);
    }

    // Return if no content
    if (empty($details)) {
        return;
    }

    // Setup embed handling
            global $wp_embed;
    
    // Add filters for content processing
    add_filter('shoptimizer_content_filter', [$wp_embed, 'autoembed']);
    
    // Process and display the content
    $processed_content = apply_filters('shoptimizer_content_filter', wp_kses_post($details));
    
    if (!empty($processed_content)) {
        printf(
            '<div class="below-woocommerce-category">%s</div>',
            $processed_content
        );
    }

    // Remove the filter after use
    remove_filter('shoptimizer_content_filter', [$wp_embed, 'autoembed']);
}

// Hook the function to display after the shop loop
add_action('woocommerce_after_shop_loop', 'shoptimizer_product_cat_display_details_meta', 40);

/**
 * Sets up taxonomy meta functionality for product tags and brands.
 * Handles registration, form display, and saving of custom meta fields.
 *
 * @since 2.0.0
 * @return void
 */
function shoptimizer_taxonomy_meta_setup(): void {
    // Register meta fields with improved configuration
    register_meta('term', 'below_tag_content', [
        'type' => 'string',
        'description' => 'Content to display below product tag listing',
        'single' => true,
        'sanitize_callback' => 'wp_kses_post',
        'auth_callback' => function() {
            return current_user_can('manage_product_terms');
        },
        'show_in_rest' => true,
    ]);

    /**
     * Displays the add form fields for product tags.
     *
     * @return void
     */
    $add_form_callback = function(): void {
        if (!current_user_can('manage_product_terms')) {
            return;
        }

        wp_nonce_field('shoptimizer_tag_meta', 'shoptimizer_product_tag_details_nonce');
        ?>
        <div class="form-field">
            <label for="shoptimizer-product-tag-details">
                <?php esc_html_e('Below Tag Content', 'shoptimizer'); ?>
            </label>
            <textarea 
                name="shoptimizer-product-tag-details" 
                id="shoptimizer-product-tag-details" 
                rows="5" 
                cols="40"
                class="large-text"
                aria-label="<?php esc_attr_e('Content to display below product tag listing', 'shoptimizer'); ?>"
            ></textarea>
            <p class="description">
                <?php esc_html_e('Tag information which appears below the product list', 'shoptimizer'); ?>
            </p>
        </div>
        <?php
    };

    /**
     * Displays the edit form fields for product tags.
     *
     * @param WP_Term $term The term being edited
     * @return void
     */
    $edit_form_callback = function(WP_Term $term): void {
        if (!current_user_can('manage_product_terms')) {
            return;
        }

        // Get existing content with fallback
        $content = get_term_meta($term->term_id, 'below_tag_content', true);
        
        // Editor settings
        $editor_settings = [
            'textarea_name' => 'shoptimizer-product-tag-details',
            'editor_height' => 200,
            'media_buttons' => true,
            'teeny' => true,
            'quicktags' => true,
            'editor_css' => '<style>.wp-editor-area{border:1px solid #ddd;}</style>',
        ];

        wp_nonce_field('shoptimizer_tag_meta', 'shoptimizer_product_tag_details_nonce');
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="shoptimizer-product-tag-details">
                    <?php esc_html_e('Below Tag Content', 'shoptimizer'); ?>
                </label>
            </th>
            <td>
                <?php wp_editor(wp_kses_post($content), 'product_tag_details', $editor_settings); ?>
                <p class="description">
                    <?php esc_html_e('Tag information which appears below the product list', 'shoptimizer'); ?>
                </p>
            </td>
        </tr>
        <?php
    };

    /**
     * Saves the tag meta data.
     *
     * @param int $term_id The term ID
     * @return void
     */
    $save_callback = function(int $term_id): void {
        // Verify user capabilities
        if (!current_user_can('manage_product_terms')) {
            return;
        }

        // Verify nonce
        if (!isset($_POST['shoptimizer_product_tag_details_nonce']) || 
            !wp_verify_nonce($_POST['shoptimizer_product_tag_details_nonce'], 'shoptimizer_tag_meta')) {
            return;
        }

        // Get and sanitize the new details
        $new_details = isset($_POST['shoptimizer-product-tag-details']) 
            ? wp_kses_post(wp_unslash($_POST['shoptimizer-product-tag-details'])) 
            : '';

        // Update or delete meta based on content
        if (!empty($new_details)) {
            update_term_meta($term_id, 'below_tag_content', $new_details);
        } else {
            delete_term_meta($term_id, 'below_tag_content');
        }

        // Clear any cached content
        wp_cache_delete('shoptimizer_tag_content_' . $term_id, 'term_meta');
    };

    /**
     * Displays the tag content on the frontend.
     *
     * @return void
     */
    $display_callback = function(): void {
        if (!is_tax('product_tag')) {
            return;
        }
        
        $term = get_queried_object();
        if (!$term instanceof WP_Term) {
            return;
        }

        // Try to get cached content
        $cache_key = 'shoptimizer_tag_content_' . $term->term_id;
        $details = wp_cache_get($cache_key, 'term_meta');

        if (false === $details) {
            $details = get_term_meta($term->term_id, 'below_tag_content', true);
            wp_cache_set($cache_key, $details, 'term_meta', HOUR_IN_SECONDS);
        }

        if (empty($details)) {
            return;
        }

        // Setup embed handling
        global $wp_embed;
        add_filter('shoptimizer_content_filter', [$wp_embed, 'autoembed']);

        printf(
            '<div class="below-woocommerce-category">%s</div>',
            apply_filters('shoptimizer_content_filter', wp_kses_post($details))
        );

        remove_filter('shoptimizer_content_filter', [$wp_embed, 'autoembed']);
    };

    // Hook all callbacks
    add_action('product_tag_add_form_fields', $add_form_callback);
    add_action('product_tag_edit_form_fields', $edit_form_callback);
    add_action('created_product_tag', $save_callback);
    add_action('edited_product_tag', $save_callback);
    add_action('woocommerce_after_shop_loop', $display_callback, 40);
}

add_action('init', 'shoptimizer_taxonomy_meta_setup');

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
  * Displays a banner for product categories, tags, and brands.
  * This function adds an ACF category banner full width field above the product listing.
  *
  * @since 2.0.0
  * @return void
  */
 function shoptimizer_product_cat_banner(): void {
     // Early return if not on a supported taxonomy page
     if (!is_product_category() && !is_product_tag() && !is_tax('product_brand')) {
         return;
     }

     // Get banner position setting
     $category_position = shoptimizer_get_option(
         'shoptimizer_layout_woocommerce_category_position',
         'below-header'
     );

     // Only proceed if banner should be below header
     if ('below-header' !== $category_position) {
         return;
     }

     // Get current term
				 $term = get_queried_object();
     if (!$term instanceof WP_Term) {
         return;
     }

     // Get banner image if ACF is active
     $category_banner = '';
     if (shoptimizer_is_acf_activated()) {
         $banner = get_field('category_banner', $term);
         // Ensure we have a valid string URL
         $category_banner = is_string($banner) ? $banner : '';
     }

     // Remove default archive description actions
     remove_action('woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);
     remove_action('woocommerce_archive_description', 'shoptimizer_woocommerce_taxonomy_archive_description');
     remove_action('woocommerce_archive_description', 'shoptimizer_category_image', 20);
     remove_action('woocommerce_before_main_content', 'shoptimizer_archives_title', 20);

     // Output banner HTML
     shoptimizer_output_category_banner($category_banner);
 }

 /**
  * Outputs the category banner HTML.
  *
  * @since 2.0.0
  * @param string $banner_url URL of the banner image
  * @return void
  */
 function shoptimizer_output_category_banner(string $banner_url = ''): void {
     // Ensure banner_url is a string and not empty before using
     if (!empty($banner_url) && is_string($banner_url)) {
         printf(
             '<style>
			 .shoptimizer-category-banner, .shoptimizer-category-banner.visible {
                 background-image: url(%s);
             }
             </style>',
             esc_url($banner_url)
         );
     }

     // Determine banner class based on whether there's an image
     $banner_class = !empty($banner_url) ? 'shoptimizer-category-banner lazy-background' : 'shoptimizer-category-banner';

     // Added role and aria-label to the banner div
     printf(
         '<div class="%s" role="region" aria-label="%s">',
         esc_attr($banner_class),
         esc_attr__('Category Header', 'shoptimizer')
     );
     ?>
				 <div class="col-full">
					 <h1><?php single_cat_title(); ?></h1>
             <?php
             $description = get_the_archive_description();
             if (!empty($description)) {
                 // Added aria-label to the description
                 printf(
                     '<div class="taxonomy-description" aria-label="%s">%s</div>',
                     esc_attr__('Category Description', 'shoptimizer'),
                     wp_kses_post($description)
                 );
             }
             ?>
				 </div>
			 </div>
				 <?php
 }
 
 // Hook the banner function to display before content
 add_action('shoptimizer_before_content', 'shoptimizer_product_cat_banner', 15);


if ( ! function_exists( 'shoptimizer_sorting_wrapper' ) ) {
	/**
	 * Opens the sorting wrapper for product list display.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	function shoptimizer_sorting_wrapper(): void {
		// Add role and aria-label
        echo '<div class="shoptimizer-sorting" role="region" aria-label="' . esc_attr__('Product sorting options', 'shoptimizer') . '">';
	}
}

if ( ! function_exists( 'shoptimizer_sorting_wrapper_end' ) ) {
	/**
	 * Opens the end sorting wrapper for product list display.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	function shoptimizer_sorting_wrapper_end(): void {
		echo '<div class="shoptimizer-sorting sorting-end">';
	}
}

if ( ! function_exists( 'shoptimizer_sorting_wrapper_close' ) ) {
	/**
	 * Closes the sorting wrapper div.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	function shoptimizer_sorting_wrapper_close(): void {
		echo '</div>';
	}
}

/**
 * Sets body classes depending on which product alignment has been selected.
 */
function shoptimizer_woocommerce_product_alignment_class(array $classes): array {
    $text_alignment = shoptimizer_get_option(
        'shoptimizer_layout_woocommerce_text_alignment',
        'left-align' // Default value
    );
    
    if (!empty($text_alignment)) {
        $classes[] = sanitize_html_class($text_alignment);
    }
    
    return array_unique($classes);
}

add_filter( 'body_class', 'shoptimizer_woocommerce_product_alignment_class' );


if ( class_exists( 'WooCommerce' ) ) {
	/**
	 * Adds a body class to just the Shop landing page.
	 */
	function shoptimizer_shop_body_class(array $classes): array {
	    if (function_exists('is_shop') && is_shop()) {
			$classes[] = 'shop';
		}
	    return array_unique($classes);
	}

	add_filter( 'body_class', 'shoptimizer_shop_body_class' );
}


/**
 * Shop page - show H1 page title for SEO and hide it with CSS.
 */
add_filter( 'woocommerce_show_page_title', 'shoptimizer_show_shop_title' );
function shoptimizer_show_shop_title(): bool {
    return is_shop();
}


/**
 * Adds a body class to control shop heading visibility.
 * This function adds a 'shop-heading' class to the body when:
 * 1. The shop title visibility option is enabled in theme settings
 * 2. The current page is the main shop page
 *
 * @since 2.0.0
 * @param array $classes Array of body classes
 * @return array Modified array of body classes
 */
function shoptimizer_shop_heading_class(array $classes): array {
    // Early return if not on shop page
    if (!is_shop()) {
	return $classes;
}

    // Get shop title visibility setting with default value
    $show_shop_title = (bool) shoptimizer_get_option(
        'shoptimizer_layout_shop_title',
        false
    );

    // Add class if shop title should be visible
    if ($show_shop_title) {
        $classes[] = 'shop-heading';
    }

    return array_unique($classes);
}
add_filter('body_class', 'shoptimizer_shop_heading_class');



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
function shoptimizer_woocommerce_taxonomy_archive_description(): void {
    $show_description = (bool) shoptimizer_get_option(
        'shoptimizer_layout_woocommerce_category_description',
        false
    );

    if (!$show_description) {
        return;
    }

    if (is_tax(['product_cat', 'product_tag']) && get_query_var('paged') !== 0) {
        $description = wc_format_content(term_description());
        if (!empty($description)) {
            printf(
                '<div class="term-description">%s</div>',
                wp_kses_post($description)
            );
        }
    }
}

/**
 * Removes default WooCommerce category description.
 *
 * @since 1.0.0
 * @return void
 */
function shoptimizer_remove_archive_description(): void {
    $show_description = (bool) shoptimizer_get_option(
        'shoptimizer_layout_woocommerce_category_description',
        false
    );

    if (!$show_description) {
        remove_action('woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);
        remove_action('woocommerce_archive_description', 'shoptimizer_woocommerce_taxonomy_archive_description');
    }
}

/**
 * Product Archives - Mobile filters
 */
function shoptimizer_mobile_filters(): void {
    if (!is_active_sidebar('sidebar-1')) {
        return;
    }

    $sidebar_layout = shoptimizer_get_option(
        'shoptimizer_layout_woocommerce_sidebar',
        'default-sidebar'
    );

    if ('no-woocommerce-sidebar' === $sidebar_layout) {
        return;
    }

    $button_html = sprintf(
        '<button class="mobile-filter shoptimizer-mobile-toggle" aria-expanded="false" aria-controls="primary-sidebar">%s%s</button>',
        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
        </svg>',
        esc_html__('Show Filters', 'shoptimizer')
    );

    echo wp_kses_post($button_html);
}

/**
 * Displays the category/brand image on taxonomy archive pages.
 * This function retrieves and displays the featured image associated with 
 * the current product category or brand taxonomy term.
 *
 * @since 2.0.0
 * @return void
 */
function shoptimizer_category_image(): void {
    // Early return if feature is disabled
    $show_category_image = (bool) shoptimizer_get_option(
        'shoptimizer_layout_woocommerce_category_image',
        false
    );
    if (!$show_category_image) {
        return;
    }

    // Check if we're on a supported taxonomy page
    if (!is_product_category() && !is_tax('product_brand')) {
        return;
    }

    // Get current term
    $term = get_queried_object();
    if (!$term instanceof WP_Term) {
        return;
    }

    // Get image data
    $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
    if (empty($thumbnail_id)) {
        return;
    }

    // Get image attributes
    $image_url = wp_get_attachment_url($thumbnail_id);
    $image_attributes = wp_get_attachment_image_src($thumbnail_id, 'full');
    if (!$image_url || !$image_attributes) {
        return;
    }

    // Get alt text with fallback to term name
    $alt_text = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
    if (empty($alt_text)) {
        $alt_text = $term->name;
    }

    // Output the image with proper attributes
    printf(
        '<img class="cg-cat-image" src="%s" alt="%s" width="%d" height="%d" loading="lazy" decoding="async"/>',
        esc_url($image_url),
        esc_attr($alt_text),
        (int) $image_attributes[1],
        (int) $image_attributes[2]
    );
}

/**
 * Adds appropriate sidebar-related classes to the body on WooCommerce pages.
 * This function determines which sidebar layout class to add based on the current
 * page type and theme settings.
 *
 * @since 2.0.0
 * @param array $classes Existing body classes
 * @return array Modified body classes
 */
function shoptimizer_woocommerce_sidebar_body_class(array $classes): array {
    // Get sidebar layout setting
    $sidebar_layout = shoptimizer_get_option(
        'shoptimizer_layout_woocommerce_sidebar',
        'default-sidebar'
    );

    // Define pages that should receive sidebar classes
    $supported_pages = [
        'is_shop',
        'is_product_category',
        'is_product_tag',
        'is_product_taxonomy'
    ];

    // Check if current page should have sidebar class
    $should_add_sidebar_class = array_reduce($supported_pages, function($carry, $page) {
        return $carry || (function_exists($page) && $page());
    }, false);

    // Also check for archive template
    $should_add_sidebar_class = $should_add_sidebar_class || 
        is_page_template('template-woocommerce-archives.php');

    // Add sidebar class if conditions are met
    if ($should_add_sidebar_class) {
        $classes[] = sanitize_html_class($sidebar_layout);
    }

    return array_unique($classes);
}
add_filter('body_class', 'shoptimizer_woocommerce_sidebar_body_class');


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
