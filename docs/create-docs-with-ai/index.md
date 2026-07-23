---
title: Create docs with AI
sidebar_position: 20
sidebar_collapsed: true
---

Build a verified Markdown documentation tree from the repository itself, review it, and then publish it through the same safe DocsPress workflow.

<!-- wp:docspress/prompt {"prompt":"Use $generate-docs-from-source to inspect this repository and create verified DocsPress-compatible documentation from its source code and tests. Preserve useful existing docs, show me the proposed docs tree, and do not publish anything yet.","model":"Coding agent","mode":"code","thinking":true,"context":"$generate-docs-from-source, @repository, src/, test/, docs/","caption":"Use this path when the repository has incomplete, stale, or no usable documentation."} /-->

If the repository already has a usable Markdown tree, skip generation and [publish the existing docs](../publish-existing-docs/index.md).

## What this path does

The generation workflow asks a repository-aware coding agent to:

1. Inventory public APIs, commands, configuration, tests, examples, and existing documentation.
2. Build a coverage map that ties every documentation claim to repository evidence.
3. Create or improve a navigable `docs/` hierarchy.
4. Use DocsPress Gutenberg blocks only when their semantics improve the page.
5. Validate routes, links, examples, block attributes, and documented behavior.
6. Hand the reviewed tree to `$docspress-install` for a manual draft dry run.

<!-- wp:docspress/callout {"tone":"note","title":"Generation and publication are separate decisions","content":"<p>Creating Markdown changes only the repository. Publishing Pages, activating WordPress components, pushing commits, and dispatching workflows remain separate approval boundaries.</p>","collapsible":false} /-->

## Continue

- [Generate documentation from source](generate-from-source.md).
- [Review the generated tree and publish safely](review-and-publish.md).

<!-- wp:docspress/result {"status":"neutral","title":"Start with evidence, not a template","content":"<p>The agent derives the documentation structure from the project’s real public surface and tests before writing pages.</p>","meta":"source → coverage map → Markdown → review"} /-->
