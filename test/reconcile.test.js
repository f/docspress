import { describe, expect, it } from "vitest";
import { hashPageState } from "../src/page-state.js";
import { planReconciliation } from "../src/reconcile.js";
import { prependSentinel } from "../src/sentinel.js";

const paragraph = (text) => `<!-- wp:paragraph -->\n<p>${text}</p>\n<!-- /wp:paragraph -->`;

function desiredPage(overrides = {}) {
  const page = {
    key: "docs",
    sourcePath: "docs/index.md",
    title: "Docs",
    slug: "docs",
    parentKey: null,
    status: "publish",
    body: paragraph("Base"),
    ...overrides
  };
  page.hash = hashPageState(page);
  page.content = prependSentinel(page.body, { key: page.key, source: page.sourcePath, hash: page.hash });
  return page;
}

function existingFrom(page, overrides = {}) {
  const baseHash = overrides.baseHash || page.hash;
  const body = overrides.body || page.body;
  return {
    id: 1,
    slug: overrides.slug || page.slug,
    parent: 0,
    title: overrides.title || page.title,
    status: overrides.status || page.status,
    content: prependSentinel(body, { key: page.key, source: page.sourcePath, hash: baseHash }),
    link: "https://example.com/docs/"
  };
}

describe("planReconciliation", () => {
  it("classifies WordPress-only edits from the sentinel base", () => {
    const desired = desiredPage();
    const plan = planReconciliation({
      desiredPages: [desired],
      existingPages: [existingFrom(desired, { body: paragraph("Edited in WordPress") })]
    });

    expect(plan.wordpressChanges).toHaveLength(1);
    expect(plan.conflicts).toEqual([]);
    expect(plan.classifications[0].state).toBe("wordpress-only");
  });

  it("classifies GitHub-only changes when WordPress still matches the base", () => {
    const base = desiredPage();
    const desired = desiredPage({ body: paragraph("Edited in GitHub") });
    const plan = planReconciliation({
      desiredPages: [desired],
      existingPages: [existingFrom(base)]
    });

    expect(plan.wordpressChanges).toEqual([]);
    expect(plan.classifications[0].state).toBe("github-only");
  });

  it("refreshes a stale sentinel when both sides already converge", () => {
    const base = desiredPage();
    const desired = desiredPage({ body: paragraph("Merged proposal") });
    const plan = planReconciliation({
      desiredPages: [desired],
      existingPages: [existingFrom(desired, { baseHash: base.hash })]
    });

    expect(plan.refreshPages).toEqual([desired]);
    expect(plan.classifications[0].state).toBe("converged");
  });

  it("reports simultaneous changes and unsupported structural edits", () => {
    const base = desiredPage();
    const desired = desiredPage({ body: paragraph("GitHub") });
    const simultaneous = planReconciliation({
      desiredPages: [desired],
      existingPages: [existingFrom(base, { body: paragraph("WordPress") })]
    });
    const structural = planReconciliation({
      desiredPages: [base],
      existingPages: [existingFrom(base, { status: "draft" })]
    });

    expect(simultaneous.conflicts[0].reason).toMatch(/both changed/);
    expect(structural.conflicts[0].reason).toMatch(/status/);
  });
});
