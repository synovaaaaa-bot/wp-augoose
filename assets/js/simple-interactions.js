/**
 * Simple Product Interactions
 * Basic functionality without complex features
 */

jQuery(document).ready(function($) {
    
    // Color Swatch Selection
    $('.color-swatch').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $swatch = $(this);
        const $container = $swatch.closest('.color-swatches');
        
        // Remove active from others, add to clicked
        $container.find('.color-swatch').removeClass('active');
        $swatch.addClass('active');
    });
    
});

// Simple CSS addition for active state
const style = document.createElement('style');
style.textContent = `
    .color-swatch.active {
        transform: scale(1.15);
        box-shadow: 0 0 0 2px #1a1a1a !important;
    }
`;
document.head.appendChild(style);

// Auto-update cart on quantity change (optimized - AJAX instead of form submit)
jQuery(document).ready(function($) {
    var cartUpdateTimeout;
    var isCartUpdating = false;
    
    $('.quantity-simple input[type="number"]').on('change', function() {
        const $input = $(this);
        const $form = $input.closest('form.woocommerce-cart-form');
        
        if (!$form.length) {
            return;
        }
        
        // Get cart item key from input name
        const inputName = $input.attr('name');
        const cartKey = inputName ? inputName.replace('cart[', '').replace('][qty]', '') : '';
        const quantity = parseInt($input.val()) || 1;
        
        if (!cartKey || isCartUpdating) {
            return;
        }
        
        // Clear previous timeout
        clearTimeout(cartUpdateTimeout);
        
        // Show loading state
        $input.closest('tr, .cart_item').addClass('updating');
        
        // Faster debounce (100ms)
        cartUpdateTimeout = setTimeout(function() {
            updateCartQuantityAjax(cartKey, quantity);
        }, 100);
    });
    
    // Optimized AJAX cart update
    function updateCartQuantityAjax(cartKey, quantity) {
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
                    // Update cart fragments (faster than form submit)
                    $(document.body).trigger('wc_fragment_refresh');
                    $(document.body).trigger('updated_wc_div');
                    
                    // Update cart totals via fragments
                    if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.cart_url) {
                        $.post(wc_add_to_cart_params.cart_url, {
                            'update_cart': 'Update cart',
                            'woocommerce-cart-nonce': wc_add_to_cart_params.update_cart_nonce
                        }, function() {
                            $(document.body).trigger('wc_fragment_refresh');
                        });
                    }
                } else {
                    // Fallback to form submit on error
                    $('.quantity-simple input[type="number"]').closest('form.woocommerce-cart-form').submit();
                }
            },
            error: function() {
                // Fallback to form submit on error
                $('.quantity-simple input[type="number"]').closest('form.woocommerce-cart-form').submit();
            },
            complete: function() {
                isCartUpdating = false;
                $('.updating').removeClass('updating');
            }
        });
    }
    
    // Quantity buttons
    $('.quantity-simple').each(function() {
        const $quantity = $(this);
        const $input = $quantity.find('input[type="number"]');
        
        if ($input.length && !$quantity.find('.qty-button').length) {
            const $minus = $('<button>').addClass('qty-button minus').text('−').attr('type', 'button');
            const $plus = $('<button>').addClass('qty-button plus').text('+').attr('type', 'button');
            
            $input.before($minus);
            $input.after($plus);
            
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
});