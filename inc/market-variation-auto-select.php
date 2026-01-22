<?php
/**
 * Market Variation Auto-Select
 * Auto-select Market variation (ID/MY/SG) based on user country
 * 
 * @package WP_Augoose
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get user country and map to Market variation
 * 
 * Priority:
 * 1. WC()->customer->get_shipping_country()
 * 2. WC()->customer->get_billing_country()
 * 3. WooCommerce geolocation
 * 4. Fallback to ID
 * 
 * @return string Market value (ID, MY, or SG)
 */
function wp_augoose_get_market_from_country() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return 'ID'; // Fallback
    }
    
    $country = '';
    
    // Priority 1: Shipping country
    if ( WC()->customer && method_exists( WC()->customer, 'get_shipping_country' ) ) {
        $country = WC()->customer->get_shipping_country();
    }
    
    // Priority 2: Billing country
    if ( empty( $country ) && WC()->customer && method_exists( WC()->customer, 'get_billing_country' ) ) {
        $country = WC()->customer->get_billing_country();
    }
    
    // Priority 3: WooCommerce geolocation
    if ( empty( $country ) && function_exists( 'wc_get_customer_default_location' ) ) {
        $location = wc_get_customer_default_location();
        if ( isset( $location['country'] ) ) {
            $country = $location['country'];
        }
    }
    
    // Priority 4: Try to get from session/cookie
    if ( empty( $country ) && WC()->session ) {
        $country = WC()->session->get( 'customer_country' );
    }
    
    // Map country to Market
    // MAIN TARGET
    $market_mapping = array(
        'ID' => 'ID', // Indonesia
        'MY' => 'MY', // Malaysia
        'SG' => 'SG', // Singapore
        // SECONDARY TARGET
        'US' => 'US', // United States
        'MX' => 'MX', // Mexico
        'ES' => 'ES', // Spain
        'FR' => 'FR', // France
        'HK' => 'HK', // Hong Kong
        'TW' => 'TW', // Taiwan
        'JP' => 'JP', // Japan
    );
    
    // Check if country matches directly
    if ( isset( $market_mapping[ $country ] ) ) {
        return $market_mapping[ $country ];
    }
    
    // Fallback to ID
    return 'ID';
}

/**
 * Enqueue script for auto-selecting Market variation
 */
function wp_augoose_enqueue_market_variation_script() {
    if ( ! class_exists( 'WooCommerce' ) || ! is_product() ) {
        return;
    }
    
    global $product;
    
    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        return;
    }
    
    // Check if product has Market attribute
    $attributes = $product->get_variation_attributes();
    $has_market = false;
    $market_attribute_name = '';
    
    foreach ( $attributes as $attr_name => $options ) {
        // Check for 'market' attribute (can be 'pa_market' or 'market')
        if ( false !== stripos( $attr_name, 'market' ) ) {
            $has_market = true;
            $market_attribute_name = $attr_name;
            break;
        }
    }
    
    if ( ! $has_market ) {
        return; // Product doesn't have Market attribute
    }
    
    // Get market value based on country
    $market_value = wp_augoose_get_market_from_country();
    
    // Get the actual attribute name for the select element
    // WooCommerce uses 'attribute_pa_market' or 'attribute_market'
    $select_name = '';
    if ( strpos( $market_attribute_name, 'pa_' ) === 0 ) {
        // Taxonomy attribute
        $select_name = 'attribute_' . $market_attribute_name; // e.g., 'attribute_pa_market'
    } else {
        // Custom attribute
        $select_name = 'attribute_' . sanitize_title( $market_attribute_name ); // e.g., 'attribute_market'
    }
    
    // Enqueue script
    $theme_dir = get_template_directory();
    $theme_dir_uri = get_template_directory_uri();
    $js_file = $theme_dir . '/assets/js/market-variation-auto-select.js';
    
    if ( ! file_exists( $js_file ) ) {
        return; // Script file doesn't exist
    }
    
    $asset_ver = filemtime( $js_file ) ?: '1.0.0';
    
    wp_enqueue_script(
        'wp-augoose-market-variation',
        $theme_dir_uri . '/assets/js/market-variation-auto-select.js',
        array( 'jquery', 'wc-add-to-cart-variation' ), // Depend on WooCommerce variation script
        $asset_ver,
        true
    );
    
    // Pass data to JavaScript
    wp_localize_script(
        'wp-augoose-market-variation',
        'wpAugooseMarket',
        array(
            'marketValue' => $market_value,
            'selectName' => $select_name,
            'attributeName' => $market_attribute_name,
        )
    );
}
add_action( 'wp_enqueue_scripts', 'wp_augoose_enqueue_market_variation_script', 20 );
