---
title: Structure pages and routes
---

DocsPress derives the WordPress Page hierarchy from Markdown paths unless a manifest defines a different contract.

## File-to-Page mapping

<!-- wp:docspress/file-tree {"root":"docs/","tree":"index.md\ngetting-started.md\nguides/\n  index.md\n  continuous-sync.md\nreference/\n  action-inputs.md","caption":"Index files become section roots; other filenames become child slugs."} /-->

With `root-slug: docs`, these files map to:

| Source | WordPress path |
| --- | --- |
| `docs/index.md` | `/docs/` |
| `docs/getting-started.md` | `/docs/getting-started/` |
| `docs/guides/index.md` | `/docs/guides/` |
| `docs/guides/continuous-sync.md` | `/docs/guides/continuous-sync/` |
| `docs/reference/action-inputs.md` | `/docs/reference/action-inputs/` |

Both `index.md` and `README.md` are treated as a folder index. Do not place both in the same directory: they normalize to the same route and DocsPress stops with an error.

When the DocsPress theme is active, every file-backed Page also exposes the exact source at the Page path with `.md` instead of the trailing slash. For example, `/docs/reference/action-inputs.md` returns the original Markdown and frontmatter. The theme lists these source endpoints in `/llms.txt`; generated placeholder Pages are omitted because they have no source file. See [Make documentation AI-friendly](../guides/ai-friendly-documentation.md) for the discovery and verification workflow.

## Titles and headings

Title precedence is:

1. frontmatter `title`;
2. the first Markdown `# H1`;
3. the filename converted to a title.

When the first H1 supplies the title, DocsPress removes it from the body. For the DocsPress theme, prefer:

```markdown
---
title: Continuous synchronization
---

One-sentence page outcome.

## First section
```

Use `create-h1: false` because the theme already renders the WordPress Page title.

## Sidebar position and initial state

The DocsPress theme reads two optional Docusaurus-style frontmatter fields:

```markdown
---
title: Continuous synchronization
sidebar_position: 20
sidebar_collapsed: true
---
```

- `sidebar_position` must be a signed integer. DocsPress synchronizes it to WordPress `menu_order`; the automatic Page tree honors it when **Page order, then title** is selected. Positions apply among siblings, with titles breaking ties.
- `sidebar_collapsed` must be `true` or `false`. It controls the initial state only when the Page has children in the rendered sidebar.
- Without `sidebar_collapsed`, inactive branches start collapsed. The current Page and all its ancestors remain visible regardless of their configured default.

A hand-built WordPress sidebar menu keeps its menu-item order, but Page-backed parent items still inherit `sidebar_collapsed`. Removing a previously synchronized `sidebar_position` resets that source-owned Page order to `0`; DocsPress leaves legacy manual Page order untouched when frontmatter never owned it. Invalid field values stop collection and identify the source file instead of publishing an ambiguous navigation state.

## Missing parents

When a nested file has no section index, DocsPress creates a managed placeholder Page for the missing parent. Add a real `index.md` when that section needs useful introductory content.

## Local links

With `rewrite-links: true`, known relative or root-relative Markdown links become WordPress routes. External URLs, anchors, protocol URLs, and unknown files remain unchanged.

```markdown
[Start here](../getting-started/index.md)
[Action inputs](/docs/reference/action-inputs.md)
[WordPress](https://wordpress.org/)
```

## Edit links

The Action can append a source link to Markdown-backed Pages with `edit-link: true`. The DocsPress theme also provides its own two-button article action bar using the sentinel source path. When using the theme action bar, keep the Action-level edit link disabled to avoid a duplicate control.
