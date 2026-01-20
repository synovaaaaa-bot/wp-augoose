<?php
/**
 * Simple Checkout Form - Direct to Payment
 * Skip billing details, go straight to order review and payment
 *
 * @package WP_Augoose
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
                    <span class="step-label">CART</span>
                </div>
                <div class="progress-step active">
                    <span class="step-number">2</span>
                    <span class="step-label">CHECKOUT</span>
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

                <div class="checkout-layout-simple">
                    
                    <!-- Left Column: Minimal Billing Info -->
                    <div class="checkout-forms-column-simple">
                        
                        <?php if ( $checkout->get_checkout_fields() ) : ?>

                            <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

                            <div class="checkout-section-simple">
                                <h2 class="section-title">CONTACT INFORMATION</h2>
                                <div class="section-fields">
                                    <?php 
                                    // Only show essential fields
                                    $fields = $checkout->get_checkout_fields( 'billing' );
                                    ?>
                                    
                                    <?php if ( isset( $fields['billing_email'] ) ) : ?>
                                        <p class="form-row form-row-wide">
                                            <label for="billing_email">
                                                <?php echo esc_html( $fields['billing_email']['label'] ); ?>
                                                <?php if ( ! empty( $fields['billing_email']['required'] ) ) : ?>
                                                    <span class="required">*</span>
                                                <?php endif; ?>
                                            </label>
                                            <input type="email" class="input-text" name="billing_email" id="billing_email" value="<?php echo esc_attr( $checkout->get_value( 'billing_email' ) ); ?>" />
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if ( isset( $fields['billing_phone'] ) ) : ?>
                                        <p class="form-row form-row-wide">
                                            <label for="billing_phone">
                                                <?php echo esc_html( $fields['billing_phone']['label'] ); ?>
                                                <?php if ( ! empty( $fields['billing_phone']['required'] ) ) : ?>
                                                    <span class="required">*</span>
                                                <?php endif; ?>
                                            </label>
                                            <input type="tel" class="input-text" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( $checkout->get_value( 'billing_phone' ) ); ?>" />
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php
                                    // Auto-fill other billing fields with defaults or hide them
                                    do_action( 'woocommerce_checkout_billing' );
                                    ?>
                                </div>
                            </div>

                            <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

                        <?php endif; ?>

                    </div>

                    <!-- Right Column: Order Summary & Payment -->
                    <div class="checkout-summary-column">
                        <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
                        
                        <div class="order-summary-wrapper">
                            <h2 class="order-summary-title">ORDER SUMMARY</h2>
                            
                            <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

                            <div id="order_review" class="woocommerce-checkout-review-order">
                                <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                            </div>

                            <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
                        </div>

                    </div>

                </div>

            </form>
        </div>
    </div>

</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>