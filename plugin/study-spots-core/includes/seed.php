<?php
/**
 * Demo content. Run once via the Playground blueprint (or `wp eval 'ss_seed();'`).
 * Ratings are sample values for this portfolio project, and the site says so.
 */

defined( 'ABSPATH' ) || exit;

function ss_seed_terms() {
	$areas = array( 'On Campus', 'Downtown', 'Japantown', 'South First (SoFA)', 'Willow Glen', 'Rose Garden', 'Kelley Park' );
	foreach ( $areas as $area ) {
		if ( ! term_exists( $area, 'spot_area' ) ) {
			wp_insert_term( $area, 'spot_area' );
		}
	}
	$vibes = array(
		'silent-focus'   => array( 'Silent focus', 205, 'Library-level quiet for deep work.' ),
		'lo-fi-buzz'     => array( 'Lo-fi buzz', 330, 'Background chatter that keeps you going.' ),
		'group-friendly' => array( 'Group-friendly', 25, 'Big tables, room to talk.' ),
		'night-owl'      => array( 'Night owl', 265, 'Somewhere to be after dark.' ),
		'fresh-air'      => array( 'Fresh air', 145, 'Outdoor seating and green space.' ),
		'cozy-cafe'      => array( 'Cozy café', 48, 'Coffee within arm’s reach.' ),
	);
	foreach ( $vibes as $slug => $v ) {
		$term = term_exists( $slug, 'spot_vibe' );
		if ( ! $term ) {
			$term = wp_insert_term( $v[0], 'spot_vibe', array( 'slug' => $slug, 'description' => $v[2] ) );
		}
		if ( ! is_wp_error( $term ) ) {
			update_term_meta( (int) $term['term_id'], 'ss_hue', $v[1] );
		}
	}
}

function ss_seed_users() {
	$users = array(
		array( 'maya-scout', 'Maya R.', 'spot_scout' ),
		array( 'jordan-editor', 'Jordan T.', 'spot_editor' ),
	);
	$ids = array();
	foreach ( $users as $u ) {
		$existing = get_user_by( 'login', $u[0] );
		$ids[ $u[2] ] = $existing ? $existing->ID : wp_insert_user(
			array(
				'user_login'   => $u[0],
				'user_pass'    => wp_generate_password( 24 ),
				'display_name' => $u[1],
				'role'         => $u[2],
			)
		);
	}
	return $ids;
}

/**
 * Builds post content in the same structure as the post-type template.
 */
function ss_spot_body( $why, $tips ) {
	$html = "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">Why it works for studying</h2>\n<!-- /wp:heading -->\n\n";
	foreach ( (array) $why as $p ) {
		$html .= "<!-- wp:paragraph -->\n<p>{$p}</p>\n<!-- /wp:paragraph -->\n\n";
	}
	$html .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">Good to know</h2>\n<!-- /wp:heading -->\n\n<!-- wp:list -->\n<ul class=\"wp-block-list\">";
	foreach ( $tips as $t ) {
		$html .= "<!-- wp:list-item -->\n<li>{$t}</li>\n<!-- /wp:list-item -->";
	}
	return $html . "</ul>\n<!-- /wp:list -->";
}

function ss_seed_spots( $users ) {
	$spots = array(
		array(
			'title'   => 'Dr. Martin Luther King Jr. Library',
			'excerpt' => 'Eight floors shared by SJSU and the city, with quiet floors up top and group space below. The default answer to “where should I study?”',
			'why'     => array(
				'The King Library is a joint SJSU and San José Public Library building, so it has the space and hours of a big city library right on campus. The higher you go, the quieter it gets.',
				'Lower floors are busier and better for meeting up; upper floors are where people go to lock in. Reservable group study rooms make it the best pick for project meetings.',
			),
			'tips'    => array( 'Group study rooms can be reserved online. Book early during midterms and finals.', 'Window seats on upper floors fill up first. Arrive before noon.', 'Bring a sweater; it runs cold.' ),
			'area'    => 'On Campus',
			'vibes'   => array( 'silent-focus', 'group-friendly' ),
			'meta'    => array( 'wifi' => 5, 'quiet' => 4, 'outlets' => 4, 'address' => '150 E San Fernando St, San José, CA 95112', 'hours' => 'Varies by semester. Check the library website.', 'price' => 'Free', 'website' => 'https://www.sjlibrary.org/', 'lat' => 37.33544, 'lng' => -121.88497, 'open_late' => false, 'last_verified' => '2026-09-14' ),
			'order'   => 1,
		),
		array(
			'title'   => 'Diaz Compean Student Union',
			'excerpt' => 'The campus living room: food court downstairs, lounges everywhere, and constant energy. Best for group work and quick sessions between classes.',
			'why'     => array( 'If you need to be around people, the Student Union is it. There is always a table somewhere, food is a few steps away, and it is central to almost every building on campus.', 'It is not where you go for silence, but it is unbeatable for group projects and for the hour between two classes.' ),
			'tips'    => array( 'Upstairs lounges are calmer than the food court level.', 'Outlets are hit-or-miss. Charge before you come.', 'Lunch rush (11:30–1:30) is the loudest time.' ),
			'area'    => 'On Campus',
			'vibes'   => array( 'group-friendly', 'lo-fi-buzz' ),
			'meta'    => array( 'wifi' => 4, 'quiet' => 2, 'outlets' => 3, 'address' => '211 S 9th St, San José, CA 95112', 'hours' => 'Varies by semester. Check the Student Union website.', 'price' => 'Free', 'website' => 'https://www.sjsu.edu/studentunion/', 'lat' => 37.33631, 'lng' => -121.88124, 'open_late' => false, 'last_verified' => '2026-09-14' ),
			'order'   => 2,
		),
		array(
			'title'   => 'Biblioteca Latinoamericana Branch Library',
			'excerpt' => 'A calm neighborhood branch on South First with a focus on Spanish-language collections. Reliably quiet when campus is packed.',
			'why'     => array( 'When the King Library is overflowing during finals, this branch is a short trip south and usually much calmer. It has a neighborhood feel and real quiet.', 'It is a good pick for reading-heavy sessions where you just need a table and no distractions.' ),
			'tips'    => array( 'Free with a San José Public Library card.', 'Pair it with a coffee from the South First area afterwards.', 'Check branch hours before you go; they are shorter than the main library.' ),
			'area'    => 'South First (SoFA)',
			'vibes'   => array( 'silent-focus' ),
			'meta'    => array( 'wifi' => 4, 'quiet' => 5, 'outlets' => 3, 'address' => '921 S 1st St, San José, CA 95110', 'hours' => 'Check the library website for branch hours.', 'price' => 'Free', 'website' => 'https://www.sjpl.org/', 'lat' => 37.32144, 'lng' => -121.87838, 'open_late' => false, 'last_verified' => '2026-08-30' ),
			'order'   => 3,
		),
		array(
			'title'   => 'Roy’s Station Coffee & Tea',
			'excerpt' => 'A converted vintage gas station in Japantown with a sunny patio. Coffee, plants and just enough background noise.',
			'why'     => array( 'Roy’s is the spot for when you want a café without the downtown crowd. The patio is great on a nice day and the indoor space feels relaxed.', 'It works best for reading, writing and planning rather than long laptop marathons.' ),
			'tips'    => array( 'Patio seating is the move on sunny afternoons.', 'Limited outlets. Come charged.', 'Combine with a walk around Japantown for a study break.' ),
			'area'    => 'Japantown',
			'vibes'   => array( 'cozy-cafe', 'fresh-air', 'lo-fi-buzz' ),
			'meta'    => array( 'wifi' => 3, 'quiet' => 3, 'outlets' => 2, 'address' => '197 Jackson St, San José, CA 95112', 'hours' => 'Daytime hours. Check before you go.', 'price' => '$ (buy a drink)', 'website' => '', 'lat' => 37.34858, 'lng' => -121.89458, 'open_late' => false, 'last_verified' => '2026-07-22' ),
			'order'   => 4,
		),
		array(
			'title'   => 'San Pedro Square Market',
			'excerpt' => 'Indoor-outdoor food hall downtown. Loud, lively and open late, for light review sessions with food in reach.',
			'why'     => array( 'This is not a silent-study spot. It is where you go to review flashcards with friends over dinner, or to get a change of scenery after the library closes.', 'Lots of shared tables, lots of food options, and it stays busy into the evening.' ),
			'tips'    => array( 'Weeknight early evenings are calmer than weekends.', 'Bring headphones if you want to focus.', 'Better for group review than solo deep work.' ),
			'area'    => 'Downtown',
			'vibes'   => array( 'night-owl', 'group-friendly', 'lo-fi-buzz' ),
			'meta'    => array( 'wifi' => 2, 'quiet' => 1, 'outlets' => 2, 'address' => '87 N San Pedro St, San José, CA 95110', 'hours' => 'Open into the evening. Hours vary by vendor.', 'price' => '$$ (food & drinks)', 'website' => 'https://sanpedrosquaremarket.com/', 'lat' => 37.33652, 'lng' => -121.89431, 'open_late' => true, 'last_verified' => '2026-09-02' ),
			'order'   => 5,
		),
		array(
			'title'   => 'Willow Glen Branch Library',
			'excerpt' => 'A bright, peaceful branch library in leafy Willow Glen. Worth the short drive when you need guaranteed quiet.',
			'why'     => array( 'Willow Glen’s branch is the kind of library where you can actually hear yourself think. Natural light, a calm crowd and plenty of tables.', 'Afterwards, Lincoln Avenue is right there for food and coffee.' ),
			'tips'    => array( 'Weekday mornings are the quietest.', 'Street parking nearby; check signs.', 'Great for a full Saturday reset.' ),
			'area'    => 'Willow Glen',
			'vibes'   => array( 'silent-focus' ),
			'meta'    => array( 'wifi' => 4, 'quiet' => 5, 'outlets' => 3, 'address' => '1157 Minnesota Ave, San José, CA 95125', 'hours' => 'Check the library website for branch hours.', 'price' => 'Free', 'website' => 'https://www.sjpl.org/', 'lat' => 37.30478, 'lng' => -121.89774, 'open_late' => false, 'last_verified' => '2026-06-18' ),
			'order'   => 6,
		),
		array(
			'title'   => 'Rose Garden Branch Library',
			'excerpt' => 'A quiet branch steps from the Municipal Rose Garden. Study inside, take your breaks among the roses.',
			'why'     => array( 'The best study breaks in San José. Work in a calm, modern branch, then walk across to the Municipal Rose Garden to reset.', 'Ideal for long writing sessions where you need to step away every hour.' ),
			'tips'    => array( 'The rose garden peaks in late spring.', 'Bring a water bottle for garden breaks.', 'Weekends can get busy with families.' ),
			'area'    => 'Rose Garden',
			'vibes'   => array( 'silent-focus', 'fresh-air' ),
			'meta'    => array( 'wifi' => 4, 'quiet' => 4, 'outlets' => 3, 'address' => '1580 Naglee Ave, San José, CA 95126', 'hours' => 'Check the library website for branch hours.', 'price' => 'Free', 'website' => 'https://www.sjpl.org/', 'lat' => 37.33188, 'lng' => -121.92826, 'open_late' => false, 'last_verified' => '2026-05-09' ),
			'order'   => 7,
		),
		array(
			'title'   => 'Tower Lawn',
			'excerpt' => 'The green heart of campus in front of Tower Hall. Laptop on a blanket, campus Wi-Fi, zero walls.',
			'why'     => array( 'On a good day there is no better place on campus to read. Shade from the trees, campus Wi-Fi in range, and the classic SJSU view.', 'Best for reading and light review; there are no outlets and glare makes long screen sessions hard.' ),
			'tips'    => array( 'Bring a blanket or grab a bench.', 'Afternoon shade is on the east side.', 'Laptop at full charge only. There are no outlets.' ),
			'area'    => 'On Campus',
			'vibes'   => array( 'fresh-air' ),
			'meta'    => array( 'wifi' => 3, 'quiet' => 3, 'outlets' => 1, 'address' => 'Tower Hall, SJSU campus, San José, CA 95192', 'hours' => 'Daylight', 'price' => 'Free', 'website' => '', 'lat' => 37.33528, 'lng' => -121.88305, 'open_late' => false, 'last_verified' => '2025-11-02' ),
			'order'   => 8,
		),
		array(
			'title'   => 'Japanese Friendship Garden',
			'excerpt' => 'Koi ponds, bridges and total calm in Kelley Park. An unplugged spot for reading and memorizing.',
			'why'     => array( 'Sometimes the best study spot has no Wi-Fi at all. The Friendship Garden is a peaceful place to read a textbook chapter or run through notecards with zero notifications.', 'Treat it as an unplugged session: print what you need, leave the laptop at home.' ),
			'tips'    => array( 'No Wi-Fi or outlets. Plan an offline session.', 'Benches near the ponds are the calmest.', 'Pairs well with a walk through Kelley Park.' ),
			'area'    => 'Kelley Park',
			'vibes'   => array( 'fresh-air', 'silent-focus' ),
			'meta'    => array( 'wifi' => 1, 'quiet' => 5, 'outlets' => 1, 'address' => '1300 Senter Rd, San José, CA 95112', 'hours' => 'Daytime. Check the City of San José website.', 'price' => 'Free', 'website' => '', 'lat' => 37.32513, 'lng' => -121.86086, 'open_late' => false, 'last_verified' => '2026-04-11' ),
			'order'   => 9,
		),
	);

	foreach ( $spots as $s ) {
		if ( get_page_by_path( sanitize_title( $s['title'] ), OBJECT, 'spot' ) ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'spot',
				'post_status'  => 'publish',
				'post_title'   => $s['title'],
				'post_excerpt' => $s['excerpt'],
				'post_content' => ss_spot_body( $s['why'], $s['tips'] ),
				'post_author'  => $s['order'] % 2 ? $users['spot_editor'] : $users['spot_scout'],
				'menu_order'   => $s['order'],
			)
		);
		foreach ( $s['meta'] as $k => $v ) {
			update_post_meta( $id, 'ss_' . $k, $v );
		}
		wp_set_object_terms( $id, $s['area'], 'spot_area' );
		wp_set_object_terms( $id, $s['vibes'], 'spot_vibe' );
	}

	// One community submission waiting in the review queue (shows the workflow).
	if ( ! get_page_by_path( 'late-night-study-room-example-submission', OBJECT, 'spot' ) ) {
		$id = wp_insert_post(
			array(
				'post_type'    => 'spot',
				'post_status'  => 'pending',
				'post_title'   => 'Late-night study room (example submission)',
				'post_excerpt' => 'Example of a community submission waiting for an editor to verify it.',
				'post_content' => ss_submission_content( "Sample submission to demonstrate the review workflow. A student suggested a study room they like. An editor should visit, verify the details, add a map pin and tips, then publish or decline it.\n\nThis is how every public suggestion arrives: as Pending, never live." ),
				'post_author'  => ss_bot_author(),
			)
		);
		update_post_meta( $id, 'ss_submitter', 'Alex' );
		update_post_meta( $id, 'ss_open_late', true );
		wp_set_object_terms( $id, array( 'night-owl', 'silent-focus' ), 'spot_vibe' );
	}

	// A scout's draft (shows scouts can draft but not publish).
	if ( ! get_page_by_path( 'draft-campus-courtyard', OBJECT, 'spot' ) ) {
		wp_insert_post(
			array(
				'post_type'    => 'spot',
				'post_status'  => 'draft',
				'post_name'    => 'draft-campus-courtyard',
				'post_title'   => 'Campus courtyard (draft)',
				'post_content' => ss_spot_body( array( 'Draft in progress by a Spot Scout.' ), array( 'TODO: add hours' ) ),
				'post_author'  => $users['spot_scout'],
			)
		);
	}
}

/**
 * Reusable (synced) content: edit once, updates on every page that uses it.
 */
function ss_seed_synced_patterns() {
	$patterns = array(
		'demo-notice' => array(
			'Demo data notice',
			'<!-- wp:group {"className":"ss-callout","layout":{"type":"constrained"}} --><div class="wp-block-group ss-callout"><!-- wp:paragraph --><p><strong>Heads up:</strong> Study Spots SJ is a student portfolio project. The places are real, but ratings are sample values. Always check hours on each place’s own website before you go.</p><!-- /wp:paragraph --></div><!-- /wp:group -->',
		),
		'suggest-cta' => array(
			'Suggest a spot CTA',
			'<!-- wp:group {"className":"ss-cta","layout":{"type":"constrained"}} --><div class="wp-block-group ss-cta"><!-- wp:heading {"className":"ss-cta__title"} --><h2 class="wp-block-heading ss-cta__title">Know a hidden gem?</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Every spot on this site was suggested by a student and checked by an editor. Add yours to the map.</p><!-- /wp:paragraph --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"className":"is-style-ss-glow"} --><div class="wp-block-button is-style-ss-glow"><a class="wp-block-button__link wp-element-button" href="/suggest-a-spot/">Suggest a spot</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:group -->',
		),
	);
	$ids = array();
	foreach ( $patterns as $slug => $p ) {
		$existing = get_page_by_path( $slug, OBJECT, 'wp_block' );
		$ids[ $slug ] = $existing ? $existing->ID : wp_insert_post(
			array(
				'post_type'    => 'wp_block',
				'post_status'  => 'publish',
				'post_name'    => $slug,
				'post_title'   => $p[0],
				'post_content' => $p[1],
			)
		);
	}
	return $ids;
}

function ss_seed_pages( $synced ) {
	$notice = '<!-- wp:block {"ref":' . (int) $synced['demo-notice'] . '} /-->';
	$cta    = '<!-- wp:block {"ref":' . (int) $synced['suggest-cta'] . '} /-->';

	$pages = array(
		'home'               => array( 'Home', '<!-- wp:pattern {"slug":"study-spots/hero"} /--><!-- wp:pattern {"slug":"study-spots/vibes"} /--><!-- wp:pattern {"slug":"study-spots/featured"} /--><!-- wp:pattern {"slug":"study-spots/how-it-works"} /--><!-- wp:pattern {"slug":"study-spots/map-section"} /-->' . $cta ),
		'suggest-a-spot'     => array( 'Suggest a spot', '<!-- wp:paragraph {"className":"ss-lead"} --><p class="ss-lead">Found a great place to study? Tell us about it. Every suggestion is reviewed by an editor before it appears on the site.</p><!-- /wp:paragraph --><!-- wp:study-spots/submit-form /-->' . $notice ),
		'contributor-guide'  => array( 'Contributor guide', ss_seed_guide_content() ),
		'accessibility'      => array( 'Accessibility', ss_seed_accessibility_content() ),
		'about'              => array( 'About', ss_seed_about_content() ),
	);
	$ids = array();
	foreach ( $pages as $slug => $p ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			continue;
		}
		$ids[ $slug ] = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => $slug,
				'post_title'   => $p[0],
				'post_content' => $p[1],
			)
		);
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['home'] );
	return $ids;
}

function ss_seed_guide_content() {
	return <<<HTML
<!-- wp:paragraph {"className":"ss-lead"} -->
<p class="ss-lead">Everything you need to add or update a spot, written for first-time contributors. No coding required.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading" id="roles">Who can do what</h2>
<!-- /wp:heading -->

<!-- wp:table {"className":"ss-table"} -->
<figure class="wp-block-table ss-table"><table><caption>Permissions by role</caption><thead><tr><th scope="col">Task</th><th scope="col">Spot Scout</th><th scope="col">Spot Editor</th></tr></thead><tbody><tr><th scope="row">Draft a new spot</th><td>Yes</td><td>Yes</td></tr><tr><th scope="row">Edit your own spots</th><td>Yes</td><td>Yes</td></tr><tr><th scope="row">Edit anyone’s spots</th><td>No</td><td>Yes</td></tr><tr><th scope="row">Publish to the live site</th><td>No (submit for review)</td><td>Yes</td></tr><tr><th scope="row">Manage areas and vibes</th><td>No</td><td>Yes</td></tr></tbody></table></figure>
<!-- /wp:table -->

<!-- wp:heading -->
<h2 class="wp-block-heading" id="add-a-spot">Add a spot in 6 steps</h2>
<!-- /wp:heading -->

<!-- wp:list {"ordered":true,"className":"ss-steps"} -->
<ol class="wp-block-list ss-steps"><!-- wp:list-item -->
<li><strong>Go to Spots → Add New.</strong> The page layout is already built for you. You only fill in the two sections: “Why it works for studying” and “Good to know”.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Open “Spot details”</strong> in the right-hand sidebar. Add ratings (1–5), address, hours, website and the map pin (latitude and longitude, which you can copy from Google Maps by right-clicking the spot).</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Choose an Area and Vibes.</strong> Pick existing ones; ask an editor if you think a new vibe is needed.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Write an excerpt</strong> of one or two sentences. It appears on cards, in search results and when the page is shared.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Preview</strong> on desktop, tablet and mobile using the Preview menu. Click every link once.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Click “Submit for Review”.</strong> An editor checks it and publishes it, or leaves you a note.</li>
<!-- /wp:list-item --></ol>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading" id="writing">Writing and accessibility checklist</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"ss-checklist"} -->
<ul class="wp-block-list ss-checklist"><!-- wp:list-item -->
<li><strong>Headings in order.</strong> Use Heading 2 for sections and Heading 3 inside them. Never pick a heading just for its size.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Descriptive links.</strong> Write “King Library hours” instead of “click here”. Screen-reader users often jump from link to link.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Alt text on every image.</strong> Describe what matters (“Long tables by tall windows on the 5th floor”), not “image of library”. Mark purely decorative images as decorative.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Tables for data only,</strong> with a caption and header row turned on.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Captions on video.</strong> Do not embed a video without captions.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Plain language.</strong> Short sentences, active voice, no jargon.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading" id="freshness">Keeping content fresh</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every spot has a <em>Last verified</em> date. Spots not verified in six months are flagged on the editor dashboard and in the Spots list. When you re-check a spot, update its hours and details, then set today’s date.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Editors can download a full content audit (CSV) from the dashboard. It lists every spot with its word count, verification date and any issues, such as missing alt text, vague links, missing map pins or missing summaries.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading" id="troubleshooting">Troubleshooting</h2>
<!-- /wp:heading -->

<!-- wp:details -->
<details class="wp-block-details"><summary>My spot doesn’t show on the map</summary><!-- wp:paragraph -->
<p>Check that both Latitude and Longitude are filled in Spot details and that they are numbers (for example 37.3354 and -121.8850; note the minus sign).</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>I can’t find the Publish button</summary><!-- wp:paragraph -->
<p>That’s expected for Spot Scouts. Use “Submit for Review” instead and an editor will publish it.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Formatting looks strange after pasting from Google Docs or Word</summary><!-- wp:paragraph -->
<p>Pasted text can carry hidden styles. Select the block, open the ⋮ menu and choose “Edit as HTML” to remove stray &lt;span style&gt; tags, or paste with Cmd/Ctrl + Shift + V to paste as plain text.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->

<!-- wp:details -->
<details class="wp-block-details"><summary>Something else is broken</summary><!-- wp:paragraph -->
<p>Note the page URL, what you expected and what happened, plus a screenshot, and send it to an editor. Permission, server or plugin issues get escalated to the site administrator.</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->
HTML;
}

function ss_seed_accessibility_content() {
	return <<<HTML
<!-- wp:paragraph {"className":"ss-lead"} -->
<p class="ss-lead">Study Spots SJ aims to meet the Web Content Accessibility Guidelines (WCAG) 2.2 at level AA.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">What we do</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Color contrast of at least 4.5:1 for text in both day and night themes.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Everything works with a keyboard, with a visible focus outline and a “Skip to content” link.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Animations are turned off when your device has “Reduce motion” enabled.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Ratings are shown as text (for example “4/5”), not just as colored bars.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>The map is optional: every spot on it is also listed as a regular link.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Forms have visible labels, grouped options and error messages connected to each field.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Pages are tested with automated tools (axe, Lighthouse) and manually with a keyboard and VoiceOver.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Known limitations</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Map tiles come from a third-party service, and the street names on the map image are not readable by screen readers. Addresses are always provided in text on each spot’s page.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Found a barrier?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Please tell us which page and what happened by opening an issue on the <a href="https://github.com/VRAJ8/study-spots-sj/issues">Study Spots SJ issue tracker on GitHub</a>. We aim to respond within five days.</p>
<!-- /wp:paragraph -->
HTML;
}

function ss_seed_about_content() {
	return <<<HTML
<!-- wp:paragraph {"className":"ss-lead"} -->
<p class="ss-lead">Study Spots SJ is a student-built guide to the best places to study around San José State, run like a real university website: structured content, roles, review before publishing, and regular audits.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">How the site is run</h2>
<!-- /wp:heading -->

<!-- wp:columns {"className":"ss-feature-cols"} -->
<div class="wp-block-columns ss-feature-cols"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Structured content</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every spot uses the same template and fields (ratings, address, hours, map pin), so pages stay consistent and data feeds the cards, filters and map automatically.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Review before publish</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Students suggest spots and Scouts draft them, but only Editors can publish. Nothing reaches the live site without a second pair of eyes.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Reusable components</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Shared elements such as the “Suggest a spot” banner and the demo notice are synced patterns: edit once, and they update on every page.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Built with</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>WordPress (block theme and custom plugin), Leaflet with OpenStreetMap map tiles, and self-hosted Bricolage Grotesque, Inter and Instrument Serif fonts. Tested with axe, Lighthouse, a broken-link checker, and manual keyboard and screen-reader passes across desktop and mobile.</p>
<!-- /wp:paragraph -->
HTML;
}

function ss_seed_menu( $pages ) {
	// Block themes use wp_navigation posts.
	if ( get_page_by_path( 'primary', OBJECT, 'wp_navigation' ) ) {
		return;
	}
	$links = array(
		array( 'Explore', home_url( '/spots/' ) ),
		array( 'Suggest a spot', get_permalink( $pages['suggest-a-spot'] ) ),
		array( 'Contributor guide', get_permalink( $pages['contributor-guide'] ) ),
		array( 'About', get_permalink( $pages['about'] ) ),
	);
	$content = '';
	foreach ( $links as $l ) {
		$content .= sprintf( '<!-- wp:navigation-link {"label":"%1$s","url":"%2$s","kind":"custom"} /-->', esc_attr( $l[0] ), esc_url( wp_make_link_relative( $l[1] ) ) );
	}
	wp_insert_post(
		array(
			'post_type'    => 'wp_navigation',
			'post_status'  => 'publish',
			'post_name'    => 'primary',
			'post_title'   => 'Primary',
			'post_content' => $content,
		)
	);
}

function ss_seed() {
	ss_register_content_model();
	ss_register_roles();
	ss_seed_terms();
	$users  = ss_seed_users();
	ss_seed_spots( $users );
	$synced = ss_seed_synced_patterns();
	$pages  = ss_seed_pages( $synced );
	ss_seed_menu( $pages );

	// Remove WordPress's sample content.
	foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as $sample ) {
		$post = get_page_by_path( $sample[0], OBJECT, $sample[1] );
		if ( $post ) {
			wp_delete_post( $post->ID, true ); // Also removes its comments.
		}
	}
	update_option( 'default_comment_status', 'closed' );

	update_option( 'blogname', 'Study Spots SJ' );
	update_option( 'blogdescription', 'The student guide to the best places to study around San José State.' );
	// Set the structure on the live rewrite object too, or the flush below
	// regenerates rules for the old structure (every unknown URL then 301s home).
	global $wp_rewrite;
	$wp_rewrite->set_permalink_structure( '/%postname%/' );
	flush_rewrite_rules();
	return 'seeded';
}
