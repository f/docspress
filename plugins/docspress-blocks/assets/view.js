( function () {
	'use strict';

	function escapeHtml( value ) {
		return value
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' )
			.replace( /'/g, '&#039;' );
	}

	function highlightLine( source, language ) {
		if ( language === 'plaintext' ) {
			return escapeHtml( source );
		}

		let working = source;
		const tokens = [];
		const usesHashComments = [ 'bash', 'shell', 'python', 'yaml' ].includes( language );
		const protectedTokenPattern = usesHashComments
			? /<!--[\s\S]*?-->|(^|\s)#[^\n]*|`(?:\\.|[^`])*`|"(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'/g
			: /<!--[\s\S]*?-->|\/\*[\s\S]*?\*\/|\/\/[^\n]*|`(?:\\.|[^`])*`|"(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'/g;

		// Protect comments and strings in one left-to-right pass. Separate passes can
		// nest private-use markers when a quoted value contains comment-like text,
		// such as the /**/ portion of a YAML glob.
		working = working.replace( protectedTokenPattern, function ( match ) {
			const marker = `\uE000${ String.fromCodePoint( 0xE100 + tokens.length ) }\uE001`;
			const trimmed = match.trimStart();
			const className = trimmed.startsWith( '<!--' ) || trimmed.startsWith( '/*' ) || trimmed.startsWith( '//' ) || trimmed.startsWith( '#' )
				? 'token-comment'
				: 'token-string';
			tokens.push( { marker, html: `<span class="${ className }">${ escapeHtml( match ) }</span>` } );
			return marker;
		} );

		let html = escapeHtml( working );
		const keywords = /\b(?:abstract|and|array|as|async|await|break|case|catch|class|const|continue|def|default|delete|do|echo|elif|else|enum|export|extends|false|final|finally|for|foreach|from|function|if|implements|import|in|instanceof|interface|let|match|namespace|new|null|or|private|protected|public|readonly|return|static|switch|throw|trait|true|try|type|typeof|use|var|while|with|yield)\b/g;
		html = html.replace( keywords, '<span class="token-keyword">$&</span>' );
		html = html.replace( /\b(?:true|false|null)\b/g, '<span class="token-boolean">$&</span>' );
		html = html.replace( /\b(?:0x[\da-f]+|\d+(?:\.\d+)?)\b/gi, '<span class="token-number">$&</span>' );

		if ( [ 'json', 'css', 'yaml' ].includes( language ) ) {
			html = html.replace( /(^|[\s,{])([A-Za-z_$][\w$-]*)(?=\s*:)/g, '$1<span class="token-property">$2</span>' );
		}

		if ( [ 'html', 'jsx', 'tsx' ].includes( language ) ) {
			html = html.replace( /(&lt;\/?)([A-Za-z][\w-]*)/g, '$1<span class="token-selector">$2</span>' );
		}

		tokens.forEach( function ( token ) {
			html = html.split( token.marker ).join( token.html );
		} );

		return html;
	}

	function highlightJson( source ) {
		const pattern = /"(?:\\u[\da-fA-F]{4}|\\["\\/bfnrt]|[^"\\\u0000-\u001F])*"|-?(?:0|[1-9]\d*)(?:\.\d+)?(?:[eE][+-]?\d+)?|\b(?:true|false|null)\b/g;
		let cursor = 0;
		let html = '';
		let match;

		while ( ( match = pattern.exec( source ) ) ) {
			html += escapeHtml( source.slice( cursor, match.index ) );
			const token = match[ 0 ];
			let kind = 'number';
			if ( token.startsWith( '"' ) ) {
				kind = /^\s*:/.test( source.slice( pattern.lastIndex ) ) ? 'property' : 'string';
			} else if ( /^(?:true|false|null)$/.test( token ) ) {
				kind = 'literal';
			}
			html += `<span class="docspress-api__token--${ kind }">${ escapeHtml( token ) }</span>`;
			cursor = pattern.lastIndex;
		}

		return html + escapeHtml( source.slice( cursor ) );
	}

	function enhanceCode( root ) {
		root.querySelectorAll( '.docspress-code__surface:not([data-docspress-highlighted])' ).forEach( function ( surface ) {
			const language = surface.dataset.language || 'plaintext';
			surface.querySelectorAll( '.docspress-code__line-content' ).forEach( function ( line ) {
				line.innerHTML = highlightLine( line.textContent, language );
			} );
			surface.dataset.docspressHighlighted = 'true';
		} );
	}

	function enhanceApiPayloads( root ) {
		root.querySelectorAll( '.docspress-api__payload[data-docspress-api-format="json"] code:not([data-docspress-api-highlighted])' ).forEach( function ( code ) {
			code.innerHTML = highlightJson( code.textContent );
			code.dataset.docspressApiHighlighted = 'true';
		} );
	}

	function textFromSurface( surface ) {
		return Array.from( surface.querySelectorAll( '.docspress-code__line-content' ) )
			.map( function ( line ) {
				return line.textContent;
			} )
			.join( '\n' );
	}

	function legacyCopy( text ) {
		const field = document.createElement( 'textarea' );
		field.value = text;
		field.setAttribute( 'readonly', '' );
		field.style.position = 'fixed';
		field.style.opacity = '0';
		document.body.appendChild( field );
		field.select();
		document.execCommand( 'copy' );
		field.remove();
	}

	function copyCode( button ) {
		const targetId = button.dataset.docspressCopyTarget;
		const target = targetId ? document.getElementById( targetId ) : null;
		let text = target ? target.textContent.trim() : '';
		let surface = button.closest( '.docspress-code__surface' );
		if ( ! text && ! surface ) {
			const tabs = button.closest( '[data-docspress-tabs]' );
			const panel = tabs && tabs.querySelector( '[role="tabpanel"]:not([hidden])' );
			surface = panel && panel.querySelector( '.docspress-code__surface' );
		}
		if ( ! text && ! surface ) {
			return;
		}

		if ( ! text ) {
			text = textFromSurface( surface );
		}
		const operation = navigator.clipboard && window.isSecureContext
			? navigator.clipboard.writeText( text ).catch( function () {
				legacyCopy( text );
			} )
			: Promise.resolve().then( function () {
				legacyCopy( text );
			} );

		operation.then( function () {
			const label = button.querySelector( 'b' );
			const previous = label ? label.textContent : '';
			button.classList.add( 'is-copied' );
			if ( label ) {
				label.textContent = 'Copied';
			}
			setTimeout( function () {
				button.classList.remove( 'is-copied' );
				if ( label ) {
					label.textContent = previous;
				}
			}, 1600 );
		} );
	}

	function selectTab( tabList, nextTab ) {
		const tabs = Array.from( tabList.querySelectorAll( '[role="tab"]' ) );
		const container = tabList.closest( '[data-docspress-tabs]' );
		if ( ! container || ! nextTab ) {
			return;
		}

		let activePanelId = '';
		tabs.forEach( function ( tab ) {
			const active = tab === nextTab;
			tab.classList.toggle( 'is-active', active );
			tab.setAttribute( 'aria-selected', active ? 'true' : 'false' );
			tab.tabIndex = active ? 0 : -1;
			const panel = document.getElementById( tab.getAttribute( 'aria-controls' ) );
			if ( panel && container.contains( panel ) ) {
				panel.hidden = ! active;
				if ( active ) {
					activePanelId = panel.id;
				}
			}
		} );

		container.querySelectorAll( '[data-docspress-tab-meta]' ).forEach( function ( metadata ) {
			metadata.hidden = metadata.dataset.docspressTabMeta !== activePanelId;
		} );
	}

	function enhanceTabs( root ) {
		root.querySelectorAll( '[data-docspress-tabs]' ).forEach( function ( container ) {
			const tabList = container.querySelector( '[role="tablist"]' );
			if ( ! tabList || tabList.dataset.docspressReady ) {
				return;
			}
			tabList.dataset.docspressReady = 'true';

			tabList.addEventListener( 'click', function ( event ) {
				const tab = event.target.closest( '[role="tab"]' );
				if ( tab && tabList.contains( tab ) ) {
					selectTab( tabList, tab );
				}
			} );

			tabList.addEventListener( 'keydown', function ( event ) {
				const tabs = Array.from( tabList.querySelectorAll( '[role="tab"]' ) );
				const current = tabs.indexOf( event.target );
				if ( current < 0 || ! [ 'ArrowLeft', 'ArrowRight', 'Home', 'End' ].includes( event.key ) ) {
					return;
				}
				event.preventDefault();
				let next = current;
				if ( event.key === 'ArrowLeft' ) {
					next = ( current - 1 + tabs.length ) % tabs.length;
				} else if ( event.key === 'ArrowRight' ) {
					next = ( current + 1 ) % tabs.length;
				} else if ( event.key === 'Home' ) {
					next = 0;
				} else if ( event.key === 'End' ) {
					next = tabs.length - 1;
				}
				selectTab( tabList, tabs[ next ] );
				tabs[ next ].focus();
			} );
		} );
	}

	function initialize() {
		enhanceCode( document );
		enhanceApiPayloads( document );
		enhanceTabs( document );
		document.addEventListener( 'click', function ( event ) {
			const button = event.target.closest( '[data-docspress-copy]' );
			if ( button ) {
				copyCode( button );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initialize );
	} else {
		initialize();
	}
} )();
