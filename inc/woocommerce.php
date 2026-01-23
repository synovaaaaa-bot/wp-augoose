<?php
/**
 * WooCommerce Compatibility File
 *
 * @package WP_Augoose
 */

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
		while ( $q->have_posts() ) {
			$q->the_post();
			$product = wc_get_product( get_the_ID() );
			if ( ! $product ) {
				continue;
			}
			$pid   = $product->get_id();
			$link  = get_permalink( $pid );
			$img   = $product->get_image( 'woocommerce_thumbnail' );
			$price = $product->get_price_html();
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
					<div class="wishlist-item-price"><?php echo wp_kses_post( $price ); ?></div>
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
	check_ajax_referer( 'wp_augoose_nonce', 'nonce' );
	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_send_json_error( array( 'message' => 'WooCommerce not available' ) );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid product' ) );
	}

	$ids = wp_augoose_wishlist_get_ids();
	if ( in_array( $product_id, $ids, true ) ) {
		$ids = array_values( array_diff( $ids, array( $product_id ) ) );
		$action = 'removed';
	} else {
		array_unshift( $ids, $product_id );
		$ids    = array_values( array_unique( $ids ) );
		$action = 'added';
	}
	wp_augoose_wishlist_set_ids( $ids );

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
	check_ajax_referer( 'wp_augoose_nonce', 'nonce' );
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
			'Lihat keranjang',
			'Lanjutkan belanja',
			'Lihat Keranjang',
			'Lanjutkan Belanja',
		),
		array(
			'has been added to your cart',
			'Has been added to your cart',
			'Added to your cart',
			'View cart',
			'Continue shopping',
			'View cart',
			'Continue shopping',
		),
		$message
	);
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
		),
		array(
			'has been added to your cart',
			'Has been added to your cart',
			'Added to your cart',
		),
		$text
	);
	return $text;
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
	
	// Process billing fields and add Secondary Name
	if ( isset( $fields['billing'] ) ) {
		// Add Secondary Name field after First Name
		$first_name_priority = isset( $fields['billing']['billing_first_name']['priority'] ) ? $fields['billing']['billing_first_name']['priority'] : 10;
		
		$fields['billing']['billing_secondary_name'] = array(
			'label'       => 'Secondary name',
			'placeholder' => 'Secondary name (optional)',
			'required'    => false,
			'class'       => array( 'form-row-wide' ),
			'priority'    => $first_name_priority + 1,
			'type'        => 'text',
		);
		
		foreach ( $fields['billing'] as $key => $field ) {
			$field_key = str_replace( 'billing_', '', $key );
			
			// Skip secondary_name from English mapping (already set above)
			if ( $field_key === 'secondary_name' ) {
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
	
	// Process order fields
	if ( isset( $fields['order'] ) && isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label'] = 'Order notes';
		$fields['order']['order_comments']['placeholder'] = 'Notes about your order, e.g. special notes for delivery.';
	}
	
	return $fields;
}

/**
 * Force country names to English
 */
add_filter( 'woocommerce_countries', 'wp_augoose_countries_english', 20 );
function wp_augoose_countries_english( $countries ) {
	// Common country name translations from Indonesian to English
	$country_translations = array(
		'Amerika Serikat' => 'United States',
		'Inggris' => 'United Kingdom',
		'Inggris Raya' => 'United Kingdom',
		'Singapura' => 'Singapore',
		'Jepang' => 'Japan',
		'Korea Selatan' => 'South Korea',
		'Cina' => 'China',
		'Filipina' => 'Philippines',
	);
	
	foreach ( $countries as $code => $name ) {
		if ( isset( $country_translations[ $name ] ) ) {
			$countries[ $code ] = $country_translations[ $name ];
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
						);
						if ( isset( $indonesian_labels[ $field_data['label'] ] ) ) {
							$locale[ $country ][ $field_key ]['label'] = $indonesian_labels[ $field_data['label'] ];
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
						);
						if ( isset( $indonesian_placeholders[ $field_data['placeholder'] ] ) ) {
							$locale[ $country ][ $field_key ]['placeholder'] = $indonesian_placeholders[ $field_data['placeholder'] ];
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

function wp_augoose_render_wishlist_sidebar() {
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
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
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
                <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="cart-sidebar-btn cart-sidebar-btn-checkout">PEMBAYARAN</a>
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
				
				// Size guide link - open modal
				$(document).on("click", ".size-guide-link", function(e) {
					e.preventDefault();
					$("#size-guide-modal").fadeIn(300);
					$("body").css("overflow", "hidden");
				});
				
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
	
	// Calculate discount percentage for variable products
	$percentage = '';
	if ( $product->is_type( 'variable' ) ) {
		$regular_price = $product->get_variation_regular_price( 'max' );
		$sale_price = $product->get_variation_sale_price( 'min' );
	} else {
		$regular_price = $product->get_regular_price();
		$sale_price = $product->get_sale_price();
	}
	
	if ( $regular_price && $sale_price ) {
		$percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
	}
	
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

add_filter( 'woocommerce_get_item_data', 'wp_augoose_format_cart_item_data', 10, 2 );

/**
 * Format cart item data: Hide Market attribute and simplify colon format
 */
function wp_augoose_format_cart_item_data( $item_data, $cart_item ) {
	if ( ! is_array( $item_data ) ) {
		return $item_data;
	}
	
	$filtered_data = array();
	
	foreach ( $item_data as $data ) {
		// Skip Market attribute
		$key = isset( $data['key'] ) ? strtolower( $data['key'] ) : '';
		if ( strpos( $key, 'market' ) !== false ) {
			continue; // Hide Market
		}
		
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
 * Save Secondary Name to order meta
 */
add_action( 'woocommerce_checkout_update_order_meta', 'wp_augoose_save_secondary_name_to_order', 10, 1 );
function wp_augoose_save_secondary_name_to_order( $order_id ) {
	if ( ! empty( $_POST['billing_secondary_name'] ) ) {
		$secondary_name = sanitize_text_field( $_POST['billing_secondary_name'] );
		update_post_meta( $order_id, '_billing_secondary_name', $secondary_name );
	}
}

/**
 * Display Secondary Name in Admin Order Details
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'wp_augoose_display_secondary_name_in_admin', 10, 1 );
function wp_augoose_display_secondary_name_in_admin( $order ) {
	$secondary_name = get_post_meta( $order->get_id(), '_billing_secondary_name', true );
	if ( ! empty( $secondary_name ) ) {
		echo '<p><strong>Secondary name:</strong> ' . esc_html( $secondary_name ) . '</p>';
	}
}

/**
 * Remove/Hide Newsletter Checkbox
 */
add_filter( 'woocommerce_checkout_newsletter_subscription_text', '__return_empty_string', 999 );
add_filter( 'woocommerce_registration_newsletter_subscription_text', '__return_empty_string', 999 );
add_action( 'wp_footer', 'wp_augoose_hide_newsletter_checkbox' );
function wp_augoose_hide_newsletter_checkbox() {
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
	// Get Terms page URL - try multiple methods
	$terms_url = '';
	
	// Method 1: WooCommerce Terms page setting
	$terms_page_id = wc_get_page_id( 'terms' );
	if ( $terms_page_id ) {
		$terms_url = get_permalink( $terms_page_id );
	}
	
	// Method 2: Try to find page by slug
	if ( empty( $terms_url ) ) {
		$terms_page = get_page_by_path( 'terms-of-service' );
		if ( $terms_page ) {
			$terms_url = get_permalink( $terms_page->ID );
		}
	}
	
	// Method 3: Try alternative slug
	if ( empty( $terms_url ) ) {
		$terms_page = get_page_by_path( 'terms' );
		if ( $terms_page ) {
			$terms_url = get_permalink( $terms_page->ID );
		}
	}
	
	// Fallback to fixed URL
	if ( empty( $terms_url ) ) {
		$terms_url = home_url( '/terms-of-service/' );
	}
	
	?>
	<p class="form-row validate-required terms-checkbox-custom" id="terms_checkbox_field">
		<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
			<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms_custom" id="terms_custom" value="1" <?php checked( isset( $_POST['terms_custom'] ), true ); ?> />
			<span class="woocommerce-form__label-text">
				Agree for <a href="<?php echo esc_url( $terms_url ); ?>" target="_blank">Terms & Conditions</a>
			</span>
			<span class="required">*</span>
		</label>
	</p>
	<?php
}

/**
 * Validate Terms Checkbox
 */
add_action( 'woocommerce_checkout_process', 'wp_augoose_validate_terms_checkbox' );
function wp_augoose_validate_terms_checkbox() {
	if ( empty( $_POST['terms_custom'] ) ) {
		wc_add_notice( 'Please confirm that you have read the Terms of Service.', 'error' );
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
				
				// Auto update cart on quantity change
				$(".cart .quantity input[type=number]").on("change", function() {
					var $form = $(this).closest("form.woocommerce-cart-form");
					if ($form.length) {
						setTimeout(function() {
							$form.find("button[name=update_cart]").trigger("click");
						}, 500);
					}
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
	check_ajax_referer( 'wp_augoose_nonce', 'nonce' );
	if ( function_exists( 'wc_clear_notices' ) ) {
		wc_clear_notices();
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$quantity   = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;

	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => 'Invalid product ID.' ) );
	}

	// Check if product exists
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		wp_send_json_error( array( 'message' => 'Product not found.' ) );
	}

	// Check if product is purchasable
	if ( ! $product->is_purchasable() ) {
		wp_send_json_error( array( 'message' => 'Product is not purchasable.' ) );
	}

	// Check if product is in stock
	if ( ! $product->is_in_stock() ) {
		wp_send_json_error( array( 'message' => 'Product is out of stock.' ) );
	}

	// Variable products need options (variation id)
	if ( $product->is_type( 'variable' ) ) {
		wp_send_json_error(
			array(
				'message'     => 'Please choose product options by visiting the product page.',
				'product_url' => get_permalink( $product_id ),
			)
		);
	}

	// Add to cart
	$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity );

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

		wp_send_json_success( $data );
	} else {
		if ( function_exists( 'wc_clear_notices' ) ) {
			wc_clear_notices();
		}
		wp_send_json_error( array( 'message' => 'Failed to add product to cart.' ) );
	}
}

/**
 * AJAX handler for updating cart quantity on checkout
 */
add_action( 'wp_ajax_update_checkout_quantity', 'wp_augoose_update_checkout_quantity' );
add_action( 'wp_ajax_nopriv_update_checkout_quantity', 'wp_augoose_update_checkout_quantity' );
function wp_augoose_update_checkout_quantity() {
	// Verify nonce
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'woocommerce-cart' ) ) {
		wp_send_json_error( array( 'message' => 'Security check failed.' ) );
		return;
	}
	
	$cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( $_POST['cart_key'] ) : '';
	$quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 0;
	
	if ( ! $cart_key ) {
		wp_send_json_error( array( 'message' => 'Invalid cart item.' ) );
		return;
	}
	
	// Update cart
	if ( $quantity === 0 ) {
		// Remove item
		WC()->cart->remove_cart_item( $cart_key );
	} else {
		// Update quantity
		WC()->cart->set_quantity( $cart_key, $quantity, true );
	}
	
	// Calculate totals
	WC()->cart->calculate_totals();
	
	wp_send_json_success( array(
		'message' => 'Cart updated',
		'cart_hash' => WC()->cart->get_cart_hash(),
	) );
}

// Checkout field layout is controlled by the theme templates + `functions.php`.
// Do NOT add CSS/JS here that hides fields; it breaks WooCommerce + WPML checkout flows.
