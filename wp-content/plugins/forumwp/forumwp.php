<?php
/*
Plugin Name: ForumWP
Plugin URI: https://forumwpplugin.com/
Description: A full-featured, powerful forum plugin for WordPress
Version: 2.0.2
Author: ForumWP
Text Domain: forumwp
Domain Path: /languages
*/

defined( 'ABSPATH' ) || exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
$plugin_data = get_plugin_data( __FILE__ );

define( 'fmwp_url', plugin_dir_url( __FILE__ ) );
define( 'fmwp_path', plugin_dir_path( __FILE__ ) );
define( 'fmwp_plugin', plugin_basename( __FILE__ ) );
define( 'fmwp_author', $plugin_data['AuthorName'] );
define( 'fmwp_version', $plugin_data['Version'] );
define( 'fmwp_plugin_name', $plugin_data['Name'] );


if ( ! function_exists( 'fmwp_check_dependencies' ) ) {


	/**
	 *
	 */
	function fmwp_check_dependencies() {
		/** @noinspection PhpIncludeInspection */
		require_once fmwp_path . 'includes/class-dependencies.php';

		if ( true !== $message = fmwp\fmwp_check_folder() ) {

			add_action( 'admin_notices', function() use ( $message ) {
				ob_start(); ?>

				<div class="error">
					<p><?php echo $message; ?></p>
				</div>

				<?php ob_get_flush();
			} );

		} elseif ( false !== $message = fmwp\is_fmwp_plus_active() ) {

			add_action( 'admin_notices', function() use ( $message ) {
				ob_start(); ?>

				<div class="error">
					<p><?php echo $message; ?></p>
				</div>

				<?php ob_get_flush();
			} );

		} else {

			require_once 'includes/class-functions.php';
			require_once 'includes/class-init.php';

			//run
			FMWP();

		}
	}
}

add_action( 'plugins_loaded', 'fmwp_check_dependencies', -21 );


if ( ! function_exists( 'fmwp_activation' ) ) {
	function fmwp_activation() {
		require_once 'includes/class-functions.php';
		require_once 'includes/class-init.php';

		//run
		FMWP()->install()->activation();
	}
}

register_activation_hook( fmwp_plugin, 'fmwp_activation' );


if ( ! function_exists( 'fmwp_maybe_network_activation' ) ) {
	function fmwp_maybe_network_activation() {
		require_once 'includes/class-functions.php';
		require_once 'includes/class-init.php';

		//run
		FMWP()->install()->maybe_network_activation();
	}
}
if ( is_multisite() && ! defined( 'DOING_AJAX' ) ) {
	add_action( 'wp_loaded', 'fmwp_maybe_network_activation' );
}