( function ( blocks, shared ) {
	'use strict';

	const { registerBlockType } = blocks;
	const {
		Button,
		Fragment,
		InspectorControls,
		PanelBody,
		PanelColorSettings,
		RichText,
		SelectControl,
		TextControl,
		ToggleControl,
		__,
		el,
		presetClass,
		themeStyle,
		useBlockProps
	} = shared;
	const defaultPaths = [
		{
			title: 'I already have Markdown docs',
			description: 'Connect an existing docs folder to WordPress and begin with a safe draft sync.',
			url: '/docs/publish-existing-docs/',
			cta: 'Publish existing docs',
			icon: 'MD',
			accent: 'blue',
			newTab: false
		},
		{
			title: 'I need to create docs',
			description: 'Generate source-grounded documentation with AI, review it, then publish it.',
			url: '/docs/create-docs-with-ai/',
			cta: 'Create docs with AI',
			icon: 'AI',
			accent: 'gold',
			newTab: false
		}
	];
	const icon = el(
		'svg',
		{ viewBox: '0 0 24 24', width: 24, height: 24, fill: 'none', stroke: 'currentColor', strokeWidth: 1.8 },
		el( 'path', { d: 'M4 5.5h6.5V12H4V5.5Zm9.5 6.5H20v6.5h-6.5V12ZM10.5 8.75h3m-1.5-1.5 1.5 1.5-1.5 1.5M10.5 15.25h3' } )
	);

	function normalizedPaths( paths ) {
		return Array.isArray( paths ) && paths.length ? paths : defaultPaths;
	}

	registerBlockType( 'docspress/audience-paths', {
		apiVersion: 3,
		title: __( 'DocsPress: Audience Paths', 'docspress-blocks' ),
		description: __( 'Guide different kinds of readers into dedicated documentation roots.', 'docspress-blocks' ),
		category: 'design',
		icon,
		keywords: [ __( 'audience', 'docspress-blocks' ), __( 'onboarding', 'docspress-blocks' ), __( 'docs navigation', 'docspress-blocks' ) ],
		attributes: {
			eyebrow: { type: 'string', default: 'Choose a starting point' },
			title: { type: 'string', default: 'Where are your docs today?' },
			description: { type: 'string', default: 'Follow the path that matches your repository.' },
			paths: { type: 'array', default: defaultPaths },
			columns: { type: 'number', default: 2 },
			tone: { type: 'string', default: 'theme' },
			textAlign: { type: 'string', default: 'left' },
			showNumbers: { type: 'boolean', default: false },
			panelColor: { type: 'string', default: '' },
			textColor: { type: 'string', default: '' },
			accentColor: { type: 'string', default: '' }
		},
		supports: { align: [ 'wide', 'full' ], anchor: true, html: false },
		edit: function AudiencePathsEdit( { attributes, setAttributes } ) {
			const paths = normalizedPaths( attributes.paths );
			const classes = [
				'docspress-audience-paths',
				`docspress-audience-paths--${ attributes.tone }`,
				`docspress-audience-paths--columns-${ attributes.columns }`,
				`docspress-audience-paths--align-${ attributes.textAlign }`,
				attributes.showNumbers ? '' : 'docspress-audience-paths--no-numbers',
				'docspress-audience-paths--editor',
				presetClass
			].filter( Boolean ).join( ' ' );
			const styles = { ...themeStyle };

			if ( attributes.panelColor ) styles[ '--db-paths-panel' ] = attributes.panelColor;
			if ( attributes.textColor ) {
				styles[ '--db-paths-heading' ] = attributes.textColor;
				styles[ '--db-paths-copy' ] = attributes.textColor;
			}
			if ( attributes.accentColor ) styles[ '--db-paths-accent' ] = attributes.accentColor;

			const blockProps = useBlockProps( { className: classes, style: styles } );
			const updatePath = ( index, key, value ) => {
				const nextPaths = paths.map( ( path, pathIndex ) => pathIndex === index ? { ...path, [ key ]: value } : path );
				setAttributes( { paths: nextPaths } );
			};
			const removePath = ( index ) => setAttributes( { paths: paths.filter( ( path, pathIndex ) => pathIndex !== index ) } );
			const addPath = () => {
				const accents = [ 'blue', 'gold', 'coral', 'green' ];
				setAttributes( {
					paths: [
						...paths,
						{
							title: __( 'New audience', 'docspress-blocks' ),
							description: __( 'Explain what this reader will find in their documentation path.', 'docspress-blocks' ),
							url: '/docs/',
							cta: __( 'Open this path', 'docspress-blocks' ),
							icon: '→',
							accent: accents[ paths.length % accents.length ],
							newTab: false
						}
					]
				} );
			};

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Layout', 'docspress-blocks' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Color style', 'docspress-blocks' ),
							value: attributes.tone,
							options: [
								{ label: __( 'Theme', 'docspress-blocks' ), value: 'theme' },
								{ label: __( 'Subtle', 'docspress-blocks' ), value: 'paper' },
								{ label: __( 'Inverse', 'docspress-blocks' ), value: 'ink' },
								{ label: __( 'Blueprint', 'docspress-blocks' ), value: 'blueprint' }
							],
							onChange: ( tone ) => setAttributes( { tone } )
						} ),
						el( SelectControl, {
							label: __( 'Columns', 'docspress-blocks' ),
							value: String( attributes.columns ),
							options: [
								{ label: '1', value: '1' },
								{ label: '2', value: '2' },
								{ label: '3', value: '3' }
							],
							onChange: ( columns ) => setAttributes( { columns: Number( columns ) } )
						} ),
						el( SelectControl, {
							label: __( 'Text alignment', 'docspress-blocks' ),
							value: attributes.textAlign,
							options: [
								{ label: __( 'Left', 'docspress-blocks' ), value: 'left' },
								{ label: __( 'Center', 'docspress-blocks' ), value: 'center' }
							],
							onChange: ( textAlign ) => setAttributes( { textAlign } )
						} ),
						el( ToggleControl, {
							label: __( 'Show path numbers', 'docspress-blocks' ),
							checked: attributes.showNumbers,
							onChange: ( showNumbers ) => setAttributes( { showNumbers } )
						} )
					),
					...paths.map( ( path, index ) => el(
						PanelBody,
						{
							key: `path-settings-${ index }`,
							title: path.title || `${ __( 'Path', 'docspress-blocks' ) } ${ index + 1 }`,
							initialOpen: false
						},
						el( TextControl, {
							label: __( 'Destination URL', 'docspress-blocks' ),
							value: path.url || '',
							type: 'url',
							onChange: ( url ) => updatePath( index, 'url', url )
						} ),
						el( TextControl, {
							label: __( 'Symbol', 'docspress-blocks' ),
							help: __( 'Use a short symbol, emoji, or abbreviation.', 'docspress-blocks' ),
							value: path.icon || '',
							onChange: ( iconValue ) => updatePath( index, 'icon', iconValue )
						} ),
						el( SelectControl, {
							label: __( 'Accent', 'docspress-blocks' ),
							value: path.accent || 'blue',
							options: [
								{ label: __( 'Blue', 'docspress-blocks' ), value: 'blue' },
								{ label: __( 'Gold', 'docspress-blocks' ), value: 'gold' },
								{ label: __( 'Coral', 'docspress-blocks' ), value: 'coral' },
								{ label: __( 'Green', 'docspress-blocks' ), value: 'green' }
							],
							onChange: ( accent ) => updatePath( index, 'accent', accent )
						} ),
						el( ToggleControl, {
							label: __( 'Open in a new tab', 'docspress-blocks' ),
							checked: Boolean( path.newTab ),
							onChange: ( newTab ) => updatePath( index, 'newTab', newTab )
						} ),
						paths.length > 1 && el( Button, {
							onClick: () => removePath( index ),
							variant: 'tertiary',
							isDestructive: true
						}, __( 'Remove path', 'docspress-blocks' ) )
					) ),
					paths.length < 6 && el(
						PanelBody,
						{ title: __( 'Add an audience', 'docspress-blocks' ), initialOpen: true },
						el( Button, { onClick: addPath, variant: 'secondary' }, __( 'Add audience path', 'docspress-blocks' ) )
					),
					el(
						PanelColorSettings,
						{
							title: __( 'Custom colors', 'docspress-blocks' ),
							initialOpen: false,
							colorSettings: [
								{ value: attributes.panelColor, onChange: ( panelColor ) => setAttributes( { panelColor: panelColor || '' } ), label: __( 'Panel', 'docspress-blocks' ) },
								{ value: attributes.textColor, onChange: ( textColor ) => setAttributes( { textColor: textColor || '' } ), label: __( 'Text', 'docspress-blocks' ) },
								{ value: attributes.accentColor, onChange: ( accentColor ) => setAttributes( { accentColor: accentColor || '' } ), label: __( 'Primary accent', 'docspress-blocks' ) }
							]
						}
					)
				),
				el(
					'section',
					blockProps,
					el(
						'header',
						{ className: 'docspress-audience-paths__header' },
						el( RichText, {
							tagName: 'p',
							className: 'docspress-audience-paths__eyebrow',
							value: attributes.eyebrow,
							onChange: ( eyebrow ) => setAttributes( { eyebrow } ),
							allowedFormats: [],
							placeholder: __( 'Choose a starting point…', 'docspress-blocks' )
						} ),
						el(
							'div',
							{ className: 'docspress-audience-paths__heading-group' },
							el( RichText, {
								tagName: 'h2',
								className: 'docspress-audience-paths__title',
								value: attributes.title,
								onChange: ( title ) => setAttributes( { title } ),
								allowedFormats: [],
								placeholder: __( 'Where are your docs today?', 'docspress-blocks' )
							} ),
							el( RichText, {
								tagName: 'p',
								className: 'docspress-audience-paths__description',
								value: attributes.description,
								onChange: ( description ) => setAttributes( { description } ),
								allowedFormats: [],
								placeholder: __( 'Explain how these paths differ…', 'docspress-blocks' )
							} )
						)
					),
					el(
						'div',
						{ className: 'docspress-audience-paths__grid', role: 'list' },
						...paths.map( ( path, index ) => el(
							'div',
							{
								key: `path-${ index }`,
								className: 'docspress-audience-paths__item',
								role: 'listitem'
							},
							el(
								'div',
								{ className: `docspress-audience-paths__card docspress-audience-paths__card--${ path.accent || 'blue' }` },
								el( 'span', { className: 'docspress-audience-paths__number', 'aria-hidden': true }, String( index + 1 ).padStart( 2, '0' ) ),
								el( RichText, {
									tagName: 'span',
									className: 'docspress-audience-paths__icon',
									value: path.icon || '',
									onChange: ( iconValue ) => updatePath( index, 'icon', iconValue ),
									allowedFormats: [],
									'aria-label': __( 'Path symbol', 'docspress-blocks' ),
									placeholder: '→'
								} ),
								el(
									'span',
									{ className: 'docspress-audience-paths__card-copy' },
									el( RichText, {
										tagName: 'span',
										className: 'docspress-audience-paths__card-title',
										value: path.title || '',
										onChange: ( title ) => updatePath( index, 'title', title ),
										allowedFormats: [],
										placeholder: __( 'Audience name…', 'docspress-blocks' )
									} ),
									el( RichText, {
										tagName: 'span',
										className: 'docspress-audience-paths__card-description',
										value: path.description || '',
										onChange: ( description ) => updatePath( index, 'description', description ),
										allowedFormats: [],
										placeholder: __( 'What will this reader find?', 'docspress-blocks' )
									} )
								),
								el( RichText, {
									tagName: 'span',
									className: 'docspress-audience-paths__cta',
									value: path.cta || '',
									onChange: ( cta ) => updatePath( index, 'cta', cta ),
									allowedFormats: [],
									placeholder: __( 'Open this path…', 'docspress-blocks' )
								} ),
								el( 'code', { className: 'docspress-audience-paths__route' }, path.url || __( 'Add destination URL', 'docspress-blocks' ) )
							)
						) )
					)
				)
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.docspressBlocksEditor );
