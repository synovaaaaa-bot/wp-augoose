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
    
    <!-- Breadcrumb -->
    <div class="product-breadcrumb">
        <div class="container">
            <?php
            if ( function_exists( 'woocommerce_breadcrumb' ) ) {
                woocommerce_breadcrumb( array(
                    'delimiter'   => ' / ',
                    'wrap_before' => '<nav class="woocommerce-breadcrumb">',
                    'wrap_after'  => '</nav>',
                    'before'      => '',
                    'after'       => '',
                    'home'        => 'Home',
                ) );
            }
            ?>
        </div>
    </div>
    
    <div class="product-main-content">
        <div class="container">
            <div class="product-layout">
                
                <!-- Product Images -->
                <div class="product-gallery-wrapper">
                    <?php
                    // Custom 2-image layout (front/back). Uses featured image + first gallery image.
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
                        if ( count( $image_ids ) >= 2 ) {
                            break;
                        }
                    }

                    // Sale flash (keep WooCommerce logic)
                    if ( function_exists( 'woocommerce_show_product_sale_flash' ) ) {
                        woocommerce_show_product_sale_flash();
                    }
                    ?>

                    <div class="product-gallery-two <?php echo ( count( $image_ids ) === 1 ) ? 'is-single' : ''; ?>">
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
                            // Get material attribute
                            $material_attr = '';
                            if ( method_exists( $product, 'get_attribute' ) ) {
                                $material_attr = $product->get_attribute( 'pa_material' );
                                if ( ! $material_attr ) {
                                    $material_attr = $product->get_attribute( 'material' );
                                }
                            }
                            
                            if ( $material_attr ) {
                                echo '<div class="material-info">';
                                echo '<strong>MATERIAL</strong>';
                                echo '<p>' . esc_html( wp_strip_all_tags( $material_attr ) ) . '</p>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                        
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="product-additional-info">
        <div class="container">
            
            <!-- Custom Tabs -->
            <div class="product-tabs-custom">
                <ul class="tabs-nav">
                    <li class="active"><a href="#tab-details">DETAILS</a></li>
                    <li><a href="#tab-shipping">SHIPPING</a></li>
                </ul>
                <div class="tabs-content">
                    <div id="tab-details" class="tab-panel active">
                        <?php
                        // DETAILS tab: Show product description (full content) only
                        $content = get_the_content();
                        if ( ! empty( $content ) ) {
                            echo '<div class="product-full-description">';
                            echo wp_kses_post( apply_filters( 'the_content', $content ) );
                            echo '</div>';
                        } else {
                            echo '<p>' . esc_html__( 'No description available.', 'wp-augoose' ) . '</p>';
                        }
                        ?>
                    </div>
                    <div id="tab-shipping" class="tab-panel">
                        <?php
                        // Shipping tab: show your product shipping class + link to shop shipping/returns pages if present.
                        $shipping_class = $product->get_shipping_class();
                        if ( $shipping_class ) {
                            $term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
                            if ( $term && ! is_wp_error( $term ) ) {
                                echo '<h3>Shipping Class</h3>';
                                echo '<p>' . esc_html( $term->name ) . '</p>';
                            }
                        }

                        // Optional: show product weight/dimensions if set
                        $weight = $product->get_weight();
                        $dims   = wc_format_dimensions( $product->get_dimensions( false ) );
                        if ( $weight || $dims ) {
                            echo '<h3>Package</h3>';
                            echo '<p>';
                            if ( $weight ) {
                                echo esc_html__( 'Weight:', 'wp-augoose' ) . ' ' . esc_html( $weight ) . ' ' . esc_html( get_option( 'woocommerce_weight_unit' ) ) . '<br>';
                            }
                            if ( $dims && $dims !== 'N/A' ) {
                                echo esc_html__( 'Dimensions:', 'wp-augoose' ) . ' ' . esc_html( $dims );
                            }
                            echo '</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
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
