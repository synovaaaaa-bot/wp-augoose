# Panduan Setup Attribute untuk Filter Size dan Color

## Data yang Diperlukan

Agar filter **Size** dan **Color** muncul di halaman shop, Anda perlu:

### 1. Buat Global Attribute di WooCommerce

1. Buka **WooCommerce > Products > Attributes**
2. Buat 2 attribute baru:
   - **Name**: `Size` (atau `size`)
   - **Slug**: `size` (otomatis)
   - **Type**: `Select`
   
   - **Name**: `Color` (atau `color` atau `colour`)
   - **Slug**: `color` (otomatis)
   - **Type**: `Select`

3. Klik **Save attributes**

### 2. Tambahkan Terms (Values) ke Attribute

Setelah attribute dibuat, klik **Configure terms** untuk menambahkan values:

**Untuk Size:**
- S
- M
- L
- XL
- XXL
- (atau ukuran lainnya sesuai kebutuhan)

**Untuk Color:**
- Black
- White
- Red
- Blue
- Green
- (atau warna lainnya sesuai kebutuhan)

### 3. Assign Attribute ke Produk

1. Edit produk di **Products > All Products**
2. Buka tab **Attributes**
3. Pilih attribute dari dropdown (misalnya `Size` atau `Color`)
4. Klik **Add**
5. Centang terms yang tersedia (misalnya untuk Size: S, M, L)
6. Pastikan **Visible on the product page** dicentang
7. Klik **Save attributes**
8. **Update** produk

### 4. Verifikasi

Filter akan muncul jika:
- ✅ Attribute `Size` atau `Color` sudah dibuat di WooCommerce
- ✅ Attribute memiliki terms (values) yang sudah di-assign ke produk
- ✅ Minimal ada 1 produk yang memiliki attribute tersebut
- ✅ Produk tersebut terlihat di halaman shop/kategori

## Catatan Penting

- Attribute harus menggunakan **Global Attribute** (taxonomy-based), bukan Custom Attribute
- Nama attribute harus persis: `size` atau `color`/`colour` (case-insensitive)
- Filter hanya menampilkan attribute dari produk yang sedang ditampilkan
  - Di halaman kategori: hanya attribute dari produk di kategori tersebut
  - Di halaman shop: attribute dari semua produk yang ditampilkan

## Troubleshooting

Jika filter tidak muncul:

1. **Cek apakah attribute sudah dibuat:**
   - WooCommerce > Products > Attributes
   - Pastikan ada attribute dengan nama `Size` atau `Color`

2. **Cek apakah attribute sudah di-assign ke produk:**
   - Edit produk > Attributes tab
   - Pastikan attribute sudah dipilih dan terms sudah dicentang

3. **Cek apakah produk memiliki attribute:**
   - Lihat di halaman edit produk, tab Attributes
   - Pastikan ada terms yang dicentang

4. **Clear cache:**
   - Jika menggunakan cache plugin, clear cache
   - Refresh halaman shop
