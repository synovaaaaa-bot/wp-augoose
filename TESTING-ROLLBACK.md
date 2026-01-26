# Testing Checklist & Rollback Steps

## Testing Checklist

- [ ] Backup `wp-content/plugins/doku-payment/Module/JokulCheckoutModule.php` before patching
- [ ] Apply patch using `patch -p1 < doku-payment-fix.patch` or manual edit
- [ ] Clear WordPress/WooCommerce caches (if using caching plugin)
- [ ] Check error_log: No "Trying to access array offset on false" from JokulCheckoutModule.php
- [ ] Check error_log: No "Undefined array key QUERY_STRING" from JokulCheckoutModule.php
- [ ] Visit checkout page: Page loads without PHP warnings
- [ ] Test checkout AJAX: Update quantity, change address fields
- [ ] Browser console: No "Cannot read properties of undefined (reading 'toString')" errors
- [ ] Payment methods: DOKU gateway appears and is selectable
- [ ] Test order placement: Complete a test order with DOKU payment
- [ ] Verify DOKU settings: All configuration values load correctly

## Quick Rollback Steps

1. **Restore from backup:**
   ```bash
   cp wp-content/plugins/doku-payment/Module/JokulCheckoutModule.php.backup \
      wp-content/plugins/doku-payment/Module/JokulCheckoutModule.php
   ```

2. **Or revert via Git (if tracked):**
   ```bash
   cd wp-content/plugins/doku-payment
   git checkout Module/JokulCheckoutModule.php
   ```

3. **Clear caches and test checkout again**

4. **If issues persist:** Disable DOKU plugin temporarily:
   ```bash
   mv wp-content/plugins/doku-payment wp-content/plugins/doku-payment.disabled
   ```
