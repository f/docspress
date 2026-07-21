<?php
/**
 * WordPress.com design preset.
 *
 * @package DocsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'label'       => __( 'WordPress.com', 'docspress' ),
	'description' => __( 'WordPress.com blue, Studio neutrals, Inter body type, and Recoleta headings.', 'docspress' ),
	'order'       => 30,
	'values'      => array(
		'docspress_ui_font'             => 'inter',
		'docspress_content_font'        => 'sans',
		'docspress_heading_font'        => 'recoleta',
		'docspress_content_font_size'   => 17,
		'docspress_heading_weight'      => 400,
		'docspress_border_radius'       => 4,
		'docspress_accent_color'        => '#3858e9',
		'docspress_accent_strong'       => '#2a46ce',
		'docspress_accent_soft'         => '#f7f8fe',
		'docspress_paper_color'         => '#ffffff',
		'docspress_canvas_color'        => '#f6f7f7',
		'docspress_ink_color'           => '#101517',
		'docspress_copy_color'          => '#3c434a',
		'docspress_muted_color'         => '#646970',
		'docspress_line_color'          => '#dcdcde',
		'docspress_line_strong_color'   => '#a7aaad',
		'docspress_dark_accent'         => '#7b90ff',
		'docspress_dark_strong'         => '#adbaf3',
		'docspress_dark_soft'           => '#14215a',
		'docspress_dark_paper'          => '#101517',
		'docspress_dark_canvas'         => '#090c0d',
		'docspress_dark_ink'            => '#f6f7f7',
		'docspress_dark_copy'           => '#dcdcde',
		'docspress_dark_muted'          => '#a7aaad',
		'docspress_dark_line'           => '#2c3338',
		'docspress_dark_line_strong'    => '#50575e',
	),
);
