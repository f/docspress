---
title: DocsPress WordPress theme
---

The companion theme turns the managed Page hierarchy into a Docusaurus-inspired reading experience while keeping WordPress navigation and customization native.

## Reading experience

- Sticky full-height documentation sidebar.
- Automatic Page-tree navigation or a selected WordPress menu.
- Header navigation menu and repository link.
- `⌘K` or `Ctrl+K` command search across the configured docs tree.
- Sidebar filtering, breadcrumbs, right-hand table of contents, and previous/next navigation.
- WordPress edit and exact GitHub Markdown proposal actions.
- Responsive navigation, light/dark mode, copy buttons, and print styles.

## Install and configure

1. Copy `theme/` to `wp-content/themes/docspress`.
2. Activate **DocsPress**.
3. Open **Appearance → Customize → DocsPress Theme**.
4. Choose the synchronized root Page under **Navigation → Documentation root**.
5. Keep the Action input `create-h1: false` because the theme renders the Page title.

<!-- wp:docspress/callout {"tone":"note","title":"Theme and sync are independent","content":"<p>The Action can synchronize Pages without this theme. The theme can render hand-authored Page trees without the Action. Use both for the complete workflow.</p>","collapsible":false} /-->

## Customizer sections

| Section | Controls |
| --- | --- |
| Design presets | DocsPress, WordPress.org, WordPress.com, Jetpack, or individually customized tokens. |
| Navigation | Docs root, Page tree or menu, order, depth, root visibility, filter, version-selector visibility. |
| Header | Menu, brand suffix, color switcher/default mode, repository link, custom logo. |
| Command search | Labels, dimensions, radius, backdrop, count, paths, excerpts, keyboard legend. |
| Layout and reading tools | Article/sidebar/TOC widths, density, TOC, breadcrumbs, previous/next, excerpt, edit actions. |
| Light and dark colors | Independent accent, surface, text, muted, and border tokens. |
| Typography | Interface, reading, and heading stacks, reading size, heading weight. |
| Article labels and actions | Kicker, TOC heading, WordPress/GitHub action labels, source repository and ref. |
| Footer | Visibility, text, optional link, `{year}` and `{site_title}` placeholders. |

The default footer is `Documentation powered by WordPress and {site_title} · {year}`.

## Design presets

Preset code is organized under `theme/inc/design-presets/{preset}/`. Each preset owns its setting recipe and optional scoped stylesheet, so new presets do not require a central registry edit.

The blocks plugin inherits the same preset, typography, borders, radii, and light/dark tokens. Authors choose semantics rather than arbitrary colors.
