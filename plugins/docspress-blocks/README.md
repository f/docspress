# DocsPress Blocks

Documentation-focused Gutenberg blocks for the DocsPress theme. The plugin has no build step and uses WordPress's bundled block-editor packages.

## Blocks

- **Colorful Code** — filename chrome, language-aware token colors, line numbers, highlighted ranges, captions, and copy-to-clipboard.
- **Code Tabs** — up to eight keyboard-accessible examples with independent labels, languages, filenames, and code.
- **Callout** — note, tip, warning, danger, and success tones, with an optional collapsible presentation.
- **API Request / Response** — one structured HTTP exchange with method, endpoint, bold/light header pairs, independently selectable JSON or raw request and response bodies, response status, and a copyable URL.
- **Terminal Session** — a copyable command separated from its read-only output, with editable prompt, shell, and title labels.
- **Result** — a compact success, neutral, warning, or error outcome for builds, checks, and verification steps.
- **File Tree** — an indentation-aware repository view with accessible file and folder entries.
- **Prompt** — a first-class, copyable AI prompt with model, mode, optional Thinking state, classified context chips, and an editable caption.

The inserter also includes **Documentation page starter**, **API request example**, and **AI prompt example** patterns under the **DocsPress** category.

Block colors are intentionally not author-configurable. Every block inherits the active DocsPress design preset, its light or dark tokens, typography, radius, borders, and article width. Only meaningful semantic states—such as callout tone, HTTP method, and result status—are exposed in the editor.

## Folder structure

Every block owns its registration, renderer, editor UI, front-end styles, and editor-only styles. To add or maintain a block, work inside its folder instead of editing a plugin-wide bundle:

```text
docspress-blocks/
├── blocks/
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

The blocks are dynamic. WordPress stores concise canonical block comments and the plugin renders accessible markup on the front end. A colorful workflow example looks like this:

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
<!-- wp:docspress/prompt {"prompt":"Review this synchronization logic and propose the smallest safe patch.","model":"GPT-5","mode":"code","thinking":true,"context":"@repository, src/sync.js, test/sync.test.js","caption":"Synchronization review prompt"} /-->
```

The theme's Playground seed at [`../../theme/playground/setup.php`](../../theme/playground/setup.php) creates every example Page as serialized Gutenberg block HTML and uses all eight blocks. Its Kitchen Sink Page covers every semantic state and meaningful configuration combination, while its live component table lists every plugin installed by the blueprint.

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
