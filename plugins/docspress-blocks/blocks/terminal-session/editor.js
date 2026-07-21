( function ( blocks, shared ) {
	'use strict';

	const { registerBlockType } = blocks;
	const { Fragment, InspectorControls, PanelBody, PlainText, TextControl, __, el, presetClass, useBlockProps } = shared;
	const icon = el(
		'svg',
		{ viewBox: '0 0 24 24', width: 24, height: 24, fill: 'none', stroke: 'currentColor', strokeWidth: 1.8 },
		el( 'rect', { x: 3, y: 4, width: 18, height: 16, rx: 2 } ),
		el( 'path', { d: 'm7 9 3 3-3 3m6 0h4' } )
	);

	registerBlockType( 'docspress/terminal-session', {
		apiVersion: 3,
		title: __( 'DocsPress: Terminal Session', 'docspress-blocks' ),
		description: __( 'A copyable command with realistic terminal output, without pretending the output is editable code.', 'docspress-blocks' ),
		category: 'text',
		icon,
		keywords: [ __( 'command', 'docspress-blocks' ), __( 'shell', 'docspress-blocks' ), __( 'CLI', 'docspress-blocks' ) ],
		attributes: {
			title: { type: 'string', default: 'Terminal' },
			shell: { type: 'string', default: 'bash' },
			prompt: { type: 'string', default: '$' },
			command: { type: 'string', default: 'npx docspress publish ./docs' },
			output: { type: 'string', default: '✓ Read 12 documents\n✓ Published 12 WordPress pages' }
		},
		supports: { anchor: true, html: false },
		edit: function TerminalSessionEdit( { attributes, setAttributes } ) {
			const blockProps = useBlockProps( { className: `docspress-terminal docspress-terminal--editor ${ presetClass }` } );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Terminal', 'docspress-blocks' ), initialOpen: true },
						el( TextControl, { label: __( 'Title', 'docspress-blocks' ), value: attributes.title, onChange: ( title ) => setAttributes( { title } ) } ),
						el( TextControl, { label: __( 'Shell label', 'docspress-blocks' ), value: attributes.shell, onChange: ( shell ) => setAttributes( { shell } ) } ),
						el( TextControl, { label: __( 'Prompt', 'docspress-blocks' ), value: attributes.prompt, onChange: ( prompt ) => setAttributes( { prompt } ) } )
					)
				),
				el(
					'figure',
					blockProps,
					el(
						'div',
						{ className: 'docspress-terminal__bar' },
						el( 'span', { className: 'docspress-terminal__icon', 'aria-hidden': true }, '›_' ),
						el( 'span', { className: 'docspress-terminal__title' }, attributes.title ),
						el( 'span', { className: 'docspress-terminal__shell' }, attributes.shell )
					),
					el(
						'div',
						{ className: 'docspress-terminal__body' },
						el(
							'div',
							{ className: 'docspress-terminal__command' },
							el( 'span', { className: 'docspress-terminal__prompt', 'aria-hidden': true }, attributes.prompt ),
							el( PlainText, {
								value: attributes.command,
								onChange: ( command ) => setAttributes( { command } ),
								placeholder: __( 'Command…', 'docspress-blocks' ),
								'aria-label': __( 'Command', 'docspress-blocks' )
							} )
						),
						el( PlainText, {
							className: 'docspress-terminal__output',
							value: attributes.output,
							onChange: ( output ) => setAttributes( { output } ),
							placeholder: __( 'Command output…', 'docspress-blocks' ),
							'aria-label': __( 'Command output', 'docspress-blocks' )
						} )
					)
				)
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.docspressBlocksEditor );
