/**
 * Shoptimizer Mini Cart Quantity Script V2
 * Based on original minicart-quantity.js with improved security
 *
 * @package Shoptimizer
 */

(() => {
    let changEv = new Event('change');
    let sidebarEl = [];
    let timerId;
    let miniItems;
    const shopWidget = document.querySelector('.widget_shopping_cart');

    const shoptimizerDelayFunction = function(func, delay) {
        clearTimeout(timerId);
        timerId = setTimeout(func, delay);
    };

    function shoptimizerInitMiniCartQty() {
        if (!shopWidget) {
            return;
        }
        sidebarEl = [];
        miniItems = shopWidget.querySelectorAll('.shoptimizer-custom-quantity-mini-cart');
        miniItems.forEach(function(item) {
            const input = item.querySelector('input');
            const buttons = Array.from(item.querySelectorAll('.shoptimizer-custom-quantity-mini-cart_button'));
            
            if (input && buttons.length) {
                sidebarEl.push({
                    qtInput: input,
                    qtButtons: buttons
                });
                
                // Add keyboard support
                buttons.forEach(btn => {
                    btn.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.click();
                        }
                    });
                });
                
                // Announce quantity changes
                input.addEventListener('change', function() {
                    const announcement = document.createElement('div');
                    announcement.setAttribute('aria-live', 'polite');
                    announcement.setAttribute('class', 'screen-reader-text');
                    announcement.textContent = `Quantity updated to ${this.value}`;
                    item.appendChild(announcement);
                    setTimeout(() => announcement.remove(), 1000);
                });
            }
        });
        
        sidebarEl.forEach(function(item) {
            item.qtButtons.forEach(function(btn) {
                btn.addEventListener('click', shoptimizerEachSideBtnListener);
            });
            item.qtInput.addEventListener('change', shoptimizerUpdateMiniCart);
        });
    }

    function shoptimizerEachSideBtnListener() {
        const item = sidebarEl.find((el) => el.qtButtons.includes(this));
        let value = parseInt(item.qtInput.value, 10);
        value = isNaN(value) ? 1 : value;
        let minValue = parseInt(item.qtInput.getAttribute('min'), 10);
        let maxValue = parseInt(item.qtInput.getAttribute('max'), 10);
        if (this.classList.contains('quantity-up')) {
            value++;
            if (!isNaN(maxValue) && value > maxValue) {
                value = maxValue;
            }
        } else {
            value--;
            if (!isNaN(minValue) && value < minValue) {
                value = minValue;
            }
            1 > value ? (value = 1) : '';
        }
        if (item.qtInput.value != value) {
            item.qtInput.value = value;
            item.qtInput.dispatchEvent(changEv);
        }
    }

    function shoptimizerUpdateMiniCart() {
        shoptimizerDelayFunction(function() {
            const formData = new FormData();
            const loader = document.querySelector('#ajax-loading');
            
            try {
                miniItems = document.querySelectorAll('.shoptimizer-custom-quantity-mini-cart');
                miniItems.forEach(function(item) {
                    const input = item.querySelector('input');
                    if (!input) return;
                    
                    const qty = parseInt(input.value, 10) || 1;
                    const itemKey = input.getAttribute('data-cart_item_key');
                    if (itemKey) {
                        formData.append('data[' + itemKey + ']', qty);
                    }
                });
                
                if (loader) loader.style.display = 'block';
                
                // Add the nonce (will be used in future version)
                if (typeof shoptimizer_mini_cart !== 'undefined') {
                    formData.append('nonce', shoptimizer_mini_cart.nonce);
                }
                
                formData.append('action', 'cg_shoptimizer_update_mini_cart');
                fetch(
                    woocommerce_params.ajax_url,
                    {
                        method: 'POST',
                        body: formData
                    }
                ).then(
                    response => response.json()
                ).then(
                    function(json) {
                        var wcfragment = new Event('wc_fragment_refresh');
                        document.body.dispatchEvent(wcfragment);
                        var wccart = document.querySelectorAll('form.woocommerce-cart-form input.qty, form.woocommerce-checkout');
                        if (0 < wccart.length) {
                            window.location.reload();
                        }
                    }
                );
            } catch (error) {
                console.error('Cart update error:', error);
                if (loader) loader.style.display = 'none';
            }
        }, 600);
    }

    jQuery('body').on('wc_fragments_refreshed wc_fragments_loaded', shoptimizerInitMiniCartQty);
    shoptimizerInitMiniCartQty();
})(); 