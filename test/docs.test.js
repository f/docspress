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

  it("rewrites local Markdown links and appends edit links", async () => {
    const cwd = await fs.mkdtemp(path.join(os.tmpdir(), "docspress-"));
    await fs.mkdir(path.join(cwd, "docs", "guides"), { recursive: true });
    await fs.mkdir(path.join(cwd, "docs", "reference"), { recursive: true });
    await fs.writeFile(path.join(cwd, "docs", "index.md"), [
      "# Docs",
      "",
      "Read [Getting Started](guides/getting-started.md), [Action Inputs](/docs/reference/action-inputs.md), and [External](https://example.com)."
    ].join("\n"));
    await fs.writeFile(path.join(cwd, "docs", "guides", "getting-started.md"), "# Getting Started");
    await fs.writeFile(path.join(cwd, "docs", "reference", "action-inputs.md"), "# Action Inputs");

    const pages = await collectDesiredPages({
      cwd,
      docsDir: "docs",
      rootSlug: "docs",
      rootTitle: "Docs",
      status: "draft",
      editLink: true,
      githubRepository: "f/docspress-demo",
      githubRef: "main"
    });
    const root = pages.find((page) => page.key === "docs");

    expect(root.content).toContain('href="/docs/guides/getting-started/"');
    expect(root.content).toContain('href="/docs/reference/action-inputs/"');
    expect(root.content).toContain('href="https://example.com"');
    expect(root.content).toContain("https://github.com/f/docspress-demo/edit/main/docs/index.md");
  });

  it("uses an optional manifest for stable titles, slugs, parents, and sources", async () => {
    const cwd = await fs.mkdtemp(path.join(os.tmpdir(), "docspress-"));
    await fs.mkdir(path.join(cwd, "docs", "guides"), { recursive: true });
    await fs.writeFile(path.join(cwd, "docs", "index.md"), "# Source Root\n\nWelcome.");
    await fs.writeFile(path.join(cwd, "docs", "guides", "start.md"), "# Source Start\n\nStart here.");
    await fs.writeFile(path.join(cwd, "docs", "manifest.json"), JSON.stringify({
      pages: [
        { id: "root", title: "Manifest Root", slug: "", markdown_source: "index.md" },
        { id: "guides", title: "Guides", slug: "guides" },
        { id: "start", title: "Start Here", slug: "getting-started", parent: "guides", markdown_source: "guides/start.md" }
      ]
    }, null, 2));

    const pages = await collectDesiredPages({
      cwd,
      docsDir: "docs",
      manifestFile: "docs/manifest.json",
      rootSlug: "docs",
      rootTitle: "Docs",
      status: "draft"
    });

    expect(pages.map((page) => page.key)).toEqual([
      "docs",
      "docs/guides",
      "docs/guides/getting-started"
    ]);
    expect(pages.find((page) => page.key === "docs")?.title).toBe("Manifest Root");
    expect(pages.find((page) => page.key === "docs/guides/getting-started")?.title).toBe("Start Here");
    expect(pages.find((page) => page.key === "docs/guides/getting-started")?.content).not.toContain("Source Start");
  });

  it("creates managed moved-page placeholders from a redirects file", async () => {
    const cwd = await fs.mkdtemp(path.join(os.tmpdir(), "docspress-"));
    await fs.mkdir(path.join(cwd, "docs", "guides"), { recursive: true });
    await fs.writeFile(path.join(cwd, "docs", "index.md"), "# Docs");
    await fs.writeFile(path.join(cwd, "docs", "guides", "getting-started.md"), "# Getting Started");
    await fs.writeFile(path.join(cwd, "docs", "redirects.json"), JSON.stringify({
      redirects: {
        "old-start": "guides/getting-started"
      }
    }, null, 2));

    const pages = await collectDesiredPages({
      cwd,
      docsDir: "docs",
      redirectsFile: "docs/redirects.json",
      rootSlug: "docs",
      rootTitle: "Docs",
      status: "draft"
    });
    const redirect = pages.find((page) => page.key === "docs/old-start");

    expect(redirect).toMatchObject({
      kind: "redirect",
      title: "Moved: Old Start",
      parentKey: "docs"
    });
    expect(redirect.content).toContain('href="/docs/guides/getting-started/"');
    expect(readSentinel(redirect.content)).toMatchObject({
      key: "docs/old-start",
      source: "redirects:docs/redirects.json#old-start"
    });
  });
});
