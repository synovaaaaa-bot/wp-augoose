/**
 * Product Gallery Navigation - Auto Slide dengan Arrow
 * Icon kiri kanan untuk geser otomatis
 */

jQuery(document).ready(function($) {
    
    function initProductGalleryNav() {
        const $gallery = $('.woocommerce-product-gallery');
        if (!$gallery.length) return;
        
        const $wrapper = $gallery.find('.woocommerce-product-gallery__wrapper');
        const $images = $wrapper.find('.woocommerce-product-gallery__image');
        const $thumbs = $gallery.find('.woocommerce-product-gallery__thumbs');
        const $thumbImages = $thumbs.find('.woocommerce-product-gallery__image');
        
        if ($images.length <= 1) return;
        
        let currentIndex = 0;
        let isAutoSliding = false;
        let slideInterval;
        
        // Create navigation arrows if not exists
        if (!$gallery.find('.woocommerce-product-gallery__nav').length) {
            const $prevNav = $('<button>')
                .addClass('woocommerce-product-gallery__nav woocommerce-product-gallery__nav--prev')
                .attr('type', 'button')
                .attr('aria-label', 'Previous image')
                .html('<svg viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6"/></svg>');
            
            const $nextNav = $('<button>')
                .addClass('woocommerce-product-gallery__nav woocommerce-product-gallery__nav--next')
                .attr('type', 'button')
                .attr('aria-label', 'Next image')
                .html('<svg viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6"/></svg>');
            
            $wrapper.append($prevNav);
            $wrapper.append($nextNav);
        }
        
        const $prevNav = $gallery.find('.woocommerce-product-gallery__nav--prev');
        const $nextNav = $gallery.find('.woocommerce-product-gallery__nav--next');
        
        function showImage(index) {
            if (index < 0) index = $images.length - 1;
            if (index >= $images.length) index = 0;
            
            currentIndex = index;
            
            // Hide all images
            $images.removeClass('active').eq(index).addClass('active');
            
            // Update thumbnails
            $thumbImages.removeClass('active').eq(index).addClass('active');
            
            // Scroll thumbnail into view
            const $activeThumb = $thumbImages.eq(index);
            if ($activeThumb.length) {
                const thumbOffset = $activeThumb.position().left + $thumbs.scrollLeft();
                const thumbWidth = $activeThumb.outerWidth();
                const thumbsWidth = $thumbs.width();
                
                if (thumbOffset < $thumbs.scrollLeft()) {
                    $thumbs.animate({ scrollLeft: thumbOffset - 12 }, 300);
                } else if (thumbOffset + thumbWidth > $thumbs.scrollLeft() + thumbsWidth) {
                    $thumbs.animate({ scrollLeft: thumbOffset + thumbWidth - thumbsWidth + 12 }, 300);
                }
            }
        }
        
        // Show first image
        showImage(0);
        
        // Next button
        $nextNav.on('click', function(e) {
            e.preventDefault();
            showImage(currentIndex + 1);
            resetAutoSlide();
        });
        
        // Prev button
        $prevNav.on('click', function(e) {
            e.preventDefault();
            showImage(currentIndex - 1);
            resetAutoSlide();
        });
        
        // Thumbnail click
        $thumbImages.on('click', function(e) {
            e.preventDefault();
            const index = $thumbImages.index($(this));
            showImage(index);
            resetAutoSlide();
        });
        
        // Auto slide function
        function startAutoSlide() {
            if (isAutoSliding) return;
            isAutoSliding = true;
            
            slideInterval = setInterval(function() {
                showImage(currentIndex + 1);
            }, 3000); // Change every 3 seconds
        }
        
        function stopAutoSlide() {
            isAutoSliding = false;
            if (slideInterval) {
                clearInterval(slideInterval);
            }
        }
        
        function resetAutoSlide() {
            stopAutoSlide();
            setTimeout(startAutoSlide, 2000); // Restart after 2 seconds
        }
        
        // Start auto slide on page load
        setTimeout(startAutoSlide, 2000);
        
        // Pause on hover
        $gallery.on('mouseenter', stopAutoSlide);
        $gallery.on('mouseleave', function() {
            setTimeout(startAutoSlide, 1000);
        });
    }
    
    // Initialize
    initProductGalleryNav();
    
    // Re-initialize on AJAX update
    $(document.body).on('updated_wc_div', function() {
        setTimeout(initProductGalleryNav, 100);
    });
    
});