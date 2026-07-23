<?php
/**
 * Seed the source-backed DocsPress documentation in WordPress Playground.
 *
 * Run npm run playground:docs from the repository root after changing docs/.
 *
 * @package DocsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/taxonomy.php';

/**
 * Create or update one generated documentation Page.
 *
 * @param array $page      Generated Page data.
 * @param int   $parent_id Parent WordPress Page ID.
 * @param int   $order     Menu order within the parent.
 * @return int
 */
function docspress_playground_upsert_page( $page, $parent_id, $order ) {
	$theme_asset_source = 'https://raw.githubusercontent.com/Automattic/docspress/main/theme/';
	$theme_asset_local  = trailingslashit( get_template_directory_uri() );
	$content            = str_replace( $theme_asset_source, $theme_asset_local, (string) $page['content'] );
	$existing = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'name'           => sanitize_title( $page['slug'] ),
			'post_parent'    => $parent_id,
			'posts_per_page' => 1,
		)
	);

	$post = array(
		'post_title'     => sanitize_text_field( $page['title'] ),
		'post_name'      => sanitize_title( $page['slug'] ),
		'post_content'   => wp_slash( $content ),
		'post_parent'    => $parent_id,
		'menu_order'     => array_key_exists( 'sidebarPosition', $page ) ? (int) $page['sidebarPosition'] : $order,
		'post_status'    => 'publish',
		'post_type'      => 'page',
		'comment_status' => 'closed',
	);

	if ( $existing ) {
		$post['ID'] = $existing[0]->ID;
		$page_id    = wp_update_post( $post );
	} else {
		$page_id = wp_insert_post( $post );
	}

	if ( $page_id && ! is_wp_error( $page_id ) && ! empty( $page['sourcePath'] ) ) {
		update_post_meta( $page_id, '_docspress_source_path', sanitize_text_field( $page['sourcePath'] ) );
	}

	return is_wp_error( $page_id ) ? 0 : (int) $page_id;
}

/**
 * Create or resolve a Playground navigation menu.
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
 * Remove existing items so generated menus remain deterministic.
 *
 * @param int $menu_id Menu term ID.
 */
function docspress_playground_clear_menu( $menu_id ) {
	foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
		wp_delete_post( $item->ID, true );
	}
}

/**
 * Add a Page to a navigation menu.
 *
 * @param int    $menu_id        Menu term ID.
 * @param int    $page_id        Page post ID.
 * @param string $title          Menu label.
 * @param int    $menu_parent_id Parent menu item ID.
 * @return int
 */
function docspress_playground_add_page_menu_item( $menu_id, $page_id, $title, $menu_parent_id = 0 ) {
	$item_id = wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'     => sanitize_text_field( $title ),
			'menu-item-object-id' => $page_id,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-parent-id' => $menu_parent_id,
			'menu-item-status'    => 'publish',
		)
	);

	return is_wp_error( $item_id ) ? 0 : (int) $item_id;
}

/**
 * Create or update a hand-authored demo post.
 *
 * @param string $post_type Post type.
 * @param string $slug      Post slug.
 * @param string $title     Post title.
 * @param string $content   Gutenberg content.
 * @param array  $overrides Additional wp_insert_post fields.
 * @return int
 */
function docspress_playground_upsert_content( $post_type, $slug, $title, $content, $overrides = array() ) {
	$existing = get_page_by_path( $slug, OBJECT, $post_type );
	$post     = array_merge(
		array(
			'post_title'     => sanitize_text_field( $title ),
			'post_name'      => sanitize_title( $slug ),
			'post_content'   => wp_slash( $content ),
			'post_status'    => 'publish',
			'post_type'      => $post_type,
			'comment_status' => 'closed',
		),
		$overrides
	);

	if ( $existing ) {
		$post['ID'] = $existing->ID;
		$post_id    = wp_update_post( $post );
	} else {
		$post_id = wp_insert_post( $post );
	}

	return is_wp_error( $post_id ) ? 0 : (int) $post_id;
}

/**
 * Create or update one deterministic demo comment.
 *
 * @param int    $post_id   Post ID.
 * @param string $key       Stable demo key.
 * @param string $author    Comment author.
 * @param string $content   Comment content.
 * @param int    $parent_id Parent comment ID.
 * @return int
 */
function docspress_playground_upsert_comment( $post_id, $key, $author, $content, $parent_id = 0 ) {
	$existing = get_comments(
		array(
			'post_id'    => $post_id,
			'meta_key'   => '_docspress_playground_comment',
			'meta_value' => $key,
			'number'     => 1,
			'status'     => 'all',
		)
	);
	$data     = array(
		'comment_post_ID'      => $post_id,
		'comment_author'       => sanitize_text_field( $author ),
		'comment_author_email' => sanitize_email( strtolower( str_replace( ' ', '.', $author ) ) . '@example.com' ),
		'comment_content'      => wp_kses_post( $content ),
		'comment_approved'     => 1,
		'comment_parent'       => $parent_id,
		'comment_type'         => 'comment',
	);

	if ( $existing ) {
		$data['comment_ID'] = $existing[0]->comment_ID;
		$result             = wp_update_comment( $data );
		$comment_id         = $result ? $existing[0]->comment_ID : 0;
	} else {
		$comment_id = wp_insert_comment( $data );
	}

	if ( $comment_id ) {
		update_comment_meta( $comment_id, '_docspress_playground_comment', $key );
	}

	return (int) $comment_id;
}

/**
 * Build a live Gutenberg table of Playground components.
 *
 * @return string
 */
function docspress_playground_component_inventory() {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$active       = (array) get_option( 'active_plugins', array() );
	$active_theme = wp_get_theme();
	$rows         = array(
		array( 'WordPress', 'Core', get_bloginfo( 'version' ), 'Running' ),
		array( $active_theme->get( 'Name' ), 'Theme', $active_theme->get( 'Version' ), 'Active' ),
	);

	foreach ( get_plugins() as $plugin_file => $plugin ) {
		$rows[] = array(
			$plugin['Name'] ? $plugin['Name'] : $plugin_file,
			'Plugin',
			$plugin['Version'] ? $plugin['Version'] : '—',
			in_array( $plugin_file, $active, true ) ? 'Active' : 'Inactive',
		);
	}

	$table_rows = '';
	foreach ( $rows as $row ) {
		$table_rows .= '<tr>';
		foreach ( $row as $cell ) {
			$table_rows .= '<td>' . esc_html( $cell ) . '</td>';
		}
		$table_rows .= '</tr>';
	}

	$heading = get_comment_delimited_block_content(
		'core/heading',
		array(),
		'<h2 class="wp-block-heading">Playground runtime</h2>'
	);
	$paragraph = get_comment_delimited_block_content(
		'core/paragraph',
		array(),
		'<p>This inventory is generated from the running WordPress installation.</p>'
	);
	$table = get_comment_delimited_block_content(
		'core/table',
		array(),
		'<figure class="wp-block-table"><table><thead><tr><th>Component</th><th>Type</th><th>Version</th><th>Status</th></tr></thead><tbody>' . $table_rows . '</tbody></table></figure>'
	);

	return implode( "\n\n", array( $heading, $paragraph, $table ) );
}

$generated_path = __DIR__ . '/generated-docs.json';
if ( ! file_exists( $generated_path ) ) {
	wp_die( 'Missing generated Playground docs. Run npm run playground:docs.' );
}

$generated = json_decode( file_get_contents( $generated_path ), true );
if ( ! is_array( $generated ) || empty( $generated['pages'] ) || ! is_array( $generated['pages'] ) ) {
	wp_die( 'The generated Playground documentation payload is invalid.' );
}

// Remove WordPress starter content so the acceptance site remains deterministic.
foreach ( array( array( 'post', 'hello-world' ), array( 'page', 'sample-page' ) ) as $starter_content ) {
	$starter_post = get_page_by_path( $starter_content[1], OBJECT, $starter_content[0] );
	if ( $starter_post ) {
		wp_delete_post( $starter_post->ID, true );
	}
}

usort(
	$generated['pages'],
	static function ( $left, $right ) {
		$depth_comparison = (int) $left['depth'] <=> (int) $right['depth'];
		return $depth_comparison ? $depth_comparison : strcmp( $left['key'], $right['key'] );
	}
);

$ids_by_key      = array();
$order_by_parent = array();
$kitchen_sink_id = 0;

foreach ( $generated['pages'] as $page ) {
	$parent_key = isset( $page['parentKey'] ) ? (string) $page['parentKey'] : '';
	$parent_id  = $parent_key && isset( $ids_by_key[ $parent_key ] ) ? $ids_by_key[ $parent_key ] : 0;
	if ( $parent_key && ! $parent_id ) {
		wp_die( esc_html( 'Generated documentation parent is unavailable: ' . $parent_key ) );
	}

	if ( 'docs/reference/kitchen-sink' === $page['key'] ) {
		$page['content'] .= "\n\n" . docspress_playground_component_inventory();
	}

	$order_key = $parent_key ? $parent_key : 'root';
	$order     = isset( $order_by_parent[ $order_key ] ) ? $order_by_parent[ $order_key ] : 0;
	$page_id   = docspress_playground_upsert_page( $page, $parent_id, $order * 10 );
	if ( ! $page_id ) {
		wp_die( esc_html( 'Could not create generated documentation Page: ' . $page['key'] ) );
	}

	$ids_by_key[ $page['key'] ] = $page_id;
	$order_by_parent[ $order_key ] = $order + 1;
	if ( 'docs/reference/kitchen-sink' === $page['key'] ) {
		$kitchen_sink_id = $page_id;
	}
}

$docs_id = isset( $ids_by_key['docs'] ) ? $ids_by_key['docs'] : 0;
if ( ! $docs_id ) {
	wp_die( 'The generated documentation does not contain the docs root Page.' );
}

$publish_existing_id = isset( $ids_by_key['docs/publish-existing-docs'] ) ? $ids_by_key['docs/publish-existing-docs'] : 0;
$create_docs_id      = isset( $ids_by_key['docs/create-docs-with-ai'] ) ? $ids_by_key['docs/create-docs-with-ai'] : 0;
if ( ! $publish_existing_id || ! $create_docs_id ) {
	wp_die( 'The generated documentation does not contain both homepage starting paths.' );
}

$updates_id = docspress_playground_upsert_content( 'page', 'updates', 'Updates', '' );
$hero_attributes = array(
	'eyebrow'         => 'Documentation, publishing, and community',
	'title'           => 'Docs that stay connected to your GitHub repo',
	'description'     => 'Write beside your code. Publish a WordPress experience that guides every reader to the docs written for them.',
	'primaryLabel'    => 'Choose your path',
	'primaryUrl'      => '#choose-your-path',
	'secondaryLabel'  => 'Latest updates',
	'secondaryUrl'    => $updates_id ? get_permalink( $updates_id ) : home_url( '/#latest-updates' ),
	'mediaUrl'        => get_theme_file_uri( 'assets/images/homepage-octocat-wapuu.webp' ),
	'mediaAlt'        => 'The GitHub Octocat and WordPress Wapuu celebrating their documentation workflow together.',
	'visualLabel'     => 'DOCS',
	'layout'          => 'editorial',
	'mediaPosition'   => 'right',
	'mediaWidth'      => 38,
	'imageScale'      => 90,
	'height'          => 'standard',
	'tone'            => 'midnight',
	'textAlign'       => 'left',
	'showGrid'        => true,
	'showOrbit'       => false,
);
$audience_paths_attributes = array(
	'anchor'      => 'choose-your-path',
	'align'       => 'wide',
	'eyebrow'     => 'Choose a starting point',
	'title'       => 'Where are your docs today?',
	'description' => 'Follow the path that matches your repository.',
	'paths'       => array(
		array(
			'title'       => 'I already have Markdown docs',
			'description' => 'Connect an existing docs folder to WordPress and begin with a safe draft sync.',
			'url'         => get_permalink( $publish_existing_id ),
			'cta'         => 'Publish existing docs',
			'icon'        => 'MD',
			'accent'      => 'blue',
			'newTab'      => false,
		),
		array(
			'title'       => 'I need to create docs',
			'description' => 'Generate source-grounded documentation with AI, review it, then publish it.',
			'url'         => get_permalink( $create_docs_id ),
			'cta'         => 'Create docs with AI',
			'icon'        => 'AI',
			'accent'      => 'gold',
			'newTab'      => false,
		),
	),
	'columns'     => 2,
	'tone'        => 'theme',
	'textAlign'   => 'left',
	'showNumbers' => false,
);
$home_content = '<!-- wp:docspress/hero ' . serialize_block_attributes( $hero_attributes ) . ' /-->'
	. "\n\n"
	. '<!-- wp:docspress/audience-paths ' . serialize_block_attributes( $audience_paths_attributes ) . ' /-->'
	. "\n\n"
	. <<<'HTML'
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">One WordPress site, every publishing surface</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>DocsPress keeps product documentation in a focused reading shell without switching off the rest of WordPress. Publish updates, invite discussion, build menus, add widgets, and customize the whole experience from the familiar admin.</p>
<!-- /wp:paragraph -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Documentation</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Source-backed Pages gain navigation, search, Markdown routes, and an <code>llms.txt</code> index.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Community</h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Native posts and threaded comments make release notes, ideas, and support conversations feel at home.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
HTML;

$home_id = docspress_playground_upsert_content(
	'page',
	'home',
	'Docs connected to your GitHub repo',
	$home_content,
	array(
		'post_excerpt' => 'Write beside your code and guide every reader to the documentation path written for them.',
	)
);

$release_post_id = docspress_playground_upsert_content(
	'post',
	'native-discussions-arrive',
	'Native discussions arrive in DocsPress',
	'<!-- wp:paragraph --><p>The theme now treats WordPress comments as a first-class publishing surface. Threaded replies, moderation, avatars, paging, and comment status remain powered by WordPress core.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Designed for real conversations</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Theme controls decide where discussion appears and how it is presented, while site owners keep using <strong>Settings → Discussion</strong> for policy and workflow.</p><!-- /wp:paragraph -->',
	array(
		'post_excerpt'   => 'Threaded replies and the complete WordPress discussion workflow now share the DocsPress reading system.',
		'comment_status' => 'open',
	)
);
$workflow_post_id = docspress_playground_upsert_content(
	'post',
	'publishing-workflow-notes',
	'Publishing workflow notes',
	'<!-- wp:paragraph --><p>A documentation site can also tell the story behind each release. Use normal WordPress posts for announcements and keep reference material in the synchronized Page tree.</p><!-- /wp:paragraph --><!-- wp:list --><ul class="wp-block-list"><li>Draft and preview in WordPress.</li><li>Organize updates with categories and tags.</li><li>Let readers subscribe through native feeds.</li></ul><!-- /wp:list -->',
	array(
		'post_excerpt'   => 'Use posts for release notes and announcements while DocsPress Pages remain source-backed.',
		'comment_status' => 'open',
	)
);
$community_post_id = docspress_playground_upsert_content(
	'post',
	'community-feedback-loop',
	'Close the community feedback loop',
	'<!-- wp:paragraph --><p>Documentation improves when readers can ask a question at the point of need. Keep comments open on selected Pages or posts, moderate them with core tools, and turn useful answers into lasting documentation.</p><!-- /wp:paragraph -->',
	array(
		'post_excerpt'   => 'A practical way to turn reader questions into stronger documentation.',
		'comment_status' => 'open',
	)
);

$updates_category = wp_create_category( 'Product updates' );
$community_category = wp_create_category( 'Community' );
if ( $release_post_id ) {
	wp_set_post_categories( $release_post_id, array_filter( array( $updates_category, $community_category ) ) );
	wp_set_post_tags( $release_post_id, array( 'comments', 'theme' ) );
	$first_comment = docspress_playground_upsert_comment(
		$release_post_id,
		'release-question',
		'Maya Reader',
		'<p>Can I keep discussions enabled on release notes while leaving synchronized reference Pages closed?</p>'
	);
	docspress_playground_upsert_comment(
		$release_post_id,
		'release-answer',
		'DocsPress Team',
		'<p>Yes. WordPress stores comment status per post, and the theme also provides separate visibility controls for Pages and posts.</p>',
		$first_comment
	);
	docspress_playground_upsert_comment(
		$release_post_id,
		'release-feedback',
		'Theo Builder',
		'<p>The separate reply button and active thread styling work especially well on mobile.</p>'
	);
}
if ( $workflow_post_id ) {
	wp_set_post_categories( $workflow_post_id, array_filter( array( $updates_category ) ) );
	wp_set_post_tags( $workflow_post_id, array( 'publishing', 'workflow' ) );
}
if ( $community_post_id ) {
	wp_set_post_categories( $community_post_id, array_filter( array( $community_category ) ) );
	wp_set_post_tags( $community_post_id, array( 'feedback', 'documentation' ) );
}

$header_menu = docspress_playground_menu( 'DocsPress Header' );
if ( $header_menu ) {
	docspress_playground_clear_menu( $header_menu );
	if ( $home_id ) {
		docspress_playground_add_page_menu_item( $header_menu, $home_id, 'Home' );
	}
	docspress_playground_add_page_menu_item( $header_menu, $docs_id, 'Docs' );
	if ( $updates_id ) {
		docspress_playground_add_page_menu_item( $header_menu, $updates_id, 'Updates' );
	}
	if ( isset( $ids_by_key['docs/why-docspress'] ) ) {
		docspress_playground_add_page_menu_item( $header_menu, $ids_by_key['docs/why-docspress'], 'Why DocsPress?' );
	}
	if ( $kitchen_sink_id ) {
		docspress_playground_add_page_menu_item( $header_menu, $kitchen_sink_id, 'Kitchen Sink' );
	}
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
if ( $sidebar_menu ) {
	docspress_playground_clear_menu( $sidebar_menu );
	$menu_ids_by_page = array( 0 => 0 );
	foreach ( $generated['pages'] as $page ) {
		$page_id       = $ids_by_key[ $page['key'] ];
		$parent_id     = $page['parentKey'] ? $ids_by_key[ $page['parentKey'] ] : 0;
		$menu_parent_id = isset( $menu_ids_by_page[ $parent_id ] ) ? $menu_ids_by_page[ $parent_id ] : 0;
		$menu_ids_by_page[ $page_id ] = docspress_playground_add_page_menu_item(
			$sidebar_menu,
			$page_id,
			$page['title'],
			$menu_parent_id
		);
	}
}

$footer_menu = docspress_playground_menu( 'DocsPress Footer' );
if ( $footer_menu ) {
	docspress_playground_clear_menu( $footer_menu );
	docspress_playground_add_page_menu_item( $footer_menu, $docs_id, 'Documentation' );
	if ( $updates_id ) {
		docspress_playground_add_page_menu_item( $footer_menu, $updates_id, 'Updates' );
	}
}

$menu_locations = get_theme_mod( 'nav_menu_locations', array() );
if ( $header_menu ) {
	$menu_locations['primary'] = $header_menu;
}
if ( $sidebar_menu ) {
	$menu_locations['docs_sidebar'] = $sidebar_menu;
}
if ( $footer_menu ) {
	$menu_locations['footer'] = $footer_menu;
}
set_theme_mod( 'nav_menu_locations', $menu_locations );

update_option( 'blogname', 'DocsPress' );
update_option( 'blogdescription', 'Markdown in GitHub. Native documentation in WordPress.' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_id );
update_option( 'page_for_posts', $updates_id );
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'default_comment_status', 'open' );
update_option( 'thread_comments', 1 );
update_option( 'page_comments', 1 );
update_option( 'comments_per_page', 5 );

set_theme_mod( 'docspress_docs_root', $docs_id );
set_theme_mod( 'docspress_sidebar_source', 'page_tree' );
set_theme_mod( 'docspress_sidebar_sort', 'menu_order' );
set_theme_mod( 'docspress_github_url', 'https://github.com/Automattic/docspress' );
set_theme_mod( 'docspress_github_edit_repository_url', 'https://github.com/Automattic/docspress' );
set_theme_mod( 'docspress_github_edit_ref', 'main' );

update_option(
	'docspress_playground_runtime',
	array(
		'jetpack_active'          => in_array( 'jetpack/jetpack.php', (array) get_option( 'active_plugins', array() ), true ),
		'docspress_blocks_active' => in_array( 'docspress-blocks/docspress-blocks.php', (array) get_option( 'active_plugins', array() ), true ),
		'page_count'              => count( $generated['pages'] ),
		'home_page'               => $home_id,
		'posts_page'              => $updates_id,
		'release_post'            => $release_post_id,
		'demo_comment_count'      => $release_post_id ? get_comments_number( $release_post_id ) : 0,
		'docs_page'               => $docs_id,
		'publish_existing_page'   => $publish_existing_id,
		'create_docs_page'        => $create_docs_id,
		'kitchen_sink_page'       => $kitchen_sink_id,
		'design_preset'           => get_theme_mod( 'docspress_design_preset', 'docspress' ),
	)
);

flush_rewrite_rules();
