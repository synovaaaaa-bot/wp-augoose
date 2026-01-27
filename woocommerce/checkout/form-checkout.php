<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WP_Augoose
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>

<div class="checkout-page-wrapper">
    
    <!-- Checkout Progress Indicator -->
    <div class="checkout-progress">
        <div class="container">
            <div class="progress-steps">
                <div class="progress-step active">
                    <span class="step-number">1</span>
                    <span class="step-label">INFORMATION</span>
                </div>
                <div class="progress-step">
                    <span class="step-number">2</span>
                    <span class="step-label">SHIPPING</span>
                </div>
                <div class="progress-step">
                    <span class="step-number">3</span>
                    <span class="step-label">PAYMENT</span>
                </div>
            </div>
        </div>
    </div>

    <div class="checkout-content">
        <div class="container">
            <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

                <div class="checkout-layout">
                    
                    <!-- Left Column: Forms -->
                    <div class="checkout-forms-column">
                        
                        <?php if ( $checkout->get_checkout_fields() ) : ?>

                            <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

                            <div class="col2-set" id="customer_details">
                                
                                <!-- Contact Information & Billing -->
                                <div class="col-1">
                                    <div class="checkout-section">
                                        <h2 class="section-title">BILLING DETAILS</h2>
                                        <div class="section-fields">
                                            <?php do_action( 'woocommerce_checkout_billing' ); ?>
                                        </div>
                                        <p class="section-note">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                                <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 14c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6zm-1-9h2v4h-2V5zm0 5h2v2H7v-2z"/>
                                            </svg>
                                            Email notification will be sent for order confirmation
                                        </p>
                                    </div>
                                </div>

                                <!-- Shipping Address -->
                                <div class="col-2">
                                    <div class="checkout-section">
                                        <h2 class="section-title">SHIPPING ADDRESS</h2>
                                        <div class="section-fields">
                                            <?php do_action( 'woocommerce_checkout_shipping' ); ?>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>

                            <?php
                            // Additional information (order notes) - render in its own section (NOT inside shipping).
                            $order_fields = $checkout->get_checkout_fields( 'order' );
                            if ( ! empty( $order_fields ) && apply_filters( 'woocommerce_enable_order_notes_field', true ) ) :
                                ?>
                                <div class="checkout-section checkout-section--additional">
                                    <h2 class="section-title">ADDITIONAL INFORMATION</h2>
                                    <div class="section-fields">
                                        <?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>
                                        <?php
                                        foreach ( $order_fields as $key => $field ) {
                                            woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
                                        }
                                        ?>
                                        <?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php /* Shipping method section intentionally hidden (no customer choice). */ ?>


                            <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

                        <?php endif; ?>

                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="checkout-summary-column">
                        <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
                        
                        <div class="order-summary-wrapper">
                            <h2 class="order-summary-title">ORDER SUMMARY</h2>
                            
                            <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

                            <div id="order_review" class="woocommerce-checkout-review-order">
                                <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                            </div>
                            
                            <!-- Payment Method -->
                            <div class="checkout-payment-section">
                                <h2 class="section-title">PAYMENT METHOD</h2>
                                <div class="section-fields">
                                    <?php do_action( 'woocommerce_checkout_before_payment' ); ?>
                                    <div id="payment" class="woocommerce-checkout-payment">
                                        <?php do_action( 'woocommerce_checkout_payment' ); ?>
                                    </div>
                                    <?php do_action( 'woocommerce_checkout_after_payment' ); ?>
                                </div>
                                <p class="section-note">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 14c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6zm-1-9h2v4h-2V5zm0 5h2v2H7v-2z"/>
                                    </svg>
                                    Integrated payment gateway ensures secure transactions
                                </p>
                            </div>

                            <!-- Place Order Button -->
                            <div class="checkout-place-order-section">
                                <?php
                                $order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) );
                                ?>
                                
                                <button type="submit" class="button alt checkout-place-order-btn<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="woocommerce_checkout_place_order" id="place_order" value="<?php echo esc_attr( $order_button_text ); ?>" data-value="<?php echo esc_attr( $order_button_text ); ?>">
                                    <?php echo esc_html( $order_button_text ); ?>
                                </button>
                                
                                
                                <p class="checkout-security-note">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 14c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6zm-1-9h2v4h-2V5zm0 5h2v2H7v-2z"/>
                                    </svg>
                                    Secure checkout + Email confirmation sent
                                </p>
                                
                                <?php do_action( 'woocommerce_review_order_after_submit' ); ?>
                                
                                <?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
                            </div>

                            <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
                            
                            <!-- Coupon Code - Below Order Summary -->
                            <?php if ( wc_coupons_enabled() ) : ?>
                                <div class="checkout-coupon-section">
                                    <h2 class="section-title">COUPON CODE</h2>
                                    <div class="section-fields">
                                        <div class="checkout-coupon">
                                            <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Enter coupon code', 'woocommerce' ); ?>" />
                                            <button type="submit" class="button apply-coupon-btn" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_attr_e( 'Apply', 'woocommerce' ); ?></button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                        </div>

                    </div>

                </div>

            </form>
        </div>
    </div>

</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
