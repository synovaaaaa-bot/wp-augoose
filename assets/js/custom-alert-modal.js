/**
 * Custom Alert Modal - Replace browser alert with styled modal
 * Uses theme classes instead of inline styles so appearance matches the theme
 */

(function() {
    'use strict';

    const isCheckoutPage = document.body.classList.contains('woocommerce-checkout') ||
                          document.body.classList.contains('checkout') ||
                          window.location.href.indexOf('/checkout') !== -1 ||
                          window.location.pathname.indexOf('/checkout') !== -1;

    if (isCheckoutPage) return;

    const originalAlert = window.alert;

    window.alert = function(message) {
        const cartSidebar = document.querySelector('.woocommerce.widget_shopping_cart, .widget_shopping_cart');
        const isCartOpen = cartSidebar && cartSidebar.classList.contains('open');
        if (isCartOpen) return originalAlert(message);

        const modalHTML = `
            <div class="custom-alert-overlay">
                <div class="custom-alert-modal">
                    <div class="custom-alert-message">${escapeHtml(message)}</div>
                    <button class="custom-alert-button">OK</button>
                </div>
            </div>
        `;

        const container = document.createElement('div');
        container.innerHTML = modalHTML;
        document.body.appendChild(container);

        const button = container.querySelector('.custom-alert-button');
        const overlay = container.querySelector('.custom-alert-overlay');

        function closeModal() {
            container.remove();
        }

        button.addEventListener('click', closeModal);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });

        button.focus();

        document.addEventListener('keydown', function onKey(e) {
            if (e.key === 'Enter' || e.key === 'Escape') {
                closeModal();
                document.removeEventListener('keydown', onKey);
            }
        });
    };

    function escapeHtml(text) {
        if (typeof text !== 'string') return text;
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
})();
