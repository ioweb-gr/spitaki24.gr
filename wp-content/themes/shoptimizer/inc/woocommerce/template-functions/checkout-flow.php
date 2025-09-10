<?php
/**
 * Checkout Flow specific template functions and hooks
 *
 * @package shoptimizer
 * 
 * Function Index:
 * - shoptimizer_cart_progress() - Displays checkout progress bar on cart and checkout pages
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Add Progress Bar to the Cart and Checkout pages.
 */
add_action( 'woocommerce_before_cart', 'shoptimizer_cart_progress' );
add_action( 'woocommerce_before_checkout_form', 'shoptimizer_cart_progress', 5 );

if ( ! function_exists( 'shoptimizer_cart_progress' ) ) {
    /**
     * Displays a progress bar for the checkout process.
     * Shows current step (Cart, Shipping/Checkout, Confirmation).
     * Only displays if enabled in theme options.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_cart_progress(): void {
        $shoptimizer_layout_progress_bar_display = shoptimizer_get_option( 'shoptimizer_layout_progress_bar_display' );

        if ( true === $shoptimizer_layout_progress_bar_display ) {
            $current_page = is_cart() ? 'cart' : (is_checkout() ? 'checkout' : '');
            ?>
            <div class="checkout-wrap" 
                 role="navigation" 
                 aria-label="<?php esc_attr_e('Checkout Progress', 'shoptimizer'); ?>"
                 aria-live="polite">
                <!-- Add overall progress -->
                <span class="screen-reader-text">
                    <?php 
                    printf(
                        /* translators: %s: current step */
                        esc_html__('Currently on step %s of 3', 'shoptimizer'),
                        $current_page === 'cart' ? '1' : ($current_page === 'checkout' ? '2' : '3')
                    ); 
                    ?>
                </span>
                <ul class="checkout-bar" role="list">
                    <li class="active first" aria-current="<?php echo $current_page === 'cart' ? 'step' : 'false'; ?>">
                        <span>
                            <a href="<?php echo esc_url(get_permalink(wc_get_page_id('cart'))); ?>"
                               aria-label="<?php 
                                   echo $current_page === 'cart' 
                                       ? esc_attr__('Shopping Cart, current step', 'shoptimizer')
                                       : esc_attr__('Return to Shopping Cart', 'shoptimizer'); 
                               ?>">
                                <?php esc_html_e('Shopping Cart', 'shoptimizer'); ?>
                            </a>
                        </span>
                    </li>
                    <li class="next" aria-current="<?php echo $current_page === 'checkout' ? 'step' : 'false'; ?>">
                        <span>
                            <a href="<?php echo esc_url(get_permalink(wc_get_page_id('checkout'))); ?>"
                               aria-label="<?php 
                                   echo $current_page === 'checkout'
                                       ? esc_attr__('Shipping and Checkout, current step', 'shoptimizer')
                                       : esc_attr__('Proceed to Shipping and Checkout', 'shoptimizer');
                               ?>">
                                <?php esc_html_e('Shipping and Checkout', 'shoptimizer'); ?>
                            </a>
                        </span>
                    </li>
                    <li aria-current="false">
                        <span>
                            <?php esc_html_e('Confirmation', 'shoptimizer'); ?>
                        </span>
                    </li>
                </ul>
            </div>
            <?php
        }
    }
}