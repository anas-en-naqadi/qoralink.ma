---
name: wp-gutenberg-blocks
description: "Best practices and standards for building custom Gutenberg blocks in WordPress with React (JSX), SCSS, block.json, dynamic render.php, WooCommerce integration, and responsive layouts."
compatibility: "WordPress 6.5+ / 7.0+, Node.js 18+, @wordpress/scripts"
---

# WordPress Custom Gutenberg Blocks Guidelines

## 1. Block Architecture
- **block.json (Metadata v3)**: Every block must define its schema, attributes, supports, editor script, style, and dynamic render template in `block.json`.
- **Directory Structure per Block**:
  ```
  block-name/
  ├── block.json        # Schema, attributes, style/editorScript definitions
  ├── index.js          # registerBlockType entrypoint
  ├── edit.js           # React editor component with InspectorControls & RichText
  ├── render.php        # Server-side PHP rendering for dynamic frontend output
  ├── style.scss        # Frontend + Editor CSS styling (component-scoped)
  └── editor.scss       # Editor-only CSS tweaks (if needed)
  ```
- **Registration**: Use `wp_register_block_types_from_metadata_collection()` or `register_block_type()` referencing the build directory.

## 2. Dynamic vs Static Rendering
- For dynamic data (WooCommerce products, categories, cart counts, live menus):
  - Do NOT save static HTML in `save()`. Use `save: () => null` or `<InnerBlocks.Content />`.
  - Let `render.php` handle frontend rendering via standard WordPress & WooCommerce APIs (`get_terms`, `wc_get_products`, `wp_nav_menu`, etc.).
  - In `edit.js`, use `@wordpress/server-side-render` or a mirrored React preview component with live `@wordpress/components` controls.

## 3. Styling & Responsiveness Standards
- Scope all selectors under the block class (e.g. `.qora-hero-slider`, `.qora-product-showcase`).
- Use CSS Custom Properties (Tokens) for consistent spacing, typography, and colors (`--qora-primary`, `--qora-radius`, etc.).
- Responsive breakpoints: Mobile-first or desktop-down with standard queries (`max-width: 1024px`, `max-width: 768px`, `max-width: 480px`).
- Never rely on fixed pixel widths for layout columns. Use CSS Grid (`grid-template-columns: repeat(6, 1fr)`) or Flexbox.

## 4. WooCommerce Integration
- Use `WC_Product_Query` or `wc_get_products()` in PHP for optimized queries with proper caching (`get_transient` / `set_transient`).
- AJAX Add-to-Cart buttons must include `add_to_cart_button ajax_add_to_cart` and `data-product_id` to integrate seamlessly with WooCommerce core scripts and cart fragments.
- Sanitize and escape all output (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`).
