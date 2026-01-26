/**
 * Checkout Coupon - Apply Coupon via AJAX
 * Integrated with WooCommerce
 */

jQuery(document).ready(function($) {
    
    // Apply coupon button
    $('.apply-coupon-btn').on('click', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $form = $button.closest('form.checkout');
        const couponCode = $form.find('#coupon_code').val();
        
        if (!couponCode) {
            alert('Please enter a coupon code');
            return;
        }
        
        // Disable button
        $button.prop('disabled', true).text('Applying...');
        
        // Apply coupon via AJAX
        // CRITICAL: Use WooCommerce core wc_checkout_params (don't override it)
        // Fallback to our custom object if needed
        const ajaxUrl = (typeof wc_checkout_params !== 'undefined' && wc_checkout_params.ajax_url) 
            ? wc_checkout_params.ajax_url 
            : (typeof wpAugooseCheckoutCoupon !== 'undefined' && wpAugooseCheckoutCoupon.ajaxUrl)
            ? wpAugooseCheckoutCoupon.ajaxUrl
            : '/wp-admin/admin-ajax.php';
        const nonce = (typeof wc_checkout_params !== 'undefined' && wc_checkout_params.apply_coupon_nonce)
            ? wc_checkout_params.apply_coupon_nonce
            : (typeof wpAugooseCheckoutCoupon !== 'undefined' && wpAugooseCheckoutCoupon.nonce)
            ? wpAugooseCheckoutCoupon.nonce
            : '';
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'woocommerce_apply_coupon',
                security: nonce,
                coupon_code: couponCode
            },
            success: function(response) {
                if (response.success) {
                    // Trigger update checkout to refresh totals
                    $('body').trigger('update_checkout');
                    $form.find('#coupon_code').val('');
                } else {
                    alert(response.data.message || 'Invalid coupon code');
                }
                $button.prop('disabled', false).text('Apply');
            },
            error: function() {
                alert('Error applying coupon. Please try again.');
                $button.prop('disabled', false).text('Apply');
            }
        });
    });
    
    // Enter key to apply coupon
    $('#coupon_code').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $(this).closest('.checkout-coupon').find('.apply-coupon-btn').trigger('click');
        }
    });
    
});