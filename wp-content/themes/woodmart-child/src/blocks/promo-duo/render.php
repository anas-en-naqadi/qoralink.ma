<?php
/**
 * Server-side render for qora/promo-duo
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

$home_url = rtrim( home_url(), '/' );
$cards    = ! empty( $attributes['cards'] ) && is_array( $attributes['cards'] ) ? $attributes['cards'] : array(
	array(
		'eyebrow'  => 'POWER & CHARGING',
		'heading'  => "Always\nCharged.",
		'subtitle' => "Fast, reliable power\nfor all your devices.",
		'ctaText'  => 'Shop Charging',
		'ctaUrl'   => '/product-category/computers/peripherals/',
		'bgImage'  => '/wp-content/uploads/2026/10/Gemini_Generated_Image_suzzcjsuzzcjsuzz.jpg',
	),
	array(
		'eyebrow'  => 'BAGS & TRAVEL',
		'heading'  => "Less Clutter.\nMore Flow.",
		'subtitle' => "Smart organization\nfor wherever you go.",
		'ctaText'  => 'Shop Travel Gear',
		'ctaUrl'   => '/product-category/computers/laptops/business-laptops/',
		'bgImage'  => '/wp-content/uploads/2026/10/Gemini_Generated_Image_z76hhaz76hhaz76h.jpg',
	),
);
?>
<section class="qora-promo-duo-section" aria-label="<?php esc_attr_e( 'Featured Collections', 'woodmart-child' ); ?>">
	<div class="qora-promo-duo-container">
		<div class="qora-promo-duo-grid">
			<?php foreach ( $cards as $idx => $card ) :
				$eyebrow  = isset( $card['eyebrow'] ) ? $card['eyebrow'] : '';
				$heading  = isset( $card['heading'] ) ? $card['heading'] : '';
				$subtitle = isset( $card['subtitle'] ) ? $card['subtitle'] : '';
				$cta_text = isset( $card['ctaText'] ) ? $card['ctaText'] : 'Shop Now';
				$cta_url  = isset( $card['ctaUrl'] ) ? $card['ctaUrl'] : '/shop/';
				$bg_image = isset( $card['bgImage'] ) ? $card['bgImage'] : '';

				if ( ! empty( $cta_url ) && strpos( $cta_url, 'http' ) !== 0 ) {
					$cta_url = $home_url . '/' . ltrim( $cta_url, '/' );
				}
				if ( ! empty( $bg_image ) && strpos( $bg_image, 'http' ) !== 0 ) {
					$bg_image = $home_url . '/' . ltrim( $bg_image, '/' );
				}
				?>
				<article class="qora-promo-card" data-card-index="<?php echo esc_attr( $idx ); ?>">
					<?php if ( $bg_image ) : ?>
						<div class="qora-promo-card__bg">
							<img src="<?php echo esc_url( $bg_image ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $heading ) ); ?>" loading="lazy" />
							<div class="qora-promo-card__overlay"></div>
						</div>
					<?php endif; ?>

					<div class="qora-promo-card__content">
						<?php if ( $eyebrow ) : ?>
							<span class="qora-promo-card__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
						<?php endif; ?>

						<h3 class="qora-promo-card__title"><?php echo nl2br( esc_html( $heading ) ); ?></h3>

						<?php if ( $subtitle ) : ?>
							<p class="qora-promo-card__subtitle"><?php echo nl2br( esc_html( $subtitle ) ); ?></p>
						<?php endif; ?>

						<a href="<?php echo esc_url( $cta_url ); ?>" class="qora-promo-card__cta">
							<span><?php echo esc_html( $cta_text ); ?></span>
							<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
						</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
