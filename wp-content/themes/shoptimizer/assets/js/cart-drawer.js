/**
 * Shoptimizer Cart Drawer Script
 * Handles cart drawer functionality including opening/closing and AJAX loading states
 *
 * @package Shoptimizer
 */

jQuery(document).ready(function($) {
    // Handle cart item addition
    $('body').on('added_to_cart', function(event, fragments, cart_hash) {
        if (!$('body').hasClass('elementor-editor-active')) {
            $('body').addClass('drawer-open');
            $('#shoptimizerCartDrawer').focus();
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const body = document.querySelector('body');
    const cartDrawer = document.getElementById('shoptimizerCartDrawer');

    // Handle all click events for the cart
    document.addEventListener('click', function(event) {
        // Close drawer when clicking outside
        const isClickInsideDrawer = event.target.closest('.shoptimizer-mini-cart-wrap');
        const isDrawerItself = event.target.classList.contains('shoptimizer-mini-cart-wrap');
        
        if (!isDrawerItself && !isClickInsideDrawer) {
            body.classList.remove('drawer-open');
        }

        // Handle cart icon clicks
        const isCartIcon = event.target.classList.contains('shoptimizer-cart');
        const isCartIconChild = event.target.closest('.shoptimizer-cart');
        
        if (isCartIcon || isCartIconChild) {
            const isHeaderCart = event.target.closest('.site-header-cart');
            const isShortcodeCart = event.target.closest('.shoptimizer-cart-shortcode');

            if (isHeaderCart || isShortcodeCart) {
                event.preventDefault();
                body.classList.toggle('drawer-open');
                cartDrawer.focus();
            }
        }

        // Handle close button clicks
        if (event.target.classList.contains('close-drawer')) {
            body.classList.remove('drawer-open');
        }
    });

    // Initialize loading state
    document.querySelector('#ajax-loading').style.display = 'none';
});

// Handle AJAX loading states
(function($) {
    'use strict';

    const ajaxEvents = [
        'wc-ajax=get_refreshed_fragments',
        'wc-ajax=remove_from_cart'
    ];

    function updateLoadingState(settings, isLoading) {
        const hasMatchingEvent = ajaxEvents.some(function(event) {
            return settings.url.indexOf(event) !== -1;
        });

        if (hasMatchingEvent) {
            $('#ajax-loading').css('display', isLoading ? 'block' : 'none');
        }
    }

    // Monitor AJAX events
    ajaxEvents.forEach(function(event) {
        $(document).ajaxSend(function(event, jqXHR, settings) {
            updateLoadingState(settings, true);
        });

        $(document).ajaxComplete(function(event, jqXHR, settings) {
            updateLoadingState(settings, false);
        });
    });
}(jQuery)); 