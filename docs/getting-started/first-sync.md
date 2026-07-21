---
title: Run the first synchronization
---

The first workflow should calculate the WordPress changes without writing any Pages.

## Start with a manual workflow

<!-- wp:docspress/colorful-code {"language":"yaml","filename":".github/workflows/sync-docs.yml","code":"name: Sync DocsPress documentation\n\non:\n  workflow_dispatch:\n\npermissions:\n  contents: read\n\njobs:\n  sync:\n    runs-on: ubuntu-latest\n    steps:\n      - uses: actions/checkout@11d5960a326750d5838078e36cf38b85af677262\n      - uses: Automattic/docspress@7db37bca3a10c67d923012606d39d1925c2277ef\n        with:\n          wordpress-site: example.wordpress.com\n          wordpress-access-token: ${{ secrets.WP_ACCESS_TOKEN }}\n          docs-dir: docs\n          root-slug: docs\n          root-title: Docs\n          create-h1: false\n          rewrite-links: true\n          edit-link: false\n          status: draft\n          delete-mode: trash\n          dry-run: true","highlightedLines":"3-4,13-14,24-26","showLineNumbers":true,"caption":"Immutable Action revisions, read-only repository permission, draft status, and dry-run enabled."} /-->

The pinned DocsPress revision is the current verified `Automattic/docspress` `main` commit for this documentation revision. Review and intentionally update it when adopting a newer release.

## Review the plan

Dispatch the workflow only after `WP_ACCESS_TOKEN` exists:

```bash
gh workflow run sync-docs.yml --repo OWNER/REPO
gh run watch --repo OWNER/REPO
```

The Action summary reports `created`, `updated`, `deleted`, `unchanged`, and `conflicts`. Inspect `summary-json` when another job needs the operation details.

Stop when you see:

- unmanaged Page conflicts;
- an unexpected deletion plan;
- the wrong root slug or Page hierarchy;
- authentication or missing `global` scope errors;
- links resolving outside the intended documentation tree.

## Approve a real draft write

After the dry run is correct, obtain approval for Page creation, updates, and Trash operations. Change only:

```yaml
dry-run: false
```

Keep `status: draft`, dispatch again, and inspect the generated Pages in WordPress before enabling automatic synchronization or publication.

<!-- wp:docspress/result {"status":"success","title":"First draft tree verified","content":"<p>The repository hierarchy, Gutenberg content, rewritten links, and managed-page boundaries are ready for editorial review.</p>","meta":"next: continuous sync"} /-->

Continue with [continuous synchronization](../guides/continuous-sync.md).
