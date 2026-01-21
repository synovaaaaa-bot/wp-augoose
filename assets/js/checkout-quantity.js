/**
 * Checkout Quantity Selector & Item Removal
 * Allows users to update quantities and remove items from checkout
 */

jQuery(document).ready(function($) {
    'use strict';

    // Quantity increase
    $(document).on('click', '.qty-plus', function(e) {
        e.preventDefault();
        var $input = $(this).siblings('.qty-input');
        var currentVal = parseInt($input.val()) || 0;
        var max = parseInt($input.attr('max')) || 999;
        
        if (currentVal < max) {
            $input.val(currentVal + 1).trigger('change');
        }
    });

    // Quantity decrease
    $(document).on('click', '.qty-minus', function(e) {
        e.preventDefault();
        var $input = $(this).siblings('.qty-input');
        var currentVal = parseInt($input.val()) || 0;
        var min = parseInt($input.attr('min')) || 0;
        
        if (currentVal > min) {
            $input.val(currentVal - 1).trigger('change');
        }
    });

    // Quantity input change - update cart via AJAX
    var updateTimeout;
    $(document).on('change', '.qty-input', function() {
        var $input = $(this);
        var cartKey = $input.data('cart-key');
        var quantity = parseInt($input.val()) || 0;
        
        // Clear previous timeout
        clearTimeout(updateTimeout);
        
        // Show loading indicator
        $input.closest('.product-item-summary').addClass('updating');
        
        // Debounce update
        updateTimeout = setTimeout(function() {
            updateCartQuantity(cartKey, quantity);
        }, 500);
    });

    // Remove item
    $(document).on('click', '.remove-item-btn', function(e) {
        e.preventDefault();
        var $button = $(this);
        var cartKey = $button.data('cart-key');
        
        if (confirm('Remove this item from cart?')) {
            $button.closest('.cart_item').addClass('removing');
            updateCartQuantity(cartKey, 0);
        }
    });

    // AJAX update cart quantity
    function updateCartQuantity(cartKey, quantity) {
        $.ajax({
            type: 'POST',
            url: wc_checkout_params.ajax_url,
            data: {
                action: 'update_checkout_quantity',
                cart_key: cartKey,
                quantity: quantity,
                security: wc_checkout_params.update_cart_nonce
            },
            success: function(response) {
                if (response.success) {
                    // Reload checkout fragments
                    $('body').trigger('update_checkout');
                    
                    // Show success message (optional)
                    console.log('Cart updated successfully');
                } else {
                    // Show error
                    alert(response.data.message || 'Failed to update cart');
                    
                    // Reload page as fallback
                    location.reload();
                }
            },
            error: function() {
                // Reload page on error
                alert('Error updating cart. Page will reload.');
                location.reload();
            },
            complete: function() {
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
