<?php
/**
 * Standalone landing page template.
 *
 * Prints a complete HTML document (own header / footer) so it looks the same on any theme,
 * while still calling wp_head() / wp_footer() so analytics, pixels and cache plugins keep working.
 *
 * A theme can override individual parts by copying them to {theme}/cj-flooring-landing/parts/{name}.php
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

$cjfl_sections = apply_filters(
	'cjfl_sections',
	array( 'hero', 'trust', 'intro', 'services', 'gallery', 'prep', 'process', 'guide', 'areas', 'faq', 'contact' )
);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<?php
CJFL_SEO::print_early();
echo CJFL_SEO::filter_head( CJFL_Page::capture( 'wp_head' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- markup printed by core / plugins.
CJFL_SEO::print_meta();
?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="cjfl-skip" href="#cjfl-main"><?php esc_html_e( 'Skip to content', 'cj-flooring-landing' ); ?></a>

<?php CJFL_Page::part( 'header' ); ?>

<main id="cjfl-main" class="cjfl-main">
<?php
foreach ( (array) $cjfl_sections as $cjfl_section ) {
	CJFL_Page::part( $cjfl_section );
}
?>
</main>

<?php
CJFL_Page::part( 'footer' );
CJFL_Page::part( 'sticky' );
wp_footer();
?>
</body>
</html>
