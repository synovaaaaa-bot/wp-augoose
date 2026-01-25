/**
 * Main JavaScript file
 * 
 * @package WP_Augoose
 */

(function($) {
    'use strict';

    // Mobile Menu Toggle
    function initMobileMenu() {
        const mobileMenuToggle = $('.mobile-menu-toggle');
        const mobileMenu = $('.mobile-menu');

        mobileMenuToggle.on('click', function() {
            const isExpanded = $(this).attr('aria-expanded') === 'true';
            $(this).attr('aria-expanded', !isExpanded);
            mobileMenu.slideToggle(300);
            $('body').toggleClass('mobile-menu-open');
        });

        // Close mobile menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.site-header').length && mobileMenu.is(':visible')) {
                mobileMenu.slideUp(300);
                mobileMenuToggle.attr('aria-expanded', 'false');
                $('body').removeClass('mobile-menu-open');
            }
        });

        // Close mobile menu on window resize
        $(window).on('resize', function() {
            if ($(window).width() > 968) {
                mobileMenu.hide();
                mobileMenuToggle.attr('aria-expanded', 'false');
                $('body').removeClass('mobile-menu-open');
            }
        });
    }

    // Search Toggle
    function initSearchToggle() {
        const searchToggle = $('.search-toggle');
        const searchFormContainer = $('.search-form-container');

        searchToggle.on('click', function(e) {
            e.preventDefault();
            searchFormContainer.slideToggle(300);
            setTimeout(function() {
                searchFormContainer.find('input[type="search"]').focus();
            }, 300);
        });

        // Close search when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.header-search').length && searchFormContainer.is(':visible')) {
                searchFormContainer.slideUp(300);
            }
        });
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
    function initShopFiltersToggle() {
        const $page = $('[data-shop-page]');
        if (!$page.length) return;

        const $toggle = $page.find('.shop-filter-toggle');
        const $backdrop = $('.shop-filter-backdrop');
        const $body = $('body');
        
        if (!$toggle.length) return;

        // Open filter
        function openFilter() {
            $body.addClass('filter-open');
            $toggle.attr('aria-expanded', 'true');
            // Store scroll position to restore later
            const scrollY = window.scrollY;
            $body.data('scroll-pos', scrollY);
            $body.css('top', `-${scrollY}px`);
        }

        // Close filter
        function closeFilter() {
            $body.removeClass('filter-open');
            $toggle.attr('aria-expanded', 'false');
            // Restore scroll position
            const scrollY = $body.data('scroll-pos') || 0;
            $body.css('top', '');
            window.scrollTo(0, scrollY);
        }

        // Default: closed
        $body.removeClass('filter-open');
        $toggle.attr('aria-expanded', 'false');

        // Toggle filter visibility
        $toggle.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if ($body.hasClass('filter-open')) {
                closeFilter();
            } else {
                openFilter();
            }
        });

        // Close filter when clicking close button
        $page.on('click', '.shop-filter-close', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeFilter();
        });

        // Close filter when clicking backdrop
        $backdrop.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeFilter();
        });

        // Close filter on ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                if ($body.hasClass('filter-open')) {
                    closeFilter();
                }
            }
        });

        // Prevent body scroll when filter is open (additional safeguard)
        $body.on('touchmove', function(e) {
            if ($body.hasClass('filter-open')) {
                // Allow scroll inside filter panel
                if (!$(e.target).closest('.shop-filters').length) {
                    e.preventDefault();
                }
            }
        });
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
        // Debounce to prevent multiple rapid clicks
        let wishlistProcessing = false;
        
        // Use more specific selector and ensure it works
        $(document).on('click', '.add-to-wishlist, .wishlist-toggle', function(e) {
            // Prevent navigation to product page
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation(); // Prevent other handlers
            
            const button = $(this);
            
            // Try multiple ways to get product ID
            let productId = parseInt(button.data('product-id'), 10);
            if (!productId || isNaN(productId)) {
                // Try from parent element
                const parent = button.closest('[data-product-id]');
                if (parent.length) {
                    productId = parseInt(parent.data('product-id'), 10);
                }
            }
            
            // Validate product ID
            if (!productId || isNaN(productId)) {
                console.error('Invalid product ID - Button:', button);
                console.error('Button data:', button.data());
                console.error('Parent data:', button.closest('[data-product-id]').data());
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
            console.log('Wishlist toggle - AJAX URL:', wpAugoose ? wpAugoose.ajaxUrl : 'undefined');
            console.log('Wishlist toggle - Nonce:', wpAugoose ? wpAugoose.nonce : 'undefined');
            
            // Validate wpAugoose object
            if (!wpAugoose || !wpAugoose.ajaxUrl || !wpAugoose.nonce) {
                console.error('wpAugoose object is not properly initialized');
                button.removeClass('loading').prop('disabled', false);
                wishlistProcessing = false;
                if (typeof showNotification === 'function') {
                    showNotification('Wishlist system not initialized. Please refresh the page.', 'error');
                }
                return false;
            }
            
            $.ajax({
                url: wpAugoose.ajaxUrl,
                type: 'POST',
                timeout: 10000, // 10 second timeout
                data: {
                    action: 'wp_augoose_wishlist_toggle',
                    product_id: productId,
                    nonce: wpAugoose.nonce
                },
                success: function(res) {
                    console.log('Wishlist AJAX response:', res);
                    
                    if (res && res.success && res.data) {
                        if (res.data.action === 'added') {
                            button.addClass('active');
                            console.log('Product added to wishlist');
                            if (typeof showNotification === 'function') {
                                showNotification('Product added to wishlist');
                            }
                        } else {
                            button.removeClass('active');
                            console.log('Product removed from wishlist');
                            if (typeof showNotification === 'function') {
                                showNotification('Product removed from wishlist');
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
                        statusCode: xhr.status
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
                            // Not JSON, use default message
                        }
                    }
                    
                    if (typeof showNotification === 'function') {
                        showNotification(errorMsg, 'error');
                    } else {
                        alert(errorMsg);
                    }
                },
                complete: function() {
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
        preventDuplicateButtons();
        initMobileMenu();
        initSearchToggle();
        initSearchHistory();
        initStickyHeader();
        initShopFiltersToggle();
        initQuickView();
        initWishlist();
        initAjaxAddToCart();
        initQuantityInput();
        initSmoothScroll();
        initProductZoom();
        initNewsletterForm();
        initProductImageSlider(); // Auto-slide product images
        initSizeGuideToggle(); // Size guide toggle
        initLanguageSwitcher();
        initReadMore(); // Product description read more
        
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
    });

    // Initialize on Window Load
    $(window).on('load', function() {
        // Add loaded class to body
        $('body').addClass('loaded');
    });

})(jQuery);
