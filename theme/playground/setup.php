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
		'menu_order'     => $order,
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

$header_menu = docspress_playground_menu( 'DocsPress Header' );
if ( $header_menu ) {
	docspress_playground_clear_menu( $header_menu );
	docspress_playground_add_page_menu_item( $header_menu, $docs_id, 'Docs' );
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

$menu_locations = get_theme_mod( 'nav_menu_locations', array() );
if ( $header_menu ) {
	$menu_locations['primary'] = $header_menu;
}
if ( $sidebar_menu ) {
	$menu_locations['docs_sidebar'] = $sidebar_menu;
}
set_theme_mod( 'nav_menu_locations', $menu_locations );

update_option( 'blogname', 'DocsPress' );
update_option( 'blogdescription', 'Markdown in GitHub. Native documentation in WordPress.' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $docs_id );
update_option( 'permalink_structure', '/%postname%/' );

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
		'docs_page'               => $docs_id,
		'kitchen_sink_page'       => $kitchen_sink_id,
		'design_preset'           => get_theme_mod( 'docspress_design_preset', 'docspress' ),
	)
);

flush_rewrite_rules();
