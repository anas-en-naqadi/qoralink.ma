<?php
/**
 * Product content template for custom hover effects (Qoralink Child Theme Override).
 *
 * Renders the Qoralink Product Card when the Qoralink Grid/List loop layout is active,
 * or delegates to standard WoodMart custom layout rendering for any other custom layout.
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

global $product;

$classes      = '';
$current_view = function_exists( 'woodmart_loop_prop' ) && 'list' === woodmart_loop_prop( 'products_view' ) ? 'list' : 'grid';
$hover_id     = 'list' === $current_view
	? ( function_exists( 'woodmart_loop_prop' ) ? (int) woodmart_loop_prop( 'product_custom_list' ) : 0 )
	: ( function_exists( 'woodmart_loop_prop' ) ? (int) woodmart_loop_prop( 'product_custom_hover' ) : 0 );

$qora_grid_id = (int) get_option( 'qora_loop_layout_grid_id', 0 );
$qora_list_id = (int) get_option( 'qora_loop_layout_list_id', 0 );
$loop_idx     = function_exists( 'woodmart_loop_prop' ) ? (int) woodmart_loop_prop( 'woocommerce_loop' ) : 0;

if ( 1 === $loop_idx && $hover_id && function_exists( 'woodmart_add_editable_post_to_admin_bar' ) ) {
	woodmart_add_editable_post_to_admin_bar( $hover_id );
}

if ( $hover_id && get_post_meta( $hover_id, 'wd_transform', true ) ) {
	$classes .= ' wd-transform';
}
?>
<div class="wd-product-wrapper wd-entry-content qora-loop-card-wrapper<?php echo esc_attr( $classes ); ?>">
	<?php
	if ( ( ! $hover_id || $hover_id === $qora_grid_id || $hover_id === $qora_list_id ) && function_exists( 'qora_render_product_card' ) ) {
		qora_render_product_card( $product, $loop_idx, $current_view );
	} else {
		do_action( 'woodmart_loop_item_content', $hover_id );
	}
	?>
</div>
