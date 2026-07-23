<?php
/**
 * DocsPress theme functions.
 *
 * @package DocsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require get_theme_file_path( 'inc/design-presets/loader.php' );

/**
 * Set up theme defaults.
 */
function docspress_setup() {
	load_theme_textdomain( 'docspress', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_editor_style( 'style.css' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 64,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary'      => __( 'Header navigation', 'docspress' ),
			'docs_sidebar' => __( 'Documentation sidebar', 'docspress' ),
			'footer'       => __( 'Footer navigation', 'docspress' ),
		)
	);
}
add_action( 'after_setup_theme', 'docspress_setup' );

/**
 * Register widget areas exposed by the theme.
 */
function docspress_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Footer widgets', 'docspress' ),
			'id'            => 'footer-widgets',
			'description'   => __( 'Widgets shown above the footer navigation and copyright line.', 'docspress' ),
			'before_widget' => '<section id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="footer-widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'docspress_widgets_init' );

/**
 * Enqueue the theme assets.
 */
function docspress_assets() {
	$theme = wp_get_theme();
	wp_enqueue_style( 'docspress-style', get_stylesheet_uri(), array(), $theme->get( 'Version' ) );
	wp_enqueue_script(
		'docspress-navigation',
		get_theme_file_uri( 'assets/js/docs.js' ),
		array(),
		$theme->get( 'Version' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'docspress_assets' );

require get_theme_file_path( 'inc/customizer.php' );
require get_theme_file_path( 'inc/llms.php' );
require get_theme_file_path( 'inc/performance.php' );
require get_theme_file_path( 'inc/search.php' );

/**
 * Return a small inline icon.
 *
 * @param string $name Icon name.
 * @return string
 */
function docspress_icon( $name ) {
	$icons = array(
		'book'     => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 4.8c2.6-.7 4.9-.2 7 1.4v13c-2.1-1.6-4.4-2.1-7-1.4v-13Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M19 4.8c-2.6-.7-4.9-.2-7 1.4v13c2.1-1.6 4.4-2.1 7-1.4v-13Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>',
		'menu'     => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
		'search'   => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
		'sun'      => '<svg class="theme-icon-light" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="3.7" stroke="currentColor" stroke-width="1.7"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
		'moon'     => '<svg class="theme-icon-dark" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 15.3A8.5 8.5 0 0 1 8.7 4a8.5 8.5 0 1 0 11.3 11.3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>',
		'pencil'   => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m14.7 5.3 4 4M5 19l2.1-5.1L16.6 4.4a1.4 1.4 0 0 1 2 0l1 1a1.4 1.4 0 0 1 0 2L10.1 17 5 19Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'github'   => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 6.58 2 12.23c0 4.51 2.87 8.34 6.84 9.69.5.1.68-.22.68-.49 0-.24-.01-1.05-.01-1.9-2.78.62-3.37-1.2-3.37-1.2-.45-1.18-1.11-1.49-1.11-1.49-.91-.64.07-.62.07-.62 1 .07 1.53 1.06 1.53 1.06.9 1.57 2.35 1.12 2.92.85.09-.66.35-1.12.64-1.37-2.22-.26-4.55-1.14-4.55-5.07 0-1.12.39-2.04 1.03-2.76-.1-.26-.45-1.3.1-2.72 0 0 .84-.28 2.75 1.05A9.36 9.36 0 0 1 12 6.92c.85 0 1.69.12 2.49.34 1.91-1.33 2.75-1.05 2.75-1.05.55 1.42.2 2.46.1 2.72.64.72 1.03 1.64 1.03 2.76 0 3.94-2.34 4.8-4.57 5.06.36.32.68.94.68 1.9 0 1.37-.01 2.47-.01 2.8 0 .27.18.59.69.49A10.25 10.25 0 0 0 22 12.23C22 6.58 17.52 2 12 2Z"/></svg>',
		'external' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14 5h5v5M19 5l-8 8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
	);

	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

/**
 * Whether the theme should render the native discussion for a singular post.
 *
 * Core comment status, registration, moderation, threading, paging, and order
 * remain controlled by WordPress. These theme settings only control placement.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function docspress_should_show_comments( $post_id = 0 ) {
	$post_id   = $post_id ? absint( $post_id ) : get_the_ID();
	$post_type = get_post_type( $post_id );
	$enabled   = 'page' === $post_type
		? get_theme_mod( 'docspress_comments_on_pages', true )
		: get_theme_mod( 'docspress_comments_on_posts', true );

	return (bool) $enabled && ( comments_open( $post_id ) || 0 < get_comments_number( $post_id ) );
}

/**
 * Render post metadata with the configured visibility controls.
 *
 * @param int  $post_id Post ID.
 * @param bool $compact Whether to render the compact archive-card variant.
 */
function docspress_post_meta( $post_id = 0, $compact = false ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( ! $post_id || ! get_theme_mod( 'docspress_show_post_meta', true ) ) {
		return;
	}

	$parts = array();
	if ( get_theme_mod( 'docspress_show_post_date', true ) ) {
		$parts[] = sprintf(
			'<time datetime="%1$s">%2$s</time>',
			esc_attr( get_the_date( DATE_W3C, $post_id ) ),
			esc_html( get_the_date( '', $post_id ) )
		);
	}

	if ( get_theme_mod( 'docspress_show_post_author', true ) ) {
		$author_id = (int) get_post_field( 'post_author', $post_id );
		$parts[]   = sprintf(
			'<span class="byline">%1$s <a href="%2$s">%3$s</a></span>',
			esc_html__( 'By', 'docspress' ),
			esc_url( get_author_posts_url( $author_id ) ),
			esc_html( get_the_author_meta( 'display_name', $author_id ) )
		);
	}

	if ( ! $compact && get_theme_mod( 'docspress_show_comment_count', true ) && ( comments_open( $post_id ) || get_comments_number( $post_id ) ) ) {
		$parts[] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( get_comments_link( $post_id ) ),
			esc_html(
				sprintf(
					/* translators: %s: Number of comments. */
					_n( '%s comment', '%s comments', get_comments_number( $post_id ), 'docspress' ),
					number_format_i18n( get_comments_number( $post_id ) )
				)
			)
		);
	}

	if ( $parts ) {
		echo '<div class="entry-meta">' . implode( '<span aria-hidden="true">·</span>', $parts ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Each part is escaped while assembled.
	}
}

/**
 * Resolve the root page for the documentation tree.
 *
 * @return int
 */
function docspress_get_docs_root_id() {
	$root_id = absint( get_theme_mod( 'docspress_docs_root', 0 ) );

	if ( $root_id ) {
		return $root_id;
	}

	if ( is_page() ) {
		$current_id = get_queried_object_id();
		$ancestors  = get_post_ancestors( $current_id );
		return $ancestors ? (int) end( $ancestors ) : $current_id;
	}

	return 0;
}

/**
 * Get pages belonging to the configured documentation tree.
 *
 * @return WP_Post[]
 */
function docspress_get_docs_pages() {
	static $page_cache = array();

	$root_id = docspress_get_docs_root_id();
	$sort     = get_theme_mod( 'docspress_sidebar_sort', 'menu_order' );
	$sort     = in_array( $sort, array( 'menu_order', 'title', 'newest', 'oldest' ), true ) ? $sort : 'menu_order';
	$cache_id = $root_id . ':' . $sort;

	if ( isset( $page_cache[ $cache_id ] ) ) {
		return $page_cache[ $cache_id ];
	}

	$sort_options = array(
		'menu_order' => array( 'menu_order,post_title', 'ASC' ),
		'title'      => array( 'post_title', 'ASC' ),
		'newest'     => array( 'post_date', 'DESC' ),
		'oldest'     => array( 'post_date', 'ASC' ),
	);
	$pages        = get_pages(
		array(
			'post_status' => 'publish',
			'sort_column' => $sort_options[ $sort ][0],
			'sort_order'  => $sort_options[ $sort ][1],
		)
	);

	if ( ! $root_id ) {
		$page_cache[ $cache_id ] = $pages;
		return $page_cache[ $cache_id ];
	}

	$parents = array();
	foreach ( $pages as $page ) {
		$parents[ (int) $page->ID ] = (int) $page->post_parent;
	}

	$page_cache[ $cache_id ] = array_values(
		array_filter(
			$pages,
			static function ( $page ) use ( $root_id, $parents ) {
				$current = (int) $page->ID;
				while ( $current ) {
					if ( $current === $root_id ) {
						return true;
					}
					$current = isset( $parents[ $current ] ) ? $parents[ $current ] : 0;
				}
				return false;
			}
		)
	);

	return $page_cache[ $cache_id ];
}

/**
 * Group Pages by parent once so large documentation trees render in linear time.
 *
 * @param WP_Post[] $pages Documentation pages.
 * @return array<int,WP_Post[]>
 */
function docspress_group_pages_by_parent( $pages ) {
	$grouped = array();
	foreach ( $pages as $page ) {
		$parent = (int) $page->post_parent;
		if ( ! isset( $grouped[ $parent ] ) ) {
			$grouped[ $parent ] = array();
		}
		$grouped[ $parent ][] = $page;
	}

	return $grouped;
}

/**
 * Recursively render the docs page tree.
 *
 * @param WP_Post[] $pages     Pages to render.
 * @param int       $parent_id Parent page ID.
 * @param int       $root_id   Configured docs root.
 * @param int       $level     Current depth.
 * @param int       $max_depth Maximum depth, or zero for unlimited.
 * @param array     $grouped   Pages grouped by parent for recursion.
 */
function docspress_render_page_tree( $pages, $parent_id = 0, $root_id = 0, $level = 1, $max_depth = 0, $grouped = null ) {
	if ( $max_depth && $level > $max_depth ) {
		return;
	}

	$grouped = null === $grouped ? docspress_group_pages_by_parent( $pages ) : $grouped;
	if ( $root_id && 0 === $parent_id ) {
		$children = array_values(
			array_filter(
				$pages,
				static function ( $page ) use ( $root_id ) {
					return (int) $page->ID === $root_id;
				}
			)
		);
	} else {
		$children = isset( $grouped[ $parent_id ] ) ? $grouped[ $parent_id ] : array();
	}

	if ( ! $children ) {
		return;
	}

	echo '<ul>';
	foreach ( $children as $page ) {
		$is_current        = (int) get_queried_object_id() === (int) $page->ID;
		$sidebar_collapsed = docspress_get_sidebar_collapsed( $page->ID );
		$collapsed_attr    = null === $sidebar_collapsed
			? ''
			: ' data-sidebar-collapsed="' . ( $sidebar_collapsed ? 'true' : 'false' ) . '"';
		printf(
			'<li data-doc-title="%1$s"><a href="%2$s"%3$s%5$s><span class="nav-dot" aria-hidden="true"></span><span>%4$s</span></a>',
			esc_attr( wp_strip_all_tags( $page->post_title ) ),
			esc_url( get_permalink( $page ) ),
			$is_current ? ' aria-current="page"' : '',
			esc_html( $page->post_title ),
			$collapsed_attr // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attribute is assembled from fixed boolean strings.
		);
		docspress_render_page_tree( $pages, (int) $page->ID, 0, $level + 1, $max_depth, $grouped );
		echo '</li>';
	}
	echo '</ul>';
}

/**
 * Add stable IDs to article headings and build a table of contents.
 *
 * @param string $content Rendered post content.
 * @return array{content:string,toc:array<int,array{level:int,id:string,title:string}>}
 */
function docspress_prepare_content( $content ) {
	$toc  = array();
	$used = array();

	$content = preg_replace_callback(
		'/<h([2-3])([^>]*)>(.*?)<\/h\1>/is',
		static function ( $matches ) use ( &$toc, &$used ) {
			$level      = (int) $matches[1];
			$attributes = $matches[2];
			$inner      = $matches[3];
			$title      = trim( wp_strip_all_tags( $inner ) );

			if ( '' === $title ) {
				return $matches[0];
			}

			if ( preg_match( '/\sid=(["\'])(.*?)\1/i', $attributes, $id_match ) ) {
				$id = sanitize_title( $id_match[2] );
			} else {
				$id = sanitize_title( $title );
				$id = $id ? $id : 'section';
			}

			$base = $id;
			$i    = 2;
			while ( isset( $used[ $id ] ) ) {
				$id = $base . '-' . $i;
				++$i;
			}
			$used[ $id ] = true;

			if ( ! preg_match( '/\sid=(["\'])(.*?)\1/i', $attributes ) ) {
				$attributes .= ' id="' . esc_attr( $id ) . '"';
			}

			$toc[] = array(
				'level' => $level,
				'id'    => $id,
				'title' => $title,
			);

			return '<h' . $level . $attributes . '>' . $inner . '</h' . $level . '>';
		},
		$content
	);

	return array(
		'content' => $content,
		'toc'     => $toc,
	);
}

/**
 * Return the previous and next pages in sidebar order.
 *
 * @param int $current_id Current page ID.
 * @return array{previous:?WP_Post,next:?WP_Post}
 */
function docspress_get_adjacent_pages( $current_id ) {
	if ( 'custom_menu' === get_theme_mod( 'docspress_sidebar_source', 'page_tree' ) ) {
		$pages = docspress_get_custom_menu_pages();
		$ids   = wp_list_pluck( $pages, 'ID' );
		$index = array_search( $current_id, $ids, true );

		if ( false === $index ) {
			return array( 'previous' => null, 'next' => null );
		}

		return array(
			'previous' => $index > 0 ? $pages[ $index - 1 ] : null,
			'next'     => $index < count( $pages ) - 1 ? $pages[ $index + 1 ] : null,
		);
	}

	$root_id   = docspress_get_docs_root_id();
	$show_root = get_theme_mod( 'docspress_sidebar_show_root', true );
	$max_depth = absint( get_theme_mod( 'docspress_sidebar_depth', 0 ) );
	$pages     = $root_id && ! $show_root
		? docspress_flatten_page_tree( docspress_get_docs_pages(), 0, $root_id, 1, $max_depth )
		: docspress_flatten_page_tree( docspress_get_docs_pages(), $root_id, 0, 1, $max_depth );
	$ids   = wp_list_pluck( $pages, 'ID' );
	$index = array_search( $current_id, $ids, true );

	if ( false === $index ) {
		return array( 'previous' => null, 'next' => null );
	}

	return array(
		'previous' => $index > 0 ? $pages[ $index - 1 ] : null,
		'next'     => $index < count( $pages ) - 1 ? $pages[ $index + 1 ] : null,
	);
}

/**
 * Return Page objects in the configured custom sidebar menu order.
 *
 * @return WP_Post[]
 */
function docspress_get_custom_menu_pages() {
	$menu_id = absint( get_theme_mod( 'docspress_sidebar_menu', 0 ) );
	if ( ! $menu_id ) {
		$locations = get_nav_menu_locations();
		$menu_id   = isset( $locations['docs_sidebar'] ) ? absint( $locations['docs_sidebar'] ) : 0;
	}

	if ( ! $menu_id ) {
		return array();
	}

	$items     = wp_get_nav_menu_items( $menu_id );
	$max_depth = absint( get_theme_mod( 'docspress_sidebar_depth', 0 ) );
	if ( ! $items || is_wp_error( $items ) ) {
		return array();
	}

	$parents = array();
	foreach ( $items as $item ) {
		$parents[ (int) $item->ID ] = (int) $item->menu_item_parent;
	}

	$pages = array();
	foreach ( $items as $item ) {
		if ( 'page' !== $item->object ) {
			continue;
		}

		$depth  = 1;
		$parent = (int) $item->menu_item_parent;
		while ( $parent && isset( $parents[ $parent ] ) ) {
			++$depth;
			$parent = $parents[ $parent ];
		}

		if ( $max_depth && $depth > $max_depth ) {
			continue;
		}

		$page = get_post( (int) $item->object_id );
		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			$pages[] = $page;
		}
	}

	return $pages;
}

/**
 * Flatten the page hierarchy in the same depth-first order used by the sidebar.
 *
 * @param WP_Post[] $pages   Documentation pages.
 * @param int       $root_id Documentation root page.
 * @param int       $parent  Parent ID for recursion.
 * @param int       $level   Current depth.
 * @param int       $max_depth Maximum depth, or zero for unlimited.
 * @param array     $grouped Pages grouped by parent for recursion.
 * @return WP_Post[]
 */
function docspress_flatten_page_tree( $pages, $root_id = 0, $parent = 0, $level = 1, $max_depth = 0, $grouped = null ) {
	if ( $max_depth && $level > $max_depth ) {
		return array();
	}

	$flat    = array();
	$grouped = null === $grouped ? docspress_group_pages_by_parent( $pages ) : $grouped;
	if ( $root_id && 0 === $parent ) {
		$children = array_values(
			array_filter(
				$pages,
				static function ( $page ) use ( $root_id ) {
					return (int) $page->ID === $root_id;
				}
			)
		);
	} else {
		$children = isset( $grouped[ $parent ] ) ? $grouped[ $parent ] : array();
	}

	foreach ( $children as $page ) {
		$flat[] = $page;
		$flat   = array_merge( $flat, docspress_flatten_page_tree( $pages, 0, (int) $page->ID, $level + 1, $max_depth, $grouped ) );
	}

	return $flat;
}

/**
 * Get Docspress version taxonomy terms when the site has registered them.
 *
 * @return array{terms:WP_Term[],current:int}
 */
function docspress_get_versions() {
	if ( ! taxonomy_exists( 'docspress_version' ) ) {
		return array( 'terms' => array(), 'current' => 0 );
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'docspress_version',
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $terms ) ) {
		$terms = array();
	}

	$current_terms = is_page() ? wp_get_post_terms( get_queried_object_id(), 'docspress_version' ) : array();
	$current_id    = ( $current_terms && ! is_wp_error( $current_terms ) ) ? (int) $current_terms[0]->term_id : 0;

	return array( 'terms' => $terms, 'current' => $current_id );
}

/**
 * Output breadcrumb navigation for a page.
 */
function docspress_breadcrumbs() {
	if ( ! is_page() ) {
		return;
	}

	$ancestors = array_reverse( get_post_ancestors( get_queried_object_id() ) );
	if ( ! $ancestors ) {
		return;
	}

	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'docspress' ) . '"><ol>';
	foreach ( $ancestors as $ancestor_id ) {
		printf(
			'<li><a href="%1$s">%2$s</a></li>',
			esc_url( get_permalink( $ancestor_id ) ),
			esc_html( get_the_title( $ancestor_id ) )
		);
	}
	echo '<li aria-current="page">' . esc_html( get_the_title() ) . '</li></ol></nav>';
}

/**
 * Use a useful excerpt for documentation pages.
 *
 * @return string
 */
function docspress_page_summary() {
	if ( has_excerpt() ) {
		return get_the_excerpt();
	}

	return '';
}

/**
 * Read DocsPress's hidden management metadata for a Page.
 *
 * @param int $post_id Page ID.
 * @return array<string,mixed>
 */
function docspress_get_managed_metadata( $post_id = 0 ) {
	static $metadata_cache = array();

	$post_id = $post_id ? absint( $post_id ) : get_queried_object_id();
	if ( ! $post_id ) {
		return array();
	}

	if ( isset( $metadata_cache[ $post_id ] ) ) {
		return $metadata_cache[ $post_id ];
	}

	$content  = (string) get_post_field( 'post_content', $post_id, 'raw' );
	$metadata = array();
	if ( $content && preg_match( '/<!--\s*docspress:(.*?)\s*-->/s', $content, $matches ) ) {
		$parsed = json_decode( trim( $matches[1] ), true );
		if ( is_array( $parsed ) && 1 === (int) ( isset( $parsed['version'] ) ? $parsed['version'] : 0 ) ) {
			$metadata = $parsed;
		}
	}

	$metadata_cache[ $post_id ] = $metadata;
	return $metadata_cache[ $post_id ];
}

/**
 * Return a Page's explicit initial sidebar state.
 *
 * @param int $post_id Page ID.
 * @return bool|null
 */
function docspress_get_sidebar_collapsed( $post_id = 0 ) {
	$metadata = docspress_get_managed_metadata( $post_id );
	if ( ! array_key_exists( 'sidebarCollapsed', $metadata ) || ! is_bool( $metadata['sidebarCollapsed'] ) ) {
		return null;
	}

	return $metadata['sidebarCollapsed'];
}

/**
 * Add managed collapse metadata to Page links in the custom docs menu.
 *
 * @param array    $attributes Menu link attributes.
 * @param WP_Post  $item       Menu item.
 * @param stdClass $args       Menu arguments.
 * @return array
 */
function docspress_sidebar_menu_link_attributes( $attributes, $item, $args ) {
	if ( empty( $args->theme_location ) || 'docs_sidebar' !== $args->theme_location || 'page' !== $item->object ) {
		return $attributes;
	}

	$collapsed = docspress_get_sidebar_collapsed( (int) $item->object_id );
	if ( null !== $collapsed ) {
		$attributes['data-sidebar-collapsed'] = $collapsed ? 'true' : 'false';
	}

	return $attributes;
}
add_filter( 'nav_menu_link_attributes', 'docspress_sidebar_menu_link_attributes', 10, 3 );

/**
 * Normalize a file-backed Docspress source path.
 *
 * @param mixed $source Untrusted source path.
 * @return string
 */
function docspress_normalize_markdown_source_path( $source ) {
	if ( ! is_string( $source ) ) {
		return '';
	}

	$source = trim( str_replace( '\\', '/', wp_strip_all_tags( $source ) ) );
	if ( '' === $source || '/' === $source[0] || false !== strpos( $source, ':' ) || false !== strpos( $source, "\0" ) ) {
		return '';
	}

	$segments = explode( '/', $source );
	foreach ( $segments as $segment ) {
		if ( '' === $segment || '.' === $segment || '..' === $segment ) {
			return '';
		}
	}

	if ( ! preg_match( '/\.(?:md|markdown|mdx)$/i', $source ) ) {
		return '';
	}

	return implode( '/', $segments );
}

/**
 * Read the exact Markdown path stored by Docspress.
 *
 * Managed Pages keep this in their hidden sentinel. The post meta fallback is
 * useful for hand-authored Pages and local demos.
 *
 * @param int $post_id Page ID.
 * @return string
 */
function docspress_get_markdown_source_path( $post_id = 0 ) {
	$post_id  = $post_id ? absint( $post_id ) : get_queried_object_id();
	$metadata = docspress_get_managed_metadata( $post_id );
	$source   = isset( $metadata['source'] ) ? $metadata['source'] : '';

	if ( ! $source && $post_id ) {
		$source = get_post_meta( $post_id, '_docspress_source_path', true );
	}

	/**
	 * Filter the Markdown source path before validation.
	 *
	 * @param mixed $source   Source path from the sentinel or post meta.
	 * @param int   $post_id  Page ID.
	 * @param array $metadata Parsed sentinel metadata, when available.
	 */
	$source = apply_filters( 'docspress_markdown_source_path', $source, $post_id, $metadata );

	return docspress_normalize_markdown_source_path( $source );
}

/**
 * Build a GitHub edit URL for the current Page's exact Markdown source.
 *
 * @param int $post_id Page ID.
 * @return string
 */
function docspress_get_github_edit_url( $post_id = 0 ) {
	$source = docspress_get_markdown_source_path( $post_id );
	if ( ! $source ) {
		return '';
	}

	$repository = get_theme_mod( 'docspress_github_edit_repository_url', '' );
	$repository = $repository ? $repository : get_theme_mod( 'docspress_github_url', 'https://github.com/Automattic/docspress' );
	$repository = untrailingslashit( esc_url_raw( $repository ) );
	$repository = preg_replace( '/\.git$/i', '', $repository );
	$repository_parts = wp_parse_url( $repository );
	if (
		! $repository ||
		! is_array( $repository_parts ) ||
		empty( $repository_parts['scheme'] ) ||
		empty( $repository_parts['host'] ) ||
		! in_array( strtolower( $repository_parts['scheme'] ), array( 'http', 'https' ), true ) ||
		isset( $repository_parts['user'] ) ||
		isset( $repository_parts['pass'] ) ||
		isset( $repository_parts['query'] ) ||
		isset( $repository_parts['fragment'] )
	) {
		return '';
	}

	$ref = trim( (string) get_theme_mod( 'docspress_github_edit_ref', 'main' ) );
	if ( ! preg_match( '#^[A-Za-z0-9._/-]+$#', $ref ) || false !== strpos( $ref, '..' ) ) {
		$ref = 'main';
	}

	$encoded_source = implode( '/', array_map( 'rawurlencode', explode( '/', $source ) ) );

	return $repository . '/edit/' . rawurlencode( $ref ) . '/' . $encoded_source;
}
