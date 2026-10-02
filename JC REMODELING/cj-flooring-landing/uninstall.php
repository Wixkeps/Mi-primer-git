<?php
/**
 * Runs when the plugin is deleted from Plugins > Installed Plugins.
 *
 * Settings, leads and the landing page are kept unless "Delete all data on uninstall"
 * was ticked in Flooring Leads > Settings (so a reinstall never loses leads by accident).
 *
 * @package CJ_Flooring_Landing
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Temporary data is always safe to remove.
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_cjfl_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_cjfl_' ) . '%'
	)
);
delete_transient( 'cjfl_activated' );

$settings = get_option( 'cjfl_settings', array() );
if ( empty( $settings['delete_data'] ) ) {
	return;
}

$leads = get_posts(
	array(
		'post_type'        => 'cjfl_lead',
		'post_status'      => 'any',
		'posts_per_page'   => -1,
		'fields'           => 'ids',
		'no_found_rows'    => true,
		'suppress_filters' => true,
	)
);
foreach ( $leads as $lead_id ) {
	wp_delete_post( (int) $lead_id, true );
}

$page_id = (int) get_option( 'cjfl_page_id' );
if ( $page_id && 'page' === get_post_type( $page_id ) ) {
	wp_delete_post( $page_id, true );
}

delete_option( 'cjfl_settings' );
delete_option( 'cjfl_page_id' );
