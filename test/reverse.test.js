import fs from "node:fs/promises";
import os from "node:os";
import path from "node:path";
import { parse } from "@wordpress/block-serialization-default-parser";
import { describe, expect, it } from "vitest";
import { markdownToBlocks } from "../src/markdown.js";
import { blocksToMarkdown, createReverseChanges, mergeWordPressIntoSource } from "../src/reverse.js";
import { prependSentinel } from "../src/sentinel.js";

function desiredFromMarkdown(source, overrides = {}) {
  const converted = markdownToBlocks(source, { fallbackTitle: "Docs" });
  return {
    key: "docs",
    sourcePath: "docs/index.md",
    title: converted.title,
    titleOverride: "",
    body: converted.blocks,
    sourceMarkdown: source,
    ...overrides
  };
}

function pageFromBody(desired, body, overrides = {}) {
  return {
    id: 1,
    title: desired.title,
    content: prependSentinel(body, { key: desired.key, source: desired.sourcePath, hash: "base" }),
    sentinel: { key: desired.key, source: desired.sourcePath, hash: "base" },
    link: "https://example.com/docs/",
    ...overrides
  };
}

function wordPressSafeAttributes(attributes) {
  return JSON.stringify(attributes)
    .replace(/--/g, "\\u002d\\u002d")
    .replace(/</g, "\\u003c")
    .replace(/>/g, "\\u003e")
    .replace(/&/g, "\\u0026")
    .replace(/\\\"/g, "\\u0022");
}

function replaceCustomBlock(body, name, mutate) {
  const blocks = parse(body);
  const block = blocks.find((candidate) => candidate.blockName === name);
  const nextAttributes = mutate({ ...(block?.attrs || {}) });
  const pattern = new RegExp(`^<!-- wp:${name.replace("/", "\\/")} \\{.*\\} \\/-->$`, "m");
  return body.replace(pattern, `<!-- wp:${name} ${wordPressSafeAttributes(nextAttributes)} /-->`);
}

const customBlockCases = [
  {
    name: "docspress/api-request",
    omit: "endpoint",
    change: ["method", "PATCH"],
    attributes: {
      method: "GET",
      endpoint: "/wp-json/wp/v2/pages",
      headers: "Accept: application/json\nAuthorization: Bearer $WP_ACCESS_TOKEN",
      requestBody: "{\n  \"title\": \"Docs -- now\"\n}",
      requestBodyFormat: "json",
      responseStatus: "200 OK",
      responseBody: "{\n  \"ok\": true\n}",
      responseBodyFormat: "json"
    }
  },
  {
    name: "docspress/callout",
    omit: "collapsible",
    change: ["tone", "warning"],
    attributes: {
      tone: "note",
      title: "Good to know",
      content: "<p>Keep <strong>both</strong> directions safe.</p>",
      collapsible: false,
      open: true
    }
  },
  {
    name: "docspress/code-tabs",
    omit: "showLineNumbers",
    change: ["caption", "Updated tabs"],
    attributes: {
      tabs: [
        { label: "JavaScript", language: "javascript", filename: "index.js", code: "const docs = true;" },
        { label: "PHP", language: "php", filename: "index.php", code: "$docs = true;" }
      ],
      showLineNumbers: true,
      caption: ""
    }
  },
  {
    name: "docspress/colorful-code",
    omit: "showLineNumbers",
    change: ["code", "const docs = \"updated\";"],
    attributes: {
      language: "javascript",
      filename: "index.js",
      code: "const docs = \"DocsPress\";",
      highlightedLines: "1",
      showLineNumbers: true,
      caption: ""
    }
  },
  {
    name: "docspress/file-tree",
    omit: "caption",
    change: ["tree", "docs/\n  index.md\n  api.md"],
    attributes: {
      root: "project/",
      tree: "docs/\n  index.md",
      caption: ""
    }
  },
  {
    name: "docspress/prompt",
    omit: "thinking",
    change: ["prompt", "Update the docs without changing unrelated Markdown."],
    attributes: {
      prompt: "Review the docs.",
      model: "GPT-5",
      mode: "code",
      thinking: true,
      context: "$docspress-install, @repository, docs/",
      caption: "Prompt example"
    }
  },
  {
    name: "docspress/result",
    omit: "status",
    change: ["status", "warning"],
    attributes: {
      status: "success",
      title: "Deployment completed",
      content: "<p>All documentation Pages are current.</p>",
      meta: "12 pages · 1.8s"
    }
  },
  {
    name: "docspress/terminal-session",
    omit: "shell",
    change: ["command", "npm test\nnpm run build"],
    attributes: {
      title: "Terminal",
      shell: "bash",
      prompt: "$",
      command: "npm test",
      output: "Tests passed"
    }
  }
];

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

  it("round-trips preformatted, separator, and HTML blocks", () => {
    const content = [
      '<!-- wp:preformatted -->\n<pre class="wp-block-preformatted">plain text</pre>\n<!-- /wp:preformatted -->',
      '<!-- wp:separator -->\n<hr class="wp-block-separator has-alpha-channel-opacity"/>\n<!-- /wp:separator -->',
      '<!-- wp:html -->\n<details><summary>Raw</summary><p>HTML</p></details>\n<!-- /wp:html -->'
    ].join("\n\n");

    const markdown = blocksToMarkdown(content);

    expect(markdown).toContain('<!-- wp:preformatted -->\n<pre class="wp-block-preformatted">plain text</pre>\n<!-- /wp:preformatted -->');
    expect(markdown).toContain("---");
    expect(markdown).toContain("<details><summary>Raw</summary><p>HTML</p></details>");
    expect(markdownToBlocks(markdown, { fallbackTitle: "Docs" }).blocks).toContain("wp:preformatted");
  });
});

describe("mergeWordPressIntoSource", () => {
  it("preserves the live Synchronization and REST API shape except for the edited heading", () => {
    const source = `---
title: Synchronization and REST API
---

DocsPress reconciles managed Pages.

## WordPress endpoints

\`\`\`text
https://public-api.wordpress.com/wp/v2/sites/{site}/pages
\`\`\`

| Condition | Operation |
| --- | --- |
| Managed | Update |

<!-- wp:docspress/api-request {"method":"GET","endpoint":"/wp-json/wp/v2/pages","headers":"Accept: application/json\nAuthorization: Bearer $WP_ACCESS_TOKEN","requestBody":"","requestBodyFormat":"json","responseStatus":"200 OK","responseBody":"{\n  \"id\": 43\n}","responseBodyFormat":"json"} /-->

The source stays readable.
`;
    const desired = desiredFromMarkdown(source, {
      key: "docs/reference/sync-and-rest-api",
      sourcePath: "docs/reference/sync-and-rest-api.md"
    });
    let live = desired.body
      .replace("<!-- wp:heading -->\n<h2>WordPress endpoints</h2>", '<!-- wp:heading -->\n<h2 class="wp-block-heading">WordPress endpoints TEST</h2>')
      .replace("<!-- wp:code -->", '<!-- wp:code {"tokenizedLines":[[["https://public-api.wordpress.com/wp/v2/sites/{site}/pages",null]]]} -->')
      .replace(' class="language-text"', "")
      .replace("<!-- wp:table -->", '<!-- wp:table {"hasFixedLayout":false} -->');
    live = replaceCustomBlock(live, "docspress/api-request", (attributes) => {
      delete attributes.endpoint;
      delete attributes.requestBodyFormat;
      delete attributes.responseBodyFormat;
      return attributes;
    });
    live = `<p></p>\n\n${live}`;

    const merged = mergeWordPressIntoSource({
      existing: source,
      desired,
      page: pageFromBody(desired, live)
    });

    expect(merged).toBe(source.replace("## WordPress endpoints", "## WordPress endpoints TEST"));
  });

  it.each([
    ["paragraph", "Paragraph **strong**.", "Paragraph", "Changed paragraph"],
    ["heading", "## Section", "Section", "Changed section"],
    ["deep heading", "#### Details", "Details", "Changed details"],
    ["list", "- Alpha\n- Beta", "Beta", "Gamma"],
    ["ordered list", "1. Alpha\n2. Beta", "Beta", "Gamma"],
    ["task list", "- [x] Done\n- [ ] Todo", "Todo", "Next"],
    ["nested list", "- Parent\n    - Child", "Child", "Nested"],
    ["quote", "> Quoted", "Quoted", "Changed quote"],
    ["code", "```js\nconsole.log(\"before\");\n```", "before", "after"],
    ["image", '![Alt](https://example.com/a.png "Caption")', "a.png", "b.png"],
    ["table", "| Name | Value |\n| --- | --- |\n| One | 1 |", "One", "Two"],
    ["HTML", '<details><summary>Raw</summary><p>HTML</p></details>', "<p>HTML</p>", "<p>Changed</p>"]
  ])("round-trips an edited %s block without touching neighboring Markdown", (_label, markdown, before, after) => {
    const source = `---\ntitle: Core blocks\nslug: keep-this\n---\n\nBefore 🙂.\n\n${markdown}\n\nAfter.\n`;
    const desired = desiredFromMarkdown(source);
    const live = desired.body.replaceAll(before, after);

    const merged = mergeWordPressIntoSource({
      existing: source,
      desired,
      page: pageFromBody(desired, live)
    });

    expect(merged).toBe(source.replaceAll(before, after));
    expect(markdownToBlocks(merged, { fallbackTitle: "Docs" }).blocks).toBe(live);
  });

  it("supports WordPress block insertion, deletion, and reordering", () => {
    const source = "---\ntitle: Structural edits\n---\n\nAlpha.\n\nBeta.\n\nGamma.\n";
    const desired = desiredFromMarkdown(source);
    const blocks = parse(desired.body).filter((block) => block.blockName);
    const serialized = desired.body.split("\n\n");
    const inserted = markdownToBlocks("## Inserted", { fallbackTitle: "Docs" }).blocks;
    const live = [serialized[1], inserted, serialized[0]].join("\n\n");
    expect(blocks).toHaveLength(3);

    const merged = mergeWordPressIntoSource({
      existing: source,
      desired,
      page: pageFromBody(desired, live)
    });

    expect(merged).toBe("---\ntitle: Structural edits\n---\n\nBeta.\n\n## Inserted\n\nAlpha.\n");
    expect(markdownToBlocks(merged, { fallbackTitle: "Docs" }).blocks).toBe(live);
  });

  it("supports inserting into and deleting the only block from a document", () => {
    const emptySource = "---\ntitle: Empty\n---\n";
    const emptyDesired = desiredFromMarkdown(emptySource);
    const inserted = markdownToBlocks("Inserted.", { fallbackTitle: "Docs" }).blocks;
    const withInsertion = mergeWordPressIntoSource({
      existing: emptySource,
      desired: emptyDesired,
      page: pageFromBody(emptyDesired, inserted)
    });
    expect(withInsertion).toBe("---\ntitle: Empty\n---\n\nInserted.\n");

    const filledDesired = desiredFromMarkdown(withInsertion);
    const afterDeletion = mergeWordPressIntoSource({
      existing: withInsertion,
      desired: filledDesired,
      page: pageFromBody(filledDesired, "")
    });
    expect(afterDeletion).toBe("---\ntitle: Empty\n---\n\n");
  });

  it("keeps attributed core blocks as valid serialized Gutenberg", () => {
    const raw = '<!-- wp:paragraph {"style":{"color":{"text":"#123456"}},"className":"notice"} -->\n<p class="notice" style="color:#123456">Colored.</p>\n<!-- /wp:paragraph -->';
    const source = `---\ntitle: Raw block\n---\n\n${raw}\n\nTail.\n`;
    const desired = desiredFromMarkdown(source);
    const live = desired.body.replace("Colored.", "Edited.");

    const merged = mergeWordPressIntoSource({
      existing: source,
      desired,
      page: pageFromBody(desired, live)
    });

    expect(merged).toBe(source.replace("Colored.", "Edited."));
    expect(markdownToBlocks(merged, { fallbackTitle: "Docs" }).blocks).toBe(live);
  });

  it.each([
    [
      "preformatted",
      '<!-- wp:preformatted -->\n<pre class="wp-block-preformatted">Before.</pre>\n<!-- /wp:preformatted -->'
    ],
    [
      "aligned table",
      '<!-- wp:table -->\n<figure class="wp-block-table"><table><tbody><tr><td style="text-align:right">Before.</td></tr></tbody></table></figure>\n<!-- /wp:table -->'
    ],
    [
      "unknown block",
      '<!-- wp:example/widget {"config":{"label":"Before."}} /-->'
    ]
  ])("keeps an edited %s lossless as serialized Gutenberg", (_label, raw) => {
    const source = `---\ntitle: Lossless block\n---\n\n${raw}\n`;
    const desired = desiredFromMarkdown(source);
    const live = desired.body.replace("Before.", "After.");
    const merged = mergeWordPressIntoSource({
      existing: source,
      desired,
      page: pageFromBody(desired, live)
    });

    expect(merged).toBe(source.replace("Before.", "After."));
    expect(markdownToBlocks(merged, { fallbackTitle: "Docs" }).blocks).toBe(live);
  });

  it("fails instead of rewriting when the source cannot be mapped safely", () => {
    const source = "---\ntitle: Safe failure\n---\n\nOne block.\n";
    const desired = desiredFromMarkdown(source, {
      body: `${markdownToBlocks("One block.\n\nUnexpected block.", { fallbackTitle: "Docs" }).blocks}`
    });

    expect(() => mergeWordPressIntoSource({
      existing: source,
      desired,
      page: pageFromBody(desired, desired.body.replace("Unexpected", "Edited"))
    })).toThrow(/cannot safely map/);
  });

  it("changes a frontmatter title without reformatting neighboring metadata", () => {
    const source = "---\ntitle: Old title\nslug: keep-this\ncustom:\n  nested: true # keep comment\n---\n\nBody.\n";
    const desired = desiredFromMarkdown(source);

    const merged = mergeWordPressIntoSource({
      existing: source,
      desired,
      page: pageFromBody(desired, desired.body, { title: "New title" })
    });

    expect(merged).toBe(source.replace("title: Old title", "title: New title"));
  });

  it("preserves CRLF line endings around an edited block", () => {
    const source = "---\r\ntitle: Windows docs\r\n---\r\n\r\nBefore.\r\n\r\n```js\r\nconst before = true;\r\n```\r\n\r\nAfter.\r\n";
    const desired = desiredFromMarkdown(source);
    const live = desired.body.replace("before", "after");

    const merged = mergeWordPressIntoSource({
      existing: source,
      desired,
      page: pageFromBody(desired, live)
    });

    expect(merged).toBe(source.replace("before", "after"));
    expect(merged.replaceAll("\r\n", "")).not.toContain("\n");
  });

  it.each(customBlockCases)("preserves and round-trips $name", ({ name, omit, change, attributes }) => {
    const original = `<!-- wp:${name} ${JSON.stringify(attributes)} /-->`;
    const source = `---\ntitle: Custom block\n---\n\nBefore.\n\n${original}\n\nAfter.\n`;
    const desired = desiredFromMarkdown(source);
    const equivalentLive = replaceCustomBlock(desired.body, name, (liveAttributes) => {
      delete liveAttributes[omit];
      return liveAttributes;
    });

    expect(mergeWordPressIntoSource({
      existing: source,
      desired,
      page: pageFromBody(desired, equivalentLive)
    })).toBe(source);

    const [changedKey, changedValue] = change;
    const changedLive = replaceCustomBlock(equivalentLive, name, (liveAttributes) => ({
      ...liveAttributes,
      [changedKey]: changedValue
    }));
    const merged = mergeWordPressIntoSource({
      existing: source,
      desired,
      page: pageFromBody(desired, changedLive)
    });
    const expectedAttributes = { ...attributes, [changedKey]: changedValue };
    const expected = source.replace(original, `<!-- wp:${name} ${JSON.stringify(expectedAttributes)} /-->`);
    const roundTrippedBlock = parse(markdownToBlocks(merged, { fallbackTitle: "Docs" }).blocks)
      .find((block) => block.blockName === name);

    expect(merged).toBe(expected);
    expect(roundTrippedBlock?.attrs).toEqual(expectedAttributes);
  });
});

describe("createReverseChanges", () => {
  it("updates an existing Markdown title and reverses managed links", async () => {
    const cwd = await fs.mkdtemp(path.join(os.tmpdir(), "docspress-reverse-"));
    await fs.mkdir(path.join(cwd, "docs"));
    await fs.writeFile(path.join(cwd, "docs", "index.md"), "# Old Docs\n\nOld body.\n");
    await fs.writeFile(path.join(cwd, "docs", "guide.md"), "# Guide\n");
    const desired = desiredFromMarkdown("# Old Docs\n\nOld body.\n");
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
