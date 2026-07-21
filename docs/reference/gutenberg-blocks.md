---
title: DocsPress Gutenberg blocks
---

The DocsPress Blocks plugin registers eight dynamic blocks. Their saved content is a self-closing Gutenberg comment whose attributes are valid JSON.

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
- Do not invent attributes or custom color controls.
- Install and activate the matching DocsPress Blocks plugin on WordPress.

Open the [Kitchen Sink](kitchen-sink.md) to inspect every semantic state and meaningful option in one Page.
