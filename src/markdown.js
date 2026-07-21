import matter from "gray-matter";
import { toString as mdastToString } from "mdast-util-to-string";
import remarkGfm from "remark-gfm";
import remarkParse from "remark-parse";
import { unified } from "unified";
import {
  codeBlock,
  codetabsBlock,
  headingBlock,
  htmlBlock,
  imageBlock,
  listBlock,
  paragraphBlock,
  preformattedBlock,
  quoteBlock,
  separatorBlock,
  tableBlock
} from "./gutenberg.js";
import { escapeAttribute, escapeHtml, normalizeBoolean } from "./utils.js";

const parser = unified().use(remarkParse).use(remarkGfm);

export function parseMarkdown(markdown) {
  const parsed = matter(markdown);
  const content = transformCodetabs(parsed.content);
  const tree = parser.parse(content);

  return {
    data: parsed.data || {},
    tree,
    content
  };
}

export function titleFromMarkdown(markdown, fallbackTitle) {
  const parsed = parseMarkdown(markdown);
  const frontmatterTitle = typeof parsed.data.title === "string" ? parsed.data.title.trim() : "";
  if (frontmatterTitle) {
    return {
      title: frontmatterTitle,
      tree: parsed.tree,
      data: parsed.data,
      removeFirstHeading: false
    };
  }

  const firstHeading = parsed.tree.children.find((node) => node.type === "heading" && node.depth === 1);
  if (firstHeading) {
    return {
      title: mdastToString(firstHeading).trim() || fallbackTitle,
      tree: parsed.tree,
      data: parsed.data,
      removeFirstHeading: true
    };
  }

  return {
    title: fallbackTitle,
    tree: parsed.tree,
    data: parsed.data,
    removeFirstHeading: false
  };
}

export function markdownToBlocks(markdown, options = {}) {
  const fallbackTitle = options.fallbackTitle || "Docs";
  const createH1 = normalizeBoolean(options.createH1);
  const parsed = titleFromMarkdown(markdown, fallbackTitle);
  const renderContext = {
    resolveLink: typeof options.resolveLink === "function" ? options.resolveLink : null
  };
  const children = createH1
    ? removeFirstMatchingHeading(parsed.tree.children, parsed.title)
    : parsed.removeFirstHeading
      ? removeFirstHeading(parsed.tree.children)
      : parsed.tree.children;
  const blocks = renderBlocks(children, renderContext);

  if (createH1) {
    blocks.unshift(headingBlock(1, escapeHtml(parsed.title)));
  }

  return {
    title: parsed.title,
    blocks: blocks.join("\n\n"),
    data: parsed.data
  };
}

function removeFirstHeading(children) {
  let removed = false;
  return children.filter((node) => {
    if (!removed && node.type === "heading" && node.depth === 1) {
      removed = true;
      return false;
    }

    return true;
  });
}

function removeFirstMatchingHeading(children, title) {
  let removed = false;
  const normalizedTitle = normalizeHeadingText(title);

  return children.filter((node) => {
    if (!removed && node.type === "heading" && node.depth === 1 && normalizeHeadingText(mdastToString(node)) === normalizedTitle) {
      removed = true;
      return false;
    }

    return true;
  });
}

function normalizeHeadingText(value) {
  return String(value || "").trim().replace(/\s+/g, " ").toLowerCase();
}

function transformCodetabs(content) {
  return String(content || "").replace(/{%\s*codetabs\s*%}([\s\S]*?){%\s*end\s*%}/g, (_match, body) => codetabsBlock(parseCodetabs(body)));
}

function parseCodetabs(body) {
  const parts = String(body || "").trim().split(/^\s*{%\s+([\w-]+)\s+%}\s*$/gm).filter((part) => part !== "");
  const tabs = [];

  for (let index = 0; index < parts.length - 1; index += 2) {
    const label = parts[index].trim();
    const rawContent = parts[index + 1].trim();
    const codeMatch = rawContent.match(/^```([\w-]+)?\n([\s\S]*?)\n?```$/);
    tabs.push({
      label,
      language: codeMatch?.[1] || label.toLowerCase(),
      code: codeMatch?.[2] ?? rawContent
    });
  }

  return tabs;
}

function renderBlocks(nodes, context = {}) {
  const blocks = [];

  for (let index = 0; index < nodes.length; index += 1) {
    const node = nodes[index];

    if (node.type === "html" && isOpeningGutenbergHtml(node.value)) {
      const collected = collectGutenbergHtml(nodes, index);
      blocks.push(normalizeGutenbergBlockComments(collected.value));
      index = collected.endIndex;
      continue;
    }

    const rendered = renderBlock(node, context);
    if (rendered) {
      blocks.push(rendered);
    }
  }

  return blocks;
}

function normalizeGutenbergBlockComments(value) {
  return String(value || "").replace(
    /<!--\s*wp:([\w/-]+)(?:\s+(\{[^\r\n]*\}))?\s*(\/)?-->/g,
    (comment, name, rawAttributes, selfClosing) => {
      if (!rawAttributes) {
        return `<!-- wp:${name}${selfClosing ? " /" : " "}-->`;
      }

      try {
        const attributes = JSON.parse(rawAttributes);
        const serialized = JSON.stringify(attributes)
          .replace(/--/g, "\\u002d\\u002d")
          .replace(/</g, "\\u003c")
          .replace(/>/g, "\\u003e")
          .replace(/&/g, "\\u0026")
          .replace(/\\\"/g, "\\u0022");

        return `<!-- wp:${name} ${serialized}${selfClosing ? " /" : " "}-->`;
      } catch {
        return comment;
      }
    }
  );
}

function collectGutenbergHtml(nodes, startIndex) {
  let value = nodes[startIndex].value || "";
  let endIndex = startIndex;

  if (isSelfClosingGutenbergHtml(value) || hasGutenbergClose(value)) {
    return { value, endIndex };
  }

  for (let index = startIndex + 1; index < nodes.length; index += 1) {
    const node = nodes[index];
    if (node.type !== "html") {
      break;
    }

    value = `${value}\n${node.value || ""}`;
    endIndex = index;

    if (hasGutenbergClose(value)) {
      break;
    }
  }

  return { value, endIndex };
}

function isOpeningGutenbergHtml(value) {
  return /^\s*<!--\s*wp:[\w/-]+(?:\s+\{[\s\S]*?\})?\s*(?:\/)?-->\s*$/s.test(String(value || ""));
}

function isSelfClosingGutenbergHtml(value) {
  return /^\s*<!--\s*wp:[\w/-]+(?:\s+\{[\s\S]*?\})?\s*\/-->\s*$/s.test(String(value || ""));
}

function hasGutenbergClose(value) {
  return /<!--\s*\/wp:[\w/-]+\s*-->/.test(String(value || ""));
}

function renderBlock(node, context = {}) {
  switch (node.type) {
    case "heading":
      return headingBlock(node.depth, renderInline(node.children || [], context));
    case "paragraph":
      return renderParagraph(node, context);
    case "list":
      return listBlock(renderListItems(node.children || [], context), Boolean(node.ordered));
    case "blockquote":
      return quoteBlock(renderQuoteChildren(node.children || [], context));
    case "code":
      return node.lang ? codeBlock(node.value || "", node.lang) : codeBlock(node.value || "", "");
    case "html":
      return htmlBlock(node.value || "");
    case "thematicBreak":
      return separatorBlock();
    case "table":
      return tableBlock(renderTable(node, context));
    case "image":
      return imageBlock(node);
    case "break":
      return paragraphBlock("<br>");
    case "yaml":
    case "definition":
    case "footnoteDefinition":
      return "";
    default:
      if (node.value) {
        return preformattedBlock(node.value);
      }
      if (node.children) {
        return renderBlocks(node.children, context).join("\n\n");
      }
      return "";
  }
}

function renderParagraph(node, context = {}) {
  const children = node.children || [];
  const onlyImage = children.length === 1 && children[0].type === "image";
  if (onlyImage) {
    return imageBlock(children[0]);
  }

  const html = renderInline(children, context).trim();
  return html ? paragraphBlock(html) : "";
}

function renderInline(nodes, context = {}) {
  return (nodes || []).map((node) => renderInlineNode(node, context)).join("");
}

function renderInlineNode(node, context = {}) {
  switch (node.type) {
    case "text":
      return escapeHtml(node.value || "");
    case "emphasis":
      return `<em>${renderInline(node.children || [], context)}</em>`;
    case "strong":
      return `<strong>${renderInline(node.children || [], context)}</strong>`;
    case "delete":
      return `<s>${renderInline(node.children || [], context)}</s>`;
    case "inlineCode":
      return `<code>${escapeHtml(node.value || "")}</code>`;
    case "link": {
      const title = node.title ? ` title="${escapeAttribute(node.title)}"` : "";
      const url = context.resolveLink ? context.resolveLink(node.url || "") : node.url || "";
      return `<a href="${escapeAttribute(url)}"${title}>${renderInline(node.children || [], context)}</a>`;
    }
    case "image": {
      const title = node.title ? ` title="${escapeAttribute(node.title)}"` : "";
      return `<img src="${escapeAttribute(node.url || "")}" alt="${escapeAttribute(node.alt || "")}"${title}/>`;
    }
    case "break":
      return "<br>";
    case "html":
      return node.value || "";
    case "footnoteReference":
      return `<sup>${escapeHtml(node.identifier || node.label || "")}</sup>`;
    default:
      if (node.children) {
        return renderInline(node.children, context);
      }
      return escapeHtml(node.value || "");
  }
}

function renderListItems(items, context = {}) {
  return items.map((item) => `<li>${renderListItem(item, context)}</li>`).join("");
}

function renderListItem(item, context = {}) {
  const checkbox = typeof item.checked === "boolean"
    ? `<input class="task-list-item-checkbox" type="checkbox"${item.checked ? " checked" : ""} disabled/> `
    : "";

  return `${checkbox}${(item.children || []).map((child) => {
    if (child.type === "paragraph") {
      return renderInline(child.children || [], context);
    }
    if (child.type === "list") {
      const tag = child.ordered ? "ol" : "ul";
      return `<${tag}>${renderListItems(child.children || [], context)}</${tag}>`;
    }
    if (child.type === "code") {
      return `<pre><code>${escapeHtml(child.value || "")}</code></pre>`;
    }
    return renderBlock(child, context);
  }).join("")}`;
}

function renderQuoteChildren(children, context = {}) {
  return children.map((child) => {
    if (child.type === "paragraph") {
      return `<p>${renderInline(child.children || [], context)}</p>`;
    }
    if (child.type === "heading") {
      const depth = Math.min(Math.max(child.depth || 2, 1), 6);
      return `<h${depth}>${renderInline(child.children || [], context)}</h${depth}>`;
    }
    return renderBlock(child, context);
  }).join("");
}

function renderTable(table, context = {}) {
  const rows = table.children || [];
  if (rows.length === 0) {
    return "";
  }

  const [header, ...body] = rows;
  const align = table.align || [];
  const headerHtml = `<thead>${renderTableRow(header, "th", align, context)}</thead>`;
  const bodyHtml = body.length > 0 ? `<tbody>${body.map((row) => renderTableRow(row, "td", align, context)).join("")}</tbody>` : "";
  return `${headerHtml}${bodyHtml}`;
}

function renderTableRow(row, cellTag, align, context = {}) {
  return `<tr>${(row.children || []).map((cell, index) => {
    const alignment = align[index] ? ` style="text-align:${escapeAttribute(align[index])}"` : "";
    return `<${cellTag}${alignment}>${renderInline(cell.children || [], context)}</${cellTag}>`;
  }).join("")}</tr>`;
}
