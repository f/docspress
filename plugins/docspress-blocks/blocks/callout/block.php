<?php
/**
 * Callout block registration and rendering.
 *
 * @package DocsPressBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the semantic icon used by a callout tone.
 *
 * @param string $tone Callout tone.
 * @return string
 */
function docspress_blocks_callout_icon( $tone ) {
	$icons = array(
		'note'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01"/></svg>',
		'tip'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18h6m-5 3h4M8.5 14.5A7 7 0 1 1 15.5 14.5c-1 1-1.5 1.7-1.5 3h-4c0-1.3-.5-2-1.5-3Z"/></svg>',
		'warning' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m12 4 9 16H3L12 4Z"/><path d="M12 9v5m0 3h.01"/></svg>',
		'danger'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 3h8l5 5v8l-5 5H8l-5-5V8l5-5Z"/><path d="M12 8v5m0 3h.01"/></svg>',
		'success' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>',
	);

	return $icons[ $tone ];
}

/**
 * Render the Callout block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function docspress_blocks_render_callout( $attributes ) {
	$tone        = docspress_blocks_allowed_value( isset( $attributes['tone'] ) ? $attributes['tone'] : '', array( 'note', 'tip', 'warning', 'danger', 'success' ), 'note' );
	$title       = isset( $attributes['title'] ) ? wp_kses_post( $attributes['title'] ) : '';
	$content     = isset( $attributes['content'] ) ? wp_kses_post( $attributes['content'] ) : '';
	$collapsible = ! empty( $attributes['collapsible'] );
	$open        = ! isset( $attributes['open'] ) || (bool) $attributes['open'];
	$wrapper     = get_block_wrapper_attributes( array( 'class' => 'docspress-callout docspress-callout--' . $tone ) );
	$icon        = '<span class="docspress-callout__icon" aria-hidden="true">' . docspress_blocks_callout_icon( $tone ) . '</span>';

	if ( $collapsible ) {
		return sprintf(
			'<details %1$s%2$s><summary>%3$s<span class="docspress-callout__title">%4$s</span><span class="docspress-callout__chevron" aria-hidden="true">⌄</span></summary><div class="docspress-callout__content">%5$s</div></details>',
			$wrapper,
			$open ? ' open' : '',
			$icon,
			$title,
			$content
		);
	}

	return sprintf(
		'<aside %1$s>%2$s<div><div class="docspress-callout__title">%3$s</div><div class="docspress-callout__content">%4$s</div></div></aside>',
		$wrapper,
		$icon,
		$title,
		$content
	);
}

/**
 * Register the Callout block and its folder-owned assets.
 */
function docspress_blocks_register_callout() {
	$block_url = DOCSPRESS_BLOCKS_URL . 'blocks/callout/';

	wp_register_script(
		'docspress-callout-editor',
		$block_url . 'editor.js',
		array( 'wp-blocks', 'docspress-blocks-editor-shared' ),
		DOCSPRESS_BLOCKS_VERSION,
		true
	);

	wp_register_style(
		'docspress-callout',
		$block_url . 'style.css',
		array(),
		DOCSPRESS_BLOCKS_VERSION
	);

	wp_register_style(
		'docspress-callout-editor-style',
		$block_url . 'editor.css',
		array( 'wp-edit-blocks', 'docspress-callout' ),
		DOCSPRESS_BLOCKS_VERSION
	);

	register_block_type(
		'docspress/callout',
		array(
			'api_version'     => 3,
			'editor_script'   => 'docspress-callout-editor',
			'style'           => 'docspress-callout',
			'editor_style'    => 'docspress-callout-editor-style',
			'render_callback' => 'docspress_blocks_render_callout',
			'attributes'      => array(
				'tone'        => array( 'type' => 'string', 'default' => 'note' ),
				'title'       => array( 'type' => 'string', 'default' => 'Good to know' ),
				'content'     => array( 'type' => 'string', 'default' => '<p>Add the detail readers need at exactly the right moment.</p>' ),
				'collapsible' => array( 'type' => 'boolean', 'default' => false ),
				'open'        => array( 'type' => 'boolean', 'default' => true ),
			),
			'supports'        => array( 'anchor' => true, 'html' => false ),
		)
	);
}
add_action( 'init', 'docspress_blocks_register_callout', 10 );
