---
title: Install the agent skills
---

Install both repository-aware skills so an agent can choose between existing documentation and verified generation from source.

## Install the skills

<!-- wp:docspress/terminal-session {"title":"Install DocsPress skills","shell":"bash","prompt":"$","command":"npx skills add Automattic/docspress --all --full-depth\nnpx skills list","output":""} /-->

The CLI discovers both nested skills, installs them for the supported project agents, and records the source in `skills-lock.json`. Commit the generated skill files and lock file with the repository so future agents receive the same repeatable contract instead of a one-time prompt hidden in chat history.

## Ask the agent to inspect first

<!-- wp:docspress/prompt {"prompt":"Use $docspress-install for this repository. Inspect the current docs tree and workflows before changing anything. Reuse existing Markdown. If no usable docs exist, invoke $generate-docs-from-source. Prepare a manual draft dry run and report every external action that still needs approval.","model":"Coding agent","mode":"code","thinking":true,"context":"$docspress-install, $generate-docs-from-source, @repository, skills-lock.json, docs/, .github/workflows/","caption":"Installation prompt for a repository-aware coding agent."} /-->

The skills instruct the agent to preserve unrelated worktree changes, derive claims from code and tests, use the DocsPress Gutenberg blocks correctly, avoid plaintext credentials, and stage WordPress writes behind explicit approvals.

## Verify the installation

Confirm the project installation through the same CLI:

<!-- wp:docspress/terminal-session {"title":"Verify DocsPress skills","shell":"bash","prompt":"$","command":"npx skills list","output":""} /-->

<!-- wp:docspress/result {"status":"success","title":"The repository can teach the agent","content":"<p>The next agent can discover the same installation, authoring, validation, and safety workflow from versioned files.</p>","meta":"2 skills installed"} /-->
