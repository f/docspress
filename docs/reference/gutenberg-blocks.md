---
title: DocsPress Gutenberg blocks
---

The DocsPress Blocks plugin registers two landing-page blocks and eight documentation blocks. Their saved content is a self-closing Gutenberg comment whose attributes are valid JSON.

## Hero

Use `docspress/hero` as the editable introduction on a WordPress front Page. Edit the eyebrow, title, description, and action labels directly in the canvas. The block sidebar also controls:

- Primary and secondary action labels, URLs, and new-tab behavior.
- A Media Library image or external image URL, alternative text, and image scale.
- Split-panel or editorial spotlight composition, image side and visual-column width, compact/standard/tall height, and left or centered text.
- A theme-native default plus optional midnight, paper, or brand-color overrides.
- Optional custom colors and opt-in grid or orbit decorations.
- Optional backdrop text behind the image in the editorial composition.

The editorial spotlight supports a dark midnight treatment and a warm paper treatment. Use paper when the hero should feel lighter than the surrounding documentation shell. With the Jetpack preset, paper uses the brand's clean sans-serif headline and green action on a flat, borderless canvas that follows the site's light or dark mode.

Empty action labels or URLs hide that action. Removing the image keeps the hero usable as a text-only block. The image renders without an added card or image background, preserving transparent artwork.

<!-- wp:docspress/hero {"eyebrow":"Documentation, publishing, and community","title":"Docs that stay connected to your GitHub repo","description":"Write beside your code. Publish a WordPress experience that guides every reader to the docs written for them.","primaryLabel":"Choose your path","primaryUrl":"#choose-your-path","secondaryLabel":"Latest updates","secondaryUrl":"/#latest-updates","mediaUrl":"https://raw.githubusercontent.com/Automattic/docspress/main/theme/assets/images/homepage-octocat-wapuu.webp","mediaAlt":"The GitHub Octocat and WordPress Wapuu celebrating their documentation workflow together.","visualLabel":"DOCS","layout":"editorial","mediaPosition":"right","mediaWidth":38,"imageScale":90,"height":"standard","tone":"midnight","textAlign":"left","showGrid":true,"showOrbit":false} /-->

## Audience Paths

Use `docspress/audience-paths` to route readers into independent documentation roots. The default paths distinguish a repository that already has Markdown docs from one that still needs documentation, but the block supports one to six fully editable starting points.

Edit the section introduction, every path name, summary, symbol, and call-to-action directly in the canvas. Each path’s sidebar panel controls its destination URL, accent, and new-tab behavior. The default presentation inherits the theme; layout controls provide one to three columns, left or centered text, optional path numbers, subtle/inverse/blueprint variants, and custom colors when a deliberate override is needed.

Use the spacious layout for a primary homepage choice. Enable **Compact layout** for task routers inside documentation articles; it preserves the same content and whole-card links while reducing panel spacing, card height, and type scale.

The published card is a native link, so navigation works without JavaScript and remains keyboard accessible. Point each card at a normal WordPress Page root such as `/docs/publish-existing-docs/` or `/docs/create-docs-with-ai/`; its child Pages then form that path’s branch in the DocsPress sidebar.

<!-- wp:docspress/audience-paths {"anchor":"choose-your-path","align":"wide","compact":false,"eyebrow":"Choose a starting point","title":"Where are your docs today?","description":"Follow the path that matches your repository.","paths":[{"title":"I already have Markdown docs","description":"Connect an existing docs folder to WordPress and begin with a safe draft sync.","url":"/docs/publish-existing-docs/","cta":"Publish existing docs","icon":"MD","accent":"blue","newTab":false},{"title":"I need to create docs","description":"Generate source-grounded documentation with AI, review it, then publish it.","url":"/docs/create-docs-with-ai/","cta":"Create docs with AI","icon":"AI","accent":"gold","newTab":false}],"columns":2,"tone":"theme","textAlign":"left","showNumbers":false} /-->

## Colorful Code

Use `docspress/colorful-code` for one copyable source example.

| Attribute | Value |
| --- | --- |
| `language` | `bash`, `css`, `html`, `javascript`, `json`, `jsx`, `markdown`, `php`, `plaintext`, `python`, `shell`, `sql`, `tsx`, `typescript`, or `yaml` |
| `filename` | Display label; falls back to language |
| `code` | Plain source text |
| `highlightedLines` | One-based lines and ranges, for example `2,4-6` |
| `showLineNumbers` | Boolean; defaults to true |
| `caption` | Optional formatted caption |

<!-- wp:docspress/colorful-code {"language":"javascript","filename":"publish.js","code":"const result = await publish();\nconsole.log(result);","highlightedLines":"1","showLineNumbers":true,"caption":"A single highlighted example."} /-->

## Code Tabs

Use `docspress/code-tabs` for two to eight equivalent examples. Each `tabs` item contains `label`, `language`, `filename`, and `code`. The block also accepts `showLineNumbers` and `caption`.

<!-- wp:docspress/code-tabs {"tabs":[{"label":"npm","language":"bash","filename":"Terminal","code":"npm install docspress"},{"label":"pnpm","language":"bash","filename":"Terminal","code":"pnpm add docspress"}],"showLineNumbers":false,"caption":"Equivalent package-manager commands."} /-->

## Callout

Use `docspress/callout` with `tone` set to `note`, `tip`, `warning`, `danger`, or `success`. `title` and `content` provide the message. Set `collapsible: true` to let readers toggle it; `open` controls its initial state.

<!-- wp:docspress/callout {"tone":"tip","title":"Preview first","content":"<p>Use a dry run before allowing Page writes.</p>","collapsible":true,"open":true} /-->

## API Request / Response

Use `docspress/api-request` for one HTTP exchange. Supported methods are `GET`, `POST`, `PUT`, `PATCH`, and `DELETE`. Write headers as `Name: value` lines. `requestBodyFormat` and `responseBodyFormat` accept `json` or `raw`.

<!-- wp:docspress/api-request {"method":"GET","endpoint":"/wp-json/wp/v2/pages?context=edit","headers":"Accept: application/json\nAuthorization: Bearer $WP_ACCESS_TOKEN","requestBody":"","requestBodyFormat":"json","responseStatus":"200 OK","responseBody":"[{\n  \"id\": 42,\n  \"slug\": \"docs\"\n}]","responseBodyFormat":"json"} /-->

## Terminal Session

Use `docspress/terminal-session` for a copyable `command` and optional read-only `output`. `title`, `shell`, and `prompt` label the session.

<!-- wp:docspress/terminal-session {"title":"Run the package checks","shell":"bash","prompt":"$","command":"npm run package","output":"Tests passed\nBundle rebuilt"} /-->

## Result

Use `docspress/result` after a procedure. `status` accepts `success`, `neutral`, `warning`, or `error`; the other attributes are `title`, formatted `content`, and compact `meta`.

<!-- wp:docspress/result {"status":"success","title":"Package verified","content":"<p>Lint, tests, and the Action bundle completed.</p>","meta":"npm run package"} /-->

## File Tree

Use `docspress/file-tree` with `root`, `tree`, and optional `caption`. Indent with two spaces per depth and end folder labels with `/`.

<!-- wp:docspress/file-tree {"root":"repository/","tree":"docs/\n  index.md\n  guides/\n    continuous-sync.md\npackage.json","caption":"A relevant source tree."} /-->

## Prompt

Use `docspress/prompt` for a reusable AI prompt. `mode` accepts `chat`, `code`, `ask`, or `plan`. `thinking` is Boolean. `context` is a comma-separated list of at most 12 items: `$skill-name`, `@mention`, `#image`, an HTTP URL, or a file path. Installed skills must be invoked as `$skill-name`, never by asking the agent to read a `SKILL.md` path. `caption` explains the example.

<!-- wp:docspress/prompt {"prompt":"Use $generate-docs-from-source to inspect the public exports and identify undocumented behavior.","model":"Coding agent","mode":"code","thinking":true,"context":"$generate-docs-from-source, @repository, src/, test/","caption":"Documentation coverage prompt"} /-->

## Serialization rules

- Emit the comment directly in Markdown, not inside a code fence.
- Use compact JSON with escaped quotes, newlines, backslashes, and control characters.
- DocsPress converts HTML-sensitive attribute characters to WordPress-safe Unicode escapes during Markdown conversion.
- Do not add rendered HTML after a dynamic block comment.
- Do not invent attributes. Documentation blocks use semantic colors; Hero and Audience Paths expose their documented presentation and custom-color controls.
- Install and activate the matching DocsPress Blocks plugin on WordPress.

Open the [Kitchen Sink](kitchen-sink.md) to inspect every semantic state and meaningful option in one Page.
