<?php
/**
 * Dynamic blocks. Rendered in PHP so templates stay in sync with the
 * structured data — editors place the block once, content updates everywhere.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		wp_register_script(
			'study-spots-editor',
			SS_CORE_URL . 'assets/editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-plugins', 'wp-editor', 'wp-data', 'wp-core-data' ),
			SS_CORE_VERSION,
			true
		);

		// Leaflet is self-hosted and loaded lazily by map.js when a map nears the viewport.
		wp_register_script( 'study-spots-map', SS_CORE_URL . 'assets/map.js', array(), SS_CORE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_register_script( 'study-spots-grid', SS_CORE_URL . 'assets/grid.js', array(), SS_CORE_VERSION, true );

		foreach ( glob( SS_CORE_DIR . 'blocks/*', GLOB_ONLYDIR ) as $dir ) {
			register_block_type( $dir );
		}
	}
);

add_filter(
	'block_categories_all',
	function ( $categories ) {
		array_unshift(
			$categories,
			array(
				'slug'  => 'study-spots',
				'title' => __( 'Study Spots', 'study-spots' ),
				'icon'  => 'location-alt',
			)
		);
		return $categories;
	}
);

// Load the meta-field sidebar in the editor for spots.
add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_enqueue_script( 'study-spots-editor' );
		wp_add_inline_script(
			'study-spots-editor',
			'window.ssFields = ' . wp_json_encode( ss_spot_fields() ) . ';',
			'before'
		);
	}
);

/**
 * Accessible rating meter: a visual bar plus real text for screen readers.
 */
function ss_render_meter( $label, $value, $size = 'md' ) {
	$value = max( 1, min( 5, (int) $value ) );
	ob_start();
	?>
	<div class="ss-meter ss-meter--<?php echo esc_attr( $size ); ?>" style="--ss-value: <?php echo esc_attr( $value ); ?>">
		<span class="ss-meter__label"><?php echo esc_html( $label ); ?></span>
		<span class="ss-meter__track" aria-hidden="true"><span class="ss-meter__fill"></span></span>
		<span class="ss-meter__value">
			<?php
			/* translators: %d: rating out of 5 */
			printf( esc_html__( '%d/5', 'study-spots' ), (int) $value );
			?>
		</span>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Generative "cover art" for a spot: layered gradients seeded from the
 * spot's ID and vibe hue. No stock photos needed, every card is unique.
 */
function ss_cover_style( $spot ) {
	$hue  = ! empty( $spot['vibes'] ) ? ss_vibe_hue( $spot['vibes'][0] ) : 260;
	$seed = crc32( (string) $spot['id'] );
	$x1   = 10 + $seed % 70;
	$y1   = 10 + ( $seed >> 3 ) % 70;
	$x2   = 20 + ( $seed >> 5 ) % 70;
	$hue2 = ( $hue + 40 + ( $seed >> 7 ) % 60 ) % 360;
	return sprintf( '--ss-hue:%1$d;--ss-hue2:%2$d;--ss-x1:%3$d%%;--ss-y1:%4$d%%;--ss-x2:%5$d%%;', $hue, $hue2, $x1, $y1, $x2 );
}

function ss_render_card( $spot, $heading_level = 3 ) {
	$tag     = 'h' . (int) $heading_level;
	$vibes   = wp_list_pluck( $spot['vibes'], 'slug' );
	$area    = $spot['areas'] ? $spot['areas'][0]->name : '';
	$haystack = strtolower( $spot['title'] . ' ' . $area . ' ' . implode( ' ', wp_list_pluck( $spot['vibes'], 'name' ) ) . ' ' . $spot['excerpt'] );
	ob_start();
	?>
	<li class="ss-card" data-vibes="<?php echo esc_attr( implode( ' ', $vibes ) ); ?>" data-late="<?php echo $spot['open_late'] ? '1' : '0'; ?>" data-search="<?php echo esc_attr( $haystack ); ?>" data-score="<?php echo esc_attr( $spot['score'] ); ?>" data-quiet="<?php echo esc_attr( $spot['quiet'] ); ?>" data-id="<?php echo esc_attr( $spot['id'] ); ?>">
		<div class="ss-card__cover" style="<?php echo esc_attr( ss_cover_style( $spot ) ); ?>" aria-hidden="true">
			<span class="ss-card__score"><?php echo esc_html( number_format( $spot['score'], 1 ) ); ?></span>
			<?php if ( $spot['open_late'] ) : ?>
				<span class="ss-card__late"><?php esc_html_e( 'Open late', 'study-spots' ); ?></span>
			<?php endif; ?>
		</div>
		<div class="ss-card__body">
			<?php if ( $area ) : ?>
				<p class="ss-card__area"><?php echo esc_html( $area ); ?></p>
			<?php endif; ?>
			<<?php echo $tag; ?> class="ss-card__title"><a href="<?php echo esc_url( $spot['url'] ); ?>"><?php echo esc_html( $spot['title'] ); ?></a></<?php echo $tag; ?>>
			<p class="ss-card__excerpt"><?php echo esc_html( wp_trim_words( $spot['excerpt'], 18 ) ); ?></p>
			<div class="ss-card__meters">
				<?php
				echo ss_render_meter( __( 'Wi-Fi', 'study-spots' ), $spot['wifi'], 'sm' ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo ss_render_meter( __( 'Quiet', 'study-spots' ), $spot['quiet'], 'sm' ); // phpcs:ignore WordPress.Security.EscapeOutput
				echo ss_render_meter( __( 'Outlets', 'study-spots' ), $spot['outlets'], 'sm' ); // phpcs:ignore WordPress.Security.EscapeOutput
				?>
			</div>
			<?php if ( $spot['vibes'] ) : ?>
				<ul class="ss-tags" aria-label="<?php esc_attr_e( 'Vibes', 'study-spots' ); ?>">
					<?php foreach ( $spot['vibes'] as $vibe ) : ?>
						<li class="ss-tag" style="--ss-hue:<?php echo esc_attr( ss_vibe_hue( $vibe ) ); ?>"><?php echo esc_html( $vibe->name ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</li>
	<?php
	return ob_get_clean();
}

function ss_published_spots( $limit = -1, $exclude = array() ) {
	$ids = get_posts(
		array(
			'post_type'      => 'spot',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'fields'         => 'ids',
			'post__not_in'   => $exclude,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);
	return array_map( 'ss_get_spot', $ids );
}
