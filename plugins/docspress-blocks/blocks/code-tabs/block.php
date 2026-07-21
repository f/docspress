<?php
/**
 * Code Tabs block registration and rendering.
 *
 * @package DocsPressBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize tab data before rendering it.
 *
 * @param mixed $tabs Raw tabs attribute.
 * @return array
 */
function docspress_blocks_normalize_tabs( $tabs ) {
	if ( ! is_array( $tabs ) ) {
		return array();
	}

	$normalized = array();
	foreach ( array_slice( $tabs, 0, 8 ) as $tab ) {
		if ( ! is_array( $tab ) ) {
			continue;
		}

		$normalized[] = array(
			'label'    => isset( $tab['label'] ) ? sanitize_text_field( $tab['label'] ) : '',
			'language' => docspress_blocks_allowed_value(
				isset( $tab['language'] ) ? $tab['language'] : '',
				array( 'bash', 'css', 'html', 'javascript', 'json', 'jsx', 'markdown', 'php', 'plaintext', 'python', 'shell', 'sql', 'tsx', 'typescript', 'yaml' ),
				'plaintext'
			),
			'filename' => isset( $tab['filename'] ) ? sanitize_text_field( $tab['filename'] ) : '',
			'code'     => isset( $tab['code'] ) ? (string) $tab['code'] : '',
		);
	}

	return $normalized;
}

/**
 * Render the Code Tabs block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function docspress_blocks_render_code_tabs( $attributes ) {
	$tabs = docspress_blocks_normalize_tabs( isset( $attributes['tabs'] ) ? $attributes['tabs'] : array() );
	if ( ! $tabs ) {
		return '';
	}

	wp_enqueue_script( 'docspress-blocks-view' );
	$instance_id  = wp_unique_id( 'docspress-code-tabs-' );
	$caption      = isset( $attributes['caption'] ) ? wp_kses_post( $attributes['caption'] ) : '';
	$line_numbers = ! isset( $attributes['showLineNumbers'] ) || (bool) $attributes['showLineNumbers'];

	ob_start();
	?>
	<figure <?php echo get_block_wrapper_attributes( array( 'class' => 'docspress-code-tabs', 'data-docspress-tabs' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="docspress-code-tabs__surface">
			<div class="docspress-code-tabs__header">
				<div class="docspress-code-tabs__list" role="tablist" aria-label="<?php esc_attr_e( 'Code examples', 'docspress-blocks' ); ?>">
					<?php foreach ( $tabs as $index => $tab ) :
						$tab_id   = $instance_id . '-tab-' . $index;
						$panel_id = $instance_id . '-panel-' . $index;
						?>
						<button id="<?php echo esc_attr( $tab_id ); ?>" class="docspress-code-tabs__tab<?php echo 0 === $index ? ' is-active' : ''; ?>" type="button" role="tab" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $panel_id ); ?>" tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>"><?php echo esc_html( $tab['label'] ? $tab['label'] : __( 'Untitled', 'docspress-blocks' ) ); ?></button>
					<?php endforeach; ?>
				</div>
				<div class="docspress-code-tabs__tools">
					<?php foreach ( $tabs as $index => $tab ) :
						$panel_id = $instance_id . '-panel-' . $index;
						$filename = $tab['filename'] ? $tab['filename'] : $tab['language'];
						?>
						<span class="docspress-code-tabs__filename" data-docspress-tab-meta="<?php echo esc_attr( $panel_id ); ?>"<?php echo 0 === $index ? '' : ' hidden'; ?>><?php echo esc_html( $filename ); ?></span>
					<?php endforeach; ?>
					<button class="docspress-code__copy docspress-code-tabs__copy" type="button" data-docspress-copy aria-label="<?php esc_attr_e( 'Copy code', 'docspress-blocks' ); ?>">
						<span aria-hidden="true">⧉</span><b><?php esc_html_e( 'Copy', 'docspress-blocks' ); ?></b>
					</button>
				</div>
			</div>
			<?php foreach ( $tabs as $index => $tab ) :
				$tab_id   = $instance_id . '-tab-' . $index;
				$panel_id = $instance_id . '-panel-' . $index;
				$surface  = array(
					'language'        => $tab['language'],
					'filename'        => $tab['filename'],
					'code'            => $tab['code'],
					'showLineNumbers' => $line_numbers,
				);
				?>
				<div id="<?php echo esc_attr( $panel_id ); ?>" class="docspress-code-tabs__panel" role="tabpanel" aria-labelledby="<?php echo esc_attr( $tab_id ); ?>"<?php echo 0 === $index ? '' : ' hidden'; ?>><?php echo docspress_blocks_code_surface( $surface, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endforeach; ?>
		</div>
		<?php if ( $caption ) : ?>
			<figcaption class="docspress-code__caption"><?php echo $caption; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figcaption>
		<?php endif; ?>
	</figure>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Register the Code Tabs block and its folder-owned assets.
 */
function docspress_blocks_register_code_tabs() {
	$block_url = DOCSPRESS_BLOCKS_URL . 'blocks/code-tabs/';

	wp_register_script(
		'docspress-code-tabs-editor',
		$block_url . 'editor.js',
		array( 'wp-blocks', 'docspress-blocks-editor-shared' ),
		DOCSPRESS_BLOCKS_VERSION,
		true
	);

	wp_register_style(
		'docspress-code-tabs',
		$block_url . 'style.css',
		array( 'docspress-blocks-code' ),
		DOCSPRESS_BLOCKS_VERSION
	);

	wp_register_style(
		'docspress-code-tabs-editor-style',
		$block_url . 'editor.css',
		array( 'docspress-blocks-code-editor', 'docspress-code-tabs' ),
		DOCSPRESS_BLOCKS_VERSION
	);

	register_block_type(
		'docspress/code-tabs',
		array(
			'api_version'     => 3,
			'editor_script'   => 'docspress-code-tabs-editor',
			'style'           => 'docspress-code-tabs',
			'editor_style'    => 'docspress-code-tabs-editor-style',
			'render_callback' => 'docspress_blocks_render_code_tabs',
			'attributes'      => array(
				'tabs'            => array(
					'type'    => 'array',
					'default' => array(
						array( 'label' => 'JavaScript', 'language' => 'javascript', 'filename' => 'example.js', 'code' => 'const docs = await publish();' ),
						array( 'label' => 'PHP', 'language' => 'php', 'filename' => 'example.php', 'code' => '$docs = docspress_publish();' ),
					),
				),
				'showLineNumbers' => array( 'type' => 'boolean', 'default' => true ),
				'caption'         => array( 'type' => 'string', 'default' => '' ),
			),
			'supports'        => array( 'anchor' => true, 'html' => false ),
		)
	);
}
add_action( 'init', 'docspress_blocks_register_code_tabs', 10 );
