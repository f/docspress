import { hashPageState } from "./page-state.js";
import { stripSentinel } from "./sentinel.js";
import { indexExistingPages } from "./sync.js";

export function planReconciliation(options) {
  const { desiredPages, existingPages } = options;
  const indexed = indexExistingPages(existingPages);
  const desiredByKey = new Map(desiredPages.map((page) => [page.key, page]));
  const wordpressChanges = [];
  const refreshPages = [];
  const conflicts = [];
  const classifications = [];

  for (const desired of desiredPages) {
    const managed = indexed.managedByKey.get(desired.key);
    if (!managed) {
      classifications.push({ key: desired.key, state: "github-only", desired });
      continue;
    }

    const live = livePageState(managed, indexed);
    const liveHash = hashPageState(live);
    const baseHash = managed.sentinel?.hash || "";

    if (liveHash === desired.hash) {
      const state = baseHash === desired.hash ? "unchanged" : "converged";
      classifications.push({ key: desired.key, state, desired, page: managed });
      if (state === "converged") {
        refreshPages.push(desired);
      }
      continue;
    }

    if (desired.hash === baseHash) {
      const structuralChanges = changedStructuralFields(desired, live);
      if (structuralChanges.length > 0) {
        conflicts.push({
          key: desired.key,
          reason: `WordPress changed unsupported field(s): ${structuralChanges.join(", ")}.`
        });
        classifications.push({ key: desired.key, state: "conflict", desired, page: managed });
        continue;
      }
      wordpressChanges.push({ desired, page: managed });
      classifications.push({ key: desired.key, state: "wordpress-only", desired, page: managed });
      continue;
    }

    if (liveHash === baseHash) {
      classifications.push({ key: desired.key, state: "github-only", desired, page: managed });
      continue;
    }

    conflicts.push({
      key: desired.key,
      reason: "The Markdown source and WordPress Page both changed since the last synchronized version."
    });
    classifications.push({ key: desired.key, state: "conflict", desired, page: managed });
  }

  for (const [key, managed] of indexed.managedByKey.entries()) {
    if (desiredByKey.has(key)) {
      continue;
    }
    const liveHash = hashPageState(livePageState(managed, indexed));
    if (liveHash !== managed.sentinel?.hash) {
      conflicts.push({
        key,
        reason: "The Markdown source was deleted while the WordPress Page also contains newer changes."
      });
      classifications.push({ key, state: "conflict", page: managed });
    } else {
      classifications.push({ key, state: "github-only-delete", page: managed });
    }
  }

  return {
    indexed,
    wordpressChanges,
    refreshPages,
    conflicts,
    classifications
  };
}

export function livePageState(page, indexed) {
  const parent = page.parent ? indexed.byId.get(page.parent) : null;
  return {
    key: page.sentinel?.key || page.path,
    sourcePath: page.sentinel?.source || "",
    title: page.title,
    slug: page.slug,
    parentKey: parent?.sentinel?.key || (page.path.includes("/") ? page.path.split("/").slice(0, -1).join("/") : null),
    status: page.status,
    body: stripSentinel(page.content)
  };
}

function changedStructuralFields(desired, live) {
  return ["key", "sourcePath", "slug", "parentKey", "status"]
    .filter((field) => desired[field] !== live[field]);
}
