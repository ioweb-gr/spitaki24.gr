<?php
/**
 * Discount specific template functions and hooks
 *
 * @package shoptimizer
 * 
 * Function Index:
 * - shoptimizer_change_displayed_sale_price_html() - Displays sale price percentage
 * - calculate_product_discount_percentage() - Calculates product discount
 * - calculate_variable_product_discount() - Calculates variable product discount
 * - calculate_simple_product_discount() - Calculates simple product discount
 * - display_sale_badge() - Displays sale badge with discount
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Add hooks for sale badge display
add_action('woocommerce_before_shop_loop_item_title', 'shoptimizer_change_displayed_sale_price_html', 9);
add_action('woocommerce_single_product_summary', 'shoptimizer_change_displayed_sale_price_html', 11);

/**
 * Displays sale price percentage badge on product loop and single product pages.
 * 
 * @since 2.0.0
 * @return void
 */
function shoptimizer_change_displayed_sale_price_html(): void {
    global $product;
    
    // Early return if no product
    if (!$product instanceof WC_Product) {
        return;
    }

    // Get theme options with default values
    $display_badge = (bool) shoptimizer_get_option('shoptimizer_layout_woocommerce_display_badge', false);
    if (!$display_badge) {
        return;
    }

    $badge_type = shoptimizer_get_option('shoptimizer_layout_woocommerce_display_badge_type', 'circle');

    // Check if product is on sale and not grouped/bundled
    if (!$product->is_on_sale() || $product->is_type(['grouped', 'bundle'])) {
        return;
    }

    // Calculate discount percentage
    $percentage = calculate_product_discount_percentage($product);
    if ($percentage <= 0) {
        return;
    }

    // Display sale badge
    display_sale_badge($percentage, $badge_type);
}

/**
 * Calculates the discount percentage for a product.
 *
 * @param WC_Product $product The product object
 * @return int The discount percentage (0-100)
 */
function calculate_product_discount_percentage(WC_Product $product): int {
    if ($product->is_type('variable')) {
        return calculate_variable_product_discount($product);
    }
    return calculate_simple_product_discount($product);
}

/**
 * Calculates discount percentage for variable products.
 *
 * @param WC_Product_Variable $product The variable product
 * @return int The highest discount percentage among variations
 */
function calculate_variable_product_discount(WC_Product_Variable $product): int {
    $percentages = [];
    $prices = $product->get_variation_prices();

    foreach ($prices['price'] as $key => $price) {
        $regular_price = $prices['regular_price'][$key];
        if ($regular_price !== $price && $regular_price > 0.005) {
            $percentages[] = (int) round(100 - ($price / $regular_price * 100));
        }
    }

    return !empty($percentages) ? (int) max($percentages) : 0;
}

/**
 * Calculates discount percentage for simple products.
 *
 * @param WC_Product $product The product object
 * @return int The discount percentage
 */
function calculate_simple_product_discount(WC_Product $product): int {
    $regular_price = (float) $product->get_regular_price();
    if ($regular_price <= 0.005) {
        return 0;
    }

    $sale_price = (float) $product->get_price();
    return (int) round(100 - ($sale_price / $regular_price * 100));
}

/**
 * Displays the sale badge with the calculated discount.
 *
 * @param int    $percentage The discount percentage
 * @param string $badge_type The type of badge to display ('bubble' or 'circle')
 * @return void
 */
function display_sale_badge(int $percentage, string $badge_type): void {
    $badge_class = $badge_type === 'bubble' ? 'type-bubble' : 'type-circle';
    $badge_html = sprintf(
        '<span class="sale-item product-label %s" role="status" aria-label="%s">-%d%%</span>',
        esc_attr($badge_class),
        sprintf(
            /* translators: %1$d: discount percentage, %2$s: product name */
            esc_attr__('Save %1$d%% on %2$s', 'shoptimizer'),
            $percentage,
            get_the_title()
        ),
        $percentage
    );
    
    echo shoptimizer_safe_html($badge_html);
}