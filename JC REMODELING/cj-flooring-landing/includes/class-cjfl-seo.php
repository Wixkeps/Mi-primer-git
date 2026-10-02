<?php
/**
 * SEO / AEO / GEO: document title, meta description, Open Graph, robots directives and
 * JSON-LD structured data (LocalBusiness + Service + FAQPage + WebPage + BreadcrumbList).
 *
 * If a dedicated SEO plugin (Yoast, Rank Math, AIOSEO, SEOPress, TSF...) is active, this class
 * only adds the entities those plugins cannot know (business, service, FAQ) and leaves titles,
 * descriptions, Open Graph and robots to them.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

class CJFL_SEO {

	public static function init() {
		add_filter( 'pre_get_document_title', array( __CLASS__, 'title' ), 99 );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ), 99 );
		add_filter( 'language_attributes', array( __CLASS__, 'language_attributes' ), 99 );
	}

	/**
	 * @return bool True when a full-featured SEO plugin is handling titles / meta.
	 */
	public static function has_seo_plugin() {
		$active = defined( 'WPSEO_VERSION' )
			|| defined( 'RANK_MATH_VERSION' )
			|| defined( 'AIOSEO_VERSION' )
			|| defined( 'SEOPRESS_VERSION' )
			|| defined( 'THE_SEO_FRAMEWORK_VERSION' )
			|| defined( 'SLIM_SEO_VER' );
		return (bool) apply_filters( 'cjfl_has_seo_plugin', $active );
	}

	/**
	 * Whether this class should print the page-level SEO tags itself.
	 *
	 * @return bool
	 */
	private static function manages_meta() {
		return CJFL_Page::is_landing() && ! self::has_seo_plugin();
	}

	/* ------------------------------------------------------------------ */
	/* Filters                                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * @param string $title Title.
	 * @return string
	 */
	public static function title( $title ) {
		if ( ! self::manages_meta() ) {
			return $title;
		}
		$seo = CJFL_Content::seo();
		return $seo['title'];
	}

	/**
	 * @param array $robots Robots directives.
	 * @return array
	 */
	public static function robots( $robots ) {
		if ( ! self::manages_meta() ) {
			return $robots;
		}
		// Let search and AI answer engines show full snippets and large previews.
		$robots['max-snippet']       = '-1';
		$robots['max-image-preview'] = 'large';
		$robots['max-video-preview'] = '-1';
		if ( empty( $robots['noindex'] ) ) {
			$robots['index']  = true;
			$robots['follow'] = true;
		}
		return $robots;
	}

	/**
	 * The page content is English: declare it, even if the WordPress site language is Spanish.
	 *
	 * @param string $output Attributes.
	 * @return string
	 */
	public static function language_attributes( $output ) {
		if ( ! CJFL_Page::is_landing() ) {
			return $output;
		}
		if ( preg_match( '/lang="[^"]*"/', $output ) ) {
			return preg_replace( '/lang="[^"]*"/', 'lang="en-US"', $output );
		}
		return trim( $output . ' lang="en-US"' );
	}

	/* ------------------------------------------------------------------ */
	/* Head output                                                         */
	/* ------------------------------------------------------------------ */

	/**
	 * Resource hints printed before wp_head(): the hero image (LCP) and the main font.
	 */
	public static function print_early() {
		echo '<meta name="theme-color" content="#0e0f11">' . "\n";
		echo '<link rel="preload" href="' . esc_url( CJFL_URL . 'assets/fonts/InstrumentSans-latin.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
		echo CJFL_Media::preload( 'hero' ); // phpcs:ignore WordPress.Security.EscapeOutput -- built with esc_url / esc_attr.
	}

	/**
	 * Cleans what other code printed inside wp_head(): drops theme / builder assets and duplicate
	 * social / description tags (we print our own), and adds a favicon when nobody did.
	 *
	 * @param string $html Captured wp_head() output.
	 * @return string
	 */
	public static function filter_head( $html ) {
		if ( ! CJFL_Page::is_landing() ) {
			return $html;
		}

		if ( CJFL_Config::get( 'isolate' ) ) {
			// Preloads / stylesheets of the theme and page builder that slipped through.
			$html = preg_replace_callback(
				'#<link\b[^>]*>\s*#i',
				static function ( $m ) {
					if ( preg_match( '#\bhref=(["\'])(.*?)\1#i', $m[0], $href ) && CJFL_Page::is_blocked_url( $href[2] ) ) {
						return '';
					}
					return $m[0];
				},
				$html
			);
		}

		if ( self::manages_meta() ) {
			// Description / keywords / Open Graph / Twitter tags printed by the theme or other plugins: we print our own.
			$html = preg_replace_callback(
				'#<meta\b[^>]*>\s*#i',
				static function ( $m ) {
					if ( preg_match( '#\b(?:name|property)=(["\'])(description|keywords|og:[^"\']+|twitter:[^"\']+)\1#i', $m[0] ) ) {
						return '';
					}
					return $m[0];
				},
				$html
			);

			// Bare "WebPage" stubs (usually keyword lists printed by page builders) only add noise next to our full graph.
			$html = preg_replace_callback(
				'#<script\b[^>]*type=(["\'])application/ld\+json\1[^>]*>(.*?)</script>\s*#is',
				static function ( $m ) {
					$data = json_decode( trim( $m[2] ), true );
					if ( is_array( $data ) && isset( $data['@type'] ) && 'WebPage' === $data['@type'] && ! isset( $data['@id'] ) && ! isset( $data['name'] ) && ! isset( $data['url'] ) ) {
						return '';
					}
					return $m[0];
				},
				$html
			);
		}

		// Themes that do not declare title-tag support (and are not block themes) would leave the page without a <title>.
		if ( ! preg_match( '#<title\b#i', $html ) ) {
			$html = '<title>' . esc_html( wp_get_document_title() ) . '</title>' . "\n" . $html;
		}

		// Core prints the viewport tag from WordPress 6.9; older versions rely on the theme's header.php.
		if ( ! preg_match( '#<meta\b[^>]*\bname=(["\'])viewport\1#i', $html ) ) {
			$html = '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n" . $html;
		}

		if ( ! preg_match( '#<link\b[^>]*\brel=(["\'])[^"\']*\bicon\b[^"\']*\1#i', $html ) ) {
			$icon = CJFL_Media::url( 'logo_small' );
			if ( $icon ) {
				$html .= '<link rel="icon" href="' . esc_url( $icon ) . '" type="image/png">' . "\n";
			}
		}

		return $html;
	}

	/**
	 * Description, Open Graph, Twitter Cards and JSON-LD.
	 */
	public static function print_meta() {
		if ( ! CJFL_Page::is_landing() ) {
			return;
		}
		$seo = CJFL_Content::seo();

		if ( self::manages_meta() ) {
			$url   = self::page_url();
			$image = self::share_image();
			$cfg   = CJFL_Config::all();

			echo '<meta name="description" content="' . esc_attr( $seo['description'] ) . '">' . "\n";
			echo '<meta property="og:locale" content="en_US">' . "\n";
			echo '<meta property="og:type" content="website">' . "\n";
			echo '<meta property="og:site_name" content="' . esc_attr( $cfg['business_name'] ) . '">' . "\n";
			echo '<meta property="og:title" content="' . esc_attr( $seo['title'] ) . '">' . "\n";
			echo '<meta property="og:description" content="' . esc_attr( $seo['description'] ) . '">' . "\n";
			echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
			if ( $image['url'] ) {
				echo '<meta property="og:image" content="' . esc_url( $image['url'] ) . '">' . "\n";
				if ( $image['w'] && $image['h'] ) {
					echo '<meta property="og:image:width" content="' . (int) $image['w'] . '">' . "\n";
					echo '<meta property="og:image:height" content="' . (int) $image['h'] . '">' . "\n";
				}
				echo '<meta property="og:image:alt" content="' . esc_attr( $image['alt'] ) . '">' . "\n";
			}
			echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
			echo '<meta name="twitter:title" content="' . esc_attr( $seo['title'] ) . '">' . "\n";
			echo '<meta name="twitter:description" content="' . esc_attr( $seo['description'] ) . '">' . "\n";
			if ( $image['url'] ) {
				echo '<meta name="twitter:image" content="' . esc_url( $image['url'] ) . '">' . "\n";
			}
		}

		$graph = self::graph( ! self::has_seo_plugin() );
		if ( $graph ) {
			$flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP;
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$flags |= JSON_PRETTY_PRINT;
			}
			echo '<script type="application/ld+json">' . wp_json_encode(
				array(
					'@context' => 'https://schema.org',
					'@graph'   => $graph,
				),
				$flags
			) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- JSON encoded with HEX_TAG / HEX_AMP.
		}
	}

	/* ------------------------------------------------------------------ */
	/* Helpers                                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * @return string Canonical URL of the landing page.
	 */
	public static function page_url() {
		$id = (int) get_queried_object_id();
		return $id ? (string) get_permalink( $id ) : home_url( '/' );
	}

	/**
	 * @return array{url:string,w:int,h:int,alt:string}
	 */
	private static function share_image() {
		$custom = trim( (string) CJFL_Config::get( 'og_image' ) );
		if ( '' !== $custom ) {
			return array(
				'url' => esc_url_raw( $custom ),
				'w'   => 0,
				'h'   => 0,
				'alt' => CJFL_Content::seo()['h1'],
			);
		}
		$item = CJFL_Media::item( 'hero' );
		return array(
			'url' => CJFL_Media::url( 'hero' ),
			'w'   => $item ? (int) $item['w'] : 0,
			'h'   => $item ? (int) $item['h'] : 0,
			'alt' => $item ? $item['alt'] : '',
		);
	}

	/**
	 * @return array[] Area-served nodes for schema.org.
	 */
	private static function area_nodes() {
		$cfg   = CJFL_Config::all();
		$nodes = array(
			array(
				'@type' => 'City',
				'name'  => $cfg['city'],
			),
		);
		$seen  = array( strtolower( $cfg['city'] ) );
		if ( 'miami' === strtolower( trim( $cfg['city'] ) ) ) {
			// Wikipedia links help search and AI engines tie the business to the right place.
			$nodes[0]['sameAs'] = 'https://en.wikipedia.org/wiki/Miami';
			$nodes[]            = array(
				'@type'  => 'AdministrativeArea',
				'name'   => 'Miami-Dade County',
				'sameAs' => 'https://en.wikipedia.org/wiki/Miami-Dade_County,_Florida',
			);
			$seen[]             = 'miami-dade county';
		}
		foreach ( CJFL_Config::areas() as $area ) {
			if ( in_array( strtolower( $area ), $seen, true ) ) {
				continue;
			}
			$seen[]  = strtolower( $area );
			$nodes[] = array(
				'@type' => 'Place',
				'name'  => $area . ', ' . $cfg['region'],
			);
		}
		return $nodes;
	}

	/**
	 * Builds the JSON-LD graph.
	 *
	 * @param bool $full Include WebPage / WebSite / BreadcrumbList (skipped when an SEO plugin prints them).
	 * @return array[]
	 */
	public static function graph( $full = true ) {
		$cfg     = CJFL_Config::all();
		$seo     = CJFL_Content::seo();
		$content = CJFL_Content::all();
		$url     = self::page_url();
		$home    = trailingslashit( home_url( '/' ) );
		$biz_id  = $home . '#business';
		$logo    = CJFL_Media::item( 'logo' );
		$hero    = CJFL_Media::item( 'hero' );
		$post_id = (int) get_queried_object_id();

		$address = array(
			'@type'           => 'PostalAddress',
			'addressLocality' => $cfg['city'],
			'addressRegion'   => $cfg['region'],
			'addressCountry'  => 'US',
		);
		if ( '' !== trim( (string) $cfg['street'] ) ) {
			$address['streetAddress'] = trim( $cfg['street'] );
		}
		if ( '' !== trim( (string) $cfg['postal_code'] ) ) {
			$address['postalCode'] = trim( $cfg['postal_code'] );
		}

		$service_names = array(
			'Porcelain and ceramic tile installation',
			'Luxury vinyl plank (LVP) installation',
			'Laminate flooring installation',
			'Wood and engineered wood flooring installation',
			'Baseboard and trim installation',
		);
		$offers = array();
		foreach ( $service_names as $name ) {
			$offers[] = array(
				'@type'       => 'Offer',
				'itemOffered' => array(
					'@type' => 'Service',
					'name'  => $name,
				),
			);
		}

		/* ----------------------- Business (GEO / local) ----------------- */
		$business = array(
			'@type'         => 'GeneralContractor',
			'@id'           => $biz_id,
			'name'          => $cfg['business_name'],
			'legalName'     => $cfg['legal_name'],
			'url'           => $home,
			'telephone'     => CJFL_Config::phone_e164(),
			'email'         => $cfg['email'],
			'slogan'        => 'Better spaces. Brighter lives.',
			'description'   => $cfg['legal_name'] . ' is a remodeling contractor in ' . $cfg['city'] . ', ' . $cfg['region'] . ' offering kitchen and bathroom remodeling, flooring, painting, roofing and full home renovations.',
			'address'       => $address,
			'areaServed'    => self::area_nodes(),
			'knowsAbout'    => array( 'Flooring installation', 'Porcelain tile installation', 'Luxury vinyl plank installation', 'Laminate flooring', 'Wood flooring', 'Home remodeling' ),
			'contactPoint'  => array(
				array(
					'@type'       => 'ContactPoint',
					'contactType' => 'customer service',
					'telephone'   => CJFL_Config::phone_e164(),
					'email'       => $cfg['email'],
					'areaServed'  => 'US-' . $cfg['region'],
				),
			),
		);
		if ( $logo ) {
			$business['logo']  = array(
				'@type'      => 'ImageObject',
				'@id'        => $home . '#logo',
				'url'        => CJFL_Media::url( 'logo' ),
				'contentUrl' => CJFL_Media::url( 'logo' ),
				'width'      => (int) $logo['w'],
				'height'     => (int) $logo['h'],
				'caption'    => $cfg['legal_name'],
			);
			$business['image'] = array_values( array_filter( array( CJFL_Media::url( 'logo' ), CJFL_Media::url( 'hero' ) ) ) );
		}
		$same_as = CJFL_Config::same_as();
		if ( $same_as ) {
			$business['sameAs'] = $same_as;
		}
		$hours = array_values( array_filter( array_map( 'trim', preg_split( '/[\r\n]+/', (string) $cfg['hours'] ) ) ) );
		if ( $hours ) {
			$business['openingHours'] = $hours;
		}
		if ( preg_match( '/^\d{4}$/', trim( (string) $cfg['founding_year'] ) ) ) {
			$business['foundingDate'] = trim( $cfg['founding_year'] );
		}
		if ( '' !== trim( (string) $cfg['license'] ) ) {
			$business['identifier'] = array(
				'@type' => 'PropertyValue',
				'name'  => 'Contractor license',
				'value' => trim( $cfg['license'] ),
			);
		}

		/* ----------------------------- Service -------------------------- */
		$service = array(
			'@type'             => 'Service',
			'@id'               => $url . '#service',
			'name'              => 'Flooring Installation in ' . $cfg['city'] . ', ' . $cfg['region'],
			'serviceType'       => 'Flooring installation',
			'description'       => $content['intro']['paragraphs'][0],
			'url'               => $url,
			'provider'          => array( '@id' => $biz_id ),
			'areaServed'        => self::area_nodes(),
			'hasOfferCatalog'   => array(
				'@type'           => 'OfferCatalog',
				'name'            => 'Flooring installation services',
				'itemListElement' => $offers,
			),
			'availableChannel'  => array(
				'@type'          => 'ServiceChannel',
				'serviceUrl'     => $url . '#quote',
				'servicePhone'   => array(
					'@type'     => 'ContactPoint',
					'telephone' => CJFL_Config::phone_e164(),
				),
			),
		);

		/* -------------------------------- FAQ --------------------------- */
		$questions = array();
		foreach ( $content['faq']['items'] as $item ) {
			$questions[] = array(
				'@type'          => 'Question',
				'name'           => $item['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['a'],
				),
			);
		}
		$faq = array(
			'@type'      => 'FAQPage',
			'@id'        => $url . '#faq',
			'url'        => $url,
			'inLanguage' => 'en-US',
			'mainEntity' => $questions,
		);
		if ( $full ) {
			$faq['mainEntityOfPage'] = array( '@id' => $url . '#webpage' );
		}

		$graph = array();

		if ( $full ) {
			$graph[] = array(
				'@type'      => 'WebSite',
				'@id'        => $home . '#website',
				'url'        => $home,
				'name'       => $cfg['business_name'],
				'inLanguage' => 'en-US',
				'publisher'  => array( '@id' => $biz_id ),
			);
		}

		$graph[] = $business;
		$graph[] = $service;
		$graph[] = $faq;

		if ( $full ) {
			$page = array(
				'@type'              => 'WebPage',
				'@id'                => $url . '#webpage',
				'url'                => $url,
				'name'               => $seo['title'],
				'description'        => $seo['description'],
				'inLanguage'         => 'en-US',
				'isPartOf'           => array( '@id' => $home . '#website' ),
				'about'              => array( '@id' => $url . '#service' ),
				'publisher'          => array( '@id' => $biz_id ),
				'breadcrumb'         => array( '@id' => $url . '#breadcrumb' ),
				'primaryImageOfPage' => array( '@id' => $url . '#primaryimage' ),
			);
			if ( $post_id ) {
				$published = get_post_time( 'c', true, $post_id );
				$modified  = get_post_modified_time( 'c', true, $post_id );
				if ( $published ) {
					$page['datePublished'] = $published;
				}
				if ( $modified ) {
					$page['dateModified'] = $modified;
				}
			}
			$graph[] = $page;
			if ( $hero ) {
				$graph[] = array(
					'@type'      => 'ImageObject',
					'@id'        => $url . '#primaryimage',
					'url'        => CJFL_Media::url( 'hero' ),
					'contentUrl' => CJFL_Media::url( 'hero' ),
					'width'      => (int) $hero['w'],
					'height'     => (int) $hero['h'],
					'caption'    => $hero['alt'],
				);
			}
			$graph[] = array(
				'@type'           => 'BreadcrumbList',
				'@id'             => $url . '#breadcrumb',
				'itemListElement' => array(
					array(
						'@type'    => 'ListItem',
						'position' => 1,
						'name'     => 'Home',
						'item'     => $home,
					),
					array(
						'@type'    => 'ListItem',
						'position' => 2,
						'name'     => $seo['h1'],
						'item'     => $url,
					),
				),
			);
		}

		return apply_filters( 'cjfl_schema_graph', $graph, $full );
	}
}
