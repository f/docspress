import fs from "node:fs/promises";
import path from "node:path";
import fg from "fast-glob";
import { headingBlock } from "./gutenberg.js";
import { markdownToBlocks } from "./markdown.js";
import { prependSentinel } from "./sentinel.js";
import { escapeHtml, normalizeBoolean, sha256, slugify, stableJson, titleFromSlug, toPosixPath } from "./utils.js";

const INDEX_FILENAMES = new Set(["index", "readme"]);

export async function collectDesiredPages(options) {
  const cwd = options.cwd || process.cwd();
  const docsDir = options.docsDir || "docs";
  const absoluteDocsDir = path.resolve(cwd, docsDir);
  const docsDirForSource = toPosixPath(path.relative(cwd, absoluteDocsDir)) || docsDir;

  const files = await fg(["**/*.md", "**/*.markdown"], {
    cwd: absoluteDocsDir,
    onlyFiles: true,
    dot: false,
    unique: true
  });

  const byRoute = new Map();

  for (const file of files.sort()) {
    const absolutePath = path.join(absoluteDocsDir, file);
    const markdown = await fs.readFile(absolutePath, "utf8");
    const routeSegments = routeSegmentsForFile(file);
    const routeKey = routeSegments.join("/");

    if (byRoute.has(routeKey)) {
      throw new Error(`Multiple Markdown files map to the same docs page: ${byRoute.get(routeKey).sourcePath} and ${file}`);
    }

    const fallbackTitle = fallbackTitleForRoute(routeSegments, options.rootTitle);
    const converted = markdownToBlocks(markdown, {
      fallbackTitle,
      createH1: options.createH1
    });
    const sourcePath = `${docsDirForSource}/${toPosixPath(file)}`;

    byRoute.set(routeKey, {
      kind: "file",
      routeKey,
      routeSegments,
      sourcePath,
      sourceMarkdown: markdown,
      title: converted.title,
      body: converted.blocks,
      frontmatter: converted.data
    });
  }

  ensurePlaceholderHierarchy(byRoute, options.rootTitle);

  return Array.from(byRoute.values())
    .map((page) => finalizePage(page, options))
    .sort((a, b) => a.depth - b.depth || a.key.localeCompare(b.key));
}

function routeSegmentsForFile(file) {
  const parsed = path.posix.parse(toPosixPath(file));
  const dirSegments = parsed.dir ? parsed.dir.split("/").filter(Boolean).map((part) => slugify(part)) : [];
  const baseSlug = slugify(parsed.name);

  if (INDEX_FILENAMES.has(parsed.name.toLowerCase())) {
    return dirSegments;
  }

  return [...dirSegments, baseSlug];
}

function fallbackTitleForRoute(routeSegments, rootTitle) {
  if (routeSegments.length === 0) {
    return rootTitle || "Docs";
  }

  return titleFromSlug(routeSegments[routeSegments.length - 1]);
}

function ensurePlaceholderHierarchy(byRoute, rootTitle) {
  if (!byRoute.has("")) {
    byRoute.set("", placeholderPage([], rootTitle || "Docs"));
  }

  for (const routeKey of Array.from(byRoute.keys())) {
    const routeSegments = routeKey ? routeKey.split("/") : [];
    for (let index = 1; index < routeSegments.length; index += 1) {
      const ancestorSegments = routeSegments.slice(0, index);
      const ancestorKey = ancestorSegments.join("/");
      if (!byRoute.has(ancestorKey)) {
        byRoute.set(ancestorKey, placeholderPage(ancestorSegments, titleFromSlug(ancestorSegments.at(-1))));
      }
    }
  }
}

function placeholderPage(routeSegments, title) {
  return {
    kind: "placeholder",
    routeKey: routeSegments.join("/"),
    routeSegments,
    sourcePath: `virtual:${routeSegments.length === 0 ? "root" : routeSegments.join("/")}`,
    title,
    body: "<!-- wp:paragraph -->\n<p>This section is generated from the docs directory.</p>\n<!-- /wp:paragraph -->"
  };
}

function finalizePage(page, options) {
  const rootSlug = slugify(options.rootSlug || "docs", "docs");
  const fullSegments = [rootSlug, ...page.routeSegments];
  const key = fullSegments.join("/");
  const parentSegments = fullSegments.slice(0, -1);
  const parentKey = parentSegments.length > 0 ? parentSegments.join("/") : null;
  const slug = fullSegments.at(-1);
  const status = options.status || "publish";
  const createH1 = normalizeBoolean(options.createH1);
  const body = createH1 && page.kind === "placeholder"
    ? `${headingBlock(1, escapeHtml(page.title))}\n\n${page.body}`
    : page.body;
  const stablePayload = {
    key,
    sourcePath: page.sourcePath,
    title: page.title,
    slug,
    parentKey,
    status,
    body
  };
  const hash = sha256(stableJson(stablePayload));
  const content = prependSentinel(body, {
    key,
    source: page.sourcePath,
    hash
  });

  return {
    ...page,
    key,
    parentKey,
    slug,
    status,
    body,
    hash,
    content,
    depth: fullSegments.length
  };
}
