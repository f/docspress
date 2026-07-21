( function ( blocks, shared ) {
	'use strict';

	const { registerBlockType } = blocks;
	const { Fragment, InspectorControls, PanelBody, PlainText, RichText, SelectControl, TextControl, ToggleControl, __, el, presetClass, useBlockProps } = shared;
	const icon = el(
		'svg',
		{ viewBox: '0 0 24 24', width: 24, height: 24, fill: 'none', stroke: 'currentColor', strokeWidth: 1.8 },
		el( 'path', { d: 'm12 3 1.5 5 4.5 1.5-4.5 1.5-1.5 5-1.5-5L6 9.5 10.5 8 12 3Z' } ),
		el( 'path', { d: 'm18 15 .7 2.3L21 18l-2.3.7L18 21l-.7-2.3L15 18l2.3-.7L18 15Z' } )
	);
	const modeLabels = { chat: 'Chat', code: 'Code', ask: 'Ask', plan: 'Plan' };

	function contextKind( label ) {
		if ( label.startsWith( '@' ) ) return 'mention';
		if ( label.startsWith( '#' ) ) return 'image';
		if ( /^https?:\/\//i.test( label ) ) return 'url';
		return 'file';
	}

	function contextItems( value ) {
		return value.split( ',' ).map( ( item ) => item.trim() ).filter( Boolean ).slice( 0, 12 );
	}

	registerBlockType( 'docspress/prompt', {
		apiVersion: 3,
		title: __( 'DocsPress: Prompt', 'docspress-blocks' ),
		description: __( 'Show a copyable AI prompt with model, mode, and context metadata without making it look like source code.', 'docspress-blocks' ),
		category: 'text',
		icon,
		keywords: [ __( 'AI', 'docspress-blocks' ), __( 'LLM', 'docspress-blocks' ), __( 'composer', 'docspress-blocks' ) ],
		attributes: {
			prompt: { type: 'string', default: 'Review this synchronization logic and propose a safer retry strategy. Return a short plan before writing code.' },
			model: { type: 'string', default: 'GPT-5' },
			mode: { type: 'string', default: 'code' },
			thinking: { type: 'boolean', default: true },
			context: { type: 'string', default: '@repository, src/sync.js, docs/' },
			caption: { type: 'string', default: 'Prompt example' }
		},
		supports: { anchor: true, html: false },
		edit: function PromptEdit( { attributes, setAttributes } ) {
			const blockProps = useBlockProps( {
				className: `docspress-prompt docspress-prompt--editor ${ presetClass }`,
				'data-mode': attributes.mode,
				'aria-label': __( 'AI prompt example', 'docspress-blocks' )
			} );
			const chips = contextItems( attributes.context );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Prompt settings', 'docspress-blocks' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Model', 'docspress-blocks' ),
							value: attributes.model,
							onChange: ( model ) => setAttributes( { model } )
						} ),
						el( SelectControl, {
							label: __( 'Mode', 'docspress-blocks' ),
							value: attributes.mode,
							options: [
								{ label: __( 'Chat', 'docspress-blocks' ), value: 'chat' },
								{ label: __( 'Code', 'docspress-blocks' ), value: 'code' },
								{ label: __( 'Ask', 'docspress-blocks' ), value: 'ask' },
								{ label: __( 'Plan', 'docspress-blocks' ), value: 'plan' }
							],
							onChange: ( mode ) => setAttributes( { mode } )
						} ),
						el( ToggleControl, {
							label: __( 'Show Thinking indicator', 'docspress-blocks' ),
							checked: attributes.thinking,
							onChange: ( thinking ) => setAttributes( { thinking } )
						} ),
						el( TextControl, {
							label: __( 'Context', 'docspress-blocks' ),
							help: __( 'Comma-separated @mentions, #images, URLs, or file paths.', 'docspress-blocks' ),
							value: attributes.context,
							onChange: ( context ) => setAttributes( { context } )
						} ),
						el( TextControl, {
							label: __( 'Caption', 'docspress-blocks' ),
							value: attributes.caption,
							onChange: ( caption ) => setAttributes( { caption } )
						} )
					)
				),
				el(
					'section',
					blockProps,
					el(
						'header',
						{ className: 'docspress-prompt__header' },
						el( 'span', { className: 'docspress-prompt__mark', 'aria-hidden': true }, '✦' ),
						el( 'span', { className: 'docspress-prompt__label' }, __( 'Prompt', 'docspress-blocks' ) ),
						el( 'span', { className: 'docspress-prompt__model' }, attributes.model || __( 'Model', 'docspress-blocks' ) ),
						el( 'span', { className: 'docspress-prompt__mode' }, modeLabels[ attributes.mode ] || attributes.mode ),
						attributes.thinking && el( 'span', { className: 'docspress-prompt__thinking' }, el( 'span', { 'aria-hidden': true }, '✦' ), __( 'Thinking', 'docspress-blocks' ) )
					),
					el(
						'div',
						{ className: 'docspress-prompt__composer' },
						el( PlainText, {
							className: 'docspress-prompt__text',
							value: attributes.prompt,
							onChange: ( prompt ) => setAttributes( { prompt } ),
							placeholder: __( 'Write the prompt exactly as a reader should use it…', 'docspress-blocks' ),
							'aria-label': __( 'Prompt text', 'docspress-blocks' )
						} ),
						chips.length > 0 && el(
							'div',
							{ className: 'docspress-prompt__context', 'aria-label': __( 'Prompt context', 'docspress-blocks' ) },
							chips.map( ( chip ) => el( 'span', { key: chip, className: `docspress-prompt__chip docspress-prompt__chip--${ contextKind( chip ) }` }, chip ) )
						),
						el(
							'footer',
							{ className: 'docspress-prompt__footer' },
							el( RichText, {
								tagName: 'span',
								className: 'docspress-prompt__caption',
								value: attributes.caption,
								onChange: ( caption ) => setAttributes( { caption } ),
								allowedFormats: [],
								placeholder: __( 'Optional caption…', 'docspress-blocks' )
							} ),
							el( 'span', { className: 'docspress-prompt__copy docspress-prompt__copy--preview' }, '⧉ ', __( 'Copy prompt', 'docspress-blocks' ) )
						)
					)
				)
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.docspressBlocksEditor );
