# Checkout AJAX Fix - Response HTML Instead of JSON

## Problem
Checkout form submission returns HTML instead of JSON when using `wc-ajax=update_order_review`.

## Root Cause
1. Request tidak menggunakan endpoint `wc-ajax` yang benar
2. Output buffer clearing terlalu agresif dan mengganggu WooCommerce handler
3. Hook `template_redirect` mungkin mengganggu WooCommerce's wc-ajax processing

## Solution Applied

### 1. Fixed Output Buffer Clearing
- Hanya clear output buffer untuk request yang benar-benar AJAX dengan `wc-ajax` parameter
- Tidak mengganggu form submission biasa
- Mengikuti pola WooCommerce: hanya clean untuk wc-ajax endpoint

### 2. Fixed template_redirect Hook
- Skip untuk wc-ajax requests - biarkan WooCommerce handle
- Hanya apply untuk halaman checkout biasa (bukan AJAX)

### 3. Proper AJAX Detection
- Deteksi `wc-ajax` endpoint dengan benar
- Deteksi `wp_doing_ajax()` untuk admin-ajax.php requests
- Tidak mengganggu WooCommerce's native `update_order_review` handler

## Testing

### Correct Request Format
WooCommerce checkout AJAX harus menggunakan:
```
POST /?wc-ajax=update_order_review
Content-Type: application/x-www-form-urlencoded

billing_first_name=...
billing_country=...
...
```

### Expected Response
```json
{
  "result": "success",
  "messages": "",
  "reload": false,
  "fragments": {
    ".woocommerce-checkout-review-order-table": "...",
    ".woocommerce-checkout-payment": "..."
  }
}
```

## Verification

1. Check browser console - tidak ada "SyntaxError: Unexpected token '<'"
2. Check Network tab - request ke `?wc-ajax=update_order_review`
3. Check response - harus JSON, bukan HTML
4. Check error_log - tidak ada PHP warnings yang merusak output

## Notes

- WooCommerce menggunakan `wc-ajax` endpoint untuk semua checkout AJAX
- Handler native WooCommerce: `WC_AJAX::update_order_review()`
- Jangan override atau mengganggu handler ini
- Output buffer clearing hanya untuk mencegah HTML sebelum JSON
