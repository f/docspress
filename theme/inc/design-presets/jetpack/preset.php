<?php
/**
 * Jetpack design preset.
 *
 * @package DocsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'label'       => __( 'Jetpack', 'docspress' ),
	'description' => __( 'Jetpack green, bright neutral surfaces, Inter body type, and Recoleta headings.', 'docspress' ),
	'order'       => 40,
	'values'      => array(
		'docspress_ui_font'             => 'inter',
		'docspress_content_font'        => 'sans',
		'docspress_heading_font'        => 'recoleta',
		'docspress_content_font_size'   => 17,
		'docspress_heading_weight'      => 400,
		'docspress_border_radius'       => 4,
		'docspress_accent_color'        => '#069e08',
		'docspress_accent_strong'       => '#007117',
		'docspress_accent_soft'         => '#f0f2eb',
		'docspress_paper_color'         => '#ffffff',
		'docspress_canvas_color'        => '#f6f7f7',
		'docspress_ink_color'           => '#101517',
		'docspress_copy_color'          => '#3c434a',
		'docspress_muted_color'         => '#646970',
		'docspress_line_color'          => '#dcdcde',
		'docspress_line_strong_color'   => '#a7aaad',
		'docspress_dark_accent'         => '#61ce4f',
		'docspress_dark_strong'         => '#91e982',
		'docspress_dark_soft'           => '#183b20',
		'docspress_dark_paper'          => '#101517',
		'docspress_dark_canvas'         => '#090c0d',
		'docspress_dark_ink'            => '#f6f7f7',
		'docspress_dark_copy'           => '#d7dade',
		'docspress_dark_muted'          => '#9ca3a8',
		'docspress_dark_line'           => '#2d3438',
		'docspress_dark_line_strong'    => '#465057',
	),
);
