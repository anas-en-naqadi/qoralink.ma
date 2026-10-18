<?php
/**
 * WoodMart Child Theme - Global Header Template Override
 *
 * Implements full child theme header template replacement preserving
 * .wd-page-wrapper.website-wrapper DOM symmetry and delegating dynamic
 * header rendering to the qora/site-header Gutenberg block.
 *
 * @package woodmart-child
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php if ( function_exists( 'wp_body_open' ) ) : ?>
		<?php wp_body_open(); ?>
	<?php endif; ?>

	<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'Skip to content', 'woodmart-child' ); ?></a>

	<?php do_action( 'woodmart_after_body_open' ); ?>

	<div class="wd-page-wrapper website-wrapper">
		<?php if ( woodmart_needs_header() ) : ?>
			<?php if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) : ?>
				<?php
				$qora_header_rendered = false;

				// 1. Theme helper function
				if ( function_exists( 'qora_render_site_header_block' ) ) {
					ob_start();
					qora_render_site_header_block();
					$qora_header_markup = ob_get_clean();
					if ( ! empty( trim( $qora_header_markup ) ) ) {
						echo $qora_header_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$qora_header_rendered = true;
					}
				}

				// 2. Core render_block() if block type registered
				if ( ! $qora_header_rendered && function_exists( 'render_block' ) && class_exists( 'WP_Block_Type_Registry' ) && WP_Block_Type_Registry::get_instance()->is_registered( 'qora/site-header' ) ) {
					$qora_block_markup = render_block( array(
						'blockName' => 'qora/site-header',
						'attrs'     => array(),
					) );
					if ( ! empty( trim( $qora_block_markup ) ) ) {
						echo $qora_block_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$qora_header_rendered = true;
					}
				}

				// 3. Fallback direct build render.php include
				if ( ! $qora_header_rendered && file_exists( get_stylesheet_directory() . '/build/blocks/site-header/render.php' ) ) {
					$attributes = array();
					$content    = '';
					$block      = null;
					require get_stylesheet_directory() . '/build/blocks/site-header/render.php';
					$qora_header_rendered = true;
				}

				// 4. Fallback direct src render.php include
				if ( ! $qora_header_rendered && file_exists( get_stylesheet_directory() . '/src/blocks/site-header/render.php' ) ) {
					$attributes = array();
					$content    = '';
					$block      = null;
					require get_stylesheet_directory() . '/src/blocks/site-header/render.php';
					$qora_header_rendered = true;
				}
				?>
			<?php endif; ?>

			<?php woodmart_page_top_part(); ?>
		<?php endif; ?>
