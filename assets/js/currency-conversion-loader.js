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

    // Log exchange rates to console for validation
    function logExchangeRates() {
        if ( typeof wpAugooseCurrencyRates !== 'undefined' && wpAugooseCurrencyRates.exchange_rates ) {
            const rates = wpAugooseCurrencyRates.exchange_rates;
            const baseCurrency = wpAugooseCurrencyRates.base_currency || 'IDR';
            
            console.group('💱 WP Augoose - Currency Exchange Rates (from WCML)');
            console.log('Base Currency:', baseCurrency);
            console.log('Exchange Rates (relative to base):');
            
            // Log all rates
            for (const [currency, rate] of Object.entries(rates)) {
                if (currency === baseCurrency) {
                    console.log(`  ${currency}: ${rate} (default)`);
                } else {
                    // Calculate conversion rate to IDR
                    const idrRate = rates['IDR'] || 1;
                    const currencyRate = rate || 1;
                    const conversionRate = idrRate / currencyRate;
                    console.log(`  ${currency}: ${rate} → 1 ${currency} = ${conversionRate.toFixed(2)} IDR`);
                }
            }
            
            // Log conversion examples for SGD and MYR
            if (rates['SGD'] && rates['IDR']) {
                const sgdToIdr = rates['IDR'] / rates['SGD'];
                console.log('\n📊 Conversion Examples:');
                console.log(`  1 SGD = ${sgdToIdr.toFixed(2)} IDR`);
                console.log(`  74 SGD = ${(74 * sgdToIdr).toFixed(2)} IDR`);
            }
            
            if (rates['MYR'] && rates['IDR']) {
                const myrToIdr = rates['IDR'] / rates['MYR'];
                console.log(`  1 MYR = ${myrToIdr.toFixed(2)} IDR`);
                console.log(`  80 MYR = ${(80 * myrToIdr).toFixed(2)} IDR`);
            }
            
            console.log('\n💡 These rates are used for currency conversion in cart/checkout');
            console.log('💡 Rates are updated from WCML settings (can be updated per hour)');
            console.groupEnd();
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Hide loading by default (will show if needed)
        hideConversionLoading();
        
        // Log exchange rates for validation
        logExchangeRates();

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
                    // Log rates again after cart update to show current rates
                    logExchangeRates();
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
