import { describe, expect, it, vi } from "vitest";
import { DOCSPRESS_PR_MARKER, GitHubPullRequestClient } from "../src/github.js";

function mockOctokit(options = {}) {
  const branchExists = options.branchExists || false;
  const pulls = options.pulls || [];
  const git = {
    getRef: vi.fn(async ({ ref }) => {
      if (ref === "heads/docspress/wordpress-sync" && !branchExists) {
        throw Object.assign(new Error("Not found"), { status: 404 });
      }
      return { data: { object: { sha: ref === "heads/main" ? "base-sha" : "branch-sha" } } };
    }),
    getCommit: vi.fn(async () => ({ data: { tree: { sha: "base-tree" } } })),
    createBlob: vi.fn(async () => ({ data: { sha: "blob-sha" } })),
    createTree: vi.fn(async () => ({ data: { sha: "new-tree" } })),
    createCommit: vi.fn(async () => ({ data: { sha: "new-commit" } })),
    createRef: vi.fn(async () => ({})),
    updateRef: vi.fn(async () => ({})),
    deleteRef: vi.fn(async () => ({}))
  };
  const pullApi = {
    list: vi.fn(async () => ({ data: pulls })),
    create: vi.fn(async () => ({ data: { number: 12, html_url: "https://github.com/o/r/pull/12" } })),
    update: vi.fn(async ({ pull_number, state }) => ({
      data: { number: pull_number, html_url: `https://github.com/o/r/pull/${pull_number}`, state }
    }))
  };
  return {
    rest: {
      repos: { get: vi.fn(async () => ({ data: { default_branch: "main" } })) },
      git,
      pulls: pullApi
    }
  };
}

function client(octokit) {
  return new GitHubPullRequestClient({
    token: "token",
    repository: "o/r",
    octokit
  });
}

describe("GitHubPullRequestClient", () => {
  it("creates a managed branch, commit, and pull request", async () => {
    const octokit = mockOctokit();
    const result = await client(octokit).syncChanges([{ path: "docs/index.md", content: "# Docs\n" }]);

    expect(result.status).toBe("created");
    expect(octokit.rest.git.createRef).toHaveBeenCalledWith(expect.objectContaining({ sha: "new-commit" }));
    expect(octokit.rest.pulls.create).toHaveBeenCalledWith(expect.objectContaining({
      body: expect.stringContaining(DOCSPRESS_PR_MARKER)
    }));
  });

  it("force-refreshes an existing managed rolling pull request", async () => {
    const octokit = mockOctokit({
      branchExists: true,
      pulls: [{ number: 9, state: "open", body: DOCSPRESS_PR_MARKER, html_url: "https://github.com/o/r/pull/9" }]
    });
    const result = await client(octokit).syncChanges([{ path: "docs/index.md", content: "# Docs\n" }]);

    expect(result.status).toBe("updated");
    expect(octokit.rest.git.updateRef).toHaveBeenCalledWith(expect.objectContaining({ force: true }));
    expect(octokit.rest.pulls.update).toHaveBeenCalledWith(expect.objectContaining({ pull_number: 9 }));
  });

  it("closes and removes a managed proposal when changes disappear", async () => {
    const octokit = mockOctokit({
      branchExists: true,
      pulls: [{ number: 9, state: "open", body: DOCSPRESS_PR_MARKER, html_url: "https://github.com/o/r/pull/9" }]
    });
    const result = await client(octokit).syncChanges([]);

    expect(result.status).toBe("closed");
    expect(octokit.rest.git.deleteRef).toHaveBeenCalled();
  });
});
