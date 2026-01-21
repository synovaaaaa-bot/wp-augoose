/**
 * Product Image Swatcher
 * Change product image on color swatch click
 */

jQuery(document).ready(function($) {
    'use strict';

    // Handle color swatch click
    $(document).on('click', '.color-swatch', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $swatch = $(this);
        var $productItem = $swatch.closest('.product-item');
        var $productImage = $productItem.find('.product-image-link img, .product-thumbnail img, .woocommerce-loop-product__link img, .woocommerce-LoopProduct-link img').first();
        var $allSwatches = $productItem.find('.color-swatch');
        
        // Remove active state from all swatches
        $allSwatches.removeClass('active');
        
        // Add active state to clicked swatch
        $swatch.addClass('active');
        
        // Get image URL from data attribute
        var imageUrl = $swatch.data('image-url');
        
        if (imageUrl && $productImage.length) {
            // Fade out
            $productImage.css('opacity', '0.5');
            
            // Change image source
            setTimeout(function() {
                $productImage.attr('src', imageUrl);
                
                // Update srcset if exists
                var imageSrcset = $swatch.data('image-srcset');
                if (imageSrcset) {
                    $productImage.attr('srcset', imageSrcset);
                }
                
                // Fade in
                $productImage.css('opacity', '1');
            }, 150);
        }
    });
    
    // Set first swatch as active by default
    $('.product-item').each(function() {
        $(this).find('.color-swatch').first().addClass('active');
    });
});
