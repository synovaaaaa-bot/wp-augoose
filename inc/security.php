<?php
/**
 * Security Enhancements
 * 
 * @package WP_Augoose
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add security headers
 */
function wp_augoose_security_headers() {
    if ( ! is_admin() ) {
        header( 'X-Content-Type-Options: nosniff' );
        header( 'X-Frame-Options: SAMEORIGIN' );
        header( 'X-XSS-Protection: 1; mode=block' );
        header( 'Referrer-Policy: strict-origin-when-cross-origin' );
        
        // Content Security Policy (adjust as needed)
        if ( ! headers_sent() ) {
            $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google-analytics.com https://www.googletagmanager.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self' https://api.frankfurter.app https://api.exchangerate.host;";
            header( "Content-Security-Policy: {$csp}" );
        }
    }
}
add_action( 'send_headers', 'wp_augoose_security_headers' );

/**
 * Sanitize and validate AJAX input
 */
function wp_augoose_sanitize_ajax_input( $input, $type = 'text' ) {
    switch ( $type ) {
        case 'int':
        case 'integer':
            return absint( $input );
        case 'float':
            return floatval( $input );
        case 'email':
            return sanitize_email( $input );
        case 'url':
            return esc_url_raw( $input );
        case 'textarea':
            return sanitize_textarea_field( $input );
        case 'key':
            return sanitize_key( $input );
        case 'text':
        default:
            return sanitize_text_field( $input );
    }
}

/**
 * Verify AJAX nonce with proper error handling
 */
function wp_augoose_verify_ajax_nonce( $nonce_action = 'wp_augoose_nonce', $nonce_name = 'nonce' ) {
    if ( ! isset( $_POST[ $nonce_name ] ) ) {
        wp_send_json_error( array( 'message' => 'Security check failed: nonce missing' ), 403 );
        wp_die();
    }
    
    $nonce = sanitize_text_field( wp_unslash( $_POST[ $nonce_name ] ) );
    
    if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
        wp_send_json_error( array( 'message' => 'Security check failed: invalid nonce' ), 403 );
        wp_die();
    }
    
    return true;
}

/**
 * Sanitize product ID from POST/GET
 */
function wp_augoose_sanitize_product_id( $input, $default = 0 ) {
    $product_id = isset( $input ) ? absint( $input ) : $default;
    
    if ( $product_id <= 0 ) {
        return $default;
    }
    
    // Verify product exists
    if ( ! wc_get_product( $product_id ) ) {
        return $default;
    }
    
    return $product_id;
}

/**
 * Sanitize cart item key
 */
function wp_augoose_sanitize_cart_key( $cart_key ) {
    if ( empty( $cart_key ) ) {
        return '';
    }
    
    // Cart keys are alphanumeric with hyphens
    return preg_replace( '/[^a-zA-Z0-9_-]/', '', sanitize_text_field( $cart_key ) );
}

/**
 * Sanitize quantity input
 */
function wp_augoose_sanitize_quantity( $quantity, $min = 1, $max = 999 ) {
    $qty = absint( $quantity );
    
    if ( $qty < $min ) {
        $qty = $min;
    }
    
    if ( $qty > $max ) {
        $qty = $max;
    }
    
    return $qty;
}

/**
 * Escape output for display
 */
function wp_augoose_escape_output( $output, $type = 'html' ) {
    switch ( $type ) {
        case 'url':
            return esc_url( $output );
        case 'attr':
            return esc_attr( $output );
        case 'js':
            return esc_js( $output );
        case 'textarea':
            return esc_textarea( $output );
        case 'html':
        default:
            return esc_html( $output );
    }
}

/**
 * Prevent direct file access
 */
function wp_augoose_prevent_direct_access() {
    // This should be in each PHP file, but we can add a check here
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
}

/**
 * Rate limiting for AJAX requests (basic)
 */
function wp_augoose_rate_limit_ajax( $action, $limit = 10, $period = 60 ) {
    $transient_key = 'wp_augoose_rate_limit_' . $action . '_' . wp_get_session_token();
    $count = get_transient( $transient_key );
    
    if ( false === $count ) {
        $count = 0;
    }
    
    if ( $count >= $limit ) {
        wp_send_json_error( array( 'message' => 'Rate limit exceeded. Please try again later.' ), 429 );
        wp_die();
    }
    
    $count++;
    set_transient( $transient_key, $count, $period );
    
    return true;
}

/**
 * Validate currency code
 */
function wp_augoose_validate_currency( $currency ) {
    $allowed_currencies = array( 'IDR', 'USD', 'EUR', 'MYR', 'SGD', 'MXN', 'HKD', 'TWD', 'JPY' );
    $currency = strtoupper( sanitize_text_field( $currency ) );
    
    if ( ! in_array( $currency, $allowed_currencies, true ) ) {
        return 'IDR'; // Default
    }
    
    return $currency;
}

/**
 * Sanitize and validate country code
 */
function wp_augoose_sanitize_country_code( $country ) {
    $country = strtoupper( sanitize_text_field( $country ) );
    
    // Validate against WooCommerce countries
    $countries = WC()->countries->get_countries();
    
    if ( ! isset( $countries[ $country ] ) ) {
        return '';
    }
    
    return $country;
}
