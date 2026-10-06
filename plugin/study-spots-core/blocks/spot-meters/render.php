<?php
/**
 * Spot Ratings for the current spot.
 *
 * @var WP_Block $block
 */

defined( 'ABSPATH' ) || exit;

$post_id = $block->context['postId'] ?? get_the_ID();
if ( ! $post_id || 'spot' !== get_post_type( $post_id ) ) {
	return;
}
$spot = ss_get_spot( $post_id );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'ss-ratings' ) ); ?> aria-labelledby="ss-ratings-<?php echo esc_attr( $post_id ); ?>">
	<div class="ss-ratings__score">
		<span class="ss-ratings__number"><?php echo esc_html( number_format( $spot['score'], 1 ) ); ?></span>
		<span class="ss-ratings__out"><?php esc_html_e( 'study score / 5', 'study-spots' ); ?></span>
	</div>
	<div class="ss-ratings__meters">
		<h2 class="screen-reader-text" id="ss-ratings-<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Ratings', 'study-spots' ); ?></h2>
		<?php
		echo ss_render_meter( __( 'Wi-Fi', 'study-spots' ), $spot['wifi'], 'lg' ); // phpcs:ignore WordPress.Security.EscapeOutput
		echo ss_render_meter( __( 'Quiet', 'study-spots' ), $spot['quiet'], 'lg' ); // phpcs:ignore WordPress.Security.EscapeOutput
		echo ss_render_meter( __( 'Outlets', 'study-spots' ), $spot['outlets'], 'lg' ); // phpcs:ignore WordPress.Security.EscapeOutput
		?>
	</div>
</section>
