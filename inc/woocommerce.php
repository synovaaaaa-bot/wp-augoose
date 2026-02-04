<?php
/**
 * WooCommerce Compatibility File
 *
 * @package WP_Augoose
 */

// Load security functions once
if ( ! function_exists( 'wp_augoose_verify_ajax_nonce' ) && file_exists( get_template_directory() . '/inc/security.php' ) ) {
	require_once get_template_directory() . '/inc/security.php';
}

/**
 * Get size chart URL based on product category/name
 * Returns the appropriate size chart image URL for the product
 * 
 * @param WC_Product|int|null $product Product object or product ID
 * @return string Size chart image URL
 */
function wp_augoose_get_size_chart_url( $product = null ) {
	// Get product object
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	} elseif ( ! $product ) {
		global $product;
	}
	
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		// Default to pants regular fit if product not found
		return 'https://augoose.co/wp-content/uploads/2026/01/Pants-Regular-Fit-Double-Knee-Carpenter-Utility_-Size-CHart.jpg';
	}
	
	$product_name = strtolower( $product->get_name() );
	$product_categories = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
	$category_names = array_map( 'strtolower', $product_categories );
	
	// Check product name and categories to determine size chart
	// Jacket Regular (Service)
	if ( 
		strpos( $product_name, 'jacket' ) !== false && 
		( strpos( $product_name, 'regular' ) !== false || strpos( $product_name, 'service' ) !== false ) &&
		strpos( $product_name, 'vintage' ) === false && strpos( $product_name, 'boxy' ) === false
	) {
		return 'https://augoose.co/wp-content/uploads/2026/01/Jacket-Regular-Service_Size-Chart.jpg';
	}
	
	// Jacket Vintage Boxy Fit
	if ( 
		strpos( $product_name, 'jacket' ) !== false && 
		( strpos( $product_name, 'vintage' ) !== false || strpos( $product_name, 'boxy' ) !== false )
	) {
		return 'https://augoose.co/wp-content/uploads/2026/01/Jacket-Vintage-Boxy-Fit_Size-Chart.jpg';
	}
	
	// Pants Straight Fit (Sentinel, Fatigue)
	if ( 
		strpos( $product_name, 'pants' ) !== false && 
		( strpos( $product_name, 'straight' ) !== false || strpos( $product_name, 'sentinel' ) !== false || strpos( $product_name, 'fatigue' ) !== false )
	) {
		return 'https://augoose.co/wp-content/uploads/2026/01/Pants-Straight-Fit-Sentinel-Fatigue_Size-Chart.jpg';
	}
	
	// Workshirt and Vest
	if ( 
		strpos( $product_name, 'workshirt' ) !== false || 
		strpos( $product_name, 'work shirt' ) !== false ||
		strpos( $product_name, 'vest' ) !== false ||
		( strpos( $product_name, 'shirt' ) !== false && strpos( $product_name, 'work' ) !== false )
	) {
		return 'https://augoose.co/wp-content/uploads/2026/01/Workshirt-and-Vest_Size-Chart.jpg';
	}
	
	// Pants Regular Fit (Double Knee, Carpenter, Utility) - default for pants
	if ( strpos( $product_name, 'pants' ) !== false ) {
		return 'https://augoose.co/wp-content/uploads/2026/01/Pants-Regular-Fit-Double-Knee-Carpenter-Utility_-Size-CHart.jpg';
	}
	
	// Check categories as fallback
	foreach ( $category_names as $cat_name ) {
		// Jacket categories
		if ( strpos( $cat_name, 'jacket' ) !== false ) {
			if ( strpos( $cat_name, 'vintage' ) !== false || strpos( $cat_name, 'boxy' ) !== false ) {
				return 'https://augoose.co/wp-content/uploads/2026/01/Jacket-Vintage-Boxy-Fit_Size-Chart.jpg';
			}
			return 'https://augoose.co/wp-content/uploads/2026/01/Jacket-Regular-Service_Size-Chart.jpg';
		}
		
		// Pants categories
		if ( strpos( $cat_name, 'pants' ) !== false || strpos( $cat_name, 'trouser' ) !== false ) {
			if ( strpos( $cat_name, 'straight' ) !== false || strpos( $cat_name, 'sentinel' ) !== false || strpos( $cat_name, 'fatigue' ) !== false ) {
				return 'https://augoose.co/wp-content/uploads/2026/01/Pants-Straight-Fit-Sentinel-Fatigue_Size-Chart.jpg';
			}
			return 'https://augoose.co/wp-content/uploads/2026/01/Pants-Regular-Fit-Double-Knee-Carpenter-Utility_-Size-CHart.jpg';
		}
		
		// Shirt/Vest categories
		if ( strpos( $cat_name, 'shirt' ) !== false || strpos( $cat_name, 'vest' ) !== false ) {
			return 'https://augoose.co/wp-content/uploads/2026/01/Workshirt-and-Vest_Size-Chart.jpg';
		}
	}
	
	// Default to pants regular fit
	return 'https://augoose.co/wp-content/uploads/2026/01/Pants-Regular-Fit-Double-Knee-Carpenter-Utility_-Size-CHart.jpg';
}

/**
 * Helper function: Check if current request is WooCommerce AJAX
 * 
 * CRITICAL: Use this to prevent HTML output during wc-ajax requests
 * WooCommerce uses ?wc-ajax=update_order_review for checkout AJAX
 * 
 * @return bool True if this is a WooCommerce AJAX request
 */
function augoose_is_wc_ajax_request() {
	// CRITICAL: Exclude WordPress Customizer requests
	// Customizer uses customize_changeset_uuid parameter but is NOT an AJAX request
	if ( isset( $_REQUEST['customize_changeset_uuid'] ) || 
	     isset( $_GET['customize_changeset_uuid'] ) || 
	     isset( $_POST['customize_changeset_uuid'] ) ||
	     is_customize_preview() ) {
		return false; // This is Customizer, not WooCommerce AJAX
	}
	
	// Check wp_doing_ajax() first (covers admin-ajax.php)
	if ( wp_doing_ajax() ) {
		// Check if it's a WooCommerce AJAX action
		if ( isset( $_REQUEST['action'] ) ) {
			$action = sanitize_text_field( $_REQUEST['action'] );
			if ( strpos( $action, 'woocommerce_' ) === 0 || 
			     $action === 'update_checkout_quantity' ||
			     $action === 'update_order_review' ) {
				return true;
			}
		}
	}
	
	// Check wc-ajax endpoint (WooCommerce's standard AJAX endpoint)
	if ( isset( $_REQUEST['wc-ajax'] ) || isset( $_GET['wc-ajax'] ) || isset( $_POST['wc-ajax'] ) ) {
		return true;
	}
	
	// Check DOING_AJAX constant
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		// Additional check: if wc-ajax is in request, it's WC AJAX
		if ( isset( $_REQUEST['wc-ajax'] ) || isset( $_GET['wc-ajax'] ) || isset( $_POST['wc-ajax'] ) ) {
			return true;
		}
	}
	
	// Check REST_REQUEST (optional, for WooCommerce REST API)
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		// Only if it's a WooCommerce REST request
		if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], '/wc/' ) !== false ) {
			return true;
		}
	}
	
	return false;
}

/**
 * Ensure WCML currency switching works properly
 * Don't interfere with WCML's currency conversion
 */
add_action( 'init', 'wp_augoose_ensure_wcml_currency_works', 999 );
function wp_augoose_ensure_wcml_currency_works() {
	// Only run if WCML is active
	if ( ! class_exists( 'woocommerce_wpml' ) && ! function_exists( 'wcml_get_woocommerce_currency_option' ) ) {
		return;
	}
	
	// Ensure no filters are blocking WCML
	// WCML uses filters like:
	// - woocommerce_currency (priority 10)
	// - woocommerce_currency_symbol (priority 10)
	// - woocommerce_product_get_price (priority 10)
	// - etc.
	
	// Don't add any filters that might interfere with WCML
	// Just ensure multicurrency plugin doesn't block it
}

/**
 * Remove duplicate category title on product taxonomy archives
 * woocommerce_product_taxonomy_archive_header displays title again, causing duplication
 */
add_action( 'init', 'wp_augoose_remove_duplicate_category_title', 20 );
function wp_augoose_remove_duplicate_category_title() {
	// Remove woocommerce_product_taxonomy_archive_header to prevent duplicate titles
	remove_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header', 10 );
}

/**
 * WooCommerce setup function.
 */
function wp_augoose_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 400,
			'single_image_width'    => 800,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 1,
				'max_columns'     => 6,
			),
		)
	);
	// Disable zoom & lightbox, hanya slider dengan thumbnail
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'wp_augoose_woocommerce_setup' );

/**
 * Ensure archive-product.php template is used (disable block templates for shop)
 * This ensures our custom PHP template is always used instead of block templates
 */
add_filter( 'woocommerce_has_block_template', function( $has_template, $template_name ) {
	// Force use PHP template for archive-product
	if ( $template_name === 'archive-product' ) {
		return false;
	}
	return $has_template;
}, 10, 2 );

/**
 * Disable WooCommerce compatibility layer to ensure our template is used
 */
add_filter( 'woocommerce_disable_compatibility_layer', '__return_true' );

/**
 * CRITICAL: Force Classic Checkout (disable Checkout Block)
 * 
 * Many payment gateways (including DOKU) have compatibility issues with Checkout Block.
 * This ensures we always use Classic Checkout with our custom templates.
 */
add_filter( 'woocommerce_blocks_is_feature_enabled', 'wp_augoose_disable_checkout_block', 10, 2 );
function wp_augoose_disable_checkout_block( $is_enabled, $feature ) {
	// Disable Checkout Block feature
	if ( 'checkout' === $feature || 'cart' === $feature ) {
		return false;
	}
	return $is_enabled;
}

/**
 * Ensure checkout page uses Classic checkout shortcode, not Block
 */
add_action( 'template_redirect', 'wp_augoose_ensure_classic_checkout', 1 );
function wp_augoose_ensure_classic_checkout() {
	// CRITICAL: Skip for wc-ajax requests - WooCommerce handles these
	// wc-ajax requests are processed by WooCommerce before this hook
	if ( isset( $_REQUEST['wc-ajax'] ) || isset( $_GET['wc-ajax'] ) || isset( $_POST['wc-ajax'] ) ) {
		return;
	}
	
	// CRITICAL: Skip for WordPress Customizer requests
	// Customizer needs to work normally, don't interfere
	if ( isset( $_REQUEST['customize_changeset_uuid'] ) || 
	     isset( $_GET['customize_changeset_uuid'] ) || 
	     isset( $_POST['customize_changeset_uuid'] ) ||
	     is_customize_preview() ) {
		return;
	}
	
	// Only on checkout page (not AJAX)
	if ( ! is_checkout() ) {
		return;
	}
	
	// Force disable Checkout Block if somehow enabled
	if ( function_exists( 'wc_blocks_is_feature_enabled' ) ) {
		// Disable checkout block feature
		add_filter( 'woocommerce_blocks_is_feature_enabled', function( $enabled, $feature ) {
			if ( 'checkout' === $feature || 'cart' === $feature ) {
				return false;
			}
			return $enabled;
		}, 999, 2 );
	}
	
	// Ensure we're using classic checkout template
	if ( function_exists( 'wc_get_template' ) ) {
		// This ensures our custom checkout template is used
		add_filter( 'woocommerce_locate_template', 'wp_augoose_force_classic_checkout_template', 10, 3 );
	}
}

/**
 * Force classic checkout template location
 */
function wp_augoose_force_classic_checkout_template( $template, $template_name, $template_path ) {
	// Force use our custom checkout templates
	if ( strpos( $template_name, 'checkout/' ) === 0 ) {
		$custom_template = get_template_directory() . '/woocommerce/' . $template_name;
		if ( file_exists( $custom_template ) ) {
			return $custom_template;
		}
	}
	return $template;
}

/**
 * Price placeholder untuk konsistensi tinggi produk
 * Menambahkan placeholder jika produk tidak punya harga
 */
add_action('woocommerce_after_shop_loop_item_title', function () {
    global $product;

    if (!$product) return;

    // Kalau sudah ada harga, WooCommerce sudah render <span class="price">...</span>
    // Kalau tidak ada harga, kita render placeholder <span class="price"> supaya tinggi konsisten
    $price = $product->get_price();
    if (empty($price) || $price === '') {
        echo '<span class="price price--placeholder">&nbsp;</span>';
    }
}, 25); // Priority 25 untuk memastikan dijalankan setelah price (10)

/**
 * Cart page redirect removed.
 * Users expect "View cart" to go to the cart page.
 */

/**
 * WooCommerce specific scripts & stylesheets.
 */
function wp_augoose_woocommerce_scripts() {
	// Enqueue WooCommerce custom styles
	$css_rel  = '/assets/css/woocommerce-custom.css';
	$css_file = get_template_directory() . $css_rel;
	if ( file_exists( $css_file ) ) {
		wp_enqueue_style(
			'wp-augoose-woocommerce-custom',
			get_template_directory_uri() . $css_rel,
			array(),
			(string) filemtime( $css_file )
		);
	}

	// Star rating font
	$font_path   = WC()->plugin_url() . '/assets/fonts/';
	$inline_font = '@font-face {
		font-family: "star";
		src: url("' . $font_path . 'star.eot");
		src: url("' . $font_path . 'star.eot?#iefix") format("embedded-opentype"),
			url("' . $font_path . 'star.woff") format("woff"),
			url("' . $font_path . 'star.ttf") format("truetype"),
			url("' . $font_path . 'star.svg#star") format("svg");
		font-weight: normal;
		font-style: normal;
	}';

	wp_add_inline_style( 'wp-augoose-woocommerce-custom', $inline_font );
}
add_action( 'wp_enqueue_scripts', 'wp_augoose_woocommerce_scripts' );

/**
 * Disable the default WooCommerce stylesheet.
 */
add_filter(
	'woocommerce_enqueue_styles',
	function ( $styles ) {
		// Only disable Woo core styles if our integrated stylesheet exists on disk.
		// This prevents "unstyled/chaotic layout" on servers where assets failed to upload.
		$integrated = get_template_directory() . '/assets/css/woocommerce-integrated.css';
		if ( file_exists( $integrated ) ) {
			return array();
		}
		return $styles;
	},
	20
);

/**
 * =========================
 * Transient Operations with Deadlock Protection
 * =========================
 * 
 * Wrapper functions to handle database deadlocks gracefully
 * Prevents errors when multiple processes try to write to database simultaneously
 */

/**
 * Safe transient setter with retry logic and deadlock handling
 * 
 * @param string $transient Transient name
 * @param mixed  $value     Value to store
 * @param int    $expiration Expiration time in seconds
 * @return bool True on success, false on failure
 */
function wp_augoose_set_transient_safe( $transient, $value, $expiration = 0 ) {
	$max_retries = 3;
	$retry_delay = 0.1; // 100ms
	
	for ( $attempt = 1; $attempt <= $max_retries; $attempt++ ) {
		try {
			$result = set_transient( $transient, $value, $expiration );
			if ( $result !== false ) {
				return true;
			}
		} catch ( Exception $e ) {
			// Log error but continue retrying
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( sprintf(
					'[WP_AUGOOSE] Transient set error (attempt %d/%d): %s',
					$attempt,
					$max_retries,
					$e->getMessage()
				) );
			}
		}
		
		// Check for database deadlock error
		global $wpdb;
		if ( isset( $wpdb->last_error ) && 
		     ( strpos( $wpdb->last_error, 'Deadlock' ) !== false || 
		       strpos( $wpdb->last_error, 'try restarting transaction' ) !== false ) ) {
			// Wait before retry (exponential backoff)
			usleep( $retry_delay * 1000000 * $attempt );
			continue;
		}
		
		// If not a deadlock, break immediately
		break;
	}
	
	// If all retries failed, log and return false
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( sprintf(
			'[WP_AUGOOSE] Failed to set transient after %d attempts: %s',
			$max_retries,
			$transient
		) );
	}
	
	return false;
}

/**
 * Suppress database deadlock errors from all plugins and WordPress core
 * This prevents error logs from being flooded with deadlock messages
 * 
 * Note: Deadlocks are usually transient and resolve automatically on retry.
 * Plugins and WordPress core will retry the operation automatically.
 * 
 * We intercept WordPress database error logging to suppress transient deadlock errors.
 */
add_action( 'shutdown', 'wp_augoose_suppress_transient_deadlock_errors', 999 );
function wp_augoose_suppress_transient_deadlock_errors() {
	global $wpdb;
	
	// Check if last error is a deadlock related to transient operations
	if ( ! empty( $wpdb->last_error ) && 
	     ( strpos( $wpdb->last_error, 'Deadlock' ) !== false || 
	       strpos( $wpdb->last_error, 'try restarting transaction' ) !== false ) ) {
		
		// Suppress deadlock errors for transient operations (all plugins and WordPress core)
		// These include: PayPal Commerce, WooCommerce Blocks, WordPress core blocks, etc.
		if ( ! empty( $wpdb->last_query ) && 
		     ( strpos( $wpdb->last_query, '_transient' ) !== false ||
		       strpos( $wpdb->last_query, 'ppcp' ) !== false || 
		       strpos( $wpdb->last_query, 'paypal' ) !== false ||
		       strpos( $wpdb->last_query, 'woocommerce_blocks' ) !== false ||
		       strpos( $wpdb->last_query, 'wp_core_block' ) !== false ) ) {
			
			// Clear the error to prevent it from being logged
			// WordPress and plugins will retry automatically, so this error is expected and harmless
			$wpdb->last_error = '';
			
			// Optionally log a single summary message (only once per request) in debug mode
			static $logged = false;
			if ( ! $logged && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$logged = true;
				error_log( '[WP_AUGOOSE] Suppressed transient deadlock error (operation will retry automatically)' );
			}
		}
	}
}

/**
 * Suppress PHP warnings for harmless errors from plugins and WordPress core
 * This prevents error logs from being flooded with non-critical warnings
 * 
 * Note: These warnings are usually harmless and don't affect functionality.
 * We only suppress in production - keep warnings in debug mode for development.
 */
add_action( 'init', 'wp_augoose_suppress_harmless_warnings', 1 );
function wp_augoose_suppress_harmless_warnings() {
	// CRITICAL: Exclude WordPress Customizer requests
	// Customizer needs to see warnings/notices for debugging
	if ( isset( $_REQUEST['customize_changeset_uuid'] ) || 
	     isset( $_GET['customize_changeset_uuid'] ) || 
	     isset( $_POST['customize_changeset_uuid'] ) ||
	     is_customize_preview() ) {
		return; // Don't suppress warnings in Customizer
	}
	
	// Only suppress in production (keep warnings in debug mode for development)
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		// Get existing error handler to chain it
		$previous_handler = set_error_handler( null );
		
		// Set our custom error handler
		set_error_handler( function( $errno, $errstr, $errfile, $errline ) use ( $previous_handler ) {
			// Suppress array to string conversion warnings from WordPress core
			if ( $errno === E_WARNING && 
			     strpos( $errstr, 'Array to string conversion' ) !== false &&
			     ( strpos( $errfile, 'wp-includes/formatting.php' ) !== false ||
			       strpos( $errfile, 'wp-includes' ) !== false ) ) {
				return true; // Suppress this warning
			}
			
			// Suppress array offset warnings from DOKU Payment plugin
			// DOKU plugin has known issues accessing array offsets without validation
			if ( $errno === E_WARNING && 
			     ( strpos( $errstr, 'Trying to access array offset' ) !== false ||
			       strpos( $errstr, 'Array to string conversion' ) !== false ||
			       strpos( $errstr, 'Undefined array key' ) !== false ) &&
			     strpos( $errfile, 'doku-payment' ) !== false ) {
				return true; // Suppress this warning
			}
			
			// Pass other errors to previous handler or default handler
			if ( $previous_handler && is_callable( $previous_handler ) ) {
				return call_user_func( $previous_handler, $errno, $errstr, $errfile, $errline );
			}
			
			return false; // Use default error handler
		}, E_WARNING );
	}
}

/**
 * =========================
 * Price Display Logic (BULLETPROOF Flow - FIXED)
 * =========================
 * 
 * CRITICAL FIX: Set currency SEKALI di awal request, jangan set ulang di fungsi price!
 * 
 * Urutan logic:
 * 1. Di awal request (template_redirect): tentukan currency SEKALI
 *    - Priority 1: WCML cookie/session (user manual select) - JANGAN override
 *    - Priority 2: Auto-detect dari geolocation (hanya first visit)
 *    - Priority 3: Base currency
 * 2. Set currency context via WCML SEKALI di awal
 * 3. Untuk output harga: cukup return $product->get_price_html()
 *    - JANGAN set currency lagi di fungsi price!
 *    - Biarkan WCML + WooCommerce handle conversion, tax, formatting
 * 
 * PENTING:
 * - JANGAN set currency di tengah request (bisa bikin cache campur → harga beda)
 * - JANGAN hitung harga manual untuk variable product
 * - JANGAN hook woocommerce_get_price_html yang memanggil get_price_html() lagi
 * - JANGAN bikin cookie currency sendiri kalau WCML sudah ada
 * - Hapus manual price check (biarkan WCML handle)
 */

/**
 * Step 1: Geolocate user country from IP
 * Returns country code (e.g., 'US', 'SG', 'MY', 'ID')
 * 
 * BULLETPROOF: Cache 24 jam, fail-safe (jangan retry berkali-kali), tidak bikin cookie currency sendiri
 */
function wp_augoose_get_user_country_from_ip() {
	// Check if already cached in cookie (24 hours)
	$cookie_name = 'wp_augoose_user_country';
	if ( isset( $_COOKIE[ $cookie_name ] ) ) {
		$cached = sanitize_text_field( $_COOKIE[ $cookie_name ] );
		if ( ! empty( $cached ) && strlen( $cached ) === 2 ) {
			return $cached;
		}
	}
	
	// Get IP address
	$ip = '';
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// X-Forwarded-For can contain multiple IPs, get the first one
		$ips = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
		$ip = sanitize_text_field( trim( $ips[0] ) );
	} else {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '';
	}
	
	// Skip localhost/private IPs
	if ( empty( $ip ) || $ip === '127.0.0.1' || $ip === '::1' || strpos( $ip, '192.168.' ) === 0 || strpos( $ip, '10.' ) === 0 ) {
		return '';
	}
	
	// Try geolocation API (with fail-safe - only try once per request)
	$country = '';
	
	// Use ip-api.com (free, no API key required, but has rate limit)
	// Alternative: ipapi.co (requires API key for production)
	$api_url = 'http://ip-api.com/json/' . $ip . '?fields=status,countryCode';
	
	// Use transient to prevent multiple API calls in same request
	$transient_key = 'wp_augoose_geo_' . md5( $ip );
	$cached_result = get_transient( $transient_key );
	
	if ( $cached_result !== false ) {
		$country = $cached_result;
	} else {
		// Make API call (with timeout and error handling)
		$response = wp_remote_get( $api_url, array(
			'timeout' => 3, // 3 seconds timeout
			'sslverify' => true,
		) );
		
		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );
			
			if ( isset( $data['status'] ) && $data['status'] === 'success' && isset( $data['countryCode'] ) ) {
				$country = strtoupper( sanitize_text_field( $data['countryCode'] ) );
				// Cache in transient for 1 hour (to prevent rate limiting)
				// Use safe transient setter to handle potential deadlocks
				wp_augoose_set_transient_safe( $transient_key, $country, HOUR_IN_SECONDS );
			}
		}
		
		// If API failed, cache empty result for 5 minutes to prevent retry spam
		if ( empty( $country ) ) {
			wp_augoose_set_transient_safe( $transient_key, '', 5 * MINUTE_IN_SECONDS );
		}
	}
	
	// Cache country in cookie (24 hours) - only if we got a valid result
	if ( ! empty( $country ) && strlen( $country ) === 2 ) {
		setcookie( $cookie_name, $country, time() + ( 24 * 60 * 60 ), '/', '', is_ssl(), true );
		$_COOKIE[ $cookie_name ] = $country;
	}
	
	return $country;
}

/**
 * Step 2: Determine currency based on country/rules
 * Returns currency code (e.g., 'USD', 'SGD', 'MYR', 'IDR')
 * 
 * BULLETPROOF: Hanya return currency code, JANGAN set currency di sini!
 * Currency akan di-set SEKALI di awal request via hook.
 */
function wp_augoose_determine_currency( $country = '' ) {
	// Priority 1: Check WCML currency (from WCML cookie/session - jangan override)
	if ( class_exists( 'WCML_Multi_Currency' ) ) {
		global $woocommerce_wpml;
		if ( isset( $woocommerce_wpml->multi_currency ) ) {
			$currency = $woocommerce_wpml->multi_currency->get_client_currency();
			if ( $currency ) {
				// User sudah pilih currency manual atau WCML sudah set - jangan override
				return $currency;
			}
		}
	}
	
	// Priority 2: Auto-detect from country (hanya kalau WCML belum set currency)
	// Ini hanya untuk "first visit" - setelah user pilih manual, WCML akan handle
	if ( $country ) {
		$country_currency_map = array(
			'US' => 'USD',
			'SG' => 'SGD',
			'MY' => 'MYR',
			'ID' => 'IDR',
			// Add more mappings as needed
		);
		
		if ( isset( $country_currency_map[ $country ] ) ) {
			$suggested_currency = $country_currency_map[ $country ];
			
			// Check if currency is available in WCML (hanya check, jangan set)
			if ( class_exists( 'WCML_Multi_Currency' ) ) {
				global $woocommerce_wpml;
				if ( isset( $woocommerce_wpml->multi_currency ) ) {
					$available_currencies = $woocommerce_wpml->multi_currency->get_currency_codes();
					if ( in_array( $suggested_currency, $available_currencies, true ) ) {
						return $suggested_currency;
					}
				}
			}
		}
	}
	
	// Priority 3: Use WooCommerce base currency
	return get_woocommerce_currency();
}

/**
 * CRITICAL: Set currency SEKALI di awal request
 * 
 * Ini harus dipanggil sebelum produk di-load untuk mencegah cache campur currency.
 * Hook di template_redirect dengan priority kecil (10-20) untuk set sebelum WooCommerce load produk.
 */
add_action( 'template_redirect', 'wp_augoose_set_currency_once', 10 );
function wp_augoose_set_currency_once() {
	// Skip admin dan AJAX (kecuali frontend AJAX)
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	
	// Skip jika WCML tidak aktif
	if ( ! class_exists( 'WCML_Multi_Currency' ) ) {
		return;
	}
	
	global $woocommerce_wpml;
	if ( ! isset( $woocommerce_wpml->multi_currency ) ) {
		return;
	}
	
	// Priority 1: Check WCML currency (user manual select atau sudah set)
	$currency = $woocommerce_wpml->multi_currency->get_client_currency();
	$currency_source = 'wcml_cookie';
	
	// Priority 2: Jika belum ada, coba geolocation
	if ( ! $currency ) {
		$country = wp_augoose_get_user_country_from_ip();
		$currency = wp_augoose_determine_currency( $country );
		$currency_source = $country ? 'geolocation' : 'base';
	}
	
	// Priority 3: Fallback ke base currency
	if ( ! $currency ) {
		$currency = get_woocommerce_currency();
		$currency_source = 'base';
	}
	
	// Set currency SEKALI di awal request
	// Setelah ini, jangan set ulang di fungsi price!
	if ( $currency ) {
		$woocommerce_wpml->multi_currency->set_client_currency( $currency );
		
		// Debug logging (guarded by WP_DEBUG)
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$country = wp_augoose_get_user_country_from_ip();
			$base_currency = get_woocommerce_currency();
			$tax_display = get_option( 'woocommerce_tax_display_shop', 'excl' );
			$price_decimals = wc_get_price_decimals();
			$price_rounding = get_option( 'wcml_currency_switcher_rounding', 'disabled' );
			
			error_log( sprintf(
				'[WP_AUGOOSE_CURRENCY] Set currency: %s (source: %s, country: %s, base: %s, tax_display: %s, decimals: %d, rounding: %s)',
				$currency,
				$currency_source,
				$country ? $country : 'unknown',
				$base_currency,
				$tax_display,
				$price_decimals,
				$price_rounding
			) );
		}
	}
}

/**
 * REMOVED: wp_augoose_get_product_price_for_currency()
 * 
 * Fungsi ini dihapus karena:
 * 1. Menghitung harga manual untuk variable product (get_variation_regular_price('min'))
 *    bisa tidak sejalan dengan WCML yang handle per variation
 * 2. Tidak perlu - cukup set currency context, lalu biarkan WCML + WooCommerce
 *    handle conversion via filter
 * 
 * Gunakan wp_augoose_get_product_price_html() saja yang lebih aman.
 */

/**
 * Get formatted price HTML
 * 
 * CRITICAL FIX: Jangan set currency di sini! Currency sudah di-set SEKALI di awal request.
 * 
 * BULLETPROOF IMPLEMENTATION:
 * 1. Currency sudah di-set di template_redirect hook (sekali di awal request)
 * 2. Cukup return $product->get_price_html() - biarkan WCML + WooCommerce handle
 * 3. Untuk variable product: biarkan get_price_html() handle min/max
 * 4. Tidak hook woocommerce_get_price_html untuk mencegah infinite loop
 * 
 * @param WC_Product $product Product object
 * @param string     $target_currency DEPRECATED - tidak digunakan lagi (currency sudah di-set di awal request)
 * @return string Formatted price HTML
 */
function wp_augoose_get_product_price_html( $product, $target_currency = '' ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}
	
	// CRITICAL: Jangan set currency di sini!
	// Currency sudah di-set SEKALI di awal request via wp_augoose_set_currency_once()
	// Setting currency di tengah request bisa bikin cache campur → harga beda (63 vs 64)
	
	// Cukup return get_price_html() - WCML + WooCommerce akan handle:
	// - Tax display (inc/exc) via WooCommerce settings
	// - Formatting (decimals, symbol) via currency settings
	// - WCML conversion (manual price atau auto convert) via filter
	// - Variable product min/max price via WooCommerce internal logic
	$price_html = $product->get_price_html();
	
	// Debug logging (guarded by WP_DEBUG)
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$current_currency = class_exists( 'WCML_Multi_Currency' ) && isset( $GLOBALS['woocommerce_wpml']->multi_currency ) 
			? $GLOBALS['woocommerce_wpml']->multi_currency->get_client_currency() 
			: get_woocommerce_currency();
		$price_raw = $product->get_price();
		$tax_display = get_option( 'woocommerce_tax_display_shop', 'excl' );
		
		error_log( sprintf(
			'[WP_AUGOOSE_PRICE] Product %d: currency=%s, raw_price=%s, tax_display=%s, html=%s',
			$product->get_id(),
			$current_currency,
			$price_raw,
			$tax_display,
			substr( strip_tags( $price_html ), 0, 50 )
		) );
	}
	
	return $price_html;
}

/**
 * =========================
 * Wishlist (Integrated)
 * - Logged-in users: user_meta `_wp_augoose_wishlist`
 * - Guests: cookie `wp_augoose_wishlist`
 * =========================
 */

function wp_augoose_wishlist_cookie_name() {
	return 'wp_augoose_wishlist';
}

function wp_augoose_wishlist_get_ids() {
	$ids = array();

	// Logged-in user meta
	if ( is_user_logged_in() ) {
		$stored = get_user_meta( get_current_user_id(), '_wp_augoose_wishlist', true );
		if ( is_array( $stored ) ) {
			$ids = $stored;
		} elseif ( is_string( $stored ) && $stored !== '' ) {
			$decoded = json_decode( $stored, true );
			if ( is_array( $decoded ) ) {
				$ids = $decoded;
			}
		}
	}

	// Guest cookie (or merge into user)
	$cookie_name = wp_augoose_wishlist_cookie_name();
	if ( isset( $_COOKIE[ $cookie_name ] ) ) {
		$cookie_val = wp_unslash( $_COOKIE[ $cookie_name ] );
		$decoded    = json_decode( $cookie_val, true );
		if ( is_array( $decoded ) ) {
			$cookie_ids = $decoded;
			if ( is_user_logged_in() ) {
				$ids = array_merge( $ids, $cookie_ids );
			} else {
				$ids = $cookie_ids;
			}
		}
	}

	$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
	return $ids;
}

function wp_augoose_wishlist_set_ids( $ids ) {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );

	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), '_wp_augoose_wishlist', $ids );
		// keep cookie for convenience as well (so header count works without AJAX)
	}

	$cookie_name = wp_augoose_wishlist_cookie_name();
	$payload     = wp_json_encode( $ids );
	$secure      = is_ssl();

	// Session cookie (expires when browser closes) for better UX
	// This ensures wishlist resets on new device/session
	setcookie(
		$cookie_name,
		$payload,
		0, // Session cookie - expires when browser closes
		COOKIEPATH ? COOKIEPATH : '/',
		COOKIE_DOMAIN,
		$secure,
		true
	);
	$_COOKIE[ $cookie_name ] = $payload; // make available in same request
}

function wp_augoose_wishlist_count() {
	return count( wp_augoose_wishlist_get_ids() );
}

function wp_augoose_wishlist_render_items_html( $ids ) {
	if ( empty( $ids ) ) {
		return '<p class="wishlist-empty">Your wishlist is empty.</p>';
	}

	// CRITICAL FIX: Jangan set currency di sini!
	// Currency sudah di-set SEKALI di awal request via wp_augoose_set_currency_once()
	// Setting currency di tengah request bisa bikin cache campur → harga beda

	$q = new WP_Query(
		array(
			'post_type'      => 'product',
			'post__in'       => $ids,
			'orderby'        => 'post__in',
			'posts_per_page' => 50,
		)
	);

	ob_start();
	echo '<div class="wishlist-items">';
	if ( $q->have_posts() ) {
		global $product; // Set global for WooCommerce hooks/filters
		
		while ( $q->have_posts() ) {
			$q->the_post();
			$pid = get_the_ID();
			
			// Get product object - force fresh load untuk mencegah cache issue
			// CRITICAL: Untuk variable product, perlu clear cache agar harga benar per product
			$product = wc_get_product( $pid );
			if ( ! $product ) {
				continue;
			}
			
			// CRITICAL FIX: Clear variable product price cache untuk memastikan harga fresh
			// Variable product cache bisa bikin semua produk tampil harga sama
			if ( $product->is_type( 'variable' ) ) {
				// Clear WooCommerce variable product price cache
				delete_transient( 'wc_var_prices_' . $pid );
				// Force refresh price calculation
				$product->get_price(); // Trigger price calculation
			}
			
			$link = get_permalink( $pid );
			$img  = $product->get_image( 'woocommerce_thumbnail' );
			
			// Get price HTML - currency sudah di-set di awal request
			// Pastikan setiap product di-load dengan currency context yang benar
			$price = wp_augoose_get_product_price_html( $product );
			
			// Debug logging untuk wishlist (guarded by WP_DEBUG)
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$current_currency = class_exists( 'WCML_Multi_Currency' ) && isset( $GLOBALS['woocommerce_wpml']->multi_currency ) 
					? $GLOBALS['woocommerce_wpml']->multi_currency->get_client_currency() 
					: get_woocommerce_currency();
				$price_raw = $product->get_price();
				$is_variable = $product->is_type( 'variable' );
				
				error_log( sprintf(
					'[WP_AUGOOSE_WISHLIST] Product %d: currency=%s, raw_price=%s, is_variable=%s, html=%s',
					$pid,
					$current_currency,
					$price_raw,
					$is_variable ? 'yes' : 'no',
					substr( strip_tags( $price ), 0, 50 )
				) );
			}
			
			$is_variable = $product->is_type( 'variable' );
			$is_simple   = $product->is_type( 'simple' );
			?>
			<div class="wishlist-item" data-product-id="<?php echo esc_attr( $pid ); ?>">
				<a class="wishlist-item-thumb" href="<?php echo esc_url( $link ); ?>">
					<?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<div class="wishlist-item-info">
					<div class="wishlist-item-title">
						<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
					</div>
					<div class="wishlist-item-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
					<div class="wishlist-item-actions">
						<?php if ( $is_simple && $product->is_in_stock() ) : ?>
							<button type="button" class="wishlist-add-to-cart" data-product-id="<?php echo esc_attr( $pid ); ?>">Add to cart</button>
						<?php else : ?>
							<a class="wishlist-choose-options" href="<?php echo esc_url( $link ); ?>">Choose options</a>
						<?php endif; ?>
						<button type="button" class="wishlist-remove" data-product-id="<?php echo esc_attr( $pid ); ?>" aria-label="<?php esc_attr_e( 'Remove from wishlist', 'wp-augoose' ); ?>">×</button>
					</div>
				</div>
			</div>
			<?php
		}
		wp_reset_postdata();
	}
	echo '</div>';
	return ob_get_clean();
}

function wp_augoose_ajax_wishlist_toggle() {
	// Security: Verify nonce (allow empty nonce for guests, but verify if provided)
	if ( isset( $_POST['nonce'] ) && ! empty( $_POST['nonce'] ) ) {
		$nonce_check = check_ajax_referer( 'wp_augoose_nonce', 'nonce', false );
		if ( ! $nonce_check ) {
			wp_send_json_error( array( 
				'message' => 'Security check failed. Please refresh the page and try again.',
				'debug' => array(
					'nonce_received' => 'yes',
					'nonce_valid' => false
				)
			) );
			return;
		}
	}
	// If no nonce provided, continue anyway (for backward compatibility with simple script)
	
	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_send_json_error( array( 'message' => 'WooCommerce not available' ) );
		return;
	}

	// Get and validate product_id
	if ( ! isset( $_POST['product_id'] ) ) {
		wp_send_json_error( array( 'message' => 'Product ID is missing' ) );
		return;
	}
	
	if ( function_exists( 'wp_augoose_sanitize_product_id' ) ) {
		$product_id = wp_augoose_sanitize_product_id( $_POST['product_id'] );
	} else {
		$product_id = absint( $_POST['product_id'] );
	}
	
	if ( ! $product_id || $product_id <= 0 ) {
		wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
		return;
	}

	// Validate product exists and is a product
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		wp_send_json_error( array( 'message' => 'Product not found' ) );
		return;
	}

	// Get current wishlist
	$ids = wp_augoose_wishlist_get_ids();
	
	// Toggle product in wishlist
	if ( in_array( $product_id, $ids, true ) ) {
		// Remove from wishlist
		$ids = array_values( array_diff( $ids, array( $product_id ) ) );
		$action = 'removed';
	} else {
		// Add to wishlist
		array_unshift( $ids, $product_id );
		$ids    = array_values( array_unique( $ids ) );
		$action = 'added';
	}
	
	// Save wishlist
	wp_augoose_wishlist_set_ids( $ids );

	// Return success response
	wp_send_json_success(
		array(
			'action' => $action,
			'count'  => count( $ids ),
			'ids'    => $ids,
		)
	);
}
add_action( 'wp_ajax_wp_augoose_wishlist_toggle', 'wp_augoose_ajax_wishlist_toggle' );
add_action( 'wp_ajax_nopriv_wp_augoose_wishlist_toggle', 'wp_augoose_ajax_wishlist_toggle' );

function wp_augoose_ajax_wishlist_get() {
	// Security: Verify nonce
	if ( function_exists( 'wp_augoose_verify_ajax_nonce' ) ) {
		wp_augoose_verify_ajax_nonce( 'wp_augoose_nonce', 'nonce' );
	} else {
		check_ajax_referer( 'wp_augoose_nonce', 'nonce' );
	}
	
	// CRITICAL FIX: Jangan set currency di sini!
	// Currency sudah di-set SEKALI di awal request via wp_augoose_set_currency_once()
	// Untuk AJAX, currency context sudah di-set sebelum AJAX handler dipanggil
	
	$ids  = wp_augoose_wishlist_get_ids();
	$html = wp_augoose_wishlist_render_items_html( $ids );
	
	wp_send_json_success(
		array(
			'count' => count( $ids ),
			'html'  => $html,
		)
	);
}
add_action( 'wp_ajax_wp_augoose_wishlist_get', 'wp_augoose_ajax_wishlist_get' );
add_action( 'wp_ajax_nopriv_wp_augoose_wishlist_get', 'wp_augoose_ajax_wishlist_get' );

function wp_augoose_wishlist_shortcode() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}
	$ids  = wp_augoose_wishlist_get_ids();
	$html = wp_augoose_wishlist_render_items_html( $ids );
	$checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/' );
	return '<div class="wishlist-page"><h1 class="wishlist-page-title">Wishlist</h1>' . $html . '<div class="wishlist-footer"><a class="wishlist-checkout" href="' . esc_url( $checkout_url ) . '">Checkout</a></div></div>';
}
add_shortcode( 'wp_augoose_wishlist', 'wp_augoose_wishlist_shortcode' );

/**
 * Force all WooCommerce text to English (hardcoded)
 */
add_filter( 'gettext', 'wp_augoose_force_english_text', 20, 3 );
function wp_augoose_force_english_text( $translated_text, $text, $domain ) {
	if ( $domain === 'woocommerce' ) {
		$english_texts = array(
			'Menampilkan semua %d hasil' => 'Showing all %d results',
			'Menampilkan %d-%d dari %d hasil' => 'Showing %d-%d of %d results',
			'Pengurutan standar' => 'Default sorting',
			'Urutkan berdasarkan popularitas' => 'Sort by popularity',
			'Urutkan berdasarkan rating rata-rata' => 'Sort by average rating',
			'Urutkan berdasarkan terbaru' => 'Sort by latest',
			'Urutkan berdasarkan harga: rendah ke tinggi' => 'Sort by price: low to high',
			'Urutkan berdasarkan harga: tinggi ke rendah' => 'Sort by price: high to low',
			'SARING' => 'Filter',
			'Saring' => 'Filter',
			'saring' => 'Filter',
			'Filter' => 'Filter',
			// Checkout translations
			'TAMBAH KE KERANJANG' => 'ADD TO CART',
			'Tambah ke keranjang' => 'Add to cart',
			'tambah ke keranjang' => 'Add to cart',
			// Add to cart messages
			'%s telah ditambahkan ke keranjang Anda.' => '%s has been added to your cart.',
			'%s telah ditambahkan' => '%s has been added',
			'telah ditambahkan ke keranjang' => 'has been added to your cart',
			'Ditambahkan ke keranjang' => 'Added to cart',
			'ditambahkan ke keranjang' => 'added to cart',
			'Berhasil ditambahkan' => 'Successfully added',
			'Gagal menambahkan produk' => 'Failed to add product',
			'Produk tidak ditemukan' => 'Product not found',
			'ID produk tidak valid' => 'Invalid product ID',
			'Produk tidak dapat dibeli' => 'Product is not purchasable',
			'Produk habis' => 'Product is out of stock',
			'Produk kehabisan stok' => 'Product is out of stock',
			'Silakan pilih opsi produk' => 'Please choose product options',
			'Keranjang diperbarui' => 'Cart updated',
			'Item keranjang tidak valid' => 'Invalid cart item',
			// Cart removal messages
			'%s telah dihapus dari keranjang karena tidak bisa dibeli lagi. Hubungi kami jika Anda butuh bantuan.' => '%s has been removed from your cart because it can no longer be purchased. Please contact us if you need assistance.',
			'%s telah dihapus dari keranjang' => '%s has been removed from your cart',
			'telah dihapus dari keranjang' => 'has been removed from your cart',
			'tidak bisa dibeli lagi' => 'can no longer be purchased',
			'Hubungi kami jika Anda butuh bantuan' => 'Please contact us if you need assistance',
			'Hubungi kami jika Anda memerlukan bantuan' => 'Please contact us if you need assistance',
			'Masuk' => 'Login',
			'masuk' => 'Login',
			'Alamat penagihan' => 'Billing address',
			'Alamat pengiriman' => 'Shipping address',
			'Metode pembayaran' => 'Payment method',
			'Ringkasan pesanan' => 'Order summary',
			'Kode kupon' => 'Coupon code',
			'Terapkan kupon' => 'Apply coupon',
			'Terapkan' => 'Apply',
			'Masukkan kode kupon' => 'Enter coupon code',
			'Tempatkan pesanan' => 'Place order',
			'Bayar sekarang' => 'Pay now',
			'Kirim ke alamat yang berbeda?' => 'Ship to a different address?',
			'Informasi tambahan' => 'Additional information',
			'Catatan pesanan' => 'Order notes',
			'Detail penagihan' => 'Billing details',
			'Detail pengiriman' => 'Shipping details',
			// Shipping labels
			'PENGIRIMAN' => 'SHIPPING',
			'Pengiriman' => 'Shipping',
			'pengiriman' => 'shipping',
			'PENGIRIMAN GRATIS' => 'FREE SHIPPING',
			'Pengiriman Gratis' => 'Free Shipping',
			'pengiriman gratis' => 'free shipping',
			// Coupon
			'Punya kupon? Klik di sini untuk memasukkan kode Anda' => 'Have a coupon? Click here to enter your code',
			'Punya kupon?' => 'Have a coupon?',
			'Klik di sini untuk memasukkan kode Anda' => 'Click here to enter your code',
			// Payment method error
			'Maaf, tampaknya tidak ada metode pembayaran yang tersedia untuk lokasi Anda. Silakan hubungi kami jika Anda memerlukan bantuan atau ingin menggunakan alternatif yang lain.' => 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.',
			'Maaf, tampaknya tidak ada' => 'Sorry, it seems that there are no',
			'metode pembayaran yang' => 'available payment methods',
			'tersedia untuk lokasi Anda.' => 'for your location.',
			'Silakan hubungi kami jika' => 'Please contact us if',
			'Anda memerlukan bantuan' => 'you require assistance',
			'atau ingin menggunakan' => 'or wish to use',
			'alternatif yang lain.' => 'another alternative.',
			// Newsletter
			'BERLANGGANAN BULETIN KAMI' => 'SUBSCRIBE TO OUR NEWSLETTER',
			'Berlangganan buletin kami' => 'Subscribe to our newsletter',
			'berlangganan buletin kami' => 'subscribe to our newsletter',
			// Place order
			'BUAT PESANAN' => 'PLACE ORDER',
			'Buat pesanan' => 'Place order',
			'buat pesanan' => 'place order',
			// Payment gateway button texts
			'Bayar Pesanan Dengan DOKU Checkout' => 'Pay Order With DOKU Checkout',
			'Bayar pesanan dengan DOKU Checkout' => 'Pay Order With DOKU Checkout',
			'Bayar sekarang' => 'Pay now',
			'Bayar Sekarang' => 'Pay Now',
			'Bayar di tempat' => 'Cash on Delivery',
			'Bayar di Tempat' => 'Cash on Delivery',
			// Form field labels
			'Nama depan' => 'First name',
			'nama depan' => 'First name',
			'Nama belakang' => 'Last name',
			'nama belakang' => 'Last name',
			'Nama lengkap' => 'Full name',
			'nama lengkap' => 'Full name',
			'Perusahaan' => 'Company',
			'perusahaan' => 'Company',
			'Alamat' => 'Address',
			'alamat' => 'Address',
			'Alamat baris 1' => 'Address line 1',
			'alamat baris 1' => 'Address line 1',
			'Alamat baris 2' => 'Address line 2',
			'alamat baris 2' => 'Address line 2',
			'Kota' => 'City',
			'kota' => 'City',
			'Provinsi' => 'State / County',
			'provinsi' => 'State / County',
			'Kode pos' => 'Postcode / ZIP',
			'kode pos' => 'Postcode / ZIP',
			'Kode Pos' => 'Postcode / ZIP',
			'Kode POS' => 'Postcode / ZIP',
			'Negara' => 'Country / Region',
			'negara' => 'Country / Region',
			'Negara / Wilayah' => 'Country / Region',
			'negara / wilayah' => 'Country / Region',
			'Negara/Wilayah' => 'Country / Region',
			'negara/wilayah' => 'Country / Region',
			'Wilayah' => 'Region',
			'wilayah' => 'Region',
			'Update country / region' => 'Update country / region',
			'Update country / region&hellip;' => 'Update country / region',
			'Telepon' => 'Phone',
			'telepon' => 'Phone',
			'Email' => 'Email address',
			'email' => 'Email address',
			'Catatan pesanan' => 'Order notes',
			'catatan pesanan' => 'Order notes',
			'Catatan tentang pesanan Anda' => 'Notes about your order, e.g. special notes for delivery.',
			'catatan tentang pesanan Anda' => 'Notes about your order, e.g. special notes for delivery.',
			// Additional form field labels
			'Nama' => 'Name',
			'nama' => 'Name',
			'Email address' => 'Email address',
			'Email address (optional)' => 'Email address (optional)',
			'Country/Region' => 'Country / Region',
			'Country/Region (optional)' => 'Country / Region (optional)',
			'Country / Wilayah' => 'Country / Region',
			'Negara / Region' => 'Country / Region',
			'Pilih negara / wilayah' => 'Select a country / region...',
			'pilih negara / wilayah' => 'Select a country / region...',
			'Pilih Negara / Wilayah' => 'Select a country / region...',
			'Pilih negara / wilayah&hellip;' => 'Select a country / region...',
			'First name (optional)' => 'First name (optional)',
			'Last name (optional)' => 'Last name (optional)',
			'Company name' => 'Company name',
			'Company name (optional)' => 'Company name (optional)',
			'Address (optional)' => 'Address (optional)',
			'City (optional)' => 'City (optional)',
			'State / County (optional)' => 'State / County (optional)',
			'Postcode / ZIP (optional)' => 'Postcode / ZIP (optional)',
			'Phone (optional)' => 'Phone (optional)',
			// Address field labels
			'ALAMAT JALAN' => 'Address',
			'Alamat jalan' => 'Address',
			'alamat jalan' => 'Address',
			'Street address' => 'Address',
			// Address placeholders
			'Nomor rumah dan nama jalan' => 'House number and street name',
			'nomor rumah dan nama jalan' => 'House number and street name',
			'Apartemen, suit, unit, dll. (opsional)' => 'Apartment, suite, unit, etc. (optional)',
			'apartemen, suit, unit, dll. (opsional)' => 'Apartment, suite, unit, etc. (optional)',
			'Apartemen, suite, unit, dll. (opsional)' => 'Apartment, suite, unit, etc. (optional)',
			// Country names
			'Amerika Serikat' => 'United States',
			'amerika serikat' => 'United States',
			// Choose option
			'Pilih opsi' => 'Choose an option',
			'pilih opsi' => 'Choose an option',
			'Pilih Opsi' => 'Choose an option',
			'Choose an option' => 'Choose an option',
		);
		
		if ( isset( $english_texts[ $translated_text ] ) ) {
			return $english_texts[ $translated_text ];
		}
		
		// Force "Filter" for price filter button
		if ( strpos( $translated_text, 'SARING' ) !== false || strpos( $translated_text, 'Saring' ) !== false ) {
			return 'Filter';
		}
		
		// Fallback: return original English text if translation exists
		if ( $text !== $translated_text && strpos( $translated_text, 'Menampilkan' ) !== false ) {
			return str_replace( array( 'Menampilkan', 'hasil', 'Pengurutan', 'standar' ), array( 'Showing', 'results', 'Sorting', 'default' ), $translated_text );
		}
		
		// Additional fallback for common Indonesian phrases
		if ( strpos( $translated_text, 'kupon' ) !== false || strpos( $translated_text, 'Kupon' ) !== false ) {
			if ( strpos( $translated_text, 'Punya' ) !== false || strpos( $translated_text, 'punya' ) !== false ) {
				return 'Have a coupon? Click here to enter your code';
			}
		}
		
		if ( strpos( $translated_text, 'Maaf' ) !== false && ( strpos( $translated_text, 'metode pembayaran' ) !== false || strpos( $translated_text, 'tidak ada metode pembayaran' ) !== false ) ) {
			return 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.';
		}
		
		// Payment method error variations
		if ( strpos( $translated_text, 'tidak ada metode pembayaran' ) !== false || strpos( $translated_text, 'Tidak ada metode pembayaran' ) !== false ) {
			return 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.';
		}
		
		if ( strpos( $translated_text, 'Berlangganan' ) !== false || strpos( $translated_text, 'BERLANGGANAN' ) !== false ) {
			return 'SUBSCRIBE TO OUR NEWSLETTER';
		}
		
		if ( strpos( $translated_text, 'Buat pesanan' ) !== false || strpos( $translated_text, 'BUAT PESANAN' ) !== false ) {
			return 'PLACE ORDER';
		}
		
		// Add to cart messages
		if ( strpos( $translated_text, 'telah ditambahkan' ) !== false || strpos( $translated_text, 'Telah ditambahkan' ) !== false || strpos( $translated_text, 'Ditambahkan' ) !== false ) {
			if ( strpos( $translated_text, 'keranjang' ) !== false ) {
				return str_replace( 
					array( 'telah ditambahkan ke keranjang', 'Telah ditambahkan ke keranjang', 'Ditambahkan ke keranjang', 'keranjang' ),
					array( 'has been added to your cart', 'Has been added to your cart', 'Added to your cart', 'cart' ),
					$translated_text
				);
			}
		}
		
		// View cart / Continue shopping
		if ( strpos( $translated_text, 'Lihat keranjang' ) !== false || strpos( $translated_text, 'Lihat Keranjang' ) !== false ) {
			return 'View cart';
		}
		if ( strpos( $translated_text, 'Lanjutkan belanja' ) !== false || strpos( $translated_text, 'Lanjutkan Belanja' ) !== false ) {
			return 'Continue shopping';
		}
	}
	return $translated_text;
}

/**
 * Override WooCommerce catalog orderby options to English
 */
add_filter( 'woocommerce_catalog_orderby', 'wp_augoose_catalog_orderby_english', 20 );
function wp_augoose_catalog_orderby_english( $options ) {
	return array(
		'menu_order' => 'Default sorting',
		'popularity' => 'Sort by popularity',
		'rating'     => 'Sort by average rating',
		'date'       => 'Sort by latest',
		'price'      => 'Sort by price: low to high',
		'price-desc' => 'Sort by price: high to low',
	);
}

/**
 * Force WooCommerce cart item removed message to English
 */
add_filter( 'woocommerce_cart_item_removed_message', 'wp_augoose_cart_item_removed_message_english', 20, 2 );
function wp_augoose_cart_item_removed_message_english( $message, $product ) {
	// Force English message regardless of locale
	if ( $product && is_a( $product, 'WC_Product' ) ) {
		$product_name = $product->get_name();
		return sprintf( '%s has been removed from your cart because it can no longer be purchased. Please contact us if you need assistance.', $product_name );
	}
	return $message;
}

/**
 * Force WooCommerce cart item removed because modified message to English
 */
add_filter( 'woocommerce_cart_item_removed_because_modified_message', 'wp_augoose_cart_item_modified_message_english', 20, 2 );
function wp_augoose_cart_item_modified_message_english( $message, $product ) {
	// Force English message regardless of locale
	if ( $product && is_a( $product, 'WC_Product' ) ) {
		$product_name = $product->get_name();
		$product_url = $product->get_permalink();
		return sprintf( '%s has been removed from your cart because it has since been modified. You can add it back to your cart <a href="%s">here</a>.', $product_name, $product_url );
	}
	return $message;
}

/**
 * Force WooCommerce cart removed notification to English
 * Handles: "%s removed." and "Undo?" translations
 */
add_filter( 'gettext', 'wp_augoose_translate_cart_removed_notification', 20, 3 );
function wp_augoose_translate_cart_removed_notification( $translated_text, $text, $domain ) {
	// Only translate WooCommerce text
	if ( 'woocommerce' !== $domain ) {
		return $translated_text;
	}
	
	// Translate cart removal messages
	$translations = array(
		'%s removed.' => '%s removed.',
		'removed.' => 'removed.',
		'Removed.' => 'Removed.',
		'Undo?' => 'Undo?',
		'undo?' => 'Undo?',
	);
	
	// If text is already in Indonesian, translate it
	$indonesian_patterns = array(
		'/dihapus\.?\s*Batalkan\?/i' => 'removed. Undo?',
		'/dihapus\.?/i' => 'removed.',
		'/Dihapus\.?/i' => 'Removed.',
		'/Batalkan\?/i' => 'Undo?',
		'/batalkan\?/i' => 'Undo?',
	);
	
	foreach ( $indonesian_patterns as $pattern => $replacement ) {
		if ( preg_match( $pattern, $translated_text ) ) {
			$translated_text = preg_replace( $pattern, $replacement, $translated_text );
		}
	}
	
	return $translated_text;
}

/**
 * Force WooCommerce add to cart messages to English
 */
add_filter( 'wc_add_to_cart_message_html', 'wp_augoose_add_to_cart_message_english', 20, 3 );
function wp_augoose_add_to_cart_message_english( $message, $products, $show_qty ) {
	// Replace Indonesian text with English
	$message = str_replace( 
		array(
			'telah ditambahkan ke keranjang',
			'Telah ditambahkan ke keranjang',
			'Ditambahkan ke keranjang',
			'ditambahkan ke keranjang',
			'belanja Anda',
			'Belanja Anda',
			'belanja anda',
			'Belanja anda',
			'keranjang belanja',
			'Keranjang belanja',
			'keranjang Anda',
			'Keranjang Anda',
			'keranjang anda',
			'Keranjang anda',
			'Lihat keranjang',
			'Lanjutkan belanja',
			'Lihat Keranjang',
			'Lanjutkan Belanja',
		),
		array(
			'has been added to your cart',
			'Has been added to your cart',
			'Added to your cart',
			'added to your cart',
			'cart',
			'Cart',
			'cart',
			'Cart',
			'cart',
			'Cart',
			'cart',
			'Cart',
			'cart',
			'Cart',
			'View cart',
			'Continue shopping',
			'View Cart',
			'Continue Shopping',
		),
		$message
	);
	
	// Remove duplicate "cart" if message contains "cart belanja Anda" or "your cart belanja Anda"
	$message = preg_replace( '/\bcart\s+belanja\s+Anda\b/i', 'cart', $message );
	$message = preg_replace( '/\bcart\s+belanja\s+anda\b/i', 'cart', $message );
	$message = preg_replace( '/\byour\s+cart\s+belanja\s+Anda\b/i', 'your cart', $message );
	$message = preg_replace( '/\byour\s+cart\s+belanja\s+anda\b/i', 'your cart', $message );
	
	return $message;
}

/**
 * Force WooCommerce product add to cart success message to English
 */
add_filter( 'woocommerce_product_add_to_cart_success_message', 'wp_augoose_product_add_to_cart_message_english', 20, 2 );
function wp_augoose_product_add_to_cart_message_english( $text, $product ) {
	if ( empty( $text ) ) {
		return $text;
	}
	// Replace Indonesian text with English
	$text = str_replace( 
		array(
			'telah ditambahkan ke keranjang',
			'Telah ditambahkan ke keranjang',
			'Ditambahkan ke keranjang',
			'ditambahkan ke keranjang',
			'belanja Anda',
			'Belanja Anda',
			'belanja anda',
			'Belanja anda',
			'keranjang belanja',
			'Keranjang belanja',
			'keranjang Anda',
			'Keranjang Anda',
			'keranjang anda',
			'Keranjang anda',
		),
		array(
			'has been added to your cart',
			'Has been added to your cart',
			'Added to your cart',
			'added to your cart',
			'cart',
			'Cart',
			'cart',
			'Cart',
			'cart',
			'Cart',
			'cart',
			'Cart',
			'cart',
			'Cart',
		),
		$text
	);
	
	// Remove duplicate "cart" if message contains "cart belanja Anda" or "your cart belanja Anda"
	$text = preg_replace( '/\bcart\s+belanja\s+Anda\b/i', 'cart', $text );
	$text = preg_replace( '/\bcart\s+belanja\s+anda\b/i', 'cart', $text );
	$text = preg_replace( '/\byour\s+cart\s+belanja\s+Anda\b/i', 'your cart', $text );
	$text = preg_replace( '/\byour\s+cart\s+belanja\s+anda\b/i', 'your cart', $text );
	
	return $text;
}

/**
 * Force all WooCommerce notices to English
 */
/**
 * Force ALL WooCommerce notices (error, success, info) to English
 * This ensures no Indonesian text appears in notifications
 */
add_filter( 'woocommerce_add_error', 'wp_augoose_force_notice_english', 1, 1 );
add_filter( 'woocommerce_add_success', 'wp_augoose_force_notice_english', 1, 1 );
add_filter( 'woocommerce_add_info', 'wp_augoose_force_notice_english', 1, 1 );
add_filter( 'woocommerce_add_notice', 'wp_augoose_force_notice_english', 1, 1 );
add_filter( 'woocommerce_add_message', 'wp_augoose_force_notice_english', 1, 1 );
function wp_augoose_force_notice_english( $message ) {
	if ( empty( $message ) ) {
		return $message;
	}
	
	// Remove HTML tags for processing
	$clean_message = wp_strip_all_tags( $message );
	$clean_message = trim( $clean_message );
	
	// Pattern 1: "Penagihan [Field] harus diisi." or "Pengiriman [Field] harus diisi."
	// This pattern captures fields with special characters like "State / County", "Postcode / ZIP", etc.
	if ( preg_match( '/^(Penagihan|Pengiriman|penagihan|pengiriman)\s+(.+?)\s+harus\s+diisi\.?$/i', $clean_message, $matches ) ) {
		$type = trim( $matches[1] );
		$field = trim( $matches[2] );
		
		// Convert type to English
		if ( stripos( $type, 'Penagihan' ) !== false || stripos( $type, 'penagihan' ) !== false ) {
			$type = 'Billing';
		} elseif ( stripos( $type, 'Pengiriman' ) !== false || stripos( $type, 'pengiriman' ) !== false ) {
			$type = 'Shipping';
		}
		
		// Reconstruct message with HTML if original had it
		if ( strpos( $message, '<strong>' ) !== false ) {
			return sprintf( '<strong>%s %s</strong> is required.', $type, $field );
		} else {
			return sprintf( '%s %s is required.', $type, $field );
		}
	}
	
	// Pattern 2: More flexible - match any message containing "Penagihan/Pengiriman" + field + "harus diisi"
	// This pattern captures fields with special characters like "State / County"
	if ( preg_match( '/(Penagihan|Pengiriman|penagihan|pengiriman)\s+(.+?)\s+harus\s+diisi/i', $clean_message, $matches ) ) {
		$type = trim( $matches[1] );
		$field = trim( $matches[2] );
		
		// Convert type to English
		if ( stripos( $type, 'Penagihan' ) !== false || stripos( $type, 'penagihan' ) !== false ) {
			$type = 'Billing';
		} elseif ( stripos( $type, 'Pengiriman' ) !== false || stripos( $type, 'pengiriman' ) !== false ) {
			$type = 'Shipping';
		}
		
		// Reconstruct message with HTML if original had it
		if ( strpos( $message, '<strong>' ) !== false ) {
			return sprintf( '<strong>%s %s</strong> is required.', $type, $field );
		} else {
			return sprintf( '%s %s is required.', $type, $field );
		}
	}
	
	// Pattern 3: Direct string replacement for "harus diisi" (ALWAYS run FIRST)
	// This ensures we catch all variations even if regex doesn't match
	// CRITICAL: This must run FIRST to catch all cases including fields with special characters
	// Check for Indonesian text in the message (both with and without HTML)
	if ( stripos( $message, 'Penagihan' ) !== false || 
	     stripos( $message, 'Pengiriman' ) !== false || 
	     stripos( $message, 'harus diisi' ) !== false ||
	     stripos( $clean_message, 'Penagihan' ) !== false || 
	     stripos( $clean_message, 'Pengiriman' ) !== false || 
	     stripos( $clean_message, 'harus diisi' ) !== false ) {
		
		// Replace "Penagihan" with "Billing" and "Pengiriman" with "Shipping"
		$message = str_ireplace( 'Penagihan', 'Billing', $message );
		$message = str_ireplace( 'Pengiriman', 'Shipping', $message );
		
		// Replace "harus diisi" with "is required" (all variations)
		$message = str_ireplace( 'harus diisi', 'is required', $message );
		$message = str_ireplace( 'Harus diisi', 'Is required', $message );
		$message = str_ireplace( 'HARUS DIISI', 'IS REQUIRED', $message );
		$message = str_ireplace( 'harus diisi.', 'is required.', $message );
		$message = str_ireplace( 'Harus diisi.', 'Is required.', $message );
		$message = str_ireplace( 'HARUS DIISI.', 'IS REQUIRED.', $message );
		$message = str_ireplace( 'harus diisi', 'is required', $message );
		
		// Return immediately after replacement
		return $message;
	}
	
	// Pattern 4: "[Field] harus diisi." (without prefix)
	if ( preg_match( '/^(.+?)\s+harus\s+diisi\.?$/i', $clean_message, $matches ) && 
	     stripos( $clean_message, 'Penagihan' ) === false && 
	     stripos( $clean_message, 'Pengiriman' ) === false &&
	     stripos( $clean_message, 'Billing' ) === false &&
	     stripos( $clean_message, 'Shipping' ) === false ) {
		$field = trim( $matches[1] );
		
		// Reconstruct message with HTML if original had it
		if ( strpos( $message, '<strong>' ) !== false ) {
			return sprintf( '<strong>%s</strong> is required.', $field );
		} else {
			return sprintf( '%s is required.', $field );
		}
	}
	
	// Common Indonesian to English translations for notices
	$replacements = array(
		// Cart removal - format: "%s dihapus. Batalkan?"
		'" dihapus. Batalkan?' => '" removed. Undo?',
		'" dihapus. Batalkan' => '" removed. Undo',
		' dihapus. Batalkan?' => ' removed. Undo?',
		' dihapus. Batalkan' => ' removed. Undo',
		'dihapus. Batalkan?' => 'removed. Undo?',
		'Dihapus. Batalkan?' => 'Removed. Undo?',
		'dihapus' => 'removed',
		'Dihapus' => 'Removed',
		'Batalkan?' => 'Undo?',
		'batalkan?' => 'Undo?',
		'Batalkan' => 'Undo',
		'batalkan' => 'Undo',
		// Cart removal - longer format
		'telah dihapus dari keranjang' => 'has been removed from your cart',
		'Telah dihapus dari keranjang' => 'Has been removed from your cart',
		'tidak bisa dibeli lagi' => 'can no longer be purchased',
		'Hubungi kami jika Anda butuh bantuan' => 'Please contact us if you need assistance',
		'Hubungi kami jika Anda memerlukan bantuan' => 'Please contact us if you need assistance',
		// Add to cart
		'telah ditambahkan ke keranjang' => 'has been added to your cart',
		'Telah ditambahkan ke keranjang' => 'Has been added to your cart',
		'ditambahkan ke keranjang' => 'added to your cart',
		'Ditambahkan ke keranjang' => 'Added to your cart',
		'belanja Anda' => 'cart',
		'Belanja Anda' => 'Cart',
		'belanja anda' => 'cart',
		'Belanja anda' => 'Cart',
		'keranjang belanja' => 'cart',
		'Keranjang belanja' => 'Cart',
		'keranjang Anda' => 'cart',
		'Keranjang Anda' => 'Cart',
		'keranjang anda' => 'cart',
		'Keranjang anda' => 'Cart',
		'Berhasil ditambahkan' => 'Successfully added',
		'berhasil ditambahkan' => 'successfully added',
		// Error messages
		'Gagal menambahkan produk' => 'Failed to add product',
		'gagal menambahkan produk' => 'failed to add product',
		'Produk tidak ditemukan' => 'Product not found',
		'produk tidak ditemukan' => 'product not found',
		'ID produk tidak valid' => 'Invalid product ID',
		'id produk tidak valid' => 'invalid product ID',
		'Produk tidak dapat dibeli' => 'Product is not purchasable',
		'produk tidak dapat dibeli' => 'product is not purchasable',
		'Produk habis' => 'Product is out of stock',
		'produk habis' => 'product is out of stock',
		'Produk kehabisan stok' => 'Product is out of stock',
		'produk kehabisan stok' => 'product is out of stock',
		'Silakan pilih opsi produk' => 'Please choose product options',
		'silakan pilih opsi produk' => 'please choose product options',
		'Item keranjang tidak valid' => 'Invalid cart item',
		'item keranjang tidak valid' => 'invalid cart item',
		// Payment errors
		'Maaf, tampaknya tidak ada metode pembayaran' => 'Sorry, it seems that there are no available payment methods',
		'maaf, tampaknya tidak ada metode pembayaran' => 'sorry, it seems that there are no available payment methods',
		'Tidak ada metode pembayaran' => 'No payment methods available',
		'tidak ada metode pembayaran' => 'no payment methods available',
		// Order processing errors
		'Terjadi error saat memproses pesanan Anda.' => 'An error occurred while processing your order.',
		'terjadi error saat memproses pesanan Anda' => 'an error occurred while processing your order',
		'Periksa apakah ada perubahan dalam metode pembayaran Anda dan tinjau riwayat pemesanan sebelum membuat pesanan lagi.' => 'Please check if there are any changes in your payment method and review your order history before placing another order.',
		'periksa apakah ada perubahan dalam metode pembayaran Anda' => 'please check if there are any changes in your payment method',
		'tinjau riwayat pemesanan' => 'review your order history',
		'sebelum membuat pesanan lagi' => 'before placing another order',
		// General
		'keranjang' => 'cart',
		'Keranjang' => 'Cart',
		'produk' => 'product',
		'Produk' => 'Product',
		'Maaf' => 'Sorry',
		'maaf' => 'sorry',
		'Silakan' => 'Please',
		'silakan' => 'please',
		'Mohon' => 'Please',
		'mohon' => 'please',
		'Terjadi kesalahan' => 'An error occurred',
		'terjadi kesalahan' => 'an error occurred',
		'Gagal' => 'Failed',
		'gagal' => 'failed',
		'Berhasil' => 'Success',
		'berhasil' => 'success',
		// Additional checkout validation errors
		'harus diisi' => 'is required',
		'Harus diisi' => 'Is required',
		'HARUS DIISI' => 'IS REQUIRED',
		'harus diisi.' => 'is required.',
		'Harus diisi.' => 'Is required.',
		'HARUS DIISI.' => 'IS REQUIRED.',
		'Penagihan' => 'Billing',
		'penagihan' => 'Billing',
		'PENAGIHAN' => 'BILLING',
		'Pengiriman' => 'Shipping',
		'pengiriman' => 'Shipping',
		'PENGIRIMAN' => 'SHIPPING',
	);
	
	$message = str_replace( array_keys( $replacements ), array_values( $replacements ), $message );
	
	// Final check: if message still contains Indonesian text, do aggressive replacement
	if ( stripos( $message, 'Penagihan' ) !== false || 
	     stripos( $message, 'Pengiriman' ) !== false || 
	     stripos( $message, 'harus diisi' ) !== false ||
	     stripos( $message, 'dihapus' ) !== false ||
	     stripos( $message, 'keranjang' ) !== false ||
	     stripos( $message, 'produk' ) !== false ||
	     stripos( $message, 'Maaf' ) !== false ||
	     stripos( $message, 'Silakan' ) !== false ||
	     stripos( $message, 'Mohon' ) !== false ) {
		
		// Aggressive replacement for any remaining Indonesian text
		$message = str_ireplace( 'Penagihan', 'Billing', $message );
		$message = str_ireplace( 'Pengiriman', 'Shipping', $message );
		$message = str_ireplace( 'harus diisi', 'is required', $message );
		$message = str_ireplace( 'dihapus', 'removed', $message );
		$message = str_ireplace( 'keranjang', 'cart', $message );
		$message = str_ireplace( 'produk', 'product', $message );
		$message = str_ireplace( 'Maaf', 'Sorry', $message );
		$message = str_ireplace( 'Silakan', 'Please', $message );
		$message = str_ireplace( 'Mohon', 'Please', $message );
	}
	
	return $message;
}

/**
 * Force checkout form fields to English
 */
add_filter( 'woocommerce_checkout_fields', 'wp_augoose_checkout_fields_english', 20 );
function wp_augoose_checkout_fields_english( $fields ) {
	// English field labels mapping
	$english_labels = array(
		'first_name' => 'First name',
		'last_name' => 'Last name',
		'company' => 'Company name',
		'address_1' => 'Address',
		'address_2' => 'Apartment, suite, etc. (optional)',
		'city' => 'City',
		'state' => 'State / County',
		'postcode' => 'Postcode / ZIP',
		'country' => 'Country / Region',
		'phone' => 'Phone',
		'email' => 'Email address',
		'order_comments' => 'Order notes',
	);
	
	// English placeholders mapping
	$english_placeholders = array(
		'first_name' => 'First name',
		'last_name' => 'Last name',
		'company' => 'Company name',
		'address_1' => 'House number and street name',
		'address_2' => 'Apartment, suite, unit, etc. (optional)',
		'city' => 'City',
		'state' => 'State / County',
		'postcode' => 'Postcode / ZIP',
		'phone' => 'Phone',
		'email' => 'Email address',
		'order_comments' => 'Notes about your order, e.g. special notes for delivery.',
	);
	
	// Process billing fields
	if ( isset( $fields['billing'] ) ) {
		// Ensure last_name field exists and make it required
		if ( ! isset( $fields['billing']['billing_last_name'] ) ) {
			// Create last_name field if it doesn't exist
			$first_name_priority = isset( $fields['billing']['billing_first_name']['priority'] ) ? $fields['billing']['billing_first_name']['priority'] : 10;
			$fields['billing']['billing_last_name'] = array(
				'label'       => 'Last name',
				'placeholder' => 'Last name',
				'required'    => true,
				'class'       => array( 'form-row-wide' ),
				'priority'    => $first_name_priority + 1,
				'type'        => 'text',
			);
		} else {
			// Make existing last_name required
			$fields['billing']['billing_last_name']['required'] = true;
			$fields['billing']['billing_last_name']['label'] = 'Last name';
			$fields['billing']['billing_last_name']['placeholder'] = 'Last name';
		}
		
		// Add phone country code field - will be rendered inline with phone via filter
		if ( isset( $fields['billing']['billing_phone'] ) ) {
			$phone_priority = isset( $fields['billing']['billing_phone']['priority'] ) ? $fields['billing']['billing_phone']['priority'] : 100;
			
			// Add country code dropdown - will be rendered inline with phone
			$fields['billing']['billing_phone_country_code'] = array(
				'label'       => 'Country Code',
				'placeholder' => 'Select code',
				'required'    => true,
				'class'       => array( 'form-row-wide', 'phone-country-code-wrapper' ),
				'priority'    => $phone_priority - 1,
				'type'        => 'select',
				'options'     => wp_augoose_get_country_codes(),
			);
			
			// Adjust phone field to be inline with country code
			$fields['billing']['billing_phone']['label'] = 'Phone Number';
			$fields['billing']['billing_phone']['class'] = array( 'form-row-wide', 'phone-with-country-code' );
			$fields['billing']['billing_phone']['priority'] = $phone_priority;
		}
		
		foreach ( $fields['billing'] as $key => $field ) {
			$field_key = str_replace( 'billing_', '', $key );
			
			// Skip phone_country_code from English mapping (already set above)
			if ( $field_key === 'phone_country_code' ) {
				continue;
			}
			
			if ( isset( $english_labels[ $field_key ] ) ) {
				$fields['billing'][ $key ]['label'] = $english_labels[ $field_key ];
			}
			if ( isset( $english_placeholders[ $field_key ] ) ) {
				$fields['billing'][ $key ]['placeholder'] = $english_placeholders[ $field_key ];
			}
			// Force override for address fields
			if ( $field_key === 'address_1' ) {
				$fields['billing'][ $key ]['label'] = 'Address';
				$fields['billing'][ $key ]['placeholder'] = 'House number and street name';
			}
			if ( $field_key === 'address_2' ) {
				$fields['billing'][ $key ]['placeholder'] = 'Apartment, suite, unit, etc. (optional)';
			}
		}
	}
	
	// Process shipping fields
	if ( isset( $fields['shipping'] ) ) {
		foreach ( $fields['shipping'] as $key => $field ) {
			$field_key = str_replace( 'shipping_', '', $key );
			if ( isset( $english_labels[ $field_key ] ) ) {
				$fields['shipping'][ $key ]['label'] = $english_labels[ $field_key ];
			}
			if ( isset( $english_placeholders[ $field_key ] ) ) {
				$fields['shipping'][ $key ]['placeholder'] = $english_placeholders[ $field_key ];
			}
			// Force override for address fields
			if ( $field_key === 'address_1' ) {
				$fields['shipping'][ $key ]['label'] = 'Address';
				$fields['shipping'][ $key ]['placeholder'] = 'House number and street name';
			}
			if ( $field_key === 'address_2' ) {
				$fields['shipping'][ $key ]['placeholder'] = 'Apartment, suite, unit, etc. (optional)';
			}
		}
	}
	
	// Remove order notes field (order_comments) - per user request
	if ( isset( $fields['order'] ) && isset( $fields['order']['order_comments'] ) ) {
		unset( $fields['order']['order_comments'] );
	}
	
	return $fields;
}

/**
 * Disable order notes field in checkout
 */
add_filter( 'woocommerce_enable_order_notes_field', '__return_false', 20 );

/**
 * Limit allowed countries to specific list only
 */
add_filter( 'woocommerce_countries_allowed_countries', 'wp_augoose_limit_allowed_countries', 10, 1 );
function wp_augoose_limit_allowed_countries( $countries ) {
	// Get allowed country codes from helper function
	$allowed_country_codes = wp_augoose_get_allowed_country_codes();
	
	// Filter countries to only include allowed ones
	$filtered_countries = array();
	foreach ( $allowed_country_codes as $code ) {
		if ( isset( $countries[ $code ] ) ) {
			$filtered_countries[ $code ] = $countries[ $code ];
		}
	}
	
	return $filtered_countries;
}

/**
 * Limit shipping countries to the same allowed list
 */
add_filter( 'woocommerce_countries_shipping_countries', 'wp_augoose_limit_shipping_countries', 10, 1 );
function wp_augoose_limit_shipping_countries( $countries ) {
	// Use the same allowed countries list
	return wp_augoose_limit_allowed_countries( $countries );
}

/**
 * Get list of allowed country codes (helper function)
 */
function wp_augoose_get_allowed_country_codes() {
	return array(
		'AF', // Afghanistan
		'AL', // Albania
		'DZ', // Algeria
		'AS', // American Samoa
		'AR', // Argentina
		'AT', // Austria
		'AU', // Australia
		'AZ', // Azerbaijan
		'BH', // Bahrain
		'BE', // Belgium
		'BR', // Brazil
		'BN', // Brunei
		'BG', // Bulgaria
		'KH', // Cambodia
		'CA', // Canada
		'CL', // Chile
		'CN', // China
		'CO', // Colombia
		'CR', // Costa Rica
		'HR', // Croatia
		'CZ', // Czech
		'DK', // Denmark
		'EG', // Egypt
		'FI', // Finland
		'FR', // France
		'DE', // Germany
		'GR', // Greece
		'HK', // Hong Kong
		'HU', // Hungary
		'IN', // India
		'IR', // Iran
		'IE', // Ireland
		'IT', // Italy
		'JP', // Japan
		'KZ', // Kazakhstan
		'KR', // Korea
		'KW', // Kuwait
		'LA', // Laos
		'LU', // Luxembourg
		'MO', // Macau
		'MV', // Maldives
		'MX', // Mexico
		'MC', // Monaco
		'MN', // Mongolia
		'MA', // Morocco
		'MM', // Myanmar
		'NP', // Nepal
		'NL', // Netherlands
		'NZ', // New Zealand
		'PY', // Paraguay
		'PE', // Peru
		'PH', // Philippines
		'PL', // Poland
		'PT', // Portugal
		'PR', // Puerto Rico
		'SA', // Saudi Arabia
		'ES', // Spain
		'SE', // Sweden
		'CH', // Switzerland
		'TW', // Taiwan
		'TH', // Thailand
		'TN', // Tunisia
		'TR', // Turkey
		'UA', // Ukraine
		'AE', // United Arab Emirates
		'GB', // UK
		'UY', // Uruguay
		'US', // USA
		'UZ', // Uzbekistan
		'VE', // Venezuela
		'VN', // Vietnam
		'ID', // Indonesia
		'SG', // Singapore
		'MY', // Malaysia
	);
}

/**
 * Force country names to English
 * CRITICAL: Run with high priority (1) to ensure it runs before other filters
 */
add_filter( 'woocommerce_countries', 'wp_augoose_countries_english', 1, 1 );
add_filter( 'woocommerce_countries_allowed_countries', 'wp_augoose_countries_english', 1, 1 );
add_filter( 'woocommerce_countries_shipping_countries', 'wp_augoose_countries_english', 1, 1 );
add_filter( 'woocommerce_form_field_country', 'wp_augoose_countries_english', 1, 1 );
function wp_augoose_countries_english( $countries ) {
	if ( ! is_array( $countries ) || empty( $countries ) ) {
		return $countries;
	}
	
	// Load default English country names from WooCommerce directly
	if ( class_exists( 'WooCommerce' ) && function_exists( 'WC' ) && WC() ) {
		try {
			$english_countries_file = WC()->plugin_path() . '/i18n/countries.php';
			if ( file_exists( $english_countries_file ) ) {
				// Temporarily switch locale to en_US to get English names
				$original_locale = get_locale();
				if ( function_exists( 'switch_to_locale' ) ) {
					switch_to_locale( 'en_US' );
				}
				
				// Load English countries directly (this file contains English names by default)
				$english_countries = include $english_countries_file;
				
				// Restore original locale
				if ( function_exists( 'restore_previous_locale' ) ) {
					restore_previous_locale();
				}
				
				// Ensure we got an array
				if ( is_array( $english_countries ) ) {
					// Replace all country names with English versions
					foreach ( $countries as $code => $name ) {
						if ( isset( $english_countries[ $code ] ) ) {
							$countries[ $code ] = $english_countries[ $code ];
						}
					}
					
					return $countries;
				}
			}
		} catch ( Exception $e ) {
			// If there's an error loading the file, fall through to translation array
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Error loading countries file: ' . $e->getMessage() );
			}
		}
	}
	
	// Fallback: Comprehensive country name translations from Indonesian to English
	$country_translations = array(
		// A
		'Afganistan' => 'Afghanistan',
		'Afghanistan' => 'Afghanistan',
		'Albania' => 'Albania',
		'Aljazair' => 'Algeria',
		'Algeria' => 'Algeria',
		'Samoa Amerika' => 'American Samoa',
		'American Samoa' => 'American Samoa',
		'Amerika Serikat' => 'United States',
		'Argentina' => 'Argentina',
		'Austria' => 'Austria',
		'Australia' => 'Australia',
		'Azerbaijan' => 'Azerbaijan',
		// B
		'Bahrain' => 'Bahrain',
		'Belgia' => 'Belgium',
		'Belgium' => 'Belgium',
		'Belanda' => 'Netherlands',
		'Netherlands' => 'Netherlands',
		'Brasil' => 'Brazil',
		'Brazil' => 'Brazil',
		'Brunei' => 'Brunei',
		'Brunei Darussalam' => 'Brunei',
		'Bulgaria' => 'Bulgaria',
		// C
		'Kamboja' => 'Cambodia',
		'Cambodia' => 'Cambodia',
		'Kanada' => 'Canada',
		'Canada' => 'Canada',
		'Cili' => 'Chile',
		'Chile' => 'Chile',
		'Tiongkok' => 'China',
		'Cina' => 'China',
		'China' => 'China',
		'Kolombia' => 'Colombia',
		'Colombia' => 'Colombia',
		'Kosta Rika' => 'Costa Rica',
		'Costa Rica' => 'Costa Rica',
		'Kroasia' => 'Croatia',
		'Croatia' => 'Croatia',
		'Republik Ceko' => 'Czech Republic',
		'Ceko' => 'Czech Republic',
		'Czech Republic' => 'Czech Republic',
		// D
		'Denmark' => 'Denmark',
		// E
		'Mesir' => 'Egypt',
		'Egypt' => 'Egypt',
		// F
		'Finlandia' => 'Finland',
		'Finland' => 'Finland',
		'Perancis' => 'France',
		'Prancis' => 'France',
		'France' => 'France',
		// G
		'Jerman' => 'Germany',
		'Germany' => 'Germany',
		'Yunani' => 'Greece',
		'Greece' => 'Greece',
		// H
		'Hong Kong' => 'Hong Kong',
		'Hungaria' => 'Hungary',
		'Hungary' => 'Hungary',
		// I
		'India' => 'India',
		'Indonesia' => 'Indonesia',
		'Iran' => 'Iran',
		'Irlandia' => 'Ireland',
		'Ireland' => 'Ireland',
		'Italia' => 'Italy',
		'Italy' => 'Italy',
		// J
		'Jepang' => 'Japan',
		'Japan' => 'Japan',
		// K
		'Kazakhstan' => 'Kazakhstan',
		'Korea Selatan' => 'South Korea',
		'Korea' => 'South Korea',
		'South Korea' => 'South Korea',
		'Kuwait' => 'Kuwait',
		// L
		'Laos' => 'Laos',
		'Luksemburg' => 'Luxembourg',
		'Luxembourg' => 'Luxembourg',
		// M
		'Makao' => 'Macau',
		'Macau' => 'Macau',
		'Maladewa' => 'Maldives',
		'Maldives' => 'Maldives',
		'Malaysia' => 'Malaysia',
		'Meksiko' => 'Mexico',
		'Mexico' => 'Mexico',
		'Monako' => 'Monaco',
		'Monaco' => 'Monaco',
		'Mongolia' => 'Mongolia',
		'Maroko' => 'Morocco',
		'Morocco' => 'Morocco',
		'Myanmar' => 'Myanmar',
		// N
		'Nepal' => 'Nepal',
		'Belanda' => 'Netherlands',
		'Netherlands' => 'Netherlands',
		'Selandia Baru' => 'New Zealand',
		'New Zealand' => 'New Zealand',
		// P
		'Paraguay' => 'Paraguay',
		'Peru' => 'Peru',
		'Peru' => 'Peru',
		'Filipina' => 'Philippines',
		'Philippines' => 'Philippines',
		'Polandia' => 'Poland',
		'Poland' => 'Poland',
		'Portugal' => 'Portugal',
		'Puerto Riko' => 'Puerto Rico',
		'Puerto Rico' => 'Puerto Rico',
		// S
		'Arab Saudi' => 'Saudi Arabia',
		'Saudi Arbia' => 'Saudi Arabia',
		'Saudi Arabia' => 'Saudi Arabia',
		'Singapura' => 'Singapore',
		'Singapore' => 'Singapore',
		'Spanyol' => 'Spain',
		'Spain' => 'Spain',
		'Swedia' => 'Sweden',
		'Sweden' => 'Sweden',
		'Swiss' => 'Switzerland',
		'Switzerland' => 'Switzerland',
		// T
		'Taiwan' => 'Taiwan',
		'Thailand' => 'Thailand',
		'Tunisia' => 'Tunisia',
		'Tunisia' => 'Tunisia',
		'Turki' => 'Turkey',
		'Turkey' => 'Turkey',
		// U
		'Ukraina' => 'Ukraine',
		'Ukraine' => 'Ukraine',
		'Uni Emirat Arab' => 'United Arab Emirates',
		'United Arab Emirates' => 'United Arab Emirates',
		'Inggris' => 'United Kingdom',
		'Inggris Raya' => 'United Kingdom',
		'United Kingdom' => 'United Kingdom',
		'Amerika Serikat' => 'United States',
		'United States' => 'United States',
		'Uruguay' => 'Uruguay',
		'Uzbekistan' => 'Uzbekistan',
		// V
		'Venezuela' => 'Venezuela',
		'Vietnam' => 'Vietnam',
	);
	
	// Apply translations
	foreach ( $countries as $code => $name ) {
		// Direct translation
		if ( isset( $country_translations[ $name ] ) ) {
			$countries[ $code ] = $country_translations[ $name ];
		} else {
			// Case-insensitive check for partial matches
			foreach ( $country_translations as $id_name => $en_name ) {
				if ( stripos( $name, $id_name ) !== false || stripos( $id_name, $name ) !== false ) {
					$countries[ $code ] = $en_name;
					break;
				}
			}
		}
	}
	
	return $countries;
}

/**
 * Force country locale fields to English (address field labels and placeholders)
 */
add_filter( 'woocommerce_get_country_locale_base', 'wp_augoose_country_locale_english', 20 );
add_filter( 'woocommerce_get_country_locale', 'wp_augoose_country_locale_english', 20 );
function wp_augoose_country_locale_english( $locale ) {
	if ( is_array( $locale ) ) {
		foreach ( $locale as $country => $fields ) {
			if ( is_array( $fields ) ) {
				foreach ( $fields as $field_key => $field_data ) {
					if ( isset( $field_data['label'] ) ) {
						// Force English labels
						$indonesian_labels = array(
							'ALAMAT JALAN' => 'Address',
							'Alamat jalan' => 'Address',
							'Street address' => 'Address',
							'Negara' => 'Country / Region',
							'negara' => 'Country / Region',
							'Negara / Wilayah' => 'Country / Region',
							'negara / wilayah' => 'Country / Region',
							'Country/Region' => 'Country / Region',
							'Country / Wilayah' => 'Country / Region',
							'Negara / Region' => 'Country / Region',
						);
						if ( isset( $indonesian_labels[ $field_data['label'] ] ) ) {
							$locale[ $country ][ $field_key ]['label'] = $indonesian_labels[ $field_data['label'] ];
						}
						// Always set to English if contains "negara" or "wilayah"
						if ( stripos( $field_data['label'], 'negara' ) !== false || stripos( $field_data['label'], 'wilayah' ) !== false ) {
							$locale[ $country ][ $field_key ]['label'] = 'Country / Region';
						}
					}
					if ( isset( $field_data['placeholder'] ) ) {
						// Force English placeholders
						$indonesian_placeholders = array(
							'Nomor rumah dan nama jalan' => 'House number and street name',
							'nomor rumah dan nama jalan' => 'House number and street name',
							'Apartemen, suit, unit, dll. (opsional)' => 'Apartment, suite, unit, etc. (optional)',
							'apartemen, suit, unit, dll. (opsional)' => 'Apartment, suite, unit, etc. (optional)',
							'Apartemen, suite, unit, dll. (opsional)' => 'Apartment, suite, unit, etc. (optional)',
							'Pilih negara' => 'Select a country / region...',
							'pilih negara' => 'Select a country / region...',
							'Pilih negara / wilayah' => 'Select a country / region...',
							'pilih negara / wilayah' => 'Select a country / region...',
							'Pilih Negara' => 'Select a country / region...',
							'Pilih Wilayah' => 'Select a country / region...',
						);
						if ( isset( $indonesian_placeholders[ $field_data['placeholder'] ] ) ) {
							$locale[ $country ][ $field_key ]['placeholder'] = $indonesian_placeholders[ $field_data['placeholder'] ];
						}
						// Always set to English if contains "negara", "wilayah", or "pilih"
						if ( stripos( $field_data['placeholder'], 'negara' ) !== false || stripos( $field_data['placeholder'], 'wilayah' ) !== false || stripos( $field_data['placeholder'], 'pilih' ) !== false ) {
							$locale[ $country ][ $field_key ]['placeholder'] = 'Select a country / region...';
						}
					}
				}
			}
		}
	}
	return $locale;
}

/**
 * Force form field labels and placeholders to English via form_field_args filter
 */
add_filter( 'woocommerce_form_field_args', 'wp_augoose_form_field_english', 20, 3 );
function wp_augoose_form_field_english( $args, $key, $value ) {
	// Force address field labels
	if ( strpos( $key, 'address_1' ) !== false ) {
		if ( isset( $args['label'] ) && ( $args['label'] === 'ALAMAT JALAN' || $args['label'] === 'Alamat jalan' || $args['label'] === 'Street address' ) ) {
			$args['label'] = 'Address';
		}
		if ( isset( $args['placeholder'] ) && ( strpos( $args['placeholder'], 'Nomor rumah' ) !== false || strpos( $args['placeholder'], 'nomor rumah' ) !== false ) ) {
			$args['placeholder'] = 'House number and street name';
		}
	}
	if ( strpos( $key, 'address_2' ) !== false ) {
		if ( isset( $args['placeholder'] ) && ( strpos( $args['placeholder'], 'Apartemen' ) !== false || strpos( $args['placeholder'], 'apartemen' ) !== false ) ) {
			$args['placeholder'] = 'Apartment, suite, unit, etc. (optional)';
		}
	}
	// Force country/region label and placeholder to English
	if ( strpos( $key, 'country' ) !== false && ! strpos( $key, 'phone_country_code' ) ) {
		// Force label to English
		if ( isset( $args['label'] ) ) {
			$indonesian_labels = array(
				'Negara' => 'Country / Region',
				'negara' => 'Country / Region',
				'Negara / Wilayah' => 'Country / Region',
				'negara / wilayah' => 'Country / Region',
				'Country/Region' => 'Country / Region',
				'Country / Wilayah' => 'Country / Region',
				'Negara / Region' => 'Country / Region',
			);
			foreach ( $indonesian_labels as $id => $en ) {
				if ( stripos( $args['label'], $id ) !== false ) {
					$args['label'] = $en;
					break;
				}
			}
			// Always set to English if contains "negara" or "wilayah"
			if ( stripos( $args['label'], 'negara' ) !== false || stripos( $args['label'], 'wilayah' ) !== false ) {
				$args['label'] = 'Country / Region';
			}
		}
		
		// Force placeholder to English
		if ( isset( $args['placeholder'] ) && ( strpos( $args['placeholder'], 'Pilih negara' ) !== false || strpos( $args['placeholder'], 'pilih negara' ) !== false || strpos( $args['placeholder'], 'wilayah' ) !== false || strpos( $args['placeholder'], 'Wilayah' ) !== false ) ) {
			$args['placeholder'] = 'Select a country / region...';
		}
		if ( ! isset( $args['placeholder'] ) || empty( $args['placeholder'] ) ) {
			$args['placeholder'] = 'Select a country / region...';
		}
		
		// Force data-placeholder attribute to English for Select2
		if ( isset( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			if ( isset( $args['custom_attributes']['data-placeholder'] ) ) {
				$data_placeholder = $args['custom_attributes']['data-placeholder'];
				if ( strpos( $data_placeholder, 'Pilih negara' ) !== false || strpos( $data_placeholder, 'pilih negara' ) !== false || strpos( $data_placeholder, 'wilayah' ) !== false ) {
					$args['custom_attributes']['data-placeholder'] = 'Select a country / region...';
				}
			} else {
				$args['custom_attributes']['data-placeholder'] = 'Select a country / region...';
			}
		} else {
			$args['custom_attributes'] = array( 'data-placeholder' => 'Select a country / region...' );
		}
	}
	return $args;
}

/**
 * Force newsletter subscription text to English
 */
add_filter( 'woocommerce_checkout_newsletter_subscription_text', 'wp_augoose_newsletter_text_english', 20 );
add_filter( 'woocommerce_registration_newsletter_subscription_text', 'wp_augoose_newsletter_text_english', 20 );
function wp_augoose_newsletter_text_english( $text ) {
	if ( strpos( $text, 'BERLANGGANAN' ) !== false || strpos( $text, 'Berlangganan' ) !== false || strpos( $text, 'berlangganan' ) !== false ) {
		return 'SUBSCRIBE TO OUR NEWSLETTER';
	}
	return $text;
}

/**
 * Force payment method error message to English
 */
add_filter( 'woocommerce_no_available_payment_methods_message', 'wp_augoose_payment_method_error_english', 20 );
function wp_augoose_payment_method_error_english( $message ) {
	if ( strpos( $message, 'Maaf' ) !== false || strpos( $message, 'metode pembayaran' ) !== false || strpos( $message, 'tidak ada metode pembayaran' ) !== false ) {
		return 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.';
	}
	return $message;
}

/**
 * Filter available payment gateways based on customer location
 * SG, MY, ID -> DOKU only
 * Others -> PayPal and Debit & Credit Cards only
 */
add_filter( 'woocommerce_available_payment_gateways', 'wp_augoose_filter_payment_gateways_by_location', 20, 1 );
function wp_augoose_filter_payment_gateways_by_location( $available_gateways ) {
	if ( empty( $available_gateways ) ) {
		return $available_gateways;
	}
	
	// Get customer billing country - try multiple methods
	$billing_country = '';
	
	// Method 1: From POST data (AJAX checkout update)
	if ( isset( $_POST['billing_country'] ) && ! empty( $_POST['billing_country'] ) ) {
		$billing_country = sanitize_text_field( $_POST['billing_country'] );
	}
	// Method 2: From WC customer object
	elseif ( WC()->customer && WC()->customer->get_billing_country() ) {
		$billing_country = WC()->customer->get_billing_country();
	}
	// Method 3: From checkout form value
	elseif ( WC()->checkout() ) {
		$checkout_country = WC()->checkout()->get_value( 'billing_country' );
		if ( ! empty( $checkout_country ) ) {
			$billing_country = $checkout_country;
		}
	}
	// Method 4: From session (if available)
	elseif ( WC()->session && WC()->session->get( 'customer' ) ) {
		$session_customer = WC()->session->get( 'customer' );
		if ( isset( $session_customer['country'] ) && ! empty( $session_customer['country'] ) ) {
			$billing_country = $session_customer['country'];
		}
	}
	
	// If no country selected yet, show all methods (important for initial page load)
	if ( empty( $billing_country ) ) {
		return $available_gateways;
	}
	
	// Countries that should use DOKU
	$doku_countries = array( 'SG', 'MY', 'ID' );
	
	// Filter gateways based on country
	$filtered_gateways = array();
	
	if ( in_array( $billing_country, $doku_countries, true ) ) {
		// SG, MY, ID -> Only DOKU
		foreach ( $available_gateways as $gateway_id => $gateway ) {
			$gateway_id_lower = strtolower( $gateway_id );
			$gateway_title_lower = strtolower( $gateway->get_title() );
			$gateway_class = get_class( $gateway );
			$gateway_class_lower = strtolower( $gateway_class );
			
			// Check multiple ways to detect DOKU
			$is_doku = (
				strpos( $gateway_id_lower, 'doku' ) !== false || 
				strpos( $gateway_id_lower, 'jokul' ) !== false ||
				strpos( $gateway_title_lower, 'doku' ) !== false ||
				strpos( $gateway_title_lower, 'jokul' ) !== false ||
				strpos( $gateway_class_lower, 'doku' ) !== false ||
				strpos( $gateway_class_lower, 'jokul' ) !== false
			);
			
			if ( $is_doku ) {
				$filtered_gateways[ $gateway_id ] = $gateway;
			}
		}
	} else {
		// Other countries -> PayPal and Debit & Credit Cards only
		foreach ( $available_gateways as $gateway_id => $gateway ) {
			$gateway_id_lower = strtolower( $gateway_id );
			$gateway_title_lower = strtolower( $gateway->get_title() );
			
			// Check if it's PayPal
			$is_paypal = ( strpos( $gateway_id_lower, 'paypal' ) !== false || 
			               strpos( $gateway_id_lower, 'ppcp' ) !== false ||
			               strpos( $gateway_title_lower, 'paypal' ) !== false );
			
			// Check if it's Credit/Debit Card
			$is_card = ( strpos( $gateway_id_lower, 'card' ) !== false || 
			             strpos( $gateway_id_lower, 'credit' ) !== false || 
			             strpos( $gateway_id_lower, 'debit' ) !== false ||
			             strpos( $gateway_id_lower, 'stripe' ) !== false ||
			             strpos( $gateway_title_lower, 'debit' ) !== false ||
			             strpos( $gateway_title_lower, 'credit' ) !== false ||
			             strpos( $gateway_title_lower, 'card' ) !== false );
			
			if ( $is_paypal || $is_card ) {
				$filtered_gateways[ $gateway_id ] = $gateway;
			}
		}
	}
	
	// If no gateways match, return original (fallback to prevent empty payment methods)
	if ( empty( $filtered_gateways ) ) {
		return $available_gateways;
	}
	
	// Auto-select DOKU for SG, MY, ID countries
	if ( in_array( $billing_country, $doku_countries, true ) && ! empty( $filtered_gateways ) ) {
		// Set first DOKU gateway as chosen/default
		foreach ( $filtered_gateways as $gateway_id => $gateway ) {
			$gateway->chosen = true;
			break; // Set first one as chosen
		}
	}
	
	return $filtered_gateways;
}

/**
 * Validate payment method during checkout process
 * Ensure SG, MY, ID customers use DOKU
 */
add_action( 'woocommerce_checkout_process', 'wp_augoose_validate_payment_method_by_country', 10 );
function wp_augoose_validate_payment_method_by_country() {
	if ( ! isset( $_POST['payment_method'] ) || ! isset( $_POST['billing_country'] ) ) {
		return;
	}
	
	$payment_method = sanitize_text_field( $_POST['payment_method'] );
	$billing_country = sanitize_text_field( $_POST['billing_country'] );
	
	// Countries that must use DOKU
	$doku_countries = array( 'SG', 'MY', 'ID' );
	
	if ( in_array( $billing_country, $doku_countries, true ) ) {
		// Must use DOKU for SG, MY, ID
		$payment_method_lower = strtolower( $payment_method );
		$is_doku = (
			strpos( $payment_method_lower, 'doku' ) !== false || 
			strpos( $payment_method_lower, 'jokul' ) !== false
		);
		
		if ( ! $is_doku ) {
			// Find DOKU gateway and force it
			$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
			$doku_gateway_id = null;
			
			foreach ( $available_gateways as $gateway_id => $gateway ) {
				$gateway_id_lower = strtolower( $gateway_id );
				if ( strpos( $gateway_id_lower, 'doku' ) !== false || 
				     strpos( $gateway_id_lower, 'jokul' ) !== false ) {
					$doku_gateway_id = $gateway_id;
					break;
				}
			}
			
			if ( $doku_gateway_id ) {
				// Force DOKU payment method
				$_POST['payment_method'] = $doku_gateway_id;
				wc_add_notice( 'Payment method has been automatically set to DOKU for your location.', 'notice' );
			} else {
				wc_add_notice( 'DOKU payment method is required for your location. Please contact us if you need assistance.', 'error' );
			}
		}
	} else {
		// Other countries should not use DOKU
		$payment_method_lower = strtolower( $payment_method );
		$is_doku = (
			strpos( $payment_method_lower, 'doku' ) !== false || 
			strpos( $payment_method_lower, 'jokul' ) !== false
		);
		
		if ( $is_doku ) {
			// Find alternative payment method (PayPal or Card)
			$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
			$alternative_gateway_id = null;
			
			foreach ( $available_gateways as $gateway_id => $gateway ) {
				$gateway_id_lower = strtolower( $gateway_id );
				$gateway_title_lower = strtolower( $gateway->get_title() );
				
				$is_paypal = ( strpos( $gateway_id_lower, 'paypal' ) !== false || 
				               strpos( $gateway_id_lower, 'ppcp' ) !== false ||
				               strpos( $gateway_title_lower, 'paypal' ) !== false );
				
				$is_card = ( strpos( $gateway_id_lower, 'card' ) !== false || 
				             strpos( $gateway_id_lower, 'credit' ) !== false || 
				             strpos( $gateway_id_lower, 'debit' ) !== false ||
				             strpos( $gateway_id_lower, 'stripe' ) !== false );
				
				if ( $is_paypal || $is_card ) {
					$alternative_gateway_id = $gateway_id;
					break;
				}
			}
			
			if ( $alternative_gateway_id ) {
				$_POST['payment_method'] = $alternative_gateway_id;
				wc_add_notice( 'Payment method has been automatically set for your location.', 'notice' );
			} else {
				wc_add_notice( 'Please select a valid payment method for your location.', 'error' );
			}
		}
	}
}

/**
 * Auto-select DOKU payment method via JavaScript when country changes
 */
add_action( 'wp_footer', 'wp_augoose_auto_select_doku_js' );
function wp_augoose_auto_select_doku_js() {
	if ( ! is_checkout() ) {
		return;
	}
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		var dokuCountries = ['SG', 'MY', 'ID'];
		
		function autoSelectDoku() {
			var billingCountry = $('select[name="billing_country"]').val();
			
			if (billingCountry && dokuCountries.indexOf(billingCountry) !== -1) {
				// Find DOKU payment method and select it
				$('input[name="payment_method"]').each(function() {
					var paymentMethod = $(this).val().toLowerCase();
					if (paymentMethod.indexOf('doku') !== -1 || paymentMethod.indexOf('jokul') !== -1) {
						$(this).prop('checked', true).trigger('change');
						return false; // Break loop
					}
				});
			}
		}
		
		// Run on page load
		autoSelectDoku();
		
		// Run when country changes
		$(document.body).on('change', 'select[name="billing_country"]', function() {
			setTimeout(autoSelectDoku, 500);
		});
		
		// Run after checkout update
		$(document.body).on('updated_checkout', function() {
			setTimeout(autoSelectDoku, 500);
		});
	});
	</script>
	<?php
}

/**
 * Force payment gateway titles and descriptions to English
 */
add_filter( 'woocommerce_gateway_title', 'wp_augoose_payment_gateway_title_english', 20, 2 );
function wp_augoose_payment_gateway_title_english( $title, $gateway_id ) {
	// Common Indonesian payment gateway titles
	$indonesian_titles = array(
		'Bayar di tempat' => 'Cash on Delivery',
		'Bayar di Tempat' => 'Cash on Delivery',
		'bayar di tempat' => 'Cash on Delivery',
		'Bayar Pesanan Dengan DOKU Checkout' => 'Pay Order With DOKU Checkout',
		'Bayar pesanan dengan DOKU Checkout' => 'Pay Order With DOKU Checkout',
		'DOKU Checkout' => 'DOKU Checkout', // Keep as is if already English
	);
	
	if ( isset( $indonesian_titles[ $title ] ) ) {
		return $indonesian_titles[ $title ];
	}
	
	// Check for common Indonesian phrases
	if ( strpos( $title, 'Bayar' ) !== false || strpos( $title, 'bayar' ) !== false ) {
		if ( strpos( $title, 'tempat' ) !== false || strpos( $title, 'Tempat' ) !== false ) {
			return 'Cash on Delivery';
		}
		if ( strpos( $title, 'DOKU' ) !== false ) {
			return str_replace( array( 'Bayar Pesanan Dengan', 'Bayar pesanan dengan' ), 'Pay Order With', $title );
		}
	}
	
	return $title;
}

/**
 * Force payment gateway descriptions to English
 */
add_filter( 'woocommerce_gateway_description', 'wp_augoose_payment_gateway_description_english', 20, 2 );
function wp_augoose_payment_gateway_description_english( $description, $gateway_id ) {
	if ( empty( $description ) ) {
		return $description;
	}
	
	// Common Indonesian payment descriptions
	$indonesian_descriptions = array(
		'Bayar di tempat saat penyerahan produk.' => 'Pay on the spot upon product delivery.',
		'Bayar di Tempat saat penyerahan produk.' => 'Pay on the spot upon product delivery.',
		'bayar di tempat saat penyerahan produk' => 'Pay on the spot upon product delivery',
		'penyerahan produk' => 'product delivery',
		'Penyerahan produk' => 'Product delivery',
	);
	
	foreach ( $indonesian_descriptions as $indonesian => $english ) {
		if ( strpos( $description, $indonesian ) !== false ) {
			$description = str_replace( $indonesian, $english, $description );
		}
	}
	
	return $description;
}

/**
 * Force payment gateway order button text to English
 */
add_filter( 'woocommerce_gateway_order_button_text', 'wp_augoose_payment_gateway_order_button_text_english', 20, 2 );
function wp_augoose_payment_gateway_order_button_text_english( $button_text, $gateway_id ) {
	if ( empty( $button_text ) ) {
		return $button_text;
	}
	
	// Common Indonesian order button texts
	$indonesian_buttons = array(
		'Bayar Pesanan Dengan DOKU Checkout' => 'Pay Order With DOKU Checkout',
		'Bayar pesanan dengan DOKU Checkout' => 'Pay Order With DOKU Checkout',
		'Bayar sekarang' => 'Pay now',
		'Bayar Sekarang' => 'Pay Now',
		'Bayar' => 'Pay',
		'bayar' => 'Pay',
	);
	
	if ( isset( $indonesian_buttons[ $button_text ] ) ) {
		return $indonesian_buttons[ $button_text ];
	}
	
	// Check for common Indonesian phrases
	if ( strpos( $button_text, 'Bayar' ) !== false || strpos( $button_text, 'bayar' ) !== false ) {
		if ( strpos( $button_text, 'DOKU' ) !== false ) {
			return str_replace( array( 'Bayar Pesanan Dengan', 'Bayar pesanan dengan' ), 'Pay Order With', $button_text );
		}
		if ( strpos( $button_text, 'sekarang' ) !== false || strpos( $button_text, 'Sekarang' ) !== false ) {
			return 'Pay Now';
		}
		return 'Pay';
	}
	
	return $button_text;
}

/**
 * Force payment gateway payment fields output to English
 * This handles any Indonesian text in payment gateway fields
 */
add_filter( 'woocommerce_gateway_payment_fields', 'wp_augoose_payment_gateway_fields_english', 20, 2 );
function wp_augoose_payment_gateway_fields_english( $fields, $gateway_id ) {
	if ( empty( $fields ) ) {
		return $fields;
	}
	
	// If fields is HTML string, replace Indonesian text
	if ( is_string( $fields ) ) {
		$replacements = array(
			'Bayar di tempat saat penyerahan produk.' => 'Pay on the spot upon product delivery.',
			'Bayar di Tempat saat penyerahan produk.' => 'Pay on the spot upon product delivery.',
			'penyerahan produk' => 'product delivery',
			'Penyerahan produk' => 'Product delivery',
		);
		
		foreach ( $replacements as $indonesian => $english ) {
			$fields = str_replace( $indonesian, $english, $fields );
		}
	}
	
	return $fields;
}

function wp_augoose_render_wishlist_sidebar() {
	// CRITICAL: Skip during AJAX requests to prevent HTML output before JSON
	if ( augoose_is_wc_ajax_request() ) {
		return;
	}
	
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	$count = wp_augoose_wishlist_count();
	$checkout_url = wc_get_checkout_url();
	?>
	<div class="wishlist-sidebar-overlay" style="display:none;"></div>
	<aside class="wishlist-sidebar" aria-label="<?php esc_attr_e( 'Wishlist', 'wp-augoose' ); ?>" style="display:none;">
		<div class="wishlist-sidebar-header">
			<div class="wishlist-sidebar-title">WISHLIST</div>
			<button type="button" class="wishlist-sidebar-close" aria-label="<?php esc_attr_e( 'Close wishlist', 'wp-augoose' ); ?>">×</button>
		</div>
		<div class="wishlist-sidebar-body" data-count="<?php echo esc_attr( $count ); ?>">
			<!-- filled by AJAX -->
		</div>
		<div class="wishlist-sidebar-footer">
			<a class="wishlist-sidebar-btn wishlist-sidebar-btn-checkout" href="<?php echo esc_url( $checkout_url ); ?>">PAYMENT</a>
			<a class="wishlist-sidebar-btn wishlist-sidebar-btn-view" href="<?php echo esc_url( home_url( '/wishlist/' ) ); ?>">VIEW WISHLIST</a>
		</div>
	</aside>
	<?php
}
add_action( 'wp_footer', 'wp_augoose_render_wishlist_sidebar', 30 );

/**
 * Remove default WooCommerce wrappers.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

/**
 * Add custom WooCommerce wrappers.
 */
add_action( 'woocommerce_before_main_content', 'wp_augoose_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'wp_augoose_wrapper_end', 10 );

function wp_augoose_wrapper_start() {
	// Don't add wrapper for checkout and cart pages (they have their own wrapper in templates)
	if ( is_checkout() || is_cart() ) {
		// Just add main tag without container, templates will handle their own containers
		echo '<main id="primary" class="site-main">';
		return;
	}
	echo '<main id="primary" class="site-main"><div class="container">';
}

function wp_augoose_wrapper_end() {
	// Don't add wrapper for checkout and cart pages (they have their own wrapper in templates)
	if ( is_checkout() || is_cart() ) {
		echo '</main>';
		return;
	}
	echo '</div></main>';
}

/**
 * Sample implementation of the WooCommerce Mini Cart.
 */
if ( ! function_exists( 'wp_augoose_woocommerce_cart_link_fragment' ) ) {
	/**
	 * Cart Fragments.
	 *
	 * @param array $fragments Fragments to refresh via AJAX.
	 * @return array Fragments to refresh via AJAX.
	 */
	function wp_augoose_woocommerce_cart_link_fragment( $fragments ) {
		ob_start();
		?>
		<button type="button" class="cart-icon" data-toggle="cart-sidebar" aria-label="<?php echo esc_attr__( 'Cart', 'wp-augoose' ); ?>">
			<span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
				<circle cx="9" cy="21" r="1"></circle>
				<circle cx="20" cy="21" r="1"></circle>
				<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
			</svg>
		</button>
		<?php
		$fragments['button.cart-icon'] = ob_get_clean();

		return $fragments;
	}
}
add_filter( 'woocommerce_add_to_cart_fragments', 'wp_augoose_woocommerce_cart_link_fragment' );

/**
 * Cart page: redirect to shop and rely on cart sidebar instead.
 * Adds `?open_cart=1` so JS can auto-open the sidebar.
 */
add_action(
	'template_redirect',
	function () {
		// CRITICAL: Skip during AJAX requests (including wc-ajax)
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || augoose_is_wc_ajax_request() ) {
			return;
		}
		if ( function_exists( 'is_cart' ) && is_cart() ) {
			$target = '';

			// Prefer returning the user to where they came from (if it's not cart/checkout).
			$ref = wp_get_referer();
			if ( $ref ) {
				$ref_path = wp_parse_url( $ref, PHP_URL_PATH );
				if ( $ref_path && false === stripos( $ref_path, '/cart' ) && false === stripos( $ref_path, '/checkout' ) ) {
					$target = $ref;
				}
			}

			if ( ! $target ) {
				$target = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
			}

			$target = add_query_arg( 'open_cart', '1', $target );
			wp_safe_redirect( $target, 302 );
			exit;
		}
	},
	20
);

/**
 * Add mini cart fragments for sidebar
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'wp_augoose_mini_cart_fragments' );
function wp_augoose_mini_cart_fragments( $fragments ) {
    // Use custom mini cart template
    ob_start();
    ?>
    <div class="cart-sidebar-items">
        <?php
        if ( ! WC()->cart->is_empty() ) {
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                
                if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 ) {
                    $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                    ?>
                    <div class="woocommerce-mini-cart-item">
                        <?php
                        $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                        if ( $product_permalink ) {
                            printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
                        } else {
                            echo $thumbnail;
                        }
                        ?>
                        
                        <div class="cart-sidebar-item-details">
                            <div class="woocommerce-mini-cart-item__product-name">
                                <?php
                                if ( $product_permalink ) {
                                    echo '<a href="' . esc_url( $product_permalink ) . '">' . wp_kses_post( $_product->get_name() ) . '</a>';
                                } else {
                                    echo wp_kses_post( $_product->get_name() );
                                }
                                ?>
                            </div>
                            
                            <div class="woocommerce-mini-cart-item__price">
                                <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?>
                            </div>
                            
                            <?php
                            $variation_data = wc_get_formatted_cart_item_data( $cart_item );
                            if ( $variation_data ) {
                                echo '<div class="woocommerce-mini-cart-item__variation">' . $variation_data . '</div>';
                            }
                            
                            // Quantity selector & remove
                            if ( $_product->is_sold_individually() ) {
                                $min_quantity = 1;
                                $max_quantity = 1;
                            } else {
                                $min_quantity = 0;
                                $max_quantity = $_product->get_max_purchase_quantity();
                            }
                            
                            $product_quantity = woocommerce_quantity_input(
                                array(
                                    'input_name'   => "cart[{$cart_item_key}][qty]",
                                    'input_value'  => $cart_item['quantity'],
                                    'max_value'    => $max_quantity,
                                    'min_value'    => $min_quantity,
                                    'product_name' => $_product->get_name(),
                                ),
                                $_product,
                                false
                            );
                            
                            $remove_link = sprintf(
                                '<a href="%s" class="cart-sidebar-remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">Remove</a>',
                                esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                esc_attr__( 'Remove this item', 'woocommerce' ),
                                esc_attr( $cart_item['product_id'] ),
                                esc_attr( $_product->get_sku() )
                            );
                            ?>
                            <div class="cart-sidebar-item-actions">
                                <div class="cart-sidebar-quantity">
                                    <?php echo $product_quantity; ?>
                                </div>
                                <?php echo $remove_link; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
        } else {
            echo '<p class="woocommerce-mini-cart__empty-message">' . esc_html__( 'Your cart is empty.', 'woocommerce' ) . '</p>';
        }
        ?>
    </div>
    <?php
    $fragments['div.cart-sidebar-items'] = ob_get_clean();
    
    // Update footer totals
    if ( ! WC()->cart->is_empty() ) {
        ob_start();
        ?>
        <div class="cart-sidebar-footer">
            <div class="cart-sidebar-total">
                <span class="cart-sidebar-total-label">Total</span>
                <span class="cart-sidebar-total-amount"><?php wc_cart_totals_order_total_html(); ?></span>
            </div>
            <div class="cart-sidebar-buttons">
                <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="cart-sidebar-btn cart-sidebar-btn-checkout">CHECKOUT</a>
            </div>
        </div>
        <?php
        $fragments['div.cart-sidebar-footer'] = ob_get_clean();
    } else {
        $fragments['div.cart-sidebar-footer'] = '';
    }
    
    return $fragments;
}


/**
 * Add mini cart HTML to footer - Custom dengan detail lengkap
 */
add_action( 'wp_footer', 'wp_augoose_mini_cart_html' );
function wp_augoose_mini_cart_html() {
	// CRITICAL: Skip during AJAX requests to prevent HTML output before JSON
	if ( augoose_is_wc_ajax_request() ) {
		return;
	}
	
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    ?>
    <div class="cart-sidebar-overlay"></div>
    <div class="woocommerce widget_shopping_cart">
        <div class="cart-sidebar-header">
            <h2 class="cart-sidebar-title">YOUR CART</h2>
            <button class="cart-sidebar-close" aria-label="Close cart">×</button>
        </div>
        <div class="cart-sidebar-items">
            <?php
            if ( ! WC()->cart->is_empty() ) {
                foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                    
                    if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 ) {
                        $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                        ?>
                        <div class="woocommerce-mini-cart-item">
                            <?php
                            $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                            if ( $product_permalink ) {
                                printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
                            } else {
                                echo $thumbnail;
                            }
                            ?>
                            
                            <div class="cart-sidebar-item-details">
                                <div class="woocommerce-mini-cart-item__product-name">
                                    <?php
                                    if ( $product_permalink ) {
                                        echo '<a href="' . esc_url( $product_permalink ) . '">' . wp_kses_post( $_product->get_name() ) . '</a>';
                                    } else {
                                        echo wp_kses_post( $_product->get_name() );
                                    }
                                    ?>
                                </div>
                                
                                <div class="woocommerce-mini-cart-item__price">
                                    <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?>
                                </div>
                                
                                <?php
                                $variation_data = wc_get_formatted_cart_item_data( $cart_item );
                                if ( $variation_data ) {
                                    echo '<div class="woocommerce-mini-cart-item__variation">' . $variation_data . '</div>';
                                }
                                
                                // Quantity selector & remove
                                if ( $_product->is_sold_individually() ) {
                                    $min_quantity = 1;
                                    $max_quantity = 1;
                                } else {
                                    $min_quantity = 0;
                                    $max_quantity = $_product->get_max_purchase_quantity();
                                }
                                
                                $product_quantity = woocommerce_quantity_input(
                                    array(
                                        'input_name'   => "cart[{$cart_item_key}][qty]",
                                        'input_value'  => $cart_item['quantity'],
                                        'max_value'    => $max_quantity,
                                        'min_value'    => $min_quantity,
                                        'product_name' => $_product->get_name(),
                                    ),
                                    $_product,
                                    false
                                );
                                
                                $remove_link = sprintf(
                                    '<a href="%s" class="cart-sidebar-remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">Remove</a>',
                                    esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                    esc_attr__( 'Remove this item', 'woocommerce' ),
                                    esc_attr( $cart_item['product_id'] ),
                                    esc_attr( $_product->get_sku() )
                                );
                                ?>
                                <div class="cart-sidebar-item-actions">
                                    <div class="cart-sidebar-quantity">
                                        <?php echo $product_quantity; ?>
                                    </div>
                                    <?php echo $remove_link; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
            } else {
                echo '<p class="woocommerce-mini-cart__empty-message">' . esc_html__( 'Your cart is empty.', 'woocommerce' ) . '</p>';
            }
            ?>
        </div>
        <?php if ( ! WC()->cart->is_empty() ) : ?>
            <div class="cart-sidebar-footer">
                <div class="cart-sidebar-total">
                    <span class="cart-sidebar-total-label">Total</span>
                    <span class="cart-sidebar-total-amount"><?php wc_cart_totals_order_total_html(); ?></span>
                </div>
                <div class="cart-sidebar-buttons">
                    <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="cart-sidebar-btn cart-sidebar-btn-checkout">PROCEED TO CHECKOUT</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Change number of products displayed per page - 12 products
 */
add_filter( 'loop_shop_per_page', 'wp_augoose_loop_shop_per_page', 20 );

function wp_augoose_loop_shop_per_page( $cols ) {
	return 12;
}

/**
 * Set products columns to 4
 */
add_filter( 'loop_shop_columns', 'wp_augoose_loop_shop_columns', 20 );

function wp_augoose_loop_shop_columns( $cols ) {
	return 4;
}

/**
 * Change number of related products on product page.
 */
function wp_augoose_related_products_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'wp_augoose_related_products_args' );

/**
 * Add custom product field for Material
 */
add_action( 'woocommerce_product_options_general_product_data', 'wp_augoose_add_product_material_field' );
function wp_augoose_add_product_material_field() {
	woocommerce_wp_text_input(
		array(
			'id'          => '_product_material',
			'label'       => __( 'Material', 'wp-augoose' ),
			'placeholder' => '100% Cotton Elastic, 13oz weight',
			'desc_tip'    => true,
			'description' => __( 'Enter the material information for this product.', 'wp-augoose' ),
		)
	);
}

/**
 * Save custom product field for Material
 */
add_action( 'woocommerce_process_product_meta', 'wp_augoose_save_product_material_field' );
function wp_augoose_save_product_material_field( $post_id ) {
	$material = isset( $_POST['_product_material'] ) ? sanitize_text_field( $_POST['_product_material'] ) : '';
	update_post_meta( $post_id, '_product_material', $material );
}

/**
 * Enqueue custom JS for variations
 */
add_action( 'wp_enqueue_scripts', 'wp_augoose_variation_scripts', 20 );
function wp_augoose_variation_scripts() {
	if ( is_product() && class_exists( 'WooCommerce' ) ) {
		// Ensure WooCommerce variation script is loaded
		if ( ! wp_script_is( 'wc-add-to-cart-variation', 'enqueued' ) ) {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
		}
		
		wp_add_inline_script( 'wc-add-to-cart-variation', '
			jQuery(document).ready(function($) {
				// Handle swatch clicks
				$(document).on("click", ".variation-swatch", function(e) {
					e.preventDefault();
					
					var $swatch = $(this);
					var value = $swatch.data("value");
					var attribute = $swatch.data("attribute");
					
					// Find the correct select element
					var $select = $("select[name=\"attribute_" + attribute + "\"]");
					if ($select.length === 0) {
						$select = $("select#" + attribute);
					}
					
					if ($select.length === 0) {
						console.error("Select element not found for attribute: " + attribute);
						return;
					}
					
					// Toggle active state
					$swatch.siblings(".variation-swatch").removeClass("is-active");
					$swatch.addClass("is-active");
					
					// Update hidden select - try exact match first, then sanitized match
					var found = false;
					$select.find("option").each(function() {
						var optionValue = $(this).val();
						if (optionValue === value || optionValue.toLowerCase() === value.toLowerCase()) {
							$select.val(optionValue);
							found = true;
							return false;
						}
					});
					
					if (!found) {
						// Try sanitized title match
						var sanitizedValue = value.toLowerCase().replace(/[^a-z0-9]+/g, "-");
						$select.find("option").each(function() {
							var optionValue = $(this).val();
							var sanitizedOption = optionValue.toLowerCase().replace(/[^a-z0-9]+/g, "-");
							if (sanitizedOption === sanitizedValue) {
								$select.val(optionValue);
								found = true;
								return false;
							}
						});
					}
					
					// Trigger change event to update variation
					if (found) {
						$select.trigger("change");
					} else {
						console.warn("Could not find matching option for value: " + value);
					}
				});
				
				// Size guide link - now opens image directly (no modal)
				// Link href is set in template, so it will open in new tab automatically
				// No JavaScript needed - removed preventDefault to allow direct link navigation
				
				// Close size guide modal
				$(document).on("click", ".size-guide-modal-close, .size-guide-modal-overlay", function(e) {
					e.preventDefault();
					$("#size-guide-modal").fadeOut(300);
					$("body").css("overflow", "");
				});
				
				// Tab navigation
				$(document).on("click", ".tabs-nav a", function(e) {
					e.preventDefault();
					var target = $(this).attr("href");
					
					$(".tabs-nav li").removeClass("active");
					$(this).parent().addClass("active");
					
					$(".tab-panel").removeClass("active");
					$(target).addClass("active");
				});
				
				// Share button
				$(document).on("click", ".share-button", function(e) {
					e.preventDefault();
					if (navigator.share) {
						navigator.share({
							title: document.title,
							url: window.location.href
						});
					} else {
						// Fallback: copy to clipboard
						var url = window.location.href;
						if (navigator.clipboard) {
							navigator.clipboard.writeText(url).then(function() {
								alert("Link copied to clipboard!");
							});
						}
					}
				});
				
				// Ensure form can be submitted
				$(document).on("submit", ".variations_form.cart", function(e) {
					// Check if all required variations are selected
					var allSelected = true;
					var missingAttributes = [];
					
					$(".variation-select-hidden").each(function() {
						var $select = $(this);
						var attributeName = $select.data("attribute_name") || $select.attr("name");
						if ($select.val() === "" || $select.val() === null) {
							allSelected = false;
							// Get label
							var label = $select.closest(".variation-group").find(".variation-header label").text() || attributeName;
							missingAttributes.push(label);
						}
					});
					
					if (!allSelected) {
						e.preventDefault();
						var message = "Please select: " + missingAttributes.join(", ");
						alert(message);
						
						// Highlight missing fields
						$(".variation-select-hidden").each(function() {
							var $select = $(this);
							if ($select.val() === "" || $select.val() === null) {
								$select.closest(".variation-group").addClass("error");
								setTimeout(function() {
									$select.closest(".variation-group").removeClass("error");
								}, 3000);
							}
						});
						
						return false;
					}
				});
				
				// Remove error class when variation is selected
				$(document).on("change", ".variation-select-hidden", function() {
					$(this).closest(".variation-group").removeClass("error");
				});
			});
		' );
	}
}

/**
 * Remove default WooCommerce hooks from single product summary
 */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

/**
 * Remove default WooCommerce SALE badge (will use custom badge from content-product.php)
 * WooCommerce default sale badge tidak terintegrasi dengan styling kita
 */
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );

/**
 * Remove default loop "add to cart" button.
 * We render our own CTA in `woocommerce/content-product.php` (either ADD TO CART or VIEW PRODUCT).
 */
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

// Safety: ensure the removal happens AFTER WooCommerce registers its default hooks.
add_action(
	'init',
	function () {
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	},
	20
);

/**
 * Add custom SALE badge yang terintegrasi
 */
add_action( 'woocommerce_before_single_product_summary', 'wp_augoose_custom_sale_badge', 10 );

function wp_augoose_custom_sale_badge() {
	global $product;
	
	if ( ! $product->is_on_sale() ) {
		return;
	}
	
	// Calculate discount percentage
	// BULLETPROOF: Gunakan get_price() yang sudah di-filter WCML
	// Jangan pakai get_variation_regular_price() karena bisa tidak sejalan dengan WCML cache
	$regular_price = $product->get_regular_price();
	$sale_price = $product->get_sale_price();
	
	$percentage = '';
	if ( $regular_price && $sale_price ) {
		$percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
	}
	
	// Untuk variable product, get_price() akan return min price
	// WCML sudah handle conversion + cache, jadi kita cukup pakai hasilnya
	
	?>
	<span class="onsale woocommerce-onsale">
		<?php if ( $percentage ) : ?>
			<?php echo esc_html( $percentage ); ?>% OFF
		<?php else : ?>
			SALE
		<?php endif; ?>
	</span>
	<?php
}

/**
 * Remove default tabs (we have custom tabs)
 */
add_filter( 'woocommerce_product_tabs', '__return_empty_array', 99 );

/**
 * Format cart item data display
 */
add_filter( 'woocommerce_cart_item_name', 'wp_augoose_cart_item_name', 10, 3 );
function wp_augoose_cart_item_name( $product_name, $cart_item, $cart_item_key ) {
	// Remove default variation display
	$product_name = strip_tags( $product_name );
	return $product_name;
}

/**
 * Remove edit link from checkout review order
 * Hide edit link that appears in checkout order review table
 */
add_filter( 'woocommerce_cart_item_name', 'wp_augoose_remove_edit_link_from_checkout', 20, 3 );
function wp_augoose_remove_edit_link_from_checkout( $product_name, $cart_item, $cart_item_key ) {
	// Only remove edit link in checkout context
	if ( is_checkout() ) {
		// Remove any edit links that might be added by plugins or themes
		$product_name = preg_replace( '/<a[^>]*class="[^"]*edit[^"]*"[^>]*>.*?<\/a>/i', '', $product_name );
		$product_name = preg_replace( '/<span[^>]*class="[^"]*edit[^"]*"[^>]*>.*?<\/span>/i', '', $product_name );
	}
	return $product_name;
}

/**
 * Remove duplicate payment method from order details table
 * Payment method is already shown in order overview, so hide it in order details table
 */
add_filter( 'woocommerce_get_order_item_totals', 'wp_augoose_remove_payment_method_from_order_details', 10, 2 );
function wp_augoose_remove_payment_method_from_order_details( $total_rows, $order ) {
	// Remove payment method from order details table (already shown in overview)
	if ( isset( $total_rows['payment_method'] ) ) {
		unset( $total_rows['payment_method'] );
	}
	return $total_rows;
}

/**
 * Ensure DOKU payment uses amount and currency directly from WCML/WooCommerce order
 * WITHOUT any manual modification, formatting, or recalculation
 * 
 * Core Rules:
 * 1. Use amount from order object as single source of truth
 * 2. NO override, manual input, or reformatting
 * 3. NO comma (,) in amount - only dot (.) for decimal
 * 4. Format: IDR = integer, others = 2 decimals
 * 5. Separate display data from transaction data
 * 
 * @param array $args Payment gateway arguments
 * @param WC_Order $order Order object
 * @return array Modified arguments
 */
/**
 * Helper function to format amount for DOKU payment (no comma allowed)
 * 
 * @param float|string $amount The amount to format
 * @param string $currency The currency code (IDR, SGD, MYR, etc.)
 * @return string Formatted amount without comma
 */
function wp_augoose_format_doku_amount( $amount, $currency ) {
	// Step 1: Convert to string and remove ALL non-numeric characters except decimal point
	$amount_str = (string) $amount;
	// Remove commas, spaces, and any other formatting
	$amount_str = preg_replace( '/[^\d.]/', '', $amount_str );
	
	// Step 2: Convert to float for calculation
	$amount_float = (float) $amount_str;
	
	// Step 3: Format by currency type
	if ( $currency === 'IDR' ) {
		// IDR: Integer only (no decimals) - round to nearest integer
		$formatted = (string) round( $amount_float );
	} else {
		// Other currencies (SGD, MYR, etc.): 2 decimal places
		// Use number_format with explicit separators to prevent locale issues
		$formatted = number_format( $amount_float, 2, '.', '' );
	}
	
	// Step 4: Final validation - absolutely no comma allowed
	if ( strpos( $formatted, ',' ) !== false ) {
		error_log( 'DOKU Payment Error: Comma found in formatted amount: ' . $formatted . ' (Currency: ' . $currency . ')');
		// Emergency fallback: remove comma and reformat
		$formatted = str_replace( ',', '', $formatted );
		$amount_float = (float) $formatted;
		if ( $currency === 'IDR' ) {
			$formatted = (string) round( $amount_float );
		} else {
			$formatted = number_format( $amount_float, 2, '.', '' );
		}
	}
	
	return $formatted;
}

add_filter( 'woocommerce_gateway_doku_payment_args', 'wp_augoose_ensure_doku_amount_from_order', 10, 2 );
add_filter( 'woocommerce_gateway_jokul_payment_args', 'wp_augoose_ensure_doku_amount_from_order', 10, 2 );
function wp_augoose_ensure_doku_amount_from_order( $args, $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return $args;
	}
	
	// Step 1: Get amount directly from order object (WCML already converted)
	// Use get_total() which returns float, but be safe and handle string formatting
	$order_total = $order->get_total();
	$currency = $order->get_currency();
	
	// Step 2: Format amount using helper function (removes all commas)
	$amount = wp_augoose_format_doku_amount( $order_total, $currency );
	
	// Step 3: Set amount and currency in args (override any manual values)
	// Clean ALL possible amount fields in args
	$amount_fields = array( 'amount', 'order_amount', 'payment_amount', 'doku_amount', 'total', 'order_total', 'value', 'price' );
	foreach ( $amount_fields as $field ) {
		if ( isset( $args[ $field ] ) ) {
			// If field contains comma, clean it
			$field_value = (string) $args[ $field ];
			if ( strpos( $field_value, ',' ) !== false ) {
				$args[ $field ] = wp_augoose_format_doku_amount( $field_value, $currency );
			} else {
				// Even if no comma, ensure it's properly formatted
				$args[ $field ] = wp_augoose_format_doku_amount( $field_value, $currency );
			}
		}
	}
	
	// Always set main amount field
	$args['amount'] = $amount;
	$args['currency'] = $currency;
	
	// Log for debugging (remove in production if needed)
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( sprintf(
			'DOKU Payment Args: Order #%d - Original: %s, Formatted: %s, Currency: %s, Args: %s',
			$order->get_id(),
			$order_total,
			$amount,
			$currency,
			wp_json_encode( $args )
		) );
	}
	
	return $args;
}

/**
 * Ensure DOKU payment gateway uses order total directly
 * Hook into payment processing to validate amount source
 * This runs BEFORE payment processing to ensure clean amount
 */
add_action( 'woocommerce_checkout_order_processed', 'wp_augoose_validate_doku_order_amount', 5, 3 );
add_action( 'woocommerce_before_pay', 'wp_augoose_validate_doku_order_amount_before_pay', 5 );
// Hook paling awal untuk membersihkan amount SEBELUM validasi gateway
add_action( 'woocommerce_before_checkout_process', 'wp_augoose_clean_cart_total_for_doku', 0 );
add_action( 'woocommerce_checkout_process', 'wp_augoose_force_clean_amount_before_gateway_validate', 0 );
add_action( 'woocommerce_checkout_process', 'wp_augoose_validate_doku_amount_before_checkout', 1 );
add_action( 'woocommerce_after_checkout_validation', 'wp_augoose_clean_doku_amount_before_validation', 1, 2 );

// Filter untuk membersihkan cart total string (jika DOKU membaca dari formatted string)
add_filter( 'woocommerce_cart_total', 'wp_augoose_clean_cart_total_string', 999, 1 );

// Filter untuk memastikan amount bersih saat gateway membaca dari cart
// Note: woocommerce_cart_get_total mungkin tidak ada, jadi kita gunakan filter lain
add_filter( 'woocommerce_cart_subtotal', 'wp_augoose_clean_cart_get_total', 999, 1 );
add_filter( 'woocommerce_cart_contents_total', 'wp_augoose_clean_cart_get_total', 999, 1 );

// Filter untuk formatted price - DOKU mungkin membaca dari formatted price
add_filter( 'formatted_woocommerce_price', 'wp_augoose_clean_formatted_price_for_doku', 999, 5 );

// Hook ke process_payment untuk memastikan amount bersih sebelum DOKU memproses
add_action( 'woocommerce_api_wc_gateway_doku', 'wp_augoose_clean_doku_amount_before_api', 0 );
add_action( 'woocommerce_api_wc_gateway_jokul', 'wp_augoose_clean_doku_amount_before_api', 0 );

// Hook sebelum process_payment untuk memastikan order total bersih
add_action( 'woocommerce_before_payment', 'wp_augoose_clean_order_total_before_payment', 0 );
add_filter( 'woocommerce_order_get_total', 'wp_augoose_clean_order_get_total_for_doku', 999, 2 );
add_filter( 'woocommerce_order_formatted_total', 'wp_augoose_clean_order_formatted_total_for_doku', 999, 2 );

/**
 * Force clean amount BEFORE gateway validation runs
 * This runs with priority 0 to ensure it executes before DOKU's validate_fields()
 * This prevents "Amount are not allowed using comma" error
 */
function wp_augoose_force_clean_amount_before_gateway_validate() {
	// Pastikan cart ada
	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}

	// Hanya untuk DOKU/Jokul
	$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';
	if ( strpos( strtolower( $payment_method ), 'doku' ) === false && strpos( strtolower( $payment_method ), 'jokul' ) === false ) {
		return;
	}

	// Currency paling aman (ambil dari cookie kalau ada)
	$currency = get_woocommerce_currency();
	if ( isset( $_COOKIE['wp_augoose_currency'] ) && $_COOKIE['wp_augoose_currency'] ) {
		$currency = sanitize_text_field( $_COOKIE['wp_augoose_currency'] );
	}

	// Ambil total versi "edit" (lebih raw, tidak formatted)
	$raw_total = WC()->cart->get_total( 'edit' ); // biasanya string numeric tanpa simbol
	
	// Pastikan raw_total tidak mengandung koma (double check)
	if ( is_string( $raw_total ) && strpos( $raw_total, ',' ) !== false ) {
		$raw_total = str_replace( ',', '', $raw_total );
	}
	
	// Convert to float first, then format
	$raw_total_float = (float) preg_replace( '/[^\d.]/', '', (string) $raw_total );
	$clean_amount = wp_augoose_format_doku_amount( $raw_total_float, $currency );

	// Paksa isi field yang sering dicek gateway (karena kita tidak tahu DOKU cek yang mana)
	// Clean semua kemungkinan field amount di $_POST, $_GET, dan $_REQUEST
	$amount_fields = array( 'amount', 'order_amount', 'payment_amount', 'doku_amount', 'total', 'order_total', 'payment_total' );
	foreach ( $amount_fields as $field ) {
		$_POST[ $field ] = $clean_amount;
		$_GET[ $field ] = $clean_amount;
		$_REQUEST[ $field ] = $clean_amount;
	}
	$_POST['currency'] = $currency;
	$_GET['currency'] = $currency;
	$_REQUEST['currency'] = $currency;

	// Debug sementara (hapus setelah beres)
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( "DOKU PRE-VALIDATE: raw_total={$raw_total} currency={$currency} clean={$clean_amount}" );
		error_log( "DOKU PRE-VALIDATE: _POST[amount]=" . ( isset( $_POST['amount'] ) ? $_POST['amount'] : 'not set' ) );
		error_log( "DOKU PRE-VALIDATE: _POST[doku_amount]=" . ( isset( $_POST['doku_amount'] ) ? $_POST['doku_amount'] : 'not set' ) );
	}
}

/**
 * Clean cart total before checkout process starts
 * This runs even earlier to ensure cart total is clean
 */
function wp_augoose_clean_cart_total_for_doku() {
	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}

	// Hanya untuk DOKU/Jokul
	$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';
	if ( strpos( strtolower( $payment_method ), 'doku' ) === false && strpos( strtolower( $payment_method ), 'jokul' ) === false ) {
		return;
	}

	// Force recalculate cart totals to ensure clean values
	WC()->cart->calculate_totals();
}

/**
 * Filter cart total string to remove comma
 * This ensures any formatted cart total string is clean
 */
function wp_augoose_clean_cart_total_string( $total ) {
	// Only clean if DOKU payment method is selected
	$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';
	if ( strpos( strtolower( $payment_method ), 'doku' ) === false && strpos( strtolower( $payment_method ), 'jokul' ) === false ) {
		return $total;
	}

	// Remove comma from total string
	if ( strpos( $total, ',' ) !== false ) {
		// Extract numeric value
		$numeric_value = preg_replace( '/[^\d.]/', '', $total );
		$currency = get_woocommerce_currency();
		if ( isset( $_COOKIE['wp_augoose_currency'] ) && $_COOKIE['wp_augoose_currency'] ) {
			$currency = sanitize_text_field( $_COOKIE['wp_augoose_currency'] );
		}
		$clean_amount = wp_augoose_format_doku_amount( $numeric_value, $currency );
		
		// Return formatted without comma (preserve currency symbol if any)
		$currency_symbol = get_woocommerce_currency_symbol( $currency );
		return $currency_symbol . $clean_amount;
	}

	return $total;
}

/**
 * Filter cart get_total() to ensure clean numeric value
 * This catches when gateway reads amount directly from cart
 */
function wp_augoose_clean_cart_get_total( $total ) {
	// Only clean if DOKU payment method is selected
	$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';
	if ( strpos( strtolower( $payment_method ), 'doku' ) === false && strpos( strtolower( $payment_method ), 'jokul' ) === false ) {
		return $total;
	}

	// If total is string and contains comma, clean it
	if ( is_string( $total ) && strpos( $total, ',' ) !== false ) {
		$numeric_value = preg_replace( '/[^\d.]/', '', $total );
		$currency = get_woocommerce_currency();
		if ( isset( $_COOKIE['wp_augoose_currency'] ) && $_COOKIE['wp_augoose_currency'] ) {
			$currency = sanitize_text_field( $_COOKIE['wp_augoose_currency'] );
		}
		return wp_augoose_format_doku_amount( $numeric_value, $currency );
	}

	// If total is float/numeric, ensure it's formatted correctly
	if ( is_numeric( $total ) ) {
		$currency = get_woocommerce_currency();
		if ( isset( $_COOKIE['wp_augoose_currency'] ) && $_COOKIE['wp_augoose_currency'] ) {
			$currency = sanitize_text_field( $_COOKIE['wp_augoose_currency'] );
		}
		return wp_augoose_format_doku_amount( $total, $currency );
	}

	return $total;
}

/**
 * Filter formatted price to remove comma for DOKU
 * This catches when DOKU reads formatted price strings
 */
function wp_augoose_clean_formatted_price_for_doku( $formatted_price, $price, $decimals, $decimal_separator, $thousand_separator ) {
	// Only clean if DOKU payment method is selected
	$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';
	if ( strpos( strtolower( $payment_method ), 'doku' ) === false && strpos( strtolower( $payment_method ), 'jokul' ) === false ) {
		return $formatted_price;
	}

	// Remove comma from formatted price
	if ( strpos( $formatted_price, ',' ) !== false ) {
		// Extract numeric value
		$numeric_value = preg_replace( '/[^\d.]/', '', $formatted_price );
		$currency = get_woocommerce_currency();
		if ( isset( $_COOKIE['wp_augoose_currency'] ) && $_COOKIE['wp_augoose_currency'] ) {
			$currency = sanitize_text_field( $_COOKIE['wp_augoose_currency'] );
		}
		$clean_amount = wp_augoose_format_doku_amount( $numeric_value, $currency );
		
		// Return without comma (preserve currency symbol if any)
		$currency_symbol = get_woocommerce_currency_symbol( $currency );
		return $currency_symbol . $clean_amount;
	}

	return $formatted_price;
}

/**
 * Clean amount before DOKU API processes payment
 * This runs when DOKU gateway API is called
 */
function wp_augoose_clean_doku_amount_before_api() {
	// Clean all amount fields in request
	$amount_fields = array( 'amount', 'order_amount', 'payment_amount', 'doku_amount', 'total', 'order_total' );
	$currency = get_woocommerce_currency();
	if ( isset( $_COOKIE['wp_augoose_currency'] ) && $_COOKIE['wp_augoose_currency'] ) {
		$currency = sanitize_text_field( $_COOKIE['wp_augoose_currency'] );
	}

	foreach ( $amount_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			$amount_value = sanitize_text_field( $_POST[ $field ] );
			if ( strpos( $amount_value, ',' ) !== false ) {
				$clean_amount = wp_augoose_format_doku_amount( $amount_value, $currency );
				$_POST[ $field ] = $clean_amount;
				$_REQUEST[ $field ] = $clean_amount;
			}
		}
		if ( isset( $_GET[ $field ] ) ) {
			$amount_value = sanitize_text_field( $_GET[ $field ] );
			if ( strpos( $amount_value, ',' ) !== false ) {
				$clean_amount = wp_augoose_format_doku_amount( $amount_value, $currency );
				$_GET[ $field ] = $clean_amount;
				$_REQUEST[ $field ] = $clean_amount;
			}
		}
	}
}

/**
 * Clean order total before payment is processed
 * This ensures order total is clean when DOKU reads it
 */
function wp_augoose_clean_order_total_before_payment() {
	// Get order from session or request
	$order_id = WC()->session->get( 'order_awaiting_payment' );
	if ( ! $order_id ) {
		$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
	}
	
	if ( ! $order_id ) {
		return;
	}
	
	$order = wc_get_order( $order_id );
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}
	
	// Only for DOKU payment methods
	$payment_method = $order->get_payment_method();
	if ( strpos( strtolower( $payment_method ), 'doku' ) === false && 
	     strpos( strtolower( $payment_method ), 'jokul' ) === false ) {
		return;
	}
	
	// Ensure clean amount is set in order meta
	wp_augoose_validate_doku_order_amount( $order_id, array(), $order );
}

/**
 * Filter order get_total to ensure clean amount for DOKU
 * This catches when DOKU reads order total directly
 */
function wp_augoose_clean_order_get_total_for_doku( $total, $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return $total;
	}
	
	// Only for DOKU payment methods
	$payment_method = $order->get_payment_method();
	if ( strpos( strtolower( $payment_method ), 'doku' ) === false && 
	     strpos( strtolower( $payment_method ), 'jokul' ) === false ) {
		return $total;
	}
	
	// If total is string and contains comma, clean it
	if ( is_string( $total ) && strpos( $total, ',' ) !== false ) {
		$numeric_value = preg_replace( '/[^\d.]/', '', $total );
		$currency = $order->get_currency();
		return wp_augoose_format_doku_amount( $numeric_value, $currency );
	}
	
	// If total is float/numeric, ensure it's formatted correctly (no comma)
	if ( is_numeric( $total ) ) {
		$currency = $order->get_currency();
		$formatted = wp_augoose_format_doku_amount( $total, $currency );
		// Return as float if original was float, string if original was string
		return is_float( $total ) ? (float) $formatted : $formatted;
	}
	
	return $total;
}

/**
 * Filter order formatted_total to ensure clean amount for DOKU
 * This catches when DOKU reads formatted order total string
 */
function wp_augoose_clean_order_formatted_total_for_doku( $formatted_total, $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return $formatted_total;
	}
	
	// Only for DOKU payment methods
	$payment_method = $order->get_payment_method();
	if ( strpos( strtolower( $payment_method ), 'doku' ) === false && 
	     strpos( strtolower( $payment_method ), 'jokul' ) === false ) {
		return $formatted_total;
	}
	
	// Remove comma from formatted total
	if ( strpos( $formatted_total, ',' ) !== false ) {
		// Extract numeric value
		$numeric_value = preg_replace( '/[^\d.]/', '', $formatted_total );
		$currency = $order->get_currency();
		$clean_amount = wp_augoose_format_doku_amount( $numeric_value, $currency );
		
		// Return formatted without comma (preserve currency symbol if any)
		$currency_symbol = get_woocommerce_currency_symbol( $currency );
		return $currency_symbol . $clean_amount;
	}
	
	return $formatted_total;
}

function wp_augoose_validate_doku_order_amount( $order_id, $posted_data, $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}
	
	// Only validate for DOKU payment methods
	$payment_method = $order->get_payment_method();
	if ( strpos( strtolower( $payment_method ), 'doku' ) === false && 
	     strpos( strtolower( $payment_method ), 'jokul' ) === false ) {
		return;
	}
	
	// Get amount and currency from order (WCML already converted)
	$order_total = $order->get_total();
	$order_currency = $order->get_currency();
	
	// Use helper function to format amount (removes all commas)
	$clean_amount = wp_augoose_format_doku_amount( $order_total, $order_currency );
	
	// Log if original had comma (for debugging)
	$total_str = (string) $order_total;
	if ( strpos( $total_str, ',' ) !== false ) {
		error_log( sprintf(
			'DOKU Payment Warning: Order #%d had comma in total: %s. Cleaned to: %s',
			$order_id,
			$total_str,
			$clean_amount
		) );
	}
	
	// Store in order meta (DOKU gateway can read this)
	$order->update_meta_data( '_doku_clean_amount', $clean_amount );
	$order->update_meta_data( '_doku_currency', $order_currency );
	$order->save_meta_data();
}

/**
 * Validate DOKU amount before pay for order page
 */
function wp_augoose_validate_doku_order_amount_before_pay() {
	global $wp;
	
	if ( ! isset( $wp->query_vars['order-pay'] ) ) {
		return;
	}
	
	$order_id = absint( $wp->query_vars['order-pay'] );
	$order = wc_get_order( $order_id );
	
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}
	
	$payment_method = $order->get_payment_method();
	if ( strpos( strtolower( $payment_method ), 'doku' ) === false && 
	     strpos( strtolower( $payment_method ), 'jokul' ) === false ) {
		return;
	}
	
	// Ensure clean amount is set
	wp_augoose_validate_doku_order_amount( $order_id, array(), $order );
}

/**
 * Validate DOKU amount before checkout process
 * This ensures amount is clean before any payment gateway validation
 */
function wp_augoose_validate_doku_amount_before_checkout() {
	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	
	// Check if DOKU payment method is selected
	$payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( $_POST['payment_method'] ) : '';
	if ( strpos( strtolower( $payment_method ), 'doku' ) === false && 
	     strpos( strtolower( $payment_method ), 'jokul' ) === false ) {
		return;
	}
	
	// Get cart total and ensure no comma (use 'edit' to get raw numeric value)
	$cart_total = WC()->cart->get_total( 'edit' );
	$cart_total_clean = str_replace( ',', '', $cart_total );
	
	// If there was a comma, update cart total
	if ( $cart_total !== $cart_total_clean ) {
		// This shouldn't happen, but if it does, log it
		error_log( 'DOKU Payment: Cart total contained comma, cleaning it: ' . $cart_total . ' -> ' . $cart_total_clean );
	}
	
	// Also clean any DOKU amount fields in $_POST if they exist
	if ( isset( $_POST['doku_amount'] ) ) {
		$doku_amount = sanitize_text_field( $_POST['doku_amount'] );
		$currency = isset( $_POST['currency'] ) ? sanitize_text_field( $_POST['currency'] ) : ( isset( $_COOKIE['wp_augoose_currency'] ) ? sanitize_text_field( $_COOKIE['wp_augoose_currency'] ) : get_woocommerce_currency() );
		$clean_doku_amount = wp_augoose_format_doku_amount( $doku_amount, $currency );
		$_POST['doku_amount'] = $clean_doku_amount;
	}
}

/**
 * Clean DOKU amount after checkout validation but before order creation
 * This ensures amount is clean even if DOKU plugin validates during checkout
 */
function wp_augoose_clean_doku_amount_before_validation( $data, $errors ) {
	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	
	// Check if DOKU payment method is selected
	$payment_method = isset( $data['payment_method'] ) ? sanitize_text_field( $data['payment_method'] ) : '';
	if ( strpos( strtolower( $payment_method ), 'doku' ) === false && 
	     strpos( strtolower( $payment_method ), 'jokul' ) === false ) {
		return;
	}
	
	// Get currency
	$currency = get_woocommerce_currency();
	if ( isset( $_COOKIE['wp_augoose_currency'] ) && $_COOKIE['wp_augoose_currency'] ) {
		$currency = sanitize_text_field( $_COOKIE['wp_augoose_currency'] );
	}
	
	// Clean any amount fields in $_POST that DOKU might use
	$amount_fields = array( 'doku_amount', 'amount', 'order_amount', 'payment_amount' );
	foreach ( $amount_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			$amount_value = sanitize_text_field( $_POST[ $field ] );
			$clean_amount = wp_augoose_format_doku_amount( $amount_value, $currency );
			$_POST[ $field ] = $clean_amount;
		}
	}
}

/**
 * Filter DOKU payment gateway to use clean amount from order meta
 * This ensures DOKU always gets properly formatted amount
 * Hook with high priority to override any plugin defaults
 */
add_filter( 'woocommerce_gateway_doku_amount', 'wp_augoose_get_doku_amount_from_order_meta', 999, 2 );
add_filter( 'woocommerce_gateway_jokul_amount', 'wp_augoose_get_doku_amount_from_order_meta', 999, 2 );
add_filter( 'doku_payment_amount', 'wp_augoose_get_doku_amount_from_order_meta', 999, 2 );
add_filter( 'jokul_payment_amount', 'wp_augoose_get_doku_amount_from_order_meta', 999, 2 );
function wp_augoose_get_doku_amount_from_order_meta( $amount, $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return $amount;
	}
	
	// First, ensure clean amount is set (in case validation hook didn't run)
	$clean_amount = $order->get_meta( '_doku_clean_amount' );
	if ( empty( $clean_amount ) ) {
		// Force validation now
		wp_augoose_validate_doku_order_amount( $order->get_id(), array(), $order );
		$clean_amount = $order->get_meta( '_doku_clean_amount' );
	}
	
	// Get clean amount from order meta
	if ( ! empty( $clean_amount ) ) {
		// Final validation - ensure no comma using helper function
		if ( strpos( $clean_amount, ',' ) !== false ) {
			$order_currency = $order->get_currency();
			$clean_amount = wp_augoose_format_doku_amount( $clean_amount, $order_currency );
			$order->update_meta_data( '_doku_clean_amount', $clean_amount );
			$order->save_meta_data();
		}
		return $clean_amount;
	}
	
	// Fallback: get from order total and format using helper function (ensures no comma)
	$order_total = $order->get_total();
	$order_currency = $order->get_currency();
	
	$amount = wp_augoose_format_doku_amount( $order_total, $order_currency );
	
	return $amount;
}

/**
 * Handle payment success/failure redirects
 * Redirect to custom pages based on payment status
 */
add_action( 'woocommerce_payment_complete', 'wp_augoose_handle_payment_success_redirect', 10, 1 );
add_action( 'woocommerce_thankyou', 'wp_augoose_handle_payment_success_redirect', 10, 1 );
function wp_augoose_handle_payment_success_redirect( $order_id ) {
	if ( ! $order_id ) {
		return;
	}
	
	$order = wc_get_order( $order_id );
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}
	
	// Only handle DOKU/Jokul payments
	$payment_method = $order->get_payment_method();
	if ( strpos( strtolower( $payment_method ), 'doku' ) === false && 
	     strpos( strtolower( $payment_method ), 'jokul' ) === false ) {
		return;
	}
	
	// If order is paid, redirect to thank you page (default WooCommerce behavior)
	if ( $order->is_paid() ) {
		// WooCommerce will handle redirect to order received page
		return;
	}
}

/**
 * Handle payment failure redirect
 * Redirect to custom payment failed page if payment fails
 * Also invalidate order and create new session
 * IMPORTANT: Do NOT modify successful DOKU redirects
 */
add_filter( 'woocommerce_payment_successful_result', 'wp_augoose_handle_payment_result_redirect', 10, 2 );
function wp_augoose_handle_payment_result_redirect( $result, $order_id ) {
	if ( ! $order_id ) {
		return $result;
	}
	
	$order = wc_get_order( $order_id );
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return $result;
	}
	
	// Check if this is DOKU payment
	$payment_method = $order->get_payment_method();
	$is_doku = (
		strpos( strtolower( $payment_method ), 'doku' ) !== false || 
		strpos( strtolower( $payment_method ), 'jokul' ) !== false
	);
	
	// If payment failed, invalidate order and redirect to new checkout
	if ( isset( $result['result'] ) && 'failure' === $result['result'] ) {
		// Invalidate the failed order
		wp_augoose_invalidate_failed_order( $order_id, $order, 'failed' );
		
		// Redirect to checkout with payment_failed parameter
		$checkout_url = wc_get_checkout_url();
		$result['redirect'] = add_query_arg( 'payment_failed', '1', $checkout_url );
	} elseif ( isset( $result['result'] ) && 'success' === $result['result'] && $is_doku ) {
		// For DOKU success, preserve the redirect URL from DOKU gateway
		// DOKU returns redirect URL to payment page (checkout.doku.com) - don't modify it
		// Just ensure redirect URL exists
		if ( empty( $result['redirect'] ) ) {
			// If no redirect URL, try to get from order meta (DOKU might store it there)
			$doku_redirect = $order->get_meta( '_doku_redirect_url' );
			if ( ! empty( $doku_redirect ) ) {
				$result['redirect'] = $doku_redirect;
			} else {
				// Last resort: use order received page as fallback
				$result['redirect'] = $order->get_checkout_order_received_url();
			}
		}
		// Otherwise, keep DOKU's redirect URL as is (don't modify it)
	}
	
	return $result;
}

/**
 * Ensure DOKU process_payment redirect works correctly
 * Hook before process_payment to ensure clean environment
 */
add_action( 'woocommerce_before_payment', 'wp_augoose_ensure_doku_redirect_works', 5 );
function wp_augoose_ensure_doku_redirect_works() {
	// Only for DOKU payments
	if ( ! isset( $_POST['payment_method'] ) ) {
		return;
	}
	
	$payment_method = sanitize_text_field( $_POST['payment_method'] );
	$is_doku = (
		strpos( strtolower( $payment_method ), 'doku' ) !== false || 
		strpos( strtolower( $payment_method ), 'jokul' ) !== false
	);
	
	if ( ! $is_doku ) {
		return;
	}
	
	// Ensure output buffers don't interfere with redirect
	// DOKU process_payment needs to be able to redirect
	// Don't clean buffers here - let DOKU handle its own redirect
}

/**
 * Handle DOKU payment callback/return
 * Process payment status from DOKU and redirect accordingly
 */
add_action( 'woocommerce_api_doku_payment_callback', 'wp_augoose_handle_doku_payment_callback' );
add_action( 'woocommerce_api_jokul_payment_callback', 'wp_augoose_handle_doku_payment_callback' );
function wp_augoose_handle_doku_payment_callback() {
	// Get order ID from request
	$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
	$order_key = isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '';
	
	if ( ! $order_id || ! $order_key ) {
		wp_redirect( wc_get_page_permalink( 'checkout' ) . 'payment-failed/' );
		exit;
	}
	
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_order_key() !== $order_key ) {
		wp_redirect( wc_get_page_permalink( 'checkout' ) . 'payment-failed/' );
		exit;
	}
	
	// Check payment status
	$order_status = $order->get_status();
	$payment_status = $order->get_meta( '_payment_status' );
	
	// Check if payment expired/failed/cancelled
	$failure_statuses = array( 'failed', 'cancelled', 'expired' );
	$is_expired = in_array( strtolower( $payment_status ), array( 'expired', 'expire', 'timeout' ), true );
	
	if ( $order->is_paid() && ! in_array( $order_status, $failure_statuses, true ) && ! $is_expired ) {
		// Payment successful - redirect to thank you page
		wp_redirect( $order->get_checkout_order_received_url() );
		exit;
	} else {
		// Payment failed/expired/cancelled - invalidate order and redirect to new checkout
		wp_augoose_invalidate_failed_order( $order_id, $order, $order_status );
		
		$checkout_url = wc_get_checkout_url();
		wp_redirect( add_query_arg( 'payment_failed', '1', $checkout_url ) );
		exit;
	}
}

add_filter( 'woocommerce_get_item_data', 'wp_augoose_format_cart_item_data', 10, 2 );

/**
 * Format cart item data: Simplify colon format
 */
function wp_augoose_format_cart_item_data( $item_data, $cart_item ) {
	if ( ! is_array( $item_data ) ) {
		return $item_data;
	}
	
	$filtered_data = array();
	
	foreach ( $item_data as $data ) {
		// Simplify format: remove colon from key, use single colon in display
		if ( isset( $data['key'] ) ) {
			// Remove colon from key if exists
			$data['key'] = str_replace( ':', '', trim( $data['key'] ) );
		}
		
		// Format display: "Key: Value" (single colon)
		if ( isset( $data['display'] ) ) {
			// If display already has colon, keep it; otherwise add one
			$display = trim( $data['display'] );
			if ( strpos( $display, ':' ) === false && isset( $data['key'] ) ) {
				$data['display'] = $data['key'] . ': ' . $display;
			}
		} elseif ( isset( $data['value'] ) ) {
			// If no display, create from key and value
			$key_label = isset( $data['key'] ) ? $data['key'] : '';
			$data['display'] = $key_label . ': ' . $data['value'];
		}
		
		$filtered_data[] = $data;
	}
	
	return $filtered_data;
}

/**
 * Customize quantity input for cart
 */
add_filter( 'woocommerce_quantity_input_args', 'wp_augoose_quantity_input_args', 10, 2 );
function wp_augoose_quantity_input_args( $args, $product ) {
	if ( is_cart() ) {
		$args['input_name'] = str_replace( 'qty', 'cart[' . $args['input_name'] . '][qty]', $args['input_name'] );
	}
	return $args;
}

/**
 * Get country codes for phone dropdown
 */
function wp_augoose_get_country_codes() {
	return array(
		''   => 'Select code',
		'+1' => '+1 (US/CA)',
		'+62' => '+62 (ID)',
		'+65' => '+65 (SG)',
		'+60' => '+60 (MY)',
		'+44' => '+44 (UK)',
		'+61' => '+61 (AU)',
		'+81' => '+81 (JP)',
		'+82' => '+82 (KR)',
		'+86' => '+86 (CN)',
		'+91' => '+91 (IN)',
		'+66' => '+66 (TH)',
		'+84' => '+84 (VN)',
		'+63' => '+63 (PH)',
		'+64' => '+64 (NZ)',
		'+33' => '+33 (FR)',
		'+49' => '+49 (DE)',
		'+39' => '+39 (IT)',
		'+34' => '+34 (ES)',
		'+31' => '+31 (NL)',
		'+32' => '+32 (BE)',
		'+41' => '+41 (CH)',
		'+46' => '+46 (SE)',
		'+47' => '+47 (NO)',
		'+45' => '+45 (DK)',
		'+358' => '+358 (FI)',
		'+351' => '+351 (PT)',
		'+353' => '+353 (IE)',
		'+48' => '+48 (PL)',
		'+420' => '+420 (CZ)',
		'+36' => '+36 (HU)',
		'+40' => '+40 (RO)',
		'+7' => '+7 (RU)',
		'+971' => '+971 (AE)',
		'+966' => '+966 (SA)',
		'+974' => '+974 (QA)',
		'+965' => '+965 (KW)',
		'+973' => '+973 (BH)',
		'+968' => '+968 (OM)',
		'+961' => '+961 (LB)',
		'+962' => '+962 (JO)',
		'+20' => '+20 (EG)',
		'+27' => '+27 (ZA)',
		'+234' => '+234 (NG)',
		'+254' => '+254 (KE)',
		'+52' => '+52 (MX)',
		'+55' => '+55 (BR)',
		'+54' => '+54 (AR)',
		'+56' => '+56 (CL)',
		'+57' => '+57 (CO)',
		'+51' => '+51 (PE)',
	);
}

/**
 * Save phone country code to order meta
 */
add_action( 'woocommerce_checkout_update_order_meta', 'wp_augoose_save_phone_country_code_to_order', 10, 1 );
function wp_augoose_save_phone_country_code_to_order( $order_id ) {
	if ( ! empty( $_POST['billing_phone_country_code'] ) ) {
		$country_code = sanitize_text_field( $_POST['billing_phone_country_code'] );
		update_post_meta( $order_id, '_billing_phone_country_code', $country_code );
		
		// Also update phone number to include country code
		if ( ! empty( $_POST['billing_phone'] ) ) {
			$phone = sanitize_text_field( $_POST['billing_phone'] );
			$full_phone = $country_code . $phone;
			update_post_meta( $order_id, '_billing_phone_full', $full_phone );
		}
	}
}

/**
 * Display phone country code in Admin Order Details
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'wp_augoose_display_phone_country_code_in_admin', 10, 1 );
function wp_augoose_display_phone_country_code_in_admin( $order ) {
	$country_code = get_post_meta( $order->get_id(), '_billing_phone_country_code', true );
	$full_phone = get_post_meta( $order->get_id(), '_billing_phone_full', true );
	if ( ! empty( $country_code ) ) {
		echo '<p><strong>Phone country code:</strong> ' . esc_html( $country_code ) . '</p>';
		if ( ! empty( $full_phone ) ) {
			echo '<p><strong>Full phone:</strong> ' . esc_html( $full_phone ) . '</p>';
		}
	}
}

/**
 * Remove/Hide Newsletter Checkbox
 */
add_filter( 'woocommerce_checkout_newsletter_subscription_text', '__return_empty_string', 999 );
add_filter( 'woocommerce_registration_newsletter_subscription_text', '__return_empty_string', 999 );
add_action( 'wp_footer', 'wp_augoose_hide_newsletter_checkbox' );
function wp_augoose_hide_newsletter_checkbox() {
	// CRITICAL: Skip during AJAX requests to prevent HTML output before JSON
	if ( augoose_is_wc_ajax_request() ) {
		return;
	}
	
	if ( is_checkout() ) {
		?>
		<style>
			/* Hide newsletter checkbox - all variations */
			.woocommerce-newsletter-subscription,
			.woocommerce-form__label-for-checkbox:has(input[name*="newsletter"]),
			.woocommerce-form__label-for-checkbox:has(input[id*="newsletter"]),
			label:has(input[name*="newsletter"]),
			label:has(input[id*="newsletter"]),
			input[name*="newsletter"],
			input[id*="newsletter"],
			/* Hide Hostinger newsletter checkbox */
			input[name="hostinger_reach_optin"],
			input[id="hostinger_reach_optin"],
			label:has(input[name="hostinger_reach_optin"]),
			label:has(input[id="hostinger_reach_optin"]),
			.hostinger-reach-optin__checkbox-text,
			label:has(.hostinger-reach-optin__checkbox-text),
			label:has(span:contains("Berlangganan")),
			label:has(span:contains("Buletin")),
			label:has(span:contains("berlangganan")),
			label:has(span:contains("buletin")) {
				display: none !important;
				visibility: hidden !important;
				opacity: 0 !important;
				height: 0 !important;
				overflow: hidden !important;
				margin: 0 !important;
				padding: 0 !important;
				line-height: 0 !important;
			}
		</style>
		<?php
	}
}

/**
 * Add Terms Checkbox (Required) - Replace Newsletter
 */
add_action( 'woocommerce_review_order_before_submit', 'wp_augoose_add_terms_checkbox', 10 );
function wp_augoose_add_terms_checkbox() {
	// Get Terms page URL - use same method as footer.php
	$page_url = static function ( $slug ) {
		$p = get_page_by_path( (string) $slug );
		if ( $p instanceof WP_Post ) {
			$url = get_permalink( $p );
			if ( $url ) {
				return $url;
			}
		}
		return '#';
	};
	
	$terms_url = $page_url( 'terms-of-service' );
	
	// Get Privacy Policy URL
	$privacy_page_id = get_option( 'wp_page_for_privacy_policy' );
	$privacy_url = $privacy_page_id ? get_permalink( $privacy_page_id ) : '#';
	
	?>
	<p class="form-row validate-required terms-checkbox-custom" id="terms_checkbox_field">
		<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
			<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms_custom" id="terms_custom" value="1" <?php checked( isset( $_POST['terms_custom'] ), true ); ?> />
			<span class="woocommerce-form__label-text">
				I have read and agree to the <a href="<?php echo esc_url( $terms_url ); ?>" target="_blank">Terms of Service</a> and <a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank">Privacy Policy</a>.
			</span>
			<span class="required">*</span>
		</label>
	</p>
	<?php
}

/**
 * Validate Terms Checkbox
 */
/**
 * CRITICAL: Ensure clean output buffer for ALL WooCommerce AJAX requests
 * This prevents "SyntaxError: Unexpected token '<'" errors
 * 
 * WooCommerce checkout uses AJAX and expects JSON responses.
 * Any HTML output before JSON will cause this error.
 * 
 * We hook early to clear output buffers before WooCommerce processes the request.
 * Following WooCommerce's pattern: clean output before any processing.
 */
/**
 * CRITICAL: Clean output buffer for WooCommerce AJAX requests ONLY
 * 
 * WooCommerce uses wc-ajax endpoint: ?wc-ajax=update_order_review
 * We must ONLY clean output for actual AJAX requests, not form submissions.
 * 
 * Following WooCommerce's pattern: only clean for wc-ajax requests.
 */
/**
 * CRITICAL: Aggressive output buffer cleaning for WooCommerce AJAX
 * This prevents ANY HTML output before JSON response
 * 
 * Hooked to multiple early hooks to catch output at different stages
 */
add_action( 'init', 'wp_augoose_clean_output_for_woocommerce_ajax', 1 );
add_action( 'wp_loaded', 'wp_augoose_clean_output_for_woocommerce_ajax', 1 );
add_action( 'template_redirect', 'wp_augoose_clean_output_for_woocommerce_ajax', 0 );
function wp_augoose_clean_output_for_woocommerce_ajax() {
	// CRITICAL: Exclude WordPress Customizer requests
	// Customizer uses customize_changeset_uuid parameter but is NOT an AJAX request
	if ( isset( $_REQUEST['customize_changeset_uuid'] ) || 
	     isset( $_GET['customize_changeset_uuid'] ) || 
	     isset( $_POST['customize_changeset_uuid'] ) ||
	     is_customize_preview() ) {
		return; // This is Customizer, not WooCommerce AJAX - don't interfere
	}
	
	// CRITICAL: Do NOT clean output during checkout place order
	// This prevents DOKU redirect from being broken
	if ( isset( $_POST['woocommerce_checkout_place_order'] ) || 
	     isset( $_POST['place_order'] ) ) {
		return; // This is checkout place order - don't clean output, let redirect work
	}
	
	// CRITICAL: Only for actual AJAX requests (wp_doing_ajax OR wc-ajax endpoint)
	// Do NOT interfere with regular form submissions or Customizer
	$is_ajax = wp_doing_ajax();
	$is_wc_ajax_endpoint = isset( $_REQUEST['wc-ajax'] ) || isset( $_GET['wc-ajax'] ) || isset( $_POST['wc-ajax'] );
	
	// Only proceed if this is a real AJAX request
	if ( ! $is_ajax && ! $is_wc_ajax_endpoint ) {
		return;
	}
	
	// CRITICAL: Clear ALL output buffers aggressively
	// This prevents HTML/warnings/notices from corrupting JSON response
	while ( ob_get_level() ) {
		ob_end_clean();
	}
	
	// Start fresh output buffer to catch any unexpected output
	ob_start();
	
	// For wc-ajax endpoint, WooCommerce handles it via template_redirect
	if ( $is_wc_ajax_endpoint ) {
		// Register shutdown function to clean buffer before WooCommerce sends JSON
		add_action( 'shutdown', 'wp_augoose_final_clean_output_for_wc_ajax', 9999 );
		return;
	}
	
	// For admin-ajax.php requests, check if it's WooCommerce-related
	if ( $is_ajax && isset( $_REQUEST['action'] ) ) {
		$action = sanitize_text_field( $_REQUEST['action'] );
		// Only clean for WooCommerce AJAX actions
		if ( strpos( $action, 'woocommerce_' ) === 0 || 
		     $action === 'update_checkout_quantity' ||
		     $action === 'update_order_review' ) {
			// Register shutdown function to clean buffer before response
			add_action( 'shutdown', 'wp_augoose_final_clean_output_for_wc_ajax', 9999 );
		}
	}
}

/**
 * Final output buffer cleaning before WooCommerce sends JSON
 * This runs at shutdown (very late) to catch any last-minute output
 */
function wp_augoose_final_clean_output_for_wc_ajax() {
	// CRITICAL: Exclude WordPress Customizer requests
	if ( isset( $_REQUEST['customize_changeset_uuid'] ) || 
	     isset( $_GET['customize_changeset_uuid'] ) || 
	     isset( $_POST['customize_changeset_uuid'] ) ||
	     is_customize_preview() ) {
		return; // This is Customizer, not WooCommerce AJAX
	}
	
	// CRITICAL: Do NOT clean output during checkout place order
	// This prevents DOKU redirect from being broken
	if ( isset( $_POST['woocommerce_checkout_place_order'] ) || 
	     isset( $_POST['place_order'] ) ||
	     ( isset( $_POST['payment_method'] ) && 
	       ( strpos( strtolower( $_POST['payment_method'] ), 'doku' ) !== false || 
	         strpos( strtolower( $_POST['payment_method'] ), 'jokul' ) !== false ) ) ) {
		return; // This is checkout place order with DOKU - don't clean output, let redirect work
	}
	
	// Only for WooCommerce AJAX requests
	$is_wc_ajax_endpoint = isset( $_REQUEST['wc-ajax'] ) || isset( $_GET['wc-ajax'] ) || isset( $_POST['wc-ajax'] );
	$is_ajax = wp_doing_ajax();
	
	if ( ! $is_ajax && ! $is_wc_ajax_endpoint ) {
		return;
	}
	
	// Get any output that might have been generated
	$output = ob_get_clean();
	
	// If output contains HTML (starts with <), discard it
	// This prevents HTML from corrupting JSON response
	if ( ! empty( $output ) && ( strpos( trim( $output ), '<' ) === 0 || strpos( trim( $output ), '<!' ) === 0 ) ) {
		// Output contains HTML - discard it completely
		// Clear all remaining buffers
		while ( ob_get_level() ) {
			ob_end_clean();
		}
		// Don't output anything - let WooCommerce send clean JSON
		return;
	}
	
	// If output is just whitespace or empty, clean it
	if ( empty( trim( $output ) ) ) {
		while ( ob_get_level() ) {
			ob_end_clean();
		}
		return;
	}
	
	// If output looks like JSON, keep it (might be from wp_send_json)
	if ( strpos( trim( $output ), '{' ) === 0 || strpos( trim( $output ), '[' ) === 0 ) {
		// Looks like JSON - let it through
		return;
	}
	
	// Any other output - discard it to be safe
	while ( ob_get_level() ) {
		ob_end_clean();
	}
}

/**
 * CRITICAL: Fix DOKU Payment plugin array access errors during checkout
 * DOKU plugin has known issues accessing array offsets without validation
 * This prevents "Trying to access array offset on false" errors from breaking checkout
 */
/**
 * Remove payment method from woocommerce_checkout_order_review hook
 * We render payment method in custom section .checkout-payment-section instead
 * This prevents payment method from appearing twice
 * WooCommerce default: add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
 */
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );

/**
 * Translate Shipping Labels from Indonesian to English
 * Fixes "PENGIRIMAN" and "PENGIRIMAN GRATIS" labels in checkout
 */
add_filter( 'woocommerce_cart_totals_shipping_html', 'wp_augoose_translate_shipping_labels', 10, 1 );
add_filter( 'woocommerce_shipping_method_label', 'wp_augoose_translate_shipping_method_label', 10, 2 );
add_filter( 'woocommerce_cart_shipping_method_full_label', 'wp_augoose_translate_shipping_method_label', 10, 2 );
add_filter( 'woocommerce_cart_shipping_method_label', 'wp_augoose_translate_shipping_method_label', 10, 2 );
add_filter( 'woocommerce_shipping_package_name', 'wp_augoose_translate_shipping_method_label', 10, 2 );
function wp_augoose_translate_shipping_labels( $html ) {
	if ( empty( $html ) ) {
		return $html;
	}
	
	// Translate common Indonesian shipping labels to English
	$translations = array(
		'PENGIRIMAN' => 'SHIPPING',
		'Pengiriman' => 'Shipping',
		'pengiriman' => 'shipping',
		'PENGIRIMAN GRATIS' => 'FREE SHIPPING',
		'Pengiriman Gratis' => 'Free Shipping',
		'pengiriman gratis' => 'free shipping',
		'GRATIS' => 'FREE',
		'Gratis' => 'Free',
		'gratis' => 'free',
		'SHIPPING GRATIS' => 'FREE SHIPPING',
		'Shipping Gratis' => 'Free Shipping',
		'shipping gratis' => 'free shipping',
		'Tarif tetap' => 'Fixed rate',
		'TARIF TETAP' => 'FIXED RATE',
		'tarif tetap' => 'fixed rate',
		'Tarif Tetap' => 'Fixed Rate',
	);
	
	foreach ( $translations as $indonesian => $english ) {
		$html = str_replace( $indonesian, $english, $html );
	}
	
	return $html;
}

function wp_augoose_translate_shipping_method_label( $label, $method = null ) {
	if ( empty( $label ) || ! is_string( $label ) ) {
		return $label;
	}
	
	// Translate common Indonesian shipping labels to English
	$translations = array(
		'PENGIRIMAN' => 'SHIPPING',
		'Pengiriman' => 'Shipping',
		'pengiriman' => 'shipping',
		'PENGIRIMAN GRATIS' => 'FREE SHIPPING',
		'Pengiriman Gratis' => 'Free Shipping',
		'pengiriman gratis' => 'free shipping',
		'GRATIS' => 'FREE',
		'Gratis' => 'Free',
		'gratis' => 'free',
		'SHIPPING GRATIS' => 'FREE SHIPPING',
		'Shipping Gratis' => 'Free Shipping',
		'shipping gratis' => 'free shipping',
		'Tarif tetap' => 'Fixed rate',
		'TARIF TETAP' => 'FIXED RATE',
		'tarif tetap' => 'fixed rate',
		'Tarif Tetap' => 'Fixed Rate',
	);
	
	foreach ( $translations as $indonesian => $english ) {
		$label = str_replace( $indonesian, $english, $label );
	}
	
	return $label;
}

/**
 * Translate shipping message to English
 */
add_filter( 'woocommerce_shipping_may_be_available_html', 'wp_augoose_translate_shipping_message', 20 );
function wp_augoose_translate_shipping_message( $message ) {
	// Translate Indonesian shipping message to English
	$indonesian_messages = array(
		'Masukkan alamat Anda untuk melihat opsi pengiriman' => 'Enter your address to view shipping options.',
		'Masukkan alamat Anda untuk melihat opsi' => 'Enter your address to view shipping options.',
		'masukkan alamat anda untuk melihat opsi pengiriman' => 'Enter your address to view shipping options.',
		'masukkan alamat anda untuk melihat opsi' => 'Enter your address to view shipping options.',
	);
	
	foreach ( $indonesian_messages as $indonesian => $english ) {
		if ( strpos( $message, $indonesian ) !== false ) {
			$message = str_replace( $indonesian, $english, $message );
		}
	}
	
	// Also check if message contains Indonesian text
	if ( stripos( $message, 'masukkan' ) !== false || stripos( $message, 'alamat' ) !== false || stripos( $message, 'opsi pengiriman' ) !== false ) {
		$message = 'Enter your address to view shipping options.';
	}
	
	return $message;
}

/**
 * Translate checkout required field notice
 * This filter catches the error message before it's displayed
 * CRITICAL: This runs with high priority (1) to catch messages before other filters
 */
add_filter( 'woocommerce_checkout_required_field_notice', 'wp_augoose_translate_required_field_notice', 1, 3 );
function wp_augoose_translate_required_field_notice( $notice, $field_label, $field_key ) {
	if ( empty( $notice ) ) {
		return $notice;
	}
	
	// Remove HTML tags for processing
	$clean_notice = wp_strip_all_tags( $notice );
	$clean_notice = trim( $clean_notice );
	
	// Check if notice contains Indonesian text (both with and without HTML)
	if ( stripos( $clean_notice, 'Penagihan' ) !== false || 
	     stripos( $clean_notice, 'Pengiriman' ) !== false || 
	     stripos( $clean_notice, 'harus diisi' ) !== false ||
	     stripos( $notice, 'Penagihan' ) !== false || 
	     stripos( $notice, 'Pengiriman' ) !== false || 
	     stripos( $notice, 'harus diisi' ) !== false ) {
		
		// Determine if it's billing or shipping
		$type = 'Billing';
		if ( stripos( $field_key, 'shipping' ) !== false || stripos( $clean_notice, 'Pengiriman' ) !== false ) {
			$type = 'Shipping';
		} elseif ( stripos( $clean_notice, 'Penagihan' ) !== false ) {
			$type = 'Billing';
		}
		
		// Extract field name from notice
		$field_name = $field_label;
		if ( preg_match( '/(Penagihan|Pengiriman|penagihan|pengiriman)\s+(.+?)\s+harus\s+diisi/i', $clean_notice, $matches ) ) {
			$field_name = trim( $matches[2] );
		}
		
		// Reconstruct notice with HTML if original had it
		if ( strpos( $notice, '<strong>' ) !== false ) {
			return sprintf( '<strong>%s %s</strong> is required.', $type, $field_name );
		} else {
			return sprintf( '%s %s is required.', $type, $field_name );
		}
	}
	
	return $notice;
}

/**
 * CRITICAL: Fix DOKU Payment plugin errors BEFORE checkout AJAX
 * This must run early to prevent DOKU plugin from outputting HTML/warnings
 */
add_action( 'init', 'wp_augoose_fix_doku_plugin_errors', 1 );
function wp_augoose_fix_doku_plugin_errors() {
	// Only if DOKU plugin is active
	if ( ! class_exists( 'WC_Gateway_Doku' ) && ! function_exists( 'doku_payment_init' ) ) {
		return;
	}
	
	// CRITICAL: Ensure $_SERVER['QUERY_STRING'] exists to prevent DOKU plugin errors
	// DOKU plugin accesses $_SERVER['QUERY_STRING'] without checking if it exists
	if ( ! isset( $_SERVER['QUERY_STRING'] ) ) {
		$_SERVER['QUERY_STRING'] = '';
	}
	
	// Ensure $_SERVER['QUERY_STRING'] is a string (not array or null)
	if ( ! is_string( $_SERVER['QUERY_STRING'] ) ) {
		$_SERVER['QUERY_STRING'] = '';
	}
}

add_action( 'woocommerce_checkout_process', 'wp_augoose_fix_doku_checkout_errors', 1 );
function wp_augoose_fix_doku_checkout_errors() {
	// Only if DOKU plugin is active
	if ( ! class_exists( 'WC_Gateway_Doku' ) && ! function_exists( 'doku_payment_init' ) ) {
		return;
	}
	
	// Ensure DOKU payment method data is properly set
	// DOKU plugin sometimes tries to access $_POST data that doesn't exist
	if ( isset( $_POST['payment_method'] ) && strpos( $_POST['payment_method'], 'doku' ) !== false ) {
		// Ensure required DOKU fields exist to prevent array access errors
		$required_fields = array( 'doku_payment_method', 'doku_channel', 'doku_amount' );
		foreach ( $required_fields as $field ) {
			if ( ! isset( $_POST[ $field ] ) ) {
				$_POST[ $field ] = '';
			}
		}
	}
}

/**
 * Validate Terms Checkbox
 */
add_action( 'woocommerce_checkout_process', 'wp_augoose_validate_terms_checkbox' );
function wp_augoose_validate_terms_checkbox() {
	if ( empty( $_POST['terms_custom'] ) ) {
		wc_add_notice( 'Please confirm that you have read and agree to the Terms of Service and Privacy Policy.', 'error' );
	}
}

/**
 * Validate phone country code
 */
add_action( 'woocommerce_checkout_process', 'wp_augoose_validate_phone_country_code' );
function wp_augoose_validate_phone_country_code() {
	if ( empty( $_POST['billing_phone_country_code'] ) ) {
		wc_add_notice( 'Please select a country code for your phone number.', 'error' );
	}
}

/**
 * Enqueue cart scripts
 */
add_action( 'wp_enqueue_scripts', 'wp_augoose_cart_scripts' );
function wp_augoose_cart_scripts() {
	if ( is_cart() ) {
		wp_add_inline_script( 'jquery', '
			jQuery(document).ready(function($) {
				// Custom quantity buttons
				$(".cart .quantity").each(function() {
					var $quantity = $(this);
					var $input = $quantity.find("input[type=number]");
					
					if ($input.length && !$quantity.find(".qty-button").length) {
						var $minus = $("<button>").addClass("qty-button minus").text("-").attr("type", "button");
						var $plus = $("<button>").addClass("qty-button plus").text("+").attr("type", "button");
						
						$input.before($minus);
						$input.after($plus);
						
						$minus.on("click", function(e) {
							e.preventDefault();
							var currentVal = parseInt($input.val()) || 0;
							var min = parseInt($input.attr("min")) || 0;
							if (currentVal > min) {
								$input.val(currentVal - 1).trigger("change");
							}
						});
						
						$plus.on("click", function(e) {
							e.preventDefault();
							var currentVal = parseInt($input.val()) || 0;
							var max = parseInt($input.attr("max")) || 9999;
							if (currentVal < max) {
								$input.val(currentVal + 1).trigger("change");
							}
						});
					}
				});
				
				// Auto update cart on quantity change (optimized - AJAX instead of form submit)
				var cartUpdateTimeout;
				var isCartUpdating = false;
				
				$(".cart .quantity input[type=number]").on("change", function() {
					var $input = $(this);
					var $form = $input.closest("form.woocommerce-cart-form");
					
					if (!$form.length || isCartUpdating) {
						return;
					}
					
					// Get cart item key from input name
					var inputName = $input.attr("name");
					var cartKey = inputName ? inputName.replace("cart[", "").replace("][qty]", "") : "";
					var quantity = parseInt($input.val()) || 1;
					
					if (!cartKey) {
						return;
					}
					
					// Clear previous timeout
					clearTimeout(cartUpdateTimeout);
					
					// Show loading state
					$input.closest("tr, .cart_item").addClass("updating");
					
					// Faster debounce (100ms instead of 500ms)
					cartUpdateTimeout = setTimeout(function() {
						if (isCartUpdating) return;
						
						isCartUpdating = true;
						
						$.ajax({
							url: wc_add_to_cart_params.ajax_url || "/wp-admin/admin-ajax.php",
							type: "POST",
							timeout: 10000,
							data: {
								action: "update_checkout_quantity",
								cart_key: cartKey,
								quantity: quantity,
								security: wc_add_to_cart_params.update_cart_nonce || ""
							},
							success: function(response) {
								if (response && response.success) {
									// Update cart fragments (faster than form submit)
									$(document.body).trigger("wc_fragment_refresh");
									$(document.body).trigger("updated_wc_div");
								} else {
									// Fallback to form submit on error
									$form.find("button[name=update_cart]").trigger("click");
								}
							},
							error: function() {
								// Fallback to form submit on error
								$form.find("button[name=update_cart]").trigger("click");
							},
							complete: function() {
								isCartUpdating = false;
								$(".updating").removeClass("updating");
							}
						});
					}, 100);
				});
			});
		' );
	}
}

/**
 * AJAX Add to Cart Handler
 */
add_action( 'wp_ajax_wp_augoose_add_to_cart', 'wp_augoose_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_wp_augoose_add_to_cart', 'wp_augoose_ajax_add_to_cart' );
function wp_augoose_ajax_add_to_cart() {
	// Security: Verify nonce and sanitize input
	if ( ! function_exists( 'wp_augoose_verify_ajax_nonce' ) ) {
		require_once get_template_directory() . '/inc/security.php';
	}
	wp_augoose_verify_ajax_nonce( 'wp_augoose_nonce', 'nonce' );
	
	if ( function_exists( 'wc_clear_notices' ) ) {
		wc_clear_notices();
	}

	$product_id = wp_augoose_sanitize_product_id( $_POST['product_id'] ?? 0 );
	$quantity   = wp_augoose_sanitize_quantity( $_POST['quantity'] ?? 1, 1, 999 );

	if ( ! $product_id ) {
		wp_augoose_send_json_clean( array( 'message' => 'Invalid product ID.' ), false );
	}

	// Check if product exists
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		wp_augoose_send_json_clean( array( 'message' => 'Product not found.' ), false );
	}

	// Check if product is purchasable
	if ( ! $product->is_purchasable() ) {
		wp_augoose_send_json_clean( array( 'message' => 'Product is not purchasable.' ), false );
	}

	// Check if product is in stock
	if ( ! $product->is_in_stock() ) {
		wp_augoose_send_json_clean( array( 'message' => 'Product is out of stock.' ), false );
	}

	// Variable products need options (variation id)
	if ( $product->is_type( 'variable' ) ) {
		wp_augoose_send_json_clean(
			array(
				'message'     => 'Please choose product options by visiting the product page.',
				'product_url' => get_permalink( $product_id ),
			),
			false
		);
	}

	// Add to cart with error handling
	try {
		$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity );
	} catch ( \Exception $e ) {
		// Log error but don't break the site
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Add to cart error: ' . $e->getMessage() );
		}
		wp_augoose_send_json_clean( array( 'message' => 'Error adding product to cart. Please try again.' ), false );
	}

	if ( $cart_item_key ) {
		// Get cart count
		$cart_count = WC()->cart->get_cart_contents_count();

		// Get cart fragments
		ob_start();
		woocommerce_mini_cart();
		$mini_cart = ob_get_clean();

		if ( function_exists( 'wc_clear_notices' ) ) {
			wc_clear_notices();
		}

		$data = array(
			'message'    => sprintf( '%s has been added to your cart.', $product->get_name() ),
			'cart_count' => $cart_count,
			'cart_key'   => $cart_item_key,
			'cart_hash'  => WC()->cart->get_cart_hash(),
		);

		wp_augoose_send_json_clean( $data, true );
	} else {
		if ( function_exists( 'wc_clear_notices' ) ) {
			wc_clear_notices();
		}
		wp_augoose_send_json_clean( array( 'message' => 'Failed to add product to cart.' ), false );
	}
}

/**
 * AJAX handler for updating cart quantity on checkout
 */
add_action( 'wp_ajax_update_checkout_quantity', 'wp_augoose_update_checkout_quantity' );
add_action( 'wp_ajax_nopriv_update_checkout_quantity', 'wp_augoose_update_checkout_quantity' );
function wp_augoose_update_checkout_quantity() {
	// CRITICAL: Clear output buffer FIRST before any processing
	// This prevents "SyntaxError: Unexpected token '<'" errors
	// Follow WooCommerce pattern: clean output before JSON response
	while ( ob_get_level() ) {
		ob_end_clean();
	}
	
	// CRITICAL: Verify nonce with our custom action name
	// Don't use WooCommerce core nonce to avoid conflicts
	check_ajax_referer( 'wp_augoose_update_qty', 'security' );
	
	// Security: Sanitize input
	if ( ! function_exists( 'wp_augoose_sanitize_cart_key' ) ) {
		require_once get_template_directory() . '/inc/security.php';
	}
	
	$cart_key = wp_augoose_sanitize_cart_key( $_POST['cart_key'] ?? '' );
	$quantity = wp_augoose_sanitize_quantity( $_POST['quantity'] ?? 0, 0, 999 );
	
	if ( ! $cart_key ) {
		wp_augoose_send_json_clean( array( 'message' => 'Invalid cart item.' ), false );
	}
	
	// Validate cart item exists
	$cart_item = WC()->cart->get_cart_item( $cart_key );
	if ( ! $cart_item ) {
		wp_augoose_send_json_clean( array( 'message' => 'Cart item not found.' ), false );
	}
	
	// Update cart (optimized - calculate totals only once)
	if ( $quantity === 0 ) {
		// Remove item
		WC()->cart->remove_cart_item( $cart_key );
	} else {
		// Update quantity (set refresh_totals to false, we'll calculate manually)
		WC()->cart->set_quantity( $cart_key, $quantity, false );
	}
	
	// Calculate totals once (faster than multiple calculations)
	WC()->cart->calculate_totals();
	
	// CRITICAL: Clear output buffer before generating fragments
	// This prevents any HTML output before JSON response
	while ( ob_get_level() ) {
		ob_end_clean();
	}
	
	// Get order review fragment to preserve product images
	ob_start();
	woocommerce_order_review();
	$order_review_html = ob_get_clean();
	
	// Get checkout payment fragment
	ob_start();
	woocommerce_checkout_payment();
	$payment_html = ob_get_clean();
	
	// Get messages if any (must be after fragments to avoid output issues)
	$messages = '';
	if ( function_exists( 'wc_print_notices' ) ) {
		ob_start();
		wc_print_notices();
		$messages = ob_get_clean();
	}
	
	// Return success with cart data and fragments for immediate UI update
	// CRITICAL: Follow WooCommerce's EXACT response format to prevent checkout.min.js errors
	// WooCommerce update_order_review expects: result (string), messages (string), reload (boolean), fragments (object)
	// Reference: woocommerce/includes/class-wc-ajax.php line 459-472
	
	// Prepare fragments - ensure they are strings
	$fragments_array = array(
		'.woocommerce-checkout-review-order-table' => is_string( $order_review_html ) ? $order_review_html : '',
		'.woocommerce-checkout-payment' => is_string( $payment_html ) ? $payment_html : '',
	);
	
	// Apply filter (same as WooCommerce does)
	$fragments = apply_filters( 'woocommerce_update_order_review_fragments', $fragments_array );
	
	// Ensure fragments is always an array with string values
	if ( ! is_array( $fragments ) ) {
		$fragments = array();
	}
	foreach ( $fragments as $key => $fragment ) {
		if ( ! is_string( $fragment ) ) {
			$fragments[ $key ] = '';
		}
	}
	
	// Build response - Support BOTH formats:
	// 1. WooCommerce update_order_review format (for checkout.min.js)
	// 2. wp_send_json_success format (for our custom JS)
	
	// CRITICAL: Ensure all values are proper types and never null/undefined
	// This prevents "Cannot read properties of undefined (reading 'toString')" errors
	$result = empty( $messages ) ? 'success' : 'failure';
	$result = is_string( $result ) ? $result : 'success';
	$messages = is_string( $messages ) ? $messages : '';
	$reload = false;
	$fragments = is_array( $fragments ) ? $fragments : array();
	
	// Get cart hash for checkout.min.js compatibility
	$cart_hash = '';
	if ( function_exists( 'WC' ) && WC() && WC()->cart ) {
		$cart_hash = WC()->cart->get_cart_hash();
	}
	
	// Build dual-format response
	// Format 1: WooCommerce update_order_review format (for checkout.min.js)
	$wc_response = array(
		'result'    => $result,
		'messages'  => $messages,
		'reload'    => $reload,
		'fragments' => $fragments,
	);
	
	// Format 2: wp_send_json_success format (for our custom JS)
	$custom_response = array(
		'success' => ( $result === 'success' ),
		'data'    => array(
			'fragments'  => $fragments,
			'cart_hash'  => $cart_hash,
			'message'    => $messages,
		),
	);
	
	// Merge both formats - our JS checks response.success, checkout.min.js checks result
	$response = array_merge( $wc_response, $custom_response );
	
	// Send JSON response - use wp_send_json directly (same as WooCommerce)
	// This ensures 100% compatibility with both checkout.min.js and our custom JS
	// Clear output buffer first to prevent any HTML before JSON
	while ( ob_get_level() ) {
		ob_end_clean();
	}
	
	// Set proper headers
	if ( ! headers_sent() ) {
		header( 'Content-Type: application/json; charset=utf-8' );
	}
	
	// Send JSON response exactly as WooCommerce does
	wp_send_json( $response );
	exit; // Ensure no output after JSON
}

/**
 * Ensure product images are preserved in checkout fragments when country changes
 * This prevents images from disappearing when country is updated
 */
add_filter( 'woocommerce_update_order_review_fragments', 'wp_augoose_preserve_checkout_product_images', 10, 1 );
function wp_augoose_preserve_checkout_product_images( $fragments ) {
	// CRITICAL: Ensure fragments is always an array
	if ( ! is_array( $fragments ) ) {
		$fragments = array();
	}
	
	// Ensure order review fragment includes product images
	if ( ! isset( $fragments['.woocommerce-checkout-review-order-table'] ) ) {
		// Clear any existing output buffer before starting
		while ( ob_get_level() ) {
			ob_end_clean();
		}
		ob_start();
		woocommerce_order_review();
		$fragments['.woocommerce-checkout-review-order-table'] = ob_get_clean();
	}
	
	return $fragments;
}

// Checkout field layout is controlled by the theme templates + `functions.php`.
// Do NOT add CSS/JS here that hides fields; it breaks WooCommerce + WPML checkout flows.

/**
 * Disable My Account pages if no login system
 * Hide all menu items and redirect to shop/home
 */
add_filter( 'woocommerce_account_menu_items', 'wp_augoose_disable_account_menu_if_no_login', 5, 2 );
function wp_augoose_disable_account_menu_if_no_login( $items, $endpoints ) {
	// If user is not logged in, hide all menu items except logout (which won't show anyway)
	if ( ! is_user_logged_in() ) {
		return array(); // Return empty array to hide all menu items
	}
	
	return $items;
}

/**
 * Redirect My Account pages to shop if user is not logged in
 */
add_action( 'template_redirect', 'wp_augoose_redirect_account_if_no_login', 5 );
function wp_augoose_redirect_account_if_no_login() {
	// Only redirect on My Account pages
	if ( ! is_account_page() ) {
		return;
	}
	
	// If user is not logged in, redirect to shop
	if ( ! is_user_logged_in() ) {
		$shop_url = wc_get_page_permalink( 'shop' );
		if ( ! $shop_url ) {
			$shop_url = home_url();
		}
		wp_safe_redirect( $shop_url );
		exit;
	}
}

/**
 * Hide My Account navigation if user is not logged in
 */
add_action( 'woocommerce_before_account_navigation', 'wp_augoose_hide_account_navigation_if_no_login', 5 );
function wp_augoose_hide_account_navigation_if_no_login() {
	if ( ! is_user_logged_in() ) {
		// Remove navigation output
		remove_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation', 10 );
		// Hide navigation with CSS as backup
		echo '<style>.woocommerce-MyAccount-navigation { display: none !important; }</style>';
	}
}

/**
 * Translate My Account menu items to English
 */
add_filter( 'woocommerce_account_menu_items', 'wp_augoose_translate_account_menu_items', 20, 2 );
function wp_augoose_translate_account_menu_items( $items, $endpoints ) {
	// If no items (disabled), return early
	if ( empty( $items ) ) {
		return $items;
	}
	
	$translations = array(
		'Dasbor' => 'Dashboard',
		'dasbor' => 'Dashboard',
		'Pesanan' => 'Orders',
		'pesanan' => 'Orders',
		'Unduhan' => 'Downloads',
		'unduhan' => 'Downloads',
		'Alamat' => 'Address',
		'alamat' => 'Address',
		'Metode Pembayaran' => 'Payment methods',
		'metode pembayaran' => 'Payment methods',
		'Detail Akun' => 'Account details',
		'detail akun' => 'Account details',
		'Keluar' => 'Log out',
		'keluar' => 'Log out',
	);
	
	foreach ( $items as $key => $label ) {
		if ( isset( $translations[ $label ] ) ) {
			$items[ $key ] = $translations[ $label ];
		} else {
			// Also check case-insensitive
			$label_lower = strtolower( $label );
			foreach ( $translations as $id => $en ) {
				if ( strtolower( $id ) === $label_lower ) {
					$items[ $key ] = $en;
					break;
				}
			}
		}
	}
	
	return $items;
}

/**
 * Translate My Account Orders columns to English
 */
add_filter( 'woocommerce_account_orders_columns', 'wp_augoose_translate_orders_columns', 20 );
function wp_augoose_translate_orders_columns( $columns ) {
	$translations = array(
		'Pesanan' => 'Order',
		'pesanan' => 'Order',
		'Tanggal' => 'Date',
		'tanggal' => 'Date',
		'Status' => 'Status',
		'status' => 'Status',
		'Total' => 'Total',
		'total' => 'Total',
		'Aksi' => 'Actions',
		'aksi' => 'Actions',
	);
	
	foreach ( $columns as $key => $label ) {
		if ( isset( $translations[ $label ] ) ) {
			$columns[ $key ] = $translations[ $label ];
		} else {
			// Case-insensitive check
			$label_lower = strtolower( $label );
			foreach ( $translations as $id => $en ) {
				if ( strtolower( $id ) === $label_lower ) {
					$columns[ $key ] = $en;
					break;
				}
			}
		}
	}
	
	return $columns;
}

/**
 * Translate order status names to English
 */
add_filter( 'woocommerce_get_order_status_name', 'wp_augoose_translate_order_status', 20, 2 );
function wp_augoose_translate_order_status( $status_name, $status ) {
	$translations = array(
		'Dibatalkan' => 'Cancelled',
		'dibatalkan' => 'Cancelled',
		'Dibatalkan untuk' => 'Cancelled for',
		'dibatalkan untuk' => 'cancelled for',
		'Diproses' => 'Processing',
		'diproses' => 'Processing',
		'Selesai' => 'Completed',
		'selesai' => 'Completed',
		'Menunggu pembayaran' => 'Pending payment',
		'menunggu pembayaran' => 'pending payment',
		'Pembayaran ditahan' => 'On hold',
		'pembayaran ditahan' => 'on hold',
		'Gagal' => 'Failed',
		'gagal' => 'Failed',
		'Refunded' => 'Refunded',
		'refunded' => 'Refunded',
	);
	
	// Check for partial matches (e.g., "Dibatalkan untuk 1 item")
	foreach ( $translations as $id => $en ) {
		if ( stripos( $status_name, $id ) !== false ) {
			$status_name = str_ireplace( $id, $en, $status_name );
		}
	}
	
	return $status_name;
}

/**
 * Translate order action buttons to English
 */
add_filter( 'woocommerce_my_account_my_orders_actions', 'wp_augoose_translate_order_actions', 20, 2 );
function wp_augoose_translate_order_actions( $actions, $order ) {
	$translations = array(
		'LIHAT' => 'View',
		'Lihat' => 'View',
		'lihat' => 'View',
		'Bayar' => 'Pay',
		'bayar' => 'Pay',
		'Batal' => 'Cancel',
		'batal' => 'Cancel',
	);
	
	foreach ( $actions as $key => $action ) {
		if ( isset( $action['name'] ) ) {
			$name = $action['name'];
			foreach ( $translations as $id => $en ) {
				if ( stripos( $name, $id ) !== false ) {
					$actions[ $key ]['name'] = str_ireplace( $id, $en, $name );
					break;
				}
			}
		}
	}
	
	return $actions;
}

/**
 * Translate all WooCommerce notices including error messages
 * This runs with priority 5 as a backup to catch any messages that might have been missed
 */
add_filter( 'woocommerce_add_error', 'wp_augoose_translate_all_notices', 5, 1 );
add_filter( 'woocommerce_add_success', 'wp_augoose_translate_all_notices', 5, 1 );
add_filter( 'woocommerce_add_info', 'wp_augoose_translate_all_notices', 5, 1 );
add_filter( 'woocommerce_add_notice', 'wp_augoose_translate_all_notices', 5, 1 );
add_filter( 'woocommerce_add_message', 'wp_augoose_translate_all_notices', 5, 1 );
function wp_augoose_translate_all_notices( $message ) {
	if ( empty( $message ) ) {
		return $message;
	}
	
	// Remove HTML tags for processing
	$clean_message = wp_strip_all_tags( $message );
	$clean_message = trim( $clean_message );
	
	// Pattern matching for "Penagihan/Pengiriman ... harus diisi"
	if ( preg_match( '/(Penagihan|Pengiriman|penagihan|pengiriman)\s+(.+?)\s+harus\s+diisi/i', $clean_message, $matches ) ) {
		$type = trim( $matches[1] );
		$field = trim( $matches[2] );
		
		// Convert type to English
		if ( stripos( $type, 'Penagihan' ) !== false || stripos( $type, 'penagihan' ) !== false ) {
			$type = 'Billing';
		} elseif ( stripos( $type, 'Pengiriman' ) !== false || stripos( $type, 'pengiriman' ) !== false ) {
			$type = 'Shipping';
		}
		
		// Reconstruct message with HTML if original had it
		if ( strpos( $message, '<strong>' ) !== false ) {
			return sprintf( '<strong>%s %s</strong> is required.', $type, $field );
		} else {
			return sprintf( '%s %s is required.', $type, $field );
		}
	}
	
	// Additional check: direct string replacement for any remaining Indonesian text
	if ( stripos( $message, 'Penagihan' ) !== false || 
	     stripos( $message, 'Pengiriman' ) !== false || 
	     stripos( $message, 'harus diisi' ) !== false ) {
		$message = str_ireplace( 'Penagihan', 'Billing', $message );
		$message = str_ireplace( 'Pengiriman', 'Shipping', $message );
		$message = str_ireplace( 'harus diisi', 'is required', $message );
		$message = str_ireplace( 'Harus diisi', 'Is required', $message );
		$message = str_ireplace( 'HARUS DIISI', 'IS REQUIRED', $message );
		return $message;
	}
	
	// Additional translations for My Account and order processing
	$additional_translations = array(
		// Order processing errors
		'Terjadi error saat memproses pesanan Anda.' => 'An error occurred while processing your order.',
		'Terjadi error saat memproses pesanan Anda' => 'An error occurred while processing your order',
		'terjadi error saat memproses pesanan Anda' => 'an error occurred while processing your order',
		'Periksa apakah ada perubahan dalam metode pembayaran Anda dan tinjau riwayat pemesanan sebelum membuat pesanan lagi.' => 'Please check if there are any changes in your payment method and review your order history before placing another order.',
		'periksa apakah ada perubahan dalam metode pembayaran Anda' => 'please check if there are any changes in your payment method',
		'tinjau riwayat pemesanan' => 'review your order history',
		'sebelum membuat pesanan lagi' => 'before placing another order',
		'riwayat pemesanan' => 'order history',
		'Riwayat pemesanan' => 'Order history',
		// My Account specific
		'Pesanan' => 'Orders',
		'pesanan' => 'Orders',
		'Dasbor' => 'Dashboard',
		'dasbor' => 'Dashboard',
		'Unduhan' => 'Downloads',
		'unduhan' => 'Downloads',
		'Alamat' => 'Address',
		'alamat' => 'Address',
		'Metode Pembayaran' => 'Payment methods',
		'metode pembayaran' => 'Payment methods',
		'Detail Akun' => 'Account details',
		'detail akun' => 'Account details',
		'Keluar' => 'Log out',
		'keluar' => 'Log out',
		// Order status
		'Dibatalkan untuk' => 'Cancelled for',
		'dibatalkan untuk' => 'cancelled for',
		'item' => 'item',
		'Item' => 'Item',
		// Action buttons
		'LIHAT' => 'View',
		'Lihat' => 'View',
		'lihat' => 'View',
	);
	
	// Apply translations
	$message = str_ireplace( array_keys( $additional_translations ), array_values( $additional_translations ), $message );
	
	return $message;
}

/**
 * Translate order details status message
 * Handles: "Order #%1$s was placed on %2$s and is currently %3$s."
 */
add_filter( 'woocommerce_order_details_status', 'wp_augoose_translate_order_details_status', 20, 2 );
function wp_augoose_translate_order_details_status( $status_text, $order ) {
	if ( empty( $status_text ) ) {
		return $status_text;
	}
	
	$translations = array(
		'Pesanan' => 'Order',
		'pesanan' => 'Order',
		'PESANAN' => 'ORDER',
		'dilakukan pada' => 'was placed on',
		'Dilakukan pada' => 'Was placed on',
		'saat ini' => 'is currently',
		'Saat ini' => 'Is currently',
		'dan saat ini' => 'and is currently',
		'Dan saat ini' => 'And is currently',
	);
	
	$status_text = str_ireplace( array_keys( $translations ), array_values( $translations ), $status_text );
	
	return $status_text;
}

/**
 * Translate order details page text
 * Handles: "Order details", "Product", "Total", "Actions", "Note:", etc.
 */
add_filter( 'gettext', 'wp_augoose_translate_order_details_text', 20, 3 );
function wp_augoose_translate_order_details_text( $translated_text, $text, $domain ) {
	// Only translate WooCommerce text
	if ( 'woocommerce' !== $domain ) {
		return $translated_text;
	}
	
	// Only translate on My Account pages
	if ( ! is_account_page() && ! is_wc_endpoint_url( 'view-order' ) ) {
		return $translated_text;
	}
	
	// Also translate cart removal messages on cart page
	if ( is_cart() || ( isset( $_GET['removed_item'] ) || isset( $_GET['undo_item'] ) ) ) {
		$cart_translations = array(
			'dihapus' => 'removed',
			'Dihapus' => 'Removed',
			'Batalkan?' => 'Undo?',
			'batalkan?' => 'Undo?',
		);
		
		foreach ( $cart_translations as $id => $en ) {
			if ( stripos( $translated_text, $id ) !== false ) {
				$translated_text = str_ireplace( $id, $en, $translated_text );
			}
		}
	}
	
	$translations = array(
		// Order details page
		'RINCIAN PESANAN' => 'ORDER DETAILS',
		'Rincian pesanan' => 'Order details',
		'rincian pesanan' => 'order details',
		'PESANAN' => 'ORDER',
		'Pesanan' => 'Order',
		'pesanan' => 'Order',
		'PRODUK' => 'PRODUCT',
		'Produk' => 'Product',
		'produk' => 'product',
		'PENGIRIMAN' => 'SHIPPING',
		'Pengiriman' => 'Shipping',
		'pengiriman' => 'shipping',
		'Pengiriman gratis' => 'Free shipping',
		'pengiriman gratis' => 'free shipping',
		'SUBTOTAL' => 'SUBTOTAL',
		'Subtotal' => 'Subtotal',
		'subtotal' => 'subtotal',
		'TOTAL' => 'TOTAL',
		'Total' => 'Total',
		'total' => 'total',
		'AKSI' => 'ACTIONS',
		'Aksi' => 'Actions',
		'aksi' => 'actions',
		'Catatan' => 'Note',
		'catatan' => 'note',
		// Order status message
		'dilakukan pada' => 'was placed on',
		'Dilakukan pada' => 'Was placed on',
		'saat ini' => 'is currently',
		'Saat ini' => 'Is currently',
		'dan saat ini' => 'and is currently',
		'Dan saat ini' => 'And is currently',
	);
	
	// Check if text needs translation
	foreach ( $translations as $id => $en ) {
		if ( stripos( $translated_text, $id ) !== false ) {
			$translated_text = str_ireplace( $id, $en, $translated_text );
		}
	}
	
	return $translated_text;
}

/**
 * Translate order totals labels
 * Handles: "Subtotal", "Shipping", "Total", etc. in order details table
 */
add_filter( 'woocommerce_get_order_item_totals', 'wp_augoose_translate_order_totals', 20, 3 );
function wp_augoose_translate_order_totals( $total_rows, $order, $tax_display ) {
	if ( empty( $total_rows ) || ! is_array( $total_rows ) ) {
		return $total_rows;
	}
	
	$translations = array(
		'SUBTOTAL' => 'SUBTOTAL',
		'Subtotal' => 'Subtotal',
		'subtotal' => 'Subtotal',
		'PENGIRIMAN' => 'SHIPPING',
		'Pengiriman' => 'Shipping',
		'pengiriman' => 'Shipping',
		'Pengiriman gratis' => 'Free shipping',
		'pengiriman gratis' => 'Free shipping',
		'TOTAL' => 'TOTAL',
		'Total' => 'Total',
		'total' => 'Total',
		'PAJAK' => 'TAX',
		'Pajak' => 'Tax',
		'pajak' => 'Tax',
		'DISKON' => 'DISCOUNT',
		'Diskon' => 'Discount',
		'diskon' => 'Discount',
	);
	
	foreach ( $total_rows as $key => $total ) {
		if ( isset( $total['label'] ) ) {
			$label = $total['label'];
			foreach ( $translations as $id => $en ) {
				if ( stripos( $label, $id ) !== false ) {
					$total_rows[ $key ]['label'] = str_ireplace( $id, $en, $label );
					break;
				}
			}
		}
	}
	
	return $total_rows;
}

/**
 * Translate My Account page titles
 * Handles: "Order #3732", "Orders", etc.
 */
add_filter( 'woocommerce_endpoint_title', 'wp_augoose_translate_endpoint_title', 20, 2 );
function wp_augoose_translate_endpoint_title( $title, $endpoint ) {
	if ( empty( $title ) ) {
		return $title;
	}
	
	$translations = array(
		'PESANAN' => 'ORDER',
		'Pesanan' => 'Order',
		'pesanan' => 'Order',
		'Pesanan #' => 'Order #',
		'pesanan #' => 'Order #',
		'PESANAN #' => 'ORDER #',
	);
	
	foreach ( $translations as $id => $en ) {
		if ( stripos( $title, $id ) !== false ) {
			$title = str_ireplace( $id, $en, $title );
		}
	}
	
	return $title;
}

/**
 * ========================================
 * GUEST CHECKOUT & PAYMENT FAILURE HANDLING
 * ========================================
 */

/**
 * Generate and store guest UUID for guest checkout
 */
add_action( 'woocommerce_checkout_init', 'wp_augoose_init_guest_uuid', 5 );
add_action( 'init', 'wp_augoose_init_guest_uuid', 5 );
function wp_augoose_init_guest_uuid() {
	// Only for non-logged-in users
	if ( is_user_logged_in() ) {
		return;
	}
	
	// Check if we already have a guest UUID
	if ( ! WC()->session ) {
		return;
	}
	
	$guest_uuid = WC()->session->get( 'guest_uuid' );
	
	// Generate new UUID if doesn't exist
	if ( empty( $guest_uuid ) ) {
		// Generate UUID v4
		$guest_uuid = wp_augoose_generate_uuid();
		WC()->session->set( 'guest_uuid', $guest_uuid );
		WC()->session->save_data();
	}
}

/**
 * Generate UUID v4
 */
function wp_augoose_generate_uuid() {
	// Use WordPress function if available (WP 5.1+)
	if ( function_exists( 'wp_generate_uuid4' ) ) {
		return wp_generate_uuid4();
	}
	
	// Fallback: Generate UUID v4 manually
	return sprintf(
		'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0x0fff ) | 0x4000,
		mt_rand( 0, 0x3fff ) | 0x8000,
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff )
	);
}

/**
 * Handle expired/failed/canceled payment - invalidate order and force new checkout
 */
add_action( 'woocommerce_order_status_changed', 'wp_augoose_handle_payment_failure', 10, 4 );
add_action( 'woocommerce_order_status_failed', 'wp_augoose_handle_payment_failure_status', 10, 2 );
add_action( 'woocommerce_order_status_cancelled', 'wp_augoose_handle_payment_failure_status', 10, 2 );
function wp_augoose_handle_payment_failure( $order_id, $old_status, $new_status, $order ) {
	// Only handle failed, cancelled, or expired statuses
	$failure_statuses = array( 'failed', 'cancelled' );
	
	// Check if status changed to failure status
	if ( ! in_array( $new_status, $failure_statuses, true ) ) {
		return;
	}
	
	// Check if order has payment method (not just draft)
	$payment_method = $order->get_payment_method();
	if ( empty( $payment_method ) ) {
		return;
	}
	
	wp_augoose_invalidate_failed_order( $order_id, $order, $new_status );
}

function wp_augoose_handle_payment_failure_status( $order_id, $order ) {
	wp_augoose_invalidate_failed_order( $order_id, $order, $order->get_status() );
}

/**
 * Invalidate failed order and force new checkout
 */
function wp_augoose_invalidate_failed_order( $order_id, $order, $status ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}
	
	// Mark order as invalidated
	$order->update_meta_data( '_payment_failed_invalidated', 'yes' );
	$order->update_meta_data( '_payment_failed_at', current_time( 'mysql' ) );
	$order->update_meta_data( '_payment_failed_status', $status );
	$order->save_meta_data();
	
	// Clear cart
	if ( WC()->cart ) {
		WC()->cart->empty_cart();
		WC()->cart->persistent_cart_destroy();
	}
	
	// Clear session data related to this order
	if ( WC()->session ) {
		WC()->session->set( 'order_awaiting_payment', false );
		WC()->session->set( 'chosen_payment_method', '' );
		
		// Generate new guest UUID for new session
		if ( ! is_user_logged_in() ) {
			$new_guest_uuid = wp_augoose_generate_uuid();
			WC()->session->set( 'guest_uuid', $new_guest_uuid );
		}
		
		WC()->session->save_data();
	}
	
	// Log for debugging
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( sprintf(
			'Payment Failure: Order #%d invalidated (status: %s). Cart cleared, new session created.',
			$order_id,
			$status
		) );
	}
}

/**
 * Check for expired payment status from payment gateway
 * This handles payment gateways that set custom status like "expired"
 */
add_action( 'woocommerce_order_status_changed', 'wp_augoose_check_expired_payment', 20, 4 );
function wp_augoose_check_expired_payment( $order_id, $old_status, $new_status, $order ) {
	// Check for expired status (some gateways use custom status)
	$expired_statuses = array( 'expired', 'payment-expired', 'payment_expired' );
	
	// Also check order meta for expired flag
	$is_expired = $order->get_meta( '_payment_expired' );
	$payment_status = $order->get_meta( '_payment_status' );
	
	if ( in_array( $new_status, $expired_statuses, true ) || 
	     $is_expired === 'yes' || 
	     in_array( strtolower( $payment_status ), array( 'expired', 'expire' ), true ) ) {
		wp_augoose_invalidate_failed_order( $order_id, $order, 'expired' );
	}
}

/**
 * Prevent access to failed/cancelled orders - redirect to new checkout
 */
add_action( 'template_redirect', 'wp_augoose_redirect_failed_order_to_checkout', 5 );
function wp_augoose_redirect_failed_order_to_checkout() {
	global $wp;
	
	// Check if viewing a failed/cancelled order
	if ( ! isset( $wp->query_vars['view-order'] ) && ! isset( $wp->query_vars['order-pay'] ) ) {
		return;
	}
	
	$order_id = isset( $wp->query_vars['view-order'] ) ? absint( $wp->query_vars['view-order'] ) : absint( $wp->query_vars['order-pay'] );
	
	if ( ! $order_id ) {
		return;
	}
	
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	
	// Check if order is invalidated due to payment failure
	$is_invalidated = $order->get_meta( '_payment_failed_invalidated' );
	$order_status = $order->get_status();
	
	$failure_statuses = array( 'failed', 'cancelled', 'expired' );
	
	// Redirect if order is failed/cancelled/expired and invalidated
	if ( ( $is_invalidated === 'yes' || in_array( $order_status, $failure_statuses, true ) ) && ! is_user_logged_in() ) {
		// Clear any order-related session
		if ( WC()->session ) {
			WC()->session->set( 'order_awaiting_payment', false );
			
			// Generate new guest UUID
			$new_guest_uuid = wp_augoose_generate_uuid();
			WC()->session->set( 'guest_uuid', $new_guest_uuid );
			WC()->session->save_data();
		}
		
		// Redirect to checkout with message
		$checkout_url = wc_get_checkout_url();
		wp_safe_redirect( add_query_arg( 'payment_failed', '1', $checkout_url ) );
		exit;
	}
}

/**
 * Show message on checkout if redirected from failed payment
 */
add_action( 'woocommerce_before_checkout_form', 'wp_augoose_show_payment_failed_message', 5 );
function wp_augoose_show_payment_failed_message() {
	if ( ! isset( $_GET['payment_failed'] ) || $_GET['payment_failed'] !== '1' ) {
		return;
	}
	
	wc_add_notice(
		'An error occurred while processing your order. Please check if there are any changes in your payment method and review your order history before placing another order.',
		'error'
	);
}

/**
 * ============================================================================
 * WCML Currency Conversion for Cart + Checkout
 * ============================================================================
 * 
 * Converts SGD/MYR to IDR in cart/checkout using WCML exchange rates
 * USD stays USD (no conversion)
 * 
 * Safe: No fatal errors if WCML is not active or not configured
 */

/**
 * Safe detect client currency from WCML
 * Returns currency code or null if WCML not available
 * 
 * @return string|null Currency code (SGD, MYR, USD, IDR, etc.) or null
 */
function wp_augoose_safe_get_client_currency() {
	// Guard: WCML must be active
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return null;
	}
	
	global $woocommerce_wpml;
	if ( ! $woocommerce_wpml || ! isset( $woocommerce_wpml->multi_currency ) ) {
		return null;
	}
	
	$multi_currency = $woocommerce_wpml->multi_currency;
	
	// Safe get client currency
	try {
		if ( method_exists( $multi_currency, 'get_client_currency' ) ) {
			$currency = $multi_currency->get_client_currency();
			if ( $currency && is_string( $currency ) ) {
				return strtoupper( trim( $currency ) );
			}
		}
	} catch ( Exception $e ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WP_Augoose: Error getting client currency - ' . $e->getMessage() );
		}
	}
	
	return null;
}

/**
 * Safe get exchange rate from WCML using WCML's built-in method
 * Returns rate for converting from source_currency to IDR
 * Uses WCML's get_currency_rate() method for consistency
 * 
 * @param string $source_currency Source currency code (SGD, MYR, etc.)
 * @return float|null Exchange rate or null if not available/invalid
 */
function wp_augoose_safe_get_exchange_rate_to_idr( $source_currency ) {
	// Guard: WCML must be active
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return null;
	}
	
	global $woocommerce_wpml;
	if ( ! $woocommerce_wpml || ! isset( $woocommerce_wpml->multi_currency ) ) {
		return null;
	}
	
	$multi_currency = $woocommerce_wpml->multi_currency;
	$source_currency = strtoupper( trim( $source_currency ) );
	
	// If source is already IDR, rate is 1
	if ( $source_currency === 'IDR' ) {
		return 1.0;
	}
	
	// Use WCML's built-in method to get currency rate
	try {
		// Method 1: Try get_currency_rate() if available
		if ( method_exists( $multi_currency, 'get_currency_rate' ) ) {
			$source_rate = $multi_currency->get_currency_rate( $source_currency );
			$idr_rate = $multi_currency->get_currency_rate( 'IDR' );
			
			if ( $source_rate && $source_rate > 0 && $idr_rate && $idr_rate > 0 ) {
				$rate = $idr_rate / $source_rate;
				if ( $rate > 0 && is_finite( $rate ) ) {
					return $rate;
				}
			}
		}
		
		// Method 2: Fallback to get_exchange_rates()
		if ( method_exists( $multi_currency, 'get_exchange_rates' ) ) {
			$exchange_rates = $multi_currency->get_exchange_rates();
			
			if ( ! is_array( $exchange_rates ) || empty( $exchange_rates ) ) {
				return null;
			}
			
			// Get rates for source currency and IDR
			$source_rate = isset( $exchange_rates[ $source_currency ] ) ? (float) $exchange_rates[ $source_currency ] : null;
			$idr_rate = isset( $exchange_rates['IDR'] ) ? (float) $exchange_rates['IDR'] : null;
			
			// Validate rates
			if ( $source_rate === null || $idr_rate === null ) {
				return null;
			}
			
			if ( $source_rate <= 0 || $idr_rate <= 0 ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( "WP_Augoose: Invalid exchange rate - source_rate={$source_rate}, idr_rate={$idr_rate}" );
				}
				return null;
			}
			
			// Calculate rate: IDR rate / source rate
			// Example: If SGD rate = 1.0 and IDR rate = 13200, then 1 SGD = 13200 IDR
			$rate = $idr_rate / $source_rate;
			
			// Validate final rate
			if ( $rate > 0 && is_finite( $rate ) ) {
				return $rate;
			}
		}
	} catch ( Exception $e ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WP_Augoose: Error getting exchange rate - ' . $e->getMessage() );
		}
	}
	
	return null;
}

/**
 * Convert price using WCML's built-in conversion method
 * This ensures consistency with product page prices
 * 
 * @param float $price Price to convert
 * @param string $from_currency Source currency (SGD, MYR, etc.)
 * @param string $to_currency Target currency (IDR)
 * @return float|null Converted price or null if error
 */
function wp_augoose_convert_price_with_wcml( $price, $from_currency, $to_currency = 'IDR' ) {
	// Guard: WCML must be active
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return null;
	}
	
	global $woocommerce_wpml;
	if ( ! $woocommerce_wpml || ! isset( $woocommerce_wpml->multi_currency ) ) {
		return null;
	}
	
	$multi_currency = $woocommerce_wpml->multi_currency;
	$from_currency = strtoupper( trim( $from_currency ) );
	$to_currency = strtoupper( trim( $to_currency ) );
	
	// If same currency, return as is
	if ( $from_currency === $to_currency ) {
		return (float) $price;
	}
	
	// Use WCML's price conversion method
	try {
		// Get exchange rate
		$rate = wp_augoose_safe_get_exchange_rate_to_idr( $from_currency );
		if ( ! $rate || $rate <= 0 ) {
			return null;
		}
		
		// Convert price
		$converted_price = (float) $price * $rate;
		
		// Round to 2 decimals
		$converted_price = round( $converted_price, 2 );
		
		return $converted_price;
	} catch ( Exception $e ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WP_Augoose: Error converting price - ' . $e->getMessage() );
		}
		return null;
	}
}

/**
 * Save original currency and converted IDR price when item is added to cart
 * Calculate conversion in backend using WCML rate
 */
add_filter( 'woocommerce_add_cart_item_data', 'wp_augoose_save_original_currency_to_cart_item', 10, 3 );
function wp_augoose_save_original_currency_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
	// Guard: Ensure cart_item_data is array
	if ( ! is_array( $cart_item_data ) ) {
		$cart_item_data = array();
	}
	
	// Guard: Validate product_id
	if ( ! $product_id || ! is_numeric( $product_id ) ) {
		return $cart_item_data;
	}
	
	// Guard: WCML must be active
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return $cart_item_data;
	}
	
	// Get product object (variation or simple product)
	$product = $variation_id ? wc_get_product( $variation_id ) : wc_get_product( $product_id );
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return $cart_item_data;
	}
	
	// Get client currency at the time item is added
	$client_currency = wp_augoose_safe_get_client_currency();
	
	// If not from WCML, try cookie
	if ( ! $client_currency && isset( $_COOKIE['wp_augoose_currency'] ) ) {
		$cookie_currency = sanitize_text_field( $_COOKIE['wp_augoose_currency'] );
		if ( $cookie_currency ) {
			$client_currency = strtoupper( trim( $cookie_currency ) );
		}
	}
	
	// IMPORTANT: For USD, save currency but don't convert
	if ( $client_currency === 'USD' ) {
		$cart_item_data['wp_augoose_original_currency'] = 'USD';
		$cart_item_data['wp_augoose_no_conversion'] = true; // Flag to indicate no conversion needed
		return $cart_item_data; // USD stays USD - no conversion
	}
	
	// Skip if not SGD/MYR
	if ( ! $client_currency || ! in_array( $client_currency, array( 'SGD', 'MYR' ), true ) ) {
		return $cart_item_data;
	}
	
	// Get price that was displayed (already converted by WCML to client_currency)
	// This is the price user saw on product page (e.g., 80 SGD)
	$displayed_price = (float) $product->get_price( 'edit' );
	
	if ( $displayed_price <= 0 ) {
		return $cart_item_data;
	}
	
	// Get WCML exchange rate to convert to IDR
	global $woocommerce_wpml;
	$price_idr = null;
	
	if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
		$multi_currency = $woocommerce_wpml->multi_currency;
		
		try {
			// Get rate from WCML - get_exchange_rates() returns latest rates (updated per hour)
			// This ensures we always use the most current exchange rates
			if ( method_exists( $multi_currency, 'get_exchange_rates' ) ) {
				$exchange_rates = $multi_currency->get_exchange_rates();
				
				if ( is_array( $exchange_rates ) && ! empty( $exchange_rates ) ) {
					// Get rates from exchange_rates array (this is the source of truth from WCML)
					$item_rate = isset( $exchange_rates[ $client_currency ] ) ? (float) $exchange_rates[ $client_currency ] : null;
					$idr_rate = isset( $exchange_rates['IDR'] ) ? (float) $exchange_rates['IDR'] : null;
					
					if ( $item_rate && $item_rate > 0 && $idr_rate && $idr_rate > 0 ) {
						// Convert: displayed_price (dalam client_currency) ke IDR
						// Formula: price_idr = displayed_price * (idr_rate / item_rate)
						$conversion_rate = $idr_rate / $item_rate;
						$price_idr = $displayed_price * $conversion_rate;
						$price_idr = round( $price_idr, 2 );
						
						if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
							error_log( "WP_Augoose: Add to cart - Product #{$product_id}, {$client_currency} {$displayed_price} → IDR {$price_idr} (item_rate: {$item_rate}, idr_rate: {$idr_rate}, conversion_rate: {$conversion_rate})" );
						}
					}
				}
			}
			
			// Fallback to get_currency_rate() if get_exchange_rates() not available
			if ( $price_idr === null && method_exists( $multi_currency, 'get_currency_rate' ) ) {
				$item_rate = $multi_currency->get_currency_rate( $client_currency );
				$idr_rate = $multi_currency->get_currency_rate( 'IDR' );
				
				if ( $item_rate && $item_rate > 0 && $idr_rate && $idr_rate > 0 ) {
					$conversion_rate = $idr_rate / $item_rate;
					$price_idr = $displayed_price * $conversion_rate;
					$price_idr = round( $price_idr, 2 );
					
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( "WP_Augoose: Add to cart (fallback) - Product #{$product_id}, {$client_currency} {$displayed_price} → IDR {$price_idr} (rate: {$conversion_rate})" );
					}
				}
			}
		} catch ( Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'WP_Augoose: Error calculating conversion on add to cart - ' . $e->getMessage() );
			}
		}
	}
	
	// Save to cart item data
	if ( $price_idr && $price_idr > 0 ) {
		$cart_item_data['wp_augoose_original_currency'] = $client_currency;
		$cart_item_data['wp_augoose_original_price'] = $displayed_price;
		$cart_item_data['wp_augoose_converted_price_idr'] = $price_idr;
		$cart_item_data['wp_augoose_converted_to_idr'] = true;
		
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "WP_Augoose: Saved conversion data - currency: {$client_currency}, original: {$displayed_price}, converted: {$price_idr}" );
		}
	}
	
	return $cart_item_data;
}

/**
 * Convert cart item prices from foreign currency to IDR
 * Only converts SGD and MYR to IDR
 * USD stays USD (no conversion)
 * 
 * Hook: woocommerce_before_calculate_totals
 * Priority: 1 (very early, before WCML and other calculations)
 * 
 * IMPORTANT: This runs BEFORE wcml-currency-routing.php forces IDR
 * So we need to check the ORIGINAL currency before it's forced to IDR
 * 
 * Also runs when cart is loaded from session to convert existing items
 */
add_action( 'woocommerce_before_calculate_totals', 'wp_augoose_convert_cart_items_to_idr', 1 );
add_action( 'woocommerce_cart_loaded_from_session', 'wp_augoose_convert_cart_items_to_idr', 20 );

function wp_augoose_convert_cart_items_to_idr( $cart ) {
	// Guard: Cart must exist and not empty
	if ( ! $cart || ! is_a( $cart, 'WC_Cart' ) ) {
		return;
	}
	
	// Guard: Check if cart is empty
	if ( $cart->is_empty() ) {
		return;
	}
	
	// Guard: WCML must be active
	if ( ! class_exists( 'woocommerce_wpml' ) ) {
		return;
	}
	
	// Guard: Ensure cart_contents is accessible
	if ( ! isset( $cart->cart_contents ) || ! is_array( $cart->cart_contents ) ) {
		return;
	}
	
	// Process each cart item to get original currency
	$client_currency = null;
	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		// Check if cart item has original currency saved
		if ( isset( $cart_item['wp_augoose_original_currency'] ) ) {
			$client_currency = $cart_item['wp_augoose_original_currency'];
			break; // Use first item's currency
		}
	}
	
	// If not found in cart items, check session
	if ( ! $client_currency && function_exists( 'WC' ) && WC()->session ) {
		$client_currency = WC()->session->get( 'wp_augoose_original_currency' );
	}
	
	// If still not found, get from WCML
	if ( ! $client_currency ) {
		$client_currency = wp_augoose_safe_get_client_currency();
	}
	
	// If still no currency, try to get from cookie (user's selected currency)
	if ( ! $client_currency && isset( $_COOKIE['wp_augoose_currency'] ) ) {
		$client_currency = strtoupper( trim( sanitize_text_field( $_COOKIE['wp_augoose_currency'] ) ) );
	}
	
	if ( ! $client_currency ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WP_Augoose: Cannot determine client currency for conversion' );
		}
		return; // Cannot determine currency
	}
	
	// IMPORTANT: USD stays USD (no conversion)
	if ( $client_currency === 'USD' ) {
		return; // USD - no conversion needed
	}
	
	// Only convert SGD and MYR to IDR
	if ( ! in_array( $client_currency, array( 'SGD', 'MYR' ), true ) ) {
		return; // Other currencies - no conversion needed
	}
	
	// Get exchange rate
	$exchange_rate = wp_augoose_safe_get_exchange_rate_to_idr( $client_currency );
	if ( ! $exchange_rate || $exchange_rate <= 0 ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "WP_Augoose: Cannot convert {$client_currency} to IDR - invalid exchange rate" );
		}
		return; // Rate not available - skip conversion to prevent errors
	}
	
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( "WP_Augoose: Starting conversion - currency: {$client_currency}, rate: {$exchange_rate}" );
	}
	
	// Process each cart item
	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		// IMPORTANT: Skip USD items completely (no conversion)
		$item_currency_check = isset( $cart_item['wp_augoose_original_currency'] ) 
			? $cart_item['wp_augoose_original_currency'] 
			: '';
		if ( $item_currency_check === 'USD' || isset( $cart_item['wp_augoose_no_conversion'] ) ) {
			continue; // USD stays USD - skip conversion
		}
		
		// Check if item already has converted price from add_to_cart
		// If yes, use that price directly (already calculated in backend)
		if ( isset( $cart_item['wp_augoose_converted_price_idr'] ) && $cart_item['wp_augoose_converted_price_idr'] > 0 ) {
			$price_idr = (float) $cart_item['wp_augoose_converted_price_idr'];
			
			// Get product
			$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
			if ( $product && is_a( $product, 'WC_Product' ) ) {
				try {
					// Set converted price to product
					$product->set_price( $price_idr );
					if ( isset( $product->data ) && is_array( $product->data ) ) {
						$product->data['price'] = (string) $price_idr;
					}
					if ( isset( $cart->cart_contents[ $cart_item_key ] ) ) {
						$cart->cart_contents[ $cart_item_key ]['data'] = $product;
					}
					
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( "WP_Augoose: Using pre-calculated IDR price {$price_idr} for cart item {$cart_item_key}" );
					}
				} catch ( Exception $e ) {
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( "WP_Augoose: Error setting pre-calculated price - " . $e->getMessage() );
					}
				}
			}
			continue; // Skip further processing, already converted
		}
		
		// If no pre-calculated price, check if needs conversion
		// IMPORTANT: Even if already converted, recalculate if currency might have changed
		// This ensures items already in cart are also converted when cart is loaded
		if ( isset( $cart_item['wp_augoose_converted_to_idr'] ) && $cart_item['wp_augoose_converted_to_idr'] === true ) {
			// Check if we need to recalculate (e.g., if currency changed or item was added before conversion logic)
			$item_currency_check = isset( $cart_item['wp_augoose_original_currency'] ) 
				? $cart_item['wp_augoose_original_currency'] 
				: null;
			
			// If currency is SGD/MYR but no converted price saved, recalculate
			if ( $item_currency_check && in_array( $item_currency_check, array( 'SGD', 'MYR' ), true ) ) {
				// Check if converted price exists
				if ( ! isset( $cart_item['wp_augoose_converted_price_idr'] ) || $cart_item['wp_augoose_converted_price_idr'] <= 0 ) {
					// Need to recalculate - continue to conversion logic below
				} else {
					continue; // Already has converted price, skip
				}
			} else {
				continue; // Currency is not SGD/MYR, skip
			}
		}
		
		// Get product
		$product = $cart_item['data'];
		if ( ! is_a( $product, 'WC_Product' ) ) {
			continue;
		}
		
		// Get original currency for this specific cart item
		$item_currency = isset( $cart_item['wp_augoose_original_currency'] ) 
			? $cart_item['wp_augoose_original_currency'] 
			: $client_currency;
		
		// Skip if this item's currency is USD or not SGD/MYR
		if ( $item_currency === 'USD' || ! in_array( $item_currency, array( 'SGD', 'MYR' ), true ) ) {
			continue;
		}
		
		// Get displayed price (already converted by WCML)
		$displayed_price = (float) $product->get_price( 'edit' );
		if ( $displayed_price <= 0 ) {
			continue;
		}
		
		// Get WCML rate and convert
		global $woocommerce_wpml;
		$price_idr = null;
		
		if ( $woocommerce_wpml && isset( $woocommerce_wpml->multi_currency ) ) {
			$multi_currency = $woocommerce_wpml->multi_currency;
			
			try {
				// Get rate from WCML - get_exchange_rates() returns latest rates (updated per hour)
				// This ensures we always use the most current exchange rates
				if ( method_exists( $multi_currency, 'get_exchange_rates' ) ) {
					$exchange_rates = $multi_currency->get_exchange_rates();
					
					if ( is_array( $exchange_rates ) && ! empty( $exchange_rates ) ) {
						// Get rates from exchange_rates array (this is the source of truth from WCML)
						$item_rate = isset( $exchange_rates[ $item_currency ] ) ? (float) $exchange_rates[ $item_currency ] : null;
						$idr_rate = isset( $exchange_rates['IDR'] ) ? (float) $exchange_rates['IDR'] : null;
						
						if ( $item_rate && $item_rate > 0 && $idr_rate && $idr_rate > 0 ) {
							// Convert: displayed_price (dalam item_currency) ke IDR
							// Formula: price_idr = displayed_price * (idr_rate / item_rate)
							$conversion_rate = $idr_rate / $item_rate;
							$price_idr = $displayed_price * $conversion_rate;
							$price_idr = round( $price_idr, 2 );
							
							if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
								error_log( "WP_Augoose: Cart conversion - {$item_currency} {$displayed_price} → IDR {$price_idr} (item_rate: {$item_rate}, idr_rate: {$idr_rate}, conversion_rate: {$conversion_rate})" );
							}
						}
					}
				}
				
				// Fallback to get_currency_rate() if get_exchange_rates() not available
				if ( $price_idr === null && method_exists( $multi_currency, 'get_currency_rate' ) ) {
					$item_rate = $multi_currency->get_currency_rate( $item_currency );
					$idr_rate = $multi_currency->get_currency_rate( 'IDR' );
					
					if ( $item_rate && $item_rate > 0 && $idr_rate && $idr_rate > 0 ) {
						$conversion_rate = $idr_rate / $item_rate;
						$price_idr = $displayed_price * $conversion_rate;
						$price_idr = round( $price_idr, 2 );
						
						if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
							error_log( "WP_Augoose: Cart conversion (fallback) - {$item_currency} {$displayed_price} → IDR {$price_idr} (rate: {$conversion_rate})" );
						}
					}
				}
			} catch ( Exception $e ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'WP_Augoose: Error converting in cart - ' . $e->getMessage() );
				}
			}
		}
		
		// If conversion successful, set price and save to cart item
		if ( $price_idr && $price_idr > 0 ) {
			// Validate price before setting
			if ( ! is_finite( $price_idr ) || $price_idr <= 0 ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( "WP_Augoose: Invalid converted price {$price_idr} for cart item {$cart_item_key}" );
				}
				continue;
			}
			
			// Set converted price to product
			try {
				if ( method_exists( $product, 'set_price' ) ) {
					$product->set_price( $price_idr );
				}
				if ( isset( $product->data ) && is_array( $product->data ) ) {
					$product->data['price'] = (string) $price_idr;
				}
			} catch ( Exception $e ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					$product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : 'unknown';
					error_log( "WP_Augoose: Error setting price for product #{$product_id} - " . $e->getMessage() );
				}
				continue;
			}
			
			// Save converted price to cart item data for later use
			if ( isset( $cart->cart_contents ) && is_array( $cart->cart_contents ) && isset( $cart->cart_contents[ $cart_item_key ] ) ) {
				$cart->cart_contents[ $cart_item_key ]['wp_augoose_converted_price_idr'] = $price_idr;
				$cart->cart_contents[ $cart_item_key ]['wp_augoose_converted_to_idr'] = true;
				$cart->cart_contents[ $cart_item_key ]['wp_augoose_original_currency'] = $item_currency;
				$cart->cart_contents[ $cart_item_key ]['wp_augoose_original_price'] = $displayed_price;
				$cart->cart_contents[ $cart_item_key ]['data'] = $product;
			}
			
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : 'unknown';
				error_log( "WP_Augoose: Converted cart item #{$product_id} - {$item_currency} {$displayed_price} → IDR {$price_idr}" );
			}
		}
	}
}

/**
 * Filter cart item price to use converted price
 * This ensures the converted price is used in cart calculations
 */
add_filter( 'woocommerce_cart_item_price', 'wp_augoose_use_converted_cart_item_price', 999, 3 );
function wp_augoose_use_converted_cart_item_price( $price_html, $cart_item, $cart_item_key ) {
	// Guard: Check if cart_item is valid array
	if ( ! is_array( $cart_item ) ) {
		return $price_html;
	}
	
	// IMPORTANT: Skip if currency is USD (no conversion for USD)
	$item_currency = isset( $cart_item['wp_augoose_original_currency'] ) 
		? $cart_item['wp_augoose_original_currency'] 
		: '';
	
	// If no original currency set, check current client currency
	if ( empty( $item_currency ) ) {
		$item_currency = wp_augoose_safe_get_client_currency();
		if ( ! $item_currency && isset( $_COOKIE['wp_augoose_currency'] ) ) {
			$item_currency = strtoupper( trim( sanitize_text_field( $_COOKIE['wp_augoose_currency'] ) ) );
		}
	}
	
	// Check if no conversion flag is set or currency is USD
	if ( isset( $cart_item['wp_augoose_no_conversion'] ) || $item_currency === 'USD' ) {
		return $price_html; // USD stays USD - no conversion
	}
	
	// Check if this item has been converted
	if ( isset( $cart_item['wp_augoose_converted_price_idr'] ) ) {
		$converted_price = (float) $cart_item['wp_augoose_converted_price_idr'];
		
		// Validate price
		if ( $converted_price > 0 && is_finite( $converted_price ) ) {
			// Force IDR currency when displaying converted price
			$price_html = wc_price( $converted_price, array( 'currency' => 'IDR' ) );
		}
	}
	
	return $price_html;
}

/**
 * Filter cart item subtotal to use converted price
 */
add_filter( 'woocommerce_cart_item_subtotal', 'wp_augoose_use_converted_cart_item_subtotal', 999, 3 );
function wp_augoose_use_converted_cart_item_subtotal( $subtotal_html, $cart_item, $cart_item_key ) {
	// Guard: Check if cart_item is valid array
	if ( ! is_array( $cart_item ) ) {
		return $subtotal_html;
	}
	
	// IMPORTANT: Skip if currency is USD (no conversion for USD)
	$item_currency = isset( $cart_item['wp_augoose_original_currency'] ) 
		? $cart_item['wp_augoose_original_currency'] 
		: '';
	
	// If no original currency set, check current client currency
	if ( empty( $item_currency ) ) {
		$item_currency = wp_augoose_safe_get_client_currency();
		if ( ! $item_currency && isset( $_COOKIE['wp_augoose_currency'] ) ) {
			$item_currency = strtoupper( trim( sanitize_text_field( $_COOKIE['wp_augoose_currency'] ) ) );
		}
	}
	
	// Check if no conversion flag is set or currency is USD
	if ( isset( $cart_item['wp_augoose_no_conversion'] ) || $item_currency === 'USD' ) {
		return $subtotal_html; // USD stays USD - no conversion
	}
	
	// Check if this item has been converted
	if ( isset( $cart_item['wp_augoose_converted_price_idr'] ) ) {
		$converted_price = (float) $cart_item['wp_augoose_converted_price_idr'];
		
		// Validate price
		if ( $converted_price > 0 && is_finite( $converted_price ) ) {
			$quantity = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 1;
			
			// Validate quantity
			if ( $quantity > 0 ) {
				$subtotal = $converted_price * $quantity;
				
				// Validate subtotal
				if ( $subtotal > 0 && is_finite( $subtotal ) ) {
					// Force IDR currency when displaying converted subtotal
					$subtotal_html = wc_price( $subtotal, array( 'currency' => 'IDR' ) );
				}
			}
		}
	}
	
	return $subtotal_html;
}

/**
 * Filter cart subtotal to use IDR currency if items were converted
 */
add_filter( 'woocommerce_cart_subtotal', 'wp_augoose_force_idr_cart_subtotal', 999, 3 );
function wp_augoose_force_idr_cart_subtotal( $subtotal_html, $compound, $cart ) {
	// Check if cart has converted items or currency is IDR
	if ( ! $cart || $cart->is_empty() ) {
		return $subtotal_html;
	}
	
	// IMPORTANT: Skip if currency is USD (no conversion for USD)
	// Use client currency from WCML instead of base currency
	$current_currency = wp_augoose_safe_get_client_currency();
	if ( ! $current_currency ) {
		$current_currency = get_woocommerce_currency();
	}
	if ( $current_currency === 'USD' ) {
		return $subtotal_html; // USD stays USD - no conversion
	}
	
	// Check if currency is IDR
	$has_converted = ( $current_currency === 'IDR' );
	
	if ( ! $has_converted ) {
		foreach ( $cart->get_cart() as $cart_item ) {
			// Skip if item currency is USD
			$item_currency = isset( $cart_item['wp_augoose_original_currency'] ) 
				? $cart_item['wp_augoose_original_currency'] 
				: '';
			if ( $item_currency === 'USD' ) {
				continue; // Skip USD items
			}
			
			if ( isset( $cart_item['wp_augoose_converted_to_idr'] ) && $cart_item['wp_augoose_converted_to_idr'] === true ) {
				$has_converted = true;
				break;
			}
		}
	}
	
	if ( $has_converted ) {
		$subtotal = $cart->get_subtotal();
		$subtotal_html = wc_price( $subtotal, array( 'currency' => 'IDR' ) );
	}
	
	return $subtotal_html;
}

/**
 * Filter cart total to use IDR currency if items were converted
 */
add_filter( 'woocommerce_cart_total', 'wp_augoose_force_idr_cart_total', 999 );
function wp_augoose_force_idr_cart_total( $total_html ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		return $total_html;
	}
	
	// IMPORTANT: Skip if currency is USD (no conversion for USD)
	// Use client currency from WCML instead of base currency
	$current_currency = wp_augoose_safe_get_client_currency();
	if ( ! $current_currency ) {
		$current_currency = get_woocommerce_currency();
	}
	if ( $current_currency === 'USD' ) {
		return $total_html; // USD stays USD - no conversion
	}
	
	// Check if currency is IDR or items were converted
	$has_converted = ( $current_currency === 'IDR' );
	
	if ( ! $has_converted ) {
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			// Skip if item currency is USD
			$item_currency = isset( $cart_item['wp_augoose_original_currency'] ) 
				? $cart_item['wp_augoose_original_currency'] 
				: '';
			if ( $item_currency === 'USD' ) {
				continue; // Skip USD items
			}
			
			if ( isset( $cart_item['wp_augoose_converted_to_idr'] ) && $cart_item['wp_augoose_converted_to_idr'] === true ) {
				$has_converted = true;
				break;
			}
		}
	}
	
	if ( $has_converted ) {
		$total = WC()->cart->get_total( 'edit' );
		$total_html = wc_price( $total, array( 'currency' => 'IDR' ) );
	}
	
	return $total_html;
}

/**
 * Filter cart totals functions to use IDR currency
 */
add_filter( 'woocommerce_cart_totals_subtotal_html', 'wp_augoose_force_idr_cart_totals_subtotal', 999 );
function wp_augoose_force_idr_cart_totals_subtotal( $subtotal_html ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		return $subtotal_html;
	}
	
	// IMPORTANT: Skip if currency is USD (no conversion for USD)
	// Use client currency from WCML instead of base currency
	$current_currency = wp_augoose_safe_get_client_currency();
	if ( ! $current_currency ) {
		$current_currency = get_woocommerce_currency();
	}
	if ( $current_currency === 'USD' ) {
		return $subtotal_html; // USD stays USD - no conversion
	}
	
	// Check if currency is IDR
	$has_converted = ( $current_currency === 'IDR' );
	
	if ( ! $has_converted ) {
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			// Skip if item currency is USD
			$item_currency = isset( $cart_item['wp_augoose_original_currency'] ) 
				? $cart_item['wp_augoose_original_currency'] 
				: '';
			if ( $item_currency === 'USD' ) {
				continue; // Skip USD items
			}
			
			if ( isset( $cart_item['wp_augoose_converted_to_idr'] ) && $cart_item['wp_augoose_converted_to_idr'] === true ) {
				$has_converted = true;
				break;
			}
		}
	}
	
	if ( $has_converted ) {
		$subtotal = WC()->cart->get_subtotal();
		$subtotal_html = wc_price( $subtotal, array( 'currency' => 'IDR' ) );
	}
	
	return $subtotal_html;
}

add_filter( 'woocommerce_cart_totals_order_total_html', 'wp_augoose_force_idr_cart_totals_order_total', 999 );
function wp_augoose_force_idr_cart_totals_order_total( $total_html ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		return $total_html;
	}
	
	// IMPORTANT: Skip if currency is USD (no conversion for USD)
	// Use client currency from WCML instead of base currency
	$current_currency = wp_augoose_safe_get_client_currency();
	if ( ! $current_currency ) {
		$current_currency = get_woocommerce_currency();
	}
	if ( $current_currency === 'USD' ) {
		return $total_html; // USD stays USD - no conversion
	}
	
	// Check if currency is IDR or items were converted
	$has_converted = ( $current_currency === 'IDR' );
	
	if ( ! $has_converted ) {
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			// Skip if item currency is USD
			$item_currency = isset( $cart_item['wp_augoose_original_currency'] ) 
				? $cart_item['wp_augoose_original_currency'] 
				: '';
			if ( $item_currency === 'USD' ) {
				continue; // Skip USD items
			}
			
			if ( isset( $cart_item['wp_augoose_converted_to_idr'] ) && $cart_item['wp_augoose_converted_to_idr'] === true ) {
				$has_converted = true;
				break;
			}
		}
	}
	
	if ( $has_converted ) {
		$total = WC()->cart->get_total( 'edit' );
		$total_html = wc_price( $total, array( 'currency' => 'IDR' ) );
	}
	
	return $total_html;
}

/**
 * Ensure converted IDR price is used when order is created from cart
 * This ensures checkout uses the same converted price
 */
add_action( 'woocommerce_checkout_create_order_line_item', 'wp_augoose_use_converted_price_in_order', 10, 4 );
function wp_augoose_use_converted_price_in_order( $item, $cart_item_key, $values, $order ) {
	// Guard: Validate inputs
	if ( ! $item || ! is_a( $item, 'WC_Order_Item_Product' ) ) {
		return;
	}
	
	if ( ! is_array( $values ) ) {
		return;
	}
	
	// Check if cart item has converted price
	if ( isset( $values['wp_augoose_converted_price_idr'] ) && $values['wp_augoose_converted_price_idr'] > 0 ) {
		$converted_price = (float) $values['wp_augoose_converted_price_idr'];
		$quantity = isset( $values['quantity'] ) ? (int) $values['quantity'] : 1;
		
		// Validate price and quantity
		if ( $converted_price > 0 && is_finite( $converted_price ) && $quantity > 0 ) {
			$line_subtotal = $converted_price * $quantity;
			$line_total = $converted_price * $quantity;
			
			// Validate calculated totals
			if ( ! is_finite( $line_subtotal ) || ! is_finite( $line_total ) ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( "WP_Augoose: Invalid calculated totals for order item" );
				}
				return;
			}
			
			try {
				// Set the line item prices to converted IDR price
				// This overrides the prices that WooCommerce calculated
				if ( method_exists( $item, 'set_subtotal' ) ) {
					$item->set_subtotal( $line_subtotal );
				}
				if ( method_exists( $item, 'set_total' ) ) {
					$item->set_total( $line_total );
				}
				
				// Preserve tax data if exists
				if ( isset( $values['line_subtotal_tax'] ) && method_exists( $item, 'set_subtotal_tax' ) ) {
					$item->set_subtotal_tax( $values['line_subtotal_tax'] );
				}
				if ( isset( $values['line_tax'] ) && method_exists( $item, 'set_total_tax' ) ) {
					$item->set_total_tax( $values['line_tax'] );
				}
				if ( isset( $values['line_tax_data'] ) && method_exists( $item, 'set_taxes' ) ) {
					$item->set_taxes( $values['line_tax_data'] );
				}
				
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( "WP_Augoose: Set order item price to converted IDR: {$converted_price} x {$quantity} = {$line_total}" );
				}
			} catch ( Exception $e ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( "WP_Augoose: Error setting order item price - " . $e->getMessage() );
				}
			}
		}
	}
}

/**
 * Reset conversion flag when cart item is updated
 * This allows re-conversion if currency changes
 */
add_action( 'woocommerce_after_cart_item_quantity_update', 'wp_augoose_reset_conversion_flag_on_qty_update', 10, 3 );
add_action( 'woocommerce_cart_item_removed', 'wp_augoose_reset_conversion_flag_on_remove', 10, 2 );
add_action( 'woocommerce_cart_item_restored', 'wp_augoose_reset_conversion_flag_on_restore', 10, 2 );

function wp_augoose_reset_conversion_flag_on_qty_update( $cart_item_key, $quantity, $old_quantity ) {
	if ( ! WC()->cart ) {
		return;
	}
	
	// Reset conversion flag so item can be re-converted
	if ( isset( WC()->cart->cart_contents[ $cart_item_key ] ) ) {
		unset( WC()->cart->cart_contents[ $cart_item_key ]['wp_augoose_converted_to_idr'] );
		unset( WC()->cart->cart_contents[ $cart_item_key ]['wp_augoose_original_currency'] );
		unset( WC()->cart->cart_contents[ $cart_item_key ]['wp_augoose_original_price'] );
		unset( WC()->cart->cart_contents[ $cart_item_key ]['wp_augoose_exchange_rate'] );
	}
}

function wp_augoose_reset_conversion_flag_on_remove( $cart_item_key, $cart ) {
	// Item removed, no need to reset
}

function wp_augoose_reset_conversion_flag_on_restore( $cart_item_key, $cart ) {
	// Reset conversion flag when item is restored
	if ( isset( $cart->cart_contents[ $cart_item_key ] ) ) {
		unset( $cart->cart_contents[ $cart_item_key ]['wp_augoose_converted_to_idr'] );
		unset( $cart->cart_contents[ $cart_item_key ]['wp_augoose_original_currency'] );
		unset( $cart->cart_contents[ $cart_item_key ]['wp_augoose_original_price'] );
		unset( $cart->cart_contents[ $cart_item_key ]['wp_augoose_exchange_rate'] );
	}
}

/**
 * Ensure variations with stock > 0 are always active and purchasable
 * Fixes issue where variations with stock are disabled
 */
add_filter( 'woocommerce_variation_is_active', 'wp_augoose_ensure_variation_active_if_in_stock', 1, 2 );
function wp_augoose_ensure_variation_active_if_in_stock( $is_active, $variation ) {
	// Check if variation is valid
	if ( ! $variation || ! is_a( $variation, 'WC_Product_Variation' ) ) {
		return $is_active;
	}
	
	// If already active, return as is
	if ( $is_active ) {
		return $is_active;
	}
	
	// Get stock information - use multiple methods to ensure accuracy
	$stock_quantity = $variation->get_stock_quantity();
	$stock_status = $variation->get_stock_status();
	$manage_stock = $variation->get_manage_stock();
	
	// Also check meta directly as fallback
	$stock_quantity_meta = get_post_meta( $variation->get_id(), '_stock', true );
	$stock_status_meta = get_post_meta( $variation->get_id(), '_stock_status', true );
	$manage_stock_meta = get_post_meta( $variation->get_id(), '_manage_stock', true );
	
	// Use meta values if get methods return null/empty
	if ( $stock_quantity === null && $stock_quantity_meta !== '' && $stock_quantity_meta !== null ) {
		$stock_quantity = (int) $stock_quantity_meta;
	}
	if ( empty( $stock_status ) && ! empty( $stock_status_meta ) ) {
		$stock_status = $stock_status_meta;
	}
	if ( $manage_stock === null && $manage_stock_meta !== '' ) {
		$manage_stock = wc_string_to_bool( $manage_stock_meta );
	}
	
	// If stock is managed and quantity > 0, make it active
	if ( $manage_stock && $stock_quantity !== null && $stock_quantity > 0 ) {
		return true;
	}
	
	// If stock quantity exists and > 0 (even if manage_stock is false), make it active
	if ( $stock_quantity !== null && $stock_quantity > 0 ) {
		return true;
	}
	
	// If stock status is 'instock' or 'onbackorder', make it active
	if ( in_array( $stock_status, array( 'instock', 'onbackorder' ), true ) ) {
		return true;
	}
	
	// If stock is not managed and product is published, make it active
	if ( ! $manage_stock && $variation->get_status() === 'publish' ) {
		return true;
	}
	
	return $is_active;
}

add_filter( 'woocommerce_variation_is_purchasable', 'wp_augoose_ensure_variation_purchasable_if_in_stock', 1, 2 );
function wp_augoose_ensure_variation_purchasable_if_in_stock( $is_purchasable, $variation ) {
	// Check if variation is valid
	if ( ! $variation || ! is_a( $variation, 'WC_Product_Variation' ) ) {
		return $is_purchasable;
	}
	
	// If already purchasable, return as is
	if ( $is_purchasable ) {
		return $is_purchasable;
	}
	
	// Get stock information - use multiple methods to ensure accuracy
	$stock_quantity = $variation->get_stock_quantity();
	$stock_status = $variation->get_stock_status();
	$manage_stock = $variation->get_manage_stock();
	
	// Also check meta directly as fallback
	$stock_quantity_meta = get_post_meta( $variation->get_id(), '_stock', true );
	$stock_status_meta = get_post_meta( $variation->get_id(), '_stock_status', true );
	$manage_stock_meta = get_post_meta( $variation->get_id(), '_manage_stock', true );
	
	// Use meta values if get methods return null/empty
	if ( $stock_quantity === null && $stock_quantity_meta !== '' && $stock_quantity_meta !== null ) {
		$stock_quantity = (int) $stock_quantity_meta;
	}
	if ( empty( $stock_status ) && ! empty( $stock_status_meta ) ) {
		$stock_status = $stock_status_meta;
	}
	if ( $manage_stock === null && $manage_stock_meta !== '' ) {
		$manage_stock = wc_string_to_bool( $manage_stock_meta );
	}
	
	// If stock is managed and quantity > 0, make it purchasable
	if ( $manage_stock && $stock_quantity !== null && $stock_quantity > 0 ) {
		return true;
	}
	
	// If stock quantity exists and > 0 (even if manage_stock is false), make it purchasable
	if ( $stock_quantity !== null && $stock_quantity > 0 ) {
		return true;
	}
	
	// If stock status is 'instock' or 'onbackorder', make it purchasable
	if ( in_array( $stock_status, array( 'instock', 'onbackorder' ), true ) ) {
		return true;
	}
	
	// If stock is not managed and product is published, make it purchasable
	if ( ! $manage_stock && $variation->get_status() === 'publish' ) {
		return true;
	}
	
	return $is_purchasable;
}

/**
 * Ensure product stock status is correct based on actual stock quantity
 */
add_filter( 'woocommerce_product_is_in_stock', 'wp_augoose_fix_product_stock_status', 1, 2 );
function wp_augoose_fix_product_stock_status( $in_stock, $product ) {
	// Only process variations
	if ( ! is_a( $product, 'WC_Product_Variation' ) ) {
		return $in_stock;
	}
	
	// If already in stock, return as is
	if ( $in_stock ) {
		return $in_stock;
	}
	
	// Get stock quantity - use multiple methods
	$stock_quantity = $product->get_stock_quantity();
	$manage_stock = $product->get_manage_stock();
	
	// Also check meta directly as fallback
	$stock_quantity_meta = get_post_meta( $product->get_id(), '_stock', true );
	$manage_stock_meta = get_post_meta( $product->get_id(), '_manage_stock', true );
	
	// Use meta values if get methods return null/empty
	if ( $stock_quantity === null && $stock_quantity_meta !== '' && $stock_quantity_meta !== null ) {
		$stock_quantity = (int) $stock_quantity_meta;
	}
	if ( $manage_stock === null && $manage_stock_meta !== '' ) {
		$manage_stock = wc_string_to_bool( $manage_stock_meta );
	}
	
	// If stock is managed and quantity > 0, it should be in stock
	if ( $manage_stock && $stock_quantity !== null && $stock_quantity > 0 ) {
		return true;
	}
	
	// If stock quantity exists and > 0 (even if manage_stock is false), it should be in stock
	if ( $stock_quantity !== null && $stock_quantity > 0 ) {
		return true;
	}
	
	return $in_stock;
}

/**
 * Update stock status when stock quantity is set
 * This ensures stock_status is synced with stock_quantity
 */
add_action( 'woocommerce_variation_set_stock_quantity', 'wp_augoose_sync_variation_stock_status', 10, 2 );
function wp_augoose_sync_variation_stock_status( $variation_id, $stock_quantity ) {
	$variation = wc_get_product( $variation_id );
	if ( ! $variation || ! is_a( $variation, 'WC_Product_Variation' ) ) {
		return;
	}
	
	// Only sync if stock is managed
	if ( ! $variation->get_manage_stock() ) {
		return;
	}
	
	// Update stock status based on quantity
	if ( $stock_quantity !== null && $stock_quantity > 0 ) {
		$variation->set_stock_status( 'instock' );
		$variation->save();
	} elseif ( $stock_quantity === 0 || $stock_quantity === null ) {
		// Check if backorders are allowed
		if ( $variation->backorders_allowed() ) {
			$variation->set_stock_status( 'onbackorder' );
		} else {
			$variation->set_stock_status( 'outofstock' );
		}
		$variation->save();
	}
}

/**
 * Ensure variations with stock are not hidden
 * This prevents WooCommerce from hiding variations that have stock
 */
add_filter( 'woocommerce_hide_invisible_variations', 'wp_augoose_show_variations_with_stock', 1, 3 );
function wp_augoose_show_variations_with_stock( $hide, $product_id, $variation ) {
	// If variation has stock, don't hide it
	if ( ! $variation || ! is_a( $variation, 'WC_Product_Variation' ) ) {
		return $hide;
	}
	
	// Get stock information - use multiple methods
	$stock_quantity = $variation->get_stock_quantity();
	$stock_status = $variation->get_stock_status();
	$manage_stock = $variation->get_manage_stock();
	
	// Also check meta directly as fallback
	$stock_quantity_meta = get_post_meta( $variation->get_id(), '_stock', true );
	$stock_status_meta = get_post_meta( $variation->get_id(), '_stock_status', true );
	$manage_stock_meta = get_post_meta( $variation->get_id(), '_manage_stock', true );
	
	// Use meta values if get methods return null/empty
	if ( $stock_quantity === null && $stock_quantity_meta !== '' && $stock_quantity_meta !== null ) {
		$stock_quantity = (int) $stock_quantity_meta;
	}
	if ( empty( $stock_status ) && ! empty( $stock_status_meta ) ) {
		$stock_status = $stock_status_meta;
	}
	if ( $manage_stock === null && $manage_stock_meta !== '' ) {
		$manage_stock = wc_string_to_bool( $manage_stock_meta );
	}
	
	// If stock is managed and quantity > 0, don't hide
	if ( $manage_stock && $stock_quantity !== null && $stock_quantity > 0 ) {
		return false;
	}
	
	// If stock quantity exists and > 0 (even if manage_stock is false), don't hide
	if ( $stock_quantity !== null && $stock_quantity > 0 ) {
		return false;
	}
	
	// If stock status is 'instock' or 'onbackorder', don't hide
	if ( in_array( $stock_status, array( 'instock', 'onbackorder' ), true ) ) {
		return false;
	}
	
	return $hide;
}

/**
 * Ensure variations with stock are always visible
 */
add_filter( 'woocommerce_product_variation_is_visible', 'wp_augoose_ensure_variation_visible_if_in_stock', 1, 2 );
function wp_augoose_ensure_variation_visible_if_in_stock( $is_visible, $variation_id ) {
	$variation = wc_get_product( $variation_id );
	if ( ! $variation || ! is_a( $variation, 'WC_Product_Variation' ) ) {
		return $is_visible;
	}
	
	// If already visible, return as is
	if ( $is_visible ) {
		return $is_visible;
	}
	
	// Get stock information - use multiple methods
	$stock_quantity = $variation->get_stock_quantity();
	$stock_status = $variation->get_stock_status();
	$manage_stock = $variation->get_manage_stock();
	
	// Also check meta directly as fallback
	$stock_quantity_meta = get_post_meta( $variation_id, '_stock', true );
	$stock_status_meta = get_post_meta( $variation_id, '_stock_status', true );
	$manage_stock_meta = get_post_meta( $variation_id, '_manage_stock', true );
	
	// Use meta values if get methods return null/empty
	if ( $stock_quantity === null && $stock_quantity_meta !== '' && $stock_quantity_meta !== null ) {
		$stock_quantity = (int) $stock_quantity_meta;
	}
	if ( empty( $stock_status ) && ! empty( $stock_status_meta ) ) {
		$stock_status = $stock_status_meta;
	}
	if ( $manage_stock === null && $manage_stock_meta !== '' ) {
		$manage_stock = wc_string_to_bool( $manage_stock_meta );
	}
	
	// If stock is managed and quantity > 0, make it visible
	if ( $manage_stock && $stock_quantity !== null && $stock_quantity > 0 ) {
		return true;
	}
	
	// If stock quantity exists and > 0 (even if manage_stock is false), make it visible
	if ( $stock_quantity !== null && $stock_quantity > 0 ) {
		return true;
	}
	
	// If stock status is 'instock' or 'onbackorder', make it visible
	if ( in_array( $stock_status, array( 'instock', 'onbackorder' ), true ) ) {
		return true;
	}
	
	return $is_visible;
}

/**
 * Force include variations with stock in available variations list
 * This ensures variations with stock are always included even if they're marked as invisible
 */
add_filter( 'woocommerce_product_get_children', 'wp_augoose_include_variations_with_stock', 1, 2 );
function wp_augoose_include_variations_with_stock( $children, $product ) {
	// Only process variable products
	if ( ! is_a( $product, 'WC_Product_Variable' ) ) {
		return $children;
	}
	
	// Get all variation IDs (including those that might be filtered out)
	$all_variation_ids = $product->get_children();
	
	// Check each variation and ensure those with stock are included
	foreach ( $all_variation_ids as $variation_id ) {
		// Skip if already in children
		if ( in_array( $variation_id, $children, true ) ) {
			continue;
		}
		
		$variation = wc_get_product( $variation_id );
		if ( ! $variation || ! is_a( $variation, 'WC_Product_Variation' ) ) {
			continue;
		}
		
		// Get stock information - use multiple methods
		$stock_quantity = $variation->get_stock_quantity();
		$stock_status = $variation->get_stock_status();
		$manage_stock = $variation->get_manage_stock();
		
		// Also check meta directly as fallback
		$stock_quantity_meta = get_post_meta( $variation_id, '_stock', true );
		$stock_status_meta = get_post_meta( $variation_id, '_stock_status', true );
		$manage_stock_meta = get_post_meta( $variation_id, '_manage_stock', true );
		
		// Use meta values if get methods return null/empty
		if ( $stock_quantity === null && $stock_quantity_meta !== '' && $stock_quantity_meta !== null ) {
			$stock_quantity = (int) $stock_quantity_meta;
		}
		if ( empty( $stock_status ) && ! empty( $stock_status_meta ) ) {
			$stock_status = $stock_status_meta;
		}
		if ( $manage_stock === null && $manage_stock_meta !== '' ) {
			$manage_stock = wc_string_to_bool( $manage_stock_meta );
		}
		
		// Check if variation has stock
		$has_stock = false;
		
		// If stock is managed and quantity > 0
		if ( $manage_stock && $stock_quantity !== null && $stock_quantity > 0 ) {
			$has_stock = true;
		}
		
		// If stock quantity exists and > 0 (even if manage_stock is false)
		if ( $stock_quantity !== null && $stock_quantity > 0 ) {
			$has_stock = true;
		}
		
		// If stock status is 'instock' or 'onbackorder'
		if ( in_array( $stock_status, array( 'instock', 'onbackorder' ), true ) ) {
			$has_stock = true;
		}
		
		// If variation has stock, include it in children
		if ( $has_stock ) {
			$children[] = $variation_id;
		}
	}
	
	return array_unique( $children );
}

/**
 * Ensure variations with stock are included in available variations
 */
add_filter( 'woocommerce_available_variation', 'wp_augoose_ensure_variation_available_if_in_stock', 1, 3 );
function wp_augoose_ensure_variation_available_if_in_stock( $variation_data, $product, $variation ) {
	// Check if variation is valid
	if ( ! $variation || ! is_a( $variation, 'WC_Product_Variation' ) ) {
		return $variation_data;
	}
	
	// Get stock information - use multiple methods
	$stock_quantity = $variation->get_stock_quantity();
	$stock_status = $variation->get_stock_status();
	$manage_stock = $variation->get_manage_stock();
	
	// Also check meta directly as fallback
	$stock_quantity_meta = get_post_meta( $variation->get_id(), '_stock', true );
	$stock_status_meta = get_post_meta( $variation->get_id(), '_stock_status', true );
	$manage_stock_meta = get_post_meta( $variation->get_id(), '_manage_stock', true );
	
	// Use meta values if get methods return null/empty
	if ( $stock_quantity === null && $stock_quantity_meta !== '' && $stock_quantity_meta !== null ) {
		$stock_quantity = (int) $stock_quantity_meta;
	}
	if ( empty( $stock_status ) && ! empty( $stock_status_meta ) ) {
		$stock_status = $stock_status_meta;
	}
	if ( $manage_stock === null && $manage_stock_meta !== '' ) {
		$manage_stock = wc_string_to_bool( $manage_stock_meta );
	}
	
	// Check if variation has stock
	$has_stock = false;
	
	// If stock is managed and quantity > 0
	if ( $manage_stock && $stock_quantity !== null && $stock_quantity > 0 ) {
		$has_stock = true;
	}
	
	// If stock quantity exists and > 0 (even if manage_stock is false)
	if ( $stock_quantity !== null && $stock_quantity > 0 ) {
		$has_stock = true;
	}
	
	// If stock status is 'instock' or 'onbackorder'
	if ( in_array( $stock_status, array( 'instock', 'onbackorder' ), true ) ) {
		$has_stock = true;
	}
	
	// If variation has stock, ensure it's available
	if ( $has_stock ) {
		// Initialize variation_data if empty
		if ( empty( $variation_data ) ) {
			$variation_data = array();
		}
		
		// Force all availability flags to true
		$variation_data['is_purchasable'] = true;
		$variation_data['is_in_stock'] = true;
		$variation_data['variation_is_active'] = true;
		$variation_data['variation_is_visible'] = true;
	}
	
	return $variation_data;
}
