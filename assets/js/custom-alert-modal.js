/**
 * Custom Alert Modal - Replace browser alert with styled modal
 * Uses theme classes instead of inline styles so appearance matches the theme
 */

(function() {
    'use strict';

    // Only show the themed modal for the exact missing-options message.
    // All other alert() calls will be logged to console to avoid extra modals.
    const originalAlert = window.alert;
    const TARGET_MESSAGE = 'Please select Size and Color before adding this product to your cart.';

    window.alert = function(message) {
        try {
            if (typeof message === 'string' && message.trim() === TARGET_MESSAGE) {
                const modalHTML = `
                    <div class="custom-alert-overlay">
                        <div class="custom-alert-modal">
                            <div class="custom-alert-message">${escapeHtml(message)}</div>
                            <button class="custom-alert-button">OK</button>
                        </div>
                    </div>`;

                const container = document.createElement('div');
                container.innerHTML = modalHTML;
                document.body.appendChild(container);

                const button = container.querySelector('.custom-alert-button');
                const overlay = container.querySelector('.custom-alert-overlay');

                function closeModal() { container.remove(); }

                button.addEventListener('click', closeModal);
                overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
                return;
            }

            // For any other messages, avoid showing a modal — log instead.
            if (typeof console !== 'undefined' && console.log) {
                console.log('alert:', message);
            }
        } catch (e) {
            // If something unexpectedly fails, fall back to native alert for visibility
            try { originalAlert(message); } catch (ee) { /* swallow */ }
        }
    };

    function escapeHtml(text) {
        if (typeof text !== 'string') return text;
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
})();
