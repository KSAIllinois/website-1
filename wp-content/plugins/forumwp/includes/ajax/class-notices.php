<?php namespace fmwp\ajax;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\ajax\Notices' ) ) {


	/**
	 * Class Notices
	 *
	 * @package fmwp\ajax
	 */
	class Notices {


		/**
		 * Notices constructor.
		 */
		function __construct() {
		}


		/**
		 * AJAX dismiss notices
		 */
		function dismiss_notice() {
			FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

			if ( empty( $_POST['key'] ) ) {
				wp_send_json_error( __( 'Wrong Data', 'forumwp' ) );
			}

			FMWP()->admin()->notices()->dismiss( sanitize_key( $_POST['key'] ) );
			wp_send_json_success();
		}

	}
}