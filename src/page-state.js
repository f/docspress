import { sha256, stableJson } from "./utils.js";

export function pageState(page) {
  return {
    key: page.key,
    sourcePath: page.sourcePath,
    title: page.title,
    slug: page.slug,
    parentKey: page.parentKey || null,
    status: page.status,
    body: page.body || ""
  };
}

export function hashPageState(page) {
  return sha256(stableJson(pageState(page)));
}
