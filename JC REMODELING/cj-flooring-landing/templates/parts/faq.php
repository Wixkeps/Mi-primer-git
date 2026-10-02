<?php
/**
 * FAQ: native <details> accordion (works without JavaScript and stays readable by crawlers).
 * The same array feeds the FAQPage JSON-LD, so visible text and structured data always match.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$c       = CJFL_Content::all();
$post_id = (int) get_queried_object_id();
$updated = $post_id ? get_post_modified_time( 'U', true, $post_id ) : 0;
?>
<section class="cjfl-section cjfl-faq" id="faq" aria-labelledby="cjfl-faq-title">
	<div class="cjfl-container cjfl-faq__wrap">
		<header class="cjfl-head">
			<p class="cjfl-eyebrow"><?php echo esc_html( $c['faq']['eyebrow'] ); ?></p>
			<h2 id="cjfl-faq-title" class="cjfl-h2"><?php echo esc_html( $c['faq']['title'] ); ?></h2>
			<?php if ( $updated ) : ?>
				<p class="cjfl-updated">
					<?php esc_html_e( 'Last updated:', 'cj-flooring-landing' ); ?>
					<time datetime="<?php echo esc_attr( gmdate( 'Y-m-d', $updated ) ); ?>"><?php echo esc_html( wp_date( 'F Y', $updated ) ); ?></time>
				</p>
			<?php endif; ?>
		</header>

		<div class="cjfl-faq__list">
			<?php foreach ( $c['faq']['items'] as $index => $item ) : ?>
				<details class="cjfl-faq__item"<?php echo 0 === $index ? ' open' : ''; ?>>
					<summary><span><?php echo esc_html( $item['q'] ); ?></span><?php echo CJFL_Icons::get( 'plus', 'cjfl-faq__plus', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></summary>
					<div class="cjfl-faq__answer"><p><?php echo CJFL_Content::html( $item['a'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?></p></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
