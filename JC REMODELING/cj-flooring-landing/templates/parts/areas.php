<?php
/**
 * Service areas (local SEO / GEO). Edit the list in Flooring Leads > Settings.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$c = CJFL_Content::all();
if ( empty( $c['areas']['list'] ) ) {
	return;
}
?>
<section class="cjfl-section cjfl-areas" id="areas" aria-labelledby="cjfl-areas-title">
	<div class="cjfl-container cjfl-areas__box">
		<header class="cjfl-head">
			<p class="cjfl-eyebrow"><?php echo esc_html( $c['areas']['eyebrow'] ); ?></p>
			<h2 id="cjfl-areas-title" class="cjfl-h2 cjfl-h2--md"><?php echo esc_html( $c['areas']['title'] ); ?></h2>
			<p class="cjfl-lead"><?php echo esc_html( $c['areas']['text'] ); ?></p>
		</header>

		<ul class="cjfl-chips">
			<?php foreach ( $c['areas']['list'] as $area ) : ?>
				<li><?php echo CJFL_Icons::get( 'pin', '', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $area ); ?></li>
			<?php endforeach; ?>
		</ul>

		<p class="cjfl-areas__after"><?php echo CJFL_Content::html( $c['areas']['after'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?></p>
	</div>
</section>
