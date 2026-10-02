<?php
/**
 * Answer-first introduction + quick facts (entity summary for search and AI answer engines).
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$c = CJFL_Content::all();
?>
<section class="cjfl-section cjfl-intro" id="about" aria-labelledby="cjfl-intro-title">
	<div class="cjfl-container cjfl-intro__grid">
		<div class="cjfl-intro__copy">
			<p class="cjfl-eyebrow"><?php echo esc_html( $c['intro']['eyebrow'] ); ?></p>
			<h2 id="cjfl-intro-title" class="cjfl-h2"><?php echo esc_html( $c['intro']['title'] ); ?></h2>
			<?php foreach ( $c['intro']['paragraphs'] as $paragraph ) : ?>
				<p><?php echo esc_html( $paragraph ); ?></p>
			<?php endforeach; ?>
			<p class="cjfl-intro__actions">
				<a class="cjfl-btn cjfl-btn--gold" href="#quote" data-cjfl-scroll data-cjfl-track="cta_intro"><?php esc_html_e( 'Get a Free Estimate', 'cj-flooring-landing' ); ?></a>
			</p>
		</div>

		<aside class="cjfl-facts" aria-label="<?php esc_attr_e( 'Quick facts', 'cj-flooring-landing' ); ?>">
			<h3 class="cjfl-facts__title"><?php esc_html_e( 'At a glance', 'cj-flooring-landing' ); ?></h3>
			<dl>
				<?php foreach ( $c['intro']['facts'] as $fact ) : ?>
					<div class="cjfl-facts__row">
						<dt><?php echo esc_html( $fact[0] ); ?></dt>
						<dd><?php echo CJFL_Content::html( $fact[1] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</aside>
	</div>
</section>
