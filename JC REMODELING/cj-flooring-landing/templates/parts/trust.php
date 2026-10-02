<?php
/**
 * Trust bar under the hero (only claims already made on the main site).
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$c = CJFL_Content::all();
?>
<section class="cjfl-trust" aria-label="<?php esc_attr_e( 'Why homeowners choose us', 'cj-flooring-landing' ); ?>">
	<div class="cjfl-container">
		<ul class="cjfl-trust__list">
			<?php foreach ( $c['trust'] as $item ) : ?>
				<li class="cjfl-trust__item">
					<span class="cjfl-trust__icon"><?php echo CJFL_Icons::get( $item['icon'], '', 30 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="cjfl-trust__text">
						<strong><?php echo esc_html( $item['title'] ); ?></strong>
						<span><?php echo esc_html( $item['text'] ); ?></span>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
