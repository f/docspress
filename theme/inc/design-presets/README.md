# Design presets

Each immediate subfolder is one automatically discovered DocsPress design preset. The folder name becomes the saved preset slug and its body class: `docspress-preset-{slug}`.

To add a preset:

1. Create `inc/design-presets/your-preset/preset.php`.
2. Return a manifest with a translated `label`, optional `description`, numeric `order`, and a complete `values` array of Customizer setting IDs and values.
3. Optionally add `style.css`, scoped under `.docspress-preset-your-preset`, for visual refinements that are not design tokens.

The loader discovers the folder, adds it to the Customizer selector, sends its recipe to the live preset controller, validates it for body classes, and loads its stylesheet. No central preset registry needs to be edited.

Use the existing folders as complete manifest examples. Keep recipes complete so switching from any other preset always produces the same result.
