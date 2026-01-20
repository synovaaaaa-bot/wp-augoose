<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * @package Minimal_Ecommerce
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $product;

// Ensure visibility.
if (empty($product) || !$product->is_visible()) {
    return;
}
?>

<li <?php wc_product_class('product-item', $product); ?>>
    <div class="product-inner">
        
        <?php
        /**
         * Hook: woocommerce_before_shop_loop_item.
         */
        do_action('woocommerce_before_shop_loop_item');
        ?>
        
        <div class="product-thumbnail" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
            <a href="<?php echo esc_url($product->get_permalink()); ?>" class="product-image-link">
                <?php
                // Get product gallery images for auto-slide
                $attachment_ids = $product->get_gallery_image_ids();
                $main_image_id = $product->get_image_id();
                
                // Create array of all images
                $all_images = array();
                if ($main_image_id) {
                    $all_images[] = $main_image_id;
                }
                $all_images = array_merge($all_images, $attachment_ids);
                
                if (!empty($all_images)) : ?>
                    <div class="product-images-slider" data-total-images="<?php echo count($all_images); ?>">
                        <?php foreach ($all_images as $index => $attachment_id) : 
                            $image_url = wp_get_attachment_image_url($attachment_id, 'woocommerce_thumbnail');
                            $active_class = ($index === 0) ? 'active' : '';
                            ?>
                            <img 
                                src="<?php echo esc_url($image_url); ?>" 
                                alt="<?php echo esc_attr($product->get_name()); ?>" 
                                class="product-image <?php echo esc_attr($active_class); ?>"
                                data-index="<?php echo esc_attr($index); ?>"
                            />
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($all_images) > 1) : ?>
                        <div class="image-indicators">
                            <?php for ($i = 0; $i < count($all_images); $i++) : ?>
                                <span class="indicator <?php echo ($i === 0) ? 'active' : ''; ?>" data-index="<?php echo esc_attr($i); ?>"></span>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php else : 
                    // Fallback to default WooCommerce image
                    do_action('woocommerce_before_shop_loop_item_title');
                endif;
                ?>
            </a>
            
            <!-- Product Actions Overlay -->
            <div class="product-actions-overlay">
                <?php if ($product->is_type('simple')) : ?>
                    <button 
                        class="btn-add-to-cart ajax-add-to-cart" 
                        data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                        data-quantity="1"
                        aria-label="<?php esc_attr_e('Add to cart', 'minimal-ecommerce'); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <span><?php esc_html_e('Add to Cart', 'minimal-ecommerce'); ?></span>
                    </button>
                <?php else : ?>
                    <a href="<?php echo esc_url($product->get_permalink()); ?>" class="btn-view-product">
                        <?php echo esc_html($product->add_to_cart_text()); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="product-info">
            <?php
            /**
             * Hook: woocommerce_shop_loop_item_title.
             *
             * @hooked woocommerce_template_loop_product_title - 10
             */
            do_action('woocommerce_shop_loop_item_title');
            ?>

            <?php
            /**
             * Hook: woocommerce_after_shop_loop_item_title.
             *
             * @hooked woocommerce_template_loop_rating - 5
             * @hooked woocommerce_template_loop_price - 10
             */
            do_action('woocommerce_after_shop_loop_item_title');
            ?>
        </div>

        <?php
        /**
         * Hook: woocommerce_after_shop_loop_item.
         */
        do_action('woocommerce_after_shop_loop_item');
        ?>
    </div>
</li>
