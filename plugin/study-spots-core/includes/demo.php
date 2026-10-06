<?php
/**
 * Demo mode (Playground only): a role switcher and a guided tour so
 * reviewers can experience each permission level without passwords.
 *
 * Active only when the `ss_demo_mode` option is set, which only the public
 * Playground blueprint does. A real install never exposes this.
 */

defined( 'ABSPATH' ) || exit;

function ss_demo_enabled() {
	return (bool) get_option( 'ss_demo_mode' );
}

function ss_demo_accounts() {
	return array(
		'admin'         => __( 'Admin', 'study-spots' ),
		'jordan-editor' => __( 'Jordan (Spot Editor)', 'study-spots' ),
		'maya-scout'    => __( 'Maya (Spot Scout)', 'study-spots' ),
	);
}

add_action(
	'admin_bar_menu',
	function ( $bar ) {
		if ( ! ss_demo_enabled() || ! is_user_logged_in() ) {
			return;
		}
		$me = wp_get_current_user();
		$bar->add_node(
			array(
				'id'    => 'ss-demo',
				/* translators: %s: role label */
				'title' => '<span class="ab-icon dashicons dashicons-groups" style="margin-top:2px"></span>' . esc_html( sprintf( __( 'Viewing as: %s', 'study-spots' ), ss_demo_accounts()[ $me->user_login ] ?? $me->display_name ) ),
			)
		);
		foreach ( ss_demo_accounts() as $login => $label ) {
			if ( $login === $me->user_login ) {
				continue;
			}
			$bar->add_node(
				array(
					'parent' => 'ss-demo',
					'id'     => 'ss-demo-' . $login,
					/* translators: %s: role label */
					'title'  => esc_html( sprintf( __( 'Switch to %s', 'study-spots' ), $label ) ),
					'href'   => wp_nonce_url( admin_url( 'admin-post.php?action=ss_demo_switch&to=' . $login ), 'ss_demo_switch' ),
				)
			);
		}
	},
	100
);

add_action(
	'admin_post_ss_demo_switch',
	function () {
		$to = sanitize_user( wp_unslash( $_GET['to'] ?? '' ) );
		if ( ! ss_demo_enabled() || ! check_admin_referer( 'ss_demo_switch' ) || ! isset( ss_demo_accounts()[ $to ] ) ) {
			wp_die( esc_html__( 'Not available.', 'study-spots' ) );
		}
		$user = get_user_by( 'login', $to );
		if ( ! $user ) {
			wp_die( esc_html__( 'Unknown demo account.', 'study-spots' ) );
		}
		wp_clear_auth_cookie();
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );
		wp_safe_redirect( admin_url( 'edit.php?post_type=spot' ) );
		exit;
	}
);

/**
 * Guided tour on the dashboard and spot list.
 */
add_action(
	'admin_notices',
	function () {
		if ( ! ss_demo_enabled() ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'edit-spot' ), true ) ) {
			return;
		}
		$role = current_user_can( 'manage_options' ) ? 'admin' : ( current_user_can( 'publish_spots' ) ? 'editor' : 'scout' );
		$tips = array(
			'admin'  => array(
				__( 'Open <strong>Spots → Pending</strong> to review a community submission, then Publish it.', 'study-spots' ),
				__( 'Download the <strong>content audit CSV</strong> from the Content health widget.', 'study-spots' ),
				__( 'Use <strong>Viewing as</strong> (top bar) to become Maya, a Spot Scout who can draft but not publish.', 'study-spots' ),
				__( 'Visit <a href="' . esc_url( home_url( '/suggest-a-spot/' ) ) . '">Suggest a spot</a>, submit the form and watch it appear here as Pending.', 'study-spots' ),
			),
			'editor' => array(
				__( 'As an Editor you can publish anyone’s spot and manage Areas and Vibes, but you cannot change site settings or plugins.', 'study-spots' ),
				__( 'Open a Pending spot, check it against the Contributor guide checklist, then Publish.', 'study-spots' ),
			),
			'scout'  => array(
				__( 'As a Scout you only see your own spots. Open “Campus courtyard (draft)”: the button says <strong>Submit for Review</strong>, not Publish.', 'study-spots' ),
				__( 'Try Spots → Add New: the layout is locked to the spot template and the Spot details panel holds the structured fields.', 'study-spots' ),
			),
		);
		echo '<div class="notice notice-info ss-tour"><p><strong>' . esc_html__( 'Demo tour', 'study-spots' ) . '</strong></p><ul style="list-style:disc;margin-left:1.5em">';
		foreach ( $tips[ $role ] as $tip ) {
			echo '<li>' . wp_kses_post( $tip ) . '</li>';
		}
		echo '</ul></div>';
	}
);
