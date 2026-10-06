<?php
/**
 * Suggest-a-spot form. Accessible by design: real labels, grouped radios
 * with legends, inline errors tied to fields, and a focusable status message.
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only status flags.
$status = isset( $_GET['ss_status'] ) ? sanitize_key( $_GET['ss_status'] ) : '';
$errors = isset( $_GET['ss_errors'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_GET['ss_errors'] ) ) ) : array();
// phpcs:enable

$areas = get_terms( array( 'taxonomy' => 'spot_area', 'hide_empty' => false ) );
$vibes = get_terms( array( 'taxonomy' => 'spot_vibe', 'hide_empty' => false ) );

$messages = array(
	'thanks'   => array( 'success', __( 'Thanks! Your spot is in the review queue. An editor will check it out before it goes live.', 'study-spots' ) ),
	'error'    => array( 'error', __( 'A few fields need attention. They are marked below.', 'study-spots' ) ),
	'expired'  => array( 'error', __( 'That form expired. Please try again.', 'study-spots' ) ),
	'slowdown' => array( 'error', __( 'You have sent a few spots already. Please try again in an hour.', 'study-spots' ) ),
);

$field_errors = array(
	'name'    => __( 'Enter the name of the place.', 'study-spots' ),
	'address' => __( 'Enter an address or cross streets.', 'study-spots' ),
	'why'     => __( 'Tell us a bit more (at least 20 characters).', 'study-spots' ),
);

$err = function ( $key ) use ( $errors, $field_errors ) {
	if ( in_array( $key, $errors, true ) ) {
		printf( '<p class="ss-field__error" id="err-spot_%1$s">%2$s</p>', esc_attr( $key ), esc_html( $field_errors[ $key ] ) );
	}
};
$aria = function ( $key ) use ( $errors ) {
	if ( in_array( $key, $errors, true ) ) {
		printf( ' aria-invalid="true" aria-describedby="err-spot_%s"', esc_attr( $key ) );
	}
};

$ratings = array(
	'wifi'    => array( __( 'Wi-Fi', 'study-spots' ), __( 'Unusable', 'study-spots' ), __( 'Blazing', 'study-spots' ) ),
	'quiet'   => array( __( 'Quiet', 'study-spots' ), __( 'Loud', 'study-spots' ), __( 'Library-silent', 'study-spots' ) ),
	'outlets' => array( __( 'Outlets', 'study-spots' ), __( 'None', 'study-spots' ), __( 'Every seat', 'study-spots' ) ),
);
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'ss-form-wrap' ) ); ?> id="ss-form" data-ss-form>
	<?php if ( isset( $messages[ $status ] ) ) : ?>
		<div class="ss-notice ss-notice--<?php echo esc_attr( $messages[ $status ][0] ); ?>" role="<?php echo 'success' === $messages[ $status ][0] ? 'status' : 'alert'; ?>" tabindex="-1" data-ss-focus>
			<?php echo esc_html( $messages[ $status ][1] ); ?>
		</div>
	<?php endif; ?>

	<form class="ss-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
		<input type="hidden" name="action" value="ss_submit_spot">
		<?php wp_nonce_field( 'ss_submit_spot', 'ss_nonce' ); ?>
		<p class="ss-form__required-note"><?php esc_html_e( 'Fields marked * are required.', 'study-spots' ); ?></p>

		<div class="ss-field">
			<label for="spot_name"><?php esc_html_e( 'Name of the place', 'study-spots' ); ?> <span aria-hidden="true">*</span></label>
			<input type="text" id="spot_name" name="spot_name" required autocomplete="off"<?php $aria( 'name' ); ?>>
			<?php $err( 'name' ); ?>
		</div>

		<div class="ss-field">
			<label for="spot_address"><?php esc_html_e( 'Address or cross streets', 'study-spots' ); ?> <span aria-hidden="true">*</span></label>
			<input type="text" id="spot_address" name="spot_address" required autocomplete="street-address"<?php $aria( 'address' ); ?>>
			<?php $err( 'address' ); ?>
		</div>

		<div class="ss-field">
			<label for="spot_area"><?php esc_html_e( 'Neighborhood', 'study-spots' ); ?></label>
			<select id="spot_area" name="spot_area">
				<option value=""><?php esc_html_e( 'Not sure', 'study-spots' ); ?></option>
				<?php foreach ( $areas as $area ) : ?>
					<option value="<?php echo esc_attr( $area->term_id ); ?>"><?php echo esc_html( $area->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<?php foreach ( $ratings as $key => $r ) : ?>
			<fieldset class="ss-field ss-rating">
				<legend>
					<?php
					/* translators: %s: rating name */
					printf( esc_html__( 'Rate the %s', 'study-spots' ), esc_html( $r[0] ) );
					?>
					<span class="ss-rating__hint"><?php echo esc_html( sprintf( '1 = %s, 5 = %s', $r[1], $r[2] ) ); ?></span>
				</legend>
				<div class="ss-rating__options">
					<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
						<label class="ss-rating__opt">
							<input type="radio" name="spot_<?php echo esc_attr( $key ); ?>" value="<?php echo (int) $i; ?>" <?php checked( 3, $i ); ?>>
							<span><?php echo (int) $i; ?></span>
						</label>
					<?php endfor; ?>
				</div>
			</fieldset>
		<?php endforeach; ?>

		<fieldset class="ss-field">
			<legend><?php esc_html_e( 'Vibes (pick any)', 'study-spots' ); ?></legend>
			<div class="ss-checks">
				<?php foreach ( $vibes as $vibe ) : ?>
					<label class="ss-check" style="--ss-hue:<?php echo esc_attr( ss_vibe_hue( $vibe ) ); ?>">
						<input type="checkbox" name="spot_vibes[]" value="<?php echo esc_attr( $vibe->term_id ); ?>">
						<span><?php echo esc_html( $vibe->name ); ?></span>
					</label>
				<?php endforeach; ?>
				<label class="ss-check">
					<input type="checkbox" name="spot_late" value="1">
					<span><?php esc_html_e( 'Open after 9pm', 'study-spots' ); ?></span>
				</label>
			</div>
		</fieldset>

		<div class="ss-field">
			<label for="spot_why"><?php esc_html_e( 'Why is it good for studying?', 'study-spots' ); ?> <span aria-hidden="true">*</span></label>
			<p class="ss-field__hint" id="spot_why_hint"><?php esc_html_e( 'Best seats, busy hours, food, anything a fellow student should know.', 'study-spots' ); ?></p>
			<textarea id="spot_why" name="spot_why" rows="5" required aria-describedby="spot_why_hint<?php echo in_array( 'why', $errors, true ) ? ' err-spot_why' : ''; ?>"<?php echo in_array( 'why', $errors, true ) ? ' aria-invalid="true"' : ''; ?>></textarea>
			<?php $err( 'why' ); ?>
		</div>

		<div class="ss-field">
			<label for="spot_by"><?php esc_html_e( 'Your first name (optional, for credit)', 'study-spots' ); ?></label>
			<input type="text" id="spot_by" name="spot_by" autocomplete="given-name">
		</div>

		<div class="ss-hp" aria-hidden="true">
			<label for="company">Company</label>
			<input type="text" id="company" name="company" tabindex="-1" autocomplete="off">
		</div>

		<button type="submit" class="ss-button ss-button--primary">
			<?php esc_html_e( 'Send for review', 'study-spots' ); ?>
			<svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</button>
	</form>
</div>
