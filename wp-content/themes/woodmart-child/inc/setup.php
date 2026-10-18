<?php
/**
 * Theme Setup, Block Category & Asset Enqueueing
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'qora_register_block_category' ) ) {
	/**
	 * Register 'Qoralink' custom block category at the top of the block inserter.
	 *
	 * @param array                   $categories Array of block categories.
	 * @param WP_Block_Editor_Context $context    Current block editor context.
	 * @return array Modified array of block categories.
	 */
	function qora_register_block_category( $categories, $context ) {
		if ( ! is_array( $categories ) ) {
			$categories = array();
		}

		$qora_category = array(
			'slug'  => 'qoralink',
			'title' => esc_html__( 'Qoralink', 'woodmart-child' ),
			'icon'  => null,
		);

		// Prepend so Qoralink blocks are prominently displayed at the top.
		return array_merge( array( $qora_category ), $categories );
	}
	add_filter( 'block_categories_all', 'qora_register_block_category', 10, 2 );
}

if ( ! function_exists( 'qora_theme_setup_supports' ) ) {
	/**
	 * Declare theme supports required for full-bleed and aligned Gutenberg blocks.
	 *
	 * @return void
	 */
	function qora_theme_setup_supports() {
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'responsive-embeds' );
	}
	add_action( 'after_setup_theme', 'qora_theme_setup_supports', 20 );
}

if ( ! function_exists( 'qora_enqueue_frontend_assets' ) ) {
	/**
	 * Enqueue design tokens, child theme stylesheet, and global assets.
	 * Typography is handled natively by WoodMart's built-in font loader.
	 *
	 * @return void
	 */
	function qora_enqueue_frontend_assets() {
		// 1. Qoralink Design Tokens CSS (Check assets/css/tokens.css then css/tokens.css)
		$token_rel_path = file_exists( QORA_THEME_DIR . '/assets/css/tokens.css' )
			? '/assets/css/tokens.css'
			: ( file_exists( QORA_THEME_DIR . '/css/tokens.css' ) ? '/css/tokens.css' : '' );

		if ( ! empty( $token_rel_path ) ) {
			wp_enqueue_style(
				'qora-design-tokens',
				QORA_THEME_URI . $token_rel_path,
				array(),
				QORA_THEME_VERSION
			);
		}

		// 2. Child Theme Stylesheet (dependent on WoodMart parent style)
		wp_enqueue_style(
			'child-style',
			QORA_THEME_URI . '/style.css',
			array( 'woodmart-style' ),
			filemtime( QORA_THEME_DIR . '/style.css' )
		);

		// 3. Pre-register / enqueue Site Header block assets globally for header.php
		$header_style_path = QORA_THEME_DIR . '/build/blocks/site-header/style-index.css';
		if ( file_exists( $header_style_path ) ) {
			wp_enqueue_style(
				'qora-site-header-style',
				QORA_THEME_URI . '/build/blocks/site-header/style-index.css',
				array( 'child-style' ),
				QORA_THEME_VERSION
			);
		}

		// 4. Enqueue consolidated flat CSS for all 7 Qora blocks
		if ( file_exists( QORA_THEME_DIR . '/assets/css/qora-blocks.css' ) ) {
			wp_enqueue_style(
				'qora-blocks-consolidated',
				QORA_THEME_URI . '/assets/css/qora-blocks.css',
				array( 'child-style' ),
				filemtime( QORA_THEME_DIR . '/assets/css/qora-blocks.css' )
			);
		}

		$header_view_path = QORA_THEME_DIR . '/build/blocks/site-header/view.js';
		$header_asset_php = QORA_THEME_DIR . '/build/blocks/site-header/view.asset.php';
		if ( file_exists( $header_view_path ) ) {
			$deps    = array();
			$version = filemtime( $header_view_path );
			if ( file_exists( $header_asset_php ) ) {
				$asset_info = require $header_asset_php;
				$deps       = isset( $asset_info['dependencies'] ) ? $asset_info['dependencies'] : array();
			}
			wp_enqueue_script(
				'qora-site-header-view',
				QORA_THEME_URI . '/build/blocks/site-header/view.js',
				$deps,
				$version,
				true
			);
			wp_localize_script(
				'qora-site-header-view',
				'qoraHeaderConfig',
				array(
					'restUrl'  => esc_url_raw( rest_url( 'qora/v1/search-products' ) ),
					'ajaxUrl'  => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
					'wcAjax'   => class_exists( 'WC_AJAX' ) ? esc_url_raw( WC_AJAX::get_endpoint( 'qora_live_product_search' ) ) : '',
					'homeUrl'  => esc_url_raw( home_url( '/' ) ),
					'limit'    => 5,
					'minChars' => 2,
					'i18n'     => array(
						'searching'   => esc_html__( 'Searching products...', 'woodmart-child' ),
						'noResults'   => esc_html__( 'No products found for', 'woodmart-child' ),
						'viewAll'     => esc_html__( 'View all results', 'woodmart-child' ),
						'clearSearch' => esc_html__( 'Clear search', 'woodmart-child' ),
						'suggestions' => esc_html__( 'Suggested Products', 'woodmart-child' ),
					),
				)
			);
		}

		$slider_view_path = QORA_THEME_DIR . '/build/blocks/hero-slider/view.js';
		if ( file_exists( $slider_view_path ) ) {
			wp_enqueue_script(
				'qora-hero-slider-view',
				QORA_THEME_URI . '/build/blocks/hero-slider/view.js',
				array(),
				filemtime( $slider_view_path ),
				true
			);
		}

		$wishlist_script_path = QORA_THEME_DIR . '/assets/js/qora-wishlist.js';
		if ( file_exists( $wishlist_script_path ) ) {
			wp_enqueue_script(
				'qora-wishlist-handler',
				QORA_THEME_URI . '/assets/js/qora-wishlist.js',
				array( 'jquery' ),
				filemtime( $wishlist_script_path ),
				true
			);
		}
	}
	add_action( 'wp_enqueue_scripts', 'qora_enqueue_frontend_assets', 1000 );
}

if ( ! function_exists( 'qora_enqueue_block_editor_assets' ) ) {
	/**
	 * Enqueue design tokens into the Gutenberg Block Editor.
	 *
	 * @return void
	 */
	function qora_enqueue_block_editor_assets() {
		$token_rel_path = file_exists( QORA_THEME_DIR . '/assets/css/tokens.css' )
			? '/assets/css/tokens.css'
			: ( file_exists( QORA_THEME_DIR . '/css/tokens.css' ) ? '/css/tokens.css' : '' );

		if ( ! empty( $token_rel_path ) ) {
			wp_enqueue_style(
				'qora-editor-tokens',
				QORA_THEME_URI . $token_rel_path,
				array(),
				QORA_THEME_VERSION
			);
		}

		if ( file_exists( QORA_THEME_DIR . '/assets/css/qora-blocks.css' ) ) {
			wp_enqueue_style(
				'qora-editor-blocks-consolidated',
				QORA_THEME_URI . '/assets/css/qora-blocks.css',
				array(),
				filemtime( QORA_THEME_DIR . '/assets/css/qora-blocks.css' )
			);
		}
	}
	add_action( 'enqueue_block_editor_assets', 'qora_enqueue_block_editor_assets' );
}
