---
title: Synchronization and REST API
---
DocsPress builds a desired Page model from Markdown, lists existing WordPress Pages, and reconciles only the Pages carrying a valid management sentinel.

## WordPress endpoints TEST

For WordPress.com, the Pages collection is:

<!-- wp:code {"tokenizedLines":[[["aHR0cHM6Ly9wdWJsaWMtYXBpLndvcmRwcmVzcy5jb20vd3AvdjIvc2l0ZXMve3NpdGV9L3BhZ2Vz"]]]} -->
<pre class="wp-block-code"><code>https://public-api.wordpress.com/wp/v2/sites/{site}/pages</code></pre>
<!-- /wp:code -->

For self-hosted WordPress, it is:

<!-- wp:code {"tokenizedLines":[[["e3dvcmRwcmVzcy11cmx9L3dwLWpzb24vd3AvdjIvcGFnZXM="]]]} -->
<pre class="wp-block-code"><code>{wordpress-url}/wp-json/wp/v2/pages</code></pre>
<!-- /wp:code -->

Listing requests use `context=edit`, `status=any`, and pages of 100 records until `x-wp-totalpages` is exhausted.

<!-- wp:docspress/api-request {"method":"POST","headers":"Accept: application/json\nContent-Type: application/json\nAuthorization: Bearer $WP_ACCESS_TOKEN","requestBody":"{\n  \u0022title\u0022: \u0022Getting started\u0022,\n  \u0022slug\u0022: \u0022getting-started\u0022,\n  \u0022status\u0022: \u0022draft\u0022,\n  \u0022parent\u0022: 42,\n  \u0022content\u0022: \u0022\u003c!\u002d\u002d docspress:{...} \u002d\u002d\u003e\u005cn\u003c!\u002d\u002d wp:paragraph \u002d\u002d\u003e...\u0022\n}","responseStatus":"201 Created","responseBody":"{\n  \u0022id\u0022: 43,\n  \u0022slug\u0022: \u0022getting-started\u0022,\n  \u0022status\u0022: \u0022draft\u0022,\n  \u0022parent\u0022: 42\n}"} /-->

Updates use `POST /pages/{id}`. Deletions use `DELETE /pages/{id}` and add `force=true` only for `delete-mode: force`.

## Management sentinel

Every generated Page starts with a hidden comment containing version, Page key, source path, and content hash:

<!-- wp:code {"tokenizedLines":[[["PCEtLSBkb2NzcHJlc3M6eyJ2ZXJzaW9uIjoxLCJrZXkiOiJkb2NzL2dldHRpbmctc3RhcnRlZCIsInNvdXJjZSI6ImRvY3MvZ2V0dGluZy1zdGFydGVkLm1kIiwiaGFzaCI6IuKApiJ9IC0tPg=="]]]} -->
<pre class="wp-block-code"><code>&lt;!-- docspress:{&#34;version&#34;:1,&#34;key&#34;:&#34;docs/getting-started&#34;,&#34;source&#34;:&#34;docs/getting-started.md&#34;,&#34;hash&#34;:&#34;…&#34;} --&gt;</code></pre>
<!-- /wp:code -->

The hash covers the Page key, source, title, slug, parent key, status, and converted body. A content, route, hierarchy, source, or status change therefore schedules an update.

In `propose` and `reconcile` modes, the same hash acts as a common ancestor. DocsPress computes the current GitHub and live WordPress states against that ancestor before it performs any write.

## Reconciliation order

Desired Pages are sorted by depth and key so parents are available before children. Existing Pages are indexed by their full parent path and by sentinel key.

<!-- wp:table {"hasFixedLayout":false} -->
<figure class="wp-block-table"><table><thead><tr><th>Condition</th><th>Operation</th></tr></thead><tbody><tr><td>No Page at the desired path</td><td>Create</td></tr><tr><td>Managed Page with changed hash or parent</td><td>Update</td></tr><tr><td>Managed Page with matching hash and parent</td><td>Unchanged</td></tr><tr><td>Unmanaged Page already using the path</td><td>Conflict; do not write</td></tr><tr><td>Managed Page below <code>root-slug</code> absent from desired docs</td><td>Trash or permanently delete</td></tr><tr><td>Desired child whose parent is unavailable</td><td>Conflict</td></tr></tbody></table></figure>
<!-- /wp:table -->

For bidirectional runs, a GitHub-only change is published, a WordPress-only title or content change becomes a pull request, and matching current states refresh the sentinel after that pull request merges. If both current states differ from the sentinel and from each other, the run fails before writes. WordPress-created or deleted Pages and WordPress slug, parent, or status changes are intentionally outside reverse-sync scope.

<!-- wp:docspress/callout {"tone":"success","title":"Manual Pages are protected","content":"\u003cp\u003eAn unmanaged Page collision fails the Action instead of overwriting content that was created outside DocsPress.\u003c/p\u003e"} /-->

## Dry-run behavior

Dry-run performs discovery, conversion, Page listing, comparison, conflict detection, deletion planning, and reverse Markdown generation. It assigns synthetic parent IDs for planned creates but does not call WordPress or GitHub write endpoints.

## API errors

DocsPress surfaces the WordPress error message. When WordPress.com reports that `global` scope is required, the error adds a hint to regenerate `WP_ACCESS_TOKEN` with the token helper.
