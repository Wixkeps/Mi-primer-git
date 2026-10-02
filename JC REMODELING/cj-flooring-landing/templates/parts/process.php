<?php
/**
 * Process: "Design. Renovate. Build." applied to a flooring project.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$c = CJFL_Content::all();
?>
<section class="cjfl-section cjfl-process" id="process" aria-labelledby="cjfl-process-title">
	<div class="cjfl-container">
		<header class="cjfl-head">
			<p class="cjfl-eyebrow"><?php echo esc_html( $c['process']['eyebrow'] ); ?></p>
			<h2 id="cjfl-process-title" class="cjfl-h2"><?php echo esc_html( $c['process']['title'] ); ?></h2>
			<p class="cjfl-lead"><?php echo esc_html( $c['process']['lead'] ); ?></p>
		</header>

		<ol class="cjfl-steps">
			<?php foreach ( $c['process']['steps'] as $step ) : ?>
				<li class="cjfl-step">
					<span class="cjfl-step__n" aria-hidden="true"><?php echo esc_html( $step['n'] ); ?></span>
					<h3 class="cjfl-h3"><?php echo esc_html( $step['title'] ); ?></h3>
					<p><?php echo esc_html( $step['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
