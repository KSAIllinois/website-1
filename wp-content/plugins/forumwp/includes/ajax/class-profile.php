<?php
namespace fmwp\ajax;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\ajax\Profile' ) ) {


	/**
	 * Class Profile
	 *
	 * @package fmwp\ajax
	 */
	class Profile {


		/**
		 * Profile constructor.
		 */
		function __construct() {
		}


		/**
		 *
		 */
		function get_tab_content() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['tab'] ) ) {
				wp_send_json_error( __( 'Invalid Tab', 'forumwp' ) );
			}
			if ( empty( $_POST['user_id'] ) ) {
				wp_send_json_error( __( 'Invalid User', 'forumwp' ) );
			}

			$tabs = array_keys( FMWP()->frontend()->profile()->tabs_list() );

			$tab = sanitize_key( $_POST['tab'] );
			if ( ! in_array( $tab, $tabs ) ) {
				wp_send_json_error( __( 'Invalid Tab', 'forumwp' ) );
			}

			$user = get_userdata( absint( $_POST['user_id'] ) );
			if ( empty( $user ) || is_wp_error( $user ) ) {
				wp_send_json_error( __( 'Invalid User', 'forumwp' ) );
			}

			$tab_data = [];
			if ( method_exists( FMWP()->frontend()->profile(),'get_' . $tab . '_tab_data' ) ) {
				$tab_data = call_user_func( [ FMWP()->frontend()->profile(), 'get_' . $tab . '_tab_data' ], $user );
			}

			wp_send_json_success( $tab_data );
		}


		/**
		 *
		 */
		function get_profile_topics() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['user_id'] ) ) {
				wp_send_json_error( __( 'Invalid User', 'forumwp' ) );
			}

			$user = get_userdata( absint( $_POST['user_id'] ) );
			if ( empty( $user ) || is_wp_error( $user ) ) {
				wp_send_json_error( __( 'Invalid User', 'forumwp' ) );
			}

			$topics = FMWP()->common()->topic()->get_topics_by_author( $user->ID, [
				'paged'             => ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : '1',
				'posts_per_page'    => FMWP()->options()->get_variable( 'topics_per_page' ),
			] );

			$data = [];
			foreach ( $topics as $topic ) {
				$data[] = FMWP()->ajax()->topic()->response_data( $topic );
			}

			wp_send_json_success( $data );
		}


		/**
		 *
		 */
		function get_profile_replies() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['user_id'] ) ) {
				wp_send_json_error( __( 'Invalid User', 'forumwp' ) );
			}

			$user = get_userdata( absint( $_POST['user_id'] ) );
			if ( empty( $user ) || is_wp_error( $user ) ) {
				wp_send_json_error( __( 'Invalid User', 'forumwp' ) );
			}

			$replies = FMWP()->common()->reply()->get_replies_by_author( $user->ID, [
				'paged'             => ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : '1',
				'posts_per_page'    => FMWP()->options()->get_variable( 'replies_per_page' ),
			] );

			$data = [];
			foreach ( $replies as $reply ) {
				$data[] = FMWP()->ajax()->reply()->response_data( $reply );
			}

			wp_send_json_success( $data );
		}
	}
}