<?php
/**
 * Roles and permissions.
 *
 * Spot Scout  – can draft and submit spots for review, edit only their own, never publish.
 * Spot Editor – reviews the queue, publishes, edits anyone's spots, manages areas/vibes.
 * Administrator – everything, plus site settings.
 */

defined( 'ABSPATH' ) || exit;

function ss_spot_caps( $level ) {
	$scout = array(
		'read'                => true,
		'upload_files'        => true,
		'edit_spots'          => true,
		'delete_spots'        => true,
	);
	$editor = array_merge(
		$scout,
		array(
			'publish_spots'          => true,
			'edit_others_spots'      => true,
			'edit_published_spots'   => true,
			'delete_others_spots'    => true,
			'delete_published_spots' => true,
			'read_private_spots'     => true,
			'edit_private_spots'     => true,
			'delete_private_spots'   => true,
			'manage_spot_terms'      => true,
		)
	);
	return 'editor' === $level ? $editor : $scout;
}

function ss_register_roles() {
	remove_role( 'spot_scout' );
	remove_role( 'spot_editor' );
	add_role( 'spot_scout', __( 'Spot Scout', 'study-spots' ), ss_spot_caps( 'scout' ) );
	add_role( 'spot_editor', __( 'Spot Editor', 'study-spots' ), ss_spot_caps( 'editor' ) );

	foreach ( array( 'administrator', 'editor' ) as $role_name ) {
		$role = get_role( $role_name );
		if ( $role ) {
			foreach ( ss_spot_caps( 'editor' ) as $cap => $grant ) {
				$role->add_cap( $cap, $grant );
			}
		}
	}
	update_option( 'ss_roles_version', SS_CORE_VERSION );
}

add_action(
	'init',
	function () {
		if ( get_option( 'ss_roles_version' ) !== SS_CORE_VERSION ) {
			ss_register_roles();
		}
	}
);

/**
 * Scouts only see their own spots in the admin list — mirrors per-department
 * permissions in a university CMS.
 */
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || 'spot' !== $query->get( 'post_type' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_others_spots' ) ) {
			$query->set( 'author', get_current_user_id() );
		}
	}
);
