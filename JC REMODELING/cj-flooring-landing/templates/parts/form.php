<?php
/**
 * Lead form. Two variants share this markup: "hero" (short, above the fold) and "main" (full).
 * Submitted with JavaScript (assets/js/landing.js) to admin-ajax.php; see includes/class-cjfl-form.php.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$variant = isset( $cjfl['variant'] ) && 'hero' === $cjfl['variant'] ? 'hero' : 'main';
$is_hero = 'hero' === $variant;
$c       = CJFL_Content::all();
$cfg     = CJFL_Config::all();
$p       = 'cjfl-' . $variant;
$tel     = CJFL_Config::phone_href();
$wa      = CJFL_Config::whatsapp_url();
?>
<form id="<?php echo esc_attr( $p ); ?>-form" class="cjfl-form cjfl-form--<?php echo esc_attr( $variant ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-cjfl-form="<?php echo esc_attr( $variant ); ?>" novalidate>
	<div class="cjfl-form__body">
		<?php if ( $is_hero ) : ?>
			<p class="cjfl-form__title"><?php echo esc_html( $c['hero']['card_title'] ); ?></p>
			<p class="cjfl-form__text"><?php echo esc_html( $c['hero']['card_text'] ); ?></p>
		<?php endif; ?>

		<div class="cjfl-field">
			<label for="<?php echo esc_attr( $p ); ?>-name"><?php esc_html_e( 'Your name', 'cj-flooring-landing' ); ?> <span class="cjfl-req" aria-hidden="true">*</span></label>
			<input type="text" id="<?php echo esc_attr( $p ); ?>-name" name="name" autocomplete="name" maxlength="80" placeholder="<?php esc_attr_e( 'John Doe', 'cj-flooring-landing' ); ?>" required aria-required="true">
		</div>

		<div class="cjfl-field">
			<label for="<?php echo esc_attr( $p ); ?>-phone"><?php esc_html_e( 'Phone number', 'cj-flooring-landing' ); ?> <span class="cjfl-req" aria-hidden="true">*</span></label>
			<input type="tel" id="<?php echo esc_attr( $p ); ?>-phone" name="phone" autocomplete="tel" inputmode="tel" maxlength="40" placeholder="(305) 555-0123" required aria-required="true">
		</div>

		<?php if ( ! $is_hero ) : ?>
			<div class="cjfl-field">
				<label for="<?php echo esc_attr( $p ); ?>-email"><?php esc_html_e( 'Email (optional)', 'cj-flooring-landing' ); ?></label>
				<input type="email" id="<?php echo esc_attr( $p ); ?>-email" name="email" autocomplete="email" maxlength="120" placeholder="<?php esc_attr_e( 'you@example.com', 'cj-flooring-landing' ); ?>">
			</div>
		<?php endif; ?>

		<div class="cjfl-field">
			<label for="<?php echo esc_attr( $p ); ?>-flooring"><?php esc_html_e( 'Type of flooring', 'cj-flooring-landing' ); ?></label>
			<select id="<?php echo esc_attr( $p ); ?>-flooring" name="flooring">
				<option value=""><?php esc_html_e( 'Select an option', 'cj-flooring-landing' ); ?></option>
				<?php foreach ( CJFL_Content::flooring_options() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<?php if ( ! $is_hero ) : ?>
			<div class="cjfl-field">
				<label for="<?php echo esc_attr( $p ); ?>-property"><?php esc_html_e( 'Property type', 'cj-flooring-landing' ); ?></label>
				<select id="<?php echo esc_attr( $p ); ?>-property" name="property">
					<option value=""><?php esc_html_e( 'Select an option', 'cj-flooring-landing' ); ?></option>
					<?php foreach ( CJFL_Content::property_options() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="cjfl-field">
				<label for="<?php echo esc_attr( $p ); ?>-message"><?php esc_html_e( 'About your project (optional)', 'cj-flooring-landing' ); ?></label>
				<textarea id="<?php echo esc_attr( $p ); ?>-message" name="message" rows="4" maxlength="2000" placeholder="<?php esc_attr_e( 'Rooms, approximate size, timeline, building rules...', 'cj-flooring-landing' ); ?>"></textarea>
			</div>
		<?php endif; ?>

		<div class="cjfl-hp" aria-hidden="true">
			<label for="<?php echo esc_attr( $p ); ?>-website"><?php esc_html_e( 'Leave this field empty', 'cj-flooring-landing' ); ?></label>
			<input type="text" id="<?php echo esc_attr( $p ); ?>-website" name="cjfl_website" value="" tabindex="-1" autocomplete="off">
		</div>

		<input type="hidden" name="action" value="cjfl_submit">
		<input type="hidden" name="form" value="<?php echo esc_attr( $variant ); ?>">

		<button type="submit" class="cjfl-btn cjfl-btn--gold cjfl-btn--block" data-cjfl-track="submit_<?php echo esc_attr( $variant ); ?>">
			<span class="cjfl-btn__label"><?php esc_html_e( 'Get My Free Estimate', 'cj-flooring-landing' ); ?></span>
		</button>

		<p class="cjfl-form__note">
			<?php esc_html_e( 'By submitting this form you agree to be contacted about your project by phone, text or email.', 'cj-flooring-landing' ); ?>
			<?php if ( ! empty( $cfg['privacy_url'] ) ) : ?>
				<a href="<?php echo esc_url( $cfg['privacy_url'] ); ?>"><?php esc_html_e( 'Privacy policy', 'cj-flooring-landing' ); ?></a>
			<?php endif; ?>
		</p>

		<noscript>
			<p class="cjfl-form__note cjfl-form__note--warn">
				<?php
				printf(
					/* translators: %s: phone number */
					esc_html__( 'Please enable JavaScript to send this form, or call us at %s.', 'cj-flooring-landing' ),
					esc_html( $cfg['phone_display'] )
				);
				?>
			</p>
		</noscript>
	</div>

	<div class="cjfl-form__status" role="status" aria-live="polite" tabindex="-1"></div>

	<div class="cjfl-form__done" hidden>
		<div class="cjfl-form__done-icon"><?php echo CJFL_Icons::get( 'check', '', 30 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<p class="cjfl-form__done-title"><?php esc_html_e( 'Request received', 'cj-flooring-landing' ); ?></p>
		<p class="cjfl-form__done-text" data-cjfl-message></p>
		<?php if ( $tel || $wa ) : ?>
			<p class="cjfl-form__done-or"><?php esc_html_e( 'Need an answer sooner?', 'cj-flooring-landing' ); ?></p>
			<div class="cjfl-form__done-actions">
				<?php if ( $tel ) : ?>
					<a class="cjfl-btn cjfl-btn--dark cjfl-btn--sm cjfl-nobr" href="<?php echo esc_url( $tel ); ?>" data-cjfl-track="call_after_submit"><?php echo CJFL_Icons::get( 'phone', '', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $cfg['phone_display'] ); ?></a>
				<?php endif; ?>
				<?php if ( $wa ) : ?>
					<a class="cjfl-btn cjfl-btn--dark cjfl-btn--sm" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-cjfl-track="whatsapp_after_submit"><?php echo CJFL_Icons::get( 'whatsapp', '', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?> WhatsApp</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</form>
