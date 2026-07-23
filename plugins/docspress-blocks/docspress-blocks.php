<?php
/**
 * Plugin Name:       DocsPress Blocks
 * Plugin URI:        https://github.com/Automattic/docspress/tree/main/plugins/docspress-blocks
 * Description:       Documentation-focused Gutenberg blocks for homepages, audience routing, code, prompts, API exchanges, terminal sessions, results, file trees, and semantic callouts.
 * Version:           0.6.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Automattic
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       docspress-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DOCSPRESS_BLOCKS_VERSION', '0.6.0' );
define( 'DOCSPRESS_BLOCKS_FILE', __FILE__ );
define( 'DOCSPRESS_BLOCKS_PATH', plugin_dir_path( __FILE__ ) );
define( 'DOCSPRESS_BLOCKS_URL', plugin_dir_url( __FILE__ ) );

require_once DOCSPRESS_BLOCKS_PATH . 'includes/code-surface.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/hero/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/audience-paths/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/colorful-code/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/code-tabs/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/callout/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/api-request/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/terminal-session/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/result/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/file-tree/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/prompt/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'includes/patterns.php';

/**
 * Register the small shared layer used by multiple block folders.
 */
function docspress_blocks_register_shared_assets() {
	$asset_url = DOCSPRESS_BLOCKS_URL . 'assets/';

	wp_register_script(
		'docspress-blocks-editor-shared',
		$asset_url . 'editor-shared.js',
		array( 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n' ),
		DOCSPRESS_BLOCKS_VERSION,
		true
	);

	$theme_is_docspress = function_exists( 'docspress_get_design_setting' ) && function_exists( 'docspress_font_stacks' );
	$default_preset     = $theme_is_docspress ? 'docspress' : 'custom';
	$design_preset      = sanitize_key( (string) get_theme_mod( 'docspress_design_preset', $default_preset ) );
	$editor_tokens      = array();

	if ( $theme_is_docspress ) {
		$fonts        = docspress_font_stacks();
		$ui_key       = docspress_get_design_setting( 'docspress_ui_font', 'nunito' );
		$content_key  = docspress_get_design_setting( 'docspress_content_font', 'sans' );
		$heading_key  = docspress_get_design_setting( 'docspress_heading_font', 'interface' );
		$ui_font      = isset( $fonts[ $ui_key ] ) ? $fonts[ $ui_key ] : $fonts['nunito'];
		$content_font = 'sans' === $content_key ? $ui_font : ( isset( $fonts[ $content_key ] ) ? $fonts[ $content_key ] : $fonts['charter'] );
		$heading_font = 'interface' === $heading_key ? $ui_font : ( isset( $fonts[ $heading_key ] ) ? $fonts[ $heading_key ] : $ui_font );
		$color_tokens = array(
			'--dp-blue'        => array( 'docspress_accent_color', '#3858e9' ),
			'--dp-blue-dark'   => array( 'docspress_accent_strong', '#2145d8' ),
			'--dp-blue-soft'   => array( 'docspress_accent_soft', '#eef1ff' ),
			'--dp-paper'       => array( 'docspress_paper_color', '#ffffff' ),
			'--dp-canvas'      => array( 'docspress_canvas_color', '#f8f9fb' ),
			'--dp-ink'         => array( 'docspress_ink_color', '#171a22' ),
			'--dp-copy'        => array( 'docspress_copy_color', '#3d4351' ),
			'--dp-muted'       => array( 'docspress_muted_color', '#6f7685' ),
			'--dp-line'        => array( 'docspress_line_color', '#e4e7ec' ),
			'--dp-line-strong' => array( 'docspress_line_strong_color', '#cfd4dc' ),
		);

		$editor_tokens = array(
			'--dp-radius'         => absint( docspress_get_design_setting( 'docspress_border_radius', 10 ) ) . 'px',
			'--dp-heading-weight' => (string) absint( docspress_get_design_setting( 'docspress_heading_weight', 750 ) ),
			'--dp-font-ui'        => $ui_font,
			'--dp-font-copy'      => $content_font,
			'--dp-font-heading'   => $heading_font,
		);

		foreach ( $color_tokens as $variable => $color ) {
			$value                      = sanitize_hex_color( docspress_get_design_setting( $color[0], $color[1] ) );
			$editor_tokens[ $variable ] = $value ? $value : $color[1];
		}
	}

	wp_add_inline_script(
		'docspress-blocks-editor-shared',
		'window.docspressBlocksSettings = ' . wp_json_encode(
			array(
				'preset' => $design_preset ? $design_preset : 'custom',
				'tokens' => $editor_tokens,
			)
		) . ';',
		'before'
	);

	wp_register_script(
		'docspress-blocks-view',
		$asset_url . 'view.js',
		array(),
		DOCSPRESS_BLOCKS_VERSION,
		true
	);

	wp_register_style(
		'docspress-blocks-code',
		$asset_url . 'code.css',
		array(),
		DOCSPRESS_BLOCKS_VERSION
	);

	wp_register_style(
		'docspress-blocks-code-editor',
		$asset_url . 'code-editor.css',
		array( 'wp-edit-blocks', 'docspress-blocks-code' ),
		DOCSPRESS_BLOCKS_VERSION
	);
}
add_action( 'init', 'docspress_blocks_register_shared_assets', 5 );
