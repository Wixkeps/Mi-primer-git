<?php
/**
 * Header: logo, in-page navigation, phone and the gold "Contact us!" button.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$cfg  = CJFL_Config::all();
$home = home_url( '/' );
$tel  = CJFL_Config::phone_href();
?>
<header class="cjfl-header" id="top">
	<div class="cjfl-container cjfl-header__inner">
		<a class="cjfl-header__brand" href="<?php echo esc_url( $home ); ?>" aria-label="<?php echo esc_attr( $cfg['business_name'] . ' - ' . __( 'Home', 'cj-flooring-landing' ) ); ?>">
			<?php
			echo CJFL_Media::img( // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
				'logo',
				array(
					'class'    => 'cjfl-logo',
					'alt'      => $cfg['legal_name'],
					'loading'  => false,
					'sizes'    => '(min-width: 961px) 200px, 140px',
					'decoding' => 'sync',
					// The hero photo is the LCP element: keep the browser's "high priority" slot for it.
					'fetchpriority' => false,
				),
				'large'
			);
			?>
		</a>

		<nav class="cjfl-nav" aria-label="<?php esc_attr_e( 'Primary', 'cj-flooring-landing' ); ?>">
			<ul>
				<li><a href="<?php echo esc_url( $home ); ?>"><?php esc_html_e( 'Home', 'cj-flooring-landing' ); ?></a></li>
				<li><a href="#flooring"><?php esc_html_e( 'Flooring', 'cj-flooring-landing' ); ?></a></li>
				<li><a href="#process"><?php esc_html_e( 'Process', 'cj-flooring-landing' ); ?></a></li>
				<li><a href="#faq"><?php esc_html_e( 'FAQ', 'cj-flooring-landing' ); ?></a></li>
			</ul>
		</nav>

		<div class="cjfl-header__actions">
			<?php if ( $tel ) : ?>
				<a class="cjfl-header__phone" href="<?php echo esc_url( $tel ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: phone number */ __( 'Call %s', 'cj-flooring-landing' ), $cfg['phone_display'] ) ); ?>" data-cjfl-track="call_header">
					<?php echo CJFL_Icons::get( 'phone', '', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="cjfl-nobr"><?php echo esc_html( $cfg['phone_display'] ); ?></span>
				</a>
			<?php endif; ?>
			<a class="cjfl-btn cjfl-btn--gold cjfl-btn--sm" href="#quote" data-cjfl-scroll data-cjfl-track="contact_header"><?php esc_html_e( 'Contact us!', 'cj-flooring-landing' ); ?></a>
		</div>
	</div>
</header>
