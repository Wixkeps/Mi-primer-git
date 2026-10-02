<?php
/**
 * Settings: defaults + accessors.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

class CJFL_Config {

	const OPTION      = 'cjfl_settings';
	const PAGE_OPTION = 'cjfl_page_id';

	/** @var array|null */
	private static $cache = null;

	/**
	 * Default values. Everything the business owner may want to change lives here
	 * (and on Flooring Leads > Settings), so no code edits are needed.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			// Business.
			'business_name'   => 'CJ Remodeling Group',
			'legal_name'      => 'CJ Remodeling Group LLC',
			'phone_display'   => '954-671-8595',
			'whatsapp'        => '19546718595',
			'whatsapp_text'   => 'Hi CJ Remodeling Group, I would like a free estimate for flooring in Miami.',
			'email'           => 'info@cjremodeling.services',
			'city'            => 'Miami',
			'region'          => 'FL',
			'street'          => '',
			'postal_code'     => '',
			'hours'           => '',
			'license'         => '',
			'founding_year'   => '',
			'same_as'         => '',
			'areas'           => "Miami\nMiami Beach\nCoral Gables\nDoral\nHialeah\nAventura\nPinecrest\nKendall\nHomestead\nNorth Miami",
			// Leads.
			'notify_to'       => '',
			'subject_prefix'  => 'New flooring estimate request',
			'privacy_url'     => '',
			// SEO (empty = built-in default text, see CJFL_Content::seo()).
			'seo_title'       => '',
			'seo_description' => '',
			'h1'              => '',
			'og_image'        => '',
			'logo_url'        => '',
			// Footer / advanced.
			'credit'          => 'Designed by grupo30.com',
			'isolate'         => 1,
			'delete_data'     => 0,
		);
	}

	/**
	 * Keys where an empty saved value is meaningful (instead of "use the default").
	 *
	 * @return string[]
	 */
	private static function allow_empty() {
		return array( 'credit', 'areas', 'whatsapp' );
	}

	/**
	 * All settings merged with defaults.
	 *
	 * @return array
	 */
	public static function all() {
		if ( null !== self::$cache ) {
			return self::$cache;
		}
		$saved  = get_option( self::OPTION, array() );
		$saved  = is_array( $saved ) ? $saved : array();
		$merged = self::defaults();
		foreach ( $saved as $key => $value ) {
			if ( ! array_key_exists( $key, $merged ) ) {
				continue;
			}
			if ( '' !== $value || in_array( $key, self::allow_empty(), true ) ) {
				$merged[ $key ] = $value;
			}
		}
		self::$cache = apply_filters( 'cjfl_settings', $merged );
		return self::$cache;
	}

	/**
	 * Forget the per-request cache (after saving options).
	 */
	public static function flush() {
		self::$cache = null;
	}

	/**
	 * @param string $key Setting key.
	 * @return mixed
	 */
	public static function get( $key ) {
		$all = self::all();
		return isset( $all[ $key ] ) ? $all[ $key ] : null;
	}

	/* ------------------------------------------------------------------ */
	/* Derived values                                                      */
	/* ------------------------------------------------------------------ */

	/**
	 * Phone in E.164 format (+1XXXXXXXXXX) built from the display number.
	 *
	 * @return string
	 */
	public static function phone_e164() {
		$digits = preg_replace( '/\D+/', '', (string) self::get( 'phone_display' ) );
		if ( '' === $digits ) {
			return '';
		}
		if ( 10 === strlen( $digits ) ) {
			$digits = '1' . $digits;
		}
		return '+' . $digits;
	}

	/**
	 * @return string tel: URL or empty string.
	 */
	public static function phone_href() {
		$e164 = self::phone_e164();
		return $e164 ? 'tel:' . $e164 : '';
	}

	/**
	 * @param string|null $text Optional prefilled message.
	 * @return string wa.me URL or empty string when WhatsApp is disabled.
	 */
	public static function whatsapp_url( $text = null ) {
		$number = preg_replace( '/\D+/', '', (string) self::get( 'whatsapp' ) );
		if ( '' === $number ) {
			return '';
		}
		if ( 10 === strlen( $number ) ) {
			$number = '1' . $number;
		}
		$text = null === $text ? (string) self::get( 'whatsapp_text' ) : (string) $text;
		$url  = 'https://wa.me/' . $number;
		return '' !== $text ? $url . '?text=' . rawurlencode( $text ) : $url;
	}

	/**
	 * Service area names, one per line / comma.
	 *
	 * @return string[]
	 */
	public static function areas() {
		$raw   = (string) self::get( 'areas' );
		$parts = preg_split( '/[\r\n,]+/', $raw );
		$out   = array();
		foreach ( (array) $parts as $part ) {
			$part = trim( wp_strip_all_tags( $part ) );
			if ( '' !== $part && ! in_array( $part, $out, true ) ) {
				$out[] = $part;
			}
		}
		return array_slice( $out, 0, 30 );
	}

	/**
	 * Profile URLs (Google Business Profile, Facebook, Instagram...).
	 *
	 * @return string[]
	 */
	public static function same_as() {
		$urls = array();
		foreach ( preg_split( '/[\r\n]+/', (string) self::get( 'same_as' ) ) as $line ) {
			$line = trim( $line );
			if ( '' !== $line && wp_http_validate_url( $line ) ) {
				$urls[] = esc_url_raw( $line );
			}
		}
		return array_values( array_unique( $urls ) );
	}

	/**
	 * Addresses that receive lead notifications.
	 *
	 * @return string[]
	 */
	public static function notify_emails() {
		$list = array();
		foreach ( preg_split( '/[\s,;]+/', (string) self::get( 'notify_to' ) ) as $mail ) {
			$mail = sanitize_email( trim( $mail ) );
			if ( $mail && is_email( $mail ) ) {
				$list[] = $mail;
			}
		}
		if ( ! $list ) {
			$fallback = sanitize_email( (string) self::get( 'email' ) );
			$list[]   = ( $fallback && is_email( $fallback ) ) ? $fallback : get_option( 'admin_email' );
		}
		return array_values( array_unique( $list ) );
	}

	/**
	 * ID of the landing page (0 when it does not exist).
	 *
	 * @return int
	 */
	public static function landing_id() {
		$id = (int) get_option( self::PAGE_OPTION );
		if ( $id ) {
			$post = get_post( $id );
			if ( $post && 'page' === $post->post_type && 'trash' !== $post->post_status ) {
				return $id;
			}
		}
		$found = get_posts(
			array(
				'post_type'        => 'page',
				'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future' ),
				'meta_key'         => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'       => CJFL_TEMPLATE, // phpcs:ignore WordPress.DB.SlowDBQuery
				'posts_per_page'   => 1,
				'fields'           => 'ids',
				'orderby'          => 'ID',
				'order'            => 'ASC',
				'no_found_rows'    => true,
				'suppress_filters' => true,
			)
		);
		if ( $found ) {
			update_option( self::PAGE_OPTION, (int) $found[0], false );
			return (int) $found[0];
		}
		return 0;
	}
}
