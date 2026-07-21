---
title: Customize the theme in WordPress
---

DocsPress keeps the documentation shell configurable through WordPress. Open **Appearance → Customize → DocsPress Theme** to change the design while the documentation preview updates beside the controls.

![The DocsPress Theme panel in the WordPress Customizer](https://raw.githubusercontent.com/Automattic/docspress/main/theme/assets/images/customizer/theme-panel.jpg "The DocsPress Theme panel groups visual and behavioral settings by job.")

## Choose a design preset

Open **Design presets** and choose DocsPress, WordPress.org, WordPress.com, or Jetpack. A preset applies a complete recipe for color, typography, spacing, and corner radius. Changing an individual recipe value afterward preserves the change and marks the configuration as Custom.

![The Design presets section with the Jetpack preset applied in live preview](https://raw.githubusercontent.com/Automattic/docspress/main/theme/assets/images/customizer/design-preset-jetpack.jpg "Selecting Jetpack immediately updates the preview's typography, colors, and interface details.")

The DocsPress Blocks plugin follows the selected preset in the editor and on the published site. Block colors remain semantic so code, prompts, callouts, API exchanges, terminal sessions, results, and file trees stay legible in light and dark mode.

## Configure documentation navigation

Open **Navigation**, choose the synchronized documentation root, and select either the automatic Page hierarchy or a hand-built WordPress menu. The live preview shows the exact sidebar tree readers will receive.

![The Navigation section with the documentation root and automatic hierarchy controls](https://raw.githubusercontent.com/Automattic/docspress/main/theme/assets/images/customizer/navigation.jpg "Navigation can follow the managed Page hierarchy or a selected WordPress menu.")

Use the remaining controls to choose Page ordering, include or hide the root Page, limit nesting depth, change the sidebar heading, and configure the filter or version selector.

<!-- wp:docspress/callout {"tone":"tip","title":"Keep the synchronized root selected","content":"<p>Set Documentation root to the root Page created by DocsPress. This keeps unrelated site Pages out of the sidebar, search results, breadcrumbs, and previous/next navigation.</p>","collapsible":false} /-->

## Shape command search

Open **Command search** to configure the `⌘K` or `Ctrl+K` experience. Labels, messages, result count, popup dimensions, corner radius, backdrop, result paths, excerpts, and the keyboard legend can all be adjusted without code.

![The Command search customization controls in WordPress](https://raw.githubusercontent.com/Automattic/docspress/main/theme/assets/images/customizer/command-search-settings.jpg "The Command search section controls labels, messages, result count, dimensions, backdrop, and result details.")

Open command search in the live preview to inspect the resulting reader experience before publishing:

![The DocsPress command search window open with results for customize](https://raw.githubusercontent.com/Automattic/docspress/main/theme/assets/images/customizer/command-search-open.jpg "The open command window ranks the new customization guide first and highlights matching text.")

When command search is enabled, readers can open it from the header, press `⌘K` or `Ctrl+K`, or press `/`. Disabling it removes both the visible control and its keyboard shortcuts.

## Finish the reading experience

Return to the DocsPress Theme panel for the remaining sections:

- **Header** controls the menu, brand suffix, repository link, custom logo, and color-mode switcher.
- **Layout & reading tools** controls article, sidebar, and table-of-contents widths plus breadcrumbs, previous/next navigation, excerpts, and edit actions.
- **Light & dark colors** exposes independent semantic palettes for both modes.
- **Typography** selects the interface, reading, and heading stacks and adjusts reading size and heading weight.
- **Article labels & actions** changes the kicker, table-of-contents label, and WordPress or GitHub action buttons.
- **Footer** controls visibility, text, link, and the `{year}` and `{site_title}` placeholders.

Use **Publish** only after the desktop, tablet, and mobile previews look correct. See [DocsPress WordPress theme](../reference/theme.md) for the complete control reference and installation details.
