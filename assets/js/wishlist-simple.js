/**
 * Simple Wishlist Handler - Reliable and Direct
 * No dependencies, works immediately
 */
(function() {
    'use strict';
    
    // Wait for DOM to be ready
    function initWishlist() {
        console.log('=== SIMPLE WISHLIST INIT ===');
        
        // Get AJAX URL
        const ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php';
        console.log('AJAX URL:', ajaxUrl);
        
        // Get nonce from meta tag or use empty
        let nonce = '';
        const nonceMeta = document.querySelector('meta[name="wp-augoose-nonce"]');
        if (nonceMeta) {
            nonce = nonceMeta.getAttribute('content') || '';
        }
        console.log('Nonce:', nonce);
        
        // Handle wishlist button clicks
        document.addEventListener('click', function(e) {
            // Find wishlist button (clicked element or parent)
            let button = e.target;
            let isWishlistButton = false;
            
            // Check if clicked element is wishlist button or inside it
            while (button && button !== document.body) {
                if (button.classList && (
                    button.classList.contains('add-to-wishlist') || 
                    button.classList.contains('wishlist-toggle')
                )) {
                    isWishlistButton = true;
                    break;
                }
                button = button.parentElement;
            }
            
            if (!isWishlistButton) {
                return; // Not a wishlist button
            }
            
            console.log('=== WISHLIST CLICKED ===');
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            // Get product ID
            let productId = null;
            if (button.dataset && button.dataset.productId) {
                productId = parseInt(button.dataset.productId, 10);
            } else {
                // Try to find data-product-id in parent
                const parentWithId = button.closest('[data-product-id]');
                if (parentWithId && parentWithId.dataset && parentWithId.dataset.productId) {
                    productId = parseInt(parentWithId.dataset.productId, 10);
                }
            }
            
            if (!productId || isNaN(productId)) {
                console.error('Product ID not found');
                alert('Error: Product ID not found');
                return false;
            }
            
            console.log('Product ID:', productId);
            
            // Prevent multiple clicks
            if (button.classList.contains('loading')) {
                console.log('Already processing...');
                return false;
            }
            
            button.classList.add('loading');
            button.disabled = true;
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('action', 'wp_augoose_wishlist_toggle');
            formData.append('product_id', productId);
            if (nonce) {
                formData.append('nonce', nonce);
            }
            
            console.log('Sending AJAX request...');
            
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                console.log('Response received:', response);
                return response.json();
            })
            .then(function(data) {
                console.log('Response data:', data);
                
                button.classList.remove('loading');
                button.disabled = false;
                
                if (data && data.success && data.data) {
                    if (data.data.action === 'added') {
                        button.classList.add('active');
                        console.log('Added to wishlist');
                    } else {
                        button.classList.remove('active');
                        console.log('Removed from wishlist');
                    }
                    
                    // Update count badge if exists
                    const count = data.data.count || 0;
                    const badge = document.querySelector('.wishlist-count');
                    if (badge) {
                        if (count > 0) {
                            badge.textContent = count;
                            badge.style.display = '';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                } else {
                    console.error('Error response:', data);
                    alert('Error: ' + (data.data && data.data.message ? data.data.message : 'Failed to update wishlist'));
                }
            })
            .catch(function(error) {
                console.error('AJAX error:', error);
                button.classList.remove('loading');
                button.disabled = false;
                alert('Error: Failed to update wishlist. Please try again.');
            });
            
            return false;
        });
        
        console.log('Wishlist handler attached');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWishlist);
    } else {
        initWishlist();
    }
})();
