<?php
/**
 * WP Augoose Theme Functions
 *
 * @package WP_Augoose
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Theme Setup
 */
function wp_augoose_setup() {
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'wp-augoose' ),
        'footer'  => __( 'Footer Menu', 'wp-augoose' ),
    ) );
    
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );
    
    add_theme_support( 'customize-selective-refresh-widgets' );
    
    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ) );
    
    // WooCommerce support
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'wp_augoose_setup' );

/**
 * Customizer: Announcement / Discount text (integrated with WP; header prefers Woo Store Notice if enabled).
 */
function wp_augoose_customize_register( $wp_customize ) {
    $wp_customize->add_setting(
        'wp_augoose_announcement_text',
        array(
            'default'           => 'INTERNATIONAL &amp; DOMESTIC SHIPPING AVAILABLE – FREE SHIPPING OVER $200',
            'sanitize_callback' => 'wp_kses_post',
            'transport'         => 'refresh',
        )
    );

    $wp_customize->add_control(
        'wp_augoose_announcement_text',
        array(
            'type'        => 'textarea',
            'section'     => 'title_tagline',
            'label'       => __( 'Announcement / Discount text', 'wp-augoose' ),
            'description' => __( 'Shown in the top announcement bar. If WooCommerce “Store notice” is enabled, that text will override this.', 'wp-augoose' ),
        )
    );

    // New arrival badge settings (integrated with WP Customizer; used in product cards).
    $wp_customize->add_setting(
        'wp_augoose_new_arrival_days',
        array(
            'default'           => 30,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'wp_augoose_new_arrival_days',
        array(
            'type'        => 'number',
            'section'     => 'title_tagline',
            'label'       => __( 'New Arrival: days threshold', 'wp-augoose' ),
            'description' => __( 'Show “New Arrival” badge for products published within this many days. Set 0 to disable.', 'wp-augoose' ),
            'input_attrs' => array(
                'min'  => 0,
                'max'  => 365,
                'step' => 1,
            ),
        )
    );

    $wp_customize->add_setting(
        'wp_augoose_new_arrival_label',
        array(
            'default'           => 'New Arrival',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        )
    );
    $wp_customize->add_control(
        'wp_augoose_new_arrival_label',
        array(
            'type'    => 'text',
            'section' => 'title_tagline',
            'label'   => __( 'New Arrival: label text', 'wp-augoose' ),
        )
    );

    /**
     * Homepage Hero Image
     */
    $wp_customize->add_setting(
        'wp_augoose_home_hero_image',
        array(
            'default'           => '',
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        )
    );

    $wp_customize->add_control(
        new WP_Customize_Media_Control(
            $wp_customize,
            'wp_augoose_home_hero_image',
            array(
                'section'     => 'title_tagline',
                'label'       => __( 'Homepage hero image', 'wp-augoose' ),
                'description' => __( 'Background image for the homepage hero section.', 'wp-augoose' ),
                'mime_type'   => 'image',
            )
        )
    );
}
add_action( 'customize_register', 'wp_augoose_customize_register' );

/**
 * Admin-only debug: show enqueued style handles + URLs in HTML comments (helps diagnose broken layouts after migration).
 */
add_action(
	'wp_footer',
	function () {
		if ( ! isset( $_GET['augoose_debug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		global $wp_styles;
		if ( ! isset( $wp_styles ) || ! is_object( $wp_styles ) || ! property_exists( $wp_styles, 'queue' ) ) {
			return;
		}

		echo "\n<!-- wp-augoose debug: styles queue\n";
		foreach ( (array) $wp_styles->queue as $handle ) {
			$src = '';
			if ( isset( $wp_styles->registered[ $handle ] ) && isset( $wp_styles->registered[ $handle ]->src ) ) {
				$src = (string) $wp_styles->registered[ $handle ]->src;
			}
			echo esc_html( $handle . ': ' . $src ) . "\n";
		}
		echo "-->\n";
	},
	9999
);

/**
 * Admin-only debug (console): logs which CSS/JS is actually enqueued on the live site.
 * Usage: open any page as admin with `?augoose_debug=1` and check DevTools Console.
 */
add_action(
	'wp_footer',
	function () {
		if ( ! isset( $_GET['augoose_debug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		global $wp_styles, $wp_scripts;
		$styles = array();
		$scripts = array();

		if ( isset( $wp_styles ) && isset( $wp_styles->queue ) ) {
			foreach ( (array) $wp_styles->queue as $handle ) {
				$src = '';
				if ( isset( $wp_styles->registered[ $handle ] ) && isset( $wp_styles->registered[ $handle ]->src ) ) {
					$src = (string) $wp_styles->registered[ $handle ]->src;
				}
				$styles[] = array(
					'handle' => $handle,
					'src'    => $src,
				);
			}
		}

		if ( isset( $wp_scripts ) && isset( $wp_scripts->queue ) ) {
			foreach ( (array) $wp_scripts->queue as $handle ) {
				$src = '';
				if ( isset( $wp_scripts->registered[ $handle ] ) && isset( $wp_scripts->registered[ $handle ]->src ) ) {
					$src = (string) $wp_scripts->registered[ $handle ]->src;
				}
				$scripts[] = array(
					'handle' => $handle,
					'src'    => $src,
				);
			}
		}

		$payload = wp_json_encode(
			array(
				'theme'   => wp_get_theme()->get( 'Name' ),
				'version' => wp_get_theme()->get( 'Version' ),
				'styles'  => $styles,
				'scripts' => $scripts,
			)
		);
		?>
		<script id="wp-augoose-debug-assets">
		// <![CDATA[
		try {
			console.log("wp-augoose debug: assets", <?php echo $payload; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>);
		} catch (e) {}
		// ]]>
		</script>
		<?php
	},
	9999
);

/**
 * Enqueue scripts and styles
 */
function wp_augoose_scripts() {
    $theme_dir     = get_template_directory();
    $theme_dir_uri = get_template_directory_uri();

    $asset_ver = static function ( $relative_path ) use ( $theme_dir ) {
        $full = $theme_dir . '/' . ltrim( $relative_path, '/' );
        return file_exists( $full ) ? (string) filemtime( $full ) : '1.0.0';
    };

    wp_enqueue_style( 'wp-augoose-style', get_stylesheet_uri(), array(), $asset_ver( 'style.css' ) );
    
    // Custom CSS files
    if ( file_exists( $theme_dir . '/assets/css/header.css' ) ) {
        wp_enqueue_style( 'wp-augoose-header', $theme_dir_uri . '/assets/css/header.css', array(), $asset_ver( 'assets/css/header.css' ) );
    }
    if ( file_exists( $theme_dir . '/assets/css/footer.css' ) ) {
        wp_enqueue_style( 'wp-augoose-footer', $theme_dir_uri . '/assets/css/footer.css', array(), $asset_ver( 'assets/css/footer.css' ) );
    }
    if ( file_exists( $theme_dir . '/assets/css/homepage.css' ) ) {
        wp_enqueue_style( 'wp-augoose-homepage', $theme_dir_uri . '/assets/css/homepage.css', array(), $asset_ver( 'assets/css/homepage.css' ) );
    }
    if ( file_exists( $theme_dir . '/assets/css/brand-guidelines.css' ) ) {
        wp_enqueue_style( 'wp-augoose-brand', $theme_dir_uri . '/assets/css/brand-guidelines.css', array(), $asset_ver( 'assets/css/brand-guidelines.css' ) );
    }
    
    // WooCommerce Integrated Styles - Fully integrated with WooCommerce & WordPress
    // Ensure it loads AFTER any legacy WooCommerce custom styles to avoid conflicts.
    if ( class_exists( 'WooCommerce' ) && file_exists( $theme_dir . '/assets/css/woocommerce-integrated.css' ) ) {
        wp_enqueue_style(
            'wp-augoose-woocommerce-integrated',
            $theme_dir_uri . '/assets/css/woocommerce-integrated.css',
            array( 'wp-augoose-woocommerce-custom' ),
            $asset_ver( 'assets/css/woocommerce-integrated.css' )
        );
        wp_style_add_data( 'wp-augoose-woocommerce-integrated', 'priority', 'high' );
    }
    
    // Homepage override styles (MAXIMUM priority - load absolutely last)
    if ( file_exists( $theme_dir . '/assets/css/homepage-override.css' ) ) {
        wp_enqueue_style( 'wp-augoose-homepage-override', $theme_dir_uri . '/assets/css/homepage-override.css', array( 'wp-augoose-woocommerce', 'wp-augoose-homepage' ), $asset_ver( 'assets/css/homepage-override.css' ) );
        
        // Add critical inline CSS to ensure override
        wp_add_inline_style( 'wp-augoose-homepage-override', '
            /* EMERGENCY OVERRIDE */
            html body .latest-collection .woocommerce ul.products.columns-4 { 
                display: grid !important; 
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important; 
                flex-direction: unset !important;
                align-items: unset !important;
                flex-wrap: unset !important;
            }
            html body .latest-collection .woocommerce ul.products.columns-4 li.product { 
                width: auto !important; 
                margin: 0 !important; 
                float: none !important;
                display: block !important;
                flex: none !important;
            }
        ' );
    }
    
    // NUCLEAR OPTION - Load last with highest priority
    if ( file_exists( $theme_dir . '/assets/css/nuclear-override.css' ) ) {
        wp_enqueue_style( 'wp-augoose-nuclear', $theme_dir_uri . '/assets/css/nuclear-override.css', array(), $asset_ver( 'assets/css/nuclear-override.css' ), 'all' );
        wp_style_add_data( 'wp-augoose-nuclear', 'priority', 'high' );
    }
    
    // Product Cards Fixed - Image Full & Typography Better
    if ( file_exists( $theme_dir . '/assets/css/product-card-fixed.css' ) ) {
        wp_enqueue_style( 'wp-augoose-product-fixed', $theme_dir_uri . '/assets/css/product-card-fixed.css', array(), $asset_ver( 'assets/css/product-card-fixed.css' ), 'all' );
    }
    
    // Cart page - redirect to shop, menggunakan cart sidebar saja
    // No CSS needed for cart page karena sudah redirect
    
    // Single Product Fixed - Sale Badge di dalam gambar
    if ( class_exists( 'WooCommerce' ) && ( function_exists( 'is_product' ) && is_product() ) ) {
        if ( file_exists( $theme_dir . '/assets/css/single-product-fixed.css' ) ) {
            wp_enqueue_style( 'wp-augoose-single-product-fixed', $theme_dir_uri . '/assets/css/single-product-fixed.css', array(), $asset_ver( 'assets/css/single-product-fixed.css' ), 'all' );
            wp_style_add_data( 'wp-augoose-single-product-fixed', 'priority', 'high' );
        }
        // Product Gallery Fixed - Thumbnail di Bawah, No Zoom
        // Load LAST untuk override semua styling lain
        if ( file_exists( $theme_dir . '/assets/css/product-gallery-fixed.css' ) ) {
            wp_enqueue_style( 
                'wp-augoose-product-gallery-fixed', 
                $theme_dir_uri . '/assets/css/product-gallery-fixed.css', 
                array( 'wp-augoose-woocommerce-custom', 'wp-augoose-woocommerce-integrated' ), // Load after both
                $asset_ver( 'assets/css/product-gallery-fixed.css' ), 
                'all' 
            );
            wp_style_add_data( 'wp-augoose-product-gallery-fixed', 'priority', 'high' );
        }
        // Product Gallery Navigation - Auto Slide dengan Arrow
        if ( file_exists( $theme_dir . '/assets/js/product-gallery-nav.js' ) ) {
            wp_enqueue_script( 'wp-augoose-product-gallery-nav', $theme_dir_uri . '/assets/js/product-gallery-nav.js', array( 'jquery' ), $asset_ver( 'assets/js/product-gallery-nav.js' ), true );
        }
    }
    
    // Shop Page Fixed - 4 Columns, 12 Products, Pagination, Filter Styling
    if ( class_exists( 'WooCommerce' ) && ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product_tag() ) ) ) {
        if ( file_exists( $theme_dir . '/assets/css/shop-page-fixed.css' ) ) {
            wp_enqueue_style( 'wp-augoose-shop-fixed', $theme_dir_uri . '/assets/css/shop-page-fixed.css', array(), $asset_ver( 'assets/css/shop-page-fixed.css' ), 'all' );
            wp_style_add_data( 'wp-augoose-shop-fixed', 'priority', 'high' );
        }
    }
    
    // Cart Sidebar - Detailed & Compact
    if ( class_exists( 'WooCommerce' ) ) {
        if ( file_exists( $theme_dir . '/assets/css/cart-sidebar-detailed.css' ) ) {
            wp_enqueue_style( 'wp-augoose-cart-sidebar-detailed', $theme_dir_uri . '/assets/css/cart-sidebar-detailed.css', array(), $asset_ver( 'assets/css/cart-sidebar-detailed.css' ), 'all' );
        }
        // Wishlist Sidebar (UI)
        if ( file_exists( $theme_dir . '/assets/css/wishlist-sidebar.css' ) ) {
            wp_enqueue_style( 'wp-augoose-wishlist-sidebar', $theme_dir_uri . '/assets/css/wishlist-sidebar.css', array(), $asset_ver( 'assets/css/wishlist-sidebar.css' ), 'all' );
        }
        if ( file_exists( $theme_dir . '/assets/js/cart-sidebar-detailed.js' ) ) {
            wp_enqueue_script( 'wp-augoose-cart-sidebar-detailed', $theme_dir_uri . '/assets/js/cart-sidebar-detailed.js', array( 'jquery' ), $asset_ver( 'assets/js/cart-sidebar-detailed.js' ), true );
            wp_localize_script( 'wp-augoose-cart-sidebar-detailed', 'wc_add_to_cart_params', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'cart_url' => wc_get_cart_url(),
                'update_cart_nonce' => wp_create_nonce( 'woocommerce-cart' ),
            ) );
        }
        // Keep cart-sidebar.js for open/close functionality
        if ( file_exists( $theme_dir . '/assets/js/cart-sidebar.js' ) ) {
            wp_enqueue_script( 'wp-augoose-cart-sidebar', $theme_dir_uri . '/assets/js/cart-sidebar.js', array( 'jquery' ), $asset_ver( 'assets/js/cart-sidebar.js' ), true );
            wp_localize_script( 'wp-augoose-cart-sidebar', 'wc_add_to_cart_params', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
            ) );
        }
        if ( file_exists( $theme_dir . '/assets/js/wishlist-sidebar.js' ) ) {
            wp_enqueue_script( 'wp-augoose-wishlist-sidebar', $theme_dir_uri . '/assets/js/wishlist-sidebar.js', array( 'jquery', 'wp-augoose-main' ), $asset_ver( 'assets/js/wishlist-sidebar.js' ), true );
        }
    }
    
    // Checkout Coupon - Apply Coupon
    if ( class_exists( 'WooCommerce' ) && ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
        if ( file_exists( $theme_dir . '/assets/js/checkout-coupon.js' ) ) {
            wp_enqueue_script( 'wp-augoose-checkout-coupon', $theme_dir_uri . '/assets/js/checkout-coupon.js', array( 'jquery', 'wc-checkout' ), $asset_ver( 'assets/js/checkout-coupon.js' ), true );
            wp_localize_script( 'wp-augoose-checkout-coupon', 'wc_checkout_params', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'apply_coupon_nonce' => wp_create_nonce( 'apply-coupon' ),
            ) );
        }
        // Checkout Quantity Selector
        if ( file_exists( $theme_dir . '/assets/js/checkout-quantity.js' ) ) {
            wp_enqueue_script( 'wp-augoose-checkout-quantity', $theme_dir_uri . '/assets/js/checkout-quantity.js', array( 'jquery', 'wc-checkout' ), $asset_ver( 'assets/js/checkout-quantity.js' ), true );
            wp_localize_script( 'wp-augoose-checkout-quantity', 'wc_checkout_params', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'update_cart_nonce' => wp_create_nonce( 'woocommerce-cart' ),
            ) );
        }
    }
    
    // Checkout styling now handled by woocommerce-integrated.css
    // No separate checkout CSS file needed
    
    // Main JavaScript
    if ( file_exists( $theme_dir . '/assets/js/main.js' ) ) {
        wp_enqueue_script( 'wp-augoose-main', $theme_dir_uri . '/assets/js/main.js', array( 'jquery' ), $asset_ver( 'assets/js/main.js' ), true );
        wp_localize_script(
            'wp-augoose-main',
            'wpAugoose',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wp_augoose_nonce' ),
            )
        );
    }

    // Variation swatches (single product)
    if ( class_exists( 'WooCommerce' ) && function_exists( 'is_product' ) && is_product() && file_exists( $theme_dir . '/assets/js/variation-swatches.js' ) ) {
        wp_enqueue_script(
            'wp-augoose-variation-swatches',
            $theme_dir_uri . '/assets/js/variation-swatches.js',
            array( 'jquery' ),
            $asset_ver( 'assets/js/variation-swatches.js' ),
            true
        );
    }

    // Shop view toggle (grid/list)
    if ( class_exists( 'WooCommerce' ) && ( function_exists( 'is_shop' ) && is_shop() || function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) ) {
        if ( file_exists( $theme_dir . '/assets/js/shop-view-toggle.js' ) ) {
            wp_enqueue_script(
                'wp-augoose-shop-view',
                $theme_dir_uri . '/assets/js/shop-view-toggle.js',
                array( 'jquery' ),
                $asset_ver( 'assets/js/shop-view-toggle.js' ),
                true
            );
        }
    }
    
    // Simple Product Interactions
    if ( file_exists( $theme_dir . '/assets/js/simple-interactions.js' ) ) {
        wp_enqueue_script( 'wp-augoose-simple-interactions', $theme_dir_uri . '/assets/js/simple-interactions.js', array( 'jquery' ), $asset_ver( 'assets/js/simple-interactions.js' ), true );
    }
    
    // Product Image Swatcher - Color swatches change product image
    if ( file_exists( $theme_dir . '/assets/js/product-image-swatcher.js' ) ) {
        wp_enqueue_script( 'wp-augoose-image-swatcher', $theme_dir_uri . '/assets/js/product-image-swatcher.js', array( 'jquery' ), $asset_ver( 'assets/js/product-image-swatcher.js' ), true );
    }
    
    // Product Tabs - Tab switching on single product pages
    if ( class_exists( 'WooCommerce' ) && is_product() ) {
        if ( file_exists( $theme_dir . '/assets/js/product-tabs.js' ) ) {
            wp_enqueue_script( 'wp-augoose-product-tabs', $theme_dir_uri . '/assets/js/product-tabs.js', array( 'jquery' ), $asset_ver( 'assets/js/product-tabs.js' ), true );
        }
    }
    
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'wp_augoose_scripts' );

/**
 * Inject category links into the center menu (Jackets / Pants / Shirts)
 * If the Primary menu already contains them, we won't duplicate.
 */
function wp_augoose_inject_primary_category_links( $items, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $items;
	}
	if ( ! class_exists( 'WooCommerce' ) ) {
		return $items;
	}

	$targets = array(
		array( 'label' => 'Jackets', 'slugs' => array( 'jackets', 'jacket' ) ),
		array( 'label' => 'Pants',   'slugs' => array( 'pants', 'pant' ) ),
		array( 'label' => 'Shirts',  'slugs' => array( 'shirts', 'shirt' ) ),
	);

	$injected = '';
	foreach ( $targets as $t ) {
		$already = ( false !== stripos( $items, '>' . $t['label'] . '<' ) );
		if ( $already ) {
			continue;
		}
		$link = '';
		foreach ( $t['slugs'] as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$term_link = get_term_link( $term, 'product_cat' );
				if ( ! is_wp_error( $term_link ) ) {
					$link = $term_link;
					break;
				}
			}
		}
		if ( $link ) {
			$injected .= '<li class="menu-item menu-item-product-cat"><a href="' . esc_url( $link ) . '">' . esc_html( $t['label'] ) . '</a></li>';
		}
	}

	// Insert before closing </ul> items output
	if ( $injected ) {
		$items .= $injected;
	}
	return $items;
}
add_filter( 'wp_nav_menu_items', 'wp_augoose_inject_primary_category_links', 10, 2 );

/**
 * Add critical CSS inline to override WooCommerce
 */
function wp_augoose_add_critical_css() {
    // Emergency/nuclear overrides should never run on production unless explicitly enabled.
    // Enable by adding `?augoose_debug=1` to the URL.
    if ( ! isset( $_GET['augoose_debug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return;
    }

    // FORCE GALLERY THUMBNAILS - NO NUMBERING, PROPER SPACING
    if ( is_product() ) {
        echo '<style id="wp-augoose-gallery-nuclear">
        /* NUCLEAR OVERRIDE - GALLERY THUMBNAILS */
        .woocommerce-product-gallery__thumbs,
        .flex-control-nav,
        .flex-control-thumbs,
        ol.flex-control-nav,
        ol.flex-control-thumbs {
            display: flex !important;
            flex-direction: row !important;
            gap: 16px !important;
            list-style-type: none !important;
            list-style: none !important;
            counter-reset: none !important;
            padding: 0 !important;
            margin: 16px 0 0 !important;
        }
        
        .woocommerce-product-gallery__thumbs li::marker,
        .flex-control-nav li::marker,
        .flex-control-thumbs li::marker,
        .woocommerce-product-gallery__thumbs li::before,
        .woocommerce-product-gallery__thumbs li::after {
            display: none !important;
            content: none !important;
        }
        
        .woocommerce-product-gallery__thumbs li,
        .flex-control-thumbs li {
            list-style: none !important;
            counter-increment: none !important;
        }
        </style>';
    }
    
    if ( is_front_page() || is_home() ) {
        echo '<style id="wp-augoose-critical">
        /* NUCLEAR OVERRIDE - FORCE GRID LAYOUT */
        body .latest-collection .woocommerce ul.products,
        body .latest-collection .woocommerce ul.products.columns-4,
        body .latest-collection .woocommerce ul.products.latest,
        body .latest-collection ul.products,
        body .latest-collection ul.products.columns-4,
        body .latest-collection ul.products.latest {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 30px !important;
            flex-direction: unset !important;
            flex-wrap: unset !important;
            align-items: unset !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
            min-width: unset !important;
            word-break: unset !important;
            box-sizing: border-box !important;
        }
        
        body .latest-collection .woocommerce ul.products li.product,
        body .latest-collection .woocommerce ul.products.columns-4 li.product,
        body .latest-collection .woocommerce ul.products.latest li.product,
        body .latest-collection ul.products li.product,
        body .latest-collection ul.products.columns-4 li.product,
        body .latest-collection ul.products.latest li.product {
            width: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            float: none !important;
            display: block !important;
            flex: none !important;
            background: #fff !important;
            border: 1px solid #e0e0e0 !important;
            box-sizing: border-box !important;
            margin-bottom: 0 !important;
            flex-direction: unset !important;
            justify-content: unset !important;
            align-items: unset !important;
        }
        
        body .latest-collection .woocommerce ul.products li.product:hover {
            border-color: #1a1a1a !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        }
        
        body .latest-collection .woocommerce ul.products li.product h2,
        body .latest-collection .woocommerce ul.products li.product .woocommerce-loop-product__title {
            font-size: 14px !important;
            font-weight: 600 !important;
            color: #1a1a1a !important;
            margin: 16px 16px 8px !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
        }
        
        body .latest-collection .woocommerce ul.products li.product .price {
            font-size: 13px !important;
            color: #666 !important;
            margin: 0 16px 16px !important;
            font-weight: 500 !important;
        }
        
        body .latest-collection .woocommerce ul.products li.product .button,
        body .latest-collection .woocommerce ul.products li.product .added_to_cart {
            display: none !important;
        }
        
        /* CRITICAL IMAGE FIX */
        body .product-thumbnail {
            position: relative !important;
            overflow: hidden !important;
            background: #f8f8f8 !important;
            height: 350px !important;
            width: 100% !important;
        }
        
        body .product-thumbnail img,
        body .product-images-slider img,
        body .product-image {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center !important;
            display: block !important;
        }
        
        body .product-images-slider {
            width: 100% !important;
            height: 100% !important;
            position: relative !important;
        }
        
        body .product-images-slider .product-image {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            opacity: 0 !important;
            transition: opacity 0.3s ease !important;
        }
        
        body .product-images-slider .product-image.active,
        body .product-images-slider .product-image:first-child {
            opacity: 1 !important;
        }
        
        @media (max-width: 768px) {
            body .latest-collection .woocommerce ul.products,
            body .latest-collection .woocommerce ul.products.columns-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 20px !important;
            }
            
            body .product-thumbnail {
                height: 250px !important;
            }
        }
        </style>';
    }
}
add_action( 'wp_head', 'wp_augoose_add_critical_css', 999 );

/**
 * Add JavaScript to force grid layout
 */
function wp_augoose_force_grid_layout() {
    // Emergency JS should never run on production unless explicitly enabled.
    // Enable by adding `?augoose_debug=1` to the URL.
    if ( ! isset( $_GET['augoose_debug'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return;
    }
    if ( is_front_page() || is_home() ) {
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("Forcing grid layout...");
            
            // Force grid layout for products - multiple attempts
            function forceGridLayout() {
                const selectors = [
                    ".latest-collection .woocommerce ul.products",
                    ".latest-collection ul.products", 
                    ".latest-collection .products",
                    ".woocommerce ul.products.columns-4",
                    ".woocommerce ul.products.latest"
                ];
                
                selectors.forEach(function(selector) {
                    const productLists = document.querySelectorAll(selector);
                    productLists.forEach(function(list) {
                        if (list) {
                            console.log("Found product list:", selector);
                            list.style.display = "grid";
                            list.style.gridTemplateColumns = "repeat(4, minmax(0, 1fr))";
                            list.style.gap = "30px";
                            list.style.flexDirection = "unset";
                            list.style.flexWrap = "unset";
                            list.style.alignItems = "unset";
                            list.style.width = "100%";
                            list.style.margin = "0";
                            list.style.padding = "0";
                            list.style.listStyle = "none";
                            
                            // Force product item styling
                            const products = list.querySelectorAll("li.product, li");
                            console.log("Found products:", products.length);
                            products.forEach(function(product, index) {
                                product.style.width = "auto";
                                product.style.margin = "0";
                                product.style.float = "none";
                                product.style.display = "block";
                                product.style.flex = "none";
                                product.style.background = "#fff";
                                product.style.border = "1px solid #e0e0e0";
                                product.style.boxSizing = "border-box";
                            });
                        }
                    });
                });
            }
            
            // Run multiple times to ensure it works
            forceGridLayout();
            setTimeout(forceGridLayout, 100);
            setTimeout(forceGridLayout, 500);
            setTimeout(forceGridLayout, 1000);
            
            // Mobile responsive
            function checkMobile() {
                const productLists = document.querySelectorAll(".latest-collection .woocommerce ul.products, .latest-collection ul.products");
                productLists.forEach(function(list) {
                    if (window.innerWidth <= 768) {
                        list.style.gridTemplateColumns = "repeat(2, minmax(0, 1fr))";
                        list.style.gap = "20px";
                    } else {
                        list.style.gridTemplateColumns = "repeat(4, minmax(0, 1fr))";
                        list.style.gap = "30px";
                    }
                });
            }
            
            checkMobile();
            window.addEventListener("resize", checkMobile);
            
            // Fix image sizing issues
            function fixImageSizing() {
                console.log("Fixing image sizing...");
                
                // Fix product thumbnails
                const thumbnails = document.querySelectorAll(".product-thumbnail");
                thumbnails.forEach(function(thumbnail) {
                    thumbnail.style.position = "relative";
                    thumbnail.style.overflow = "hidden";
                    thumbnail.style.height = "350px";
                    thumbnail.style.width = "100%";
                    thumbnail.style.background = "#f8f8f8";
                    
                    const imgs = thumbnail.querySelectorAll("img");
                    imgs.forEach(function(img) {
                        img.style.width = "100%";
                        img.style.height = "100%";
                        img.style.objectFit = "cover";
                        img.style.objectPosition = "center";
                        img.style.display = "block";
                    });
                });
                
                // Fix image sliders
                const sliders = document.querySelectorAll(".product-images-slider");
                sliders.forEach(function(slider) {
                    slider.style.width = "100%";
                    slider.style.height = "100%";
                    slider.style.position = "relative";
                    
                    const images = slider.querySelectorAll(".product-image");
                    images.forEach(function(img, index) {
                        img.style.position = "absolute";
                        img.style.top = "0";
                        img.style.left = "0";
                        img.style.width = "100%";
                        img.style.height = "100%";
                        img.style.objectFit = "cover";
                        img.style.objectPosition = "center";
                        img.style.opacity = index === 0 ? "1" : "0";
                        img.style.transition = "opacity 0.3s ease";
                    });
                });
            }
            
            // Run image fix multiple times
            fixImageSizing();
            setTimeout(fixImageSizing, 100);
            setTimeout(fixImageSizing, 500);
            setTimeout(fixImageSizing, 1000);
        });
        </script>';
    }
}
add_action( 'wp_footer', 'wp_augoose_force_grid_layout' );

/**
 * Register widget areas
 */
function wp_augoose_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Sidebar', 'wp-augoose' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Add widgets here.', 'wp-augoose' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );
    
    register_sidebar( array(
        'name'          => __( 'Footer Widget Area', 'wp-augoose' ),
        'id'            => 'footer-1',
        'description'   => __( 'Footer widget area.', 'wp-augoose' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    // WooCommerce Shop Filters (left sidebar like Figma)
    register_sidebar( array(
        'name'          => __( 'Shop Filters', 'wp-augoose' ),
        'id'            => 'shop-filters',
        'description'   => __( 'Widgets shown on Shop / Category pages (left filters column).', 'wp-augoose' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'wp_augoose_widgets_init' );

/**
 * Include template functions
 */
if ( file_exists( get_template_directory() . '/inc/template-functions.php' ) ) {
    require get_template_directory() . '/inc/template-functions.php';
}

if ( file_exists( get_template_directory() . '/inc/template-tags.php' ) ) {
    require get_template_directory() . '/inc/template-tags.php';
}

/**
 * Load WooCommerce compatibility file
 */
if ( class_exists( 'WooCommerce' ) && file_exists( get_template_directory() . '/inc/woocommerce.php' ) ) {
    require get_template_directory() . '/inc/woocommerce.php';
}
