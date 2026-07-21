---
title: Troubleshooting
---

Start with the GitHub Actions summary, then match the symptom to the narrowest source-backed check.

## Authentication fails

| Symptom | Check |
| --- | --- |
| `401` or “Invalid token” | Confirm `WP_ACCESS_TOKEN` exists in the correct repository and was not revoked. |
| WordPress.com reports required `global` scope | Regenerate the token with the DocsPress helper, whose default scope is `global`. |
| Self-hosted endpoint rejects Bearer authentication | Confirm the site has a trusted Bearer-token mechanism; DocsPress does not provision one. |
| WordPress.com endpoint is missing the site | Set `wordpress-site` to the site domain or ID. |

Do not print the token while debugging. Verify only the secret name with `gh secret list`.

## An unmanaged Page conflict stops the run

DocsPress found a WordPress Page at a desired path without a valid management sentinel. This is intentional protection.

Choose one safe resolution:

1. change the documentation route or `root-slug`;
2. move the manual Page to a different path;
3. migrate its content deliberately before allowing DocsPress to own that route.

Never inject a fake sentinel merely to bypass the conflict.

<!-- wp:docspress/result {"status":"warning","title":"Conflict requires a content decision","content":"<p>The Action leaves the manual Page untouched and reports the exact key in conflict details.</p>","meta":"0 writes to conflicting path"} /-->

## A parent Page is unavailable

Inspect earlier conflict details. Desired Pages are processed parent first; a missing or conflicting parent prevents its children from receiving a valid parent ID.

## The wrong Pages are scheduled for deletion

Confirm:

- the intended `docs-dir` was checked out;
- `root-slug` matches the existing managed tree;
- files were not renamed accidentally;
- the manifest or redirects file is present;
- the workflow has not switched repositories or branches.

Keep `dry-run: true`. `delete-mode: trash` is recoverable; `force` is permanent.

## Two files map to the same Page

Do not place both `index.md` and `README.md` in one folder. Also check for filenames that become identical after slug normalization.

## Links still point to Markdown

Confirm `rewrite-links: true` and use a relative link to a discovered documentation source. DocsPress leaves unknown local files, external URLs, anchors, and protocol links unchanged.

## Custom blocks do not render

Confirm the matching DocsPress Blocks plugin is installed and active. Validate the `wp:docspress/*` comment JSON, block name, attributes, enums, and escaped newlines against the [block reference](reference/gutenberg-blocks.md).

## The theme shows duplicate H1 headings

Set `create-h1: false`. The DocsPress theme prints the WordPress Page title as the document H1; Markdown body sections should start at `##`.

## Search or navigation misses Pages

The theme searches and lists published Pages in the configured documentation root or selected sidebar menu. Confirm the Pages are published, the correct root is selected, and the menu contains the expected items.

## Development checks fail

Review package scripts before executing them, then run:

<!-- wp:docspress/terminal-session {"title":"Verify DocsPress","shell":"bash","prompt":"$","command":"npm test\nnpm run lint\nnpm run build","output":""} /-->

`dist/index.js` is committed. Rebuild it whenever Action source changes, but do not rebuild it for documentation-only edits unless the package process requires it.
