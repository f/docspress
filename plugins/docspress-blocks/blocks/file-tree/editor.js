( function ( blocks, shared ) {
	'use strict';

	const { registerBlockType } = blocks;
	const { Fragment, InspectorControls, PanelBody, PlainText, RichText, TextControl, __, el, presetClass, useBlockProps } = shared;
	const icon = el(
		'svg',
		{ viewBox: '0 0 24 24', width: 24, height: 24, fill: 'none', stroke: 'currentColor', strokeWidth: 1.8 },
		el( 'path', { d: 'M4 5h6l2 2h8v12H4V5Zm4 6h8m-8 4h5' } )
	);

	registerBlockType( 'docspress/file-tree', {
		apiVersion: 3,
		title: __( 'DocsPress: File Tree', 'docspress-blocks' ),
		description: __( 'Show a repository or project structure with indentation-aware file and folder entries.', 'docspress-blocks' ),
		category: 'text',
		icon,
		keywords: [ __( 'files', 'docspress-blocks' ), __( 'folder', 'docspress-blocks' ), __( 'structure', 'docspress-blocks' ) ],
		attributes: {
			root: { type: 'string', default: 'project/' },
			tree: { type: 'string', default: 'docs/\n  getting-started.md\n  api/\n    endpoints.md\npackage.json' },
			caption: { type: 'string', default: '' }
		},
		supports: { anchor: true, html: false },
		edit: function FileTreeEdit( { attributes, setAttributes } ) {
			const blockProps = useBlockProps( { className: `docspress-file-tree docspress-file-tree--editor ${ presetClass }` } );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'File tree', 'docspress-blocks' ), initialOpen: true },
						el( TextControl, { label: __( 'Root label', 'docspress-blocks' ), value: attributes.root, onChange: ( root ) => setAttributes( { root } ) } )
					)
				),
				el(
					'figure',
					blockProps,
					el(
						'div',
						{ className: 'docspress-file-tree__bar' },
						el( 'span', { className: 'docspress-file-tree__root-icon', 'aria-hidden': true }, '▱' ),
						el( 'code', null, attributes.root ),
						el( 'span', null, __( 'File tree', 'docspress-blocks' ) )
					),
					el(
						'div',
						{ className: 'docspress-file-tree__entries' },
						el( PlainText, {
							value: attributes.tree,
							onChange: ( tree ) => setAttributes( { tree } ),
							placeholder: 'docs/\n  getting-started.md\n  api/',
							'aria-label': __( 'Indented file tree', 'docspress-blocks' )
						} )
					),
					el( RichText, {
						tagName: 'figcaption',
						className: 'docspress-file-tree__caption',
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
