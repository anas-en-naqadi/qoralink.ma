<?php
/**
 * Server-side dynamic render for qora/category-grid
 * Queries WooCommerce product_cat terms from DB and resolves images.
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

$columns   = isset( $attributes['columns'] ) ? max( 2, min( 4, (int) $attributes['columns'] ) ) : 4;
$items     = ! empty( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
$home_url  = rtrim( home_url(), '/' );

// Fetch all non-empty or existing WooCommerce product categories from DB for fallback matching
$db_terms = get_terms( array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => false,
	'orderby'    => 'count',
	'order'      => 'DESC',
) );

$terms_by_slug = array();
$terms_ordered = array();
if ( ! is_wp_error( $db_terms ) && is_array( $db_terms ) ) {
	foreach ( $db_terms as $t ) {
		if ( 'uncategorized' === $t->slug ) {
			continue;
		}
		$terms_by_slug[ $t->slug ] = $t;
		$terms_ordered[]           = $t;
	}
}

if ( empty( $items ) ) {
	$items = array(
		array( 'slug' => 'laptops', 'customTitle' => '', 'subtitle' => "Power your\npotential.", 'customImage' => '' ),
		array( 'slug' => 'telephonie', 'customTitle' => '', 'subtitle' => "Protect, connect,\npersonalize.", 'customImage' => '' ),
		array( 'slug' => 'peripherals', 'customTitle' => '', 'subtitle' => "Stay powered\nanywhere.", 'customImage' => '/wp-content/uploads/2026/09/cable.png' ),
		array( 'slug' => 'workstations', 'customTitle' => '', 'subtitle' => "A better desk,\nevery day.", 'customImage' => '' ),
		array( 'slug' => 'audio', 'customTitle' => '', 'subtitle' => "For work, study\nand creativity.", 'customImage' => '' ),
		array( 'slug' => 'business-laptops', 'customTitle' => '', 'subtitle' => "Carry your tech\nin style.", 'customImage' => '' ),
		array( 'slug' => 'desktops', 'customTitle' => '', 'subtitle' => "Small tech.\nA smarter home.", 'customImage' => '' ),
		array( 'slug' => 'gaming-laptops', 'customTitle' => '', 'subtitle' => "Great tech.\nBetter value.", 'customImage' => '' ),
	);
}
?>
<section class="qora-category-grid-section" aria-label="<?php esc_attr_e( 'Shop by Category', 'woodmart-child' ); ?>">
	<div class="qora-category-grid-container">
		<div class="qora-category-grid" style="--qora-cat-cols: <?php echo esc_attr( $columns ); ?>;">
			<?php foreach ( $items as $index => $item ) :
				$slug         = isset( $item['slug'] ) ? sanitize_title( $item['slug'] ) : '';
				$term         = isset( $terms_by_slug[ $slug ] ) ? $terms_by_slug[ $slug ] : ( isset( $terms_ordered[ $index ] ) ? $terms_ordered[ $index ] : null );
				$custom_title = isset( $item['customTitle'] ) ? trim( $item['customTitle'] ) : '';
				$title        = ! empty( $custom_title ) ? $custom_title : ( $term ? $term->name : ucfirst( str_replace( '-', ' ', $slug ) ) );

				$subtitle = isset( $item['subtitle'] ) ? trim( $item['subtitle'] ) : '';
				if ( empty( $subtitle ) && $term && ! empty( $term->description ) ) {
					$subtitle = wp_trim_words( $term->description, 5, '' );
				}

				$custom_img = isset( $item['customImage'] ) ? trim( $item['customImage'] ) : '';
				if ( ! empty( $custom_img ) && strpos( $custom_img, 'http' ) !== 0 ) {
					$custom_img = $home_url . '/' . ltrim( $custom_img, '/' );
				}

				$img_url = '';
				if ( function_exists( 'qora_get_category_image_url' ) && $term ) {
					$img_url = qora_get_category_image_url( $term, $custom_img, 'medium' );
				} elseif ( ! empty( $custom_img ) ) {
					$img_url = $custom_img;
				}

				$term_link = $term ? get_term_link( $term ) : $home_url . '/shop/';
				if ( is_wp_error( $term_link ) ) {
					$term_link = $home_url . '/shop/';
				}
				?>
				<a href="<?php echo esc_url( $term_link ); ?>" class="qora-category-tile" data-cat-slug="<?php echo esc_attr( $term ? $term->slug : $slug ); ?>">
					<div class="qora-category-tile__info">
						<h3 class="qora-category-tile__title"><?php echo esc_html( html_entity_decode( $title ) ); ?></h3>
						<?php if ( $subtitle ) : ?>
							<p class="qora-category-tile__subtitle"><?php echo nl2br( esc_html( $subtitle ) ); ?></p>
						<?php endif; ?>
						<span class="qora-category-tile__arrow" aria-hidden="true">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
						</span>
					</div>
					<?php if ( $img_url ) : ?>
						<div class="qora-category-tile__media">
							<img
								src="<?php echo esc_url( $img_url ); ?>"
								alt="<?php echo esc_attr( html_entity_decode( $title ) ); ?>"
								loading="lazy"
							/>
						</div>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
