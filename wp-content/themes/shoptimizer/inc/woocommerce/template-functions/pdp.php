<?php
/**
 * PDP specific template functions and hooks
 *
 * @package shoptimizer
 * 
 * Function Index:
 * - shoptimizer_remove_product_sidebar() - Removes sidebar on single product pages
 * - shoptimizer_woocommerce_upsell_display_args() - Customizes upsells display arguments
 * - shoptimizer_upsells_title() - Customizes upsells section title
 * - shoptimizer_upsells_related() - Controls display order of upsells and related products
 * - shoptimizer_related_products() - Customizes related products display arguments
 * - shoptimizer_upsell_display() - Custom upsell display function
 * - shoptimizer_exclude_jetpack_related_from_products() - Disables Jetpack related posts on products
 * - shoptimizer_woocommerce_reviews_tab_title() - Modifies reviews tab title
 * - shoptimizer_is_lazyload_activated() - Checks if lazy load is enabled
 * - get_adjacent_in_stock_product() - Gets adjacent in-stock products
 * - shoptimizer_prev_next_product() - Displays previous/next product navigation
 * - shoptimizer_call_back_trigger() - Displays call back trigger button
 * - shoptimizer_call_back_modal() - Displays call back modal
 * - shoptimizer_get_client_ip() - Gets client IP address safely
 * - shoptimizer_check_rate_limit() - Checks rate limiting
 * - shoptimizer_get_sale_prices() - AJAX handler for variable product sale prices
 * - shoptimizer_get_sale_prices_script() - Outputs JavaScript for sale price display
 * - shoptimizer_highlight_selected_variation() - Highlights selected product variations
 * - shoptimizer_product_content_wrapper_start() - Opens product content wrapper
 * - shoptimizer_product_content_wrapper_end() - Closes product content wrapper
 * - shoptimizer_product_custom_content() - Displays custom widget content
 * - shoptimizer_related_content_wrapper_start() - Opens related content wrapper
 * - shoptimizer_related_content_wrapper_end() - Closes related content wrapper
 * - shoptimizer_add_to_cart_message_filter() - Customizes add to cart message
 * - shoptimizer_pdp_ajax_atc() - Handles AJAX add to cart
 * - shoptimizer_pdp_ajax_atc_enqueue() - Enqueues AJAX assets
 * - shoptimizer_activate_gutenberg_product() - Controls Gutenberg editor for products
 * - shoptimizer_pdp_short_description_position() - Controls short description position
 * - shoptimizer_gallery_columns() - Sets gallery thumbnail columns
 * - shoptimizer_flexslider_options() - Customizes FlexSlider options
 * - shoptimizer_sticky_variations_anchor() - Adds sticky variations anchor
 * - shoptimizer_pdp_modal_wrapper_open() - Opens modal wrapper
 * - shoptimizer_pdp_modal_wrapper_close() - Closes modal wrapper
 * - shoptimizer_single_product_shortcode_styles() - Enqueues shortcode styles
 * - shoptimizer_single_product_shortcode_ajax_scripts() - Enqueues shortcode AJAX scripts
 * - shoptimizer_pdp_shortcode_body_class() - Adds shortcode body class
 * - shoptimizer_reset_product_template() - Resets product template
 * - shoptimizer_tinyslider_js() - Enqueues tiny slider JS
 * - shoptimizer_pdp_cross_sells_carousel() - Displays cross-sells carousel
 * - shoptimizer_sticky_single_add_to_cart() - Displays theme sticky add to cart
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
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
    // Get title from theme options with default fallback
    $upsells_title = shoptimizer_get_option(
        'shoptimizer_upsells_title_text',
        esc_html__('You may also like&hellip;', 'shoptimizer')
    );

    // Ensure we have a string value
    if (!is_string($upsells_title)) {
        $upsells_title = esc_html__('You may also like&hellip;', 'shoptimizer');
    }

    // Allow the title to be filtered
    return apply_filters(
        'shoptimizer_upsells_section_title',
        wp_kses(
            $upsells_title,
            array(
                'span' => array('class' => array()),
                'br' => array(),
                'em' => array(),
                'strong' => array(),
            )
        )
    );
}

/**
 * Controls the display order of upsells and related products on single product pages.
 * 
 * When enabled via theme options, this function moves the upsells section before
 * related products by adjusting their respective hook priorities.
 *
 * @since 2.0.0
 * @return void
 */
function shoptimizer_upsells_related(): void {
    // Get theme option for upsells position
    $upsells_first = (bool) shoptimizer_get_option(
        'shoptimizer_layout_woocommerce_upsells_first',
        false
    );

    // Early return if setting is not enabled
    if (!$upsells_first) {
        return;
    }

    // Remove default upsells position
    remove_action(
        'woocommerce_after_single_product_summary',
        'woocommerce_upsell_display',
        25
    );

    // Add upsells in new position before related products
    add_action(
        'woocommerce_after_single_product_summary',
        'woocommerce_upsell_display',
        18
    );
}
add_action('after_setup_theme', 'shoptimizer_upsells_related', 99);

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
 * Single Product - exclude from Jetpack's Lazy Load.
 */
function shoptimizer_is_lazyload_activated() {
	$lazyload_status = shoptimizer_get_option( 'shoptimizer_lazy_load_status' );
	return ( true === $lazyload_status );
}

add_filter( 'lazyload_is_enabled', 'shoptimizer_is_lazyload_activated', 10, 3 );


/**
 * Display previous/next product navigation on single product pages
 * 
 * Shows navigation arrows with product preview tooltips for adjacent products
 * in the same category. Only displays if enabled in theme options.
 *
 * @since 1.0.0
 * @return void
 */
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
    return $query->have_posts() ? wc_get_product($query->posts[0]->ID) : false;
}

function shoptimizer_prev_next_product() {
    global $post;

    $prev_next_display = shoptimizer_get_option('shoptimizer_layout_woocommerce_prev_next_display');
    if (true !== $prev_next_display) {
        return;
    }

    // Get adjacent products
    $prev_product = get_adjacent_in_stock_product(true, '', true, 'product_cat');
    $next_product = get_adjacent_in_stock_product(true, '', false, 'product_cat');

    // Only output if we have products to show
    if (!$prev_product && !$next_product) {
        return;
    }
    ?>
    <div class="shoptimizer-product-prevnext">
        <?php if ($prev_product) : ?>
            <a href="<?php echo esc_url($prev_product->get_permalink()); ?>" 
               aria-label="<?php echo shoptimizer_safe_html($prev_product->get_name()); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                </svg>
                <div class="tooltip">
                    <?php echo shoptimizer_safe_html($prev_product->get_image()); ?>
                    <span class="title"><?php echo shoptimizer_safe_html($prev_product->get_name()); ?></span>
                    <span class="prevnext_price"><?php echo shoptimizer_safe_html($prev_product->get_price_html()); ?></span>
                </div>
            </a>
        <?php endif; ?>

        <?php if ($next_product) : ?>
            <a href="<?php echo esc_url($next_product->get_permalink()); ?>" 
               aria-label="<?php echo shoptimizer_safe_html($next_product->get_name()); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                          d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="tooltip">
                    <?php echo shoptimizer_safe_html($next_product->get_image()); ?>
                    <span class="title"><?php echo shoptimizer_safe_html($next_product->get_name()); ?></span>
                    <span class="prevnext_price"><?php echo shoptimizer_safe_html($next_product->get_price_html()); ?></span>
                </div>
            </a>
        <?php endif; ?>
    </div>
    <?php
}
add_action('woocommerce_single_product_summary', 'shoptimizer_prev_next_product', 0);


/**
 * Single Product Page - Call me back trigger.
 */
add_action( 'woocommerce_single_product_summary', 'shoptimizer_call_back_trigger', 79 );

if ( ! function_exists( 'shoptimizer_call_back_trigger' ) ) {
	/**
	 * Display call back trigger button on single product pages
	 * 
	 * Shows a customizable "Call Back" button that triggers a modal.
	 * Only displays if enabled in theme options.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function shoptimizer_call_back_trigger() {
		// Get theme options
		$floating_button_display = shoptimizer_get_option( 'shoptimizer_layout_floating_button_display' );
		$floating_button_text = shoptimizer_get_option( 'shoptimizer_layout_floating_button_text' );

		// Early return if feature is disabled
		if ( true !== $floating_button_display ) {
			return;
		}

		// Output the button HTML
		?>
		<div class="call-back-feature">
			<button data-trigger="callBack">
				<?php echo shoptimizer_safe_html( $floating_button_text ); ?>
			</button>
		</div>
		<?php
	}
}


/**
 * Single Product Page - Call me back modal.
 */
add_action( 'woocommerce_single_product_summary', 'shoptimizer_call_back_modal', 80 );

if ( ! function_exists( 'shoptimizer_call_back_modal' ) ) {
	/**
	 * Display call back modal on single product pages
	 * 
	 * Shows a modal dialog containing product information and a contact form.
	 * Only displays if the floating button feature is enabled in theme options.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function shoptimizer_call_back_modal(): void {
		// Early return if feature is disabled
		$floating_button_display = shoptimizer_get_option( 'shoptimizer_layout_floating_button_display' );
		if ( true !== $floating_button_display ) {
			return;
		}

		// Get current product
		global $product;
		if ( ! $product ) {
			return;
		}

		?>
		<dialog class="shoptimizer-modal" 
				data-shoptimizermodal-id="callBack"
				role="dialog"
				aria-modal="true"
				aria-labelledby="modal-title">
			<h2 id="modal-title" class="screen-reader-text"><?php esc_html_e('Request a Call Back', 'shoptimizer'); ?></h2>
			<div class="shoptimizer-modal--container">
				<form method="dialog">
					<button type="button" 
							class="shoptimizer-modal--button_close" 
							data-dismiss="modal">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
						</svg>                      
					</button>
				</form>

				<div class="shoptimizer-modal--content">
					<div class="callback-product_wrapper">
						<?php echo wp_kses_post($product->get_image()); ?>
						
						<div class="callback-product_content">
							<div class="callback-product_title">
								<?php echo esc_html($product->get_name()); ?>
							</div>

							<?php if ( $product->get_review_count() && wc_review_ratings_enabled() ) : ?>
								<div class="callback-product_rating">
									<?php echo wc_get_rating_html( $product->get_average_rating() ); ?>
								</div>
							<?php endif; ?>
									
							<div class="callback-product_price">
								<?php echo wp_kses_post( $product->get_price_html() ); ?>
							</div>
						</div>
					</div>
						
					<?php 
					ob_start();
					dynamic_sidebar('floating-button-content');
					echo do_shortcode(ob_get_clean());
					?>
				</div>
			</div>
		</dialog>
		<?php
	}
}



/**
 * Helper function to get client IP address safely
 *
 * @return string Client IP address
 */
function shoptimizer_get_client_ip() {
    $ip = '';
    
    // Check for CloudFlare IP
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = sanitize_text_field($_SERVER['HTTP_CF_CONNECTING_IP']);
    } 
    // Check for proxy IP
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = sanitize_text_field(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    // Get direct IP
    elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
}

/**
 * Helper function to check rate limiting
 *
 * @param string $action The action to rate limit
 * @param int $expiration Expiration time in seconds
 * @param int $limit Number of allowed requests
 * @return bool True if within limit, false if exceeded
 */
function shoptimizer_check_rate_limit($action, $expiration, $limit) {
    $ip = shoptimizer_get_client_ip();
    if (empty($ip)) {
        return false;
    }
    
    $transient_name = 'rate_limit_' . $action . '_' . md5($ip);
    $current = get_transient($transient_name);
    
    if (false === $current) {
        set_transient($transient_name, 1, $expiration);
        return true;
    }
    
    if ($current >= $limit) {
        return false;
    }
    
    set_transient($transient_name, $current + 1, $expiration);
    return true;
}

/**
 * Ajax handler for getting variable product sale label prices.
 * 
 * Securely retrieves and calculates sale percentages for variable product variations.
 * Includes nonce verification, input validation, and proper error handling.
 *
 * @since 1.0.0
 * @return void
 */
function shoptimizer_get_sale_prices() {
    try {
        // Verify nonce and check capabilities
        if (!check_ajax_referer('shoptimizer_sale_prices', 'security', false)) {
            throw new Exception(__('Security check failed', 'shoptimizer'));
        }

        // Rate limiting
        if (!shoptimizer_check_rate_limit('get_sale_prices', 60, 30)) { // 30 requests per minute
            throw new Exception(__('Too many requests, please try again later', 'shoptimizer'));
        }

        // Validate product ID
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if (!$product_id) {
            throw new Exception(__('Invalid product ID', 'shoptimizer'));
        }

        // Get and validate product
        $product = wc_get_product($product_id);
        if (!$product || !$product->is_type('variable')) {
            throw new Exception(__('Invalid product type', 'shoptimizer'));
        }

        // Verify product visibility and status
        if (!$product->is_visible() || $product->get_status() !== 'publish') {
            throw new Exception(__('Product not available', 'shoptimizer'));
        }

        // Initialize response array
        $response = array(
            'success' => true,
            'percents' => array()
        );

        // Get variation prices with caching
        $prices = $product->get_variation_prices(true);
        if (empty($prices['price'])) {
            throw new Exception(__('No variation prices found', 'shoptimizer'));
        }

        // Calculate sale percentages
        foreach ($prices['price'] as $variation_id => $price) {
            $sale_price = $prices['sale_price'][$variation_id];
            $regular_price = $prices['regular_price'][$variation_id];

            // Only calculate if there's actually a sale
            if ($regular_price > 0 && $sale_price !== $regular_price) {
                $percentage = round(100 - ($sale_price / $regular_price * 100));
                if ($percentage > 0) {
                    // Sanitize the variation ID and percentage before adding to response
                    $variation_id = absint($variation_id);
                    $response['percents'][$variation_id] = '-' . absint($percentage) . '%';
                }
            }
        }

        wp_send_json_success($response);

    } catch (Exception $e) {
        wp_send_json_error(array(
            'message' => $e->getMessage()
        ));
    }
}
add_action('wp_ajax_shoptimizer_get_sale_prices', 'shoptimizer_get_sale_prices');
add_action('wp_ajax_nopriv_shoptimizer_get_sale_prices', 'shoptimizer_get_sale_prices');

/**
 * Outputs JavaScript to handle sale price display for variable products.
 * 
 * This function generates inline JavaScript that manages the visibility of sale labels
 * for variable products based on the selected variation. It makes an AJAX call to
 * fetch sale prices and updates the sale badge accordingly.
 *
 * @since 1.0.0
 * @return void
 */
function shoptimizer_get_sale_prices_script() {
    global $product;

    // Early returns for invalid conditions
    if (!is_product() || !$product || !$product->is_type('variable') || !$product->is_on_sale()) {
        return;
    }

    $show_sale_badge = shoptimizer_get_option('shoptimizer_layout_woocommerce_display_badge');
    if (true !== $show_sale_badge) {
        return;
    }

    // Generate nonce for security
    $nonce = wp_create_nonce('shoptimizer_sale_prices');
    
    // Prepare data for JS with proper escaping
    $data = array(
        'ajaxurl' => esc_url(admin_url('admin-ajax.php')),
        'product_id' => absint($product->get_id()),
        'security' => $nonce
    );
    ?>
<script type="text/javascript">
/* <![CDATA[ */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        var $saleBadge = $('.summary .sale-item.product-label');
        var salesData = null;
        var shoptimizerData = <?php echo wp_json_encode($data); ?>;
        
        // Initially hide the sale badge
        $saleBadge.css('visibility', 'hidden');

        // Fetch sale prices via AJAX
        $.ajax({
            type: 'POST',
            url: shoptimizerData.ajaxurl,
            data: {
                'action': 'shoptimizer_get_sale_prices',
                'product_id': shoptimizerData.product_id,
                'security': shoptimizerData.security
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data && response.data.percents) {
                    salesData = response.data.percents;
                    updateSaleBadge();
                }
            },
            error: function(xhr, status, error) {
                console.warn('Failed to fetch sale prices:', error);
            }
        });

        // Update badge when variation changes
        $('.summary input.variation_id').on('change', updateSaleBadge);

        function updateSaleBadge() {
            var variationId = $('.summary input.variation_id').val();
            
            if (variationId && salesData && salesData.hasOwnProperty(variationId)) {
                $saleBadge.html(salesData[variationId]).css('visibility', 'visible');
            } else {
                $saleBadge.css('visibility', 'hidden');
            }
        }
    });
})(jQuery);
/* ]]> */
</script>
    <?php
}
add_action('wp_footer', 'shoptimizer_get_sale_prices_script', 999);




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
 * Add opening wrapper div to product content
 * 
 * Creates a container div for product details on single product pages.
 * This wrapper helps with layout and styling of the product information.
 *
 * @since 1.0.0
 * @return void
 */
function shoptimizer_product_content_wrapper_start() {
    echo '<div class="product-details-wrapper">';
}
add_action( 'woocommerce_before_single_product_summary', 'shoptimizer_product_content_wrapper_start', 5 );

/**
 * Add closing wrapper div to product content
 * 
 * Closes the product details wrapper div opened by shoptimizer_product_content_wrapper_start().
 * Includes a comment for easier debugging in page source.
 *
 * @since 1.0.0
 * @return void
 */
function shoptimizer_product_content_wrapper_end() {
    echo '</div><!--/product-details-wrapper-end-->';
}
add_action( 'woocommerce_single_product_summary', 'shoptimizer_product_content_wrapper_end', 120 );

/**
 * Display custom widget content below Add to Cart button
 * 
 * Shows content from the 'single-product-field' widget area if it's active
 * and not disabled via product meta options.
 *
 * @since 1.0.0
 * @return void
 */
function shoptimizer_product_custom_content() {
	// Check if widget is disabled for this product
	$disable_widget = get_post_meta( get_the_ID(), 'shoptimizer-disable-pdp-custom-widget', true );
	
	// Early return if widget is disabled
	if ( 'disabled' === $disable_widget ) {
		return;
	}
	
	// Display widget area if active
	if ( is_active_sidebar( 'single-product-field' ) ) {
		echo '<div class="product-widget">';
		dynamic_sidebar( 'single-product-field' );
		echo '</div>';
	}
}
add_action( 'woocommerce_single_product_summary', 'shoptimizer_product_custom_content', 45 );

add_action( 'woocommerce_after_single_product_summary', 'shoptimizer_related_content_wrapper_start', 10 );
add_action( 'woocommerce_after_single_product_summary', 'shoptimizer_related_content_wrapper_end', 60 );

/**
 * Add opening wrapper for related products section
 * 
 * Creates a container section for related products and upsells.
 * This wrapper helps with layout and styling of the related products area.
 *
 * @since 1.0.0
 * @return void
 */
function shoptimizer_related_content_wrapper_start() {
    echo '<section class="related-wrapper">';
}

/**
 * Add closing wrapper for related products section
 * 
 * Closes the related products wrapper section opened by shoptimizer_related_content_wrapper_start().
 * Wraps both related products and upsells sections.
 *
 * @since 1.0.0
 * @return void
 */
function shoptimizer_related_content_wrapper_end() {
    echo '</section>';
}

/**
 * Single Product Page - Reorder Upsells position.
 */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
add_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 25 );

/**
 * Single Product Page - Reorder Rating position.
 */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 20 );



/**
 * Single Product Page - Added to cart message.
 */
add_filter( 'wc_add_to_cart_message_html', 'shoptimizer_add_to_cart_message_filter', 10, 2 );

/**
 * Customize the "Added to cart" message
 * 
 * Modifies WooCommerce's default add to cart message to include
 * custom styling and additional buttons for cart and checkout.
 *
 * @since 1.0.0
 * @param string $message The default WooCommerce message
 * @return string Modified message HTML
 */
function shoptimizer_add_to_cart_message_filter( $message ) {
    $message_html = sprintf(
        '<div class="message-inner">
            <div class="message-content">%s</div>
            <div class="buttons-wrapper">
                <a href="%s" class="button checkout">
                    <span>%s</span>
                </a>
                <a href="%s" class="button cart">
                    <span>%s</span>
                </a>
            </div>
        </div>',
        shoptimizer_safe_html( $message ),
        esc_url( wc_get_page_permalink( 'checkout' ) ),
        esc_html__( 'Checkout', 'shoptimizer' ),
        esc_url( wc_get_page_permalink( 'cart' ) ),
        esc_html__( 'View Cart', 'shoptimizer' )
    );

    return $message_html;
}



if ( ! function_exists( 'shoptimizer_pdp_ajax_atc' ) ) {
	/**
	 * Handles AJAX add to cart functionality for single product pages.
	 * 
	 * Processes the add to cart request, validates input, and returns
	 * cart fragments and notices for frontend updates.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function shoptimizer_pdp_ajax_atc() {
		try {
			// Verify nonce and check if request is valid
			if ( ! check_ajax_referer( 'ajax-nonce', 'security', false ) ) {
				throw new Exception( __( 'Security check failed', 'shoptimizer' ) );
			}

			// Validate product data
			$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
			$product_id = isset( $_POST['add-to-cart'] ) ? absint( $_POST['add-to-cart'] ) : 0;
			
			if ( ! $product_id ) {
				throw new Exception( __( 'Invalid product', 'shoptimizer' ) );
			}

			// Get product
			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				throw new Exception( __( 'Product not found', 'shoptimizer' ) );
			}

			// Set SKU for tracking
			$sku = $variation_id ? $variation_id : $product_id;

			// Start output buffering for notices
			ob_start();
			wc_print_notices();
			$notices = ob_get_clean();

			// Get mini cart content
			ob_start();
			woocommerce_mini_cart();
			$mini_cart = ob_get_clean();

			// Prepare cart fragments
			$fragments = apply_filters(
				'woocommerce_add_to_cart_fragments',
				array(
					'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
				)
			);

			// Get cart hash
			$cart_hash = '';
			if ( WC()->cart && WC()->cart->get_cart_for_session() ) {
				$cart_hash = apply_filters( 
					'woocommerce_add_to_cart_hash', 
					md5( wp_json_encode( WC()->cart->get_cart_for_session() ) ),
					WC()->cart->get_cart_for_session()
				);
			}

			// Trigger added to cart action
			do_action( 'woocommerce_ajax_added_to_cart', $sku );

			// Send success response
			wp_send_json_success(array(
				'notices' => $notices,
				'fragments' => $fragments,
				'cart_hash' => $cart_hash,
			));

		} catch ( Exception $e ) {
			wp_send_json_error(array(
				'error' => $e->getMessage(),
			));
		}
	}
}

// Only add AJAX handlers if AJAX add to cart is enabled
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
 * Block Editor Integration for Product Detail Pages
 * 
 * Controls whether the Gutenberg block editor is enabled for WooCommerce product pages.
 * This feature allows using the modern WordPress editor for product descriptions and content.
 *
 * @since 2.0.0
 */

// Get block editor setting
$shoptimizer_layout_pdp_block_editor = shoptimizer_get_option(
    'shoptimizer_layout_pdp_block_editor'
);

if ($shoptimizer_layout_pdp_block_editor) {
    add_filter(
        'use_block_editor_for_post_type',
        'shoptimizer_activate_gutenberg_product',
        10,
        2
    );
}

/**
 * Enables the Gutenberg block editor for WooCommerce product pages.
 *
 * @param bool   $can_edit  Whether the post type can be edited or not
 * @param string $post_type The post type being checked
 * @return bool
 */
function shoptimizer_activate_gutenberg_product($can_edit, $post_type) {
    if ($post_type === 'product') {
        return true;
    }
    return $can_edit;
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
 * Customize FlexSlider options for product gallery
 * 
 * Modifies WooCommerce's default FlexSlider configuration to enable
 * directional navigation arrows within the core product gallery slider.
 *
 * @since 1.0.0
 * @param array $options Default FlexSlider options
 * @return array Modified FlexSlider options
 */
function shoptimizer_flexslider_options( $options ) {
    $options['directionNav'] = true;
    return $options;
}

add_action( 'woocommerce_archive_description', 'shoptimizer_category_image', 20 );



/**
 * Add a div with an ID before the variations form, so that the sticky select options button can scroll up to it.
 */
add_action( 'woocommerce_before_add_to_cart_form', 'shoptimizer_sticky_variations_anchor' );

/**
 * Add anchor div for sticky variations scroll
 * 
 * Adds a div with an ID that the sticky select options button 
 * uses as a scroll target when clicked.
 *
 * @since 1.0.0
 * @return void
 */
function shoptimizer_sticky_variations_anchor() {
    echo '<div id="shoptimizer-sticky-anchor"></div>';
}


/**
 * Opens modal wrapper div for product page modals.
 * Includes validation checks to ensure proper context and security.
 *
 * @since 2.0.0
 * @return void
 */
function shoptimizer_pdp_modal_wrapper_open(): void {
    // Validate we're on a product page and WooCommerce is active
    if (!is_product() || !function_exists('WC')) {
        return;
    }

    // Validate we have a valid product
    global $product;
    if (!$product instanceof WC_Product) {
        return;
    }

    // Start output buffering for security
    ob_start();
    echo '<div id="shoptimizer-modals-wrapper">';
    echo wp_kses_post(ob_get_clean());
}

// Add modal wrapper hooks
add_action('woocommerce_single_product_summary', 'shoptimizer_pdp_modal_wrapper_open', 36);
add_action('woocommerce_single_product_summary', 'shoptimizer_pdp_modal_wrapper_close', 38);

/**
 * Closes modal wrapper div for product page modals.
 * Includes validation checks to ensure proper context and security.
 *
 * @since 2.0.0
 * @return void
 */
function shoptimizer_pdp_modal_wrapper_close(): void {
    // Validate we're on a product page and WooCommerce is active
    if (!is_product() || !function_exists('WC')) {
        return;
    }

    // Validate we have a valid product
    global $product;
    if (!$product instanceof WC_Product) {
        return;
    }

    // Start output buffering for security
    ob_start();
    echo '</div>';
    echo wp_kses_post(ob_get_clean());
}


/**
 * Enqueue styles and scripts for product shortcode display.
 *
 * @since 2.0.0
 * @return void
 */
function shoptimizer_single_product_shortcode_styles() {
    if (!is_singular()) {
        return;
    }

    global $post, $shoptimizer_version;
    if (!$post || !has_shortcode($post->post_content, 'product_page')) {
        return;
    }

    wp_enqueue_style('shoptimizer-product', 
        get_template_directory_uri() . '/assets/css/main/product.css',
        array(),
        $shoptimizer_version
    );

    wp_enqueue_style('shoptimizer-modal',
        get_template_directory_uri() . '/assets/css/main/modal.css',
        array(),
        $shoptimizer_version
    );

    wp_enqueue_script('shoptimizer-quantity',
        get_template_directory_uri() . '/assets/js/quantity.min.js',
        array('jquery'),
        '1.1.1',
        true
    );
}
add_action('wp_enqueue_scripts', 'shoptimizer_single_product_shortcode_styles', 20);

/**
 * Enqueues AJAX scripts for single product shortcode functionality.
 * 
 * This function handles the enqueuing of JavaScript files and localization data
 * needed for AJAX-based add to cart functionality on pages containing the 
 * [product_page] shortcode.
 *
 * @since 2.0.0
 * @return void
 */
function shoptimizer_single_product_shortcode_ajax_scripts(): void {
    // Early return if not in a singular context
    if (!is_singular()) {
        return;
    }

    // Get required globals
    global $post, $shoptimizer_version;

    // Early return if no valid post or no product_page shortcode
    if (!($post instanceof WP_Post) || !has_shortcode($post->post_content, 'product_page')) {
        return;
    }

    // Check if AJAX add to cart is enabled in theme options
    $ajax_enabled = (bool) shoptimizer_get_option('shoptimizer_layout_woocommerce_single_product_ajax');
    if (!$ajax_enabled) {
        return;
    }

    // Prepare script dependencies
    $dependencies = array('jquery', 'wc-add-to-cart');

    // Enqueue the AJAX script
    wp_enqueue_script(
        'shoptimizer-ajax-script',
        get_template_directory_uri() . '/assets/js/single-product-ajax.js',
        $dependencies,
        $shoptimizer_version,
        true
    );

    // Localize the script with required data
    wp_localize_script(
        'shoptimizer-ajax-script',
        'shoptimizer_ajax_obj',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('ajax-nonce'),
            'i18n'    => array(
                'addingToCart' => esc_html__('Adding to cart...', 'shoptimizer'),
                'added'        => esc_html__('Added to cart', 'shoptimizer'),
                'error'        => esc_html__('Error occurred. Please try again.', 'shoptimizer'),
            ),
            'cartUrl' => wc_get_cart_url(),
            'isAjax'  => true,
        )
    );

    // Add support for WooCommerce blocks if active
    if (class_exists('WC_Admin_Assets')) {
        wp_enqueue_script('wc-blocks-registry');
        wp_enqueue_script('wc-blocks-middleware');
    }
}

function shoptimizer_pdp_shortcode_body_class( $shoptimizer_pdp_shortcode ) {

	global $post;

	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'product_page' ) ) {
		$shoptimizer_pdp_shortcode[] = 'pdp-shortcode';
	}
	return $shoptimizer_pdp_shortcode;
}
add_filter( 'body_class', 'shoptimizer_pdp_shortcode_body_class' );



/**
* Remove "Description" heading from WooCommerce tabs.
*
* @since 1.0.0
*/
add_filter( 'woocommerce_product_description_heading', '__return_null' );



/**
 * Remove default product template for block editor compatibility
 * 
 * Fixes compatibility issue with WooCommerce product tab block by removing
 * the default template from post type arguments.
 *
 * @since 2.6.6
 * @param array $post_type_args Post type registration arguments
 * @return array Modified post type arguments
 */
function shoptimizer_reset_product_template( $post_type_args ) {
    if ( isset( $post_type_args['template'] ) ) {
        unset( $post_type_args['template'] );
    }

    return $post_type_args;
}
add_filter( 'woocommerce_register_post_type_product', 'shoptimizer_reset_product_template' );



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


/**
 * Displays cross-sells carousel on product detail pages.
 * Includes security measures and proper data validation.
 *
 * @since 2.0.0
 * @return void
 */
function shoptimizer_pdp_cross_sells_carousel(): void {
    // Early returns
    if (!is_product() || !function_exists('WC')) {
        return;
    }

    // Validate theme option
    $cross_sells_enabled = (bool) shoptimizer_get_option('shoptimizer_cross_sells_carousel');
    if (!$cross_sells_enabled) {
        return;
    }

    // Get and validate product
    global $product;
    if (!$product instanceof WC_Product) {
        $product = wc_get_product(); // Fix: Use wc_get_product() instead of direct WC_Product
        if (!$product instanceof WC_Product) {
            return;
        }
    }

    // Get cross-sells with sanitization
    $cross_sell_ids = array_filter(array_map('absint', $product->get_cross_sell_ids()));
    if (empty($cross_sell_ids)) {
        return;
    }

    // Get and sanitize heading
    $carousel_heading = wp_kses(
        shoptimizer_get_option('shoptimizer_cross_sells_carousel_heading'),
        array(
            'span' => array('class' => array()),
            'em' => array(),
            'strong' => array()
        )
    );

    // Define AJAX enabled state
    $ajax_enabled = get_option('woocommerce_enable_ajax_add_to_cart') === 'yes';

    // Start output buffering for security
    ob_start();
    ?>
    <div class="pdp-complementary-carousel" 
         role="region" 
         aria-label="<?php esc_attr_e('Related Products', 'shoptimizer'); ?>"
         aria-roledescription="carousel">
        <div class="pdp-complementary--header">
            <?php if (!empty($carousel_heading)) : ?>
                <div class="pdp-complementary--heading">
                    <?php echo wp_kses_post($carousel_heading); ?>
                </div>
            <?php endif; ?>
            
            <div class="pdp-complementary--nav">
                <button type="button" class="tns-prev pdp-complementary--nav-prev" aria-label="<?php esc_attr_e('Previous product', 'shoptimizer'); ?>" aria-controls="pdp-complementary-container" aria-disabled="false">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>
                <button type="button" class="tns-next pdp-complementary--nav-next" aria-label="<?php esc_attr_e('Next product', 'shoptimizer'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="tns-carousel">
            <div class="pdp-complementary--container">
                <?php
                foreach ($cross_sell_ids as $cross_sell_id) {
                    $cross_sell_product = wc_get_product($cross_sell_id);
                    
                    // Skip if product is invalid or not visible
                    if (!$cross_sell_product || !$cross_sell_product->is_visible()) {
                        continue;
                    }

                    // Force meta refresh for accurate pricing
                    $cross_sell_product->read_meta_data(true);

                    // Get and validate product data
                    $product_url = esc_url(get_permalink($cross_sell_id));
                    $product_name = esc_html($cross_sell_product->get_name());
                    $thumbnail = $cross_sell_product->get_image('woocommerce_thumbnail');
                    if (empty($thumbnail)) {
                        $thumbnail = wc_placeholder_img('woocommerce_thumbnail');
                    }
                    ?>
                    <div class="pdp-complementary-item">
                        <div class="pdp-complementary--single">
                            <a href="<?php echo $product_url; ?>" 
                               aria-label="<?php echo esc_attr(sprintf(__('View %s product details', 'shoptimizer'), $product_name)); ?>">
                                <?php echo $thumbnail; ?> <!-- Fixed: Use $thumbnail instead of $product->get_image() -->
                            </a>
                            <div class="pdp-complementary--content">
                                <span class="pdp-complementary--title">
                                    <a href="<?php echo $product_url; ?>" 
                                       aria-label="<?php echo esc_attr(sprintf(__('View %s product details', 'shoptimizer'), $product_name)); ?>">
                                        <?php echo $product_name; ?>
                                    </a>
                                </span>
                                <span class="price"><?php echo wp_kses_post($cross_sell_product->get_price_html()); ?></span>
                                <?php if ($cross_sell_product->is_purchasable() && $cross_sell_product->is_in_stock()) : ?>
                                    <div class="pdp-complementary--add-to-cart">
                                        <?php
                                        if ($cross_sell_product->is_type('simple')) {
                                            $add_to_cart_url = esc_url($cross_sell_product->add_to_cart_url());
                                            $add_to_cart_text = esc_html($cross_sell_product->add_to_cart_text());
                                            printf(
                                                '<a href="%1$s" class="button%2$s" %3$s %4$s %5$s %6$s>%7$s</a>',
                                                $add_to_cart_url,
                                                $ajax_enabled ? ' product_type_simple add_to_cart_button ajax_add_to_cart' : '',
                                                $ajax_enabled ? ' data-product_id="' . esc_attr($cross_sell_product->get_id()) . '"' : '',
                                                $ajax_enabled ? ' data-quantity="1"' : '',
                                                $ajax_enabled ? ' rel="nofollow"' : '',
                                                $ajax_enabled ? ' aria-label="' . esc_attr(sprintf(__('Add %s to cart', 'shoptimizer'), $product_name)) . '"' : '',
                                                $add_to_cart_text
                                            );
                                        } else {
                                            $product_url = esc_url($cross_sell_product->add_to_cart_url());
                                            $product_type = esc_attr($cross_sell_product->get_type());
                                            $product_id = esc_attr($cross_sell_product->get_id());
                                            $product_sku = esc_attr($cross_sell_product->get_sku());
                                            $add_to_cart_text = esc_html($cross_sell_product->add_to_cart_text());
                                            printf(
                                                '<a href="%1$s" class="button product_type_%2$s" %3$s %4$s %5$s %6$s>%7$s</a>',
                                                $product_url,
                                                $product_type,
                                                $ajax_enabled ? ' data-product_id="' . $product_id . '"' : '',
                                                $ajax_enabled ? ' data-quantity="1"' : '',
                                                $ajax_enabled ? ' rel="nofollow"' : '',
                                                $ajax_enabled ? ' aria-label="' . esc_attr(sprintf(__('Add %s to cart', 'shoptimizer'), $product_name)) . '"' : '',
                                                $add_to_cart_text
                                            );
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    // Output the buffered content
    echo ob_get_clean();
}
add_action('woocommerce_single_product_summary', 'shoptimizer_pdp_cross_sells_carousel', 90);


if ( ! function_exists( 'shoptimizer_sticky_single_add_to_cart' ) ) {
	/**
	 * Display sticky add to cart bar on single product pages
	 * 
	 * Shows a sticky bar with product image, title, price and add to cart button
	 * when scrolling down the product page. Only displays if enabled in theme options
	 * and if the product is in stock.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	function shoptimizer_sticky_single_add_to_cart(): void {
		// Early returns with strict type checking
		if (!is_product() || !function_exists('WC')) {
			return;
		}

		// Get and validate product
		global $product;
		if (!($product instanceof WC_Product)) {
			return;
		}

		// Verify product status and stock
		if (!$product->is_visible() || !$product->is_purchasable() || !$product->is_in_stock()) {
			return;
		}

		// Check if sticky cart is enabled in theme options
		$sticky_cart_enabled = (bool) shoptimizer_get_option('shoptimizer_layout_woocommerce_sticky_cart_display');
		if (!$sticky_cart_enabled) {
			return;
		}

		// Generate nonce for AJAX requests
		$nonce = wp_create_nonce('shoptimizer_sticky_cart');

		// Add JavaScript for sticky functionality with improved security
		$sticky_js = "
			(function() {
				const stickyContainer = document.querySelector('.shoptimizer-sticky-add-to-cart');
				if (!stickyContainer) return;

				function toggleStickyClass() {
					const scrollPos = window.scrollY;
					const threshold = 150;
					
					if (scrollPos > threshold) {
						stickyContainer.classList.add('visible');
					} else {
						stickyContainer.classList.remove('visible');
					}
				}

				function handleBottomScroll() {
					const isAtBottom = (window.innerHeight + window.pageYOffset) >= document.documentElement.offsetHeight;
					if (isAtBottom) {
						stickyContainer.classList.remove('visible');
					}
				}

				// Throttle scroll events
				let ticking = false;
				window.addEventListener('scroll', function() {
					if (!ticking) {
						window.requestAnimationFrame(function() {
							toggleStickyClass();
							handleBottomScroll();
							ticking = false;
						});
						ticking = true;
					}
				}, { passive: true });
			})();
		";

		wp_add_inline_script('shoptimizer-main', $sticky_js);

		// Start output buffering for security
		ob_start();
		?>
		<section class="shoptimizer-sticky-add-to-cart" 
				 role="region" 
				 aria-label="<?php esc_attr_e('Sticky Add to Cart', 'shoptimizer'); ?>"
				 aria-live="polite">
			<div class="col-full">
				<div class="shoptimizer-sticky-add-to-cart__content">
					<?php 
					// Product thumbnail with fallback
					$thumbnail = $product->get_image('woocommerce_gallery_thumbnail');
					if (empty($thumbnail)) {
						$thumbnail = wc_placeholder_img('woocommerce_gallery_thumbnail');
					}
					echo wp_kses_post($thumbnail);
					?>

					<div class="shoptimizer-sticky-add-to-cart__content-product-info">
						<span class="shoptimizer-sticky-add-to-cart__content-title">
							<?php echo esc_html($product->get_name()); ?>
						</span>    
						<?php
						// Display ratings if enabled
						$review_count = $product->get_review_count();
						if ($review_count && wc_review_ratings_enabled()) {
							echo wc_get_rating_html($product->get_average_rating());
						}
						?>
					</div>

					<div class="shoptimizer-sticky-add-to-cart__content-button">
						<span class="shoptimizer-sticky-add-to-cart__content-price">
							<?php echo wp_kses_post($product->get_price_html()); ?>
						</span>

						<?php 
						$ajax_enabled = (bool) shoptimizer_get_option('shoptimizer_layout_woocommerce_single_product_ajax');
						$product_type = $product->get_type();
						$product_id = $product->get_id();

						// Handle different product types
						if (in_array($product_type, array('variable', 'composite', 'bundle', 'grouped'), true)) {
							printf(
								'<a href="#shoptimizer-sticky-anchor" class="variable-grouped-sticky button">%s</a>',
								esc_html__('Select options', 'shoptimizer')
							);
						} elseif ($product_type === 'external') {
							printf(
								'<a href="%1$s" class="button" rel="nofollow" target="_blank">%2$s</a>',
								esc_url($product->get_product_url()),
								esc_html($product->get_button_text())
							);
						} else {
							// Simple product
							$add_to_cart_url = $product->add_to_cart_url();
							$add_to_cart_text = $product->add_to_cart_text();
							
							printf(
								'<a href="%1$s" class="button" %2$s %3$s %4$s %5$s" %6$s>%7$s</a>',
								esc_url($add_to_cart_url),
								$ajax_enabled ? 'data-product_id="' . esc_attr($product_id) . '"' : '',
								$ajax_enabled ? 'data-quantity="1"' : '',
								$ajax_enabled ? 'rel="nofollow"' : '',
								$ajax_enabled ? 'target="_blank"' : '',
								$ajax_enabled ? 'aria-label="' . esc_attr(sprintf(__('Add %s to cart', 'shoptimizer'), $product->get_name())) . '"' : '',
								esc_html($add_to_cart_text)
							);
						}
						?>
					</div>
				</div>
			</div>
		</section>
		<?php
		// Output the buffered content
		echo wp_kses_post(ob_get_clean());
	}
}