---
title: Review and publish generated docs
sidebar_position: 20
---

Verify the generated Markdown against the repository, then hand the accepted tree to the normal DocsPress publishing workflow.

## Review the generated tree

Confirm:

- Every page is nonempty and has a unique route and title.
- Relative links and local images resolve from the file containing them.
- Commands, options, exports, environment variables, defaults, and errors match source or tests.
- Examples are runnable, or clearly marked when execution is not practical.
- Every `wp:docspress/*` attribute object is valid JSON and uses supported attributes.
- No placeholder, credential, private helper, or unsupported claim slipped into public documentation.

<!-- wp:docspress/prompt {"prompt":"Review the generated docs against this repository. Check every public API, command, configuration name, example, relative link, and DocsPress block attribute. Return incorrect or unsupported claims first, then make only evidence-backed documentation fixes.","model":"Coding agent","mode":"code","thinking":true,"context":"$generate-docs-from-source, @repository, docs/, src/, test/","caption":"Run a source-backed editorial review before publication."} /-->

## Run repository checks

Use the project’s real scripts in proportion to the change. At minimum, validate links and block JSON, run the relevant tests or examples, and check the final diff.

<!-- wp:docspress/terminal-session {"title":"Check the documentation diff","shell":"bash","prompt":"$","command":"git diff --check\ngit status --short","output":""} /-->

## Hand off to publication

Once the generated tree is accepted, continue through [Publish existing docs](../publish-existing-docs/index.md). The generated Markdown is now the existing documentation tree for `$docspress-install`.

1. [Install or verify the agent skills](../publish-existing-docs/install-agent-skills.md).
2. [Authenticate WordPress](../publish-existing-docs/authentication.md).
3. [Run the first synchronization](../publish-existing-docs/first-sync.md) as a manual draft dry run.

<!-- wp:docspress/result {"status":"success","title":"Generated docs are ready for a safe preview","content":"<p>The reviewed Markdown tree can now enter the same dry-run and draft lifecycle as any hand-authored documentation.</p>","meta":"reviewed Markdown → draft WordPress Pages"} /-->
