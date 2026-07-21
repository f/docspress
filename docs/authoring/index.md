---
title: Authoring documentation
---

Write ordinary Markdown for ordinary content and use DocsPress blocks where documentation needs richer semantics such as commands, results, API exchanges, or reusable prompts.

## Authoring contract

- Put the documentation root at `docs/index.md`.
- Use folder `index.md` files for intentional section landing pages.
- Add frontmatter `title` and begin body sections at `##` when the DocsPress theme renders the Page title.
- Link pages with relative Markdown paths; DocsPress rewrites known documentation routes by default.
- Use real, verified examples with fake credentials and non-production identifiers.
- Keep serialized `wp:docspress/*` comments as valid compact JSON.

<!-- wp:docspress/callout {"tone":"note","title":"One Markdown tree, two block systems","content":"<p>Common Markdown becomes WordPress core blocks. Serialized DocsPress comments remain custom dynamic blocks rendered by the companion plugin.</p>","collapsible":false} /-->

## Learn the formats

- [Page structure and routes](page-structure.md) explains filenames, titles, parent Pages, links, manifests, and edit links.
- [Markdown and Gutenberg](markdown-and-gutenberg.md) maps Markdown features and explains when to choose each DocsPress block.
- [DocsPress block reference](../reference/gutenberg-blocks.md) lists exact attributes and allowed values.
- [Kitchen Sink](../reference/kitchen-sink.md) exercises every custom block and semantic state.
