<?php
/**
 * File Tree block registration and rendering.
 *
 * @package DocsPressBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a file or folder icon.
 *
 * @param bool $folder Whether the entry is a folder.
 * @return string
 */
function docspress_blocks_file_tree_icon( $folder ) {
	if ( $folder ) {
		return '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2.5 5.5h5l1.5 2h8.5v7.5a1.5 1.5 0 0 1-1.5 1.5H4A1.5 1.5 0 0 1 2.5 15V5.5Z"/><path d="M2.5 7.5V5A1.5 1.5 0 0 1 4 3.5h3l2 2h7A1.5 1.5 0 0 1 17.5 7v.5"/></svg>';
	}

	return '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 2.5h6l4 4V17H5V2.5Z"/><path d="M11 2.5v4h4"/></svg>';
}

/**
 * Render the File Tree block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function docspress_blocks_render_file_tree( $attributes ) {
	$root    = isset( $attributes['root'] ) ? sanitize_text_field( $attributes['root'] ) : 'project/';
	$tree    = isset( $attributes['tree'] ) ? (string) $attributes['tree'] : '';
	$caption = isset( $attributes['caption'] ) ? wp_kses_post( $attributes['caption'] ) : '';
	$lines   = preg_split( '/\R/', str_replace( "\t", '  ', $tree ) );

	ob_start();
	?>
	<figure <?php echo get_block_wrapper_attributes( array( 'class' => 'docspress-file-tree' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="docspress-file-tree__bar">
			<span class="docspress-file-tree__root-icon" aria-hidden="true"><?php echo docspress_blocks_file_tree_icon( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<code><?php echo esc_html( $root ); ?></code>
			<span><?php esc_html_e( 'File tree', 'docspress-blocks' ); ?></span>
		</div>
		<div class="docspress-file-tree__entries" role="tree" aria-label="<?php echo esc_attr( sprintf( __( 'Files in %s', 'docspress-blocks' ), $root ) ); ?>">
			<?php foreach ( $lines as $line ) : ?>
				<?php
				preg_match( '/^(\s*)(.*)$/', $line, $matches );
				$label  = isset( $matches[2] ) ? trim( $matches[2] ) : '';
				$depth  = isset( $matches[1] ) ? min( 12, (int) floor( strlen( $matches[1] ) / 2 ) ) : 0;
				$folder = '/' === substr( $label, -1 );
				if ( '' === $label ) {
					continue;
				}
				?>
				<div class="docspress-file-tree__entry<?php echo $folder ? ' is-folder' : ' is-file'; ?>" role="treeitem" aria-level="<?php echo esc_attr( $depth + 1 ); ?>" style="--db-tree-depth: <?php echo esc_attr( $depth ); ?>">
					<span class="docspress-file-tree__connector" aria-hidden="true"></span>
					<span class="docspress-file-tree__icon" aria-hidden="true"><?php echo docspress_blocks_file_tree_icon( $folder ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<code><?php echo esc_html( $label ); ?></code>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( $caption ) : ?><figcaption class="docspress-file-tree__caption"><?php echo $caption; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figcaption><?php endif; ?>
	</figure>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Register the File Tree block and its folder-owned assets.
 */
function docspress_blocks_register_file_tree() {
	$block_url = DOCSPRESS_BLOCKS_URL . 'blocks/file-tree/';

	wp_register_script( 'docspress-file-tree-editor', $block_url . 'editor.js', array( 'wp-blocks', 'docspress-blocks-editor-shared' ), DOCSPRESS_BLOCKS_VERSION, true );
	wp_register_style( 'docspress-file-tree', $block_url . 'style.css', array(), DOCSPRESS_BLOCKS_VERSION );
	wp_register_style( 'docspress-file-tree-editor-style', $block_url . 'editor.css', array( 'wp-edit-blocks', 'docspress-file-tree' ), DOCSPRESS_BLOCKS_VERSION );

	register_block_type(
		'docspress/file-tree',
		array(
			'api_version'     => 3,
			'editor_script'   => 'docspress-file-tree-editor',
			'style'           => 'docspress-file-tree',
			'editor_style'    => 'docspress-file-tree-editor-style',
			'render_callback' => 'docspress_blocks_render_file_tree',
			'attributes'      => array(
				'root'    => array( 'type' => 'string', 'default' => 'project/' ),
				'tree'    => array( 'type' => 'string', 'default' => "docs/\n  getting-started.md\n  api/\n    endpoints.md\npackage.json" ),
				'caption' => array( 'type' => 'string', 'default' => '' ),
			),
			'supports'        => array( 'anchor' => true, 'html' => false ),
		)
	);
}
add_action( 'init', 'docspress_blocks_register_file_tree', 10 );
