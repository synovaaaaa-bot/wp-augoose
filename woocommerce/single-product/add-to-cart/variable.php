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
				
				// IMPORTANT: Get ALL attribute terms (including out of stock) to show all colors
				// This ensures all colors are displayed, not just available ones
				$all_attribute_terms = array();
				if ( taxonomy_exists( $attribute_id ) ) {
					$terms = get_terms( array(
						'taxonomy'   => $attribute_id,
						'hide_empty' => false, // Include all terms, even if no variations
					) );
					if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
						foreach ( $terms as $term ) {
							$all_attribute_terms[] = $term->name;
						}
					}
				}
				
				// Use all terms if available, otherwise fallback to options from variations
				$display_options = ! empty( $all_attribute_terms ) ? $all_attribute_terms : $options;
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
						
						// Display ALL options (including out of stock)
						foreach ( $display_options as $option ) {
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
								
								// Fallback to comprehensive color mapping - support ALL colors
								if ( empty( $color_hex ) ) {
									$color_map = array(
										// Basic colors
										'black'  => '#000000',
										'white'  => '#ffffff',
										'gray'   => '#808080',
										'grey'   => '#808080',
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
										'chocolate' => '#7b3f00',
										'dark-chocolate' => '#3d1f00',
										'dark chocolate' => '#3d1f00',
										'off-white' => '#fafafa',
										'off white' => '#fafafa',
										'offwhite' => '#fafafa',
										'terracotta' => '#e2725b',
										'terracota' => '#e2725b',
										// Extended colors
										'cobalt' => '#0047ab',
										'cobalt-blue' => '#0047ab',
										'olive'  => '#808000',
										'olive-green' => '#808000',
										'obsidian' => '#000000',
										'camo'   => '#78866b',
										'camo-green' => '#78866b',
										'camel'  => '#c19a6b',
										'khaki'  => '#c3b091',
										'tan'    => '#d2b48c',
										'burgundy' => '#800020',
										'maroon' => '#800000',
										'teal'   => '#008080',
										'turquoise' => '#40e0d0',
										'turqoise' => '#40e0d0',
										'cyan'   => '#00ffff',
										'magenta' => '#ff00ff',
										'coral'  => '#ff7f50',
										'salmon' => '#fa8072',
										'peach'  => '#ffcba4',
										'cream'  => '#fffdd0',
										'ivory'  => '#fffff0',
										'charcoal' => '#36454f',
										'slate'  => '#708090',
										'silver' => '#c0c0c0',
										'gold'   => '#ffd700',
										'bronze' => '#cd7f32',
										'copper' => '#b87333',
										// Additional common colors
										'light-blue' => '#add8e6',
										'dark-blue' => '#00008b',
										'light-green' => '#90ee90',
										'dark-green' => '#006400',
										'light-gray' => '#d3d3d3',
										'light-grey' => '#d3d3d3',
										'dark-gray' => '#a9a9a9',
										'dark-grey' => '#a9a9a9',
										'light-pink' => '#ffb6c1',
										'dark-pink' => '#ff1493',
										'lavender' => '#e6e6fa',
										'lilac'   => '#c8a2c8',
										'mint'    => '#98ff98',
										'emerald' => '#50c878',
										'ruby'    => '#e0115f',
										'sapphire' => '#0f52ba',
										'amber'   => '#ffbf00',
										'crimson' => '#dc143c',
										'indigo'  => '#4b0082',
										'violet'  => '#8f00ff',
									);
									
									// Try exact match first
									$option_lower = strtolower( trim( $option ) );
									$color_hex = isset( $color_map[ $option_lower ] ) ? $color_map[ $option_lower ] : '';
									
									// If no exact match, try partial match (e.g., "cobalt blue" -> "cobalt-blue")
									if ( empty( $color_hex ) ) {
										$option_normalized = str_replace( array( ' ', '_' ), '-', $option_lower );
										$color_hex = isset( $color_map[ $option_normalized ] ) ? $color_map[ $option_normalized ] : '';
									}
									
									// If still no match, handle multi-color combinations (e.g., "Navy Black", "Terracota Black", "White Black", "Olive Khaki")
									// Extract first color from combination
									if ( empty( $color_hex ) ) {
										// Split by common separators (space, slash, comma, dash)
										$color_parts = preg_split( '/[\s\/,\-]+/', $option_lower );
										if ( ! empty( $color_parts ) ) {
											// Try first color part
											$first_color = trim( $color_parts[0] );
											if ( isset( $color_map[ $first_color ] ) ) {
												$color_hex = $color_map[ $first_color ];
											} else {
												// Try normalized first color
												$first_color_normalized = str_replace( array( ' ', '_' ), '-', $first_color );
												if ( isset( $color_map[ $first_color_normalized ] ) ) {
													$color_hex = $color_map[ $first_color_normalized ];
												}
											}
										}
									}
									
									// If still no match, try to extract color name from option (e.g., "Cobalt Blue Active Jacket" -> "cobalt-blue")
									if ( empty( $color_hex ) ) {
										// Sort by length (longest first) to match "dark-chocolate" before "chocolate"
										$sorted_colors = $color_map;
										uksort( $sorted_colors, function( $a, $b ) {
											return strlen( $b ) - strlen( $a );
										} );
										
										foreach ( $sorted_colors as $color_name => $hex ) {
											if ( strpos( $option_lower, $color_name ) !== false ) {
												$color_hex = $hex;
												break;
											}
										}
									}
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
								title="<?php echo esc_attr( $option ); ?>"
							>
								<?php if ( $is_color ) : ?>
									<?php if ( $color_hex ) : ?>
										<span class="swatch-color-dot" style="background-color: <?php echo esc_attr( $color_hex ); ?>"></span>
									<?php else : ?>
										<!-- Fallback: show text label if color hex not found -->
										<span class="swatch-label"><?php echo esc_html( strtoupper( $option ) ); ?></span>
									<?php endif; ?>
								<?php else : ?>
									<span class="swatch-label"><?php echo esc_html( strtoupper( $option ) ); ?></span>
								<?php endif; ?>
							</button>
							
						<?php } ?>
					</div>
					
					<!-- Hidden select for WooCommerce - must include ALL options -->
					<select 
						name="attribute_<?php echo esc_attr( $attribute_slug ); ?>" 
						id="<?php echo esc_attr( $attribute_slug ); ?>"
						class="variation-select-hidden"
						data-attribute_name="attribute_<?php echo esc_attr( $attribute_slug ); ?>"
						data-show_option_none="yes"
						style="display: none !important;"
					>
						<option value=""><?php echo esc_html( 'Choose an option' ); ?></option>
						<?php foreach ( $display_options as $option ) : ?>
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
