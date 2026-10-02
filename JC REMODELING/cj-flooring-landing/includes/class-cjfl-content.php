<?php
/**
 * Landing page copy. Single source of truth for the template AND the structured data
 * (the FAQ schema is generated from the same array that renders the visible FAQ).
 *
 * Only claims that already appear on cjremodeling.services (or are general flooring
 * knowledge) are used. Do not add licenses, years in business, prices or reviews here
 * unless they are real - use Flooring Leads > Settings for those.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

class CJFL_Content {

	/** @var array|null */
	private static $cache = null;

	/**
	 * Options of the "type of flooring" field.
	 *
	 * @return array<string,string>
	 */
	public static function flooring_options() {
		return apply_filters(
			'cjfl_flooring_options',
			array(
				'tile'       => __( 'Porcelain / ceramic tile', 'cj-flooring-landing' ),
				'lvp'        => __( 'Luxury vinyl plank (LVP)', 'cj-flooring-landing' ),
				'laminate'   => __( 'Laminate', 'cj-flooring-landing' ),
				'wood'       => __( 'Wood / engineered wood', 'cj-flooring-landing' ),
				'baseboards' => __( 'Baseboards & trim', 'cj-flooring-landing' ),
				'unsure'     => __( 'Not sure yet — I need advice', 'cj-flooring-landing' ),
			)
		);
	}

	/**
	 * Options of the "property type" field.
	 *
	 * @return array<string,string>
	 */
	public static function property_options() {
		return apply_filters(
			'cjfl_property_options',
			array(
				'house'     => __( 'Single-family home', 'cj-flooring-landing' ),
				'condo'     => __( 'Condo / apartment', 'cj-flooring-landing' ),
				'townhome'  => __( 'Townhome', 'cj-flooring-landing' ),
				'other'     => __( 'Other', 'cj-flooring-landing' ),
			)
		);
	}

	/**
	 * Escapes text for display and keeps the phone number on a single line.
	 *
	 * @param string $text Plain text.
	 * @return string Safe HTML.
	 */
	public static function html( $text ) {
		$out   = esc_html( (string) $text );
		$phone = esc_html( (string) CJFL_Config::get( 'phone_display' ) );
		if ( '' !== $phone ) {
			$out = str_replace( $phone, '<span class="cjfl-nobr">' . $phone . '</span>', $out );
		}
		return $out;
	}

	/**
	 * SEO title / description / H1 (settings override the built-in defaults).
	 *
	 * @return array{title:string,description:string,h1:string}
	 */
	public static function seo() {
		$cfg   = CJFL_Config::all();
		$phone = $cfg['phone_display'];
		$biz   = $cfg['business_name'];

		$defaults = array(
			'title'       => 'Flooring Installation in Miami, FL | ' . $biz,
			'description' => 'Porcelain tile, luxury vinyl plank, laminate and wood flooring installation in Miami, FL. Careful prep, clean job sites. Call ' . $phone . ' for a free estimate.',
			'h1'          => 'Flooring Installation in Miami, FL',
		);

		return array(
			'title'       => '' !== trim( (string) $cfg['seo_title'] ) ? trim( $cfg['seo_title'] ) : $defaults['title'],
			'description' => '' !== trim( (string) $cfg['seo_description'] ) ? trim( $cfg['seo_description'] ) : $defaults['description'],
			'h1'          => '' !== trim( (string) $cfg['h1'] ) ? trim( $cfg['h1'] ) : $defaults['h1'],
		);
	}

	/**
	 * Everything the template renders.
	 *
	 * @return array
	 */
	public static function all() {
		if ( null !== self::$cache ) {
			return self::$cache;
		}

		$cfg   = CJFL_Config::all();
		$phone = $cfg['phone_display'];
		$areas = CJFL_Config::areas();

		$content = array();

		/* ---------------------------- Hero ------------------------------ */
		$content['hero'] = array(
			'eyebrow'    => 'Design | Renovate | Build',
			'lead'       => 'Porcelain and ceramic tile, luxury vinyl plank, laminate and wood floors, installed with careful prep for a level, long-lasting finish.',
			'checks'     => array(
				'Free estimate for your project',
				'Clean, organized job site',
				'One team from first design to final walkthrough',
			),
			'cta'        => 'Get a Free Estimate',
			'card_title' => 'Request a Free Estimate',
			'card_text'  => 'Tell us about your floors and our team will contact you to plan your project.',
		);

		/* --------------------------- Trust bar -------------------------- */
		$content['trust'] = array(
			array(
				'icon'  => 'clipboard',
				'title' => 'Free estimates',
				'text'  => 'Know your options and budget before you commit.',
			),
			array(
				'icon'  => 'layers',
				'title' => 'Careful prep',
				'text'  => 'Level, clean subfloors for a long-lasting finish.',
			),
			array(
				'icon'  => 'sparkle',
				'title' => 'Clean job sites',
				'text'  => 'Organized work areas from demolition to cleanup.',
			),
			array(
				'icon'  => 'shield',
				'title' => 'Final walkthrough',
				'text'  => 'We review every detail with you before handover.',
			),
		);

		/* ----------------------- Intro / quick facts -------------------- */
		$facts = array(
			array( 'Company', $cfg['legal_name'] ),
			array( 'Services', 'Tile, luxury vinyl plank, laminate and wood flooring, plus baseboards and trim' ),
			array( 'Service area', $cfg['city'] . ', ' . $cfg['region'] . ' and nearby communities' ),
			array( 'Estimates', 'Free, with a visit to your space' ),
			array( 'Contact', $phone . ' / WhatsApp / online form' ),
		);
		if ( '' !== trim( (string) $cfg['license'] ) ) {
			$facts[] = array( 'License', trim( $cfg['license'] ) );
		}
		$content['intro'] = array(
			'eyebrow'    => 'Miami flooring company',
			'title'      => 'Flooring Contractor in Miami, Florida',
			'paragraphs' => array(
				$cfg['legal_name'] . ' installs porcelain and ceramic tile, luxury vinyl plank (LVP), laminate and wood flooring in Miami, FL, along with baseboards and trim. Whether you are replacing worn floors in one room or updating your whole home, we handle the planning, prep, installation and final walkthrough with one team.',
				'Every project starts with a free estimate. We visit your space, listen to your goals and help you choose the right material, layout and budget for the way you live, and for South Florida\'s hot, humid climate.',
			),
			'facts'      => $facts,
		);

		/* ---------------------- Flooring services ----------------------- */
		$content['services'] = array(
			'eyebrow' => 'What we install',
			'title'   => 'Flooring Installation Services in Miami',
			'lead'    => 'These are the flooring types we install, with notes on how each one performs in Miami\'s humid climate.',
			'items'   => array(
				array(
					'icon'   => 'tile',
					'title'  => 'Porcelain & Ceramic Tile',
					'text'   => 'Hard-wearing, easy to clean and cool underfoot. Porcelain absorbs very little water, so it holds up well to Miami humidity, spills and heavy foot traffic. Wood-look planks, large-format tiles and mosaics are all popular choices.',
					'best'   => 'Kitchens, bathrooms, living areas and entryways',
					'points' => array( 'Large-format and wood-look tile', 'Water-resistant, durable surface', 'Stays cool in hot weather' ),
				),
				array(
					'icon'   => 'plank',
					'title'  => 'Luxury Vinyl Plank (LVP)',
					'text'   => 'Realistic wood-look planks that are water-resistant or waterproof, depending on the product, comfortable underfoot and quick to install. A smart option for busy households and moisture-prone rooms.',
					'best'   => 'Kitchens, bedrooms, living areas and whole-home updates',
					'points' => array( 'Water-resistant to waterproof options', 'Wood and stone looks', 'Comfortable, quieter underfoot' ),
				),
				array(
					'icon'   => 'layers',
					'title'  => 'Laminate Flooring',
					'text'   => 'An affordable way to get the look of hardwood, with a scratch-resistant surface and click-lock installation. Best for dry interior rooms; in Miami, choose a water-resistant laminate.',
					'best'   => 'Bedrooms, offices and dry living areas',
					'points' => array( 'Budget-friendly wood look', 'Scratch-resistant surface', 'Fast click-lock installation' ),
				),
				array(
					'icon'   => 'wood',
					'title'  => 'Wood & Engineered Wood',
					'text'   => 'Warm, timeless and long-lasting. Because Miami\'s humidity can make solid wood move, engineered wood is often the better fit, and careful subfloor prep and moisture testing matter most.',
					'best'   => 'Living rooms, dining rooms and bedrooms',
					'points' => array( 'Classic, natural look', 'Engineered options for humidity', 'Adds lasting value to your home' ),
				),
				array(
					'icon'   => 'trim',
					'title'  => 'Baseboards & Trim',
					'text'   => 'The finishing detail that completes the room. New baseboards, shoe molding and transitions give your floors a clean, polished edge.',
					'best'   => 'Any room getting new floors',
					'points' => array( 'Baseboard replacement', 'Shoe molding and quarter round', 'Clean transitions between rooms' ),
				),
			),
			'cta'     => array(
				'title'  => 'Not sure which floor fits your home?',
				'text'   => 'Tell us about your space and we will help you choose the right material, layout and budget.',
				'button' => 'Talk to Our Team',
			),
		);

		/* ---------------------------- Gallery --------------------------- */
		$content['gallery'] = array(
			'eyebrow' => 'Flooring styles',
			'title'   => 'Styles and Finishes for Every Room',
			'items'   => array(
				array(
					'image'   => 'light',
					'caption' => 'Light wood-look plank flooring',
					'alt'     => 'Open kitchen and living area with light wood-look plank flooring',
					'pos'     => '0% 100%',
					'zoom'    => '1.2',
				),
				array(
					'image'   => 'wood',
					'caption' => 'Dark wood plank flooring',
					'alt'     => 'Dark wood plank floors in an open-plan living area',
					'pos'     => '50% 100%',
					'zoom'    => '1.12',
				),
				array(
					'image'   => 'bath',
					'caption' => 'Basketweave mosaic tile',
					'alt'     => 'Basketweave mosaic tile floor in a remodeled bathroom',
					'pos'     => '92% 100%',
					'zoom'    => '2.3',
				),
			),
		);

		/* ----------------------------- Prep ----------------------------- */
		$content['prep'] = array(
			'eyebrow' => 'Why prep matters',
			'title'   => 'Careful Prep for a Level, Long-Lasting Finish',
			'text'    => 'A beautiful floor starts below the surface. The prep work is what decides how your new floor looks and lasts, so it is where we are most careful.',
			'points'  => array(
				'Removal of old flooring, baseboards and debris when needed',
				'Subfloor inspection, cleaning and leveling',
				'Moisture checks and the right underlayment for your floor type',
				'Precise layout and cuts for a clean, even finish',
				'Final cleanup and a walkthrough with you before handover',
			),
		);

		/* ---------------------------- Process --------------------------- */
		$content['process'] = array(
			'eyebrow' => 'How we work',
			'title'   => 'Design. Renovate. Build.',
			'lead'    => 'The same three steps we use on every project, applied to your floors.',
			'steps'   => array(
				array(
					'n'     => '1.',
					'title' => 'Design',
					'text'  => 'We visit your space, measure the area, listen to your goals and plan the flooring, layout and budget.',
				),
				array(
					'n'     => '2.',
					'title' => 'Renovate',
					'text'  => 'Our team handles demolition, subfloor prep and updates with a clean, organized job site.',
				),
				array(
					'n'     => '3.',
					'title' => 'Build',
					'text'  => 'We install, finish and walk through every detail with you before handover.',
				),
			),
		);

		/* ------------------------ Miami flooring guide ------------------ */
		$content['guide'] = array(
			'eyebrow' => 'Miami flooring guide',
			'title'   => 'Best Flooring for Miami Homes and Condos',
			'lead'    => 'South Florida\'s heat, humidity and concrete-slab construction affect which floors last. Use this quick comparison to narrow down your options.',
			'table'   => array(
				'caption' => 'Flooring types compared for Miami homes',
				'head'    => array( 'Flooring type', 'Humidity and moisture', 'Best for', 'Keep in mind' ),
				'rows'    => array(
					array( 'Porcelain & ceramic tile', 'Excellent. Very low water absorption.', 'Kitchens, bathrooms, living areas', 'Grout needs periodic cleaning; harder and cooler underfoot.' ),
					array( 'Luxury vinyl plank (LVP)', 'Very good. Many products are waterproof.', 'Whole-home use, busy households', 'Quality varies by product; needs a flat subfloor.' ),
					array( 'Laminate', 'Fair. Choose a water-resistant line.', 'Bedrooms and dry living areas', 'Avoid wet areas; edges can swell if flooded.' ),
					array( 'Wood & engineered wood', 'Moderate. Engineered is more stable than solid.', 'Living rooms, dining rooms, bedrooms', 'Subfloor moisture testing is essential.' ),
				),
			),
			'note'    => array(
				'title' => 'Living in a Miami condo?',
				'text'  => 'Many condo associations require approval and a sound-rated underlayment before flooring work begins. Check your building\'s rules first, then mention your building and floor level when you request your estimate.',
			),
		);

		/* ------------------------------ Areas --------------------------- */
		$content['areas'] = array(
			'eyebrow' => 'Where we work',
			'title'   => 'Flooring Installation Across Miami',
			'text'    => 'Based in ' . $cfg['city'] . ', ' . $cfg['region'] . ', we install floors in homes and condos in Miami and nearby communities, including:',
			'list'    => $areas,
			'after'   => 'Not sure we cover your address? Call ' . $phone . ' and we will let you know.',
		);

		/* ------------------------------- FAQ ---------------------------- */
		$area_sentence = $areas ? implode( ', ', array_slice( $areas, 0, 6 ) ) : 'Miami';
		$content['faq'] = array(
			'eyebrow' => 'Good to know',
			'title'   => 'Flooring Installation FAQs',
			'items'   => array(
				array(
					'q' => 'What is the best flooring for Miami\'s humid climate?',
					'a' => 'Porcelain tile and luxury vinyl plank are the most dependable choices for Miami\'s heat and humidity, because both resist moisture far better than solid wood or standard laminate. Engineered wood can also work when the subfloor is tested and prepared correctly. During your free estimate we recommend the best material for each room.',
				),
				array(
					'q' => 'How much does flooring installation cost in Miami?',
					'a' => 'The cost depends on the material you choose, the size of the area, the condition of the subfloor and whether old flooring or baseboards need to be removed. The material is usually the biggest factor. The quickest way to get an accurate number is a free estimate, where we visit your space and quote your specific project.',
				),
				array(
					'q' => 'How long does a flooring installation take?',
					'a' => 'Small rooms with click-lock vinyl plank or laminate can often be finished in about a day, while tile and wood usually take longer because of prep, setting and curing time. Whole-home projects take longer still. Ask us for a realistic schedule when you request your estimate.',
				),
				array(
					'q' => 'Can new flooring be installed over my existing floor?',
					'a' => 'Sometimes. Luxury vinyl plank and laminate can often go over a flat, sound existing floor, but tile, wood and any uneven or damaged surface usually need to be removed or leveled first. We check the existing surface during your estimate and explain what your project needs.',
				),
				array(
					'q' => 'What flooring works best in a Miami condo?',
					'a' => 'Many Miami condo associations require approval and a sound-rated underlayment before flooring work begins, so check your building\'s rules first. Porcelain tile and luxury vinyl plank are both popular condo choices. Tell us your building and floor level when you request your estimate.',
				),
				array(
					'q' => 'Is luxury vinyl plank better than laminate?',
					'a' => 'For most Miami homes, yes. LVP handles moisture better than standard laminate, which can swell when it gets wet. Laminate is still a budget-friendly option for dry rooms, and water-resistant laminate lines are available.',
				),
				array(
					'q' => 'Do you install baseboards and trim?',
					'a' => 'Yes. Baseboards and trim are part of our flooring service, so your new floors get a clean, finished edge. Mention it in your estimate request and we will include it in the plan.',
				),
				array(
					'q' => 'How do I get a free flooring estimate?',
					'a' => 'Call ' . $phone . ', message us on WhatsApp or fill out the form on this page. Tell us about the rooms, the type of flooring you are considering and whether you live in a house or a condo, and our team will follow up to plan your estimate.',
				),
				array(
					'q' => 'Which areas do you serve?',
					'a' => 'We are based in ' . $cfg['city'] . ', ' . $cfg['region'] . ' and install floors in Miami and nearby communities, including ' . $area_sentence . '. If you are not sure we cover your address, call ' . $phone . ' and we will let you know.',
				),
			),
		);

		/* ----------------------------- Contact -------------------------- */
		$content['contact'] = array(
			'eyebrow' => 'Free estimate',
			'title'   => 'Request Your Free Flooring Estimate',
			'text'    => 'Tell us about your flooring project and our team will get in touch to learn more about your ideas, your needs and the space you want to transform.',
		);

		self::$cache = apply_filters( 'cjfl_content', $content );
		return self::$cache;
	}
}
