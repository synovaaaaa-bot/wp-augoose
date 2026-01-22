<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * @package WP_Augoose
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'single-product-wrapper', $product ); ?>>
    
    <div class="product-main-content">
        <div class="container">
            <div class="product-layout">
                
                <!-- Product Images -->
                <div class="product-gallery-wrapper">
                    <?php
                    // Custom gallery layout - all images in portrait vertical layout
                    $image_ids = array();
                    $thumb_id  = $product ? (int) $product->get_image_id() : 0;
                    if ( $thumb_id ) {
                        $image_ids[] = $thumb_id;
                    }
                    $gallery_ids = $product ? (array) $product->get_gallery_image_ids() : array();
                    foreach ( $gallery_ids as $gid ) {
                        $gid = (int) $gid;
                        if ( $gid && ! in_array( $gid, $image_ids, true ) ) {
                            $image_ids[] = $gid;
                        }
                    }

                    // Sale flash (keep WooCommerce logic)
                    if ( function_exists( 'woocommerce_show_product_sale_flash' ) ) {
                        woocommerce_show_product_sale_flash();
                    }
                    ?>

                    <div class="product-gallery-portrait <?php echo ( count( $image_ids ) === 1 ) ? 'is-single' : ''; ?>">
                        <?php
                        if ( ! empty( $image_ids ) ) {
                            foreach ( $image_ids as $iid ) {
                                echo '<div class="product-gallery-two__item">';
                                echo wp_get_attachment_image( $iid, 'large', false, array( 'class' => 'product-gallery-two__img' ) );
                                echo '</div>';
                            }
                        } else {
                            // Fallback to default images if none set.
                            do_action( 'woocommerce_before_single_product_summary' );
                        }
                        ?>
                    </div>
                </div>

                <!-- Product Summary (Figma Design) -->
                <div class="product-summary-wrapper">
                    <div class="summary entry-summary">
                        
                        <!-- Product Title -->
                        <h1 class="product_title entry-title"><?php echo esc_html( $product->get_name() ); ?></h1>
                        
                        <!-- Product Price -->
                        <div class="price">
                            <?php echo $product->get_price_html(); ?>
                        </div>
                        
                        <!-- Product Description with Read More -->
                        <?php
                        $short_description = $product->get_short_description();
                        if ( ! empty( $short_description ) ) {
                            $description_length = strlen( wp_strip_all_tags( $short_description ) );
                            $max_length = 150; // Characters before showing "read more"
                            
                            if ( $description_length > $max_length ) {
                                $truncated = wp_strip_all_tags( $short_description );
                                $short_text = mb_substr( $truncated, 0, $max_length ) . '...';
                                $full_text = $short_description;
                                ?>
                                <div class="product-description-summary">
                                    <div class="product-description-short">
                                        <?php echo esc_html( $short_text ); ?>
                                    </div>
                                    <div class="product-description-full" style="display: none;">
                                        <?php echo wp_kses_post( apply_filters( 'the_content', $full_text ) ); ?>
                                    </div>
                                    <button type="button" class="read-more-toggle" data-expanded="false">
                                        <span class="read-more-text">READ MORE</span>
                                        <span class="read-less-text" style="display: none;">READ LESS</span>
                                    </button>
                                </div>
                                <?php
                            } else {
                                ?>
                                <div class="product-description-summary">
                                    <div class="product-description-full">
                                        <?php echo wp_kses_post( apply_filters( 'the_content', $short_description ) ); ?>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                        
                        <!-- Variations Form -->
                        <?php
                        if ( $product->is_type( 'variable' ) ) {
                            woocommerce_variable_add_to_cart();
                        } else {
                            woocommerce_simple_add_to_cart();
                        }
                        ?>
                        
                        <!-- Shipping & Material Info (Below Add to Cart) -->
                        <div class="product-shipping-material-info">
                            <div class="shipping-info">
                                <strong>SHIPPING</strong>
                                <p>Free shipping on orders over $200</p>
                            </div>
                            
                            <?php
                            // Get material attribute from WooCommerce - try multiple methods
                            $material_attr = '';
                            
                            // Method 1: Try taxonomy attribute (pa_material)
                            if ( method_exists( $product, 'get_attribute' ) ) {
                                $material_attr = $product->get_attribute( 'pa_material' );
                            }
                            
                            // Method 2: Try custom attribute (material)
                            if ( empty( $material_attr ) && method_exists( $product, 'get_attribute' ) ) {
                                $material_attr = $product->get_attribute( 'material' );
                            }
                            
                            // Method 3: Search through all attributes
                            if ( empty( $material_attr ) && method_exists( $product, 'get_attributes' ) ) {
                                $attributes = $product->get_attributes();
                                foreach ( $attributes as $attr_name => $attr_obj ) {
                                    $attr_label = wc_attribute_label( $attr_name );
                                    // Check if attribute name or label contains "material"
                                    if ( stripos( $attr_name, 'material' ) !== false || stripos( $attr_label, 'material' ) !== false ) {
                                        $material_attr = $product->get_attribute( $attr_name );
                                        break;
                                    }
                                }
                            }
                            
                            // Method 4: Try product meta/custom fields
                            if ( empty( $material_attr ) ) {
                                $product_id = $product->get_id();
                                $material_attr = get_post_meta( $product_id, '_material', true );
                                if ( empty( $material_attr ) ) {
                                    $material_attr = get_post_meta( $product_id, 'material', true );
                                }
                                if ( empty( $material_attr ) ) {
                                    $material_attr = get_post_meta( $product_id, '_product_material', true );
                                }
                            }
                            
                            // Method 5: Try ACF field if available
                            if ( empty( $material_attr ) && function_exists( 'get_field' ) ) {
                                $material_attr = get_field( 'material', $product->get_id() );
                            }
                            
                            // Always display material section (even if empty, for consistency)
                            echo '<div class="material-info">';
                            echo '<strong>MATERIAL</strong>';
                            if ( ! empty( $material_attr ) ) {
                                echo '<p>' . esc_html( wp_strip_all_tags( $material_attr ) ) . '</p>';
                            } else {
                                // Show placeholder or leave empty
                                echo '<p>' . esc_html__( 'Material information not available.', 'wp-augoose' ) . '</p>';
                            }
                            echo '</div>';
                            ?>
                        </div>
                        
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="product-additional-info">
        <div class="container">
            
            <!-- Related Products -->
            <div class="related-products-section">
                <h2>YOU MAY ALSO LIKE</h2>
                <?php
                // Get related products
                $related_ids = wc_get_related_products( $product->get_id(), 4 );
                
                if ( ! empty( $related_ids ) ) {
                    $args = array(
                        'post_type'      => 'product',
                        'posts_per_page' => 4,
                        'post__in'        => $related_ids,
                        'orderby'         => 'post__in',
                    );
                    
                    $related_products = new WP_Query( $args );
                    
                    if ( $related_products->have_posts() ) {
                        echo '<ul class="products related-products-grid">';
                        while ( $related_products->have_posts() ) {
                            $related_products->the_post();
                            wc_get_template_part( 'content', 'product' );
                        }
                        echo '</ul>';
                        wp_reset_postdata();
                    }
                } else {
                    // Fallback: show recent products if no related products
                    $args = array(
                        'post_type'      => 'product',
                        'posts_per_page' => 4,
                        'orderby'        => 'date',
                        'order'          => 'DESC',
                        'post__not_in'   => array( $product->get_id() ),
                    );
                    
                    $recent_products = new WP_Query( $args );
                    
                    if ( $recent_products->have_posts() ) {
                        echo '<ul class="products related-products-grid">';
                        while ( $recent_products->have_posts() ) {
                            $recent_products->the_post();
                            wc_get_template_part( 'content', 'product' );
                        }
                        echo '</ul>';
                        wp_reset_postdata();
                    }
                }
                ?>
            </div>
            
        </div>
    </div>

</div>

<?php do_action('woocommerce_after_single_product'); ?>
