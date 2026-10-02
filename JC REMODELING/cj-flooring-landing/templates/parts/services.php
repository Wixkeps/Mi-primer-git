<?php
/**
 * Flooring types (the services). Same dark rounded cards, gold icons and check bullets as the main site.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$c = CJFL_Content::all();
?>
<section class="cjfl-section cjfl-services" id="flooring" aria-labelledby="cjfl-services-title">
	<div class="cjfl-container">
		<header class="cjfl-head cjfl-head--line">
			<p class="cjfl-eyebrow"><?php echo esc_html( $c['services']['eyebrow'] ); ?></p>
			<h2 id="cjfl-services-title" class="cjfl-h2"><?php echo esc_html( $c['services']['title'] ); ?></h2>
			<p class="cjfl-lead"><?php echo esc_html( $c['services']['lead'] ); ?></p>
		</header>

		<div class="cjfl-grid cjfl-grid--3">
			<?php foreach ( $c['services']['items'] as $item ) : ?>
				<article class="cjfl-service">
					<div class="cjfl-service__top">
						<span class="cjfl-badge"><?php echo CJFL_Icons::get( $item['icon'], '', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<h3 class="cjfl-h3"><?php echo esc_html( $item['title'] ); ?></h3>
					</div>
					<p><?php echo esc_html( $item['text'] ); ?></p>
					<ul class="cjfl-list">
						<?php foreach ( $item['points'] as $point ) : ?>
							<li><span class="cjfl-list__icon"><?php echo CJFL_Icons::get( 'check', '', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php echo esc_html( $point ); ?></li>
						<?php endforeach; ?>
					</ul>
					<p class="cjfl-service__best"><strong><?php esc_html_e( 'Best for:', 'cj-flooring-landing' ); ?></strong> <?php echo esc_html( $item['best'] ); ?></p>
				</article>
			<?php endforeach; ?>

			<article class="cjfl-service cjfl-service--cta">
				<h3 class="cjfl-h3"><?php echo esc_html( $c['services']['cta']['title'] ); ?></h3>
				<p><?php echo esc_html( $c['services']['cta']['text'] ); ?></p>
				<a class="cjfl-btn cjfl-btn--gold" href="#quote" data-cjfl-scroll data-cjfl-track="cta_services"><?php echo esc_html( $c['services']['cta']['button'] ); ?></a>
			</article>
		</div>
	</div>
</section>
