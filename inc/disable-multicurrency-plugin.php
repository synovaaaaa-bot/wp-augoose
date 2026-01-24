<?php
/**
 * Disable Multicurrency Plugin Hooks
 * 
 * Prevents plugin multicurrency-autoconvert from modifying WooCommerce prices
 * which causes fatal errors when loading product pages and adding to cart.
 * 
 * @package WP_Augoose
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove all price conversion hooks from multicurrency plugin
 * This prevents fatal errors when loading products and adding to cart
 * 
 * This function is called from functions.php with priority 999
 * which ensures it runs AFTER plugin registers hooks
 */
function wp_augoose_disable_multicurrency_plugin_hooks() {
	// Only run if multicurrency plugin is active
	if ( ! class_exists( 'MultiCurrency_AutoConvert' ) ) {
		return;
	}

	// Get plugin instance
	$instance = MultiCurrency_AutoConvert::get_instance();
	if ( ! $instance ) {
		return;
	}
	
	// Remove ALL price conversion filters that modify WooCommerce prices
	// These cause fatal errors when loading product pages
	
	// Product price filters (priority 999)
	remove_filter( 'woocommerce_product_get_price', array( $instance, 'convert_price_display' ), 999 );
	remove_filter( 'woocommerce_product_get_sale_price', array( $instance, 'convert_price_display' ), 999 );
	remove_filter( 'woocommerce_product_get_regular_price', array( $instance, 'convert_price_display' ), 999 );
	
	// Variation price filters (priority 999)
	remove_filter( 'woocommerce_product_variation_get_price', array( $instance, 'convert_price_display' ), 999 );
	remove_filter( 'woocommerce_product_variation_get_sale_price', array( $instance, 'convert_price_display' ), 999 );
	remove_filter( 'woocommerce_product_variation_get_regular_price', array( $instance, 'convert_price_display' ), 999 );
	
	// Cart price filters (priority 10)
	remove_filter( 'woocommerce_cart_item_price', array( $instance, 'convert_cart_item_price' ), 10 );
	remove_filter( 'woocommerce_cart_item_subtotal', array( $instance, 'convert_cart_item_subtotal' ), 10 );
	remove_filter( 'woocommerce_cart_subtotal', array( $instance, 'convert_cart_subtotal' ), 10 );
	remove_filter( 'woocommerce_cart_total', array( $instance, 'convert_cart_total' ), 10 );
	
	// Shipping filters (priority 10)
	remove_filter( 'woocommerce_shipping_packages', array( $instance, 'convert_shipping_packages' ), 10 );
	remove_filter( 'woocommerce_package_rates', array( $instance, 'convert_shipping_rates' ), 10 );
	
	// Fee and tax filters (priority 10)
	remove_filter( 'woocommerce_cart_fee_total', array( $instance, 'convert_cart_fee_total' ), 10 );
	remove_filter( 'woocommerce_cart_tax_totals', array( $instance, 'convert_cart_tax_totals' ), 10 );
	
	// Checkout total filters (priority 10)
	remove_filter( 'woocommerce_cart_totals_order_total_html', array( $instance, 'convert_checkout_total' ), 10 );
	remove_filter( 'woocommerce_cart_totals_subtotal_html', array( $instance, 'convert_checkout_subtotal' ), 10 );
	remove_filter( 'woocommerce_cart_totals_shipping_html', array( $instance, 'convert_checkout_shipping' ), 10 );
	remove_filter( 'woocommerce_cart_totals_fee_html', array( $instance, 'convert_checkout_fee' ), 10 );
	remove_filter( 'woocommerce_cart_totals_taxes_total_html', array( $instance, 'convert_checkout_taxes' ), 10 );
	
	// Price format and currency symbol filters (priority 10)
	remove_filter( 'woocommerce_price_format', array( $instance, 'price_format' ), 10 );
	remove_filter( 'woocommerce_currency_symbol', array( $instance, 'get_currency_symbol' ), 10 );
}
