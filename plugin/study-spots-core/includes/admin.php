<?php
/**
 * Admin tooling for editors: review queue, content-freshness audit,
 * list-table columns and a CSV content-audit export.
 */

defined( 'ABSPATH' ) || exit;

const SS_STALE_DAYS = 180;

function ss_is_stale( $post_id ) {
	$date = get_post_meta( $post_id, 'ss_last_verified', true );
	return ! $date || ( time() - strtotime( $date ) ) > SS_STALE_DAYS * DAY_IN_SECONDS;
}

/**
 * Audit checks run against every spot. Each returns a human-readable
 * problem or null. Used by the dashboard widget and the CSV export.
 */
function ss_audit_spot( $post_id ) {
	$issues  = array();
	$content = get_post_field( 'post_content', $post_id );

	if ( ss_is_stale( $post_id ) ) {
		$issues[] = __( 'Not verified in 6+ months', 'study-spots' );
	}
	if ( ! get_post_meta( $post_id, 'ss_lat', true ) || ! get_post_meta( $post_id, 'ss_lng', true ) ) {
		$issues[] = __( 'Missing map pin', 'study-spots' );
	}
	if ( ! has_excerpt( $post_id ) ) {
		$issues[] = __( 'Missing summary (used for cards and SEO)', 'study-spots' );
	}
	if ( ! wp_get_post_terms( $post_id, 'spot_vibe', array( 'fields' => 'ids' ) ) ) {
		$issues[] = __( 'No vibe tags', 'study-spots' );
	}
	if ( preg_match( '/<img(?![^>]*\balt="[^"]+")[^>]*>/i', $content ) ) {
		$issues[] = __( 'Image missing alt text', 'study-spots' );
	}
	if ( preg_match( '/>\s*(click here|here|read more|link)\s*<\/a>/i', $content ) ) {
		$issues[] = __( 'Vague link text', 'study-spots' );
	}
	if ( str_word_count( wp_strip_all_tags( $content ) ) < 40 ) {
		$issues[] = __( 'Thin content (< 40 words)', 'study-spots' );
	}
	return $issues;
}

/* ---------- Dashboard widgets ---------- */

add_action(
	'wp_dashboard_setup',
	function () {
		if ( current_user_can( 'publish_spots' ) ) {
			wp_add_dashboard_widget( 'ss_review_queue', __( 'Spot review queue', 'study-spots' ), 'ss_widget_queue', null, null, 'normal', 'high' );
			wp_add_dashboard_widget( 'ss_audit', __( 'Content health', 'study-spots' ), 'ss_widget_audit', null, null, 'side', 'high' );
		}
		if ( current_user_can( 'edit_spots' ) ) {
			wp_add_dashboard_widget( 'ss_help', __( 'Contributor quick guide', 'study-spots' ), 'ss_widget_help', null, null, 'normal', 'high' );
		}
	}
);

function ss_widget_queue() {
	$pending = get_posts( array( 'post_type' => 'spot', 'post_status' => 'pending', 'posts_per_page' => 10 ) );
	if ( ! $pending ) {
		echo '<p>' . esc_html__( 'Inbox zero. Nothing waiting for review.', 'study-spots' ) . '</p>';
		return;
	}
	echo '<ul class="ss-admin-list">';
	foreach ( $pending as $p ) {
		printf(
			'<li><a href="%1$s"><strong>%2$s</strong></a> <span class="ss-admin-meta">%3$s · %4$s</span></li>',
			esc_url( get_edit_post_link( $p->ID ) ),
			esc_html( get_the_title( $p ) ),
			esc_html( get_post_meta( $p->ID, 'ss_submitter', true ) ? get_post_meta( $p->ID, 'ss_submitter', true ) : get_the_author_meta( 'display_name', $p->post_author ) ),
			// Pending posts have no GMT date yet, so compare in site-local time.
			/* translators: %s: human time diff */
			esc_html( sprintf( __( '%s ago', 'study-spots' ), human_time_diff( get_post_time( 'U', false, $p ), current_time( 'timestamp' ) ) ) )
		);
	}
	echo '</ul>';
	printf( '<p><a class="button" href="%s">%s</a></p>', esc_url( admin_url( 'edit.php?post_status=pending&post_type=spot' ) ), esc_html__( 'Open full queue', 'study-spots' ) );
}

function ss_widget_audit() {
	$ids    = get_posts( array( 'post_type' => 'spot', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) );
	$flags  = array();
	foreach ( $ids as $id ) {
		$issues = ss_audit_spot( $id );
		if ( $issues ) {
			$flags[ $id ] = $issues;
		}
	}
	$healthy = count( $ids ) - count( $flags );
	printf(
		'<p class="ss-health"><span class="ss-health__num">%1$d/%2$d</span> %3$s</p>',
		(int) $healthy,
		count( $ids ),
		esc_html__( 'published spots pass every check', 'study-spots' )
	);
	if ( $flags ) {
		echo '<ul class="ss-admin-list">';
		foreach ( array_slice( $flags, 0, 8, true ) as $id => $issues ) {
			printf(
				'<li><a href="%1$s">%2$s</a><br><span class="ss-admin-meta">%3$s</span></li>',
				esc_url( get_edit_post_link( $id ) ),
				esc_html( get_the_title( $id ) ),
				esc_html( implode( ' · ', $issues ) )
			);
		}
		echo '</ul>';
	}
	printf(
		'<p><a class="button" href="%s">%s</a></p>',
		esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ss_audit_csv' ), 'ss_audit_csv' ) ),
		esc_html__( 'Download content audit (CSV)', 'study-spots' )
	);
}

function ss_widget_help() {
	?>
	<ol class="ss-admin-steps">
		<li><?php esc_html_e( 'Spots → Add New. The layout is pre-built; just fill in the two sections.', 'study-spots' ); ?></li>
		<li><?php esc_html_e( 'Open the "Spot details" panel in the right sidebar for ratings, address and map pin.', 'study-spots' ); ?></li>
		<li><?php esc_html_e( 'Add an excerpt. It appears on cards and in search results.', 'study-spots' ); ?></li>
		<li><?php esc_html_e( 'Preview on desktop and mobile (top-right preview menu).', 'study-spots' ); ?></li>
		<li><?php esc_html_e( 'Click "Submit for Review". An editor will publish it.', 'study-spots' ); ?></li>
	</ol>
	<p><a href="<?php echo esc_url( home_url( '/contributor-guide/' ) ); ?>"><?php esc_html_e( 'Read the full contributor guide', 'study-spots' ); ?></a></p>
	<?php
}

/* ---------- CSV export ---------- */

add_action(
	'admin_post_ss_audit_csv',
	function () {
		if ( ! current_user_can( 'publish_spots' ) || ! check_admin_referer( 'ss_audit_csv' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'study-spots' ) );
		}
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=study-spots-content-audit-' . gmdate( 'Y-m-d' ) . '.csv' );
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'ID', 'Title', 'Status', 'URL', 'Area', 'Vibes', 'Words', 'Last verified', 'Last modified', 'Issues' ) );
		$ids = get_posts( array( 'post_type' => 'spot', 'post_status' => array( 'publish', 'pending', 'draft' ), 'posts_per_page' => -1, 'fields' => 'ids' ) );
		foreach ( $ids as $id ) {
			fputcsv(
				$out,
				array(
					$id,
					get_the_title( $id ),
					get_post_status( $id ),
					get_permalink( $id ),
					implode( '; ', wp_get_post_terms( $id, 'spot_area', array( 'fields' => 'names' ) ) ),
					implode( '; ', wp_get_post_terms( $id, 'spot_vibe', array( 'fields' => 'names' ) ) ),
					str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $id ) ) ),
					get_post_meta( $id, 'ss_last_verified', true ),
					get_the_modified_date( 'Y-m-d', $id ),
					implode( '; ', ss_audit_spot( $id ) ),
				)
			);
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
);

/* ---------- List table columns ---------- */

add_filter(
	'manage_spot_posts_columns',
	function ( $cols ) {
		$new = array();
		foreach ( $cols as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['ss_score']    = __( 'Score', 'study-spots' );
				$new['ss_verified'] = __( 'Verified', 'study-spots' );
			}
		}
		return $new;
	}
);

add_action(
	'manage_spot_posts_custom_column',
	function ( $col, $post_id ) {
		if ( 'ss_score' === $col ) {
			$s = ss_get_spot( $post_id );
			echo esc_html( number_format( $s['score'], 1 ) );
		}
		if ( 'ss_verified' === $col ) {
			$date = get_post_meta( $post_id, 'ss_last_verified', true );
			$text = $date ? date_i18n( 'M j, Y', strtotime( $date ) ) : __( 'Never', 'study-spots' );
			if ( ss_is_stale( $post_id ) ) {
				printf( '<span class="ss-badge ss-badge--warn">%s</span>', esc_html( $text ) );
			} else {
				echo esc_html( $text );
			}
		}
	},
	10,
	2
);

/* ---------- Pending count bubble in the menu ---------- */

add_action(
	'admin_menu',
	function () {
		global $menu;
		$count = wp_count_posts( 'spot' )->pending ?? 0;
		if ( ! $count || ! current_user_can( 'publish_spots' ) ) {
			return;
		}
		foreach ( $menu as $i => $item ) {
			if ( 'edit.php?post_type=spot' === $item[2] ) {
				$menu[ $i ][0] .= sprintf( ' <span class="awaiting-mod"><span class="pending-count">%d</span></span>', (int) $count ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			}
		}
	},
	999
);

add_action(
	'admin_head',
	function () {
		?>
		<style>
			.ss-admin-list{margin:0}.ss-admin-list li{padding:8px 0;border-bottom:1px solid #f0f0f1}
			.ss-admin-meta{color:#646970;font-size:12px}
			.ss-health{display:flex;align-items:baseline;gap:8px;margin-top:0}.ss-health__num{font-size:28px;font-weight:700;color:#1d2327}
			.ss-badge--warn{background:#fcf0e3;color:#8a4b00;padding:2px 8px;border-radius:99px;font-weight:600}
			.ss-admin-steps li{margin-bottom:6px}
			.column-ss_score,.column-ss_verified{width:110px}
		</style>
		<?php
	}
);

/* ---------- Focused admin: hide what this site doesn't use ---------- */

add_action( 'admin_init', fn() => remove_action( 'welcome_panel', 'wp_welcome_panel' ) );

add_action(
	'wp_dashboard_setup',
	function () {
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
	},
	20
);

add_action(
	'admin_menu',
	function () {
		remove_menu_page( 'edit.php' );          // No blog on this site.
		remove_menu_page( 'edit-comments.php' ); // Comments are off.
	}
);

// Comments off everywhere.
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
