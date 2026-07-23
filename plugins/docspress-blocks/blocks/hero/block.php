<?php
/**
 * Hero block registration and rendering.
 *
 * @package DocsPressBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build an optional hero action.
 *
 * @param string $label  Action label.
 * @param string $url    Action URL.
 * @param string $class  Action class.
 * @param bool   $new_tab Whether the action opens in a new tab.
 * @return string
 */
function docspress_blocks_render_hero_action( $label, $url, $class, $new_tab ) {
	$label = wp_kses_post( $label );
	$url   = esc_url( $url );

	if ( ! $label || ! $url ) {
		return '';
	}

	return sprintf(
		'<a class="%1$s" href="%2$s"%3$s>%4$s</a>',
		esc_attr( $class ),
		$url,
		$new_tab ? ' target="_blank" rel="noopener noreferrer"' : '',
		$label
	);
}

/**
 * Render the Hero block.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function docspress_blocks_render_hero( $attributes ) {
	$eyebrow       = isset( $attributes['eyebrow'] ) ? wp_kses_post( $attributes['eyebrow'] ) : '';
	$title         = isset( $attributes['title'] ) ? wp_kses_post( $attributes['title'] ) : '';
	$description   = isset( $attributes['description'] ) ? wp_kses_post( $attributes['description'] ) : '';
	$primary_label = isset( $attributes['primaryLabel'] ) ? $attributes['primaryLabel'] : '';
	$primary_url   = isset( $attributes['primaryUrl'] ) ? $attributes['primaryUrl'] : '';
	$secondary_label = isset( $attributes['secondaryLabel'] ) ? $attributes['secondaryLabel'] : '';
	$secondary_url = isset( $attributes['secondaryUrl'] ) ? $attributes['secondaryUrl'] : '';
	$media_id      = isset( $attributes['mediaId'] ) ? absint( $attributes['mediaId'] ) : 0;
	$media_url     = isset( $attributes['mediaUrl'] ) ? esc_url( $attributes['mediaUrl'] ) : '';
	$media_alt     = isset( $attributes['mediaAlt'] ) ? sanitize_text_field( $attributes['mediaAlt'] ) : '';
	$visual_label  = isset( $attributes['visualLabel'] ) ? sanitize_text_field( $attributes['visualLabel'] ) : '';
	$layout         = docspress_blocks_allowed_value( isset( $attributes['layout'] ) ? $attributes['layout'] : '', array( 'split', 'editorial' ), 'split' );
	$media_position = docspress_blocks_allowed_value( isset( $attributes['mediaPosition'] ) ? $attributes['mediaPosition'] : '', array( 'left', 'right' ), 'right' );
	$height         = docspress_blocks_allowed_value( isset( $attributes['height'] ) ? $attributes['height'] : '', array( 'compact', 'standard', 'tall' ), 'standard' );
	$tone           = docspress_blocks_allowed_value( isset( $attributes['tone'] ) ? $attributes['tone'] : '', array( 'theme', 'midnight', 'paper', 'brand' ), 'theme' );
	$text_align     = docspress_blocks_allowed_value( isset( $attributes['textAlign'] ) ? $attributes['textAlign'] : '', array( 'left', 'center' ), 'left' );
	$media_width    = isset( $attributes['mediaWidth'] ) ? min( 58, max( 34, absint( $attributes['mediaWidth'] ) ) ) : 44;
	$image_scale    = isset( $attributes['imageScale'] ) ? min( 120, max( 60, absint( $attributes['imageScale'] ) ) ) : 100;
	$show_grid      = ! empty( $attributes['showGrid'] );
	$show_orbit     = ! empty( $attributes['showOrbit'] );
	$primary_new_tab = ! empty( $attributes['primaryNewTab'] );
	$secondary_new_tab = ! empty( $attributes['secondaryNewTab'] );
	$has_visual     = (bool) ( $media_url || $media_id );
	$classes        = array(
		'docspress-hero',
		'docspress-hero--' . $tone,
		'docspress-hero--layout-' . $layout,
		'docspress-hero--media-' . $media_position,
		'docspress-hero--height-' . $height,
		'docspress-hero--align-' . $text_align,
	);
	$styles         = array(
		'--db-hero-media-width:' . $media_width . '%',
		'--db-hero-image-scale:' . $image_scale . '%',
	);

	if ( ! $show_grid ) {
		$classes[] = 'docspress-hero--no-grid';
	}
	if ( ! $show_orbit ) {
		$classes[] = 'docspress-hero--no-orbit';
	}
	if ( ! $has_visual ) {
		$classes[] = 'docspress-hero--no-visual';
	}

	$custom_colors = array(
		'panelColor'  => '--db-hero-panel',
		'visualColor' => '--db-hero-visual',
		'textColor'   => '--db-hero-heading',
		'accentColor' => '--db-hero-accent',
	);
	foreach ( $custom_colors as $attribute_name => $variable ) {
		$color = isset( $attributes[ $attribute_name ] ) ? sanitize_hex_color( $attributes[ $attribute_name ] ) : '';
		if ( $color ) {
			$styles[] = $variable . ':' . $color;
			if ( 'textColor' === $attribute_name ) {
				$styles[] = '--db-hero-copy:' . $color;
			}
		}
	}

	$primary_action   = docspress_blocks_render_hero_action( $primary_label, $primary_url, 'docspress-hero__button docspress-hero__button--primary', $primary_new_tab );
	$secondary_action = docspress_blocks_render_hero_action( $secondary_label, $secondary_url, 'docspress-hero__button docspress-hero__button--secondary', $secondary_new_tab );
	$image             = '';
	if ( $media_id ) {
		$image = wp_get_attachment_image(
			$media_id,
			'full',
			false,
			array(
				'class'         => 'docspress-hero__image',
				'alt'           => $media_alt,
				'loading'       => 'eager',
				'decoding'      => 'async',
				'fetchpriority' => 'high',
			)
		);
	}
	if ( ! $image && $media_url ) {
		$image = sprintf(
			'<img class="docspress-hero__image" src="%1$s" alt="%2$s" loading="eager" decoding="async" fetchpriority="high">',
			$media_url,
			esc_attr( $media_alt )
		);
	}

	ob_start();
	?>
	<section <?php echo get_block_wrapper_attributes( array( 'class' => implode( ' ', $classes ), 'style' => implode( ';', $styles ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="docspress-hero__copy">
			<?php if ( $eyebrow ) : ?><p class="docspress-hero__eyebrow"><?php echo $eyebrow; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p><?php endif; ?>
			<?php if ( $title ) : ?><h1 class="docspress-hero__title"><?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1><?php endif; ?>
			<?php if ( $description ) : ?><p class="docspress-hero__description"><?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p><?php endif; ?>
			<?php if ( $primary_action || $secondary_action ) : ?>
				<div class="docspress-hero__actions"><?php echo $primary_action . $secondary_action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<?php endif; ?>
		</div>
		<?php if ( $has_visual ) : ?>
			<div class="docspress-hero__visual">
				<?php if ( $visual_label ) : ?><span class="docspress-hero__visual-label" aria-hidden="true"><?php echo esc_html( $visual_label ); ?></span><?php endif; ?>
				<?php if ( $image ) : ?><figure class="docspress-hero__media"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></figure><?php endif; ?>
			</div>
		<?php endif; ?>
	</section>
	<?php
	return trim( ob_get_clean() );
}

/**
 * Register the Hero block and its folder-owned assets.
 */
function docspress_blocks_register_hero() {
	$block_url = DOCSPRESS_BLOCKS_URL . 'blocks/hero/';

	wp_register_script(
		'docspress-hero-editor',
		$block_url . 'editor.js',
		array( 'wp-blocks', 'docspress-blocks-editor-shared' ),
		DOCSPRESS_BLOCKS_VERSION,
		true
	);
	wp_register_style( 'docspress-hero', $block_url . 'style.css', array(), DOCSPRESS_BLOCKS_VERSION );
	wp_register_style( 'docspress-hero-editor-style', $block_url . 'editor.css', array( 'wp-edit-blocks', 'docspress-hero' ), DOCSPRESS_BLOCKS_VERSION );

	register_block_type(
		'docspress/hero',
		array(
			'api_version'     => 3,
			'editor_script'   => 'docspress-hero-editor',
			'style'           => 'docspress-hero',
			'editor_style'    => 'docspress-hero-editor-style',
			'render_callback' => 'docspress_blocks_render_hero',
			'attributes'      => array(
				'eyebrow'       => array( 'type' => 'string', 'default' => 'Documentation, publishing, and community' ),
				'title'         => array( 'type' => 'string', 'default' => 'Docs that stay connected to your GitHub repo' ),
				'description'   => array( 'type' => 'string', 'default' => 'Write beside your code. Publish a WordPress experience that guides every reader to the docs written for them.' ),
				'primaryLabel'  => array( 'type' => 'string', 'default' => 'Browse documentation' ),
				'primaryUrl'    => array( 'type' => 'string', 'default' => '' ),
				'primaryNewTab' => array( 'type' => 'boolean', 'default' => false ),
				'secondaryLabel' => array( 'type' => 'string', 'default' => 'Latest updates' ),
				'secondaryUrl'  => array( 'type' => 'string', 'default' => '' ),
				'secondaryNewTab' => array( 'type' => 'boolean', 'default' => false ),
				'mediaId'       => array( 'type' => 'number', 'default' => 0 ),
				'mediaUrl'      => array( 'type' => 'string', 'default' => '' ),
				'mediaAlt'      => array( 'type' => 'string', 'default' => '' ),
				'visualLabel'   => array( 'type' => 'string', 'default' => '' ),
				'layout'        => array( 'type' => 'string', 'default' => 'split' ),
				'mediaPosition' => array( 'type' => 'string', 'default' => 'right' ),
				'mediaWidth'    => array( 'type' => 'number', 'default' => 44 ),
				'imageScale'    => array( 'type' => 'number', 'default' => 100 ),
				'height'        => array( 'type' => 'string', 'default' => 'standard' ),
				'tone'          => array( 'type' => 'string', 'default' => 'theme' ),
				'textAlign'     => array( 'type' => 'string', 'default' => 'left' ),
				'showGrid'      => array( 'type' => 'boolean', 'default' => false ),
				'showOrbit'     => array( 'type' => 'boolean', 'default' => false ),
				'panelColor'    => array( 'type' => 'string', 'default' => '' ),
				'visualColor'   => array( 'type' => 'string', 'default' => '' ),
				'textColor'     => array( 'type' => 'string', 'default' => '' ),
				'accentColor'   => array( 'type' => 'string', 'default' => '' ),
			),
			'supports'        => array(
				'align'  => array( 'wide', 'full' ),
				'anchor' => true,
				'html'   => false,
			),
		)
	);
}
add_action( 'init', 'docspress_blocks_register_hero', 10 );
