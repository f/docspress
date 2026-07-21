---
title: Keep documentation synchronized
---

Enable automatic synchronization only after the same pinned workflow succeeds as a dry run and as a real draft write.

## Stage 1: manual dry run

Use `workflow_dispatch`, `status: draft`, `delete-mode: trash`, and `dry-run: true`. Review the Action summary and confirm that every planned create, update, and delete is expected.

## Stage 2: manual draft write

After explicit approval, change only `dry-run` to `false`. The workflow can now create, update, and trash WordPress Pages, so keep the trigger manual while you inspect:

- the Page hierarchy;
- titles and headings;
- Gutenberg block rendering;
- rewritten internal links;
- exact GitHub source actions;
- unexpected unmanaged conflicts.

## Stage 3: path-scoped synchronization

After the draft lifecycle succeeds, add the default-branch trigger:

<!-- wp:docspress/colorful-code {"language":"yaml","filename":".github/workflows/sync-docs.yml","code":"on:\n  push:\n    branches: [main]\n    paths:\n      - \"docs/**/*.md\"\n      - \"docs/**/*.markdown\"\n      - \"docs/**/*.json\"\n      - \".github/workflows/sync-docs.yml\"\n  workflow_dispatch:\n\npermissions:\n  contents: read","highlightedLines":"2-8,11-12","showLineNumbers":true,"caption":"Only documentation and workflow changes start the ongoing sync."} /-->

Keep `status: draft` if WordPress remains an editorial review gate. Set `status: publish` only when merged Markdown should update public Pages automatically.

<!-- wp:docspress/callout {"tone":"danger","title":"Automatic sync includes removals","content":"<p>Deleting a managed Markdown file schedules its managed WordPress Page for Trash, or permanent deletion when <code>delete-mode: force</code>. Review that policy before enabling the push trigger.</p>","collapsible":true,"open":false} /-->

## Observe each run

The Action exports counters for created, updated, deleted, unchanged, and conflict operations plus `summary-json` for downstream jobs.

<!-- wp:docspress/result {"status":"success","title":"Continuous documentation delivery","content":"<p>Every merged change under the selected docs paths now reconciles the same managed WordPress Page tree.</p>","meta":"GitHub remains source of truth"} /-->

## Pin and update intentionally

Use full verified commit SHAs for both `actions/checkout` and `Automattic/docspress`. When adopting a newer DocsPress revision:

1. inspect its `action.yml` inputs;
2. review source and bundled `dist/` changes;
3. update the SHA;
4. return to a manual dry run;
5. restore automatic synchronization after verification.
