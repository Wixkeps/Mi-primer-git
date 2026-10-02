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
$blog  = (int) get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : '';
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
					'sizes'    => '200px',
				),
				'medium'
			);
			?>
		</a>

		<ul class="cjfl-footer__boxes">
			<?php if ( $email ) : ?>
				<li><?php esc_html_e( 'Mail:', 'cj-flooring-landing' ); ?> <a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
			<?php endif; ?>
			<?php if ( $tel ) : ?>
				<li><?php esc_html_e( 'Phone:', 'cj-flooring-landing' ); ?> <a class="cjfl-nobr" href="<?php echo esc_url( $tel ); ?>"><?php echo esc_html( $cfg['phone_display'] ); ?></a></li>
			<?php endif; ?>
			<li><?php echo esc_html( $cfg['city'] . ', Florida' ); ?></li>
		</ul>

		<nav class="cjfl-footer__nav" aria-label="<?php esc_attr_e( 'Footer', 'cj-flooring-landing' ); ?>">
			<a href="<?php echo esc_url( $home ); ?>"><?php esc_html_e( 'Home', 'cj-flooring-landing' ); ?></a>
			<a href="<?php echo esc_url( $home . '#Services' ); ?>"><?php esc_html_e( 'All services', 'cj-flooring-landing' ); ?></a>
			<?php if ( $blog ) : ?>
				<a href="<?php echo esc_url( $blog ); ?>"><?php esc_html_e( 'Blog', 'cj-flooring-landing' ); ?></a>
			<?php endif; ?>
			<a href="#top"><?php esc_html_e( 'Back to top', 'cj-flooring-landing' ); ?></a>
		</nav>

		<p class="cjfl-footer__copy">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $cfg['legal_name'] ); ?>. <?php esc_html_e( 'All rights reserved.', 'cj-flooring-landing' ); ?>
			<?php if ( '' !== trim( (string) $cfg['credit'] ) ) : ?>
				<span aria-hidden="true">|</span> <?php echo esc_html( $cfg['credit'] ); ?>
			<?php endif; ?>
		</p>
	</div>
</footer>
