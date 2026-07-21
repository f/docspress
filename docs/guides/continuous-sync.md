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

## Stage 4: reconcile WordPress edits

After normal publishing is stable, use one workflow for push-based publishing and scheduled WordPress polling:

Before the first reverse-sync run, open [Settings → Actions → General for the repository](https://github.com/Automattic/docspress/settings/actions). Under **Workflow permissions**, enable **Allow GitHub Actions to create and approve pull requests**, then select **Save**. For another repository, open the same settings page under its owner and repository name.

![GitHub Actions workflow permissions with Allow GitHub Actions to create and approve pull requests enabled](https://raw.githubusercontent.com/Automattic/docspress/main/theme/assets/images/github-actions/allow-actions-create-pull-requests.png "Enable the pull request setting and save it before running WordPress-to-GitHub synchronization.")

<!-- wp:docspress/colorful-code {"language":"yaml","filename":".github/workflows/sync-docs.yml","code":"on:\n  push:\n    branches: [main]\n    paths: [\"docs/**\", \".github/workflows/sync-docs.yml\"]\n  schedule:\n    - cron: \"3/5 * * * *\"\n  workflow_dispatch:\n\npermissions:\n  contents: write\n  pull-requests: write\n\nconcurrency:\n  group: docspress-sync\n  cancel-in-progress: false\n\njobs:\n  sync:\n    # Do not publish a merged WordPress proposal back to WordPress.\n    if: >-\n      github.event_name != 'push' ||\n      !contains(\n        github.event.head_commit.message,\n        format('from {0}/docspress/wordpress-sync', github.repository_owner)\n      )\n    runs-on: ubuntu-latest\n    steps:\n      - uses: actions/checkout@FULL_COMMIT_SHA\n      - uses: Automattic/docspress@FULL_COMMIT_SHA\n        with:\n          mode: reconcile\n          wordpress-site: example.wordpress.com\n          wordpress-access-token: ${{ secrets.WP_ACCESS_TOKEN }}\n          docs-dir: docs\n          root-slug: docs\n          status: publish","highlightedLines":"5-6,10-12,20-26,33","showLineNumbers":true,"caption":"Pushes publish Markdown; schedules propose WordPress edits; merges from the managed proposal branch are skipped."} /-->

DocsPress compares the live Gutenberg tree with the tree generated from the current Markdown, then rewrites only source regions whose blocks changed. Unchanged frontmatter, spacing, code-fence languages, tables, and serialized custom blocks stay byte-for-byte intact. Supported core blocks become readable Markdown; attributed, preformatted, and unrecognized blocks stay as lossless serialized Gutenberg comments. If DocsPress cannot map the blocks safely, the run fails instead of regenerating the whole file.

The Action updates one action-owned branch and pull request instead of opening duplicates on every poll.

The job condition skips the `push` event created when GitHub merges the action-owned `docspress/wordpress-sync` branch. Scheduled and manual runs still reconcile normally. If you set a custom `pull-request-branch`, use the same branch name in the condition.

While that pull request is open, `reconcile` leaves the WordPress-only Page untouched. After the pull request merges, the next run recognizes that both sides converge and refreshes the Page sentinel; GitHub-only changes to other Pages can continue publishing in the same run.

<!-- wp:docspress/callout {"tone":"warning","title":"Two-sided edits stop before writes","content":"<p>If GitHub and WordPress both changed the same managed Page since the sentinel baseline, reconcile mode reports a conflict and changes neither system. Resolve one side deliberately, then run the workflow again.</p>","collapsible":false} /-->

## Observe each run

The Action exports counters for created, updated, deleted, unchanged, and conflict operations plus `summary-json` for downstream jobs.

The [first synchronization walkthrough](../getting-started/first-sync.md) shows the run overview and generated Sync Summary with tightly cropped screenshots. Use the same counters to review every automatic run.

## Pin and update intentionally

Use full verified commit SHAs for both `actions/checkout` and `Automattic/docspress`. When adopting a newer DocsPress revision:

1. inspect its `action.yml` inputs;
2. review source and bundled `dist/` changes;
3. update the SHA;
4. return to a manual dry run;
5. restore automatic synchronization after verification.
