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
 * IMPORTANT: Must trigger cart recalculation after currency change to ensure proper conversion
 */
add_action( 'template_redirect', 'wp_augoose_force_idr_for_asean_countries', 1 );
add_action( 'woocommerce_checkout_init', 'wp_augoose_force_idr_for_asean_countries', 5 );
add_action( 'woocommerce_before_checkout_process', 'wp_augoose_force_idr_for_asean_countries', 5 );
add_action( 'wp_loaded', 'wp_augoose_force_idr_for_asean_countries', 20 ); // After cart is loaded
add_action( 'woocommerce_before_calculate_totals', 'wp_augoose_ensure_idr_before_cart_calc', 5 ); // Before cart calculates
add_filter( 'woocommerce_currency', 'wp_augoose_force_idr_currency_checkout', 999, 1 );

function wp_augoose_force_idr_for_asean_countries() {
	// Only run if WCML is active
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return;
	}
	
	// Only on checkout page or cart page
	if ( ! is_checkout() && ! is_cart() ) {
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
				// Get current currency before changing
				$current_currency = $multi_currency->get_client_currency();
				
				// Save original currency to session for conversion notice
				if ( $current_currency !== 'IDR' && WC()->session ) {
					WC()->session->set( 'wp_augoose_original_currency', $current_currency );
				}
				
				// Set client currency to IDR (this triggers WCML conversion)
				$multi_currency->set_client_currency( 'IDR' );
				
				// If currency changed, trigger cart recalculation to apply conversion
				if ( $current_currency !== 'IDR' && WC()->cart && ! WC()->cart->is_empty() ) {
					// Force cart to recalculate with new currency
					// This will trigger WCML to convert prices using exchange rates
					WC()->cart->calculate_totals();
				}
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
 * Ensure IDR currency is set BEFORE cart calculates totals
 * This is critical for WCML to convert prices correctly
 * IMPORTANT: This must run early to ensure WCML converts prices using exchange rates
 */
function wp_augoose_ensure_idr_before_cart_calc( $cart ) {
	// Only run if WCML is active
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return;
	}
	
	// Only on checkout or cart page
	if ( ! is_checkout() && ! is_cart() ) {
		return;
	}
	
	$country = wp_augoose_get_customer_country();
	if ( ! $country ) {
		return;
	}
	
	$idr_countries = wp_augoose_get_idr_countries();
	
	// If country is ID/SG/MY, ensure IDR is set before calculation
	if ( in_array( $country, $idr_countries, true ) ) {
		global $woocommerce_wpml;
		if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
			$multi_currency = $woocommerce_wpml->multi_currency;
			
			// Check if IDR is available
			$available_currencies = $multi_currency->get_currency_codes();
			if ( in_array( 'IDR', $available_currencies, true ) ) {
				$current_currency = $multi_currency->get_client_currency();
				
				// Force IDR if not already set
				// WCML will automatically convert prices using exchange rates when currency is set
				if ( $current_currency !== 'IDR' ) {
					$multi_currency->set_client_currency( 'IDR' );
					
					// Debug: Log currency change for troubleshooting
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( "WCML Currency Change: {$current_currency} → IDR for country: {$country}" );
					}
				}
			}
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
 * IMPORTANT: Must trigger cart recalculation to apply currency conversion
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
				// Get current currency before changing
				$current_currency = $multi_currency->get_client_currency();
				
				// Save original currency to session for conversion notice
				if ( $current_currency !== 'IDR' && WC()->session ) {
					WC()->session->set( 'wp_augoose_original_currency', $current_currency );
				}
				
				// Set client currency to IDR (this triggers WCML conversion)
				$multi_currency->set_client_currency( 'IDR' );
				
				// If currency changed, trigger cart recalculation to apply conversion
				if ( $current_currency !== 'IDR' && WC()->cart && ! WC()->cart->is_empty() ) {
					// Force cart to recalculate with new currency
					// WCML will automatically convert prices using exchange rates
					WC()->cart->calculate_totals();
				}
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
 * Force cart recalculation after currency change to ensure WCML conversion is applied
 * This runs after WCML sets the currency to ensure prices are converted
 */
add_action( 'woocommerce_after_calculate_totals', 'wp_augoose_verify_currency_after_calc', 999 );

function wp_augoose_verify_currency_after_calc( $cart ) {
	// Only run if WCML is active
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return;
	}
	
	// Only on checkout or cart page
	if ( ! is_checkout() && ! is_cart() ) {
		return;
	}
	
	$country = wp_augoose_get_customer_country();
	if ( ! $country ) {
		return;
	}
	
	$idr_countries = wp_augoose_get_idr_countries();
	
	// If country is ID/SG/MY, verify currency is IDR
	if ( in_array( $country, $idr_countries, true ) ) {
		global $woocommerce_wpml;
		if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
			$multi_currency = $woocommerce_wpml->multi_currency;
			$current_currency = $multi_currency->get_client_currency();
			
			// If currency is not IDR, force it and recalculate
			if ( $current_currency !== 'IDR' ) {
				$available_currencies = $multi_currency->get_currency_codes();
				if ( in_array( 'IDR', $available_currencies, true ) ) {
					$multi_currency->set_client_currency( 'IDR' );
					// Recalculate again to apply conversion
					if ( WC()->cart && ! WC()->cart->is_empty() ) {
						WC()->cart->calculate_totals();
					}
				}
			}
		}
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

/**
 * Display currency conversion notice on checkout
 * Shows original price before conversion and confirms conversion to IDR
 */
add_action( 'woocommerce_review_order_after_order_total', 'wp_augoose_display_currency_conversion_notice', 10 );

function wp_augoose_display_currency_conversion_notice() {
	// Only run if WCML is active
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return;
	}
	
	// Only on checkout page
	if ( ! is_checkout() ) {
		return;
	}
	
	// Check if cart is empty
	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	
	$country = wp_augoose_get_customer_country();
	if ( ! $country ) {
		return;
	}
	
	$idr_countries = wp_augoose_get_idr_countries();
	
	// Only show notice for ID/SG/MY countries
	if ( ! in_array( $country, $idr_countries, true ) ) {
		return;
	}
	
	global $woocommerce_wpml;
	if ( ! $woocommerce_wpml || ! isset( $woocommerce_wpml->multi_currency ) ) {
		return;
	}
	
	$multi_currency = $woocommerce_wpml->multi_currency;
	$current_currency = $multi_currency->get_client_currency();
	
	// Only show if current currency is IDR
	if ( $current_currency !== 'IDR' ) {
		return;
	}
	
	// Get base currency (store default currency)
	$base_currency = wcml_get_woocommerce_currency_option();
	
	// If base currency is already IDR, no conversion needed
	if ( $base_currency === 'IDR' ) {
		return;
	}
	
	// Get cart total in IDR (current currency after conversion)
	$cart_total_idr = (float) WC()->cart->get_total( 'edit' );
	
	// Try to get original currency from session/cookie (currency before forcing IDR)
	$original_currency = $base_currency;
	if ( WC()->session ) {
		$original_currency = WC()->session->get( 'wp_augoose_original_currency' );
		if ( ! $original_currency ) {
			// Try to get from cookie
			if ( isset( $_COOKIE['wp_augoose_currency'] ) && $_COOKIE['wp_augoose_currency'] !== 'IDR' ) {
				$original_currency = sanitize_text_field( $_COOKIE['wp_augoose_currency'] );
			}
		}
	}
	
	// If we can't determine original currency, use base currency
	if ( ! $original_currency || $original_currency === 'IDR' ) {
		$original_currency = $base_currency;
	}
	
	// If original currency is already IDR, no conversion needed
	if ( $original_currency === 'IDR' ) {
		return;
	}
	
	// Get exchange rate from WCML
	$exchange_rates = $multi_currency->get_exchange_rates();
	$original_to_idr_rate = 1;
	
	if ( isset( $exchange_rates[ $original_currency ] ) && isset( $exchange_rates['IDR'] ) ) {
		// Calculate rate: IDR rate / Original currency rate
		$original_rate = (float) $exchange_rates[ $original_currency ];
		$idr_rate = (float) $exchange_rates['IDR'];
		if ( $original_rate > 0 ) {
			$original_to_idr_rate = $idr_rate / $original_rate;
		}
	}
	
	// Calculate original price (before conversion)
	// Reverse the conversion: IDR price / exchange rate = original price
	$cart_total_original = $cart_total_idr / $original_to_idr_rate;
	
	// Format original price
	$formatted_original_price = wc_price( $cart_total_original, array( 'currency' => $original_currency ) );
	$formatted_idr_price = wc_price( $cart_total_idr, array( 'currency' => 'IDR' ) );
	
	// Get currency symbol for original currency
	$original_symbol = get_woocommerce_currency_symbol( $original_currency );
	
	// Display notice
	?>
	<tr class="currency-conversion-notice">
		<td colspan="2" class="conversion-notice-content">
			<div class="currency-conversion-info">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="vertical-align: middle; margin-right: 6px;">
					<path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 14c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6zm-1-9h2v4h-2V5zm0 5h2v2H7v-2z"/>
				</svg>
				<span class="conversion-text">
					<strong>Price converted:</strong> Original price was <?php echo wp_kses_post( $formatted_original_price ); ?> 
					(<?php echo esc_html( $original_currency ); ?>). 
					Amount shown above is already converted to IDR (Indonesian Rupiah).
				</span>
			</div>
		</td>
	</tr>
	<?php
}
