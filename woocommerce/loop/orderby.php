<?php
/**
 * Show options for ordering - English Hardcoded
 *
 * @package WP_Augoose
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id_suffix = wp_unique_id();

// Force English options
$catalog_orderby_options = array(
	'menu_order' => 'Default sorting',
	'popularity' => 'Sort by popularity',
	'rating'     => 'Sort by average rating',
	'date'       => 'Sort by latest',
	'price'      => 'Sort by price: low to high',
	'price-desc' => 'Sort by price: high to low',
);

?>
<form class="woocommerce-ordering" method="get">
	<?php if ( $use_label ) : ?>
		<label for="woocommerce-orderby-<?php echo esc_attr( $id_suffix ); ?>">Sort by</label>
	<?php endif; ?>
	<select
		name="orderby"
		class="orderby"
		<?php if ( $use_label ) : ?>
			id="woocommerce-orderby-<?php echo esc_attr( $id_suffix ); ?>"
		<?php else : ?>
			aria-label="Shop order"
		<?php endif; ?>
	>
		<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
			<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
		<?php endforeach; ?>
	</select>
	<input type="hidden" name="paged" value="1" />
	<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', 'paged', 'product-page' ) ); ?>
</form>
