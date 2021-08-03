<?php
namespace fmwp\frontend;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\frontend\Actions_Listener' ) ) {


	/**
	 * Class Actions_Listener
	 *
	 * @package fmwp\frontend
	 */
	class Actions_Listener {


		/**
		 * Actions_Listener constructor.
		 */
		function __construct() {
			//filter edit/show profile handler
			add_action( 'init', [ &$this, 'submit_form_handler' ] );
		}


		/**
		 * Password strength test
		 *
		 * @param string $candidate
		 *
		 * @return bool
		 */
		function strong_pass( $candidate ) {

			if ( strlen( $candidate ) < 8) {
				return false;
			}

			// are used Unicode Regular Expressions
			$regexps = [
				'/[\p{Lu}]/u', // any Letter Uppercase symbol
				'/[\p{Ll}]/u', // any Letter Lowercase symbol
				'/[\p{N}]/u', // any Number symbol
			];
			foreach ( $regexps as $regexp ) {
				if ( preg_match_all( $regexp, $candidate, $o ) < 1 ) {
					return false;
				}
			}
			return true;
		}


		/**
		 * Handler $_POST forms to avoid headers already sent after wp_redirect function
		 *
		 * @since 2.0
		 */
		function submit_form_handler() {

			if ( empty( $_POST['fmwp-action'] ) ) {
				return;
			}

			$action = sanitize_key( $_POST['fmwp-action'] );
			if ( empty( $action ) ) {
				return;
			}

			switch ( $action ) {
				case 'registration': {

					global $registration;

					$registration = FMWP()->frontend()->forms( [ 'id'   => 'fmwp-register', ] );

					$registration->flush_errors();

					if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'fmwp-registration' ) ) {
						$registration->add_error( 'global', __( 'Security issue, Please try again', 'forumwp' ) );
					}

					if ( empty( $_POST['user_login'] ) ) {
						$registration->add_error( 'user_login', __( 'Login is empty', 'forumwp' ) );
					}
					$user_login = sanitize_user( $_POST['user_login'] );
					if ( username_exists( $user_login ) ) {
						$registration->add_error( 'user_login', __( 'A user with this username already exists', 'forumwp' ) );
					}

					if ( empty( $_POST['user_email'] ) ) {
						$registration->add_error( 'user_email', __( 'Email is empty', 'forumwp' ) );
					}
					$user_email = sanitize_email( trim( $_POST['user_email'] ) );
					if ( ! is_email( $user_email ) ) {
						$registration->add_error( 'user_email', __( 'Email is invalid', 'forumwp' ) );
					}
					if ( email_exists( $user_email ) ) {
						$registration->add_error( 'user_email', __( 'A user with this email already exists', 'forumwp' ) );
					}


					if ( empty( $_POST['user_pass'] ) ) {
						$registration->add_error( 'user_pass', __( 'Password cannot be an empty', 'forumwp' ) );
					}
					if ( empty( $_POST['user_pass2'] ) ) {
						$registration->add_error( 'user_pass2', __( 'Please confirm the password', 'forumwp' ) );
					}
					$user_pass = trim( $_POST['user_pass'] );
					$user_pass2 = trim( $_POST['user_pass2'] );

					if ( ! $this->strong_pass( $user_pass ) ) {
						$registration->add_error( 'user_pass', __( 'Your password must contain at least one lowercase letter, one capital letter and one number and be at least 8 characters', 'forumwp' ) );
					}

					if ( $user_pass != $user_pass2 ) {
						$registration->add_error( 'user_pass2', __( 'Sorry, passwords do not match!', 'forumwp' ) );
					}

					do_action( 'fmwp_before_submit_registration', $registration );

					if ( ! $registration->has_errors() ) {
						$userdata = [
							'user_login'    => $user_login,
							'user_pass'     => $user_pass,
							'user_email'    => $user_email,
							'first_name'    => ! empty( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '',
							'last_name'     => ! empty( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '',
							'role'          => FMWP()->options()->get( 'default_role' ),
						];
						$userdata = apply_filters( 'fmwp_onsubmit_registration_args', $userdata, $registration );

						$user_id = wp_insert_user( $userdata );

						do_action( 'fmwp_user_register', $user_id );

						// auto-login after registration
						$user = wp_signon( [
							'user_login'    => $user_login,
							'user_password' => $user_pass
						] );

						if ( is_wp_error( $user ) ) {
							$redirect = FMWP()->common()->get_preset_page_link( 'register' );
						} else {
							//redirect to profile page
							$redirect = FMWP()->user()->get_profile_link( $user->ID );
						}

						if ( ! empty( $_POST['redirect_to'] ) ) {
							$redirect = esc_url_raw( $_POST['redirect_to'] );
						}

						wp_safe_redirect( $redirect );
						exit;
					}

					break;
				}
				case 'edit-profile': {

					global $edit_profile;

					$edit_profile = FMWP()->frontend()->forms( [ 'id'   => 'fmwp-edit-profile', ] );

					$edit_profile->flush_errors();
					$edit_profile->flush_notices();

					if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'fmwp-edit-profile' ) ) {
						$edit_profile->add_error( 'global', __( 'Security issue, Please try again', 'forumwp' ) );
					}

					if ( empty( $_POST['user_id'] ) ) {
						$edit_profile->add_error( 'global', __( 'Empty User', 'forumwp' ) );
					}

					$user_id = absint( $_POST['user_id'] );

					$previous_data = get_userdata( $user_id );

					if ( empty( $_POST['user_email'] ) ) {
						$edit_profile->add_error( 'user_email', __( 'Empty Email', 'forumwp' ) );
					}
					$user_email = sanitize_email( trim( $_POST['user_email'] ) );
					if ( ! is_email( $user_email ) ) {
						$edit_profile->add_error( 'user_email', __( 'Invalid email', 'forumwp' ) );
					}
					if ( $previous_data->user_email != $user_email && email_exists( $user_email ) ) {
						$edit_profile->add_error( 'user_email', __( 'Email already exists', 'forumwp' ) );
					}

					if ( ! empty( $_POST['user_url'] ) && $filter_url = filter_var( $_POST['user_url'], FILTER_VALIDATE_URL ) === false ) {
						$edit_profile->add_error( 'user_url', __( 'Invalid user URL', 'forumwp' ) );
					}

					do_action( 'fmwp_before_submit_profile', $edit_profile );

					if ( ! $edit_profile->has_errors() ) {
						$first_name = ! empty( $_POST['first_name'] ) ? sanitize_text_field( trim( $_POST['first_name'] ) ) : '';
						$last_name = ! empty( $_POST['last_name'] ) ? sanitize_text_field( trim( $_POST['last_name'] ) ) : '';
						$display_name = $first_name . ' ' . $last_name;
						$display_name = empty( $display_name ) ? $previous_data->user_login : $display_name;

						$userdata = [
							'ID'            => $user_id,
							'user_email'    => $user_email,
							'first_name'    => $first_name,
							'last_name'     => $last_name,
							'user_url'      => $filter_url ? $filter_url : '',
							'display_name'  => $display_name,
							'description'   => sanitize_textarea_field( trim( $_POST['description'] ) ),
						];

						$userdata = apply_filters( 'fmwp_onsubmit_profile_args', $userdata, $edit_profile );

						wp_update_user( $userdata );

						do_action( 'fmwp_user_update_profile', $user_id );

						//redirect to profile page
						$profile_link = FMWP()->user()->get_profile_link( get_current_user_id(), 'edit' );

						wp_redirect( $profile_link );
						exit;
					}

					break;
				}
				case 'create-forum': {

					global $new_forum;

					$new_forum = FMWP()->frontend()->forms( [ 'id' => 'fmwp-create-forum', ] );

					$new_forum->flush_errors();
					$new_forum->flush_notices();

					if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'fmwp-create-forum' ) ) {
						$new_forum->add_error( 'global', __( 'Security issue, Please try again', 'forumwp' ) );
					}

					if ( ! FMWP()->user()->can_create_forum() ) {
						$new_forum->add_error( 'global', __( 'You do not have capability to create forums', 'forumwp' ) );
					}

					if ( empty( $_POST['fmwp-forum'] ) ) {
						$new_forum->add_error( 'global', __( 'Invalid data', 'forumwp' ) );
					}

					$request = $_POST['fmwp-forum'];

					if ( empty( $request['title'] ) ) {
						$new_forum->add_error( 'title', __( 'Name is required', 'forumwp' ) );
					}

					if ( empty( $request['content'] ) ) {
						$new_forum->add_error( 'content', __( 'Description is required', 'forumwp' ) );
					}

					if ( ! in_array( $request['visibility'], array_keys( FMWP()->common()->forum()->visibilities ) ) ) {
						$new_forum->add_error( 'visibility', __( 'Invalid visibility', 'forumwp' ) );
					}

					do_action( 'fmwp_before_submit_create_forum', $new_forum );

					if ( ! $new_forum->has_errors() ) {

						$data = [
							'title'         => sanitize_text_field( $request['title'] ),
							'content'       => wp_kses_post( $request['content'] ),
							'categories'    => sanitize_text_field( $request['categories'] ),
							'visibility'    => sanitize_key( $request['visibility'] ),
						];

						$data = apply_filters( 'fmwp_onsubmit_create_forum_args', $data, $new_forum );

						if ( ! FMWP()->options()->get( 'forum_categories' ) ) {
							unset( $data['categories'] );
						}

						FMWP()->common()->forum()->create( $data );

						wp_redirect( add_query_arg( [ 'fmwp-msg' => 'forum-created' ], FMWP()->get_current_url() ) );
						exit;
					}

					break;
				}
			}
		}

	}
}