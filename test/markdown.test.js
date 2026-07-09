import { parse } from "@wordpress/block-serialization-default-parser";
import { describe, expect, it } from "vitest";
import { markdownToBlocks } from "../src/markdown.js";

describe("markdownToBlocks", () => {
  it("uses the first h1 as title and removes it from body", () => {
    const result = markdownToBlocks(`# Hello Docs

Welcome to **Docspress** with [links](https://example.com).

## Next

- One
- Two
`, { fallbackTitle: "Fallback" });

    expect(result.title).toBe("Hello Docs");
    expect(result.blocks).not.toContain("<h1>Hello Docs</h1>");
    expect(result.blocks).toContain("<!-- wp:paragraph -->");
    expect(result.blocks).toContain("<strong>Docspress</strong>");
    expect(result.blocks).toContain("<!-- wp:list -->");
    expect(parse(result.blocks).map((block) => block.blockName).filter(Boolean)).toEqual([
      "core/paragraph",
      "core/heading",
      "core/list"
    ]);
  });

  it("keeps h1 in content when frontmatter supplies title", () => {
    const result = markdownToBlocks(`---
title: Frontmatter Title
---

# Visible Heading

Body.
`, { fallbackTitle: "Fallback" });

    expect(result.title).toBe("Frontmatter Title");
    expect(result.blocks).toContain("<h1>Visible Heading</h1>");
  });

  it("can create a title h1 without duplicating the source h1", () => {
    const result = markdownToBlocks(`# Hello Docs

Body.
`, {
      fallbackTitle: "Fallback",
      createH1: true
    });

    expect(result.title).toBe("Hello Docs");
    expect(result.blocks.match(/<h1>Hello Docs<\/h1>/g)).toHaveLength(1);
    expect(parse(result.blocks).map((block) => block.blockName).filter(Boolean)[0]).toBe("core/heading");
  });

  it("does not create a title h1 when createH1 is the string false", () => {
    const result = markdownToBlocks(`# Hello Docs

Body.
`, {
      fallbackTitle: "Fallback",
      createH1: "false"
    });

    expect(result.blocks).not.toContain("<h1>Hello Docs</h1>");
  });

  it("renders docs blocks for code, quote, image, table, separator, and html", () => {
    const result = markdownToBlocks(`> Keep source in Git.

\`\`\`js
console.log("hi");
\`\`\`

![Alt text](https://example.com/image.png "Caption")

| Name | Value |
| --- | ---: |
| One | 1 |

---

<details><summary>More</summary>Raw HTML</details>
`, { fallbackTitle: "Fallback" });

    expect(result.blocks).toContain("<!-- wp:quote -->");
    expect(result.blocks).toContain("<!-- wp:code -->");
    expect(result.blocks).toContain("language-js");
    expect(result.blocks).toContain("<!-- wp:image");
    expect(result.blocks).toContain("<!-- wp:table -->");
    expect(result.blocks).toContain("<!-- wp:separator -->");
    expect(result.blocks).toContain("<!-- wp:html -->");
    expect(parse(result.blocks).length).toBeGreaterThan(4);
  });
});
