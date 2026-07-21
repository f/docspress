import { createReverseChanges } from "./reverse.js";
import { planReconciliation } from "./reconcile.js";
import { syncPages } from "./sync.js";

export async function syncBidirectional(options) {
  const {
    mode,
    desiredPages,
    client,
    githubClient,
    dryRun = false,
    deleteMode = "trash",
    rootSlug = "docs",
    cwd = process.cwd(),
    manifestFile = "",
    createH1 = false,
    logger = console
  } = options;
  const existingPages = await client.listPages();
  const plan = planReconciliation({ desiredPages, existingPages });
  let publishPreview = emptyResult(true);

  if (mode === "reconcile") {
    publishPreview = await syncPages({
      desiredPages,
      client,
      existingPages,
      dryRun: true,
      deleteMode,
      rootSlug,
      logger: { info() {} }
    });
  }

  const conflicts = mergeConflicts(plan.conflicts, publishPreview.conflictDetails);
  if (conflicts.length > 0) {
    return {
      ...emptyResult(dryRun),
      mode,
      conflicts: conflicts.length,
      conflictDetails: conflicts,
      operations: conflicts.map((conflict) => ({ action: "conflict", ...conflict })),
      classifications: plan.classifications,
      proposed: 0,
      proposedFiles: [],
      pullRequest: null
    };
  }

  const changes = await createReverseChanges({
    cwd,
    pages: plan.wordpressChanges,
    desiredPages,
    manifestFile,
    createH1
  });
  const proposedOperations = changes.map((change) => ({ action: "propose", path: change.path }));

  if (dryRun) {
    const preview = mode === "reconcile" ? publishPreview : emptyResult(true);
    return {
      ...preview,
      dryRun: true,
      mode,
      classifications: plan.classifications,
      proposed: changes.length,
      proposedFiles: changes.map((change) => change.path),
      pullRequest: null,
      operations: [...preview.operations, ...proposedOperations]
    };
  }

  const pullRequest = await githubClient.syncChanges(changes);
  let wordpressResult;
  if (mode === "reconcile") {
    wordpressResult = await syncPages({
      desiredPages,
      client,
      existingPages,
      dryRun: false,
      deleteMode,
      rootSlug,
      logger
    });
  } else if (plan.refreshPages.length > 0) {
    wordpressResult = await syncPages({
      desiredPages: plan.refreshPages,
      client,
      existingPages,
      dryRun: false,
      deleteMode,
      rootSlug,
      allowDeletions: false,
      logger
    });
  } else {
    wordpressResult = emptyResult(false);
  }

  return {
    ...wordpressResult,
    mode,
    classifications: plan.classifications,
    proposed: changes.length,
    proposedFiles: changes.map((change) => change.path),
    pullRequest,
    operations: [...wordpressResult.operations, ...proposedOperations]
  };
}

function emptyResult(dryRun) {
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

function mergeConflicts(...groups) {
  const byKeyAndReason = new Map();
  for (const conflict of groups.flat()) {
    byKeyAndReason.set(`${conflict.key}:${conflict.reason}`, conflict);
  }
  return Array.from(byKeyAndReason.values());
}
