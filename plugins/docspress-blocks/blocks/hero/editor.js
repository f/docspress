( function ( blocks, shared ) {
	'use strict';

	const { registerBlockType } = blocks;
	const {
		Button,
		Fragment,
		InspectorControls,
		MediaUpload,
		MediaUploadCheck,
		PanelBody,
		PanelColorSettings,
		RangeControl,
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
	const icon = el(
		'svg',
		{ viewBox: '0 0 24 24', width: 24, height: 24, fill: 'none', stroke: 'currentColor', strokeWidth: 1.8 },
		el( 'rect', { x: 3, y: 5, width: 18, height: 14, rx: 2 } ),
		el( 'path', { d: 'M7 10h6M7 14h4m5-5 2 2-2 2' } )
	);

	function mediaPicker( attributes, setAttributes, compact ) {
		return el(
			MediaUploadCheck,
			null,
			el( MediaUpload, {
				allowedTypes: [ 'image' ],
				value: attributes.mediaId,
				onSelect: ( media ) => setAttributes( {
					mediaId: media.id || 0,
					mediaUrl: media.url || '',
					mediaAlt: media.alt || attributes.mediaAlt
				} ),
				render: ( { open } ) => el(
					Button,
					{
						onClick: open,
						variant: compact ? 'secondary' : 'primary',
						className: compact ? '' : 'docspress-hero__media-placeholder'
					},
					attributes.mediaUrl ? __( 'Replace image', 'docspress-blocks' ) : __( 'Choose hero image', 'docspress-blocks' )
				)
			} )
		);
	}

	registerBlockType( 'docspress/hero', {
		apiVersion: 3,
		title: __( 'DocsPress: Hero', 'docspress-blocks' ),
		description: __( 'A fully configurable homepage hero with actions, media, layout controls, and responsive presentation.', 'docspress-blocks' ),
		category: 'design',
		icon,
		keywords: [ __( 'homepage', 'docspress-blocks' ), __( 'banner', 'docspress-blocks' ), __( 'landing', 'docspress-blocks' ) ],
		attributes: {
			eyebrow: { type: 'string', default: 'Documentation, publishing, and community' },
			title: { type: 'string', default: 'Docs that stay connected to your GitHub repo' },
			description: { type: 'string', default: 'Write beside your code. Publish a WordPress experience that guides every reader to the docs written for them.' },
			primaryLabel: { type: 'string', default: 'Browse documentation' },
			primaryUrl: { type: 'string', default: '' },
			primaryNewTab: { type: 'boolean', default: false },
			secondaryLabel: { type: 'string', default: 'Latest updates' },
			secondaryUrl: { type: 'string', default: '' },
			secondaryNewTab: { type: 'boolean', default: false },
			mediaId: { type: 'number', default: 0 },
			mediaUrl: { type: 'string', default: '' },
			mediaAlt: { type: 'string', default: '' },
			visualLabel: { type: 'string', default: '' },
			layout: { type: 'string', default: 'split' },
			mediaPosition: { type: 'string', default: 'right' },
			mediaWidth: { type: 'number', default: 44 },
			imageScale: { type: 'number', default: 100 },
			height: { type: 'string', default: 'standard' },
			tone: { type: 'string', default: 'theme' },
			textAlign: { type: 'string', default: 'left' },
			showGrid: { type: 'boolean', default: false },
			showOrbit: { type: 'boolean', default: false },
			panelColor: { type: 'string', default: '' },
			visualColor: { type: 'string', default: '' },
			textColor: { type: 'string', default: '' },
			accentColor: { type: 'string', default: '' }
		},
		supports: { align: [ 'wide', 'full' ], anchor: true, html: false },
		edit: function HeroEdit( { attributes, setAttributes } ) {
			const hasVisual = Boolean( attributes.mediaUrl );
			const classes = [
				'docspress-hero',
				`docspress-hero--${ attributes.tone }`,
				`docspress-hero--layout-${ attributes.layout }`,
				`docspress-hero--media-${ attributes.mediaPosition }`,
				`docspress-hero--height-${ attributes.height }`,
				`docspress-hero--align-${ attributes.textAlign }`,
				attributes.showGrid ? '' : 'docspress-hero--no-grid',
				attributes.showOrbit ? '' : 'docspress-hero--no-orbit',
				hasVisual ? '' : 'docspress-hero--no-visual',
				'docspress-hero--editor',
				presetClass
			].filter( Boolean ).join( ' ' );
			const styles = {
				...themeStyle,
				'--db-hero-media-width': `${ attributes.mediaWidth }%`,
				'--db-hero-image-scale': `${ attributes.imageScale }%`
			};

			if ( attributes.panelColor ) styles[ '--db-hero-panel' ] = attributes.panelColor;
			if ( attributes.visualColor ) styles[ '--db-hero-visual' ] = attributes.visualColor;
			if ( attributes.textColor ) {
				styles[ '--db-hero-heading' ] = attributes.textColor;
				styles[ '--db-hero-copy' ] = attributes.textColor;
			}
			if ( attributes.accentColor ) styles[ '--db-hero-accent' ] = attributes.accentColor;

			const blockProps = useBlockProps( { className: classes, style: styles } );
			const image = attributes.mediaUrl
				? el(
					'figure',
					{ className: 'docspress-hero__media' },
					el( 'img', {
						className: 'docspress-hero__image',
						src: attributes.mediaUrl,
						alt: attributes.mediaAlt || ''
					} )
				)
				: mediaPicker( attributes, setAttributes, false );
			const visual = hasVisual || ! attributes.mediaUrl
				? el(
					'div',
					{ className: 'docspress-hero__visual' },
					attributes.visualLabel && el(
						'span',
						{ className: 'docspress-hero__visual-label', 'aria-hidden': true },
						attributes.visualLabel
					),
					image
				)
				: null;

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
							label: __( 'Composition', 'docspress-blocks' ),
							value: attributes.layout,
							options: [
								{ label: __( 'Split panel', 'docspress-blocks' ), value: 'split' },
								{ label: __( 'Editorial spotlight', 'docspress-blocks' ), value: 'editorial' }
							],
							onChange: ( layout ) => setAttributes( { layout } )
						} ),
						el( SelectControl, {
							label: __( 'Color style', 'docspress-blocks' ),
							value: attributes.tone,
							options: [
								{ label: __( 'Theme', 'docspress-blocks' ), value: 'theme' },
								{ label: __( 'Midnight', 'docspress-blocks' ), value: 'midnight' },
								{ label: __( 'Paper', 'docspress-blocks' ), value: 'paper' },
								{ label: __( 'Brand color', 'docspress-blocks' ), value: 'brand' }
							],
							onChange: ( tone ) => setAttributes( { tone } )
						} ),
						el( SelectControl, {
							label: __( 'Image side', 'docspress-blocks' ),
							value: attributes.mediaPosition,
							options: [
								{ label: __( 'Right', 'docspress-blocks' ), value: 'right' },
								{ label: __( 'Left', 'docspress-blocks' ), value: 'left' }
							],
							onChange: ( mediaPosition ) => setAttributes( { mediaPosition } )
						} ),
						el( SelectControl, {
							label: __( 'Hero height', 'docspress-blocks' ),
							value: attributes.height,
							options: [
								{ label: __( 'Compact', 'docspress-blocks' ), value: 'compact' },
								{ label: __( 'Standard', 'docspress-blocks' ), value: 'standard' },
								{ label: __( 'Tall', 'docspress-blocks' ), value: 'tall' }
							],
							onChange: ( height ) => setAttributes( { height } )
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
						el( RangeControl, {
							label: __( 'Visual width', 'docspress-blocks' ),
							value: attributes.mediaWidth,
							min: 34,
							max: 58,
							step: 1,
							onChange: ( mediaWidth ) => setAttributes( { mediaWidth } )
						} ),
						el( ToggleControl, {
							label: __( 'Show grid', 'docspress-blocks' ),
							checked: attributes.showGrid,
							onChange: ( showGrid ) => setAttributes( { showGrid } )
						} ),
						el( ToggleControl, {
							label: __( 'Show orbit', 'docspress-blocks' ),
							checked: attributes.showOrbit,
							onChange: ( showOrbit ) => setAttributes( { showOrbit } )
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Image', 'docspress-blocks' ), initialOpen: true },
						mediaPicker( attributes, setAttributes, true ),
						attributes.mediaUrl && el( Button, {
							onClick: () => setAttributes( { mediaId: 0, mediaUrl: '', mediaAlt: '' } ),
							variant: 'tertiary',
							isDestructive: true
						}, __( 'Remove image', 'docspress-blocks' ) ),
						el( TextControl, {
							label: __( 'External image URL', 'docspress-blocks' ),
							help: __( 'Choose a Media Library image above, or paste an image URL.', 'docspress-blocks' ),
							value: attributes.mediaUrl,
							type: 'url',
							onChange: ( mediaUrl ) => setAttributes( { mediaId: 0, mediaUrl } )
						} ),
						el( TextControl, {
							label: __( 'Alternative text', 'docspress-blocks' ),
							value: attributes.mediaAlt,
							onChange: ( mediaAlt ) => setAttributes( { mediaAlt } )
						} ),
						el( TextControl, {
							label: __( 'Backdrop label', 'docspress-blocks' ),
							help: __( 'Optional decorative text behind the image in the editorial composition.', 'docspress-blocks' ),
							value: attributes.visualLabel,
							onChange: ( visualLabel ) => setAttributes( { visualLabel } )
						} ),
						el( RangeControl, {
							label: __( 'Image scale', 'docspress-blocks' ),
							value: attributes.imageScale,
							min: 60,
							max: 120,
							step: 5,
							onChange: ( imageScale ) => setAttributes( { imageScale } )
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Primary action', 'docspress-blocks' ), initialOpen: false },
						el( TextControl, {
							label: __( 'Label', 'docspress-blocks' ),
							value: attributes.primaryLabel,
							onChange: ( primaryLabel ) => setAttributes( { primaryLabel } )
						} ),
						el( TextControl, {
							label: __( 'URL', 'docspress-blocks' ),
							value: attributes.primaryUrl,
							type: 'url',
							onChange: ( primaryUrl ) => setAttributes( { primaryUrl } )
						} ),
						el( ToggleControl, {
							label: __( 'Open in a new tab', 'docspress-blocks' ),
							checked: attributes.primaryNewTab,
							onChange: ( primaryNewTab ) => setAttributes( { primaryNewTab } )
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Secondary action', 'docspress-blocks' ), initialOpen: false },
						el( TextControl, {
							label: __( 'Label', 'docspress-blocks' ),
							value: attributes.secondaryLabel,
							onChange: ( secondaryLabel ) => setAttributes( { secondaryLabel } )
						} ),
						el( TextControl, {
							label: __( 'URL', 'docspress-blocks' ),
							value: attributes.secondaryUrl,
							type: 'url',
							onChange: ( secondaryUrl ) => setAttributes( { secondaryUrl } )
						} ),
						el( ToggleControl, {
							label: __( 'Open in a new tab', 'docspress-blocks' ),
							checked: attributes.secondaryNewTab,
							onChange: ( secondaryNewTab ) => setAttributes( { secondaryNewTab } )
						} )
					),
					el(
						PanelColorSettings,
						{
							title: __( 'Custom colors', 'docspress-blocks' ),
							initialOpen: false,
							colorSettings: [
								{ value: attributes.panelColor, onChange: ( panelColor ) => setAttributes( { panelColor: panelColor || '' } ), label: __( 'Text panel', 'docspress-blocks' ) },
								{ value: attributes.visualColor, onChange: ( visualColor ) => setAttributes( { visualColor: visualColor || '' } ), label: __( 'Visual panel', 'docspress-blocks' ) },
								{ value: attributes.textColor, onChange: ( textColor ) => setAttributes( { textColor: textColor || '' } ), label: __( 'Text', 'docspress-blocks' ) },
								{ value: attributes.accentColor, onChange: ( accentColor ) => setAttributes( { accentColor: accentColor || '' } ), label: __( 'Accent', 'docspress-blocks' ) }
							]
						}
					)
				),
				el(
					'section',
					blockProps,
					el(
						'div',
						{ className: 'docspress-hero__copy' },
						el( RichText, {
							tagName: 'p',
							className: 'docspress-hero__eyebrow',
							value: attributes.eyebrow,
							onChange: ( eyebrow ) => setAttributes( { eyebrow } ),
							allowedFormats: [],
							placeholder: __( 'Hero eyebrow…', 'docspress-blocks' )
						} ),
						el( RichText, {
							tagName: 'h1',
							className: 'docspress-hero__title',
							value: attributes.title,
							onChange: ( title ) => setAttributes( { title } ),
							allowedFormats: [],
							placeholder: __( 'Hero title…', 'docspress-blocks' )
						} ),
						el( RichText, {
							tagName: 'p',
							className: 'docspress-hero__description',
							value: attributes.description,
							onChange: ( description ) => setAttributes( { description } ),
							placeholder: __( 'Explain the value of this site…', 'docspress-blocks' )
						} ),
						( attributes.primaryLabel || attributes.secondaryLabel ) && el(
							'div',
							{ className: 'docspress-hero__actions' },
							attributes.primaryLabel && el( RichText, {
								tagName: 'span',
								className: 'docspress-hero__button docspress-hero__button--primary',
								value: attributes.primaryLabel,
								onChange: ( primaryLabel ) => setAttributes( { primaryLabel } ),
								allowedFormats: [],
								'aria-label': __( 'Primary action label', 'docspress-blocks' ),
								placeholder: __( 'Primary action…', 'docspress-blocks' )
							} ),
							attributes.secondaryLabel && el( RichText, {
								tagName: 'span',
								className: 'docspress-hero__button docspress-hero__button--secondary',
								value: attributes.secondaryLabel,
								onChange: ( secondaryLabel ) => setAttributes( { secondaryLabel } ),
								allowedFormats: [],
								'aria-label': __( 'Secondary action label', 'docspress-blocks' ),
								placeholder: __( 'Secondary action…', 'docspress-blocks' )
							} )
						)
					),
					visual
				)
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.docspressBlocksEditor );
