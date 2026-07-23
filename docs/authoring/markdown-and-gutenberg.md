---
title: Markdown and Gutenberg
---

DocsPress converts familiar Markdown into editable Gutenberg-compatible content and preserves valid serialized block comments.

## Core Markdown mapping

| Markdown | WordPress block |
| --- | --- |
| Paragraphs and inline formatting | `core/paragraph` |
| Headings | `core/heading` |
| Ordered, unordered, nested, and task lists | `core/list` |
| Blockquotes | `core/quote` |
| Fenced code | `core/code` |
| GFM tables | `core/table` |
| Images | `core/image` |
| Horizontal rules | `core/separator` |
| Raw HTML | `core/html` |
| Serialized Gutenberg comments | preserved, with WordPress-safe attribute escaping |

## Reverse synchronization

In `propose` and `reconcile` modes, DocsPress matches the live top-level Gutenberg blocks to the blocks generated from the current source. It applies a WordPress-only edit to the corresponding Markdown region and preserves every unchanged region exactly, including frontmatter formatting, blank lines, code-fence languages, and custom-block JSON.

WordPress may add editor-only attributes or omit attributes whose values equal a block default. DocsPress treats those serialization differences as equivalent. All DocsPress custom blocks round-trip as self-closing serialized comments; supported plain core blocks return to readable Markdown. A core block with meaningful attributes, an unknown block, or a preformatted block remains serialized so publishing the resulting pull request cannot discard its block data.

If the source structure cannot be mapped safely to the live blocks, reverse synchronization stops with an error rather than rewriting the entire Markdown body.

## Add screenshots and diagrams

A standalone Markdown image becomes a native `core/image` block. Write meaningful alternative text and add an optional quoted title when the image needs a visible caption:

```markdown
![The navigation controls in the DocsPress Customizer](https://example.com/navigation.jpg "Choose a Page tree or a WordPress menu.")
```

DocsPress preserves the image URL; it does not upload repository files to the WordPress Media Library. Use a stable HTTPS URL, or upload the asset separately before synchronization. The [theme customization guide](../guides/customize-theme.md) is an image-rich example with real WordPress administration screenshots.

## Choose a DocsPress block

<!-- wp:docspress/code-tabs {"tabs":[{"label":"Source code","language":"plaintext","filename":"Use","code":"DocsPress: Colorful Code\nOne example with a filename, highlighting, line numbers, caption, and copy."},{"label":"Alternatives","language":"plaintext","filename":"Use","code":"DocsPress: Code Tabs\nEquivalent package-manager, language, platform, or API-client examples."},{"label":"Commands","language":"plaintext","filename":"Use","code":"DocsPress: Terminal Session\nA copyable command separated from its observed output."}],"showLineNumbers":false,"caption":"Choose blocks by meaning, not decoration."} /-->

- Use **Callout** for a note, tip, warning, danger, or success message.
- Use **Hero** for a fully editable WordPress homepage introduction; unlike documentation blocks, it intentionally exposes layout and color controls.
- Use **Audience Paths** to send readers into independent Page roots based on a useful starting state, such as publishing existing Markdown or creating documentation from source.
- Use **API Request / Response** to keep one verified HTTP exchange together.
- Use **Result** to summarize a verified outcome after a procedure.
- Use **File Tree** for relevant repository or generated structure.
- Use **Prompt** for a reusable AI prompt whose model, mode, context, and caption matter.

## Serialize custom blocks

DocsPress Blocks are dynamic. Store one self-closing comment with compact valid JSON and no rendered block body:

```html
<!-- wp:docspress/result {"status":"success","title":"Checks passed","content":"<p>The examples match the source.</p>","meta":"24 tests"} /-->
```

Escape quotes, backslashes, control characters, and newlines within JSON strings. DocsPress additionally normalizes HTML-sensitive characters in block attributes to the Unicode escapes WordPress expects before synchronization. Do not add unsupported attributes. Documentation blocks use preset-owned semantic colors; Hero and Audience Paths accept only their documented presentation attributes.

<!-- wp:docspress/callout {"tone":"warning","title":"The plugin is required","content":"<p>WordPress must have the matching DocsPress Blocks plugin installed and active to render <code>wp:docspress/*</code> blocks.</p>","collapsible":false} /-->

See the [complete block reference](../reference/gutenberg-blocks.md) and [Kitchen Sink](../reference/kitchen-sink.md).
