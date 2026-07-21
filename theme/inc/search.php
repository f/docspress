<?php
/**
 * Client-side documentation search index.
 *
 * @package DocsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return searchable Pages in the same scope as the documentation navigation.
 *
 * @return WP_Post[]
 */
function docspress_search_pages() {
	$pages = 'custom_menu' === get_theme_mod( 'docspress_sidebar_source', 'page_tree' )
		? docspress_get_custom_menu_pages()
		: docspress_get_docs_pages();
	$seen  = array();

	return array_values(
		array_filter(
			$pages,
			static function ( $page ) use ( &$seen ) {
				if ( ! $page instanceof WP_Post || isset( $seen[ $page->ID ] ) ) {
					return false;
				}

				$seen[ $page->ID ] = true;
				return true;
			}
		)
	);
}

/**
 * Convert raw Page content into compact searchable text.
 *
 * @param string $content Raw post content.
 * @return string
 */
function docspress_searchable_text( $content ) {
	$content = preg_replace( '/<!--.*?-->/s', ' ', (string) $content );
	$content = strip_shortcodes( $content );
	$content = html_entity_decode( wp_strip_all_tags( $content, true ), ENT_QUOTES, get_bloginfo( 'charset' ) );
	$content = preg_replace( '/\s+/u', ' ', $content );
	$content = trim( (string) $content );

	return function_exists( 'mb_substr' ) ? mb_substr( $content, 0, 12000 ) : substr( $content, 0, 12000 );
}

/**
 * Build the hierarchy label displayed above a search result.
 *
 * @param WP_Post $page Documentation Page.
 * @return string
 */
function docspress_search_page_path( $page ) {
	$labels = array();

	foreach ( array_reverse( get_post_ancestors( $page ) ) as $ancestor_id ) {
		$title = wp_strip_all_tags( get_the_title( $ancestor_id ) );
		if ( $title ) {
			$labels[] = $title;
		}
	}

	return implode( ' / ', $labels );
}

/**
 * Build the public search index passed to the theme script.
 *
 * @return array<int,array<string,mixed>>
 */
function docspress_search_index() {
	$index = array();

	foreach ( docspress_search_pages() as $page ) {
		$content = docspress_searchable_text( $page->post_content );
		$excerpt = $page->post_excerpt
			? wp_strip_all_tags( $page->post_excerpt )
			: wp_trim_words( $content, 24, '…' );

		$index[] = array(
			'id'      => (int) $page->ID,
			'title'   => html_entity_decode( wp_strip_all_tags( get_the_title( $page ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'path'    => docspress_search_page_path( $page ),
			'excerpt' => html_entity_decode( $excerpt, ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'content' => $content,
			'url'     => get_permalink( $page ),
		);
	}

	/**
	 * Filter the browser-search index before it is encoded for the front end.
	 *
	 * @param array<int,array<string,mixed>> $index Search records.
	 */
	return apply_filters( 'docspress_search_index', $index );
}

/**
 * Attach the search index and translated interface strings to docs.js.
 */
function docspress_search_assets() {
	if ( ! get_theme_mod( 'docspress_show_header_search', true ) ) {
		return;
	}

	wp_localize_script(
		'docspress-navigation',
		'docspressSearchData',
		array(
			'index'          => docspress_search_index(),
			'limit'          => min( 20, max( 3, absint( get_theme_mod( 'docspress_search_results_limit', 8 ) ) ) ),
			'suggestedLabel' => get_theme_mod( 'docspress_search_suggested_label', __( 'Suggested pages', 'docspress' ) ),
			'resultsLabel'   => __( 'Search results', 'docspress' ),
			'noResultsLabel' => get_theme_mod( 'docspress_search_no_results_label', __( 'No documentation matched that search.', 'docspress' ) ),
			'resultSingular' => __( '1 result', 'docspress' ),
			'resultPlural'   => __( '%d results', 'docspress' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'docspress_search_assets', 15 );
