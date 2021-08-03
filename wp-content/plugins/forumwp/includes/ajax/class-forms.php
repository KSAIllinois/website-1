<?php
namespace fmwp\ajax;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\ajax\Forms' ) ) {


	/**
	 * Class Forms
	 *
	 * @package fmwp\ajax
	 */
	class Forms {


	    /**
		 * Forms constructor.
		 */
		function __construct() {
		}


		/**
		 *
		 */
		function get_icons() {
			FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

			$icons = file_get_contents( fmwp_path . 'assets/common/libs/fontawesome/metadata/icons.json' );

			wp_send_json_success( json_decode( $icons ) );
		}

	}
}