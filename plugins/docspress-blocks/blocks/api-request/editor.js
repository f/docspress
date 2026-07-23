( function ( blocks, shared ) {
	'use strict';

	const { registerBlockType } = blocks;
	const { Fragment, InspectorControls, PanelBody, PlainText, SelectControl, TextControl, __, el, presetClass, themeStyle, useBlockProps } = shared;
	const icon = el(
		'svg',
		{ viewBox: '0 0 24 24', width: 24, height: 24, fill: 'none', stroke: 'currentColor', strokeWidth: 1.8 },
		el( 'path', { d: 'M4 7h16M4 17h16M8 4 4 7l4 3m8 4 4 3-4 3' } )
	);

	function PayloadEditor( { label, value, placeholder, onChange, modifier, format } ) {
		const formatLabel = format === 'headers' ? __( 'Key: value', 'docspress-blocks' ) : format.toUpperCase();
		return el(
			'section',
			{ className: `docspress-api__payload docspress-api__payload--${ modifier }`, 'data-docspress-api-format': format },
			el(
				'div',
				{ className: 'docspress-api__payload-label' },
				el( 'span', null, label ),
				el( 'span', { className: 'docspress-api__format' }, formatLabel )
			),
			el( PlainText, { value, onChange, placeholder, 'aria-label': label } )
		);
	}

	registerBlockType( 'docspress/api-request', {
		apiVersion: 3,
		title: __( 'DocsPress: API Request / Response', 'docspress-blocks' ),
		description: __( 'Document an endpoint, request headers and body, and the exact response in one structured example.', 'docspress-blocks' ),
		category: 'text',
		icon,
		keywords: [ __( 'REST', 'docspress-blocks' ), __( 'endpoint', 'docspress-blocks' ), __( 'response', 'docspress-blocks' ) ],
		attributes: {
			method: { type: 'string', default: 'GET' },
			endpoint: { type: 'string', default: '/wp-json/wp/v2/pages' },
			headers: { type: 'string', default: 'Accept: application/json\nAuthorization: Bearer $WP_ACCESS_TOKEN' },
			requestBody: { type: 'string', default: '' },
			requestBodyFormat: { type: 'string', default: 'json' },
			responseStatus: { type: 'string', default: '200 OK' },
			responseBody: { type: 'string', default: '{\n  "id": 42,\n  "slug": "getting-started"\n}' },
			responseBodyFormat: { type: 'string', default: 'json' }
		},
		supports: { anchor: true, html: false },
		edit: function ApiRequestEdit( { attributes, setAttributes } ) {
			const blockProps = useBlockProps( {
				className: `docspress-api docspress-api--editor ${ presetClass }`,
				style: themeStyle,
				'data-method': attributes.method.toLowerCase()
			} );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'API exchange', 'docspress-blocks' ), initialOpen: true },
						el( SelectControl, {
							label: __( 'Method', 'docspress-blocks' ),
							value: attributes.method,
							options: [ 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ].map( ( method ) => ( { label: method, value: method } ) ),
							onChange: ( method ) => setAttributes( { method } )
						} ),
						el( TextControl, {
							label: __( 'Endpoint', 'docspress-blocks' ),
							value: attributes.endpoint,
							onChange: ( endpoint ) => setAttributes( { endpoint } )
						} ),
						el( TextControl, {
							label: __( 'Response status', 'docspress-blocks' ),
							value: attributes.responseStatus,
							onChange: ( responseStatus ) => setAttributes( { responseStatus } )
						} ),
						el( SelectControl, {
							label: __( 'Request body display', 'docspress-blocks' ),
							value: attributes.requestBodyFormat,
							options: [
								{ label: __( 'JSON — syntax highlighted', 'docspress-blocks' ), value: 'json' },
								{ label: __( 'Raw — unformatted text', 'docspress-blocks' ), value: 'raw' }
							],
							onChange: ( requestBodyFormat ) => setAttributes( { requestBodyFormat } )
						} ),
						el( SelectControl, {
							label: __( 'Response body display', 'docspress-blocks' ),
							value: attributes.responseBodyFormat,
							options: [
								{ label: __( 'JSON — syntax highlighted', 'docspress-blocks' ), value: 'json' },
								{ label: __( 'Raw — unformatted text', 'docspress-blocks' ), value: 'raw' }
							],
							onChange: ( responseBodyFormat ) => setAttributes( { responseBodyFormat } )
						} )
					)
				),
				el(
					'figure',
					blockProps,
					el(
						'div',
						{ className: 'docspress-api__request-line' },
						el( 'span', { className: 'docspress-api__eyebrow' }, __( 'Request', 'docspress-blocks' ) ),
						el( 'span', { className: 'docspress-api__method' }, attributes.method ),
						el( 'code', { className: 'docspress-api__endpoint' }, attributes.endpoint || __( 'Endpoint…', 'docspress-blocks' ) )
					),
					el( PayloadEditor, {
						label: __( 'Headers', 'docspress-blocks' ),
						value: attributes.headers,
						placeholder: 'Accept: application/json',
						modifier: 'headers',
						format: 'headers',
						onChange: ( headers ) => setAttributes( { headers } )
					} ),
					el( PayloadEditor, {
						label: __( 'Request body', 'docspress-blocks' ),
						value: attributes.requestBody,
						placeholder: __( 'Optional request body…', 'docspress-blocks' ),
						modifier: 'request',
						format: attributes.requestBodyFormat,
						onChange: ( requestBody ) => setAttributes( { requestBody } )
					} ),
					el(
						'section',
						{ className: 'docspress-api__response' },
						el(
							'div',
							{ className: 'docspress-api__response-line' },
							el( 'span', { className: 'docspress-api__eyebrow' }, __( 'Response', 'docspress-blocks' ) ),
							el( 'span', { className: 'docspress-api__status' }, attributes.responseStatus )
						),
						el( PayloadEditor, {
							label: __( 'Body', 'docspress-blocks' ),
							value: attributes.responseBody,
							placeholder: '{\n  "ok": true\n}',
							modifier: 'response',
							format: attributes.responseBodyFormat,
							onChange: ( responseBody ) => setAttributes( { responseBody } )
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
