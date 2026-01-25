/**
 * Main JavaScript file
 * 
 * @package WP_Augoose
 */

(function($) {
    'use strict';

    // Mobile Menu Toggle - Fixed with event delegation
    function initMobileMenu() {
        console.log('=== INIT MOBILE MENU ===');
        
        // Use event delegation for mobile menu toggle
        $(document).on('click', '.mobile-menu-toggle', function(e) {
            console.log('=== MOBILE MENU TOGGLE CLICKED ===');
            e.preventDefault();
            e.stopPropagation();
            
            const $toggle = $(this);
            const $menu = $('.mobile-menu');
            const isExpanded = $toggle.attr('aria-expanded') === 'true';
            
            if (isExpanded) {
                $menu.slideUp(300);
                $toggle.attr('aria-expanded', 'false');
                $('body').removeClass('mobile-menu-open');
            } else {
                $menu.slideDown(300);
                $toggle.attr('aria-expanded', 'true');
                $('body').addClass('mobile-menu-open');
            }
        });

        // Close mobile menu when clicking outside
        $(document).on('click', function(e) {
            const $menu = $('.mobile-menu');
            const $header = $('.site-header');
            if (!$header.length) {
                $header = $('header');
            }
            
            if (!$header.length || (!$header.is(e.target) && $header.has(e.target).length === 0)) {
                if ($menu.is(':visible')) {
                    $menu.slideUp(300);
                    $('.mobile-menu-toggle').attr('aria-expanded', 'false');
                    $('body').removeClass('mobile-menu-open');
                }
            }
        });

        // Close mobile menu on window resize
        $(window).on('resize', function() {
            if ($(window).width() > 968) {
                $('.mobile-menu').hide();
                $('.mobile-menu-toggle').attr('aria-expanded', 'false');
                $('body').removeClass('mobile-menu-open');
            }
        });
        
        console.log('Mobile menu initialized');
    }

    // Search Toggle - Fixed for mobile with event delegation
    function initSearchToggle() {
        console.log('=== INIT SEARCH TOGGLE ===');
        
        // Use event delegation to catch clicks on search toggle button (including SVG inside)
        $(document).on('click', '.search-toggle, .search-toggle *', function(e) {
            console.log('=== SEARCH TOGGLE CLICKED ===', e.target);
            e.preventDefault();
            e.stopPropagation();
            
            // Find the actual button (might be clicked on SVG or path inside)
            let $button = $(this);
            if (!$button.hasClass('search-toggle')) {
                $button = $button.closest('.search-toggle');
            }
            
            if ($button.length === 0) {
                console.error('Search toggle button not found!');
                return;
            }
            
            let $searchFormContainer = $button.closest('.header-search').find('.search-form-container');
            if ($searchFormContainer.length === 0) {
                // Fallback: find by class
                $searchFormContainer = $('.search-form-container');
            }
            
            console.log('Search container found:', $searchFormContainer.length);
            
            // Toggle search form
            if ($searchFormContainer.is(':visible')) {
                $searchFormContainer.slideUp(300, function() {
                    $(this).attr('aria-hidden', 'true');
                });
                $button.attr('aria-expanded', 'false');
            } else {
                $searchFormContainer.attr('aria-hidden', 'false');
                $searchFormContainer.slideDown(300);
                $button.attr('aria-expanded', 'true');
                
                // Focus input after animation
                setTimeout(function() {
                    const searchInput = $searchFormContainer.find('input[type="search"], input[type="text"], .search-field');
                    if (searchInput.length) {
                        searchInput.focus();
                        // For mobile, sometimes need to trigger click to show keyboard
                        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                            searchInput[0].click();
                        }
                    }
                }, 350);
            }
        });

        // Close search when clicking outside - improved for mobile
        $(document).on('click touchstart', function(e) {
            const searchFormContainer = $('.search-form-container');
            const headerSearch = $('.header-search');
            
            // Check if click is outside search area
            if (!$(e.target).closest('.header-search').length && 
                !$(e.target).closest('.search-form-container').length &&
                searchFormContainer.is(':visible')) {
                console.log('Closing search - clicked outside');
                searchFormContainer.slideUp(300, function() {
                    $(this).attr('aria-hidden', 'true');
                });
                $('.search-toggle').attr('aria-expanded', 'false');
            }
        });
        
        // Close search on ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                const searchFormContainer = $('.search-form-container');
                if (searchFormContainer.is(':visible')) {
                    searchFormContainer.slideUp(300, function() {
                        $(this).attr('aria-hidden', 'true');
                    });
                    $('.search-toggle').attr('aria-expanded', 'false');
                }
            }
        });
        
        console.log('Search toggle initialized');
    }

    // Site-only search history (localStorage) for product search
    function initSearchHistory() {
        const $input = $('#augoose-product-search-field');
        const $form = $input.closest('form.augoose-search');
        const $datalist = $('#augoose-search-history');
        if (!$input.length || !$form.length || !$datalist.length) return;

        const key = 'augoose_search_history_v1';

        function readHistory() {
            try {
                const raw = localStorage.getItem(key);
                const list = raw ? JSON.parse(raw) : [];
                return Array.isArray(list) ? list : [];
            } catch (e) {
                return [];
            }
        }

        function writeHistory(list) {
            try {
                localStorage.setItem(key, JSON.stringify(list.slice(0, 8)));
            } catch (e) {}
        }

        function renderDatalist() {
            const list = readHistory();
            $datalist.empty();
            list.forEach(function(q) {
                if (!q) return;
                $datalist.append($('<option>').attr('value', q));
            });
        }

        $input.on('focus', renderDatalist);
        $form.on('submit', function() {
            const q = ($input.val() || '').trim();
            if (!q) return;
            const list = readHistory().filter(function(x) { return x && x !== q; });
            list.unshift(q);
            writeHistory(list);
        });
    }

    // Sticky Header
    function initStickyHeader() {
        const header = $('.site-header');
        const headerHeight = header.outerHeight();

        $(window).on('scroll', function() {
            const currentScroll = $(this).scrollTop();

            if (currentScroll > headerHeight) {
                header.addClass('scrolled');
            } else {
                header.removeClass('scrolled');
            }
        });
    }

    // Shop Filters Toggle (Archive pages) - Off-Canvas with No Layout Shift
    // NOTE: This function is disabled because shop-filter-toggle.js handles it
    // Keeping this function stub to avoid breaking other code that might call it
    function initShopFiltersToggle() {
        console.log('=== INIT SHOP FILTERS TOGGLE (DISABLED - using shop-filter-toggle.js instead) ===');
        // Filter toggle is now handled by shop-filter-toggle.js (vanilla JS)
        // This prevents conflicts between jQuery and vanilla JS handlers
        return;
    }

    // Product Quick View
    function initQuickView() {
        $(document).on('click', '.quick-view', function(e) {
            e.preventDefault();
            const productId = $(this).data('product-id');

            // Create modal
            const modal = $('<div class="quick-view-modal"><div class="modal-overlay"></div><div class="modal-content"><button class="modal-close">&times;</button><div class="modal-body loading">Loading...</div></div></div>');
            $('body').append(modal);

            // Load product via AJAX
            $.ajax({
                url: wpAugoose.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'quick_view_product',
                    product_id: productId,
                    nonce: wpAugoose.nonce
                },
                success: function(response) {
                    if (response.success) {
                        modal.find('.modal-body').html(response.data.html).removeClass('loading');
                    } else {
                        modal.find('.modal-body').html('<p>Error loading product.</p>').removeClass('loading');
                    }
                },
                error: function() {
                    modal.find('.modal-body').html('<p>Error loading product.</p>').removeClass('loading');
                }
            });

            // Close modal
            modal.find('.modal-close, .modal-overlay').on('click', function() {
                modal.fadeOut(300, function() {
                    $(this).remove();
                });
            });

            modal.fadeIn(300);
        });
    }

    // Add to Wishlist (Integrated - server cookie/user meta)
    function initWishlist() {
        console.log('initWishlist() called');
        
        // Ensure wpAugoose is available (fallback)
        if (typeof wpAugoose === 'undefined') {
            console.error('wpAugoose object not found. Make sure main.js is loaded with wp_localize_script.');
            // Try to get AJAX URL from WordPress default
            window.wpAugoose = {
                ajaxUrl: typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php',
                nonce: ''
            };
        }
        
        console.log('wpAugoose object:', wpAugoose);
        console.log('Wishlist buttons on page:', $('.add-to-wishlist, .wishlist-toggle').length);
        
        // Debounce to prevent multiple rapid clicks
        let wishlistProcessing = false;
        
        // Use event delegation - attach to document to catch dynamically added buttons
        // Handle clicks on button OR any child element (SVG, path, etc.)
        $(document).on('click', '.add-to-wishlist, .wishlist-toggle, .add-to-wishlist *, .wishlist-toggle *', function(e) {
            console.log('=== WISHLIST BUTTON CLICKED ===');
            console.log('Event target:', e.target);
            console.log('Current target:', e.currentTarget);
            console.log('Clicked element:', this);
            
            // Find the actual button (might be clicked on SVG or path inside)
            let button = $(this);
            if (!button.hasClass('add-to-wishlist') && !button.hasClass('wishlist-toggle')) {
                // Clicked on child element, find parent button
                button = button.closest('.add-to-wishlist, .wishlist-toggle');
            }
            
            if (button.length === 0) {
                console.error('Button not found!');
                return false;
            }
            
            console.log('Button found:', button[0]);
            console.log('Button classes:', button.attr('class'));
            
            // CRITICAL: Stop event immediately to prevent product link navigation
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation(); // Prevent other handlers
            
            console.log('Button jQuery object:', button);
            console.log('Button classes:', button.attr('class'));
            console.log('Button data attributes:', button.data());
            
            // Try multiple ways to get product ID
            let productId = parseInt(button.data('product-id'), 10);
            console.log('Product ID from data attribute:', productId);
            
            if (!productId || isNaN(productId)) {
                // Try from parent element
                const parent = button.closest('[data-product-id]');
                if (parent.length) {
                    productId = parseInt(parent.data('product-id'), 10);
                    console.log('Got product ID from parent:', productId);
                }
            }
            
            // Validate product ID
            if (!productId || isNaN(productId)) {
                console.error('Invalid product ID - Button:', button);
                console.error('Button HTML:', button[0] ? button[0].outerHTML : 'No element');
                console.error('Parent HTML:', button.parent().html());
                alert('Error: Product ID not found. Please refresh the page.');
                return false;
            }

            // Prevent multiple simultaneous requests
            if (wishlistProcessing || button.hasClass('loading')) {
                console.log('Wishlist already processing, skipping...');
                return false;
            }

            wishlistProcessing = true;
            button.addClass('loading').prop('disabled', true);
            
            // Debug logging
            console.log('Wishlist toggle - Product ID:', productId);
            console.log('Wishlist toggle - wpAugoose:', wpAugoose);
            console.log('Wishlist toggle - AJAX URL:', wpAugoose ? wpAugoose.ajaxUrl : 'undefined');
            console.log('Wishlist toggle - Nonce:', wpAugoose ? wpAugoose.nonce : 'undefined');
            
            // Validate wpAugoose object
            if (typeof wpAugoose === 'undefined' || !wpAugoose || !wpAugoose.ajaxUrl) {
                console.error('wpAugoose object is not properly initialized');
                button.removeClass('loading').prop('disabled', false);
                wishlistProcessing = false;
                alert('Error: Wishlist system not initialized. Please refresh the page.');
                return false;
            }
            
            // Get nonce
            const nonce = wpAugoose.nonce || '';
            if (!nonce) {
                console.error('Nonce not found');
                button.removeClass('loading').prop('disabled', false);
                wishlistProcessing = false;
                alert('Error: Security token not found. Please refresh the page.');
                return false;
            }
            
            console.log('Sending AJAX request to:', wpAugoose.ajaxUrl);
            console.log('AJAX data:', {
                action: 'wp_augoose_wishlist_toggle',
                product_id: productId,
                nonce: nonce
            });
            
            $.ajax({
                url: wpAugoose.ajaxUrl,
                type: 'POST',
                timeout: 10000, // 10 second timeout
                data: {
                    action: 'wp_augoose_wishlist_toggle',
                    product_id: productId,
                    nonce: nonce
                },
                beforeSend: function() {
                    console.log('AJAX request started');
                },
                success: function(res) {
                    console.log('Wishlist AJAX response:', res);
                    
                    if (res && res.success && res.data) {
                        if (res.data.action === 'added') {
                            button.addClass('active');
                            console.log('Product added to wishlist');
                            if (typeof showNotification === 'function') {
                                showNotification('Product added to wishlist');
                            } else {
                                alert('Product added to wishlist');
                            }
                        } else {
                            button.removeClass('active');
                            console.log('Product removed from wishlist');
                            if (typeof showNotification === 'function') {
                                showNotification('Product removed from wishlist');
                            } else {
                                alert('Product removed from wishlist');
                            }
                        }
                        
                        // Update badge count
                        const count = res.data.count || 0;
                        console.log('Wishlist count:', count);
                        const $badge = $('.wishlist-count');
                        if ($badge.length) {
                            if (count > 0) {
                                $badge.text(count).show();
                            } else {
                                $badge.hide();
                            }
                        }
                    } else {
                        const errorMsg = (res && res.data && res.data.message) ? res.data.message : 'Error updating wishlist';
                        console.error('Wishlist error:', errorMsg, res);
                        if (typeof showNotification === 'function') {
                            showNotification(errorMsg, 'error');
                        } else {
                            alert(errorMsg);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Wishlist AJAX error:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusCode: xhr.status,
                        readyState: xhr.readyState
                    });
                    
                    // Try to parse error response
                    let errorMsg = 'Error updating wishlist. Please try again.';
                    if (xhr.responseText) {
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse.data && errorResponse.data.message) {
                                errorMsg = errorResponse.data.message;
                            }
                        } catch (e) {
                            console.error('Failed to parse error response:', e);
                        }
                    }
                    
                    if (typeof showNotification === 'function') {
                        showNotification(errorMsg, 'error');
                    } else {
                        alert(errorMsg);
                    }
                },
                complete: function() {
                    console.log('AJAX request completed');
                    button.removeClass('loading').prop('disabled', false);
                    wishlistProcessing = false;
                }
            });
            
            return false;
        });

        // Initialize wishlist buttons from server (optimized)
        let wishlistInitialized = false;
        function updateWishlistButtons() {
            // Only initialize once per page load to reduce lag
            if (wishlistInitialized) {
                return;
            }
            
            // Skip if no wishlist buttons on page
            if ($('.add-to-wishlist').length === 0) {
                return;
            }
            
            wishlistInitialized = true;
            
            $.ajax({
                url: wpAugoose.ajaxUrl,
                type: 'POST',
                timeout: 5000, // 5 second timeout
                data: {
                    action: 'wp_augoose_wishlist_get',
                    nonce: wpAugoose.nonce
                },
                success: function(res) {
                    if (res && res.success && res.data) {
                        const count = res.data.count || 0;
                        const html = res.data.html || '';
                        
                        // Derive ids by parsing data-product-id in HTML (lightweight)
                        const ids = [];
                        if (html) {
                            const $tmp = $('<div>').html(html);
                            $tmp.find('.wishlist-item').each(function() {
                                const pid = parseInt($(this).data('product-id'), 10);
                                if (pid && !isNaN(pid)) {
                                    ids.push(pid);
                                }
                            });
                        }
                        
                        // Update button states
                        $('.add-to-wishlist').each(function() {
                            const pid = parseInt($(this).data('product-id'), 10);
                            if (pid && !isNaN(pid) && ids.includes(pid)) {
                                $(this).addClass('active');
                            } else {
                                $(this).removeClass('active');
                            }
                        });
                        
                        // Update badge count
                        const $badge = $('.wishlist-count');
                        if ($badge.length) {
                            if (count > 0) {
                                $badge.text(count).show();
                            } else {
                                $badge.hide();
                            }
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load wishlist:', status, error);
                    wishlistInitialized = false; // Allow retry on error
                }
            });
        }

        // Initialize on page load (with small delay to reduce initial lag)
        setTimeout(updateWishlistButtons, 100);
        
        // Test handler attachment
        console.log('Wishlist handler attached. Buttons found:', $('.add-to-wishlist, .wishlist-toggle').length);
        
        // Test click on existing buttons to verify handler works
        $('.add-to-wishlist, .wishlist-toggle').each(function() {
            const $btn = $(this);
            const productId = $btn.data('product-id');
            console.log('Wishlist button found - Product ID:', productId, 'Element:', this);
        });
    }
    

    // AJAX Add to Cart
    function initAjaxAddToCart() {
        $(document).on('click', '.ajax_add_to_cart:not(.product_type_variable), .ajax-add-to-cart', function(e) {
            e.preventDefault();
            const button = $(this);
            const productId = button.data('product_id') || button.data('product-id');
            const quantity = button.data('quantity') || 1;

            button.addClass('loading').prop('disabled', true);

            $.ajax({
                url: wpAugoose.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_augoose_add_to_cart',
                    product_id: productId,
                    quantity: quantity,
                    nonce: wpAugoose.nonce
                },
                success: function(response) {
                    if (response.success) {
                        button.removeClass('loading').addClass('added');
                        button.find('span').text('Added!');
                        showNotification(response.data.message);
                        
                        // Update cart count
                        if (response.data.cart_count) {
                            $('.cart-count').text(response.data.cart_count);
                            if ($('.cart-count').length === 0 && response.data.cart_count > 0) {
                                $('.cart-link').append('<span class="cart-count">' + response.data.cart_count + '</span>');
                            }
                        }
                        
                        // Reset button after 2 seconds
                        setTimeout(function() {
                            button.removeClass('added').prop('disabled', false);
                            button.find('span').text('Add to Cart');
                        }, 2000);
                        
                        // Trigger WooCommerce event
                        $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, button]);
                        
                        // Prevent duplicate buttons
                        $('.single-product-wrapper form.cart .single_add_to_cart_button:not(:first)').remove();
                    } else {
                        button.removeClass('loading').prop('disabled', false);
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    button.removeClass('loading').prop('disabled', false);
                    showNotification('Error adding product to cart', 'error');
                }
            });
        });
    }

    // Show Notification
    function showNotification(message, type = 'success') {
        const notification = $('<div class="notification ' + type + '">' + message + '</div>');
        $('body').append(notification);

        setTimeout(function() {
            notification.addClass('show');
        }, 100);

        setTimeout(function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Quantity Input
    function initQuantityInput() {
        // Add plus/minus buttons
        $('div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)').addClass('buttons_added').append('<input type="button" value="+" class="plus" />').prepend('<input type="button" value="-" class="minus" />');

        // Minus button
        $(document).on('click', '.quantity .minus', function(e) {
            e.preventDefault();
            const input = $(this).siblings('.qty');
            const min = parseFloat(input.attr('min'));
            let val = parseFloat(input.val());

            if (val > min) {
                input.val(val - 1).trigger('change');
            }
        });

        // Plus button
        $(document).on('click', '.quantity .plus', function(e) {
            e.preventDefault();
            const input = $(this).siblings('.qty');
            const max = parseFloat(input.attr('max'));
            let val = parseFloat(input.val());

            if (!max || val < max) {
                input.val(val + 1).trigger('change');
            }
        });
    }

    // Smooth Scroll
    function initSmoothScroll() {
        $('a[href*="#"]:not([href="#"])').on('click', function() {
            if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') && location.hostname === this.hostname) {
                let target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');

                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 800);
                    return false;
                }
            }
        });
    }

    // Product Image Zoom (for single product)
    function initProductZoom() {
        if (typeof $.fn.zoom !== 'undefined') {
            $('.woocommerce-product-gallery__image').zoom();
        }
    }

    // Newsletter Form
    function initNewsletterForm() {
        $('.newsletter-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const email = form.find('input[type="email"]').val();

            $.ajax({
                url: wpAugoose.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'subscribe_newsletter',
                    email: email,
                    nonce: wpAugoose.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification(response.data.message);
                        form[0].reset();
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    showNotification('Error subscribing to newsletter', 'error');
                }
            });
        });
    }

    // Size Guide Toggle (Single Product Page)
    function initSizeGuideToggle() {
        // Simple function to open modal with specific guide
        function openSizeGuide(guide) {
            guide = guide || 'pants';
            const modal = document.getElementById('size-guide-modal');
            if (!modal) {
                console.error('Size guide modal not found');
                return;
            }
            
            // Show correct guide
            const tabs = modal.querySelectorAll('.size-guide-tab');
            const wrappers = modal.querySelectorAll('.size-guide-table-wrapper');
            
            tabs.forEach(function(tab) {
                if (tab.getAttribute('data-guide') === guide) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });
            
            wrappers.forEach(function(wrapper) {
                if (wrapper.getAttribute('data-guide') === guide) {
                    wrapper.style.display = 'block';
                    wrapper.style.visibility = 'visible';
                } else {
                    wrapper.style.display = 'none';
                    wrapper.style.visibility = 'hidden';
                }
            });
            
            // Show modal - use !important to override CSS
            modal.setAttribute('style', 'display: flex !important; visibility: visible !important;');
            document.body.classList.add('size-guide-open');
            
            // Force show content wrapper
            const contentWrapper = modal.querySelector('.size-guide-content-wrapper');
            if (contentWrapper) {
                contentWrapper.style.display = 'block';
                contentWrapper.style.visibility = 'visible';
            }
            
            const content = modal.querySelector('.size-guide-content');
            if (content) {
                content.style.display = 'block';
                content.style.visibility = 'visible';
            }
        }
        
        // Close modal
        function closeSizeGuide() {
            const modal = document.getElementById('size-guide-modal');
            if (modal) {
                modal.setAttribute('style', 'display: none !important; visibility: hidden !important;');
                document.body.classList.remove('size-guide-open');
            }
        }
        
        // Open from product page SIZE GUIDE link
        $(document).on('click', '.size-guide-link', function(e) {
            e.preventDefault();
            let guide = 'pants';
            
            // Simple detection
            const url = window.location.href.toLowerCase();
            const title = document.title.toLowerCase();
            const productTitle = $('.product_title, h1.product-title').text().toLowerCase();
            
            if (url.includes('jacket') || url.includes('shirt') || title.includes('jacket') || title.includes('shirt') || 
                productTitle.includes('jacket') || productTitle.includes('shirt')) {
                guide = 'jackets';
            }
            
            openSizeGuide(guide);
        });
        
        // Open from footer links
        $(document).on('click', '.footer-size-guide-link', function(e) {
            e.preventDefault();
            const guide = $(this).attr('data-guide') || 'pants';
            openSizeGuide(guide);
        });
        
        // Close modal
        $(document).on('click', '.size-guide-close, .size-guide-overlay', function(e) {
            e.preventDefault();
            closeSizeGuide();
        });
        
        // Switch between tabs
        $(document).on('click', '.size-guide-tab', function(e) {
            e.preventDefault();
            const guide = $(this).attr('data-guide');
            if (guide) {
                openSizeGuide(guide);
            }
        });
        
        // Close on ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                closeSizeGuide();
            }
        });
    }

    // Product Image Auto-Slide (Figma Design)
    function initProductImageSlider() {
        $('.product-thumbnail').each(function() {
            const $thumbnail = $(this);
            const $slider = $thumbnail.find('.product-images-slider');
            const $images = $slider.find('.product-image');
            const $indicators = $thumbnail.find('.indicator');
            const totalImages = $images.length;
            
            if (totalImages <= 1) return;
            
            let currentIndex = 0;
            let slideInterval;
            
            function showImage(index) {
                $images.removeClass('active');
                $indicators.removeClass('active');
                $images.eq(index).addClass('active');
                $indicators.eq(index).addClass('active');
            }
            
            function nextImage() {
                currentIndex = (currentIndex + 1) % totalImages;
                showImage(currentIndex);
            }
            
            // Start auto-slide on hover
            $thumbnail.on('mouseenter', function() {
                slideInterval = setInterval(nextImage, 1000); // Change image every 1 second
            });
            
            // Stop auto-slide on mouse leave
            $thumbnail.on('mouseleave', function() {
                clearInterval(slideInterval);
                currentIndex = 0;
                showImage(0); // Reset to first image
            });
            
            // Manual indicator click
            $indicators.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                clearInterval(slideInterval);
                currentIndex = $(this).data('index');
                showImage(currentIndex);
            });
        });
    }

    // CRITICAL: Attach wishlist handler IMMEDIATELY - don't wait for document ready
    // This ensures handler is attached as early as possible, even before DOM is ready
    console.log('=== WISHLIST HANDLER SETUP STARTING ===');
    
    // Use immediate function to attach handler right away
    (function() {
        // Check if jQuery is available
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded! Wishlist will not work.');
            return;
        }
        
        console.log('jQuery is available, attaching wishlist handler...');
        
        // Attach handler immediately using jQuery (works even before DOM ready)
        jQuery(document).on('click', '.add-to-wishlist, .wishlist-toggle', function(e) {
            console.log('=== WISHLIST BUTTON CLICKED (IMMEDIATE HANDLER) ===');
            console.log('Event:', e);
            console.log('Button:', this);
            console.log('jQuery object:', jQuery(this));
            
            // Stop event immediately
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            const button = jQuery(this);
            const productId = parseInt(button.data('product-id'), 10) || parseInt(button.closest('[data-product-id]').data('product-id'), 10);
            
            console.log('Product ID:', productId);
            
            if (!productId) {
                console.error('No product ID found!');
                alert('Error: Product ID not found');
                return false;
            }
            
            // Check wpAugoose
            if (typeof wpAugoose === 'undefined' || !wpAugoose || !wpAugoose.ajaxUrl) {
                console.error('wpAugoose not available:', typeof wpAugoose);
                alert('Error: Wishlist system not initialized');
                return false;
            }
            
            console.log('wpAugoose available:', wpAugoose);
            console.log('Sending AJAX request...');
            
            // Prevent multiple clicks
            if (button.hasClass('loading')) {
                console.log('Already processing...');
                return false;
            }
            
            button.addClass('loading').prop('disabled', true);
            
            jQuery.ajax({
                url: wpAugoose.ajaxUrl,
                type: 'POST',
                timeout: 10000,
                data: {
                    action: 'wp_augoose_wishlist_toggle',
                    product_id: productId,
                    nonce: wpAugoose.nonce || ''
                },
                success: function(res) {
                    console.log('AJAX Success:', res);
                    if (res && res.success && res.data) {
                        if (res.data.action === 'added') {
                            button.addClass('active');
                            alert('Product added to wishlist');
                        } else {
                            button.removeClass('active');
                            alert('Product removed from wishlist');
                        }
                        
                        // Update badge
                        const count = res.data.count || 0;
                        const $badge = jQuery('.wishlist-count');
                        if ($badge.length) {
                            if (count > 0) {
                                $badge.text(count).show();
                            } else {
                                $badge.hide();
                            }
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error, xhr.responseText);
                    alert('Error: ' + (xhr.responseText || error));
                },
                complete: function() {
                    button.removeClass('loading').prop('disabled', false);
                }
            });
            
            return false;
        });
        
        console.log('Wishlist handler attached successfully!');
    })();
    
    // Initialize on Document Ready
    // Language Switcher (cookie-based)
    function initLanguageSwitcher() {
        $('.augoose-lang-switcher').on('change', function() {
            const lang = $(this).val();
            document.cookie = 'wp_augoose_lang=' + lang + '; path=/; max-age=31536000'; // 1 year
            // Reload page to apply language (if you have translation files)
            location.reload();
        });
    }


    // Product Description Read More Toggle
    function initReadMore() {
        $('.read-more-toggle').on('click', function(e) {
            e.preventDefault();
            const $toggle = $(this);
            const $summary = $toggle.closest('.product-description-summary');
            const $short = $summary.find('.product-description-short');
            const $full = $summary.find('.product-description-full');
            const $readMore = $toggle.find('.read-more-text');
            const $readLess = $toggle.find('.read-less-text');
            const isExpanded = $toggle.attr('data-expanded') === 'true';

            if (isExpanded) {
                // Collapse
                $short.slideDown(300);
                $full.slideUp(300, function() {
                    $full.hide();
                });
                $readMore.show();
                $readLess.hide();
                $toggle.attr('data-expanded', 'false');
            } else {
                // Expand
                $full.show();
                $short.slideUp(300);
                $full.slideDown(300);
                $readMore.hide();
                $readLess.show();
                $toggle.attr('data-expanded', 'true');
            }
        });
    }

    // Prevent duplicate add to cart buttons
    function preventDuplicateButtons() {
        $('.single-product-wrapper form.cart').each(function() {
            const $form = $(this);
            const $buttons = $form.find('.single_add_to_cart_button');
            if ($buttons.length > 1) {
                $buttons.not(':first').remove();
            }
        });
    }
    
    // Run on page load and after AJAX
    // CRITICAL: Wrap everything in document.ready to ensure DOM is loaded
    $(document).ready(function() {
        console.log('=== MAIN.JS LOADED - INITIALIZING ALL FUNCTIONS ===');
        
        try {
            preventDuplicateButtons();
            console.log('✓ preventDuplicateButtons initialized');
        } catch(e) {
            console.error('✗ preventDuplicateButtons error:', e);
        }
        
        try {
            initMobileMenu();
            console.log('✓ initMobileMenu initialized');
        } catch(e) {
            console.error('✗ initMobileMenu error:', e);
        }
        
        try {
            initSearchToggle();
            console.log('✓ initSearchToggle initialized');
        } catch(e) {
            console.error('✗ initSearchToggle error:', e);
        }
        
        try {
            initSearchHistory();
            console.log('✓ initSearchHistory initialized');
        } catch(e) {
            console.error('✗ initSearchHistory error:', e);
        }
        
        try {
            initStickyHeader();
            console.log('✓ initStickyHeader initialized');
        } catch(e) {
            console.error('✗ initStickyHeader error:', e);
        }
        
        try {
            initShopFiltersToggle();
            console.log('✓ initShopFiltersToggle initialized');
        } catch(e) {
            console.error('✗ initShopFiltersToggle error:', e);
        }
        
        try {
            initQuickView();
            console.log('✓ initQuickView initialized');
        } catch(e) {
            console.error('✗ initQuickView error:', e);
        }
        
        try {
            initWishlist();
            console.log('✓ initWishlist initialized');
        } catch(e) {
            console.error('✗ initWishlist error:', e);
        }
        
        try {
            initAjaxAddToCart();
            console.log('✓ initAjaxAddToCart initialized');
        } catch(e) {
            console.error('✗ initAjaxAddToCart error:', e);
        }
        
        try {
            initQuantityInput();
            console.log('✓ initQuantityInput initialized');
        } catch(e) {
            console.error('✗ initQuantityInput error:', e);
        }
        
        try {
            initSmoothScroll();
            console.log('✓ initSmoothScroll initialized');
        } catch(e) {
            console.error('✗ initSmoothScroll error:', e);
        }
        
        try {
            initProductZoom();
            console.log('✓ initProductZoom initialized');
        } catch(e) {
            console.error('✗ initProductZoom error:', e);
        }
        
        try {
            initNewsletterForm();
            console.log('✓ initNewsletterForm initialized');
        } catch(e) {
            console.error('✗ initNewsletterForm error:', e);
        }
        
        try {
            initProductImageSlider();
            console.log('✓ initProductImageSlider initialized');
        } catch(e) {
            console.error('✗ initProductImageSlider error:', e);
        }
        
        try {
            initSizeGuideToggle();
            console.log('✓ initSizeGuideToggle initialized');
        } catch(e) {
            console.error('✗ initSizeGuideToggle error:', e);
        }
        
        try {
            initLanguageSwitcher();
            console.log('✓ initLanguageSwitcher initialized');
        } catch(e) {
            console.error('✗ initLanguageSwitcher error:', e);
        }
        
        try {
            initReadMore();
            console.log('✓ initReadMore initialized');
        } catch(e) {
            console.error('✗ initReadMore error:', e);
        }
        
        // Prevent duplicate buttons after AJAX
        $(document.body).on('added_to_cart updated_wc_div', function() {
            setTimeout(preventDuplicateButtons, 100);
        });
        
        // Force checkout form fields to English
        function forceCheckoutFieldsEnglish() {
            // Force address field labels
            $('label[for*="address_1"], label:contains("ALAMAT JALAN"), label:contains("Alamat jalan"), label:contains("Street address")').each(function() {
                if ($(this).text().includes('ALAMAT JALAN') || $(this).text().includes('Alamat jalan') || $(this).text().includes('Street address')) {
                    $(this).text($(this).text().replace(/ALAMAT JALAN|Alamat jalan|Street address/gi, 'Address'));
                }
            });
            
            // Force address placeholders
            $('input[name*="address_1"]').each(function() {
                var placeholder = $(this).attr('placeholder');
                if (placeholder && (placeholder.includes('Nomor rumah') || placeholder.includes('nomor rumah'))) {
                    $(this).attr('placeholder', 'House number and street name');
                }
            });
            
            $('input[name*="address_2"]').each(function() {
                var placeholder = $(this).attr('placeholder');
                if (placeholder && (placeholder.includes('Apartemen') || placeholder.includes('apartemen'))) {
                    $(this).attr('placeholder', 'Apartment, suite, unit, etc. (optional)');
                }
            });
            
            // Hide newsletter subscription checkbox completely
            $('.woocommerce-checkout-newsletter-subscription, .woocommerce-newsletter-subscription, .newsletter-subscription').hide();
            $('label:has(input[name*="newsletter"]), label:has(input[id*="newsletter"])').hide();
            $('input[name*="newsletter"], input[id*="newsletter"]').closest('label, p, div').hide();
            
            // Hide Hostinger newsletter checkbox
            $('input[name="hostinger_reach_optin"], input[id="hostinger_reach_optin"]').closest('label, p, div').hide();
            $('label:has(input[name="hostinger_reach_optin"]), label:has(input[id="hostinger_reach_optin"])').hide();
            $('.hostinger-reach-optin__checkbox-text').closest('label, p, div').hide();
            
            // Hide any element containing newsletter subscription text
            $('label, span, p').filter(function() {
                var text = $(this).text();
                return text.includes('BERLANGGANAN BULETIN KAMI') || 
                       text.includes('Berlangganan buletin kami') || 
                       text.includes('Berlangganan Buletin') ||
                       text.includes('SUBSCRIBE TO OUR NEWSLETTER');
            }).each(function() {
                $(this).closest('label, p, div').hide();
            });
            
            // Force "Place order" button text
            $('button[name="woocommerce_checkout_place_order"], #place_order').each(function() {
                var text = $(this).text();
                var value = $(this).val();
                if (text.includes('BUAT PESANAN') || text.includes('Buat pesanan') || value && value.includes('BUAT PESANAN')) {
                    $(this).text('PLACE ORDER');
                    $(this).val('PLACE ORDER');
                    $(this).attr('data-value', 'PLACE ORDER');
                }
            });
            
            // Force payment method error message
            $('.woocommerce-info:contains("Maaf"), .woocommerce-info:contains("metode pembayaran")').each(function() {
                var text = $(this).text();
                if (text.includes('Maaf') && text.includes('metode pembayaran')) {
                    $(this).text('Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.');
                }
            });
            
            // Force coupon message
            $('.woocommerce-form-coupon-toggle, .showcoupon').each(function() {
                var text = $(this).text();
                if (text.includes('Punya kupon') || text.includes('Klik di sini untuk memasukkan kode')) {
                    $(this).html($(this).html().replace(/Punya kupon\?/gi, 'Have a coupon?').replace(/Klik di sini untuk memasukkan kode Anda/gi, 'Click here to enter your code'));
                }
            });
            
            // Force country names in dropdowns
            $('select[name*="country"] option').each(function() {
                var text = $(this).text();
                var countryMap = {
                    'Amerika Serikat': 'United States',
                    'Singapura': 'Singapore',
                    'Jepang': 'Japan',
                    'Korea Selatan': 'South Korea',
                    'Cina': 'China',
                    'Filipina': 'Philippines',
                    'Inggris': 'United Kingdom',
                    'Inggris Raya': 'United Kingdom'
                };
                if (countryMap[text]) {
                    $(this).text(countryMap[text]);
                }
            });
        }
        
        // Run on page load
        forceCheckoutFieldsEnglish();
        
        // Run after country change (WooCommerce updates fields dynamically)
        $(document.body).on('country_to_state_changing updated_checkout', function() {
            setTimeout(forceCheckoutFieldsEnglish, 100);
        });
        
        // Also watch for address field updates
        $(document).on('change', 'select[name*="country"]', function() {
            setTimeout(forceCheckoutFieldsEnglish, 200);
        });
        
        // Handle variable product button - redirect to product page
        $(document).on('click', '.variable-product-btn', function(e) {
            e.preventDefault();
            var productUrl = $(this).data('product-url');
            if (productUrl) {
                window.location.href = productUrl;
            }
        });
        
        console.log('=== ALL FUNCTIONS INITIALIZED ===');
    });

    // Initialize on Window Load
    $(window).on('load', function() {
        // Add loaded class to body
        $('body').addClass('loaded');
    });

})(jQuery);
