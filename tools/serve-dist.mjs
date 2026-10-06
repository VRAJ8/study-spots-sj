// Serves ./dist under /study-spots-sj/ exactly like GitHub Pages (incl. 404.html).
import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import { join, extname } from 'node:path';
const BASE = '/study-spots-sj';
const DIST = new URL( '../dist', import.meta.url ).pathname;
const TYPES = { '.html': 'text/html; charset=utf-8', '.css': 'text/css', '.js': 'text/javascript', '.mjs': 'text/javascript', '.woff2': 'font/woff2', '.svg': 'image/svg+xml', '.png': 'image/png', '.json': 'application/json' };
createServer( async ( req, res ) => {
	let p = decodeURIComponent( new URL( req.url, 'http://x' ).pathname );
	if ( p === '/' ) {
		res.writeHead( 302, { location: BASE + '/' } ).end();
		return;
	}
	if ( ! p.startsWith( BASE ) ) {
		res.writeHead( 404 ).end( 'Not found' );
		return;
	}
	p = p.slice( BASE.length ) || '/';
	let file = join( DIST, p );
	try {
		if ( ( await stat( file ) ).isDirectory() ) file = join( file, 'index.html' );
		res.writeHead( 200, { 'content-type': TYPES[ extname( file ) ] || 'application/octet-stream' } ).end( await readFile( file ) );
	} catch {
		res.writeHead( 404, { 'content-type': TYPES['.html'] } ).end( await readFile( join( DIST, '404.html' ) ) );
	}
} ).listen( 8090, () => console.log( 'http://127.0.0.1:8090' + BASE + '/' ) );
