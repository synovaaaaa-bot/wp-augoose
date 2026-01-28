<?php
/**
 * Cart Page - Simple & Clean
 *
 * @package WP_Augoose
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<div class="cart-page-simple">
    <div class="container">
        
        <h1 class="cart-page-title">YOUR CART</h1>
        
        <?php if ( WC()->cart->is_empty() ) : ?>
            
            <div class="cart-empty">
                <p class="cart-empty-message"><?php esc_html_e( 'Your cart is currently empty.', 'woocommerce' ); ?></p>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="return-to-shop">
                    <?php esc_html_e( 'Return to shop', 'woocommerce' ); ?>
                </a>
            </div>
            
        <?php else : ?>
            
            <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
                <?php do_action( 'woocommerce_before_cart_table' ); ?>

                <div class="cart-items-simple">
                    <?php
                    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                        if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                            $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                            ?>
                            <div class="cart-item-simple">
                                
                                <!-- Product Image -->
                                <div class="cart-item-image">
                                    <?php
                                    $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                                    if ( $product_permalink ) {
                                        printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
                                    } else {
                                        echo $thumbnail;
                                    }
                                    ?>
                                </div>

                                <!-- Product Details -->
                                <div class="cart-item-details">
                                    <div class="cart-item-name">
                                        <?php
                                        if ( $product_permalink ) {
                                            echo '<a href="' . esc_url( $product_permalink ) . '">' . wp_kses_post( $_product->get_name() ) . '</a>';
                                        } else {
                                            echo wp_kses_post( $_product->get_name() );
                                        }
                                        ?>
                                    </div>
                                    
                                    <div class="cart-item-price">
                                        <?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?>
                                    </div>
                                    
                                    <?php
                                    // Variation details
                                    $variation_data = wc_get_formatted_cart_item_data( $cart_item );
                                    if ( $variation_data ) {
                                        echo '<div class="cart-item-variation">' . $variation_data . '</div>';
                                    }
                                    ?>
                                    
                                    <!-- Quantity & Remove -->
                                    <div class="cart-item-actions">
                                        <div class="quantity-simple">
                                            <?php
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

                                            echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
                                            ?>
                                        </div>
                                        
                                        <a href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>" class="remove-item">
                                            Remove
                                        </a>
                                    </div>
                                </div>

                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>

                <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                <?php do_action( 'woocommerce_after_cart_table' ); ?>
            </form>

            <!-- Cart Totals -->
            <div class="cart-totals-simple">
                <div class="cart-total-row">
                    <span class="total-label">Total</span>
                    <span class="total-amount"><?php wc_cart_totals_order_total_html(); ?></span>
                </div>
                
                <?php
                // Display currency conversion notice if items were converted
                $show_notice = false;
                $original_currency = null;
                $current_currency = get_woocommerce_currency();
                
                // Always show notice if current currency is IDR (items were converted)
                // This ensures note appears even if original currency is not found
                if ( $current_currency === 'IDR' ) {
                    $show_notice = true;
                    
                    // Try to get original currency from multiple sources
                    if ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) {
                        // 1. Check cart items
                        foreach ( WC()->cart->get_cart() as $cart_item ) {
                            if ( isset( $cart_item['wp_augoose_original_currency'] ) ) {
                                $original_currency = $cart_item['wp_augoose_original_currency'];
                                if ( in_array( $original_currency, array( 'SGD', 'MYR' ), true ) ) {
                                    break;
                                }
                            }
                        }
                    }
                    
                    // 2. Check session
                    if ( ! $original_currency && function_exists( 'WC' ) && WC()->session ) {
                        $original_currency = WC()->session->get( 'wp_augoose_original_currency' );
                    }
                    
                    // 3. Check cookie
                    if ( ! $original_currency && isset( $_COOKIE['wp_augoose_currency'] ) ) {
                        $cookie_currency = strtoupper( trim( sanitize_text_field( $_COOKIE['wp_augoose_currency'] ) ) );
                        if ( in_array( $cookie_currency, array( 'SGD', 'MYR' ), true ) ) {
                            $original_currency = $cookie_currency;
                        }
                    }
                }
                
                // (Currency conversion notice for cart removed as per latest requirement.)
                ?>
                
                <!-- Loading indicator for currency conversion -->
                <div class="cart-currency-conversion-loading" style="display: none;">
                    <svg class="spinner" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="31.416">
                            <animate attributeName="stroke-dasharray" dur="2s" values="0 31.416;15.708 15.708;0 31.416;0 31.416" repeatCount="indefinite"/>
                            <animate attributeName="stroke-dashoffset" dur="2s" values="0;-15.708;-31.416;-31.416" repeatCount="indefinite"/>
                        </circle>
                    </svg>
                    <span>Converting prices to IDR...</span>
                </div>
                
                <div class="cart-buttons">
                    <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="btn-view-cart">View Cart</a>
                    <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="btn-checkout">Checkout</a>
                </div>
            </div>

        <?php endif; ?>

    </div>
</div>
