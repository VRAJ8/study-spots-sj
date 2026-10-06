// Broken-link checker: crawls every internal page, then checks each internal
// and external link/asset once. Usage: node tools/check-links.mjs <base-url> [cookie]
const base = new URL(process.argv[2] || 'http://127.0.0.1:9400/');
const cookie = process.argv[3] || '';
const SKIP = /wp-admin|wp-login|xmlrpc|wp-json|\/feed|admin-post|\?replytocom|tile\.openstreetmap|#$/;
const headers = { cookie, 'user-agent': 'StudySpotsLinkCheck/1.0' };
const seen = new Map(); // url -> {status, from:Set}
const queue = [base.href];
const crawled = new Set();

async function status(url) {
	try {
		let r = await fetch(url, { method: 'HEAD', headers, redirect: 'follow' });
		if (r.status === 405 || r.status === 403) r = await fetch(url, { headers, redirect: 'follow' });
		return r.status;
	} catch (e) { return 'ERR ' + (e.cause?.code || e.message); }
}

while (queue.length) {
	const page = queue.shift();
	if (crawled.has(page)) continue;
	crawled.add(page);
	const res = await fetch(page, { headers, redirect: 'follow' });
	const html = await res.text();
	for (const m of html.matchAll(/\b(?:href|src)="([^"]+)"/g)) {
		let url;
		try { url = new URL(m[1].replace(/&amp;/g, '&'), page); } catch { continue; }
		if (!/^https?:$/.test(url.protocol) || SKIP.test(url.href)) continue;
		url.hash = '';
		const key = url.href;
		if (!seen.has(key)) seen.set(key, { from: new Set() });
		seen.get(key).from.add(new URL(page).pathname);
		const isPage = url.origin === base.origin && !/\.(css|js|woff2?|png|jpe?g|svg|ico|webp)(\?|$)/.test(url.pathname) && !url.pathname.startsWith('/wp-content');
		if (isPage && !crawled.has(key) && !queue.includes(key)) queue.push(key);
	}
}

const bad = [];
await Promise.all([...seen.keys()].map(async (u) => {
	const s = await status(u);
	seen.get(u).status = s;
	if (s !== 200) bad.push(`${s}  ${u}\n      linked from: ${[...seen.get(u).from].join(', ')}`);
}));
console.log(`Pages crawled: ${crawled.size}`);
console.log(`Unique links/assets checked: ${seen.size} (internal ${[...seen.keys()].filter(u => u.startsWith(base.origin)).length}, external ${[...seen.keys()].filter(u => !u.startsWith(base.origin)).length})`);
console.log(bad.length ? `Problems (${bad.length}):\n` + bad.sort().join('\n') : 'No broken links.');
