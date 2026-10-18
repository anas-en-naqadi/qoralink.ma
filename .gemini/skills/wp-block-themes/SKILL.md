---
name: wp-block-themes
description: "Use when developing WordPress block themes: theme.json (global settings/styles), templates and template parts, patterns, style variations, and Site Editor troubleshooting (style hierarchy, overrides, caching)."
compatibility: "Targets WordPress 6.5+ / 7.0+ (PHP 7.4+). Filesystem based agent with bash + node. Some workflows require WP CLI."
---

# WP Block Themes & Gutenberg Standards

## Core Principles
1. **Separation of Concerns**:
   - Template hierarchy: templates, template-parts, patterns, and dynamic block components.
   - Global Styles: Managed via design tokens and CSS variables consistent with theme settings.
2. **Block Patterns & Components**:
   - Reusable layout sections should be registered as block patterns or modular block components.
   - Each section component maintains its own React editor interface and dedicated CSS file.
3. **Child Theme Customization**:
   - Place all custom block assets, source code, build scripts, and PHP hooks inside the child theme directory (`wp-content/themes/woodmart-child/`).
   - Use child theme `functions.php` to register blocks, enqueue scripts, and configure theme hooks.
4. **Responsive Layouts**:
   - Ensure 100% fluid responsiveness across mobile (320px-480px), tablet (768px-1024px), and desktop (1280px+).
   - Use CSS clamp() for typography and spacing.
