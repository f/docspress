# Docspress

Docspress is a GitHub Action that syncs Markdown docs from a repository into WordPress Pages as Gutenberg-compatible block content.

Markdown is the source of truth. Docspress only updates or deletes WordPress pages that contain its own hidden sentinel comment, so existing manually-created WordPress pages are protected and reported as conflicts.

## Usage

```yaml
name: Sync docs to WordPress

on:
  push:
    branches: [main]
    paths:
      - "docs/**/*.md"
      - ".github/workflows/sync-docs.yml"
  workflow_dispatch:

jobs:
  sync:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: f/docspress@main
        with:
          wordpress-site: fkadev.blog
          wordpress-access-token: ${{ secrets.WP_ACCESS_TOKEN }}
          docs-dir: docs
          root-slug: docs
          root-title: Docs
          status: draft
          dry-run: true
```

## Inputs

| Input | Default | Description |
| --- | --- | --- |
| `wordpress-url` | `https://public-api.wordpress.com` | WordPress API base URL. |
| `wordpress-site` | required | WordPress.com site ID or domain, such as `fkadev.blog`. |
| `wordpress-access-token` | required | OAuth bearer token that can edit pages. |
| `docs-dir` | `docs` | Markdown docs directory. |
| `root-slug` | `docs` | Managed root page slug. |
| `root-title` | `Docs` | Managed root page title when no root `index.md` exists. |
| `status` | `publish` | Status for created or updated pages. |
| `delete-mode` | `trash` | Use `trash` or `force` for removed Markdown files. |
| `dry-run` | `false` | Plan changes without writing to WordPress. |

## WordPress.com authentication

WordPress.com API writes require an OAuth bearer token. Create that token on WordPress.com, then store it as a GitHub Actions secret.

For personal projects or demos that only access your own WordPress.com site, use the authorization-code flow. This works with two-factor authentication because you authorize in the browser instead of sending your WordPress.com password to the token endpoint.

1. Create an app at [WordPress.com Apps](https://developer.wordpress.com/apps/) and copy its `client_id` and `client_secret`.

   For the `f/docspress-demo` repository, use these app form values:

   | Field | Value |
   | --- | --- |
   | Name | `Docspress Demo` |
   | Description | `Sync Markdown docs from GitHub to WordPress Pages as Gutenberg content.` |
   | Website URL | `https://github.com/f/docspress-demo` |
   | Redirect URLs | `http://localhost:8787/callback` |
   | Javascript Origins | Leave blank |
   | Type | `Web` |
   | Follow Developer blog | Optional; leave unchecked unless you want the emails |
   | Owner | Use your personal owner, such as `fatihkadirakin`, for personal/demo apps |

   The redirect URL must match the local callback URL used by the token helper below.

2. Run the local token helper from the Docspress checkout:

```bash
npm run token -- \
  --client-id YOUR_CLIENT_ID \
  --client-secret YOUR_CLIENT_SECRET \
  --site fkadev.blog \
  --repo f/docspress-demo
```

The helper opens WordPress.com in your browser, waits for the `http://localhost:8787/callback` redirect, exchanges the authorization code for an access token, and prints the token.

3. Store the returned access token as `WP_ACCESS_TOKEN` in the repository that runs Docspress:

```bash
gh secret set WP_ACCESS_TOKEN --repo OWNER/REPO
```

Paste the token when prompted, then press `Ctrl-D`.

You can also let the helper store the secret directly:

```bash
npm run token -- \
  --client-id YOUR_CLIENT_ID \
  --client-secret YOUR_CLIENT_SECRET \
  --site fkadev.blog \
  --repo f/docspress-demo \
  --set-secret
```

Docspress sends the resulting OAuth token to WordPress.com as `Authorization: Bearer ...`.

## Docs mapping

- `docs/index.md` or `docs/README.md` becomes `/docs/`.
- `docs/getting-started.md` becomes `/docs/getting-started/`.
- `docs/guides/index.md` becomes `/docs/guides/`.
- Missing parent sections are created as managed placeholder pages.

The page title comes from frontmatter `title`, then the first H1, then the filename. When the first H1 is used as the title, it is removed from the body to avoid duplication.

## Development

```bash
npm install
npm test
npm run lint
npm run build
```

`dist/index.js` is committed so workflows can run the action without installing dependencies.
