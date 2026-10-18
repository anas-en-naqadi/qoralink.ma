<?php
/**
 * Server-side render for qora/trust-bar
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

// Temporarily hidden per user request
return;

$items = ! empty( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array(
	array( 'icon' => 'truck', 'title' => 'Fast delivery', 'subtitle' => 'Across Morocco' ),
	array( 'icon' => 'card', 'title' => 'Secure payment', 'subtitle' => 'CMI, cards & more' ),
	array( 'icon' => 'shield', 'title' => 'Official warranty', 'subtitle' => 'Genuine products' ),
	array( 'icon' => 'headset', 'title' => 'Real technical support', 'subtitle' => "We're here to help" ),
);

$render_trust_icon = function( $icon ) {
	switch ( $icon ) {
		case 'card':
			return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="6" y1="15" x2="10" y2="15"/></svg>';
		case 'shield':
			return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>';
		case 'headset':
			return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>';
		case 'truck':
		default:
			return '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>';
	}
};
?>
<section class="qora-trust-bar-section" aria-label="<?php esc_attr_e( 'Store Guarantees', 'woodmart-child' ); ?>">
	<div class="qora-trust-bar-container">
		<div class="qora-trust-bar">
			<?php foreach ( $items as $idx => $item ) :
				$icon     = isset( $item['icon'] ) ? $item['icon'] : 'truck';
				$title    = isset( $item['title'] ) ? $item['title'] : '';
				$subtitle = isset( $item['subtitle'] ) ? $item['subtitle'] : '';
				?>
				<div class="qora-trust-item" data-trust-index="<?php echo esc_attr( $idx ); ?>">
					<span class="qora-trust-item__icon">
						<?php echo $render_trust_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<div class="qora-trust-item__text">
						<h4 class="qora-trust-item__title"><?php echo esc_html( $title ); ?></h4>
						<?php if ( $subtitle ) : ?>
							<p class="qora-trust-item__subtitle"><?php echo esc_html( $subtitle ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
