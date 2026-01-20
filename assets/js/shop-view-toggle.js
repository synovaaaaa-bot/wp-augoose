/**
 * Shop view toggle (grid/list)
 */
(function ($) {
  'use strict';

  function applyView(view) {
    var $grid = $('.woocommerce ul.products');
    $grid.removeClass('view-grid view-list');
    $grid.addClass(view === 'list' ? 'view-list' : 'view-grid');
    $('.shop-view-toggle button').removeClass('is-active');
    $('.shop-view-toggle button[data-view="' + (view === 'list' ? 'list' : 'grid') + '"]').addClass('is-active');
  }

  $(function () {
    var saved = localStorage.getItem('wpaugoose_shop_view') || 'grid';
    applyView(saved);

    $(document).on('click', '.shop-view-toggle button', function () {
      var view = $(this).data('view') || 'grid';
      localStorage.setItem('wpaugoose_shop_view', view);
      applyView(view);
    });
  });
})(jQuery);

