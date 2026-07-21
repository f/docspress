# DocsPress Blocks

Documentation-focused Gutenberg blocks for the DocsPress theme. The plugin has no build step and uses WordPress's bundled block-editor packages.

## Blocks

- **Colorful Code** — filename chrome, language-aware token colors, line numbers, highlighted ranges, captions, and copy-to-clipboard.
- **Code Tabs** — up to eight keyboard-accessible examples with independent labels, languages, filenames, and code.
- **Callout** — note, tip, warning, danger, and success tones, with an optional collapsible presentation.

The inserter also includes **Documentation page starter** and **API request example** patterns under the **DocsPress** category.

Block colors are intentionally not author-configurable. Code, tabs, and callouts inherit the active DocsPress design preset, its light or dark tokens, typography, radius, borders, and article width. Semantic callout tones are the only color choice exposed in the editor.

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
│   └── callout/
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

The theme's Playground seed at [`../../theme/playground/setup.php`](../../theme/playground/setup.php) creates every example Page as serialized Gutenberg block HTML and uses all three blocks.

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
