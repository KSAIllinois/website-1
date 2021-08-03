<?php namespace fmwp\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\common\Enqueue' ) ) {


	/**
	 * Class Enqueue
	 *
	 * @package fmwp\common
	 */
	class Enqueue {


		/**
		 * @var string scripts' Standard or Minified versions
		 *
		 * @since 2.0
		 */
		var $scripts_prefix;


		/**
		 * @var array JS URLs
		 *
		 * @since 2.0
		 */
		var $js_url = [];


		/**
		 * @var array CSS URLs
		 *
		 * @since 2.0
		 */
		var $css_url = [];


		/**
		 * @var array assets URLs
		 *
		 * @since 2.0
		 */
		var $url = [];


		/**
		 * @var string FontAwesome version
		 *
		 * @since 2.0
		 */
		var $fa_version = '5.13.0';


		/**
		 * Enqueue constructor.
		 *
		 * @since 2.0
		 */
		function __construct() {
			add_action( 'forumwp_init', [ $this, 'init_variables' ] );

			add_action( 'admin_enqueue_scripts', [ &$this, 'common_libs' ], 9 );
			add_action( 'wp_enqueue_scripts', [ &$this, 'common_libs' ], 9 );

			add_filter( 'fmwp_frontend_common_styles_deps', [ &$this, 'extends_frontend_styles' ], 10, 1 );
			add_filter( 'fmwp_admin_common_styles_deps', [ &$this, 'extends_admin_styles' ], 10, 1 );
		}


		/**
		 * Init variables for enqueue scripts
		 *
		 * @since 2.0
		 */
		function init_variables() {
			$this->scripts_prefix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			$this->url['common'] = fmwp_url . 'assets/common/';
			$this->js_url['common'] = fmwp_url . 'assets/common/js/';
			$this->css_url['common'] = fmwp_url . 'assets/common/css/';
		}


		/**
		 * Register common JS/CSS libraries
		 *
		 * @since 2.0
		 */
		function common_libs() {
			wp_register_script( 'fmwp-helptip', $this->js_url['common'] . 'helptip' . $this->scripts_prefix . '.js', [ 'jquery', 'jquery-ui-tooltip' ], fmwp_version, true );

			wp_register_style( 'fmwp-jquery-ui', $this->url['common'] . 'libs/jquery-ui/jquery-ui' . $this->scripts_prefix . '.css', [], '1.12.1' );

			wp_register_style( 'fmwp-helptip', $this->css_url['common'] . 'helptip' . $this->scripts_prefix . '.css', [ 'dashicons', 'fmwp-jquery-ui' ], fmwp_version );

			if ( ! FMWP()->options()->get( 'disable-fa-styles' ) ) {
				wp_register_style( 'fmwp-far', $this->url['common'] . 'libs/fontawesome/css/regular' . $this->scripts_prefix . '.css', [], $this->fa_version );
				wp_register_style( 'fmwp-fas', $this->url['common'] . 'libs/fontawesome/css/solid' . $this->scripts_prefix . '.css', [], $this->fa_version );
				wp_register_style( 'fmwp-fab', $this->url['common'] . 'libs/fontawesome/css/brands' . $this->scripts_prefix . '.css', [], $this->fa_version );
				wp_register_style( 'fmwp-fa', $this->url['common'] . 'libs/fontawesome/css/v4-shims' . $this->scripts_prefix . '.css', [], $this->fa_version );
				wp_register_style( 'fmwp-font-awesome', $this->url['common'] . 'libs/fontawesome/css/fontawesome' . $this->scripts_prefix . '.css', [ 'fmwp-fa', 'fmwp-far', 'fmwp-fas', 'fmwp-fab' ], $this->fa_version );
			}
		}


		/**
		 * Add FontAwesome styles to dependencies if it's not disabled frontend
		 *
		 * @param array $styles
		 *
		 * @return array
		 *
		 * @since 2.0
		 */
		function extends_frontend_styles( $styles ) {
			if ( FMWP()->options()->get( 'disable-fa-styles' ) ) {
				return $styles;
			}

			$styles[] = 'fmwp-font-awesome';
			return $styles;
		}


		/**
		 * Add FontAwesome styles to dependencies if it's not disabled wp-admin
		 *
		 * @param array $styles
		 *
		 * @return array
		 *
		 * @since 2.0
		 */
		function extends_admin_styles( $styles ) {
			if ( FMWP()->options()->get( 'disable-fa-styles' ) ) {
				return $styles;
			}

			$styles[] = 'fmwp-font-awesome';
			return $styles;
		}

	}
}