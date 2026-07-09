import matter from "gray-matter";
import { toString as mdastToString } from "mdast-util-to-string";
import remarkGfm from "remark-gfm";
import remarkParse from "remark-parse";
import { unified } from "unified";
import {
  codeBlock,
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
  const tree = parser.parse(parsed.content);

  return {
    data: parsed.data || {},
    tree,
    content: parsed.content
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
  const children = createH1
    ? removeFirstMatchingHeading(parsed.tree.children, parsed.title)
    : parsed.removeFirstHeading
      ? removeFirstHeading(parsed.tree.children)
      : parsed.tree.children;
  const blocks = renderBlocks(children);

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

function renderBlocks(nodes) {
  return nodes.flatMap((node) => {
    const rendered = renderBlock(node);
    return rendered ? [rendered] : [];
  });
}

function renderBlock(node) {
  switch (node.type) {
    case "heading":
      return headingBlock(node.depth, renderInline(node.children || []));
    case "paragraph":
      return renderParagraph(node);
    case "list":
      return listBlock(renderListItems(node.children || []), Boolean(node.ordered));
    case "blockquote":
      return quoteBlock(renderQuoteChildren(node.children || []));
    case "code":
      return node.lang ? codeBlock(node.value || "", node.lang) : codeBlock(node.value || "", "");
    case "html":
      return htmlBlock(node.value || "");
    case "thematicBreak":
      return separatorBlock();
    case "table":
      return tableBlock(renderTable(node));
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
        return renderBlocks(node.children).join("\n\n");
      }
      return "";
  }
}

function renderParagraph(node) {
  const children = node.children || [];
  const onlyImage = children.length === 1 && children[0].type === "image";
  if (onlyImage) {
    return imageBlock(children[0]);
  }

  const html = renderInline(children).trim();
  return html ? paragraphBlock(html) : "";
}

function renderInline(nodes) {
  return (nodes || []).map(renderInlineNode).join("");
}

function renderInlineNode(node) {
  switch (node.type) {
    case "text":
      return escapeHtml(node.value || "");
    case "emphasis":
      return `<em>${renderInline(node.children || [])}</em>`;
    case "strong":
      return `<strong>${renderInline(node.children || [])}</strong>`;
    case "delete":
      return `<s>${renderInline(node.children || [])}</s>`;
    case "inlineCode":
      return `<code>${escapeHtml(node.value || "")}</code>`;
    case "link": {
      const title = node.title ? ` title="${escapeAttribute(node.title)}"` : "";
      return `<a href="${escapeAttribute(node.url || "")}"${title}>${renderInline(node.children || [])}</a>`;
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
        return renderInline(node.children);
      }
      return escapeHtml(node.value || "");
  }
}

function renderListItems(items) {
  return items.map((item) => `<li>${renderListItem(item)}</li>`).join("");
}

function renderListItem(item) {
  return (item.children || []).map((child) => {
    if (child.type === "paragraph") {
      return renderInline(child.children || []);
    }
    if (child.type === "list") {
      const tag = child.ordered ? "ol" : "ul";
      return `<${tag}>${renderListItems(child.children || [])}</${tag}>`;
    }
    if (child.type === "code") {
      return `<pre><code>${escapeHtml(child.value || "")}</code></pre>`;
    }
    return renderBlock(child);
  }).join("");
}

function renderQuoteChildren(children) {
  return children.map((child) => {
    if (child.type === "paragraph") {
      return `<p>${renderInline(child.children || [])}</p>`;
    }
    if (child.type === "heading") {
      const depth = Math.min(Math.max(child.depth || 2, 1), 6);
      return `<h${depth}>${renderInline(child.children || [])}</h${depth}>`;
    }
    return renderBlock(child);
  }).join("");
}

function renderTable(table) {
  const rows = table.children || [];
  if (rows.length === 0) {
    return "";
  }

  const [header, ...body] = rows;
  const align = table.align || [];
  const headerHtml = `<thead>${renderTableRow(header, "th", align)}</thead>`;
  const bodyHtml = body.length > 0 ? `<tbody>${body.map((row) => renderTableRow(row, "td", align)).join("")}</tbody>` : "";
  return `${headerHtml}${bodyHtml}`;
}

function renderTableRow(row, cellTag, align) {
  return `<tr>${(row.children || []).map((cell, index) => {
    const alignment = align[index] ? ` style="text-align:${escapeAttribute(align[index])}"` : "";
    return `<${cellTag}${alignment}>${renderInline(cell.children || [])}</${cellTag}>`;
  }).join("")}</tr>`;
}
