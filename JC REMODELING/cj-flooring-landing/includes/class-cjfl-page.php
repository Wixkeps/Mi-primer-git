<?php
/**
 * Landing page: creation on activation, standalone template routing and theme isolation.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

class CJFL_Page {

	/** @var bool|null Cached result of is_landing() once the main query is ready. */
	private static $is_landing = null;

	public static function init() {
		add_filter( 'theme_page_templates', array( __CLASS__, 'register_template' ), 10, 4 );
		add_filter( 'template_include', array( __CLASS__, 'template_include' ), 99 );
		add_action( 'wp', array( __CLASS__, 'setup' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
		add_filter( 'style_loader_tag', array( __CLASS__, 'filter_asset_tag' ), 9999, 3 );
		add_filter( 'script_loader_tag', array( __CLASS__, 'filter_asset_tag' ), 9999, 3 );
	}

	/* ------------------------------------------------------------------ */
	/* Activation                                                          */
	/* ------------------------------------------------------------------ */

	/**
	 * Creates the landing page as a DRAFT (nothing goes public until the owner reviews and publishes it).
	 */
	public static function activate() {
		self::ensure_page();
		set_transient( 'cjfl_activated', 1, 10 * MINUTE_IN_SECONDS );
	}

	/**
	 * @return int Page ID (0 on failure).
	 */
	public static function ensure_page() {
		$existing = CJFL_Config::landing_id();
		if ( $existing ) {
			return $existing;
		}
		$id = wp_insert_post(
			array(
				'post_type'      => 'page',
				'post_status'    => 'draft',
				'post_title'     => 'Flooring Installation in Miami, FL',
				'post_name'      => 'flooring-installation-miami',
				'post_content'   => '',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			true
		);
		if ( is_wp_error( $id ) || ! $id ) {
			return 0;
		}
		update_post_meta( $id, '_wp_page_template', CJFL_TEMPLATE );
		update_option( CJFL_Config::PAGE_OPTION, (int) $id, false );
		return (int) $id;
	}

	/* ------------------------------------------------------------------ */
	/* Template routing                                                    */
	/* ------------------------------------------------------------------ */

	/**
	 * Adds the template to the "Template" dropdown of pages.
	 *
	 * @param array       $templates Templates.
	 * @param WP_Theme    $theme     Theme.
	 * @param WP_Post|null $post     Post.
	 * @param string      $post_type Post type.
	 * @return array
	 */
	public static function register_template( $templates, $theme = null, $post = null, $post_type = 'page' ) {
		if ( ! $post_type || 'page' === $post_type ) {
			$templates[ CJFL_TEMPLATE ] = 'CJ Flooring Landing (standalone)';
		}
		return $templates;
	}

	/**
	 * True when the current request is the landing page.
	 *
	 * @return bool
	 */
	public static function is_landing() {
		if ( is_admin() ) {
			return false;
		}
		if ( null !== self::$is_landing ) {
			return self::$is_landing;
		}
		if ( ! did_action( 'wp' ) ) {
			return false; // Main query not ready yet; do not cache.
		}
		$id               = is_singular( 'page' ) ? (int) get_queried_object_id() : 0;
		self::$is_landing = $id > 0 && CJFL_TEMPLATE === get_page_template_slug( $id );
		return self::$is_landing;
	}

	/**
	 * @param string $template Path of the template WordPress picked.
	 * @return string
	 */
	public static function template_include( $template ) {
		// A password-protected landing page falls back to the theme so WordPress can ask for the password.
		if ( self::is_landing() && ! post_password_required() ) {
			$file = CJFL_DIR . 'templates/landing.php';
			if ( is_readable( $file ) ) {
				return $file;
			}
		}
		return $template;
	}

	/**
	 * Per-request setup for the landing page.
	 */
	public static function setup() {
		if ( ! self::is_landing() ) {
			return;
		}
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );

		if ( CJFL_Config::get( 'isolate' ) ) {
			self::drop_core_extras();
		}
	}

	/**
	 * The landing page does not use WordPress' block styles, global styles or emoji script
	 * (it ships its own CSS), so they are not loaded here.
	 */
	private static function drop_core_extras() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
		remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_block_template_skip_link' );
		remove_action( 'wp_footer', 'the_block_template_skip_link' );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_core_styles' ), 100 );
	}

	public static function dequeue_core_styles() {
		foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'wc-blocks-style' ) as $handle ) {
			wp_dequeue_style( $handle );
		}
	}

	/**
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public static function body_class( $classes ) {
		$classes[] = 'cjfl-landing';
		return $classes;
	}

	/**
	 * Front-end assets (only on the landing page).
	 */
	public static function enqueue() {
		if ( ! self::is_landing() ) {
			return;
		}
		// Plain (readable) files on purpose: easy to edit; a caching / optimization plugin can minify them.
		$css = 'assets/css/landing.css';
		$js  = 'assets/js/landing.js';
		// The file's modification time is part of the version, so edited CSS / JS is never served from an old cache.
		wp_enqueue_style( 'cjfl-landing', CJFL_URL . $css, array(), CJFL_VERSION . '.' . (int) filemtime( CJFL_DIR . $css ) );
		wp_enqueue_script(
			'cjfl-landing',
			CJFL_URL . $js,
			array(),
			CJFL_VERSION . '.' . (int) filemtime( CJFL_DIR . $js ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	/* ------------------------------------------------------------------ */
	/* Theme isolation                                                     */
	/* ------------------------------------------------------------------ */

	/**
	 * Fragments of asset URLs that belong to the theme / page builder and are not needed
	 * (and only add weight and CSS conflicts) on the standalone landing page.
	 *
	 * @return string[]
	 */
	private static function blocked_fragments() {
		// URL *paths* of the active theme (and parent theme), so CDN hosts or http/https differences never matter.
		$fragments = array();
		foreach ( array( get_template_directory_uri(), get_stylesheet_directory_uri() ) as $uri ) {
			$path = wp_parse_url( $uri, PHP_URL_PATH );
			if ( $path ) {
				$fragments[] = trailingslashit( $path );
			}
		}
		$fragments = array_merge(
			$fragments,
			array(
				'/uploads/fusion-styles/',
				'/uploads/fusion-scripts/',
				'/uploads/fusion-icons/',
				'/uploads/fusion-gfonts/',
				'/plugins/fusion-builder/',
				'/plugins/fusion-core/',
			)
		);
		return array_values( array_unique( array_filter( (array) apply_filters( 'cjfl_isolate_fragments', $fragments ) ) ) );
	}

	/**
	 * @param string $url Asset URL.
	 * @return bool
	 */
	public static function is_blocked_url( $url ) {
		$url = (string) $url;
		if ( '' === $url ) {
			return false;
		}
		foreach ( self::blocked_fragments() as $fragment ) {
			if ( false !== strpos( $url, $fragment ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Drops <link>/<script> tags of the theme and page builder on the landing page.
	 *
	 * @param string $tag    Full tag.
	 * @param string $handle Handle.
	 * @param string $src    URL.
	 * @return string
	 */
	public static function filter_asset_tag( $tag, $handle = '', $src = '' ) {
		if ( ! self::is_landing() || ! CJFL_Config::get( 'isolate' ) ) {
			return $tag;
		}
		return self::is_blocked_url( $src ) ? '' : $tag;
	}

	/* ------------------------------------------------------------------ */
	/* Template helpers                                                    */
	/* ------------------------------------------------------------------ */

	/**
	 * Runs wp_head() / wp_footer() and returns what they printed.
	 *
	 * @param callable $callback Function that prints output.
	 * @return string
	 */
	public static function capture( $callback ) {
		ob_start();
		call_user_func( $callback );
		return (string) ob_get_clean();
	}

	/**
	 * Renders a template part. A theme can override any part by copying it to
	 * {theme}/cj-flooring-landing/parts/{name}.php
	 *
	 * @param string $name Part name.
	 * @param array  $vars Variables passed to the part.
	 */
	public static function part( $name, array $vars = array() ) {
		$name = preg_replace( '/[^a-z0-9_-]/i', '', $name );
		$file = locate_template( 'cj-flooring-landing/parts/' . $name . '.php' );
		if ( ! $file ) {
			$file = CJFL_DIR . 'templates/parts/' . $name . '.php';
		}
		if ( ! is_readable( $file ) ) {
			return;
		}
		$cjfl = $vars; // Parts read their data from $cjfl.
		include $file;
	}
}
