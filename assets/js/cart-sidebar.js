/**
 * Cart Sidebar - Slide from Right
 * Default WooCommerce style
 */

jQuery(document).ready(function($) {
    
    // Open cart sidebar when cart icon clicked
    $(document).on('click', '.cart-icon', function(e) {
        e.preventDefault();
        openCartSidebar();
    });

    // If redirected from /cart, auto-open sidebar
    try {
        const params = new URLSearchParams(window.location.search);
        if (params.get('open_cart') === '1') {
            refreshCartSidebar();
            openCartSidebar();
        }
    } catch (e) {}

    // "View cart" button in WooCommerce notice should open sidebar (not go to cart page)
    $(document).on('click', 'a.added_to_cart.wc-forward, .woocommerce-message a.wc-forward, .woocommerce-notices-wrapper a.wc-forward', function(e) {
        e.preventDefault();
        refreshCartSidebar();
        openCartSidebar();
    });
    
    // Refresh cart sidebar content
    function refreshCartSidebar() {
        $.ajax({
            url: wc_add_to_cart_params.ajax_url || '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'woocommerce_get_refreshed_fragments',
            },
            success: function(response) {
                if (response.fragments) {
                    // Update cart items
                    if (response.fragments['div.cart-sidebar-items']) {
                        $('.cart-sidebar-items').replaceWith(response.fragments['div.cart-sidebar-items']);
                    }
                    // Update footer
                    if (response.fragments['div.cart-sidebar-footer']) {
                        if ($('.cart-sidebar-footer').length) {
                            $('.cart-sidebar-footer').replaceWith(response.fragments['div.cart-sidebar-footer']);
                        } else {
                            $('.woocommerce.widget_shopping_cart').append(response.fragments['div.cart-sidebar-footer']);
                        }
                    } else {
                        $('.cart-sidebar-footer').remove();
                    }
                }
            }
        });
    }
    
    // Close cart sidebar
    $('body').on('click', '.cart-sidebar-close, .cart-sidebar-overlay', function() {
        closeCartSidebar();
    });
    
    // Prevent sidebar from closing when clicking inside
    $('body').on('click', '.woocommerce.widget_shopping_cart', function(e) {
        e.stopPropagation();
    });
    
    function openCartSidebar() {
        $('.woocommerce.widget_shopping_cart').addClass('open');
        $('.cart-sidebar-overlay').addClass('active');
        $('body').css('overflow', 'hidden');
    }
    
    function closeCartSidebar() {
        $('.woocommerce.widget_shopping_cart').removeClass('open');
        $('.cart-sidebar-overlay').removeClass('active');
        $('body').css('overflow', '');
    }
    
    // Close on ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('.woocommerce.widget_shopping_cart').hasClass('open')) {
            closeCartSidebar();
        }
    });
    
    // Update cart sidebar when cart updated
    $(document.body).on('added_to_cart updated_wc_div', function() {
        refreshCartSidebar();
    });
    
    // Update cart count
    $(document.body).on('added_to_cart', function(event, fragments, cart_hash) {
        if (fragments && fragments['button.cart-icon']) {
            $('.cart-icon').replaceWith(fragments['button.cart-icon']);
        }
        refreshCartSidebar();
    });
    
});