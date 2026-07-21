( function ( blocks, shared ) {
	'use strict';

	const { registerBlockType } = blocks;
	const { Fragment, InspectorControls, PanelBody, RichText, SelectControl, TextControl, __, el, presetClass, useBlockProps } = shared;
	const icon = el(
		'svg',
		{ viewBox: '0 0 24 24', width: 24, height: 24, fill: 'none', stroke: 'currentColor', strokeWidth: 1.8 },
		el( 'circle', { cx: 12, cy: 12, r: 9 } ),
		el( 'path', { d: 'm8 12 2.5 2.5L16 9' } )
	);

	registerBlockType( 'docspress/result', {
		apiVersion: 3,
		title: __( 'DocsPress: Result', 'docspress-blocks' ),
		description: __( 'A compact, scannable outcome for commands, checks, builds, and verification steps.', 'docspress-blocks' ),
		category: 'text',
		icon,
		keywords: [ __( 'output', 'docspress-blocks' ), __( 'status', 'docspress-blocks' ), __( 'verification', 'docspress-blocks' ) ],
		attributes: {
			status: { type: 'string', default: 'success' },
			title: { type: 'string', default: 'Deployment completed' },
			content: { type: 'string', default: '<p>All documentation pages are up to date.</p>' },
			meta: { type: 'string', default: '12 pages · 1.8s' }
		},
		supports: { anchor: true, html: false },
		edit: function ResultEdit( { attributes, setAttributes } ) {
			const blockProps = useBlockProps( {
				className: `docspress-result docspress-result--${ attributes.status } docspress-result--editor ${ presetClass }`
			} );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Result', 'docspress-blocks' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Status', 'docspress-blocks' ),
							value: attributes.status,
							options: [
								{ label: __( 'Success', 'docspress-blocks' ), value: 'success' },
								{ label: __( 'Neutral', 'docspress-blocks' ), value: 'neutral' },
								{ label: __( 'Warning', 'docspress-blocks' ), value: 'warning' },
								{ label: __( 'Error', 'docspress-blocks' ), value: 'error' }
							],
							onChange: ( status ) => setAttributes( { status } )
						} ),
						el( TextControl, { label: __( 'Metadata', 'docspress-blocks' ), value: attributes.meta, onChange: ( meta ) => setAttributes( { meta } ) } )
					)
				),
				el(
					'section',
					blockProps,
					el( 'span', { className: 'docspress-result__icon', 'aria-hidden': true }, attributes.status === 'success' ? '✓' : attributes.status === 'error' ? '×' : attributes.status === 'warning' ? '!' : '–' ),
					el(
						'div',
						{ className: 'docspress-result__body' },
						el( 'div', { className: 'docspress-result__kicker' }, attributes.status ),
						el( RichText, {
							tagName: 'div',
							className: 'docspress-result__title',
							value: attributes.title,
							onChange: ( title ) => setAttributes( { title } ),
							allowedFormats: [],
							placeholder: __( 'Result title…', 'docspress-blocks' )
						} ),
						el( RichText, {
							tagName: 'div',
							multiline: 'p',
							className: 'docspress-result__content',
							value: attributes.content,
							onChange: ( content ) => setAttributes( { content } ),
							placeholder: __( 'What happened…', 'docspress-blocks' )
						} )
					),
					el( 'div', { className: 'docspress-result__meta' }, attributes.meta )
				)
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.docspressBlocksEditor );
