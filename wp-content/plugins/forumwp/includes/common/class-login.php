<?php namespace fmwp\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\common\Login' ) ) {


	/**
	 * Class Login
	 *
	 * @package fmwp\common
	 */
	class Login {


		/**
		 * Login constructor.
		 *
		 * @since 2.0
		 */
		function __construct() {
			//filter login/logout URLs
			add_action( 'wp_logout', [ &$this, 'logout' ] );
			add_filter( 'logout_url', [ &$this, 'logout_url' ], 10, 2 );
			add_action( 'wp_login_failed', [ &$this, 'login_failed' ] );

			add_filter( 'authenticate', [ &$this, 'verify_username_password' ], 1, 3 );
			add_action( 'template_redirect', [ &$this, 'custom_logout_handler' ], 1 );
		}


		/**
		 * On logout action
		 */
		public function logout() {
			$login_url = FMWP()->common()->get_preset_page_link( 'login' );
			$logout_redirect = FMWP()->options()->get( 'logout_redirect' );

			// if empty 'logout_redirect' option then redirect to login page
			$baseurl = ! empty( $logout_redirect ) ? $logout_redirect : $login_url;

			$redirect_url = add_query_arg( [ 'logout' => 'success' ], $baseurl );

			$redirect_url = apply_filters( 'fmwp_logout_redirect_url', $redirect_url, $baseurl );

			exit( wp_redirect( $redirect_url ) );
		}


		/**
		 * Change logout URL
		 *
		 * @param $logout_url
		 * @param $redirect
		 *
		 * @return string
		 */
		public function logout_url( $logout_url, $redirect ) {

			$args = [ 'action' => 'logout' ];
			if ( ! empty( $redirect ) ) {
				$args['redirect_to'] = urlencode( $redirect );
			}

			$logout_url = add_query_arg( $args, FMWP()->common()->get_preset_page_link( 'login' ) );
			$logout_url = wp_nonce_url( $logout_url, 'log-out' );

			return $logout_url;
		}


		/**
		 * Redirects visitor to the login page with login
		 * failed status.
		 *
		 * @return void
		 */
		public function login_failed() {
			if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
				$postid = url_to_postid( $_SERVER['HTTP_REFERER'] );

				if ( ! empty( $postid ) && $postid == FMWP()->common()->get_preset_page_id( 'login' ) ) {
					$logout_link = add_query_arg( [ 'login' => 'failed' ], FMWP()->common()->get_preset_page_link( 'login' ) );
					exit( wp_redirect( $logout_link ) );
				}
			}
		}


		/**
		 * Verifies username and password. Redirects visitor
		 * to the login page with login empty status if
		 * eather username or password is empty.
		 *
		 * @param mixed $user
		 * @param string $username
		 * @param string $password
		 *
		 * @return \WP_Error
		 */
		public function verify_username_password( $user, $username, $password ) {
			if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
				$postid = url_to_postid( $_SERVER['HTTP_REFERER'] );

				if ( ! empty( $postid ) && $postid == FMWP()->common()->get_preset_page_id( 'login' ) ) {
					if ( $user === null && ( $username == "" || $password == "" ) ) {
						return new \WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>: Invalid username, email address or incorrect password.' ) );
					}
				}
			}

			return $user;
		}


		/**
		 *
		 */
		function custom_logout_handler() {
			if ( FMWP()->is_core_page( 'login' ) ) {
				if ( is_user_logged_in() && isset( $_GET['action'] ) && 'logout' == $_GET['action'] ) {
					check_admin_referer( 'log-out' );

					$user = wp_get_current_user();

					wp_logout();

					if ( ! empty( $_REQUEST['redirect_to'] ) ) {
						$redirect_to = $requested_redirect_to = esc_url_raw( $_REQUEST['redirect_to'] );
					} else {
						$redirect_to           = 'wp-login.php?loggedout=true';
						$requested_redirect_to = '';
					}

					/**
					 * Filters the log out redirect URL.
					 *
					 * @since 4.2.0
					 *
					 * @param string  $redirect_to           The redirect destination URL.
					 * @param string  $requested_redirect_to The requested redirect destination URL passed as a parameter.
					 * @param \WP_User $user                  The WP_User object for the user that's logging out.
					 */
					$redirect_to = apply_filters( 'logout_redirect', $redirect_to, $requested_redirect_to, $user );
					wp_safe_redirect( $redirect_to );
					exit();
				} elseif ( ! is_user_logged_in() && isset( $_GET['action'] ) && 'logout' == $_GET['action'] ) {
					wp_safe_redirect( FMWP()->common()->get_preset_page_link( 'login' ) );
					exit();
				}
			}
		}

	}
}