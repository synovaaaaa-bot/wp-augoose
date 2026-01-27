# Sumber Rate Konversi Currency

## Dari Mana Rate Diambil?

Sistem mengambil **exchange rate (rate konversi)** dari **WCML (WooCommerce Multilingual)** plugin.

## Cara Kerja

### 1. Sumber Data
- **WCML Settings** → Multi-currency settings
- Rate yang Anda set di admin panel WCML (seperti yang terlihat di gambar)
- Rate ini bisa di-update **per jam** jika dikonfigurasi di WCML

### 2. Method yang Digunakan

Sistem menggunakan **2 method** dari WCML (prioritas):

#### **Method 1: `get_exchange_rates()` (PRIORITAS UTAMA)**
```php
$multi_currency = $woocommerce_wpml->multi_currency;
$exchange_rates = $multi_currency->get_exchange_rates();
```

**Keuntungan:**
- Mengambil **semua rates sekaligus** dalam satu array
- Rate **terbaru** dari WCML settings
- Update **per jam** jika WCML dikonfigurasi untuk auto-update

**Contoh data yang dikembalikan:**
```php
array(
    'USD' => 1.0,      // Base currency
    'SGD' => 0.000078, // 1 IDR = 0.000078 SGD
    'MYR' => 0.000244, // 1 IDR = 0.000244 MYR
    'IDR' => 1.0       // Default currency
)
```

#### **Method 2: `get_currency_rate()` (FALLBACK)**
```php
$item_rate = $multi_currency->get_currency_rate( 'SGD' );
$idr_rate = $multi_currency->get_currency_rate( 'IDR' );
```

**Digunakan jika:**
- `get_exchange_rates()` tidak tersedia
- Sebagai fallback method

### 3. Lokasi Kode

Rate diambil di **2 tempat**:

#### **A. Saat Add to Cart** (`wp_augoose_save_original_currency_to_cart_item`)
- File: `wp-augoose/inc/woocommerce.php` line ~4963-5008
- Mengambil rate saat item ditambahkan ke cart
- Menyimpan harga yang sudah dikonversi ke cart item data

#### **B. Saat Cart Calculation** (`wp_augoose_convert_cart_items_to_idr`)
- File: `wp-augoose/inc/woocommerce.php` line ~5200-5230
- Mengambil rate saat cart dihitung ulang
- Untuk item yang sudah ada di cart

### 4. Formula Konversi

```php
// Ambil rate dari WCML
$item_rate = $exchange_rates['SGD'];  // Contoh: 0.000078
$idr_rate = $exchange_rates['IDR'];    // Contoh: 1.0

// Hitung conversion rate
$conversion_rate = $idr_rate / $item_rate;
// Contoh: 1.0 / 0.000078 = 12,820.51

// Konversi harga
$price_idr = $displayed_price * $conversion_rate;
// Contoh: 74 SGD * 12,820.51 = 948,717.74 IDR
```

### 5. Update Rate

Rate akan **otomatis update** jika:
- WCML dikonfigurasi untuk **auto-update per jam**
- Admin mengupdate rate secara manual di WCML settings
- Sistem akan menggunakan rate terbaru saat:
  - Item ditambahkan ke cart
  - Cart dihitung ulang
  - Checkout

## Catatan Penting

1. **Rate selalu diambil dari WCML** - tidak ada hardcoded rate
2. **Rate terbaru** - menggunakan `get_exchange_rates()` yang mengambil data langsung dari WCML
3. **Konsisten dengan product page** - menggunakan rate yang sama dengan yang digunakan WCML untuk menampilkan harga di product page
4. **Update otomatis** - jika WCML update rate, sistem akan otomatis menggunakan rate baru

## Debug

Untuk melihat rate yang digunakan, aktifkan `WP_DEBUG` dan cek log:
```
WP_Augoose: Add to cart - Product #123, SGD 74 → IDR 948717.74 (item_rate: 0.000078, idr_rate: 1, conversion_rate: 12820.51)
```
