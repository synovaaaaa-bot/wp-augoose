# Wishlist Price Flow - Dari Mana Harga Diambil?

## Flow Lengkap Harga di Wishlist

### 1. Entry Point: `wp_augoose_wishlist_render_items_html()`

**File**: `wp-augoose/inc/woocommerce.php` (line 510-580)

```php
// Loop setiap product di wishlist
while ( $q->have_posts() ) {
    $product = wc_get_product( $pid );
    
    // Clear cache untuk variable product
    if ( $product->is_type( 'variable' ) ) {
        delete_transient( 'wc_var_prices_' . $pid );
        $product->get_price(); // Force refresh
    }
    
    // Panggil helper function
    $price = wp_augoose_get_product_price_html( $product );
}
```

### 2. Helper Function: `wp_augoose_get_product_price_html()`

**File**: `wp-augoose/inc/woocommerce.php` (line 396-431)

```php
function wp_augoose_get_product_price_html( $product ) {
    // Currency sudah di-set di awal request (template_redirect)
    // Cukup panggil get_price_html()
    return $product->get_price_html();
}
```

### 3. WooCommerce Core: `$product->get_price_html()`

**Source**: `woocommerce/includes/class-wc-product-variable.php` (line 167-190)

#### Untuk Variable Product:

```php
public function get_price_html( $price = '' ) {
    // 1. Ambil harga dari semua variations
    $prices = $this->get_variation_prices( true );
    
    // 2. Extract min/max price
    $min_price = current( $prices['price'] );
    $max_price = end( $prices['price'] );
    
    // 3. Format sebagai range atau single price
    if ( $min_price !== $max_price ) {
        $price = wc_format_price_range( $min_price, $max_price );
    } else {
        $price = wc_price( $min_price );
    }
    
    // 4. Apply filters (WCML hook di sini!)
    return apply_filters( 'woocommerce_get_price_html', $price, $this );
}
```

### 4. WooCommerce Core: `get_variation_prices()`

**Source**: `woocommerce/includes/class-wc-product-variable.php`

```php
public function get_variation_prices( $for_display = false ) {
    // 1. Check cache (transient: wc_var_prices_{product_id})
    $transient_name = 'wc_var_prices_' . $this->get_id();
    $prices = get_transient( $transient_name );
    
    if ( false === $prices ) {
        // 2. Query semua variations
        $variation_ids = $this->get_children();
        
        // 3. Loop setiap variation
        foreach ( $variation_ids as $variation_id ) {
            $variation = wc_get_product( $variation_id );
            
            // 4. Ambil harga variation
            $regular_price = $variation->get_regular_price();
            $sale_price = $variation->get_sale_price();
            $price = $sale_price ? $sale_price : $regular_price;
            
            // 5. Apply filters (WCML convert di sini!)
            $price = apply_filters( 'woocommerce_product_get_price', $price, $variation );
            
            $prices['price'][] = $price;
            $prices['regular_price'][] = $regular_price;
        }
        
        // 6. Sort prices
        sort( $prices['price'] );
        sort( $prices['regular_price'] );
        
        // 7. Cache hasil
        set_transient( $transient_name, $prices, DAY_IN_SECONDS );
    }
    
    return $prices;
}
```

### 5. WCML Currency Conversion

**WCML Hook**: `woocommerce_product_get_price` (priority 10)

```php
// WCML filter hook
add_filter( 'woocommerce_product_get_price', 'wcml_product_price', 10, 2 );

function wcml_product_price( $price, $product ) {
    // 1. Check currency context (dari set_client_currency())
    $current_currency = $woocommerce_wpml->multi_currency->get_client_currency();
    $base_currency = get_woocommerce_currency();
    
    // 2. Jika currency berbeda, convert
    if ( $current_currency !== $base_currency ) {
        // 3. Check manual price dulu
        $manual_price = get_post_meta( $product->get_id(), '_price_' . $current_currency, true );
        
        if ( $manual_price ) {
            // Pakai manual price
            $price = $manual_price;
        } else {
            // Auto convert dengan rate
            $rate = wcml_get_currency_rate( $current_currency );
            $price = $price * $rate;
            
            // Apply rounding
            $price = wcml_round_price( $price, $current_currency );
        }
    }
    
    return $price;
}
```

## Sumber Harga (Dari Mana Datangnya?)

### Untuk Simple Product:
1. **Database**: `wp_postmeta` → `_regular_price`, `_sale_price`
2. **WCML Convert**: Filter `woocommerce_product_get_price` → convert ke currency aktif
3. **Tax**: `wc_get_price_to_display()` → apply tax (inc/exc)
4. **Format**: `wc_price()` → format dengan currency symbol

### Untuk Variable Product:
1. **Database**: Setiap variation → `wp_postmeta` → `_regular_price`, `_sale_price` per variation
2. **Cache**: Transient `wc_var_prices_{product_id}` → cache semua variation prices
3. **WCML Convert**: Filter `woocommerce_product_get_price` → convert setiap variation price
4. **Min/Max**: Ambil min dan max dari semua variation prices
5. **Tax**: `wc_get_price_to_display()` → apply tax (inc/exc)
6. **Format**: `wc_format_price_range()` atau `wc_price()` → format dengan currency symbol

## Masalah: Harga Sama untuk Semua Variable Product

### Penyebab:
1. **Cache Issue**: Transient `wc_var_prices_{product_id}` menggunakan currency lama
2. **WCML Cache**: WCML meng-cache harga min untuk semua variable product
3. **Currency Context**: Currency di-set setelah cache sudah dibuat

### Solusi yang Sudah Diterapkan:

```php
// Clear cache sebelum ambil harga
if ( $product->is_type( 'variable' ) ) {
    delete_transient( 'wc_var_prices_' . $pid );
    $product->get_price(); // Force refresh
}
```

### Debug Logging:

Aktifkan `WP_DEBUG` untuk melihat flow:

```php
// Di wp_augoose_get_product_price_html()
[WP_AUGOOSE_PRICE] Product 3494: currency=IDR, raw_price=770000, tax_display=excl, html=Rp770,000.00

// Di wp_augoose_wishlist_render_items_html()
[WP_AUGOOSE_WISHLIST] Product 3494: currency=IDR, raw_price=770000, is_variable=yes, html=Rp770,000.00
```

## Checklist Debugging

Jika harga masih sama, cek:

1. ✅ **Currency Context**: Apakah currency sudah di-set di awal request?
   - Cek log: `[WP_AUGOOSE_CURRENCY] Set currency: ...`

2. ✅ **Variable Product Cache**: Apakah cache sudah di-clear?
   - Cek: `delete_transient( 'wc_var_prices_' . $pid )` sudah dipanggil?

3. ✅ **Variation Prices**: Apakah variations punya harga berbeda?
   - Cek di admin: Product → Variations → Harga per variation

4. ✅ **WCML Manual Price**: Apakah ada manual price di WCML?
   - Cek di admin: Product → WCML → Multi-currency → Manual prices

5. ✅ **WCML Rate**: Apakah rate conversion benar?
   - Cek di admin: WCML → Multi-currency → Exchange rates

## Kesimpulan

**Harga di wishlist diambil dari**:
1. Database (variation prices) 
2. WCML conversion (manual price atau auto convert)
3. WooCommerce cache (transient `wc_var_prices_{product_id}`)
4. Tax calculation (inc/exc)
5. Formatting (currency symbol, decimals)

**Flow**: Wishlist → Helper Function → `get_price_html()` → `get_variation_prices()` → WCML Convert → Format
