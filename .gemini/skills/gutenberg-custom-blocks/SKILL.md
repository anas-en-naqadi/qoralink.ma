---
name: gutenberg-custom-blocks
description: Build custom Gutenberg blocks (block.json, edit/save or render.php, attributes, InspectorControls, RichText, media, InnerBlocks) so admins can edit content and settings from the WordPress editor. Use when turning a front-end component into an editable block.
---

# Gutenberg custom blocks

## When to use
Use this skill when a UI component (hero, feature grid, testimonial slider, CTA, pricing...) must be editable by an admin in the block editor: text inline, images from the media library, links, toggles, colors.

## Stack and setup
- Scaffold with `npx @wordpress/create-block@latest my-block --namespace=mytheme` or add `@wordpress/scripts` to an existing plugin/theme (`wp-scripts start | build`).
- Ship blocks in a **plugin** (survives theme changes) unless the blocks are purely theme-specific. A theme can register blocks too.
- Folder per block: `src/blocks/hero/{block.json, index.js, edit.js, save.js, render.php, style.scss, editor.scss, view.js}`.
- Always register from metadata:
```php
add_action( 'init', function () {
    register_block_type( __DIR__ . '/build/blocks/hero' );
} );
```
(WP 6.8+: `wp_register_block_types_from_metadata_collection()` can register a whole build folder.)

## block.json (source of truth)
```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "mytheme/hero",
  "title": "Hero",
  "category": "design",
  "icon": "cover-image",
  "description": "Home hero with title, text, image and CTA.",
  "textdomain": "mytheme",
  "attributes": {
    "heading": { "type": "string", "source": "html", "selector": "h1" },
    "text": { "type": "string", "source": "html", "selector": "p" },
    "imageId": { "type": "number" },
    "imageUrl": { "type": "string" },
    "imageAlt": { "type": "string", "default": "" },
    "ctaLabel": { "type": "string", "default": "Learn more" },
    "ctaUrl": { "type": "string", "default": "" },
    "align": { "type": "string", "default": "full" }
  },
  "supports": {
    "html": false,
    "align": ["wide", "full"],
    "anchor": true,
    "color": { "background": true, "text": true },
    "spacing": { "padding": true, "margin": true },
    "typography": { "fontSize": true }
  },
  "editorScript": "file:./index.js",
  "editorStyle": "file:./index.css",
  "style": "file:./style-index.css"
}
```
Rules:
- Prefer `supports` (color, spacing, typography, align, border) over hand-built controls. Less code, consistent UI, theme.json aware.
- Attribute `source: "html"` + selector keeps content in saved markup; plain attributes go in the block comment JSON.
- Give every attribute a sensible `default` so a freshly inserted block looks finished.

## Edit component pattern
```js
import { useBlockProps, RichText, InspectorControls,
  MediaUpload, MediaUploadCheck, URLInputButton } from '@wordpress/block-editor';
import { PanelBody, Button, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit( { attributes, setAttributes } ) {
  const { heading, text, imageId, imageUrl, ctaLabel, ctaUrl } = attributes;
  const blockProps = useBlockProps( { className: 'hero' } );
  return (
    <>
      <InspectorControls>
        <PanelBody title={ __( 'Call to action', 'mytheme' ) }>
          <TextControl label={ __( 'Label', 'mytheme' ) } value={ ctaLabel }
            onChange={ ( v ) => setAttributes( { ctaLabel: v } ) } />
          <TextControl label={ __( 'URL', 'mytheme' ) } value={ ctaUrl }
            onChange={ ( v ) => setAttributes( { ctaUrl: v } ) } />
        </PanelBody>
      </InspectorControls>
      <section { ...blockProps }>
        <RichText tagName="h1" value={ heading } allowedFormats={ [] }
          placeholder={ __( 'Title…', 'mytheme' ) }
          onChange={ ( v ) => setAttributes( { heading: v } ) } />
        <RichText tagName="p" value={ text }
          placeholder={ __( 'Text…', 'mytheme' ) }
          onChange={ ( v ) => setAttributes( { text: v } ) } />
        <MediaUploadCheck>
          <MediaUpload allowedTypes={ [ 'image' ] } value={ imageId }
            onSelect={ ( m ) => setAttributes( { imageId: m.id, imageUrl: m.url, imageAlt: m.alt } ) }
            render={ ( { open } ) => <Button variant="secondary" onClick={ open }>
              { imageUrl ? __( 'Replace image', 'mytheme' ) : __( 'Choose image', 'mytheme' ) }
            </Button> } />
        </MediaUploadCheck>
        { imageUrl && <img src={ imageUrl } alt="" /> }
      </section>
    </>
  );
}
```
Guidelines:
- **Inline editing first** (RichText) for visible text; Inspector sidebar for settings (URLs, toggles, layout variants, counts).
- Always spread `useBlockProps()` on the wrapper; use `useBlockProps.save()` in save.
- Use `allowedFormats` to limit formatting where design demands plain text.
- Use `MediaUploadCheck` around media pickers; store both ID and URL (ID lets you request responsive sizes server side).

## Static vs dynamic
| Choose | When |
|---|---|
| Static (`save.js`) | Pure content/markup, no server data. Markup stored in post content. |
| Dynamic (`"render": "file:./render.php"`, `save` returns `null` or InnerBlocks content) | Needs queries (latest posts, WooCommerce products), must update everywhere when markup changes, or needs PHP escaping/logic. |

Dynamic render:
```php
<?php
/** @var array $attributes @var string $content @var WP_Block $block */
$wrapper = get_block_wrapper_attributes( [ 'class' => 'hero' ] );
?>
<section <?php echo $wrapper; ?>>
  <h1><?php echo wp_kses_post( $attributes['heading'] ?? '' ); ?></h1>
  <?php if ( ! empty( $attributes['ctaUrl'] ) ) : ?>
    <a class="hero__cta" href="<?php echo esc_url( $attributes['ctaUrl'] ); ?>">
      <?php echo esc_html( $attributes['ctaLabel'] ); ?>
    </a>
  <?php endif; ?>
</section>
```
Always escape on output: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`.

## Repeatable items (cards, slides, FAQs)
Use **InnerBlocks with a child block** instead of hand-rolled repeater attributes. Admin can add, remove, reorder and edit each item natively.
- Parent: `<InnerBlocks allowedBlocks={['mytheme/feature-card']} template={[['mytheme/feature-card'],['mytheme/feature-card'],['mytheme/feature-card']]} orientation="horizontal" renderAppender={ InnerBlocks.ButtonBlockAppender } />`
- Child `block.json`: `"parent": ["mytheme/features"]`, `"supports": {"reusable": false}`.
- Pass parent settings (columns, style) to children via `providesContext` / `usesContext`.
- Save in parent: `<div {...useBlockProps.save()}><InnerBlocks.Content /></div>`.

## Letting editors compose with core blocks
When the component is mostly layout, a **block pattern** built from core blocks (Group, Columns, Heading, Image, Buttons) is cheaper than a custom block. See `gutenberg-patterns-templates-locking`.

## Dynamic data without a custom block: Block Bindings (WP 6.5+)
Bind a core block attribute to a custom field or source (`metadata.bindings`), e.g. a Heading bound to a post meta. Register sources with `register_block_bindings_source()`.

## Changing a block later
Changing `save()` markup invalidates existing blocks. Add a `deprecated` array (old attributes + old save + `migrate`) in `registerBlockType`, or make the block dynamic.

## Interactivity (sliders, tabs, accordions)
Use the Interactivity API (`"supports": {"interactivity": true}`, `data-wp-interactive`, `data-wp-on--click`, `viewScriptModule`) instead of jQuery. Load JS only where the block is used.

## Definition of done
- Block appears in inserter under a clear category, has icon/description and example (`"example"` in block.json for preview).
- Every visible string/image/link is editable; sensible placeholders; no empty-state breakage.
- Front end matches design; editor preview matches front end (shared SCSS + `editorStyle`).
- Output escaped; strings translatable (`__()`).
- Works with theme.json colors/spacing; responsive; keyboard accessible; images have alt.
