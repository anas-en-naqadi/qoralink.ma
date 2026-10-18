---
name: gutenberg-patterns-templates-locking
description: Make home page sections editable AND restructurable by admins using block patterns, synced patterns, front-page templates, template parts, theme.json and block locking. Use when admins must add, remove, reorder or restyle whole sections, not just edit text.
---

# Patterns, templates and locking

## Decision guide
| Need | Use |
|---|---|
| Section is mostly layout of core blocks, content changes per use | **Block pattern** (inserted, then edited freely) |
| Same section must stay identical across pages and update everywhere | **Synced pattern** (`wp_block`), optionally with **Pattern Overrides** |
| Section has custom behavior/markup core blocks can't give | **Custom block** (see `gutenberg-custom-blocks`) |
| Header, footer, repeated site chrome | **Template part** |
| Whole home page layout | **Front page template** (block theme) or page content + page template (classic theme) |

## First: identify the theme type
- **Block theme** (has `theme.json` and `templates/*.html`): admin edits via Appearance > Editor (Site Editor). Best experience for editable home page.
- **Classic theme** (e.g. **WoodMart**, even with Gutenberg as the builder): there is no Site Editor and no `front-page.html`. The home page is a normal Page edited in Pages > Home; patterns, synced patterns, custom blocks and locking all work inside that page, while header/footer come from the theme's own builders. Put patterns/CSS in a child theme and blocks in a plugin. See `woodmart-gutenberg-integration`. Sections below about `templates/front-page.html` apply only to block themes; for classic themes use the page content plus the post type `template` shown there.

## Registering patterns (file based, WP 6.0+)
Create `patterns/home-hero.php` in the theme:
```php
<?php
/**
 * Title: Home hero
 * Slug: mytheme/home-hero
 * Description: Hero with title, text, image and CTA.
 * Categories: mytheme-home
 * Keywords: hero, banner
 * Viewport Width: 1400
 * Block Types: core/post-content
 * Inserter: true
 */
?>
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull">
  <!-- wp:heading {"level":1} --><h1 class="wp-block-heading">Your title</h1><!-- /wp:heading -->
  <!-- wp:paragraph --><p>Supporting text.</p><!-- /wp:paragraph -->
  <!-- wp:buttons --><div class="wp-block-buttons">
    <!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Call to action</a></div><!-- /wp:button -->
  </div><!-- /wp:buttons -->
</div>
<!-- /wp:group -->
```
- Add a category: `register_block_pattern_category( 'mytheme-home', [ 'label' => __( 'Home sections', 'mytheme' ) ] );`
- Use `<?php echo esc_url( get_theme_file_uri( 'assets/img/x.jpg' ) ); ?>` for bundled images.
- Plugins/older themes: `register_block_pattern( $slug, $args )`.
- Copy the markup from the editor: build the section visually, then Code editor view, paste into the pattern file. Never hand-write block comments from memory if you can avoid it.

## Synced patterns and overrides
- Create in the editor: select blocks > Create pattern > Synced. Edits propagate everywhere.
- **Pattern Overrides (WP 6.6+)**: in a synced pattern, set a block's Advanced > "Enable overrides" so each instance can change only text/image/link while layout stays locked.
- Use for repeated CTA banners, newsletter strips, trust badges.

## Front page template (block theme)
`templates/front-page.html`:
```html
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->
<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->
<main class="wp-block-group">
  <!-- wp:post-content {"layout":{"type":"constrained"}} /-->
</main>
<!-- /wp:group -->
<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
```
Then the admin builds the home page in Pages > Home using patterns and blocks. To make a ready-made layout the default content for a page, use a pattern, or register a post type template:
```php
add_action( 'init', function () {
    $pt = get_post_type_object( 'page' );
    $pt->template = [
        [ 'mytheme/hero' ],
        [ 'mytheme/features' ],
        [ 'core/pattern', [ 'slug' => 'mytheme/home-cta' ] ],
    ];
} );
```
(Applies to new pages only; existing content is untouched.)

## Locking: control how much the admin can change
Pick the level per goal:
- **Free**: no lock. Admin can add, delete, move anything.
- **Structure locked, content editable**: `templateLock: "contentOnly"` (on a Group/InnerBlocks) or the "Content only" lock in the editor. Admin edits text/images but cannot move/delete blocks.
- **No move/remove for specific blocks**: block attribute `"lock": {"move": true, "remove": true}`.
- **Fully locked**: `templateLock: "all"`.
- Code-level for a CPT/page: `$pt->template_lock = 'contentOnly';`.
- Role-based: use `block_editor_settings_all` / `allowed_block_types_all` filters to restrict blocks for non-admins.

For "admin can edit the blocs and the blocs themselves", keep the page itself **unlocked** at the top level, and lock only the internals of fragile custom blocks.

## theme.json: make "restyling" safe and easy
Expose design tokens so admins can change look without code:
```json
{
  "$schema": "https://schemas.wp.org/trunk/theme.json",
  "version": 3,
  "settings": {
    "appearanceTools": true,
    "layout": { "contentSize": "1140px", "wideSize": "1320px" },
    "color": {
      "palette": [
        { "slug": "primary", "name": "Primary", "color": "#0A2540" },
        { "slug": "accent",  "name": "Accent",  "color": "#87BFD1" }
      ],
      "custom": false, "defaultPalette": false
    },
    "typography": {
      "fluid": true,
      "fontSizes": [
        { "slug": "sm", "size": "0.875rem", "name": "Small" },
        { "slug": "lg", "size": "clamp(1.25rem, 2vw, 1.5rem)", "name": "Large" }
      ]
    },
    "spacing": { "spacingSizes": [
      { "slug": "30", "size": "1rem", "name": "S" },
      { "slug": "50", "size": "2rem", "name": "M" },
      { "slug": "70", "size": "4rem", "name": "L" } ] }
  },
  "styles": {
    "blocks": { "core/button": { "border": { "radius": "8px" } } }
  }
}
```
- Disable what the design doesn't need (`custom: false`) to keep the UI simple for admins.
- Register **block styles** for variants: `register_block_style( 'core/group', [ 'name' => 'card', 'label' => 'Card' ] );` then style `.is-style-card`.

## Classic theme notes
- Add `add_theme_support( 'editor-styles' ); add_editor_style( 'assets/editor.css' );` so the editor matches the front end.
- Opt in to theme.json even in classic themes (it works) for palette/typography.
- `add_theme_support( 'align-wide' )`, `responsive-embeds`, `wp-block-styles`.

## Verification checklist
- Open the home page as an admin: can add a section from the inserter (Patterns tab, "Home sections").
- Can reorder, delete and duplicate sections with the List View.
- Can edit all text, images and links without touching code.
- Switching theme variations/colors in Styles updates the sections.
- Front end and editor look the same at mobile, tablet, desktop.
