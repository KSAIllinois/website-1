<?php
namespace fmwp\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\common\Options' ) ) {


	/**
	 * Class Options
	 *
	 * @package fmwp\common
	 */
	class Options {


		/**
		 * @var array
		 */
		var $options = [];


		/**
		 * @var array
		 */
		var $pages = [];


		/**
		 * @var array
		 */
		var $variables = [];


		/**
		 * Options constructor.
		 */
		function __construct() {
			$this->init();
			$this->init_pages();
		}


		/**
		 * Set variables
		 */
		function init() {
			$this->options = get_option( 'fmwp_options', [] );
		}


		/**
		 * Set variables
		 */
		function init_pages() {
			$core_pages = array_keys( FMWP()->config()->get( 'core_pages' ) );
			if ( ! empty( $core_pages ) ) {
				foreach ( $core_pages as $page_key ) {
					$this->pages[ $page_key ] = $this->get( $page_key . '_page' );
				}
			}
		}


		/**
		 * Get FMWP option value
		 *
		 * @param string $option_id
		 * @return mixed
		 */
		function get( $option_id ) {
			if ( isset( $this->options[ $option_id ] ) ) {
				return apply_filters( "fmwp_get_option_filter__{$option_id}", $this->options[ $option_id ] );
			}

			switch ( $option_id ) {
				case 'site_name':
					return get_bloginfo( 'name' );
					break;
				default:
					return '';
					break;
			}
		}


		/**
		 * Returns options key
		 *
		 * @param string $option
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		function get_key( $option ) {
			return apply_filters( 'fmwp_options_key', "fmwp_{$option}", $option );
		}


		/**
		 * Update FMWP option value
		 *
		 * @param string $option_id
		 * @param mixed $value
		 */
		function update( $option_id, $value ) {
			$this->options[ $option_id ] = $value;
			update_option( 'fmwp_options', $this->options );
		}


		/**
		 * Sanitize option value before insert to DB
		 *
		 * @param array $settings
		 * @return array
		 */
		function sanitize( $settings ) {
			foreach ( $settings as $key => &$value ) {
				switch ( $key ) {
					default:
						$value = apply_filters( 'fmwp_sanitize_option_value', sanitize_text_field( $value ), $value, $key );
						break;
					case 'default_role':
					case 'default_forums_order':
					case 'default_topics_order':
					case 'reply_delete':
						$value = sanitize_key( $value );
						break;
					case 'login_redirect':
					case 'register_redirect':
						$value = esc_url_raw( $value );
						break;
					case 'forum_categories':
					case 'topic_tags':
					case 'raw_html_enabled':
					case 'breadcrumb_enabled':
					case 'show_forum':
					case 'ajax_increment_views':
					case 'reply_user_role':
					case 'disable-fa-styles':
					case 'uninstall-delete-settings':
						$value = (bool) $value;
						break;
					case 'default_forum':
					case 'topic_throttle':
					case 'reply_throttle':
						$value = absint( $value );
						break;
					case 'default_forums_template':
					case 'default_topics_template':
					case 'mail_from':
						$value = sanitize_text_field( $value );
						break;
					case 'admin_email':
					case 'mail_from_addr':
						$value = sanitize_email( $value );
						break;
				}
			}

			return $settings;
		}


		/**
		 * Delete FMWP option
		 *
		 * @param $option_id
		 */
		function remove( $option_id ) {
			if ( ! empty( $this->options[ $option_id ] ) ) {
				unset( $this->options[ $option_id ] );
			}

			update_option( 'fmwp_options', $this->options );
		}


		/**
		 * Get FMWP option default value
		 *
		 * @param $option_id
		 * @return bool
		 */
		function get_default( $option_id ) {
			$settings_defaults = FMWP()->config()->get( 'defaults' );
			if ( ! isset( $settings_defaults[ $option_id ] ) ) {
				return false;
			}

			return $settings_defaults[ $option_id ];
		}


		/**
		 * @param string $key
		 *
		 * @return mixed
		 */
		function get_variable( $key ) {
			$variables = FMWP()->config()->get( 'variables' );
			$value = isset( $variables[ $key ] ) ? $variables[ $key ] : null;

			return apply_filters( 'fmwp_get_variable', $value, $key );
		}


		/**
		 * Set default FMWP settings
		 *
		 * @param array $defaults
		 */
		function set_defaults( $defaults ) {
			$need_update = false;
			$options = get_option( 'fmwp_options', [] );

			if ( ! empty( $defaults ) ) {
				foreach ( $defaults as $key => $value ) {
					//set new options to default
					if ( ! isset( $options[ $key ] ) ) {
						$options[ $key ] = $value;
						$need_update = true;
					}
				}
			}

			if ( $need_update ) {
				update_option( 'fmwp_options', $options );
			}
		}



		/**
		 * Are FMWP pages installed
		 *
		 * @since 2.0
		 *
		 * @return bool
		 */
		function are_pages_installed() {
			$installed = true;

			if ( empty( $this->pages ) ) {
				$installed = false;
			} else {
				foreach ( $this->pages as $page_id ) {
					$page = get_post( $page_id );

					if ( ! isset( $page->ID ) ) {
						$installed = false;
						break;
					}
				}
			}

			return $installed;
		}

	}
}