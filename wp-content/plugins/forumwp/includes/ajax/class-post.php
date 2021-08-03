<?php
namespace fmwp\ajax;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\ajax\Post' ) ) {


	/**
	 * Class Post
	 *
	 * @package fmwp\ajax
	 */
	class Post {


		/**
		 * Post constructor.
		 */
		function __construct() {
		}


		/**
		 * Build preview via AJAX request
		 */
		function build_preview() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( isset( $_REQUEST['action'] ) ) {
				$content = '';

				switch ( $_REQUEST['action'] ) {
					case 'fmwp_topic_build_preview':
						list( $origin_content, $content ) = FMWP()->common()->post()->prepare_content( $_REQUEST['content'], 'fmwp_topic' );
						break;
					case 'fmwp_reply_build_preview':
						list( $origin_content, $content ) = FMWP()->common()->post()->prepare_content( $_REQUEST['content'], 'fmwp_reply' );
						break;
				}

				wp_send_json_success( stripslashes( nl2br( $content ) ) );
			}

			wp_send_json_error( __( 'Wrong request', 'forumwp' ) );
		}
	}
}