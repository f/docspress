import fs from "node:fs/promises";
import path from "node:path";
import { describe, expect, it } from "vitest";

const root = process.cwd();
const blocksRoot = path.join(root, "plugins", "docspress-blocks", "blocks");
const blockNames = [
  "api-request",
  "audience-paths",
  "callout",
  "code-tabs",
  "colorful-code",
  "file-tree",
  "hero",
  "prompt",
  "result",
  "terminal-session"
];

describe("DocsPress block theme constraints", () => {
  it("uses the exact theme radius instead of independent minimums or pills", async () => {
    const stylePaths = [
      path.join(root, "plugins", "docspress-blocks", "assets", "code.css"),
      ...blockNames.map((name) => path.join(blocksRoot, name, "style.css"))
    ];
    const styles = (await Promise.all(stylePaths.map((file) => fs.readFile(file, "utf8")))).join("\n");

    expect(styles).not.toMatch(/border-radius:\s*(?:max\(|calc\(|999px)/);
    expect(styles).toContain("border-radius: var(--dp-radius, 10px);");
  });

  it("passes the active theme tokens into every block editor", async () => {
    const editors = await Promise.all(
      blockNames.map((name) => fs.readFile(path.join(blocksRoot, name, "editor.js"), "utf8"))
    );

    for (const editor of editors) {
      expect(editor).toContain("themeStyle");
    }
  });

  it("keeps the published preset radius contracts explicit", async () => {
    const presetRadius = {
      wordpress: 2,
      "wordpress-com": 4,
      jetpack: 4
    };

    for (const [preset, radius] of Object.entries(presetRadius)) {
      const manifest = await fs.readFile(
        path.join(root, "theme", "inc", "design-presets", preset, "preset.php"),
        "utf8"
      );

      expect(manifest).toContain(`'docspress_border_radius'       => ${radius},`);
    }
  });
});
