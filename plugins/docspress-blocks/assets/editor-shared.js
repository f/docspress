( function ( blockEditor, components, element, i18n ) {
	'use strict';

	const { InspectorControls, PlainText, RichText, useBlockProps } = blockEditor;
	const { Button, ButtonGroup, PanelBody, SelectControl, TextControl, ToggleControl } = components;
	const { Fragment, createElement: el, useState } = element;
	const { __ } = i18n;
	const designPreset = window.docspressBlocksSettings && window.docspressBlocksSettings.preset
		? window.docspressBlocksSettings.preset.replace( /[^a-z0-9-]/g, '' )
		: 'custom';
	const presetClass = `docspress-blocks--preset-${ designPreset }`;

	const languages = [
		{ label: 'Bash', value: 'bash' },
		{ label: 'CSS', value: 'css' },
		{ label: 'HTML', value: 'html' },
		{ label: 'JavaScript', value: 'javascript' },
		{ label: 'JSON', value: 'json' },
		{ label: 'JSX', value: 'jsx' },
		{ label: 'Markdown', value: 'markdown' },
		{ label: 'PHP', value: 'php' },
		{ label: __( 'Plain text', 'docspress-blocks' ), value: 'plaintext' },
		{ label: 'Python', value: 'python' },
		{ label: 'Shell', value: 'shell' },
		{ label: 'SQL', value: 'sql' },
		{ label: 'TSX', value: 'tsx' },
		{ label: 'TypeScript', value: 'typescript' },
		{ label: 'YAML', value: 'yaml' }
	];

	function CodeSettings( { attributes, setAttributes, includeHighlights = true } ) {
		return el(
			PanelBody,
			{ title: __( 'Code display', 'docspress-blocks' ), initialOpen: true },
			el( ToggleControl, {
				label: __( 'Show line numbers', 'docspress-blocks' ),
				checked: attributes.showLineNumbers,
				onChange: ( showLineNumbers ) => setAttributes( { showLineNumbers } )
			} ),
			includeHighlights && el( TextControl, {
				label: __( 'Highlighted lines', 'docspress-blocks' ),
				help: __( 'Use commas and ranges, for example 2,4-6.', 'docspress-blocks' ),
				value: attributes.highlightedLines,
				onChange: ( highlightedLines ) => setAttributes( { highlightedLines } )
			} ),
			el( TextControl, {
				label: __( 'Caption', 'docspress-blocks' ),
				value: attributes.caption,
				onChange: ( caption ) => setAttributes( { caption } )
			} )
		);
	}

	window.docspressBlocksEditor = {
		Button,
		ButtonGroup,
		CodeSettings,
		Fragment,
		InspectorControls,
		PanelBody,
		PlainText,
		RichText,
		SelectControl,
		TextControl,
		ToggleControl,
		__,
		el,
		languages,
		presetClass,
		useBlockProps,
		useState
	};
} )( window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n );
