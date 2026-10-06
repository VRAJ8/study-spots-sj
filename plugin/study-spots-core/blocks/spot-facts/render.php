<?php
/**
 * Spot Facts for the current spot, as a description list.
 *
 * @var WP_Block $block
 */

defined( 'ABSPATH' ) || exit;

$post_id = $block->context['postId'] ?? get_the_ID();
if ( ! $post_id || 'spot' !== get_post_type( $post_id ) ) {
	return;
}
$spot = ss_get_spot( $post_id );

$facts = array();
if ( $spot['address'] ) {
	$maps    = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $spot['title'] . ', ' . $spot['address'] );
	$facts[] = array(
		__( 'Address', 'study-spots' ),
		sprintf(
			'%1$s <a href="%2$s" class="ss-facts__link">%3$s<span class="screen-reader-text"> %4$s</span></a>',
			esc_html( $spot['address'] ),
			esc_url( $maps ),
			esc_html__( 'Directions', 'study-spots' ),
			/* translators: %s: spot name */
			esc_html( sprintf( __( 'to %s (opens Google Maps)', 'study-spots' ), $spot['title'] ) )
		),
	);
}
if ( $spot['hours'] ) {
	$facts[] = array( __( 'Hours', 'study-spots' ), esc_html( $spot['hours'] ) );
}
if ( $spot['price'] ) {
	$facts[] = array( __( 'Cost', 'study-spots' ), esc_html( $spot['price'] ) );
}
$facts[] = array( __( 'Late night', 'study-spots' ), $spot['open_late'] ? esc_html__( 'Yes, open after 9pm on most days', 'study-spots' ) : esc_html__( 'Closes before 9pm', 'study-spots' ) );
if ( $spot['website'] ) {
	$facts[] = array(
		__( 'Website', 'study-spots' ),
		sprintf(
			'<a href="%1$s">%2$s<span class="screen-reader-text"> %3$s</span></a>',
			esc_url( $spot['website'] ),
			esc_html( wp_parse_url( $spot['website'], PHP_URL_HOST ) ),
			/* translators: %s: spot name */
			esc_html( sprintf( __( 'for %s', 'study-spots' ), $spot['title'] ) )
		),
	);
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'ss-facts' ) ); ?>>
	<dl class="ss-facts__list">
		<?php foreach ( $facts as $fact ) : ?>
			<div class="ss-facts__item">
				<dt><?php echo esc_html( $fact[0] ); ?></dt>
				<dd><?php echo wp_kses_post( $fact[1] ); ?></dd>
			</div>
		<?php endforeach; ?>
	</dl>
	<?php if ( $spot['last_verified'] ) : ?>
		<p class="ss-facts__verified">
			<svg aria-hidden="true" viewBox="0 0 24 24" width="16" height="16"><path d="m5 12 4.5 4.5L19 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
			<?php
			/* translators: %s: date */
			printf( esc_html__( 'Details last verified %s', 'study-spots' ), '<time datetime="' . esc_attr( $spot['last_verified'] ) . '">' . esc_html( date_i18n( 'M j, Y', strtotime( $spot['last_verified'] ) ) ) . '</time>' );
			?>
		</p>
	<?php endif; ?>
</div>
