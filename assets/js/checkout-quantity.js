/**
 * Checkout Quantity Selector & Item Removal
 * Allows users to update quantities and remove items from checkout
 */

jQuery(document).ready(function($) {
    'use strict';

    // Quantity increase - Fixed selector
    $(document).on('click', '.qty-plus', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $button = $(this);
        var $input = $button.siblings('.qty-input');
        if ($input.length === 0) {
            $input = $button.closest('.quantity-input-group').find('.qty-input');
        }
        var currentVal = parseInt($input.val()) || 0;
        var max = parseInt($input.attr('max')) || 999;
        
        if (currentVal < max) {
            $input.val(currentVal + 1).trigger('change');
            updateCartQuantity($input.data('cart-key'), currentVal + 1);
        }
    });

    // Quantity decrease - Fixed selector
    $(document).on('click', '.qty-minus', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $button = $(this);
        var $input = $button.siblings('.qty-input');
        if ($input.length === 0) {
            $input = $button.closest('.quantity-input-group').find('.qty-input');
        }
        var currentVal = parseInt($input.val()) || 0;
        var min = parseInt($input.attr('min')) || 1;
        
        if (currentVal > min) {
            $input.val(currentVal - 1).trigger('change');
            updateCartQuantity($input.data('cart-key'), currentVal - 1);
        }
    });

    // Quantity input change - update cart via AJAX (optimized for speed)
    var updateTimeout;
    var isUpdating = false;
    $(document).on('change blur', '.qty-input', function() {
        var $input = $(this);
        var cartKey = $input.data('cart-key');
        var quantity = parseInt($input.val()) || 1;
        
        // Ensure minimum quantity
        if (quantity < 1) {
            quantity = 1;
            $input.val(1);
        }
        
        // Prevent multiple simultaneous updates
        if (isUpdating) {
            return;
        }
        
        // Clear previous timeout
        clearTimeout(updateTimeout);
        
        // Show loading indicator
        $input.closest('.product-item-summary').addClass('updating');
        
        // Faster debounce (150ms instead of 500ms)
        updateTimeout = setTimeout(function() {
            updateCartQuantity(cartKey, quantity);
        }, 150);
    });

    // Remove item
    $(document).on('click', '.remove-item-btn', function(e) {
        e.preventDefault();
        var $button = $(this);
        var cartKey = $button.data('cart-key');
        
        if (confirm('Are you sure you want to remove this item from cart?')) {
            $button.closest('.cart_item').addClass('removing');
            updateCartQuantity(cartKey, 0);
        }
    });

    // AJAX update cart quantity (optimized for speed and accuracy)
    function updateCartQuantity(cartKey, quantity) {
        if (isUpdating) {
            return;
        }
        
        isUpdating = true;
        
        $.ajax({
            type: 'POST',
            url: wc_checkout_params.ajax_url,
            timeout: 10000, // 10 second timeout
            data: {
                action: 'update_checkout_quantity',
                cart_key: cartKey,
                quantity: quantity,
                security: wc_checkout_params.update_cart_nonce
            },
            success: function(response) {
                // Check if response is valid JSON
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.error('Invalid JSON response:', response);
                        isUpdating = false;
                        alert('Error updating cart. Page will reload.');
                        location.reload();
                        return;
                    }
                }
                
                if (response && response.success) {
                    // Trigger WooCommerce checkout update (faster than full reload)
                    $('body').trigger('update_checkout');
                    
                    // Also trigger cart fragments update for mini cart
                    $(document.body).trigger('wc_fragment_refresh');
                } else {
                    // Show error
                    const errorMsg = response && response.data && response.data.message 
                        ? response.data.message 
                        : 'Failed to update cart';
                    console.error('Cart update error:', errorMsg);
                    
                    // Reload page as fallback
                    location.reload();
                }
            },
            error: function(xhr, status, error) {
                console.error('Cart update AJAX error:', status, error);
                
                // Check if response is HTML (error page)
                if (xhr.responseText && xhr.responseText.trim().startsWith('<')) {
                    console.error('Server returned HTML instead of JSON. This may indicate a PHP error.');
                    alert('Error updating cart. Please refresh the page.');
                    location.reload();
                    return;
                }
                
                // Reload page on error
                alert('Error updating cart. Page will reload.');
                location.reload();
            },
            complete: function() {
                isUpdating = false;
                $('.updating, .removing').removeClass('updating removing');
            }
        });
    }

    // Add loading state styles
    var style = document.createElement('style');
    style.textContent = `
        .product-item-summary.updating {
            opacity: 0.6;
            pointer-events: none;
        }
        .cart_item.removing {
            opacity: 0.3;
            pointer-events: none;
        }
    `;
    document.head.appendChild(style);
});
