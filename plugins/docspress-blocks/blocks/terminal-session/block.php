<?php
/**
 * Terminal Session block registration and rendering.
 *
 * @package DocsPressBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Terminal Session block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function docspress_blocks_render_terminal_session( $attributes ) {
	wp_enqueue_script( 'docspress-blocks-view' );

	$title      = isset( $attributes['title'] ) ? sanitize_text_field( $attributes['title'] ) : 'Terminal';
	$shell      = isset( $attributes['shell'] ) ? sanitize_text_field( $attributes['shell'] ) : 'bash';
	$prompt     = isset( $attributes['prompt'] ) ? sanitize_text_field( $attributes['prompt'] ) : '$';
	$command    = isset( $attributes['command'] ) ? (string) $attributes['command'] : '';
	$output     = isset( $attributes['output'] ) ? (string) $attributes['output'] : '';
	$command_id = wp_unique_id( 'docspress-terminal-command-' );

	ob_start();
	?>
	<figure <?php echo get_block_wrapper_attributes( array( 'class' => 'docspress-terminal' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="docspress-terminal__bar">
			<span class="docspress-terminal__icon" aria-hidden="true">›_</span>
			<span class="docspress-terminal__title"><?php echo esc_html( $title ); ?></span>
			<span class="docspress-terminal__shell"><?php echo esc_html( $shell ); ?></span>
			<button class="docspress-terminal__copy" type="button" data-docspress-copy data-docspress-copy-target="<?php echo esc_attr( $command_id ); ?>" aria-label="<?php esc_attr_e( 'Copy command', 'docspress-blocks' ); ?>">
				<svg viewBox="0 0 20 20" aria-hidden="true"><rect x="7" y="7" width="9" height="9" rx="1.5"/><path d="M13 7V5.5A1.5 1.5 0 0 0 11.5 4h-7A1.5 1.5 0 0 0 3 5.5v7A1.5 1.5 0 0 0 4.5 14H7"/></svg>
				<b><?php esc_html_e( 'Copy', 'docspress-blocks' ); ?></b>
			</button>
		</div>
		<div class="docspress-terminal__body">
			<div class="docspress-terminal__command"><span class="docspress-terminal__prompt" aria-hidden="true"><?php echo esc_html( $prompt ); ?></span><pre><code id="<?php echo esc_attr( $command_id ); ?>"><?php echo esc_html( $command ); ?></code></pre></div>
			<?php if ( '' !== trim( $output ) ) : ?>
				<pre class="docspress-terminal__output" aria-label="<?php esc_attr_e( 'Command output', 'docspress-blocks' ); ?>"><code><?php echo esc_html( $output ); ?></code></pre>
			<?php endif; ?>
		</div>
	</figure>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Register the Terminal Session block and its folder-owned assets.
 */
function docspress_blocks_register_terminal_session() {
	$block_url = DOCSPRESS_BLOCKS_URL . 'blocks/terminal-session/';

	wp_register_script( 'docspress-terminal-session-editor', $block_url . 'editor.js', array( 'wp-blocks', 'docspress-blocks-editor-shared' ), DOCSPRESS_BLOCKS_VERSION, true );
	wp_register_style( 'docspress-terminal-session', $block_url . 'style.css', array(), DOCSPRESS_BLOCKS_VERSION );
	wp_register_style( 'docspress-terminal-session-editor-style', $block_url . 'editor.css', array( 'wp-edit-blocks', 'docspress-terminal-session' ), DOCSPRESS_BLOCKS_VERSION );

	register_block_type(
		'docspress/terminal-session',
		array(
			'api_version'     => 3,
			'editor_script'   => 'docspress-terminal-session-editor',
			'style'           => 'docspress-terminal-session',
			'editor_style'    => 'docspress-terminal-session-editor-style',
			'render_callback' => 'docspress_blocks_render_terminal_session',
			'attributes'      => array(
				'title'   => array( 'type' => 'string', 'default' => 'Terminal' ),
				'shell'   => array( 'type' => 'string', 'default' => 'bash' ),
				'prompt'  => array( 'type' => 'string', 'default' => '$' ),
				'command' => array( 'type' => 'string', 'default' => 'npx docspress publish ./docs' ),
				'output'  => array( 'type' => 'string', 'default' => "✓ Read 12 documents\n✓ Published 12 WordPress pages" ),
			),
			'supports'        => array( 'anchor' => true, 'html' => false ),
		)
	);
}
add_action( 'init', 'docspress_blocks_register_terminal_session', 10 );
