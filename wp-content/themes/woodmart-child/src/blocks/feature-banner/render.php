<?php
/**
 * Server-side render for qora/feature-banner
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

$home_url  = rtrim( home_url(), '/' );
$eyebrow   = isset( $attributes['eyebrow'] ) ? $attributes['eyebrow'] : 'WORKSPACE ESSENTIALS';
$heading   = isset( $attributes['heading'] ) ? $attributes['heading'] : "Your Space.\nYour Way.";
$subtitle  = isset( $attributes['subtitle'] ) ? $attributes['subtitle'] : "Tools for a more comfortable,\nproductive and organized day.";
$cta_text  = isset( $attributes['ctaText'] ) ? $attributes['ctaText'] : 'Explore Workspace';
$cta_url   = isset( $attributes['ctaUrl'] ) ? $attributes['ctaUrl'] : '/product-category/computers/peripherals/';
$bg_image  = isset( $attributes['bgImage'] ) ? $attributes['bgImage'] : '/wp-content/uploads/2026/10/Gemini_Generated_Image_nr594hnr594hnr59.jpg';
$checklist = ! empty( $attributes['checklist'] ) && is_array( $attributes['checklist'] ) ? $attributes['checklist'] : array(
	array( 'icon' => 'check', 'label' => 'Focus better' ),
	array( 'icon' => 'ergonomic', 'label' => 'Work in comfort' ),
	array( 'icon' => 'grid', 'label' => 'Stay organized' ),
	array( 'icon' => 'shield', 'label' => 'Do more' ),
);

if ( ! empty( $cta_url ) && strpos( $cta_url, 'http' ) !== 0 ) {
	$cta_url = $home_url . '/' . ltrim( $cta_url, '/' );
}
if ( ! empty( $bg_image ) && strpos( $bg_image, 'http' ) !== 0 ) {
	$bg_image = $home_url . '/' . ltrim( $bg_image, '/' );
}

$render_check_icon = function( $icon ) {
	switch ( $icon ) {
		case 'ergonomic':
			return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="12" rx="2"/><line x1="8" y1="20" x2="16" y2="20"/><line x1="12" y1="16" x2="12" y2="20"/></svg>';
		case 'grid':
			return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>';
		case 'shield':
			return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>';
		case 'check':
		default:
			return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m9 12 2 2 4-4"/></svg>';
	}
};
?>
<section class="qora-feature-banner-section" aria-label="<?php echo esc_attr( $eyebrow ); ?>">
	<div class="qora-feature-banner-container">
		<div class="qora-feature-banner">
			<?php if ( $bg_image ) : ?>
				<div class="qora-feature-banner__bg">
					<img src="<?php echo esc_url( $bg_image ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $heading ) ); ?>" loading="lazy" />
					<div class="qora-feature-banner__scrim"></div>
				</div>
			<?php endif; ?>

			<div class="qora-feature-banner__inner">
				<div class="qora-feature-banner__copy">
					<?php if ( $eyebrow ) : ?>
						<span class="qora-feature-banner__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
					<?php endif; ?>

					<h2 class="qora-feature-banner__title"><?php echo nl2br( esc_html( $heading ) ); ?></h2>

					<?php if ( $subtitle ) : ?>
						<p class="qora-feature-banner__subtitle"><?php echo nl2br( esc_html( $subtitle ) ); ?></p>
					<?php endif; ?>

					<a href="<?php echo esc_url( $cta_url ); ?>" class="qora-feature-banner__cta">
						<span><?php echo esc_html( $cta_text ); ?></span>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
					</a>
				</div>

				<?php if ( ! empty( $checklist ) ) : ?>
					<div class="qora-feature-banner__card">
						<ul class="qora-feature-banner__list">
							<?php foreach ( $checklist as $item ) : ?>
								<li class="qora-feature-banner__item">
									<span class="qora-feature-banner__icon">
										<?php echo $render_check_icon( isset( $item['icon'] ) ? $item['icon'] : 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</span>
									<span class="qora-feature-banner__label"><?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
