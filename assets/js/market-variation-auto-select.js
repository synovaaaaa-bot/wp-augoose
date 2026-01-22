/**
 * Market Variation Auto-Select
 * Auto-select Market variation (ID/MY/SG) based on user country
 * 
 * @package WP_Augoose
 */

(function($) {
    'use strict';
    
    /**
     * Auto-select Market variation
     */
    function autoSelectMarketVariation() {
        if ( typeof wpAugooseMarket === 'undefined' ) {
            return; // Data not available
        }
        
        const marketValue = wpAugooseMarket.marketValue;
        const selectName = wpAugooseMarket.selectName;
        
        // Find the Market variation select element
        // Try multiple selectors to be compatible with different themes
        let $marketSelect = null;
        
        // Priority 1: Try exact name match
        $marketSelect = $('select[name="' + selectName + '"]');
        
        // Priority 2: Try with pa_ prefix
        if ( $marketSelect.length === 0 && selectName.indexOf('pa_') === -1 ) {
            $marketSelect = $('select[name="attribute_pa_' + selectName.replace('attribute_', '') + '"]');
        }
        
        // Priority 3: Try without pa_ prefix
        if ( $marketSelect.length === 0 && selectName.indexOf('pa_') !== -1 ) {
            const attrName = selectName.replace('attribute_pa_', '').replace('attribute_', '');
            $marketSelect = $('select[name="attribute_' + attrName + '"]');
        }
        
        // Priority 4: Try to find by attribute containing "market" (case insensitive)
        if ( $marketSelect.length === 0 ) {
            $('select[name*="attribute"]').each(function() {
                const name = $(this).attr('name') || '';
                if ( name.toLowerCase().indexOf('market') !== -1 ) {
                    $marketSelect = $(this);
                    return false; // Break loop
                }
            });
        }
        
        // Priority 5: Try hidden select (for custom swatches)
        if ( $marketSelect.length === 0 ) {
            $('select.variation-select-hidden').each(function() {
                const name = $(this).attr('name') || '';
                if ( name.toLowerCase().indexOf('market') !== -1 ) {
                    $marketSelect = $(this);
                    return false; // Break loop
                }
            });
        }
        
        // Priority 6: Try by ID containing "market"
        if ( $marketSelect.length === 0 ) {
            $('select[id]').each(function() {
                const id = $(this).attr('id') || '';
                if ( id.toLowerCase().indexOf('market') !== -1 ) {
                    $marketSelect = $(this);
                    return false; // Break loop
                }
            });
        }
        
        if ( $marketSelect.length === 0 ) {
            console.log('Market variation select not found');
            return; // Select element not found
        }
        
        // EXTRA REQUIREMENT: Don't overwrite if user already manually selected
        const currentValue = $marketSelect.val();
        if ( currentValue && currentValue !== '' && currentValue !== '0' ) {
            console.log('User has already selected Market variation: ' + currentValue + ', skipping auto-select');
            return; // User already made a selection, don't overwrite
        }
        
        // Check if the market value exists in options
        let optionExists = $marketSelect.find('option[value="' + marketValue + '"]').length > 0;
        let selectedValue = marketValue;
        
        // FALLBACK LOGIC: If Market term doesn't exist, try ID
        if ( ! optionExists && marketValue !== 'ID' ) {
            console.log('Market value "' + marketValue + '" not found, trying fallback to ID');
            optionExists = $marketSelect.find('option[value="ID"]').length > 0;
            if ( optionExists ) {
                selectedValue = 'ID';
            }
        }
        
        // FALLBACK LOGIC: If ID also doesn't exist, select first available variation
        if ( ! optionExists ) {
            const $firstOption = $marketSelect.find('option[value!=""][value!="0"]').not(':first'); // Skip "Choose an option"
            if ( $firstOption.length > 0 ) {
                selectedValue = $firstOption.first().val();
                optionExists = true;
                console.log('Both Market value and ID not found, selecting first available: ' + selectedValue);
            }
        }
        
        if ( ! optionExists ) {
            console.log('No valid Market variation found');
            return; // No valid option available
        }
        
        // Set the value
        $marketSelect.val( selectedValue );
        
        // Trigger change event to update WooCommerce variation (both jQuery and vanilla JS)
        $marketSelect.trigger('change');
        
        // Also trigger vanilla JS change event for compatibility
        if ( $marketSelect.length > 0 ) {
            const selectElement = $marketSelect[0];
            if ( selectElement && typeof selectElement.dispatchEvent !== 'undefined' ) {
                selectElement.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
        
        // Also trigger update_variations event (WooCommerce standard)
        $(document.body).trigger('update_variations');
        
        // If using custom swatches, also update the swatch button
        let $swatchButton = null;
        $('.variation-swatch').each(function() {
            const attr = $(this).attr('data-attribute') || '';
            const val = $(this).attr('data-value') || '';
            if ( attr.toLowerCase().indexOf('market') !== -1 && val === selectedValue ) {
                $swatchButton = $(this);
                return false; // Break loop
            }
        });
        if ( $swatchButton && $swatchButton.length > 0 && ! $swatchButton.hasClass('is-active') ) {
            $swatchButton.trigger('click');
        }
        
        console.log('Auto-selected Market variation: ' + selectedValue);
    }
    
    /**
     * Wait for WooCommerce variation script to be ready
     */
    function initMarketVariationAutoSelect() {
        // EXTRA REQUIREMENT: Check if product is variable product
        const $variationsForm = $('form.variations_form');
        
        if ( $variationsForm.length === 0 ) {
            // Not a variable product or form not ready yet
            // Retry after a short delay (max 3 retries)
            if ( typeof initMarketVariationAutoSelect.retryCount === 'undefined' ) {
                initMarketVariationAutoSelect.retryCount = 0;
            }
            initMarketVariationAutoSelect.retryCount++;
            
            if ( initMarketVariationAutoSelect.retryCount < 3 ) {
                setTimeout(initMarketVariationAutoSelect, 500);
            } else {
                console.log('Variations form not found, product may not be variable');
            }
            return;
        }
        
        // Reset retry count
        initMarketVariationAutoSelect.retryCount = 0;
        
        // Wait for variation selects to be rendered
        const checkSelects = setInterval(function() {
            const $selects = $variationsForm.find('select[name*="attribute"]');
            if ( $selects.length > 0 ) {
                clearInterval(checkSelects);
                
                // Small delay to ensure WooCommerce has initialized
                setTimeout(function() {
                    autoSelectMarketVariation();
                }, 300);
            }
        }, 100);
        
        // Timeout after 5 seconds
        setTimeout(function() {
            clearInterval(checkSelects);
            autoSelectMarketVariation();
        }, 5000);
    }
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        // EXTRA REQUIREMENT: Only run on product pages and check if variable product exists
        if ( $('body').hasClass('single-product') || $('form.variations_form').length > 0 ) {
            // Small delay to ensure WooCommerce scripts are loaded
            setTimeout(function() {
                initMarketVariationAutoSelect();
            }, 100);
        }
    });
    
    // Also run after AJAX updates (if variations are loaded via AJAX)
    $(document.body).on('updated_wc_div', function() {
        setTimeout(initMarketVariationAutoSelect, 500);
    });
    
    // Run when variations form is found (for dynamic loading)
    $(document).on('found_variation', function() {
        // Variation found, ensure Market is still selected
        setTimeout(autoSelectMarketVariation, 100);
    });
    
})(jQuery);
