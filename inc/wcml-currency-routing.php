<?php
/**
 * WCML Currency + DOKU routing.
 *
 * Currency source is WCML built-in client currency.
 * Custom logic is only used when DOKU needs IDR settlement.
 *
 * @package WP_Augoose
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get current client currency from WCML, fallback to WooCommerce currency.
 */
function wp_augoose_get_client_currency() {
	$currency = get_woocommerce_currency();

	if ( class_exists( 'woocommerce_wpml' ) ) {
		global $woocommerce_wpml;

		if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
			$multi_currency = $woocommerce_wpml->multi_currency;
			if ( method_exists( $multi_currency, 'get_client_currency' ) ) {
				$wcml_currency = $multi_currency->get_client_currency();
				if ( ! empty( $wcml_currency ) ) {
					$currency = $wcml_currency;
				}
			}
		}
	}

	return strtoupper( (string) $currency );
}

/**
 * Resolve customer country with WooCommerce signals first, then geolocation fallback.
 */
function wp_augoose_get_customer_country_code() {
	// Checkout payload has highest priority during AJAX order-review refresh.
	if ( isset( $_POST['post_data'] ) ) {
		$posted_data = wp_unslash( $_POST['post_data'] );
		if ( is_string( $posted_data ) ) {
			parse_str( $posted_data, $checkout_data );
			if ( ! empty( $checkout_data['billing_country'] ) ) {
				return strtoupper( sanitize_text_field( $checkout_data['billing_country'] ) );
			}
		}
	}

	if ( isset( $_POST['billing_country'] ) && ! empty( $_POST['billing_country'] ) ) {
		return strtoupper( sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ) );
	}

	if ( function_exists( 'WC' ) && WC() && WC()->customer ) {
		$billing_country = WC()->customer->get_billing_country();
		if ( ! empty( $billing_country ) ) {
			return strtoupper( (string) $billing_country );
		}

		$shipping_country = WC()->customer->get_shipping_country();
		if ( ! empty( $shipping_country ) ) {
			return strtoupper( (string) $shipping_country );
		}
	}

	if ( function_exists( 'wc_get_customer_default_location' ) ) {
		$location = wc_get_customer_default_location();
		if ( ! empty( $location['country'] ) ) {
			return strtoupper( (string) $location['country'] );
		}
	}

	if ( class_exists( 'WC_Geolocation' ) && function_exists( 'WC' ) && WC() && WC()->customer ) {
		$ip = WC()->customer->get_ip_address();
		if ( ! empty( $ip ) ) {
			$geo = WC_Geolocation::geolocate_ip( $ip );
			if ( ! empty( $geo['country'] ) ) {
				return strtoupper( (string) $geo['country'] );
			}
		}
	}

	return '';
}

/**
 * Hard fallback: if visitor country is Indonesia, ensure WCML currency becomes IDR.
 */
function wp_augoose_get_expected_currency_by_country( $country_code ) {
	$country_code = strtoupper( (string) $country_code );

	if ( $country_code === 'ID' ) {
		return 'IDR';
	}

	if ( $country_code === 'SG' ) {
		return 'SGD';
	}

	if ( $country_code === 'MY' ) {
		return 'MYR';
	}

	return 'USD';
}

/**
 * Hard fallback: sync WCML currency to country mapping:
 * ID -> IDR, SG -> SGD, MY -> MYR, others -> USD.
 */
function wp_augoose_force_currency_by_country_if_needed() {
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return;
	}

	$country_code = wp_augoose_get_customer_country_code();
	if ( empty( $country_code ) ) {
		return;
	}

	global $woocommerce_wpml;
	if ( ! $woocommerce_wpml || ! isset( $woocommerce_wpml->multi_currency ) ) {
		return;
	}

	$multi_currency     = $woocommerce_wpml->multi_currency;
	$current            = wp_augoose_get_client_currency();
	$expected_currency  = wp_augoose_get_expected_currency_by_country( $country_code );

	if ( $current === $expected_currency ) {
		return;
	}

	$available_currencies = method_exists( $multi_currency, 'get_currency_codes' ) ? $multi_currency->get_currency_codes() : array();
	if ( ! in_array( $expected_currency, $available_currencies, true ) ) {
		return;
	}

	$multi_currency->set_client_currency( $expected_currency );

	// Keep WCML cookie aligned to reduce stale currency from previous cached sessions.
	if ( ! headers_sent() && function_exists( 'wc_setcookie' ) ) {
		wc_setcookie( 'wcml_client_currency', $expected_currency, time() + DAY_IN_SECONDS );
	}

	if ( function_exists( 'WC' ) && WC() && WC()->session ) {
		WC()->session->set( 'client_currency', $expected_currency );
	}

	if ( function_exists( 'WC' ) && WC() && WC()->cart && ! WC()->cart->is_empty() ) {
		WC()->cart->calculate_totals();
	}
}

/**
 * Check if gateway id is DOKU/Jokul.
 */
function wp_augoose_is_doku_gateway( $gateway_id ) {
	$gateway_id = strtolower( (string) $gateway_id );
	return false !== strpos( $gateway_id, 'doku' ) || false !== strpos( $gateway_id, 'jokul' );
}

/**
 * Return true if payment method from checkout data is DOKU/Jokul.
 */
function wp_augoose_is_posted_doku_payment_method( $post_data ) {
	if ( empty( $post_data ) || ! is_array( $post_data ) ) {
		return false;
	}

	if ( empty( $post_data['payment_method'] ) ) {
		return false;
	}

	return wp_augoose_is_doku_gateway( sanitize_text_field( wp_unslash( $post_data['payment_method'] ) ) );
}

/**
 * Switch client currency to IDR only when user selects DOKU and current currency is SGD/MYR.
 */
function wp_augoose_convert_doku_currency_to_idr_if_needed() {
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return;
	}

	global $woocommerce_wpml;
	if ( ! $woocommerce_wpml || ! isset( $woocommerce_wpml->multi_currency ) ) {
		return;
	}

	$multi_currency   = $woocommerce_wpml->multi_currency;
	$current_currency = wp_augoose_get_client_currency();

	// Only custom conversion for SGD/MYR -> IDR (as requested).
	if ( ! in_array( $current_currency, array( 'SGD', 'MYR' ), true ) ) {
		return;
	}

	$available_currencies = method_exists( $multi_currency, 'get_currency_codes' ) ? $multi_currency->get_currency_codes() : array();
	if ( ! in_array( 'IDR', $available_currencies, true ) ) {
		return;
	}

	if ( function_exists( 'WC' ) && WC() && WC()->session ) {
		WC()->session->set( 'wp_augoose_doku_original_currency', $current_currency );
	}

	$multi_currency->set_client_currency( 'IDR' );

	if ( function_exists( 'WC' ) && WC() && WC()->cart && ! WC()->cart->is_empty() ) {
		WC()->cart->calculate_totals();
	}
}

/**
 * Restore currency when user changes payment method away from DOKU.
 */
function wp_augoose_restore_currency_after_doku_if_needed() {
	if ( ! class_exists( 'woocommerce_wpml' ) || ! function_exists( 'WC' ) || ! WC() || ! WC()->session ) {
		return;
	}

	$original_currency = WC()->session->get( 'wp_augoose_doku_original_currency' );
	if ( empty( $original_currency ) ) {
		return;
	}

	global $woocommerce_wpml;
	if ( ! $woocommerce_wpml || ! isset( $woocommerce_wpml->multi_currency ) ) {
		return;
	}

	$multi_currency = $woocommerce_wpml->multi_currency;
	$current        = wp_augoose_get_client_currency();

	if ( $current !== 'IDR' ) {
		WC()->session->set( 'wp_augoose_doku_original_currency', '' );
		return;
	}

	$available_currencies = method_exists( $multi_currency, 'get_currency_codes' ) ? $multi_currency->get_currency_codes() : array();
	if ( ! in_array( $original_currency, $available_currencies, true ) ) {
		WC()->session->set( 'wp_augoose_doku_original_currency', '' );
		return;
	}

	$multi_currency->set_client_currency( $original_currency );
	WC()->session->set( 'wp_augoose_doku_original_currency', '' );

	if ( WC()->cart && ! WC()->cart->is_empty() ) {
		WC()->cart->calculate_totals();
	}
}

/**
 * Keep gateway visibility based on current currency from WCML.
 * - IDR/MYR/SGD: show only DOKU/Jokul
 * - Other currencies: hide DOKU/Jokul
 */
add_filter( 'woocommerce_available_payment_gateways', 'wp_augoose_filter_payment_gateways_by_currency', 999, 1 );
function wp_augoose_filter_payment_gateways_by_currency( $available_gateways ) {
	if ( empty( $available_gateways ) ) {
		return $available_gateways;
	}

	$current_currency = wp_augoose_get_client_currency();
	$use_doku         = in_array( $current_currency, array( 'IDR', 'MYR', 'SGD' ), true );

	foreach ( $available_gateways as $gateway_id => $gateway ) {
		$is_doku = wp_augoose_is_doku_gateway( $gateway_id );

		if ( $use_doku && ! $is_doku ) {
			unset( $available_gateways[ $gateway_id ] );
		}

		if ( ! $use_doku && $is_doku ) {
			unset( $available_gateways[ $gateway_id ] );
		}
	}

	return $available_gateways;
}

/**
 * React to payment method changes on checkout refresh.
 */
add_action( 'woocommerce_checkout_update_order_review', 'wp_augoose_handle_doku_currency_on_review_update', 10, 1 );
function wp_augoose_handle_doku_currency_on_review_update( $posted_data ) {
	// Ensure visitor currency follows country mapping even if WCML cache/geolocation is stale.
	wp_augoose_force_currency_by_country_if_needed();

	if ( empty( $posted_data ) ) {
		return;
	}

	parse_str( $posted_data, $checkout_data );
	if ( wp_augoose_is_posted_doku_payment_method( $checkout_data ) ) {
		wp_augoose_convert_doku_currency_to_idr_if_needed();
		return;
	}

	wp_augoose_restore_currency_after_doku_if_needed();
}

// Run early on frontend requests to make country->currency mapping deterministic.
add_action( 'wp_loaded', 'wp_augoose_force_currency_by_country_if_needed', 5 );

/**
 * Final safeguard before checkout validation.
 */
add_action( 'woocommerce_checkout_process', 'wp_augoose_force_idr_for_doku', 10 );
function wp_augoose_force_idr_for_doku() {
	if ( empty( $_POST['payment_method'] ) ) {
		return;
	}

	$payment_method = sanitize_text_field( wp_unslash( $_POST['payment_method'] ) );
	if ( ! wp_augoose_is_doku_gateway( $payment_method ) ) {
		return;
	}

	wp_augoose_convert_doku_currency_to_idr_if_needed();
}
