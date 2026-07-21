<?php
/**
 * WordPress.org design preset.
 *
 * @package DocsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'label'       => __( 'WordPress.org', 'docspress' ),
	'description' => __( 'WordPress blue, Inter body type, EB Garamond headings, and crisp corners.', 'docspress' ),
	'order'       => 20,
	'values'      => array(
		'docspress_ui_font'             => 'inter',
		'docspress_content_font'        => 'sans',
		'docspress_heading_font'        => 'eb_garamond',
		'docspress_content_font_size'   => 17,
		'docspress_heading_weight'      => 400,
		'docspress_border_radius'       => 2,
		'docspress_accent_color'        => '#3858e9',
		'docspress_accent_strong'       => '#2145e6',
		'docspress_accent_soft'         => '#eff2ff',
		'docspress_paper_color'         => '#ffffff',
		'docspress_canvas_color'        => '#f6f6f6',
		'docspress_ink_color'           => '#1e1e1e',
		'docspress_copy_color'          => '#40464d',
		'docspress_muted_color'         => '#656a71',
		'docspress_line_color'          => '#d9d9d9',
		'docspress_line_strong_color'   => '#b8bcc1',
		'docspress_dark_accent'         => '#7b90ff',
		'docspress_dark_strong'         => '#a8b5ff',
		'docspress_dark_soft'           => '#29336b',
		'docspress_dark_paper'          => '#1e1e1e',
		'docspress_dark_canvas'         => '#151515',
		'docspress_dark_ink'            => '#f6f6f6',
		'docspress_dark_copy'           => '#d9d9d9',
		'docspress_dark_muted'          => '#a7aaae',
		'docspress_dark_line'           => '#3c4045',
		'docspress_dark_line_strong'    => '#565b61',
	),
);
