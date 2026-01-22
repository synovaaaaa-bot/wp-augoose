<?php
/**
 * Attribute Helper Functions
 * Helper untuk memverifikasi dan debug attribute setup
 *
 * @package WP_Augoose
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Debug: Check if Size and Color attributes are properly set up
 * Call this function to verify attribute setup
 *
 * @return array Debug information
 */
function wp_augoose_check_attribute_setup() {
	$debug = array(
		'size' => array(
			'attribute_exists' => false,
			'taxonomy_exists' => false,
			'terms_count' => 0,
			'products_with_attribute' => 0,
		),
		'color' => array(
			'attribute_exists' => false,
			'taxonomy_exists' => false,
			'terms_count' => 0,
			'products_with_attribute' => 0,
		),
	);

	// Get all attributes
	$attribute_taxonomies = wc_get_attribute_taxonomies();
	
	foreach ( $attribute_taxonomies as $attr ) {
		$attr_name = strtolower( $attr->attribute_name );
		$taxonomy = wc_attribute_taxonomy_name( $attr->attribute_name );
		
		if ( $attr_name === 'size' ) {
			$debug['size']['attribute_exists'] = true;
			$debug['size']['taxonomy_exists'] = taxonomy_exists( $taxonomy );
			
			if ( taxonomy_exists( $taxonomy ) ) {
				$terms = get_terms( array(
					'taxonomy' => $taxonomy,
					'hide_empty' => false,
				) );
				$debug['size']['terms_count'] = is_array( $terms ) ? count( $terms ) : 0;
				
				// Count products with this attribute
				$products = get_posts( array(
					'post_type' => 'product',
					'posts_per_page' => -1,
					'tax_query' => array(
						array(
							'taxonomy' => $taxonomy,
							'operator' => 'EXISTS',
						),
					),
				) );
				$debug['size']['products_with_attribute'] = count( $products );
			}
		}
		
		if ( $attr_name === 'color' || $attr_name === 'colour' ) {
			$debug['color']['attribute_exists'] = true;
			$debug['color']['taxonomy_exists'] = taxonomy_exists( $taxonomy );
			
			if ( taxonomy_exists( $taxonomy ) ) {
				$terms = get_terms( array(
					'taxonomy' => $taxonomy,
					'hide_empty' => false,
				) );
				$debug['color']['terms_count'] = is_array( $terms ) ? count( $terms ) : 0;
				
				// Count products with this attribute
				$products = get_posts( array(
					'post_type' => 'product',
					'posts_per_page' => -1,
					'tax_query' => array(
						array(
							'taxonomy' => $taxonomy,
							'operator' => 'EXISTS',
						),
					),
				) );
				$debug['color']['products_with_attribute'] = count( $products );
			}
		}
	}
	
	return $debug;
}

/**
 * Display attribute setup status (for admin/debugging)
 * Add this to functions.php or use in admin panel
 */
function wp_augoose_display_attribute_status() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	$debug = wp_augoose_check_attribute_setup();
	
	echo '<div style="background: #fff; padding: 20px; margin: 20px; border: 1px solid #ddd;">';
	echo '<h3>Attribute Setup Status</h3>';
	
	// Size
	echo '<h4>Size Attribute:</h4>';
	echo '<ul>';
	echo '<li>Attribute exists: ' . ( $debug['size']['attribute_exists'] ? '✅ Yes' : '❌ No' ) . '</li>';
	echo '<li>Taxonomy exists: ' . ( $debug['size']['taxonomy_exists'] ? '✅ Yes' : '❌ No' ) . '</li>';
	echo '<li>Terms count: ' . $debug['size']['terms_count'] . '</li>';
	echo '<li>Products with attribute: ' . $debug['size']['products_with_attribute'] . '</li>';
	echo '</ul>';
	
	// Color
	echo '<h4>Color Attribute:</h4>';
	echo '<ul>';
	echo '<li>Attribute exists: ' . ( $debug['color']['attribute_exists'] ? '✅ Yes' : '❌ No' ) . '</li>';
	echo '<li>Taxonomy exists: ' . ( $debug['color']['taxonomy_exists'] ? '✅ Yes' : '❌ No' ) . '</li>';
	echo '<li>Terms count: ' . $debug['color']['terms_count'] . '</li>';
	echo '<li>Products with attribute: ' . $debug['color']['products_with_attribute'] . '</li>';
	echo '</ul>';
	
	echo '</div>';
}

// Uncomment line below to display status on admin pages (for debugging)
// add_action( 'admin_notices', 'wp_augoose_display_attribute_status' );
