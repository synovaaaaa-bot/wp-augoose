/**
 * Custom Alert Modal - Replace browser alert with styled modal
 * Uses theme classes instead of inline styles so appearance matches the theme
 */

(function() {
    'use strict';

    // Minimal message-only modal: simply replace window.alert with a themed modal
    // No conditional logic or page checks — displays the message and an OK button.
    const originalAlert = window.alert;

    window.alert = function(message) {
        try {
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
        } catch (e) {
            // Fallback to original alert if anything goes wrong
            originalAlert(message);
        }
    };

    function escapeHtml(text) {
        if (typeof text !== 'string') return text;
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
})();
