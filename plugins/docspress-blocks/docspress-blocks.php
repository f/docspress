<?php
/**
 * Plugin Name:       DocsPress Blocks
 * Plugin URI:        https://github.com/Automattic/docspress/tree/main/plugins/docspress-blocks
 * Description:       Documentation-focused Gutenberg blocks for code, API exchanges, terminal sessions, results, file trees, and semantic callouts.
 * Version:           0.2.1
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

define( 'DOCSPRESS_BLOCKS_VERSION', '0.2.1' );
define( 'DOCSPRESS_BLOCKS_FILE', __FILE__ );
define( 'DOCSPRESS_BLOCKS_PATH', plugin_dir_path( __FILE__ ) );
define( 'DOCSPRESS_BLOCKS_URL', plugin_dir_url( __FILE__ ) );

require_once DOCSPRESS_BLOCKS_PATH . 'includes/code-surface.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/colorful-code/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/code-tabs/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/callout/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/api-request/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/terminal-session/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/result/block.php';
require_once DOCSPRESS_BLOCKS_PATH . 'blocks/file-tree/block.php';
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

	$theme          = wp_get_theme();
	$default_preset = 'DocsPress' === $theme->get( 'Name' ) ? 'docspress' : 'custom';
	$design_preset  = sanitize_key( (string) get_theme_mod( 'docspress_design_preset', $default_preset ) );
	wp_add_inline_script(
		'docspress-blocks-editor-shared',
		'window.docspressBlocksSettings = ' . wp_json_encode( array( 'preset' => $design_preset ? $design_preset : 'custom' ) ) . ';',
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
