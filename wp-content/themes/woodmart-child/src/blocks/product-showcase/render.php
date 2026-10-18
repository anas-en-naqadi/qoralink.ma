<?php
/**
 * Server-side dynamic render for qora/product-showcase
 * Renders 12 products (6 per row on desktop) with customizable WooCommerce query.
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

$home_url      = rtrim( home_url(), '/' );
$heading       = isset( $attributes['heading'] ) ? $attributes['heading'] : 'SELECTED';
$subtitle      = isset( $attributes['subtitle'] ) ? $attributes['subtitle'] : 'Curated technology for work, study and everyday life.';
$view_all_text = isset( $attributes['viewAllText'] ) ? $attributes['viewAllText'] : 'View all products';
$view_all_url  = isset( $attributes['viewAllUrl'] ) ? $attributes['viewAllUrl'] : '/shop/';
$query_source  = isset( $attributes['querySource'] ) ? $attributes['querySource'] : 'new_arrivals';
$category_slug = isset( $attributes['categorySlug'] ) ? $attributes['categorySlug'] : '';
$product_count = isset( $attributes['productCount'] ) ? max( 1, (int) $attributes['productCount'] ) : 12;
$columns       = isset( $attributes['columns'] ) ? max( 2, min( 6, (int) $attributes['columns'] ) ) : 6;

if ( ! empty( $view_all_url ) && strpos( $view_all_url, 'http' ) !== 0 ) {
	$view_all_url = $home_url . '/' . ltrim( $view_all_url, '/' );
}

$products = array();
if ( function_exists( 'qora_get_showcase_products' ) ) {
	$products = qora_get_showcase_products( $query_source, $category_slug, $product_count );
}
?>
<section class="qora-product-showcase-section" data-query-source="<?php echo esc_attr( $query_source ); ?>" aria-label="<?php echo esc_attr( $heading ); ?>">
	<div class="qora-product-showcase-container">
		<header class="qora-product-showcase__header">
			<div class="qora-product-showcase__heading-group">
				<h2 class="qora-product-showcase__title">
					<span class="qora-product-showcase__main"><?php echo esc_html( $heading ); ?></span>
				</h2>
				<?php if ( $subtitle ) : ?>
					<p class="qora-product-showcase__subtitle"><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $view_all_text ) : ?>
				<a href="<?php echo esc_url( $view_all_url ); ?>" class="qora-product-showcase__view-all">
					<span><?php echo esc_html( $view_all_text ); ?></span>
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
				</a>
			<?php endif; ?>
		</header>

		<div class="qora-product-showcase__grid" style="--qora-prod-cols: <?php echo esc_attr( $columns ); ?>;">
			<?php if ( ! empty( $products ) ) : ?>
				<?php foreach ( $products as $idx => $product ) :
					if ( ! is_object( $product ) ) {
						continue;
					}
					if ( function_exists( 'qora_render_product_card' ) ) {
						qora_render_product_card( $product, $idx, 'grid' );
					}
				endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
