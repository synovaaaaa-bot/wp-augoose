# Price Display Logic Flow (BULLETPROOF Implementation - FIXED)

## Prinsip Dasar

**Set currency SEKALI di awal request, jangan set ulang di fungsi price!**

Biarkan WCML + WooCommerce yang handle:
- Conversion (manual price atau auto convert)
- Tax display (inc/exc)
- Formatting (decimals, symbol)
- Variable product min/max price

## CRITICAL FIX: Masalah yang Diperbaiki

### ❌ Masalah Sebelumnya

1. **set_client_currency() dipanggil di tengah request** → bikin cache campur currency
2. **Manual price check salah** → `_wcml_custom_prices_status` itu flag, bukan nilai harga
3. **Harga beda (63 vs 64)** → karena currency context campur atau rounding/tax
4. **Geolocation "mati"** → selalu return '' karena hardcoded

### ✅ Solusi

1. **Set currency SEKALI di awal request** (hook `template_redirect` priority 10)
2. **Hapus semua set_client_currency() dari fungsi price**
3. **Hapus manual price check** → biarkan WCML handle
4. **Fix geolocation** → benar-benar call API dengan cache

## Flow Lengkap (FIXED)

### 1. Set Currency di Awal Request

**Hook**: `template_redirect` (priority 10)

**Fungsi**: `wp_augoose_set_currency_once()`

**Priority**:
1. **WCML cookie/session** (user manual select) - JANGAN override
2. Auto-detect dari geolocation (hanya first visit)
3. Base currency

**PENTING**: Set SEKALI di sini, jangan set ulang di fungsi price!

### 2. Geolocation (IP → Country)

- **Fungsi**: `wp_augoose_get_user_country_from_ip()`
- **Cache**: Cookie 24 jam + Transient 1 jam (untuk API call)
- **Fail-safe**: 
  - Jika API gagal, cache empty result 5 menit (jangan retry berkali-kali)
  - Timeout 3 detik
  - Skip localhost/private IPs
- **API**: ip-api.com (free, no API key)

### 3. Determine Currency (Tanpa Set)

- **Fungsi**: `wp_augoose_determine_currency()`
- **Hanya return currency code**, jangan set currency di sini
- Currency akan di-set di `wp_augoose_set_currency_once()`

### 4. Output Price HTML

- **Fungsi**: `wp_augoose_get_product_price_html()`
- **Cara**: Cukup `return $product->get_price_html()`
- **JANGAN set currency di sini!** Currency sudah di-set di awal request

## Yang TIDAK Boleh Dilakukan

### ❌ Jangan Set Currency di Tengah Request

```php
// SALAH - bisa bikin cache campur → harga beda (63 vs 64)
function wp_augoose_get_product_price_html( $product ) {
    $woocommerce_wpml->multi_currency->set_client_currency( 'USD' );
    return $product->get_price_html();
}
```

**Kenapa?**
- WCML/WooCommerce punya caching (terutama variation min/max price)
- Kalau currency di-set setelah produk sudah di-load, bisa dapat campuran:
  - Sebagian harga sudah ke-cache di currency A
  - Lalu kamu set currency B
  - Hasilnya: harga beda per komponen (wishlist vs listing vs single)

**Solusi**: Set currency SEKALI di awal request (template_redirect), lalu cukup panggil `get_price_html()`.

### ❌ Jangan Hitung Harga Manual untuk Variable Product

```php
// SALAH - bisa tidak sejalan dengan WCML
$regular_price = $product->get_variation_regular_price('min');
$sale_price = $product->get_variation_sale_price('min');
```

**Solusi**: Pakai `$product->get_price()` atau `get_price_html()` saja - biarkan WooCommerce + WCML handle.

### ❌ Jangan Hook `woocommerce_get_price_html` dengan Recursion

```php
// SALAH - akan infinite loop (fatal error / blank page)
add_filter('woocommerce_get_price_html', function($price_html, $product) {
    return $product->get_price_html(); // ← Memanggil lagi, infinite loop!
});
```

**Solusi**: Jangan hook `woocommerce_get_price_html` kalau di dalam hook kamu memanggil `get_price_html()` lagi.

### ❌ Jangan Check Manual Price (Salah Objek Meta)

```php
// SALAH - _wcml_custom_prices_status itu flag, bukan nilai harga
$manual_regular = get_post_meta( $product_id, '_wcml_custom_prices_status', true );
if ( $manual_regular ) {
    // "Manual price exists" ← ini misleading!
}
```

**Solusi**: Hapus seluruh "manual price check" dari theme. Biarkan WCML handle.

### ❌ Jangan Bikin Cookie Currency Sendiri

```php
// SALAH - bisa konflik dengan WCML
setcookie('my_currency', 'USD', ...);
```

**Solusi**: Cukup baca currency aktif dari WCML/woocommerce session.

## Kenapa Bisa 63 di Admin Tapi Tampil 64 di Front?

Ini paling sering terjadi karena salah satu dari ini:

### A) Rounding WCML

WCML punya setting rounding (misal "round to nearest integer" atau "round to 0 decimals").
Kalau hasil kalkulasi/formatting bikin 63.5 → dibulatkan jadi 64.

### B) Tax Inclusive/Exclusive

WooCommerce bisa display "including tax" di shop, tapi kamu input "regular price" yang mungkin "excluding tax".
Kalau ada tax rate, $63 bisa jadi $64.xx lalu dibulatkan tampil $64.00.

### C) Currency Context Campur (PALING SERING)

Di beberapa tempat kamu force set currency, di tempat lain WCML pakai currency dari cookie/session.
Ini bikin perbedaan output.

**Solusi**: Set currency SEKALI di awal request, jangan set ulang di fungsi price.

## Kesimpulan

**Tidak akan error** kalau:
1. ✅ Set currency SEKALI di awal request (template_redirect)
2. ✅ Tidak set currency di tengah request (fungsi price)
3. ✅ Tidak memaksa hitung harga sendiri untuk variable product
4. ✅ Geolocate di-cache dan fail-safe
5. ✅ Tidak bikin cookie currency sendiri kalau WCML sudah ada

**Implementasi paling "bulletproof"**:
1. Set currency SEKALI di awal request
2. Output harga: selalu `$product->get_price_html()`
3. Untuk variable product, jangan compute min/max sendiri
