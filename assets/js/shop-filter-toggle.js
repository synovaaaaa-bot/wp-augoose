/**
 * Shop Filter Toggle - Simple & Direct
 * Handles filter sidebar open/close
 */

(function() {
    'use strict';
    
    console.log('=== SHOP FILTER TOGGLE SCRIPT LOADED ===');
    
    // Wait for DOM to be ready
    function init() {
        console.log('=== INITIALIZING SHOP FILTER TOGGLE ===');
        
        const body = document.body;
        const toggleButton = document.querySelector('.shop-filter-toggle');
        const closeButton = document.querySelector('.shop-filter-close');
        const backdrop = document.querySelector('.shop-filter-backdrop');
        
        console.log('Toggle button found:', toggleButton);
        console.log('Close button found:', closeButton);
        console.log('Backdrop found:', backdrop);
        
        if (!toggleButton) {
            console.warn('Shop filter toggle button not found!');
            return;
        }
        
        // Open filter
        function openFilter() {
            console.log('Opening filter...');
            body.classList.add('filter-open');
            toggleButton.setAttribute('aria-expanded', 'true');
            
            // Store scroll position
            const scrollY = window.scrollY;
            body.style.top = `-${scrollY}px`;
            body.dataset.scrollPos = scrollY;
            
            // Force set transform directly via JavaScript to ensure it works
            const filterElement = document.querySelector('.shop-filters');
            if (filterElement) {
                filterElement.style.transform = 'translateX(0)';
                filterElement.style.webkitTransform = 'translateX(0)';
                filterElement.style.mozTransform = 'translateX(0)';
                filterElement.style.msTransform = 'translateX(0)';
                filterElement.style.oTransform = 'translateX(0)';
            }
            
            // Debug: Check if class was added
            console.log('Body classes:', body.className);
            console.log('Filter element:', filterElement);
            console.log('Filter computed style:', window.getComputedStyle(filterElement).transform);
        }
        
        // Close filter
        function closeFilter() {
            console.log('Closing filter...');
            body.classList.remove('filter-open');
            toggleButton.setAttribute('aria-expanded', 'false');
            
            // Force set transform directly via JavaScript to ensure it works
            const filterElement = document.querySelector('.shop-filters');
            if (filterElement) {
                filterElement.style.transform = 'translateX(-100%)';
                filterElement.style.webkitTransform = 'translateX(-100%)';
                filterElement.style.mozTransform = 'translateX(-100%)';
                filterElement.style.msTransform = 'translateX(-100%)';
                filterElement.style.oTransform = 'translateX(-100%)';
            }
            
            // Restore scroll position
            const scrollY = body.dataset.scrollPos || 0;
            body.style.top = '';
            window.scrollTo(0, parseInt(scrollY, 10));
        }
        
        // Toggle filter on button click
        toggleButton.addEventListener('click', function(e) {
            console.log('=== FILTER TOGGLE CLICKED ===');
            e.preventDefault();
            e.stopPropagation();
            
            if (body.classList.contains('filter-open')) {
                closeFilter();
            } else {
                openFilter();
            }
        });
        
        // Close filter on close button click
        if (closeButton) {
            closeButton.addEventListener('click', function(e) {
                console.log('=== FILTER CLOSE CLICKED ===');
                e.preventDefault();
                e.stopPropagation();
                closeFilter();
            });
        }
        
        // Close filter on backdrop click
        if (backdrop) {
            backdrop.addEventListener('click', function(e) {
                console.log('=== BACKDROP CLICKED ===');
                e.preventDefault();
                e.stopPropagation();
                closeFilter();
            });
        }
        
        // Close filter on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                if (body.classList.contains('filter-open')) {
                    console.log('ESC pressed - closing filter');
                    closeFilter();
                }
            }
        });
        
        // Prevent body scroll when filter is open
        body.addEventListener('touchmove', function(e) {
            if (body.classList.contains('filter-open')) {
                // Allow scroll inside filter panel
                if (!e.target.closest('.shop-filters')) {
                    e.preventDefault();
                }
            }
        }, { passive: false });
        
        console.log('=== SHOP FILTER TOGGLE INITIALIZED ===');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM already loaded
        init();
    }
    
    // Also try to initialize after a short delay (in case DOM is still loading)
    setTimeout(init, 100);
    
})();
