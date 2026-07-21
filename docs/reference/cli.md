---
title: Token helper CLI
---

The `docspress` npm executable currently provides one command: `token`.

## Top-level help

<!-- wp:docspress/terminal-session {"title":"DocsPress CLI","shell":"bash","prompt":"$","command":"npx docspress@0.2.0 --help","output":"Docspress CLI\n\nUsage:\n  docspress token --client-id CLIENT_ID --client-secret CLIENT_SECRET --site fkadev.blog --repo f/docspress-demo\n\nCommands:\n  token    Create and optionally store a WordPress.com OAuth token."} /-->

Unknown commands exit with an error. The CLI does not publish documentation from a local command; synchronization runs through the GitHub Action.

## Token options

| Option | Required | Purpose |
| --- | --- | --- |
| `--client-id` | yes | WordPress.com application client ID. |
| `--client-secret` | yes | WordPress.com application client secret. Keep it outside chat and shell history. |
| `--site` | no | Site domain or ID supplied to the authorization screen. |
| `--scope` | no | OAuth scope; defaults to `global`. |
| `--redirect-uri` | no | Local HTTP callback; defaults to `http://localhost:8787/callback`. |
| `--repo` | required with `--set-secret` | GitHub repository in `OWNER/REPO` form. |
| `--set-secret` | no | Stores the access token as `WP_ACCESS_TOKEN` through GitHub CLI instead of printing it. |

The redirect must use `http` and the hostname must be `localhost` or `127.0.0.1`. The helper generates and validates OAuth state before exchanging the authorization code.

<!-- wp:docspress/callout {"tone":"danger","title":"Do not let an agent observe token output","content":"<p>Without <code>--set-secret</code>, the helper prints the access token and a secret-setting command. Use <code>--set-secret</code> in a trusted local terminal.</p>","collapsible":false} /-->

See [Authenticate WordPress](../getting-started/authentication.md) for the recommended invocation.
