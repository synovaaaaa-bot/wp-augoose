<?php
/**
 * Variable product add to cart
 *
 * @package WP_Augoose
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_type( 'variable' ) ) {
	return;
}

$attributes = $product->get_variation_attributes();
$available_variations = $product->get_available_variations();
$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

// Build a map of in-stock attribute values (so we can disable sold-out colors/sizes)
$in_stock_values = array();
if ( is_array( $available_variations ) ) {
	foreach ( $available_variations as $v ) {
		if ( empty( $v['is_in_stock'] ) ) {
			continue;
		}
		if ( empty( $v['attributes'] ) || ! is_array( $v['attributes'] ) ) {
			continue;
		}
		foreach ( $v['attributes'] as $k => $val ) {
			$k = (string) $k; // e.g. attribute_pa_color
			$val = (string) $val;
			if ( $val === '' ) {
				continue;
			}
			if ( ! isset( $in_stock_values[ $k ] ) ) {
				$in_stock_values[ $k ] = array();
			}
			$in_stock_values[ $k ][ sanitize_title( $val ) ] = true;
		}
	}
}

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
	<?php else : ?>
		
		<div class="variations">
			<?php foreach ( $attributes as $attribute_name => $options ) : ?>
				<?php
				$attribute_label = wc_attribute_label( $attribute_name );
				$attribute_slug  = sanitize_title( $attribute_name );
				$attribute_id    = 'pa_' . $attribute_slug;
				
				// Check if this is a color attribute
				$is_color = ( strpos( strtolower( $attribute_name ), 'color' ) !== false || strpos( strtolower( $attribute_name ), 'colour' ) !== false );
				$is_size  = ( strpos( strtolower( $attribute_name ), 'size' ) !== false );
				?>
				
				<div class="variation-group variation-<?php echo esc_attr( $attribute_slug ); ?>">
					<div class="variation-header">
						<label for="<?php echo esc_attr( $attribute_slug ); ?>">
							<?php echo esc_html( strtoupper( $attribute_label ) ); ?>
						</label>
						<?php if ( $is_size ) : ?>
							<a href="#" class="size-guide-link">SIZE GUIDE</a>
						<?php endif; ?>
					</div>
					
					<div class="variation-swatches <?php echo $is_color ? 'is-color' : ( $is_size ? 'is-size' : '' ); ?>">
						<?php
						$selected_value = isset( $_REQUEST[ 'attribute_' . $attribute_slug ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . $attribute_slug ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
						
						foreach ( $options as $option ) {
							$is_selected = sanitize_title( $selected_value ) === sanitize_title( $option );
							$option_slug = sanitize_title( $option );
							$attr_key = 'attribute_' . $attribute_slug;
							$is_disabled = false;
							if ( isset( $in_stock_values[ $attr_key ] ) && is_array( $in_stock_values[ $attr_key ] ) ) {
								$is_disabled = empty( $in_stock_values[ $attr_key ][ $option_slug ] );
							}
							
							// Get color hex if it's a color attribute
							$color_hex = '';
							if ( $is_color ) {
								// Try to get color from term meta
								$term = get_term_by( 'slug', $option_slug, $attribute_id );
								if ( $term ) {
									$color_hex = get_term_meta( $term->term_id, 'color', true );
								}
								
								// Fallback to common color mapping
								if ( empty( $color_hex ) ) {
									$color_map = array(
										'black'  => '#000000',
										'white'  => '#ffffff',
										'gray'   => '#cccccc',
										'grey'   => '#cccccc',
										'red'    => '#ff0000',
										'blue'   => '#0000ff',
										'green'  => '#00ff00',
										'yellow' => '#ffff00',
										'pink'   => '#ffc0cb',
										'purple' => '#800080',
										'orange' => '#ffa500',
										'brown'  => '#a52a2a',
										'beige'  => '#f5f5dc',
										'navy'   => '#000080',
									);
									$color_hex = isset( $color_map[ strtolower( $option ) ] ) ? $color_map[ strtolower( $option ) ] : '';
								}
							}
							?>
							
							<button 
								type="button" 
								class="variation-swatch <?php echo $is_selected ? 'is-active' : ''; ?> <?php echo $is_disabled ? 'is-disabled' : ''; ?> <?php echo $is_color ? 'swatch-color' : ( $is_size ? 'swatch-size' : 'swatch-text' ); ?>"
								data-value="<?php echo esc_attr( $option ); ?>"
								data-attribute="<?php echo esc_attr( $attribute_slug ); ?>"
								<?php echo $is_disabled ? 'disabled aria-disabled="true"' : ''; ?>
								<?php if ( $is_color && $color_hex ) : ?>
									style="--swatch-color: <?php echo esc_attr( $color_hex ); ?>"
								<?php endif; ?>
								aria-label="<?php echo esc_attr( $option ); ?>"
							>
								<?php if ( $is_color && $color_hex ) : ?>
									<span class="swatch-color-dot" style="background-color: <?php echo esc_attr( $color_hex ); ?>"></span>
								<?php else : ?>
									<span class="swatch-label"><?php echo esc_html( strtoupper( $option ) ); ?></span>
								<?php endif; ?>
							</button>
							
						<?php } ?>
					</div>
					
					<!-- Hidden select for WooCommerce -->
					<select 
						name="attribute_<?php echo esc_attr( $attribute_slug ); ?>" 
						id="<?php echo esc_attr( $attribute_slug ); ?>"
						class="variation-select-hidden"
						data-attribute_name="attribute_<?php echo esc_attr( $attribute_slug ); ?>"
						data-show_option_none="yes"
						style="display: none !important;"
					>
						<option value=""><?php echo esc_html( 'Choose an option' ); ?></option>
						<?php foreach ( $options as $option ) : ?>
							<option value="<?php echo esc_attr( $option ); ?>" <?php selected( sanitize_title( $selected_value ), sanitize_title( $option ) ); ?>><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				
			<?php endforeach; ?>
		</div>

		<div class="single_variation_wrap">
			<?php
				/**
				 * Hook: woocommerce_before_single_variation.
				 */
				do_action( 'woocommerce_before_single_variation' );

				/**
				 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
				 *
				 * @since 2.4.0
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );

				/**
				 * Hook: woocommerce_after_single_variation.
				 */
				do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
