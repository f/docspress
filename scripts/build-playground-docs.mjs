#!/usr/bin/env node
import fs from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { collectDesiredPages } from "../src/docs.js";

const scriptDir = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(scriptDir, "..");
const outputPath = path.join(rootDir, "theme", "playground", "generated-docs.json");

const pages = await collectDesiredPages({
  cwd: rootDir,
  docsDir: "docs",
  rootSlug: "docs",
  rootTitle: "DocsPress documentation",
  createH1: false,
  rewriteLinks: true,
  editLink: false,
  status: "publish"
});

const payload = {
  generatedBy: "scripts/build-playground-docs.mjs",
  pages: pages.map((page) => ({
    key: page.key,
    parentKey: page.parentKey,
    slug: page.slug,
    title: page.title,
    content: page.content,
    sourcePath: page.sourcePath,
    depth: page.depth
  }))
};

await fs.writeFile(outputPath, `${JSON.stringify(payload, null, 2)}\n`);
console.log(`Generated ${payload.pages.length} Playground pages at ${path.relative(rootDir, outputPath)}.`);
