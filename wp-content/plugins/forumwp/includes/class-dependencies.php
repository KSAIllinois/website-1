<?php
namespace fmwp;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\Dependencies' ) ) {


	/**
	 * Class Dependencies
	 *
	 * @package fmwp
	 */
	class Dependencies {


		/**
		 * @var array
		 */
		private static $active_plugins;


		/**
		 * Set active plugins
		 */
		public static function init() {
			self::$active_plugins = (array) get_option( 'active_plugins', [] );

			if ( is_multisite() ) {
				self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', [] ) );
			}
		}


		/**
		 * Get active plugins class variable
		 *
		 * @return array
		 */
		public static function get_active_plugins() {
			if ( ! self::$active_plugins ) {
				self::init();
			}

			return self::$active_plugins;
		}


		/**
		 * Check if ForumWP - Plus Modules plugin is active
		 *
		 * @return bool|string
		 */
		public static function forumwp_plus_active_check() {
			$slug = 'forumwp-plus/forumwp-plus.php';
			$active_plugins = self::get_active_plugins();

			if ( in_array( $slug, $active_plugins ) || array_key_exists( $slug, $active_plugins ) ) {
				return __( 'ForumWP 2.0 is not compatible with the ForumWP - Plus Modules plugin. Please download the ForumWP - Pro plugin from your account page <a href="https://forumwpplugin.com/account" target="_blank">here</a> and install/activate this plugin on your site to replace the Plus Modules plugin.', 'forumwp' );
			}

			return false;
		}


		/**
		 * @return bool|string
		 */
		public static function check_folder() {
			//check correct folder name for extensions
			$slug = 'forumwp/forumwp.php';
			$active_plugins = self::get_active_plugins();

			if ( ! in_array( $slug, $active_plugins ) && ! array_key_exists( $slug, $active_plugins ) ) {
				return sprintf( __( 'Please check <strong>"%s"</strong> plugin\'s folder name. Correct folder name is <strong>"forumwp"</strong>', 'forumwp' ), fmwp_plugin_name );
			}

			return true;
		}

	}
}


if ( ! function_exists( 'is_fmwp_plus_active' ) ) {


	/**
	 * Check ForumWP - Plus is active
	 *
	 * @return bool|string
	 */
	function is_fmwp_plus_active() {
		return Dependencies::forumwp_plus_active_check();
	}
}


if ( ! function_exists( 'fmwp_check_folder' ) ) {


	/**
	 * Check ForumWP - Pro folder
	 *
	 * @return bool true - correct | string - message with an error
	 */
	function fmwp_check_folder() {
		return Dependencies::check_folder();
	}
}