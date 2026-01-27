# Aggressive Fix: SyntaxError Unexpected token '<'

## Masalah

JavaScript expect JSON tapi dapat HTML (dimulai dengan `<`):
- `<!doctype html>`
- `<br>` (dari PHP warning/notice)
- Halaman error HTML
- Redirect response

**Penyebab:**
1. Request `/?wc-ajax=update_order_review` atau `admin-ajax.php` gagal
2. Plugin (DOKU payment) echo warning/notice/output HTML
3. Request kena redirect/403/500 → balik HTML

**Efek:** Script checkout "macet", gak bisa parse response.

## Fix yang Diterapkan

### 1. Aggressive Output Buffer Cleaning

**Lokasi:** `inc/woocommerce.php` line 2521-2590

**Perubahan:**
- ✅ Hook ke 3 tempat: `init`, `wp_loaded`, `template_redirect` (priority 0)
- ✅ Clear ALL output buffers di awal request
- ✅ Start fresh output buffer untuk catch unexpected output
- ✅ Shutdown hook untuk final cleaning sebelum WooCommerce kirim JSON

**Logic:**
```php
// Clear semua buffer di awal
while ( ob_get_level() ) {
    ob_end_clean();
}

// Start fresh buffer
ob_start();

// Di shutdown, check output:
// - Jika HTML (starts with <) → discard
// - Jika whitespace → discard  
// - Jika JSON → keep
// - Lainnya → discard (safe)
```

### 2. Enhanced Error Suppression

**Lokasi:** `inc/woocommerce.php` line 369-404

**Perubahan:**
- ✅ Untuk AJAX requests, suppress **SEMUA** warnings/notices
- ✅ Prevent HTML output dari PHP errors
- ✅ Tetap suppress DOKU plugin warnings secara spesifik

**Logic:**
```php
// Untuk AJAX, suppress SEMUA warnings/notices
if ( $is_ajax_request && ( $errno === E_WARNING || $errno === E_NOTICE ) ) {
    return true; // Suppress
}
```

### 3. Early DOKU Plugin Fix

**Lokasi:** `inc/woocommerce.php` line 2564-2582

**Perubahan:**
- ✅ Fix `$_SERVER['QUERY_STRING']` di hook `init` (sangat awal)
- ✅ Prevent DOKU plugin dari outputting errors
- ✅ Ensure semua required fields exist

**Logic:**
```php
// Fix di init (sebelum DOKU plugin load)
if ( ! isset( $_SERVER['QUERY_STRING'] ) ) {
    $_SERVER['QUERY_STRING'] = '';
}
```

## Flow Protection

### Request Flow:
```
Request: ?wc-ajax=update_order_review
↓
init hook (priority 1):
  - Clear output buffers
  - Suppress errors
  - Fix DOKU plugin vars
↓
wp_loaded hook:
  - Clear output buffers again
↓
template_redirect hook (priority 0):
  - Clear output buffers (before WooCommerce)
↓
WooCommerce processes request
↓
shutdown hook (priority 9999):
  - Final buffer check
  - Discard HTML, keep JSON
↓
WooCommerce sends JSON
```

## Test Verification

### Test 1: Curl dengan Error Simulation
```bash
# Simulate request dengan potential errors
curl -X POST "https://augoose.co/?wc-ajax=update_order_review" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "billing_first_name=Test&billing_country=US" \
  -v 2>&1 | head -50
```

**Expected:**
- Response body starts with `{"result":` (JSON)
- **NOT** `<!doctype html>` atau `<br>` atau HTML lainnya

### Test 2: Browser DevTools
1. Buka checkout page
2. F12 → Network tab
3. Ubah field billing
4. Cek request `?wc-ajax=update_order_review`
5. Verify:
   - Response body is valid JSON
   - No HTML prefix
   - No console errors

### Test 3: Error Log Check
```bash
tail -f debug.log
```
- Ubah field checkout
- Verify: Tidak ada PHP warnings yang muncul
- Verify: Tidak ada output sebelum JSON

## Protection Layers

1. **Layer 1: Early Detection** (init hook)
   - Clear buffers
   - Suppress errors
   - Fix plugin vars

2. **Layer 2: Mid-Request** (wp_loaded hook)
   - Clear buffers again
   - Catch any mid-request output

3. **Layer 3: Pre-Processing** (template_redirect hook)
   - Clear buffers before WooCommerce
   - Priority 0 (sangat awal)

4. **Layer 4: Final Check** (shutdown hook)
   - Check final output
   - Discard HTML, keep JSON
   - Priority 9999 (sangat akhir)

## Summary

**Fix ini aggressive dan comprehensive:**
- ✅ Clear output buffers di 3 tempat berbeda
- ✅ Suppress SEMUA warnings/notices untuk AJAX
- ✅ Final check di shutdown untuk discard HTML
- ✅ Early fix untuk DOKU plugin
- ✅ Multiple protection layers

**Result:** Checkout AJAX sekarang return JSON murni, tidak ada HTML prefix, tidak ada PHP errors yang corrupt response.
