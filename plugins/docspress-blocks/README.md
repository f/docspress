# DocsPress Blocks

Documentation-focused Gutenberg blocks for the DocsPress theme. The plugin has no build step and uses WordPress's bundled block-editor packages.

## Blocks

- **Hero** — a responsive, theme-native homepage introduction with inline editing for every visible text label, configurable actions, transparent artwork, split-panel and editorial spotlight compositions, and opt-in decorative or color overrides.
- **Audience Paths** — one to six starting-point cards that route to independent documentation roots, with spacious and compact layouts, inline copy, destination URLs, symbols, accents, new-tab behavior, responsive columns, and optional presentation overrides.
- **Colorful Code** — filename chrome, language-aware token colors, line numbers, highlighted ranges, captions, and copy-to-clipboard.
- **Code Tabs** — up to eight keyboard-accessible examples with independent labels, languages, filenames, and code.
- **Callout** — note, tip, warning, danger, and success tones, with an optional collapsible presentation.
- **API Request / Response** — one structured HTTP exchange with method, endpoint, bold/light header pairs, independently selectable JSON or raw request and response bodies, response status, and a copyable URL.
- **Terminal Session** — a copyable command separated from its read-only output, with editable prompt, shell, and title labels.
- **Result** — a compact success, neutral, warning, or error outcome for builds, checks, and verification steps.
- **File Tree** — an indentation-aware repository view with accessible file and folder entries.
- **Prompt** — a first-class, copyable AI prompt with model, mode, optional Thinking state, highlighted `$skill-name` references, classified context chips, and an editable caption.

The inserter also includes **Homepage hero**, **Documentation starting paths**, **Documentation page starter**, **API request example**, and **AI prompt example** patterns under the **DocsPress** category.

Every block inherits the active DocsPress design preset in both Gutenberg and the published site, including its light or dark tokens, typography, exact radius, borders, and article width. WordPress.org therefore keeps crisp 2px corners, while WordPress.com and Jetpack use their 4px recipes; block styles do not impose a larger minimum radius or a separate card shadow. Hero and Audience Paths default to those theme tokens with clean surfaces and modest type scales; the Hero's editorial spotlight, inverse styles, decorations, and custom colors are explicit opt-ins.

## Folder structure

Every block owns its registration, renderer, editor UI, front-end styles, and editor-only styles. To add or maintain a block, work inside its folder instead of editing a plugin-wide bundle:

```text
docspress-blocks/
├── blocks/
│   ├── hero/
│   ├── audience-paths/
│   ├── colorful-code/
│   │   ├── block.php
│   │   ├── editor.js
│   │   ├── style.css
│   │   └── editor.css
│   ├── code-tabs/
│   ├── callout/
│   ├── api-request/
│   ├── terminal-session/
│   ├── result/
│   ├── file-tree/
│   └── prompt/
├── assets/
│   ├── editor-shared.js
│   ├── code.css
│   ├── code-editor.css
│   └── view.js
├── includes/
│   ├── code-surface.php
│   └── patterns.php
└── docspress-blocks.php
```

`assets/` contains only behavior and presentation genuinely shared by multiple blocks. `includes/code-surface.php` is the common server renderer for code surfaces, while `includes/patterns.php` keeps inserter patterns separate from block registration. The root plugin file is only the bootstrap and shared-asset registry.

## Gutenberg serialization

The blocks are dynamic. WordPress stores concise canonical block comments and the plugin renders accessible markup on the front end. A homepage hero can be serialized with every presentation choice kept in block attributes:

```html
<!-- wp:docspress/hero {"title":"Docs that stay connected to your GitHub repo","primaryLabel":"Choose your path","primaryUrl":"#choose-your-path","secondaryLabel":"Latest updates","secondaryUrl":"/#latest-updates","mediaUrl":"https://example.com/hero.png","mediaAlt":"Two project mascots celebrating together.","visualLabel":"DOCS","layout":"editorial","mediaPosition":"right","height":"standard","tone":"midnight","showGrid":true,"showOrbit":false} /-->
```

Starting paths keep each reader’s destination explicit:

```html
<!-- wp:docspress/audience-paths {"anchor":"choose-your-path","align":"wide","compact":false,"paths":[{"title":"I already have Markdown docs","description":"Connect an existing docs folder to WordPress and begin with a safe draft sync.","url":"/docs/publish-existing-docs/","cta":"Publish existing docs","icon":"MD","accent":"blue","newTab":false},{"title":"I need to create docs","description":"Generate source-grounded documentation with AI, review it, then publish it.","url":"/docs/create-docs-with-ai/","cta":"Create docs with AI","icon":"AI","accent":"gold","newTab":false}]} /-->
```

Set `"compact":true` for task routers inside documentation articles. Compact paths keep the same content, responsive columns, and accessible whole-card links while reducing the panel spacing, card height, and type scale.

A colorful workflow example looks like this:

```html
<!-- wp:docspress/colorful-code {"language":"yaml","filename":".github/workflows/docs.yml","highlightedLines":"5-6","code":"name: Publish docs\nsteps:\n  - uses: actions/checkout@v4\n  - uses: Automattic/docspress@main"} /-->
```

Code tabs use one `tabs` attribute:

```html
<!-- wp:docspress/code-tabs {"tabs":[{"label":"npm","language":"bash","filename":"Terminal","code":"npx docspress token --site example.com"},{"label":"GitHub CLI","language":"bash","filename":"Terminal","code":"gh secret set WP_ACCESS_TOKEN"}]} /-->
```

Callouts can stay open or become collapsible:

```html
<!-- wp:docspress/callout {"tone":"warning","title":"Protect credentials","content":"<p>Never place access tokens in browser-side examples.</p>","collapsible":false} /-->
```

API exchanges keep their request and response together:

```html
<!-- wp:docspress/api-request {"method":"POST","endpoint":"/wp-json/wp/v2/pages","headers":"Content-Type: application/json","requestBody":"{\n  \"status\": \"draft\"\n}","requestBodyFormat":"json","responseStatus":"201 Created","responseBody":"{\n  \"id\": 42\n}","responseBodyFormat":"json"} /-->
```

Terminal sessions distinguish commands from their output, while Result summarizes the outcome:

```html
<!-- wp:docspress/terminal-session {"title":"Publish a preview","shell":"bash","prompt":"$","command":"npx docspress publish ./docs --status=draft","output":"✓ Created 12 draft pages"} /-->

<!-- wp:docspress/result {"status":"success","title":"Preview published","content":"<p>The page tree is ready to review.</p>","meta":"12 pages · 1.8s"} /-->
```

File trees use two spaces per nesting level and a trailing slash for folders:

```html
<!-- wp:docspress/file-tree {"root":"repository/","tree":"docs/\n  introduction.md\n  api/\n    endpoints.md","caption":"Documentation source tree."} /-->
```

Prompts remain readable, crawlable HTML instead of screenshots or iframes:

```html
<!-- wp:docspress/prompt {"prompt":"Use $docspress-install to review this synchronization logic and propose the smallest safe patch.","model":"GPT-5","mode":"code","thinking":true,"context":"$docspress-install, @repository, src/sync.js, test/sync.test.js","caption":"Synchronization review prompt"} /-->
```

The theme's Playground seed at [`../../theme/playground/setup.php`](../../theme/playground/setup.php) creates every example Page as serialized Gutenberg block HTML. The Home page uses Hero and Audience Paths with working publish-existing and create-with-AI roots, while the Kitchen Sink covers every semantic state and meaningful configuration combination across all eight documentation blocks. Its live component table lists every plugin installed by the blueprint.

## Run with the theme

From the repository root:

```bash
npx @wp-playground/cli@latest start \
  --path=theme \
  --mount="$(pwd)/plugins/docspress-blocks:/wordpress/wp-content/plugins/docspress-blocks" \
  --blueprint=theme/blueprint.json \
  --port=9400
```

The blueprint activates this mounted plugin before it seeds the demo Pages.
