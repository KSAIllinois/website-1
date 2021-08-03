<?php
namespace fmwp\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\admin\Actions_Listener' ) ) {


	/**
	 * Class Actions_Listener
	 *
	 * @package fmwp\admin
	 */
	class Actions_Listener {


		/**
		 * Actions_Listener constructor.
		 */
		function __construct() {
			add_action( 'admin_init', [ $this, 'save_settings' ], 10 );

			//save handlers
			add_filter( 'fmwp_change_settings_before_save', [ $this, 'save_email_templates' ] );
			add_filter( 'fmwp_change_settings_before_save', [ $this, 'multi_checkbox_formatting' ] );

			add_action( 'fmwp_settings_save', [ FMWP()->modules(), 'install_modules' ], 10 );


			add_action( 'admin_init', [ $this, 'core_pages' ], 10 );
		}


		/**
		 * Handler for settings forms
		 * when "Save Settings" button click
		 *
		 */
		function save_settings() {
			if ( ! isset( $_POST['fmwp-settings-action'] ) || 'save' !== $_POST['fmwp-settings-action'] ) {
				return;
			}

			if ( empty( $_POST['fmwp_options'] ) ) {
				return;
			}

			$nonce = ! empty( $_POST['__fmwpnonce'] ) ? $_POST['__fmwpnonce'] : '';
			if ( ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'fmwp-settings-nonce' ) ) ||
			     ! current_user_can( 'manage_options' ) ) {

				// This nonce is not valid.
				wp_die( 'Security Check', 'forumwp' );
			}

			do_action( 'fmwp_settings_before_save' );

			$settings = apply_filters( 'fmwp_change_settings_before_save', $_POST['fmwp_options'] );

			$settings = FMWP()->options()->sanitize( $settings );

			foreach ( $settings as $key => $value ) {
				FMWP()->options()->update( $key, $value );
			}

			do_action( 'fmwp_settings_save' );

			//redirect after save settings
			$arg = [
				'page' => 'forumwp-settings',
			];
			if ( ! empty( $_GET['tab'] ) ) {
				$arg['tab'] = sanitize_key( $_GET['tab'] );
			}
			if ( ! empty( $_GET['section'] ) ) {
				$arg['section'] = sanitize_key( $_GET['section'] );
			}

			wp_redirect( add_query_arg( $arg, admin_url( 'admin.php' ) ) );
			exit;
		}


		/**
		 * @param $settings
		 *
		 * @return mixed
		 */
		function save_email_templates( $settings ) {
			if ( empty( $settings['fmwp_email_template'] ) ) {
				return $settings;
			}

			$template = $settings['fmwp_email_template'];
			$content = stripslashes( $settings[ $template ] );

			$theme_template_path = FMWP()->common()->mail()->get_template_file( 'theme', $template );

			if ( ! file_exists( $theme_template_path ) ) {
				FMWP()->common()->mail()->copy_template( $template );
			}

			if ( file_exists( $theme_template_path ) ) {
				$fp = fopen( $theme_template_path, "w" );
				$result = fputs( $fp, $content );
				fclose( $fp );

				if ( $result !== false ) {
					unset( $settings['fmwp_email_template'] );
					unset( $settings[ $template ] );
				}
			}

			return $settings;
		}


		/**
		 * @param $settings
		 *
		 * @return mixed
		 */
		function multi_checkbox_formatting( $settings ) {
			$current_tab = empty( $_GET['tab'] ) ? '' : urldecode( sanitize_key( $_GET['tab'] ) );
			$current_subtab = empty( $_GET['section'] ) ? '' : urldecode( sanitize_key( $_GET['section'] ) );

			$fields = FMWP()->admin()->settings()->get_settings( $current_tab, $current_subtab, true );

			if ( ! $fields ) {
				return $settings;
			}

			foreach ( $settings as $key => &$value ) {

				if ( ! isset( $fields[ $key ]['type'] ) || $fields[ $key ]['type'] !== 'multi_checkbox' ) {
					continue;
				}

				if ( empty( $value ) ) {
					continue;
				}

				$value = array_keys( $value );
			}

			return $settings;
		}


		/**
		 * Core pages installation process
		 */
		function core_pages() {
			if ( ! empty( $_REQUEST['fmwp_adm_action'] ) && current_user_can( 'manage_options' ) ) {
				switch ( $_REQUEST['fmwp_adm_action'] ) {
					case 'install_core_pages': {
						FMWP()->install()->core_pages();

						if ( FMWP()->options()->are_pages_installed() ) {
							FMWP()->admin()->notices()->dismiss( 'wrong_pages' );
						}

						$url = add_query_arg( [ 'page' => 'forumwp-settings' ], admin_url( 'admin.php' ) );
						exit( wp_redirect( $url ) );

						break;
					}
				}
			}

			if ( ! empty( $_REQUEST['fmwp_adm_action'] ) ) {
				switch ( $_REQUEST['fmwp_adm_action'] ) {
					case 'clear_reports': {
						if ( ! empty( $_GET['post_id'] ) ) {
							$post_id = absint( $_GET['post_id'] );
							if ( wp_verify_nonce( $_GET['_wpnonce'], 'fmwp_clear_reports' . $post_id ) ) {
								FMWP()->reports()->clear( $post_id );
								wp_redirect( remove_query_arg( [ '_wpnonce', 'post_id', 'fmwp_adm_action' ] ) );
								exit;
							}
						}

						break;
					}
				}
			}
		}
	}
}