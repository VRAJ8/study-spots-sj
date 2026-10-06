<?php
/**
 * Public "Suggest a spot" submissions. Every submission lands in the
 * review queue as a Pending spot — nothing goes live without an editor.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_nopriv_ss_submit_spot', 'ss_handle_submission' );
add_action( 'admin_post_ss_submit_spot', 'ss_handle_submission' );

function ss_handle_submission() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/suggest-a-spot/' );
	$back = remove_query_arg( array( 'ss_status', 'ss_errors' ), $back );

	if ( ! isset( $_POST['ss_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ss_nonce'] ), 'ss_submit_spot' ) ) {
		wp_safe_redirect( add_query_arg( 'ss_status', 'expired', $back ) . '#ss-form' );
		exit;
	}

	// Honeypot: real people never fill the hidden "company" field.
	if ( ! empty( $_POST['company'] ) ) {
		wp_safe_redirect( add_query_arg( 'ss_status', 'thanks', $back ) . '#ss-form' );
		exit;
	}

	// Simple rate limit: 3 submissions per visitor per hour.
	$ip_key = 'ss_rl_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown' );
	$count  = (int) get_transient( $ip_key );
	if ( $count >= 3 ) {
		wp_safe_redirect( add_query_arg( 'ss_status', 'slowdown', $back ) . '#ss-form' );
		exit;
	}

	$name    = sanitize_text_field( wp_unslash( $_POST['spot_name'] ?? '' ) );
	$address = sanitize_text_field( wp_unslash( $_POST['spot_address'] ?? '' ) );
	$why     = sanitize_textarea_field( wp_unslash( $_POST['spot_why'] ?? '' ) );
	$area    = absint( $_POST['spot_area'] ?? 0 );
	$vibes   = array_map( 'absint', (array) ( $_POST['spot_vibes'] ?? array() ) );
	$by      = sanitize_text_field( wp_unslash( $_POST['spot_by'] ?? '' ) );

	$errors = array();
	if ( strlen( $name ) < 2 ) {
		$errors[] = 'name';
	}
	if ( strlen( $address ) < 4 ) {
		$errors[] = 'address';
	}
	if ( strlen( $why ) < 20 ) {
		$errors[] = 'why';
	}
	if ( $errors ) {
		wp_safe_redirect( add_query_arg( array( 'ss_status' => 'error', 'ss_errors' => implode( ',', $errors ) ), $back ) . '#ss-form' );
		exit;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'spot',
			'post_status'  => 'pending',
			'post_title'   => $name,
			'post_excerpt' => wp_trim_words( $why, 24 ),
			'post_content' => ss_submission_content( $why ),
			'post_author'  => get_current_user_id() ? get_current_user_id() : ss_bot_author(),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_safe_redirect( add_query_arg( 'ss_status', 'error', $back ) . '#ss-form' );
		exit;
	}

	foreach ( array( 'wifi', 'quiet', 'outlets' ) as $rating ) {
		update_post_meta( $post_id, 'ss_' . $rating, ss_sanitize_rating( $_POST[ 'spot_' . $rating ] ?? 3 ) );
	}
	update_post_meta( $post_id, 'ss_address', $address );
	update_post_meta( $post_id, 'ss_open_late', ! empty( $_POST['spot_late'] ) );
	update_post_meta( $post_id, 'ss_submitter', $by ? $by : __( 'Anonymous Spartan', 'study-spots' ) );

	if ( $area && term_exists( $area, 'spot_area' ) ) {
		wp_set_object_terms( $post_id, array( $area ), 'spot_area' );
	}
	$vibes = array_filter( $vibes, fn( $id ) => term_exists( $id, 'spot_vibe' ) );
	if ( $vibes ) {
		wp_set_object_terms( $post_id, array_values( $vibes ), 'spot_vibe' );
	}

	set_transient( $ip_key, $count + 1, HOUR_IN_SECONDS );

	wp_safe_redirect( add_query_arg( 'ss_status', 'thanks', $back ) . '#ss-form' );
	exit;
}

/**
 * Wrap the submitted text in the same block structure the post-type
 * template uses, so editors review it in a familiar layout.
 */
function ss_submission_content( $why ) {
	$paragraphs = array_filter( array_map( 'trim', preg_split( "/\n\s*\n/", $why ) ) );
	$html       = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Why it works for studying', 'study-spots' ) . "</h2><!-- /wp:heading -->\n";
	foreach ( $paragraphs as $p ) {
		$html .= '<!-- wp:paragraph --><p>' . esc_html( $p ) . "</p><!-- /wp:paragraph -->\n";
	}
	$html .= '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Good to know', 'study-spots' ) . "</h2><!-- /wp:heading -->\n";
	$html .= '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>' . esc_html__( 'Editor: add tips after visiting.', 'study-spots' ) . '</li><!-- /wp:list-item --></ul><!-- /wp:list -->';
	return $html;
}

/**
 * Anonymous submissions are owned by a dedicated, login-disabled account
 * so they never get attributed to a real staff member.
 */
function ss_bot_author() {
	$user = get_user_by( 'login', 'community-submissions' );
	if ( $user ) {
		return $user->ID;
	}
	$id = wp_insert_user(
		array(
			'user_login'   => 'community-submissions',
			'user_pass'    => wp_generate_password( 32, true, true ),
			'display_name' => __( 'Community Submission', 'study-spots' ),
			'role'         => 'spot_scout',
		)
	);
	return is_wp_error( $id ) ? 1 : $id;
}
