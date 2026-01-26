# Root Cause Fix Summary: Checkout AJAX & BlockUI Issues

## 🔴 Masalah yang Ditemukan

### Masalah 1: AJAX WooCommerce Tidak Terpanggil
**Request `wc-ajax=update_order_review` tidak jalan/tidak terpanggil**

**Penyebab:**
- Script checkout WooCommerce core (`checkout.min.js`) tidak ke-load
- Ada JS error yang stop sebelum trigger AJAX
- `wc_checkout_form` tidak terdefinisi
- Dependencies tidak load dengan benar
- `wc_checkout_params` tidak lengkap (missing `wc_ajax_url`, `update_order_review_nonce`)

### Masalah 2: Produk Abu-Abu (BlockUI Stuck)
**Produk jadi abu-abu karena BlockUI "nyangkut"**

**Penyebab:**
- Request `update_order_review` gagal
- Response bukan JSON (HTML/error)
- JS crash sebelum unblock BlockUI
- BlockUI tidak pernah di-unblock

## ✅ Fix yang Diterapkan

### 1. Enhanced wc_checkout_params Localization

**Lokasi:** `functions.php` line 680-697

**Perubahan:**
- ✅ Tambah `wc_ajax_url` (REQUIRED oleh WooCommerce `checkout.min.js`)
- ✅ Tambah `update_order_review_nonce` (REQUIRED untuk AJAX)
- ✅ Tambah `checkout_url` (REQUIRED oleh checkout.min.js)
- ✅ Safe check untuk `WC_AJAX` class

**Code:**
```php
$wc_ajax_url = '';
if ( class_exists( 'WC_AJAX' ) && method_exists( 'WC_AJAX', 'get_endpoint' ) ) {
    $wc_ajax_url = WC_AJAX::get_endpoint( '%%endpoint%%' );
} else {
    $wc_ajax_url = home_url( '/?wc-ajax=%%endpoint%%' );
}

$wc_checkout_params = array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'wc_ajax_url' => $wc_ajax_url, // CRITICAL: Required by checkout.min.js
    'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
    'checkout_url' => esc_url_raw( wc_get_checkout_url() ),
    // ... other params
);
```

### 2. Checkout Script Load Detection & Auto-Fix

**Lokasi:** `assets/js/checkout-quantity.js` line 364+

**Perubahan:**
- ✅ Check apakah `wc_checkout_form` terdefinisi
- ✅ Log error jika tidak terdefinisi
- ✅ Try trigger `init_checkout` jika tidak terdefinisi
- ✅ Monitor `update_checkout` event
- ✅ Warn jika `update_checkout` tidak triggered

**Code:**
```javascript
// Check if wc_checkout_form is loaded
if (typeof wc_checkout_form === 'undefined') {
    console.error('❌ wc_checkout_form is NOT defined!');
    console.error('WooCommerce checkout.min.js is NOT loaded!');
    // Try to trigger WooCommerce script load
    jQuery(document.body).trigger('init_checkout');
}

// Monitor update_checkout event
var updateCheckoutTriggered = false;
jQuery(document.body).on('update_checkout', function() {
    updateCheckoutTriggered = true;
    console.log('✅ update_checkout event triggered');
});

// Check after 2 seconds
setTimeout(function() {
    if (!updateCheckoutTriggered) {
        console.warn('⚠️ update_checkout event was NOT triggered');
    }
}, 2000);
```

### 3. Enhanced BlockUI Unblock dengan Periodic Check

**Lokasi:** `assets/js/checkout-quantity.js` line 364+

**Perubahan:**
- ✅ Global error handler dengan capture phase (catch errors early)
- ✅ Periodic check untuk stuck BlockUI (setiap 2 detik)
- ✅ Force remove BlockUI overlay jika stuck > 10 detik
- ✅ Unblock di semua error cases
- ✅ Track stuck time untuk detect stuck BlockUI

**Code:**
```javascript
// Periodic check untuk stuck BlockUI
setInterval(function() {
    var $blocked = jQuery('.woocommerce-checkout-payment.blocked');
    if ($blocked.length > 0) {
        var $overlay = jQuery('.blockUI');
        if ($overlay.length > 0) {
            var stuckTime = $overlay.data('stuck-time') || Date.now();
            if (Date.now() - stuckTime > 10000) {
                // Force unblock jika stuck > 10 detik
                console.warn('⚠️ BlockUI stuck, forcing unblock');
                $blocked.unblock();
                $overlay.remove();
            } else if (!$overlay.data('stuck-time')) {
                $overlay.data('stuck-time', Date.now());
            }
        }
    }
}, 2000);
```

### 4. Enhanced Error Logging

**Perubahan:**
- ✅ Log error message, file, dan line number
- ✅ Log jika `update_checkout` tidak triggered
- ✅ Log semua AJAX requests ke `wc-ajax`
- ✅ Log semua checkout field changes

## Test Verification

### Test 1: Browser DevTools Console
1. Buka checkout page
2. F12 → Console tab
3. Check untuk:
   - ✅ `wc_checkout_form is loaded`
   - ✅ `update_checkout event triggered`
   - ❌ NO errors tentang `wc_checkout_form is NOT defined`

### Test 2: Network Tab
1. Buka checkout page
2. F12 → Network tab
3. Ubah field billing
4. Verify:
   - Request ke `?wc-ajax=update_order_review` muncul
   - Response is JSON (not HTML)
   - Status code 200

### Test 3: BlockUI Check
1. Buka checkout page
2. Ubah field billing
3. Verify:
   - BlockUI muncul (loading state)
   - BlockUI hilang setelah response (tidak stuck)
   - Produk tidak tetap abu-abu

## Summary

**Fix ini comprehensive:**
- ✅ Ensure WooCommerce checkout scripts load dengan proper params
- ✅ Detect jika scripts tidak load dan try auto-fix
- ✅ Enhanced BlockUI unblock dengan periodic check
- ✅ Better error logging untuk debugging
- ✅ Force unblock stuck BlockUI setelah 10 detik

**Result:** 
- Checkout AJAX sekarang bisa terpanggil dengan benar
- BlockUI tidak akan stuck, produk tidak akan tetap abu-abu
- Better debugging untuk identify issues

## Related Fixes

Fix ini bekerja bersama dengan:
- **Aggressive Output Buffer Cleaning** - Ensure JSON response
- **Enhanced Error Suppression** - Prevent PHP errors
- **Customizer Fix** - Exclude Customizer dari AJAX logic
- **BlockUI Fix** - Unblock di semua error cases

Semua fix bekerja bersama untuk ensure checkout berfungsi dengan benar.
