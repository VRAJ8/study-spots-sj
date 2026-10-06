<?php
/**
 * Spot Stats: live counts pulled from the content model.
 */

defined( 'ABSPATH' ) || exit;

$spots = ss_published_spots();
$late  = count( array_filter( $spots, fn( $s ) => (bool) $s['open_late'] ) );
$areas = wp_count_terms( array( 'taxonomy' => 'spot_area', 'hide_empty' => true ) );
$stats = array(
	array( count( $spots ), __( 'spots reviewed', 'study-spots' ) ),
	array( is_wp_error( $areas ) ? 0 : (int) $areas, __( 'neighborhoods', 'study-spots' ) ),
	array( $late, __( 'open after 9pm', 'study-spots' ) ),
);
?>
<ul <?php echo get_block_wrapper_attributes( array( 'class' => 'ss-stats' ) ); ?>>
	<?php foreach ( $stats as $stat ) : ?>
		<li class="ss-stats__item">
			<span class="ss-stats__num" data-ss-count="<?php echo esc_attr( $stat[0] ); ?>"><?php echo esc_html( $stat[0] ); ?></span>
			<span class="ss-stats__label"><?php echo esc_html( $stat[1] ); ?></span>
		</li>
	<?php endforeach; ?>
</ul>
