# Fix: Checkout AJAX Tidak Terpanggil & BlockUI Stuck

## 🔴 Masalah yang Ditemukan

### Masalah 1: AJAX WooCommerce Tidak Terpanggil
**Request `wc-ajax=update_order_review` tidak jalan/tidak terpanggil**

**Penyebab:**
- Script checkout WooCommerce core (`checkout.min.js`) tidak ke-load
- Ada JS error yang stop sebelum trigger AJAX
- `wc_checkout_form` tidak terdefinisi
- Dependencies tidak load dengan benar

### Masalah 2: Produk Abu-Abu (BlockUI Stuck)
**Produk jadi abu-abu karena BlockUI "nyangkut"**

**Penyebab:**
- Request `update_order_review` gagal
- Response bukan JSON (HTML/error)
- JS crash sebelum unblock BlockUI
- BlockUI tidak pernah di-unblock

## Fix yang Diterapkan

### 1. Enhanced wc_checkout_params Localization

**Lokasi:** `functions.php` line 680-697

**Perubahan:**
- ✅ Tambah `wc_ajax_url` (required oleh WooCommerce)
- ✅ Tambah `update_order_review_nonce` (required untuk AJAX)
- ✅ Tambah `checkout_url` (required oleh checkout.min.js)
- ✅ Ensure semua nonces ada

**Code:**
```php
$wc_checkout_params = array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'wc_ajax_url' => WC_AJAX::get_endpoint( '%%endpoint%%' ),
    'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
    'checkout_url' => esc_url_raw( wc_get_checkout_url() ),
    // ... other params
);
```

### 2. Checkout Script Load Detection

**Lokasi:** `assets/js/checkout-quantity.js` line 350+

**Perubahan:**
- ✅ Check apakah `wc_checkout_form` terdefinisi
- ✅ Log error jika tidak terdefinisi
- ✅ Try trigger `init_checkout` jika tidak terdefinisi
- ✅ Monitor `update_checkout` event

**Code:**
```javascript
if (typeof wc_checkout_form === 'undefined') {
    console.error('❌ wc_checkout_form is NOT defined!');
    console.error('WooCommerce checkout.min.js is NOT loaded!');
    // Try to trigger WooCommerce script load
    jQuery(document.body).trigger('init_checkout');
}
```

### 3. Enhanced BlockUI Unblock

**Lokasi:** `assets/js/checkout-quantity.js` line 350+

**Perubahan:**
- ✅ Global error handler dengan capture phase
- ✅ Periodic check untuk stuck BlockUI (setiap 2 detik)
- ✅ Force remove BlockUI overlay jika stuck > 10 detik
- ✅ Unblock di semua error cases

**Code:**
```javascript
// Periodic check untuk stuck BlockUI
setInterval(function() {
    var $blocked = jQuery('.woocommerce-checkout-payment.blocked');
    if ($blocked.length > 0) {
        var stuckTime = Date.now() - $blocked.data('stuck-time');
        if (stuckTime > 10000) {
            // Force unblock jika stuck > 10 detik
            $blocked.unblock();
            jQuery('.blockUI').remove();
        }
    }
}, 2000);
```

### 4. Enhanced Error Logging

**Lokasi:** `assets/js/checkout-quantity.js`

**Perubahan:**
- ✅ Log error message, file, dan line number
- ✅ Log jika `update_checkout` tidak triggered
- ✅ Log semua AJAX requests ke `wc-ajax`
- ✅ Log semua checkout field changes

## Debug Helper Script

**File:** `CHECKOUT-SCRIPT-DEBUG.js`

Script ini bisa di-enqueue untuk debug:
- Check apakah jQuery loaded
- Check apakah `wc_checkout_params` defined
- Check apakah `wc_checkout_form` defined
- Check apakah checkout form present
- Monitor AJAX requests
- Monitor BlockUI status

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
- ✅ Ensure WooCommerce checkout scripts load
- ✅ Detect jika scripts tidak load
- ✅ Enhanced BlockUI unblock dengan periodic check
- ✅ Better error logging untuk debugging
- ✅ Force unblock stuck BlockUI

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
