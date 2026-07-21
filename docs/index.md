---
title: DocsPress documentation
---

DocsPress keeps documentation beside the code that explains it, then publishes that Markdown as native WordPress Pages and Gutenberg blocks.

<!-- wp:docspress/callout {"tone":"tip","title":"The shortest path","content":"<p>Give your coding agent the DocsPress skills. It can inspect an existing repository, generate missing documentation from verified source, and prepare the WordPress synchronization workflow.</p>","collapsible":false} /-->

## Install DocsPress with your coding agent

Install both skills into the repository that owns the documentation:

<!-- wp:docspress/terminal-session {"title":"Install DocsPress skills","shell":"bash","prompt":"$","command":"npx skills add Automattic/docspress --all --full-depth\nnpx skills list","output":""} /-->

Then give the agent this prompt:

<!-- wp:docspress/prompt {"prompt":"Use $docspress-install to inspect this repository and publish its existing Markdown documentation with DocsPress. If no usable documentation exists, use $generate-docs-from-source to derive a comprehensive docs/ tree from source code and tests first. Configure a manual draft dry run, preserve unrelated changes, and never expose credentials.","model":"Coding agent","mode":"code","thinking":true,"context":"$docspress-install, $generate-docs-from-source, @repository, skills-lock.json, docs/, .github/workflows/","caption":"Copy this prompt into Claude Code, Codex, or another repository-aware agent."} /-->

If the repository already has Markdown docs, the installer reuses them. If it does not, the generator builds a verified `docs/` hierarchy before handing publication back to the installer.

## How DocsPress works

<!-- wp:docspress/file-tree {"root":"your-repository/","tree":".claude/\n  skills/\n    docspress-install/\n    generate-docs-from-source/\n.github/\n  workflows/\n    sync-docs.yml\ndocs/\n  index.md\n  getting-started/\n    index.md\n    first-sync.md","caption":"Markdown stays in GitHub; the workflow mirrors its hierarchy to WordPress Pages."} /-->

1. Authors and agents update Markdown under `docs/`.
2. The GitHub Action converts Markdown into Gutenberg-compatible block content.
3. DocsPress compares the desired tree with Pages carrying its hidden management sentinel.
4. WordPress creates, updates, or trashes only managed Pages.

Read [Getting started](getting-started/index.md) for the safe first-run sequence or [Authoring documentation](authoring/index.md) for the Markdown contract.

See [Why DocsPress?](why-docspress.md) for a practical comparison with Docusaurus and the cases where keeping WordPress as the publishing surface removes an entire parallel docs stack.

## Keep documentation synchronized

Prove the connection with `workflow_dispatch`, `status: draft`, and `dry-run: true`. After the dry run and draft Page tree are approved, add this path-scoped trigger:

<!-- wp:docspress/colorful-code {"language":"yaml","filename":".github/workflows/sync-docs.yml","code":"on:\n  push:\n    branches: [main]\n    paths:\n      - \"docs/**/*.md\"\n      - \"docs/**/*.markdown\"\n      - \"docs/**/*.json\"\n      - \".github/workflows/sync-docs.yml\"\n  workflow_dispatch:","highlightedLines":"2-8","showLineNumbers":true,"caption":"Once approved, documentation changes on the default branch can synchronize automatically."} /-->

<!-- wp:docspress/result {"status":"success","title":"One source of truth","content":"<p>Every merged documentation change can flow from GitHub to the same WordPress Page hierarchy without maintaining a second copy.</p>","meta":"Markdown → Gutenberg → WordPress"} /-->

Follow the complete [continuous synchronization guide](guides/continuous-sync.md) before enabling automatic writes.

## Explore the documentation

- [Getting started](getting-started/index.md): install the skills, authenticate, and run the first safe sync.
- [Why DocsPress?](why-docspress.md): compare the WordPress-native model with a Docusaurus static site.
- [Authoring](authoring/index.md): structure pages and use Markdown or DocsPress Gutenberg blocks.
- [Guides](guides/index.md): automate synchronization and control routes with manifests or redirects.
- [Reference](reference/index.md): Action inputs, CLI behavior, REST reconciliation, theme, and block schemas.
- [Troubleshooting](troubleshooting.md): diagnose authentication, conflicts, links, and workflow failures.
