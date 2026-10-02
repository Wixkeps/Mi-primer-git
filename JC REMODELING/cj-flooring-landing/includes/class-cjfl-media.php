<?php
/**
 * Images: reuses the files that already live in the site's media library.
 *
 * Each image is looked up in the library by file name so WordPress can build the
 * responsive srcset; if it is not found the plain uploads URL is used instead.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

class CJFL_Media {

	/**
	 * Image catalog. Paths are relative to wp-content/uploads.
	 *
	 * @return array
	 */
	public static function catalog() {
		$items = array(
			'hero'       => array(
				'file' => '2026/09/roma-arquitectura-remodelaciones-portada.jpg',
				'w'    => 2000,
				'h'    => 1200,
				'alt'  => 'Bright living and dining room with light wood-look plank flooring',
			),
			'install'    => array(
				'file' => '2026/09/images-5.jpg',
				'w'    => 701,
				'h'    => 438,
				'alt'  => 'Installer fitting plank flooring over a prepared subfloor',
			),
			'wood'       => array(
				'file' => '2026/09/images-6.jpg',
				'w'    => 678,
				'h'    => 452,
				'alt'  => 'Open-plan living area with dark wood plank floors',
			),
			'light'      => array(
				'file' => '2026/09/images-7.jpg',
				'w'    => 889,
				'h'    => 345,
				'alt'  => 'Open kitchen and living area with light wood-look plank flooring',
			),
			'kitchen'    => array(
				'file' => '2026/09/images-3.jpg',
				'w'    => 675,
				'h'    => 455,
				'alt'  => 'Remodeled kitchen with a quartz island and wood-look flooring',
			),
			'bath'       => array(
				'file' => '2026/09/3b9aef_63ce63c853b644c9a9774a2b8ac39e78-mv2.webp',
				'w'    => 2400,
				'h'    => 1600,
				'alt'  => 'Remodeled bathroom with a basketweave mosaic tile floor',
			),
			'logo'       => array(
				'file' => '2026/09/Logo_website-removebg-preview.png',
				'w'    => 669,
				'h'    => 373,
				'alt'  => 'CJ Remodeling Group LLC logo',
			),
			'logo_small' => array(
				'file' => '2026/09/Logo_website-removebg-previewmb.png',
				'w'    => 383,
				'h'    => 315,
				'alt'  => 'CJ Remodeling Group LLC logo',
			),
		);
		return apply_filters( 'cjfl_images', $items );
	}

	/**
	 * Base URL of the uploads folder (filterable so staging copies can point to production).
	 *
	 * @return string
	 */
	public static function uploads_base() {
		$upload = wp_get_upload_dir();
		return untrailingslashit( apply_filters( 'cjfl_uploads_base_url', $upload['baseurl'] ) );
	}

	/**
	 * @param string $key Catalog key.
	 * @return array|null
	 */
	public static function item( $key ) {
		$catalog = self::catalog();
		return isset( $catalog[ $key ] ) ? $catalog[ $key ] : null;
	}

	/**
	 * Public URL of an image.
	 *
	 * @param string $key Catalog key.
	 * @return string
	 */
	public static function url( $key ) {
		if ( 'logo' === $key || 'logo_small' === $key ) {
			$custom = trim( (string) CJFL_Config::get( 'logo_url' ) );
			if ( '' !== $custom ) {
				return esc_url_raw( $custom );
			}
		}
		$item = self::item( $key );
		return $item ? self::uploads_base() . '/' . ltrim( $item['file'], '/' ) : '';
	}

	/**
	 * Attachment ID of a catalog image (0 when it is not in the media library).
	 *
	 * @param string $key Catalog key.
	 * @return int
	 */
	public static function attachment_id( $key ) {
		$item = self::item( $key );
		if ( ! $item || ( ( 'logo' === $key || 'logo_small' === $key ) && '' !== trim( (string) CJFL_Config::get( 'logo_url' ) ) ) ) {
			return 0;
		}
		$cache_key = 'cjfl_att_' . md5( $item['file'] . self::uploads_base() );
		$id        = get_transient( $cache_key );
		if ( false === $id ) {
			$id = (int) attachment_url_to_postid( self::url( $key ) );
			set_transient( $cache_key, $id, $id ? DAY_IN_SECONDS : 10 * MINUTE_IN_SECONDS );
		}
		return (int) $id;
	}

	/**
	 * <img> markup for a catalog image.
	 *
	 * @param string $key   Catalog key.
	 * @param array  $attrs HTML attributes (class, alt, loading, sizes, fetchpriority...).
	 * @param string $size  WordPress image size used when the file is in the media library.
	 * @return string
	 */
	public static function img( $key, array $attrs = array(), $size = 'large' ) {
		$item = self::item( $key );
		if ( ! $item ) {
			return '';
		}
		if ( ! isset( $attrs['alt'] ) ) {
			$attrs['alt'] = $item['alt'];
		}

		$id = self::attachment_id( $key );
		if ( $id ) {
			$html = wp_get_attachment_image( $id, $size, false, $attrs );
			if ( $html ) {
				return $html;
			}
		}

		$attrs = array_merge(
			array(
				'src'      => self::url( $key ),
				'width'    => (int) $item['w'],
				'height'   => (int) $item['h'],
				'decoding' => 'async',
			),
			$attrs
		);
		$out   = '<img';
		foreach ( $attrs as $name => $value ) {
			if ( false === $value || null === $value ) {
				continue;
			}
			$out .= ' ' . esc_attr( $name ) . '="' . ( 'src' === $name ? esc_url( $value ) : esc_attr( $value ) ) . '"';
		}
		return $out . '>';
	}

	/**
	 * <link rel="preload"> for the hero image (LCP element).
	 *
	 * @param string $key Catalog key.
	 * @return string
	 */
	public static function preload( $key ) {
		$id = self::attachment_id( $key );
		if ( $id ) {
			$src    = wp_get_attachment_image_url( $id, 'large' );
			$srcset = wp_get_attachment_image_srcset( $id, 'large' );
			if ( $src ) {
				return sprintf(
					'<link rel="preload" as="image" href="%1$s"%2$s fetchpriority="high">' . "\n",
					esc_url( $src ),
					$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '" imagesizes="100vw"' : ''
				);
			}
		}
		$url = self::url( $key );
		return $url ? '<link rel="preload" as="image" href="' . esc_url( $url ) . '" fetchpriority="high">' . "\n" : '';
	}
}
