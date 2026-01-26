# Fix: WordPress Customizer Request Bukan AJAX WooCommerce

## 🔴 Root Cause

**Request dengan `customize_changeset_uuid` parameter:**
```
checkout-2/?customize_changeset_uuid=...
```

**Ini BUKAN endpoint AJAX WooCommerce!**

Ini adalah **WordPress Customizer request**, bukan AJAX request. Customizer menggunakan parameter `customize_changeset_uuid` untuk preview changes, tapi ini bukan AJAX request.

## Masalah

Code kita sebelumnya menganggap semua request dengan parameter tertentu adalah AJAX, sehingga:
1. Output buffer cleaning di-trigger untuk Customizer requests
2. Error suppression di-trigger untuk Customizer requests
3. BlockUI logic mungkin di-trigger untuk Customizer requests
4. Customizer tidak bisa bekerja dengan benar

## Fix yang Diterapkan

### 1. Exclude Customizer dari Helper Function

**Lokasi:** `inc/woocommerce.php` - `augoose_is_wc_ajax_request()`

**Perubahan:**
- ✅ Check `customize_changeset_uuid` parameter di awal
- ✅ Check `is_customize_preview()` function
- ✅ Return `false` jika ini Customizer request

**Code:**
```php
function augoose_is_wc_ajax_request() {
    // CRITICAL: Exclude WordPress Customizer requests
    if ( isset( $_REQUEST['customize_changeset_uuid'] ) || 
         isset( $_GET['customize_changeset_uuid'] ) || 
         isset( $_POST['customize_changeset_uuid'] ) ||
         is_customize_preview() ) {
        return false; // This is Customizer, not WooCommerce AJAX
    }
    // ... rest of function
}
```

### 2. Exclude Customizer dari Output Buffer Cleaning

**Lokasi:** `inc/woocommerce.php` - `wp_augoose_clean_output_for_woocommerce_ajax()`

**Perubahan:**
- ✅ Early return jika detect Customizer request
- ✅ Don't interfere dengan Customizer output

**Code:**
```php
function wp_augoose_clean_output_for_woocommerce_ajax() {
    // CRITICAL: Exclude WordPress Customizer requests
    if ( isset( $_REQUEST['customize_changeset_uuid'] ) || 
         is_customize_preview() ) {
        return; // Don't interfere with Customizer
    }
    // ... rest of function
}
```

### 3. Exclude Customizer dari Error Suppression

**Lokasi:** `inc/woocommerce.php` - `wp_augoose_suppress_harmless_warnings()`

**Perubahan:**
- ✅ Early return jika detect Customizer request
- ✅ Customizer needs to see warnings/notices for debugging

**Code:**
```php
function wp_augoose_suppress_harmless_warnings() {
    // CRITICAL: Exclude WordPress Customizer requests
    if ( isset( $_REQUEST['customize_changeset_uuid'] ) || 
         is_customize_preview() ) {
        return; // Don't suppress warnings in Customizer
    }
    // ... rest of function
}
```

### 4. Exclude Customizer dari Template Redirect

**Lokasi:** `inc/woocommerce.php` - `wp_augoose_ensure_classic_checkout()`

**Perubahan:**
- ✅ Early return jika detect Customizer request
- ✅ Customizer needs to work normally

**Code:**
```php
function wp_augoose_ensure_classic_checkout() {
    // CRITICAL: Skip for WordPress Customizer requests
    if ( isset( $_REQUEST['customize_changeset_uuid'] ) || 
         is_customize_preview() ) {
        return; // Don't interfere with Customizer
    }
    // ... rest of function
}
```

## Detection Logic

**Customizer Detection:**
1. Check `$_REQUEST['customize_changeset_uuid']` parameter
2. Check `$_GET['customize_changeset_uuid']` parameter
3. Check `$_POST['customize_changeset_uuid']` parameter
4. Check `is_customize_preview()` WordPress function

**Jika salah satu true → Ini Customizer, bukan AJAX → Skip semua AJAX-specific logic**

## Test Verification

### Test 1: Customizer Request
```bash
# Simulate Customizer request
curl "https://augoose.co/checkout-2/?customize_changeset_uuid=test123" \
  -v 2>&1 | head -30
```

**Expected:**
- Response is normal HTML (not JSON)
- No output buffer cleaning interference
- No error suppression
- Customizer works normally

### Test 2: WooCommerce AJAX Request
```bash
# Simulate WooCommerce AJAX request
curl -X POST "https://augoose.co/?wc-ajax=update_order_review" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "billing_first_name=Test" \
  -v 2>&1 | head -30
```

**Expected:**
- Response is JSON (not HTML)
- Output buffer cleaning works
- Error suppression works
- AJAX works normally

## Summary

**Fix ini critical:**
- ✅ Exclude Customizer dari semua AJAX-specific logic
- ✅ Customizer bisa bekerja normal
- ✅ WooCommerce AJAX tetap berfungsi
- ✅ No false positives

**Result:** Customizer dan WooCommerce AJAX sekarang tidak saling interfere.

## Related Fixes

Fix ini bekerja bersama dengan:
- **Aggressive Output Buffer Cleaning** - Hanya untuk AJAX, bukan Customizer
- **Enhanced Error Suppression** - Hanya untuk AJAX, bukan Customizer
- **BlockUI Fix** - Hanya untuk AJAX, bukan Customizer

Semua fix sekarang properly exclude Customizer requests.
