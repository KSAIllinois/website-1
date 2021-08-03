<?php namespace fmwp\ajax;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\ajax\User' ) ) {


	/**
	 * Class User
	 *
	 * @package fmwp\ajax
	 */
	class User {


		/**
		 * User constructor.
		 */
		function __construct() {
		}


		function get_suggestions() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			$data = [];

			if ( empty( $_GET['term'] ) ) {
				wp_send_json( $data );
			}

			$term = urldecode( sanitize_text_field( $_GET['term'] ) );
			$term = str_replace( '@', '', $term );

			if ( empty( $term ) ) {
				wp_send_json( $data );
			}

			if ( current_user_can( 'administrator' ) ) {

				$lower_term = strtolower( $term );

				if ( $lower_term == 'everyone' || similar_text( $lower_term, 'everyone' ) == strlen( $term ) ) {
					$data[0]['list_item'] = '<strong>' . __( 'Everyone', 'forumwp' ) . '</strong> (@everyone)';
					$data[0]['replace_item'] = '@everyone';
				}

				if ( $lower_term == 'all' || similar_text( $lower_term, 'all' ) == strlen( $term ) ) {

					$data[0]['list_item'] = '<strong>' . __( 'All', 'forumwp' ) . '</strong> (@all)';
					$data[0]['replace_item'] = '@all';

				}

			}

			$response = wp_remote_get( add_query_arg( [
				'_locale'   => 'user',
				'search'    => $term,
			], get_site_url( get_current_blog_id(), '/wp-json/wp/v2/users' ) ) );

			if ( ! is_wp_error( $response ) ) {
				$response = json_decode( wp_remote_retrieve_body( $response ) );
			}

			if ( empty( $response ) ) {
				wp_send_json( $data );
			}

			foreach ( $response as $user ) {
				$user_slug = get_user_meta( $user->id, 'fmwp_permalink', true );
				$data[ $user->id ]['list_item'] = FMWP()->user()->get_avatar( $user->id, 'inline', '24' ) . '<strong>' . $user->name . '</strong> (@' . $user_slug . ')';
				$data[ $user->id ]['replace_item'] = '@' . $user_slug;
			}

			$data = array_unique( $data, SORT_REGULAR );

			wp_send_json( $data );
		}

	}
}