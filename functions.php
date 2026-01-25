<?php
/**
 * WP Augoose Theme Functions
 *
 * @package WP_Augoose
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load security and performance enhancements
require_once get_template_directory() . '/inc/security.php';
require_once get_template_directory() . '/inc/performance.php';

/**
 * Theme Setup
 */
function wp_augoose_setup() {
    load_theme_textdomain( 'wp-augoose', get_template_directory() . '/languages' );

    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'wp-augoose' ),
        'footer'  => __( 'Footer Menu', 'wp-augoose' ),
        'language' => __( 'Language Switcher Menu', 'wp-augoose' ),
        'footer_about' => __( 'Footer: About', 'wp-augoose' ),
        'footer_help'  => __( 'Footer: Help', 'wp-augoose' ),
        'footer_shop'  => __( 'Footer: Shop', 'wp-augoose' ),
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
 * Create core static pages on theme activation (only if missing).
 * - Contact Us
 * - About Us
 * - FAQ
 * - Terms of Service (includes privacy/cookies/refund/commercial use/contact)
 */
function wp_augoose_maybe_create_static_pages() {
    // Only run in admin context.
    if ( ! is_admin() ) {
        return;
    }

    $pages = array(
        array(
            'slug'     => 'contact-us',
            'title'    => 'Contact Us',
            'template' => 'page-contact-us.php',
            'content'  => '',
        ),
        array(
            'slug'     => 'about-us',
            'title'    => 'About Us',
            'template' => 'page-about-us.php',
            'content'  =>
                '<div class="augoose-section">' .
                '<p class="craftsmanship-text">Started by our vision and love for American workwear.</p>' .
                '<p>Each of Augoose\'s pieces is built with steady hands, perfected details, and made to endure the weight of real work. Augoose is a workwear brand that crafts versatile pieces using high-quality, locally sourced materials and collaborates with local tailors to create items with timeless design and long-lasting durability.</p>' .
                '<p>We begin with our signature Double Knee pants and have developed to heavy duty Active Jacket.</p>' .
                '<p>Each cut is made with precision, ensuring that every detail contributes to the flawless finish of the final product. As we continually refine our technique and craftsmanship, our goal remains simple: to bring you the finest Augoose creations possible.</p>' .
                '</div>',
        ),
        array(
            'slug'     => 'faq',
            'title'    => 'FAQ',
            'template' => 'page-faq.php',
            'content'  =>
                '<div class="augoose-section"><h2>PLEASE READ THEM CAREFULLY.</h2></div>' .
                '<div class="augoose-faq">' .
                '<details><summary>How do I track my order?</summary><div class="augoose-faq-answer"><p>Once your Augoose order has been shipped, you will receive an email with your tracking information so you may track the package online.</p></div></details>' .
                '<details><summary>How do I cancel my order?</summary><div class="augoose-faq-answer"><p>Once you place an order, we cannot change or cancel it as we immediately process your orders for shipment. Please carefully review your order before making payment.</p></div></details>' .
                '<details><summary>Can I change or refund my order?</summary><div class="augoose-faq-answer"><p>At the moment we can\'t accept any refund or change any order unless you received an incorrect, unfinished, or defective product. Please carefully review your order before making payment.</p></div></details>' .
                '<details><summary>What if I received an incorrect / unfinished / defective product?</summary><div class="augoose-faq-answer"><p>We\'re sorry if it happened to you. Please contact us via email at <a href="mailto:halo@augoose.co">halo@augoose.co</a> for further processing. The product may be exchanged or returned if it meets the following criteria:</p><ol><li>The product has defects or damage to any part of the item.</li><li>The product has a size discrepancy exceeding the acceptable tolerance (±2 cm).</li><li>The product shows a significant color difference.</li><li>The product is received in a damaged condition, such as being torn, etc.</li></ol></div></details>' .
                '<details><summary>What payment do you accept?</summary><div class="augoose-faq-answer"><p>We accept various payment methods that you can check on the checkout page (PayPal, Credit/Debit card, QRIS, VA for Indonesia, etc).</p></div></details>' .
                '<details><summary>How long is the delivery time?</summary><div class="augoose-faq-answer"><p>Delivery times are estimates. On average it takes 3–5 days to Southeast Asia, 5–7 days to Asia Pacific and Australia, and 7–10 days to Europe and America. Please check your Air Way Bill (AWB) number sent to the email you registered at checkout and frequently track through the shipping portal.</p></div></details>' .
                '<details><summary>What is the shipment method?</summary><div class="augoose-faq-answer"><p>We partner with DHL to ship your order.</p></div></details>' .
                '</div>',
        ),
        array(
            'slug'     => 'terms-of-service',
            'title'    => 'Terms of Service',
            'template' => 'page-terms-of-service.php',
            'content'  =>
                '<div class="augoose-section"><h2 id="terms-of-use">Terms of Use</h2>' .
                '<p>By using this site, you agree to these terms of use, as well as any other terms, guidelines, or rules that apply to any portion of this site, without limitation or qualification. If you do not agree to these terms of use, you must exit the site immediately and discontinue any use of information or product from this site.</p>' .
                '</div>' .
                '<div class="augoose-section"><h2 id="privacy-policy">Privacy Policy</h2>' .
                '<p>At Augoose, we are committed to protecting your personal information and ensuring your privacy is respected. You can rest assured that any information you submit to us will not be misused, abused, or sold to any other parties. We only use your personal information to complete your order.</p>' .
                '</div>' .
                '<div class="augoose-section"><h2 id="cookies-tracking">Cookies &amp; Tracking</h2>' .
                '<p>Our website may use cookies to enhance your browsing experience and improve functionality. You can manage your cookie preferences through your browser settings at any time.</p>' .
                '</div>' .
                '<div class="augoose-section"><h2 id="return-refund-policy">Return &amp; Refund Policy</h2>' .
                '<p>We always try our best to keep our items in perfect condition before we ship them to you. Currently, we do not offer refunds under any circumstances. We apologize that once you’ve submitted an order, the product cannot be cancelled or exchanged. Please check your item(s) before submitting an order to prevent any mistakes.</p>' .
                '<p>If you receive an incorrect, unfinished, or defective product, please contact us at <a href="mailto:halo@augoose.co">halo@augoose.co</a>. The product may be exchanged or returned if it meets the following criteria:</p>' .
                '<ol><li>The product has defects or damage to any part of the item.</li><li>The product has a size discrepancy exceeding the acceptable tolerance (±2 cm).</li><li>The product shows a significant color difference.</li><li>The product is received in a damaged condition, such as being torn, etc.</li></ol>' .
                '<p>Please make sure you document an unboxing video as proof.</p>' .
                '</div>' .
                '<div class="augoose-section"><h2 id="commercial-use">Commercial Use</h2>' .
                '<p>You may not copy, reproduce, or sell any content of this site for any commercial use on your own site.</p>' .
                '</div>' .
                '<div class="augoose-section"><h2 id="acceptance">Your Acceptance of These Terms</h2>' .
                '<p>By using this site, you accept these terms of use and the privacy policy. By providing us with your information, you agree to the site’s privacy policy. If you do not agree to this policy, please leave this site immediately.</p>' .
                '</div>' .
                '<div class="augoose-section"><h2 id="contact-us">Contact Us</h2>' .
                '<p>For any privacy-related concerns or questions, please contact us at: <a href="mailto:halo@augoose.co">halo@augoose.co</a></p>' .
                '</div>',
        ),
    );

    foreach ( $pages as $p ) {
        $existing = get_page_by_path( $p['slug'] );
        if ( $existing instanceof WP_Post ) {
            // If template is not set, set it (do not overwrite content).
            if ( ! empty( $p['template'] ) ) {
                $current_tpl = get_post_meta( $existing->ID, '_wp_page_template', true );
                if ( $current_tpl !== $p['template'] ) {
                    update_post_meta( $existing->ID, '_wp_page_template', $p['template'] );
                }
            }
            continue;
        }

        $page_id = wp_insert_post(
            array(
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_title'   => $p['title'],
                'post_name'    => $p['slug'],
                'post_content' => $p['content'],
            ),
            true
        );

        if ( is_wp_error( $page_id ) ) {
            continue;
        }

        if ( ! empty( $p['template'] ) ) {
            update_post_meta( (int) $page_id, '_wp_page_template', $p['template'] );
        }
    }
}
function wp_augoose_seed_static_pages_once() {
    // Seed once, but allow re-seeding if the option is deleted.
    if ( '1' === get_option( 'wp_augoose_static_pages_seeded', '0' ) ) {
        return;
    }
    wp_augoose_maybe_create_static_pages();
    update_option( 'wp_augoose_static_pages_seeded', '1' );
}
add_action( 'after_switch_theme', 'wp_augoose_seed_static_pages_once' );
add_action( 'admin_init', 'wp_augoose_seed_static_pages_once' );

/**
 * =========================
 * Integrations: Multi-language + Multi-currency (WooCommerce)
 * - UI is provided by theme.
 * - Actual translation/currency conversion must be handled by plugins.
 * =========================
 */

function wp_augoose_render_language_switcher() {
    // Allow plugins/custom code to override completely.
    if ( has_action( 'wp_augoose_language_switcher' ) ) {
        echo '<div class="header-locale header-language">';
        do_action( 'wp_augoose_language_switcher' );
        echo '</div>';
        return;
    }

    // Polylang
    if ( function_exists( 'pll_the_languages' ) ) {
        echo '<div class="header-locale header-language">';
        echo '<div class="lang-switcher">';
        pll_the_languages(
            array(
                'show_flags' => 1,
                'show_names' => 0,
                'dropdown'   => 1,
            )
        );
        echo '</div>';
        echo '</div>';
        return;
    }

    // WPML
    if ( function_exists( 'icl_get_languages' ) ) {
        $langs = icl_get_languages( 'skip_missing=0&orderby=code' );
        if ( is_array( $langs ) && ! empty( $langs ) ) {
            echo '<div class="header-locale header-language"><div class="lang-switcher"><select class="lang-select" onchange="if(this.value){window.location.href=this.value;}">';
            foreach ( $langs as $l ) {
                $selected = ! empty( $l['active'] ) ? ' selected' : '';
                $url      = isset( $l['url'] ) ? $l['url'] : '';
                $name     = '';
                if ( isset( $l['native_name'] ) ) {
                    $name = $l['native_name'];
                } elseif ( isset( $l['translated_name'] ) ) {
                    $name = $l['translated_name'];
                } elseif ( isset( $l['language_code'] ) ) {
                    $name = $l['language_code'];
                }
                printf( '<option value="%s"%s>%s</option>', esc_url( $url ), $selected, esc_html( $name ) );
            }
            echo '</select></div></div>';
        }
        return;
    }

    // TranslatePress (if they use its shortcode)
    if ( shortcode_exists( 'language-switcher' ) ) {
        echo '<div class="header-locale header-language">';
        echo do_shortcode( '[language-switcher]' );
        echo '</div>';
        return;
    }

    // Fallback: WP menu location "language" (user can populate it manually)
    if ( has_nav_menu( 'language' ) ) {
        echo '<div class="header-locale header-language">';
        wp_nav_menu(
            array(
                'theme_location' => 'language',
                'menu_id'        => 'language-menu',
                'container'      => false,
                'fallback_cb'    => false,
                'depth'          => 1,
            )
        );
        echo '</div>';
        return;
    }

    // Built-in fallback: Simple language switcher (cookie-based, UI only - no translation)
    $current_lang = isset( $_COOKIE['wp_augoose_lang'] ) ? sanitize_text_field( $_COOKIE['wp_augoose_lang'] ) : 'en';
    $languages = array(
        'en' => 'English',
        'id' => 'Indonesia',
    );
    echo '<div class="header-locale header-language">';
    echo '<select class="lang-select augoose-lang-switcher" data-current="' . esc_attr( $current_lang ) . '">';
    foreach ( $languages as $code => $name ) {
        $selected = ( $code === $current_lang ) ? ' selected' : '';
        printf( '<option value="%s"%s>%s</option>', esc_attr( $code ), $selected, esc_html( $name ) );
    }
    echo '</select>';
    echo '</div>';
}

function wp_augoose_render_currency_switcher() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    // Allow plugins/custom code to override completely.
    if ( has_action( 'wp_augoose_currency_switcher' ) ) {
        do_action( 'wp_augoose_currency_switcher' );
        return;
    }

    // WPML WooCommerce Multilingual (WCML) - Let plugin render directly, no wrapper
    if ( has_action( 'wcml_currency_switcher' ) ) {
        do_action( 'wcml_currency_switcher', array( 'switcher_style' => 'wcml-dropdown' ) );
        return;
    }
}

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
    if ( file_exists( $theme_dir . '/assets/css/pages-static.css' ) ) {
        wp_enqueue_style( 'wp-augoose-pages-static', $theme_dir_uri . '/assets/css/pages-static.css', array( 'wp-augoose-brand' ), $asset_ver( 'assets/css/pages-static.css' ) );
        
        // Contact Us page styles
        if ( is_page( 'contact-us' ) || is_page_template( 'page-contact-us.php' ) ) {
            wp_enqueue_style( 'wp-augoose-contact-us', $theme_dir_uri . '/assets/css/contact-us.css', array(), $asset_ver( 'assets/css/contact-us.css' ) );
        }
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
        wp_enqueue_style( 'wp-augoose-latest-collection-v2', $theme_dir_uri . '/assets/css/latest-collection-v2.css', array( 'wp-augoose-product-fixed', 'wp-augoose-brand' ), $asset_ver( 'assets/css/latest-collection-v2.css' ), 'all' );
		wp_enqueue_style( 'wp-augoose-button-global-style', $theme_dir_uri . '/assets/css/button-global-style.css', array( 'wp-augoose-woocommerce', 'wp-augoose-product-fixed', 'wp-augoose-latest-collection-v2' ), $asset_ver( 'assets/css/button-global-style.css' ), 'all' );
		wp_style_add_data( 'wp-augoose-button-global-style', 'priority', 'high' );
		
        wp_enqueue_style( 'wp-augoose-header-mobile-fix', $theme_dir_uri . '/assets/css/header-mobile-fix.css', array( 'wp-augoose-header' ), $asset_ver( 'assets/css/header-mobile-fix.css' ), 'all' );
        wp_enqueue_style( 'wp-augoose-latest-collection-mobile-fix', $theme_dir_uri . '/assets/css/latest-collection-mobile-fix.css', array( 'wp-augoose-latest-collection-v2' ), $asset_ver( 'assets/css/latest-collection-mobile-fix.css' ), 'all' );
        wp_enqueue_style( 'wp-augoose-hero-fixed', $theme_dir_uri . '/assets/css/hero-fixed.css', array( 'wp-augoose-homepage' ), $asset_ver( 'assets/css/hero-fixed.css' ), 'all' );
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
        // Product Gallery Refactored - Load AFTER gallery-fixed untuk override
        // Clean layout, mentok kiri, aspect ratio 3:4
        if ( file_exists( $theme_dir . '/assets/css/product-gallery-refactored.css' ) ) {
            wp_enqueue_style( 
                'wp-augoose-product-gallery-refactored', 
                $theme_dir_uri . '/assets/css/product-gallery-refactored.css', 
                array( 'wp-augoose-product-gallery-fixed', 'wp-augoose-woocommerce-integrated', 'wp-augoose-woocommerce-custom' ), 
                $asset_ver( 'assets/css/product-gallery-refactored.css' ), 
                'all' 
            );
            wp_style_add_data( 'wp-augoose-product-gallery-refactored', 'priority', 'high' );
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
        // Off-canvas filter (no layout shift)
        if ( file_exists( $theme_dir . '/assets/css/shop-filter-offcanvas.css' ) ) {
            wp_enqueue_style( 'wp-augoose-shop-filter-offcanvas', $theme_dir_uri . '/assets/css/shop-filter-offcanvas.css', array( 'wp-augoose-shop-fixed' ), $asset_ver( 'assets/css/shop-filter-offcanvas.css' ), 'all' );
            wp_style_add_data( 'wp-augoose-shop-filter-offcanvas', 'priority', 'high' );
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
            
            // Get cart hash to prevent checkout.min.js errors
            $cart_hash = '';
            if ( class_exists( 'WooCommerce' ) && WC()->cart ) {
                $cart_hash = WC()->cart->get_cart_hash();
            }
            
            wp_localize_script( 'wp-augoose-checkout-quantity', 'wc_checkout_params', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'update_cart_nonce' => wp_create_nonce( 'woocommerce-cart' ),
                'cart_hash' => $cart_hash ? $cart_hash : '',
            ) );
        }
    }
    
    // Checkout styling now handled by woocommerce-integrated.css
    // No separate checkout CSS file needed
    
    // Main JavaScript
    if ( file_exists( $theme_dir . '/assets/js/main.js' ) ) {
        wp_enqueue_script( 'wp-augoose-main', $theme_dir_uri . '/assets/js/main.js', array( 'jquery' ), $asset_ver( 'assets/js/main.js' ), true );
        
        // Latest Collection slider (only on homepage)
        if ( is_front_page() ) {
            wp_enqueue_script( 'wp-augoose-latest-collection-slider', $theme_dir_uri . '/assets/js/latest-collection-slider.js', array( 'jquery' ), $asset_ver( 'assets/js/latest-collection-slider.js' ), true );
        }
        wp_localize_script(
            'wp-augoose-main',
            'wpAugoose',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wp_augoose_nonce' ),
            )
        );
        
        // Simple Wishlist Script - No dependencies, works immediately
        if ( file_exists( $theme_dir . '/assets/js/wishlist-simple.js' ) ) {
            wp_enqueue_script( 
                'wp-augoose-wishlist-simple', 
                $theme_dir_uri . '/assets/js/wishlist-simple.js', 
                array(), // No dependencies
                $asset_ver( 'assets/js/wishlist-simple.js' ), 
                true 
            );
        }
        
        // Add nonce to meta tag for wishlist-simple.js
        add_action( 'wp_head', function() {
            echo '<meta name="wp-augoose-nonce" content="' . esc_attr( wp_create_nonce( 'wp_augoose_nonce' ) ) . '">' . "\n";
        }, 1 );
        
        // CRITICAL: Add inline script to ensure wishlist handler works even if main.js fails
        $inline_script = "
        console.log('=== INLINE WISHLIST SCRIPT LOADED ===');
        console.log('wpAugoose:', typeof wpAugoose !== 'undefined' ? wpAugoose : 'NOT DEFINED');
        
        // Attach handler immediately when jQuery is ready
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function($) {
                console.log('=== DOCUMENT READY - ATTACHING WISHLIST HANDLER ===');
                console.log('Wishlist buttons found:', $('.add-to-wishlist, .wishlist-toggle').length);
                
                $('.add-to-wishlist, .wishlist-toggle').each(function() {
                    console.log('Button found:', this, 'Product ID:', $(this).data('product-id'));
                });
            });
        } else {
            console.error('jQuery not available in inline script!');
        }
        ";
        wp_add_inline_script( 'wp-augoose-main', $inline_script );
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
 * Shop filters: hide unwanted widgets (Category) but keep Price range, Size, and Color.
 */
add_filter(
	'widget_display_callback',
	function ( $instance, $widget, $args ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return $instance;
		}
		if ( ! ( function_exists( 'is_shop' ) && is_shop() ) && ! ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) ) {
			return $instance;
		}

		// Hide categories widget on product archives (user is already browsing a category page or shop listing).
		if ( $widget instanceof WC_Widget_Product_Categories ) {
			return false;
		}

		// Allow all layered nav widgets including color
		// Color filter will now be visible

		return $instance;
	},
	10,
	3
);

/**
 * Size filter: dynamically show size terms based on actual product data per category.
 * - Pants: numeric sizes (28, 30, 32, 34, 36, 38, etc.)
 * - Jackets/Shirts: alpha sizes (S, M, L, XL, etc.)
 */
add_filter(
	'woocommerce_layered_nav_term_html',
	function ( $term_html, $term, $link, $count ) {
		if ( ! class_exists( 'WooCommerce' ) || ! ( $term instanceof WP_Term ) ) {
			return $term_html;
		}

		// Only affect size taxonomy terms.
		$tax = isset( $term->taxonomy ) ? (string) $term->taxonomy : '';
		if ( stripos( $tax, 'size' ) === false && stripos( $tax, 'pa_size' ) === false ) {
			return $term_html;
		}

		$slug = strtolower( (string) $term->slug );
		$name = strtolower( (string) $term->name );

		// Determine current category context.
		$current_slug = '';
		$current_cat_ids = array();
		if ( function_exists( 'is_product_category' ) && is_product_category() ) {
			$q = get_queried_object();
			if ( $q && isset( $q->slug ) ) {
				$current_slug = strtolower( (string) $q->slug );
				$current_cat_ids[] = $q->term_id;
			}
		} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
			// On shop page, check all products to infer category
		}

		// Check if term name/slug is numeric (pants) or alpha (jackets/shirts).
		$is_numeric = is_numeric( $slug ) || is_numeric( $name );
		$is_alpha = preg_match( '/^[a-z]+$/i', $slug ) || preg_match( '/^[a-z]+$/i', $name );

		// Pants category: show only numeric sizes.
		if ( in_array( $current_slug, array( 'pants', 'pant' ), true ) ) {
			if ( ! $is_numeric ) {
				return '';
			}
			return $term_html;
		}

		// Jackets/Shirts: show only alpha sizes (S, M, L, XL, XXL, etc.).
		if ( in_array( $current_slug, array( 'jackets', 'jacket', 'shirts', 'shirt' ), true ) ) {
			if ( ! $is_alpha ) {
				return '';
			}
			return $term_html;
		}

		// If no category context, show all sizes (fallback).
		return $term_html;
	},
	10,
	4
);

/**
 * Checkout: revise billing field labels + required/optional rules.
 */
add_filter(
	'woocommerce_checkout_fields',
	function ( $fields ) {
		if ( ! is_array( $fields ) ) {
			return $fields;
		}

		// Billing fields - ALL LABELS IN ENGLISH
		if ( isset( $fields['billing'] ) && is_array( $fields['billing'] ) ) {
			// Name: keep first_name, remove last_name
			if ( isset( $fields['billing']['billing_first_name'] ) ) {
				$fields['billing']['billing_first_name']['label']       = 'Full Name';
				$fields['billing']['billing_first_name']['placeholder'] = 'Enter your full name';
				$fields['billing']['billing_first_name']['required']    = true;
			}
			if ( isset( $fields['billing']['billing_last_name'] ) ) {
				unset( $fields['billing']['billing_last_name'] );
			}

			// Address
			if ( isset( $fields['billing']['billing_address_1'] ) ) {
				$fields['billing']['billing_address_1']['label']       = 'Address';
				$fields['billing']['billing_address_1']['placeholder'] = 'Street address';
				$fields['billing']['billing_address_1']['required']    = true;
			}

			// Address remarks (optional)
			if ( isset( $fields['billing']['billing_address_2'] ) ) {
				$fields['billing']['billing_address_2']['label']       = 'Address Line 2 (Optional)';
				$fields['billing']['billing_address_2']['placeholder'] = 'Apartment, suite, etc.';
				$fields['billing']['billing_address_2']['required']    = false;
			}

			// Postal code
			if ( isset( $fields['billing']['billing_postcode'] ) ) {
				$fields['billing']['billing_postcode']['label']       = 'Postal Code';
				$fields['billing']['billing_postcode']['placeholder'] = 'Postal code';
				$fields['billing']['billing_postcode']['required']    = true;
			}

			// Country
			if ( isset( $fields['billing']['billing_country'] ) ) {
				$fields['billing']['billing_country']['label']    = 'Country';
				$fields['billing']['billing_country']['required'] = true;
			}

			// Phone (country code hint)
			if ( isset( $fields['billing']['billing_phone'] ) ) {
				$fields['billing']['billing_phone']['label']       = 'Phone Number';
				$fields['billing']['billing_phone']['placeholder'] = 'e.g. +1 234-567-8900';
				$fields['billing']['billing_phone']['required']    = true;
			}

			// Email
			if ( isset( $fields['billing']['billing_email'] ) ) {
				$fields['billing']['billing_email']['label']       = 'Email';
				$fields['billing']['billing_email']['placeholder'] = 'you@example.com';
				$fields['billing']['billing_email']['required']    = true;
			}
		}

		// Shipping: keep last_name removed for consistency (optional)
		if ( isset( $fields['shipping'] ) && is_array( $fields['shipping'] ) ) {
			if ( isset( $fields['shipping']['shipping_last_name'] ) ) {
				unset( $fields['shipping']['shipping_last_name'] );
			}
		}

		// Order notes (Additional Information) - English label
		if ( isset( $fields['order'] ) && is_array( $fields['order'] ) ) {
			if ( isset( $fields['order']['order_comments'] ) ) {
				$fields['order']['order_comments']['label']       = 'Order Notes (Optional)';
				$fields['order']['order_comments']['placeholder'] = 'Notes about your order, e.g., special notes for delivery.';
				$fields['order']['order_comments']['required']    = false;
			}
		}

		return $fields;
	},
	20
);

/**
 * Product search form (rounded + no browser autocomplete).
 * Also adds a datalist that we will populate from site-only search history (localStorage).
 */
add_filter(
	'get_product_search_form',
	function ( $form ) {
		$action = esc_url( home_url( '/' ) );
		$query  = get_search_query();

		$form = '
		<form role="search" method="get" class="woocommerce-product-search augoose-search" action="' . $action . '" autocomplete="off">
			<label class="screen-reader-text" for="augoose-product-search-field">' . esc_html__( 'Search for:', 'wp-augoose' ) . '</label>
			<input type="search"
				id="augoose-product-search-field"
				class="search-field"
				placeholder="' . esc_attr__( 'Search products…', 'wp-augoose' ) . '"
				value="' . esc_attr( $query ) . '"
				name="s"
				autocomplete="off"
				autocapitalize="off"
				autocorrect="off"
				spellcheck="false"
				list="augoose-search-history" />
			<datalist id="augoose-search-history"></datalist>
			<button type="submit" class="search-submit" value="' . esc_attr__( 'Search', 'wp-augoose' ) . '" aria-label="' . esc_attr__( 'Search', 'wp-augoose' ) . '">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="11" cy="11" r="7"></circle>
					<path d="M21 21l-4.3-4.3"></path>
				</svg>
			</button>
			<input type="hidden" name="post_type" value="product" />
		</form>';

		return $form;
	},
	20
);

/**
 * Browser tab titles
 * Examples:
 * - Augoose | Jackets Selections
 * - Augoose | Your Wishlist
 * - Augoose | Checkout Your Workwear
 */
add_filter(
	'pre_get_document_title',
	function ( $title ) {
		$brand = 'Augoose';

		// Wishlist page (theme template)
		if ( is_page_template( 'page-wishlist.php' ) ) {
			return $brand . ' | Your Wishlist';
		}

		// Checkout page
		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			return $brand . ' | Checkout Your Workwear';
		}

		// Product category selections
		if ( function_exists( 'is_product_category' ) && is_product_category() ) {
			$q = get_queried_object();
			$name = ( $q && isset( $q->name ) ) ? (string) $q->name : 'Shop';
			return $brand . ' | ' . $name . ' Selections';
		}

		// Shop page
		if ( function_exists( 'is_shop' ) && is_shop() ) {
			return $brand . ' | Selections';
		}

		return $title;
	},
	20
);

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
 * Add critical wishlist handler inline script
 * This ensures wishlist works even if main.js fails to load
 */
function wp_augoose_add_wishlist_handler_inline() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    ?>
    <script type="text/javascript">
    console.log('=== CRITICAL WISHLIST HANDLER LOADING ===');
    
    // Wait for jQuery to be available
    (function() {
        function attachWishlistHandler() {
            if (typeof jQuery === 'undefined') {
                console.log('jQuery not ready, retrying...');
                setTimeout(attachWishlistHandler, 100);
                return;
            }
            
            console.log('jQuery ready, attaching wishlist handler...');
            
            // Handle clicks on button OR any child element (SVG, path, etc.)
            jQuery(document).on('click', '.add-to-wishlist, .wishlist-toggle, .add-to-wishlist *, .wishlist-toggle *', function(e) {
                console.log('=== WISHLIST CLICKED (CRITICAL HANDLER) ===');
                console.log('Clicked element:', this);
                console.log('Event target:', e.target);
                
                // Find the actual button (might be clicked on SVG or path inside)
                let $btn = jQuery(this);
                if (!$btn.hasClass('add-to-wishlist') && !$btn.hasClass('wishlist-toggle')) {
                    // Clicked on child element, find parent button
                    $btn = $btn.closest('.add-to-wishlist, .wishlist-toggle');
                }
                
                if ($btn.length === 0) {
                    console.error('Button not found!');
                    return false;
                }
                
                console.log('Button found:', $btn[0]);
                console.log('Button classes:', $btn.attr('class'));
                
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                const productId = parseInt($btn.data('product-id'), 10) || parseInt($btn.closest('[data-product-id]').data('product-id'), 10);
                
                console.log('Product ID:', productId);
                
                if (!productId) {
                    console.error('No product ID!');
                    alert('Error: Product ID not found');
                    return false;
                }
                
                if (typeof wpAugoose === 'undefined' || !wpAugoose || !wpAugoose.ajaxUrl) {
                    console.error('wpAugoose not available!');
                    alert('Error: Wishlist system not initialized. Please refresh the page.');
                    return false;
                }
                
                console.log('wpAugoose:', wpAugoose);
                console.log('Sending AJAX...');
                
                if ($btn.hasClass('loading')) {
                    return false;
                }
                
                $btn.addClass('loading').prop('disabled', true);
                
                jQuery.ajax({
                    url: wpAugoose.ajaxUrl,
                    type: 'POST',
                    timeout: 10000,
                    data: {
                        action: 'wp_augoose_wishlist_toggle',
                        product_id: productId,
                        nonce: wpAugoose.nonce || ''
                    },
                    success: function(res) {
                        console.log('AJAX Success:', res);
                        if (res && res.success && res.data) {
                            if (res.data.action === 'added') {
                                $btn.addClass('active');
                                alert('Product added to wishlist');
                            } else {
                                $btn.removeClass('active');
                                alert('Product removed from wishlist');
                            }
                            
                            const count = res.data.count || 0;
                            const $badge = jQuery('.wishlist-count');
                            if ($badge.length) {
                                if (count > 0) {
                                    $badge.text(count).show();
                                } else {
                                    $badge.hide();
                                }
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error, xhr.responseText);
                        alert('Error: ' + (xhr.responseText || error));
                    },
                    complete: function() {
                        $btn.removeClass('loading').prop('disabled', false);
                    }
                });
                
                return false;
            });
            
            console.log('Critical wishlist handler attached!');
            console.log('Buttons found:', jQuery('.add-to-wishlist, .wishlist-toggle').length);
        }
        
        // Try immediately, then on DOM ready, then on window load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', attachWishlistHandler);
        } else {
            attachWishlistHandler();
        }
        
        window.addEventListener('load', function() {
            console.log('Window loaded, buttons:', jQuery('.add-to-wishlist, .wishlist-toggle').length);
        });
    })();
    </script>
    <?php
}
add_action( 'wp_head', 'wp_augoose_add_wishlist_handler_inline', 5 );

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

/**
 * Override product template for Latest Collection section
 * Use custom template content-product-latest.php when products have class "latest"
 */
add_filter( 'wc_get_template_part', 'wp_augoose_use_latest_collection_template', 10, 3 );
function wp_augoose_use_latest_collection_template( $template, $slug, $name ) {
    // Only override content-product template
    if ( $slug !== 'content' || $name !== 'product' ) {
        return $template;
    }
    
    // Check if we're in Latest Collection section
    // We detect this by checking if we're on homepage and using shortcode with class="latest"
    if ( is_front_page() && wc_get_loop_prop( 'is_shortcode' ) ) {
        // Check if products list has class "latest" (set by shortcode [products class="latest"])
        // We use a global flag to track if we're in Latest Collection
        static $in_latest_collection = false;
        
        // Set flag when we detect Latest Collection section
        if ( ! $in_latest_collection && did_action( 'woocommerce_shortcode_before_products_loop' ) ) {
            $in_latest_collection = true;
        }
        
        if ( $in_latest_collection ) {
            $custom_template = get_template_directory() . '/woocommerce/content-product-latest.php';
            if ( file_exists( $custom_template ) ) {
                return $custom_template;
            }
        }
        
        // Reset flag after loop ends
        if ( did_action( 'woocommerce_shortcode_after_products_loop' ) ) {
            $in_latest_collection = false;
        }
    }
    
    return $template;
}


/**
 * Disable Multicurrency Plugin if WCML is active
 * Since we're using WCML for currency conversion, multicurrency plugin is not needed
 * and can cause conflicts. This will disable it automatically.
 */
add_action( 'plugins_loaded', 'wp_augoose_disable_multicurrency_if_wcml_active', 999 );
function wp_augoose_disable_multicurrency_if_wcml_active() {
	// Check if WCML is active
	$wcml_active = class_exists( 'woocommerce_wpml' ) || function_exists( 'wcml_get_woocommerce_currency_option' );
	
	if ( ! $wcml_active ) {
		// WCML not active, keep the disable hooks function as backup
		if ( file_exists( get_template_directory() . '/inc/disable-multicurrency-plugin.php' ) ) {
			require_once get_template_directory() . '/inc/disable-multicurrency-plugin.php';
			if ( function_exists( 'wp_augoose_disable_multicurrency_plugin_hooks' ) ) {
				wp_augoose_disable_multicurrency_plugin_hooks();
			}
		}
		return;
	}
	
	// WCML is active - disable multicurrency plugin completely
	if ( class_exists( 'MultiCurrency_AutoConvert' ) ) {
		// Remove all hooks from multicurrency plugin
		$instance = MultiCurrency_AutoConvert::get_instance();
		if ( $instance ) {
			// Remove all possible filters with all priorities
			$priorities = array( 1, 5, 10, 15, 20, 25, 30, 50, 99, 100, 999 );
			foreach ( $priorities as $priority ) {
				remove_filter( 'woocommerce_currency', array( $instance, 'get_currency' ), $priority );
				remove_filter( 'woocommerce_currency', array( $instance, 'get_current_currency' ), $priority );
				remove_filter( 'woocommerce_currency_symbol', array( $instance, 'get_currency_symbol' ), $priority );
				remove_filter( 'woocommerce_price_format', array( $instance, 'price_format' ), $priority );
				remove_filter( 'woocommerce_product_get_price', array( $instance, 'convert_price_display' ), $priority );
				remove_filter( 'woocommerce_product_get_sale_price', array( $instance, 'convert_price_display' ), $priority );
				remove_filter( 'woocommerce_product_get_regular_price', array( $instance, 'convert_price_display' ), $priority );
			}
			
			// Remove from global filter array
			global $wp_filter;
			$filters_to_remove = array(
				'woocommerce_currency',
				'woocommerce_currency_symbol',
				'woocommerce_price_format',
				'woocommerce_product_get_price',
				'woocommerce_product_get_sale_price',
				'woocommerce_product_get_regular_price',
			);
			
			foreach ( $filters_to_remove as $filter_name ) {
				if ( isset( $wp_filter[ $filter_name ] ) ) {
					foreach ( $wp_filter[ $filter_name ]->callbacks as $priority => $callbacks ) {
						foreach ( $callbacks as $callback ) {
							if ( is_array( $callback['function'] ) && 
								 is_object( $callback['function'][0] ) && 
								 $callback['function'][0] === $instance ) {
								remove_filter( $filter_name, $callback['function'], $priority );
							}
						}
					}
				}
			}
		}
		
		// Log for debugging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'WCML is active. Multicurrency plugin hooks removed to prevent conflicts.' );
		}
	}
}
