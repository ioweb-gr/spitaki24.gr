<?php
/**
 * Cart specific template functions and hooks
 *
 * This file contains functions and hooks specific to the cart functionality.
 * 
 * @package shoptimizer
 * 
 * Function Index:
 * - shoptimizer_cart_link() - Displays cart icon and total
 * - shoptimizer_cart_link_fragment() - Updates cart fragments via AJAX
 * - shoptimizer_header_cart() - Displays header cart navigation
 * - shoptimizer_cart_link_shortcode() - Provides cart shortcode functionality
 * - shoptimizer_cart_register_elementor_widget() - Registers Elementor cart widget
 * - shoptimizer_cross_sells_columns() - Controls cross-sells column count
 * - shoptimizer_cross_sells_number() - Controls number of cross-sells
 * - shoptimizer_display_cross_sells() - Manages cross-sells display
 * - shoptimizer_cart_custom_field() - Adds custom widget below cart
 * - shoptimizer_cart_custom_summary() - Adds custom widget below summary
 * - shoptimizer_cart_wrapper_open() - Opens cart wrapper
 * - shoptimizer_cart_wrapper_close() - Closes cart wrapper
 * - shoptimizer_cart_ajax_update_quantity() - Handles AJAX quantity updates
 * - shoptimizer_mobile_cart_body_class() - Adds mobile cart body class
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Cart Display Functions
 * ---------------------
 */

if ( ! function_exists( 'shoptimizer_cart_link' ) ) {
    /**
     * Displays the cart link including item count and total.
     * Supports different cart icon styles based on theme settings.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_cart_link(): void {
        if ( ! shoptimizer_woo_cart_available() ) {
            return;
        }

        $sidebar_cart_enabled = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_enable_sidebar_cart' );
        $cart_icon_style = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_cart_icon' );
        $cart_url = $sidebar_cart_enabled ? '#' : esc_url( wc_get_cart_url() );
        
        ?>
        <div class="shoptimizer-cart">
            <a class="cart-contents" 
               role="button" 
               href="<?php echo $cart_url; ?>" 
               title="<?php esc_attr_e( 'View your shopping cart', 'shoptimizer' ); ?>">
                
                <span class="amount"><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span>

                <?php if ( 'basket' === $cart_icon_style ) : ?>
                    <span class="count">
                        <?php echo wp_kses_post( 
                            sprintf( 
                                _n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'shoptimizer' ), 
                                WC()->cart->get_cart_contents_count() 
                            ) 
                        ); ?>
                    </span>
                <?php endif; ?>

                <?php if ( 'cart' === $cart_icon_style ) : ?>
                    <span class="shoptimizer-cart-icon">
                        <svg aria-hidden="true" role="presentation" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="mini-count">
                            <?php echo wp_kses_data( 
                                sprintf( 
                                    _n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'shoptimizer' ), 
                                    WC()->cart->get_cart_contents_count() 
                                ) 
                            ); ?>
                        </span>
                    </span>
                <?php endif; ?>

                <?php if ( 'bag' === $cart_icon_style ) : ?>
                    <span class="shoptimizer-cart-icon">
                        <svg aria-hidden="true" role="presentation" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span class="mini-count">
                            <?php echo wp_kses_data( 
                                sprintf( 
                                    _n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'shoptimizer' ), 
                                    WC()->cart->get_cart_contents_count() 
                                ) 
                            ); ?>
                        </span>
                    </span>
                <?php endif; ?>
            </a>
        </div>
        <?php
    }
}

if ( ! function_exists( 'shoptimizer_cart_link_fragment' ) ) {
    /**
     * Updates cart fragments via AJAX.
     * Ensures cart contents update when products are added to the cart via AJAX.
     *
     * @since 1.0.0
     * @param array $fragments Fragments to refresh via AJAX
     * @return array Modified fragments
     */
    function shoptimizer_cart_link_fragment( array $fragments ): array {
        ob_start();
        shoptimizer_cart_link();
        $fragments['div.shoptimizer-cart'] = ob_get_clean();

        return $fragments;
    }
}

if ( ! function_exists( 'shoptimizer_header_cart' ) ) {
    /**
     * Displays the header cart if WooCommerce is activated and cart display is enabled.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_header_cart(): void {
        if ( ! shoptimizer_is_woocommerce_activated() ) {
            return;
        }

        $display_cart = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_display_cart' );

        if ( true === $display_cart ) {
            ?>
            <nav class="site-header-cart menu" aria-label="<?php esc_attr_e( 'Cart contents', 'shoptimizer' ); ?>">
                <?php shoptimizer_cart_link(); ?>
            </nav>
            <?php
        }
    }
}

/**
 * Cross Sells Functions
 * --------------------
 */

if ( ! function_exists( 'shoptimizer_cross_sells_columns' ) ) {
    /**
     * Controls the number of columns for cross-sells display.
     *
     * @since 1.0.0
     * @param mixed $columns Current number of columns
     * @return int Modified number of columns
     */
    function shoptimizer_cross_sells_columns( $columns ): int {
        return (int) shoptimizer_get_option( 'shoptimizer_layout_cross_sells_amount' );
    }
}
add_filter( 'woocommerce_cross_sells_columns', 'shoptimizer_cross_sells_columns' );

if ( ! function_exists( 'shoptimizer_cross_sells_number' ) ) {
    /**
     * Controls the total number of cross-sells to display.
     *
     * @since 1.0.0
     * @param mixed $columns Current number of cross-sells
     * @return int Modified number of cross-sells
     */
    function shoptimizer_cross_sells_number( $columns ): int {
        return (int) shoptimizer_get_option( 'shoptimizer_layout_cross_sells_amount' );
    }
}
add_filter( 'woocommerce_cross_sells_total', 'shoptimizer_cross_sells_number' );

if ( ! function_exists( 'shoptimizer_display_cross_sells' ) ) {
    /**
     * Controls whether to display cross-sells on the cart page.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_display_cross_sells(): void {
        $display_cross_sells = shoptimizer_get_option( 'shoptimizer_display_cross_sells' );

        if ( false === $display_cross_sells ) {
            remove_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );
        }
    }
}
add_action( 'wp', 'shoptimizer_display_cross_sells' );

/**
 * Cart Widget Areas
 * ----------------
 */

if ( ! function_exists( 'shoptimizer_cart_custom_field' ) ) {
    /**
     * Displays custom widget area below the cart totals.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_cart_custom_field(): void {
        if ( is_active_sidebar( 'cart-field' ) ) {
            echo '<div class="cart-custom-field">';
            dynamic_sidebar( 'cart-field' );
            echo '</div>';
        }
    }
}
add_action( 'woocommerce_after_cart_totals', 'shoptimizer_cart_custom_field', 15 );

if ( ! function_exists( 'shoptimizer_cart_custom_summary' ) ) {
    /**
     * Displays custom widget area below the cart table.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_cart_custom_summary(): void {
        if ( is_active_sidebar( 'cart-summary' ) ) {
            echo '<div class="cart-summary">';
            dynamic_sidebar( 'cart-summary' );
            echo '</div>';
        }
    }
}
add_action( 'woocommerce_after_cart_table', 'shoptimizer_cart_custom_summary', 50 );

/**
 * Cart Layout Functions
 * --------------------
 */

if ( ! function_exists( 'shoptimizer_cart_wrapper_open' ) ) {
    /**
     * Opens the cart wrapper section.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_cart_wrapper_open(): void {
        echo '<section class="shoptimizer-cart-wrapper" role="region" aria-label="' . esc_attr__('Shopping Cart', 'shoptimizer') . '">';
    }
}

if ( ! function_exists( 'shoptimizer_cart_wrapper_close' ) ) {
    /**
     * Closes the cart wrapper section.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_cart_wrapper_close(): void {
        echo '</section>';
    }
}

add_action( 'woocommerce_before_cart', 'shoptimizer_cart_wrapper_open', 20 );
add_action( 'woocommerce_after_cart', 'shoptimizer_cart_wrapper_close', 10 );

/**
 * Cart AJAX Functions
 * ------------------
 */

if ( ! function_exists( 'shoptimizer_cart_ajax_update_quantity' ) ) {
    /**
     * Enables automatic cart updates when quantity is changed.
     *
     * @since 2.6.6
     * @return void
     */
    function shoptimizer_cart_ajax_update_quantity(): void {
        $ajax_cart_quantity = shoptimizer_get_option( 'shoptimizer_ajaxcart_quantity' );

        if ( true === $ajax_cart_quantity && ( is_cart() || ( is_cart() && is_checkout() ) ) ) {
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
add_action( 'wp_footer', 'shoptimizer_cart_ajax_update_quantity' );

/**
 * Cart Body Classes
 * ----------------
 */

if ( ! function_exists( 'shoptimizer_mobile_cart_body_class' ) ) {
    /**
     * Adds mobile cart specific body class.
     *
     * @since 1.0.0
     * @param array $classes Existing body classes
     * @return array Modified body classes
     */
    function shoptimizer_mobile_cart_body_class( array $classes ): array {
        $mobile_cart_page = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_mobile_cart_page' );

        if ( true === $mobile_cart_page && is_cart() ) {
            $classes[] = 'm-cart';
        }
        return $classes;
    }
}
add_filter( 'body_class', 'shoptimizer_mobile_cart_body_class' );

// Move cross-sells to after cart
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );