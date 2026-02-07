/**
 * Custom Alert Modal - Replace browser alert with styled modal
 * Ensures proper text wrapping and visibility on mobile
 * CRITICAL: Disabled on checkout page to prevent conflicts
 */

(function() {
    'use strict';
    
    // CRITICAL: Don't override alert on checkout page
    // Checkout page needs native alerts for WooCommerce validation
    const isCheckoutPage = document.body.classList.contains('woocommerce-checkout') || 
                          document.body.classList.contains('checkout') ||
                          window.location.href.indexOf('/checkout') !== -1 ||
                          window.location.pathname.indexOf('/checkout') !== -1;
    
    if (isCheckoutPage) {
        // On checkout, use native alert - don't override
        return;
    }
    
    // Override window.alert with custom modal
    const originalAlert = window.alert;
    
    window.alert = function(message) {
        // CRITICAL: Replace WooCommerce default variation messages with our custom message
        const customMessage = "Please select Size And Color before adding this product to your cart.";
        
        if (typeof message === 'string') {
            const messageLower = message.toLowerCase();
            // Check if this is a WooCommerce variation/option selection message
            if ((messageLower.indexOf('select') !== -1 && 
                 (messageLower.indexOf('option') !== -1 || 
                  messageLower.indexOf('variation') !== -1 ||
                  messageLower.indexOf('attribute') !== -1 ||
                  messageLower.indexOf('product option') !== -1)) ||
                messageLower.indexOf('please select') !== -1) {
                // Replace with our custom message
                message = customMessage;
            }
        }
        
        // CRITICAL: Don't show modal if cart sidebar is open
        // Check if cart sidebar is open
        const cartSidebar = document.querySelector('.woocommerce.widget_shopping_cart, .widget_shopping_cart');
        const isCartOpen = cartSidebar && cartSidebar.classList.contains('open');
        
        if (isCartOpen) {
            // If cart is open, use native alert to avoid conflicts
            return originalAlert(message);
        }
        
        // Create modal HTML
        const modalHTML = `
            <div class="custom-alert-overlay" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
            ">
                <div class="custom-alert-modal" style="
                    background: #fff;
                    border-radius: 8px;
                    padding: 24px;
                    max-width: 85vw;
                    width: 100%;
                    max-height: 80vh;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                    overflow-y: auto;
                    box-sizing: border-box;
                    margin: 0 auto;
                ">
                    <div class="custom-alert-message" style="
                        font-size: 13px;
                        line-height: 1.6;
                        color: #1a1a1a;
                        word-wrap: break-word;
                        white-space: normal;
                        margin-bottom: 24px;
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                        text-align: left;
                    ">
                        ${escapeHtml(message)}
                    </div>
                    <button class="custom-alert-button" style="
                        background: #000000;
                        background-color: #000000;
                        color: #ffffff;
                        border: none;
                        padding: 0 14px;
                        border-radius: 8px;
                        font-size: 11px;
                        font-weight: 800;
                        letter-spacing: 0.08em;
                        text-transform: uppercase;
                        cursor: pointer;
                        width: 100%;
                        box-sizing: border-box;
                        height: 44px;
                        min-height: 44px;
                        max-height: 44px;
                        font-family: 'Killarney', Georgia, 'Times New Roman', serif;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        text-align: center;
                        transition: all 0.2s ease;
                        line-height: 1;
                    ">OK</button>
                </div>
            </div>
        `;
        
        // Create container
        const container = document.createElement('div');
        container.innerHTML = modalHTML;
        document.body.appendChild(container);
        
        // Get button and set click handler
        const button = container.querySelector('.custom-alert-button');
        const overlay = container.querySelector('.custom-alert-overlay');
        
        const closeModal = () => {
            container.remove();
        };
        
        button.addEventListener('click', closeModal);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeModal();
        });
        
        // Add hover effects to match theme button style
        button.addEventListener('mouseenter', function() {
            this.style.background = '#222222';
            this.style.transform = 'translateY(-1px)';
        });
        button.addEventListener('mouseleave', function() {
            this.style.background = '#000000';
            this.style.transform = 'translateY(0)';
        });
        button.addEventListener('mousedown', function() {
            this.style.background = '#111111';
            this.style.transform = 'translateY(0)';
        });
        button.addEventListener('mouseup', function() {
            this.style.background = '#222222';
            this.style.transform = 'translateY(-1px)';
        });
        
        // Focus button for keyboard accessibility
        button.focus();
        
        // Allow Enter key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                closeModal();
                document.removeEventListener('keydown', arguments.callee);
            }
        });
    };
    
    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, (m) => map[m]);
    }
})();
