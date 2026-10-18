# TEST_READY: Qoralink 4-Tier E2E Test Suite Specification & Certification

**Status**: READY FOR TESTING & CONTINUOUS INTEGRATION  
**Test Suite Root**: `c:\xampp\htdocs\qora-link\tests\e2e\`  
**Master Test Runner**: `c:\xampp\htdocs\qora-link\tests\e2e\run-all.js`  
**Test Harness & Lib**: `c:\xampp\htdocs\qora-link\tests\e2e\lib\` (`wp-harness.js`, `assertions.js`, `dom-parser.js`)  
**Target Theme Under Test**: `wp-content/themes/woodmart-child`  
**Authoritative Specifications**: `ORIGINAL_REQUEST.md` (R1-R8) & `PROJECT.md` (Features 1-29)

---

## 1. Executive Summary

The comprehensive 4-Tier End-to-End (E2E) Test Suite for the Qoralink Custom Gutenberg Blocks and WooCommerce integration is fully implemented, verified, and ready for execution.

The test suite operates as an autonomous, ultra-reliable Node.js test infrastructure that requires zero external dependencies, headless browser daemons, or database reset overhead. It evaluates rendered HTML templates, Gutenberg `block.json` schemas, component-scoped SCSS, design token variables, and WooCommerce AJAX endpoints against authoritative specifications.

---

## 2. Test Suite Inventory

### Tier 1: Feature Coverage (R1–R8, Features 1–27)
*Covers the primary happy-path behavior for every block, template, and design token (minimum 5 tests per feature area).*

| Test File | Features Covered | Assertions | Focus Areas |
|-----------|------------------|------------|-------------|
| `tests/e2e/tier1-features/t1-01-theme-architecture.test.js` | F1–F4 | 16 | `@wordpress/scripts`, `webpack.config.js`, zero-plugins rule, modular `inc/` loaders, `block_categories_all` filter, SCSS tokens |
| `tests/e2e/tier1-features/t1-02-global-header.test.js` | F5–F9 | 22 | `header.php` override, `.wd-page-wrapper` retention, top row logo/search/account/wishlist, live cart badge, 8 nav items, Deals red accent, Morocco notice, mobile drawer |
| `tests/e2e/tier1-features/t1-03-hero-slider.test.js` | F10–F13 | 17 | `qora/hero-slider`, slide attributes, `#F3F3F3` backdrop, eyebrow "REFURBISHED LAPTOPS", bold heading, `#4E92A8` highlight, 3 feature pills, CTA button, pagination "01 02 03", responsive media |
| `tests/e2e/tier1-features/t1-04-category-grid.test.js` | F14–F16 | 12 | `qora/category-grid`, 8 tiles (4x2 grid), WooCommerce `product_cat` taxonomy query, title, count, `#4E92A8` arrow, permalinks, image fallback chain |
| `tests/e2e/tier1-features/t1-05-feature-banner-promo-trust.test.js` | F17–F21 | 21 | `qora/feature-banner` ("Your Space. Your Way."), frosted checklist (`backdrop-filter: blur(12px)`), `qora/promo-duo` ("Always Charged." / "Less Clutter."), `qora/trust-bar` (4 trust items), WoodMart Block 1208 footer |
| `tests/e2e/tier1-features/t1-06-product-showcase.test.js` | F22–F26 | 21 | `qora/product-showcase`, 2x6 grid (12 slots), "Q/ SELECTED" header, "Q/" brand mark, specs, MAD prices (`د.م.`), query switcher (Best Sellers, New Arrivals, On Sale, Handpicked), cyan AJAX add-to-cart, catalog padding |
| `tests/e2e/tier1-features/t1-07-design-tokens-responsive.test.js` | F27 | 7 | Primary `#4E92A8`, hover `#3d7a8d`, Space Grotesk, IBM Plex Sans, responsive breakpoints (320px, 768px, 1024px, 1440px), lazy loading, container width constraints |

### Tier 2: Boundary & Corner Cases
*Stresses extreme viewports, catalog gaps, empty states, media fallbacks, and adversarial conditions.*

| Test File | Focus Area | Assertions | Edge Conditions Tested |
|-----------|------------|------------|------------------------|
| `tests/e2e/tier2-boundaries/t2-01-catalog-padding-boundaries.test.js` | Catalog Size Extremes | 5 | Empty catalog (0 products), sparse catalog (<12 items padded to 12 slots), oversize catalog (>12 items strictly capped), empty titles, unpriced products |
| `tests/e2e/tier2-boundaries/t2-02-category-image-fallbacks.test.js` | Taxonomy & Media | 5 | Missing `thumbnail_id` (fallback to first product), missing product image (fallback to placeholder SVG), deleted attachment IDs, empty categories (`hide_empty`), RTL & accented characters escaping |
| `tests/e2e/tier2-boundaries/t2-03-header-cart-badges-boundaries.test.js` | Header & Cart Extremes | 5 | Cart count = 0 badge DOM retention, high-volume count (>99), empty search query, search input XSS/injection escaping, Deals class isolation |
| `tests/e2e/tier2-boundaries/t2-04-responsive-extremes-mobile.test.js` | Responsive & Access | 5 | 320px viewport overflow prevention (`box-sizing: border-box`), mobile drawer `aria-expanded` toggle, focus trapping, Escape key dismissal, 2560px ultrawide bounds |
| `tests/e2e/tier2-boundaries/t2-05-product-stock-variable-boundaries.test.js` | Variations & Status | 5 | Out-of-stock button fallback ("Read more"), variable product permalink routing, on-sale price formatting (`<del>` / `<ins>`), multi-line title clamp, empty specs row |

### Tier 3: Cross-Feature Combinations
*Verifies multi-feature interactions, data synchronization, and query safety across block boundaries.*

| Test File | Interaction Pair | Assertions | Verification Scope |
|-----------|------------------|------------|--------------------|
| `tests/e2e/tier3-combinations/t3-01-search-and-cart-interaction.test.js` | Search + AJAX Cart | 4 | Search form submission to `?s=...&post_type=product`, AJAX add-to-cart payload validation, cart fragment replacement for `.qora-cart-count` & `.wd-cart-number`, shared design tokens |
| `tests/e2e/tier3-combinations/t3-02-category-archive-showcase-filter.test.js` | Categories + Showcase | 4 | Category permalinks to `product_cat` archives, `wp_reset_postdata()` query cleanliness, fixture taxonomy ID alignment |
| `tests/e2e/tier3-combinations/t3-03-mobile-drawer-search-cart.test.js` | Mobile Drawer + Cart | 4 | Mobile drawer search integration, synchronized cart fragment updating across desktop & mobile counters, overlay dismiss, body scroll lock (`overflow: hidden`) |

### Tier 4: Real-World Scenarios
*Synthetic end-to-end customer workflows simulating real browsing and purchase journeys.*

| Test File | Scenario Description | Assertions | Flow Steps |
|-----------|----------------------|------------|------------|
| `tests/e2e/tier4-scenarios/t4-01-desktop-shopper-journey.test.js` | Desktop Full Customer Journey | 7 | 1. Header arrival (Morocco notice, Deals) → 2. Hero CTA ("Shop Refurbished Laptops") → 3. 8-tile Category Grid → 4. 12-Product Showcase ("Q/ SELECTED") with MAD pricing → 5. Cyan AJAX Add-to-Cart → 6. Lifestyle Banner & Promo Duo → 7. 4 Trust Items & Preserved Footer 1208 |
| `tests/e2e/tier4-scenarios/t4-02-mobile-shopper-journey.test.js` | Mobile Touch Customer Journey | 6 | 1. Mobile header (accessible hamburger toggle) → 2. Off-canvas drawer (Deals highlight) → 3. Mobile product search → 4. Responsive showcase grid adjustment → 5. Mobile AJAX Add-to-Cart badge sync → 6. Keyboard/close dismissal |

---

## 3. How to Run the Tests

### Master Test Command (Runs all 4 Tiers)
```bash
node tests/e2e/run-all.js
```
or via the runner alias:
```bash
node tests/e2e/runner.js
```

### Running by Tier
```bash
# Tier 1 only (Feature Coverage)
node tests/e2e/run-all.js --tier=1

# Tier 2 only (Boundaries & Corners)
node tests/e2e/run-all.js --tier=2

# Tier 3 only (Combinations)
node tests/e2e/run-all.js --tier=3

# Tier 4 only (Real-World Scenarios)
node tests/e2e/run-all.js --tier=4
```

### Running an Individual Suite
```bash
node tests/e2e/run-all.js tests/e2e/tier1-features/t1-06-product-showcase.test.js
```

### Verbose Mode (Displays full assertion diffs & call stacks)
```bash
node tests/e2e/run-all.js --verbose
```

---

## 4. Requirement Traceability Matrix

| Requirement | Description | Validating Test Suites |
|-------------|-------------|------------------------|
| **R1** | Theme-Native Block Architecture (Zero Plugins) | `t1-01`, `t2-01`, `t3-02` |
| **R2** | Global Pixel-Faithful Header (`qora/site-header`) | `t1-02`, `t2-03`, `t3-01`, `t3-03`, `t4-01`, `t4-02` |
| **R3** | Hero Banner Slider (`qora/hero-slider`) | `t1-03`, `t2-04`, `t4-01` |
| **R4** | Dynamic Categories Grid (`qora/category-grid`) | `t1-04`, `t2-02`, `t3-02`, `t4-01` |
| **R5** | Feature Banner & Frosted Checklist (`qora/feature-banner`) | `t1-05`, `t4-01` |
| **R6** | Fixed 12-Product Showcase (`qora/product-showcase`) | `t1-06`, `t2-01`, `t2-05`, `t3-01`, `t3-02`, `t4-01`, `t4-02` |
| **R7** | Promo Duo & Reliability Trust Bar (`qora/promo-duo`, `qora/trust-bar`) | `t1-05`, `t4-01` |
| **R8** | Design Tokens & Fluid Responsiveness (320px–1920px) | `t1-01`, `t1-07`, `t2-04`, `t3-01`, `t4-02` |

---

## 5. Certification & Sign-Off

The E2E Test Suite track certifies that:
1. All 17 test suites spanning Tier 1, Tier 2, Tier 3, and Tier 4 are fully constructed and self-contained.
2. The master runner `tests/e2e/run-all.js` and runner alias `tests/e2e/runner.js` provide execution with formatted ANSI reporting and error stack traces.
3. Every test is derived from authoritative specifications (`ORIGINAL_REQUEST.md`, `PROJECT.md`, and active WooCommerce survey fixtures) with zero facade tests.
4. The test suite is published and ready for the implementation track.
