---
name: gutenberg-block-qa-security-handoff
description: Quality, security, performance and admin-handoff checklist for custom Gutenberg blocks and patterns. Use before delivering editable home page blocks to a client or going live.
---

# QA, security and handoff for Gutenberg blocks

## Security
- Escape every output in PHP/render.php: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` (for rich text), `wp_kses` with an allowlist for custom HTML.
- Never trust attributes: they come from post content and can be edited. Validate/cast types in `render.php` (`absint`, `sanitize_text_field`).
- Don't expose a raw HTML field unless required; if required, gate it behind `unfiltered_html` capability.
- REST/AJAX endpoints used by blocks: use `permission_callback`, nonces, and capability checks (`current_user_can`).
- External scripts: avoid third-party CDNs; self-host; respect privacy (GDPR/Loi 09-08 in Morocco) for embeds.
- Keep dependencies updated; run `npm audit` before shipping.

## Performance
- Load assets only when the block is present (block.json `style`, `viewScript` are enqueued per block automatically).
- Images: store ID, output with `wp_get_attachment_image()` in dynamic blocks (srcset, sizes, lazy), set width/height to avoid layout shift. The hero (LCP) image should not be lazy-loaded: use `fetchpriority="high"`, `loading="eager"`.
- Avoid large JS bundles in the front end; prefer CSS-only effects; use the Interactivity API for behavior.
- Fonts: self-host, `font-display: swap`, subset.
- Test with Lighthouse/PageSpeed on mobile.

## Accessibility
- One `h1` on the page; headings in order; heading level should be configurable if the block can appear anywhere.
- Meaningful `alt` (empty alt for decorative); links with discernible text; buttons vs links used correctly.
- Color contrast 4.5:1; visible focus; reduced-motion respected (`prefers-reduced-motion`).
- Sliders/tabs/accordions: keyboard operable, ARIA roles/states correct.

## Internationalization and RTL
- Wrap strings with `__()`/`_x()`; text domain matches block.json.
- If the site is Arabic/French: test RTL (`dir="rtl"`), use logical CSS properties (`margin-inline-start`), and test long French/Arabic strings.
- Register JS translations: `wp_set_script_translations()` (or `wp-scripts` + `block.json` textdomain).

## Compatibility
- Test on current WordPress and the previous major version; PHP 8.1+.
- Test with the active theme and no other theme (default Twenty Twenty-Five) to separate block bugs from theme CSS conflicts.
- Test with caching/optimization plugins (minify, defer JS) and with WooCommerce if present.
- Gutenberg plugin vs core: verify against core only for production.

## Editor quality
- No block validation errors on reload; no console warnings.
- Undo/redo works; copy/paste between pages works; duplicate works.
- Inserter shows category, icon, preview (`example`).
- List View labels are meaningful (use `metadata.name` or `__experimentalLabel`).
- Roles: Administrator, Editor, Author behave as intended.

## Automated checks
- Lint: `npx wp-scripts lint-js`, `lint-style`.
- Unit/e2e: `wp-scripts test-unit-js`, Playwright with `@wordpress/e2e-test-utils-playwright`.
- PHP: `phpcs --standard=WordPress`.
- Build in CI; commit `build/` for plugins distributed as zip, or build in the release step.

## Release checklist
1. Version bump in plugin header and `block.json` `version`.
2. `npm ci && npm run build`.
3. Zip without `node_modules`, `src` optional (keep for maintainability), include `readme`.
4. Staging test on a copy of production data.
5. Backup (files + DB) before deploy.
6. Deploy, clear caches (plugin cache, CDN, browser), re-test front page.
7. Rollback plan: keep previous zip; synced patterns and content stay in the database.

## Admin handoff (non-technical)
Provide a short guide (1 to 2 pages, French if the client is French-speaking):
1. Open Pages > Home > Edit.
2. Edit text directly; click images to replace; use the right sidebar for links and options.
3. Add a section: "+" > Patterns > Home sections.
4. Reorder: List View (top bar icon) > drag; or use the up/down arrows.
5. Duplicate / delete: block toolbar "..." menu.
6. Change colors/fonts: Styles panel (block theme) or block color settings.
7. Undo mistakes: Ctrl+Z; restore older versions: Revisions in the sidebar.
8. Who to contact for issues; what not to touch (locked sections, plugin deactivation).
Include screenshots and a 2-minute screen recording if possible.

## Maintenance notes to leave in the repo
- Block list with purpose and attributes.
- How to add a new variant/pattern.
- Known limitations and deprecated markup versions.
- Compatibility matrix (WP/PHP/theme versions tested).
