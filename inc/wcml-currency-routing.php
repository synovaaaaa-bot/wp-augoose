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
 * Mapping of countries to their preferred local currency.
 *
 * - Indonesia  => IDR
 * - Malaysia   => MYR
 * - Singapore  => SGD
 *
 * Any country not listed here will keep its selected currency (usually USD).
 */
function wp_augoose_get_country_currency_map() {
    return array(
        'ID' => 'IDR', // Indonesia
        'MY' => 'MYR', // Malaysia
        'SG' => 'SGD', // Singapore
    );
}

/**
 * Return the currency code that corresponds to a given country.
 * Returns null if the country does not have a forced currency.
 */
function wp_augoose_get_currency_for_country( $country ) {
    $map = wp_augoose_get_country_currency_map();
    if ( isset( $map[ $country ] ) ) {
        return $map[ $country ];
    }
    return null;
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
	
	// Priority 4: Geolocation (if available via WCML helper)
	if ( function_exists( 'wc_get_customer_default_location' ) ) {
		$location = wc_get_customer_default_location();
		if ( ! empty( $location['country'] ) ) {
			return $location['country'];
		}
	}

	// Priority 5: direct IP geolocation fallback (runs even if the helper didn't return anything)
	// This is important because WCML sometimes only resolves a hash and not
	// the actual country until later; geolocate_ip gives us a strict ISO code.
	if ( class_exists( 'WC_Geolocation' ) ) {
		$ip = WC()->customer ? WC()->customer->get_ip_address() : '';
		if ( $ip ) {
			$geo = WC_Geolocation::geolocate_ip( $ip );
			if ( ! empty( $geo['country'] ) ) {
				return $geo['country'];
			}
		}
	}

	// Priority 6: WooCommerce geo location cookie
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
 * Force local currency for mapped countries at checkout.
 * This runs early to override WCML currency selection.
 * IMPORTANT: Must trigger cart recalculation after currency change to ensure proper conversion.
 */
// run very early so WCML price functions pick up the correct currency
add_action( 'init', 'wp_augoose_force_currency_for_mapped_countries', 0 );
add_action( 'template_redirect', 'wp_augoose_force_currency_for_mapped_countries', 1 );
add_action( 'woocommerce_checkout_init', 'wp_augoose_force_currency_for_mapped_countries', 5 );
add_action( 'woocommerce_before_checkout_process', 'wp_augoose_force_currency_for_mapped_countries', 5 );
add_action( 'wp_loaded', 'wp_augoose_force_currency_for_mapped_countries', 20 ); // After cart is loaded
add_action( 'woocommerce_before_calculate_totals', 'wp_augoose_ensure_local_currency_before_cart_calc', 5 ); // Before cart calculates
add_filter( 'woocommerce_currency', 'wp_augoose_force_currency_checkout', 999, 1 );

function wp_augoose_force_currency_for_mapped_countries() {
    // Only run if WCML is active
    if ( ! class_exists( 'woocommerce_wpml' ) ) {
        return;
    }

    // No page restriction: we want the client currency to be set
    // early so that product listings, homepage, etc. display correctly.
    // (currency switching is cheap and WCML handles caching internally)

    $country = wp_augoose_get_customer_country();
    if ( ! $country ) {
        return;
    }

    $local_currency = wp_augoose_get_currency_for_country( $country );
    if ( ! $local_currency ) {
        // no mapping, let WCML handle it (usually USD)
        return;
    }

    // Get current client currency first
    global $woocommerce_wpml;
    $current_currency = null;
    if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
        $multi_currency = $woocommerce_wpml->multi_currency;
        if ( method_exists( $multi_currency, 'get_client_currency' ) ) {
            $current_currency = $multi_currency->get_client_currency();
        }
    }

    // IMPORTANT: If current currency is USD we only skip the override when
    // the customer explicitly chose USD (e.g. via the WCML switcher).  In
    // other cases USD is just the default/geo result and we should still
    // force the local currency for countries that require it.
    if ( $current_currency === 'USD' ) {
        $explicit_usd = false;
        if ( WC()->session ) {
            $explicit_usd = WC()->session->get( 'wp_augoose_explicit_usd' );
        }

        if ( $explicit_usd ) {
            return; // user really wanted USD
        }
        // otherwise fall through and force the mapped currency
    }

    // Only change if current currency is set and differs from desired local
    if ( $current_currency && $current_currency === $local_currency ) {
        return;
    }

    // Set WCML currency to the local one
    if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
        $multi_currency = $woocommerce_wpml->multi_currency;

        // Check if desired currency is available in WCML
        $available_currencies = $multi_currency->get_currency_codes();
        if ( in_array( $local_currency, $available_currencies, true ) ) {
            // Save original currency for notice
            if ( $current_currency && $current_currency !== $local_currency && WC()->session ) {
                WC()->session->set( 'wp_augoose_original_currency', $current_currency );
            }

            // Change currency
            $multi_currency->set_client_currency( $local_currency );

            // if we just moved away from USD clear explicit flag
            if ( $current_currency === 'USD' && WC()->session ) {
                WC()->session->set( 'wp_augoose_explicit_usd', false );
            }

            // Recalculate cart if needed
            if ( $current_currency !== $local_currency && WC()->cart && ! WC()->cart->is_empty() ) {
                WC()->cart->calculate_totals();
            }
        }
    }

    // Persist selection
    if ( WC()->session ) {
        WC()->session->set( 'client_currency', $local_currency );
    }
    if ( ! headers_sent() ) {
        wc_setcookie( 'wcml_client_currency', $local_currency, time() + DAY_IN_SECONDS );
    }
}

/**
 * Ensure IDR currency is set BEFORE cart calculates totals
 * This is critical for WCML to convert prices correctly
 * IMPORTANT: This must run early to ensure WCML converts prices using exchange rates
 */
function wp_augoose_ensure_local_currency_before_cart_calc( $cart ) {
    // Only run if WCML is active
    if ( ! class_exists( 'woocommerce_wpml' ) ) {
        return;
    }

    // Run on all front-end pages so local currency is always applied.
    // We restrict nothing here because set_client_currency itself is
    // harmless if called repeatedly.

    $country = wp_augoose_get_customer_country();
    if ( ! $country ) {
        return;
    }

    $local_currency = wp_augoose_get_currency_for_country( $country );
    if ( ! $local_currency ) {
        return; // nothing to force
    }

    global $woocommerce_wpml;
    if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
        $multi_currency = $woocommerce_wpml->multi_currency;

        // Ensure desired currency is available
        $available_currencies = $multi_currency->get_currency_codes();
        if ( in_array( $local_currency, $available_currencies, true ) ) {
            $current_currency = $multi_currency->get_client_currency();

            if ( $current_currency !== $local_currency ) {
                $multi_currency->set_client_currency( $local_currency );

                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( "WCML Currency Change: {$current_currency} → {$local_currency} for country: {$country}" );
                }
            }
        }
    }
}

/**
 * Filter currency at checkout to force IDR for ASEAN countries
 */
function wp_augoose_force_currency_checkout( $currency ) {
    // Only on checkout page
    if ( ! is_checkout() ) {
        return $currency;
    }

    // Only run if WCML is active
    if ( ! class_exists( 'woocommerce_wpml' ) ) {
        return $currency;
    }

    // IMPORTANT: keep USD if already selected for PayPal
    if ( $currency === 'USD' ) {
        return 'USD';
    }

    $country = wp_augoose_get_customer_country();
    if ( ! $country ) {
        return $currency;
    }

    $local_currency = wp_augoose_get_currency_for_country( $country );
    if ( $local_currency ) {
        return $local_currency;
    }

    // otherwise keep whatever currency WCML picked
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
	
	// Decide whether DOKU should be offered.  DOKU accepts IDR only,
	// but we display local currency (IDR/MYR/SGD) to the customer.
	// We don't alter $current_currency here; the removal logic below
	// will treat any of IDR/MYR/SGD as "show DOKU".
	
	// Debug logging
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && is_checkout() ) {
		error_log( "WP_Augoose Payment Gateway Filter: current_currency={$current_currency}, country=" . ( isset( $country ) ? $country : 'unknown' ) );
	}
	
	// Filter gateways based on currency (or allowed local currencies)
	$use_doku = in_array( $current_currency, array( 'IDR', 'MYR', 'SGD' ), true );
	if ( $use_doku ) {
		// Only show DOKU/Jokul gateways, hide PayPal/Credit Card
		$doku_found = false;
		$gateways_to_remove = array();
		
		foreach ( $available_gateways as $gateway_id => $gateway ) {
			$gateway_id_lower = strtolower( $gateway_id );
			
			// Check if this is DOKU/Jokul gateway
			if ( strpos( $gateway_id_lower, 'doku' ) !== false || 
			     strpos( $gateway_id_lower, 'jokul' ) !== false ) {
				$doku_found = true;
				continue; // Keep DOKU gateways
			}
			
			// Mark non-DOKU gateways for removal
			$gateways_to_remove[] = $gateway_id;
		}
		
		// Remove non-DOKU gateways
		foreach ( $gateways_to_remove as $gateway_id ) {
			unset( $available_gateways[ $gateway_id ] );
		}
		
		// Debug: Log if DOKU not found
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $doku_found ) {
			$all_gateway_ids = array_keys( $available_gateways );
			error_log( "WP_Augoose: DOKU gateway not found! Available gateways: " . implode( ', ', $all_gateway_ids ) );
		}
	} else {
		// Non-IDR: Only show PayPal/Credit Card, hide DOKU
		$gateways_to_remove = array();
		
		foreach ( $available_gateways as $gateway_id => $gateway ) {
			$gateway_id_lower = strtolower( $gateway_id );
			
			// Mark DOKU gateways for removal
			if ( strpos( $gateway_id_lower, 'doku' ) !== false || 
			     strpos( $gateway_id_lower, 'jokul' ) !== false ) {
				$gateways_to_remove[] = $gateway_id;
			}
		}
		
		// Remove DOKU gateways
		foreach ( $gateways_to_remove as $gateway_id ) {
			unset( $available_gateways[ $gateway_id ] );
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

// when the customer picks a DOKU/Jokul gateway we must convert the cart to IDR
// because the payment processor only accepts Indonesian rupiah.
add_action( 'woocommerce_checkout_process', 'wp_augoose_force_idr_for_doku', 10 );


function wp_augoose_force_idr_for_doku() {
    // only run if WCML available
    if ( ! class_exists( 'woocommerce_wpml' ) ) {
        return;
    }

    if ( empty( $_POST['payment_method'] ) ) {
        return;
    }

    $pm = sanitize_text_field( wp_unslash( $_POST['payment_method'] ) );
    $pm_lower = strtolower( $pm );

    if ( strpos( $pm_lower, 'doku' ) === false && strpos( $pm_lower, 'jokul' ) === false ) {
        return; // not a DOKU payment
    }

    global $woocommerce_wpml;
    if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
        $multi_currency = $woocommerce_wpml->multi_currency;
        $current_currency = $multi_currency->get_client_currency();
        if ( $current_currency !== 'IDR' ) {
            $multi_currency->set_client_currency( 'IDR' );
            if ( WC()->cart && ! WC()->cart->is_empty() ) {
                WC()->cart->calculate_totals();
            }
        }
    }
}

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

	$local_currency = wp_augoose_get_currency_for_country( $billing_country );

	// If we have a mapped currency for the selected country, apply it
	if ( $local_currency ) {
		global $woocommerce_wpml;
		if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
			$multi_currency = $woocommerce_wpml->multi_currency;

			// Check if the desired currency is available
			$available_currencies = $multi_currency->get_currency_codes();
			if ( in_array( $local_currency, $available_currencies, true ) ) {
				$current_currency = $multi_currency->get_client_currency();

				// Save original currency for notice
				if ( $current_currency !== $local_currency && WC()->session ) {
					WC()->session->set( 'wp_augoose_original_currency', $current_currency );
				}

				// Change currency to the local one
				$multi_currency->set_client_currency( $local_currency );

				if ( $current_currency !== $local_currency && WC()->cart && ! WC()->cart->is_empty() ) {
					WC()->cart->calculate_totals();
				}
			}
		}

		// Persist
		if ( WC()->session ) {
			WC()->session->set( 'client_currency', $local_currency );
		}
		if ( ! headers_sent() ) {
			wc_setcookie( 'wcml_client_currency', $local_currency, time() + DAY_IN_SECONDS );
		}
	} else {
		// No mapping – let WCML handle the currency normally
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

	$local_currency = wp_augoose_get_currency_for_country( $country );
	if ( ! $local_currency ) {
		return;
	}

	global $woocommerce_wpml;
	if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
		$multi_currency = $woocommerce_wpml->multi_currency;
		$current_currency = $multi_currency->get_client_currency();

		if ( $current_currency !== $local_currency ) {
			$available_currencies = $multi_currency->get_currency_codes();
			if ( in_array( $local_currency, $available_currencies, true ) ) {
				$multi_currency->set_client_currency( $local_currency );
				if ( WC()->cart && ! WC()->cart->is_empty() ) {
					WC()->cart->calculate_totals();
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

	$local_currency = wp_augoose_get_currency_for_country( $billing_country );
	if ( ! $local_currency ) {
		return;
	}

	$order_currency = $order->get_currency();
	if ( $order_currency !== $local_currency ) {
		$order->set_currency( $local_currency );
		$order->calculate_totals();
		$order->save();
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

	// Only show notice for countries with a forced local currency
	$local_currency = wp_augoose_get_currency_for_country( $country );
	if ( ! $local_currency ) {
		return;
	}

	global $woocommerce_wpml;
	if ( ! $woocommerce_wpml || ! isset( $woocommerce_wpml->multi_currency ) ) {
		return;
	}

	$multi_currency = $woocommerce_wpml->multi_currency;
	$current_currency = $multi_currency->get_client_currency();

	// Only show if current currency matches our local value
	if ( $current_currency !== $local_currency ) {
		return;
	}

	// Get base currency (store default currency)
	$base_currency = wcml_get_woocommerce_currency_option();

	// If base currency is already local, no conversion needed
	if ( $base_currency === $local_currency ) {
		return;
	}

	// Get cart total in current currency (after conversion)
	$cart_total_local = (float) WC()->cart->get_total( 'edit' );

	// Try to get original currency from session/cookie
	$original_currency = $base_currency;
	if ( WC()->session ) {
		$original_currency = WC()->session->get( 'wp_augoose_original_currency' );
		if ( ! $original_currency ) {
			if ( isset( $_COOKIE['wp_augoose_currency'] ) && $_COOKIE['wp_augoose_currency'] !== $local_currency ) {
				$original_currency = sanitize_text_field( $_COOKIE['wp_augoose_currency'] );
			}
		}
	}

	if ( ! $original_currency || $original_currency === $local_currency ) {
		$original_currency = $base_currency;
	}

	if ( $original_currency === $local_currency ) {
		return;
	}

	// … and later use $cart_total_local etc
	
	// Get exchange rate from WCML
	$exchange_rates = $multi_currency->get_exchange_rates();
	$original_to_local_rate = 1;

	if ( isset( $exchange_rates[ $original_currency ] ) && isset( $exchange_rates[ $local_currency ] ) ) {
		// Calculate rate: local rate / original rate
		$original_rate = (float) $exchange_rates[ $original_currency ];
		$local_rate = (float) $exchange_rates[ $local_currency ];
		if ( $original_rate > 0 ) {
			$original_to_local_rate = $local_rate / $original_rate;
		}
	}

	// Calculate original price (before conversion)
	// Reverse the conversion: local price / exchange rate = original price
	$cart_total_original = $cart_total_local / $original_to_local_rate;

	// Format prices
	$formatted_original_price = wc_price( $cart_total_original, array( 'currency' => $original_currency ) );
	$formatted_local_price    = wc_price( $cart_total_local, array( 'currency' => $local_currency ) );

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
					Amount shown above is already converted to <?php echo esc_html( $local_currency ); ?> (<?php echo esc_html( get_woocommerce_currency_symbol( $local_currency ) ); ?>).
				<?php if ( in_array( $local_currency, array( 'IDR', 'MYR', 'SGD' ), true ) ) : ?>
					<br>DOKU payments will be processed in IDR (converted automatically).
				<?php endif; ?>
				</span>
			</div>
		</td>
	</tr>
	<?php
}
