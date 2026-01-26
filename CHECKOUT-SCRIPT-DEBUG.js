/**
 * Checkout Script Debug Helper
 * 
 * Add this to checkout page to debug why wc-ajax=update_order_review is not being called
 * 
 * Usage: Add this script to functions.php or enqueue it on checkout page
 */

jQuery(document).ready(function($) {
    'use strict';
    
    console.log('=== CHECKOUT SCRIPT DEBUG ===');
    
    // Check 1: Is jQuery loaded?
    if (typeof jQuery === 'undefined') {
        console.error('❌ jQuery is NOT loaded!');
    } else {
        console.log('✅ jQuery is loaded:', jQuery.fn.jquery);
    }
    
    // Check 2: Is wc_checkout_params defined?
    if (typeof wc_checkout_params === 'undefined') {
        console.error('❌ wc_checkout_params is NOT defined!');
        console.error('This means WooCommerce checkout script is NOT loaded!');
    } else {
        console.log('✅ wc_checkout_params is defined:', wc_checkout_params);
    }
    
    // Check 3: Is wc_checkout_form defined?
    if (typeof wc_checkout_form === 'undefined') {
        console.error('❌ wc_checkout_form is NOT defined!');
        console.error('This means WooCommerce checkout.min.js is NOT loaded!');
    } else {
        console.log('✅ wc_checkout_form is defined');
    }
    
    // Check 4: Is checkout form present?
    var $checkoutForm = $('form.checkout');
    if ($checkoutForm.length === 0) {
        console.error('❌ Checkout form not found!');
    } else {
        console.log('✅ Checkout form found:', $checkoutForm.length);
    }
    
    // Check 5: Are checkout fields present?
    var $checkoutFields = $checkoutForm.find('input, select, textarea');
    console.log('✅ Checkout fields found:', $checkoutFields.length);
    
    // Check 6: Is BlockUI loaded?
    if (typeof $.blockUI === 'undefined') {
        console.error('❌ BlockUI is NOT loaded!');
    } else {
        console.log('✅ BlockUI is loaded');
    }
    
    // Check 7: Listen for update_checkout event
    $(document.body).on('update_checkout', function() {
        console.log('✅ update_checkout event triggered');
    });
    
    // Check 8: Listen for updated_checkout event
    $(document.body).on('updated_checkout', function() {
        console.log('✅ updated_checkout event triggered');
    });
    
    // Check 9: Monitor AJAX requests
    var originalAjax = $.ajax;
    $.ajax = function(options) {
        if (options.url && (options.url.indexOf('wc-ajax') !== -1 || options.url.indexOf('update_order_review') !== -1)) {
            console.log('✅ AJAX request detected:', options.url);
            console.log('   Data:', options.data);
        }
        return originalAjax.apply(this, arguments);
    };
    
    // Check 10: Monitor form changes
    $checkoutForm.on('change input', 'input, select, textarea', function() {
        console.log('✅ Checkout field changed:', $(this).attr('name') || $(this).attr('id'));
    });
    
    // Check 11: Check if BlockUI is stuck
    setTimeout(function() {
        var $blocked = $('.woocommerce-checkout-payment.blocked, .woocommerce-checkout-review-order-table.blocked');
        if ($blocked.length > 0) {
            console.warn('⚠️ BlockUI is stuck! Found blocked elements:', $blocked.length);
            console.warn('   Attempting to unblock...');
            $blocked.unblock();
        } else {
            console.log('✅ No stuck BlockUI detected');
        }
    }, 5000);
    
    console.log('=== END CHECKOUT SCRIPT DEBUG ===');
});
