/**
 * Spot Map — Leaflet + OpenStreetMap tiles. Night mode is a CSS filter on the
 * tile layer (see site.css), so no API key or second tile provider is needed.
 *
 * Leaflet (~150 KB) is only downloaded when a map scrolls near the viewport,
 * so pages with the map below the fold stay fast.
 */
( function () {
	const TILES = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
	const ATTR = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
	const esc = ( s ) => String( s ).replace( /[&<>"']/g, ( c ) => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' } )[ c ] );
	let loading;

	function loadLeaflet( node ) {
		if ( window.L ) {
			return Promise.resolve();
		}
		if ( ! loading ) {
			loading = new Promise( ( resolve, reject ) => {
				const css = document.createElement( 'link' );
				css.rel = 'stylesheet';
				css.href = node.dataset.ssLeafletCss;
				document.head.appendChild( css );
				const js = document.createElement( 'script' );
				js.src = node.dataset.ssLeafletJs;
				js.onload = resolve;
				js.onerror = reject;
				document.head.appendChild( js );
			} );
		}
		return loading;
	}

	function init( node ) {
		const pins = JSON.parse( node.dataset.ssMap );
		const map = L.map( node, { scrollWheelZoom: false, zoomControl: true, attributionControl: true } );
		L.tileLayer( TILES, { attribution: ATTR, maxZoom: 19, detectRetina: true } ).addTo( map );

		const markers = pins.map( ( p ) => {
			const icon = L.divIcon( {
				className: 'ss-pin',
				html: '<span class="ss-pin__dot" style="--ss-hue:' + p.hue + '"><span>' + Number( p.score ).toFixed( 1 ) + '</span></span>',
				iconSize: [ 40, 40 ],
				iconAnchor: [ 20, 40 ],
				popupAnchor: [ 0, -36 ],
			} );
			return L.marker( [ p.lat, p.lng ], { icon, title: p.title, alt: p.title, keyboard: true } )
				.bindPopup( '<a class="ss-pop" href="' + esc( p.url ) + '"><strong>' + esc( p.title ) + '</strong><span>' + esc( p.area ) + ' · ' + Number( p.score ).toFixed( 1 ) + '/5</span></a>' )
				.addTo( map );
		} );

		if ( markers.length === 1 ) {
			map.setView( markers[ 0 ].getLatLng(), 16 );
		} else {
			map.fitBounds( L.featureGroup( markers ).getBounds().pad( 0.15 ) );
		}

		// Scroll-zoom only after the user engages with the map.
		node.addEventListener( 'click', () => map.scrollWheelZoom.enable(), { once: true } );
	}

	const start = ( node ) => loadLeaflet( node ).then( () => init( node ) ).catch( () => node.classList.add( 'is-failed' ) );
	const nodes = document.querySelectorAll( '[data-ss-map]' );
	if ( ! ( 'IntersectionObserver' in window ) ) {
		nodes.forEach( start );
		return;
	}
	const io = new IntersectionObserver(
		( entries ) =>
			entries.forEach( ( e ) => {
				if ( e.isIntersecting ) {
					io.unobserve( e.target );
					start( e.target );
				}
			} ),
		{ rootMargin: '400px 0px' }
	);
	nodes.forEach( ( n ) => io.observe( n ) );
} )();
