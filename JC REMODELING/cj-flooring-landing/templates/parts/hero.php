<?php
/**
 * Hero: the ONE <h1> of the page, supporting copy, contact buttons and the quick estimate form.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$c   = CJFL_Content::all();
$seo = CJFL_Content::seo();
$cfg = CJFL_Config::all();
$tel = CJFL_Config::phone_href();
$wa  = CJFL_Config::whatsapp_url();
?>
<section class="cjfl-hero" aria-labelledby="cjfl-h1">
	<?php
	echo CJFL_Media::img( // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
		'hero',
		array(
			'class'         => 'cjfl-hero__bg',
			'loading'       => false,
			'fetchpriority' => 'high',
			'decoding'      => 'async',
			'sizes'         => '100vw',
		),
		'large'
	);
	?>
	<div class="cjfl-hero__overlay" aria-hidden="true"></div>

	<div class="cjfl-container cjfl-hero__inner">
		<div class="cjfl-hero__copy">
			<p class="cjfl-eyebrow"><?php echo esc_html( $c['hero']['eyebrow'] ); ?></p>
			<h1 id="cjfl-h1" class="cjfl-hero__title"><?php echo esc_html( $seo['h1'] ); ?></h1>
			<p class="cjfl-hero__lead"><?php echo esc_html( $c['hero']['lead'] ); ?></p>

			<ul class="cjfl-checks">
				<?php foreach ( $c['hero']['checks'] as $check ) : ?>
					<li><span class="cjfl-checks__icon"><?php echo CJFL_Icons::get( 'check', '', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php echo esc_html( $check ); ?></li>
				<?php endforeach; ?>
			</ul>

			<div class="cjfl-hero__ctas">
				<a class="cjfl-btn cjfl-btn--gold" href="#quote" data-cjfl-scroll data-cjfl-track="cta_hero"><?php echo esc_html( $c['hero']['cta'] ); ?></a>
				<?php if ( $tel ) : ?>
					<a class="cjfl-btn cjfl-btn--dark cjfl-nobr" href="<?php echo esc_url( $tel ); ?>" data-cjfl-track="call_hero">
						<?php echo CJFL_Icons::get( 'phone', '', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php
						/* translators: %s: phone number */
						echo esc_html( sprintf( __( 'Call %s', 'cj-flooring-landing' ), $cfg['phone_display'] ) );
						?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $wa ) : ?>
				<p class="cjfl-hero__wa">
					<a href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" data-cjfl-track="whatsapp_hero">
						<?php echo CJFL_Icons::get( 'whatsapp', '', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php esc_html_e( 'Or message us on WhatsApp', 'cj-flooring-landing' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>

		<div class="cjfl-hero__card cjfl-card" id="estimate">
			<?php CJFL_Page::part( 'form', array( 'variant' => 'hero' ) ); ?>
		</div>
	</div>
</section>
