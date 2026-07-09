import * as core from "@actions/core";
import { collectDesiredPages } from "./docs.js";
import { syncPages } from "./sync.js";
import { normalizeBoolean } from "./utils.js";
import { WordPressClient } from "./wordpress.js";

async function main() {
  const config = {
    baseUrl: core.getInput("wordpress-url") || "https://public-api.wordpress.com",
    site: core.getInput("wordpress-site", { required: true }),
    token: core.getInput("wordpress-access-token", { required: true }),
    docsDir: core.getInput("docs-dir") || "docs",
    manifestFile: core.getInput("manifest-file") || "",
    redirectsFile: core.getInput("redirects-file") || "",
    rootSlug: core.getInput("root-slug") || "docs",
    rootTitle: core.getInput("root-title") || "Docs",
    createH1: normalizeBoolean(core.getInput("create-h1") || "false"),
    rewriteLinks: normalizeBoolean(core.getInput("rewrite-links") || "true"),
    editLink: normalizeBoolean(core.getInput("edit-link") || "false"),
    editLinkText: core.getInput("edit-link-text") || "Edit this page on GitHub",
    githubRepository: core.getInput("github-repository") || process.env.GITHUB_REPOSITORY || "",
    githubRef: core.getInput("github-ref") || process.env.GITHUB_REF_NAME || "main",
    githubServerUrl: core.getInput("github-server-url") || process.env.GITHUB_SERVER_URL || "https://github.com",
    status: core.getInput("status") || "publish",
    deleteMode: core.getInput("delete-mode") || "trash",
    dryRun: normalizeBoolean(core.getInput("dry-run") || "false")
  };

  const desiredPages = await collectDesiredPages({
    cwd: process.cwd(),
    docsDir: config.docsDir,
    manifestFile: config.manifestFile,
    redirectsFile: config.redirectsFile,
    rootSlug: config.rootSlug,
    rootTitle: config.rootTitle,
    createH1: config.createH1,
    rewriteLinks: config.rewriteLinks,
    editLink: config.editLink,
    editLinkText: config.editLinkText,
    githubRepository: config.githubRepository,
    githubRef: config.githubRef,
    githubServerUrl: config.githubServerUrl,
    status: config.status
  });

  core.info(`Docspress found ${desiredPages.length} desired page(s) in ${config.docsDir}.`);

  const client = new WordPressClient({
    baseUrl: config.baseUrl,
    site: config.site,
    token: config.token
  });

  const result = await syncPages({
    desiredPages,
    client,
    dryRun: config.dryRun,
    deleteMode: config.deleteMode,
    rootSlug: config.rootSlug,
    logger: core
  });

  setOutputs(result);
  await writeSummary(result);

  if (result.conflicts > 0) {
    core.setFailed(`Docspress found ${result.conflicts} unmanaged WordPress page conflict(s).`);
  }
}

function setOutputs(result) {
  core.setOutput("created", String(result.created));
  core.setOutput("updated", String(result.updated));
  core.setOutput("deleted", String(result.deleted));
  core.setOutput("unchanged", String(result.unchanged));
  core.setOutput("conflicts", String(result.conflicts));
  core.setOutput("summary-json", JSON.stringify(result));
}

async function writeSummary(result) {
  if (!core.summary) {
    return;
  }

  core.summary
    .addHeading(`Docspress ${result.dryRun ? "Dry Run" : "Sync"} Summary`)
    .addTable([
      [{ data: "Created", header: true }, String(result.created)],
      [{ data: "Updated", header: true }, String(result.updated)],
      [{ data: "Deleted", header: true }, String(result.deleted)],
      [{ data: "Unchanged", header: true }, String(result.unchanged)],
      [{ data: "Conflicts", header: true }, String(result.conflicts)]
    ]);

  if (result.conflictDetails.length > 0) {
    core.summary.addHeading("Conflicts", 2);
    for (const conflict of result.conflictDetails) {
      core.summary.addRaw(`- \`${conflict.key}\`: ${conflict.reason}\n`);
    }
  }

  await core.summary.write();
}

main().catch((error) => {
  core.setFailed(error instanceof Error ? error.message : String(error));
});
