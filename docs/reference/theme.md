---
title: DocsPress WordPress theme
---

The companion theme turns the managed Page hierarchy into a Docusaurus-inspired reading experience while keeping WordPress navigation and customization native.

## Reading experience

- Sticky full-height documentation sidebar with independently collapsible branches.
- Automatic Page-tree navigation or a selected WordPress menu.
- Header navigation menu and repository link.
- `⌘K` or `Ctrl+K` command search across the configured docs tree.
- Sidebar filtering, breadcrumbs, right-hand table of contents, and previous/next navigation.
- WordPress edit and exact GitHub Markdown proposal actions.
- A block-built custom site homepage and dedicated post, archive, search, and singular templates.
- Optional native WordPress discussions with threaded replies and accessible forms.
- Responsive navigation, light/dark mode, copy buttons, and print styles.

## Install and configure

1. Copy `theme/` to `wp-content/themes/docspress` and `plugins/docspress-blocks/` to `wp-content/plugins/docspress-blocks`.
2. Activate **DocsPress Blocks**, then activate **DocsPress**.
3. Open **Appearance → Customize → DocsPress Theme**.
4. Choose the synchronized root Page under **Navigation → Documentation root**.
5. Add **DocsPress: Hero** and optional **DocsPress: Audience Paths** blocks to the front Page selected in **Settings → Reading**.
6. Keep the Action input `create-h1: false` because the documentation template renders the Page title.

<!-- wp:docspress/callout {"tone":"note","title":"Theme and sync are independent","content":"<p>The Action can synchronize Pages without this theme. The theme can render hand-authored Page trees without the Action. Use both for the complete workflow.</p>","collapsible":false} /-->

## Customizer sections

| Section | Controls |
| --- | --- |
| Design presets | DocsPress, WordPress.org, WordPress.com, Jetpack, or individually customized tokens. |
| Navigation | Docs root, Page tree or menu, order, depth, root visibility, filter, version-selector visibility. |
| Homepage | Landing or documentation layout and the optional recent-post section. Hero content and audience routing live in the front Page’s DocsPress blocks. |
| Header | Menu, brand suffix, color switcher/default mode, repository link, custom logo. |
| Command search | Labels, dimensions, radius, backdrop, count, paths, excerpts, keyboard legend. |
| Layout and reading tools | Article/sidebar/TOC widths, density, TOC, breadcrumbs, previous/next, excerpt, edit actions. |
| Posts and archives | Metadata, date, author, featured image, categories, and tags. |
| Discussion | Page/post visibility, headings, count, avatars, dates, and closed message. |
| Light and dark colors | Independent accent, surface, text, muted, and border tokens. |
| Typography | Interface, reading, and heading stacks, reading size, heading weight. |
| Article labels and actions | Kicker, TOC heading, WordPress/GitHub action labels, source repository and ref. |
| Footer | Visibility, text, optional link, `{year}` and `{site_title}` placeholders. |

The default footer is `Documentation powered by WordPress and {site_title} · {year}`. The theme also registers native footer navigation and footer widget locations.

## Homepage, posts, and discussion

Choose the front Page and posts page in **Settings → Reading**. Add **DocsPress: Hero** to the front Page and customize its text, actions, image, layout, decorations, and colors in the block editor. Add **DocsPress: Audience Paths** when readers with different starting states should enter distinct Page roots. The DocsPress **Homepage** theme section can render that Page’s Gutenberg blocks as a site landing page with optional recent posts, or preserve the standard three-column documentation layout.

Posts, archives, categories, tags, authors, search results, feeds, featured images, and previous/next post navigation use native WordPress data and dedicated templates. Their visible metadata is configurable under **Posts & archives**.

Discussions are optional. The theme can independently expose them on Pages and posts, including existing replies after a thread closes. WordPress **Settings → Discussion** remains responsible for default status, registration, moderation, threading, paging, ordering, notifications, avatars, and spam handling; the per-post Discussion panel decides whether one specific item accepts replies.

Try these surfaces in the [browser-ready DocsPress Playground](https://playground.wordpress.net/?blueprint-url=https%3A%2F%2Fraw.githubusercontent.com%2FAutomattic%2Fdocspress%2Fmain%2Ftheme%2Fblueprint-browser.json&page-title=DocsPress%20Theme%20Playground). It opens the logged-in Customizer with seeded homepage, post, and threaded-comment examples.

## LLM-friendly endpoints

The theme generates `/llms.txt` with the site title, description, and absolute links to source-backed documentation Pages. Each normal Page route also has a clean Markdown representation by replacing its trailing slash with `.md`; for example, `/docs/guides/continuous-sync/` becomes `/docs/guides/continuous-sync.md`.

Markdown responses preserve the exact synchronized source, including frontmatter, and use the `text/markdown` content type. Only published, file-backed DocsPress Pages are exposed. Generated placeholders and hand-authored WordPress Pages return `404`. Pages synchronized before this feature become available after the next DocsPress run refreshes their management metadata.

See [Make documentation AI-friendly](../guides/ai-friendly-documentation.md) for the discovery workflow, response examples, verification commands, and publishing boundaries.

## Sidebar frontmatter

Markdown-backed Pages can set `sidebar_position` to a signed integer and `sidebar_collapsed` to a boolean. Position maps to native WordPress Page order and applies to the automatic Page tree when **Page order, then title** is selected. Collapse defaults apply to both automatic navigation and Page-backed items in a custom sidebar menu; custom menu ordering remains controlled in WordPress.

Parent Page links and disclosure buttons are separate controls. Inactive branches start collapsed, `sidebar_collapsed: false` keeps a branch open, and the current Page's ancestor path always opens. Filtering temporarily expands the tree to reveal matches, then restores the prior state. Without JavaScript, all links remain visible.

For a screenshot-led walkthrough of the native controls and live preview, see [Customize the theme in WordPress](../guides/customize-theme.md).

## Design presets

Preset code is organized under `theme/inc/design-presets/{preset}/`. Each preset owns its setting recipe and optional scoped stylesheet, so new presets do not require a central registry edit.

The blocks plugin inherits the same preset, typography, borders, radii, and light/dark tokens. Authors choose semantics rather than arbitrary colors.
