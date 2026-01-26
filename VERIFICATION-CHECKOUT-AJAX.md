# Verifikasi: Checkout AJAX Fix Mengikuti Template WooCommerce

## ✅ Cara WooCommerce Handle wc-ajax

Berdasarkan `woocommerce/includes/class-wc-ajax.php`:

1. **Hook Priority:** `template_redirect` dengan priority **0** (sangat awal)
2. **Flow:**
   ```
   Request: ?wc-ajax=update_order_review
   ↓
   WC_AJAX::do_wc_ajax() (priority 0)
   ↓
   Set DOING_AJAX constant
   ↓
   Set headers (Content-Type: text/html)
   ↓
   Fire action: wc_ajax_update_order_review
   ↓
   WC_AJAX::update_order_review() → wp_send_json()
   ↓
   wp_die() → STOP execution
   ```

3. **PENTING:** WooCommerce **TIDAK** skip `wp_footer` dan `wp_head` hooks!
   - WooCommerce hanya stop dengan `wp_die()` setelah handler selesai
   - Tapi jika ada output buffer yang tidak dibersihkan, `wp_footer`/`wp_head` bisa di-fire

## ✅ Fix Kita Sudah Benar

### 1. Helper Function: `augoose_is_wc_ajax_request()`
**Lokasi:** `inc/woocommerce.php` line 21-57

**Check:**
- ✅ `wc-ajax` parameter (GET/POST/REQUEST) - **PENTING untuk endpoint WooCommerce**
- ✅ `wp_doing_ajax()` + WooCommerce action names
- ✅ `DOING_AJAX` constant
- ✅ REST API requests (optional)

**Ini mengikuti cara WooCommerce deteksi wc-ajax!**

### 2. Guards di wp_footer Hooks
**Lokasi:** `inc/woocommerce.php`

✅ `wp_augoose_render_wishlist_sidebar()` - line 1700 (perlu cek guard)
✅ `wp_augoose_mini_cart_html()` - line 1919 (guard ada)
✅ `wp_augoose_hide_newsletter_checkbox()` - line 2409 (guard ada)

**Status:** 2/3 hooks sudah di-guard. Perlu verifikasi `wp_augoose_render_wishlist_sidebar()`.

### 3. Output Buffer Cleaning
**Lokasi:** `inc/woocommerce.php` line 2521-2557

✅ `wp_augoose_clean_output_for_woocommerce_ajax()` 
- Clear output buffer untuk `wc-ajax` endpoint
- Clear output buffer untuk WooCommerce admin-ajax actions
- **Ini prevent HTML output sebelum WooCommerce kirim JSON**

### 4. Template Redirect Guard
**Lokasi:** `inc/woocommerce.php` line 139-168

✅ `wp_augoose_ensure_classic_checkout()` 
- Skip untuk `wc-ajax` requests
- **Ini prevent template override selama AJAX**

## ⚠️ Yang Perlu Diperbaiki

### 1. `wp_augoose_render_wishlist_sidebar()` - Perlu Guard
**Lokasi:** `inc/woocommerce.php` line 1700

**Current:**
```php
add_action( 'wp_footer', 'wp_augoose_render_wishlist_sidebar', 30 );
function wp_augoose_render_wishlist_sidebar() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	// ... output HTML
}
```

**Perlu:**
```php
function wp_augoose_render_wishlist_sidebar() {
	// CRITICAL: Skip during AJAX requests
	if ( augoose_is_wc_ajax_request() ) {
		return;
	}
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	// ... output HTML
}
```

### 2. `functions.php` line 724 - Perlu Guard
**Lokasi:** `functions.php` line 724-726

**Current:**
```php
add_action( 'wp_head', function() {
    echo '<meta name="wp-augoose-nonce" content="...">' . "\n";
}, 1 );
```

**Perlu:**
```php
add_action( 'wp_head', function() {
	// CRITICAL: Skip during AJAX requests
	if ( function_exists( 'augoose_is_wc_ajax_request' ) && augoose_is_wc_ajax_request() ) {
		return;
	}
    echo '<meta name="wp-augoose-nonce" content="...">' . "\n";
}, 1 );
```

## ✅ Yang User Hapus (OK)

User menghapus guard di:
- `wp_augoose_wrapper_start()` - **OK**, karena hook ini hanya untuk WooCommerce pages (is_checkout/is_cart), bukan untuk AJAX
- `wp_augoose_wrapper_end()` - **OK**, sama seperti di atas
- `wp_augoose_set_currency_once()` - **OK**, karena ada logic lain yang handle ini

## 🧪 Test Verification

### Test 1: Curl
```bash
curl -X POST "https://augoose.co/?wc-ajax=update_order_review" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "billing_first_name=Test&billing_country=US" \
  -v 2>&1 | grep -E "(Content-Type|^\{)"
```

**Expected:**
- `Content-Type: application/json` (atau `text/html` dari WooCommerce header, tapi body harus JSON)
- Response body starts with `{"result":`

### Test 2: Browser DevTools
1. Buka checkout page
2. F12 → Network tab
3. Ubah field billing
4. Cek request `?wc-ajax=update_order_review`
5. Verify:
   - Response body is valid JSON (bukan HTML)
   - No console errors

## 📋 Checklist Final

- [x] Helper function check `wc-ajax` parameter ✅
- [x] Output buffer cleaning untuk wc-ajax ✅
- [x] Template redirect guard ✅
- [ ] `wp_augoose_render_wishlist_sidebar()` guard ⚠️
- [ ] `functions.php` wp_head guard ⚠️
- [x] `wp_augoose_mini_cart_html()` guard ✅
- [x] `wp_augoose_hide_newsletter_checkbox()` guard ✅

## 🎯 Kesimpulan

**Fix sudah 90% benar dan mengikuti template WooCommerce!**

Yang masih perlu:
1. Tambah guard di `wp_augoose_render_wishlist_sidebar()`
2. Tambah guard di `functions.php` wp_head hook

Setelah itu, fix akan 100% compatible dengan WooCommerce AJAX handling.
