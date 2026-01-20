<?php
/**
 * Mini Cart Template - Sidebar Slide from Right
 *
 * @package WP_Augoose
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'woocommerce_mini_cart' ) ) {
    return;
}
?>

<div class="cart-sidebar-overlay"></div>

<div class="woocommerce widget_shopping_cart">
    <div class="cart-sidebar-header">
        <h2 class="cart-sidebar-title">KERANJANG ANDA</h2>
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
                    <div class="cart-sidebar-item">
                        <div class="cart-sidebar-item-image">
                            <?php
                            $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                            if ( $product_permalink ) {
                                printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
                            } else {
                                echo $thumbnail;
                            }
                            ?>
                        </div>
                        
                        <div class="cart-sidebar-item-details">
                            <div class="cart-sidebar-item-name">
                                <?php
                                if ( $product_permalink ) {
                                    echo '<a href="' . esc_url( $product_permalink ) . '">' . wp_kses_post( $_product->get_name() ) . '</a>';
                                } else {
                                    echo wp_kses_post( $_product->get_name() );
                                }
                                ?>
                            </div>
                            
                            <?php
                            $variation_data = wc_get_formatted_cart_item_data( $cart_item );
                            if ( $variation_data ) {
                                echo '<div class="cart-sidebar-item-variation">' . $variation_data . '</div>';
                            }
                            ?>
                            
                            <div class="cart-sidebar-item-price">
                                <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?>
                            </div>
                            
                            <!-- Quantity Selector & Remove -->
                            <?php
                            $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                            
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
            echo '<p class="cart-empty-message">' . esc_html__( 'Your cart is empty.', 'woocommerce' ) . '</p>';
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
                <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="cart-sidebar-btn cart-sidebar-btn-view">View Cart</a>
                <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="cart-sidebar-btn cart-sidebar-btn-checkout">Pembayaran</a>
            </div>
        </div>
    <?php endif; ?>
</div>