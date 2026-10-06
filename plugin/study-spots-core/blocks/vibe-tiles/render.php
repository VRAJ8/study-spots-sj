<?php
/**
 * Vibe Tiles: one tile per vibe, each a link to the pre-filtered explore page.
 */

defined( 'ABSPATH' ) || exit;

$vibes = get_terms( array( 'taxonomy' => 'spot_vibe', 'hide_empty' => true ) );
if ( ! $vibes || is_wp_error( $vibes ) ) {
	return;
}
$archive = get_post_type_archive_link( 'spot' );
?>
<ul <?php echo get_block_wrapper_attributes( array( 'class' => 'ss-vibes' ) ); ?>>
	<?php foreach ( $vibes as $i => $vibe ) : ?>
		<li class="ss-vibe" style="--ss-hue:<?php echo esc_attr( ss_vibe_hue( $vibe ) ); ?>;--ss-i:<?php echo (int) $i; ?>">
			<a href="<?php echo esc_url( add_query_arg( 'vibe', $vibe->slug, $archive ) ); ?>">
				<span class="ss-vibe__name"><?php echo esc_html( $vibe->name ); ?></span>
				<span class="ss-vibe__desc"><?php echo esc_html( $vibe->description ); ?></span>
				<span class="ss-vibe__count">
					<?php
					/* translators: %d: number of spots */
					printf( esc_html( _n( '%d spot', '%d spots', $vibe->count, 'study-spots' ) ), (int) $vibe->count );
					?>
				</span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
