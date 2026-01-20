<?php
/**
 * The Template for displaying product archives, including the main shop page
 * Matches Figma Design: https://fox-oak-07790223.figma.site/
 *
 * @package Minimal_Ecommerce
 * @version 8.6.0
 */

defined('ABSPATH') || exit;

get_header('shop');

/**
 * Hook: woocommerce_before_main_content.
 */
do_action('woocommerce_before_main_content');

// Get current view from cookie or default to 4
$grid_columns = isset($_COOKIE['grid_columns']) ? intval($_COOKIE['grid_columns']) : 4;

?>

<!-- Announcement Banner (Figma Design) -->
<div class="shop-announcement" style="padding: 12px 0; background-color: #FFFFFF; border-bottom: 1px solid #D1D5DB; text-align: center;">
    <div class="container">
        <p style="margin: 0; font-size: 0.875rem; color: #30475E; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">
            INTERNATIONAL & DOMESTIC SHIPPING AVAILABLE • FREE SHIPPING OVER $200
        </p>
    </div>
</div>

<div class="shop-header">
    <div class="container">
        <?php if (apply_filters('woocommerce_show_page_title', true)) : ?>
            <h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
        <?php endif; ?>

        <p class="shop-product-count" style="color: #000; opacity: 0.6; font-size: 0.875rem;">
            <?php
            $term = get_queried_object();
            if ($term && isset($term->count)) {
                echo esc_html($term->count) . ' ' . _n('product', 'products', $term->count, 'minimal-ecommerce');
            } else {
                echo esc_html(wp_count_posts('product')->publish) . ' products';
            }
            ?>
        </p>

        <?php
        /**
         * Hook: woocommerce_archive_description.
         */
        do_action('woocommerce_archive_description');
        ?>
    </div>
</div>

<div class="shop-container">
    <div class="container">
        <?php if (woocommerce_product_loop()) : ?>

            <div class="shop-layout">
                <!-- Sidebar Filters (Figma Design) -->
                <aside class="shop-sidebar">
                    <div class="filters-wrapper">
                        <h3 class="filter-title">CATEGORY</h3>
                        <?php
                        $product_categories = get_terms(array(
                            'taxonomy' => 'product_cat',
                            'hide_empty' => true,
                        ));
                        
                        if (!empty($product_categories)) :
                            echo '<ul class="filter-list">';
                            echo '<li><label><input type="radio" name="category" value="all" checked> All</label></li>';
                            foreach ($product_categories as $category) :
                                echo '<li><label><input type="radio" name="category" value="' . esc_attr($category->slug) . '"> ' . esc_html($category->name) . '</label></li>';
                            endforeach;
                            echo '</ul>';
                        endif;
                        ?>
                        
                        <h3 class="filter-title">SIZE</h3>
                        <div class="size-filters">
                            <?php
                            $sizes = array('S', 'M', 'L', 'XL');
                            foreach ($sizes as $size) :
                                echo '<button class="size-btn" data-size="' . esc_attr($size) . '">' . esc_html($size) . '</button>';
                            endforeach;
                            ?>
                        </div>
                        
                        <h3 class="filter-title">COLOR</h3>
                        <ul class="filter-list">
                            <li><label><input type="checkbox" name="color" value="black"> Black</label></li>
                            <li><label><input type="checkbox" name="color" value="blue"> Blue</label></li>
                            <li><label><input type="checkbox" name="color" value="khaki"> Khaki</label></li>
                            <li><label><input type="checkbox" name="color" value="olive"> Olive</label></li>
                        </ul>
                        
                        <h3 class="filter-title">PRICE RANGE</h3>
                        <div class="price-range">
                            <div class="price-inputs">
                                <input type="number" id="price-min" placeholder="Min" value="0" min="0">
                                <span>-</span>
                                <input type="number" id="price-max" placeholder="Max" value="500" max="1000">
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Products Area -->
                <div class="shop-main">
                    <div class="shop-toolbar">
                        <div class="shop-toolbar-left">
                            <p class="showing-results">
                                Showing <?php echo esc_html(wc_get_loop_prop('total')); ?> 
                                <?php echo _n('product', 'products', wc_get_loop_prop('total'), 'minimal-ecommerce'); ?>
                            </p>
                        </div>
                        
                        <div class="shop-toolbar-right">
                            <!-- Grid Toggle (Figma Design) -->
                            <div class="grid-toggle">
                                <button class="grid-btn <?php echo $grid_columns === 2 ? 'active' : ''; ?>" data-columns="2" aria-label="2 columns">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                        <rect x="0" y="0" width="7" height="7"/>
                                        <rect x="9" y="0" width="7" height="7"/>
                                        <rect x="0" y="9" width="7" height="7"/>
                                        <rect x="9" y="9" width="7" height="7"/>
                                    </svg>
                                </button>
                                <button class="grid-btn <?php echo $grid_columns === 3 ? 'active' : ''; ?>" data-columns="3" aria-label="3 columns">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                        <rect x="0" y="0" width="4" height="4"/>
                                        <rect x="6" y="0" width="4" height="4"/>
                                        <rect x="12" y="0" width="4" height="4"/>
                                    </svg>
                                </button>
                                <button class="grid-btn <?php echo $grid_columns === 4 ? 'active' : ''; ?>" data-columns="4" aria-label="4 columns">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                        <rect x="0" y="0" width="3" height="3"/>
                                        <rect x="4.5" y="0" width="3" height="3"/>
                                        <rect x="9" y="0" width="3" height="3"/>
                                        <rect x="13.5" y="0" width="3" height="3"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <?php woocommerce_catalog_ordering(); ?>
                        </div>
                    </div>

                    <div class="products-wrapper" data-columns="<?php echo esc_attr($grid_columns); ?>">
                        <?php
                        woocommerce_product_loop_start();

                        if (wc_get_loop_prop('total')) {
                            while (have_posts()) {
                                the_post();

                                /**
                                 * Hook: woocommerce_shop_loop.
                                 */
                                do_action('woocommerce_shop_loop');

                                wc_get_template_part('content', 'product');
                            }
                        }

                        woocommerce_product_loop_end();
                        ?>
                    </div>

                    <div class="shop-pagination">
                        <?php
                        /**
                         * Hook: woocommerce_after_shop_loop.
                         */
                        do_action('woocommerce_after_shop_loop');
                        ?>
                    </div>
                </div><!-- .shop-main -->
            </div><!-- .shop-layout -->

        <?php else : ?>
            
            <div class="no-products-found">
                <?php
                /**
                 * Hook: woocommerce_no_products_found.
                 */
                do_action('woocommerce_no_products_found');
                ?>
            </div>

        <?php endif; ?>
    </div>
</div>

<script>
// Grid Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const gridButtons = document.querySelectorAll('.grid-btn');
    const productsWrapper = document.querySelector('.products-wrapper');
    
    gridButtons.forEach(button => {
        button.addEventListener('click', function() {
            const columns = this.dataset.columns;
            
            // Update active state
            gridButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Update grid
            if (productsWrapper) {
                productsWrapper.setAttribute('data-columns', columns);
                productsWrapper.style.gridTemplateColumns = `repeat(${columns}, 1fr)`;
            }
            
            // Save preference
            document.cookie = `grid_columns=${columns}; path=/; max-age=31536000`;
        });
    });
});
</script>

<?php

/**
 * Hook: woocommerce_after_main_content.
 */
do_action('woocommerce_after_main_content');

/**
 * Hook: woocommerce_sidebar.
 */
// do_action('woocommerce_sidebar'); // Uncomment if you want sidebar

get_footer('shop');
