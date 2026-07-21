<?php
/**
 * Discover and expose folder-based DocsPress design presets.
 *
 * @package DocsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return validated preset manifests from inc/design-presets/<slug>/preset.php.
 *
 * A new preset becomes available automatically when its folder contains a
 * valid manifest. An optional style.css file is also discovered and loaded.
 *
 * @return array<string,array<string,mixed>>
 */
function docspress_design_preset_manifests() {
	static $manifests = null;

	if ( null !== $manifests ) {
		return $manifests;
	}

	$manifests = array();
	$base_path = get_theme_file_path( 'inc/design-presets' );
	$files     = glob( trailingslashit( $base_path ) . '*/preset.php' );

	if ( ! is_array( $files ) ) {
		return $manifests;
	}

	sort( $files, SORT_NATURAL | SORT_FLAG_CASE );

	foreach ( $files as $file ) {
		$slug = basename( dirname( $file ) );
		if ( sanitize_key( $slug ) !== $slug ) {
			continue;
		}

		$manifest = require $file;
		if (
			! is_array( $manifest ) ||
			empty( $manifest['label'] ) ||
			empty( $manifest['values'] ) ||
			! is_array( $manifest['values'] )
		) {
			continue;
		}

		$relative_stylesheet = 'inc/design-presets/' . $slug . '/style.css';
		$manifest['slug']     = $slug;
		$manifest['order']    = isset( $manifest['order'] ) ? (int) $manifest['order'] : 100;
		$manifest['description'] = isset( $manifest['description'] ) ? (string) $manifest['description'] : '';
		$manifest['stylesheet']  = file_exists( get_theme_file_path( $relative_stylesheet ) ) ? $relative_stylesheet : '';
		$manifests[ $slug ]      = $manifest;
	}

	uasort(
		$manifests,
		static function ( $first, $second ) {
			if ( $first['order'] === $second['order'] ) {
				return strnatcasecmp( $first['label'], $second['label'] );
			}

			return $first['order'] <=> $second['order'];
		}
	);

	/**
	 * Filter the discovered design preset manifests.
	 *
	 * @param array<string,array<string,mixed>> $manifests Preset manifests keyed by slug.
	 */
	$manifests = apply_filters( 'docspress_design_preset_manifests', $manifests );

	return is_array( $manifests ) ? $manifests : array();
}

/**
 * Return only the editable setting recipes used by the Customizer controller.
 *
 * @return array<string,array<string,mixed>>
 */
function docspress_design_presets() {
	$recipes = array();

	foreach ( docspress_design_preset_manifests() as $slug => $manifest ) {
		$recipes[ $slug ] = $manifest['values'];
	}

	return $recipes;
}

/**
 * Return a setting's value from the active preset when no saved theme mod exists.
 *
 * This keeps a fresh theme activation visually identical to selecting the same
 * preset in the Customizer. Saved values always continue to take precedence.
 *
 * @param string $setting_id Setting identifier.
 * @param mixed  $fallback   Fallback when the active preset does not define it.
 * @return mixed
 */
function docspress_design_preset_default( $setting_id, $fallback = null ) {
	$preset    = get_theme_mod( 'docspress_design_preset', 'docspress' );
	$manifests = docspress_design_preset_manifests();

	if (
		'custom' !== $preset &&
		isset( $manifests[ $preset ]['values'] ) &&
		array_key_exists( $setting_id, $manifests[ $preset ]['values'] )
	) {
		return $manifests[ $preset ]['values'][ $setting_id ];
	}

	return $fallback;
}

/**
 * Read a saved design setting, falling back to the active preset recipe.
 *
 * @param string $setting_id Setting identifier.
 * @param mixed  $fallback   Final fallback value.
 * @return mixed
 */
function docspress_get_design_setting( $setting_id, $fallback = null ) {
	return get_theme_mod(
		$setting_id,
		docspress_design_preset_default( $setting_id, $fallback )
	);
}

/**
 * Return translated preset labels for the Customizer select control.
 *
 * @return array<string,string>
 */
function docspress_design_preset_choices() {
	$choices = array();

	foreach ( docspress_design_preset_manifests() as $slug => $manifest ) {
		$choices[ $slug ] = $manifest['label'];
	}

	$choices['custom'] = __( 'Custom', 'docspress' );

	return $choices;
}

/**
 * Enqueue optional preset-specific refinements after the shared theme styles.
 *
 * Every stylesheet is scoped by its preset body class, which keeps Customizer
 * switching instant while adding only small, cacheable files.
 */
function docspress_design_preset_styles() {
	$theme = wp_get_theme();

	foreach ( docspress_design_preset_manifests() as $slug => $manifest ) {
		if ( empty( $manifest['stylesheet'] ) ) {
			continue;
		}

		wp_enqueue_style(
			'docspress-preset-' . $slug,
			get_theme_file_uri( $manifest['stylesheet'] ),
			array( 'docspress-style' ),
			$theme->get( 'Version' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'docspress_design_preset_styles', 20 );
