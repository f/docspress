---
title: WordPress to GitHub
---

Use WordPress-to-GitHub synchronization when an editor changes a managed Page in Gutenberg and the corresponding Markdown change should return to the repository through reviewable pull requests.

## Requirements

- The same `WP_ACCESS_TOKEN` used for publishing; DocsPress uses it to read editable Page content.
- `contents: write` and `pull-requests: write` permissions for the workflow's `GITHUB_TOKEN`.
- **Allow GitHub Actions to create and approve pull requests** enabled under the repository's [Actions settings](https://github.com/Automattic/docspress/settings/actions).
- A scheduled or manually dispatched workflow, because WordPress editor saves do not directly start a GitHub workflow.

![GitHub Actions workflow permissions with Allow GitHub Actions to create and approve pull requests enabled](https://raw.githubusercontent.com/Automattic/docspress/main/theme/assets/images/github-actions/allow-actions-create-pull-requests.png "Enable the pull request setting and save it before asking DocsPress to create proposals.")

## Poll WordPress and propose Markdown

Use `mode: propose` when this workflow should only import WordPress changes:

<!-- wp:docspress/colorful-code {"language":"yaml","filename":".github/workflows/sync-docs.yml","code":"name: Propose WordPress documentation changes\n\non:\n  schedule:\n    - cron: \"3/5 * * * *\"\n  workflow_dispatch:\n\npermissions:\n  contents: write\n  pull-requests: write\n\nconcurrency:\n  group: docspress-sync\n  cancel-in-progress: false\n\njobs:\n  sync:\n    runs-on: ubuntu-latest\n    steps:\n      - uses: actions/checkout@11d5960a326750d5838078e36cf38b85af677262\n      - uses: Automattic/docspress@14d318924a81fb95ce4d3aaa9c3b547bf76b7768\n        with:\n          mode: propose\n          wordpress-site: example.wordpress.com\n          wordpress-access-token: ${{ secrets.WP_ACCESS_TOKEN }}\n          docs-dir: docs\n          root-slug: docs\n          pull-request-base: main\n          pull-request-branch: docspress/wordpress-sync\n          dry-run: false","highlightedLines":"3-6,8-10,23-24,28-31","showLineNumbers":true,"caption":"A scheduled proposal workflow reads Gutenberg changes and maintains one rolling pull request."} /-->

GitHub schedules can start later than the exact cron minute. Run `workflow_dispatch` when an editor needs an immediate proposal.

## Review the rolling pull request

DocsPress maintains one action-owned branch, `docspress/wordpress-sync`, instead of opening duplicate pull requests on every poll. Each run refreshes that branch from the latest base and updates the proposal.

The pull request uses a Conventional Commits title derived from its files, such as `docs(sync-and-rest-api): sync changes from WordPress`. Its description names the direction, lists changed Markdown files, and explains that the branch is managed and may be refreshed.

Review the Markdown diff exactly like any other documentation change. When it is correct, merge it. Do not commit unrelated work to the managed branch.

## Preserve the Markdown structure

DocsPress compares Gutenberg blocks semantically and rewrites only matching Markdown regions. Unchanged frontmatter, spacing, code-fence languages, tables, and custom-block comments stay intact. Supported core blocks become readable Markdown; attributed, preformatted, and unrecognized blocks stay as serialized Gutenberg comments when that is the only lossless representation.

Reverse synchronization updates the title and content of an existing managed Page. WordPress-created or deleted Pages and editor changes to slug, parent, or publication status remain outside reverse-sync scope.

## Combine both directions safely

Use `mode: reconcile` when the same workflow also has a `push` trigger for [GitHub-to-WordPress](github-to-wordpress.md) publication. Add this condition to the sync job so merging the managed proposal does not publish the same change back to WordPress:

```yaml
if: >-
  github.event_name != 'push' ||
  !contains(
    github.event.head_commit.message,
    format('from {0}/docspress/wordpress-sync', github.repository_owner)
  )
```

If `pull-request-branch` changes, use the same branch name in the condition. The Action also recognizes a managed merge internally and exits successfully with `skipped=true` when a caller omits the job condition.

<!-- wp:docspress/callout {"tone":"warning","title":"Two-sided edits require a decision","content":"<p>If Markdown and the WordPress Page both changed differently since their shared synchronization marker, DocsPress reports a conflict and writes neither side. Choose the intended version, align the other side, and run again.</p>","collapsible":false} /-->

The complete combined example lives in [Keep documentation synchronized](continuous-sync.md).
