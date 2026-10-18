<?php
/**
 * Server-side render for qora/hero-slider
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

$autoplay = isset( $attributes['autoplay'] ) ? (bool) $attributes['autoplay'] : true;
$interval = isset( $attributes['interval'] ) && (int) $attributes['interval'] !== 6000 && (int) $attributes['interval'] !== 3500 ? (int) $attributes['interval'] : 7000;
$slides   = ! empty( $attributes['slides'] ) && is_array( $attributes['slides'] ) ? $attributes['slides'] : array();

if ( empty( $slides ) ) {
	$slides = array(
		array(
			'eyebrow'        => '',
			'headingPrimary' => "Reliable\nPerformance.",
			'headingAccent'  => 'A Brighter Tomorrow.',
			'subtitle'       => 'Business-grade laptops. More value. Less waste.',
			'ctaText'        => 'Shop Refurbished Laptops',
			'ctaUrl'         => '/product-category/computers/laptops/',
			'bgImage'        => '/wp-content/uploads/2026/10/Gemini_Generated_Image_k9061nk9061nk906.jpg',
			'features'       => array(
				array( 'icon' => 'leaf', 'line1' => 'Sustainable', 'line2' => 'choice' ),
				array( 'icon' => 'shield', 'line1' => 'Tested', 'line2' => '& guaranteed' ),
				array( 'icon' => 'gear', 'line1' => 'Ready for', 'line2' => 'work and study' ),
			),
		),
		array(
			'eyebrow'        => '',
			'headingPrimary' => "Level Up\nYour Game.",
			'headingAccent'  => 'Built For Speed.',
			'subtitle'       => 'Gaming laptops and custom desktops engineered for ultra-smooth frame rates.',
			'ctaText'        => 'Explore Gaming Gear',
			'ctaUrl'         => '/product-category/computers/laptops/gaming-laptops/',
			'bgImage'        => '/wp-content/uploads/2026/10/Gemini_Generated_Image_bg0crhbg0crhbg0c.jpg',
			'features'       => array(
				array( 'icon' => 'bolt', 'line1' => 'High FPS', 'line2' => 'performance' ),
				array( 'icon' => 'shield', 'line1' => 'Official', 'line2' => 'warranty' ),
				array( 'icon' => 'gear', 'line1' => 'Pro-grade', 'line2' => 'cooling' ),
			),
		),
		array(
			'eyebrow'        => '',
			'headingPrimary' => "Sound That\nMoves You.",
			'headingAccent'  => 'Pure Clarity.',
			'subtitle'       => 'Wireless headphones and ANC earbuds crafted for deep focus and crystal-clear calls.',
			'ctaText'        => 'Shop Audio Collection',
			'ctaUrl'         => '/product-category/audio/',
			'bgImage'        => '/wp-content/uploads/2026/10/Gemini_Generated_Image_6r3zb16r3zb16r3z.jpg',
			'features'       => array(
				array( 'icon' => 'leaf', 'line1' => 'Active noise', 'line2' => 'cancelling' ),
				array( 'icon' => 'shield', 'line1' => 'All-day', 'line2' => 'comfort' ),
				array( 'icon' => 'gear', 'line1' => 'Studio', 'line2' => 'acoustics' ),
			),
		),
	);
}

$home_url = rtrim( home_url(), '/' );
$resolve_url = function( $url ) use ( $home_url ) {
	if ( empty( $url ) ) {
		return '';
	}
	if ( strpos( $url, 'http://' ) === 0 || strpos( $url, 'https://' ) === 0 ) {
		return $url;
	}
	return $home_url . '/' . ltrim( $url, '/' );
};

$render_feat_icon = function( $icon ) {
	switch ( $icon ) {
		case 'shield':
			return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>';
		case 'gear':
			return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>';
		case 'bolt':
			return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>';
		case 'leaf':
		default:
			return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>';
	}
};
?>
<section class="qora-hero-section" data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>" data-interval="<?php echo esc_attr( $interval ); ?>" aria-label="<?php esc_attr_e( 'Featured promotions', 'woodmart-child' ); ?>">
	<div class="qora-hero-slides">
		<?php foreach ( $slides as $idx => $slide ) :
			$is_active       = ( 0 === $idx );
			$eyebrow         = isset( $slide['eyebrow'] ) ? $slide['eyebrow'] : '';
			$heading_primary = isset( $slide['headingPrimary'] ) ? $slide['headingPrimary'] : '';
			$heading_accent  = isset( $slide['headingAccent'] ) ? $slide['headingAccent'] : '';
			$subtitle        = isset( $slide['subtitle'] ) ? $slide['subtitle'] : '';
			$cta_text        = isset( $slide['ctaText'] ) ? $slide['ctaText'] : 'Shop Now';
			$cta_url         = $resolve_url( isset( $slide['ctaUrl'] ) ? $slide['ctaUrl'] : '/shop/' );
			$bg_image        = $resolve_url( isset( $slide['bgImage'] ) ? $slide['bgImage'] : '' );
			$features        = ! empty( $slide['features'] ) && is_array( $slide['features'] ) ? $slide['features'] : array();
			?>
			<article class="qora-hero-slide<?php echo $is_active ? ' is-active' : ''; ?>" data-slide-index="<?php echo esc_attr( $idx ); ?>" aria-hidden="<?php echo $is_active ? 'false' : 'true'; ?>">
				<?php if ( $bg_image ) : ?>
					<div class="qora-hero-bg">
						<img
							src="<?php echo esc_url( $bg_image ); ?>"
							alt="<?php echo esc_attr( wp_strip_all_tags( $heading_primary . ' ' . $heading_accent ) ); ?>"
							<?php echo $is_active ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
						/>
						<div class="qora-hero-overlay"></div>
					</div>
				<?php endif; ?>

				<div class="qora-hero-container">
					<div class="qora-hero-content">
						<h1 class="qora-hero-title">
							<span class="qora-hero-title__primary"><?php echo nl2br( esc_html( $heading_primary ) ); ?></span>
							<?php if ( $heading_accent ) : ?>
								<span class="qora-hero-title__accent"><?php echo esc_html( $heading_accent ); ?></span>
							<?php endif; ?>
						</h1>

						<?php if ( $subtitle ) : ?>
							<p class="qora-hero-subtitle"><?php echo esc_html( $subtitle ); ?></p>
						<?php endif; ?>

						<?php if ( ! empty( $features ) ) : ?>
							<ul class="qora-hero-features">
								<?php foreach ( $features as $feat ) : ?>
									<li class="qora-hero-feature">
										<span class="qora-hero-feature__icon">
											<?php echo $render_feat_icon( isset( $feat['icon'] ) ? $feat['icon'] : 'leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</span>
										<span class="qora-hero-feature__text">
											<strong><?php echo esc_html( isset( $feat['line1'] ) ? $feat['line1'] : '' ); ?></strong>
											<span><?php echo esc_html( isset( $feat['line2'] ) ? $feat['line2'] : '' ); ?></span>
										</span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<div class="qora-hero-actions">
							<a href="<?php echo esc_url( $cta_url ); ?>" class="qora-hero-cta">
								<span><?php echo esc_html( $cta_text ); ?></span>
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
							</a>
						</div>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<?php if ( count( $slides ) > 1 ) : ?>
		<div class="qora-hero-pagination" role="tablist" aria-label="<?php esc_attr_e( 'Slide navigation', 'woodmart-child' ); ?>">
			<?php foreach ( $slides as $idx => $slide ) :
				$num = str_pad( (string) ( $idx + 1 ), 2, '0', STR_PAD_LEFT );
				?>
				<button
					type="button"
					class="qora-hero-page-btn<?php echo 0 === $idx ? ' is-active' : ''; ?>"
					data-slide-to="<?php echo esc_attr( $idx ); ?>"
					role="tab"
					aria-selected="<?php echo 0 === $idx ? 'true' : 'false'; ?>"
					aria-label="<?php echo esc_attr( sprintf( __( 'Slide %s', 'woodmart-child' ), $num ) ); ?>"
				>
					<span class="qora-hero-page-num"><?php echo esc_html( $num ); ?></span>
					<span class="qora-hero-page-bar"></span>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
