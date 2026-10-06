/**
 * Study Spots SJ — front-end interactions.
 * Everything here is enhancement: the site is fully usable without it.
 */
( function () {
	const root = document.documentElement;
	const reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	const systemDark = window.matchMedia( '(prefers-color-scheme: dark)' );
	root.classList.add( 'js' );

	/* ---------- Day / night ---------- */
	const resolved = () => root.dataset.theme || ( systemDark.matches ? 'dark' : 'light' );
	const toggles = document.querySelectorAll( '[data-ss-theme-toggle]' );
	function syncMode() {
		const mode = resolved();
		root.dataset.ssMode = mode;
		toggles.forEach( ( t ) => {
			t.setAttribute( 'aria-label', mode === 'dark' ? 'Switch to day mode' : 'Switch to night mode' );
		} );
	}
	toggles.forEach( ( t ) =>
		t.addEventListener( 'click', () => {
			const next = resolved() === 'dark' ? 'light' : 'dark';
			const apply = () => {
				root.dataset.theme = next;
				syncMode();
			};
			try {
				localStorage.setItem( 'ss-theme', next );
			} catch ( e ) {}
			if ( document.startViewTransition && ! reduceMotion ) {
				document.startViewTransition( apply ).ready.catch( () => {} );
			} else {
				apply();
			}
		} )
	);
	systemDark.addEventListener( 'change', syncMode );
	syncMode();

	/* ---------- Sticky header state ---------- */
	const header = document.querySelector( '.ss-site-header' );
	if ( header ) {
		const onScroll = () => header.classList.toggle( 'is-scrolled', window.scrollY > 12 );
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}

	/* ---------- Rotating hero word ---------- */
	document.querySelectorAll( '[data-ss-rotator]' ).forEach( ( el ) => {
		const words = el.dataset.ssRotator.split( ',' );
		// Screen readers get one stable sentence instead of a changing word.
		el.setAttribute( 'aria-hidden', 'true' );
		const sr = document.createElement( 'span' );
		sr.className = 'screen-reader-text';
		sr.textContent = 'perfect';
		el.after( sr );
		if ( reduceMotion || words.length < 2 ) {
			return;
		}
		let i = 0;
		setInterval( () => {
			if ( document.hidden ) {
				return;
			}
			el.classList.add( 'is-out' );
			setTimeout( () => {
				i = ( i + 1 ) % words.length;
				el.textContent = words[ i ];
				el.classList.remove( 'is-out' );
			}, 350 );
		}, 2600 );
	} );

	/* ---------- Scroll reveals ---------- */
	document.querySelectorAll( '.ss-cards' ).forEach( ( list ) =>
		Array.from( list.children ).forEach( ( c, n ) => c.style.setProperty( '--ss-n', Math.min( n, 8 ) ) )
	);
	const revealables = document.querySelectorAll( '.ss-reveal, .ss-cards' );
	if ( 'IntersectionObserver' in window && ! reduceMotion ) {
		const io = new IntersectionObserver(
			( entries ) =>
				entries.forEach( ( e ) => {
					if ( e.isIntersecting ) {
						e.target.classList.add( 'is-in' );
						io.unobserve( e.target );
					}
				} ),
			{ rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
		);
		revealables.forEach( ( el ) => io.observe( el ) );
	} else {
		revealables.forEach( ( el ) => el.classList.add( 'is-in' ) );
	}

	/* ---------- Count-up stats ---------- */
	const counters = document.querySelectorAll( '[data-ss-count]' );
	if ( counters.length && ! reduceMotion && 'IntersectionObserver' in window ) {
		const cio = new IntersectionObserver( ( entries ) =>
			entries.forEach( ( e ) => {
				if ( ! e.isIntersecting ) {
					return;
				}
				cio.unobserve( e.target );
				const end = parseInt( e.target.dataset.ssCount, 10 );
				const start = performance.now();
				const tick = ( now ) => {
					const p = Math.min( 1, ( now - start ) / 1200 );
					e.target.textContent = Math.round( end * ( 1 - Math.pow( 1 - p, 3 ) ) );
					if ( p < 1 ) {
						requestAnimationFrame( tick );
					}
				};
				requestAnimationFrame( tick );
				// Guarantee the real number lands even if rAF is throttled (background tabs).
				setTimeout( () => ( e.target.textContent = end ), 1500 );
			} )
		);
		counters.forEach( ( c ) => cio.observe( c ) );
	}

	/* ---------- 3D tilt on the hero card ---------- */
	const tilt = document.querySelector( '[data-ss-tilt]' );
	if ( tilt && ! reduceMotion && window.matchMedia( '(pointer: fine)' ).matches ) {
		const hero = tilt.closest( '.ss-hero' ) || document.body;
		hero.addEventListener( 'pointermove', ( e ) => {
			const r = hero.getBoundingClientRect();
			const x = ( e.clientX - r.left ) / r.width - 0.5;
			const y = ( e.clientY - r.top ) / r.height - 0.5;
			tilt.style.setProperty( '--rx', ( x * 14 ).toFixed( 2 ) + 'deg' );
			tilt.style.setProperty( '--ry', ( -y * 10 ).toFixed( 2 ) + 'deg' );
		} );
		hero.addEventListener( 'pointerleave', () => {
			tilt.style.setProperty( '--rx', '0deg' );
			tilt.style.setProperty( '--ry', '0deg' );
		} );
	}

	/* ---------- Form: move focus to the status message after submit ---------- */
	const notice = document.querySelector( '[data-ss-focus]' );
	if ( notice ) {
		notice.focus();
	}

	/* ---------- Form: validate in the browser first so nothing typed is lost ---------- */
	document.querySelectorAll( '.ss-form' ).forEach( ( form ) => {
		const rules = [
			[ 'spot_name', 2, 'Enter the name of the place.' ],
			[ 'spot_address', 4, 'Enter an address or cross streets.' ],
			[ 'spot_why', 20, 'Tell us a bit more (at least 20 characters).' ],
		];
		const setError = ( field, msg ) => {
			const id = 'err-' + field.id;
			let err = document.getElementById( id );
			const described = ( field.getAttribute( 'aria-describedby' ) || '' ).split( ' ' ).filter( ( x ) => x && x !== id );
			if ( msg ) {
				if ( ! err ) {
					err = document.createElement( 'p' );
					err.className = 'ss-field__error';
					err.id = id;
					field.after( err );
				}
				err.textContent = msg;
				field.setAttribute( 'aria-invalid', 'true' );
				described.push( id );
			} else {
				err?.remove();
				field.removeAttribute( 'aria-invalid' );
			}
			described.length ? field.setAttribute( 'aria-describedby', described.join( ' ' ) ) : field.removeAttribute( 'aria-describedby' );
		};
		form.addEventListener( 'submit', ( e ) => {
			let first = null;
			rules.forEach( ( [ name, min, msg ] ) => {
				const field = form.elements[ name ];
				const bad = field.value.trim().length < min;
				setError( field, bad ? msg : '' );
				first = first || ( bad ? field : null );
			} );
			if ( first ) {
				e.preventDefault();
				first.focus();
			}
		} );
		// Clear an error as soon as the field becomes valid.
		form.addEventListener( 'input', ( e ) => {
			const rule = rules.find( ( r ) => r[ 0 ] === e.target.name );
			if ( rule && e.target.getAttribute( 'aria-invalid' ) && e.target.value.trim().length >= rule[ 1 ] ) {
				setError( e.target, '' );
			}
		} );
	} );

	/* ---------- Static (GitHub Pages) build: forms need the live WordPress demo ---------- */
	const demo = root.dataset.ssDemo;
	if ( demo ) {
		document.querySelectorAll( '[data-ss-form]:has(form)' ).forEach( ( wrap ) => {
			wrap.innerHTML =
				'<div class="ss-static-note"><p><strong>This is the static showcase build.</strong> Submissions are handled by WordPress, so the form runs in the live demo, where you can submit a spot and then see it land in the editor’s review queue.</p>' +
				'<p><a class="ss-button ss-button--primary" href="' + demo + '">Open the live WordPress demo</a></p></div>';
		} );
	}
} )();
