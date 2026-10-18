---
name: qora-child-theme-blocks
description: Project playbook for the qora-link WooCommerce site (WoodMart + woodmart-child). Use whenever a new coded home page component/block is added or an existing one must become admin-editable from Pages > Home > Edit. Defines where files live, how to build, how to audit a block for editability, how to seed the home page and how to handle the header.
---

# Qora child theme: make every block admin-editable

## Project map (woodmart-child)
- `src/blocks/<name>/` source: `block.json`, `edit.js`, `index.js`, `render.php`, `style.scss`, optional `view.js`.
- `build/blocks/<name>/` compiled output (what WordPress loads). Never edit by hand; rebuild.
- `inc/blocks-register.php` registers every folder in `build/blocks/` automatically. A new folder with a valid `block.json` needs no extra registration code, but verify the loop still matches.
- `inc/setup.php` enqueues assets; `inc/helpers.php` has the SVG renderer and wishlist endpoints; `woocommerce/content-product-custom.php` is the product card.
- `assets/css/tokens.css` and `css/tokens.css` are both token files (duplicate). `assets/css/qora-blocks.css` is a consolidated stylesheet, while each block also emits `style-index.css`. Check for double loading and conflicting rules before adding more CSS.
- Existing blocks: category-grid, feature-banner, hero-slider, product-showcase, promo-duo, site-header, trust-bar.
- Build: `npm install && npm run build` (wp-scripts via `webpack.config.js`). Confirm the script names in `package.json`; use `npm run start` for watch mode.

## Definition of "editable"
A block is admin-editable only if ALL are true:
1. Every visible string, image, link, number and color comes from an **attribute** (no literal content left in `render.php`, no hard-coded URLs, `href="#"` or placeholder images).
2. `edit.js` provides a control for **each** attribute: `RichText` for visible text, `MediaUpload` for images, `InspectorControls` fields for links/toggles/counts, `supports` for color/spacing/typography.
3. Repeated items (slides, cards, badges, promos) can be **added, removed and reordered** by the admin.
4. The editor shows a faithful preview (static markup in `edit.js`, or `ServerSideRender` for WooCommerce-driven blocks).
5. The block is in the Home page content in the database (see Seeding), so Edit page shows it.
6. Defaults exist so a freshly inserted block looks finished.

## Workflow for each block (new or existing)
1. Run the audit: `node .claude/skills/qora-child-theme-blocks/scripts/audit-blocks.js <path-to>/woodmart-child` and read the report.
2. For every finding, move the hard-coded value into `block.json` (`attributes` with `default`), read it in `render.php` via `$attributes`, and add the control in `edit.js`.
3. Escape output in `render.php`: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` for RichText content; use `wp_get_attachment_image()` when you store `imageId`.
4. Keep `index.js` registering with `save: () => null` for dynamic blocks (or `InnerBlocks.Content` when using child blocks).
5. Rebuild, then test in the editor (insert, edit, save, reload, front end).
6. Re-run the audit until it reports no errors.

## Repeatable items: pick one
- **Small fixed sets** (trust-bar badges, promo-duo's two promos): array attribute plus add/remove/move buttons in `edit.js`.
```json
"items": { "type": "array", "default": [ { "icon": "truck", "title": "Free delivery", "text": "" } ] }
```
```js
const update = ( i, patch ) => setAttributes( { items: items.map( ( it, k ) => k === i ? { ...it, ...patch } : it ) } );
const add = () => setAttributes( { items: [ ...items, { icon: '', title: '', text: '' } ] } );
const remove = ( i ) => setAttributes( { items: items.filter( ( _, k ) => k !== i ) } );
const move = ( i, d ) => { const a = [ ...items ]; const j = i + d; if ( j < 0 || j >= a.length ) return; [ a[ i ], a[ j ] ] = [ a[ j ], a[ i ] ]; setAttributes( { items: a } ); };
```
- **Rich or variable items** (hero-slider slides, category-grid cards): a **child block** (`qora/hero-slide`, `"parent": ["qora/hero-slider"]`) with `InnerBlocks`; the parent `render.php` outputs `$content`. The admin gets native add/remove/drag-reorder. Check the real namespace in the existing `block.json` names and reuse it.

## WooCommerce-driven blocks (product-showcase, category-grid)
- Attributes: `title`, `source` (category | featured | sale | latest | ids), `categoryIds`, `count`, `columns`, `orderby`.
- Category picker in the sidebar: `useEntityRecords( 'taxonomy', 'product_cat', { per_page: -1 } )`.
- Preview: `ServerSideRender` from `@wordpress/server-side-render` so the editor shows real products.
- Query in `render.php` with `wc_get_products()` / `WP_Query`; reuse `woocommerce/content-product-custom.php` for cards. Cast and clamp attributes (`absint`, `max/min`).

## Icons and SVG
`inc/helpers.php` renders SVGs. Expose icons as a **select** attribute (a fixed list of icon keys) instead of free text or inline SVG markup, so admins cannot break the layout.

## site-header is special
`header.php` renders the header outside the page content, so it is **not** editable from Pages > Home > Edit. Make it admin-editable with a dedicated editable part:
1. Register a private custom post type (e.g. `qora_part`) with `show_in_rest => true`, `supports => ['title','editor']`, `public => false`, `show_ui => true`, `show_in_menu => true`, menu label "Header and footer".
2. Seed one post "Header" containing `<!-- wp:qora/site-header /-->` (with attributes).
3. In `header.php`, query that post (cache the ID in an option) and output `do_blocks( $post->post_content )`; fall back to the block's defaults when none exists.
4. Lock the post (`templateLock` via the CPT `template` + `template_lock => 'all'`) so the admin edits only the block's fields.
Alternative: keep WoodMart's Header Builder for the header and drop the custom block. Ask the owner which one before choosing.

## Seeding the home page (so Edit page shows all blocks)
Dynamic blocks serialize as self-closing comments, so the page markup is easy to generate:
```html
<!-- wp:qora/hero-slider {"slides":[{"title":"...","image":123}]} /-->
<!-- wp:qora/trust-bar /-->
<!-- wp:qora/category-grid {"columns":4} /-->
<!-- wp:qora/product-showcase {"title":"Best sellers","source":"featured","count":8} /-->
<!-- wp:qora/promo-duo /-->
<!-- wp:qora/feature-banner /-->
```
Replace `qora/...` with the exact `name` values from each `block.json`, and list the blocks in the order of the current coded home page. Save as `home-content.html`, then:
```bash
wp db export backup-before-home.sql
wp post list --post_type=page --fields=ID,post_title,post_name
wp post update <HOME_ID> --post_content="$(cat home-content.html)"
wp option update show_on_front page
wp option update page_on_front <HOME_ID>
wp cache flush
```
If WP-CLI is unavailable, give the admin the file to paste into the **Code editor**. If the home page currently renders from a PHP template (`front-page.php`, a shortcode, or hooks in `functions.php`), remove that hard-coded rendering so the page content is the single source of truth, and keep a copy of the old template in git.

## Patterns for faster composition
Register a pattern per home section and a "Qora home" pattern category (in `functions.php` or `inc/setup.php`) so a deleted section can be re-inserted from Patterns. Also register one full-page pattern containing all sections in order.

## Verification checklist (run on staging)
- Audit script reports 0 errors; `npm run build` succeeds; `build/blocks/*` is newer than `src/blocks/*`.
- Pages > Home > Edit shows every section as its own block in List View.
- Each block: edit all fields, save, reload, view the front end; no "Attempt block recovery".
- Add, remove, reorder and duplicate sections and repeated items.
- Header (if made editable) edits from the admin menu entry.
- Mobile/tablet/desktop match the design; `view.js` features (slider, header menu) work after edits.
- Caches cleared; WoodMart performance options do not strip block CSS/JS.
- Test as an Editor-role user.

## Guardrails
- Do not edit `wp-admin/`, `wp-includes/` or the WoodMart parent theme.
- Back up DB and files before changing page content; keep every change in git.
- Do not delete existing attribute names (breaks saved content); add new ones with defaults, or add `deprecated` when markup changes.
- Never commit `node_modules/`; commit `build/` only if production has no build step.
