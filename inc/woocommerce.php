<?php
/**
 * WooCommerce Compatibility File
 *
 * @package Minimal_Ecommerce
 */

/**
 * WooCommerce setup function.
 */
function minimal_ecommerce_woocommerce_setup() {
    add_theme_support('woocommerce', array(
        'thumbnail_image_width' => 400,
        'single_image_width' => 800,
        'product_grid' => array(
            'default_rows' => 3,
            'min_rows' => 1,
            'default_columns' => 4,
            'min_columns' => 1,
            'max_columns' => 6,
        ),
    ));
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'minimal_ecommerce_woocommerce_setup');

/**
 * WooCommerce specific scripts & stylesheets.
 */
function minimal_ecommerce_woocommerce_scripts() {
    wp_enqueue_style('minimal-ecommerce-woocommerce-style', MINIMAL_ECOMMERCE_URI . '/woocommerce.css', array(), MINIMAL_ECOMMERCE_VERSION);
    
    // Enqueue custom WooCommerce styles (after brand guidelines)
    wp_enqueue_style('minimal-ecommerce-woocommerce-custom', MINIMAL_ECOMMERCE_URI . '/assets/css/woocommerce-custom.css', array('minimal-ecommerce-woocommerce-style', 'augoose-brand-guidelines'), MINIMAL_ECOMMERCE_VERSION);

    $font_path = WC()->plugin_url() . '/assets/fonts/';
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

    wp_add_inline_style('minimal-ecommerce-woocommerce-style', $inline_font);
}
add_action('wp_enqueue_scripts', 'minimal_ecommerce_woocommerce_scripts');

/**
 * Disable the default WooCommerce stylesheet.
 */
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

/**
 * Add 'woocommerce-active' class to the body tag.
 */
function minimal_ecommerce_woocommerce_active_body_class($classes) {
    $classes[] = 'woocommerce-active';
    return $classes;
}
add_filter('body_class', 'minimal_ecommerce_woocommerce_active_body_class');

/**
 * Products per page.
 */
function minimal_ecommerce_woocommerce_products_per_page() {
    return 12;
}
add_filter('loop_shop_per_page', 'minimal_ecommerce_woocommerce_products_per_page');

/**
 * Product gallery thumnbail columns.
 */
function minimal_ecommerce_woocommerce_thumbnail_columns() {
    return 4;
}
add_filter('woocommerce_product_thumbnails_columns', 'minimal_ecommerce_woocommerce_thumbnail_columns');

/**
 * Default loop columns on product archives.
 */
function minimal_ecommerce_woocommerce_loop_columns() {
    return 4;
}
add_filter('loop_shop_columns', 'minimal_ecommerce_woocommerce_loop_columns');

/**
 * Related Products Args.
 */
function minimal_ecommerce_woocommerce_related_products_args($args) {
    $defaults = array(
        'posts_per_page' => 4,
        'columns' => 4,
    );

    $args = wp_parse_args($defaults, $args);

    return $args;
}
add_filter('woocommerce_output_related_products_args', 'minimal_ecommerce_woocommerce_related_products_args');

/**
 * Remove default WooCommerce wrapper.
 */
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

if (!function_exists('minimal_ecommerce_woocommerce_wrapper_before')) {
    /**
     * Before Content.
     */
    function minimal_ecommerce_woocommerce_wrapper_before() {
        ?>
        <div id="primary" class="content-area">
            <main id="main" class="site-main container" role="main">
        <?php
    }
}
add_action('woocommerce_before_main_content', 'minimal_ecommerce_woocommerce_wrapper_before');

if (!function_exists('minimal_ecommerce_woocommerce_wrapper_after')) {
    /**
     * After Content.
     */
    function minimal_ecommerce_woocommerce_wrapper_after() {
        ?>
            </main><!-- #main -->
        </div><!-- #primary -->
        <?php
    }
}
add_action('woocommerce_after_main_content', 'minimal_ecommerce_woocommerce_wrapper_after');

/**
 * Sample implementation of the WooCommerce Mini Cart.
 */
if (!function_exists('minimal_ecommerce_woocommerce_cart_link_fragment')) {
    /**
     * Cart Fragments.
     */
    function minimal_ecommerce_woocommerce_cart_link_fragment($fragments) {
        ob_start();
        minimal_ecommerce_woocommerce_cart_link();
        $fragments['a.cart-contents'] = ob_get_clean();

        return $fragments;
    }
}
add_filter('woocommerce_add_to_cart_fragments', 'minimal_ecommerce_woocommerce_cart_link_fragment');

if (!function_exists('minimal_ecommerce_woocommerce_cart_link')) {
    /**
     * Cart Link.
     */
    function minimal_ecommerce_woocommerce_cart_link() {
        ?>
        <a class="cart-contents" href="<?php echo esc_url(wc_get_cart_url()); ?>" title="<?php esc_attr_e('View your shopping cart', 'minimal-ecommerce'); ?>">
            <?php
            $item_count_text = sprintf(
                /* translators: number of items in the mini cart. */
                _n('%d item', '%d items', WC()->cart->get_cart_contents_count(), 'minimal-ecommerce'),
                WC()->cart->get_cart_contents_count()
            );
            ?>
            <span class="amount"><?php echo wp_kses_data(WC()->cart->get_cart_subtotal()); ?></span> <span class="count"><?php echo esc_html($item_count_text); ?></span>
        </a>
        <?php
    }
}

/**
 * Customize product sorting dropdown
 */
function minimal_ecommerce_woocommerce_catalog_orderby($sortby) {
    $sortby['popularity'] = __('Sort by popularity', 'minimal-ecommerce');
    $sortby['rating'] = __('Sort by average rating', 'minimal-ecommerce');
    $sortby['date'] = __('Sort by latest', 'minimal-ecommerce');
    $sortby['price'] = __('Sort by price: low to high', 'minimal-ecommerce');
    $sortby['price-desc'] = __('Sort by price: high to low', 'minimal-ecommerce');
    
    return $sortby;
}
add_filter('woocommerce_default_catalog_orderby_options', 'minimal_ecommerce_woocommerce_catalog_orderby');
add_filter('woocommerce_catalog_orderby', 'minimal_ecommerce_woocommerce_catalog_orderby');

/**
 * Customize breadcrumb defaults
 */
function minimal_ecommerce_woocommerce_breadcrumbs() {
    return array(
        'delimiter' => ' &rsaquo; ',
        'wrap_before' => '<nav class="woocommerce-breadcrumb">',
        'wrap_after' => '</nav>',
        'before' => '',
        'after' => '',
        'home' => _x('Home', 'breadcrumb', 'minimal-ecommerce'),
    );
}
add_filter('woocommerce_breadcrumb_defaults', 'minimal_ecommerce_woocommerce_breadcrumbs');

/**
 * Custom Add to Cart Button Text
 */
function minimal_ecommerce_custom_add_to_cart_text() {
    return __('Add to Cart', 'minimal-ecommerce');
}
add_filter('woocommerce_product_single_add_to_cart_text', 'minimal_ecommerce_custom_add_to_cart_text');
add_filter('woocommerce_product_add_to_cart_text', 'minimal_ecommerce_custom_add_to_cart_text');

/**
 * Customize WooCommerce Messages
 */
function minimal_ecommerce_add_to_cart_message_html($message, $products) {
    $titles = array();
    $count = 0;

    if (!is_array($products)) {
        $products = array($products => 1);
        $show_qty = false;
    } else {
        $show_qty = true;
    }

    foreach ($products as $product_id => $qty) {
        $titles[] = ($qty > 1 ? absint($qty) . ' &times; ' : '') . sprintf(_x('&ldquo;%s&rdquo;', 'Item name in quotes', 'minimal-ecommerce'), strip_tags(get_the_title($product_id)));
        $count += $qty;
    }

    $titles = array_filter($titles);
    $added_text = sprintf(_n('%s has been added to your cart.', '%s have been added to your cart.', $count, 'minimal-ecommerce'), wc_format_list_of_items($titles));

    $message = sprintf('%s <a href="%s" class="btn btn-primary">%s</a>',
        esc_html($added_text),
        esc_url(wc_get_cart_url()),
        esc_html__('View Cart', 'minimal-ecommerce')
    );

    return $message;
}
add_filter('wc_add_to_cart_message_html', 'minimal_ecommerce_add_to_cart_message_html', 10, 2);

/**
 * Change number of upsells output
 */
function minimal_ecommerce_woocommerce_upsell_display() {
    woocommerce_upsell_display(4, 4);
}
remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
add_action('woocommerce_after_single_product_summary', 'minimal_ecommerce_woocommerce_upsell_display', 15);

/**
 * Customize sale flash
 */
function minimal_ecommerce_custom_sale_flash() {
    return '<span class="onsale">' . esc_html__('Sale!', 'minimal-ecommerce') . '</span>';
}
add_filter('woocommerce_sale_flash', 'minimal_ecommerce_custom_sale_flash');

/**
 * Adjust cross-sells display
 */
function minimal_ecommerce_cross_sells_columns() {
    return 4;
}
add_filter('woocommerce_cross_sells_columns', 'minimal_ecommerce_cross_sells_columns');

function minimal_ecommerce_cross_sells_total() {
    return 4;
}
add_filter('woocommerce_cross_sells_total', 'minimal_ecommerce_cross_sells_total');
