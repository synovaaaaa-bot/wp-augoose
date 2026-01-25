/**
 * Cart Sidebar Detailed - Quantity Selector & Remove
 * Terintegrasi dengan WooCommerce
 */

jQuery(document).ready(function($) {
    
    // Add quantity buttons to mini cart
    function initCartSidebarQuantity() {
        $('.cart-sidebar-quantity').each(function() {
            const $quantity = $(this);
            const $input = $quantity.find('input[type="number"]');
            
            if ($input.length && !$quantity.find('.qty-button').length) {
                const $minus = $('<button>').addClass('qty-button minus').text('−').attr('type', 'button');
                const $plus = $('<button>').addClass('qty-button plus').text('+').attr('type', 'button');
                
                // Vertical layout: minus di atas, input di tengah, plus di bawah
                $quantity.prepend($minus);
                $quantity.append($plus);
                
                $minus.on('click', function(e) {
                    e.preventDefault();
                    const currentVal = parseInt($input.val()) || 0;
                    const min = parseInt($input.attr('min')) || 0;
                    if (currentVal > min) {
                        $input.val(currentVal - 1).trigger('change');
                    }
                });
                
                $plus.on('click', function(e) {
                    e.preventDefault();
                    const currentVal = parseInt($input.val()) || 0;
                    const max = parseInt($input.attr('max')) || 9999;
                    if (currentVal < max) {
                        $input.val(currentVal + 1).trigger('change');
                    }
                });
            }
        });
    }
    
    // Initialize on load
    initCartSidebarQuantity();
    
    // Re-initialize after cart update
    $(document.body).on('updated_wc_div added_to_cart', function() {
        setTimeout(initCartSidebarQuantity, 100);
    });
    
    // Auto update cart on quantity change (optimized - no page reload)
    var cartUpdateTimeout;
    var isCartUpdating = false;
    
    $(document).on('change', '.cart-sidebar-quantity input[type="number"]', function() {
        const $input = $(this);
        const cartKey = $input.attr('name') ? $input.attr('name').replace('cart[', '').replace('][qty]', '') : '';
        const quantity = parseInt($input.val()) || 1;
        
        if (!cartKey || isCartUpdating) {
            return;
        }
        
        // Clear previous timeout
        clearTimeout(cartUpdateTimeout);
        
        // Show loading state
        $input.closest('.cart-sidebar-item').addClass('updating');
        
        // Debounce update (100ms for faster response)
        cartUpdateTimeout = setTimeout(function() {
            updateCartSidebarQuantity(cartKey, quantity);
        }, 100);
    });
    
    // Optimized AJAX cart update function
    function updateCartSidebarQuantity(cartKey, quantity) {
        if (isCartUpdating) {
            return;
        }
        
        isCartUpdating = true;
        
        $.ajax({
            url: wc_add_to_cart_params.ajax_url || '/wp-admin/admin-ajax.php',
            type: 'POST',
            timeout: 10000,
            data: {
                action: 'update_checkout_quantity',
                cart_key: cartKey,
                quantity: quantity,
                security: wc_add_to_cart_params.update_cart_nonce || ''
            },
            success: function(response) {
                if (response && response.success) {
                    // Update cart fragments (faster than page reload)
                    $(document.body).trigger('wc_fragment_refresh');
                    $(document.body).trigger('updated_wc_div');
                } else {
                    // Fallback to page reload on error
                    location.reload();
                }
            },
            error: function() {
                // Fallback to page reload on error
                location.reload();
            },
            complete: function() {
                isCartUpdating = false;
                $('.cart-sidebar-item').removeClass('updating');
            }
        });
    }
    
    // Remove item with confirmation
    $(document).on('click', '.cart-sidebar-remove', function(e) {
        e.preventDefault();
        const $link = $(this);
        const url = $link.attr('href');
        
        if (confirm('Remove this item from cart?')) {
            window.location.href = url;
        }
    });
    
});