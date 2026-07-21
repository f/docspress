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
			'description' => __( 'A documentation outline with a callout, copyable terminal command, and verification result.', 'docspress-blocks' ),
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
					'docspress/terminal-session',
					array(
						'title'   => 'Publish documentation',
						'shell'   => 'bash',
						'command' => 'npx docspress publish ./docs',
						'output'  => "✓ Read 12 documents\n✓ Published 12 WordPress pages",
					)
				)
				. docspress_blocks_serialize(
					'docspress/result',
					array(
						'status'  => 'success',
						'title'   => 'Publication verified',
						'content' => '<p>The documentation tree is ready to review.</p>',
						'meta'    => '12 pages',
					)
				),
		)
	);

	register_block_pattern(
		'docspress/api-request-example',
		array(
			'title'       => __( 'API request example', 'docspress-blocks' ),
			'description' => __( 'A structured API request and response followed by equivalent client examples.', 'docspress-blocks' ),
			'categories'  => array( 'docspress' ),
			'content'     => '<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Make a request</h2><!-- /wp:heading -->'
				. docspress_blocks_serialize(
					'docspress/api-request',
					array(
						'method'         => 'POST',
						'endpoint'       => '/wp-json/wp/v2/pages',
						'headers'        => "Content-Type: application/json\nAuthorization: Bearer \$WP_ACCESS_TOKEN",
						'requestBody'    => "{\n  \"title\": \"Getting Started\",\n  \"status\": \"draft\"\n}",
						'requestBodyFormat' => 'json',
						'responseStatus' => '201 Created',
						'responseBody'   => "{\n  \"id\": 42,\n  \"slug\": \"getting-started\",\n  \"status\": \"draft\"\n}",
						'responseBodyFormat' => 'json',
					)
				)
				. docspress_blocks_serialize(
					'docspress/code-tabs',
					array(
						'tabs' => array(
							array( 'label' => 'cURL', 'language' => 'bash', 'filename' => 'Terminal', 'code' => "curl https://example.com/wp-json/wp/v2/pages \\\n  -H 'Authorization: Bearer \$WP_ACCESS_TOKEN'" ),
							array( 'label' => 'JavaScript', 'language' => 'javascript', 'filename' => 'request.js', 'code' => "const response = await fetch( '/wp-json/wp/v2/pages' );\nconst pages = await response.json();" ),
						),
					)
				),
		)
	);

	register_block_pattern(
		'docspress/ai-prompt-example',
		array(
			'title'       => __( 'AI prompt example', 'docspress-blocks' ),
			'description' => __( 'A documented AI prompt with model, mode, context, and a concise expected result.', 'docspress-blocks' ),
			'categories'  => array( 'docspress' ),
			'content'     => '<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Try this prompt</h2><!-- /wp:heading -->'
				. '<!-- wp:paragraph --><p>Use this prompt to review the implementation before proposing a change.</p><!-- /wp:paragraph -->'
				. docspress_blocks_serialize(
					'docspress/prompt',
					array(
						'prompt'   => "Review the synchronization logic for failure modes.\n\nReturn a short risk list, then propose the smallest safe patch.",
						'model'    => 'GPT-5',
						'mode'     => 'code',
						'thinking' => true,
						'context'  => '@repository, src/sync.js, test/sync.test.js',
						'caption'  => 'Synchronization review prompt',
					)
				)
				. docspress_blocks_serialize(
					'docspress/result',
					array(
						'status'  => 'neutral',
						'title'   => 'Expected output',
						'content' => '<p>A focused review with risks, a minimal patch, and matching tests.</p>',
						'meta'    => 'review checklist',
					)
				),
		)
	);
}
add_action( 'init', 'docspress_blocks_register_patterns', 20 );
