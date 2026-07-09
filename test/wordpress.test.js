import { describe, expect, it } from "vitest";
import { WordPressClient } from "../src/wordpress.js";

function jsonResponse(data, init = {}) {
  return {
    ok: init.ok ?? true,
    status: init.status ?? 200,
    headers: {
      get(name) {
        return init.headers?.[name.toLowerCase()] || init.headers?.[name] || null;
      }
    },
    async text() {
      return JSON.stringify(data);
    }
  };
}

describe("WordPressClient", () => {
  it("uses the WordPress.com pages endpoint and bearer token", async () => {
    const calls = [];
    const client = new WordPressClient({
      baseUrl: "https://public-api.wordpress.com",
      site: "fkadev.blog",
      token: "token",
      fetchImpl: async (url, init) => {
        calls.push({ url: String(url), init });
        return jsonResponse([], { headers: { "x-wp-totalpages": "1" } });
      }
    });

    await client.listPages();

    expect(calls[0].url).toContain("https://public-api.wordpress.com/wp/v2/sites/fkadev.blog/pages");
    expect(calls[0].url).toContain("context=edit");
    expect(calls[0].init.headers.Authorization).toBe("Bearer token");
  });

  it("paginates page listing", async () => {
    const calls = [];
    const client = new WordPressClient({
      baseUrl: "https://public-api.wordpress.com",
      site: "fkadev.blog",
      token: "token",
      fetchImpl: async (url, init) => {
        calls.push({ url: String(url), init });
        const page = new URL(String(url)).searchParams.get("page");
        return jsonResponse([{ id: Number(page), slug: `page-${page}`, parent: 0, content: { raw: "" }, title: { raw: "" } }], {
          headers: { "x-wp-totalpages": "2" }
        });
      }
    });

    const pages = await client.listPages();

    expect(pages.map((page) => page.id)).toEqual([1, 2]);
    expect(calls).toHaveLength(2);
  });

  it("raises WordPress API errors", async () => {
    const client = new WordPressClient({
      baseUrl: "https://public-api.wordpress.com",
      site: "fkadev.blog",
      token: "bad-token",
      fetchImpl: async () => jsonResponse({ message: "Invalid token" }, { ok: false, status: 401 })
    });

    await expect(client.listPages()).rejects.toThrow("Invalid token");
  });

  it("adds a useful hint for WordPress.com global scope failures", async () => {
    const client = new WordPressClient({
      baseUrl: "https://public-api.wordpress.com",
      site: "fkadev.blog",
      token: "narrow-token",
      fetchImpl: async () => jsonResponse(
        { message: "That API call is not allowed for this account. Required scope: `global`. Granted scope(s): `posts,media`." },
        { ok: false, status: 403 }
      )
    });

    await expect(client.listPages()).rejects.toThrow(/Regenerate WP_ACCESS_TOKEN/);
  });
});
