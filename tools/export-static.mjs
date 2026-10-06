// Static export for GitHub Pages.
// Boots a logged-out WordPress (Playground), crawls every page and every
// asset it references, rewrites URLs to the Pages base path, writes ./dist.
//
// Usage: node tools/export-static.mjs [--base=/study-spots-sj] [--demo=<playground url>]
import { spawn } from 'node:child_process';
import { mkdir, writeFile, rm } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const arg = ( k, d ) => ( process.argv.find( ( a ) => a.startsWith( `--${ k }=` ) ) || '' ).split( '=' ).slice( 1 ).join( '=' ) || d;
const BASE = arg( 'base', '/study-spots-sj' ).replace( /\/$/, '' );
const DEMO = arg( 'demo', 'https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/VRAJ8/study-spots-sj/main/blueprint.json' );
const PORT = 9401;
const ORIGIN = `http://127.0.0.1:${ PORT }`;
const OUT = join( root, 'dist' );

// ---------- 1. Boot a clean, logged-out WordPress ----------
const server = spawn( 'npx', [ '-y', '@wp-playground/cli@latest', 'server', `--port=${ PORT }`,
	`--mount=${ root }/plugin/study-spots-core:/wordpress/wp-content/plugins/study-spots-core`,
	`--mount=${ root }/theme/study-spots:/wordpress/wp-content/themes/study-spots`,
	'--blueprint=blueprint.export.json' ], { cwd: root, stdio: [ 'ignore', 'pipe', 'pipe' ] } );
await new Promise( ( resolve, reject ) => {
	const onData = ( d ) => /Ready!/.test( d ) && resolve();
	server.stdout.on( 'data', onData );
	server.stderr.on( 'data', ( d ) => /Error/i.test( d ) && process.stderr.write( d ) );
	server.on( 'exit', ( c ) => reject( new Error( 'server exited ' + c ) ) );
	setTimeout( () => reject( new Error( 'server timeout' ) ), 180000 );
} );
console.log( 'WordPress ready on', ORIGIN );

try {
	// ---------- 2. Crawl ----------
	const esc = ORIGIN.replace( /\//g, '\\/' ); // JSON-escaped form: http:\/\/127...
	const plainRe = /http:\/\/127\.0\.0\.1:9401(\/[^"'\s)<>&\\]*)?/g; // http://127.0.0.1:9401/path
	const jsonRe = /http:\\\/\\\/127\.0\.0\.1:9401((?:\\\/[^"'\s)<>&\\]*)*)/g; // http:\/\/127.0.0.1:9401\/path
	const pages = new Set( [ '/' ] );
	const assets = new Set();
	const files = new Map(); // path -> Buffer|string
	const queue = [ '/' ];
	const isAsset = ( p ) => /\.(css|js|mjs|woff2?|ttf|png|jpe?g|gif|svg|webp|ico|json)$/i.test( p.split( '?' )[ 0 ] );

	const collect = ( text, fromPath ) => {
		// Absolute (and JSON-escaped) URLs to this origin.
		for ( const m of [ ...text.matchAll( plainRe ), ...text.matchAll( jsonRe ) ] ) {
			const p = ( m[ 1 ] || '/' ).replace( /\\\//g, '/' );
			isAsset( p ) ? assets.add( p.split( '#' )[ 0 ] ) : addPage( p );
		}
		// Root-relative links written in content, e.g. href="/spots/".
		for ( const m of text.matchAll( /(?:href|src|action)="(\/(?!\/)[^"#]*)/g ) ) {
			isAsset( m[ 1 ] ) ? assets.add( m[ 1 ] ) : addPage( m[ 1 ] );
		}
		// url(...) inside CSS, relative to the stylesheet.
		if ( fromPath?.endsWith( '.css' ) ) {
			for ( const m of text.matchAll( /url\(\s*['"]?(?!data:|https?:|\/\/|#|%23)([^'")]+)['"]?\s*\)/g ) ) {
				assets.add( new URL( m[ 1 ], ORIGIN + fromPath ).pathname );
			}
		}
	};
	function addPage( p ) {
		p = p.split( '#' )[ 0 ];
		const path = p.split( '?' )[ 0 ];
		if ( /wp-admin|wp-login|wp-json|xmlrpc|admin-post|\/feed|wp-content|wp-includes|\/vibe\/|\/area\//.test( path ) ) {
			return;
		}
		if ( ! pages.has( path ) ) {
			pages.add( path );
			queue.push( path );
		}
	}

	while ( queue.length ) {
		const path = queue.shift();
		let res = await fetch( ORIGIN + path, { redirect: 'manual' } );
		// Playground answers its very first request with a redirect to the same URL.
		for ( let i = 0; i < 3 && res.status >= 300 && res.status < 400 && new URL( res.headers.get( 'location' ), ORIGIN ).pathname === path; i++ ) {
			res = await fetch( ORIGIN + path, { redirect: 'manual' } );
		}
		if ( res.status !== 200 ) {
			console.warn( `  skip ${ res.status } ${ path }` );
			pages.delete( path );
			continue;
		}
		const html = await res.text();
		files.set( path.endsWith( '/' ) ? path + 'index.html' : path, html );
		collect( html );
	}
	// GitHub Pages serves /404.html for any missing URL.
	const nf = await fetch( ORIGIN + '/this-page-does-not-exist/' );
	const nfHtml = await nf.text();
	files.set( '/404.html', nfHtml );
	collect( nfHtml );

	const done = new Set();
	while ( [ ...assets ].some( ( a ) => ! done.has( a ) ) ) {
		for ( const a of [ ...assets ] ) {
			if ( done.has( a ) ) {
				continue;
			}
			done.add( a );
			const res = await fetch( ORIGIN + a );
			if ( ! res.ok ) {
				console.warn( `  asset ${ res.status } ${ a }` );
				continue;
			}
			const path = a.split( '?' )[ 0 ];
			if ( /\.(css|js|mjs|json|svg)$/i.test( path ) ) {
				const text = await res.text();
				files.set( path, text );
				collect( text, path );
			} else {
				files.set( path, Buffer.from( await res.arrayBuffer() ) );
			}
		}
	}

	// ---------- 3. Rewrite + write ----------
	const rewrite = ( text, isHtml ) => {
		let t = text
			.replace( /<link rel='dns-prefetch' href='\/\/127\.0\.0\.1' \/>\n?/g, '' )
			.replaceAll( ORIGIN, BASE )
			.replaceAll( esc, BASE.replace( /\//g, '\\/' ) )
			.replace( /((?:href|src|action)=")\/(?!\/)/g, `$1${ BASE }/` )
			.replace( new RegExp( `${ BASE }${ BASE }/`, 'g' ), `${ BASE }/` );
		if ( isHtml ) {
			// Static build flag: forms swap to a link to the live WordPress demo.
			t = t.replace( /<html /, `<html data-ss-demo="${ DEMO }" ` );
			// Swap the WordPress-only form for a link to the live demo at build time (no layout shift).
			t = t.replace( /<form class="ss-form"[\s\S]*?<\/form>/, `<div class="ss-static-note"><p><strong>This is the static showcase build.</strong> Submissions are handled by WordPress, so the form runs in the live demo, where you can submit a spot and then see it land in the editor’s review queue.</p><p><a class="ss-button ss-button--primary" href="${ DEMO }">Open the live WordPress demo</a></p></div>` );
			t = t.replace( /(<link rel=["']canonical["'] href=["'])\//g, `$1${ arg( 'site', 'https://vraj8.github.io' ) }/` );
		}
		return t;
	};

	await rm( OUT, { recursive: true, force: true } );
	for ( const [ path, body ] of files ) {
		const file = join( OUT, decodeURIComponent( path ) );
		await mkdir( dirname( file ), { recursive: true } );
		await writeFile( file, typeof body === 'string' ? rewrite( body, path.endsWith( '.html' ) ) : body );
	}
	await writeFile( join( OUT, '.nojekyll' ), '' );

	// SEO: absolute canonical/og:url, sitemap and robots.txt for the live host.
	const SITE = arg( 'site', 'https://vraj8.github.io' ) + BASE;
	const pageList = [ ...pages ].sort();
	await writeFile( join( OUT, 'sitemap.xml' ), `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${ pageList.map( ( p ) => `  <url><loc>${ SITE }${ p }</loc></url>` ).join( '\n' ) }\n</urlset>\n` );
	await writeFile( join( OUT, 'robots.txt' ), `User-agent: *\nAllow: /\nSitemap: ${ SITE }/sitemap.xml\n` );
	console.log( `Exported ${ pages.size } pages + 404 and ${ done.size } assets to dist/ (base ${ BASE })` );
} finally {
	server.kill();
}
process.exit( 0 );
