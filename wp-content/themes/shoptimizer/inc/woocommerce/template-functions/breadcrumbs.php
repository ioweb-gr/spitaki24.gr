<?php
/**
 * Breadcrumbs specific template functions and hooks
 *
 * @package shoptimizer
 * 
 * Function Index:
 * - shoptimizer_breadcrumbs() - Displays breadcrumbs based on theme settings
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Breadcrumb Functions
 * -------------------
 */

if ( ! function_exists( 'shoptimizer_breadcrumbs' ) ) {
    /**
     * Displays breadcrumbs based on theme settings and available plugins.
     * Supports multiple breadcrumb providers:
     * - Default WooCommerce
     * - All in One SEO
     * - RankMath
     * - The SEO Framework
     * - SEOPress
     * - Yoast SEO
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_breadcrumbs(): void {
        // Get theme options
        $display_breadcrumbs = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_breadcrumbs' );
        $breadcrumbs_type = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_breadcrumbs_type' );

        // Early return if breadcrumbs are disabled
        if ( ! $display_breadcrumbs ) {
            return;
        }

        // Skip breadcrumbs for specific templates
        if ( is_page_template( array(
            'template-fullwidth-no-heading.php',
            'template-blank-canvas.php',
            'template-canvas.php'
        ))) {
            return;
        }

        // Display appropriate breadcrumbs based on selected type
        switch ( $breadcrumbs_type ) {
            case 'default':
                add_action( 'shoptimizer_content_top', 'woocommerce_breadcrumb', 10 );
                break;

            case 'aioseo':
                if ( function_exists( 'aioseo_breadcrumbs' ) ) {
                    echo '<nav class="aioseo woocommerce-breadcrumb" aria-label="' . esc_attr__('Breadcrumb', 'shoptimizer') . '">';
                    aioseo_breadcrumbs();
                    echo '</nav>';
                }
                break;

            case 'rankmath':
                if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
                    echo '<nav class="rankmath woocommerce-breadcrumb" aria-label="' . esc_attr__('Breadcrumb', 'shoptimizer') . '">';
                    rank_math_the_breadcrumbs();
                    echo '</nav>';
                }
                break;

            case 'seoframework':
                echo '<nav class="seoframework woocommerce-breadcrumb" aria-label="' . esc_attr__('Breadcrumb', 'shoptimizer') . '">';
                echo do_shortcode('[tsf_breadcrumb]');
                echo '</nav>';
                break;

            case 'seopress':
                if ( function_exists( 'seopress_display_breadcrumbs' ) ) {
                    echo '<nav class="seopress woocommerce-breadcrumb" aria-label="' . esc_attr__('Breadcrumb', 'shoptimizer') . '">';
                    seopress_display_breadcrumbs();
                    echo '</nav>';
                }
                break;

            case 'yoast':
                if ( function_exists( 'yoast_breadcrumb' ) ) {
                    yoast_breadcrumb(
                        '<nav class="yoast woocommerce-breadcrumb" aria-label="' . esc_attr__('Breadcrumb', 'shoptimizer') . '">',
                        '</nav>'
                    );
                }
                break;
        }
    }
}

/**
 * Filters Yoast breadcrumb separator to add a wrapper span.
 * This allows for better styling control of the separator.
 *
 * @param string $separator The breadcrumb separator
 * @return string Modified separator with wrapper span
 */
add_filter(
    'wpseo_breadcrumb_separator',
    function( string $separator ): string {
        return '<span class="breadcrumb-separator">' . $separator . '</span>';
    }
);
