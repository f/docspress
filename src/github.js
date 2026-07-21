import * as github from "@actions/github";

export const DOCSPRESS_PR_MARKER = "<!-- docspress:wordpress-sync -->";
export const githubContext = github.context;

export class GitHubPullRequestClient {
  constructor(options) {
    const repository = String(options.repository || process.env.GITHUB_REPOSITORY || "");
    const [owner, repo] = repository.split("/");
    if (!owner || !repo) {
      throw new Error("github-token reverse sync requires GITHUB_REPOSITORY in owner/repo form.");
    }
    if (!options.token) {
      throw new Error("github-token is required for propose and reconcile modes.");
    }

    this.owner = owner;
    this.repo = repo;
    this.base = options.base || "";
    this.branch = options.branch || "docspress/wordpress-sync";
    this.title = String(options.title || "").trim();
    this.octokit = options.octokit || github.getOctokit(options.token);
  }

  async syncChanges(changes) {
    const base = await this.resolveBase();
    const pulls = await this.listBranchPulls(base);
    const managedPulls = pulls.filter((pull) => String(pull.body || "").includes(DOCSPRESS_PR_MARKER));
    const unmarkedOpen = pulls.find((pull) => pull.state === "open" && !managedPulls.includes(pull));
    if (unmarkedOpen) {
      throw new Error(`Refusing to overwrite unmarked pull request #${unmarkedOpen.number} on ${this.branch}.`);
    }

    const branchRef = await this.getBranchRef();
    if (branchRef && managedPulls.length === 0) {
      throw new Error(`Refusing to overwrite existing branch ${this.branch}; it is not marked as managed by Docspress.`);
    }

    const openPull = managedPulls.find((pull) => pull.state === "open") || null;
    if (changes.length === 0) {
      if (openPull) {
        await this.octokit.rest.pulls.update({
          owner: this.owner,
          repo: this.repo,
          pull_number: openPull.number,
          state: "closed"
        });
      }
      if (branchRef && managedPulls.length > 0) {
        await this.octokit.rest.git.deleteRef({
          owner: this.owner,
          repo: this.repo,
          ref: `heads/${this.branch}`
        });
      }
      return {
        number: openPull?.number || null,
        url: openPull?.html_url || "",
        status: openPull ? "closed" : "unchanged"
      };
    }

    const baseRef = await this.octokit.rest.git.getRef({
      owner: this.owner,
      repo: this.repo,
      ref: `heads/${base}`
    });
    const baseSha = baseRef.data.object.sha;
    const baseCommit = await this.octokit.rest.git.getCommit({
      owner: this.owner,
      repo: this.repo,
      commit_sha: baseSha
    });
    const treeEntries = await Promise.all(changes.map(async (change) => {
      const blob = await this.octokit.rest.git.createBlob({
        owner: this.owner,
        repo: this.repo,
        content: change.content,
        encoding: "utf-8"
      });
      return {
        path: change.path,
        mode: "100644",
        type: "blob",
        sha: blob.data.sha
      };
    }));
    const tree = await this.octokit.rest.git.createTree({
      owner: this.owner,
      repo: this.repo,
      base_tree: baseCommit.data.tree.sha,
      tree: treeEntries
    });

    if (tree.data.sha === baseCommit.data.tree.sha) {
      return this.syncChanges([]);
    }

    const title = this.title || pullRequestTitle(changes);
    const commit = await this.octokit.rest.git.createCommit({
      owner: this.owner,
      repo: this.repo,
      message: title,
      tree: tree.data.sha,
      parents: [baseSha]
    });

    if (branchRef) {
      await this.octokit.rest.git.updateRef({
        owner: this.owner,
        repo: this.repo,
        ref: `heads/${this.branch}`,
        sha: commit.data.sha,
        force: true
      });
    } else {
      await this.octokit.rest.git.createRef({
        owner: this.owner,
        repo: this.repo,
        ref: `refs/heads/${this.branch}`,
        sha: commit.data.sha
      });
    }

    const body = pullRequestBody(changes);
    if (openPull) {
      const updated = await this.octokit.rest.pulls.update({
        owner: this.owner,
        repo: this.repo,
        pull_number: openPull.number,
        title,
        body
      });
      return {
        number: updated.data.number,
        url: updated.data.html_url,
        status: "updated"
      };
    }

    const created = await this.octokit.rest.pulls.create({
      owner: this.owner,
      repo: this.repo,
      head: this.branch,
      base,
      title,
      body
    });
    return {
      number: created.data.number,
      url: created.data.html_url,
      status: "created"
    };
  }

  async resolveBase() {
    if (this.base) {
      return this.base;
    }
    const repository = await this.octokit.rest.repos.get({
      owner: this.owner,
      repo: this.repo
    });
    return repository.data.default_branch;
  }

  async listBranchPulls(base) {
    const response = await this.octokit.rest.pulls.list({
      owner: this.owner,
      repo: this.repo,
      state: "all",
      base,
      per_page: 100
    });
    const repository = `${this.owner}/${this.repo}`.toLowerCase();
    return response.data.filter((pull) =>
      pull.head?.ref === this.branch &&
      String(pull.head?.repo?.full_name || "").toLowerCase() === repository
    );
  }

  async getBranchRef() {
    try {
      const response = await this.octokit.rest.git.getRef({
        owner: this.owner,
        repo: this.repo,
        ref: `heads/${this.branch}`
      });
      return response.data;
    } catch (error) {
      if (error?.status === 404) {
        return null;
      }
      throw error;
    }
  }
}

function pullRequestBody(changes) {
  const files = changes.map((change) => `- \`${change.path}\``).join("\n");
  const fileLabel = `${changes.length} Markdown ${changes.length === 1 ? "file" : "files"}`;
  return `${DOCSPRESS_PR_MARKER}\n\n## Summary\n\nSynchronizes documentation edits made in WordPress back to their Markdown sources. This rolling pull request is maintained automatically by DocsPress.\n\n| Direction | Changes |\n| --- | ---: |\n| WordPress → GitHub | ${fileLabel} |\n\n## Changed files\n\n${files}\n\n## Review and merge\n\nReview these as normal documentation changes. After this pull request merges, the next DocsPress reconcile run refreshes the WordPress synchronization baseline.\n\n> This branch is owned by DocsPress and may be force-refreshed on every synchronization run.\n`;
}

function pullRequestTitle(changes) {
  if (changes.length !== 1) {
    return `docs(wordpress): sync ${changes.length} files from WordPress`;
  }

  const parts = String(changes[0].path || "")
    .replace(/\\/g, "/")
    .replace(/\.[^.\/]+$/, "")
    .split("/")
    .filter(Boolean);
  let candidate = parts.at(-1) || "wordpress";
  if (candidate.toLowerCase() === "index" && parts.length > 1) {
    candidate = parts.at(-2);
  }
  const scope = candidate
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .slice(0, 40)
    .replace(/-+$/g, "") || "wordpress";
  return `docs(${scope}): sync changes from WordPress`;
}
