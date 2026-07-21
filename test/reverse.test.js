import fs from "node:fs/promises";
import os from "node:os";
import path from "node:path";
import { describe, expect, it } from "vitest";
import { markdownToBlocks } from "../src/markdown.js";
import { blocksToMarkdown, createReverseChanges } from "../src/reverse.js";
import { prependSentinel } from "../src/sentinel.js";

describe("blocksToMarkdown", () => {
  it("creates readable Markdown and preserves custom blocks", () => {
    const content = [
      '<!-- wp:paragraph -->\n<p>Hello <strong>world</strong> and <a href="/docs/guide/">guide</a>.</p>\n<!-- /wp:paragraph -->',
      '<!-- wp:heading -->\n<h2>Next</h2>\n<!-- /wp:heading -->',
      '<!-- wp:list -->\n<ul><li>One</li><li>Two</li></ul>\n<!-- /wp:list -->',
      '<!-- wp:docspress/callout {"tone":"tip","content":"<p>Keep me</p>"} /-->'
    ].join("\n\n");

    const markdown = blocksToMarkdown(content, {
      resolveLink: (url) => url === "/docs/guide/" ? "guide.md" : url
    });

    expect(markdown).toContain("Hello **world** and [guide](guide.md).");
    expect(markdown).toContain("## Next");
    expect(markdown).toContain("One");
    expect(markdown).toContain('<!-- wp:docspress/callout {"tone":"tip"');
  });

  it("removes generated title and source-link blocks", () => {
    const content = [
      '<!-- wp:heading {"level":1} -->\n<h1>Docs</h1>\n<!-- /wp:heading -->',
      '<!-- wp:paragraph -->\n<p>Body.</p>\n<!-- /wp:paragraph -->',
      '<!-- wp:paragraph {"className":"docspress-source-link"} -->\n<p class="docspress-source-link"><a href="https://github.com/edit">Edit</a></p>\n<!-- /wp:paragraph -->'
    ].join("\n\n");

    const markdown = blocksToMarkdown(content, { createH1: true, title: "Docs" });

    expect(markdown).toBe("Body.\n");
  });

  it("round-trips readable blocks and keeps attributed blocks raw", () => {
    const readable = '<!-- wp:paragraph -->\n<p>Hello <strong>world</strong>.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2>Next</h2>\n<!-- /wp:heading -->';
    const attributed = '<!-- wp:paragraph {"style":{"color":{"text":"#123456"}}} -->\n<p style="color:#123456">Colored.</p>\n<!-- /wp:paragraph -->';

    expect(markdownToBlocks(blocksToMarkdown(readable), { fallbackTitle: "Docs" }).blocks).toBe(readable);
    const attributedMarkdown = blocksToMarkdown(attributed);
    expect(attributedMarkdown).toContain('<!-- wp:paragraph {"style"');
    expect(markdownToBlocks(attributedMarkdown, { fallbackTitle: "Docs" }).blocks).toBe(attributed);
  });

  it("round-trips the supported Markdown block set", () => {
    const source = `> Keep this.

\`\`\`js
console.log("hi");
\`\`\`

![Alt](https://example.com/image.png "Caption")

| Name | Value |
| --- | ---: |
| One | 1 |

---
`;
    const first = markdownToBlocks(source, { fallbackTitle: "Docs" }).blocks;
    const second = markdownToBlocks(blocksToMarkdown(first), { fallbackTitle: "Docs" }).blocks;

    expect(second).toBe(first);
  });
});

describe("createReverseChanges", () => {
  it("updates an existing Markdown title and reverses managed links", async () => {
    const cwd = await fs.mkdtemp(path.join(os.tmpdir(), "docspress-reverse-"));
    await fs.mkdir(path.join(cwd, "docs"));
    await fs.writeFile(path.join(cwd, "docs", "index.md"), "# Old Docs\n\nOld body.\n");
    await fs.writeFile(path.join(cwd, "docs", "guide.md"), "# Guide\n");
    const desired = {
      key: "docs",
      sourcePath: "docs/index.md",
      title: "Old Docs",
      titleOverride: "",
      body: "",
      sourceMarkdown: "# Old Docs\n\nOld body.\n"
    };
    const guide = {
      key: "docs/guide",
      sourcePath: "docs/guide.md",
      title: "Guide"
    };
    const body = '<!-- wp:paragraph -->\n<p>Read the <a href="/docs/guide/">guide</a>.</p>\n<!-- /wp:paragraph -->';
    const page = {
      id: 1,
      title: "New Docs",
      content: prependSentinel(body, { key: "docs", source: "docs/index.md", hash: "base" }),
      sentinel: { key: "docs", source: "docs/index.md", hash: "base" },
      link: "https://example.com/docs/"
    };

    const changes = await createReverseChanges({
      cwd,
      pages: [{ page, desired }],
      desiredPages: [desired, guide]
    });

    expect(changes).toHaveLength(1);
    expect(changes[0].path).toBe("docs/index.md");
    expect(changes[0].content).toContain("# New Docs");
    expect(changes[0].content).toContain("[guide](guide.md)");
  });
});
