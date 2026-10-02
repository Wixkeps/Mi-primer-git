<?php
/**
 * Miami flooring guide: comparison table + condo note. Structured, quotable content for AEO / GEO.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$c = CJFL_Content::all();
$t = $c['guide']['table'];
?>
<section class="cjfl-section cjfl-guide" id="guide" aria-labelledby="cjfl-guide-title">
	<div class="cjfl-container">
		<header class="cjfl-head">
			<p class="cjfl-eyebrow"><?php echo esc_html( $c['guide']['eyebrow'] ); ?></p>
			<h2 id="cjfl-guide-title" class="cjfl-h2"><?php echo esc_html( $c['guide']['title'] ); ?></h2>
			<p class="cjfl-lead"><?php echo esc_html( $c['guide']['lead'] ); ?></p>
		</header>

		<div class="cjfl-table-wrap" role="region" aria-label="<?php echo esc_attr( $t['caption'] ); ?>" tabindex="0">
			<table class="cjfl-table" role="table">
				<caption class="cjfl-sr"><?php echo esc_html( $t['caption'] ); ?></caption>
				<thead role="rowgroup">
					<tr role="row">
						<?php foreach ( $t['head'] as $heading ) : ?>
							<th scope="col" role="columnheader"><?php echo esc_html( $heading ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody role="rowgroup">
					<?php foreach ( $t['rows'] as $row ) : ?>
						<tr role="row">
							<th scope="row" role="rowheader"><?php echo esc_html( $row[0] ); ?></th>
							<?php for ( $i = 1, $n = count( $row ); $i < $n; $i++ ) : ?>
								<td role="cell" data-label="<?php echo esc_attr( $t['head'][ $i ] ); ?>"><?php echo esc_html( $row[ $i ] ); ?></td>
							<?php endfor; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<div class="cjfl-note" role="note">
			<span class="cjfl-note__icon"><?php echo CJFL_Icons::get( 'home', '', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<div>
				<h3 class="cjfl-h3"><?php echo esc_html( $c['guide']['note']['title'] ); ?></h3>
				<p><?php echo esc_html( $c['guide']['note']['text'] ); ?></p>
			</div>
		</div>
	</div>
</section>
