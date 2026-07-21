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

## Choose a DocsPress block

<!-- wp:docspress/code-tabs {"tabs":[{"label":"Source code","language":"plaintext","filename":"Use","code":"DocsPress: Colorful Code\nOne example with a filename, highlighting, line numbers, caption, and copy."},{"label":"Alternatives","language":"plaintext","filename":"Use","code":"DocsPress: Code Tabs\nEquivalent package-manager, language, platform, or API-client examples."},{"label":"Commands","language":"plaintext","filename":"Use","code":"DocsPress: Terminal Session\nA copyable command separated from its observed output."}],"showLineNumbers":false,"caption":"Choose blocks by meaning, not decoration."} /-->

- Use **Callout** for a note, tip, warning, danger, or success message.
- Use **API Request / Response** to keep one verified HTTP exchange together.
- Use **Result** to summarize a verified outcome after a procedure.
- Use **File Tree** for relevant repository or generated structure.
- Use **Prompt** for a reusable AI prompt whose model, mode, context, and caption matter.

## Serialize custom blocks

DocsPress Blocks are dynamic. Store one self-closing comment with compact valid JSON and no rendered block body:

```html
<!-- wp:docspress/result {"status":"success","title":"Checks passed","content":"<p>The examples match the source.</p>","meta":"24 tests"} /-->
```

Escape quotes, backslashes, control characters, and newlines within JSON strings. DocsPress additionally normalizes HTML-sensitive characters in block attributes to the Unicode escapes WordPress expects before synchronization. Do not add unsupported attributes or colors. The active theme preset owns visual tokens in light and dark modes.

<!-- wp:docspress/callout {"tone":"warning","title":"The plugin is required","content":"<p>WordPress must have the matching DocsPress Blocks plugin installed and active to render <code>wp:docspress/*</code> blocks.</p>","collapsible":false} /-->

See the [complete block reference](../reference/gutenberg-blocks.md) and [Kitchen Sink](../reference/kitchen-sink.md).
