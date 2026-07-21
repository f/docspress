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
		// wp_insert_post() unslashes input. Pre-slash serialized block JSON so
		// newlines and escaped HTML survive exactly as the editor stores them.
		'post_content' => wp_slash( $content ),
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

/**
 * Serialize one Gutenberg block for demo post content.
 *
 * @param string      $name       Block name.
 * @param array       $attributes Block attributes.
 * @param string|null $content    Saved block HTML, or null for a dynamic block.
 * @return string
 */
function docspress_playground_block( $name, $attributes = array(), $content = null ) {
	return get_comment_delimited_block_content( $name, $attributes, $content );
}

/**
 * Join serialized blocks into a Gutenberg document.
 *
 * @param string ...$blocks Serialized blocks.
 * @return string
 */
function docspress_playground_document( ...$blocks ) {
	return implode( "\n\n", array_filter( $blocks ) );
}

/**
 * Serialize a paragraph block.
 *
 * @param string $content Paragraph HTML.
 * @return string
 */
function docspress_playground_paragraph( $content ) {
	return docspress_playground_block( 'core/paragraph', array(), '<p>' . $content . '</p>' );
}

/**
 * Serialize a heading block.
 *
 * @param string $content Heading text.
 * @param int    $level   Heading level.
 * @return string
 */
function docspress_playground_heading( $content, $level = 2 ) {
	return docspress_playground_block(
		'core/heading',
		array( 'level' => $level ),
		sprintf( '<h%1$d class="wp-block-heading">%2$s</h%1$d>', $level, esc_html( $content ) )
	);
}

/**
 * Serialize a list using list-item inner blocks.
 *
 * @param array $items   List item HTML strings.
 * @param bool  $ordered Whether to use an ordered list.
 * @return string
 */
function docspress_playground_list( $items, $ordered = false ) {
	$inner = '';
	foreach ( $items as $item ) {
		$inner .= docspress_playground_block( 'core/list-item', array(), '<li>' . $item . '</li>' );
	}
	$tag = $ordered ? 'ol' : 'ul';

	return docspress_playground_block(
		'core/list',
		$ordered ? array( 'ordered' => true ) : array(),
		'<' . $tag . ' class="wp-block-list">' . $inner . '</' . $tag . '>'
	);
}

/**
 * Serialize a simple table block.
 *
 * @param array $headers Header cells.
 * @param array $rows    Body rows.
 * @return string
 */
function docspress_playground_table( $headers, $rows ) {
	$head = '';
	foreach ( $headers as $header ) {
		$head .= '<th>' . $header . '</th>';
	}
	$body = '';
	foreach ( $rows as $row ) {
		$cells = '';
		foreach ( $row as $cell ) {
			$cells .= '<td>' . $cell . '</td>';
		}
		$body .= '<tr>' . $cells . '</tr>';
	}

	return docspress_playground_block(
		'core/table',
		array( 'hasFixedLayout' => false ),
		'<figure class="wp-block-table"><table><thead><tr>' . $head . '</tr></thead><tbody>' . $body . '</tbody></table></figure>'
	);
}

/**
 * Serialize a DocsPress Callout dynamic block.
 *
 * @param string $tone    Callout tone.
 * @param string $title   Callout title.
 * @param string $content Callout content HTML.
 * @param bool   $collapsible Whether the callout can collapse.
 * @param bool   $open        Whether a collapsible callout starts open.
 * @return string
 */
function docspress_playground_callout( $tone, $title, $content, $collapsible = false, $open = true ) {
	return docspress_playground_block(
		'docspress/callout',
		array(
			'tone'        => $tone,
			'title'       => $title,
			'content'     => '<p>' . $content . '</p>',
			'collapsible' => $collapsible,
			'open'        => $open,
		)
	);
}

/**
 * Serialize a DocsPress Colorful Code dynamic block.
 *
 * @param string $code       Source code.
 * @param string $language   Language identifier.
 * @param string $filename   Filename label.
 * @param string $highlights Highlighted line expression.
 * @param bool   $show_line_numbers Whether to show line numbers.
 * @param string $caption    Optional caption.
 * @return string
 */
function docspress_playground_code( $code, $language, $filename = '', $highlights = '', $show_line_numbers = true, $caption = '' ) {
	return docspress_playground_block(
		'docspress/colorful-code',
		array(
			'code'             => $code,
			'language'         => $language,
			'filename'         => $filename,
			'highlightedLines' => $highlights,
			'showLineNumbers'  => $show_line_numbers,
			'caption'          => $caption,
		)
	);
}

/**
 * Serialize a DocsPress Code Tabs dynamic block.
 *
 * @param array  $tabs Code tabs.
 * @param bool   $show_line_numbers Whether to show line numbers.
 * @param string $caption Optional caption.
 * @return string
 */
function docspress_playground_code_tabs( $tabs, $show_line_numbers = true, $caption = '' ) {
	return docspress_playground_block(
		'docspress/code-tabs',
		array(
			'tabs'            => $tabs,
			'showLineNumbers' => $show_line_numbers,
			'caption'         => $caption,
		)
	);
}

/**
 * Serialize a DocsPress API Request / Response dynamic block.
 *
 * @param string $method         HTTP method.
 * @param string $endpoint       Request endpoint.
 * @param string $headers        Request headers.
 * @param string $request_body   Request body.
 * @param string $response_status Response status.
 * @param string $response_body  Response body.
 * @return string
 */
function docspress_playground_api_request( $method, $endpoint, $headers, $request_body, $response_status, $response_body ) {
	return docspress_playground_block(
		'docspress/api-request',
		array(
			'method'         => $method,
			'endpoint'       => $endpoint,
			'headers'        => $headers,
			'requestBody'    => $request_body,
			'responseStatus' => $response_status,
			'responseBody'   => $response_body,
		)
	);
}

/**
 * Serialize a DocsPress Terminal Session dynamic block.
 *
 * @param string $title   Terminal title.
 * @param string $command Command text.
 * @param string $output  Command output.
 * @param string $shell   Shell label.
 * @param string $prompt  Prompt label.
 * @return string
 */
function docspress_playground_terminal( $title, $command, $output, $shell = 'bash', $prompt = '$' ) {
	return docspress_playground_block(
		'docspress/terminal-session',
		array(
			'title'   => $title,
			'shell'   => $shell,
			'prompt'  => $prompt,
			'command' => $command,
			'output'  => $output,
		)
	);
}

/**
 * Serialize a DocsPress Result dynamic block.
 *
 * @param string $status  Result status.
 * @param string $title   Result title.
 * @param string $content Result content.
 * @param string $meta    Compact metadata.
 * @return string
 */
function docspress_playground_result( $status, $title, $content, $meta = '' ) {
	return docspress_playground_block(
		'docspress/result',
		array(
			'status'  => $status,
			'title'   => $title,
			'content' => '<p>' . $content . '</p>',
			'meta'    => $meta,
		)
	);
}

/**
 * Serialize a DocsPress File Tree dynamic block.
 *
 * @param string $root    Root label.
 * @param string $tree    Indented file list.
 * @param string $caption Optional caption.
 * @return string
 */
function docspress_playground_file_tree( $root, $tree, $caption = '' ) {
	return docspress_playground_block(
		'docspress/file-tree',
		array(
			'root'    => $root,
			'tree'    => $tree,
			'caption' => $caption,
		)
	);
}

/**
 * Serialize a DocsPress Prompt dynamic block.
 *
 * @param string $prompt   Prompt text.
 * @param string $model    Model label.
 * @param string $mode     Prompt mode.
 * @param string $context  Comma-separated context.
 * @param bool   $thinking Whether to show the thinking indicator.
 * @return string
 */
function docspress_playground_prompt( $prompt, $model, $mode, $context = '', $thinking = false ) {
	return docspress_playground_block(
		'docspress/prompt',
		array(
			'prompt'   => $prompt,
			'model'    => $model,
			'mode'     => $mode,
			'context'  => $context,
			'thinking' => $thinking,
		)
	);
}

/**
 * Build a current component inventory for the Kitchen Sink page.
 *
 * @return array
 */
function docspress_playground_component_rows() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$rows         = array();
	$active       = (array) get_option( 'active_plugins', array() );
	$active_theme = wp_get_theme();
	$rows[]       = array( 'WordPress', 'Core', esc_html( get_bloginfo( 'version' ) ), 'Running' );
	$rows[]       = array( esc_html( $active_theme->get( 'Name' ) ), 'Theme', esc_html( $active_theme->get( 'Version' ) ), 'Active' );

	foreach ( get_plugins() as $plugin_file => $plugin ) {
		$rows[] = array(
			esc_html( $plugin['Name'] ? $plugin['Name'] : $plugin_file ),
			'Plugin',
			esc_html( $plugin['Version'] ? $plugin['Version'] : '—' ),
			in_array( $plugin_file, $active, true ) ? 'Active' : 'Inactive',
		);
	}

	return $rows;
}

$docs = docspress_playground_page(
	'Docs',
	'docs',
	docspress_playground_document(
		docspress_playground_paragraph( 'DocsPress keeps Markdown in GitHub and uses WordPress as the publishing surface.' ),
		docspress_playground_callout( 'note', 'These are real blocks', 'Every starter page is stored as serialized Gutenberg block HTML, including the DocsPress code and callout blocks.' )
	),
	0,
	0,
	'Documentation that starts in Markdown and feels native to WordPress.',
	'docs/index.md'
);

$introduction = docspress_playground_page(
	'Introduction',
	'introduction',
	docspress_playground_document(
		docspress_playground_paragraph( 'DocsPress turns a folder of Markdown files into a structured WordPress documentation site. Your repository remains the source of truth while WordPress handles publishing, permissions, previews, and the reading experience.' ),
		docspress_playground_callout( 'tip', 'Write where developers already work', 'Publish where your audience already reads.' ),
		docspress_playground_heading( 'Why DocsPress?' ),
		docspress_playground_paragraph( 'Documentation stacks often introduce a second deployment pipeline and a separate editing model. DocsPress keeps the workflow deliberately small: Markdown goes in, native WordPress Pages come out.' ),
		docspress_playground_list(
			array(
				'Nested folders become parent and child Pages.',
				'Markdown becomes Gutenberg-compatible core blocks.',
				'Managed pages stay in sync without touching hand-authored content.',
				'The entire front end remains a regular WordPress theme.',
			)
		),
		docspress_playground_heading( 'How it works' ),
		docspress_playground_paragraph( 'A GitHub Action reads the docs tree, converts each file, and reconciles it with your WordPress site through the REST API.' ),
		docspress_playground_file_tree( 'repository/', "docs/\n  introduction.md\n  getting-started/\n    install.md\n  guides/\n    markdown-features.md", 'Folders become parent Pages; Markdown files become documentation Pages.' ),
		docspress_playground_heading( 'Safe by default', 3 ),
		docspress_playground_paragraph( 'Only Pages carrying the DocsPress sentinel are updated or removed. Pages created manually in WordPress remain untouched.' ),
		docspress_playground_heading( 'What you get' ),
		docspress_playground_table(
			array( 'Repository', 'WordPress' ),
			array(
				array( 'Markdown files', 'Native blocks' ),
				array( 'Folder hierarchy', 'Page hierarchy' ),
				array( 'Git history', 'Editorial publishing' ),
			)
		)
	),
	$docs,
	0,
	'Publish Markdown documentation through WordPress without giving up a Git-based workflow.',
	'docs/index.md'
);

$getting_started = docspress_playground_page(
	'Getting Started',
	'getting-started',
	docspress_playground_document(
		docspress_playground_paragraph( 'Connect a documentation repository to a WordPress site in a few small steps.' ),
		docspress_playground_heading( 'Before you begin' ),
		docspress_playground_callout( 'warning', 'Required access', 'You need a WordPress site, a repository containing Markdown, and an access token that can create Pages.' ),
		docspress_playground_heading( 'Choose a path' ),
		docspress_playground_paragraph( 'Start with installation, then configure the action for your site and repository.' )
	),
	$docs,
	10,
	'Connect your Markdown repository to WordPress.',
	'docs/guides/getting-started.md'
);

docspress_playground_page(
	'Installation',
	'installation',
	docspress_playground_document(
		docspress_playground_paragraph( 'Add DocsPress to an existing repository with a GitHub Actions workflow.' ),
		docspress_playground_heading( 'Create the workflow' ),
		docspress_playground_paragraph( 'Create <code>.github/workflows/docs.yml</code> and add the action:' ),
		docspress_playground_code( "name: Publish docs\non:\n  push:\n    branches: [main]\n    paths: [\"docs/**\"]\n\njobs:\n  publish:\n    runs-on: ubuntu-latest\n    steps:\n      - uses: actions/checkout@v4\n      - uses: Automattic/docspress@main\n        with:\n          wordpress-site: example.com\n          wordpress-access-token: \${{ secrets.WP_ACCESS_TOKEN }}\n          docs-dir: docs\n          root-slug: docs", 'yaml', '.github/workflows/docs.yml', '11-12' ),
		docspress_playground_heading( 'Add your token' ),
		docspress_playground_paragraph( 'Store the WordPress access token as <code>WP_ACCESS_TOKEN</code> in the repository secrets.' ),
		docspress_playground_callout( 'tip', 'Preview first', 'Set <code>status: draft</code> while validating a new integration, then switch to <code>publish</code> when the page tree looks right.' ),
		docspress_playground_heading( 'Verify the setup' ),
		docspress_playground_terminal( 'Publish a preview', 'npx docspress publish ./docs --status=draft', "✓ Read 12 documents\n✓ Created 12 draft pages\nPreview: https://example.com/docs/" ),
		docspress_playground_result( 'success', 'Preview published', 'The complete page tree is ready for editorial review in WordPress.', '12 pages · 1.8s' )
	),
	$getting_started,
	0,
	'Add the DocsPress action and publish your first documentation tree.'
);

docspress_playground_page(
	'Configuration',
	'configuration',
	docspress_playground_document(
		docspress_playground_paragraph( 'DocsPress is configured through action inputs so the publishing contract stays visible beside the source.' ),
		docspress_playground_heading( 'Core options' ),
		docspress_playground_table(
			array( 'Input', 'Purpose' ),
			array(
				array( '<code>docs-dir</code>', 'Folder containing Markdown documents.' ),
				array( '<code>root-slug</code>', 'WordPress Page that owns the docs tree.' ),
				array( '<code>status</code>', 'Publish Pages immediately or keep them as drafts.' ),
				array( '<code>create-h1</code>', 'Add the page title to the synchronized blocks.' ),
			)
		),
		docspress_playground_heading( 'Recommended theme setting' ),
		docspress_playground_paragraph( 'This theme already prints each Page title as its only <code>h1</code>, so use <code>create-h1: false</code> to avoid duplicate headings.' ),
		docspress_playground_callout( 'tip', 'Keep navigation scoped', 'Choose the same Page as <strong>Documentation root</strong> in the Customizer. The sidebar will stay scoped to that hierarchy.', true )
	),
	$getting_started,
	10,
	'Understand the inputs that control DocsPress publishing.'
);

$guides = docspress_playground_page(
	'Guides',
	'guides',
	docspress_playground_document(
		docspress_playground_paragraph( 'Practical patterns for authoring and shipping maintainable documentation.' ),
		docspress_playground_heading( 'Authoring principles' ),
		docspress_playground_paragraph( 'Prefer short pages with one clear job, meaningful headings, and examples readers can paste safely.' ),
		docspress_playground_heading( 'Keep the tree shallow' ),
		docspress_playground_paragraph( 'Two or three navigation levels are usually enough. A shallow tree is easier to scan in the sidebar and easier to maintain in Git.' )
	),
	$docs,
	20,
	'Patterns for writing, organizing, and shipping documentation.'
);

docspress_playground_page(
	'Markdown Features',
	'markdown-features',
	docspress_playground_document(
		docspress_playground_paragraph( 'The converter supports the common building blocks of technical documentation.' ),
		docspress_playground_heading( 'Rich text' ),
		docspress_playground_paragraph( 'Use <strong>bold</strong>, <em>emphasis</em>, <a href="https://wordpress.org/">links</a>, and <code>inline code</code> naturally.' ),
		docspress_playground_heading( 'Lists and tasks' ),
		docspress_playground_list( array( 'Ordered and unordered lists', 'Nested items', 'Task lists from GitHub Flavored Markdown' ) ),
		docspress_playground_heading( 'Code tabs' ),
		docspress_playground_code_tabs(
			array(
				array( 'label' => 'JavaScript', 'language' => 'javascript', 'filename' => 'publish.js', 'code' => "const message = 'Markdown in, WordPress out';\nconsole.log( message );" ),
				array( 'label' => 'PHP', 'language' => 'php', 'filename' => 'publish.php', 'code' => "\$message = 'Markdown in, WordPress out';\necho \$message;" ),
				array( 'label' => 'JSON', 'language' => 'json', 'filename' => 'docspress.json', 'code' => "{\n  \"source\": \"Markdown\",\n  \"surface\": \"WordPress\"\n}" ),
			)
		),
		docspress_playground_heading( 'AI prompts' ),
		docspress_playground_paragraph( 'Prompts are documentation artifacts, not source code. Keep the model, interaction mode, and supplied context visible so readers can reproduce the example.' ),
		docspress_playground_prompt(
			"Review the documentation synchronization logic for failure modes.\n\nReturn a short risk list, then propose the smallest safe patch and the tests it needs.",
			'GPT-5',
			'code',
			'@repository, src/sync.js, test/sync.test.js',
			true
		),
		docspress_playground_table(
			array( 'Markdown', 'Block' ),
			array(
				array( 'Fenced code', 'docspress/colorful-code' ),
				array( 'Code tabs', 'docspress/code-tabs' ),
				array( 'Admonition', 'docspress/callout' ),
				array( 'API exchange', 'docspress/api-request' ),
				array( 'Terminal command and output', 'docspress/terminal-session' ),
				array( 'Execution outcome', 'docspress/result' ),
				array( 'Repository structure', 'docspress/file-tree' ),
				array( 'AI prompt', 'docspress/prompt' ),
			)
		),
		docspress_playground_callout( 'note', 'Gutenberg escape hatch', 'Serialized Gutenberg comments are preserved, so authors can use these native blocks directly when a Markdown mapping does not exist yet.' )
	),
	$guides,
	0,
	'Headings, code, tables, lists, and Gutenberg escape hatches.',
	'docs/guides/markdown-features.md'
);

docspress_playground_page(
	'Deployment',
	'deployment',
	docspress_playground_document(
		docspress_playground_paragraph( 'DocsPress runs wherever GitHub Actions can reach the WordPress REST API.' ),
		docspress_playground_heading( 'Draft workflow' ),
		docspress_playground_list( array( 'Sync new documents as drafts.', 'Review the Pages in WordPress.', 'Change the action status to publish.' ), true ),
		docspress_playground_heading( 'Continuous publishing' ),
		docspress_playground_paragraph( 'Limit the workflow trigger to changes under <code>docs/**</code>. This keeps documentation deploys focused and easy to audit.' ),
		docspress_playground_callout( 'success', 'Ready to ship', 'A reviewed draft and a path-scoped workflow make documentation releases predictable.' ),
		docspress_playground_result( 'neutral', 'Deployment contract', 'Only changes under <code>docs/**</code> trigger publication; application builds stay untouched.', 'path scoped' )
	),
	$guides,
	10,
	'Move from a safe draft preview to continuous documentation publishing.'
);

docspress_playground_page(
	'API Reference',
	'api-reference',
	docspress_playground_document(
		docspress_playground_paragraph( 'The WordPress REST API is the small seam between repository content and published Pages.' ),
		docspress_playground_heading( 'Page reconciliation' ),
		docspress_playground_paragraph( 'DocsPress compares the desired page tree with managed Pages already present on the site.' ),
		docspress_playground_api_request(
			'POST',
			'/wp-json/wp/v2/pages',
			"Content-Type: application/json\nAuthorization: Bearer \$WP_ACCESS_TOKEN",
			"{\n  \"title\": \"Getting Started\",\n  \"slug\": \"getting-started\",\n  \"status\": \"draft\"\n}",
			'201 Created',
			"{\n  \"id\": 42,\n  \"slug\": \"getting-started\",\n  \"status\": \"draft\"\n}"
		),
		docspress_playground_heading( 'Client examples', 3 ),
		docspress_playground_code_tabs(
			array(
				array( 'label' => 'cURL', 'language' => 'bash', 'filename' => 'Terminal', 'code' => "curl https://example.com/wp-json/wp/v2/pages\n\ncurl -X POST https://example.com/wp-json/wp/v2/pages" ),
				array( 'label' => 'JavaScript', 'language' => 'javascript', 'filename' => 'pages.js', 'code' => "const response = await fetch( '/wp-json/wp/v2/pages' );\nconst pages = await response.json();" ),
				array( 'label' => 'PHP', 'language' => 'php', 'filename' => 'pages.php', 'code' => "\$response = wp_remote_get( rest_url( 'wp/v2/pages' ) );\n\$pages = json_decode( wp_remote_retrieve_body( \$response ) );" ),
			)
		),
		docspress_playground_heading( 'Version taxonomy' ),
		docspress_playground_paragraph( 'When versioning is enabled, the first folder below <code>docs/</code> becomes the version term. Register <code>docspress_version</code> for Pages and expose it through REST; the theme will show its version selector automatically.' ),
		docspress_playground_callout( 'danger', 'Protect credentials', 'Use server-side tokens and repository secrets. Never place WordPress access tokens in browser-side examples.' )
	),
	$docs,
	30,
	'The REST resources used to keep documentation Pages in sync.'
);

$kitchen_sink = docspress_playground_page(
	'Kitchen Sink',
	'kitchen-sink',
	docspress_playground_document(
		docspress_playground_paragraph( 'This page is the complete WordPress Playground acceptance surface for the DocsPress theme and every installed documentation block.' ),
		docspress_playground_callout( 'note', 'How to use this page', 'Change the design preset or light/dark mode, then scan every section for typography, spacing, borders, interactions, and responsive behavior.' ),
		docspress_playground_heading( 'Playground runtime' ),
		docspress_playground_paragraph( 'The inventory below is generated from the running WordPress installation, so newly installed blueprint plugins appear automatically.' ),
		docspress_playground_table(
			array( 'Component', 'Type', 'Version', 'Status' ),
			docspress_playground_component_rows()
		),
		docspress_playground_callout( 'tip', 'Jetpack Offline Mode', 'Jetpack is active with <code>JETPACK_DEV_DEBUG</code>, so its local features can be inspected without connecting this temporary site to WordPress.com.' ),
		docspress_playground_heading( 'Theme option matrix' ),
		docspress_playground_table(
			array( 'Customizer section', 'Options exercised by the demo' ),
			array(
				array( 'Design preset', 'DocsPress, WordPress.org, WordPress.com, Jetpack, Custom' ),
				array( 'Color mode', 'Light, Dark, visible switcher, enforced default when hidden' ),
				array( 'Navigation', 'Automatic Page tree or selected WordPress menu; depth, ordering, root, filter, version selector' ),
				array( 'Command search', 'Labels, result count, dimensions, radius, backdrop, paths, excerpts, keyboard legend' ),
				array( 'Reading layout', 'Article/sidebar/TOC widths; compact, comfortable, roomy density' ),
				array( 'Article actions', 'WordPress edit and exact GitHub Markdown proposal links' ),
			)
		),
		docspress_playground_heading( 'Colorful Code' ),
		docspress_playground_paragraph( 'Options: language, filename, highlighted line expression, line numbers, caption, and copy.' ),
		docspress_playground_code(
			"name: Publish docs\non:\n  push:\n    paths: [\"docs/**\"]\nsteps:\n  - uses: Automattic/docspress@main",
			'yaml',
			'.github/workflows/docs.yml',
			'3-4,6',
			true,
			'Line numbers, multiple highlighted ranges, filename, language, and caption enabled.'
		),
		docspress_playground_code(
			"Markdown in.\nWordPress out.",
			'plaintext',
			'Without line numbers',
			'',
			false,
			'Plain text with line numbers disabled.'
		),
		docspress_playground_heading( 'Code Tabs' ),
		docspress_playground_paragraph( 'Options: two to eight tabs, independent labels, languages, filenames and source, shared line-number visibility, caption, keyboard navigation, and copy.' ),
		docspress_playground_code_tabs(
			array(
				array( 'label' => 'npm', 'language' => 'bash', 'filename' => 'Terminal', 'code' => 'npm install docspress' ),
				array( 'label' => 'pnpm', 'language' => 'bash', 'filename' => 'Terminal', 'code' => 'pnpm add docspress' ),
				array( 'label' => 'Yarn', 'language' => 'bash', 'filename' => 'Terminal', 'code' => 'yarn add docspress' ),
				array( 'label' => 'Bun', 'language' => 'bash', 'filename' => 'Terminal', 'code' => 'bun add docspress' ),
				array( 'label' => 'JavaScript', 'language' => 'javascript', 'filename' => 'publish.js', 'code' => "import { publish } from 'docspress';\nawait publish();" ),
				array( 'label' => 'PHP', 'language' => 'php', 'filename' => 'publish.php', 'code' => "<?php\ndocspress_publish();" ),
				array( 'label' => 'Python', 'language' => 'python', 'filename' => 'publish.py', 'code' => "from docspress import publish\npublish()" ),
				array( 'label' => 'cURL', 'language' => 'bash', 'filename' => 'Terminal', 'code' => 'curl https://example.com/wp-json/wp/v2/pages' ),
			),
			false,
			'The maximum eight compact tabs with line numbers disabled.'
		),
		docspress_playground_heading( 'Callouts' ),
		docspress_playground_paragraph( 'Options: note, tip, warning, danger, and success tones; static or reader-collapsible; open or closed by default.' ),
		docspress_playground_callout( 'note', 'Note', 'Neutral context that belongs beside the current step.' ),
		docspress_playground_callout( 'tip', 'Tip', 'A useful shortcut or recommended practice.' ),
		docspress_playground_callout( 'warning', 'Warning', 'A condition readers should check before continuing.' ),
		docspress_playground_callout( 'danger', 'Danger', 'A destructive or security-sensitive action.' ),
		docspress_playground_callout( 'success', 'Success', 'A confirmed positive state or completed milestone.' ),
		docspress_playground_callout( 'note', 'Collapsible, open by default', 'Readers can hide this longer explanation.', true, true ),
		docspress_playground_callout( 'tip', 'Collapsible, closed by default', 'This content begins hidden and remains accessible to keyboard and screen-reader users.', true, false ),
		docspress_playground_heading( 'API Request / Response' ),
		docspress_playground_paragraph( 'Options: GET, POST, PUT, PATCH, and DELETE; endpoint, optional headers/body, response status, response body, and URL copy.' ),
		docspress_playground_api_request( 'GET', '/wp-json/wp/v2/pages?per_page=2', 'Accept: application/json', '', '200 OK', "[\n  { \"id\": 41, \"slug\": \"docs\" },\n  { \"id\": 42, \"slug\": \"getting-started\" }\n]" ),
		docspress_playground_api_request( 'POST', '/wp-json/wp/v2/pages', "Content-Type: application/json\nAuthorization: Bearer \$WP_ACCESS_TOKEN", "{\n  \"title\": \"API Reference\",\n  \"status\": \"draft\"\n}", '201 Created', "{\n  \"id\": 43,\n  \"status\": \"draft\"\n}" ),
		docspress_playground_api_request( 'PUT', '/wp-json/wp/v2/pages/43', 'Content-Type: application/json', "{\n  \"title\": \"REST API Reference\"\n}", '200 OK', "{\n  \"id\": 43,\n  \"title\": { \"rendered\": \"REST API Reference\" }\n}" ),
		docspress_playground_api_request( 'PATCH', '/wp-json/wp/v2/pages/43', 'Content-Type: application/json', "{\n  \"status\": \"publish\"\n}", '200 OK', "{\n  \"id\": 43,\n  \"status\": \"publish\"\n}" ),
		docspress_playground_api_request( 'DELETE', '/wp-json/wp/v2/pages/43?force=true', 'Authorization: Bearer $WP_ACCESS_TOKEN', '', '204 No Content', '' ),
		docspress_playground_heading( 'Terminal Session' ),
		docspress_playground_paragraph( 'Options: title, shell label, prompt character, multiline command, optional output, and command copy.' ),
		docspress_playground_terminal( 'Publish documentation', "npx docspress publish ./docs \\\n  --status=draft", "✓ Read 18 documents\n✓ Created 18 draft pages\nCompleted in 2.1s", 'bash', '$' ),
		docspress_playground_terminal( 'Inspect the site', 'wp option get docspress_playground_runtime --format=json', '', 'wp-cli', '>' ),
		docspress_playground_terminal( 'Windows example', 'Get-ChildItem .\\docs -Recurse', "Directory: C:\\project\\docs\nMode  Name\n-a--- introduction.md", 'pwsh', 'PS>' ),
		docspress_playground_heading( 'Result' ),
		docspress_playground_paragraph( 'Options: success, neutral, warning, and error status; title, rich content, and optional compact metadata.' ),
		docspress_playground_result( 'success', 'All checks passed', 'The generated Pages match the repository tree.', '18 pages · 2.1s' ),
		docspress_playground_result( 'neutral', 'No changes required', 'WordPress is already synchronized with the current commit.', '0 updates' ),
		docspress_playground_result( 'warning', 'Drafts need review', 'Three new Pages are waiting for editorial approval.', '3 drafts' ),
		docspress_playground_result( 'error', 'Publication failed', 'The access token cannot create Pages on this site.', 'HTTP 403' ),
		docspress_playground_heading( 'File Tree' ),
		docspress_playground_paragraph( 'Options: root label, two-space indentation, folder detection from a trailing slash, files, and optional caption.' ),
		docspress_playground_file_tree(
			'Automattic/docspress/',
			"docs/\n  introduction.md\n  guides/\n    installation.md\n    deployment.md\nplugins/\n  docspress-blocks/\n    blocks/\n      prompt/\ntheme/\n  playground/\n    setup.php\npackage.json",
			'Nested folders, files, a custom root label, and caption.'
		),
		docspress_playground_file_tree( 'docs/', "index.md\napi.md\nchangelog.md" ),
		docspress_playground_heading( 'Prompt' ),
		docspress_playground_paragraph( 'Options: free-form model label, Chat/Code/Ask/Plan modes, optional Thinking state, copy, and context chips for @mentions, #images, URLs, and file paths.' ),
		docspress_playground_prompt( 'Explain DocsPress to a new contributor in three short paragraphs.', 'GPT-5', 'chat', '@documentation, https://github.com/Automattic/docspress', false ),
		docspress_playground_prompt( "Review this synchronization function for race conditions.\n\nReturn risks first, then the smallest safe patch.", 'Claude Sonnet', 'code', '@repository, src/sync.js, test/sync.test.js, #trace', true ),
		docspress_playground_prompt( 'Which configuration inputs affect the generated WordPress page hierarchy?', 'Gemini Pro', 'ask', '@web, action.yml, README.md', false ),
		docspress_playground_prompt( 'Create a phased migration plan from a static documentation site to DocsPress.', 'Custom planning agent', 'plan', 'docs/, #architecture, https://example.com/current-docs', true ),
		docspress_playground_heading( 'Patterns and native blocks' ),
		docspress_playground_table(
			array( 'Pattern', 'Included DocsPress blocks' ),
			array(
				array( 'Documentation page starter', 'Callout, Terminal Session, Result' ),
				array( 'API request example', 'API Request / Response, Code Tabs' ),
				array( 'AI prompt example', 'Prompt, Result' ),
			)
		),
		docspress_playground_list(
			array(
				'This page also exercises native Gutenberg Paragraph, Heading, List, and Table blocks.',
				'Every DocsPress example is stored as canonical serialized <code>&lt;!-- wp:docspress/* --&gt;</code> markup.',
				'Open this Page in WordPress to edit every example through its native block controls.',
			)
		),
		docspress_playground_callout( 'success', 'Kitchen Sink complete', 'Every installed component, DocsPress block, semantic variant, and meaningful option is represented on this page.' )
	),
	$docs,
	40,
	'Every DocsPress block, state, option, installed plugin, and theme control in one Playground acceptance page.',
	'docs/kitchen-sink.md'
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
			'menu-item-title'     => 'Kitchen Sink',
			'menu-item-object-id' => $kitchen_sink,
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
		'docspress_blocks_active' => in_array( 'docspress-blocks/docspress-blocks.php', (array) get_option( 'active_plugins', array() ), true ),
		'jetpack_offline_mode' => defined( 'JETPACK_DEV_DEBUG' ) && JETPACK_DEV_DEBUG,
		'environment'         => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		'kitchen_sink_page'   => (int) $kitchen_sink,
		'design_preset'       => get_theme_mod( 'docspress_design_preset', 'docspress' ),
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
