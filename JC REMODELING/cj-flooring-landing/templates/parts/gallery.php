<?php
/**
 * Photo strip: flooring styles. Images come from the existing media library.
 * Captions describe the style shown (they do not claim the photos are past projects).
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$c = CJFL_Content::all();
?>
<section class="cjfl-section cjfl-gallery" aria-labelledby="cjfl-gallery-title">
	<div class="cjfl-container">
		<header class="cjfl-head">
			<p class="cjfl-eyebrow"><?php echo esc_html( $c['gallery']['eyebrow'] ); ?></p>
			<h2 id="cjfl-gallery-title" class="cjfl-h2"><?php echo esc_html( $c['gallery']['title'] ); ?></h2>
		</header>

		<ul class="cjfl-gallery__grid">
			<?php foreach ( $c['gallery']['items'] as $item ) : ?>
				<?php
				// Focal point + zoom (numbers / percentages only) keep the FLOOR in frame.
				$pos  = ( ! empty( $item['pos'] ) && preg_match( '/^\d{1,3}% \d{1,3}%$/', $item['pos'] ) ) ? $item['pos'] : '50% 50%';
				$zoom = ( ! empty( $item['zoom'] ) && is_numeric( $item['zoom'] ) ) ? max( 1, min( 4, (float) $item['zoom'] ) ) : 1;
				$zoom_css = number_format( $zoom, 2, '.', '' ); // Locale-proof decimal point.
				?>
				<li class="cjfl-gallery__item">
					<figure style="--cjfl-pos:<?php echo esc_attr( $pos ); ?>;--cjfl-zoom:<?php echo esc_attr( $zoom_css ); ?>">
						<?php
						echo CJFL_Media::img( // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
							$item['image'],
							array(
								'alt'      => isset( $item['alt'] ) ? $item['alt'] : null,
								'class'    => 'cjfl-gallery__img',
								'loading'  => 'lazy',
								'decoding' => 'async',
								// Zoomed crops need a larger source image to stay sharp.
								'sizes'    => sprintf( '(min-width: 900px) %1$dpx, (min-width: 600px) calc(50vw * %2$s), calc(100vw * %2$s)', (int) round( 380 * $zoom ), $zoom_css ),
							),
							'large'
						);
						?>
						<figcaption><?php echo esc_html( $item['caption'] ); ?></figcaption>
					</figure>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
