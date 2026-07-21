import { readSentinel } from "./sentinel.js";

export async function syncPages(options) {
  const {
    desiredPages,
    client,
    existingPages: suppliedExistingPages,
    dryRun = false,
    deleteMode = "trash",
    rootSlug = "docs",
    allowDeletions = true,
    skipUpdateKeys = new Set(),
    logger = console
  } = options;

  const existingPages = suppliedExistingPages || await client.listPages();
  const indexed = indexExistingPages(existingPages);
  const desiredKeys = new Set(desiredPages.map((page) => page.key));
  const result = createResult(dryRun);
  const idByKey = new Map();
  let syntheticId = -1;

  for (const [key, page] of indexed.managedByKey.entries()) {
    idByKey.set(key, page.id);
  }

  for (const desired of desiredPages) {
    const existingAtPath = indexed.byPath.get(desired.key);
    const managed = indexed.managedByKey.get(desired.key);

    if (desired.parentKey && !idByKey.has(desired.parentKey)) {
      addConflict(result, desired.key, `Parent page is unavailable: ${desired.parentKey}`);
      continue;
    }

    if (existingAtPath && !managed) {
      addConflict(result, desired.key, "An unmanaged WordPress page already uses this path.");
      continue;
    }

    const parentId = desired.parentKey ? idByKey.get(desired.parentKey) : 0;
    const payload = pagePayload(desired, parentId);

    if (managed) {
      if (skipUpdateKeys.has(desired.key)) {
        result.unchanged += 1;
        result.operations.push({
          action: "unchanged",
          key: desired.key,
          id: managed.id,
          reason: "WordPress-only edit is awaiting its pull request."
        });
        continue;
      }
      if (managed.sentinel?.hash === desired.hash && managed.parent === parentId) {
        result.unchanged += 1;
        result.operations.push({ action: "unchanged", key: desired.key, id: managed.id });
        continue;
      }

      result.updated += 1;
      result.operations.push({ action: "update", key: desired.key, id: managed.id });
      logger.info?.(`${dryRun ? "Would update" : "Updating"} ${desired.key}`);
      if (!dryRun) {
        const updated = await client.updatePage(managed.id, payload);
        idByKey.set(desired.key, updated.id);
      } else {
        idByKey.set(desired.key, managed.id);
      }
      continue;
    }

    result.created += 1;
    result.operations.push({ action: "create", key: desired.key });
    logger.info?.(`${dryRun ? "Would create" : "Creating"} ${desired.key}`);
    if (!dryRun) {
      const created = await client.createPage(payload);
      idByKey.set(desired.key, created.id);
    } else {
      idByKey.set(desired.key, syntheticId);
      syntheticId -= 1;
    }
  }

  const deletions = allowDeletions ? Array.from(indexed.managedByKey.values())
    .filter((page) => isUnderRoot(page.sentinel?.key, rootSlug) && !desiredKeys.has(page.sentinel.key))
    .sort((a, b) => b.path.split("/").length - a.path.split("/").length) : [];

  for (const page of deletions) {
    result.deleted += 1;
    result.operations.push({ action: "delete", key: page.sentinel.key, id: page.id });
    logger.info?.(`${dryRun ? "Would delete" : "Deleting"} ${page.sentinel.key}`);
    if (!dryRun) {
      await client.deletePage(page.id, { force: deleteMode === "force" });
    }
  }

  return result;
}

function pagePayload(page, parentId) {
  const payload = {
    title: page.title,
    content: page.content,
    slug: page.slug,
    status: page.status,
    parent: parentId || 0
  };

  return payload;
}

function createResult(dryRun) {
  return {
    dryRun,
    created: 0,
    updated: 0,
    deleted: 0,
    unchanged: 0,
    conflicts: 0,
    conflictDetails: [],
    operations: []
  };
}

function addConflict(result, key, reason) {
  result.conflicts += 1;
  result.conflictDetails.push({ key, reason });
  result.operations.push({ action: "conflict", key, reason });
}

function isUnderRoot(key, rootSlug) {
  return key === rootSlug || key?.startsWith(`${rootSlug}/`);
}

export function indexExistingPages(pages) {
  const byId = new Map();
  const byPath = new Map();
  const managedByKey = new Map();

  for (const page of pages) {
    byId.set(page.id, {
      ...page,
      sentinel: readSentinel(page.content)
    });
  }

  for (const page of byId.values()) {
    page.path = pathForPage(page, byId);
    byPath.set(page.path, page);

    if (page.sentinel?.key) {
      managedByKey.set(page.sentinel.key, page);
    }
  }

  return {
    byId,
    byPath,
    managedByKey
  };
}

function pathForPage(page, byId, seen = new Set()) {
  if (!page.parent || seen.has(page.id) || !byId.has(page.parent)) {
    return page.slug;
  }

  seen.add(page.id);
  const parent = byId.get(page.parent);
  return `${pathForPage(parent, byId, seen)}/${page.slug}`;
}
