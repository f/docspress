---
title: Make documentation AI-friendly
---

DocsPress can publish one documentation tree for people and AI tools. Readers keep the complete WordPress experience, while agents, retrieval systems, and other text-first clients can discover and fetch the exact Markdown source without parsing the surrounding HTML.

AI-friendliness here means making published documentation discoverable, structured, and faithful to its reviewed source. It does not grant access to private content or replace normal permissions.

## Discover the documentation

The DocsPress theme serves an `llms.txt` index at the site root:

```text
https://docs.example.com/llms.txt
```

The response contains the site title and description followed by absolute links to every published, source-backed documentation Page:

```markdown
# Example documentation

> Product and API documentation.

## Documentation

- [Publish existing docs](https://docs.example.com/docs/publish-existing-docs.md)
- [API reference](https://docs.example.com/docs/reference/api.md)
```

This gives an AI client a small discovery document instead of requiring it to crawl navigation HTML or guess documentation routes.

## Fetch the exact Markdown

Replace a Page route's trailing slash with `.md`:

| Experience | URL |
| --- | --- |
| WordPress Page | `https://docs.example.com/docs/guides/continuous-sync/` |
| Markdown source | `https://docs.example.com/docs/guides/continuous-sync.md` |

The Markdown endpoint returns the exact UTF-8 source synchronized by DocsPress, including frontmatter, code fences, tables, and supported Gutenberg block comments. It uses the `text/markdown; charset=utf-8` content type and does not include the theme shell.

Because the response is the reviewed source rather than Markdown reconstructed from rendered HTML, identifiers, examples, and formatting remain stable for retrieval and citation.

## Keep both representations aligned

During synchronization, DocsPress stores the original Markdown in the managed Page metadata alongside its source path. The theme reads that source-owned metadata for both `llms.txt` and `.md` responses.

Run DocsPress once after installing a theme version that supports these endpoints. That run refreshes existing managed Pages even when their visible content has not changed. Future synchronizations update the WordPress Page and its Markdown representation together.

Only published Pages with a real Markdown source appear in `llms.txt`. Generated placeholder Pages and hand-authored WordPress Pages without source metadata are omitted, and their `.md` routes return `404`.

## Verify the endpoints

Check discovery, content type, and one source route after publishing:

```bash
curl https://docs.example.com/llms.txt
curl --head https://docs.example.com/docs/publish-existing-docs.md
curl https://docs.example.com/docs/publish-existing-docs.md
```

Expect `text/plain; charset=utf-8` for `llms.txt` and `text/markdown; charset=utf-8` for each Page source.

Treat these endpoints as another public representation of the documentation, not as an access-control boundary or a promise about how third parties train models. For the route and source-file contract, see [Structure pages and routes](../authoring/page-structure.md). For theme installation and behavior, see [DocsPress WordPress theme](../reference/theme.md).
