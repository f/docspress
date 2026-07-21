import * as github from "@actions/github";

export const DOCSPRESS_PR_MARKER = "<!-- docspress:wordpress-sync -->";

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
    this.title = options.title || "Sync WordPress documentation changes";
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

    const commit = await this.octokit.rest.git.createCommit({
      owner: this.owner,
      repo: this.repo,
      message: this.title,
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
        title: this.title,
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
      title: this.title,
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
      head: `${this.owner}:${this.branch}`,
      base,
      per_page: 100
    });
    return response.data;
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
  return `${DOCSPRESS_PR_MARKER}\n\nWordPress contains documentation changes that are not yet in the repository. This pull request is maintained automatically by Docspress.\n\n## Changed files\n\n${files}\n`;
}
