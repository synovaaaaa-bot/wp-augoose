# Penjelasan Fix: Checkout AJAX Returns HTML Instead of JSON

## Root Cause

**Masalah utama:** Theme custom hooks ke `wp_footer` dan `wp_head` yang output HTML tetap dieksekusi saat WooCommerce AJAX request (`?wc-ajax=update_order_review`), sehingga HTML tercampur dengan JSON response.

**Kenapa ini terjadi:**
1. WooCommerce menggunakan endpoint `?wc-ajax=update_order_review` untuk checkout AJAX
2. WordPress masih load theme dan fire hooks `wp_head` dan `wp_footer` bahkan untuk AJAX requests
3. Theme custom output HTML di hooks ini: `<div>`, `<style>`, `<script>`, dll
4. WooCommerce kemudian kirim JSON: `{"result":"success",...}`
5. Browser terima: `<!doctype html>...<div>...</div>{"result":"success"...}`
6. JavaScript coba parse sebagai JSON → `SyntaxError: Unexpected token '<'`

## Solusi

**Helper Function:** `augoose_is_wc_ajax_request()`
- Deteksi semua jenis WooCommerce AJAX requests
- Check `wp_doing_ajax()` + action name
- Check `wc-ajax` parameter
- Check `DOING_AJAX` constant
- Check REST API requests (optional)

**Guard di semua hooks yang output HTML:**
1. `wp_augoose_render_wishlist_sidebar()` - output `<div class="wishlist-sidebar">`
2. `wp_augoose_mini_cart_html()` - output `<div class="cart-sidebar-overlay">`
3. `wp_augoose_hide_newsletter_checkbox()` - output `<style>`
4. `wp_augoose_add_critical_css()` - output `<style>`
5. `wp_augoose_add_wishlist_handler_inline()` - output `<script>`
6. `wp_augoose_force_grid_layout()` - output `<script>`
7. Anonymous `wp_head` hook - output `<meta>`
8. `template_redirect` hook untuk cart redirect
9. `woocommerce_update_order_review_fragments` filter - improve output buffer handling

## Kenapa Ini Fix

1. **Prevent HTML Output:** Semua hooks yang output HTML sekarang skip saat AJAX
2. **Clean JSON Response:** WooCommerce bisa kirim JSON murni tanpa HTML prefix
3. **Backward Compatible:** Helper function check semua kondisi AJAX yang mungkin
4. **Minimal Changes:** Hanya tambah guard, tidak ubah logic existing
5. **Production Safe:** Tidak disable WooCommerce, tidak ubah UI

## Cara Test

### Test 1: Curl Request
```bash
curl -X POST "https://augoose.co/?wc-ajax=update_order_review" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "billing_first_name=Test&billing_country=US" \
  -v
```

**Expected Response:**
- `Content-Type: application/json`
- Body starts with `{"result":` (bukan `<!doctype html>`)
- Valid JSON structure

### Test 2: Browser DevTools
1. Buka checkout page
2. Open DevTools → Network tab
3. Ubah field billing (nama, negara, dll)
4. Cek request ke `?wc-ajax=update_order_review`
5. Verify:
   - Response Headers: `Content-Type: application/json`
   - Response Body: Valid JSON (bukan HTML)
   - No console errors

### Test 3: Error Log
```bash
tail -f /path/to/error_log
```
- Ubah field checkout
- Verify: Tidak ada PHP warnings yang corrupt output
- Verify: Tidak ada "Trying to access array offset" dari DOKU plugin

## Verification Checklist

- [ ] Curl test returns JSON (not HTML)
- [ ] Browser DevTools shows `Content-Type: application/json`
- [ ] Response body is valid JSON starting with `{`
- [ ] No console errors: `SyntaxError: Unexpected token '<'`
- [ ] No console errors: `Cannot read properties of undefined (reading 'toString')`
- [ ] Checkout form updates correctly when fields change
- [ ] Payment method selection works
- [ ] Order can be placed successfully
- [ ] Error log shows no PHP warnings during checkout
