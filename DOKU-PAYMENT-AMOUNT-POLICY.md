# DOKU Payment Amount Policy

## 🎯 Objective

Pastikan transaksi DOKU selalu valid dengan menggunakan **amount langsung dari plugin WCML** tanpa modifikasi manual, dan diformat sesuai standar internasional.

---

## 📌 Core Rules

1. **Gunakan amount dari payment plugin sebagai sumber utama (single source of truth)**
2. **JANGAN override, input manual, atau reformat di frontend**
3. **JANGAN gunakan comma (,) dalam amount**
4. **Gunakan dot (.) hanya untuk desimal**
5. **Pisahkan data display dan data transaksi**

---

## 📌 Data Source Policy

### ✅ Wajib

```
Amount → Ambil langsung dari order object (WCML sudah convert)
Currency → Ambil dari order object (WCML sudah set)
```

### ❌ Dilarang

```
- Hardcode nominal
- Recalculate di frontend
- Parsing dari UI
- Ambil dari text display
- Format ulang pakai toLocaleString()
- Override amount di JavaScript
```

> ⚠️ Semua nilai transaksi HARUS berasal dari WooCommerce order object yang sudah diproses WCML.

---

## 📌 Allowed Amount Format

### ✔️ Valid

```
1000
1000.00
88
88.50
```

### ❌ Invalid

```
1,000
88,00
1.000
2,500.00
```

---

## 📌 Currency Handling

| Currency | API Format         |
| -------- | ------------------ |
| IDR      | Integer → `10000`  |
| USD      | Decimal → `100.00` |
| SGD      | Decimal → `88.50`  |
| MYR      | Decimal → `25.00`  |

---

## 📌 Backend Processing Logic

### Step 1 — Get From Order Object

```php
$amount = (float) $order->get_total();
$currency = $order->get_currency();
```

> ✅ WCML sudah melakukan conversion, jadi langsung ambil dari order object.

---

### Step 2 — Sanitize (Safety Only)

```php
$amount = str_replace(',', '', (string) $amount);
```

> ❗ Hanya untuk safety. Bukan untuk reformat manual.

---

### Step 3 — Format by Currency

```php
if ($currency === 'IDR') {
   $amount = (string) round((float) $amount);
} else {
   $amount = number_format((float) $amount, 2, '.', '');
}
```

---

### Step 4 — Validation

```php
if (strpos($amount, ',') !== false) {
   error_log("Invalid DOKU amount format");
   // Fix: remove comma and reformat
}
```

---

## 📌 UI vs Transaction Rule

| Layer       | Format                |
| ----------- | --------------------- |
| UI Display  | Bebas (locale format) |
| Order Object | Raw number (WCML converted) |
| DOKU API    | Raw number (no comma) |

> ❗ API hanya boleh pakai data dari order object.

---

## 📌 Architecture Rule (Wajib)

```
WCML → Order Object → DOKU Gateway → DOKU API
```

🚫 Dilarang:

```
UI → Format → Backend → DOKU
```

---

## 📌 Implementation

### PHP Hooks

1. **`woocommerce_gateway_doku_payment_args`** - Filter payment args
   - Ambil amount dari `$order->get_total()`
   - Format sesuai currency
   - Validasi no comma

2. **`woocommerce_checkout_order_processed`** - Validate order
   - Simpan clean amount di order meta
   - Validasi format

3. **`woocommerce_gateway_doku_amount`** - Get amount for DOKU
   - Ambil dari order meta (clean amount)
   - Fallback ke order total jika meta tidak ada

### JavaScript Rules

- ❌ JANGAN format amount di frontend
- ❌ JANGAN parse amount dari UI text
- ❌ JANGAN gunakan `toLocaleString()` untuk transaction data
- ✅ Biarkan WooCommerce handle semua formatting untuk display

---

## 📌 Example Flow

### Order Created (WCML Converted)

```php
$order->get_total() = 1250.50
$order->get_currency() = 'SGD'
```

### Backend Processing

```php
→ Get from order: 1250.50
→ Sanitize: "1250.50" (no comma)
→ Format: "1250.50" (2 decimals for SGD)
→ Validate: No comma ✓
→ Store in order meta: "_doku_clean_amount" = "1250.50"
```

### DOKU API Request

```
amount=1250.50
currency=SGD
```

---

## 📌 Acceptance Criteria

Sistem dinyatakan benar jika:

✅ Amount selalu dari order object (WCML converted)
✅ Tidak ada manipulasi frontend
✅ Tidak muncul error format
✅ Semua transaksi cross-border sukses
✅ Tidak ada parsing dari UI
✅ Order meta `_doku_clean_amount` selalu valid

---

## 🔥 One-Liner Prompt (Ringkas)

> "Always use the WooCommerce order object's amount and currency (already converted by WCML) as the only source of truth. Do not override or recalculate values from UI. Send raw numbers without commas to DOKU, using integers for IDR and two decimals for non-IDR."

---

## 📌 Files Modified

- `wp-augoose/inc/woocommerce.php`
  - `wp_augoose_ensure_doku_amount_from_order()` - Filter payment args
  - `wp_augoose_validate_doku_order_amount()` - Validate and store clean amount
  - `wp_augoose_get_doku_amount_from_order_meta()` - Get clean amount for DOKU

---

## 📌 Testing Checklist

- [ ] Order dengan IDR → amount integer (no decimal)
- [ ] Order dengan SGD/USD/MYR → amount 2 decimals
- [ ] Tidak ada comma dalam amount yang dikirim ke DOKU
- [ ] Amount sama dengan order total (WCML converted)
- [ ] Currency sesuai dengan order currency
- [ ] Order meta `_doku_clean_amount` terisi dengan benar
- [ ] Tidak ada error log tentang invalid format
