---
name: generate-docs-from-source
description: Generate accurate DocsPress-compatible Markdown documentation from an existing source-code repository, select and serialize the available DocsPress Gutenberg blocks correctly, verify examples and claims against code and tests, create or improve the docs/ hierarchy, and configure a safe DocsPress GitHub Actions workflow when one is missing. Use when a project has incomplete, stale, or no documentation and an agent must derive installation, configuration, guides, API or CLI references, architecture, and troubleshooting material from the repository itself before publishing to WordPress.
---

# Generate Docs from Source

Build documentation from evidence in the repository, not assumptions. Produce a navigable Markdown tree that DocsPress can convert into WordPress Pages, then hand publication setup to `$docspress-install`.

## 1. Establish the source of truth

1. Resolve the repository root and preserve unrelated working-tree changes.
2. Inventory the project with `rg --files`. Inspect package manifests, lockfiles, entrypoints, exports, command definitions, schemas, environment examples, tests, examples, release configuration, and existing docs.
3. Identify the intended audience and supported public surface from repository evidence.
4. Build an internal coverage map before writing:
   - installation commands → package manifests and lockfiles;
   - configuration and environment variables → schemas, defaults, and code reads;
   - API signatures → exported source and type declarations;
   - CLI commands and flags → parser definitions and help output;
   - behavior and edge cases → tests;
   - operational steps → scripts and CI workflows.
5. Treat tests and executable examples as stronger evidence than comments. Mark contradictions for resolution instead of choosing silently.

Do not document private helpers as public APIs. Do not invent commands, options, URLs, support guarantees, performance claims, or output text.

## 2. Reuse and plan the docs tree

Preserve useful existing documentation and its voice. Update stale pages in place rather than replacing the whole directory.

Scale the tree to the project. A typical structure is:

```text
docs/
  index.md
  getting-started/
    index.md
    installation.md
    configuration.md
  guides/
    index.md
    first-real-workflow.md
  reference/
    index.md
    api.md
    cli.md
  troubleshooting.md
```

Create only pages supported by the source. Small libraries may need only an overview, installation, usage, and API reference. Applications may need architecture, deployment, operations, and troubleshooting.

Map `docs/index.md` to the Docs root. Use folder `index.md` files for section landing pages. Avoid multiple files that normalize to the same route.

## 3. Write DocsPress-compatible Markdown

Use this page pattern:

```markdown
---
title: Clear page title
---

One short paragraph explaining the outcome of this page.

## First section

Verified instructions and examples.
```

Follow these constraints:

- Prefer frontmatter `title` and begin body sections at `##`; the DocsPress theme supplies the Page `h1`.
- Use `.md` or `.markdown` files and relative Markdown links between pages.
- Keep paths stable and slugs readable; directories become parent Pages.
- Use fenced code blocks with accurate language labels.
- Use standard Markdown for ordinary prose, headings, lists, links, tables, and images. Use the DocsPress blocks below whenever their documentation-specific semantics apply.
- Preserve existing serialized Gutenberg block comments when valid. Validate their attributes before reusing them.
- Prefer `docspress/code-tabs` over Gutenberg Handbook-style `{% codetabs %}` when the DocsPress Blocks plugin is part of the target installation.
- Explain prerequisites before commands and verification after commands.
- Keep examples minimal but runnable. Never use real credentials or production identifiers.
- Link conceptual claims to the relevant reference page instead of duplicating long explanations.

## 4. Use DocsPress Gutenberg blocks

Always review all eight blocks before writing the docs and make a page-by-page block plan. Use every relevant block, but do not force a block where ordinary Markdown communicates the material better.

DocsPress preserves serialized Gutenberg comments in Markdown and normalizes HTML-sensitive attribute characters to WordPress-safe Unicode escapes during conversion. These plugin blocks are dynamic, so write one self-closing comment with valid compact JSON and no rendered HTML body:

```html
<!-- wp:docspress/block-name {"attribute":"value"} /-->
```

In generated Markdown, emit the comment directly, without the surrounding code fence. JSON-escape quotes, backslashes, control characters, and literal newlines inside attribute strings. Never add trailing commas, JavaScript object syntax, invented attributes, or custom colors. The plugin inherits colors, typography, radius, borders, and light/dark presentation from the active DocsPress preset.

### Block selection and schemas

| Editor block | Serialized name | Use for | Supported attributes |
| --- | --- | --- | --- |
| DocsPress: Colorful Code | `docspress/colorful-code` | One source or configuration example that benefits from filename, highlighting, line numbers, caption, and copy | `language`, `filename`, `code`, `highlightedLines`, `showLineNumbers`, `caption` |
| DocsPress: Code Tabs | `docspress/code-tabs` | Two to eight equivalent implementations, package managers, languages, or platforms | `tabs`, `showLineNumbers`, `caption`; each tab has `label`, `language`, `filename`, `code` |
| DocsPress: Callout | `docspress/callout` | Important notes, tips, warnings, hazards, or success guidance | `tone`, `title`, `content`, `collapsible`, `open` |
| DocsPress: API Request / Response | `docspress/api-request` | A verified HTTP request and its response as one unit | `method`, `endpoint`, `headers`, `requestBody`, `requestBodyFormat`, `responseStatus`, `responseBody`, `responseBodyFormat` |
| DocsPress: Terminal Session | `docspress/terminal-session` | A copyable command with optional read-only output | `title`, `shell`, `prompt`, `command`, `output` |
| DocsPress: Result | `docspress/result` | A concise verified outcome after a build, check, command, or procedure | `status`, `title`, `content`, `meta` |
| DocsPress: File Tree | `docspress/file-tree` | A relevant project or generated-directory structure | `root`, `tree`, `caption` |
| DocsPress: Prompt | `docspress/prompt` | A reusable AI prompt with model, mode, context, and caption | `prompt`, `model`, `mode`, `thinking`, `context`, `caption` |

Use only these allowed values:

- Code `language`: `bash`, `css`, `html`, `javascript`, `json`, `jsx`, `markdown`, `php`, `plaintext`, `python`, `shell`, `sql`, `tsx`, `typescript`, or `yaml`.
- `highlightedLines`: comma-separated one-based lines and ranges such as `2,4-6`.
- Callout `tone`: `note`, `tip`, `warning`, `danger`, or `success`. Set `open` only when `collapsible` is `true`.
- API `method`: `GET`, `POST`, `PUT`, `PATCH`, or `DELETE`. Write headers as one `Name: value` pair per line. Body formats are `json` or `raw`.
- Result `status`: `success`, `neutral`, `warning`, or `error`.
- Prompt `mode`: `chat`, `code`, `ask`, or `plan`. `context` is a comma-separated list of at most 12 items; `$` denotes an installed skill, `@` a mention, `#` an image, `http://` or `https://` a URL, and other values a file. Always invoke installed skills as `$skill-name`, never as a `SKILL.md` file path inside a user-facing prompt.
- File trees use two spaces per depth level and a trailing slash for folders.

### Canonical examples

```html
<!-- wp:docspress/colorful-code {"language":"typescript","filename":"src/client.ts","code":"import { Client } from \"pkg\";\n\nconst client = new Client();","highlightedLines":"3","showLineNumbers":true,"caption":"Create the client."} /-->

<!-- wp:docspress/code-tabs {"tabs":[{"label":"npm","language":"bash","filename":"Terminal","code":"npm install example"},{"label":"pnpm","language":"bash","filename":"Terminal","code":"pnpm add example"}],"showLineNumbers":false,"caption":"Install with the package manager used by the project."} /-->

<!-- wp:docspress/callout {"tone":"warning","title":"Protect credentials","content":"<p>Store the token in a secret manager.</p>","collapsible":false} /-->

<!-- wp:docspress/api-request {"method":"POST","endpoint":"/v1/items","headers":"Content-Type: application/json\nAuthorization: Bearer $TOKEN","requestBody":"{\n  \"name\": \"example\"\n}","requestBodyFormat":"json","responseStatus":"201 Created","responseBody":"{\n  \"id\": 42\n}","responseBodyFormat":"json"} /-->

<!-- wp:docspress/terminal-session {"title":"Run the checks","shell":"bash","prompt":"$","command":"npm test","output":"Tests: 24 passed"} /-->

<!-- wp:docspress/result {"status":"success","title":"Verification passed","content":"<p>All documented examples completed successfully.</p>","meta":"24 tests"} /-->

<!-- wp:docspress/file-tree {"root":"project/","tree":"docs/\n  index.md\n  guides/\n    first-task.md","caption":"Generated documentation tree."} /-->

<!-- wp:docspress/prompt {"prompt":"Use $generate-docs-from-source to review the public API and identify undocumented error cases.","model":"GPT-5","mode":"code","thinking":true,"context":"$generate-docs-from-source, @repository, src/index.ts, test/api.test.ts","caption":"API coverage review prompt"} /-->
```

Use verified source values in real docs instead of copying these placeholders. Keep secrets fake. Use HTML only in the `content` and `caption` attributes that support formatted text, and keep it minimal and valid.

This catalog matches the DocsPress Blocks source shipped with the skill revision. If a verified target plugin revision differs, inspect its `blocks/*/block.php` registrations and render allow-lists, then use that revision as the source of truth.

## 5. Generate from evidence

### Overview and getting started

Explain what the project does, who it is for, its real prerequisites, installation, and the smallest useful workflow. Derive package-manager commands from the checked-in package metadata and lockfile.

### Configuration

Document only settings read by the application. Include name, required/default state, accepted values, effect, and security sensitivity. Distinguish build-time, runtime, client-visible, and secret values.

### Guides

Choose workflows demonstrated by examples, tests, or normal source composition. Make each guide outcome-oriented and verify every referenced file and command.

### API or CLI reference

Enumerate public exports or registered commands from source. Preserve exact spelling, types, defaults, exit behavior, and errors. Generate help output locally when a safe `--help` command exists.

### Troubleshooting

Include failures evidenced by tests, explicit error messages, issue templates, or defensive branches. Pair symptoms with concrete checks and safe recovery steps.

## 6. Verify before calling the docs complete

Run the cheapest relevant checks first and record exact results.

1. Confirm every generated page is nonempty and has a unique route and title.
2. Resolve every relative link and local image path from the file containing it.
3. Search for placeholders such as `TODO`, `TBD`, `YOUR_*`, fake domains, and unverified version numbers. Keep deliberate placeholders only inside clearly labeled templates.
4. Match documented exports, flags, environment variables, filenames, and defaults back to source.
5. Run code samples when they are safe and practical. Prefer examples already covered by tests.
6. Parse every `wp:docspress/*` attribute object as JSON. Confirm the block name, attributes, enum values, tab count, tree indentation, and required plugin support against this catalog or the verified plugin source.
7. Run representative generated Markdown through the pinned DocsPress converter and confirm every custom block comment is preserved byte-for-byte.
8. Inspect repository scripts and dependency lifecycle hooks before executing them. Run the existing formatter, lint, typecheck, tests, and build in proportion to the changes; isolate commands that rewrite generated files in a temporary copy or worktree when practical.
9. Run `git diff --check` and inspect the complete docs diff for accidental source changes or copied secrets.
10. If a check cannot run, state why and narrow the claim. Never present an unrun example as verified.

Do not weaken tests or alter product behavior merely to make documentation examples pass. If source behavior is broken or ambiguous, report it separately.

## 7. Configure publication when missing

Search `.github/workflows/` for an existing DocsPress action. If none exists:

1. Invoke `$docspress-install`.
2. Create one `.github/workflows/sync-docs.yml` targeting the generated docs directory.
3. Start with `status: draft`, `dry-run: true`, and `delete-mode: trash`.
4. Reference `${{ secrets.WP_ACCESS_TOKEN }}`; never create a plaintext credential file.
5. Resolve checkout and DocsPress actions to verified immutable commit SHAs, then validate all inputs against `action.yml` at the pinned DocsPress revision.
6. Detect the repository default branch rather than hard-coding `main`, but begin with `workflow_dispatch` only. Add a default-branch push trigger after the manual dry-run and draft-write lifecycle succeeds and the user approves ongoing synchronization.
7. If any `wp:docspress/*` blocks are present, require the verified matching `plugins/docspress-blocks/` plugin on the WordPress target. Ask separately before installing or activating it.
8. Validate the workflow locally. Do not push, dispatch, add secrets, install or activate plugins, activate a theme, or write WordPress Pages unless the user separately authorized those external changes.

Documentation generation must still complete when WordPress credentials are unavailable. Leave the workflow ready and report the exact authentication step the user must perform.

## Completion report

Report:

- pages created, updated, and intentionally preserved;
- source files used as evidence;
- code examples and commands actually executed;
- DocsPress blocks used, their locations, serialization validation, and plugin requirement;
- lint, test, build, link, and workflow validation results;
- DocsPress workflow state;
- any unverified claims, source contradictions, or required user decisions.
