/**
 * Shop Filter Toggle - Simple & Direct
 * Handles filter sidebar open/close
 */

(function() {
    'use strict';
    
    console.log('=== SHOP FILTER TOGGLE SCRIPT LOADED ===');
    
    const body = document.body;
    let isInitialized = false;
    
    // Open filter
    function openFilter() {
        console.log('Opening filter...');
        body.classList.add('filter-open');
        
        // Update all toggle buttons
        const toggleButtons = document.querySelectorAll('.shop-filter-toggle');
        toggleButtons.forEach(function(btn) {
            btn.setAttribute('aria-expanded', 'true');
        });
        
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
            filterElement.style.display = 'block';
            filterElement.style.visibility = 'visible';
        }
        
        // Show backdrop
        const backdrop = document.querySelector('.shop-filter-backdrop');
        if (backdrop) {
            backdrop.style.display = 'block';
            backdrop.style.opacity = '1';
            backdrop.style.visibility = 'visible';
            backdrop.style.pointerEvents = 'auto';
        }
        
        // Debug: Check if class was added
        console.log('Body classes:', body.className);
        console.log('Filter element:', filterElement);
        if (filterElement) {
            console.log('Filter computed style:', window.getComputedStyle(filterElement).transform);
        }
    }
    
    // Close filter
    function closeFilter() {
        console.log('Closing filter...');
        body.classList.remove('filter-open');
        
        // Update all toggle buttons
        const toggleButtons = document.querySelectorAll('.shop-filter-toggle');
        toggleButtons.forEach(function(btn) {
            btn.setAttribute('aria-expanded', 'false');
        });
        
        // Force set transform directly via JavaScript to ensure it works
        const filterElement = document.querySelector('.shop-filters');
        if (filterElement) {
            filterElement.style.transform = 'translateX(-100%)';
            filterElement.style.webkitTransform = 'translateX(-100%)';
            filterElement.style.mozTransform = 'translateX(-100%)';
            filterElement.style.msTransform = 'translateX(-100%)';
            filterElement.style.oTransform = 'translateX(-100%)';
        }
        
        // Hide backdrop
        const backdrop = document.querySelector('.shop-filter-backdrop');
        if (backdrop) {
            backdrop.style.opacity = '0';
            backdrop.style.visibility = 'hidden';
            backdrop.style.pointerEvents = 'none';
        }
        
        // Restore scroll position
        const scrollY = body.dataset.scrollPos || 0;
        body.style.top = '';
        window.scrollTo(0, parseInt(scrollY, 10));
    }
    
    // Initialize event handlers
    function init() {
        if (isInitialized) {
            return;
        }
        
        console.log('=== INITIALIZING SHOP FILTER TOGGLE ===');
        
        const toggleButton = document.querySelector('.shop-filter-toggle');
        const closeButton = document.querySelector('.shop-filter-close');
        const backdrop = document.querySelector('.shop-filter-backdrop');
        
        console.log('Toggle button found:', toggleButton);
        console.log('Close button found:', closeButton);
        console.log('Backdrop found:', backdrop);
        
        // Use event delegation for toggle button (works even if button is added dynamically)
        document.addEventListener('click', function(e) {
            // Check if clicked element or its parent is the toggle button
            const clickedToggle = e.target.closest('.shop-filter-toggle');
            if (clickedToggle) {
                console.log('=== FILTER TOGGLE CLICKED ===');
                e.preventDefault();
                e.stopPropagation();
                
                if (body.classList.contains('filter-open')) {
                    closeFilter();
                } else {
                    openFilter();
                }
                return;
            }
            
            // Check if clicked element or its parent is the close button
            const clickedClose = e.target.closest('.shop-filter-close');
            if (clickedClose) {
                console.log('=== FILTER CLOSE CLICKED ===');
                e.preventDefault();
                e.stopPropagation();
                closeFilter();
                return;
            }
            
            // Check if clicked on backdrop
            if (e.target === backdrop || e.target.classList.contains('shop-filter-backdrop')) {
                console.log('=== BACKDROP CLICKED ===');
                e.preventDefault();
                e.stopPropagation();
                closeFilter();
                return;
            }
        });
        
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
        
        isInitialized = true;
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
    setTimeout(init, 500); // Extra delay for slow loading
    setTimeout(init, 1000); // Extra delay for very slow loading
    
    // Debug: Check if button exists after page load
    window.addEventListener('load', function() {
        console.log('=== PAGE LOADED - CHECKING FILTER BUTTON ===');
        const btn = document.querySelector('.shop-filter-toggle');
        if (btn) {
            console.log('✓ Filter button found:', btn);
            console.log('Button styles:', window.getComputedStyle(btn));
            console.log('Button z-index:', window.getComputedStyle(btn).zIndex);
            console.log('Button pointer-events:', window.getComputedStyle(btn).pointerEvents);
            
            // Test click programmatically
            btn.addEventListener('click', function(e) {
                console.log('=== DIRECT CLICK EVENT FIRED ===', e);
            });
        } else {
            console.error('✗ Filter button NOT found!');
        }
    });
    
})();
