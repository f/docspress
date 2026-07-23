<?php
/**
 * Audience Paths block registration and rendering.
 *
 * @package DocsPressBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the default audience paths.
 *
 * @return array
 */
function docspress_blocks_audience_paths_defaults() {
	return array(
		array(
			'title'       => 'I already have Markdown docs',
			'description' => 'Connect an existing docs folder to WordPress and begin with a safe draft sync.',
			'url'         => '/docs/publish-existing-docs/',
			'cta'         => 'Publish existing docs',
			'icon'        => 'MD',
			'accent'      => 'blue',
			'newTab'      => false,
		),
		array(
			'title'       => 'I need to create docs',
			'description' => 'Generate source-grounded documentation with AI, review it, then publish it.',
			'url'         => '/docs/create-docs-with-ai/',
			'cta'         => 'Create docs with AI',
			'icon'        => 'AI',
			'accent'      => 'gold',
			'newTab'      => false,
		),
	);
}

/**
 * Normalize one audience path for safe rendering.
 *
 * @param array $path Raw path attributes.
 * @return array
 */
function docspress_blocks_normalize_audience_path( $path ) {
	$path = is_array( $path ) ? $path : array();

	return array(
		'title'       => isset( $path['title'] ) ? sanitize_text_field( $path['title'] ) : '',
		'description' => isset( $path['description'] ) ? sanitize_text_field( $path['description'] ) : '',
		'url'         => isset( $path['url'] ) ? esc_url( $path['url'] ) : '',
		'cta'         => isset( $path['cta'] ) ? sanitize_text_field( $path['cta'] ) : '',
		'icon'        => isset( $path['icon'] ) ? sanitize_text_field( $path['icon'] ) : '',
		'accent'      => docspress_blocks_allowed_value( isset( $path['accent'] ) ? $path['accent'] : '', array( 'blue', 'gold', 'coral', 'green' ), 'blue' ),
		'new_tab'     => ! empty( $path['newTab'] ),
	);
}

/**
 * Render the Audience Paths block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function docspress_blocks_render_audience_paths( $attributes ) {
	$eyebrow      = isset( $attributes['eyebrow'] ) ? sanitize_text_field( $attributes['eyebrow'] ) : '';
	$title        = isset( $attributes['title'] ) ? sanitize_text_field( $attributes['title'] ) : '';
	$description  = isset( $attributes['description'] ) ? sanitize_text_field( $attributes['description'] ) : '';
	$paths        = isset( $attributes['paths'] ) && is_array( $attributes['paths'] ) ? array_slice( $attributes['paths'], 0, 6 ) : docspress_blocks_audience_paths_defaults();
	$columns      = isset( $attributes['columns'] ) ? min( 3, max( 1, absint( $attributes['columns'] ) ) ) : 2;
	$tone         = docspress_blocks_allowed_value( isset( $attributes['tone'] ) ? $attributes['tone'] : '', array( 'theme', 'paper', 'ink', 'blueprint' ), 'theme' );
	$text_align   = docspress_blocks_allowed_value( isset( $attributes['textAlign'] ) ? $attributes['textAlign'] : '', array( 'left', 'center' ), 'left' );
	$show_numbers = ! empty( $attributes['showNumbers'] );
	$classes      = array(
		'docspress-audience-paths',
		'docspress-audience-paths--' . $tone,
		'docspress-audience-paths--columns-' . $columns,
		'docspress-audience-paths--align-' . $text_align,
	);
	$styles       = array();

	if ( ! $show_numbers ) {
		$classes[] = 'docspress-audience-paths--no-numbers';
	}

	$custom_colors = array(
		'panelColor'  => '--db-paths-panel',
		'textColor'   => '--db-paths-heading',
		'accentColor' => '--db-paths-accent',
	);
	foreach ( $custom_colors as $attribute_name => $variable ) {
		$color = isset( $attributes[ $attribute_name ] ) ? sanitize_hex_color( $attributes[ $attribute_name ] ) : '';
		if ( $color ) {
			$styles[] = $variable . ':' . $color;
			if ( 'textColor' === $attribute_name ) {
				$styles[] = '--db-paths-copy:' . $color;
			}
		}
	}

	ob_start();
	?>
	<section <?php echo get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ), 'style' => implode( ';', $styles ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<header class="docspress-audience-paths__header">
			<?php if ( $eyebrow ) : ?><p class="docspress-audience-paths__eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
			<div class="docspress-audience-paths__heading-group">
				<?php if ( $title ) : ?><h2 class="docspress-audience-paths__title"><?php echo esc_html( $title ); ?></h2><?php endif; ?>
				<?php if ( $description ) : ?><p class="docspress-audience-paths__description"><?php echo esc_html( $description ); ?></p><?php endif; ?>
			</div>
		</header>
		<div class="docspress-audience-paths__grid" role="list">
			<?php foreach ( $paths as $index => $raw_path ) : ?>
				<?php
				$path = docspress_blocks_normalize_audience_path( $raw_path );
				$tag  = $path['url'] ? 'a' : 'div';
				?>
				<div class="docspress-audience-paths__item" role="listitem">
					<<?php echo esc_attr( $tag ); ?>
						class="docspress-audience-paths__card docspress-audience-paths__card--<?php echo esc_attr( $path['accent'] ); ?>"
						<?php if ( $path['url'] ) : ?>
							href="<?php echo esc_url( $path['url'] ); ?>"
							<?php if ( $path['new_tab'] ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
						<?php endif; ?>
					>
						<span class="docspress-audience-paths__number" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<span class="docspress-audience-paths__icon" aria-hidden="true"><?php echo esc_html( $path['icon'] ); ?></span>
						<span class="docspress-audience-paths__card-copy">
							<?php if ( $path['title'] ) : ?><span class="docspress-audience-paths__card-title"><?php echo esc_html( $path['title'] ); ?></span><?php endif; ?>
							<?php if ( $path['description'] ) : ?><span class="docspress-audience-paths__card-description"><?php echo esc_html( $path['description'] ); ?></span><?php endif; ?>
						</span>
						<?php if ( $path['cta'] ) : ?>
							<span class="docspress-audience-paths__cta"><?php echo esc_html( $path['cta'] ); ?><span aria-hidden="true">↗</span></span>
						<?php endif; ?>
					</<?php echo esc_attr( $tag ); ?>>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Register the Audience Paths block and its folder-owned assets.
 */
function docspress_blocks_register_audience_paths() {
	$block_url = DOCSPRESS_BLOCKS_URL . 'blocks/audience-paths/';
	$defaults  = docspress_blocks_audience_paths_defaults();

	wp_register_script( 'docspress-audience-paths-editor', $block_url . 'editor.js', array( 'wp-blocks', 'docspress-blocks-editor-shared' ), DOCSPRESS_BLOCKS_VERSION, true );
	wp_register_style( 'docspress-audience-paths', $block_url . 'style.css', array(), DOCSPRESS_BLOCKS_VERSION );
	wp_register_style( 'docspress-audience-paths-editor-style', $block_url . 'editor.css', array( 'wp-edit-blocks', 'docspress-audience-paths' ), DOCSPRESS_BLOCKS_VERSION );

	register_block_type(
		'docspress/audience-paths',
		array(
			'api_version'     => 3,
			'editor_script'   => 'docspress-audience-paths-editor',
			'style'           => 'docspress-audience-paths',
			'editor_style'    => 'docspress-audience-paths-editor-style',
			'render_callback' => 'docspress_blocks_render_audience_paths',
			'attributes'      => array(
				'eyebrow'      => array( 'type' => 'string', 'default' => 'Choose a starting point' ),
				'title'        => array( 'type' => 'string', 'default' => 'Where are your docs today?' ),
				'description'  => array( 'type' => 'string', 'default' => 'Follow the path that matches your repository.' ),
				'paths'        => array( 'type' => 'array', 'default' => $defaults ),
				'columns'      => array( 'type' => 'number', 'default' => 2 ),
				'tone'         => array( 'type' => 'string', 'default' => 'theme' ),
				'textAlign'    => array( 'type' => 'string', 'default' => 'left' ),
				'showNumbers'  => array( 'type' => 'boolean', 'default' => false ),
				'panelColor'   => array( 'type' => 'string', 'default' => '' ),
				'textColor'    => array( 'type' => 'string', 'default' => '' ),
				'accentColor'  => array( 'type' => 'string', 'default' => '' ),
			),
			'supports'        => array(
				'align'  => array( 'wide', 'full' ),
				'anchor' => true,
				'html'   => false,
			),
		)
	);
}
add_action( 'init', 'docspress_blocks_register_audience_paths', 10 );
