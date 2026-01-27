/**
 * Currency Conversion Loading Indicator
 * Shows loading spinner while prices are being converted to IDR
 * 
 * @package WP_Augoose
 */

(function() {
    'use strict';

    // Show loading indicator when cart is updating
    function showConversionLoading() {
        const loadingEl = document.querySelector('.cart-currency-conversion-loading');
        if (loadingEl) {
            loadingEl.style.display = 'flex';
        }
    }

    // Hide loading indicator when conversion is complete
    function hideConversionLoading() {
        const loadingEl = document.querySelector('.cart-currency-conversion-loading');
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
    }

    // Check if cart has items that need conversion
    function hasItemsNeedingConversion() {
        // Check if there are any cart items with SGD/MYR currency
        // This is a simple check - actual conversion happens on server
        return document.querySelector('.cart-item-simple, .woocommerce-cart-form') !== null;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Hide loading by default (will show if needed)
        hideConversionLoading();

        // Show loading when cart is being updated
        if (typeof jQuery !== 'undefined' && jQuery.fn.on) {
            // WooCommerce cart update events
            jQuery(document.body).on('wc_fragment_refresh wc_cart_emptied update_checkout', function() {
                if (hasItemsNeedingConversion()) {
                    showConversionLoading();
                }
            });

            // Hide loading after cart fragments are updated
            jQuery(document.body).on('updated_cart_totals updated_wc_div', function() {
                // Small delay to ensure conversion is complete
                setTimeout(function() {
                    hideConversionLoading();
                }, 500);
            });

            // Show loading when quantity is changed
            jQuery(document).on('change', '.qty-input, input[name^="cart["]', function() {
                if (hasItemsNeedingConversion()) {
                    showConversionLoading();
                }
            });
        }
    });

    // Also handle vanilla JS events for cart updates
    document.addEventListener('change', function(e) {
        if (e.target.matches('.qty-input, input[name^="cart["]')) {
            if (hasItemsNeedingConversion()) {
                showConversionLoading();
            }
        }
    });

})();
