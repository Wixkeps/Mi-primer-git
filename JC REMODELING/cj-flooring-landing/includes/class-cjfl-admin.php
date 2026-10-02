<?php
/**
 * Admin: settings screen (under "Flooring Leads"), activation notice, test e-mail.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

class CJFL_Admin {

	const SLUG  = 'cjfl-settings';
	const GROUP = 'cjfl_settings_group';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_notices', array( __CLASS__, 'activation_notice' ) );
		add_action( 'admin_post_cjfl_create_page', array( __CLASS__, 'handle_create_page' ) );
		add_action( 'admin_post_cjfl_test_email', array( __CLASS__, 'handle_test_email' ) );
		add_action( 'update_option_' . CJFL_Config::OPTION, array( 'CJFL_Config', 'flush' ) );
		add_action( 'add_option_' . CJFL_Config::OPTION, array( 'CJFL_Config', 'flush' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( CJFL_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/* ------------------------------------------------------------------ */
	/* Menu / links                                                        */
	/* ------------------------------------------------------------------ */

	public static function menu() {
		add_submenu_page(
			'edit.php?post_type=' . CJFL_Leads::POST_TYPE,
			__( 'Flooring Landing settings', 'cj-flooring-landing' ),
			__( 'Settings', 'cj-flooring-landing' ),
			'manage_options',
			self::SLUG,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * @param string[] $links Plugin row links.
	 * @return string[]
	 */
	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( self::settings_url() ) . '">' . esc_html__( 'Settings', 'cj-flooring-landing' ) . '</a>' );
		return $links;
	}

	/**
	 * @param array $args Extra query args.
	 * @return string
	 */
	public static function settings_url( array $args = array() ) {
		return add_query_arg( $args, admin_url( 'edit.php?post_type=' . CJFL_Leads::POST_TYPE . '&page=' . self::SLUG ) );
	}

	/* ------------------------------------------------------------------ */
	/* Notices                                                             */
	/* ------------------------------------------------------------------ */

	public static function activation_notice() {
		if ( ! current_user_can( 'manage_options' ) || ! get_transient( 'cjfl_activated' ) ) {
			return;
		}
		delete_transient( 'cjfl_activated' );

		$id    = CJFL_Config::landing_id();
		$links = array();
		if ( $id ) {
			$status  = get_post_status( $id );
			$links[] = '<a href="' . esc_url( 'publish' === $status ? get_permalink( $id ) : get_preview_post_link( $id ) ) . '" target="_blank" rel="noopener"><strong>' . esc_html( 'publish' === $status ? __( 'View the landing page', 'cj-flooring-landing' ) : __( 'Preview the landing page', 'cj-flooring-landing' ) ) . '</strong></a>';
			$links[] = '<a href="' . esc_url( get_edit_post_link( $id, 'raw' ) ) . '">' . esc_html__( 'Edit page / publish', 'cj-flooring-landing' ) . '</a>';
		}
		$links[] = '<a href="' . esc_url( self::settings_url() ) . '">' . esc_html__( 'Settings', 'cj-flooring-landing' ) . '</a>';

		echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'CJ Flooring Landing is active.', 'cj-flooring-landing' ) . '</strong> ';
		echo esc_html__( 'The landing page was created as a draft so you can review it before it goes public.', 'cj-flooring-landing' ) . '</p><p>' . implode( ' &nbsp;|&nbsp; ', $links ) . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- links escaped above.
	}

	/* ------------------------------------------------------------------ */
	/* Settings                                                            */
	/* ------------------------------------------------------------------ */

	public static function register_settings() {
		register_setting(
			self::GROUP,
			CJFL_Config::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Field definitions grouped by section.
	 *
	 * @return array
	 */
	private static function sections() {
		return array(
			'contact'  => array(
				'title'  => __( 'Contact details', 'cj-flooring-landing' ),
				'desc'   => __( 'Shown on the page and used for the call / WhatsApp buttons and the structured data.', 'cj-flooring-landing' ),
				'fields' => array(
					'phone_display' => array( 'type' => 'text', 'label' => __( 'Phone number', 'cj-flooring-landing' ), 'help' => __( 'US number, e.g. 954-671-8595. Used for click-to-call.', 'cj-flooring-landing' ) ),
					'whatsapp'      => array( 'type' => 'text', 'label' => __( 'WhatsApp number', 'cj-flooring-landing' ), 'help' => __( 'Digits only, with country code (19546718595). Leave empty to hide every WhatsApp button.', 'cj-flooring-landing' ) ),
					'whatsapp_text' => array( 'type' => 'text', 'label' => __( 'WhatsApp pre-filled message', 'cj-flooring-landing' ) ),
					'email'         => array( 'type' => 'email', 'label' => __( 'Business email', 'cj-flooring-landing' ), 'help' => __( 'Shown in the footer and in the structured data.', 'cj-flooring-landing' ) ),
					'city'          => array( 'type' => 'text', 'label' => __( 'City', 'cj-flooring-landing' ) ),
					'region'        => array( 'type' => 'text', 'label' => __( 'State', 'cj-flooring-landing' ), 'help' => __( 'Two letters, e.g. FL.', 'cj-flooring-landing' ) ),
					'street'        => array( 'type' => 'text', 'label' => __( 'Street address (optional)', 'cj-flooring-landing' ), 'help' => __( 'Leave empty if you work at customers\' homes and do not want to publish an address.', 'cj-flooring-landing' ) ),
					'postal_code'   => array( 'type' => 'text', 'label' => __( 'ZIP code (optional)', 'cj-flooring-landing' ) ),
				),
			),
			'leads'    => array(
				'title'  => __( 'Form & leads', 'cj-flooring-landing' ),
				'desc'   => __( 'Every request is saved under Flooring Leads and also emailed to the addresses below.', 'cj-flooring-landing' ),
				'fields' => array(
					'notify_to'      => array( 'type' => 'text', 'label' => __( 'Send lead notifications to', 'cj-flooring-landing' ), 'help' => __( 'One or more emails separated by commas. Empty = the business email above.', 'cj-flooring-landing' ) ),
					'subject_prefix' => array( 'type' => 'text', 'label' => __( 'Email subject', 'cj-flooring-landing' ), 'help' => __( 'The customer name is added at the end.', 'cj-flooring-landing' ) ),
					'privacy_url'    => array( 'type' => 'url', 'label' => __( 'Privacy policy URL (optional)', 'cj-flooring-landing' ), 'help' => __( 'If set, a link is shown under the form.', 'cj-flooring-landing' ) ),
				),
			),
			'seo'      => array(
				'title'  => __( 'SEO', 'cj-flooring-landing' ),
				'desc'   => __( 'Leave a field empty to use the optimized default (shown in gray). If an SEO plugin (Yoast, Rank Math...) is active, it controls the title, description and social tags instead.', 'cj-flooring-landing' ),
				'fields' => array(
					'h1'              => array( 'type' => 'text', 'label' => __( 'H1 (main heading)', 'cj-flooring-landing' ), 'help' => __( 'Only one H1 per page. Keep the service + city.', 'cj-flooring-landing' ), 'placeholder' => 'seo:h1' ),
					'seo_title'       => array( 'type' => 'text', 'label' => __( 'SEO title', 'cj-flooring-landing' ), 'help' => __( 'Ideal: up to 60 characters.', 'cj-flooring-landing' ), 'placeholder' => 'seo:title' ),
					'seo_description' => array( 'type' => 'textarea', 'label' => __( 'Meta description', 'cj-flooring-landing' ), 'help' => __( 'Ideal: 120 to 160 characters.', 'cj-flooring-landing' ), 'placeholder' => 'seo:description' ),
					'og_image'        => array( 'type' => 'url', 'label' => __( 'Social share image URL', 'cj-flooring-landing' ), 'help' => __( 'Optional. Default: the hero photo. Best size 1200x630.', 'cj-flooring-landing' ) ),
					'logo_url'        => array( 'type' => 'url', 'label' => __( 'Logo URL (optional)', 'cj-flooring-landing' ), 'help' => __( 'Default: the logo already in your media library.', 'cj-flooring-landing' ) ),
				),
			),
			'business' => array(
				'title'  => __( 'Business information (local SEO / GEO)', 'cj-flooring-landing' ),
				'desc'   => __( 'Search and AI engines read these facts. Only fill in what is true and keep it identical to your Google Business Profile.', 'cj-flooring-landing' ),
				'fields' => array(
					'legal_name'    => array( 'type' => 'text', 'label' => __( 'Legal name', 'cj-flooring-landing' ) ),
					'business_name' => array( 'type' => 'text', 'label' => __( 'Brand name', 'cj-flooring-landing' ) ),
					'areas'         => array( 'type' => 'textarea', 'label' => __( 'Service areas', 'cj-flooring-landing' ), 'help' => __( 'One city or neighborhood per line. Edit this list so it matches where you really work.', 'cj-flooring-landing' ), 'rows' => 8 ),
					'same_as'       => array( 'type' => 'textarea', 'label' => __( 'Profile links', 'cj-flooring-landing' ), 'help' => __( 'Google Business Profile, Facebook, Instagram, Yelp... one URL per line.', 'cj-flooring-landing' ), 'rows' => 4 ),
					'hours'         => array( 'type' => 'textarea', 'label' => __( 'Business hours (optional)', 'cj-flooring-landing' ), 'help' => __( 'One line per range, schema.org format: Mo-Fr 08:00-18:00', 'cj-flooring-landing' ), 'rows' => 3 ),
					'license'       => array( 'type' => 'text', 'label' => __( 'Contractor license (optional)', 'cj-flooring-landing' ), 'help' => __( 'Only if it is real and current. It is shown on the page and in the structured data.', 'cj-flooring-landing' ) ),
					'founding_year' => array( 'type' => 'text', 'label' => __( 'Year founded (optional)', 'cj-flooring-landing' ), 'help' => __( 'Four digits, e.g. 2015.', 'cj-flooring-landing' ) ),
				),
			),
			'advanced' => array(
				'title'  => __( 'Advanced', 'cj-flooring-landing' ),
				'desc'   => '',
				'fields' => array(
					'credit'      => array( 'type' => 'text', 'label' => __( 'Footer credit', 'cj-flooring-landing' ), 'help' => __( 'Empty = no credit line.', 'cj-flooring-landing' ) ),
					'isolate'     => array( 'type' => 'checkbox', 'label' => __( 'Fast, clean mode', 'cj-flooring-landing' ), 'help' => __( 'Do not load the theme\'s CSS and JavaScript on this page (faster, no style conflicts). Turn it off only if something from your theme or page builder must appear on the landing page.', 'cj-flooring-landing' ) ),
					'delete_data' => array( 'type' => 'checkbox', 'label' => __( 'Delete all data on uninstall', 'cj-flooring-landing' ), 'help' => __( 'Removes settings, leads and the landing page when the plugin is deleted.', 'cj-flooring-landing' ) ),
				),
			),
		);
	}

	/**
	 * @param mixed $input Posted values.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = CJFL_Config::defaults();
		$clean    = array();

		foreach ( self::sections() as $section ) {
			foreach ( $section['fields'] as $key => $field ) {
				if ( ! array_key_exists( $key, $defaults ) ) {
					continue;
				}
				$raw = isset( $input[ $key ] ) ? wp_unslash( $input[ $key ] ) : '';
				switch ( $field['type'] ) {
					case 'checkbox':
						$clean[ $key ] = empty( $raw ) ? 0 : 1;
						break;
					case 'email':
						$clean[ $key ] = sanitize_email( (string) $raw );
						break;
					case 'url':
						$clean[ $key ] = '' === trim( (string) $raw ) ? '' : esc_url_raw( trim( (string) $raw ) );
						break;
					case 'textarea':
						$clean[ $key ] = sanitize_textarea_field( (string) $raw );
						break;
					default:
						$clean[ $key ] = sanitize_text_field( (string) $raw );
				}
			}
		}

		// Notification list: keep only valid addresses.
		if ( isset( $clean['notify_to'] ) ) {
			$valid = array();
			foreach ( preg_split( '/[\s,;]+/', $clean['notify_to'] ) as $mail ) {
				$mail = sanitize_email( $mail );
				if ( $mail && is_email( $mail ) ) {
					$valid[] = $mail;
				}
			}
			$clean['notify_to'] = implode( ', ', array_unique( $valid ) );
		}
		if ( isset( $clean['whatsapp'] ) ) {
			$clean['whatsapp'] = preg_replace( '/\D+/', '', $clean['whatsapp'] );
		}
		if ( isset( $clean['region'] ) ) {
			$clean['region'] = strtoupper( substr( $clean['region'], 0, 2 ) );
		}
		if ( isset( $clean['founding_year'] ) && ! preg_match( '/^\d{4}$/', $clean['founding_year'] ) ) {
			$clean['founding_year'] = '';
		}

		return $clean;
	}

	/* ------------------------------------------------------------------ */
	/* Actions                                                             */
	/* ------------------------------------------------------------------ */

	public static function handle_create_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'cj-flooring-landing' ) );
		}
		check_admin_referer( 'cjfl_create_page' );
		$id = CJFL_Page::ensure_page();
		wp_safe_redirect( self::settings_url( array( 'cjfl_msg' => $id ? 'page_ok' : 'page_fail' ) ) );
		exit;
	}

	public static function handle_test_email() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'cj-flooring-landing' ) );
		}
		check_admin_referer( 'cjfl_test_email' );
		wp_safe_redirect( self::settings_url( array( 'cjfl_msg' => CJFL_Form::send_test() ? 'mail_ok' : 'mail_fail' ) ) );
		exit;
	}

	/* ------------------------------------------------------------------ */
	/* Screen                                                              */
	/* ------------------------------------------------------------------ */

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$cfg = CJFL_Config::all();
		$seo = CJFL_Content::seo();
		$id  = CJFL_Config::landing_id();
		$msg = isset( $_GET['cjfl_msg'] ) ? sanitize_key( wp_unslash( $_GET['cjfl_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		echo '<div class="wrap"><h1>' . esc_html__( 'Flooring Landing', 'cj-flooring-landing' ) . '</h1>';

		$messages = array(
			'mail_ok'   => array( 'success', __( 'Test email handed to WordPress for delivery. Check the inbox (and spam folder) of the notification address.', 'cj-flooring-landing' ) ),
			'mail_fail' => array( 'error', __( 'WordPress could not send the test email. Install an SMTP plugin (WP Mail SMTP, FluentSMTP...) and try again. Leads are still saved under Flooring Leads.', 'cj-flooring-landing' ) ),
			'page_ok'   => array( 'success', __( 'Landing page created as a draft.', 'cj-flooring-landing' ) ),
			'page_fail' => array( 'error', __( 'The landing page could not be created.', 'cj-flooring-landing' ) ),
		);
		if ( isset( $messages[ $msg ] ) ) {
			echo '<div class="notice notice-' . esc_attr( $messages[ $msg ][0] ) . ' is-dismissible"><p>' . esc_html( $messages[ $msg ][1] ) . '</p></div>';
		}
		if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved. If you use a caching plugin, purge the cache so the landing page updates.', 'cj-flooring-landing' ) . '</p></div>';
		}

		/* ---- Landing page status ---- */
		echo '<div class="card" style="max-width:none;padding:1px 20px 14px"><h2>' . esc_html__( 'Landing page', 'cj-flooring-landing' ) . '</h2>';
		if ( $id ) {
			$status = get_post_status( $id );
			$labels = array(
				'publish' => __( 'Published', 'cj-flooring-landing' ),
				'draft'   => __( 'Draft (not public yet)', 'cj-flooring-landing' ),
				'pending' => __( 'Pending review', 'cj-flooring-landing' ),
				'private' => __( 'Private', 'cj-flooring-landing' ),
				'future'  => __( 'Scheduled', 'cj-flooring-landing' ),
			);
			echo '<p><strong>' . esc_html__( 'Status:', 'cj-flooring-landing' ) . '</strong> ' . esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : $status ) . '<br>';
			echo '<strong>' . esc_html__( 'Address:', 'cj-flooring-landing' ) . '</strong> <code>' . esc_html( get_permalink( $id ) ) . '</code></p>';
			echo '<p><a class="button button-primary" target="_blank" rel="noopener" href="' . esc_url( 'publish' === $status ? get_permalink( $id ) : get_preview_post_link( $id ) ) . '">' . esc_html( 'publish' === $status ? __( 'View page', 'cj-flooring-landing' ) : __( 'Preview page', 'cj-flooring-landing' ) ) . '</a> ';
			echo '<a class="button" href="' . esc_url( get_edit_post_link( $id, 'raw' ) ) . '">' . esc_html( 'publish' === $status ? __( 'Edit page settings', 'cj-flooring-landing' ) : __( 'Edit page and publish', 'cj-flooring-landing' ) ) . '</a></p>';
		} else {
			echo '<p>' . esc_html__( 'The landing page does not exist yet.', 'cj-flooring-landing' ) . '</p>';
			echo '<p><a class="button button-primary" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cjfl_create_page' ), 'cjfl_create_page' ) ) . '">' . esc_html__( 'Create the landing page', 'cj-flooring-landing' ) . '</a></p>';
		}
		if ( '' === get_option( 'permalink_structure' ) ) {
			echo '<div class="notice notice-warning inline" style="margin:10px 0 0"><p><strong>' . esc_html__( 'SEO tip:', 'cj-flooring-landing' ) . '</strong> ';
			echo wp_kses_post(
				sprintf(
					/* translators: %s: link to the Permalinks screen */
					__( 'Your site uses plain permalinks, so this page address looks like ?page_id=123. Switch to <em>Post name</em> in %s to get a clean, keyword-friendly address (and a working sitemap and robots.txt).', 'cj-flooring-landing' ),
					'<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">' . esc_html__( 'Settings > Permalinks', 'cj-flooring-landing' ) . '</a>'
				)
			);
			echo '</p></div>';
		}
		if ( CJFL_SEO::has_seo_plugin() ) {
			echo '<div class="notice notice-info inline" style="margin:10px 0 0"><p>' . esc_html__( 'An SEO plugin was detected. It controls the title, meta description and social tags for this page; this plugin still adds the business, service and FAQ structured data.', 'cj-flooring-landing' ) . '</p></div>';
		}
		echo '</div>';

		/* ---- Settings form ---- */
		echo '<form method="post" action="options.php">';
		settings_fields( self::GROUP );

		foreach ( self::sections() as $section ) {
			echo '<h2>' . esc_html( $section['title'] ) . '</h2>';
			if ( $section['desc'] ) {
				echo '<p class="description">' . esc_html( $section['desc'] ) . '</p>';
			}
			echo '<table class="form-table" role="presentation"><tbody>';
			foreach ( $section['fields'] as $key => $field ) {
				self::render_field( $key, $field, $cfg, $seo );
			}
			echo '</tbody></table>';
		}
		submit_button();
		echo '</form>';

		/* ---- Test email ---- */
		echo '<hr><h2>' . esc_html__( 'Check email delivery', 'cj-flooring-landing' ) . '</h2>';
		echo '<p>' . esc_html(
			sprintf(
				/* translators: %s: email addresses */
				__( 'Sends a test message to: %s', 'cj-flooring-landing' ),
				implode( ', ', CJFL_Config::notify_emails() )
			)
		) . '</p>';
		echo '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cjfl_test_email' ), 'cjfl_test_email' ) ) . '">' . esc_html__( 'Send test email', 'cj-flooring-landing' ) . '</a></p>';
		echo '</div>';
	}

	/**
	 * @param string $key   Option key.
	 * @param array  $field Field definition.
	 * @param array  $cfg   Effective settings.
	 * @param array  $seo   Effective SEO texts.
	 */
	private static function render_field( $key, array $field, array $cfg, array $seo ) {
		$name  = CJFL_Config::OPTION . '[' . $key . ']';
		$id    = 'cjfl-' . $key;
		$value = isset( $cfg[ $key ] ) ? $cfg[ $key ] : '';
		$ph    = '';

		// SEO fields show the effective default as a placeholder and stay empty until overridden.
		if ( isset( $field['placeholder'] ) && 0 === strpos( $field['placeholder'], 'seo:' ) ) {
			$seo_key = substr( $field['placeholder'], 4 );
			$ph      = isset( $seo[ $seo_key ] ) ? $seo[ $seo_key ] : '';
			$saved   = get_option( CJFL_Config::OPTION, array() );
			$value   = isset( $saved[ $key ] ) ? $saved[ $key ] : '';
		} elseif ( in_array( $key, array( 'seo_title', 'seo_description', 'h1', 'og_image', 'logo_url', 'notify_to', 'privacy_url' ), true ) ) {
			$saved = get_option( CJFL_Config::OPTION, array() );
			$value = isset( $saved[ $key ] ) ? $saved[ $key ] : '';
		}

		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
		switch ( $field['type'] ) {
			case 'checkbox':
				echo '<label><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1" ' . checked( ! empty( $value ), true, false ) . '> ' . esc_html( $field['help'] ) . '</label>';
				echo '</td></tr>';
				return;
			case 'textarea':
				echo '<textarea class="large-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="' . (int) ( isset( $field['rows'] ) ? $field['rows'] : 3 ) . '" placeholder="' . esc_attr( $ph ) . '">' . esc_textarea( (string) $value ) . '</textarea>';
				break;
			default:
				$type = in_array( $field['type'], array( 'email', 'url' ), true ) ? $field['type'] : 'text';
				echo '<input class="regular-text" style="width:100%;max-width:560px" type="' . esc_attr( $type ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '" placeholder="' . esc_attr( $ph ) . '">';
		}
		if ( ! empty( $field['help'] ) ) {
			echo '<p class="description">' . esc_html( $field['help'] ) . '</p>';
		}
		echo '</td></tr>';
	}
}
