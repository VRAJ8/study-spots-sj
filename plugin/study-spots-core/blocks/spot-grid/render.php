<?php
/**
 * Spot Grid. Server-rendered list of every card; filtering is progressive
 * enhancement (grid.js), so the full list works with JavaScript off.
 *
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$exclude = array();
if ( ! empty( $attributes['excludeCurrent'] ) && is_singular( 'spot' ) ) {
	$exclude[] = get_the_ID();
}
$spots = ss_published_spots( (int) $attributes['limit'], $exclude );
$vibes = get_terms( array( 'taxonomy' => 'spot_vibe', 'hide_empty' => true ) );
$uid   = wp_unique_id( 'ss-grid-' );
$level = max( 2, min( 4, (int) $attributes['headingLevel'] ) );

if ( ! $spots ) {
	echo '<p>' . esc_html__( 'No spots published yet.', 'study-spots' ) . '</p>';
	return;
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'ss-grid' ) ); ?> data-ss-grid>
	<?php if ( $attributes['showFilters'] ) : ?>
		<div class="ss-filters" role="search" aria-label="<?php esc_attr_e( 'Filter spots', 'study-spots' ); ?>">
			<div class="ss-filters__row">
				<label class="ss-search" for="<?php echo esc_attr( $uid ); ?>-q">
					<span class="screen-reader-text"><?php esc_html_e( 'Search spots', 'study-spots' ); ?></span>
					<svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="m20 20-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
					<input type="search" id="<?php echo esc_attr( $uid ); ?>-q" placeholder="<?php esc_attr_e( 'Search “library”, “late night”, “Japantown”…', 'study-spots' ); ?>" data-ss-q autocomplete="off">
				</label>
				<label class="ss-sort" for="<?php echo esc_attr( $uid ); ?>-sort">
					<span><?php esc_html_e( 'Sort', 'study-spots' ); ?></span>
					<select id="<?php echo esc_attr( $uid ); ?>-sort" data-ss-sort>
						<option value="score"><?php esc_html_e( 'Best overall', 'study-spots' ); ?></option>
						<option value="quiet"><?php esc_html_e( 'Quietest first', 'study-spots' ); ?></option>
						<option value="az"><?php esc_html_e( 'A–Z', 'study-spots' ); ?></option>
					</select>
				</label>
			</div>
			<div class="ss-chips" role="group" aria-label="<?php esc_attr_e( 'Vibes', 'study-spots' ); ?>">
				<?php foreach ( $vibes as $vibe ) : ?>
					<button type="button" class="ss-chip" aria-pressed="false" data-ss-vibe="<?php echo esc_attr( $vibe->slug ); ?>" style="--ss-hue:<?php echo esc_attr( ss_vibe_hue( $vibe ) ); ?>"><?php echo esc_html( $vibe->name ); ?></button>
				<?php endforeach; ?>
				<button type="button" class="ss-chip ss-chip--late" aria-pressed="false" data-ss-late><?php esc_html_e( 'Open after 9pm', 'study-spots' ); ?></button>
			</div>
			<p class="ss-filters__status" aria-live="polite" data-ss-status></p>
		</div>
	<?php endif; ?>

	<ul class="ss-cards" data-ss-cards>
		<?php
		foreach ( $spots as $spot ) {
			echo ss_render_card( $spot, $level ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
	</ul>
	<div class="ss-empty" data-ss-empty hidden>
		<p class="ss-empty__title"><?php esc_html_e( 'No spots match that combo… yet.', 'study-spots' ); ?></p>
		<p><a href="<?php echo esc_url( home_url( '/suggest-a-spot/' ) ); ?>"><?php esc_html_e( 'Know one? Suggest it', 'study-spots' ); ?></a></p>
	</div>
</div>
