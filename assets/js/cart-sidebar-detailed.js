/**
 * Cart Sidebar Detailed - Quantity Selector & Remove
 * Terintegrasi dengan WooCommerce
 */

jQuery(document).ready(function($) {
    
    // Auto update cart on quantity change (optimized - no page reload)
    var cartUpdateTimeout;
    var isCartUpdating = false;
    
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
            dataType: 'json',
            data: {
                action: 'update_checkout_quantity',
                cart_key: cartKey,
                quantity: quantity,
                security: (typeof wpAugooseCheckoutQty !== 'undefined' && wpAugooseCheckoutQty.nonce) 
                    ? wpAugooseCheckoutQty.nonce 
                    : (wc_add_to_cart_params.update_cart_nonce || '')
            },
            success: function(response) {
                // Handle both response formats
                const isSuccess = (response && response.success === true) || 
                                (response && response.result === 'success');
                
                if (isSuccess) {
                    // Get fragments from either format
                    let fragments = null;
                    if (response.data && response.data.fragments) {
                        fragments = response.data.fragments;
                    } else if (response.fragments) {
                        fragments = response.fragments;
                    }
                    
                    // Update fragments if available
                    if (fragments && typeof fragments === 'object') {
                        $.each(fragments, function(key, value) {
                            if (key && value) {
                                const $target = $(key);
                                if ($target.length) {
                                    $target.replaceWith(value);
                                }
                            }
                        });
                    }
                    
                    // Update cart fragments (faster than page reload)
                    $(document.body).trigger('wc_fragment_refresh');
                    $(document.body).trigger('updated_wc_div');
                    
                    // Re-initialize quantity buttons after update
                    setTimeout(function() {
                        initCartSidebarQuantity();
                    }, 100);
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
    
    // Add quantity buttons to mini cart
    function initCartSidebarQuantity() {
        $('.cart-sidebar-quantity').each(function() {
            const $quantity = $(this);
            const $input = $quantity.find('input[type="number"]');
            
            // Remove existing buttons to prevent duplicates
            $quantity.find('.qty-button').remove();
            
            if ($input.length) {
                const $minus = $('<button>').addClass('qty-button minus').text('−').attr('type', 'button');
                const $plus = $('<button>').addClass('qty-button plus').text('+').attr('type', 'button');
                
                // Horizontal layout: minus di kiri, input di tengah, plus di kanan
                $quantity.prepend($minus);
                $quantity.append($plus);
                
                $minus.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    const currentVal = parseInt($input.val()) || 0;
                    const min = parseInt($input.attr('min')) || 0;
                    
                    if (currentVal > min) {
                        const newVal = currentVal - 1;
                        $input.val(newVal);
                        
                        // Update cart directly without triggering change event to avoid double update
                        const cartKey = $input.attr('name') ? $input.attr('name').replace('cart[', '').replace('][qty]', '') : '';
                        if (cartKey) {
                            updateCartSidebarQuantity(cartKey, newVal);
                        }
                    }
                });
                
                $plus.on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    const currentVal = parseInt($input.val()) || 0;
                    const max = parseInt($input.attr('max')) || 9999;
                    
                    if (currentVal < max) {
                        const newVal = currentVal + 1;
                        $input.val(newVal);
                        
                        // Update cart directly without triggering change event to avoid double update
                        const cartKey = $input.attr('name') ? $input.attr('name').replace('cart[', '').replace('][qty]', '') : '';
                        if (cartKey) {
                            updateCartSidebarQuantity(cartKey, newVal);
                        }
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