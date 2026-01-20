<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * @package WP_Augoose
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>

<li <?php wc_product_class( 'product-item', $product ); ?>>
    <div class="product-inner">

        <?php
        // Open product link (we manage open/close explicitly to avoid hook-injected duplicates).
        if ( function_exists( 'woocommerce_template_loop_product_link_open' ) ) {
            woocommerce_template_loop_product_link_open();
        }
        ?>

        <div class="product-thumbnail" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
            <!-- Wishlist Heart Icon -->
            <div class="product-wishlist">
                <button class="wishlist-toggle add-to-wishlist" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" aria-label="Add to wishlist">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Product Badges -->
            <div class="product-badges">
                <?php
                // SALE badge (integrated with Woo data: % OFF) - rendered here so badges stack consistently.
                if ( function_exists( 'wp_augoose_custom_sale_badge' ) ) {
                    wp_augoose_custom_sale_badge();
                }

                // NEW ARRIVAL badge (integrated with WP Customizer setting).
                $new_days  = absint( get_theme_mod( 'wp_augoose_new_arrival_days', 30 ) );
                $new_label = (string) get_theme_mod( 'wp_augoose_new_arrival_label', 'New Arrival' );
                if ( $new_days > 0 ) {
                    $product_date        = $product->get_date_created();
                    $days_since_creation = $product_date ? $product_date->diff( new DateTime() )->days : 999;
                    if ( $days_since_creation <= $new_days ) {
                        echo '<span class="badge badge-new">' . esc_html( $new_label ) . '</span>';
                    }
                }
                ?>
            </div>
                    
            <?php
            // Thumbnail only (SALE badge handled above for consistent layout).
            if ( function_exists( 'woocommerce_template_loop_product_thumbnail' ) ) {
                woocommerce_template_loop_product_thumbnail();
            }
            ?>
        </div>

        <div class="product-info">
            <?php
            /**
             * Hook: woocommerce_shop_loop_item_title.
             *
             * @hooked woocommerce_template_loop_product_title - 10
             */
            do_action( 'woocommerce_shop_loop_item_title' );
            ?>

            <?php
            /**
             * Hook: woocommerce_after_shop_loop_item_title.
             *
             * @hooked woocommerce_template_loop_rating - 5
             * @hooked woocommerce_template_loop_price - 10
             */
            do_action( 'woocommerce_after_shop_loop_item_title' );
            ?>
        </div>

        <?php
        // Close product link before swatches/CTA (so CTA stays independent).
        if ( function_exists( 'woocommerce_template_loop_product_link_close' ) ) {
            woocommerce_template_loop_product_link_close();
        }
        ?>
        
        <!-- Color Swatches (WooCommerce Data) -->
        <?php
        $swatches = array();
        $color_attr = '';
        $variation_attrs = array();

        if ( $product->is_type( 'variable' ) ) {
            $variation_attrs = $product->get_variation_attributes();
            foreach ( $variation_attrs as $attr_name => $options ) {
                if ( false !== stripos( $attr_name, 'color' ) || false !== stripos( $attr_name, 'colour' ) ) {
                    $color_attr = $attr_name;
                    break;
                }
            }

            if ( $color_attr ) {
                foreach ( $product->get_available_variations() as $variation ) {
                    $attr_val = isset( $variation['attributes'][ $color_attr ] ) ? $variation['attributes'][ $color_attr ] : '';
                    if ( ! $attr_val ) {
                        continue;
                    }
                    $key = sanitize_title( $attr_val );
                    if ( isset( $swatches[ $key ] ) ) {
                        continue;
                    }
                    $swatches[ $key ] = array(
                        'label' => $attr_val,
                        'image' => isset( $variation['image'] ) ? $variation['image'] : array(),
                        'taxonomy' => $color_attr,
                    );
                }
            }
        }

        if ( ! empty( $swatches ) ) :
            $max_swatches = 6;
            $swatch_count = count( $swatches );
            $display_swatches = array_slice( $swatches, 0, $max_swatches, true );
            ?>
            <div class="product-color-options">
                <div class="color-swatches">
                    <?php foreach ( $display_swatches as $swatch ) :
                        $label = $swatch['label'];
                        $image = $swatch['image'];
                        $image_url = isset( $image['src'] ) ? $image['src'] : '';
                        $image_srcset = isset( $image['srcset'] ) ? $image['srcset'] : '';
                        $style = '';
                        $color_value = '';

                        // Try to resolve a color from taxonomy term meta if available.
                        if ( taxonomy_exists( str_replace( 'attribute_', '', $swatch['taxonomy'] ) ) ) {
                            $tax = str_replace( 'attribute_', '', $swatch['taxonomy'] );
                            $term = get_term_by( 'slug', sanitize_title( $label ), $tax );
                            if ( $term ) {
                                $meta_color = get_term_meta( $term->term_id, 'color', true );
                                if ( ! $meta_color ) {
                                    $meta_color = get_term_meta( $term->term_id, 'colour', true );
                                }
                                if ( ! $meta_color ) {
                                    $meta_color = get_term_meta( $term->term_id, 'swatch_color', true );
                                }
                                if ( $meta_color ) {
                                    $color_value = $meta_color;
                                }
                            }
                        }

                        if ( ! $color_value ) {
                            $normalized = strtolower( trim( $label ) );
                            if ( preg_match( '/^#?[0-9a-f]{3,6}$/i', $normalized ) ) {
                                $color_value = $normalized[0] === '#' ? $normalized : '#' . $normalized;
                            } elseif ( preg_match( '/^[a-z]+$/i', $normalized ) ) {
                                $color_value = $normalized;
                            }
                        }

                        if ( $color_value ) {
                            $style = 'style="background-color: ' . esc_attr( $color_value ) . ';"';
                        }
                        ?>
                        <span
                            class="color-swatch"
                            <?php echo $style; ?>
                            title="<?php echo esc_attr( $label ); ?>"
                            data-image-url="<?php echo esc_url( $image_url ); ?>"
                            data-image-srcset="<?php echo esc_attr( $image_srcset ); ?>"
                        ></span>
                    <?php endforeach; ?>
                    <?php if ( $swatch_count > $max_swatches ) : ?>
                        <span class="color-count">+<?php echo esc_html( $swatch_count - $max_swatches ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Add to Cart Button -->
        <div class="product-add-to-cart">
            <?php
            if ( $product->is_type( 'simple' ) && $product->is_in_stock() ) {
                ?>
                <form class="cart" action="<?php echo esc_url( $product->get_permalink() ); ?>" method="post" enctype='multipart/form-data'>
                    <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />
                    <button type="submit" class="add-to-cart-btn" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <span>ADD TO CART</span>
                    </button>
                </form>
                <?php
            } else {
                ?>
                <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="view-product-btn">
                    VIEW PRODUCT
                </a>
                <?php
            }
            ?>
        </div>

        <?php
        // Intentionally not calling `woocommerce_after_shop_loop_item` here.
        // We render exactly one CTA above (ADD TO CART or VIEW PRODUCT) and avoid duplicate buttons/links.
        ?>
    </div>
</li>