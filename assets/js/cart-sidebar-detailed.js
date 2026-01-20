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
    
    // Auto update cart on quantity change
    $(document).on('change', '.cart-sidebar-quantity input[type="number"]', function() {
        const $input = $(this);
        const $form = $('<form>').attr('method', 'post').attr('action', wc_add_to_cart_params.cart_url || '/cart/');
        
        // Get all cart items
        $('.cart-sidebar-quantity input[type="number"]').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();
            $form.append($('<input>').attr('type', 'hidden').attr('name', name).val(value));
        });
        
        // Add nonce
        $form.append($('<input>').attr('type', 'hidden').attr('name', 'woocommerce-cart-nonce').val(wc_add_to_cart_params.update_cart_nonce));
        $form.append($('<input>').attr('type', 'hidden').attr('name', 'update_cart').val('Update cart'));
        
        // Submit form
        $.ajax({
            url: wc_add_to_cart_params.cart_url || '/cart/',
            type: 'POST',
            data: $form.serialize(),
            success: function() {
                // Reload page to update cart
                location.reload();
            }
        });
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