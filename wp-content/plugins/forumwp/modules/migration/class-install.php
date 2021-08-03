<?php
namespace fmwpm\migration;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwpm\migration\Install' ) ) {


	/**
	 * Class Install
	 *
	 * @package fmwpm\migration
	 */
	class Install {


		/**
		 * @var array
		 */
		var $settings_defaults;


		/**
		 * Setup constructor.
		 */
		function __construct() {
			//settings defaults
			$this->settings_defaults = [];
		}


		/**
		 *
		 */
		function start() {
			FMWP()->options()->set_defaults( $this->settings_defaults );
		}
	}
}