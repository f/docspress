#!/usr/bin/env node
import { spawn } from "node:child_process";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, "..");

async function main() {
  const [command, ...args] = process.argv.slice(2);

  if (!command || command === "--help" || command === "-h") {
    printHelp();
    return;
  }

  if (command === "token") {
    const scriptPath = path.join(rootDir, "scripts", "create-wordpress-token.mjs");
    await run(process.execPath, [scriptPath, ...args]);
    return;
  }

  throw new Error(`Unknown command: ${command}`);
}

function run(command, args) {
  return new Promise((resolve, reject) => {
    const child = spawn(command, args, {
      stdio: "inherit"
    });
    child.on("error", reject);
    child.on("exit", (code) => {
      if (code === 0) {
        resolve();
      } else {
        reject(new Error(`Command exited with code ${code}`));
      }
    });
  });
}

function printHelp() {
  console.log(`Docspress CLI

Usage:
  docspress token --client-id CLIENT_ID --client-secret CLIENT_SECRET --site fkadev.blog --repo f/docspress-demo

Commands:
  token    Create and optionally store a WordPress.com OAuth token.

Run "docspress token --help" for token options.
`);
}

main().catch((error) => {
  console.error(error instanceof Error ? error.message : error);
  process.exit(1);
});
