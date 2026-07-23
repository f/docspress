# DocsPress WordPress Theme

A small, Docusaurus-inspired documentation theme for [Docspress](https://github.com/Automattic/docspress). It turns an ordinary hierarchy of WordPress Pages into a focused documentation experience while keeping WordPress—and the content that Docspress synchronizes—fully native.

![DocsPress theme showing the Kitchen Sink documentation page](screenshot.png)

[Launch the complete theme demo in WordPress Playground](https://playground.wordpress.net/?blueprint-url=https%3A%2F%2Fraw.githubusercontent.com%2FAutomattic%2Fdocspress%2Fmain%2Ftheme%2Fblueprint-browser.json&page-title=DocsPress%20Theme%20Playground). It creates a temporary site and logs you into the admin. Edit the Home page to customize its Hero and Audience Paths blocks, or open the DocsPress Customizer for navigation, discussion, typography, color, and layout settings with a live preview.

## What it includes

- A sticky, hierarchy-aware documentation sidebar built from published Pages.
- A full-viewport Docusaurus-style shell with a flush 300px documentation rail and compact 8px menu gutters.
- A Docusaurus-style command search with `⌘K`/`Ctrl+K`, `/`, ranked full-content results, highlighted matches, and keyboard navigation.
- Instant sidebar-only Page filtering for quickly narrowing the visible navigation tree.
- An automatic “On this page” table of contents from `h2` and `h3` headings.
- Previous and next links that follow the sidebar order.
- A Gutenberg-built site homepage with configurable DocsPress Hero and audience-routing blocks, plus an optional recent-posts grid and native posts, archives, categories, tags, feeds, featured images, and post navigation.
- Optional WordPress discussions on Pages and posts, including threading, avatars, moderation states, reply links, paging, and comment forms.
- A generated `/llms.txt` index and exact source Markdown responses for managed Pages.
- A two-button article action bar for editing in WordPress or proposing changes to the exact source Markdown file on GitHub.
- Responsive mobile navigation, dark mode, code-copy buttons, and print styles.
- Optional `docspress_version` taxonomy selector when that taxonomy is registered on the site.
- A dedicated **DocsPress Theme** Customizer panel with homepage, discussion, post, preset, navigation, search, layout, color, typography, header, article, and footer controls.
- Automatic Page-tree navigation or fully hand-built WordPress menus for the header, documentation sidebar, and footer.
- A transparent Octocat/Wapuu hybrid default header mark, while preserving WordPress's standard custom-logo override.
- The companion DocsPress Blocks plugin with homepage Hero and Audience Paths, code, prompt, callout, API exchange, terminal session, result, and file-tree blocks plus starter patterns.
- Jetpack installed and activated by the Playground blueprint in local Offline Mode.
- A WordPress Playground blueprint with realistic nested demo Pages and a generated Kitchen Sink stored as Gutenberg block HTML.

The layout deliberately follows the useful parts of a classic Docusaurus docs page—header, left navigation, central article, right-hand table of contents—without copying Docusaurus code or branding.

## Run it in WordPress Playground

From the Docspress repository root, run:

```bash
npx @wp-playground/cli@latest start \
  --path=theme \
  --mount="$(pwd)/plugins/docspress-blocks:/wordpress/wp-content/plugins/docspress-blocks" \
  --blueprint=theme/blueprint.json \
  --port=9400
```

Playground mounts and activates the theme and DocsPress Blocks, installs Jetpack, creates editable navigation menus, a homepage, a posts page, sample discussions, and the source-backed documentation tree, logs you into WordPress, and opens the site homepage at:

```text
http://127.0.0.1:9400/
```

The Playground content is generated from the repository's `docs/` tree with `npm run playground:docs`, so local preview and production synchronization use the same converted Gutenberg content. The Home page contains Hero and Audience Paths blocks linked to the source-backed `/docs/publish-existing-docs/` and `/docs/create-docs-with-ai/` Page roots. The Kitchen Sink remains available at `http://127.0.0.1:9400/docs/reference/kitchen-sink/`; it appends a live component inventory and exercises all eight documentation blocks, every semantic state, and meaningful configuration combinations.

To rebuild the persisted Playground site from scratch, add `--reset`. To keep it from opening a browser automatically, add `--skip-browser`.

## Install on WordPress

1. Copy this directory to `wp-content/themes/docspress`.
2. Copy `plugins/docspress-blocks/` to `wp-content/plugins/docspress-blocks/`.
3. Activate **DocsPress Blocks**, then activate **DocsPress**.
4. Create a root Page such as **Docs** and arrange the documentation beneath it with parent/child Pages.
5. Choose a front Page under **Settings → Reading**, then add **DocsPress: Hero** and optional **DocsPress: Audience Paths** blocks in its editor.
6. Open **Appearance → Customize → DocsPress Theme → Navigation** and select the docs root Page.
7. Choose automatic Page navigation or select a WordPress menu for the sidebar and header.

If no documentation root is configured, the theme uses the current Page’s top-most ancestor. On non-Page views, it lists all published Pages.

## Markdown and llms.txt endpoints

The theme exposes a standard `llms.txt` index at `/llms.txt`. It contains the site title and description followed by absolute links to every source-backed Page in the configured documentation tree.

Each linked Page is available as its exact synchronized Markdown source by replacing the trailing slash with `.md`:

```text
/docs/publish-existing-docs/  → /docs/publish-existing-docs.md
```

The response includes the original frontmatter and uses `text/markdown; charset=utf-8`. Placeholder sections, hand-authored WordPress Pages, drafts, and invalid paths return `404` because they do not have source-owned Markdown. Existing managed Pages gain this metadata on their next DocsPress synchronization.

## Theme customization

Open **Appearance → Customize → DocsPress Theme**. Settings are grouped by job instead of presented as one long form.

### Design presets

The preset selector applies a complete, editable recipe. Selecting a preset updates the individual color, typography, and corner controls; changing any of those values afterward changes the selector to **Custom** without discarding the rest of the recipe.

- **DocsPress** follows the project wordmark: its sampled `#005CB3` blue, `#FEC408` Wapuu yellow, `#FE8301` orange, deep `#232323` ink, Nunito Sans typography, and friendly pill-shaped brand details.
- **WordPress.org** follows the current [WordPress.org](https://wordpress.org/) visual system: WordPress blue, neutral gray surfaces, Inter interface and body copy, EB Garamond headings, lighter heading weights, and crisp corners.
- **WordPress.com** follows the current [WordPress.com](https://wordpress.com/) marketing system: WordPress blue, Studio neutral surfaces, Inter interface and body copy, Recoleta display headings, and compact corners.
- **Jetpack** follows the current [Jetpack.com](https://jetpack.com/) visual system: Jetpack green, bright neutral surfaces, Inter interface and body copy, Recoleta display headings, and compact corners.

The DocsPress preset uses bundled Nunito Sans with a Trebuchet fallback so its friendly rounded typography is consistent from the first render. Inter and EB Garamond are also bundled as variable WOFF2 fonts so the reference presets render consistently. The WordPress.com and Jetpack presets load Recoleta from WordPress.com's production font endpoint, with EB Garamond and Georgia as fallbacks.

Preset code lives under `inc/design-presets/`, with one automatically discovered folder per preset. Each folder contains its setting recipe and can include scoped CSS refinements. To add another preset, create one new folder; the loader adds it to the Customizer, live recipe controller, body-class validation, and stylesheet queue without a central registry edit. See [`inc/design-presets/README.md`](inc/design-presets/README.md) for the manifest contract.

### Navigation

| Setting | What it controls |
| --- | --- |
| Documentation root | Scopes automatic navigation to one Page tree. |
| Sidebar source | Switches between automatic Page hierarchy and a WordPress navigation menu. |
| Sidebar menu | Selects a specific menu or uses the **Documentation sidebar** menu location. |
| Automatic Page order | Orders Pages by Page order, title, newest, or oldest. |
| Show documentation root | Includes or removes the root Page from the sidebar. |
| Maximum navigation depth | Limits nested levels from 1–6, or shows every level. |
| Sidebar heading | Changes the navigation eyebrow text. |
| Sidebar filter | Shows or hides filtering and changes its placeholder. |
| Version selector | Shows `docspress_version` terms when that taxonomy is available. |

Every sidebar item with children receives a separate disclosure button, so its Page title remains a normal link. Inactive branches start collapsed; the branch containing the current Page always opens. Managed Pages can override the initial state with `sidebar_collapsed: true` or `sidebar_collapsed: false` in Markdown frontmatter. The same collapse behavior applies to Page-backed items in a hand-built sidebar menu, while custom links use the inactive-branch default.

Use the default **Page order, then title** setting to honor frontmatter `sidebar_position` through WordPress's native Page order. Custom menus always retain their explicit menu-item order. Sidebar filtering temporarily reveals matching branches and restores their previous state when cleared.

### Command search

The header search opens a command palette with `⌘K` on macOS, `Ctrl+K` elsewhere, `/`, or the visible header control. It searches the published Pages in the configured documentation tree or sidebar menu, ranks title and hierarchy matches ahead of excerpt and body matches, and supports arrow-key selection, Enter, Escape, pointer selection, and outside-click dismissal. Submitting a query without a matched suggestion falls through to the normal WordPress Page search.

The dedicated **Command search** section can enable or disable the feature; change the header label, field placeholder, suggested-results label, no-results message, and result count; resize the popup; override or inherit its corner radius; tune the backdrop opacity and blur; and independently show or hide result paths, excerpts, and the keyboard legend. Dimension and appearance controls preview live.

### Header

- Select a header menu directly or use the **Header navigation** menu location.
- Show, hide, and rename the brand suffix.
- Show or hide the color-mode switcher and repository link. When the switcher is hidden, choose Light or Dark as the enforced default for every visitor.
- Change the repository URL.
- Set a custom logo under the standard **Site Identity** section.

### Homepage

WordPress still chooses the front Page under **Settings → Reading**. Edit that Page and add **DocsPress: Hero** to control its copy, actions, transparent artwork, labels, and restrained layout. Add **DocsPress: Audience Paths** to route one to six reader personas into dedicated Page roots. Both blocks inherit the active theme by default; decorative treatments, inverse styles, and custom colors remain optional overrides.

The **Homepage** theme section switches the front Page between the documentation template and a site landing layout, then controls the optional recent-post grid. The landing layout renders the Page’s Gutenberg blocks directly; it does not generate a separate static hero.

Blank action URLs use useful native fallbacks: the primary action opens the configured documentation root, while the secondary action opens the WordPress posts page or recent-post section.

### Layout & reading tools

- Adjust article, sidebar, and table-of-contents widths with live range controls.
- Choose compact, comfortable, or roomy reading density.
- Independently toggle the table of contents, breadcrumbs, previous/next cards, WordPress edit button, and Page excerpt.

### Posts & archives

Individual posts, the posts page, archives, and search results use dedicated templates rather than the documentation Page shell. Controls toggle post dates, authors, featured images, categories, tags, and the shared metadata row. WordPress sticky posts, pagination, archive descriptions, password protection, wide alignment, and post navigation remain native.

### Discussion

The theme can independently show discussions on Pages and posts, then customize the discussion and reply headings, count, avatar visibility and size, dates, and closed message. A discussion appears when comments are open or existing replies need to remain visible.

WordPress core remains authoritative for policy and workflow. Use **Settings → Discussion** for default status, registration, moderation, threading, paging, ordering, notifications, avatars, and spam controls, and use each post’s **Discussion** panel to open or close that specific conversation.

### Light & dark colors

The light and dark palettes are independent. Each mode exposes accent, link accent, selected surface, article background, sidebar background, heading text, body text, muted text, borders, and strong borders. Changes preview live.

### Typography

Choose independent interface, reading, and heading font stacks, including bundled Inter and EB Garamond plus the WordPress.com and Jetpack Recoleta display option, then adjust reading size and heading weight with live controls.

### Article labels and actions

Customize the article kicker, table-of-contents heading, and both article action buttons. The WordPress and GitHub actions can be shown independently, their labels can be changed, and the Markdown repository plus branch/ref can be configured separately from the header repository link.

The GitHub action reads the exact `source` path from the hidden Docspress sentinel and opens GitHub's editor for that file. Hand-authored Pages can provide the same path through `_docspress_source_path` post meta or the `docspress_markdown_source_path` filter. Pages without a validated `.md`, `.markdown`, or `.mdx` source path do not show a misleading GitHub action.

### Footer

The footer defaults to `Documentation powered by WordPress and {site_title} · {year}`. It can be hidden, given custom text, and paired with an optional link. Footer text supports `{year}` and `{site_title}` placeholders. A native **Footer navigation** location and **Footer widgets** area can add administrator-managed content above that line.

## Use it with Docspress

DocsPress is designed for the Page tree produced by the Docspress GitHub Action. A typical workflow uses:

```yaml
- uses: Automattic/docspress@main
  with:
    wordpress-site: example.com
    wordpress-access-token: ${{ secrets.WP_ACCESS_TOKEN }}
    docs-dir: docs
    root-slug: docs
    root-title: Docs
    status: publish
    create-h1: false
```

Use `create-h1: false`: the theme renders the WordPress Page title as the document’s single `h1`. The Markdown body should begin with `h2` sections, which also populate the right-hand table of contents.

Keep the action's `edit-link` input disabled when using this theme. The theme builds its own GitHub proposal button from the same sentinel source metadata, avoiding a duplicate link inside the article body.

The theme preserves the Page hierarchy generated from nested Markdown folders. Set the generated **Docs** Page as the documentation root in the Customizer to exclude unrelated site Pages from the sidebar.

### Documentation blocks

The companion plugin lives at [`../plugins/docspress-blocks/`](../plugins/docspress-blocks/). It adds Hero, Audience Paths, Colorful Code, Code Tabs, Callout, API Request / Response, Terminal Session, Result, File Tree, and Prompt blocks plus five starter patterns. The blocks automatically follow the active DocsPress, WordPress.org, WordPress.com, Jetpack, or custom preset in both light and dark mode. Documentation blocks inherit semantic colors; Hero and Audience Paths additionally expose intentional landing-page presentation controls. The Playground pages use their canonical serialized `<!-- wp:docspress/* -->` markup, so opening a demo Page in the block editor shows editable native blocks rather than a Classic block or raw HTML fallback. The Kitchen Sink Page is the single-page reference for every documentation-block option and semantic state.

### Versioned docs

Docspress can assign Pages to a `docspress_version` taxonomy. The theme does not register that taxonomy—it should be registered by the WordPress site or a plugin and exposed for Pages through REST. When it exists and has terms, the theme automatically adds a version selector above the sidebar navigation.

## Page ordering

Sidebar and previous/next order use the WordPress Page `menu_order`, then title. Set **Order** in the Page attributes panel to control the sequence. Parent Pages form navigation groups automatically.

When the sidebar uses a custom WordPress menu, previous/next navigation follows that menu’s Page-item order instead.

## Jetpack and performance

The Playground blueprint installs and activates the official `jetpack` plugin. It also enables Jetpack Offline Mode with `JETPACK_DEV_DEBUG` because CDN-backed features cannot connect from `127.0.0.1`.

On a public production site, connect Jetpack to WordPress.com and enable **Jetpack → Settings → Performance → Site Accelerator** to serve supported images plus WordPress, Jetpack, and WooCommerce assets from Jetpack’s CDN. Jetpack does not CDN-host custom theme files, so DocsPress also optimizes its own runtime:

- Page queries are reused within each request.
- Large Page trees are grouped and rendered in linear time instead of repeatedly rescanning the whole tree.
- Legacy emoji, RSD, Windows Live Writer, generator, and shortlink output are removed where the theme does not use them. REST, feeds, oEmbed discovery, and WordPress embeds remain available.
- Core block CSS is loaded per block instead of as the full combined library.
- The interaction script is deferred and has no framework dependency.
- Inter and EB Garamond are self-hosted; the WordPress.com and Jetpack Recoleta face loads on demand from the official WordPress.com font CDN.
- The configured article width is exposed through WordPress’s `$content_width`, allowing WordPress and Jetpack to request appropriately-sized images.

## Theme files

```text
.
├── assets/images/docspress-mark.svg # Default header mark
├── assets/fonts/            # Bundled Nunito Sans, Inter, and EB Garamond WOFF2 subsets
├── assets/js/docs.js       # Command search, navigation, dark mode, TOC, copy buttons
├── assets/js/customizer-controls.js # Applies discovered preset recipes
├── assets/js/customizer-preview.js
├── inc/design-presets/     # Auto-discovered preset manifests and scoped CSS
├── inc/customizer.php      # Theme panel, sanitization, live design tokens
├── inc/performance.php     # Conservative front-end performance hooks
├── inc/search.php          # Scoped documentation search-index builder
├── playground/setup.php    # Idempotent demo-content seeder
├── template-parts/search-dialog.php # Accessible command-search markup
├── 404.php
├── blueprint.json
├── blueprint-browser.json      # One-click remote Playground demo
├── comments.php
├── front-page.php              # Customizable site landing page
├── footer.php
├── functions.php           # Theme setup and documentation helpers
├── header.php
├── home.php                    # WordPress posts page
├── index.php
├── page.php                # Main three-column documentation template
├── single.php                  # Individual post and discussion
├── archive.php
├── search.php
├── sidebar-docs.php
├── style.css
└── theme.json              # Matching block editor design tokens
```

## Browser support and accessibility

The theme uses semantic landmarks, a skip link, visible keyboard focus, ARIA state for the mobile drawer, and reduced-motion support. JavaScript enhances the experience but every documentation link remains an ordinary server-rendered WordPress link.

## License

GPL-2.0-or-later.
