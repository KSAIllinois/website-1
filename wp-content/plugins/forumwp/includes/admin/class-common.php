<?php
namespace fmwp\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\admin\Common' ) ) {


	/**
	 * Class Common
	 *
	 * @package fmwp\admin
	 */
	class Common {


		/**
		 * @var string
		 */
		var $templates_path = '';


		/**
		 * Common constructor.
		 */
		function __construct() {
			$this->templates_path = fmwp_path . 'includes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
		}


		/**
		 * Create classes' instances where __construct isn't empty for hooks init
		 *
		 * @used-by \FMWP::includes()
		 */
		function includes() {
			$this->upgrade();

			$this->settings();

			$this->menu();
			$this->metabox();
			$this->enqueue();
			$this->actions_listener();
			$this->notices();
			$this->columns();
		}


		/**
		 * Check if ForumWP screen is loaded
		 *
		 * @return bool
		 */
		function is_own_screen() {
			global $current_screen;
			$screen_id = $current_screen->id;

			if ( strstr( $screen_id, 'forumwp' ) || strstr( $screen_id, 'fmwp_' ) ) {
				return true;
			}

			if ( $this->is_own_post_type() ) {
				return true;
			}

			return false;
		}


		/**
		 * Check if current page load ForumWP CPT
		 *
		 * @return bool
		 */
		function is_own_post_type() {
			$cpt = array_keys( FMWP()->get_cpt() );

			if ( isset( $_REQUEST['post_type'] ) ) {
				$post_type = $_REQUEST['post_type'];
				if ( in_array( $post_type, $cpt ) ) {
					return true;
				}
			} elseif ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ) {
				$post_type = get_post_type();
				if ( in_array( $post_type, $cpt ) ) {
					return true;
				}
			}

			return false;
		}


		/**
		 * @since 1.0
		 *
		 * @return Settings
		 */
		function settings() {
			if ( empty( FMWP()->classes['fmwp\admin\settings'] ) ) {
				FMWP()->classes['fmwp\admin\settings'] = new Settings();
			}
			return FMWP()->classes['fmwp\admin\settings'];
		}


		/**
		 * @since 2.0
		 *
		 * @return Actions_Listener
		 */
		function actions_listener() {
			if ( empty( FMWP()->classes['fmwp\admin\actions_listener'] ) ) {
				FMWP()->classes['fmwp\admin\actions_listener'] = new Actions_Listener();
			}
			return FMWP()->classes['fmwp\admin\actions_listener'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Enqueue
		 */
		function enqueue() {
			if ( empty( FMWP()->classes['fmwp\admin\enqueue'] ) ) {
				FMWP()->classes['fmwp\admin\enqueue'] = new Enqueue();
			}
			return FMWP()->classes['fmwp\admin\enqueue'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Menu
		 */
		function menu() {
			if ( empty( FMWP()->classes['fmwp\admin\menu'] ) ) {
				FMWP()->classes['fmwp\admin\menu'] = new Menu();
			}
			return FMWP()->classes['fmwp\admin\menu'];
		}



		/**
		 * @since 1.0
		 *
		 * @return Metabox()
		 */
		function metabox() {
			if ( empty( FMWP()->classes['fmwp\admin\metabox'] ) ) {
				FMWP()->classes['fmwp\admin\metabox'] = new Metabox();
			}
			return FMWP()->classes['fmwp\admin\metabox'];
		}


		/**
		 * @since 1.0
		 *
		 * @param array $data
		 *
		 * @return bool|Forms
		 */
		function forms( $data ) {
			if ( ! array_key_exists( 'class', $data ) ) {
				return false;
			}

			if ( empty( FMWP()->classes[ 'fmwp\admin\forms' . $data['class'] ] ) ) {
				FMWP()->classes[ 'fmwp\admin\forms' . $data['class'] ] = new Forms( $data );
			}
			return FMWP()->classes[ 'fmwp\admin\forms' . $data['class'] ];
		}


		/**
		 * @since 1.0
		 *
		 * @return Notices()
		 */
		function notices() {
			if ( empty( FMWP()->classes['fmwp\admin\notices'] ) ) {
				FMWP()->classes['fmwp\admin\notices'] = new Notices();
			}
			return FMWP()->classes['fmwp\admin\notices'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Columns()
		 */
		function columns() {
			if ( empty( FMWP()->classes['fmwp\admin\columns'] ) ) {
				FMWP()->classes['fmwp\admin\columns'] = new Columns();
			}

			return FMWP()->classes['fmwp\admin\columns'];
		}


		/**
		 * @since 2.0
		 *
		 * @return Upgrade()
		 */
		function upgrade() {
			if ( empty( FMWP()->classes['fmwp\admin\upgrade'] ) ) {
				FMWP()->classes['fmwp\admin\upgrade'] = new Upgrade();
			}

			return FMWP()->classes['fmwp\admin\upgrade'];
		}
	}
}