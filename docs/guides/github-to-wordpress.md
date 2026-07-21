---
title: GitHub to WordPress
---

Use GitHub-to-WordPress synchronization when reviewed Markdown on the repository's default branch should publish as native WordPress Pages and Gutenberg blocks.

## Choose the mode

| Workflow design | Mode | Behavior |
| --- | --- | --- |
| GitHub is the only editing source | `publish` | Treat Markdown as authoritative and update managed WordPress Pages. |
| Authors edit in GitHub and WordPress | `reconcile` | Publish GitHub-only changes while preserving WordPress-only changes for a pull request. |

Start with the [manual two-way dry run](../getting-started/first-sync.md). Use the automatic workflow below only after its draft Page tree is approved.

## Publish merged Markdown

This standalone workflow runs only when documentation or its workflow changes on `main`:

<!-- wp:docspress/colorful-code {"language":"yaml","filename":".github/workflows/sync-docs.yml","code":"name: Publish DocsPress documentation\n\non:\n  push:\n    branches: [main]\n    paths:\n      - \"docs/**/*.md\"\n      - \"docs/**/*.markdown\"\n      - \"docs/**/*.json\"\n      - \".github/workflows/sync-docs.yml\"\n  workflow_dispatch:\n\npermissions:\n  contents: read\n\njobs:\n  sync:\n    runs-on: ubuntu-latest\n    steps:\n      - uses: actions/checkout@11d5960a326750d5838078e36cf38b85af677262\n      - uses: Automattic/docspress@14d318924a81fb95ce4d3aaa9c3b547bf76b7768\n        with:\n          mode: publish\n          wordpress-site: example.wordpress.com\n          wordpress-access-token: ${{ secrets.WP_ACCESS_TOKEN }}\n          docs-dir: docs\n          root-slug: docs\n          root-title: Docs\n          status: publish\n          delete-mode: trash\n          dry-run: false","highlightedLines":"3-10,13-14,21-23,28-30","showLineNumbers":true,"caption":"A path-scoped push publishes merged Markdown without granting GitHub pull request permissions."} /-->

Replace the site domain and pin revisions you have reviewed. Keep `status: draft` until publishing directly from `main` is an approved editorial policy.

## What the Action changes

1. Discovers Markdown and optional manifest or redirect files under `docs-dir`.
2. Converts supported Markdown into serialized Gutenberg blocks.
3. Compares the desired tree with WordPress Pages carrying the DocsPress management marker.
4. Creates or updates only those managed Pages.
5. Moves a managed Page to Trash when its Markdown source disappears and `delete-mode: trash` is active.

<!-- wp:docspress/callout {"tone":"danger","title":"A deleted file can remove a Page","content":"<p>Automatic publication includes removals. Keep <code>delete-mode: trash</code> unless permanent deletion is an explicit requirement, and review the Deleted counter on every rollout.</p>","collapsible":false} /-->

## Use the unified workflow for two-way editing

Do not run a separate `publish` workflow beside a WordPress proposal workflow for the same Page tree. Use one [continuous `reconcile` workflow](continuous-sync.md) so DocsPress evaluates both versions against the same synchronization marker and stops on genuine two-sided edits.

In `reconcile` mode, a normal Markdown push still follows the GitHub-to-WordPress path. The difference is that a newer WordPress edit is preserved for the [WordPress-to-GitHub](wordpress-to-github.md) pull request path instead of being overwritten.

## Review the result

Check the Action summary for created, updated, deleted, unchanged, and conflict counts. A successful job means the planned operations completed; it does not replace editorial review of public content, navigation, blocks, or rewritten links.
