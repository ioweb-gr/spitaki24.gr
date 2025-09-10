<?php
/**
 * Search Results specific template functions and hooks
 *
 * @package shoptimizer
 * 
 * Function Index:
 * - shoptimizer_no_search_results() - Adds custom "No search results" page functionality
 * - shoptimizer_no_search_results_content() - Displays custom content for no search results
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * No Search Results Functions
 * --------------------------
 */

if ( ! function_exists( 'shoptimizer_no_search_results' ) ) {
    /**
     * Adds custom "No search results" page functionality.
     * Hooks into WooCommerce's no products found action.
     *
     * @since 2.6.7
     * @return void
     */
    function shoptimizer_no_search_results(): void {
        add_action( 'woocommerce_no_products_found', 'shoptimizer_no_search_results_content' );
    }
}
add_action( 'after_setup_theme', 'shoptimizer_no_search_results' );

if ( ! function_exists( 'shoptimizer_no_search_results_content' ) ) {
    /**
     * Displays custom content for no search results.
     * Fetches and displays content from a reusable block titled "No Search Results".
     * Includes proper sanitization of block content before display.
     *
     * @since 2.6.7
     * @return void
     */
    function shoptimizer_no_search_results_content(): void {
        // Query for the "No Search Results" block with sanitized parameters
        $query = new WP_Query([
            'post_type'      => 'wp_block',
            'title'          => sanitize_text_field('No Search Results'),
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'no_found_rows'  => true,
            'cache_results'  => true
        ]);

        // Display the block content if found
        if ( $query->have_posts() ) {
            $block = $query->posts[0];
            
            // Ensure we have valid block content
            if ( ! empty( $block->post_content ) ) {
                // Sanitize and prepare the block content
                $content = wp_kses_post( $block->post_content );
                
                // Apply content filters with proper sanitization
                $filtered_content = apply_filters( 
                    'shoptimizer_no_results_content', 
                    do_blocks( $content )
                );

                // Output the content with proper escaping
                printf(
                    '<section class="no-results-block">%s</section>',
                    wp_kses_post( $filtered_content )
                );
            }
        }

        wp_reset_postdata();
    }
}