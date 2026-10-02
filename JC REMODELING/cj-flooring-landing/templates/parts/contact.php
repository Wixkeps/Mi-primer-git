<?php
/**
 * Contact section: same two-column layout as the main site (copy left, white form card right).
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$c     = CJFL_Content::all();
$cfg   = CJFL_Config::all();
$tel   = CJFL_Config::phone_href();
$wa    = CJFL_Config::whatsapp_url();
$email = sanitize_email( (string) $cfg['email'] );
?>
<section class="cjfl-section cjfl-contact" id="quote" aria-labelledby="cjfl-contact-title">
	<div class="cjfl-container cjfl-contact__grid">
		<div class="cjfl-contact__copy">
			<p class="cjfl-eyebrow"><?php echo esc_html( $c['contact']['eyebrow'] ); ?></p>
			<h2 id="cjfl-contact-title" class="cjfl-h2 cjfl-h2--md"><?php echo esc_html( $c['contact']['title'] ); ?></h2>
			<p><?php echo esc_html( $c['contact']['text'] ); ?></p>

			<ul class="cjfl-contact__list">
				<?php if ( $tel ) : ?>
					<li>
						<a href="<?php echo esc_url( $tel ); ?>" data-cjfl-track="call_contact">
							<span class="cjfl-badge cjfl-badge--sm"><?php echo CJFL_Icons::get( 'phone', '', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<span><small><?php esc_html_e( 'Call us', 'cj-flooring-landing' ); ?></small><strong class="cjfl-nobr"><?php echo esc_html( $cfg['phone_display'] ); ?></strong></span>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( $wa ) : ?>
					<li>
						<a href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-cjfl-track="whatsapp_contact">
							<span class="cjfl-badge cjfl-badge--sm"><?php echo CJFL_Icons::get( 'whatsapp', '', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<span><small><?php esc_html_e( 'Message us', 'cj-flooring-landing' ); ?></small><strong>WhatsApp</strong></span>
						</a>
					</li>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<li>
						<a href="<?php echo esc_url( 'mailto:' . $email ); ?>" data-cjfl-track="email_contact">
							<span class="cjfl-badge cjfl-badge--sm"><?php echo CJFL_Icons::get( 'mail', '', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<span><small><?php esc_html_e( 'Email us', 'cj-flooring-landing' ); ?></small><strong><?php echo esc_html( $email ); ?></strong></span>
						</a>
					</li>
				<?php endif; ?>
				<li>
					<span class="cjfl-contact__static">
						<span class="cjfl-badge cjfl-badge--sm"><?php echo CJFL_Icons::get( 'pin', '', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<span><small><?php esc_html_e( 'Based in', 'cj-flooring-landing' ); ?></small><strong><?php echo esc_html( $cfg['city'] . ', Florida' ); ?></strong></span>
					</span>
				</li>
			</ul>
		</div>

		<div class="cjfl-contact__card cjfl-card">
			<?php CJFL_Page::part( 'form', array( 'variant' => 'main' ) ); ?>
		</div>
	</div>
</section>
