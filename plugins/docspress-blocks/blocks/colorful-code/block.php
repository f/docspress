<?php
/**
 * Colorful Code block registration and rendering.
 *
 * @package DocsPressBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Colorful Code block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function docspress_blocks_render_colorful_code( $attributes ) {
	wp_enqueue_script( 'docspress-blocks-view' );

	$caption = isset( $attributes['caption'] ) ? wp_kses_post( $attributes['caption'] ) : '';

	ob_start();
	?>
	<figure <?php echo get_block_wrapper_attributes( array( 'class' => 'docspress-code' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php echo docspress_blocks_code_surface( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php if ( $caption ) : ?>
			<figcaption class="docspress-code__caption"><?php echo $caption; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figcaption>
		<?php endif; ?>
	</figure>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Register the Colorful Code block and its folder-owned assets.
 */
function docspress_blocks_register_colorful_code() {
	$block_url = DOCSPRESS_BLOCKS_URL . 'blocks/colorful-code/';

	wp_register_script(
		'docspress-colorful-code-editor',
		$block_url . 'editor.js',
		array( 'wp-blocks', 'docspress-blocks-editor-shared' ),
		DOCSPRESS_BLOCKS_VERSION,
		true
	);

	wp_register_style(
		'docspress-colorful-code',
		$block_url . 'style.css',
		array( 'docspress-blocks-code' ),
		DOCSPRESS_BLOCKS_VERSION
	);

	wp_register_style(
		'docspress-colorful-code-editor-style',
		$block_url . 'editor.css',
		array( 'docspress-blocks-code-editor', 'docspress-colorful-code' ),
		DOCSPRESS_BLOCKS_VERSION
	);

	register_block_type(
		'docspress/colorful-code',
		array(
			'api_version'     => 3,
			'editor_script'   => 'docspress-colorful-code-editor',
			'style'           => 'docspress-colorful-code',
			'editor_style'    => 'docspress-colorful-code-editor-style',
			'render_callback' => 'docspress_blocks_render_colorful_code',
			'attributes'      => array(
				'language'          => array( 'type' => 'string', 'default' => 'javascript' ),
				'filename'          => array( 'type' => 'string', 'default' => '' ),
				'code'              => array( 'type' => 'string', 'default' => "const hello = \"DocsPress\";\nconsole.log( hello );" ),
				'highlightedLines'  => array( 'type' => 'string', 'default' => '' ),
				'showLineNumbers'   => array( 'type' => 'boolean', 'default' => true ),
				'caption'           => array( 'type' => 'string', 'default' => '' ),
			),
			'supports'        => array( 'anchor' => true, 'html' => false ),
		)
	);
}
add_action( 'init', 'docspress_blocks_register_colorful_code', 10 );
