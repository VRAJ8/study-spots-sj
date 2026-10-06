<?php
/**
 * Spot Hero for single spot pages.
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
<header <?php echo get_block_wrapper_attributes( array( 'class' => 'ss-hero-spot' ) ); ?>>
	<div class="ss-hero-spot__art" style="<?php echo esc_attr( ss_cover_style( $spot ) ); ?>" aria-hidden="true"></div>
	<div class="ss-hero-spot__inner">
		<nav class="ss-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'study-spots' ); ?>">
			<ol>
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'study-spots' ); ?></a></li>
				<li><a href="<?php echo esc_url( get_post_type_archive_link( 'spot' ) ); ?>"><?php esc_html_e( 'Spots', 'study-spots' ); ?></a></li>
				<li><span aria-current="page"><?php echo esc_html( $spot['title'] ); ?></span></li>
			</ol>
		</nav>
		<?php if ( $spot['areas'] ) : ?>
			<p class="ss-hero-spot__area"><?php echo esc_html( $spot['areas'][0]->name ); ?></p>
		<?php endif; ?>
		<h1 class="ss-hero-spot__title"><?php echo esc_html( $spot['title'] ); ?></h1>
		<p class="ss-hero-spot__excerpt"><?php echo esc_html( $spot['excerpt'] ); ?></p>
		<?php if ( $spot['vibes'] ) : ?>
			<ul class="ss-tags ss-tags--lg" aria-label="<?php esc_attr_e( 'Vibes', 'study-spots' ); ?>">
				<?php foreach ( $spot['vibes'] as $vibe ) : ?>
					<li class="ss-tag" style="--ss-hue:<?php echo esc_attr( ss_vibe_hue( $vibe ) ); ?>"><a href="<?php echo esc_url( add_query_arg( 'vibe', $vibe->slug, get_post_type_archive_link( 'spot' ) ) ); ?>"><?php echo esc_html( $vibe->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</header>
