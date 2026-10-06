// Portfolio screenshots via Chrome DevTools Protocol (true device emulation).
// Usage: node tools/screenshots.mjs [base-url]   (serve dist first: node tools/serve-dist.mjs)
import { spawn } from 'node:child_process';
import { writeFile, mkdir, mkdtemp } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const BASE = process.argv[ 2 ] || 'http://127.0.0.1:8090/study-spots-sj';
const OUT = new URL( '../docs/screenshots/', import.meta.url ).pathname;
const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const SHOTS = [
	[ 'home-night', '/', 1440, 900, 1, 'dark' ],
	[ 'home-day', '/', 1440, 900, 1, 'light' ],
	[ 'explore-night', '/spots/', 1440, 1400, 1, 'dark' ],
	[ 'spot-day', '/spots/dr-martin-luther-king-jr-library/', 1440, 1300, 1, 'light' ],
	[ 'mobile-home-night', '/', 390, 844, 2, 'dark', true ],
	[ 'mobile-explore-day', '/spots/', 390, 844, 2, 'light', true ],
	[ 'mobile-spot-night', '/spots/rose-garden-branch-library/', 390, 844, 2, 'dark', true ],
];

await mkdir( OUT, { recursive: true } );
const profile = await mkdtemp( join( tmpdir(), 'ss-shots-' ) );
const chrome = spawn( CHROME, [ '--headless=new', '--remote-debugging-port=9333', `--user-data-dir=${ profile }`, '--hide-scrollbars', 'about:blank' ], { stdio: 'ignore' } );
let ws;
for ( let i = 0; i < 50 && ! ws; i++ ) {
	await new Promise( ( r ) => setTimeout( r, 200 ) );
	try {
		const targets = await ( await fetch( 'http://127.0.0.1:9333/json' ) ).json();
		ws = targets.find( ( t ) => t.type === 'page' ).webSocketDebuggerUrl;
	} catch {}
}
const sock = new WebSocket( ws );
await new Promise( ( r ) => sock.addEventListener( 'open', r ) );
let id = 0;
const pending = new Map();
const events = [];
sock.addEventListener( 'message', ( e ) => {
	const m = JSON.parse( e.data );
	if ( m.id && pending.has( m.id ) ) {
		pending.get( m.id )( m.result );
		pending.delete( m.id );
	} else if ( m.method ) {
		events.push( m.method );
	}
} );
const send = ( method, params = {} ) => new Promise( ( r ) => {
	pending.set( ++id, r );
	sock.send( JSON.stringify( { id, method, params } ) );
} );
const sleep = ( ms ) => new Promise( ( r ) => setTimeout( r, ms ) );

await send( 'Page.enable' );
for ( const [ name, path, w, h, dpr, scheme, mobile ] of SHOTS ) {
	await send( 'Emulation.setDeviceMetricsOverride', { width: w, height: h, deviceScaleFactor: dpr, mobile: !! mobile } );
	await send( 'Emulation.setEmulatedMedia', { features: [ { name: 'prefers-color-scheme', value: scheme }, { name: 'prefers-reduced-motion', value: 'reduce' } ] } );
	await send( 'Emulation.setTouchEmulationEnabled', { enabled: !! mobile } );
	events.length = 0;
	await send( 'Page.navigate', { url: BASE + path } );
	for ( let i = 0; i < 50 && ! events.includes( 'Page.loadEventFired' ); i++ ) {
		await sleep( 100 );
	}
	await sleep( 2500 ); // fonts, map tiles
	const { data } = await send( 'Page.captureScreenshot', { format: 'png' } );
	await writeFile( join( OUT, name + '.png' ), Buffer.from( data, 'base64' ) );
	console.log( 'saved', name );
}
sock.close();
chrome.kill();
