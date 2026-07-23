---
title: Generate documentation from source
sidebar_position: 10
---

Turn repository evidence into a DocsPress-compatible Markdown tree without inventing commands, behavior, or support guarantees.

## 1. Install the repository skills

Install both DocsPress skills so generation can hand off to publication after review:

<!-- wp:docspress/terminal-session {"title":"Install DocsPress skills","shell":"bash","prompt":"$","command":"npx skills add Automattic/docspress --all --full-depth\nnpx skills list","output":""} /-->

## 2. Ask for an evidence pass first

<!-- wp:docspress/prompt {"prompt":"Use $generate-docs-from-source to inspect the public surface of this repository. Build a coverage map from package metadata, exports, commands, configuration reads, tests, examples, and existing docs. Show the proposed docs tree before writing pages, and identify any contradictions instead of guessing.","model":"Coding agent","mode":"plan","thinking":true,"context":"$generate-docs-from-source, @repository, package.json, src/, test/, docs/","caption":"Separate repository research from documentation writing."} /-->

The agent should treat tests and executable examples as stronger evidence than comments. It should preserve useful existing documentation and avoid documenting private helpers as public APIs.

## 3. Generate the smallest complete tree

A typical project may need:

<!-- wp:docspress/file-tree {"root":"docs/","tree":"index.md\ngetting-started/\n  index.md\n  installation.md\n  configuration.md\nguides/\n  first-workflow.md\nreference/\n  api.md\n  cli.md\ntroubleshooting.md","caption":"The source determines which pages exist; this is a shape, not a required template."} /-->

Each page should have one clear outcome, verified examples, relative links, and a stable route. Use ordinary Markdown for ordinary prose. Use DocsPress blocks for prompts, terminal sessions, API exchanges, callouts, file trees, code alternatives, and verification results when those semantics are useful.

## 4. Keep publication disabled

The generation pass may prepare a DocsPress workflow when one is missing, but it should start with `workflow_dispatch`, `status: draft`, and `dry-run: true`.

<!-- wp:docspress/callout {"tone":"warning","title":"Do not publish generated claims before review","content":"<p>Generation completes when the Markdown tree is evidence-backed and validated. WordPress authentication, Page writes, repository pushes, and public publication require their own approvals.</p>","collapsible":false} /-->

Continue with [Review and publish](review-and-publish.md).
