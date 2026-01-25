# 🔍 Panduan Debug Wishlist

## 1. Browser Console (JavaScript Logs) - **PALING PENTING**

### Cara Membuka:
1. Buka website di browser (Chrome/Firefox/Edge)
2. Tekan **F12** atau **Ctrl+Shift+I** (Windows) / **Cmd+Option+I** (Mac)
3. Klik tab **"Console"**

### Log yang Harus Muncul:

#### Saat Page Load:
```
initWishlist() called
wpAugoose object: {ajaxUrl: "...", nonce: "..."}
Wishlist buttons on page: X
Wishlist handler attached. Buttons found: X
```

#### Saat Klik Button Wishlist:
```
=== WISHLIST BUTTON CLICKED ===
Event target: <svg>...</svg>
Current target: <button>...</button>
Button element: <button>...</button>
Button jQuery object: [object Object]
Button classes: wishlist-toggle add-to-wishlist
Button data attributes: {product-id: 123}
Product ID from data attribute: 123
Wishlist toggle - Product ID: 123
Wishlist toggle - wpAugoose: {ajaxUrl: "...", nonce: "..."}
Wishlist toggle - AJAX URL: /wp-admin/admin-ajax.php
Wishlist toggle - Nonce: abc123...
Sending AJAX request to: /wp-admin/admin-ajax.php
AJAX data: {action: "wp_augoose_wishlist_toggle", product_id: 123, nonce: "..."}
AJAX request started
Wishlist AJAX response: {success: true, data: {...}}
Product added to wishlist
Wishlist count: 1
AJAX request completed
```

### Jika TIDAK Ada Log:
- **Tidak ada log "initWishlist() called"** → JavaScript tidak ter-load
- **Tidak ada log "=== WISHLIST BUTTON CLICKED ==="** → Event handler tidak ter-attach
- **Ada error merah** → Ada JavaScript error yang perlu diperbaiki

---

## 2. Network Tab (AJAX Requests)

### Cara Membuka:
1. Buka Browser Console (F12)
2. Klik tab **"Network"**
3. Filter: **XHR** atau **Fetch**
4. Klik button wishlist
5. Cari request ke `admin-ajax.php`

### Yang Harus Muncul:
- **Request Name**: `admin-ajax.php`
- **Method**: `POST`
- **Status**: `200 OK` (success) atau `400/500` (error)
- **Payload**: 
  ```json
  {
    "action": "wp_augoose_wishlist_toggle",
    "product_id": 123,
    "nonce": "abc123..."
  }
  ```
- **Response**: 
  ```json
  {
    "success": true,
    "data": {
      "action": "added",
      "count": 1,
      "ids": [123]
    }
  }
  ```

### Jika TIDAK Ada Request:
- Event handler tidak ter-trigger
- Button tidak terdeteksi
- Ada JavaScript error yang mencegah AJAX

---

## 3. WordPress Debug Log (PHP Errors)

### Lokasi File:
- **Local**: `wp-content/debug.log`
- **Production**: Biasanya di root WordPress atau sesuai konfigurasi server

### Cara Enable (jika belum):
Tambahkan di `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false); // Jangan tampilkan di frontend
```

### Log yang Mungkin Muncul:
```
[DD-MM-YYYY HH:MM:SS] PHP Warning: ...
[DD-MM-YYYY HH:MM:SS] Multicurrency plugin hooks removed. WCML should now work properly.
[DD-MM-YYYY HH:MM:SS] WCML is active. Multicurrency plugin hooks removed to prevent conflicts.
```

---

## 4. Quick Debug Checklist

### ✅ Checklist:
- [ ] Browser Console terbuka (F12)
- [ ] Tab "Console" aktif
- [ ] Tidak ada error merah di console
- [ ] Log "initWishlist() called" muncul saat page load
- [ ] Log "=== WISHLIST BUTTON CLICKED ===" muncul saat klik button
- [ ] Network tab menunjukkan request ke `admin-ajax.php`
- [ ] Response AJAX menunjukkan `success: true`

### ❌ Jika Masalah:
1. **Tidak ada log sama sekali** → JavaScript tidak ter-load, cek:
   - File `main.js` ter-load di Network tab
   - Tidak ada JavaScript error di console
   - jQuery ter-load dengan benar

2. **Log muncul tapi AJAX tidak ter-trigger** → Cek:
   - `wpAugoose` object ter-initialize
   - Nonce valid
   - Network tab untuk melihat error response

3. **AJAX error 400/500** → Cek:
   - Response di Network tab untuk detail error
   - WordPress debug log untuk PHP errors
   - Server logs untuk server-side errors

---

## 5. Screenshot yang Diperlukan untuk Debug

Jika masih error, kirim screenshot:
1. **Browser Console** (tab Console) - semua log yang muncul
2. **Network Tab** - request ke `admin-ajax.php` (jika ada)
3. **Error Message** - jika ada error merah di console

---

## 6. Test Manual

### Test 1: Cek Button Exists
Buka Console, ketik:
```javascript
$('.add-to-wishlist, .wishlist-toggle').length
```
Harus return angka > 0

### Test 2: Cek wpAugoose Object
Buka Console, ketik:
```javascript
wpAugoose
```
Harus return object dengan `ajaxUrl` dan `nonce`

### Test 3: Test Click Handler
Buka Console, ketik:
```javascript
$('.add-to-wishlist').first().click()
```
Harus trigger log "=== WISHLIST BUTTON CLICKED ==="

---

**Lokasi File Log:**
- Browser Console: F12 → Console tab
- Network Requests: F12 → Network tab → Filter XHR
- WordPress Debug: `wp-content/debug.log` (jika WP_DEBUG enabled)
