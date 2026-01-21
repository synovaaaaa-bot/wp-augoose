<?php
/**
 * The header for the theme
 *
 * @package WP_Augoose
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'wp-augoose' ); ?></a>

    <div class="header-stack">
    <div class="announcement-bar">
        <div class="container">
            <p>
                <?php
                // Integrated announcement: prefer WooCommerce Store Notice if enabled, otherwise theme mod.
                if ( class_exists( 'WooCommerce' ) && 'yes' === get_option( 'woocommerce_demo_store' ) ) {
                    echo wp_kses_post( get_option( 'woocommerce_demo_store_notice' ) );
                } else {
                    $txt = get_theme_mod( 'wp_augoose_announcement_text', 'INTERNATIONAL &amp; DOMESTIC SHIPPING AVAILABLE – FREE SHIPPING OVER $200' );
                    echo wp_kses_post( do_shortcode( $txt ) );
                }
                ?>
            </p>
        </div>
    </div>

    <header id="masthead" class="site-header">
        <div class="header-container">
            <div class="site-branding">
                    <?php
                    the_custom_logo();
                    ?>
                    <p class="site-title screen-reader-text">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
                    </p>
                </div>

                <nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary', 'wp-augoose' ); ?>">
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'primary',
                            'menu_id'        => 'primary-menu',
                            'fallback_cb'    => false,
                        )
                    );
                    ?>
                </nav>

                <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                    <nav class="header-category-nav" aria-label="<?php esc_attr_e( 'Shop categories', 'wp-augoose' ); ?>">
                        <ul class="header-category-menu">
                            <?php
                            $targets = array(
                                array( 'label' => 'Jackets', 'slugs' => array( 'jackets', 'jacket' ) ),
                                array( 'label' => 'Pants',   'slugs' => array( 'pants', 'pant' ) ),
                                array( 'label' => 'Shirts',  'slugs' => array( 'shirts', 'shirt' ) ),
                            );
                            foreach ( $targets as $t ) {
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
                                    echo '<li><a href="' . esc_url( $link ) . '">' . esc_html( $t['label'] ) . '</a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <div class="header-actions" aria-label="<?php esc_attr_e( 'Header actions', 'wp-augoose' ); ?>">

                    <?php
                    // Multi-language + Multi-currency (only renders if plugins/menus are available)
                    if ( function_exists( 'wp_augoose_render_language_switcher' ) ) {
                        wp_augoose_render_language_switcher();
                    }
                    if ( function_exists( 'wp_augoose_render_currency_switcher' ) ) {
                        wp_augoose_render_currency_switcher();
                    }
                    ?>

                    <div class="header-search">
                        <button type="button" class="search-toggle" aria-label="<?php esc_attr_e( 'Search', 'wp-augoose' ); ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="M21 21l-4.3-4.3"></path>
                            </svg>
                        </button>
                        <div class="search-form-container" style="display:none;">
                            <?php
                            if ( class_exists( 'WooCommerce' ) && function_exists( 'get_product_search_form' ) ) {
                                echo get_product_search_form( false );
                            } else {
                                get_search_form();
                            }
                            ?>
                        </div>
                    </div>

                    <div class="header-wishlist">
                        <a class="wishlist-icon" href="#" aria-label="<?php esc_attr_e( 'Wishlist', 'wp-augoose' ); ?>">
                            <?php
                            $wishlist_count = function_exists( 'wp_augoose_wishlist_count' ) ? (int) wp_augoose_wishlist_count() : 0;
                            ?>
                            <span class="wishlist-count" <?php echo $wishlist_count > 0 ? '' : 'style="display:none;"'; ?>><?php echo esc_html( $wishlist_count ); ?></span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </a>
                    </div>

                    <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                        <div class="header-cart">
                            <button type="button" class="cart-icon" data-toggle="cart-sidebar" aria-label="<?php esc_attr_e( 'Cart', 'wp-augoose' ); ?>">
                                <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?>
            </div>
        </div>
    </header>
    </div>
