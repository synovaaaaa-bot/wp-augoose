<?php
/**
 * Empty Cart Page
 *
 * @package WP_Augoose
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<div class="cart-page-wrapper">
    <div class="container">
        <h1 class="cart-page-title">CART</h1>
        
        <div class="cart-empty">
            <p class="cart-empty-message"><?php esc_html_e( 'Your cart is currently empty.', 'woocommerce' ); ?></p>
            <p class="return-to-shop">
                <a class="button wc-backward<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
                    <?php esc_html_e( 'Return to shop', 'woocommerce' ); ?>
                </a>
            </p>
        </div>
    </div>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
