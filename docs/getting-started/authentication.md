---
title: Authenticate WordPress
---

DocsPress sends a Bearer token to the WordPress Pages REST endpoint. Store the token as the GitHub Actions secret `WP_ACCESS_TOKEN`.

## WordPress.com

Create an application at [WordPress.com Apps](https://developer.wordpress.com/apps/) with this callback URL:

```text
http://localhost:8787/callback
```

The current helper requests the WordPress.com `global` scope. Run the exact trusted package version locally and use `--set-secret` so the token is written to GitHub without being printed:

<!-- wp:docspress/colorful-code {"language":"bash","filename":"Trusted local terminal","code":"printf \"WordPress.com client secret: \"\nIFS= read -r -s DOCSPRESS_CLIENT_SECRET\nprintf \"\\n\"\nnpx docspress@0.2.0 token \\\n  --client-id YOUR_CLIENT_ID \\\n  --client-secret \"$DOCSPRESS_CLIENT_SECRET\" \\\n  --site example.wordpress.com \\\n  --repo OWNER/REPO \\\n  --set-secret\nunset DOCSPRESS_CLIENT_SECRET","highlightedLines":"2,5-10","showLineNumbers":true,"caption":"The masked prompt keeps the client secret out of shell history; the helper stores WP_ACCESS_TOKEN through GitHub CLI."} /-->

<!-- wp:docspress/callout {"tone":"danger","title":"Run this yourself","content":"<p>The helper currently passes the client secret as a process argument. Use a trusted local machine, avoid screen or session recording, and do not ask an agent to capture the command or its output.</p>","collapsible":true,"open":false} /-->

Verify only the secret name:

```bash
gh secret list --repo OWNER/REPO
```

## Self-hosted WordPress

Set `wordpress-url` to the site origin, without `/wp-json`. DocsPress will call `/wp-json/wp/v2/pages` and send `Authorization: Bearer …`.

Core WordPress does not create that Bearer token for DocsPress. Configure a trusted authentication mechanism that accepts it, then store the resulting token as `WP_ACCESS_TOKEN`.

<!-- wp:docspress/callout {"tone":"warning","title":"The Action input is still required","content":"<p>Supply <code>wordpress-site</code> in the workflow even in self-hosted mode because the Action metadata marks it required. The self-hosted REST URL itself is derived from <code>wordpress-url</code>.</p>","collapsible":false} /-->

Continue with [the first synchronization](first-sync.md).
