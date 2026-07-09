import fs from "node:fs/promises";
import os from "node:os";
import path from "node:path";
import { describe, expect, it } from "vitest";
import { collectDesiredPages } from "../src/docs.js";
import { readSentinel } from "../src/sentinel.js";

describe("collectDesiredPages", () => {
  it("maps nested docs into a WordPress page hierarchy with placeholders", async () => {
    const cwd = await fs.mkdtemp(path.join(os.tmpdir(), "docspress-"));
    await fs.mkdir(path.join(cwd, "docs", "guides"), { recursive: true });
    await fs.writeFile(path.join(cwd, "docs", "index.md"), "# Product Docs\n\nWelcome.");
    await fs.writeFile(path.join(cwd, "docs", "guides", "install.md"), "---\ntitle: Install Guide\n---\n\nUse it.");

    const pages = await collectDesiredPages({
      cwd,
      docsDir: "docs",
      rootSlug: "docs",
      rootTitle: "Docs",
      status: "draft"
    });

    expect(pages.map((page) => page.key)).toEqual([
      "docs",
      "docs/guides",
      "docs/guides/install"
    ]);
    expect(pages.find((page) => page.key === "docs")?.title).toBe("Product Docs");
    expect(pages.find((page) => page.key === "docs/guides")?.kind).toBe("placeholder");
    expect(pages.find((page) => page.key === "docs/guides/install")?.parentKey).toBe("docs/guides");
    expect(readSentinel(pages[0].content)).toMatchObject({
      key: "docs",
      source: "docs/index.md"
    });
  });

  it("adds title h1 blocks to files and placeholders when requested", async () => {
    const cwd = await fs.mkdtemp(path.join(os.tmpdir(), "docspress-"));
    await fs.mkdir(path.join(cwd, "docs", "guides"), { recursive: true });
    await fs.writeFile(path.join(cwd, "docs", "index.md"), "# Product Docs\n\nWelcome.");
    await fs.writeFile(path.join(cwd, "docs", "guides", "install.md"), "# Install\n\nUse it.");

    const pages = await collectDesiredPages({
      cwd,
      docsDir: "docs",
      rootSlug: "docs",
      rootTitle: "Docs",
      status: "draft",
      createH1: true
    });

    expect(pages.find((page) => page.key === "docs")?.content).toContain("<h1>Product Docs</h1>");
    expect(pages.find((page) => page.key === "docs/guides")?.content).toContain("<h1>Guides</h1>");
    expect(pages.find((page) => page.key === "docs/guides/install")?.content.match(/<h1>Install<\/h1>/g)).toHaveLength(1);
  });

  it("rejects files that map to the same page", async () => {
    const cwd = await fs.mkdtemp(path.join(os.tmpdir(), "docspress-"));
    await fs.mkdir(path.join(cwd, "docs"), { recursive: true });
    await fs.writeFile(path.join(cwd, "docs", "index.md"), "# Index");
    await fs.writeFile(path.join(cwd, "docs", "README.md"), "# Readme");

    await expect(collectDesiredPages({
      cwd,
      docsDir: "docs",
      rootSlug: "docs",
      rootTitle: "Docs",
      status: "draft"
    })).rejects.toThrow(/same docs page/);
  });
});
