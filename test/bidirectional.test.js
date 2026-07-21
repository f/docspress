import fs from "node:fs/promises";
import os from "node:os";
import path from "node:path";
import { describe, expect, it, vi } from "vitest";
import { syncBidirectional } from "../src/bidirectional.js";
import { hashPageState } from "../src/page-state.js";
import { prependSentinel } from "../src/sentinel.js";

const paragraph = (text) => `<!-- wp:paragraph -->\n<p>${text}</p>\n<!-- /wp:paragraph -->`;

function desiredPage(text = "Base", overrides = {}) {
  const page = {
    key: "docs",
    sourcePath: "docs/index.md",
    sourceMarkdown: `# Docs\n\n${text}\n`,
    title: "Docs",
    titleOverride: "",
    slug: "docs",
    parentKey: null,
    status: "publish",
    body: paragraph(text),
    depth: 1,
    ...overrides
  };
  page.hash = hashPageState(page);
  page.content = prependSentinel(page.body, { key: page.key, source: page.sourcePath, hash: page.hash });
  return page;
}

function existingPage(base, text = "Base", overrides = {}) {
  return {
    id: 1,
    slug: base.slug,
    parent: 0,
    title: base.title,
    status: base.status,
    content: prependSentinel(paragraph(text), { key: base.key, source: base.sourcePath, hash: base.hash }),
    link: `https://example.com/${base.slug}/`,
    ...overrides
  };
}

function mockClient(pages) {
  return {
    listPages: vi.fn(async () => pages),
    createPage: vi.fn(async (payload) => ({ id: 2, ...payload })),
    updatePage: vi.fn(async (id, payload) => ({ id, ...payload })),
    deletePage: vi.fn(async () => ({}))
  };
}

describe("syncBidirectional", () => {
  it("opens a proposal for a WordPress-only edit without replacing the Page", async () => {
    const cwd = await fs.mkdtemp(path.join(os.tmpdir(), "docspress-bidirectional-"));
    await fs.mkdir(path.join(cwd, "docs"));
    await fs.writeFile(path.join(cwd, "docs", "index.md"), "# Docs\n\nBase\n");
    const desired = desiredPage();
    const client = mockClient([existingPage(desired, "Edited in WordPress")]);
    const githubClient = { syncChanges: vi.fn(async () => ({ number: 4, url: "https://github.com/o/r/pull/4", status: "created" })) };

    const result = await syncBidirectional({
      mode: "propose",
      desiredPages: [desired],
      client,
      githubClient,
      cwd,
      logger: { info() {} }
    });

    expect(result.proposed).toBe(1);
    expect(githubClient.syncChanges).toHaveBeenCalledWith([
      expect.objectContaining({ path: "docs/index.md", content: expect.stringContaining("Edited in WordPress") })
    ]);
    expect(client.updatePage).not.toHaveBeenCalled();
  });

  it("keeps a WordPress-only edit intact while reconcile opens its pull request", async () => {
    const cwd = await fs.mkdtemp(path.join(os.tmpdir(), "docspress-bidirectional-"));
    await fs.mkdir(path.join(cwd, "docs"));
    await fs.writeFile(path.join(cwd, "docs", "index.md"), "# Docs\n\nBase\n");
    const desired = desiredPage();
    const client = mockClient([existingPage(desired, "Edited in WordPress")]);
    const githubClient = { syncChanges: vi.fn(async () => ({ number: 4, url: "https://github.com/o/r/pull/4", status: "created" })) };

    const result = await syncBidirectional({
      mode: "reconcile",
      desiredPages: [desired],
      client,
      githubClient,
      cwd,
      logger: { info() {} }
    });

    expect(result.proposed).toBe(1);
    expect(result.updated).toBe(0);
    expect(result.unchanged).toBe(1);
    expect(githubClient.syncChanges).toHaveBeenCalledOnce();
    expect(client.updatePage).not.toHaveBeenCalled();
  });

  it("publishes GitHub-only Pages while preserving a WordPress-only Page", async () => {
    const cwd = await fs.mkdtemp(path.join(os.tmpdir(), "docspress-bidirectional-"));
    await fs.mkdir(path.join(cwd, "docs"));
    await fs.writeFile(path.join(cwd, "docs", "wordpress.md"), "# WordPress\n\nBase\n");
    await fs.writeFile(path.join(cwd, "docs", "github.md"), "# GitHub\n\nEdited in GitHub\n");
    const wordpressBase = desiredPage("Base", {
      key: "wordpress",
      sourcePath: "docs/wordpress.md",
      title: "WordPress",
      slug: "wordpress"
    });
    const githubBase = desiredPage("Base", {
      key: "github",
      sourcePath: "docs/github.md",
      title: "GitHub",
      slug: "github"
    });
    const githubDesired = desiredPage("Edited in GitHub", {
      key: "github",
      sourcePath: "docs/github.md",
      title: "GitHub",
      slug: "github"
    });
    const client = mockClient([
      existingPage(wordpressBase, "Edited in WordPress", { id: 1 }),
      existingPage(githubBase, "Base", { id: 2 })
    ]);
    const githubClient = { syncChanges: vi.fn(async () => ({ number: 5, url: "https://github.com/o/r/pull/5", status: "created" })) };

    const result = await syncBidirectional({
      mode: "reconcile",
      desiredPages: [wordpressBase, githubDesired],
      client,
      githubClient,
      cwd,
      logger: { info() {} }
    });

    expect(result.proposed).toBe(1);
    expect(result.updated).toBe(1);
    expect(client.updatePage).toHaveBeenCalledOnce();
    expect(client.updatePage).toHaveBeenCalledWith(2, expect.objectContaining({ content: githubDesired.content }));
  });

  it("publishes a GitHub-only edit in reconcile mode", async () => {
    const cwd = await fs.mkdtemp(path.join(os.tmpdir(), "docspress-bidirectional-"));
    await fs.mkdir(path.join(cwd, "docs"));
    await fs.writeFile(path.join(cwd, "docs", "index.md"), "# Docs\n\nEdited in GitHub\n");
    const base = desiredPage();
    const desired = desiredPage("Edited in GitHub");
    const client = mockClient([existingPage(base)]);
    const githubClient = { syncChanges: vi.fn(async () => ({ status: "unchanged", number: null, url: "" })) };

    const result = await syncBidirectional({
      mode: "reconcile",
      desiredPages: [desired],
      client,
      githubClient,
      cwd,
      logger: { info() {} }
    });

    expect(result.updated).toBe(1);
    expect(client.updatePage).toHaveBeenCalledWith(1, expect.objectContaining({ content: desired.content }));
  });

  it("fails preflight without mutations when both sides changed", async () => {
    const cwd = await fs.mkdtemp(path.join(os.tmpdir(), "docspress-bidirectional-"));
    await fs.mkdir(path.join(cwd, "docs"));
    await fs.writeFile(path.join(cwd, "docs", "index.md"), "# Docs\n\nGitHub\n");
    const base = desiredPage();
    const desired = desiredPage("GitHub");
    const client = mockClient([existingPage(base, "WordPress")]);
    const githubClient = { syncChanges: vi.fn() };

    const result = await syncBidirectional({
      mode: "reconcile",
      desiredPages: [desired],
      client,
      githubClient,
      cwd,
      logger: { info() {} }
    });

    expect(result.conflicts).toBe(1);
    expect(githubClient.syncChanges).not.toHaveBeenCalled();
    expect(client.updatePage).not.toHaveBeenCalled();
  });
});
