<?php
/**
 * Footer: logo, the three contact boxes of the main site, and the copyright line.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$cfg   = CJFL_Config::all();
$tel   = CJFL_Config::phone_href();
$email = sanitize_email( (string) $cfg['email'] );
$home  = home_url( '/' );
?>
<footer class="cjfl-footer">
	<div class="cjfl-container">
		<a class="cjfl-footer__brand" href="<?php echo esc_url( $home ); ?>" aria-label="<?php echo esc_attr( $cfg['business_name'] . ' - ' . __( 'Home', 'cj-flooring-landing' ) ); ?>">
			<?php
			echo CJFL_Media::img( // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
				'logo',
				array(
					'class'    => 'cjfl-footer__logo',
					'alt'      => $cfg['legal_name'],
					'loading'  => 'lazy',
					'decoding' => 'async',
					'sizes'    => '240px',
				),
				'medium'
			);
			?>
		</a>

		<ul class="cjfl-footer__boxes">
			<?php if ( $email ) : ?>
				<li><span><?php esc_html_e( 'Mail:', 'cj-flooring-landing' ); ?> <a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a></span></li>
			<?php endif; ?>
			<?php if ( $tel ) : ?>
				<li><span><?php esc_html_e( 'Phone:', 'cj-flooring-landing' ); ?> <a class="cjfl-nobr" href="<?php echo esc_url( $tel ); ?>"><?php echo esc_html( preg_replace( '/[-.]+/', ' ', (string) $cfg['phone_display'] ) ); ?></a></span></li>
			<?php endif; ?>
			<li><span><?php echo esc_html( $cfg['city'] . ', Florida' ); ?></span></li>
		</ul>

		<p class="cjfl-footer__copy">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> | <?php esc_html_e( 'All rights reserved', 'cj-flooring-landing' ); ?>
			<?php if ( '' !== trim( (string) $cfg['credit'] ) ) : ?>
				| <?php echo esc_html( $cfg['credit'] ); ?>
			<?php endif; ?>
		</p>
	</div>
</footer>
