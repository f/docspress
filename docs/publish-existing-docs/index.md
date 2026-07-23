---
title: Publish existing docs
sidebar_position: 10
sidebar_collapsed: true
---

Connect an existing Markdown documentation tree to WordPress without allowing an unreviewed workflow run to publish Pages or open pull requests.

<!-- wp:docspress/prompt {"prompt":"Use $docspress-install to inspect this repository, reuse its existing Markdown documentation, and prepare a safe draft synchronization to WordPress.","model":"Coding agent","mode":"code","thinking":true,"context":"$docspress-install, @repository, docs/, .github/workflows/","caption":"Use this path when the repository already has usable Markdown documentation."} /-->

If the repository does not have a usable docs tree yet, begin with [Create docs with AI](../create-docs-with-ai/index.md) instead.

## Prerequisites

- A GitHub repository containing or ready to receive `docs/`.
- A WordPress.com site or a self-hosted WordPress site whose Pages endpoint accepts the supplied Bearer token.
- Permission to add a repository workflow and the `WP_ACCESS_TOKEN` Actions secret.
- The DocsPress Blocks plugin when documentation uses `wp:docspress/*` custom blocks.
- The DocsPress theme when you want the bundled documentation reading experience.

<!-- wp:docspress/callout {"tone":"warning","title":"Keep credentials outside the repository","content":"<p>Never commit an OAuth client secret or WordPress access token. The workflow reads only <code>secrets.WP_ACCESS_TOKEN</code>.</p>","collapsible":false} /-->

## Recommended sequence

1. [Install the agent skills](install-agent-skills.md) and ask the agent to reuse the existing docs directory.
2. [Authenticate WordPress](authentication.md).
3. [Run the first synchronization](first-sync.md) manually with `mode: reconcile`, drafts, and dry-run enabled.
4. Inspect the Action summary, expected Page hierarchy, and proposed Markdown files.
5. Approve a real draft write and review any WordPress-to-GitHub pull request.
6. Enable [GitHub-to-WordPress](../guides/github-to-wordpress.md), [WordPress-to-GitHub](../guides/wordpress-to-github.md), or combined [continuous synchronization](../guides/continuous-sync.md) only after the manual lifecycle succeeds.

<!-- wp:docspress/result {"status":"neutral","title":"Existing docs stay in control","content":"<p>The starting workflow is manual, dry-run only, and targets draft Pages. WordPress publication and GitHub pull requests remain separate approval decisions.</p>","meta":"existing Markdown · dry-run · draft"} /-->
