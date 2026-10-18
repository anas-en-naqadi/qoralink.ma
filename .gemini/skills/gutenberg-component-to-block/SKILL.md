---
name: gutenberg-component-to-block
description: Workflow for converting an existing HTML/CSS/JS home page component into an admin-editable Gutenberg block or pattern. Use when the user already has designed components and needs them editable in WordPress.
---

# Convert a component into an editable Gutenberg block

## Inputs to collect (ask only what is missing)
1. The component HTML/CSS/JS (or Figma/screenshot).
2. Theme type: block theme, classic theme, or page-builder theme (Elementor, WoodMart...). Decides where the result lives.
3. Whether the admin may only **edit content** or also **add/remove/reorder** items and sections.
4. WordPress and PHP versions, and whether a build step (Node) is available on the dev machine.

## Step 1: Audit the component
Make a table of every element:
| Element | Editable? | Type | Attribute / control |
|---|---|---|---|
| Heading | yes | rich text | `heading` (RichText) |
| Background image | yes | media | `bgId`, `bgUrl` |
| CTA button | yes | label + URL + new tab | `ctaLabel`, `ctaUrl`, `ctaNewTab` |
| 3 feature cards | add/remove | repeatable | child block via InnerBlocks |
| Section background | yes | color | `supports.color` |
| Layout variant | yes | select | `variant` in Inspector |
| Decorative SVG | no | static | markup in save/render |

Rule of thumb: text, images, links, colors, spacing = editable. Structural decoration and class names = fixed.

## Step 2: Choose the implementation (cheapest that works)
1. **Pattern of core blocks**: component is plain layout (group, columns, heading, image, buttons). No JS build needed. Fastest, most flexible for admins.
2. **Pattern + block styles + theme.json**: same as 1 with a custom look via CSS classes.
3. **Custom block**: custom markup, tight design control, repeatable items, or behavior (slider, tabs, counters). See `gutenberg-custom-blocks`.
4. **Dynamic block**: data from posts/products/ACF/options. Use `render.php`.
5. **Page builder widget** (Elementor/WoodMart): only if the site standardizes on that builder; otherwise Gutenberg.

## Step 3: Map HTML to block markup
- Keep original class names (BEM) so existing CSS still applies; add `wp-block-<namespace>-<name>` via `useBlockProps`.
- Replace hard-coded text with `RichText`, images with `MediaUpload`, links with URL attributes.
- Wrap repeated markup with `InnerBlocks` + a child block.
- Move inline styles to CSS; expose colors/spacing through `supports` and theme.json presets.
- Convert hard-coded colors/fonts/spacing to CSS variables from theme.json (`var(--wp--preset--color--primary)`).

## Step 4: Build
- Scaffold: `npx @wordpress/create-block@latest <slug> --namespace=<ns>`; or add to an existing plugin.
- Register in a plugin/theme with `register_block_type( __DIR__ . '/build/<slug>' )`.
- Provide `example` data in block.json so the inserter preview looks good.
- Share SCSS between editor and front end; add `editor.scss` only for editor-only tweaks.
- JS behavior: Interactivity API or small vanilla module in `viewScriptModule`; no jQuery dependency.

## Step 5: Seed the home page
Don't make the admin rebuild everything. Provide the starting layout as a pattern or page template, then:
- Create/update the Home page content programmatically (one-time script or WP-CLI):
```bash
wp post update <HOME_ID> --post_content="$(cat home-content.html)"
```
- Or `wp eval` to insert a `wp_insert_post` with serialized block markup.
- Set it as static front page: `wp option update show_on_front page && wp option update page_on_front <HOME_ID>`.
- Back up before overwriting existing content (`wp db export`).

## Step 6: Editor experience polish
- Clear block titles, icons, descriptions; group blocks in a custom category (`block_categories_all` filter).
- Helpful placeholders ("Write the section title...").
- Sensible defaults so a new block never looks broken.
- Limit risky controls: disable `html`, restrict `allowedFormats`, use `allowedBlocks` in InnerBlocks.
- Hide controls the design does not need (supports flags false).

## Step 7: Acceptance tests
- Add the block from the inserter on an empty page: looks designed.
- Edit every text/image/link; reload; nothing lost; no "Attempt block recovery" notice.
- Duplicate, move, delete, undo/redo.
- Mobile/tablet/desktop; long text; missing image; empty fields.
- Keyboard navigation, focus states, alt text, color contrast.
- Check front end HTML validity and no console errors.
- Run as an Editor-role user (not only Administrator).

## Common pitfalls
- Changing `save()` after content exists causes validation errors; add `deprecated` or use a dynamic block.
- Saving only `imageUrl` and not `imageId` blocks responsive images.
- Forgetting to escape in `render.php`.
- Theme CSS overriding block CSS: scope with the wrapper class, avoid `!important`.
- Page-builder themes injecting their own typography: test inside the real theme, not only the default one.
- Block registered in the theme but the theme later changes: keep reusable blocks in a plugin.

## Deliverables to hand to the client/admin
- The plugin or theme folder (zip), with build output included.
- A one-page admin guide: how to add a section, edit, reorder, duplicate, change colors, reset to default pattern.
- Screenshots of the editor with List View open.
