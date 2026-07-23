---
title: DocsPress documentation
sidebar_position: 0
sidebar_collapsed: false
---

DocsPress keeps documentation beside the code that explains it, then publishes that Markdown as native WordPress Pages and Gutenberg blocks.

## Choose a starting point

<!-- wp:docspress/audience-paths {"eyebrow":"Start here","title":"Where are your docs today?","description":"Choose the workflow that matches the current state of your repository.","paths":[{"title":"I already have Markdown docs","description":"Connect an existing docs folder to WordPress and begin with a safe draft sync.","url":"/docs/publish-existing-docs/","cta":"Publish existing docs","icon":"MD","accent":"blue","newTab":false},{"title":"I need to create docs","description":"Generate source-grounded documentation with AI, review it, then publish it.","url":"/docs/create-docs-with-ai/","cta":"Create docs with AI","icon":"AI","accent":"gold","newTab":false}],"columns":2,"tone":"theme","textAlign":"left","showNumbers":false} /-->

Both paths use repository-aware skills and end with the same reviewed Markdown-to-WordPress workflow. The difference is whether a usable documentation tree already exists.

## Install DocsPress with your coding agent

Install both skills into the repository that owns the documentation:

<!-- wp:docspress/terminal-session {"title":"Install DocsPress skills","shell":"bash","prompt":"$","command":"npx skills add Automattic/docspress --all --full-depth\nnpx skills list","output":""} /-->

Then follow [Publish existing docs](publish-existing-docs/index.md) or [Create docs with AI](create-docs-with-ai/index.md). Each path provides a focused, copy-ready agent prompt.

## How DocsPress works

<!-- wp:docspress/file-tree {"root":"your-repository/","tree":".claude/\n  skills/\n    docspress-install/\n    generate-docs-from-source/\n.github/\n  workflows/\n    sync-docs.yml\ndocs/\n  index.md\n  publish-existing-docs/\n    index.md\n    first-sync.md\n  create-docs-with-ai/\n    index.md\n    review-and-publish.md","caption":"Choose a starting workflow, then publish the reviewed Markdown tree as WordPress Pages."} /-->

<!-- wp:list {"ordered":true} -->
<ol class="wp-block-list"><!-- wp:list-item -->
<li>Authors and agents can update Markdown under <code>docs/</code>, while editors can update existing managed Pages in Gutenberg.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>The GitHub Action converts Markdown into Gutenberg blocks and Gutenberg changes back into focused Markdown edits.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>DocsPress compares both versions with their shared management marker.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>GitHub-only changes update WordPress; WordPress-only changes open a pull request; two-sided changes stop as conflicts.</li>
<!-- /wp:list-item --></ol>
<!-- /wp:list -->

Read [Publish existing docs](publish-existing-docs/index.md) for the safe synchronization sequence, [Create docs with AI](create-docs-with-ai/index.md) when documentation must be generated first, or [Authoring documentation](authoring/index.md) for the Markdown contract.

See [Why DocsPress?](why-docspress.md) for a practical comparison with Docusaurus and the cases where keeping WordPress as the publishing surface removes an entire parallel docs stack.

## Keep documentation synchronized

Prove the connection with `workflow_dispatch`, `status: draft`, and `dry-run: true`. After the dry run and draft Page tree are approved, add this path-scoped trigger:

<!-- wp:docspress/colorful-code {"language":"yaml","filename":".github/workflows/sync-docs.yml","code":"on:\n  push:\n    branches: [main]\n    paths:\n      - \"docs/**/*.md\"\n      - \"docs/**/*.markdown\"\n      - \"docs/**/*.json\"\n      - \".github/workflows/sync-docs.yml\"\n  workflow_dispatch:","highlightedLines":"2-8","showLineNumbers":true,"caption":"Once approved, documentation changes on the default branch can synchronize automatically."} /-->

<!-- wp:docspress/result {"status":"success","title":"One source of truth","content":"<p>Every merged documentation change can flow from GitHub to the same WordPress Page hierarchy without maintaining a second copy.</p>","meta":"Markdown → Gutenberg → WordPress"} /-->

Read [GitHub to WordPress](guides/github-to-wordpress.md) and [WordPress to GitHub](guides/wordpress-to-github.md) separately, or follow the complete [continuous synchronization guide](guides/continuous-sync.md) before enabling automatic writes.

## Explore the documentation

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><a href="/docs/publish-existing-docs/">Publish existing docs</a>: connect a Markdown tree, authenticate, and run the first safe sync.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="/docs/create-docs-with-ai/">Create docs with AI</a>: generate a source-grounded documentation tree, review it, and hand it to the publishing workflow.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="/docs/why-docspress/">Why DocsPress?</a>: compare the WordPress-native model with a Docusaurus static site.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="/docs/authoring/">Authoring</a>: structure pages and use Markdown or DocsPress Gutenberg blocks.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="/docs/guides/">Guides</a>: synchronize in either direction, prevent merge loops, and control routes with manifests or redirects.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="/docs/reference/">Reference</a>: Action inputs, CLI behavior, REST reconciliation, theme, and block schemas.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><a href="/docs/troubleshooting/">Troubleshooting</a>: diagnose authentication, conflicts, links, and workflow failures.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->
