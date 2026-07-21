---
title: Why DocsPress?
---

DocsPress is the advantageous choice when documentation belongs in GitHub but WordPress is already the trusted publishing, editorial, and presentation platform.

<!-- wp:docspress/callout {"tone":"success","title":"Keep the workflow you already have","content":"<p>Developers keep Markdown, pull requests, and code review. Readers and editors keep WordPress Pages, previews, menus, themes, users, and plugins.</p>","collapsible":false} /-->

## DocsPress and Docusaurus solve different deployment problems

[Docusaurus](https://docusaurus.io/) is a React-based static-site generator. It turns Markdown or MDX into a separately built documentation application that must be deployed to static hosting. DocsPress turns Markdown into managed WordPress Pages on an existing site.

| Capability | DocsPress | Docusaurus |
| --- | --- | --- |
| Source of truth | Markdown beside the code | Markdown or MDX beside the code |
| Published surface | Native WordPress Pages and Gutenberg blocks | A generated React static site |
| Hosting | Reuses an existing WordPress site | Requires a static-site deployment target |
| Editorial review | WordPress draft Pages, roles, previews, and publishing controls | Usually pull requests and deployment previews |
| Rich components | Gutenberg core blocks plus constrained DocsPress blocks | React components embedded through MDX |
| Navigation | WordPress Page hierarchy or configurable menus | Sidebars generated or configured in the Docusaurus project |
| Search | Theme-integrated Page search and command palette | First-class Algolia DocSearch; community local-search options |
| Visual configuration | WordPress Customizer, presets, light/dark tokens, no theme build | React/Infima configuration, CSS, and component swizzling |
| Platform ecosystem | WordPress users, plugins, REST APIs, menus, and site operations | Node.js, React, Docusaurus plugins, and static-host tooling |
| Built-in versioned docs | Not currently provided by the DocsPress Action | Dedicated versioning CLI and versioned docs directories |

## Where DocsPress is more advantageous

### One publishing platform

DocsPress avoids introducing a second public website, hosting contract, deployment configuration, analytics surface, and design system just for documentation. The docs can live beside product pages, support content, and the rest of an established WordPress site.

### Native editorial review

The first synchronization can create draft Pages for inspection. WordPress administrators can verify hierarchy, blocks, links, menus, and presentation before public publication. This is especially useful when documentation publication has an editorial or compliance step after code review.

### Constrained, theme-aware components

DocsPress blocks cover code, tabs, callouts, HTTP exchanges, terminal output, results, file trees, and AI prompts. Authors choose semantic options, while the active design preset controls colors, typography, borders, radii, and light/dark behavior. That keeps generated documentation visually coherent without asking every author to design React components.

### WordPress-native navigation and customization

The bundled theme can use the synchronized Page hierarchy automatically or use hand-built WordPress menus. Site owners can configure the header, sidebar, command search, article actions, typography, colors, footer, widths, and reading tools through the Customizer.

### A smaller operational change for WordPress teams

DocsPress adds one GitHub Action and one managed Page tree to infrastructure the team already operates. Docusaurus is an excellent fit when the team explicitly wants a React static application; DocsPress is simpler when adding another Node-powered public site would duplicate existing WordPress capabilities.

<!-- wp:docspress/result {"status":"success","title":"Best fit: WordPress is already the destination","content":"<p>DocsPress preserves developer-native authoring while making documentation a first-class part of the WordPress site.</p>","meta":"GitHub authoring · WordPress publishing"} /-->

## When Docusaurus may be the better fit

Choose Docusaurus when you specifically need a standalone static React application, extensive MDX component composition, built-in documentation version snapshots, or its mature internationalization and search ecosystem. Its official documentation describes [the React application structure](https://docusaurus.io/docs/installation), [static deployment options](https://docusaurus.io/docs/deployment), [search integrations](https://docusaurus.io/docs/search), and [versioning workflow](https://docusaurus.io/docs/versioning).

<!-- wp:docspress/callout {"tone":"note","title":"A practical decision","content":"<p>If your organization already publishes through WordPress, start with DocsPress. If the documentation must be an independent React product, start with Docusaurus.</p>","collapsible":false} /-->
