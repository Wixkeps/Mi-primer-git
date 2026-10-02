<?php
/**
 * "Careful prep" feature card: text on one side, installation photo on the other
 * (same pattern as the service cards on the main site).
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$c = CJFL_Content::all();
?>
<section class="cjfl-section cjfl-prep" aria-labelledby="cjfl-prep-title">
	<div class="cjfl-container">
		<div class="cjfl-feature">
			<div class="cjfl-feature__copy">
				<p class="cjfl-eyebrow"><?php echo esc_html( $c['prep']['eyebrow'] ); ?></p>
				<h2 id="cjfl-prep-title" class="cjfl-h2 cjfl-h2--md"><?php echo esc_html( $c['prep']['title'] ); ?></h2>
				<p><?php echo esc_html( $c['prep']['text'] ); ?></p>
				<ul class="cjfl-list">
					<?php foreach ( $c['prep']['points'] as $point ) : ?>
						<li><span class="cjfl-list__icon"><?php echo CJFL_Icons::get( 'check', '', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php echo esc_html( $point ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="cjfl-feature__media">
				<?php
				echo CJFL_Media::img( // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
					'install',
					array(
						'class'    => 'cjfl-feature__img',
						'loading'  => 'lazy',
						'decoding' => 'async',
						'sizes'    => '(min-width: 900px) 560px, 100vw',
					),
					'large'
				);
				?>
			</div>
		</div>
	</div>
</section>
