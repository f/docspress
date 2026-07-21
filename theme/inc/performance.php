<?php
/**
 * Small, conservative front-end performance improvements.
 *
 * @package DocsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove discovery markup and legacy assets that documentation pages do not use.
 */
function docspress_performance_setup() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'docspress_performance_setup' );

/**
 * Remove the legacy embed runtime and mark the theme interaction script deferred.
 */
function docspress_performance_assets() {
	wp_dequeue_script( 'wp-embed' );
	wp_deregister_script( 'wp-embed' );
	wp_script_add_data( 'docspress-navigation', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'docspress_performance_assets', 100 );

// Let WordPress enqueue block styles only when their blocks are rendered.
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

/**
 * Avoid an unnecessary SVG emoji CDN resource hint.
 *
 * @return false
 */
function docspress_disable_emoji_svg_url() {
	return false;
}
add_filter( 'emoji_svg_url', 'docspress_disable_emoji_svg_url' );

/**
 * Make the custom logo eager and high priority because it is visible immediately.
 *
 * @param array<string,string> $attributes Image attributes.
 * @param WP_Post              $attachment Attachment object.
 * @param string|int[]         $size       Image size.
 * @return array<string,string>
 */
function docspress_logo_loading_attributes( $attributes, $attachment, $size ) {
	$logo_id = absint( get_theme_mod( 'custom_logo' ) );
	if ( $logo_id && (int) $attachment->ID === $logo_id ) {
		$attributes['loading']       = 'eager';
		$attributes['fetchpriority'] = 'high';
		$attributes['decoding']      = 'async';
	}

	return $attributes;
}
add_filter( 'wp_get_attachment_image_attributes', 'docspress_logo_loading_attributes', 10, 3 );
