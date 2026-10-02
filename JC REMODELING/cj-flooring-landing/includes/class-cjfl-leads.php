<?php
/**
 * "Flooring Leads": every form submission is stored here (private, administrators only).
 *
 * @package CJ_Flooring_Landing
 */

defined( 'ABSPATH' ) || exit;

class CJFL_Leads {

	const POST_TYPE = 'cjfl_lead';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_action( 'add_meta_boxes_' . self::POST_TYPE, array( __CLASS__, 'meta_boxes' ) );
		add_filter( 'post_row_actions', array( __CLASS__, 'row_actions' ), 10, 2 );
		add_filter( 'bulk_actions-edit-' . self::POST_TYPE, array( __CLASS__, 'bulk_actions' ) );
	}

	public static function register() {
		$cap = 'manage_options';
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'               => __( 'Flooring Leads', 'cj-flooring-landing' ),
					'singular_name'      => __( 'Flooring Lead', 'cj-flooring-landing' ),
					'menu_name'          => __( 'Flooring Leads', 'cj-flooring-landing' ),
					'all_items'          => __( 'All Leads', 'cj-flooring-landing' ),
					'edit_item'          => __( 'Lead details', 'cj-flooring-landing' ),
					'view_item'          => __( 'Lead details', 'cj-flooring-landing' ),
					'search_items'       => __( 'Search leads', 'cj-flooring-landing' ),
					'not_found'          => __( 'No leads yet. They will show up here when someone submits the form.', 'cj-flooring-landing' ),
					'not_found_in_trash' => __( 'No leads in the trash.', 'cj-flooring-landing' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'menu_position'       => 26,
				'menu_icon'           => 'dashicons-email-alt',
				'supports'            => array( 'title' ),
				'map_meta_cap'        => false,
				'capabilities'        => array(
					'edit_post'              => $cap,
					'read_post'              => $cap,
					'delete_post'            => $cap,
					'edit_posts'             => $cap,
					'edit_others_posts'      => $cap,
					'delete_posts'           => $cap,
					'delete_others_posts'    => $cap,
					'publish_posts'          => $cap,
					'read_private_posts'     => $cap,
					'delete_private_posts'   => $cap,
					'delete_published_posts' => $cap,
					'edit_private_posts'     => $cap,
					'edit_published_posts'   => $cap,
					'create_posts'           => 'do_not_allow',
				),
			)
		);
	}

	/**
	 * Stores a lead.
	 *
	 * @param array $data Sanitized lead data (see CJFL_Form::collect()).
	 * @return int Lead ID (0 on failure).
	 */
	public static function create( array $data ) {
		$lead_id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'publish',
				'post_title'   => $data['name'],
				'post_content' => $data['message'],
			),
			true
		);
		if ( is_wp_error( $lead_id ) || ! $lead_id ) {
			return 0;
		}
		$meta = array(
			'phone'    => $data['phone'],
			'email'    => $data['email'],
			'flooring' => $data['flooring'],
			'property' => $data['property'],
			'form'     => $data['form'],
			'source'   => $data['source_url'],
			'referrer' => $data['referrer'],
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $lead_id, '_cjfl_' . $key, $value );
		}
		if ( $data['tracking'] ) {
			update_post_meta( $lead_id, '_cjfl_tracking', $data['tracking'] );
		}
		return (int) $lead_id;
	}

	/* ------------------------------------------------------------------ */
	/* Admin list                                                          */
	/* ------------------------------------------------------------------ */

	/**
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		return array(
			'cb'       => isset( $columns['cb'] ) ? $columns['cb'] : '<input type="checkbox" />',
			'title'    => __( 'Name', 'cj-flooring-landing' ),
			'phone'    => __( 'Phone', 'cj-flooring-landing' ),
			'email'    => __( 'Email', 'cj-flooring-landing' ),
			'flooring' => __( 'Flooring', 'cj-flooring-landing' ),
			'property' => __( 'Property', 'cj-flooring-landing' ),
			'mail'     => __( 'Notification', 'cj-flooring-landing' ),
			'received' => __( 'Received', 'cj-flooring-landing' ),
		);
	}

	/**
	 * @param string $column  Column key.
	 * @param int    $post_id Lead ID.
	 */
	public static function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'phone':
				$phone = (string) get_post_meta( $post_id, '_cjfl_phone', true );
				if ( $phone ) {
					$tel = preg_replace( '/[^\d+]/', '', $phone );
					echo '<a href="' . esc_url( 'tel:' . $tel ) . '">' . esc_html( $phone ) . '</a>';
				}
				break;
			case 'email':
				$email = (string) get_post_meta( $post_id, '_cjfl_email', true );
				if ( $email ) {
					echo '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>';
				} else {
					echo '&mdash;';
				}
				break;
			case 'flooring':
				$options = CJFL_Content::flooring_options();
				$key     = (string) get_post_meta( $post_id, '_cjfl_flooring', true );
				echo isset( $options[ $key ] ) ? esc_html( $options[ $key ] ) : '&mdash;';
				break;
			case 'property':
				$options = CJFL_Content::property_options();
				$key     = (string) get_post_meta( $post_id, '_cjfl_property', true );
				echo isset( $options[ $key ] ) ? esc_html( $options[ $key ] ) : '&mdash;';
				break;
			case 'received':
				$stamp = (int) get_post_time( 'U', true, $post_id );
				if ( $stamp ) {
					/* translators: %s: time difference such as "5 mins" */
					echo esc_html( sprintf( __( '%s ago', 'cj-flooring-landing' ), human_time_diff( $stamp, time() ) ) );
					echo '<br><small>' . esc_html( wp_date( 'M j, Y g:i a', $stamp ) ) . '</small>';
				}
				break;
			case 'mail':
				$status = (string) get_post_meta( $post_id, '_cjfl_email_status', true );
				if ( 'sent' === $status ) {
					echo '<span style="color:#1a7f37">' . esc_html__( 'Email sent', 'cj-flooring-landing' ) . '</span>';
				} elseif ( 'failed' === $status ) {
					echo '<span style="color:#b32d2e">' . esc_html__( 'Email failed', 'cj-flooring-landing' ) . '</span>';
				} else {
					echo '&mdash;';
				}
				break;
		}
	}

	/**
	 * @param array   $actions Row actions.
	 * @param WP_Post $post    Post.
	 * @return array
	 */
	public static function row_actions( $actions, $post ) {
		if ( self::POST_TYPE === $post->post_type ) {
			unset( $actions['inline hide-if-no-js'] );
		}
		return $actions;
	}

	/**
	 * @param array $actions Bulk actions.
	 * @return array
	 */
	public static function bulk_actions( $actions ) {
		unset( $actions['edit'] );
		return $actions;
	}

	/* ------------------------------------------------------------------ */
	/* Lead details screen                                                 */
	/* ------------------------------------------------------------------ */

	public static function meta_boxes() {
		add_meta_box( 'cjfl_lead_details', __( 'Request details', 'cj-flooring-landing' ), array( __CLASS__, 'render_details' ), self::POST_TYPE, 'normal', 'high' );
	}

	/**
	 * @param WP_Post $post Lead.
	 */
	public static function render_details( $post ) {
		$flooring = CJFL_Content::flooring_options();
		$property = CJFL_Content::property_options();
		$get      = static function ( $key ) use ( $post ) {
			return (string) get_post_meta( $post->ID, '_cjfl_' . $key, true );
		};
		$phone    = $get( 'phone' );
		$email    = $get( 'email' );
		$fl       = $get( 'flooring' );
		$pr       = $get( 'property' );
		$status   = $get( 'email_status' );
		$tracking = get_post_meta( $post->ID, '_cjfl_tracking', true );

		echo '<table class="widefat striped" style="max-width:760px"><tbody>';
		self::row( __( 'Name', 'cj-flooring-landing' ), esc_html( $post->post_title ) );
		self::row( __( 'Phone', 'cj-flooring-landing' ), $phone ? '<a href="' . esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a>' : '&mdash;' );
		self::row( __( 'Email', 'cj-flooring-landing' ), $email ? '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>' : '&mdash;' );
		self::row( __( 'Type of flooring', 'cj-flooring-landing' ), isset( $flooring[ $fl ] ) ? esc_html( $flooring[ $fl ] ) : '&mdash;' );
		self::row( __( 'Property type', 'cj-flooring-landing' ), isset( $property[ $pr ] ) ? esc_html( $property[ $pr ] ) : '&mdash;' );
		self::row( __( 'Message', 'cj-flooring-landing' ), '' !== $post->post_content ? nl2br( esc_html( $post->post_content ) ) : '&mdash;' );
		self::row( __( 'Page', 'cj-flooring-landing' ), $get( 'source' ) ? '<a href="' . esc_url( $get( 'source' ) ) . '" target="_blank" rel="noopener">' . esc_html( $get( 'source' ) ) . '</a>' : '&mdash;' );
		if ( is_array( $tracking ) && $tracking ) {
			$pairs = array();
			foreach ( $tracking as $key => $value ) {
				$pairs[] = esc_html( $key . ' = ' . $value );
			}
			self::row( __( 'Campaign', 'cj-flooring-landing' ), implode( '<br>', $pairs ) );
		}
		if ( $get( 'referrer' ) ) {
			self::row( __( 'Referrer', 'cj-flooring-landing' ), esc_html( $get( 'referrer' ) ) );
		}
		self::row(
			__( 'Email notification', 'cj-flooring-landing' ),
			'sent' === $status ? esc_html__( 'Sent to the team', 'cj-flooring-landing' ) : ( 'failed' === $status ? esc_html__( 'Failed: install an SMTP plugin so WordPress emails are delivered', 'cj-flooring-landing' ) : '&mdash;' )
		);
		echo '</tbody></table>';
	}

	/**
	 * @param string $label Label.
	 * @param string $html  Already-escaped HTML.
	 */
	private static function row( $label, $html ) {
		echo '<tr><th style="width:170px;text-align:left">' . esc_html( $label ) . '</th><td>' . $html . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput -- callers escape.
	}
}
