<?php
/**
 * Dynamic Gutenberg Block Registration
 *
 * Scans build/blocks/ for compiled block metadata (block.json) and registers
 * each block type using WordPress core register_block_type().
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'qora_register_custom_blocks' ) ) {
	/**
	 * Automatically register all compiled Gutenberg blocks in build/blocks/.
	 *
	 * @return void
	 */
	function qora_register_custom_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$build_dir = QORA_BLOCKS_BUILD_DIR;

		// Expected 7 Qoralink blocks for deterministic ordering
		$expected_blocks = array(
			'site-header',
			'hero-slider',
			'category-grid',
			'feature-banner',
			'product-showcase',
			'promo-duo',
			'trust-bar',
		);

		// First, register from the expected list if present
		$registered_slugs = array();
		foreach ( $expected_blocks as $slug ) {
			$block_path = $build_dir . '/' . $slug;
			if ( file_exists( $block_path . '/block.json' ) ) {
				$result = register_block_type( $block_path );
				if ( $result ) {
					$registered_slugs[] = $slug;
				}
			}
		}

		// Second, dynamically discover any additional blocks compiled into build/blocks/
		if ( is_dir( $build_dir ) ) {
			$block_dirs = glob( $build_dir . '/*', GLOB_ONLYDIR );
			if ( is_array( $block_dirs ) ) {
				foreach ( $block_dirs as $block_dir ) {
					$slug = basename( $block_dir );
					if ( in_array( $slug, $registered_slugs, true ) ) {
						continue; // Already registered
					}
					if ( file_exists( $block_dir . '/block.json' ) ) {
						register_block_type( $block_dir );
					}
				}
			}
		}
	}
	add_action( 'init', 'qora_register_custom_blocks', 10 );
}

if ( ! function_exists( 'qora_render_site_header_block' ) ) {
	/**
	 * Safe helper to render the qora/site-header block in header.php.
	 *
	 * Uses render_block() if the block is registered; otherwise directly requires render.php.
	 *
	 * @return void
	 */
	function qora_render_site_header_block() {
		if ( function_exists( 'render_block' ) && class_exists( 'WP_Block_Type_Registry' ) && WP_Block_Type_Registry::get_instance()->is_registered( 'qora/site-header' ) ) {
			echo render_block( array( // phpcs:ignore
				'blockName' => 'qora/site-header',
				'attrs'     => array(),
			) );
		} elseif ( file_exists( QORA_BLOCKS_BUILD_DIR . '/site-header/render.php' ) ) {
			$attributes = array();
			$content    = '';
			$block      = null;
			require QORA_BLOCKS_BUILD_DIR . '/site-header/render.php';
		}
	}
}
