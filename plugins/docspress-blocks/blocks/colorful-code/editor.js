( function ( blocks, shared ) {
	'use strict';

	const { registerBlockType } = blocks;
	const {
		CodeSettings,
		Fragment,
		InspectorControls,
		PanelBody,
		PlainText,
		RichText,
		SelectControl,
		TextControl,
		__,
		el,
		languages,
		presetClass,
		themeStyle,
		useBlockProps
	} = shared;
	const icon = el(
		'svg',
		{ viewBox: '0 0 24 24', width: 24, height: 24, fill: 'none', stroke: 'currentColor', strokeWidth: 1.8 },
		el( 'path', { d: 'm8 9-3 3 3 3M16 9l3 3-3 3M14 5l-4 14' } )
	);

	registerBlockType( 'docspress/colorful-code', {
		apiVersion: 3,
		title: __( 'DocsPress: Colorful Code', 'docspress-blocks' ),
		description: __( 'A polished code sample that follows the active theme preset, with line highlights and one-click copy.', 'docspress-blocks' ),
		category: 'text',
		icon,
		keywords: [ __( 'syntax', 'docspress-blocks' ), __( 'snippet', 'docspress-blocks' ), __( 'developer', 'docspress-blocks' ) ],
		attributes: {
			language: { type: 'string', default: 'javascript' },
			filename: { type: 'string', default: '' },
			code: { type: 'string', default: 'const hello = "DocsPress";\nconsole.log( hello );' },
			highlightedLines: { type: 'string', default: '' },
			showLineNumbers: { type: 'boolean', default: true },
			caption: { type: 'string', default: '' }
		},
		supports: { anchor: true, html: false },
		edit: function ColorfulCodeEdit( { attributes, setAttributes } ) {
			const blockProps = useBlockProps( {
				className: `docspress-code docspress-code--editor ${ presetClass }`,
				style: themeStyle
			} );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Snippet', 'docspress-blocks' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Language', 'docspress-blocks' ),
							value: attributes.language,
							options: languages,
							onChange: ( language ) => setAttributes( { language } )
						} ),
						el( TextControl, {
							label: __( 'Filename or label', 'docspress-blocks' ),
							value: attributes.filename,
							onChange: ( filename ) => setAttributes( { filename } )
						} )
					),
					el( CodeSettings, { attributes, setAttributes } )
				),
				el(
					'figure',
					blockProps,
					el(
						'div',
						{ className: 'docspress-code__surface' + ( attributes.showLineNumbers ? ' has-line-numbers' : '' ) },
						el(
							'div',
							{ className: 'docspress-code__bar' },
							el( 'span', { className: 'docspress-code__language' }, attributes.language ),
							el( 'span', { className: 'docspress-code__filename' }, attributes.filename || attributes.language )
						),
						el( PlainText, {
							className: 'docspress-code__editor',
							value: attributes.code,
							onChange: ( code ) => setAttributes( { code } ),
							placeholder: __( 'Paste or write code…', 'docspress-blocks' ),
							'aria-label': __( 'Code', 'docspress-blocks' )
						} )
					),
					el( RichText, {
						tagName: 'figcaption',
						className: 'docspress-code__caption',
						value: attributes.caption,
						onChange: ( caption ) => setAttributes( { caption } ),
						allowedFormats: [],
						placeholder: __( 'Optional caption…', 'docspress-blocks' )
					} )
				)
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.docspressBlocksEditor );
