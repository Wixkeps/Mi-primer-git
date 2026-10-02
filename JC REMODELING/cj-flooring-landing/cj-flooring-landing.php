<?php
/**
 * Plugin Name:       CJ Flooring Landing
 * Description:       Flooring landing page for CJ Remodeling Group (Miami, FL). Standalone page template with the site's visual identity, SEO / AEO / GEO structured data, contact buttons and a lead form that emails the team and stores every request in the WordPress admin.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Grupo30
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cj-flooring-landing
 * Domain Path:       /languages
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

define( 'CJFL_VERSION', '1.0.0' );
define( 'CJFL_FILE', __FILE__ );
define( 'CJFL_DIR', plugin_dir_path( __FILE__ ) );
define( 'CJFL_URL', plugin_dir_url( __FILE__ ) );
/** Slug stored in the page's _wp_page_template meta. */
define( 'CJFL_TEMPLATE', 'cjfl-landing' );

foreach ( array( 'config', 'icons', 'media', 'content', 'page', 'seo', 'form', 'leads', 'admin' ) as $cjfl_part ) {
	require_once CJFL_DIR . 'includes/class-cjfl-' . $cjfl_part . '.php';
}
unset( $cjfl_part );

register_activation_hook( __FILE__, array( 'CJFL_Page', 'activate' ) );

add_action(
	'init',
	static function () {
		load_plugin_textdomain( 'cj-flooring-landing', false, dirname( plugin_basename( CJFL_FILE ) ) . '/languages' );
	},
	1
);

add_action(
	'plugins_loaded',
	static function () {
		CJFL_Page::init();
		CJFL_SEO::init();
		CJFL_Form::init();
		CJFL_Leads::init();
		if ( is_admin() ) {
			CJFL_Admin::init();
		}
	}
);
