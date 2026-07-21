---
title: Kitchen Sink
---

This Page is the acceptance surface for the DocsPress theme and all eight documentation blocks. Switch design presets and light/dark mode while checking spacing, typography, borders, interactions, and copy controls.

<!-- wp:docspress/callout {"tone":"note","title":"Playground acceptance page","content":"<p>The local Playground appends its live component inventory to this source-backed Page after seeding it.</p>","collapsible":false} /-->

## Colorful Code

<!-- wp:docspress/colorful-code {"language":"yaml","filename":".github/workflows/sync-docs.yml","code":"name: Sync docs\non:\n  push:\n    paths: [\"docs/**\"]\nsteps:\n  - uses: Automattic/docspress@COMMIT_SHA","highlightedLines":"2-4,6","showLineNumbers":true,"caption":"Filename, language, line numbers, highlighted ranges, caption, and copy."} /-->

<!-- wp:docspress/colorful-code {"language":"plaintext","filename":"Without line numbers","code":"Markdown in.\nWordPress out.","highlightedLines":"","showLineNumbers":false,"caption":"Plain text with line numbers disabled."} /-->

## Code Tabs

<!-- wp:docspress/code-tabs {"tabs":[{"label":"npm","language":"bash","filename":"Terminal","code":"npm install docspress"},{"label":"pnpm","language":"bash","filename":"Terminal","code":"pnpm add docspress"},{"label":"Yarn","language":"bash","filename":"Terminal","code":"yarn add docspress"},{"label":"Bun","language":"bash","filename":"Terminal","code":"bun add docspress"},{"label":"JavaScript","language":"javascript","filename":"example.js","code":"console.log('DocsPress');"},{"label":"PHP","language":"php","filename":"example.php","code":"<?php echo 'DocsPress';"},{"label":"Python","language":"python","filename":"example.py","code":"print('DocsPress')"},{"label":"JSON","language":"json","filename":"example.json","code":"{ \"name\": \"DocsPress\" }"}],"showLineNumbers":false,"caption":"The maximum eight compact tabs with independent labels, languages, filenames, and code."} /-->

## Callouts

<!-- wp:docspress/callout {"tone":"note","title":"Note","content":"<p>Neutral context that belongs beside the current step.</p>","collapsible":false} /-->

<!-- wp:docspress/callout {"tone":"tip","title":"Tip","content":"<p>A useful shortcut or recommended practice.</p>","collapsible":false} /-->

<!-- wp:docspress/callout {"tone":"warning","title":"Warning","content":"<p>A condition readers should check before continuing.</p>","collapsible":false} /-->

<!-- wp:docspress/callout {"tone":"danger","title":"Danger","content":"<p>A destructive or security-sensitive action.</p>","collapsible":false} /-->

<!-- wp:docspress/callout {"tone":"success","title":"Success","content":"<p>A confirmed positive state or completed milestone.</p>","collapsible":false} /-->

<!-- wp:docspress/callout {"tone":"note","title":"Collapsible and open","content":"<p>Readers can hide this longer explanation.</p>","collapsible":true,"open":true} /-->

<!-- wp:docspress/callout {"tone":"tip","title":"Collapsible and closed","content":"<p>This content begins hidden and remains keyboard accessible.</p>","collapsible":true,"open":false} /-->

## API Request / Response

<!-- wp:docspress/api-request {"method":"GET","endpoint":"/wp-json/wp/v2/pages?per_page=2","headers":"Accept: application/json","requestBody":"","requestBodyFormat":"json","responseStatus":"200 OK","responseBody":"[{ \"id\": 41 }, { \"id\": 42 }]","responseBodyFormat":"json"} /-->

<!-- wp:docspress/api-request {"method":"POST","endpoint":"/wp-json/wp/v2/pages","headers":"Content-Type: application/json\nAuthorization: Bearer $WP_ACCESS_TOKEN","requestBody":"{\n  \"title\": \"API reference\",\n  \"status\": \"draft\"\n}","requestBodyFormat":"json","responseStatus":"201 Created","responseBody":"{\n  \"id\": 43,\n  \"status\": \"draft\"\n}","responseBodyFormat":"json"} /-->

<!-- wp:docspress/api-request {"method":"PUT","endpoint":"/wp-json/wp/v2/pages/43","headers":"Content-Type: application/x-www-form-urlencoded","requestBody":"title=REST+API+Reference","requestBodyFormat":"raw","responseStatus":"200 OK","responseBody":"Updated page 43: REST API Reference","responseBodyFormat":"raw"} /-->

<!-- wp:docspress/api-request {"method":"PATCH","endpoint":"/wp-json/wp/v2/pages/43","headers":"Content-Type: application/json","requestBody":"{ \"status\": \"publish\" }","requestBodyFormat":"json","responseStatus":"200 OK","responseBody":"{ \"id\": 43, \"status\": \"publish\" }","responseBodyFormat":"json"} /-->

<!-- wp:docspress/api-request {"method":"DELETE","endpoint":"/wp-json/wp/v2/pages/43?force=true","headers":"Authorization: Bearer $WP_ACCESS_TOKEN","requestBody":"","requestBodyFormat":"raw","responseStatus":"204 No Content","responseBody":"","responseBodyFormat":"raw"} /-->

## Terminal Session

<!-- wp:docspress/terminal-session {"title":"Run package verification","shell":"bash","prompt":"$","command":"npm run package","output":"Lint passed\nTests passed\nAction bundle rebuilt"} /-->

<!-- wp:docspress/terminal-session {"title":"Inspect the site","shell":"wp-cli","prompt":">","command":"wp option get docspress_playground_runtime --format=json","output":""} /-->

## Result

<!-- wp:docspress/result {"status":"success","title":"All checks passed","content":"<p>The generated Pages match the repository tree.</p>","meta":"20 pages"} /-->

<!-- wp:docspress/result {"status":"neutral","title":"No changes required","content":"<p>WordPress already matches the current commit.</p>","meta":"0 updates"} /-->

<!-- wp:docspress/result {"status":"warning","title":"Drafts need review","content":"<p>Three new Pages are waiting for editorial approval.</p>","meta":"3 drafts"} /-->

<!-- wp:docspress/result {"status":"error","title":"Synchronization failed","content":"<p>The access token cannot create Pages on this site.</p>","meta":"HTTP 403"} /-->

## File Tree

<!-- wp:docspress/file-tree {"root":"Automattic/docspress/","tree":".claude/\n  skills/\ndocs/\n  index.md\n  getting-started/\n  reference/\n    kitchen-sink.md\nplugins/\n  docspress-blocks/\ntheme/\npackage.json","caption":"Nested folders, files, a custom root label, and caption."} /-->

<!-- wp:docspress/file-tree {"root":"docs/","tree":"index.md\nwhy-docspress.md\ntroubleshooting.md","caption":"A compact tree."} /-->

## Prompt

<!-- wp:docspress/prompt {"prompt":"Explain DocsPress to a new contributor in three short paragraphs.","model":"GPT-5","mode":"chat","thinking":false,"context":"@documentation, https://github.com/Automattic/docspress","caption":"Contributor explainer"} /-->

<!-- wp:docspress/prompt {"prompt":"Review the synchronization function for race conditions. Return risks first, then the smallest safe patch.","model":"Claude Sonnet","mode":"code","thinking":true,"context":"@repository, src/sync.js, test/sync.test.js, #trace","caption":"Race-condition review"} /-->

<!-- wp:docspress/prompt {"prompt":"Which Action inputs affect the generated WordPress Page hierarchy?","model":"Gemini Pro","mode":"ask","thinking":false,"context":"action.yml, src/docs.js","caption":"Configuration question"} /-->

<!-- wp:docspress/prompt {"prompt":"Use $docspress-install to create a phased migration plan from a static documentation site to DocsPress.","model":"Planning agent","mode":"plan","thinking":true,"context":"$docspress-install, docs/, #architecture, https://example.com/current-docs","caption":"Migration plan with a skill reference"} /-->

<!-- wp:docspress/result {"status":"success","title":"Kitchen Sink complete","content":"<p>Every DocsPress block, semantic variant, and meaningful option is represented on this Page.</p>","meta":"8 custom blocks"} /-->
