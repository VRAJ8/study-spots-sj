/**
 * Spot Grid filtering — progressive enhancement over a server-rendered list.
 * Uses View Transitions (when supported and motion is OK) for smooth re-sorting.
 */
( function () {
	const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	document.querySelectorAll( '[data-ss-grid]' ).forEach( ( grid ) => {
		const list = grid.querySelector( '[data-ss-cards]' );
		const cards = Array.from( list.children );
		const q = grid.querySelector( '[data-ss-q]' );
		const sort = grid.querySelector( '[data-ss-sort]' );
		const chips = grid.querySelectorAll( '[data-ss-vibe]' );
		const late = grid.querySelector( '[data-ss-late]' );
		const status = grid.querySelector( '[data-ss-status]' );
		const empty = grid.querySelector( '[data-ss-empty]' );
		if ( ! q ) {
			return;
		}

		cards.forEach( ( c ) => ( c.style.viewTransitionName = 'ss-card-' + c.dataset.id ) );

		// Allow deep links such as /spots/?vibe=night-owl
		const params = new URLSearchParams( location.search );
		if ( params.get( 'vibe' ) ) {
			chips.forEach( ( c ) => c.dataset.ssVibe === params.get( 'vibe' ) && c.setAttribute( 'aria-pressed', 'true' ) );
		}
		if ( params.get( 'q' ) ) {
			q.value = params.get( 'q' );
		}

		function apply( announce ) {
			const term = q.value.trim().toLowerCase();
			const vibes = Array.from( chips ).filter( ( c ) => c.getAttribute( 'aria-pressed' ) === 'true' ).map( ( c ) => c.dataset.ssVibe );
			const lateOnly = late.getAttribute( 'aria-pressed' ) === 'true';

			const visible = cards.filter( ( c ) => {
				const okTerm = ! term || c.dataset.search.includes( term );
				const own = c.dataset.vibes.split( ' ' );
				const okVibe = ! vibes.length || vibes.every( ( v ) => own.includes( v ) );
				const okLate = ! lateOnly || c.dataset.late === '1';
				return okTerm && okVibe && okLate;
			} );

			const by = sort.value;
			const sorted = cards.slice().sort( ( a, b ) => {
				if ( by === 'az' ) {
					return a.querySelector( '.ss-card__title' ).textContent.localeCompare( b.querySelector( '.ss-card__title' ).textContent );
				}
				const key = by === 'quiet' ? 'quiet' : 'score';
				return parseFloat( b.dataset[ key ] ) - parseFloat( a.dataset[ key ] ) || parseFloat( b.dataset.score ) - parseFloat( a.dataset.score );
			} );

			const render = () => {
				sorted.forEach( ( c ) => {
					c.hidden = ! visible.includes( c );
					list.appendChild( c );
				} );
				empty.hidden = visible.length > 0;
			};

			if ( document.startViewTransition && ! reduceMotion.matches ) {
				// Transitions abort harmlessly when the tab is hidden; swallow that rejection.
				document.startViewTransition( render ).ready.catch( () => {} );
			} else {
				render();
			}

			if ( announce ) {
				status.textContent = visible.length === 1 ? '1 spot matches.' : visible.length + ' spots match.';
			}
		}

		let t;
		q.addEventListener( 'input', () => {
			clearTimeout( t );
			t = setTimeout( () => apply( true ), 180 );
		} );
		sort.addEventListener( 'change', () => apply( true ) );
		[ ...chips, late ].forEach( ( chip ) =>
			chip.addEventListener( 'click', () => {
				chip.setAttribute( 'aria-pressed', chip.getAttribute( 'aria-pressed' ) === 'true' ? 'false' : 'true' );
				apply( true );
			} )
		);

		apply( false );
	} );
} )();
