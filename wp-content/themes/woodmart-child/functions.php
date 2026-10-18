<?php
/**
 * WoodMart Child Theme - Qoralink Functions
 *
 * Theme-native modular architecture for Qoralink Gutenberg blocks,
 * design tokens, and live WooCommerce data integration.
 *
 * @package woodmart-child
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// -----------------------------------------------------------------------------
// 1. Core Theme Constants
// -----------------------------------------------------------------------------
define( 'QORA_THEME_VERSION', '1.0.0' );
define( 'QORA_THEME_DIR', get_stylesheet_directory() );
define( 'QORA_THEME_URI', get_stylesheet_directory_uri() );
define( 'QORA_INC_DIR', QORA_THEME_DIR . '/inc' );
define( 'QORA_BLOCKS_BUILD_DIR', QORA_THEME_DIR . '/build/blocks' );
define( 'QORA_BLOCKS_BUILD_URI', QORA_THEME_URI . '/build/blocks' );

// -----------------------------------------------------------------------------
// 2. Load Modular Subsystems
// -----------------------------------------------------------------------------

// Theme setup, block categories, Google fonts, and asset enqueueing.
require_once QORA_INC_DIR . '/setup.php';

// Dynamic Gutenberg block registration iterator.
require_once QORA_INC_DIR . '/blocks-register.php';

// WooCommerce compatibility, live AJAX cart fragments, and cart helpers.
require_once QORA_INC_DIR . '/woocommerce-compat.php';

// Data query helpers, category image fallbacks, and 12-slot product padding.
require_once QORA_INC_DIR . '/helpers.php';