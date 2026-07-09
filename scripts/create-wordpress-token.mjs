#!/usr/bin/env node
import { spawn } from "node:child_process";
import crypto from "node:crypto";
import http from "node:http";

const DEFAULT_REDIRECT_URI = "http://localhost:8787/callback";

async function main() {
  const args = parseArgs(process.argv.slice(2));

  if (args.help) {
    printHelp();
    return;
  }

  const clientId = required(args["client-id"], "--client-id");
  const clientSecret = required(args["client-secret"], "--client-secret");
  const redirectUri = args["redirect-uri"] || DEFAULT_REDIRECT_URI;
  const scope = args.scope || "posts media";
  const site = args.site || "";
  const repo = args.repo || "";
  const setSecret = Boolean(args["set-secret"]);
  const redirect = new URL(redirectUri);

  if (!["localhost", "127.0.0.1"].includes(redirect.hostname) || redirect.protocol !== "http:") {
    throw new Error(`This helper requires a local HTTP redirect URI, for example ${DEFAULT_REDIRECT_URI}`);
  }

  const state = crypto.randomBytes(18).toString("hex");
  const codePromise = waitForAuthorizationCode(redirect, state);
  const authorizationUrl = authorizationUrlFor({
    clientId,
    redirectUri,
    scope,
    site,
    state
  });

  console.log("Opening WordPress.com authorization URL...");
  console.log(authorizationUrl);
  openBrowser(authorizationUrl);

  const code = await codePromise;
  const token = await exchangeCodeForToken({
    clientId,
    clientSecret,
    redirectUri,
    code
  });

  console.log("\nWordPress.com access token received.");
  if (token.blog_id || token.blog_url) {
    console.log(`Blog: ${token.blog_url || token.blog_id}`);
  }

  if (setSecret) {
    if (!repo) {
      throw new Error("--set-secret requires --repo OWNER/REPO.");
    }
    await setGitHubSecret(repo, token.access_token);
    console.log(`Stored token as WP_ACCESS_TOKEN in ${repo}.`);
    return;
  }

  console.log("\nAccess token:");
  console.log(token.access_token);

  if (repo) {
    console.log("\nSet the GitHub Actions secret with:");
    console.log(`printf '%s' '${shellSingleQuote(token.access_token)}' | gh secret set WP_ACCESS_TOKEN --repo ${repo} --body-file -`);
  }
}

function parseArgs(argv) {
  const result = {};

  for (let index = 0; index < argv.length; index += 1) {
    const arg = argv[index];
    if (arg === "--help" || arg === "-h") {
      result.help = true;
      continue;
    }
    if (!arg.startsWith("--")) {
      throw new Error(`Unexpected argument: ${arg}`);
    }

    const key = arg.slice(2);
    const next = argv[index + 1];
    if (!next || next.startsWith("--")) {
      result[key] = true;
      continue;
    }

    result[key] = next;
    index += 1;
  }

  return result;
}

function required(value, name) {
  if (!value || value === true) {
    throw new Error(`Missing required argument: ${name}`);
  }

  return value;
}

function authorizationUrlFor({ clientId, redirectUri, scope, site, state }) {
  const url = new URL("https://public-api.wordpress.com/oauth2/authorize");
  url.searchParams.set("client_id", clientId);
  url.searchParams.set("redirect_uri", redirectUri);
  url.searchParams.set("response_type", "code");
  url.searchParams.set("scope", scope);
  url.searchParams.set("state", state);
  if (site) {
    url.searchParams.set("blog", site);
  }

  return String(url);
}

function waitForAuthorizationCode(redirect, expectedState) {
  return new Promise((resolve, reject) => {
    const server = http.createServer((request, response) => {
      try {
        const callbackUrl = new URL(request.url || "/", redirect.origin);
        const error = callbackUrl.searchParams.get("error") || callbackUrl.searchParams.get("error_description");
        const code = callbackUrl.searchParams.get("code");
        const state = callbackUrl.searchParams.get("state");

        if (error) {
          response.end("Authorization failed. You can close this tab.");
          reject(new Error(error));
          server.close();
          return;
        }

        if (!code) {
          response.end("Missing authorization code. You can close this tab.");
          reject(new Error("WordPress.com did not return an authorization code."));
          server.close();
          return;
        }

        if (state !== expectedState) {
          response.end("Invalid OAuth state. You can close this tab.");
          reject(new Error("OAuth state mismatch."));
          server.close();
          return;
        }

        response.setHeader("Content-Type", "text/html; charset=utf-8");
        response.end("<h1>Docspress token received</h1><p>You can close this tab and return to your terminal.</p>");
        resolve(code);
        server.close();
      } catch (error) {
        reject(error);
        server.close();
      }
    });

    server.on("error", reject);
    server.listen(Number(redirect.port || 80), redirect.hostname, () => {
      console.log(`Listening for OAuth callback at ${redirect.href}`);
    });
  });
}

async function exchangeCodeForToken({ clientId, clientSecret, redirectUri, code }) {
  const body = new URLSearchParams({
    client_id: clientId,
    client_secret: clientSecret,
    redirect_uri: redirectUri,
    code,
    grant_type: "authorization_code"
  });

  const response = await fetch("https://public-api.wordpress.com/oauth2/token", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body
  });
  const text = await response.text();
  const data = text ? JSON.parse(text) : {};

  if (!response.ok || !data.access_token) {
    throw new Error(data.error_description || data.error || `Token exchange failed with HTTP ${response.status}`);
  }

  return data;
}

function openBrowser(url) {
  const command = process.platform === "darwin" ? "open" : process.platform === "win32" ? "cmd" : "xdg-open";
  const args = process.platform === "win32" ? ["/c", "start", "", url] : [url];
  const child = spawn(command, args, {
    detached: true,
    stdio: "ignore"
  });
  child.unref();
}

function shellSingleQuote(value) {
  return String(value).replace(/'/g, "'\\''");
}

function setGitHubSecret(repo, token) {
  return new Promise((resolve, reject) => {
    const child = spawn("gh", ["secret", "set", "WP_ACCESS_TOKEN", "--repo", repo, "--body-file", "-"], {
      stdio: ["pipe", "inherit", "inherit"]
    });
    child.stdin.end(token);
    child.on("error", reject);
    child.on("exit", (code) => {
      if (code === 0) {
        resolve();
      } else {
        reject(new Error(`gh secret set exited with code ${code}`));
      }
    });
  });
}

function printHelp() {
  console.log(`Create a WordPress.com OAuth token for Docspress.

Usage:
  npm run token -- --client-id CLIENT_ID --client-secret CLIENT_SECRET --site fkadev.blog --repo f/docspress-demo

Options:
  --client-id       WordPress.com app client ID.
  --client-secret   WordPress.com app client secret.
  --site            WordPress.com site domain or ID to request access for.
  --scope           OAuth scope. Defaults to "posts media".
  --redirect-uri    Local redirect URI. Defaults to ${DEFAULT_REDIRECT_URI}.
  --repo            GitHub repository for the printed secret command.
  --set-secret      Store WP_ACCESS_TOKEN in --repo using gh instead of printing it.
`);
}

main().catch((error) => {
  console.error(error instanceof Error ? error.message : error);
  process.exit(1);
});
