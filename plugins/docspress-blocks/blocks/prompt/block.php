<?php
/**
 * Prompt block registration and rendering.
 *
 * @package DocsPressBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classify a prompt context item for presentation.
 *
 * @param string $item Raw context item.
 * @return string
 */
function docspress_blocks_prompt_context_kind( $item ) {
	if ( '@' === substr( $item, 0, 1 ) ) {
		return 'mention';
	}
	if ( '#' === substr( $item, 0, 1 ) ) {
		return 'image';
	}
	if ( preg_match( '#^https?://#i', $item ) ) {
		return 'url';
	}

	return 'file';
}

/**
 * Parse comma-separated prompt context.
 *
 * @param string $context Raw context value.
 * @return array
 */
function docspress_blocks_prompt_context_items( $context ) {
	$items = array();
	foreach ( explode( ',', (string) $context ) as $item ) {
		$item = trim( sanitize_text_field( $item ) );
		if ( '' === $item ) {
			continue;
		}
		$items[] = array(
			'label' => $item,
			'kind'  => docspress_blocks_prompt_context_kind( $item ),
		);
	}

	return array_slice( $items, 0, 12 );
}

/**
 * Render the Prompt block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function docspress_blocks_render_prompt( $attributes ) {
	wp_enqueue_script( 'docspress-blocks-view' );

	$prompt      = isset( $attributes['prompt'] ) ? (string) $attributes['prompt'] : '';
	$model       = isset( $attributes['model'] ) ? sanitize_text_field( $attributes['model'] ) : 'GPT-5';
	$mode        = docspress_blocks_allowed_value( isset( $attributes['mode'] ) ? $attributes['mode'] : '', array( 'chat', 'code', 'ask', 'plan' ), 'chat' );
	$thinking    = ! empty( $attributes['thinking'] );
	$context     = isset( $attributes['context'] ) ? docspress_blocks_prompt_context_items( $attributes['context'] ) : array();
	$caption     = isset( $attributes['caption'] ) ? wp_kses_post( $attributes['caption'] ) : __( 'Prompt example', 'docspress-blocks' );
	$prompt_id   = wp_unique_id( 'docspress-prompt-text-' );
	$mode_labels = array(
		'chat' => __( 'Chat', 'docspress-blocks' ),
		'code' => __( 'Code', 'docspress-blocks' ),
		'ask'  => __( 'Ask', 'docspress-blocks' ),
		'plan' => __( 'Plan', 'docspress-blocks' ),
	);

	ob_start();
	?>
	<section <?php echo get_block_wrapper_attributes( array( 'class' => 'docspress-prompt', 'data-mode' => $mode, 'aria-label' => __( 'AI prompt example', 'docspress-blocks' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<header class="docspress-prompt__header">
			<span class="docspress-prompt__mark" aria-hidden="true"><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 2.5 11.4 7l4.1 1.4-4.1 1.4L10 14.5 8.6 9.8 4.5 8.4 8.6 7 10 2.5Z"/><path d="m15.5 13 .6 1.9 1.9.6-1.9.6-.6 1.9-.6-1.9-1.9-.6 1.9-.6.6-1.9Z"/></svg></span>
			<span class="docspress-prompt__label"><?php esc_html_e( 'Prompt', 'docspress-blocks' ); ?></span>
			<span class="docspress-prompt__model"><?php echo esc_html( $model ); ?></span>
			<span class="docspress-prompt__mode"><?php echo esc_html( $mode_labels[ $mode ] ); ?></span>
			<?php if ( $thinking ) : ?><span class="docspress-prompt__thinking"><span aria-hidden="true">✦</span><?php esc_html_e( 'Thinking', 'docspress-blocks' ); ?></span><?php endif; ?>
		</header>
		<div class="docspress-prompt__composer">
			<div id="<?php echo esc_attr( $prompt_id ); ?>" class="docspress-prompt__text"><?php echo esc_html( $prompt ); ?></div>
			<?php if ( $context ) : ?>
				<div class="docspress-prompt__context" aria-label="<?php esc_attr_e( 'Prompt context', 'docspress-blocks' ); ?>">
					<?php foreach ( $context as $item ) : ?>
						<span class="docspress-prompt__chip docspress-prompt__chip--<?php echo esc_attr( $item['kind'] ); ?>"><?php echo esc_html( $item['label'] ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<footer class="docspress-prompt__footer">
				<?php if ( $caption ) : ?><span class="docspress-prompt__caption"><?php echo $caption; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php endif; ?>
				<button class="docspress-prompt__copy" type="button" data-docspress-copy data-docspress-copy-target="<?php echo esc_attr( $prompt_id ); ?>" aria-label="<?php esc_attr_e( 'Copy prompt', 'docspress-blocks' ); ?>">
					<svg viewBox="0 0 20 20" aria-hidden="true"><rect x="7" y="7" width="9" height="9" rx="1.5"/><path d="M13 7V5.5A1.5 1.5 0 0 0 11.5 4h-7A1.5 1.5 0 0 0 3 5.5v7A1.5 1.5 0 0 0 4.5 14H7"/></svg>
					<b><?php esc_html_e( 'Copy prompt', 'docspress-blocks' ); ?></b>
				</button>
			</footer>
		</div>
	</section>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Register the Prompt block and its folder-owned assets.
 */
function docspress_blocks_register_prompt() {
	$block_url = DOCSPRESS_BLOCKS_URL . 'blocks/prompt/';

	wp_register_script( 'docspress-prompt-editor', $block_url . 'editor.js', array( 'wp-blocks', 'docspress-blocks-editor-shared' ), DOCSPRESS_BLOCKS_VERSION, true );
	wp_register_style( 'docspress-prompt', $block_url . 'style.css', array(), DOCSPRESS_BLOCKS_VERSION );
	wp_register_style( 'docspress-prompt-editor-style', $block_url . 'editor.css', array( 'wp-edit-blocks', 'docspress-prompt' ), DOCSPRESS_BLOCKS_VERSION );

	register_block_type(
		'docspress/prompt',
		array(
			'api_version'     => 3,
			'editor_script'   => 'docspress-prompt-editor',
			'style'           => 'docspress-prompt',
			'editor_style'    => 'docspress-prompt-editor-style',
			'render_callback' => 'docspress_blocks_render_prompt',
			'attributes'      => array(
				'prompt'   => array( 'type' => 'string', 'default' => 'Review this synchronization logic and propose a safer retry strategy. Return a short plan before writing code.' ),
				'model'    => array( 'type' => 'string', 'default' => 'GPT-5' ),
				'mode'     => array( 'type' => 'string', 'default' => 'code' ),
				'thinking' => array( 'type' => 'boolean', 'default' => true ),
				'context'  => array( 'type' => 'string', 'default' => '@repository, src/sync.js, docs/' ),
				'caption'  => array( 'type' => 'string', 'default' => 'Prompt example' ),
			),
			'supports'        => array( 'anchor' => true, 'html' => false ),
		)
	);
}
add_action( 'init', 'docspress_blocks_register_prompt', 10 );
