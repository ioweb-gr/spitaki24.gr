<?php
/**
 * Mini Cart Template Functions and Hooks.
 *
 * This file contains functions and hooks specific to the mini cart functionality.
 * 
 * @package shoptimizer
 * 
 * Function Index:
 * - shoptimizer_always_show_cart() - Controls cart widget visibility
 * - shoptimizer_header_cart_drawer() - Renders the slide-out cart drawer
 * - shoptimizer_remove_view_cart_minicart() - Manages view cart button visibility
 * - shoptimizer_empty_mini_cart() - Handles empty cart state display
 * - shoptimizer_sidebar_cart_below_text() - Displays custom text below cart
 * - add_minicart_quantity_fields() - Adds quantity controls to cart items
 * - minicart_shoptimizer_update_mini_cart() - Handles AJAX quantity updates
 * - minicart_shoptimizer_get_scripts() - Enqueues required scripts
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Cart Visibility Functions
 * ------------------------
 */

if ( ! function_exists( 'shoptimizer_always_show_cart' ) ) {
	/**
	 * Controls cart widget visibility across all pages.
	 * 
	 * By default, WooCommerce hides the cart widget on cart and checkout pages.
	 * This filter overrides that behavior to always show the mini cart.
	 * 
	 * @since 1.0.0
	 * @return bool Always returns false to ensure cart widget is visible
	 */
	function shoptimizer_always_show_cart(): bool {
		return false;
	}
}

// Priority 40 ensures this runs after WooCommerce's default cart visibility logic
add_filter( 'woocommerce_widget_cart_is_hidden', 'shoptimizer_always_show_cart', 40, 0 );

/**
 * Cart Drawer Functions
 * --------------------
 */

if ( ! function_exists( 'shoptimizer_header_cart_drawer' ) ) {
	/**
	 * Renders the slide-out cart drawer.
	 *
	 * Displays a modal-style drawer containing the cart contents, loading state,
	 * cart title, and close button.
	 *
	 * @since  1.0.0
	 * @uses   shoptimizer_is_woocommerce_activated() Check if WooCommerce is activated
	 * @return void
	 */
	function shoptimizer_header_cart_drawer(): void {
		// Early return if WooCommerce isn't active
		if ( ! shoptimizer_is_woocommerce_activated() ) {
			return;
		}

		$shoptimizer_cart_title = shoptimizer_get_option( 'shoptimizer_cart_title' );
		$class = is_cart() ? 'current-menu-item' : '';

		?>
		<div tabindex="-1" id="shoptimizerCartDrawer" class="shoptimizer-mini-cart-wrap" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Cart drawer', 'shoptimizer' ); ?>">
			<div id="ajax-loading" 
				 role="status" 
				 aria-live="polite" 
				 aria-label="<?php esc_attr_e('Loading cart contents', 'shoptimizer'); ?>">
				<div class="shoptimizer-loader">
					<div class="spinner" aria-hidden="true">
						<div class="bounce1"></div>
						<div class="bounce2"></div>
						<div class="bounce3"></div>
					</div>
				</div>
			</div>
			<div class="cart-drawer-heading"><?php echo esc_html( $shoptimizer_cart_title ); ?></div>
			<button type="button" aria-label="<?php esc_attr_e( 'Close drawer', 'shoptimizer' ); ?>" class="close-drawer">
				<span aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
				</span>
			</button>

			<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>

		</div>
		<?php

		// Enqueue cart drawer script
		wp_enqueue_script(
			'shoptimizer-cart-drawer',
			get_theme_file_uri( '/assets/js/cart-drawer.js' ),
			array( 'jquery' ),
			time(),
			true
		);
	}
}

/**
 * Conditionally removes the "View Cart" button from the mini cart.
 * 
 * @since 1.0.0
 * @return void
 */
function shoptimizer_remove_view_cart_minicart() {
    // Get the option directly - no need for empty initialization
    $hide_cart_link = shoptimizer_get_option( 'shoptimizer_sidebar_hide_cart_link' );
    
    // Early return if the option isn't enabled
    if ( ! $hide_cart_link ) {
        return;
    }
    
    remove_action( 
        'woocommerce_widget_shopping_cart_buttons', 
        'woocommerce_widget_shopping_cart_button_view_cart', 
        10 
    );
}

// Priority 1 ensures this runs before the button is added
add_action( 'woocommerce_widget_shopping_cart_buttons', 'shoptimizer_remove_view_cart_minicart', 1 );


if ( ! function_exists( 'shoptimizer_empty_mini_cart' ) ) {
    /**
     * Display empty mini cart content
     * Shows the empty mini cart widget area if there are no items in the cart.
     *
     * @since   2.5.4
     * @return  void
     */
    function shoptimizer_empty_mini_cart() {
        // Early return if cart is not empty
        if ( ! WC()->cart->is_empty() ) {
            return;
        }

        // Check and display empty mini cart sidebar
        if ( is_active_sidebar( 'empty-mini-cart' ) ) {
            echo '<div class="shoptimizer-empty-mini-cart">';
            dynamic_sidebar( 'empty-mini-cart' );
            echo '</div>';
        }
    }
}
add_action( 'woocommerce_before_mini_cart', 'shoptimizer_empty_mini_cart', 20 );


if ( ! function_exists( 'shoptimizer_sidebar_cart_below_text' ) ) {
    /**
     * Display Below text area Cart Drawer
     *
     * Adds a customizable text area below the mini cart buttons.
     * Text content is managed through theme options.
     *
     * @since 1.0.0
     * @return void
     */
    function shoptimizer_sidebar_cart_below_text() {
        $shoptimizer_cart_below_text = shoptimizer_get_option( 'shoptimizer_cart_below_text' );

        if ( ! empty( $shoptimizer_cart_below_text ) ) {
            echo '<div class="cart-drawer-below">';
            echo wp_kses_post( $shoptimizer_cart_below_text );
            echo '</div>';
        }
    }
}
add_action( 'woocommerce_widget_shopping_cart_after_buttons', 'shoptimizer_sidebar_cart_below_text', 10 );

/**
 *  Quantity selectors for Shoptimizer mini cart
 *
 * @package Shoptimizer
 *
 * Description: Adds quantity buttons for the Shoptimizer mini cart
 * Version: 1.0
 * Author: CommerceGurus
 */

// Initialize theme option once, outside the function
$shoptimizer_minicart_quantity = shoptimizer_get_option( 'shoptimizer_minicart_quantity' );

/**
 * Add minicart quantity fields
 *
 * @param  string $html          cart html.
 * @param  string $cart_item     cart item.
 * @param  string $cart_item_key cart item key.
 */
function add_minicart_quantity_fields($html, $cart_item, $cart_item_key) {
    $product_price = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($cart_item['data']), $cart_item, $cart_item_key);
    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
    $max_qty = $_product->get_max_purchase_quantity();
    
    if ($_product->is_sold_individually()) {
        return $product_price;
    }
    
    $max_qty_attr = (-1 !== $max_qty) ? sprintf('max="%d"', $max_qty) : '';
    $product_name = $_product->get_name();
    
    // Enhanced accessibility attributes
    $quantity_html = sprintf(
        '<div class="shoptimizer-custom-quantity-mini-cart_container">
            <div class="shoptimizer-custom-quantity-mini-cart" role="spinbutton">
                <button type="button" 
                        class="shoptimizer-custom-quantity-mini-cart_button quantity-down" 
                        aria-label="%s">-</button>
                <input type="number" 
                       class="shoptimizer-custom-quantity-mini-cart_input" 
                       data-cart_item_key="%s" 
                       min="0" 
                       %s 
                       step="1" 
                       value="%d"
                       aria-label="%s"
                       aria-live="polite">
                <button type="button" 
                        class="shoptimizer-custom-quantity-mini-cart_button quantity-up" 
                        aria-label="%s">+</button>
            </div>
        </div>',
        esc_attr(sprintf(__('Decrease quantity for %s', 'shoptimizer'), $product_name)),
        esc_attr($cart_item_key),
        $max_qty_attr,
        $cart_item['quantity'],
        esc_attr(sprintf(__('Quantity for %s', 'shoptimizer'), $product_name)),
        esc_attr(sprintf(__('Increase quantity for %s', 'shoptimizer'), $product_name))
    );

    return $product_price . $quantity_html;
}

// Only add the filter if the theme option is enabled
if ( true === $shoptimizer_minicart_quantity ) {
	add_filter( 'woocommerce_widget_cart_item_quantity', 'add_minicart_quantity_fields', 10, 3 );
}

if ( ! function_exists( 'minicart_shoptimizer_update_mini_cart' ) ) {
    /**
     * Updates mini cart quantities via AJAX.
     * 
     * @since 1.0.0
     * @return void
     */
    function minicart_shoptimizer_update_mini_cart() {
        // Add nonce check but make it non-blocking initially
        if (isset($_POST['nonce']) && !wp_verify_nonce($_POST['nonce'], 'shoptimizer_update_mini_cart_nonce')) {
            error_log('Invalid nonce in mini cart update - will enforce in future version');
        }
        
        $formdata = isset($_POST['data']) ? (array) $_POST['data'] : array();
        
        if ($formdata) {
            foreach ($formdata as $cart_item_key => $quantity) {
                // Sanitize inputs
                $cart_item_key = sanitize_text_field($cart_item_key);
                $quantity = absint($quantity);
                
                // Validate cart item exists
                if (isset(WC()->cart->get_cart()[$cart_item_key])) {
                    WC()->cart->set_quantity($cart_item_key, $quantity);
                }
            }
        }
        
        WC()->cart->calculate_totals();
        
        $data = array(
            'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array()),
            'success' => true
        );
        
        wp_send_json($data);
    }
}

// Add AJAX actions for both logged in and non-logged in users
add_action( 'wp_ajax_cg_shoptimizer_update_mini_cart', 'minicart_shoptimizer_update_mini_cart' );
add_action( 'wp_ajax_nopriv_cg_shoptimizer_update_mini_cart', 'minicart_shoptimizer_update_mini_cart' );


if ( ! function_exists( 'minicart_shoptimizer_get_scripts' ) ) {
    /**
     * Enqueue mini-cart quantity scripts
     */
    function minicart_shoptimizer_get_scripts() {
        wp_enqueue_script(
            'custom-shoptimizer-quantity-js',
            get_theme_file_uri('/assets/js/minicart-quantity_v2.js'),
            array('jquery'),
            time(),
            true
        );

        wp_localize_script(
            'custom-shoptimizer-quantity-js',
            'shoptimizer_mini_cart',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('shoptimizer_update_mini_cart_nonce'),
                'updating_text' => esc_html__('Updating cart...', 'shoptimizer'),
                'error_text' => esc_html__('Error updating cart.', 'shoptimizer')
            )
        );
    }
}
add_action('wp_enqueue_scripts', 'minicart_shoptimizer_get_scripts', 30);
