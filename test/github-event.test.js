import { describe, expect, it } from "vitest";
import { isManagedPullRequestMerge } from "../src/github-event.js";

describe("isManagedPullRequestMerge", () => {
  const managedMerge = {
    eventName: "push",
    repository: "Automattic/docspress",
    branch: "docspress/wordpress-sync",
    commitMessage: "Merge pull request #4 from Automattic/docspress/wordpress-sync\n\ndocs(sync-and-rest-api): sync changes from WordPress"
  };

  it("recognizes a merge from the action-owned reverse-sync branch", () => {
    expect(isManagedPullRequestMerge(managedMerge)).toBe(true);
  });

  it("recognizes a configured action-owned branch", () => {
    expect(isManagedPullRequestMerge({
      ...managedMerge,
      branch: "docs/from-wordpress",
      commitMessage: "Merge pull request #8 from Automattic/docs/from-wordpress"
    })).toBe(true);
  });

  it("does not skip ordinary pushes or other source branches", () => {
    expect(isManagedPullRequestMerge({
      ...managedMerge,
      commitMessage: "docs: update the API guide"
    })).toBe(false);
    expect(isManagedPullRequestMerge({
      ...managedMerge,
      commitMessage: "Merge pull request #9 from Automattic/feature/docs"
    })).toBe(false);
  });

  it("does not skip scheduled or manually dispatched runs", () => {
    expect(isManagedPullRequestMerge({ ...managedMerge, eventName: "schedule" })).toBe(false);
    expect(isManagedPullRequestMerge({ ...managedMerge, eventName: "workflow_dispatch" })).toBe(false);
  });
});
