<?php
/**
 * Performance Optimizations
 * 
 * @package WP_Augoose
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Optimize asset loading
 */
function wp_augoose_optimize_assets() {
    // Defer non-critical scripts
    add_filter( 'script_loader_tag', 'wp_augoose_defer_scripts', 10, 2 );
    
    // Remove unnecessary scripts/styles
    add_action( 'wp_enqueue_scripts', 'wp_augoose_remove_unused_assets', 99 );
    
    // Lazy load images
    add_filter( 'wp_get_attachment_image_attributes', 'wp_augoose_lazy_load_images', 10, 3 );
    
    // Optimize database queries
    add_action( 'pre_get_posts', 'wp_augoose_optimize_queries' );
    
    // Add caching headers
    add_action( 'send_headers', 'wp_augoose_cache_headers' );
}

add_action( 'init', 'wp_augoose_optimize_assets' );

/**
 * Defer non-critical scripts
 */
function wp_augoose_defer_scripts( $tag, $handle ) {
    // Scripts that can be deferred
    $defer_scripts = array(
        'wp-augoose-latest-collection-slider',
        'wp-augoose-product-gallery-nav',
        'wp-augoose-product-tabs',
        'wp-augoose-simple-interactions',
        'wp-augoose-image-swatcher',
    );
    
    if ( in_array( $handle, $defer_scripts, true ) ) {
        return str_replace( ' src', ' defer src', $tag );
    }
    
    // Scripts that should be async
    $async_scripts = array(
        'market-autoselect-js',
    );
    
    if ( in_array( $handle, $async_scripts, true ) ) {
        return str_replace( ' src', ' async src', $tag );
    }
    
    return $tag;
}

/**
 * Remove unused assets
 */
function wp_augoose_remove_unused_assets() {
    // Remove emoji scripts
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    
    // Remove embed scripts if not needed
    if ( ! is_singular() || ! get_option( 'embed_autourls' ) ) {
        wp_deregister_script( 'wp-embed' );
    }
    
    // Remove block library CSS if not using blocks
    if ( ! has_blocks() ) {
        wp_dequeue_style( 'wp-block-library' );
        wp_dequeue_style( 'wp-block-library-theme' );
        wp_dequeue_style( 'wc-block-style' );
    }
}

/**
 * Lazy load images
 */
function wp_augoose_lazy_load_images( $attr, $attachment, $size ) {
    // Skip lazy loading for above-the-fold images
    if ( is_front_page() && has_post_thumbnail() ) {
        return $attr;
    }
    
    // Add loading="lazy" attribute
    if ( ! isset( $attr['loading'] ) ) {
        $attr['loading'] = 'lazy';
    }
    
    return $attr;
}

/**
 * Optimize database queries
 */
function wp_augoose_optimize_queries( $query ) {
    if ( ! is_admin() && $query->is_main_query() ) {
        // Limit post queries
        if ( is_home() || is_archive() ) {
            $query->set( 'posts_per_page', 12 );
        }
        
        // Disable unnecessary meta queries
        $query->set( 'update_post_meta_cache', true );
        $query->set( 'update_post_term_cache', true );
    }
    
    return $query;
}

/**
 * Add caching headers
 */
function wp_augoose_cache_headers() {
    if ( ! is_admin() && ! is_user_logged_in() ) {
        // Cache static assets for 1 year
        if ( is_singular() ) {
            header( 'Cache-Control: public, max-age=31536000, immutable' );
        }
        
        // Cache HTML for 1 hour
        header( 'Cache-Control: public, max-age=3600' );
    }
}

/**
 * Optimize WooCommerce queries
 */
function wp_augoose_optimize_woocommerce_queries() {
    // Disable unnecessary WooCommerce features if not needed
    if ( ! is_admin() ) {
        // Disable product reviews count query if not showing reviews
        add_filter( 'woocommerce_product_review_count', '__return_false', 99 );
        
        // Optimize product queries
        add_filter( 'woocommerce_product_query', 'wp_augoose_optimize_product_query' );
    }
}

add_action( 'init', 'wp_augoose_optimize_woocommerce_queries' );

/**
 * Optimize product query
 */
function wp_augoose_optimize_product_query( $query ) {
    // Only load necessary fields
    $query->set( 'fields', 'ids' );
    
    return $query;
}

/**
 * Minify inline CSS (basic)
 */
function wp_augoose_minify_css( $css ) {
    // Remove comments
    $css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
    
    // Remove whitespace
    $css = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $css );
    
    return $css;
}

/**
 * Combine and minify CSS files (for production)
 */
function wp_augoose_combine_css_files( $handles ) {
    // Only in production
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        return $handles;
    }
    
    // Combine critical CSS files
    // This is a placeholder - implement based on your needs
    
    return $handles;
}

/**
 * Preload critical resources
 */
function wp_augoose_preload_resources() {
    // Preload critical fonts
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/fonts/killarney.woff2" as="font" type="font/woff2" crossorigin>' . "\n";
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/fonts/minion-pro.woff2" as="font" type="font/woff2" crossorigin>' . "\n";
    
    // Preload critical CSS
    echo '<link rel="preload" href="' . get_stylesheet_uri() . '" as="style">' . "\n";
}

add_action( 'wp_head', 'wp_augoose_preload_resources', 1 );

/**
 * Disable unnecessary WordPress features
 */
function wp_augoose_disable_unnecessary_features() {
    // Disable XML-RPC
    add_filter( 'xmlrpc_enabled', '__return_false' );
    
    // Disable REST API for non-authenticated users (optional)
    // add_filter( 'rest_authentication_errors', 'wp_augoose_restrict_rest_api' );
    
    // Remove unnecessary meta tags
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'wp_generator' );
}

add_action( 'init', 'wp_augoose_disable_unnecessary_features' );

/**
 * Optimize transients cleanup
 */
function wp_augoose_optimize_transients() {
    // Clean old transients on admin init
    if ( is_admin() ) {
        global $wpdb;
        
        // Delete expired transients
        $wpdb->query( 
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_timeout_%' 
            AND option_value < UNIX_TIMESTAMP()" 
        );
        
        // Delete orphaned transients
        $wpdb->query( 
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_%' 
            AND option_name NOT IN (
                SELECT CONCAT('_transient_timeout_', SUBSTRING(option_name, 12))
                FROM {$wpdb->options}
                WHERE option_name LIKE '_transient_timeout_%'
            )" 
        );
    }
}

add_action( 'admin_init', 'wp_augoose_optimize_transients' );
