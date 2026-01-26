# Root Cause Analysis: Checkout AJAX Returns HTML Instead of JSON

## Root Cause Hypotheses (Ranked by Likelihood)

### 1. **CRITICAL: wp_footer hooks output HTML during wc-ajax** (95% likelihood)
**Evidence:**
- `wp_augoose_render_wishlist_sidebar()` (line 1649) - outputs `<div class="wishlist-sidebar">...`
- `wp_augoose_mini_cart_html()` (line 1867) - outputs `<div class="cart-sidebar-overlay">...`
- `wp_augoose_hide_newsletter_checkbox()` (line 2352) - outputs `<style>...</style>`

**Why this breaks:**
- WooCommerce `wc-ajax` requests still load the theme and fire `wp_footer`
- These hooks output HTML before WooCommerce sends JSON
- Result: Response starts with `<!doctype html>` or HTML fragments instead of JSON

**Fix:** Add `wp_doing_ajax()` or `wc-ajax` detection to skip output during AJAX.

---

### 2. **DOKU Plugin PHP Warnings Corrupt Output** (90% likelihood)
**Evidence:**
- Error log shows: "Trying to access array offset on false" (lines 29-35, 44-45)
- Error log shows: "Undefined array key QUERY_STRING" (line 55)
- PHP warnings output HTML error messages before JSON

**Why this breaks:**
- PHP warnings are output as HTML: `<b>Warning</b>: ...`
- This HTML appears before `wp_send_json()` response
- Browser receives: `<!doctype html><b>Warning</b>...{"result":"success"...}`

**Fix:** Apply DOKU plugin patch + ensure error suppression works.

---

### 3. **woocommerce_update_order_review_fragments filter uses ob_start()** (60% likelihood)
**Evidence:**
- Line 2885-2894: `wp_augoose_preserve_checkout_product_images()` uses `ob_start()`/`ob_get_clean()`
- If output buffer is already cleared, this could cause issues

**Why this might break:**
- If output buffer was cleared earlier, `ob_get_clean()` might return empty string
- Could cause fragments to be malformed

**Fix:** Ensure proper output buffer management.

---

### 4. **template_redirect hooks run during wc-ajax** (40% likelihood)
**Evidence:**
- `wp_augoose_set_currency_once()` (line 522) runs on `template_redirect`
- `wp_augoose_ensure_classic_checkout()` (line 93) runs on `template_redirect`

**Why this might break:**
- Both already skip wc-ajax (good), but if they output anything, it breaks JSON

**Fix:** Already handled, but verify no output.

---

### 5. **Caching/Optimization Plugin Interference** (20% likelihood)
**Evidence:**
- LiteSpeed Cache, WP Rocket, or similar might cache AJAX responses
- Might serve HTML cache instead of JSON

**Fix:** Exclude `wc-ajax` from caching.

---

## Most Likely Root Cause: #1 + #2 Combined

**Scenario:**
1. User submits checkout form field change
2. WooCommerce sends `?wc-ajax=update_order_review` request
3. WordPress loads theme, fires `wp_footer` hooks
4. `wp_augoose_mini_cart_html()` outputs HTML: `<div class="cart-sidebar-overlay">...`
5. DOKU plugin triggers PHP warnings, outputs: `<b>Warning</b>: ...`
6. WooCommerce tries to send JSON: `{"result":"success"...}`
7. Browser receives: `<!doctype html>...<div class="cart-sidebar-overlay">...<b>Warning</b>...{"result":"success"...}`
8. JavaScript tries to parse as JSON → `SyntaxError: Unexpected token '<'`

**Fix Priority:**
1. Fix wp_footer hooks to skip during AJAX (CRITICAL)
2. Apply DOKU plugin patch (CRITICAL)
3. Verify output buffer management (IMPORTANT)
4. Add caching exclusions (OPTIONAL)
