---
title: GitHub Action inputs and outputs
---

DocsPress is a Node 20 GitHub Action configured entirely through `with` inputs.

## WordPress connection

| Input | Default | Description |
| --- | --- | --- |
| `wordpress-url` | `https://public-api.wordpress.com` | API base. For self-hosted WordPress, use the site origin without `/wp-json`. |
| `wordpress-site` | required | WordPress.com site ID or domain. Action metadata requires it for every run. |
| `wordpress-access-token` | required | Bearer token permitted to edit Pages. Use the `WP_ACCESS_TOKEN` GitHub Actions secret. |

## Documentation discovery and routing

| Input | Default | Description |
| --- | --- | --- |
| `docs-dir` | `docs` | Directory containing `.md` and `.markdown` files. |
| `manifest-file` | empty | Optional JSON manifest for explicit titles, slugs, parents, and source files. |
| `redirects-file` | empty | Optional JSON redirects map that creates managed moved-page placeholders. |
| `root-slug` | `docs` | Slug of the managed root Page. |
| `root-title` | `Docs` | Fallback root title when no root document supplies one. |

## Content conversion and source links

| Input | Default | Description |
| --- | --- | --- |
| `create-h1` | `false` | Adds the Page title as an H1 Gutenberg block. Keep false with the DocsPress theme. |
| `rewrite-links` | `true` | Rewrites known local Markdown targets to their WordPress routes. |
| `edit-link` | `false` | Appends a source link to Markdown-backed Pages. |
| `edit-link-text` | `Edit this page on GitHub` | Text for the appended source link. |
| `github-repository` | `GITHUB_REPOSITORY` | Repository used in edit URLs, for example `owner/repo`. |
| `github-ref` | `GITHUB_REF_NAME`, then `main` | Branch or ref used in edit URLs. |
| `github-server-url` | `GITHUB_SERVER_URL`, then `https://github.com` | GitHub server used in edit URLs. |

## Publication and deletion

| Input | Default | Description |
| --- | --- | --- |
| `status` | `publish` | WordPress status for created or updated Pages. Start with `draft`. |
| `delete-mode` | `trash` | `trash` moves removed managed Pages to Trash; `force` permanently deletes them. |
| `dry-run` | `false` | Plans operations without WordPress writes. Start with `true`. |

<!-- wp:docspress/callout {"tone":"warning","title":"Override the write-capable defaults","content":"<p>The Action metadata defaults to <code>status: publish</code> and <code>dry-run: false</code>. New installations should explicitly set <code>status: draft</code>, <code>delete-mode: trash</code>, and <code>dry-run: true</code>.</p>","collapsible":false} /-->

## Outputs

| Output | Description |
| --- | --- |
| `created` | Pages created or planned for creation. |
| `updated` | Pages updated or planned for update. |
| `deleted` | Pages deleted or planned for deletion. |
| `unchanged` | Managed Pages already matching the desired state. |
| `conflicts` | Unmanaged Page path collisions. |
| `summary-json` | JSON object containing counters, conflict details, and ordered operations. |

The Action also writes a GitHub Actions job summary and fails the run when `conflicts` is greater than zero.
