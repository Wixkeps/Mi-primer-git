<?php
/**
 * Always-visible contact buttons:
 *  - phones: a bottom bar with Call / WhatsApp / Free estimate
 *  - larger screens: a floating WhatsApp button
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$tel = CJFL_Config::phone_href();
$wa  = CJFL_Config::whatsapp_url();
?>
<aside class="cjfl-dock" aria-label="<?php esc_attr_e( 'Quick contact', 'cj-flooring-landing' ); ?>">
<div class="cjfl-sticky">
	<?php if ( $tel ) : ?>
		<a class="cjfl-sticky__btn" href="<?php echo esc_url( $tel ); ?>" data-cjfl-track="call_sticky">
			<?php echo CJFL_Icons::get( 'phone', '', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span><?php esc_html_e( 'Call', 'cj-flooring-landing' ); ?></span>
		</a>
	<?php endif; ?>
	<?php if ( $wa ) : ?>
		<a class="cjfl-sticky__btn" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-cjfl-track="whatsapp_sticky">
			<?php echo CJFL_Icons::get( 'whatsapp', '', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span>WhatsApp</span>
		</a>
	<?php endif; ?>
	<a class="cjfl-sticky__btn cjfl-sticky__btn--primary" href="#quote" data-cjfl-scroll data-cjfl-track="cta_sticky">
		<?php echo CJFL_Icons::get( 'clipboard', '', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span><?php esc_html_e( 'Free estimate', 'cj-flooring-landing' ); ?></span>
	</a>
</div>

<?php if ( $wa ) : ?>
	<a class="cjfl-fab" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-cjfl-track="whatsapp_fab">
		<?php echo CJFL_Icons::get( 'whatsapp', '', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span><?php esc_html_e( 'Chat on WhatsApp', 'cj-flooring-landing' ); ?></span>
	</a>
<?php endif; ?>
</aside>
