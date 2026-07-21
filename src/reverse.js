import fs from "node:fs/promises";
import path from "node:path";
import matter from "gray-matter";
import { parse as parseBlocks } from "@wordpress/block-serialization-default-parser";
import TurndownService from "turndown";
import { gfm } from "turndown-plugin-gfm";
import { titleFromMarkdown } from "./markdown.js";
import { stripSentinel } from "./sentinel.js";
import { toPosixPath } from "./utils.js";

const REVERSE_BLOCKS = new Set([
  "core/paragraph",
  "core/heading",
  "core/list",
  "core/quote",
  "core/code",
  "core/preformatted",
  "core/image",
  "core/table"
]);

export function blocksToMarkdown(content, options = {}) {
  const chunks = splitSerializedBlocks(stripSentinel(content));
  removeGeneratedSourceLink(chunks);
  removeGeneratedTitle(chunks, options);
  const service = createTurndownService(options.resolveLink);
  const rendered = chunks
    .map((chunk) => blockChunkToMarkdown(chunk, service))
    .filter(Boolean)
    .join("\n\n")
    .replace(/\n{3,}/g, "\n\n")
    .trim();

  return rendered ? `${rendered}\n` : "";
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
    const body = blocksToMarkdown(page.content, {
      createH1,
      title: page.title,
      resolveLink: createReverseLinkResolver({
        sourcePath,
        desiredByKey,
        pageByKey
      })
    });
    const content = updateSourceMarkdown({
      existing,
      body,
      title: page.title,
      desired
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

function blockChunkToMarkdown(chunk, service) {
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
  if (name === "core/code" || name === "core/preformatted") {
    return codeBlockToMarkdown(chunk.raw, service);
  }
  if (name === "core/image") {
    return imageBlockToMarkdown(chunk.raw, service);
  }
  if (REVERSE_BLOCKS.has(name)) {
    return service.turndown(stripBlockComments(chunk.raw)).trim();
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
    "core/preformatted": [],
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

function codeBlockToMarkdown(raw, service) {
  const html = stripBlockComments(raw);
  const match = html.match(/<pre[^>]*>\s*<code(?:[^>]*class=["'][^"']*language-([\w-]+)[^"']*["'])?[^>]*>([\s\S]*?)<\/code>\s*<\/pre>/i);
  if (!match) {
    return service.turndown(html).trim();
  }
  const language = match[1] || "";
  const decoded = service.turndown(`<p>${match[2]}</p>`).replace(/\\([\\`*_{}\[\]()#+\-.!])/g, "$1");
  const fence = decoded.includes("```") ? "````" : "```";
  return `${fence}${language}\n${decoded}\n${fence}`;
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
  const tokenPattern = /<!--\s*(\/?)wp:([\w/-]+)(?:\s+[\s\S]*?)?\s*(\/)?-->/g;
  const chunks = [];
  let depth = 0;
  let blockStart = -1;
  let cursor = 0;
  let match;

  while ((match = tokenPattern.exec(source))) {
    const closing = Boolean(match[1]);
    const selfClosing = Boolean(match[3]);
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

function updateSourceMarkdown({ existing, body, title, desired }) {
  const parsed = matter(existing);
  const frontmatterTitle = typeof parsed.data.title === "string" && parsed.data.title.trim();
  const titleInfo = titleFromMarkdown(existing, desired.title);
  const usedHeadingTitle = !frontmatterTitle && titleInfo.removeFirstHeading;
  const titleChanged = desired.title !== title;
  let nextBody = body.trim();

  if (usedHeadingTitle) {
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

  const normalizedBody = nextBody ? `${nextBody}\n` : "";
  if (frontmatterChanged) {
    return matter.stringify(normalizedBody, data);
  }

  const prefix = existing.slice(0, existing.length - parsed.content.length);
  return `${prefix}${normalizedBody}`;
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
