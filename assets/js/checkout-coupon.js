/**
 * Checkout Coupon - Apply Coupon via AJAX
 * Terintegrasi dengan WooCommerce
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
        $.ajax({
            url: wc_checkout_params.ajax_url || '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'woocommerce_apply_coupon',
                security: wc_checkout_params.apply_coupon_nonce,
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