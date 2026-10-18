---
name: woodmart-gutenberg-integration
description: Build admin-editable home page components on the WoodMart WooCommerce theme using core Gutenberg (classic theme, no Site Editor). Covers enabling Gutenberg in WoodMart, child theme setup, patterns, custom blocks in a plugin, CSS scoping and WoodMart-specific pitfalls.
---

# WoodMart + Gutenberg: editable home page components

## What WoodMart is (and is not)
- WoodMart is a **classic theme**, not a block (FSE) theme. There is no Site Editor, no `templates/*.html`, no `front-page.html`. The home page is a normal WordPress **Page** edited in Pages > Home. Header, footer and shop layouts are managed by WoodMart's own builders, outside the page content.
- WoodMart ships its **own Gutenberg block library** (Row/Container, Full width section, slider, dynamic content, product and layout blocks, etc.). Styles and scripts are loaded only for blocks actually used on a page. Many product/layout blocks are dynamic (server rendered). Check the inserter for the WoodMart categories in your installed version.
- Core blocks + WoodMart blocks + your own blocks can live in the same page.

## Enable Gutenberg as the builder
- Existing site: **Dashboard > Theme Settings > Other > Page builder**. Options: *Current builder* (format used when importing demos/layouts) and *Gutenberg editor for products*.
- These settings **do not convert** existing pages. Elementor/WPBakery pages keep their builder. To migrate a page: duplicate it, rebuild sections with blocks, test desktop/tablet/mobile, then replace.
- Make sure the Home page is the static front page: Settings > Reading > "A static page".

## Where to put custom code (do not edit the parent theme)
If the project already keeps its blocks in the child theme (`src/blocks`, `build/blocks`, `inc/blocks-register.php`, as in the Qora site), keep following that structure and read `qora-child-theme-blocks`; do not move blocks around. The plugin option below is the recommendation for new projects.
| Code | Location | Why |
|---|---|---|
| Custom blocks (JS/PHP) | **Own plugin** (`mysite-blocks`) for new projects; the child theme if the project already does it | Plugin survives theme switch/update, reusable |
| Patterns, block styles, editor CSS, tiny PHP hooks | **WoodMart child theme** | Theme-specific look |
| Seed content for the home page | WP-CLI / one-time script | Avoid manual rebuild |

## Child theme setup
`functions.php` (child):
```php
add_action( 'init', function () {
    register_block_pattern_category( 'mysite-home', [ 'label' => __( 'Home sections', 'mysite' ) ] );
} );

// Make the editor look like the front end
add_action( 'enqueue_block_assets', function () {
    if ( is_admin() ) {
        wp_enqueue_style( 'mysite-editor', get_stylesheet_directory_uri() . '/assets/editor.css', [], filemtime( get_stylesheet_directory() . '/assets/editor.css' ) );
    }
} );

// Optional: custom block style variants
add_action( 'init', function () {
    register_block_style( 'core/group', [ 'name' => 'card', 'label' => __( 'Card', 'mysite' ) ] );
} );
```
- File-based patterns: put `patterns/home-hero.php` (with the header comment: Title, Slug, Categories, Inserter) in the child theme; WordPress registers them for classic themes too (WP 6.0+). Test that your WP version picks them up; fall back to `register_block_pattern()` if not.
- `theme.json` in a classic child theme works (palette, font sizes, spacing presets), but it changes some editor behavior. Add it deliberately and test, or use `add_theme_support( 'editor-color-palette', [...] )` and `editor-font-sizes` for a lighter approach. Match values to WoodMart's Theme Settings colors/fonts so editor, patterns and site stay consistent.

## Choosing how to implement each component
Order of preference:
1. **WoodMart block already does it** (slider, product grid, banner/info box, countdown...). Use it. Zero code, consistent with the theme.
2. **Pattern** of core + WoodMart blocks, saved in the child theme. Admin inserts it, then edits text/images/colors. Can add/remove/reorder freely.
3. **Synced pattern** (with overrides) for repeated strips (newsletter, trust badges, promo bar).
4. **Custom block in your plugin** when you need your own markup, repeatable items (InnerBlocks), or behavior. See `gutenberg-custom-blocks`.

Rule: if a component can be 90% built with Group + Columns + Heading + Image + Buttons, make it a **pattern**; reserve custom blocks for real custom markup.

## Making the home page both editable and restructurable
- Keep the page content unlocked at top level so the admin can add, remove, move and duplicate sections (List View).
- Only lock inside fragile custom blocks (`templateLock: "contentOnly"` or per-block `lock`).
- Provide all home sections as patterns in the "Home sections" category so a deleted section can be re-added.
- Optionally give the Page post type a default `template` (hero + features + CTA) for new pages.

## Styling pitfalls specific to WoodMart
- WoodMart applies global typography, button and heading styles from Theme Settings. Your blocks inherit them; do not hard-code fonts/colors. Prefer WoodMart's CSS variables where available (inspect `:root` on the front end to see the exact variable names for your version) and fall back to your own tokens.
- Scope your CSS under the block wrapper class (`.wp-block-mysite-hero`, or BEM `.mysite-hero`). Avoid `!important`.
- Many WoodMart elements use `wd-` prefixed classes and their own container widths. If your section looks narrower/wider than the theme's, wrap it in a core Group with `align: full` and an inner constrained layout, or match WoodMart's container width in CSS.
- Check **WoodMart page options** (header style, title bar, sidebar, layout) on the Home page so the page template does not add a title or sidebar above your hero.
- Caching/optimization: WoodMart offers CSS/JS options in Theme Settings > Performance; if blocks look unstyled on the front end, clear caches and test with those options off.
- RTL/Arabic: if used, enable RTL in WP language and test logical CSS properties.

## Using WoodMart blocks inside patterns
- Build the section visually with WoodMart + core blocks, switch to the **Code editor** (top bar > Options), copy the markup into the pattern file.
- Replace uploaded image URLs with theme asset URLs or leave empty placeholders so the pattern is portable between environments.
- Test the pattern on staging with the same WoodMart version; WoodMart blocks require the theme (and sometimes WooCommerce) to be active.

## Seeding the home page
```bash
wp db export backup-before-home.sql
wp post update <HOME_ID> --post_content="$(cat home-content.html)"
wp option update show_on_front page
wp option update page_on_front <HOME_ID>
```
`home-content.html` is the serialized block markup copied from the Code editor.

## Acceptance checklist
- Home page edits with Gutenberg only; Page builder setting is Gutenberg.
- Admin can: edit every text/image/link, add a section from Patterns > Home sections, reorder with List View, duplicate and delete sections.
- Looks identical on staging and production; mobile/tablet checked with WoodMart responsive tools.
- Custom blocks are in a plugin, patterns/CSS in the child theme; parent theme untouched.
- Parent theme update tested on staging (blocks and patterns still render).
- Short admin guide delivered (see `gutenberg-block-qa-security-handoff`).
