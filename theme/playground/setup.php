<?php
/**
 * Seed a small, idempotent DocsPress demo site in WordPress Playground.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create or update a demo page.
 *
 * @param string $title   Page title.
 * @param string $slug    Page slug.
 * @param string $content Page content.
 * @param int    $parent  Parent page ID.
 * @param int    $order   Menu order.
 * @param string $excerpt Page excerpt.
 * @param string $source_path Exact repository-relative Markdown source path.
 * @return int
 */
function docspress_playground_page( $title, $slug, $content, $parent = 0, $order = 0, $excerpt = '', $source_path = '' ) {
	$existing = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'name'           => $slug,
			'post_parent'    => $parent,
			'posts_per_page' => 1,
		)
	);

	$page = array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $content,
		'post_excerpt' => $excerpt,
		'post_parent'  => $parent,
		'menu_order'   => $order,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'comment_status' => 'closed',
	);

	if ( $existing ) {
		$page['ID'] = $existing[0]->ID;
		$page_id    = wp_update_post( $page );
	} else {
		$page_id = wp_insert_post( $page );
	}

	if ( $source_path && $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_docspress_source_path', $source_path );
	}

	return $page_id;
}

/**
 * Create an empty demo navigation menu if it does not exist.
 *
 * @param string $name Menu name.
 * @return int
 */
function docspress_playground_menu( $name ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		return (int) $menu->term_id;
	}

	$menu_id = wp_create_nav_menu( $name );
	return is_wp_error( $menu_id ) ? 0 : (int) $menu_id;
}

$docs = docspress_playground_page(
	'Docs',
	'docs',
	'<p>DocsPress keeps Markdown in GitHub and uses WordPress as the publishing surface.</p>',
	0,
	0,
	'Documentation that starts in Markdown and feels native to WordPress.',
	'docs/index.md'
);

$introduction = docspress_playground_page(
	'Introduction',
	'introduction',
	'<p>DocsPress turns a folder of Markdown files into a structured WordPress documentation site. Your repository remains the source of truth while WordPress handles publishing, permissions, previews, and the reading experience.</p>
	<blockquote class="wp-block-quote"><p><strong>Write where developers already work.</strong> Publish where your audience already reads.</p></blockquote>
	<h2>Why DocsPress?</h2>
	<p>Documentation stacks often introduce a second deployment pipeline and a separate editing model. DocsPress keeps the workflow deliberately small: Markdown goes in, native WordPress Pages come out.</p>
	<ul><li>Nested folders become parent and child Pages.</li><li>Markdown becomes Gutenberg-compatible core blocks.</li><li>Managed pages stay in sync without touching hand-authored content.</li><li>The entire front end remains a regular WordPress theme.</li></ul>
	<h2>How it works</h2>
	<p>A GitHub Action reads the docs tree, converts each file, and reconciles it with your WordPress site through the REST API.</p>
	<pre class="wp-block-code"><code>docs/                     WordPress
├── introduction.md  →   Docs / Introduction
├── getting-started/     Docs / Getting Started
│   └── install.md   →   Docs / Getting Started / Installation
└── guides/               Docs / Guides</code></pre>
	<h3>Safe by default</h3>
	<p>Only Pages carrying the DocsPress sentinel are updated or removed. Pages created manually in WordPress remain untouched.</p>
	<h2>What you get</h2>
	<table><thead><tr><th>Repository</th><th>WordPress</th></tr></thead><tbody><tr><td>Markdown files</td><td>Native blocks</td></tr><tr><td>Folder hierarchy</td><td>Page hierarchy</td></tr><tr><td>Git history</td><td>Editorial publishing</td></tr></tbody></table>',
	$docs,
	0,
	'Publish Markdown documentation through WordPress without giving up a Git-based workflow.',
	'docs/index.md'
);

$getting_started = docspress_playground_page(
	'Getting Started',
	'getting-started',
	'<p>Connect a documentation repository to a WordPress site in a few small steps.</p><h2>Before you begin</h2><p>You need a WordPress site, a repository containing Markdown, and an access token that can create Pages.</p><h2>Choose a path</h2><p>Start with installation, then configure the action for your site and repository.</p>',
	$docs,
	10,
	'Connect your Markdown repository to WordPress.',
	'docs/guides/getting-started.md'
);

docspress_playground_page(
	'Installation',
	'installation',
	'<p>Add DocsPress to an existing repository with a GitHub Actions workflow.</p><h2>Create the workflow</h2><p>Create <code>.github/workflows/docs.yml</code> and add the action:</p><pre class="wp-block-code"><code>name: Publish docs
on:
  push:
    branches: [main]
    paths: ["docs/**"]

jobs:
  publish:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: Automattic/docspress@main
        with:
          wordpress-site: example.com
          wordpress-access-token: ${{ secrets.WP_ACCESS_TOKEN }}
          docs-dir: docs
          root-slug: docs</code></pre><h2>Add your token</h2><p>Store the WordPress access token as <code>WP_ACCESS_TOKEN</code> in the repository secrets.</p><h3>Preview first</h3><p>Set <code>status: draft</code> while validating a new integration, then switch to <code>publish</code> when the page tree looks right.</p>',
	$getting_started,
	0,
	'Add the DocsPress action and publish your first documentation tree.'
);

docspress_playground_page(
	'Configuration',
	'configuration',
	'<p>DocsPress is configured through action inputs so the publishing contract stays visible beside the source.</p><h2>Core options</h2><table><thead><tr><th>Input</th><th>Purpose</th></tr></thead><tbody><tr><td><code>docs-dir</code></td><td>Folder containing Markdown documents.</td></tr><tr><td><code>root-slug</code></td><td>WordPress Page that owns the docs tree.</td></tr><tr><td><code>status</code></td><td>Publish Pages immediately or keep them as drafts.</td></tr><tr><td><code>create-h1</code></td><td>Add the page title to the synchronized blocks.</td></tr></tbody></table><h2>Recommended theme setting</h2><p>This theme already prints each Page title as its only <code>h1</code>, so use <code>create-h1: false</code> to avoid duplicate headings.</p><blockquote><p>Tip: choose the same Page as <strong>Documentation root</strong> in the Customizer. The sidebar will stay scoped to that hierarchy.</p></blockquote>',
	$getting_started,
	10,
	'Understand the inputs that control DocsPress publishing.'
);

$guides = docspress_playground_page(
	'Guides',
	'guides',
	'<p>Practical patterns for authoring and shipping maintainable documentation.</p><h2>Authoring principles</h2><p>Prefer short pages with one clear job, meaningful headings, and examples readers can paste safely.</p><h2>Keep the tree shallow</h2><p>Two or three navigation levels are usually enough. A shallow tree is easier to scan in the sidebar and easier to maintain in Git.</p>',
	$docs,
	20,
	'Patterns for writing, organizing, and shipping documentation.'
);

docspress_playground_page(
	'Markdown Features',
	'markdown-features',
	'<p>The converter supports the common building blocks of technical documentation.</p><h2>Rich text</h2><p>Use <strong>bold</strong>, <em>emphasis</em>, <a href="https://wordpress.org/">links</a>, and <code>inline code</code> naturally.</p><h2>Lists and tasks</h2><ul><li>Ordered and unordered lists</li><li>Nested items</li><li>Task lists from GitHub Flavored Markdown</li></ul><h2>Code and tables</h2><pre class="wp-block-code"><code>const message = "Markdown in, WordPress out";
console.log(message);</code></pre><table><thead><tr><th>Markdown</th><th>Block</th></tr></thead><tbody><tr><td>Fenced code</td><td>core/code</td></tr><tr><td>GFM table</td><td>core/table</td></tr><tr><td>Blockquote</td><td>core/quote</td></tr></tbody></table><h3>Escape hatch</h3><p>Serialized Gutenberg comments are preserved, so authors can use a native block directly when a Markdown mapping does not exist yet.</p>',
	$guides,
	0,
	'Headings, code, tables, lists, and Gutenberg escape hatches.',
	'docs/guides/markdown-features.md'
);

docspress_playground_page(
	'Deployment',
	'deployment',
	'<p>DocsPress runs wherever GitHub Actions can reach the WordPress REST API.</p><h2>Draft workflow</h2><ol><li>Sync new documents as drafts.</li><li>Review the Pages in WordPress.</li><li>Change the action status to publish.</li></ol><h2>Continuous publishing</h2><p>Limit the workflow trigger to changes under <code>docs/**</code>. This keeps documentation deploys focused and easy to audit.</p>',
	$guides,
	10,
	'Move from a safe draft preview to continuous documentation publishing.'
);

docspress_playground_page(
	'API Reference',
	'api-reference',
	'<p>The WordPress REST API is the small seam between repository content and published Pages.</p><h2>Page reconciliation</h2><p>DocsPress compares the desired page tree with managed Pages already present on the site.</p><pre class="wp-block-code"><code>GET  /wp-json/wp/v2/pages
POST /wp-json/wp/v2/pages
PUT  /wp-json/wp/v2/pages/{id}</code></pre><h2>Version taxonomy</h2><p>When versioning is enabled, the first folder below <code>docs/</code> becomes the version term. Register <code>docspress_version</code> for Pages and expose it through REST; the theme will show its version selector automatically.</p>',
	$docs,
	30,
	'The REST resources used to keep documentation Pages in sync.'
);

$header_menu = docspress_playground_menu( 'DocsPress Header' );
if ( $header_menu && ! wp_get_nav_menu_items( $header_menu ) ) {
	wp_update_nav_menu_item(
		$header_menu,
		0,
		array(
			'menu-item-title'     => 'Docs',
			'menu-item-object-id' => $introduction,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		)
	);
	wp_update_nav_menu_item(
		$header_menu,
		0,
		array(
			'menu-item-title'  => 'GitHub',
			'menu-item-url'    => 'https://github.com/Automattic/docspress',
			'menu-item-type'   => 'custom',
			'menu-item-status' => 'publish',
		)
	);
}

$sidebar_menu = docspress_playground_menu( 'DocsPress Sidebar' );
if ( $sidebar_menu && ! wp_get_nav_menu_items( $sidebar_menu ) ) {
	$tree_pages = get_pages(
		array(
			'child_of'    => $docs,
			'post_status' => 'publish',
			'sort_column' => 'menu_order,post_title',
			'sort_order'  => 'ASC',
		)
	);
	array_unshift( $tree_pages, get_post( $docs ) );
	$menu_parents = array( 0 => 0 );
	$pending      = $tree_pages;
	while ( $pending ) {
		$added = false;
		foreach ( $pending as $index => $tree_page ) {
			if ( ! isset( $menu_parents[ (int) $tree_page->post_parent ] ) ) {
				continue;
			}
			$menu_item_id = wp_update_nav_menu_item(
				$sidebar_menu,
				0,
				array(
					'menu-item-title'     => $tree_page->post_title,
					'menu-item-object-id' => $tree_page->ID,
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-parent-id' => $menu_parents[ (int) $tree_page->post_parent ],
					'menu-item-status'    => 'publish',
				)
			);
			if ( ! is_wp_error( $menu_item_id ) ) {
				$menu_parents[ (int) $tree_page->ID ] = (int) $menu_item_id;
			}
			unset( $pending[ $index ] );
			$added = true;
		}
		if ( ! $added ) {
			break;
		}
	}
}

$menu_locations = get_theme_mod( 'nav_menu_locations', array() );
if ( $header_menu ) {
	$menu_locations['primary'] = $header_menu;
}
if ( $sidebar_menu ) {
	$menu_locations['docs_sidebar'] = $sidebar_menu;
}
set_theme_mod( 'nav_menu_locations', $menu_locations );

update_option( 'blogname', 'DocsPress' );

// Leave an inspectable record that the blueprint's runtime configuration ran.
update_option(
	'docspress_playground_runtime',
	array(
		'jetpack_active'      => in_array( 'jetpack/jetpack.php', (array) get_option( 'active_plugins', array() ), true ),
		'jetpack_offline_mode' => defined( 'JETPACK_DEV_DEBUG' ) && JETPACK_DEV_DEBUG,
		'environment'         => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
	)
);
update_option( 'blogdescription', 'Markdown-first documentation, published with WordPress.' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $introduction );
update_option( 'permalink_structure', '/%postname%/' );
set_theme_mod( 'docspress_docs_root', $docs );
set_theme_mod( 'docspress_github_url', 'https://github.com/Automattic/docspress' );
set_theme_mod( 'docspress_github_edit_repository_url', 'https://github.com/f/docspress-demo' );
set_theme_mod( 'docspress_github_edit_ref', 'main' );
flush_rewrite_rules();
