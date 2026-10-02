<?php
/**
 * Lead form: AJAX endpoints, validation, anti-spam, e-mail notification and storage.
 *
 * Flow: the page JS asks for a fresh nonce (so cached pages never ship an expired one),
 * then posts the form to admin-ajax.php. Every valid request is stored as a "Flooring Lead"
 * (so nothing is lost if e-mail delivery fails) and e-mailed to the team.
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

class CJFL_Form {

	const NONCE_ACTION = 'cjfl_submit';
	const RATE_LIMIT   = 10; // Default submissions per IP per hour (filter: cjfl_rate_limit).

	public static function init() {
		add_action( 'wp_ajax_nopriv_cjfl_nonce', array( __CLASS__, 'ajax_nonce' ) );
		add_action( 'wp_ajax_cjfl_nonce', array( __CLASS__, 'ajax_nonce' ) );
		add_action( 'wp_ajax_nopriv_cjfl_submit', array( __CLASS__, 'ajax_submit' ) );
		add_action( 'wp_ajax_cjfl_submit', array( __CLASS__, 'ajax_submit' ) );
	}

	/**
	 * Fresh nonce for the form (called by JS right before submitting).
	 */
	public static function ajax_nonce() {
		nocache_headers();
		wp_send_json_success( array( 'nonce' => wp_create_nonce( self::NONCE_ACTION ) ) );
	}

	/**
	 * Handles a submission.
	 */
	public static function ajax_submit() {
		nocache_headers();

		if ( ! check_ajax_referer( self::NONCE_ACTION, '_cjfl_nonce', false ) ) {
			wp_send_json_error(
				array( 'message' => self::fallback_message( __( 'Your session expired. Please reload the page and try again.', 'cj-flooring-landing' ) ) ),
				403
			);
		}

		// Honeypot: real visitors never see or fill this field. Pretend success so bots learn nothing.
		if ( ! empty( $_POST['cjfl_website'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- verified above.
			wp_send_json_success( array( 'message' => self::success_message( '' ) ) );
		}

		$data   = self::collect();
		$errors = self::validate( $data );
		if ( $errors ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please check the highlighted fields.', 'cj-flooring-landing' ),
					'fields'  => $errors,
				),
				422
			);
		}

		// Obvious link spam in the free-text message: accept silently, store nothing.
		if ( preg_match_all( '#https?://|www\.#i', $data['message'] ) > 2 ) {
			wp_send_json_success( array( 'message' => self::success_message( $data['name'] ) ) );
		}

		// Double click / duplicate protection.
		$dupe_key = 'cjfl_dupe_' . md5( $data['phone_digits'] . '|' . strtolower( $data['name'] ) );
		if ( get_transient( $dupe_key ) ) {
			wp_send_json_success( array( 'message' => self::success_message( $data['name'] ) ) );
		}

		// Rate limit per IP.
		$rate_key = 'cjfl_rate_' . md5( self::client_ip() );
		$count    = (int) get_transient( $rate_key );
		if ( $count >= (int) apply_filters( 'cjfl_rate_limit', self::RATE_LIMIT ) ) {
			wp_send_json_error(
				array( 'message' => self::fallback_message( __( 'Too many requests from your connection.', 'cj-flooring-landing' ) ) ),
				429
			);
		}
		set_transient( $rate_key, $count + 1, HOUR_IN_SECONDS );
		set_transient( $dupe_key, 1, 2 * MINUTE_IN_SECONDS );

		$lead_id = CJFL_Leads::create( $data );
		$sent    = self::notify( $data, $lead_id );
		if ( $lead_id ) {
			update_post_meta( $lead_id, '_cjfl_email_status', $sent ? 'sent' : 'failed' );
		}

		/**
		 * Fires after a lead is stored. Use it to push the lead to a CRM, Zapier, Slack, etc.
		 *
		 * @param int   $lead_id Lead post ID (0 if it could not be stored).
		 * @param array $data    Sanitized lead data.
		 * @param bool  $sent    Whether wp_mail() accepted the notification.
		 */
		do_action( 'cjfl_lead_created', $lead_id, $data, $sent );

		wp_send_json_success( array( 'message' => self::success_message( $data['name'] ) ) );
	}

	/* ------------------------------------------------------------------ */
	/* Input                                                               */
	/* ------------------------------------------------------------------ */

	/**
	 * Reads and sanitizes $_POST.
	 *
	 * @return array
	 */
	private static function collect() {
		// phpcs:disable WordPress.Security.NonceVerification -- nonce verified in ajax_submit().
		$text = static function ( $key, $max = 120 ) {
			$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( (string) $_POST[ $key ] ) ) : '';
			return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $max ) : substr( $value, 0, $max );
		};

		$phone   = $text( 'phone', 40 );
		$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( (string) $_POST['message'] ) ) : '';
		$message = function_exists( 'mb_substr' ) ? mb_substr( $message, 0, 2000 ) : substr( $message, 0, 2000 );

		$flooring = $text( 'flooring', 30 );
		$property = $text( 'property', 30 );

		$data = array(
			'name'         => $text( 'name', 80 ),
			'phone'        => $phone,
			'phone_digits' => preg_replace( '/\D+/', '', $phone ),
			'email'        => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( (string) $_POST['email'] ) ) : '',
			'flooring'     => array_key_exists( $flooring, CJFL_Content::flooring_options() ) ? $flooring : '',
			'property'     => array_key_exists( $property, CJFL_Content::property_options() ) ? $property : '',
			'message'      => $message,
			'form'         => in_array( $text( 'form', 10 ), array( 'hero', 'main' ), true ) ? $text( 'form', 10 ) : 'main',
			'source_url'   => isset( $_POST['source_url'] ) ? esc_url_raw( wp_unslash( (string) $_POST['source_url'] ) ) : '',
			'referrer'     => isset( $_POST['referrer'] ) ? esc_url_raw( wp_unslash( (string) $_POST['referrer'] ) ) : '',
			'tracking'     => array(),
		);
		foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid' ) as $key ) {
			$value = $text( $key, 120 );
			if ( '' !== $value ) {
				$data['tracking'][ $key ] = $value;
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification

		// Only keep URLs that belong to this site as the source page.
		$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$src_host  = wp_parse_url( $data['source_url'], PHP_URL_HOST );
		if ( $src_host && $home_host && strtolower( $src_host ) !== strtolower( $home_host ) ) {
			$data['source_url'] = '';
		}

		return $data;
	}

	/**
	 * @param array $data Sanitized data.
	 * @return array<string,string> field => message
	 */
	private static function validate( array $data ) {
		$errors = array();
		$length = function_exists( 'mb_strlen' ) ? mb_strlen( $data['name'] ) : strlen( $data['name'] );
		if ( $length < 2 ) {
			$errors['name'] = __( 'Please enter your name.', 'cj-flooring-landing' );
		}
		$digits = strlen( $data['phone_digits'] );
		if ( $digits < 10 || $digits > 15 ) {
			$errors['phone'] = __( 'Please enter a valid phone number.', 'cj-flooring-landing' );
		}
		$raw_email = isset( $_POST['email'] ) ? trim( wp_unslash( (string) $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
		if ( '' !== $raw_email && ( '' === $data['email'] || ! is_email( $data['email'] ) ) ) {
			$errors['email'] = __( 'Please enter a valid email address.', 'cj-flooring-landing' );
		}
		return $errors;
	}

	/**
	 * Best-effort client IP (only used, hashed, for rate limiting; never stored).
	 *
	 * @return string
	 */
	private static function client_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) ) : '';
		return (string) apply_filters( 'cjfl_client_ip', $ip );
	}

	/* ------------------------------------------------------------------ */
	/* Messages                                                            */
	/* ------------------------------------------------------------------ */

	/**
	 * @param string $name Customer name.
	 * @return string
	 */
	private static function success_message( $name ) {
		$first = '' !== $name ? strtok( $name, ' ' ) : '';
		if ( $first ) {
			/* translators: %s: customer first name */
			return sprintf( __( 'Thank you, %s! Your request was sent. Our team will contact you to plan your free estimate.', 'cj-flooring-landing' ), $first );
		}
		return __( 'Thank you! Your request was sent. Our team will contact you to plan your free estimate.', 'cj-flooring-landing' );
	}

	/**
	 * @param string $reason Why it failed.
	 * @return string
	 */
	private static function fallback_message( $reason ) {
		/* translators: 1: reason, 2: phone number */
		return sprintf( __( '%1$s You can also call us at %2$s.', 'cj-flooring-landing' ), $reason, CJFL_Config::get( 'phone_display' ) );
	}

	/* ------------------------------------------------------------------ */
	/* Notification e-mail                                                 */
	/* ------------------------------------------------------------------ */

	/**
	 * Plain-text notification to the team (plain text = best deliverability).
	 *
	 * @param array $data    Lead data.
	 * @param int   $lead_id Stored lead ID.
	 * @return bool wp_mail() result.
	 */
	private static function notify( array $data, $lead_id ) {
		$flooring = CJFL_Content::flooring_options();
		$property = CJFL_Content::property_options();

		$subject = trim( (string) CJFL_Config::get( 'subject_prefix' ) ) . ' - ' . $data['name'];

		$lines   = array();
		$lines[] = 'New flooring estimate request from the landing page.';
		$lines[] = '';
		$lines[] = 'Name: ' . $data['name'];
		$lines[] = 'Phone: ' . $data['phone'];
		$lines[] = 'Email: ' . ( $data['email'] ? $data['email'] : '(not provided)' );
		$lines[] = 'Type of flooring: ' . ( $data['flooring'] ? $flooring[ $data['flooring'] ] : '(not selected)' );
		$lines[] = 'Property type: ' . ( $data['property'] ? $property[ $data['property'] ] : '(not selected)' );
		$lines[] = '';
		$lines[] = 'Message:';
		$lines[] = '' !== $data['message'] ? $data['message'] : '(none)';
		$lines[] = '';
		$lines[] = '---';
		$lines[] = 'Form: ' . ( 'hero' === $data['form'] ? 'quick form (top of page)' : 'full form (bottom of page)' );
		$lines[] = 'Page: ' . ( $data['source_url'] ? $data['source_url'] : home_url( '/' ) );
		if ( $data['tracking'] ) {
			$pairs = array();
			foreach ( $data['tracking'] as $key => $value ) {
				$pairs[] = $key . '=' . $value;
			}
			$lines[] = 'Campaign: ' . implode( ' | ', $pairs );
		}
		if ( $data['referrer'] ) {
			$lines[] = 'Referrer: ' . $data['referrer'];
		}
		$lines[] = 'Received: ' . wp_date( 'Y-m-d H:i' );
		if ( $lead_id ) {
			$lines[] = 'Lead in WordPress: ' . admin_url( 'post.php?post=' . (int) $lead_id . '&action=edit' );
		}

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		if ( $data['email'] ) {
			$headers[] = 'Reply-To: ' . str_replace( array( "\r", "\n", ',', '<', '>' ), ' ', $data['name'] ) . ' <' . $data['email'] . '>';
		}

		return (bool) wp_mail( CJFL_Config::notify_emails(), wp_specialchars_decode( $subject, ENT_QUOTES ), implode( "\n", $lines ), $headers );
	}

	/**
	 * Sends a test message (used by the settings screen).
	 *
	 * @return bool
	 */
	public static function send_test() {
		$lines = array(
			'This is a test message from the CJ Flooring Landing plugin.',
			'',
			'If you can read this, lead notifications are being delivered to this address.',
			'Site: ' . home_url( '/' ),
			'Sent: ' . wp_date( 'Y-m-d H:i' ),
		);
		return (bool) wp_mail(
			CJFL_Config::notify_emails(),
			'Test: ' . trim( (string) CJFL_Config::get( 'subject_prefix' ) ),
			implode( "\n", $lines ),
			array( 'Content-Type: text/plain; charset=UTF-8' )
		);
	}
}
