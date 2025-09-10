<?php
/**
 * Product Loop Template Functions and Hooks
 *
 * This file contains functions and hooks specific to the WooCommerce product loop display.
 * 
 * @package shoptimizer
 * 
 * Function Index:
 * - shoptimizer_product_columns_wrapper() - Opens product columns wrapper
 * - shoptimizer_loop_columns() - Sets default product columns
 * - shoptimizer_product_columns_wrapper_close() - Closes product columns wrapper
 * - shoptimizer_remove_woocommerce_template_loop_product_link_open() - Removes default product link open
 * - shoptimizer_remove_woocommerce_template_loop_product_link_close() - Removes default product link close
 * - shoptimizer_template_loop_image_link_open() - Opens custom product link
 * - shoptimizer_template_loop_image_link_close() - Closes custom product link
 * - shoptimizer_gallery_image() - Adds gallery flip image functionality
 * - shoptimizer_loop_product_image_wrapper_open() - Opens product image wrapper
 * - shoptimizer_loop_product_image_wrapper_close() - Closes product image wrapper
 * - shoptimizer_loop_product_content_header_open() - Opens product content header
 * - shoptimizer_loop_product_content_header_close() - Closes product content header
 * - shoptimizer_loop_product_title() - Displays product title with optional category
 * - shoptimizer_loop_product_gallery_pagination() - Adds gallery pagination dots
 * - shoptimizer_loop_product_gallery_pagination_js() - Adds gallery pagination JavaScript
 * - shoptimizer_template_loop_carousel_open() - Opens carousel container
 * - shoptimizer_template_loop_carousel_close() - Closes carousel container
 * - shoptimizer_shop_out_of_stock() - Displays out of stock labels
 * - shoptimizer_loop_product_get_rating_html() - Modifies product rating display
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Product Column Functions
 * -----------------------
 */

if ( ! function_exists( 'shoptimizer_product_columns_wrapper' ) ) {
    /**
     * Product columns wrapper.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_product_columns_wrapper(): void {
        $columns = shoptimizer_loop_columns();
        echo '<div class="columns-' . absint( $columns ) . '">';
    }
}

if ( ! function_exists( 'shoptimizer_loop_columns' ) ) {
    /**
     * Default loop columns on product archives.
     *
     * @since 1.0.0
     * @return integer Products per row
     */
    function shoptimizer_loop_columns(): int {
        $columns = 3;

        if ( function_exists( 'wc_get_default_products_per_row' ) ) {
            $columns = wc_get_default_products_per_row();
        }

        return apply_filters( 'shoptimizer_loop_columns', $columns );
    }
}

if ( ! function_exists( 'shoptimizer_product_columns_wrapper_close' ) ) {
    /**
     * Product columns wrapper close.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_product_columns_wrapper_close(): void {
        echo '</div>';
    }
}

/**
 * Product Link Functions
 * ---------------------
 */

function shoptimizer_remove_woocommerce_template_loop_product_link_open(): void {
    remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
}
add_action( 'wp_head', 'shoptimizer_remove_woocommerce_template_loop_product_link_open' );

function shoptimizer_remove_woocommerce_template_loop_product_link_close(): void {
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
}
add_action( 'wp_head', 'shoptimizer_remove_woocommerce_template_loop_product_link_close' );

function shoptimizer_template_loop_image_link_open(): void {
    printf(
        '<a href="%s" class="woocommerce-LoopProduct-link woocommerce-loop-product__link" aria-label="%s">',
        get_the_permalink(),
        sprintf(
            /* translators: %s: product name */
            esc_attr__('View product: %s', 'shoptimizer'),
            get_the_title()
        )
    );
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_template_loop_image_link_open', 5 );

function shoptimizer_template_loop_image_link_close(): void {
    echo '</a>';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_template_loop_image_link_close', 20 );

/**
 * Product Gallery Functions
 * -----------------------
 */

function shoptimizer_gallery_image(): void {
    global $product;
    if (!$product || !is_a($product, 'WC_Product')) {
        return;
    }
    $attachment_ids = $product->get_gallery_image_ids();
    $flip_image_enabled = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_flip_image' );

    if ( true === $flip_image_enabled && isset( $attachment_ids[0] ) ) {
        $image = wp_get_attachment_image($attachment_ids[0], 'woocommerce_thumbnail', '', 
            array('loading' => 'lazy', 'class' => 'gallery-image')
        );
        if ($image) {
            echo shoptimizer_safe_html($image);
        }
    }
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_gallery_image', 10 );

/**
 * Product Image Wrapper Functions
 * -----------------------------
 */

function shoptimizer_loop_product_image_wrapper_open(): void {
    echo '<div class="woocommerce-image__wrapper">';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_loop_product_image_wrapper_open', 4 );

function shoptimizer_loop_product_image_wrapper_close(): void {
    echo '</div>';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_loop_product_image_wrapper_close', 60 );

/**
 * Product Content Header Functions
 * ------------------------------
 */

function shoptimizer_loop_product_content_header_open(): void {
    echo '<div class="woocommerce-card__header">';
}
add_action( 'woocommerce_shop_loop_item_title', 'shoptimizer_loop_product_content_header_open', 5 );

function shoptimizer_loop_product_content_header_close(): void {
    echo '</div>';
}
add_action( 'woocommerce_after_shop_loop_item', 'shoptimizer_loop_product_content_header_close', 60 );

/**
 * Product Title Functions
 * ----------------------
 */

remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
add_action( 'woocommerce_shop_loop_item_title', 'shoptimizer_loop_product_title', 10 );

function shoptimizer_loop_product_title(): void {
    $display_category = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_category' );

    if ( true === $display_category ) {
        $categories = wc_get_product_category_list( get_the_id(), ', ', '', '' );
        if ($categories) {
            printf('<p class="product__categories">%s</p>', $categories);
        }
    }

    $title = get_the_title();
    $permalink = get_the_permalink();
    printf(
        '<div class="woocommerce-loop-product__title"><a tabindex="0" href="%s" aria-label="%s" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">%s</a></div>',
        esc_url($permalink),
        esc_attr($title),
        esc_html($title)
    );
}

/**
 * Product Gallery Pagination Functions
 * ---------------------------------
 */

function shoptimizer_loop_product_gallery_pagination(): void {
    // Check if we're on a product archive page using WooCommerce native functions
    if (!is_shop() && !is_product_taxonomy() && !is_product_category() && !is_product_tag()) {
        return;
    }

    global $product;
    if (!$product || !is_a($product, 'WC_Product')) {
        return;
    }

    $flip_image_enabled = shoptimizer_get_option('shoptimizer_layout_woocommerce_flip_image');
    if (!$flip_image_enabled) {
        return;
    }

    $attachment_ids = $product->get_gallery_image_ids();
    if (empty($attachment_ids[0])) {
        return;
    }

    echo '<div class="shoptimizer-plp-carousel--pagination">
        <span class="shoptimizer-plp-carousel--dot active"></span>
        <span class="shoptimizer-plp-carousel--dot"></span>
    </div>';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_loop_product_gallery_pagination', 58 );

function shoptimizer_loop_product_gallery_pagination_js(): void {
    $js = "
        const updateCarouselDots = (carousel) => {
            const carouselContainer = carousel.querySelector('.shoptimizer-plp-carousel-container');
            const dots = carousel.querySelectorAll('.shoptimizer-plp-carousel--dot');
            
            if (!carouselContainer || !dots.length) return;

            let scrollTimeout;
            const handleScroll = () => {
                if (scrollTimeout) {
                    window.cancelAnimationFrame(scrollTimeout);
                }
                scrollTimeout = window.requestAnimationFrame(() => {
                    const scrollLeft = carouselContainer.scrollLeft;
                    const viewportWidth = carouselContainer.clientWidth;
                    const index = Math.round(scrollLeft / viewportWidth);
                    dots.forEach((dot, i) => dot.classList.toggle('active', i === index));
                });
            };

            carouselContainer.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll(); // Initial state
        };

        document.querySelectorAll('.woocommerce-image__wrapper').forEach(updateCarouselDots);
    ";
    wp_add_inline_script('shoptimizer-main', $js);
}
add_action( 'wp_head', 'shoptimizer_loop_product_gallery_pagination_js', 100 );

/**
 * Product Carousel Functions
 * ------------------------
 */

function shoptimizer_template_loop_carousel_open(): void {
    echo '<div class="shoptimizer-plp-carousel-container"><div class="shoptimizer-plp-image-wrapper">';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_template_loop_carousel_open', 9 );

function shoptimizer_template_loop_carousel_close(): void {
    echo '</div></div>';
}
add_action( 'woocommerce_before_shop_loop_item_title', 'shoptimizer_template_loop_carousel_close', 18 );

/**
 * Sale Badge Functions
 * ------------------
 */

$display_badge = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_badge' );

if ( false === $display_badge ) {
    remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
    add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 7 );
}

/**
 * Displays an "Out of Stock" label for products that are not in stock.
 * This function is hooked to 'woocommerce_before_shop_loop_item_title'.
 *
 * @since 2.0.0
 * @return void
 */
function shoptimizer_shop_out_of_stock(): void {
    global $product;
    
    if (!$product instanceof WC_Product) {
        return;
    }

    if ($product->is_in_stock()) {
        return;
    }

    // Get theme option for out of stock text
    $out_of_stock_text = apply_filters(
        'shoptimizer_out_of_stock_text', 
        esc_html__('Out of stock', 'shoptimizer')
    );

    // Add role="alert" and aria-label for better screen reader announcement
    printf(
        '<span class="product-out-of-stock" role="alert" aria-label="%s">%s</span>',
        sprintf(
            /* translators: %s: product name */
            esc_attr__('%s is currently out of stock', 'shoptimizer'),
            get_the_title()
        ),
        esc_html($out_of_stock_text)
    );
}
add_action('woocommerce_before_shop_loop_item_title', 'shoptimizer_shop_out_of_stock', 8);


/**
 * Display reviews count on catalog pages.
 */

 $shoptimizer_layout_catalog_reviews_count = '';
 $shoptimizer_layout_catalog_reviews_count = shoptimizer_get_option( 'shoptimizer_layout_catalog_reviews_count' );
 
 if ( true === $shoptimizer_layout_catalog_reviews_count ) {
 
 /**
 * Modifies the product rating HTML to include review count on catalog pages.
 * This function adds the number of reviews in parentheses next to the star rating,
 * but only on product listing pages (not on single product pages).
 *
 * @since 2.0.0
 * @param string $html   The default rating HTML
 * @param float  $rating The product's average rating
 * @param int    $count  The total number of ratings
 * @return string Modified rating HTML with review count
 */
function shoptimizer_loop_product_get_rating_html(string $html, float $rating, int $count): string {
    // Early return if no rating or on single product page
    if ($rating <= 0 || is_product()) {
        return $html;
    }

    global $product;
    $count_html = '';
    $actual_count = $count;
    if ($product && is_object($product) && method_exists($product, 'get_review_count')) {
        $actual_count = $product->get_review_count();
        $count_html = '<div class="shoptimizer_ratingCount">(' . $actual_count . ')</div>';
    }

    $html = '<div class="shoptimizer_ratingContainer"><div class="star-rating">';
    $html .= wc_get_star_rating_html($rating, $actual_count);
    $html .= '</div>' . $count_html . '</div>';

    return $html;
}

// Only add the filter if review count display is enabled
if ((bool) shoptimizer_get_option('shoptimizer_layout_catalog_reviews_count', false)) {
    add_filter('woocommerce_product_get_rating_html', 'shoptimizer_loop_product_get_rating_html', 20, 3);
}
 
}