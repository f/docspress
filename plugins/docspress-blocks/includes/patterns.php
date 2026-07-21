<?php
/**
 * DocsPress block patterns.
 *
 * @package DocsPressBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Serialize a dynamic block for use inside a pattern.
 *
 * @param string $name       Block name.
 * @param array  $attributes Block attributes.
 * @return string
 */
function docspress_blocks_serialize( $name, $attributes = array() ) {
	$attributes_json = $attributes ? ' ' . serialize_block_attributes( $attributes ) : '';
	return '<!-- wp:' . $name . $attributes_json . ' /-->';
}

/**
 * Register the plugin's starter patterns.
 */
function docspress_blocks_register_patterns() {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	register_block_pattern_category(
		'docspress',
		array( 'label' => __( 'DocsPress', 'docspress-blocks' ) )
	);

	register_block_pattern(
		'docspress/documentation-page-starter',
		array(
			'title'       => __( 'Documentation page starter', 'docspress-blocks' ),
			'description' => __( 'A documentation outline with a callout and a copyable code example.', 'docspress-blocks' ),
			'categories'  => array( 'docspress' ),
			'content'     => '<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Overview</h2><!-- /wp:heading -->'
				. '<!-- wp:paragraph --><p>Explain what the reader will accomplish and what they need before starting.</p><!-- /wp:paragraph -->'
				. docspress_blocks_serialize(
					'docspress/callout',
					array(
						'tone'    => 'tip',
						'title'   => 'Before you begin',
						'content' => '<p>Keep credentials in environment variables and out of committed files.</p>',
					)
				)
				. '<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Run the example</h2><!-- /wp:heading -->'
				. docspress_blocks_serialize(
					'docspress/colorful-code',
					array(
						'language' => 'bash',
						'filename' => 'Terminal',
						'code'     => 'npx docspress publish ./docs',
					)
				),
		)
	);

	register_block_pattern(
		'docspress/api-request-example',
		array(
			'title'       => __( 'API request example', 'docspress-blocks' ),
			'description' => __( 'Equivalent request examples in multiple languages followed by a response note.', 'docspress-blocks' ),
			'categories'  => array( 'docspress' ),
			'content'     => '<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Make a request</h2><!-- /wp:heading -->'
				. docspress_blocks_serialize(
					'docspress/code-tabs',
					array(
						'tabs' => array(
							array( 'label' => 'cURL', 'language' => 'bash', 'filename' => 'Terminal', 'code' => "curl https://example.com/wp-json/wp/v2/pages \\\n  -H 'Authorization: Bearer \$WP_ACCESS_TOKEN'" ),
							array( 'label' => 'JavaScript', 'language' => 'javascript', 'filename' => 'request.js', 'code' => "const response = await fetch( '/wp-json/wp/v2/pages' );\nconst pages = await response.json();" ),
						),
					)
				)
				. docspress_blocks_serialize(
					'docspress/callout',
					array(
						'tone'    => 'note',
						'title'   => 'Response format',
						'content' => '<p>The endpoint returns JSON and paginates large collections.</p>',
					)
				),
		)
	);
}
add_action( 'init', 'docspress_blocks_register_patterns', 20 );
