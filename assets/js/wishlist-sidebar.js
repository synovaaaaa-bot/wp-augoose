/**
 * Wishlist Sidebar (integrated with WP + WooCommerce)
 */
jQuery(function ($) {
  function openWishlist() {
    $('.wishlist-sidebar-overlay').fadeIn(150);
    $('.wishlist-sidebar').fadeIn(150);
    $('body').addClass('wishlist-open');
    refreshWishlist();
  }

  function closeWishlist() {
    $('.wishlist-sidebar-overlay').fadeOut(150);
    $('.wishlist-sidebar').fadeOut(150);
    $('body').removeClass('wishlist-open');
  }

  function setWishlistBadge(count) {
    const $badge = $('.wishlist-count');
    if (!$badge.length) return;
    if (count > 0) {
      $badge.text(count).show();
    } else {
      $badge.hide();
    }
  }

  function refreshWishlist() {
    if (!window.wpAugoose) return;
    const $body = $('.wishlist-sidebar-body');
    $body.addClass('is-loading');
    $.post(wpAugoose.ajaxUrl, { action: 'wp_augoose_wishlist_get', nonce: wpAugoose.nonce })
      .done(function (res) {
        if (res && res.success) {
          $body.html(res.data.html);
          setWishlistBadge(res.data.count || 0);
        }
      })
      .always(function () {
        $body.removeClass('is-loading');
      });
  }

  // Open/close
  $(document).on('click', '.wishlist-icon', function (e) {
    // if you want to navigate to /wishlist/, remove preventDefault and data-toggle logic
    e.preventDefault();
    openWishlist();
  });
  $(document).on('click', '.wishlist-sidebar-overlay, .wishlist-sidebar-close', function (e) {
    e.preventDefault();
    closeWishlist();
  });

  // Remove item
  $(document).on('click', '.wishlist-remove', function (e) {
    e.preventDefault();
    const productId = $(this).data('product-id');
    $.post(wpAugoose.ajaxUrl, { action: 'wp_augoose_wishlist_toggle', nonce: wpAugoose.nonce, product_id: productId })
      .done(function () {
        refreshWishlist();
        // also update heart buttons
        $('.add-to-wishlist[data-product-id="' + productId + '"]').removeClass('active');
      });
  });

  // Add to cart from wishlist
  $(document).on('click', '.wishlist-add-to-cart', function (e) {
    e.preventDefault();
    const productId = $(this).data('product-id');
    const $btn = $(this);
    $btn.prop('disabled', true);
    $.post(wpAugoose.ajaxUrl, { action: 'wp_augoose_add_to_cart', nonce: wpAugoose.nonce, product_id: productId, quantity: 1 })
      .done(function (res) {
        if (res && res.success) {
          // Refresh Woo fragments (cart count + mini cart etc.)
          $(document.body).trigger('wc_fragment_refresh');
        } else if (res && res.data && res.data.product_url) {
          window.location.href = res.data.product_url;
        }
      })
      .always(function () {
        $btn.prop('disabled', false);
      });
  });

  // Choose options (variable product)
  $(document).on('click', '.wishlist-choose-options', function () {
    closeWishlist();
  });
});

