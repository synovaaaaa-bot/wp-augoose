# DOKU Payment Plugin Fix - JokulCheckoutModule.php

## Problem Summary
The DOKU Payment plugin's `JokulCheckoutModule.php` has multiple unsafe array access patterns causing PHP warnings that break WooCommerce checkout AJAX responses.

## Errors to Fix

1. **Lines 29-35, 44-45**: `Trying to access array offset on false`
   - `get_option('woocommerce_doku_gateway_settings')` can return `false`
   - Direct array access without validation

2. **Line 55**: `Undefined array key "QUERY_STRING"`
   - Direct `$_SERVER['QUERY_STRING']` access without checking existence

3. **Potential WC()->session access**: Need defensive checks

## Patch Instructions

### Step 1: Locate the problematic code

Open: `wp-content/plugins/doku-payment/Module/JokulCheckoutModule.php`

Find these patterns:

```php
// Pattern 1: Around line 25-30 (settings loading)
$mainSettings = get_option('woocommerce_doku_gateway_settings');

// Pattern 2: Lines 29-35, 44-45 (direct array access)
$clientId = $mainSettings['client_id'];
$clientSecret = $mainSettings['client_secret'];
$environment = $mainSettings['environment'];
// ... more direct accesses

// Pattern 3: Line 55 (QUERY_STRING)
$queryString = $_SERVER['QUERY_STRING'];
```

### Step 2: Apply the fixes

Replace the problematic sections with the safe versions below.

---

## Fix 1: Safe Settings Loading

**BEFORE:**
```php
$mainSettings = get_option('woocommerce_doku_gateway_settings');
```

**AFTER:**
```php
// Safely get settings with fallback to empty array
$mainSettings = get_option('woocommerce_doku_gateway_settings', array());
if (!is_array($mainSettings)) {
    $mainSettings = array();
}
```

---

## Fix 2: Safe Array Access with Null Coalescing

**BEFORE:**
```php
$clientId = $mainSettings['client_id'];
$clientSecret = $mainSettings['client_secret'];
$environment = $mainSettings['environment'];
$sharedKey = $mainSettings['shared_key'];
$serverKey = $mainSettings['server_key'];
$merchantName = $mainSettings['merchant_name'];
```

**AFTER:**
```php
// Safe array access with null coalescing and sensible defaults
$clientId = $mainSettings['client_id'] ?? '';
$clientSecret = $mainSettings['client_secret'] ?? '';
$environment = $mainSettings['environment'] ?? 'sandbox'; // or 'production' based on your default
$sharedKey = $mainSettings['shared_key'] ?? '';
$serverKey = $mainSettings['server_key'] ?? '';
$merchantName = $mainSettings['merchant_name'] ?? '';
```

**For checkbox-like settings (if any):**
```php
$enabled = $mainSettings['enabled'] ?? 'no';
$testMode = $mainSettings['test_mode'] ?? 'no';
```

---

## Fix 3: Safe QUERY_STRING Access

**BEFORE:**
```php
$queryString = $_SERVER['QUERY_STRING'];
$params = array_filter(explode('&', $queryString));
```

**AFTER:**
```php
// Safe QUERY_STRING access with fallback
$queryString = $_SERVER['QUERY_STRING'] ?? '';
$queryString = is_string($queryString) ? $queryString : '';
$params = array_filter(explode('&', $queryString));
```

---

## Fix 4: Defensive WooCommerce Session Check

**BEFORE:**
```php
$session = WC()->session;
// or
WC()->session->set('key', 'value');
```

**AFTER:**
```php
// Defensive check for WooCommerce session
if (!function_exists('WC') || !WC() || !WC()->session) {
    // Handle gracefully - return early or set default
    return; // or throw exception, or set default values
}
$session = WC()->session;
```

---

## Complete Example Patch

Here's what a complete section might look like after fixes:

```php
// Safely get settings with fallback to empty array
$mainSettings = get_option('woocommerce_doku_gateway_settings', array());
if (!is_array($mainSettings)) {
    $mainSettings = array();
}

// Safe array access with null coalescing and sensible defaults
$clientId = $mainSettings['client_id'] ?? '';
$clientSecret = $mainSettings['client_secret'] ?? '';
$environment = $mainSettings['environment'] ?? 'sandbox';
$sharedKey = $mainSettings['shared_key'] ?? '';
$serverKey = $mainSettings['server_key'] ?? '';
$merchantName = $mainSettings['merchant_name'] ?? '';
$enabled = $mainSettings['enabled'] ?? 'no';

// Safe QUERY_STRING access with fallback
$queryString = $_SERVER['QUERY_STRING'] ?? '';
$queryString = is_string($queryString) ? $queryString : '';
$params = array_filter(explode('&', $queryString));

// Defensive check for WooCommerce session
if (!function_exists('WC') || !WC() || !WC()->session) {
    // Handle gracefully based on your business logic
    return; // or set defaults, or throw exception
}
$session = WC()->session;
```

---

## Additional Checks

### Check for accidental output

Search the file for:
- `echo` statements (except in proper output contexts)
- `var_dump()`, `print_r()` without `ob_start()`
- `die()` or `exit()` without proper JSON response
- Any output before `wp_send_json()` calls

### Verify AJAX compatibility

Ensure any functions called during AJAX:
1. Don't output HTML/whitespace
2. Use `wp_send_json()` or `wp_send_json_success()` / `wp_send_json_error()`
3. Clear output buffers before sending JSON

---

## Testing Checklist

After applying the patch:

- [ ] No PHP warnings in error_log from JokulCheckoutModule.php
- [ ] Checkout page loads without errors
- [ ] Checkout AJAX updates work (quantity changes, address updates)
- [ ] Payment method selection works
- [ ] DOKU payment gateway appears and functions correctly
- [ ] No "Cannot read properties of undefined" errors in browser console

---

## Rollback Plan

If issues occur after patching:
1. Restore from backup
2. Or revert specific changes one by one
3. Contact DOKU plugin vendor for official fix

---

## Notes

- This is a defensive patch for a plugin bug
- The ideal solution is for DOKU to fix their plugin
- Keep this patch documented for future plugin updates
- Re-apply patch after plugin updates if needed
