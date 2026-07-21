import fs from "node:fs/promises";
import path from "node:path";
import matter from "gray-matter";
import { parse as parseBlocks } from "@wordpress/block-serialization-default-parser";
import remarkGfm from "remark-gfm";
import remarkParse from "remark-parse";
import TurndownService from "turndown";
import { gfm } from "turndown-plugin-gfm";
import { unified } from "unified";
import { markdownToBlocks, titleFromMarkdown } from "./markdown.js";
import { stripSentinel } from "./sentinel.js";
import { stableJson, toPosixPath } from "./utils.js";

const REVERSE_BLOCKS = new Set([
  "core/paragraph",
  "core/heading",
  "core/list",
  "core/quote",
  "core/code",
  "core/image",
  "core/table"
]);

const markdownParser = unified().use(remarkParse).use(remarkGfm);
const BLOCK_TOKEN_PATTERN = /<!--\s+(\/)?wp:([a-z][a-z0-9_-]*\/)?([a-z][a-z0-9_-]*)\s+({(?:(?=([^}]+|}+(?=})|(?!}\s+\/?-->)[^])*)\5|[^]*?)}\s+)?(\/)?-->/g;
const CUSTOM_BLOCK_DEFAULTS = {
  "docspress/api-request": {
    method: "GET",
    endpoint: "/wp-json/wp/v2/pages",
    headers: "Accept: application/json\nAuthorization: Bearer $WP_ACCESS_TOKEN",
    requestBody: "",
    requestBodyFormat: "json",
    responseStatus: "200 OK",
    responseBody: "{\n  \"id\": 42,\n  \"slug\": \"getting-started\"\n}",
    responseBodyFormat: "json"
  },
  "docspress/callout": {
    tone: "note",
    title: "Good to know",
    content: "<p>Add the detail readers need at exactly the right moment.</p>",
    collapsible: false,
    open: true
  },
  "docspress/code-tabs": {
    tabs: [
      { label: "JavaScript", language: "javascript", filename: "example.js", code: "const docs = await publish();" },
      { label: "PHP", language: "php", filename: "example.php", code: "$docs = docspress_publish();" }
    ],
    showLineNumbers: true,
    caption: ""
  },
  "docspress/colorful-code": {
    language: "javascript",
    filename: "",
    code: "const hello = \"DocsPress\";\nconsole.log( hello );",
    highlightedLines: "",
    showLineNumbers: true,
    caption: ""
  },
  "docspress/file-tree": {
    root: "project/",
    tree: "docs/\n  getting-started.md\n  api/\n    endpoints.md\npackage.json",
    caption: ""
  },
  "docspress/prompt": {
    prompt: "Use $docspress-install to review this repository's documentation setup. Return a short plan before writing code.",
    model: "GPT-5",
    mode: "code",
    thinking: true,
    context: "$docspress-install, @repository, src/sync.js, docs/",
    caption: "Prompt example"
  },
  "docspress/result": {
    status: "success",
    title: "Deployment completed",
    content: "<p>All documentation pages are up to date.</p>",
    meta: "12 pages · 1.8s"
  },
  "docspress/terminal-session": {
    title: "Terminal",
    shell: "bash",
    prompt: "$",
    command: "npx docspress publish ./docs",
    output: "✓ Read 12 documents\n✓ Published 12 WordPress pages"
  }
};

export function blocksToMarkdown(content, options = {}) {
  const chunks = contentBlockChunks(content, options);
  const service = createTurndownService(options.resolveLink);
  const rendered = chunks
    .map((chunk) => blockChunkToMarkdown(chunk, service))
    .filter(Boolean)
    .join("\n\n")
    .replace(/\n{3,}/g, "\n\n")
    .trim();

  return rendered ? `${rendered}\n` : "";
}

export function mergeWordPressIntoSource(options) {
  const {
    existing,
    page,
    desired,
    createH1 = false,
    resolveLink
  } = options;
  const desiredChunks = contentBlockChunks(desired.body, {
    createH1,
    title: desired.title
  });
  const liveChunks = contentBlockChunks(page.content, {
    createH1,
    title: page.title
  });
  const layout = sourceBlockLayout(existing, desired, desiredChunks.length);
  const service = createTurndownService(resolveLink);
  const hunks = blockChangeHunks(desiredChunks, liveChunks);
  const lineEnding = existing.includes("\r\n") ? "\r\n" : "\n";
  const separator = `${lineEnding}${lineEnding}`;
  let body = layout.body;

  const replacements = hunks.map((hunk) => {
    const oldCount = hunk.oldEnd - hunk.oldStart;
    const newCount = hunk.newEnd - hunk.newStart;
    const rendered = liveChunks
      .slice(hunk.newStart, hunk.newEnd)
      .map((chunk, index) => blockChunkToSourceMarkdown(
        chunk,
        service,
        oldCount === newCount ? desiredChunks[hunk.oldStart + index] : null
      ))
      .filter(Boolean)
      .join("\n\n")
      .replace(/\r?\n/g, lineEnding);

    if (oldCount > 0) {
      let start = layout.blockRanges[hunk.oldStart].start;
      let end = layout.blockRanges[hunk.oldEnd - 1].end;
      if (!rendered && hunk.oldEnd < layout.blockRanges.length) {
        end = layout.blockRanges[hunk.oldEnd].start;
      } else if (!rendered && hunk.oldStart > 0) {
        start = layout.blockRanges[hunk.oldStart - 1].end;
      } else if (!rendered && /^\s*$/.test(body.slice(end))) {
        end = body.length;
      }
      return {
        start,
        end,
        content: rendered
      };
    }

    const atEnd = hunk.oldStart >= layout.blockRanges.length;
    const offset = atEnd
      ? layout.blockRanges.at(-1)?.end ?? body.length
      : layout.blockRanges[hunk.oldStart].start;
    if (layout.blockRanges.length === 0 && rendered) {
      const hasFrontmatter = existing.length !== layout.body.length;
      const before = body.trim()
        ? separator
        : body.length === 0 && hasFrontmatter
          ? lineEnding
          : "";
      return {
        start: offset,
        end: offset,
        content: `${before}${rendered}${lineEnding}`
      };
    }
    return {
      start: offset,
      end: offset,
      content: rendered
        ? `${atEnd && layout.blockRanges.length > 0 ? separator : ""}${rendered}${atEnd ? "" : separator}`
        : ""
    };
  });

  for (const replacement of replacements.sort((left, right) => right.start - left.start)) {
    body = `${body.slice(0, replacement.start)}${replacement.content}${body.slice(replacement.end)}`;
  }

  return updateSourceMarkdown({
    existing,
    body,
    title: page.title,
    desired,
    preserveBody: true
  });
}

function contentBlockChunks(content, options = {}) {
  const chunks = splitSerializedBlocks(stripSentinel(content));
  removeGeneratedSourceLink(chunks);
  removeGeneratedTitle(chunks, options);
  return chunks.filter((chunk) => (chunk.block || chunk.raw.trim()) && !isIgnorableEmptyBlock(chunk));
}

function isIgnorableEmptyBlock(chunk) {
  const html = chunk.block?.innerHTML || chunk.raw;
  return (!chunk.block || chunk.block.blockName === "core/paragraph") &&
    /^\s*<p>(?:\s|&nbsp;|<br\s*\/?\s*>)*<\/p>\s*$/i.test(html || "");
}

function blockChangeHunks(desiredChunks, liveChunks) {
  const desired = desiredChunks.map(blockFingerprint);
  const live = liveChunks.map(blockFingerprint);
  const rows = desired.length + 1;
  const columns = live.length + 1;
  const lengths = Array.from({ length: rows }, () => new Uint32Array(columns));

  for (let oldIndex = desired.length - 1; oldIndex >= 0; oldIndex -= 1) {
    for (let newIndex = live.length - 1; newIndex >= 0; newIndex -= 1) {
      lengths[oldIndex][newIndex] = desired[oldIndex] === live[newIndex]
        ? lengths[oldIndex + 1][newIndex + 1] + 1
        : Math.max(lengths[oldIndex + 1][newIndex], lengths[oldIndex][newIndex + 1]);
    }
  }

  const matches = [];
  let oldIndex = 0;
  let newIndex = 0;
  while (oldIndex < desired.length && newIndex < live.length) {
    if (desired[oldIndex] === live[newIndex]) {
      matches.push([oldIndex, newIndex]);
      oldIndex += 1;
      newIndex += 1;
    } else if (lengths[oldIndex + 1][newIndex] >= lengths[oldIndex][newIndex + 1]) {
      oldIndex += 1;
    } else {
      newIndex += 1;
    }
  }

  const hunks = [];
  let previousOld = -1;
  let previousNew = -1;
  for (const [matchedOld, matchedNew] of [...matches, [desired.length, live.length]]) {
    const oldStart = previousOld + 1;
    const newStart = previousNew + 1;
    if (oldStart < matchedOld || newStart < matchedNew) {
      hunks.push({ oldStart, oldEnd: matchedOld, newStart, newEnd: matchedNew });
    }
    previousOld = matchedOld;
    previousNew = matchedNew;
  }
  return hunks;
}

function blockFingerprint(chunk) {
  if (!chunk.block) {
    return stableJson({ name: null, content: chunk.raw.trim() });
  }

  const block = normalizeBlockForComparison(chunk.block);
  const name = block.blockName;
  if (Object.hasOwn(CUSTOM_BLOCK_DEFAULTS, name)) {
    return stableJson({ name, attrs: effectiveCustomAttributes(name, block.attrs) });
  }

  const service = createTurndownService();
  const comparable = { ...chunk, block };
  let content = canConvertBlock(block)
    ? blockChunkToMarkdown(comparable, service)
    : stableJson({ innerHTML: block.innerHTML, innerBlocks: block.innerBlocks });
  if (name === "core/code") {
    content = content.replace(/^(`{3,})[^\n]*\n/, "$1\n");
  }

  return stableJson({ name, attrs: block.attrs || {}, content });
}

function normalizeBlockForComparison(block) {
  const attrs = { ...(block.attrs || {}) };
  if (block.blockName === "core/code") {
    delete attrs.tokenizedLines;
  }
  if (block.blockName === "core/table" && attrs.hasFixedLayout === false) {
    delete attrs.hasFixedLayout;
  }
  if (block.blockName === "core/paragraph" && attrs.dropCap === false) {
    delete attrs.dropCap;
  }
  if (block.blockName === "core/heading" && attrs.level === 2) {
    delete attrs.level;
  }
  if (block.blockName === "core/list" && attrs.ordered === false) {
    delete attrs.ordered;
  }
  return { ...block, attrs };
}

function effectiveCustomAttributes(name, attributes) {
  return {
    ...(CUSTOM_BLOCK_DEFAULTS[name] || {}),
    ...(attributes || {})
  };
}

function blockChunkToSourceMarkdown(chunk, service, originalChunk) {
  const name = chunk.block?.blockName || "";
  if (Object.hasOwn(CUSTOM_BLOCK_DEFAULTS, name)) {
    return serializeCustomBlockForMarkdown(chunk.block, originalChunk?.block);
  }

  const normalized = chunk.block ? normalizeBlockForComparison(chunk.block) : null;
  if (normalized && canConvertBlock(normalized)) {
    return blockChunkToMarkdown(
      { ...chunk, block: normalized },
      service,
      { fallbackLanguage: codeLanguage(originalChunk?.raw || "") }
    );
  }

  return serializeRawBlockForMarkdown(chunk);
}

function serializeCustomBlockForMarkdown(block, originalBlock) {
  const attrs = mergeCustomAttributes(
    block.blockName,
    originalBlock?.attrs || {},
    block.attrs || {}
  );
  const serialized = Object.keys(attrs).length > 0 ? ` ${JSON.stringify(attrs)}` : "";
  return `<!-- wp:${block.blockName}${serialized} /-->`;
}

function mergeCustomAttributes(name, original, live) {
  const defaults = CUSTOM_BLOCK_DEFAULTS[name] || {};
  const result = {};
  const keys = new Set([...Object.keys(defaults), ...Object.keys(original), ...Object.keys(live)]);

  for (const key of keys) {
    const originalHas = Object.hasOwn(original, key);
    const liveHas = Object.hasOwn(live, key);
    const originalValue = originalHas ? original[key] : defaults[key];
    const liveValue = liveHas ? live[key] : defaults[key];

    if (originalHas && stableJson(originalValue) === stableJson(liveValue)) {
      result[key] = original[key];
    } else if (liveHas || stableJson(liveValue) !== stableJson(defaults[key])) {
      result[key] = liveValue;
    }
  }

  return result;
}

function serializeRawBlockForMarkdown(chunk) {
  if (!chunk.block) {
    return chunk.raw.trim();
  }
  const attrs = chunk.block.attrs || {};
  const serialized = Object.keys(attrs).length > 0 ? ` ${JSON.stringify(attrs)}` : "";
  const name = chunk.block.blockName.startsWith("core/")
    ? chunk.block.blockName.slice("core/".length)
    : chunk.block.blockName;
  const openerPattern = new RegExp(BLOCK_TOKEN_PATTERN.source);
  return chunk.raw.trim().replace(
    openerPattern,
    (_match, closing, _namespace, _blockName, _attributes, _balanced, selfClosing) => closing
      ? _match
      : `<!-- wp:${name}${serialized}${selfClosing ? " /" : " "}-->`
  );
}

function sourceBlockLayout(existing, desired, desiredBlockCount) {
  const parsed = matter(existing);
  const body = parsed.content;
  const specialRanges = findSpecialMarkdownRanges(body);
  const masked = maskRanges(body, specialRanges);
  const tree = markdownParser.parse(masked);
  const frontmatterTitle = typeof parsed.data.title === "string" && parsed.data.title.trim();
  const titleNode = frontmatterTitle
    ? null
    : tree.children.find((node) => node.type === "heading" && node.depth === 1);
  const entries = [
    ...specialRanges,
    ...tree.children
      .filter((node) => node !== titleNode && node.position?.start?.offset !== undefined && node.position?.end?.offset !== undefined)
      .map((node) => ({
        start: node.position.start.offset,
        end: node.position.end.offset,
        type: node.type
      }))
  ].sort((left, right) => left.start - right.start);
  const blockRanges = [];

  for (const entry of entries) {
    const source = body.slice(entry.start, entry.end);
    const count = sourceChunkBlockCount(source, desired.title);
    if (count === 0) {
      continue;
    }
    if (count !== 1) {
      throw new Error(`Docspress cannot safely map a Markdown source region that produces ${count} Gutenberg blocks.`);
    }
    blockRanges.push({ start: entry.start, end: entry.end });
  }

  if (blockRanges.length !== desiredBlockCount) {
    throw new Error(
      `Docspress cannot safely map ${desiredBlockCount} Gutenberg blocks onto ${blockRanges.length} Markdown source regions for ${desired.sourcePath}.`
    );
  }

  return { body, blockRanges };
}

function sourceChunkBlockCount(source, fallbackTitle) {
  const wrapped = `---\ntitle: ${JSON.stringify(fallbackTitle || "Docs")}\n---\n\n${source}`;
  const rendered = markdownToBlocks(wrapped, { fallbackTitle: fallbackTitle || "Docs" }).blocks;
  return splitSerializedBlocks(rendered).filter((chunk) => chunk.block || chunk.raw.trim()).length;
}

function findSpecialMarkdownRanges(source) {
  const rawTree = markdownParser.parse(source);
  const codeRanges = [];
  collectNodeRanges(rawTree, (node) => node.type === "code" || node.type === "inlineCode", codeRanges);
  const ranges = [];
  const tokens = new RegExp(BLOCK_TOKEN_PATTERN.source, "g");
  let depth = 0;
  let start = -1;
  let match;

  while ((match = tokens.exec(source))) {
    if (rangeContains(codeRanges, match.index)) {
      continue;
    }
    const closing = Boolean(match[1]);
    const selfClosing = Boolean(match[6]);
    if (!closing && depth === 0) {
      start = match.index;
    }
    if (closing) {
      depth = Math.max(0, depth - 1);
      if (depth === 0 && start >= 0) {
        ranges.push({ start, end: tokens.lastIndex, type: "gutenberg" });
        start = -1;
      }
    } else if (selfClosing) {
      if (depth === 0 && start >= 0) {
        ranges.push({ start, end: tokens.lastIndex, type: "gutenberg" });
        start = -1;
      }
    } else {
      depth += 1;
    }
  }

  const codetabs = /{%\s*codetabs\s*%}[\s\S]*?{%\s*end\s*%}/g;
  while ((match = codetabs.exec(source))) {
    if (!rangeContains(codeRanges, match.index) && !ranges.some((range) => overlaps(range, { start: match.index, end: codetabs.lastIndex }))) {
      ranges.push({ start: match.index, end: codetabs.lastIndex, type: "codetabs" });
    }
  }

  return ranges.sort((left, right) => left.start - right.start);
}

function collectNodeRanges(node, predicate, ranges) {
  if (predicate(node) && node.position?.start?.offset !== undefined && node.position?.end?.offset !== undefined) {
    ranges.push({ start: node.position.start.offset, end: node.position.end.offset });
  }
  for (const child of node.children || []) {
    collectNodeRanges(child, predicate, ranges);
  }
}

function rangeContains(ranges, offset) {
  return ranges.some((range) => offset >= range.start && offset < range.end);
}

function overlaps(left, right) {
  return left.start < right.end && right.start < left.end;
}

function maskRanges(source, ranges) {
  const characters = source.split("");
  for (const range of ranges) {
    for (let index = range.start; index < range.end; index += 1) {
      if (characters[index] !== "\n" && characters[index] !== "\r") {
        characters[index] = " ";
      }
    }
  }
  return characters.join("");
}

export async function createReverseChanges(options) {
  const {
    cwd = process.cwd(),
    pages,
    desiredPages,
    manifestFile = "",
    createH1 = false
  } = options;
  const desiredByKey = new Map(desiredPages.map((page) => [page.key, page]));
  const pageByKey = new Map(pages.map(({ page }) => [page.sentinel.key, page]));
  const changes = new Map();
  const manifestTitles = new Map();

  for (const { page, desired } of pages) {
    const sourcePath = validateSourcePath(page.sentinel.source, cwd);
    const absolutePath = path.resolve(cwd, sourcePath);
    const existing = await fs.readFile(absolutePath, "utf8");
    const resolveLink = createReverseLinkResolver({
      sourcePath,
      desiredByKey,
      pageByKey
    });
    const content = mergeWordPressIntoSource({
      existing,
      page,
      title: page.title,
      desired,
      createH1,
      resolveLink
    });

    if (content !== existing) {
      changes.set(sourcePath, content);
    }

    if (desired.titleOverride && desired.manifestId && desired.title !== page.title) {
      manifestTitles.set(desired.manifestId, page.title);
    }
  }

  if (manifestTitles.size > 0) {
    if (!manifestFile) {
      throw new Error("A manifest-backed WordPress title changed, but manifest-file is not configured.");
    }
    const manifestPath = validateRepositoryPath(manifestFile, cwd, "manifest-file");
    const absoluteManifest = path.resolve(cwd, manifestPath);
    const existingManifest = await fs.readFile(absoluteManifest, "utf8");
    const manifest = JSON.parse(existingManifest);
    const entries = Array.isArray(manifest) ? manifest : manifest.pages || manifest.items;
    if (!Array.isArray(entries)) {
      throw new Error(`Docspress manifest must contain a pages array: ${manifestPath}`);
    }
    for (const [id, title] of manifestTitles.entries()) {
      const entry = entries.find((candidate) => String(candidate?.id || candidate?.slug || "") === id);
      if (!entry) {
        throw new Error(`Docspress could not find manifest entry ${id} while applying a WordPress title change.`);
      }
      entry.title = title;
    }
    const serialized = `${JSON.stringify(manifest, null, 2)}\n`;
    if (serialized !== existingManifest) {
      changes.set(manifestPath, serialized);
    }
  }

  return Array.from(changes, ([filePath, content]) => ({ path: filePath, content }));
}

function createTurndownService(resolveLink) {
  const service = new TurndownService({
    headingStyle: "atx",
    bulletListMarker: "-",
    codeBlockStyle: "fenced",
    emDelimiter: "*",
    strongDelimiter: "**"
  });
  service.use(gfm);
  service.addRule("docspress-links", {
    filter: "a",
    replacement(content, node) {
      const href = resolveLink ? resolveLink(node.getAttribute("href") || "") : node.getAttribute("href") || "";
      const title = node.getAttribute("title");
      const destination = title ? `${href} \"${title.replace(/\"/g, "\\\"")}\"` : href;
      return `[${content}](${destination})`;
    }
  });
  return service;
}

function blockChunkToMarkdown(chunk, service, options = {}) {
  if (!chunk.block) {
    return chunk.raw.trim() ? service.turndown(chunk.raw).trim() : "";
  }

  const name = chunk.block.blockName;
  if (!canConvertBlock(chunk.block)) {
    return chunk.raw.trim();
  }
  if (name === "core/separator") {
    return "---";
  }
  if (name === "core/html") {
    return chunk.block.innerHTML.trim();
  }
  if (name === "core/code") {
    return codeBlockToMarkdown(chunk.raw, service, options.fallbackLanguage);
  }
  if (name === "core/image") {
    return imageBlockToMarkdown(chunk.raw, service);
  }
  if (REVERSE_BLOCKS.has(name)) {
    const markdown = service.turndown(stripBlockComments(chunk.raw)).trim();
    return name === "core/list"
      ? markdown
        .replace(/^(\s*[-+*])\s{2,}/gm, "$1 ")
        .replace(/^(\s*\d+\.)\s{2,}/gm, "$1 ")
        .replace(/^(\s*(?:[-+*]|\d+\.)\s+\[[ xX]\])\s{2,}/gm, "$1 ")
      : markdown;
  }

  return chunk.raw.trim();
}

function imageBlockToMarkdown(raw, service) {
  const html = stripBlockComments(raw);
  const image = html.match(/<img[^>]*src=["']([^"']*)["'][^>]*>/i)?.[0] || "";
  const source = image.match(/\ssrc=["']([^"']*)["']/i)?.[1] || "";
  const alt = image.match(/\salt=["']([^"']*)["']/i)?.[1] || "";
  const captionHtml = html.match(/<figcaption[^>]*>([\s\S]*?)<\/figcaption>/i)?.[1] || "";
  const caption = captionHtml ? service.turndown(captionHtml).trim() : "";
  const title = caption ? ` \"${caption.replace(/\"/g, "\\\"")}\"` : "";
  return `![${alt}](${source}${title})`;
}

function canConvertBlock(block) {
  if (!block?.blockName?.startsWith("core/") || block.innerBlocks?.length > 0) {
    return false;
  }
  const allowedAttrs = {
    "core/paragraph": [],
    "core/heading": ["level"],
    "core/list": ["ordered"],
    "core/quote": [],
    "core/code": [],
    "core/image": ["url", "alt"],
    "core/table": [],
    "core/separator": [],
    "core/html": []
  }[block.blockName];
  if (block.blockName === "core/table" && /text-align\s*:/i.test(block.innerHTML)) {
    return false;
  }
  return Array.isArray(allowedAttrs) && Object.keys(block.attrs || {}).every((key) => allowedAttrs.includes(key));
}

function codeBlockToMarkdown(raw, service, fallbackLanguage = "") {
  const html = stripBlockComments(raw);
  const match = html.match(/<pre[^>]*>\s*<code(?:[^>]*class=["'][^"']*language-([\w-]+)[^"']*["'])?[^>]*>([\s\S]*?)<\/code>\s*<\/pre>/i);
  if (!match) {
    return service.turndown(html).trim();
  }
  const language = match[1] || fallbackLanguage || "";
  const decoded = service.turndown(`<p>${match[2]}</p>`).replace(/\\([\\`*_{}\[\]()#+\-.!])/g, "$1");
  const fence = decoded.includes("```") ? "````" : "```";
  return `${fence}${language}\n${decoded}\n${fence}`;
}

function codeLanguage(raw) {
  return String(raw || "").match(/<code(?:[^>]*class=["'][^"']*language-([\w-]+)[^"']*["'])/i)?.[1] || "";
}

function removeGeneratedSourceLink(chunks) {
  for (let index = chunks.length - 1; index >= 0; index -= 1) {
    const chunk = chunks[index];
    if (!chunk.raw.trim()) {
      continue;
    }
    const isSourceLink = chunk.block?.blockName === "core/paragraph" && (
      chunk.block.attrs?.className === "docspress-source-link" ||
      /class=["'][^"']*docspress-source-link/.test(chunk.block.innerHTML)
    );
    if (isSourceLink) {
      chunks.splice(index, 1);
    }
    return;
  }
}

function removeGeneratedTitle(chunks, options) {
  if (!options.createH1) {
    return;
  }
  const index = chunks.findIndex((chunk) => chunk.raw.trim());
  const chunk = chunks[index];
  const isH1 = chunk?.block?.blockName === "core/heading" && (
    chunk.block.attrs?.level === 1 || /<h1(?:\s|>)/i.test(chunk.block.innerHTML)
  );
  if (!isH1) {
    throw new Error("The generated title H1 is missing from the WordPress Page.");
  }
  const title = new TurndownService({ headingStyle: "atx" }).turndown(chunk.block.innerHTML).replace(/^#\s+/, "").trim();
  if (normalizeText(title) !== normalizeText(options.title)) {
    throw new Error("The WordPress Page title and generated title H1 changed independently.");
  }
  chunks.splice(index, 1);
}

function splitSerializedBlocks(value) {
  const source = String(value || "");
  const tokenPattern = new RegExp(BLOCK_TOKEN_PATTERN.source, "g");
  const chunks = [];
  let depth = 0;
  let blockStart = -1;
  let cursor = 0;
  let match;

  while ((match = tokenPattern.exec(source))) {
    const closing = Boolean(match[1]);
    const selfClosing = Boolean(match[6]);
    if (!closing && depth === 0) {
      if (match.index > cursor) {
        pushChunk(chunks, source.slice(cursor, match.index));
      }
      blockStart = match.index;
    }

    if (closing) {
      depth = Math.max(0, depth - 1);
      if (depth === 0 && blockStart >= 0) {
        pushChunk(chunks, source.slice(blockStart, tokenPattern.lastIndex));
        cursor = tokenPattern.lastIndex;
        blockStart = -1;
      }
    } else if (selfClosing) {
      if (depth === 0 && blockStart >= 0) {
        pushChunk(chunks, source.slice(blockStart, tokenPattern.lastIndex));
        cursor = tokenPattern.lastIndex;
        blockStart = -1;
      }
    } else {
      depth += 1;
    }
  }

  if (blockStart >= 0) {
    pushChunk(chunks, source.slice(blockStart));
  } else if (cursor < source.length) {
    pushChunk(chunks, source.slice(cursor));
  }
  return chunks;
}

function pushChunk(chunks, raw) {
  const parsed = parseBlocks(raw);
  chunks.push({
    raw,
    block: parsed.find((block) => block.blockName) || null
  });
}

function stripBlockComments(value) {
  return String(value || "").replace(/<!--\s*\/?wp:[\s\S]*?-->/g, "").trim();
}

function updateSourceMarkdown({ existing, body, title, desired, preserveBody = false }) {
  const parsed = matter(existing);
  const frontmatterTitle = typeof parsed.data.title === "string" && parsed.data.title.trim();
  const titleInfo = titleFromMarkdown(existing, desired.title);
  const usedHeadingTitle = !frontmatterTitle && titleInfo.removeFirstHeading;
  const titleChanged = desired.title !== title;
  let nextBody = preserveBody ? body : body.trim();

  if (usedHeadingTitle && titleChanged && preserveBody) {
    nextBody = replaceFirstTitleHeading(nextBody, title);
  } else if (usedHeadingTitle && !preserveBody) {
    nextBody = `# ${title}\n\n${nextBody}`.trim();
  }

  const data = { ...parsed.data };
  let frontmatterChanged = false;
  if (frontmatterTitle) {
    if (data.title !== title) {
      data.title = title;
      frontmatterChanged = true;
    }
  } else if (titleChanged && !usedHeadingTitle && !desired.titleOverride) {
    data.title = title;
    frontmatterChanged = true;
  }

  const normalizedBody = preserveBody ? nextBody : nextBody ? `${nextBody}\n` : "";
  if (frontmatterChanged) {
    const prefix = existing.slice(0, existing.length - parsed.content.length);
    const updatedPrefix = replaceFrontmatterTitle(prefix, title);
    if (updatedPrefix !== null) {
      return `${updatedPrefix}${normalizedBody}`;
    }
    return matter.stringify(normalizedBody, data);
  }

  const prefix = existing.slice(0, existing.length - parsed.content.length);
  return `${prefix}${normalizedBody}`;
}

function replaceFrontmatterTitle(prefix, title) {
  const pattern = /^(title[ \t]*:)[ \t]*([^\r\n]*)(\r?\n|$)/gm;
  const matches = [...prefix.matchAll(pattern)];
  if (matches.length !== 1 || /^[>|][+-]?\s*(?:#.*)?$/.test(matches[0][2].trim())) {
    return null;
  }

  const match = matches[0];
  const currentValue = match[2].trim();
  const value = yamlTitleScalar(title, currentValue);
  const replacement = `${match[1]} ${value}${match[3]}`;
  return `${prefix.slice(0, match.index)}${replacement}${prefix.slice(match.index + match[0].length)}`;
}

function yamlTitleScalar(title, currentValue) {
  const value = String(title || "");
  if (currentValue.startsWith("'") && currentValue.endsWith("'")) {
    return `'${value.replace(/'/g, "''")}'`;
  }
  if (currentValue.startsWith('"') && currentValue.endsWith('"')) {
    return JSON.stringify(value);
  }
  if (isSafePlainYamlScalar(value)) {
    return value;
  }
  return JSON.stringify(value);
}

function isSafePlainYamlScalar(value) {
  return Boolean(value) &&
    value === value.trim() &&
    !/[\r\n]/.test(value) &&
    !/^(?:[-?:,\[\]{}#&*!|>'"%@`]\s*|(?:null|true|false|yes|no|on|off|~)$)/i.test(value) &&
    !/:\s|\s#/.test(value);
}

function replaceFirstTitleHeading(body, title) {
  const tree = markdownParser.parse(body);
  const heading = tree.children.find((node) => node.type === "heading" && node.depth === 1);
  if (!heading?.position?.start || !heading?.position?.end) {
    throw new Error("Docspress could not find the Markdown H1 that supplies the Page title.");
  }
  const start = heading.position.start.offset;
  const end = heading.position.end.offset;
  return `${body.slice(0, start)}# ${title}${body.slice(end)}`;
}

function createReverseLinkResolver({ sourcePath, desiredByKey, pageByKey }) {
  const aliases = new Map();
  for (const [key, desired] of desiredByKey.entries()) {
    if (!isFileSource(desired.sourcePath)) {
      continue;
    }
    aliases.set(`/${key}`, desired.sourcePath);
    aliases.set(`/${key}/`, desired.sourcePath);
    const pageLink = pageByKey.get(key)?.link;
    if (pageLink) {
      try {
        const url = new URL(pageLink);
        aliases.set(url.href, desired.sourcePath);
        aliases.set(url.pathname, desired.sourcePath);
        aliases.set(url.pathname.replace(/\/$/, ""), desired.sourcePath);
      } catch {
        // Keep path-based aliases when WordPress returns a non-standard link.
      }
    }
  }

  return (value) => {
    const raw = String(value || "");
    const match = raw.match(/^([^?#]*)(\?[^#]*)?(#.*)?$/);
    const target = aliases.get(match?.[1]);
    if (!target) {
      return raw;
    }
    const relative = toPosixPath(path.posix.relative(path.posix.dirname(sourcePath), target)) || path.posix.basename(target);
    return `${relative}${match?.[2] || ""}${match?.[3] || ""}`;
  };
}

function validateSourcePath(value, cwd) {
  const sourcePath = validateRepositoryPath(value, cwd, "managed Page source");
  if (!/\.(?:md|markdown)$/i.test(sourcePath)) {
    throw new Error(`WordPress reverse sync only supports Markdown sources: ${sourcePath}`);
  }
  return sourcePath;
}

function validateRepositoryPath(value, cwd, label) {
  const raw = String(value || "");
  if (!raw || raw.includes(":") || path.isAbsolute(raw)) {
    throw new Error(`Invalid ${label}: ${raw || "(empty)"}`);
  }
  const resolved = path.resolve(cwd, raw);
  const relative = path.relative(cwd, resolved);
  if (!relative || relative.startsWith("..") || path.isAbsolute(relative)) {
    throw new Error(`The ${label} must stay inside the checked-out repository: ${raw}`);
  }
  return toPosixPath(relative);
}

function isFileSource(value) {
  return Boolean(value) && !String(value).includes(":") && /\.(?:md|markdown)$/i.test(value);
}

function normalizeText(value) {
  return String(value || "").trim().replace(/\s+/g, " ").toLowerCase();
}
