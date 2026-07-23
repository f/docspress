( function ( blocks, shared ) {
	'use strict';

	const { registerBlockType } = blocks;
	const {
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
		__,
		el,
		languages,
		presetClass,
		themeStyle,
		useBlockProps,
		useState
	} = shared;
	const icon = el(
		'svg',
		{ viewBox: '0 0 24 24', width: 24, height: 24, fill: 'none', stroke: 'currentColor', strokeWidth: 1.8 },
		el( 'path', { d: 'M4 5h16v14H4zM4 9h16M8 5v4m4-4v4' } )
	);

	registerBlockType( 'docspress/code-tabs', {
		apiVersion: 3,
		title: __( 'DocsPress: Code Tabs', 'docspress-blocks' ),
		description: __( 'Switchable code examples for languages, package managers, or platforms.', 'docspress-blocks' ),
		category: 'text',
		icon,
		keywords: [ __( 'tabs', 'docspress-blocks' ), __( 'code', 'docspress-blocks' ), __( 'examples', 'docspress-blocks' ) ],
		attributes: {
			tabs: {
				type: 'array',
				default: [
					{ label: 'JavaScript', language: 'javascript', filename: 'example.js', code: 'const docs = await publish();' },
					{ label: 'PHP', language: 'php', filename: 'example.php', code: '$docs = docspress_publish();' }
				]
			},
			showLineNumbers: { type: 'boolean', default: true },
			caption: { type: 'string', default: '' }
		},
		supports: { anchor: true, html: false },
		edit: function CodeTabsEdit( { attributes, setAttributes } ) {
			const tabs = Array.isArray( attributes.tabs ) && attributes.tabs.length ? attributes.tabs : [ { label: 'Example', language: 'plaintext', filename: '', code: '' } ];
			const [ selected, setSelected ] = useState( 0 );
			const activeIndex = Math.min( selected, tabs.length - 1 );
			const active = tabs[ activeIndex ];
			const blockProps = useBlockProps( {
				className: `docspress-code-tabs docspress-code-tabs--editor ${ presetClass }`,
				style: themeStyle
			} );

			function updateTab( key, value ) {
				setAttributes( {
					tabs: tabs.map( ( tab, index ) => index === activeIndex ? { ...tab, [ key ]: value } : tab )
				} );
			}

			function addTab() {
				if ( tabs.length >= 8 ) {
					return;
				}
				const next = [ ...tabs, { label: __( 'New example', 'docspress-blocks' ), language: 'plaintext', filename: '', code: '' } ];
				setAttributes( { tabs: next } );
				setSelected( next.length - 1 );
			}

			function removeTab() {
				if ( tabs.length <= 1 ) {
					return;
				}
				const next = tabs.filter( ( tab, index ) => index !== activeIndex );
				setAttributes( { tabs: next } );
				setSelected( Math.max( 0, activeIndex - 1 ) );
			}

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Active tab', 'docspress-blocks' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Tab label', 'docspress-blocks' ),
							value: active.label,
							onChange: ( label ) => updateTab( 'label', label )
						} ),
						el( SelectControl, {
							label: __( 'Language', 'docspress-blocks' ),
							value: active.language,
							options: languages,
							onChange: ( language ) => updateTab( 'language', language )
						} ),
						el( TextControl, {
							label: __( 'Filename or label', 'docspress-blocks' ),
							value: active.filename,
							onChange: ( filename ) => updateTab( 'filename', filename )
						} ),
						el(
							ButtonGroup,
							null,
							el( Button, { variant: 'secondary', onClick: addTab, disabled: tabs.length >= 8 }, __( 'Add tab', 'docspress-blocks' ) ),
							el( Button, { variant: 'tertiary', isDestructive: true, onClick: removeTab, disabled: tabs.length <= 1 }, __( 'Remove tab', 'docspress-blocks' ) )
						)
					),
					el( CodeSettings, { attributes, setAttributes, includeHighlights: false } )
				),
				el(
					'figure',
					blockProps,
					el(
						'div',
						{ className: 'docspress-code-tabs__surface' },
						el(
							'div',
							{ className: 'docspress-code-tabs__header' },
							el(
								'div',
								{ className: 'docspress-code-tabs__list', role: 'tablist' },
								...tabs.map( ( tab, index ) => el(
									'button',
									{
										key: index,
										type: 'button',
										role: 'tab',
										'aria-selected': index === activeIndex,
										className: 'docspress-code-tabs__tab' + ( index === activeIndex ? ' is-active' : '' ),
										onClick: () => setSelected( index )
									},
									tab.label || __( 'Untitled', 'docspress-blocks' )
								) )
							),
							el(
								'div',
								{ className: 'docspress-code-tabs__tools' },
								el( 'span', { className: 'docspress-code-tabs__filename' }, active.filename || active.language )
							)
						),
						el(
							'div',
							{ className: 'docspress-code-tabs__panel' },
							el(
								'div',
								{ className: 'docspress-code__surface' + ( attributes.showLineNumbers ? ' has-line-numbers' : '' ) },
								el( PlainText, {
									className: 'docspress-code__editor',
									value: active.code,
									onChange: ( code ) => updateTab( 'code', code ),
									placeholder: __( 'Write this tab’s code…', 'docspress-blocks' ),
									'aria-label': __( 'Code', 'docspress-blocks' )
								} )
							)
						)
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
