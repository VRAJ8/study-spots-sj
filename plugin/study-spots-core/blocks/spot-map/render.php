<?php
/**
 * Spot Map. Pins come from structured lat/lng fields. The map is a visual
 * extra — every spot is also reachable from the grid/list, so keyboard and
 * screen-reader users never depend on it.
 *
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$spots = is_singular( 'spot' ) ? array( ss_get_spot( get_the_ID() ) ) : ss_published_spots();
$pins  = array();
foreach ( $spots as $spot ) {
	if ( '' === $spot['lat'] || '' === $spot['lng'] ) {
		continue;
	}
	$pins[] = array(
		'title' => $spot['title'],
		'url'   => $spot['url'],
		'lat'   => (float) $spot['lat'],
		'lng'   => (float) $spot['lng'],
		'area'  => $spot['areas'] ? $spot['areas'][0]->name : '',
		'score' => $spot['score'],
		'hue'   => $spot['vibes'] ? ss_vibe_hue( $spot['vibes'][0] ) : 260,
		'id'    => $spot['id'],
	);
}
if ( ! $pins ) {
	return;
}
$height = max( 240, (int) $attributes['height'] );
?>
<figure <?php echo get_block_wrapper_attributes( array( 'class' => 'ss-map' ) ); ?>>
	<div class="ss-map__canvas" style="height: <?php echo esc_attr( $height ); ?>px" data-ss-map="<?php echo esc_attr( wp_json_encode( $pins ) ); ?>" data-ss-leaflet-js="<?php echo esc_url( SS_CORE_URL . 'assets/vendor/leaflet/leaflet.js' ); ?>" data-ss-leaflet-css="<?php echo esc_url( SS_CORE_URL . 'assets/vendor/leaflet/leaflet.css' ); ?>" role="region" aria-label="<?php esc_attr_e( 'Map of study spots', 'study-spots' ); ?>"></div>
	<figcaption class="ss-map__caption">
		<?php
		if ( is_singular( 'spot' ) ) {
			esc_html_e( 'Map pin is approximate. Use the address above for directions.', 'study-spots' );
		} else {
			/* translators: %d: number of pins */
			printf( esc_html( _n( '%d spot on the map. Every spot is also listed below.', '%d spots on the map. Every spot is also listed below.', count( $pins ), 'study-spots' ) ), count( $pins ) );
		}
		?>
	</figcaption>
</figure>
