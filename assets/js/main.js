/**
 * Main JavaScript file
 * 
 * @package Minimal_Ecommerce
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

    // Sticky Header
    function initStickyHeader() {
        const header = $('.site-header');
        const headerHeight = header.outerHeight();
        let lastScroll = 0;

        $(window).on('scroll', function() {
            const currentScroll = $(this).scrollTop();

            if (currentScroll > headerHeight) {
                header.addClass('scrolled');
            } else {
                header.removeClass('scrolled');
            }

            // Hide/Show header on scroll
            if (currentScroll > lastScroll && currentScroll > headerHeight * 2) {
                header.css('transform', 'translateY(-100%)');
            } else {
                header.css('transform', 'translateY(0)');
            }

            lastScroll = currentScroll;
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
                url: minimalEcommerce.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'quick_view_product',
                    product_id: productId,
                    nonce: minimalEcommerce.nonce
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

    // Add to Wishlist
    function initWishlist() {
        $(document).on('click', '.add-to-wishlist', function(e) {
            e.preventDefault();
            const button = $(this);
            const productId = button.data('product-id');

            button.addClass('loading');

            // Get existing wishlist from localStorage
            let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');

            // Check if product already in wishlist
            const index = wishlist.indexOf(productId);
            if (index > -1) {
                wishlist.splice(index, 1);
                button.removeClass('active');
                showNotification('Product removed from wishlist');
            } else {
                wishlist.push(productId);
                button.addClass('active');
                showNotification('Product added to wishlist');
            }

            // Save to localStorage
            localStorage.setItem('wishlist', JSON.stringify(wishlist));

            button.removeClass('loading');
        });

        // Initialize wishlist buttons
        function updateWishlistButtons() {
            const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
            $('.add-to-wishlist').each(function() {
                const productId = $(this).data('product-id');
                if (wishlist.includes(productId)) {
                    $(this).addClass('active');
                }
            });
        }

        updateWishlistButtons();
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
                url: minimalEcommerce.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'minimal_ecommerce_add_to_cart',
                    product_id: productId,
                    quantity: quantity,
                    nonce: minimalEcommerce.nonce
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
                url: minimalEcommerce.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'subscribe_newsletter',
                    email: email,
                    nonce: minimalEcommerce.nonce
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
        $('.size-guide-toggle').on('click', function() {
            $(this).toggleClass('active');
            $(this).next('.size-guide-content').slideToggle(300).toggleClass('open');
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
    $(document).ready(function() {
        initMobileMenu();
        initSearchToggle();
        initStickyHeader();
        initQuickView();
        initWishlist();
        initAjaxAddToCart();
        initQuantityInput();
        initSmoothScroll();
        initProductZoom();
        initNewsletterForm();
        initProductImageSlider(); // Auto-slide product images
        initSizeGuideToggle(); // Size guide toggle
    });

    // Initialize on Window Load
    $(window).on('load', function() {
        // Add loaded class to body
        $('body').addClass('loaded');
    });

})(jQuery);
