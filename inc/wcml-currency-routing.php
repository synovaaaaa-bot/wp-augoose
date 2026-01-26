<?php
/**
 * WCML Currency Routing & Payment Gateway Filter
 * 
 * Force IDR currency for ID/SG/MY countries
 * Route payment gateways based on currency (IDR → DOKU, Others → PayPal)
 * 
 * @package WP_Augoose
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Countries that should use IDR currency
 */
function wp_augoose_get_idr_countries() {
	return array( 'ID', 'SG', 'MY' ); // Indonesia, Singapore, Malaysia
}

/**
 * Get customer country from various sources
 * Priority: Billing address > Checkout form > Geolocation > Default
 */
function wp_augoose_get_customer_country() {
	// Priority 1: Checkout form data (if user is filling checkout - most reliable)
	if ( isset( $_POST['billing_country'] ) && ! empty( $_POST['billing_country'] ) ) {
		return sanitize_text_field( $_POST['billing_country'] );
	}
	
	// Priority 2: AJAX update_order_review data
	if ( isset( $_POST['post_data'] ) ) {
		parse_str( $_POST['post_data'], $post_data );
		if ( isset( $post_data['billing_country'] ) && ! empty( $post_data['billing_country'] ) ) {
			return sanitize_text_field( $post_data['billing_country'] );
		}
	}
	
	// Priority 3: Billing address (if customer is logged in or has entered address)
	if ( WC()->customer && WC()->customer->get_billing_country() ) {
		return WC()->customer->get_billing_country();
	}
	
	// Priority 4: Geolocation (if available)
	if ( function_exists( 'wc_get_customer_default_location' ) ) {
		$location = wc_get_customer_default_location();
		if ( ! empty( $location['country'] ) ) {
			return $location['country'];
		}
	}
	
	// Priority 5: WooCommerce geo location cookie
	if ( isset( $_COOKIE['woocommerce_geo_hash'] ) ) {
		// Try to get from WooCommerce geolocation if available
		$geo_hash = sanitize_text_field( $_COOKIE['woocommerce_geo_hash'] );
		// Note: This is a hash, not the actual country, but we can try to get from session
		if ( WC()->session ) {
			$geo_location = WC()->session->get( 'customer_location' );
			if ( isset( $geo_location['country'] ) ) {
				return $geo_location['country'];
			}
		}
	}
	
	// Default: return null (let WCML handle it)
	return null;
}

/**
 * Force IDR currency for ID/SG/MY countries at checkout
 * This runs early to override WCML currency selection
 */
add_action( 'template_redirect', 'wp_augoose_force_idr_for_asean_countries', 1 );
add_action( 'woocommerce_checkout_init', 'wp_augoose_force_idr_for_asean_countries', 5 );
add_action( 'woocommerce_before_checkout_process', 'wp_augoose_force_idr_for_asean_countries', 5 );
add_filter( 'woocommerce_currency', 'wp_augoose_force_idr_currency_checkout', 999, 1 );

function wp_augoose_force_idr_for_asean_countries() {
	// Only run if WCML is active
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return;
	}
	
	// Only on checkout page
	if ( ! is_checkout() ) {
		return;
	}
	
	$country = wp_augoose_get_customer_country();
	if ( ! $country ) {
		return;
	}
	
	$idr_countries = wp_augoose_get_idr_countries();
	
	// If country is ID/SG/MY, force IDR currency
	if ( in_array( $country, $idr_countries, true ) ) {
		// Set WCML currency to IDR
		global $woocommerce_wpml;
		if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
			$multi_currency = $woocommerce_wpml->multi_currency;
			
			// Check if IDR is available in WCML
			$available_currencies = $multi_currency->get_currency_codes();
			if ( in_array( 'IDR', $available_currencies, true ) ) {
				// Set client currency to IDR
				$multi_currency->set_client_currency( 'IDR' );
			}
		}
		
		// Also set in session/cookie for persistence
		if ( WC()->session ) {
			WC()->session->set( 'client_currency', 'IDR' );
		}
		
		// Set cookie for WCML
		if ( ! headers_sent() ) {
			wc_setcookie( 'wcml_client_currency', 'IDR', time() + DAY_IN_SECONDS );
		}
	}
}

/**
 * Filter currency at checkout to force IDR for ASEAN countries
 */
function wp_augoose_force_idr_currency_checkout( $currency ) {
	// Only on checkout page
	if ( ! is_checkout() ) {
		return $currency;
	}
	
	// Only run if WCML is active
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return $currency;
	}
	
	$country = wp_augoose_get_customer_country();
	if ( ! $country ) {
		return $currency;
	}
	
	$idr_countries = wp_augoose_get_idr_countries();
	
	// Force IDR for ID/SG/MY
	if ( in_array( $country, $idr_countries, true ) ) {
		return 'IDR';
	}
	
	// For other countries, keep their selected currency
	return $currency;
}

/**
 * Filter available payment gateways based on currency
 * IDR → Only DOKU
 * Others → Only PayPal/Credit Card
 */
add_filter( 'woocommerce_available_payment_gateways', 'wp_augoose_filter_payment_gateways_by_currency', 999, 1 );

function wp_augoose_filter_payment_gateways_by_currency( $available_gateways ) {
	if ( empty( $available_gateways ) ) {
		return $available_gateways;
	}
	
	// Get current currency
	$current_currency = get_woocommerce_currency();
	
	// If WCML is active, get currency from WCML
	if ( class_exists( 'woocommerce_wpml' ) ) {
		global $woocommerce_wpml;
		if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
			$multi_currency = $woocommerce_wpml->multi_currency;
			$wcml_currency = $multi_currency->get_client_currency();
			if ( $wcml_currency ) {
				$current_currency = $wcml_currency;
			}
		}
	}
	
	// Force IDR check for ASEAN countries at checkout
	if ( is_checkout() ) {
		$country = wp_augoose_get_customer_country();
		$idr_countries = wp_augoose_get_idr_countries();
		
		if ( $country && in_array( $country, $idr_countries, true ) ) {
			$current_currency = 'IDR';
		}
	}
	
	// Filter gateways based on currency
	if ( $current_currency === 'IDR' ) {
		// IDR: Only show DOKU/Jokul gateways, hide PayPal/Credit Card
		foreach ( $available_gateways as $gateway_id => $gateway ) {
			$gateway_id_lower = strtolower( $gateway_id );
			
			// Remove PayPal and other non-DOKU gateways
			if ( strpos( $gateway_id_lower, 'doku' ) === false && 
			     strpos( $gateway_id_lower, 'jokul' ) === false ) {
				// This is not DOKU, remove it
				unset( $available_gateways[ $gateway_id ] );
			}
		}
	} else {
		// Non-IDR: Only show PayPal/Credit Card, hide DOKU
		foreach ( $available_gateways as $gateway_id => $gateway ) {
			$gateway_id_lower = strtolower( $gateway_id );
			
			// Remove DOKU gateways
			if ( strpos( $gateway_id_lower, 'doku' ) !== false || 
			     strpos( $gateway_id_lower, 'jokul' ) !== false ) {
				unset( $available_gateways[ $gateway_id ] );
			}
		}
	}
	
	return $available_gateways;
}

/**
 * Update currency when billing country changes during checkout
 * This ensures currency switches immediately when user selects country
 */
add_action( 'woocommerce_checkout_update_order_review', 'wp_augoose_update_currency_on_country_change', 10, 1 );

function wp_augoose_update_currency_on_country_change( $post_data ) {
	// Only run if WCML is active
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return;
	}
	
	// Parse post data
	parse_str( $post_data, $data );
	
	$billing_country = isset( $data['billing_country'] ) ? sanitize_text_field( $data['billing_country'] ) : '';
	if ( ! $billing_country ) {
		return;
	}
	
	$idr_countries = wp_augoose_get_idr_countries();
	
	// If country is ID/SG/MY, force IDR
	if ( in_array( $billing_country, $idr_countries, true ) ) {
		global $woocommerce_wpml;
		if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
			$multi_currency = $woocommerce_wpml->multi_currency;
			
			// Check if IDR is available
			$available_currencies = $multi_currency->get_currency_codes();
			if ( in_array( 'IDR', $available_currencies, true ) ) {
				$multi_currency->set_client_currency( 'IDR' );
			}
		}
		
		// Update session
		if ( WC()->session ) {
			WC()->session->set( 'client_currency', 'IDR' );
		}
		
		// Set cookie
		if ( ! headers_sent() ) {
			wc_setcookie( 'wcml_client_currency', 'IDR', time() + DAY_IN_SECONDS );
		}
	} else {
		// For non-ASEAN countries, allow their selected currency
		// Don't force anything, let WCML handle it
	}
}

/**
 * Ensure currency is set correctly when order is created
 */
add_action( 'woocommerce_checkout_order_processed', 'wp_augoose_ensure_order_currency_correct', 10, 3 );

function wp_augoose_ensure_order_currency_correct( $order_id, $posted_data, $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}
	
	$billing_country = $order->get_billing_country();
	if ( ! $billing_country ) {
		return;
	}
	
	$idr_countries = wp_augoose_get_idr_countries();
	
	// If country is ID/SG/MY, ensure order currency is IDR
	if ( in_array( $billing_country, $idr_countries, true ) ) {
		$order_currency = $order->get_currency();
		if ( $order_currency !== 'IDR' ) {
			// Update order currency to IDR
			$order->set_currency( 'IDR' );
			
			// Recalculate totals with IDR
			$order->calculate_totals();
			$order->save();
		}
	}
}

/**
 * Prevent currency switcher from showing IDR for non-ASEAN countries
 * (Optional: Hide currency switcher on checkout if needed)
 */
add_filter( 'wcml_show_currency_switcher', 'wp_augoose_conditionally_hide_currency_switcher', 10, 2 );

function wp_augoose_conditionally_hide_currency_switcher( $show, $args ) {
	// Hide currency switcher on checkout page
	// Currency is auto-determined by country
	if ( is_checkout() ) {
		return false;
	}
	
	return $show;
}
