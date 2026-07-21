---
title: Use manifests and redirects
---

Use a manifest when public routes or parents must remain independent of filenames. Use redirects when an old documentation path should remain discoverable after a move.

## Define a manifest

<!-- wp:docspress/colorful-code {"language":"json","filename":"docs/manifest.json","code":"{\n  \"pages\": [\n    { \"id\": \"root\", \"title\": \"Docs\", \"slug\": \"\", \"markdown_source\": \"index.md\" },\n    { \"id\": \"guides\", \"title\": \"Guides\", \"slug\": \"guides\" },\n    {\n      \"id\": \"start\",\n      \"title\": \"Start here\",\n      \"slug\": \"getting-started\",\n      \"parent\": \"guides\",\n      \"markdown_source\": \"getting-started/first-sync.md\"\n    }\n  ]\n}","highlightedLines":"3-4,9-11","showLineNumbers":true,"caption":"Manifest source paths resolve relative to the manifest file."} /-->

Set `manifest-file: docs/manifest.json`. Each entry needs a stable `id`; `parent` references another entry ID. Entries without `markdown_source` become managed placeholder Pages.

DocsPress stops on duplicate IDs, missing parents, parent cycles, invalid entries, or two entries that normalize to the same route.

## Preserve moved paths

<!-- wp:docspress/colorful-code {"language":"json","filename":"docs/redirects.json","code":"{\n  \"redirects\": {\n    \"old-start\": \"guides/getting-started\",\n    \"legacy/api\": \"https://developer.wordpress.org/rest-api/\"\n  }\n}","highlightedLines":"3-4","showLineNumbers":true,"caption":"Relative targets remain under root-slug; absolute URLs remain external."} /-->

Set `redirects-file: docs/redirects.json`. DocsPress creates a managed moved-page placeholder containing a link to the new destination.

<!-- wp:docspress/callout {"tone":"note","title":"Not an HTTP redirect","content":"<p>The moved-page placeholder is a WordPress Page with a destination link. It is not a server-level 301 response.</p>","collapsible":false} /-->

A redirect cannot replace the managed root and cannot occupy the same route as a real documentation Page.
