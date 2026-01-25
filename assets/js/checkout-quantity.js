/**
 * Checkout Quantity Selector & Item Removal
 * Allows users to update quantities and remove items from checkout
 */

jQuery(document).ready(function($) {
    'use strict';

    // Quantity increase - Fixed selector
    $(document).on('click', '.qty-plus, .qty-btn.qty-plus', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $button = $(this);
        
        // Try multiple selectors to find input
        var $input = $button.siblings('.qty-input');
        if ($input.length === 0) {
            $input = $button.closest('.quantity-input-group').find('.qty-input');
        }
        if ($input.length === 0) {
            $input = $button.closest('td').find('input[type="number"]');
        }
        if ($input.length === 0) {
            $input = $button.closest('.cart_item').find('input[type="number"]');
        }
        
        if ($input.length === 0) {
            console.error('Quantity input not found for increase button');
            return;
        }
        
        var currentVal = parseInt($input.val()) || 0;
        var max = parseInt($input.attr('max')) || 999;
        
        // Get cart key from button or input
        var cartKey = $button.data('cart-key') || $input.data('cart-key');
        if (!cartKey && $input.attr('name')) {
            // Extract cart key from input name like "cart[abc123][qty]"
            var nameMatch = $input.attr('name').match(/cart\[([^\]]+)\]/);
            if (nameMatch && nameMatch[1]) {
                cartKey = nameMatch[1];
            }
        }
        
        if (!cartKey) {
            console.error('Cart key not found for increase button');
            return;
        }
        
        if (currentVal < max) {
            var newQuantity = currentVal + 1;
            $input.val(newQuantity);
            console.log('Increasing quantity:', cartKey, 'from', currentVal, 'to', newQuantity);
            updateCartQuantity(cartKey, newQuantity);
        }
    });

    // Quantity decrease - Fixed selector
    $(document).on('click', '.qty-minus, .qty-btn.qty-minus', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $button = $(this);
        
        // Try multiple selectors to find input
        var $input = $button.siblings('.qty-input');
        if ($input.length === 0) {
            $input = $button.closest('.quantity-input-group').find('.qty-input');
        }
        if ($input.length === 0) {
            $input = $button.closest('td').find('input[type="number"]');
        }
        if ($input.length === 0) {
            $input = $button.closest('.cart_item').find('input[type="number"]');
        }
        
        if ($input.length === 0) {
            console.error('Quantity input not found for decrease button');
            return;
        }
        
        var currentVal = parseInt($input.val()) || 0;
        var min = parseInt($input.attr('min')) || 1;
        
        // Get cart key from button or input
        var cartKey = $button.data('cart-key') || $input.data('cart-key');
        if (!cartKey && $input.attr('name')) {
            // Extract cart key from input name like "cart[abc123][qty]"
            var nameMatch = $input.attr('name').match(/cart\[([^\]]+)\]/);
            if (nameMatch && nameMatch[1]) {
                cartKey = nameMatch[1];
            }
        }
        
        if (!cartKey) {
            console.error('Cart key not found for decrease button');
            return;
        }
        
        if (currentVal > min) {
            var newQuantity = currentVal - 1;
            $input.val(newQuantity);
            console.log('Decreasing quantity:', cartKey, 'from', currentVal, 'to', newQuantity);
            updateCartQuantity(cartKey, newQuantity);
        } else if (currentVal === min && min === 1) {
            // If quantity is 1 and user clicks minus, remove item
            if (confirm('Remove this item from cart?')) {
                console.log('Removing item:', cartKey);
                updateCartQuantity(cartKey, 0);
            }
        }
    });

    // Quantity input change - update cart via AJAX (optimized for speed)
    var updateTimeout;
    var isUpdating = false;
    $(document).on('change blur', '.qty-input', function() {
        var $input = $(this);
        
        // Get cart key from multiple sources
        var cartKey = $input.data('cart-key');
        if (!cartKey && $input.attr('name')) {
            // Extract cart key from input name like "cart[abc123][qty]"
            var nameMatch = $input.attr('name').match(/cart\[([^\]]+)\]/);
            if (nameMatch && nameMatch[1]) {
                cartKey = nameMatch[1];
            }
        }
        
        if (!cartKey) {
            console.error('Cart key not found for input change');
            return;
        }
        
        var quantity = parseInt($input.val()) || 1;
        
        // Ensure minimum quantity
        if (quantity < 1) {
            quantity = 1;
            $input.val(1);
        }
        
        // Prevent multiple simultaneous updates
        if (isUpdating) {
            console.log('Update already in progress, skipping...');
            return;
        }
        
        // Clear previous timeout
        clearTimeout(updateTimeout);
        
        // Show loading indicator
        $input.closest('.product-item-summary, .cart_item, tr').addClass('updating');
        
        // Faster debounce (150ms instead of 500ms)
        updateTimeout = setTimeout(function() {
            console.log('Input change - updating quantity:', cartKey, 'to', quantity);
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
                    console.log('Cart quantity updated successfully:', response);
                    
                    // Force checkout update - use multiple methods to ensure it works
                    $('body').trigger('update_checkout');
                    $(document.body).trigger('update_checkout');
                    
                    // Also trigger cart fragments update for mini cart
                    $(document.body).trigger('wc_fragment_refresh');
                    $(document.body).trigger('added_to_cart');
                    
                    // Force a small delay then trigger again to ensure update
                    setTimeout(function() {
                        $('body').trigger('update_checkout');
                    }, 100);
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
