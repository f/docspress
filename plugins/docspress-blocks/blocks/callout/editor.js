( function ( blocks, shared ) {
	'use strict';

	const { registerBlockType } = blocks;
	const {
		Fragment,
		InspectorControls,
		PanelBody,
		RichText,
		SelectControl,
		ToggleControl,
		__,
		el,
		presetClass,
		themeStyle,
		useBlockProps
	} = shared;
	const icon = el(
		'svg',
		{ viewBox: '0 0 24 24', width: 24, height: 24, fill: 'none', stroke: 'currentColor', strokeWidth: 1.8 },
		el( 'path', { d: 'M12 8v5m0 3h.01M5.6 19h12.8a2 2 0 0 0 1.7-3L13.7 5a2 2 0 0 0-3.4 0L3.9 16a2 2 0 0 0 1.7 3Z' } )
	);

	registerBlockType( 'docspress/callout', {
		apiVersion: 3,
		title: __( 'DocsPress: Callout', 'docspress-blocks' ),
		description: __( 'A semantic note, tip, warning, danger, or success message that can optionally collapse.', 'docspress-blocks' ),
		category: 'text',
		icon,
		keywords: [ __( 'notice', 'docspress-blocks' ), __( 'admonition', 'docspress-blocks' ), __( 'warning', 'docspress-blocks' ) ],
		attributes: {
			tone: { type: 'string', default: 'note' },
			title: { type: 'string', default: 'Good to know' },
			content: { type: 'string', default: '<p>Add the detail readers need at exactly the right moment.</p>' },
			collapsible: { type: 'boolean', default: false },
			open: { type: 'boolean', default: true }
		},
		supports: { anchor: true, html: false },
		edit: function CalloutEdit( { attributes, setAttributes } ) {
			const blockProps = useBlockProps( {
				className: `docspress-callout docspress-callout--${ attributes.tone } docspress-callout--editor ${ presetClass }`,
				style: themeStyle
			} );
			const toneIcon = el( 'span', { className: 'docspress-callout__icon', 'aria-hidden': true }, attributes.tone.slice( 0, 1 ).toUpperCase() );
			const title = el( RichText, {
				tagName: attributes.collapsible ? 'span' : 'div',
				className: 'docspress-callout__title',
				value: attributes.title,
				onChange: ( nextTitle ) => setAttributes( { title: nextTitle } ),
				allowedFormats: [],
				placeholder: __( 'Callout title…', 'docspress-blocks' )
			} );
			const content = el( RichText, {
				tagName: 'div',
				multiline: 'p',
				className: 'docspress-callout__content',
				value: attributes.content,
				onChange: ( nextContent ) => setAttributes( { content: nextContent } ),
				placeholder: __( 'Helpful context…', 'docspress-blocks' )
			} );
			const preview = attributes.collapsible
				? el(
					'details',
					{ ...blockProps, open: attributes.open ? true : undefined },
					el(
						'summary',
						null,
						toneIcon,
						title,
						el( 'span', { className: 'docspress-callout__chevron', 'aria-hidden': true }, '⌄' )
					),
					content
				)
				: el( 'aside', blockProps, toneIcon, el( 'div', null, title, content ) );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Callout settings', 'docspress-blocks' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Tone', 'docspress-blocks' ),
							value: attributes.tone,
							options: [
								{ label: __( 'Note', 'docspress-blocks' ), value: 'note' },
								{ label: __( 'Tip', 'docspress-blocks' ), value: 'tip' },
								{ label: __( 'Warning', 'docspress-blocks' ), value: 'warning' },
								{ label: __( 'Danger', 'docspress-blocks' ), value: 'danger' },
								{ label: __( 'Success', 'docspress-blocks' ), value: 'success' }
							],
							onChange: ( tone ) => setAttributes( { tone } )
						} ),
						el( ToggleControl, {
							label: __( 'Allow readers to collapse', 'docspress-blocks' ),
							checked: attributes.collapsible,
							onChange: ( collapsible ) => setAttributes( { collapsible } )
						} ),
						attributes.collapsible && el( ToggleControl, {
							label: __( 'Open by default', 'docspress-blocks' ),
							checked: attributes.open,
							onChange: ( open ) => setAttributes( { open } )
						} )
					)
				),
				preview
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.docspressBlocksEditor );
