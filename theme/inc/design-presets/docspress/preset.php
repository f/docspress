<?php
/**
 * DocsPress design preset.
 *
 * @package DocsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'label'       => __( 'DocsPress', 'docspress' ),
	'description' => __( 'Logo-matched blue, Wapuu yellow, deep ink, and a friendly rounded interface.', 'docspress' ),
	'order'       => 10,
	'values'      => array(
		'docspress_ui_font'             => 'rounded',
		'docspress_content_font'        => 'sans',
		'docspress_heading_font'        => 'interface',
		'docspress_content_font_size'   => 17,
		'docspress_heading_weight'      => 900,
		'docspress_border_radius'       => 14,
		'docspress_accent_color'        => '#005cb3',
		'docspress_accent_strong'       => '#004a91',
		'docspress_accent_soft'         => '#e9f4ff',
		'docspress_paper_color'         => '#ffffff',
		'docspress_canvas_color'        => '#f5faff',
		'docspress_ink_color'           => '#232323',
		'docspress_copy_color'          => '#3d4348',
		'docspress_muted_color'         => '#6e7479',
		'docspress_line_color'          => '#d8e5ee',
		'docspress_line_strong_color'   => '#a9c1d2',
		'docspress_dark_accent'         => '#62b5ff',
		'docspress_dark_strong'         => '#93ccff',
		'docspress_dark_soft'           => '#123b5d',
		'docspress_dark_paper'          => '#15191d',
		'docspress_dark_canvas'         => '#0c1217',
		'docspress_dark_ink'            => '#fefefe',
		'docspress_dark_copy'           => '#dce6ed',
		'docspress_dark_muted'          => '#a7b4bd',
		'docspress_dark_line'           => '#293842',
		'docspress_dark_line_strong'    => '#455966',
	),
);
