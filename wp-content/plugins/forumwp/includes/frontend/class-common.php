<?php namespace fmwp\frontend;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\frontend\Common' ) ) {


	/**
	 * Class Common
	 *
	 * @package fmwp\frontend
	 */
	class Common {


		/**
		 * Common constructor.
		 */
		function __construct() {
			add_action( 'fmwp_before_forums_list', [ &$this, 'breadcrumbs_ui' ] );
			add_action( 'fmwp_before_topics_list', [ &$this, 'breadcrumbs_ui' ] );
			add_action( 'fmwp_before_individual_forum', [ &$this, 'breadcrumbs_ui' ] );
			add_action( 'fmwp_before_individual_topic', [ &$this, 'breadcrumbs_ui' ] );
		}


		/**
		 * Create classes' instances where __construct isn't empty for hooks init
		 *
		 * @used-by \FMWP::includes()
		 */
		function includes() {
			$this->actions_listener();
			$this->enqueue();
			$this->shortcodes();
		}


		/**
		 * @since 1.0
		 *
		 * @return Shortcodes
		 */
		function shortcodes() {
			if ( empty( FMWP()->classes['fmwp\frontend\shortcodes'] ) ) {
				FMWP()->classes['fmwp\frontend\shortcodes'] = new Shortcodes();
			}

			return FMWP()->classes['fmwp\frontend\shortcodes'];
		}


		/**
		 * @since 1.0
		 *
		 * @return Enqueue
		 */
		function enqueue() {
			if ( empty( FMWP()->classes['fmwp\frontend\enqueue'] ) ) {
				FMWP()->classes['fmwp\frontend\enqueue'] = new Enqueue();
			}

			return FMWP()->classes['fmwp\frontend\enqueue'];
		}


		/**
		 * @since 1.0
		 *
		 * @param array $data
		 *
		 * @return bool|Forms
		 */
		function forms( $data ) {
			if ( ! array_key_exists( 'id', $data ) ) {
				return false;
			}

			if ( empty( FMWP()->classes[ 'fmwp\frontend\forms' . $data['id'] ] ) ) {
				FMWP()->classes[ 'fmwp\frontend\forms' . $data['id'] ] = new Forms( $data );
			}

			return FMWP()->classes[ 'fmwp\frontend\forms' . $data['id'] ];
		}


		/**
		 * @since 1.0
		 *
		 * @return Profile
		 */
		function profile() {
			if ( empty( FMWP()->classes['fmwp\frontend\profile'] ) ) {
				FMWP()->classes['fmwp\frontend\profile'] = new Profile();
			}

			return FMWP()->classes['fmwp\frontend\profile'];
		}


		/**
		 * @since 2.0
		 *
		 * @return Actions_Listener
		 */
		function actions_listener() {
			if ( empty( FMWP()->classes['fmwp\frontend\actions_listener'] ) ) {
				FMWP()->classes['fmwp\frontend\actions_listener'] = new Actions_Listener();
			}
			return FMWP()->classes['fmwp\frontend\actions_listener'];
		}


		/**
		 *
		 */
		function breadcrumbs_ui() {
			if ( ! FMWP()->options()->get( 'breadcrumb_enabled' ) ) {
				return;
			}

			$arr = FMWP()->get_breadcrumbs_data();
			echo '<div class="fmwp-breadcrumbs">';
			echo '<ul>';
			for ( $i = 0; $i < count( $arr ); $i++ ) {

				$url = $arr[ $i ]['url'];
				$css_class = 'fmwp-breadcrumb-link';
				if ( $i == count( $arr ) - 1 ) {
					$url = '#';
					$css_class = 'fmwp-breadcrumb-link last-item';
				}
				echo '<li class="fmwp-breadcrumb-item fmwp-breadcrumbs-'.$i.'"><a class="'.$css_class.'" href="'.$url.'">'.$arr[$i]['title'].'</a></li>';
			}
			echo '</ul>';
			echo '<div class="clear"></div>';
			echo '</div>';

		}
	}
}