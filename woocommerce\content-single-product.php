<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * @package Minimal_Ecommerce
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 */
do_action('woocommerce_before_single_product');

if (post_password_required()) {
    echo get_the_password_form(); // WPCS: XSS ok.
    return;
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('single-product-wrapper', $product); ?>>
    
    <div class="product-main-content">
        <div class="container">
            <div class="product-layout">
                
                <!-- Product Images -->
                <div class="product-gallery-wrapper">
                    <?php
                    /**
                     * Hook: woocommerce_before_single_product_summary.
                     *
                     * @hooked woocommerce_show_product_sale_flash - 10
                     * @hooked woocommerce_show_product_images - 20
                     */
                    do_action('woocommerce_before_single_product_summary');
                    ?>
                </div>

                <!-- Product Summary (Enhanced Figma Design) -->
                <div class="product-summary-wrapper">
                    <div class="summary entry-summary">
                        <?php
                        /**
                         * Hook: woocommerce_single_product_summary.
                         *
                         * @hooked woocommerce_template_single_title - 5
                         * @hooked woocommerce_template_single_rating - 10
                         * @hooked woocommerce_template_single_price - 10
                         * @hooked woocommerce_template_single_excerpt - 20
                         * @hooked woocommerce_template_single_add_to_cart - 30
                         * @hooked woocommerce_template_single_meta - 40
                         * @hooked woocommerce_template_single_sharing - 50
                         */
                        do_action('woocommerce_single_product_summary');
                        ?>
                        
                        <!-- Size Guide Accordion (Figma Design) -->
                        <div class="product-size-guide">
                            <button class="size-guide-toggle" type="button">
                                <span>📏 SIZE GUIDE</span>
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <path d="M4.5 6l3.5 3.5L11.5 6"></path>
                                </svg>
                            </button>
                            <div class="size-guide-content">
                                <table class="size-chart">
                                    <thead>
                                        <tr>
                                            <th>Size</th>
                                            <th>Chest (in)</th>
                                            <th>Waist (in)</th>
                                            <th>Length (in)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>S</td>
                                            <td>36-38</td>
                                            <td>30-32</td>
                                            <td>28</td>
                                        </tr>
                                        <tr>
                                            <td>M</td>
                                            <td>39-41</td>
                                            <td>33-35</td>
                                            <td>29</td>
                                        </tr>
                                        <tr>
                                            <td>L</td>
                                            <td>42-44</td>
                                            <td>36-38</td>
                                            <td>30</td>
                                        </tr>
                                        <tr>
                                            <td>XL</td>
                                            <td>45-47</td>
                                            <td>39-41</td>
                                            <td>31</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Product Features (Figma Design) -->
                        <div class="product-features">
                            <ul>
                                <li>✓ <strong>American Workwear Design</strong></li>
                                <li>✓ <strong>Premium Quality Fabric</strong></li>
                                <li>✓ <strong>Durable Construction</strong></li>
                                <li>✓ <strong>Sustainably Made in Indonesia</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="product-additional-info">
        <div class="container">
            <?php
            /**
             * Hook: woocommerce_after_single_product_summary.
             *
             * @hooked woocommerce_output_product_data_tabs - 10
             * @hooked woocommerce_upsell_display - 15
             * @hooked woocommerce_output_related_products - 20
             */
            do_action('woocommerce_after_single_product_summary');
            ?>
        </div>
    </div>

</div>

<?php do_action('woocommerce_after_single_product'); ?>
