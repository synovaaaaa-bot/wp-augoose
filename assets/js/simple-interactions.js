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

// Auto-update cart on quantity change
jQuery(document).ready(function($) {
    $('.quantity-simple input[type="number"]').on('change', function() {
        const $form = $(this).closest('form.woocommerce-cart-form');
        if ($form.length) {
            // Auto submit form to update cart
            setTimeout(function() {
                $form.submit();
            }, 300);
        }
    });
    
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