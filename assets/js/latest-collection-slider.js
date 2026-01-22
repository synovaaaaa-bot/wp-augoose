/**
 * Latest Collection Auto-Slider
 * Auto-play carousel dengan scroll-snap
 * Hanya berlaku untuk Latest Collection di homepage
 */

(function($) {
    'use strict';
    
    /**
     * Initialize Latest Collection slider
     * DISABLED on mobile (≤768px) - use grid layout instead
     */
    function initLatestCollectionSlider() {
        // DISABLE slider on mobile
        if ( $(window).width() <= 768 ) {
            return; // Mobile: use grid layout, no slider
        }
        
        const $latestCollection = $('.latest-collection .latest-collection-products');
        
        if ( $latestCollection.length === 0 ) {
            return; // Not Latest Collection section
        }
        
        const $productsList = $latestCollection.find('.woocommerce ul.products, ul.products');
        
        if ( $productsList.length === 0 ) {
            return; // Products list not found
        }
        
        const $products = $productsList.find('li.product-item.lc-card, li.lc-card');
        
        if ( $products.length <= 6 ) {
            return; // Not enough products to slide
        }
        
        let currentIndex = 0;
        let isPaused = false;
        let autoplayInterval = null;
        const autoplayDelay = 3500; // 3500ms
        const transitionDuration = 500; // 500ms (450-600ms range)
        
        /**
         * Scroll to next item
         */
        function scrollToNext() {
            if ( isPaused ) {
                return;
            }
            
            const totalProducts = $products.length;
            const visibleProducts = getVisibleProductsCount();
            
            // Calculate next scroll position
            currentIndex = ( currentIndex + 1 ) % totalProducts;
            
            // If we've scrolled past the last visible item, loop back to start
            if ( currentIndex + visibleProducts > totalProducts ) {
                currentIndex = 0;
            }
            
            const $currentProduct = $products.eq( currentIndex );
            if ( $currentProduct.length > 0 ) {
                const scrollLeft = $currentProduct[0].offsetLeft - $productsList[0].offsetLeft;
                
                $productsList.animate(
                    { scrollLeft: scrollLeft },
                    transitionDuration,
                    'swing',
                    function() {
                        // After animation, check if we need to loop
                        if ( currentIndex + visibleProducts >= totalProducts ) {
                            // Smoothly reset to start for infinite loop
                            setTimeout(function() {
                                $productsList.scrollLeft( 0 );
                                currentIndex = 0;
                            }, 100);
                        }
                    }
                );
            }
        }
        
        /**
         * Get number of visible products based on screen size
         */
        function getVisibleProductsCount() {
            const width = $(window).width();
            if ( width <= 480 ) {
                return 1;
            } else if ( width <= 768 ) {
                return 2;
            } else if ( width <= 992 ) {
                return 3;
            } else if ( width <= 1200 ) {
                return 4;
            } else if ( width <= 1400 ) {
                return 5;
            } else {
                return 6;
            }
        }
        
        /**
         * Start autoplay
         */
        function startAutoplay() {
            if ( autoplayInterval ) {
                clearInterval( autoplayInterval );
            }
            
            autoplayInterval = setInterval(function() {
                scrollToNext();
            }, autoplayDelay);
        }
        
        /**
         * Stop autoplay
         */
        function stopAutoplay() {
            if ( autoplayInterval ) {
                clearInterval( autoplayInterval );
                autoplayInterval = null;
            }
        }
        
        /**
         * Pause on hover (desktop only)
         */
        $latestCollection.on('mouseenter', function() {
            if ( $(window).width() > 768 ) {
                isPaused = true;
                stopAutoplay();
            }
        });
        
        $latestCollection.on('mouseleave', function() {
            if ( $(window).width() > 768 ) {
                isPaused = false;
                startAutoplay();
            }
        });
        
        /**
         * Handle touch/swipe on mobile
         */
        let touchStartX = 0;
        let touchEndX = 0;
        
        $productsList.on('touchstart', function(e) {
            touchStartX = e.originalEvent.touches[0].clientX;
            isPaused = true;
            stopAutoplay();
        });
        
        $productsList.on('touchend', function(e) {
            touchEndX = e.originalEvent.changedTouches[0].clientX;
            handleSwipe();
            // Resume autoplay after swipe
            setTimeout(function() {
                isPaused = false;
                startAutoplay();
            }, autoplayDelay);
        });
        
        /**
         * Handle swipe gesture
         */
        function handleSwipe() {
            const swipeThreshold = 50; // Minimum swipe distance
            const diff = touchStartX - touchEndX;
            
            if ( Math.abs( diff ) > swipeThreshold ) {
                if ( diff > 0 ) {
                    // Swipe left: next
                    scrollToNext();
                } else {
                    // Swipe right: previous
                    currentIndex = ( currentIndex - 1 + $products.length ) % $products.length;
                    const $currentProduct = $products.eq( currentIndex );
                    if ( $currentProduct.length > 0 ) {
                        const scrollLeft = $currentProduct[0].offsetLeft - $productsList[0].offsetLeft;
                        $productsList.animate(
                            { scrollLeft: scrollLeft },
                            transitionDuration
                        );
                    }
                }
            }
        }
        
        /**
         * Initialize on DOM ready
         */
        $(document).ready(function() {
            // Wait for products to be fully loaded
            setTimeout(function() {
                startAutoplay();
            }, 1000);
        });
        
        /**
         * Recalculate on window resize
         */
        let resizeTimer;
        $(window).on('resize', function() {
            clearTimeout( resizeTimer );
            resizeTimer = setTimeout(function() {
                // Disable slider if window resized to mobile
                if ( $(window).width() <= 768 ) {
                    stopAutoplay();
                    // Reset scroll position
                    currentIndex = 0;
                    $productsList.scrollLeft( 0 );
                    return;
                }
                
                // Reset to first item on resize (desktop only)
                currentIndex = 0;
                $productsList.scrollLeft( 0 );
                if ( !isPaused ) {
                    stopAutoplay();
                    startAutoplay();
                }
            }, 250);
        });
    }
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        initLatestCollectionSlider();
    });
    
    // Also initialize after AJAX updates (if products are loaded dynamically)
    $(document.body).on('updated_wc_div', function() {
        setTimeout(initLatestCollectionSlider, 500);
    });
    
})(jQuery);
