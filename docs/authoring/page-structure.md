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
