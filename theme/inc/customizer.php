<?php
/**
 * DocsPress Customizer controls and front-end design tokens.
 *
 * @package DocsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize a checkbox value.
 *
 * @param mixed $value Submitted value.
 * @return bool
 */
function docspress_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Sanitize a choice against its control choices.
 *
 * @param string               $value   Submitted value.
 * @param WP_Customize_Setting $setting Customizer setting.
 * @return string
 */
function docspress_sanitize_choice( $value, $setting ) {
	$control = $setting->manager->get_control( $setting->id );
	$choices = $control ? $control->choices : array();

	return array_key_exists( $value, $choices ) ? $value : $setting->default;
}

/**
 * Sanitize a numeric range control.
 *
 * @param float|int|string     $value   Submitted value.
 * @param WP_Customize_Setting $setting Customizer setting.
 * @return float|int
 */
function docspress_sanitize_range( $value, $setting ) {
	$control = $setting->manager->get_control( $setting->id );
	$minimum = isset( $control->input_attrs['min'] ) ? (float) $control->input_attrs['min'] : (float) $value;
	$maximum = isset( $control->input_attrs['max'] ) ? (float) $control->input_attrs['max'] : (float) $value;
	$step    = isset( $control->input_attrs['step'] ) ? (float) $control->input_attrs['step'] : 1;
	$value   = (float) $value;
	$value   = min( $maximum, max( $minimum, $value ) );

	if ( 1 <= $step ) {
		return (int) round( $value );
	}

	return round( $value / $step ) * $step;
}

/**
 * Return available navigation menus for select controls.
 *
 * @return array<int,string>
 */
function docspress_customizer_menu_choices() {
	$choices = array( 0 => __( 'Use assigned theme location', 'docspress' ) );

	foreach ( wp_get_nav_menus() as $menu ) {
		$choices[ (int) $menu->term_id ] = $menu->name;
	}

	return $choices;
}

/**
 * Whether Page hierarchy controls are relevant.
 *
 * @param WP_Customize_Control $control Current control.
 * @return bool
 */
function docspress_customizer_uses_page_tree( $control ) {
	return 'page_tree' === $control->manager->get_setting( 'docspress_sidebar_source' )->value();
}

/**
 * Whether custom sidebar menu controls are relevant.
 *
 * @param WP_Customize_Control $control Current control.
 * @return bool
 */
function docspress_customizer_uses_custom_menu( $control ) {
	return 'custom_menu' === $control->manager->get_setting( 'docspress_sidebar_source' )->value();
}

/**
 * Whether WordPress edit-action controls are relevant.
 *
 * @param WP_Customize_Control $control Current control.
 * @return bool
 */
function docspress_customizer_shows_wordpress_edit( $control ) {
	return (bool) $control->manager->get_setting( 'docspress_show_edit_link' )->value();
}

/**
 * Whether GitHub edit-action controls are relevant.
 *
 * @param WP_Customize_Control $control Current control.
 * @return bool
 */
function docspress_customizer_shows_github_edit( $control ) {
	return (bool) $control->manager->get_setting( 'docspress_show_github_edit_link' )->value();
}

/**
 * Whether command-search controls are relevant.
 *
 * @param WP_Customize_Control $control Current control.
 * @return bool
 */
function docspress_customizer_shows_header_search( $control ) {
	return (bool) $control->manager->get_setting( 'docspress_show_header_search' )->value();
}

/**
 * Whether a fixed default color mode is required.
 *
 * @param WP_Customize_Control $control Current control.
 * @return bool
 */
function docspress_customizer_hides_color_toggle( $control ) {
	return ! (bool) $control->manager->get_setting( 'docspress_show_color_toggle' )->value();
}

/**
 * Register the full DocsPress theme configuration panel.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function docspress_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'docspress_theme',
		array(
			'title'       => __( 'DocsPress Theme', 'docspress' ),
			'description' => __( 'Configure navigation, layout, appearance, reading typography, and documentation controls.', 'docspress' ),
			'priority'    => 30,
		)
	);

	$sections = array(
		'docspress_presets'    => array(
			'title'       => __( 'Design presets', 'docspress' ),
			'description' => __( 'Apply a complete visual recipe, then fine-tune any individual color, font, or layout control.', 'docspress' ),
		),
		'docspress_navigation' => array(
			'title'       => __( 'Navigation', 'docspress' ),
			'description' => __( 'Use automatic Page hierarchy or a hand-built WordPress menu for documentation navigation.', 'docspress' ),
		),
		'docspress_header'     => array(
			'title'       => __( 'Header', 'docspress' ),
			'description' => __( 'Control the brand, primary menu, color switcher, and repository link.', 'docspress' ),
		),
		'docspress_command_search' => array(
			'title'       => __( 'Command search', 'docspress' ),
			'description' => __( 'Configure the Cmd/Ctrl+K search window, its result details, dimensions, and backdrop.', 'docspress' ),
		),
		'docspress_layout'     => array(
			'title'       => __( 'Layout & reading tools', 'docspress' ),
			'description' => __( 'Tune column widths and decide which documentation aids are shown.', 'docspress' ),
		),
		'docspress_colors'     => array(
			'title'       => __( 'Light & dark colors', 'docspress' ),
			'description' => __( 'Set independent palettes for both reading modes.', 'docspress' ),
		),
		'docspress_typography' => array(
			'title'       => __( 'Typography', 'docspress' ),
			'description' => __( 'Choose local, zero-request font stacks and adjust the reading scale.', 'docspress' ),
		),
		'docspress_content'    => array(
			'title'       => __( 'Article labels & actions', 'docspress' ),
			'description' => __( 'Customize article labels and the WordPress and GitHub editing actions.', 'docspress' ),
		),
		'docspress_footer'     => array(
			'title'       => __( 'Footer', 'docspress' ),
			'description' => __( 'Set footer copy and an optional destination link.', 'docspress' ),
		),
	);

	$priority = 10;
	foreach ( $sections as $section_id => $section ) {
		$wp_customize->add_section(
			$section_id,
			array(
				'title'       => $section['title'],
				'description' => $section['description'],
				'panel'       => 'docspress_theme',
				'priority'    => $priority,
			)
		);
		$priority += 10;
	}

	$menu_choices = docspress_customizer_menu_choices();
	$preset_choices = docspress_design_preset_choices();
	$fields       = array(
		'docspress_design_preset' => array(
			'default' => 'docspress',
			'sanitize_callback' => 'docspress_sanitize_choice',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_presets',
			'label' => __( 'Design preset', 'docspress' ),
			'description' => __( 'Apply a complete visual recipe. Changing any individual recipe value afterward switches this selector to Custom.', 'docspress' ),
			'type' => 'select',
			'choices' => $preset_choices,
		),
		'docspress_docs_root' => array(
			'default' => 0,
			'sanitize_callback' => 'absint',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_navigation',
			'label' => __( 'Documentation root', 'docspress' ),
			'description' => __( 'Limits the automatic sidebar to this Page and its children.', 'docspress' ),
			'type' => 'dropdown-pages',
		),
		'docspress_sidebar_source' => array(
			'default' => 'page_tree',
			'sanitize_callback' => 'docspress_sanitize_choice',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_navigation',
			'label' => __( 'Sidebar source', 'docspress' ),
			'type' => 'select',
			'choices' => array(
				'page_tree' => __( 'Automatic Page hierarchy', 'docspress' ),
				'custom_menu' => __( 'WordPress navigation menu', 'docspress' ),
			),
		),
		'docspress_sidebar_menu' => array(
			'default' => 0,
			'sanitize_callback' => 'absint',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_navigation',
			'label' => __( 'Sidebar menu', 'docspress' ),
			'description' => __( 'Select a menu or use the menu assigned to Documentation sidebar.', 'docspress' ),
			'type' => 'select',
			'choices' => $menu_choices,
			'active_callback' => 'docspress_customizer_uses_custom_menu',
		),
		'docspress_sidebar_sort' => array(
			'default' => 'menu_order',
			'sanitize_callback' => 'docspress_sanitize_choice',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_navigation',
			'label' => __( 'Automatic Page order', 'docspress' ),
			'type' => 'select',
			'choices' => array(
				'menu_order' => __( 'Page order, then title', 'docspress' ),
				'title' => __( 'Title', 'docspress' ),
				'newest' => __( 'Newest first', 'docspress' ),
				'oldest' => __( 'Oldest first', 'docspress' ),
			),
			'active_callback' => 'docspress_customizer_uses_page_tree',
		),
		'docspress_sidebar_show_root' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_navigation',
			'label' => __( 'Show documentation root', 'docspress' ),
			'type' => 'checkbox',
			'active_callback' => 'docspress_customizer_uses_page_tree',
		),
		'docspress_sidebar_depth' => array(
			'default' => 0,
			'sanitize_callback' => 'docspress_sanitize_range',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_navigation',
			'label' => __( 'Maximum navigation depth', 'docspress' ),
			'description' => __( '0 shows every level.', 'docspress' ),
			'type' => 'range',
			'input_attrs' => array( 'min' => 0, 'max' => 6, 'step' => 1 ),
		),
		'docspress_sidebar_title' => array(
			'default' => __( 'Documentation', 'docspress' ),
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_navigation',
			'label' => __( 'Sidebar heading', 'docspress' ),
			'type' => 'text',
			'transport' => 'postMessage',
		),
		'docspress_show_sidebar_search' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_navigation',
			'label' => __( 'Show sidebar filter', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_search_placeholder' => array(
			'default' => __( 'Filter pages…', 'docspress' ),
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_navigation',
			'label' => __( 'Filter placeholder', 'docspress' ),
			'type' => 'text',
			'transport' => 'postMessage',
		),
		'docspress_show_version_selector' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_navigation',
			'label' => __( 'Show version selector when available', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_header_menu' => array(
			'default' => 0,
			'sanitize_callback' => 'absint',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_header',
			'label' => __( 'Header menu', 'docspress' ),
			'description' => __( 'Select a menu or use the menu assigned to Header navigation.', 'docspress' ),
			'type' => 'select',
			'choices' => $menu_choices,
		),
		'docspress_show_brand_suffix' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_header',
			'label' => __( 'Show brand suffix', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_brand_suffix' => array(
			'default' => __( 'on WordPress', 'docspress' ),
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_header',
			'label' => __( 'Brand suffix', 'docspress' ),
			'type' => 'text',
			'transport' => 'postMessage',
		),
		'docspress_show_header_search' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_command_search',
			'label' => __( 'Enable command search', 'docspress' ),
			'description' => __( 'Shows the header shortcut and enables Cmd/Ctrl+K and / keyboard access.', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_header_search_label' => array(
			'default' => __( 'Search docs', 'docspress' ),
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_command_search',
			'label' => __( 'Header shortcut label', 'docspress' ),
			'type' => 'text',
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_search_dialog_placeholder' => array(
			'default' => __( 'Search documentation…', 'docspress' ),
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_command_search',
			'label' => __( 'Search field placeholder', 'docspress' ),
			'type' => 'text',
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_search_suggested_label' => array(
			'default' => __( 'Suggested pages', 'docspress' ),
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_command_search',
			'label' => __( 'Suggested-results label', 'docspress' ),
			'type' => 'text',
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_search_no_results_label' => array(
			'default' => __( 'No documentation matched that search.', 'docspress' ),
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_command_search',
			'label' => __( 'No-results message', 'docspress' ),
			'type' => 'text',
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_search_results_limit' => array(
			'default' => 8,
			'sanitize_callback' => 'docspress_sanitize_range',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_command_search',
			'label' => __( 'Maximum results', 'docspress' ),
			'type' => 'range',
			'input_attrs' => array( 'min' => 3, 'max' => 20, 'step' => 1 ),
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_search_width' => array(
			'default' => 680,
			'sanitize_callback' => 'docspress_sanitize_range',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_command_search',
			'label' => __( 'Popup width (px)', 'docspress' ),
			'type' => 'range',
			'input_attrs' => array( 'min' => 480, 'max' => 900, 'step' => 10 ),
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_search_height' => array(
			'default' => 640,
			'sanitize_callback' => 'docspress_sanitize_range',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_command_search',
			'label' => __( 'Popup maximum height (px)', 'docspress' ),
			'type' => 'range',
			'input_attrs' => array( 'min' => 360, 'max' => 800, 'step' => 10 ),
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_search_radius_mode' => array(
			'default' => 'inherit',
			'sanitize_callback' => 'docspress_sanitize_choice',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_command_search',
			'label' => __( 'Popup corner style', 'docspress' ),
			'type' => 'select',
			'choices' => array(
				'inherit' => __( 'Follow theme', 'docspress' ),
				'custom' => __( 'Custom radius', 'docspress' ),
			),
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_search_radius' => array(
			'default' => 10,
			'sanitize_callback' => 'docspress_sanitize_range',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_command_search',
			'label' => __( 'Custom corner radius (px)', 'docspress' ),
			'description' => __( 'Used when Popup corner style is set to Custom radius.', 'docspress' ),
			'type' => 'range',
			'input_attrs' => array( 'min' => 0, 'max' => 24, 'step' => 1 ),
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_search_overlay_opacity' => array(
			'default' => 44,
			'sanitize_callback' => 'docspress_sanitize_range',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_command_search',
			'label' => __( 'Backdrop opacity (%)', 'docspress' ),
			'type' => 'range',
			'input_attrs' => array( 'min' => 0, 'max' => 80, 'step' => 1 ),
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_search_overlay_blur' => array(
			'default' => 2,
			'sanitize_callback' => 'docspress_sanitize_range',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_command_search',
			'label' => __( 'Backdrop blur (px)', 'docspress' ),
			'type' => 'range',
			'input_attrs' => array( 'min' => 0, 'max' => 12, 'step' => 1 ),
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_search_show_paths' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_command_search',
			'label' => __( 'Show result paths', 'docspress' ),
			'type' => 'checkbox',
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_search_show_excerpts' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_command_search',
			'label' => __( 'Show result excerpts', 'docspress' ),
			'type' => 'checkbox',
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_search_show_hints' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_command_search',
			'label' => __( 'Show keyboard hints', 'docspress' ),
			'type' => 'checkbox',
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_header_search',
		),
		'docspress_show_color_toggle' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_header',
			'label' => __( 'Show light/dark switcher', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_default_color_mode' => array(
			'default' => 'light',
			'sanitize_callback' => 'docspress_sanitize_choice',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_header',
			'label' => __( 'Default color mode', 'docspress' ),
			'description' => __( 'Used for every visitor while the light/dark switcher is hidden.', 'docspress' ),
			'type' => 'select',
			'choices' => array(
				'light' => __( 'Light', 'docspress' ),
				'dark' => __( 'Dark', 'docspress' ),
			),
			'active_callback' => 'docspress_customizer_hides_color_toggle',
		),
		'docspress_show_repository' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_header',
			'label' => __( 'Show repository link', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_github_url' => array(
			'default' => 'https://github.com/Automattic/docspress',
			'sanitize_callback' => 'esc_url_raw',
			'sanitize_js_callback' => 'esc_url_raw',
			'section' => 'docspress_header',
			'label' => __( 'Repository URL', 'docspress' ),
			'type' => 'url',
		),
		'docspress_article_width' => array(
			'default' => 800,
			'sanitize_callback' => 'docspress_sanitize_range',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_layout',
			'label' => __( 'Article width (px)', 'docspress' ),
			'type' => 'range',
			'input_attrs' => array( 'min' => 620, 'max' => 980, 'step' => 10 ),
			'transport' => 'postMessage',
		),
		'docspress_sidebar_width' => array(
			'default' => 300,
			'sanitize_callback' => 'docspress_sanitize_range',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_layout',
			'label' => __( 'Sidebar width (px)', 'docspress' ),
			'type' => 'range',
			'input_attrs' => array( 'min' => 230, 'max' => 360, 'step' => 2 ),
			'transport' => 'postMessage',
		),
		'docspress_toc_width' => array(
			'default' => 220,
			'sanitize_callback' => 'docspress_sanitize_range',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_layout',
			'label' => __( 'Table of contents width (px)', 'docspress' ),
			'type' => 'range',
			'input_attrs' => array( 'min' => 180, 'max' => 300, 'step' => 2 ),
			'transport' => 'postMessage',
		),
		'docspress_border_radius' => array(
			'default' => 10,
			'sanitize_callback' => 'docspress_sanitize_range',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_layout',
			'label' => __( 'Corner radius (px)', 'docspress' ),
			'type' => 'range',
			'input_attrs' => array( 'min' => 0, 'max' => 20, 'step' => 1 ),
			'transport' => 'postMessage',
		),
		'docspress_content_density' => array(
			'default' => 'comfortable',
			'sanitize_callback' => 'docspress_sanitize_choice',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_layout',
			'label' => __( 'Reading density', 'docspress' ),
			'type' => 'select',
			'choices' => array(
				'compact' => __( 'Compact', 'docspress' ),
				'comfortable' => __( 'Comfortable', 'docspress' ),
				'roomy' => __( 'Roomy', 'docspress' ),
			),
		),
		'docspress_show_toc' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_layout',
			'label' => __( 'Show “On this page”', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_show_breadcrumbs' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_layout',
			'label' => __( 'Show breadcrumbs', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_show_pagination' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_layout',
			'label' => __( 'Show previous/next navigation', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_show_edit_link' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_content',
			'label' => __( 'Show WordPress edit button', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_wordpress_edit_label' => array(
			'default' => __( 'Edit this page in WordPress', 'docspress' ),
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_content',
			'label' => __( 'WordPress button label', 'docspress' ),
			'type' => 'text',
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_wordpress_edit',
		),
		'docspress_show_github_edit_link' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_content',
			'label' => __( 'Show GitHub proposal button', 'docspress' ),
			'description' => __( 'Shown only when the Page has an exact Markdown source path from Docspress or _docspress_source_path post meta.', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_github_edit_label' => array(
			'default' => __( 'Propose changes to this page in GitHub', 'docspress' ),
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_content',
			'label' => __( 'GitHub button label', 'docspress' ),
			'type' => 'text',
			'transport' => 'postMessage',
			'active_callback' => 'docspress_customizer_shows_github_edit',
		),
		'docspress_github_edit_repository_url' => array(
			'default' => '',
			'sanitize_callback' => 'esc_url_raw',
			'sanitize_js_callback' => 'esc_url_raw',
			'section' => 'docspress_content',
			'label' => __( 'Markdown repository URL', 'docspress' ),
			'description' => __( 'Leave blank to use the repository URL configured in Header.', 'docspress' ),
			'type' => 'url',
			'active_callback' => 'docspress_customizer_shows_github_edit',
		),
		'docspress_github_edit_ref' => array(
			'default' => 'main',
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_content',
			'label' => __( 'Markdown branch or ref', 'docspress' ),
			'type' => 'text',
			'active_callback' => 'docspress_customizer_shows_github_edit',
		),
		'docspress_show_summary' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_layout',
			'label' => __( 'Show Page excerpt', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_ui_font' => array(
			'default' => 'avenir',
			'sanitize_callback' => 'docspress_sanitize_choice',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_typography',
			'label' => __( 'Interface font', 'docspress' ),
			'description' => __( 'Includes a local rounded system stack, bundled Inter and EB Garamond, and the WordPress.com Recoleta option.', 'docspress' ),
			'type' => 'select',
			'choices' => array(
				'rounded' => __( 'Rounded system / Arial Rounded', 'docspress' ),
				'avenir' => __( 'Avenir / Century Gothic', 'docspress' ),
				'inter' => __( 'Inter / system sans', 'docspress' ),
				'humanist' => __( 'Optima / Candara', 'docspress' ),
				'geometric' => __( 'Futura / Trebuchet', 'docspress' ),
				'editorial' => __( 'Gill Sans / Calibri', 'docspress' ),
			),
			'transport' => 'postMessage',
		),
		'docspress_content_font' => array(
			'default' => 'charter',
			'sanitize_callback' => 'docspress_sanitize_choice',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_typography',
			'label' => __( 'Reading font', 'docspress' ),
			'type' => 'select',
			'choices' => array(
				'charter' => __( 'Charter / Sitka', 'docspress' ),
				'iowan' => __( 'Iowan / Palatino', 'docspress' ),
				'georgia' => __( 'Georgia / Times', 'docspress' ),
				'sans' => __( 'Use interface font', 'docspress' ),
			),
			'transport' => 'postMessage',
		),
		'docspress_heading_font' => array(
			'default' => 'interface',
			'sanitize_callback' => 'docspress_sanitize_choice',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_typography',
			'label' => __( 'Heading font', 'docspress' ),
			'type' => 'select',
			'choices' => array(
				'interface' => __( 'Use interface font', 'docspress' ),
				'inter' => __( 'Inter / system sans', 'docspress' ),
				'eb_garamond' => __( 'EB Garamond / classic serif', 'docspress' ),
				'recoleta' => __( 'Recoleta / WordPress.com display', 'docspress' ),
				'charter' => __( 'Charter / Sitka', 'docspress' ),
				'iowan' => __( 'Iowan / Palatino', 'docspress' ),
			),
			'transport' => 'postMessage',
		),
		'docspress_content_font_size' => array(
			'default' => 17,
			'sanitize_callback' => 'docspress_sanitize_range',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_typography',
			'label' => __( 'Reading size (px)', 'docspress' ),
			'type' => 'range',
			'input_attrs' => array( 'min' => 15, 'max' => 21, 'step' => 1 ),
			'transport' => 'postMessage',
		),
		'docspress_heading_weight' => array(
			'default' => 750,
			'sanitize_callback' => 'docspress_sanitize_range',
			'sanitize_js_callback' => 'absint',
			'section' => 'docspress_typography',
			'label' => __( 'Heading weight', 'docspress' ),
			'type' => 'range',
			'input_attrs' => array( 'min' => 300, 'max' => 900, 'step' => 50 ),
			'transport' => 'postMessage',
		),
		'docspress_show_kicker' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_content',
			'label' => __( 'Show article kicker', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_kicker_label' => array(
			'default' => __( 'Guide', 'docspress' ),
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_content',
			'label' => __( 'Article kicker text', 'docspress' ),
			'type' => 'text',
			'transport' => 'postMessage',
		),
		'docspress_toc_title' => array(
			'default' => __( 'On this page', 'docspress' ),
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_content',
			'label' => __( 'Table of contents title', 'docspress' ),
			'type' => 'text',
			'transport' => 'postMessage',
		),
		'docspress_show_footer' => array(
			'default' => true,
			'sanitize_callback' => 'docspress_sanitize_checkbox',
			'sanitize_js_callback' => 'rest_sanitize_boolean',
			'section' => 'docspress_footer',
			'label' => __( 'Show footer', 'docspress' ),
			'type' => 'checkbox',
		),
		'docspress_footer_text' => array(
			'default' => __( 'Documentation powered by WordPress · {year}', 'docspress' ),
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_footer',
			'label' => __( 'Footer text', 'docspress' ),
			'description' => __( 'Use {year} and {site_title} as dynamic placeholders.', 'docspress' ),
			'type' => 'text',
			'transport' => 'postMessage',
		),
		'docspress_footer_link_label' => array(
			'default' => '',
			'sanitize_callback' => 'sanitize_text_field',
			'sanitize_js_callback' => 'sanitize_text_field',
			'section' => 'docspress_footer',
			'label' => __( 'Footer link label', 'docspress' ),
			'type' => 'text',
		),
		'docspress_footer_link_url' => array(
			'default' => '',
			'sanitize_callback' => 'esc_url_raw',
			'sanitize_js_callback' => 'esc_url_raw',
			'section' => 'docspress_footer',
			'label' => __( 'Footer link URL', 'docspress' ),
			'type' => 'url',
		),
	);

	foreach ( $fields as $setting_id => $field ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'              => $field['default'],
				'sanitize_callback'    => $field['sanitize_callback'],
				'sanitize_js_callback' => $field['sanitize_js_callback'],
				'transport'            => isset( $field['transport'] ) ? $field['transport'] : 'refresh',
			)
		);

		$control = array(
			'label'       => $field['label'],
			'section'     => $field['section'],
			'type'        => $field['type'],
			'description' => isset( $field['description'] ) ? $field['description'] : '',
		);

		foreach ( array( 'choices', 'input_attrs', 'active_callback' ) as $optional_key ) {
			if ( isset( $field[ $optional_key ] ) ) {
				$control[ $optional_key ] = $field[ $optional_key ];
			}
		}

		$wp_customize->add_control( $setting_id, $control );
	}

	$color_fields = array(
		'docspress_accent_color'      => array( '#3858e9', __( 'Light: accent', 'docspress' ) ),
		'docspress_accent_strong'     => array( '#2145d8', __( 'Light: accent links', 'docspress' ) ),
		'docspress_accent_soft'       => array( '#eef1ff', __( 'Light: selected surface', 'docspress' ) ),
		'docspress_paper_color'       => array( '#ffffff', __( 'Light: article background', 'docspress' ) ),
		'docspress_canvas_color'      => array( '#f8f9fb', __( 'Light: sidebar background', 'docspress' ) ),
		'docspress_ink_color'         => array( '#171a22', __( 'Light: heading color', 'docspress' ) ),
		'docspress_copy_color'        => array( '#3d4351', __( 'Light: body text', 'docspress' ) ),
		'docspress_muted_color'       => array( '#6f7685', __( 'Light: muted text', 'docspress' ) ),
		'docspress_line_color'        => array( '#e4e7ec', __( 'Light: borders', 'docspress' ) ),
		'docspress_line_strong_color' => array( '#cfd4dc', __( 'Light: strong borders', 'docspress' ) ),
		'docspress_dark_accent'       => array( '#8198ff', __( 'Dark: accent', 'docspress' ) ),
		'docspress_dark_strong'       => array( '#a8b7ff', __( 'Dark: accent links', 'docspress' ) ),
		'docspress_dark_soft'         => array( '#20294c', __( 'Dark: selected surface', 'docspress' ) ),
		'docspress_dark_paper'        => array( '#171b23', __( 'Dark: article background', 'docspress' ) ),
		'docspress_dark_canvas'       => array( '#11141b', __( 'Dark: sidebar background', 'docspress' ) ),
		'docspress_dark_ink'          => array( '#f3f5f8', __( 'Dark: heading color', 'docspress' ) ),
		'docspress_dark_copy'         => array( '#c6cbd5', __( 'Dark: body text', 'docspress' ) ),
		'docspress_dark_muted'        => array( '#929aab', __( 'Dark: muted text', 'docspress' ) ),
		'docspress_dark_line'         => array( '#303744', __( 'Dark: borders', 'docspress' ) ),
		'docspress_dark_line_strong'  => array( '#434c5b', __( 'Dark: strong borders', 'docspress' ) ),
	);

	foreach ( $color_fields as $setting_id => $color ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $color[0],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$setting_id,
				array(
					'label'   => $color[1],
					'section' => 'docspress_colors',
				)
			)
		);
	}
}
add_action( 'customize_register', 'docspress_customize_register' );

/**
 * Return approved font stacks.
 *
 * @return array<string,string>
 */
function docspress_font_stacks() {
	return array(
		'rounded'   => 'ui-rounded, "Arial Rounded MT Bold", "Avenir Next", "Trebuchet MS", sans-serif',
		'avenir'    => '"Avenir Next", Avenir, "Century Gothic", Futura, sans-serif',
		'inter'     => 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif',
		'humanist'  => 'Optima, Candara, "Noto Sans", sans-serif',
		'geometric' => 'Futura, "Trebuchet MS", "Century Gothic", sans-serif',
		'editorial' => '"Gill Sans", "Gill Sans MT", Calibri, sans-serif',
		'eb_garamond' => '"EB Garamond", "Iowan Old Style", Palatino, "Palatino Linotype", serif',
		'recoleta'  => 'Recoleta, "EB Garamond", Georgia, serif',
		'charter'   => 'Charter, "Bitstream Charter", "Sitka Text", Georgia, serif',
		'iowan'     => '"Iowan Old Style", Palatino, "Palatino Linotype", serif',
		'georgia'   => 'Georgia, "Times New Roman", serif',
	);
}

/**
 * Print sanitized Customizer values as CSS variables.
 */
function docspress_customizer_css() {
	$fonts        = docspress_font_stacks();
	$ui_key       = get_theme_mod( 'docspress_ui_font', 'avenir' );
	$content_key  = get_theme_mod( 'docspress_content_font', 'charter' );
	$heading_key  = get_theme_mod( 'docspress_heading_font', 'interface' );
	$ui_font      = isset( $fonts[ $ui_key ] ) ? $fonts[ $ui_key ] : $fonts['avenir'];
	$content_font = 'sans' === $content_key ? $ui_font : ( isset( $fonts[ $content_key ] ) ? $fonts[ $content_key ] : $fonts['charter'] );
	$heading_font = 'interface' === $heading_key ? $ui_font : ( isset( $fonts[ $heading_key ] ) ? $fonts[ $heading_key ] : $ui_font );
	$search_width = min( 900, max( 480, absint( get_theme_mod( 'docspress_search_width', 680 ) ) ) );
	$search_height = min( 800, max( 360, absint( get_theme_mod( 'docspress_search_height', 640 ) ) ) );
	$search_radius_mode = get_theme_mod( 'docspress_search_radius_mode', 'inherit' );
	$search_radius_mode = in_array( $search_radius_mode, array( 'inherit', 'custom' ), true ) ? $search_radius_mode : 'inherit';
	$search_radius = min( 24, absint( get_theme_mod( 'docspress_search_radius', 10 ) ) );
	$search_overlay_opacity = min( 80, absint( get_theme_mod( 'docspress_search_overlay_opacity', 44 ) ) );
	$search_overlay_blur = min( 12, absint( get_theme_mod( 'docspress_search_overlay_blur', 2 ) ) );
	$colors       = array(
		'--dp-blue'      => array( 'docspress_accent_color', '#3858e9' ),
		'--dp-blue-dark' => array( 'docspress_accent_strong', '#2145d8' ),
		'--dp-blue-soft' => array( 'docspress_accent_soft', '#eef1ff' ),
		'--dp-paper'     => array( 'docspress_paper_color', '#ffffff' ),
		'--dp-canvas'    => array( 'docspress_canvas_color', '#f8f9fb' ),
		'--dp-ink'       => array( 'docspress_ink_color', '#171a22' ),
		'--dp-copy'      => array( 'docspress_copy_color', '#3d4351' ),
		'--dp-muted'     => array( 'docspress_muted_color', '#6f7685' ),
		'--dp-line'      => array( 'docspress_line_color', '#e4e7ec' ),
		'--dp-line-strong' => array( 'docspress_line_strong_color', '#cfd4dc' ),
	);
	$dark_colors  = array(
		'--dp-blue'      => array( 'docspress_dark_accent', '#8198ff' ),
		'--dp-blue-dark' => array( 'docspress_dark_strong', '#a8b7ff' ),
		'--dp-blue-soft' => array( 'docspress_dark_soft', '#20294c' ),
		'--dp-paper'     => array( 'docspress_dark_paper', '#171b23' ),
		'--dp-canvas'    => array( 'docspress_dark_canvas', '#11141b' ),
		'--dp-ink'       => array( 'docspress_dark_ink', '#f3f5f8' ),
		'--dp-copy'      => array( 'docspress_dark_copy', '#c6cbd5' ),
		'--dp-muted'     => array( 'docspress_dark_muted', '#929aab' ),
		'--dp-line'      => array( 'docspress_dark_line', '#303744' ),
		'--dp-line-strong' => array( 'docspress_dark_line_strong', '#434c5b' ),
	);
	$root_rules   = array(
		'--dp-content-width:' . absint( get_theme_mod( 'docspress_article_width', 800 ) ) . 'px',
		'--dp-sidebar-width:' . absint( get_theme_mod( 'docspress_sidebar_width', 300 ) ) . 'px',
		'--dp-toc-width:' . absint( get_theme_mod( 'docspress_toc_width', 220 ) ) . 'px',
		'--dp-content-font-size:' . absint( get_theme_mod( 'docspress_content_font_size', 17 ) ) . 'px',
		'--dp-heading-weight:' . absint( get_theme_mod( 'docspress_heading_weight', 750 ) ),
		'--dp-radius:' . absint( get_theme_mod( 'docspress_border_radius', 10 ) ) . 'px',
		'--dp-search-width:' . $search_width . 'px',
		'--dp-search-height:' . $search_height . 'px',
		'--dp-search-radius:' . ( 'custom' === $search_radius_mode ? $search_radius . 'px' : 'var(--dp-radius)' ),
		'--dp-search-overlay-opacity:' . $search_overlay_opacity . '%',
		'--dp-search-overlay-blur:' . $search_overlay_blur . 'px',
		'--dp-font-ui:' . $ui_font,
		'--dp-font-copy:' . $content_font,
		'--dp-font-heading:' . $heading_font,
	);

	foreach ( $colors as $variable => $color ) {
		$value        = sanitize_hex_color( get_theme_mod( $color[0], $color[1] ) );
		$root_rules[] = $variable . ':' . ( $value ? $value : $color[1] );
	}

	$dark_rules = array();
	foreach ( $dark_colors as $variable => $color ) {
		$value        = sanitize_hex_color( get_theme_mod( $color[0], $color[1] ) );
		$dark_rules[] = $variable . ':' . ( $value ? $value : $color[1] );
	}

	echo '<style id="docspress-customizer-css">:root{' . implode( ';', $root_rules ) . '}html[data-theme="dark"]{' . implode( ';', $dark_rules ) . '}</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Values are sanitized colors, integers, and fixed font maps.
}
add_action( 'wp_head', 'docspress_customizer_css', 90 );

/**
 * Add settings-derived layout classes.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function docspress_customizer_body_classes( $classes ) {
	$density = get_theme_mod( 'docspress_content_density', 'comfortable' );
	$density = in_array( $density, array( 'compact', 'comfortable', 'roomy' ), true ) ? $density : 'comfortable';
	$classes[] = 'docspress-density-' . $density;
	$preset = get_theme_mod( 'docspress_design_preset', 'docspress' );
	$valid_presets = array_merge( array_keys( docspress_design_preset_manifests() ), array( 'custom' ) );
	$preset = in_array( $preset, $valid_presets, true ) ? $preset : 'custom';
	$classes[] = 'docspress-preset-' . $preset;
	$classes[] = 'docspress-sidebar-' . ( 'custom_menu' === get_theme_mod( 'docspress_sidebar_source', 'page_tree' ) ? 'menu' : 'pages' );

	if ( ! get_theme_mod( 'docspress_show_toc', true ) ) {
		$classes[] = 'docspress-no-toc';
	}

	if ( ! get_theme_mod( 'docspress_search_show_paths', true ) ) {
		$classes[] = 'docspress-search-hide-paths';
	}

	if ( ! get_theme_mod( 'docspress_search_show_excerpts', true ) ) {
		$classes[] = 'docspress-search-hide-excerpts';
	}

	if ( ! get_theme_mod( 'docspress_search_show_hints', true ) ) {
		$classes[] = 'docspress-search-hide-hints';
	}

	return $classes;
}
add_filter( 'body_class', 'docspress_customizer_body_classes' );

/**
 * Make Jetpack and WordPress image sizing aware of the configured article width.
 */
function docspress_set_content_width() {
	$GLOBALS['content_width'] = absint( get_theme_mod( 'docspress_article_width', 800 ) );
}
add_action( 'after_setup_theme', 'docspress_set_content_width', 0 );

/**
 * Enqueue live-preview bindings only inside the Customizer preview.
 */
function docspress_customize_preview_assets() {
	$theme = wp_get_theme();
	wp_enqueue_script(
		'docspress-customizer-preview',
		get_theme_file_uri( 'assets/js/customizer-preview.js' ),
		array( 'customize-preview' ),
		$theme->get( 'Version' ),
		true
	);
}
add_action( 'customize_preview_init', 'docspress_customize_preview_assets' );

/**
 * Enqueue the preset recipe controller in the Customizer controls pane.
 */
function docspress_customize_controls_assets() {
	$theme   = wp_get_theme();
	$presets = docspress_design_presets();
	$setting_ids = array();

	foreach ( $presets as $recipe ) {
		$setting_ids = array_merge( $setting_ids, array_keys( $recipe ) );
	}

	wp_enqueue_script(
		'docspress-customizer-controls',
		get_theme_file_uri( 'assets/js/customizer-controls.js' ),
		array( 'customize-controls' ),
		$theme->get( 'Version' ),
		true
	);
	wp_localize_script(
		'docspress-customizer-controls',
		'docspressPresetData',
		array(
			'presets'            => $presets,
			'controlledSettings' => array_values( array_unique( $setting_ids ) ),
		)
	);
}
add_action( 'customize_controls_enqueue_scripts', 'docspress_customize_controls_assets' );
