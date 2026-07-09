import { describe, expect, it } from "vitest";
import { prependSentinel } from "../src/sentinel.js";
import { syncPages } from "../src/sync.js";

function desiredPage(key, overrides = {}) {
  const segments = key.split("/");
  const body = "<!-- wp:paragraph -->\n<p>Hello</p>\n<!-- /wp:paragraph -->";
  const hash = overrides.hash || `${key}-hash`;

  return {
    key,
    parentKey: segments.length > 1 ? segments.slice(0, -1).join("/") : null,
    slug: segments.at(-1),
    title: overrides.title || key,
    status: overrides.status || "draft",
    hash,
    content: prependSentinel(body, { key, source: overrides.source || `docs/${key}.md`, hash }),
    depth: segments.length,
    ...overrides
  };
}

function existingPage(id, key, options = {}) {
  const hash = options.hash || `${key}-hash`;
  const content = options.managed === false
    ? "<p>Manual page</p>"
    : prependSentinel("<p>Managed page</p>", { key, source: `docs/${key}.md`, hash });

  return {
    id,
    slug: options.slug || key.split("/").at(-1),
    parent: options.parent || 0,
    content,
    title: key,
    status: options.status || "draft"
  };
}

function mockClient(pages = []) {
  const calls = [];
  let nextId = 100;

  return {
    calls,
    async listPages() {
      return pages;
    },
    async createPage(payload) {
      calls.push(["create", payload]);
      return { id: nextId += 1, ...payload };
    },
    async updatePage(id, payload) {
      calls.push(["update", id, payload]);
      return { id, ...payload };
    },
    async deletePage(id, options) {
      calls.push(["delete", id, options]);
      return { id, deleted: true };
    }
  };
}

describe("syncPages", () => {
  it("plans creates in dry-run mode without writing", async () => {
    const client = mockClient([]);
    const result = await syncPages({
      desiredPages: [desiredPage("docs"), desiredPage("docs/install")],
      client,
      dryRun: true,
      rootSlug: "docs",
      logger: { info() {} }
    });

    expect(result.created).toBe(2);
    expect(result.updated).toBe(0);
    expect(client.calls).toEqual([]);
  });

  it("updates changed managed pages and leaves matching pages unchanged", async () => {
    const existing = [
      existingPage(1, "docs", { hash: "old-hash" }),
      existingPage(2, "docs/install", { parent: 1, hash: "docs/install-hash" })
    ];
    const client = mockClient(existing);
    const result = await syncPages({
      desiredPages: [desiredPage("docs"), desiredPage("docs/install")],
      client,
      dryRun: false,
      rootSlug: "docs",
      logger: { info() {} }
    });

    expect(result.updated).toBe(1);
    expect(result.unchanged).toBe(1);
    expect(client.calls[0][0]).toBe("update");
    expect(client.calls[0][1]).toBe(1);
  });

  it("reports unmanaged path collisions as conflicts", async () => {
    const client = mockClient([existingPage(1, "docs", { managed: false })]);
    const result = await syncPages({
      desiredPages: [desiredPage("docs")],
      client,
      dryRun: false,
      rootSlug: "docs",
      logger: { info() {} }
    });

    expect(result.conflicts).toBe(1);
    expect(result.conflictDetails[0].reason).toMatch(/unmanaged/);
    expect(client.calls).toEqual([]);
  });

  it("deletes managed pages removed from the docs tree", async () => {
    const client = mockClient([
      existingPage(1, "docs"),
      existingPage(2, "docs/old", { parent: 1 })
    ]);
    const result = await syncPages({
      desiredPages: [desiredPage("docs")],
      client,
      dryRun: false,
      deleteMode: "force",
      rootSlug: "docs",
      logger: { info() {} }
    });

    expect(result.deleted).toBe(1);
    expect(client.calls).toContainEqual(["delete", 2, { force: true }]);
  });
});
