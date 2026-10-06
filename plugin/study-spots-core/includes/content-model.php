<?php
/**
 * Content model: the "Spot" post type, its taxonomies and structured fields.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Structured fields stored on every spot. One definition drives REST
 * registration, the editor sidebar, the submission form and validation.
 */
function ss_spot_fields() {
	return array(
		'ss_wifi'          => array( 'type' => 'integer', 'label' => __( 'Wi-Fi', 'study-spots' ), 'rating' => true ),
		'ss_quiet'         => array( 'type' => 'integer', 'label' => __( 'Quiet', 'study-spots' ), 'rating' => true ),
		'ss_outlets'       => array( 'type' => 'integer', 'label' => __( 'Outlets', 'study-spots' ), 'rating' => true ),
		'ss_address'       => array( 'type' => 'string', 'label' => __( 'Address', 'study-spots' ) ),
		'ss_hours'         => array( 'type' => 'string', 'label' => __( 'Hours', 'study-spots' ) ),
		'ss_price'         => array( 'type' => 'string', 'label' => __( 'Cost', 'study-spots' ) ),
		'ss_website'       => array( 'type' => 'string', 'label' => __( 'Website', 'study-spots' ) ),
		'ss_lat'           => array( 'type' => 'number', 'label' => __( 'Latitude', 'study-spots' ) ),
		'ss_lng'           => array( 'type' => 'number', 'label' => __( 'Longitude', 'study-spots' ) ),
		'ss_open_late'     => array( 'type' => 'boolean', 'label' => __( 'Open after 9pm', 'study-spots' ) ),
		'ss_last_verified' => array( 'type' => 'string', 'label' => __( 'Last verified (YYYY-MM-DD)', 'study-spots' ) ),
		'ss_submitter'     => array( 'type' => 'string', 'label' => __( 'Submitted by', 'study-spots' ) ),
	);
}

function ss_register_content_model() {
	register_post_type(
		'spot',
		array(
			'labels'          => array(
				'name'               => __( 'Spots', 'study-spots' ),
				'singular_name'      => __( 'Spot', 'study-spots' ),
				'add_new_item'       => __( 'Add New Spot', 'study-spots' ),
				'edit_item'          => __( 'Edit Spot', 'study-spots' ),
				'view_item'          => __( 'View Spot', 'study-spots' ),
				'all_items'          => __( 'All Spots', 'study-spots' ),
				'search_items'       => __( 'Search Spots', 'study-spots' ),
				'not_found'          => __( 'No spots found.', 'study-spots' ),
				'item_published'     => __( 'Spot published.', 'study-spots' ),
				'item_scheduled'     => __( 'Spot scheduled.', 'study-spots' ),
			),
			'public'          => true,
			'show_in_rest'    => true,
			'menu_icon'       => 'dashicons-location-alt',
			'menu_position'   => 5,
			'has_archive'     => 'spots',
			'rewrite'         => array( 'slug' => 'spots', 'with_front' => false ),
			'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'author' ),
			'capability_type' => array( 'spot', 'spots' ),
			'map_meta_cap'    => true,
			// Every new spot starts from the same locked layout, like a CMS page template.
			'template'        => array(
				array( 'core/heading', array( 'level' => 2, 'content' => __( 'Why it works for studying', 'study-spots' ) ) ),
				array( 'core/paragraph', array( 'placeholder' => __( 'Describe the vibe, the best seats, and when it gets busy…', 'study-spots' ) ) ),
				array( 'core/heading', array( 'level' => 2, 'content' => __( 'Good to know', 'study-spots' ) ) ),
				array( 'core/list', array(), array( array( 'core/list-item', array( 'placeholder' => __( 'Tip…', 'study-spots' ) ) ) ) ),
			),
			'template_lock'   => 'insert',
		)
	);

	register_taxonomy(
		'spot_area',
		'spot',
		array(
			'labels'            => array(
				'name'          => __( 'Areas', 'study-spots' ),
				'singular_name' => __( 'Area', 'study-spots' ),
			),
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'area' ),
			'capabilities'      => ss_term_caps(),
		)
	);

	register_taxonomy(
		'spot_vibe',
		'spot',
		array(
			'labels'            => array(
				'name'          => __( 'Vibes', 'study-spots' ),
				'singular_name' => __( 'Vibe', 'study-spots' ),
			),
			'hierarchical'      => false,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'vibe' ),
			'capabilities'      => ss_term_caps(),
		)
	);

	foreach ( ss_spot_fields() as $key => $field ) {
		$args = array(
			'type'          => $field['type'],
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_spots' );
			},
		);
		if ( ! empty( $field['rating'] ) ) {
			$args['sanitize_callback'] = 'ss_sanitize_rating';
			$args['default']           = 3;
		}
		register_post_meta( 'spot', $key, $args );
	}
}
add_action( 'init', 'ss_register_content_model' );

function ss_term_caps() {
	return array(
		'manage_terms' => 'manage_spot_terms',
		'edit_terms'   => 'manage_spot_terms',
		'delete_terms' => 'manage_spot_terms',
		'assign_terms' => 'edit_spots',
	);
}

function ss_sanitize_rating( $value ) {
	return max( 1, min( 5, (int) $value ) );
}

/**
 * Convenience getter used by blocks and templates.
 */
function ss_get_spot( $post_id ) {
	$data = array(
		'id'      => $post_id,
		'title'   => get_the_title( $post_id ),
		'url'     => get_permalink( $post_id ),
		'excerpt' => get_the_excerpt( $post_id ),
	);
	foreach ( array_keys( ss_spot_fields() ) as $key ) {
		$data[ substr( $key, 3 ) ] = get_post_meta( $post_id, $key, true );
	}
	$data['vibes'] = wp_get_post_terms( $post_id, 'spot_vibe', array( 'fields' => 'all' ) );
	$data['areas'] = wp_get_post_terms( $post_id, 'spot_area', array( 'fields' => 'all' ) );
	$data['score'] = round( ( (int) $data['wifi'] + (int) $data['quiet'] + (int) $data['outlets'] ) / 3, 1 );
	return $data;
}

/**
 * Each vibe gets an accent hue so cards, chips and map pins stay consistent.
 * Stored as term meta so editors can change it without touching code.
 */
function ss_vibe_hue( $term ) {
	$hue = get_term_meta( $term->term_id, 'ss_hue', true );
	return '' === $hue ? 260 : (int) $hue;
}

/**
 * Area/vibe archive URLs forward to the explorer with the filter applied,
 * so there is one canonical, filterable listing instead of thin duplicates.
 */
add_action(
	'template_redirect',
	function () {
		if ( is_tax( 'spot_vibe' ) ) {
			wp_safe_redirect( add_query_arg( 'vibe', get_queried_object()->slug, get_post_type_archive_link( 'spot' ) ), 301 );
			exit;
		}
		if ( is_tax( 'spot_area' ) ) {
			wp_safe_redirect( add_query_arg( 'q', rawurlencode( strtolower( get_queried_object()->name ) ), get_post_type_archive_link( 'spot' ) ), 301 );
			exit;
		}
	}
);

/**
 * Real 404s instead of WordPress "guessing" a nearby URL. Guessing hides
 * broken links from audits and can send visitors somewhere unrelated.
 */
add_filter( 'do_redirect_guess_404_permalink', '__return_false' );
