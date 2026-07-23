---
title: Customize the theme in WordPress
---

DocsPress keeps the documentation shell configurable through WordPress. Open **Appearance → Customize → DocsPress Theme** to change the design while the documentation preview updates beside the controls.

![The DocsPress Theme panel in the WordPress Customizer](https://raw.githubusercontent.com/Automattic/docspress/main/theme/assets/images/customizer/theme-panel.jpg "The DocsPress Theme panel groups visual and behavioral settings by job.")

## Choose a design preset

Open **Design presets** and choose DocsPress, WordPress.org, WordPress.com, or Jetpack. A preset applies a complete recipe for color, typography, spacing, and corner radius. Changing an individual recipe value afterward preserves the change and marks the configuration as Custom.

![The Design presets section with the Jetpack preset applied in live preview](https://raw.githubusercontent.com/Automattic/docspress/main/theme/assets/images/customizer/design-preset-jetpack.jpg "Selecting Jetpack immediately updates the preview's typography, colors, and interface details.")

The DocsPress Blocks plugin follows the selected preset in the editor and on the published site. Every block surface inherits the preset’s corner radius, borders, light and dark colors, interface type, copy type, and heading type; changing those theme settings updates the blocks instead of leaving a second visual system behind. Documentation-block accents remain semantic so code, prompts, callouts, API exchanges, terminal sessions, results, and file trees stay legible. The design-focused Hero and Audience Paths blocks additionally offer intentional color and layout overrides.

## Build the site homepage

First choose a static front Page and optional posts page under **Settings → Reading**. Edit that front Page, insert **DocsPress: Hero**, and customize it in the block editor. Add **DocsPress: Audience Paths** after it when different readers should enter dedicated documentation roots. The Playground Home page already contains both blocks.

The block canvas edits the eyebrow, title, description, and action labels. Its clean default inherits the active theme’s type, colors, borders, and radius. The sidebar controls actions and URLs, artwork, alternative text, image placement, height, alignment, and optional presentation overrides. Grid, orbit, inverse styles, and custom colors are opt-in. Empty action labels or URLs hide that button; removing the image creates a text-only hero.

Audience Paths edits every visible label directly in the canvas. Add up to six paths, then give each one a Page-root URL, symbol, accent, and optional new-tab behavior in the sidebar. Keep the spacious layout for the primary homepage choice; enable **Compact layout** for smaller task routers within documentation articles. The default split asks whether the repository already has Markdown docs: existing docs lead to `/docs/publish-existing-docs/`, while a source-only project leads to `/docs/create-docs-with-ai/`. Normal child Pages beneath those roots provide each path’s focused sidebar branch.

Open **DocsPress Theme → Homepage** only to switch between the site landing template and the familiar documentation Page template, or to enable and configure the optional one-to-six-post recent grid. Recent posts are hidden by default so a documentation landing page stays focused. The landing template renders the front Page’s Gutenberg blocks directly, so the Hero and any following content remain normal editable Page content.

The [DocsPress browser Playground](https://playground.wordpress.net/?blueprint-url=https%3A%2F%2Fraw.githubusercontent.com%2FAutomattic%2Fdocspress%2Fmain%2Ftheme%2Fblueprint-browser.json&page-title=DocsPress%20Theme%20Playground) opens directly in this logged-in customization environment with representative content already seeded.

## Configure documentation navigation

Open **Navigation**, choose the synchronized documentation root, and select either the automatic Page hierarchy or a hand-built WordPress menu. The live preview shows the exact sidebar tree readers will receive.

![The Navigation section with the documentation root and automatic hierarchy controls](https://raw.githubusercontent.com/Automattic/docspress/main/theme/assets/images/customizer/navigation.jpg "Navigation can follow the managed Page hierarchy or a selected WordPress menu.")

Use the remaining controls to choose Page ordering, include or hide the root Page, limit nesting depth, change the sidebar heading, and configure the filter or version selector.

Parent Pages keep their normal links and gain a separate disclosure button for their children. Inactive branches start collapsed, while the branch containing the current Page always opens. Use Markdown frontmatter `sidebar_collapsed: false` to keep a managed section expanded or `sidebar_collapsed: true` to make its default explicit. With **Page order, then title** selected, `sidebar_position` controls a managed Page's order among its siblings. Hand-built menus keep their WordPress menu-item order.

<!-- wp:docspress/callout {"tone":"tip","title":"Keep the synchronized root selected","content":"<p>Set Documentation root to the root Page created by DocsPress. This keeps unrelated site Pages out of the sidebar, search results, breadcrumbs, and previous/next navigation.</p>","collapsible":false} /-->

## Shape command search

Open **Command search** to configure the `⌘K` or `Ctrl+K` experience. Labels, messages, result count, popup dimensions, corner radius, backdrop, result paths, excerpts, and the keyboard legend can all be adjusted without code.

![The Command search customization controls in WordPress](https://raw.githubusercontent.com/Automattic/docspress/main/theme/assets/images/customizer/command-search-settings.jpg "The Command search section controls labels, messages, result count, dimensions, backdrop, and result details.")

Open command search in the live preview to inspect the resulting reader experience before publishing:

![The DocsPress command search window open with results for customize](https://raw.githubusercontent.com/Automattic/docspress/main/theme/assets/images/customizer/command-search-open.jpg "The open command window ranks the new customization guide first and highlights matching text.")

When command search is enabled, readers can open it from the header, press `⌘K` or `Ctrl+K`, or press `/`. Disabling it removes both the visible control and its keyboard shortcuts.

## Configure posts and discussion

Open **Posts & archives** to decide whether post metadata, dates, authors, featured images, categories, and tags appear. These choices apply across the posts page, archives, search cards, and individual posts.

Open **Discussion** to show or hide conversations separately on Pages and posts. You can also change the headings, comment count, avatar display and size, dates, and the message shown when an existing thread is closed.

The theme does not replace WordPress’s discussion workflow. Use **Settings → Discussion** for default comment status, account requirements, moderation, threading, paging, ordering, notifications, avatars, and spam behavior. Use the editor’s **Discussion** panel to open or close comments on one Page or post. If a closed item already has replies, DocsPress can keep those replies visible without showing a new-comment form.

<!-- wp:docspress/callout {"tone":"note","title":"Documentation comments are optional","content":"<p>Keep reference Pages closed, enable comments only on guides that benefit from questions, or hide all Page discussions from the theme while retaining normal post conversations.</p>","collapsible":false} /-->

## Finish the reading experience

Return to the DocsPress Theme panel for the remaining sections:

- **Header** controls the menu, brand suffix, repository link, custom logo, and color-mode switcher.
- **Layout & reading tools** controls article, sidebar, and table-of-contents widths plus breadcrumbs, previous/next navigation, excerpts, and edit actions.
- **Homepage** selects the front-page template and recent posts; edit the front Page’s DocsPress Hero and Audience Paths blocks for its content, routing, and design.
- **Posts & archives** controls metadata and taxonomy presentation.
- **Discussion** controls where conversations appear and how they are labeled.
- **Light & dark colors** exposes independent semantic palettes for both modes.
- **Typography** selects the interface, reading, and heading stacks and adjusts reading size and heading weight.
- **Article labels & actions** changes the kicker, table-of-contents label, and WordPress or GitHub action buttons.
- **Footer** controls visibility, text, link, and the `{year}` and `{site_title}` placeholders.

Use **Publish** only after the desktop, tablet, and mobile previews look correct. See [DocsPress WordPress theme](../reference/theme.md) for the complete control reference and installation details.
