/**
 * Variation swatches (Color / Size) UI for WooCommerce variable products
 *
 * Keeps WooCommerce <select> in sync, so variation logic still works.
 */
(function ($) {
  'use strict';

  function findAttrSelect($form, keyword) {
    var $sel = $();
    $form.find('select[name^="attribute_"]').each(function () {
      var name = ($(this).attr('name') || '').toLowerCase();
      if (name.indexOf(keyword) !== -1) $sel = $sel.add($(this));
    });
    return $sel.first();
  }

  function syncButtonState($form, attrName) {
    var $select = $form.find('select[name="' + attrName + '"]');
    var val = $select.val();
    var $group = $form.find('.wpaugoose-attr[data-attr="' + attrName + '"]');
    $group.find('.wpaugoose-swatch').removeClass('is-active');
    if (val) {
      $group.find('.wpaugoose-swatch[data-value="' + val + '"]').addClass('is-active');
    }
  }

  function updateSizeGuide($form) {
    var $sizeSelect = findAttrSelect($form, 'size');
    var sizeVal = ($sizeSelect.val() || '').toString().toLowerCase();
    var $chart = $('.size-chart');
    if (!$chart.length) return;

    $chart.removeClass('has-selection');
    $chart.find('tbody tr').removeClass('is-selected');

    if (!sizeVal) return;

    // normalize: "xl" / "x-large" / etc -> keep simple
    sizeVal = sizeVal.replace(/[^a-z0-9]/g, '');
    var $row = $chart.find('tbody tr[data-size="' + sizeVal + '"]');
    if ($row.length) {
      $chart.addClass('has-selection');
      $row.addClass('is-selected');
    }
  }

  function updateGalleryImage(variation) {
    if (!variation || !variation.image || !variation.image.src) return;

    var $img = $('.woocommerce-product-gallery__wrapper img.wp-post-image').first();
    if (!$img.length) return;

    $img.attr('src', variation.image.src);
    if (variation.image.srcset) $img.attr('srcset', variation.image.srcset);
    if (variation.image.sizes) $img.attr('sizes', variation.image.sizes);
    if (variation.image.full_src) $img.attr('data-large_image', variation.image.full_src);
    if (variation.image.full_src_w) $img.attr('data-large_image_width', variation.image.full_src_w);
    if (variation.image.full_src_h) $img.attr('data-large_image_height', variation.image.full_src_h);
    if (variation.image.alt) $img.attr('alt', variation.image.alt);
  }

  $(document).on('click', '.wpaugoose-swatch', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var attrName = $btn.closest('.wpaugoose-attr').data('attr');
    var value = $btn.data('value');
    var $form = $btn.closest('form.variations_form');
    var $select = $form.find('select[name="' + attrName + '"]');
    if (!$select.length) return;

    $select.val(value).trigger('change');
    syncButtonState($form, attrName);
    updateSizeGuide($form);
  });

  $(document).on('click', 'a.reset_variations', function () {
    var $form = $(this).closest('form.variations_form');
    $form.find('.wpaugoose-swatch').removeClass('is-active');
    updateSizeGuide($form);
  });

  // When Woo updates variation selection, keep buttons in sync
  $(document.body).on('woocommerce_update_variation_values reset_data', function (e) {
    var $form = $(e.target).closest('form.variations_form');
    if (!$form.length) return;
    $form.find('.wpaugoose-attr').each(function () {
      var attrName = $(this).data('attr');
      syncButtonState($form, attrName);
    });
    updateSizeGuide($form);
  });

  // When a specific variation is found (after selecting Color/Size)
  $(document.body).on('found_variation', function (e, variation) {
    var $form = $(e.target).closest('form.variations_form');
    if ($form.length) updateSizeGuide($form);
    updateGalleryImage(variation);
  });
})(jQuery);

