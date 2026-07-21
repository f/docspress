<?php
/**
 * Shared code rendering helpers.
 *
 * @package DocsPressBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return a value only when it is in the allow-list.
 *
 * @param string $value    Candidate value.
 * @param array  $allowed  Allowed values.
 * @param string $fallback Fallback value.
 * @return string
 */
function docspress_blocks_allowed_value( $value, $allowed, $fallback ) {
	$value = sanitize_key( (string) $value );
	return in_array( $value, $allowed, true ) ? $value : $fallback;
}

/**
 * Parse a human-friendly list such as 2,4-6 into line numbers.
 *
 * @param string $value Line expression.
 * @return array
 */
function docspress_blocks_highlighted_lines( $value ) {
	$lines = array();
	foreach ( explode( ',', (string) $value ) as $part ) {
		$part = trim( $part );
		if ( preg_match( '/^(\d{1,4})$/', $part, $match ) ) {
			$lines[ (int) $match[1] ] = true;
			continue;
		}

		if ( preg_match( '/^(\d{1,4})-(\d{1,4})$/', $part, $match ) ) {
			$start = min( (int) $match[1], (int) $match[2] );
			$end   = max( (int) $match[1], (int) $match[2] );
			$end   = min( $end, $start + 100 );
			for ( $line = $start; $line <= $end; $line++ ) {
				$lines[ $line ] = true;
			}
		}
	}

	return $lines;
}

/**
 * Build the reusable code surface used by code and code-tab blocks.
 *
 * @param array $attributes Block attributes.
 * @param bool  $show_header Whether to render the filename/language bar.
 * @return string
 */
function docspress_blocks_code_surface( $attributes, $show_header = true ) {
	$language          = docspress_blocks_allowed_value(
		isset( $attributes['language'] ) ? $attributes['language'] : '',
		array( 'bash', 'css', 'html', 'javascript', 'json', 'jsx', 'markdown', 'php', 'plaintext', 'python', 'shell', 'sql', 'tsx', 'typescript', 'yaml' ),
		'plaintext'
	);
	$filename          = isset( $attributes['filename'] ) ? sanitize_text_field( $attributes['filename'] ) : '';
	$code              = isset( $attributes['code'] ) ? (string) $attributes['code'] : '';
	$highlighted       = docspress_blocks_highlighted_lines( isset( $attributes['highlightedLines'] ) ? $attributes['highlightedLines'] : '' );
	$show_line_numbers = ! isset( $attributes['showLineNumbers'] ) || (bool) $attributes['showLineNumbers'];
	$lines             = preg_split( '/\r\n|\r|\n/', $code );
	$classes           = 'docspress-code__surface';

	if ( $show_line_numbers ) {
		$classes .= ' has-line-numbers';
	}

	ob_start();
	?>
	<div class="<?php echo esc_attr( $classes ); ?>" data-language="<?php echo esc_attr( $language ); ?>">
		<?php if ( $show_header ) : ?>
			<div class="docspress-code__bar">
				<span class="docspress-code__language"><?php echo esc_html( $language ); ?></span>
				<span class="docspress-code__filename"><?php echo esc_html( $filename ? $filename : $language ); ?></span>
				<button class="docspress-code__copy" type="button" data-docspress-copy aria-label="<?php esc_attr_e( 'Copy code', 'docspress-blocks' ); ?>">
					<span aria-hidden="true">⧉</span><b><?php esc_html_e( 'Copy', 'docspress-blocks' ); ?></b>
				</button>
			</div>
		<?php endif; ?>
		<pre class="docspress-code__pre" tabindex="0"><code><?php foreach ( $lines as $index => $line ) : $number = $index + 1; ?><span class="docspress-code__line<?php echo isset( $highlighted[ $number ] ) ? ' is-highlighted' : ''; ?>" data-line="<?php echo esc_attr( (string) $number ); ?>"><span class="docspress-code__line-content"><?php echo esc_html( $line ); ?></span></span><?php endforeach; ?></code></pre>
	</div>
	<?php
	return trim( ob_get_clean() );
}
