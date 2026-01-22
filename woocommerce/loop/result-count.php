<?php
/**
 * Result Count - English Hardcoded
 *
 * @package WP_Augoose
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p class="woocommerce-result-count" role="alert" aria-relevant="all" <?php echo ( empty( $orderedby ) || 1 === intval( $total ) ) ? '' : 'data-is-sorted-by="true"'; ?>>
	<?php
	if ( 1 === intval( $total ) ) {
		echo 'Showing the single result';
	} elseif ( $total <= $per_page || -1 === $per_page ) {
		$orderedby_placeholder = empty( $orderedby ) ? '%2$s' : '<span class="screen-reader-text">%2$s</span>';
		printf( _n( 'Showing all %1$d result', 'Showing all %1$d results', $total, 'wp-augoose' ) . $orderedby_placeholder, $total, esc_html( $orderedby ) );
	} else {
		$first                 = ( $per_page * $current ) - $per_page + 1;
		$last                  = min( $total, $per_page * $current );
		$orderedby_placeholder = empty( $orderedby ) ? '%4$s' : '<span class="screen-reader-text">%4$s</span>';
		printf( _nx( 'Showing %1$d&ndash;%2$d of %3$d result', 'Showing %1$d&ndash;%2$d of %3$d results', $total, 'with first and last result', 'wp-augoose' ) . $orderedby_placeholder, $first, $last, $total, esc_html( $orderedby ) );
	}
	?>
</p>
