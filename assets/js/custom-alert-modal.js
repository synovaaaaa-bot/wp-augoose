/**
 * Custom Alert Modal - Replace browser alert with styled modal
 * Ensures proper text wrapping and visibility on mobile
 */

(function() {
    'use strict';
    
    // Override window.alert with custom modal
    const originalAlert = window.alert;
    
    window.alert = function(message) {
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
                z-index: 999999;
            ">
                <div class="custom-alert-modal" style="
                    background: #fff;
                    border-radius: 8px;
                    padding: 20px;
                    max-width: 85vw;
                    width: 100%;
                    max-height: 80vh;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                    overflow-y: auto;
                    box-sizing: border-box;
                    margin: 0 auto;
                ">
                    <div class="custom-alert-message" style="
                        font-size: 14px;
                        line-height: 1.6;
                        color: #333;
                        word-wrap: break-word;
                        white-space: normal;
                        margin-bottom: 20px;
                    ">
                        ${escapeHtml(message)}
                    </div>
                    <button class="custom-alert-button" style="
                        background: #007bff;
                        color: #fff;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 4px;
                        font-size: 14px;
                        font-weight: 600;
                        cursor: pointer;
                        width: 100%;
                        box-sizing: border-box;
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
