<?php
/**
 * Uninstall ForumWP
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

if ( ! defined( 'fmwp_path' ) ) {
	define( 'fmwp_path', plugin_dir_path( __FILE__ ) ); 
}

if ( ! defined( 'fmwp_url' ) ) {
	define( 'fmwp_url', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'fmwp_plugin' ) ) {
	define( 'fmwp_plugin', plugin_basename( __FILE__ ) );
}

/** @noinspection PhpIncludeInspection */
require_once fmwp_path . 'includes/class-functions.php';
/** @noinspection PhpIncludeInspection */
require_once fmwp_path . 'includes/class-init.php';

$delete_options = FMWP()->options()->get( 'uninstall-delete-settings' );

if ( ! empty( $delete_options ) ) {

	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}fmwp_reports" );

	delete_option( 'fmwp_options' );
	delete_option( 'fmwp_last_version_upgrade' );
	delete_option( 'fmwp_first_activation_date' );
	delete_option( 'fmwp_version' );
	delete_option( 'fmwp_flush_rewrite_rules' );
	delete_option( 'fmwp_hidden_admin_notices' );

	$wpdb->query(
		"DELETE *
		FROM {$wpdb->usermeta} 
		WHERE meta_key LIKE 'fmwp_%'"
	);
}