<?php namespace fmwp\ajax;


use fmwp\common\Forum_Category;

if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\ajax\Common' ) ) {


	/**
	 * Class Common
	 *
	 * @package fmwp\ajax
	 */
	class Common {


		/**
		 * Common constructor.
		 */
		function __construct() {

			//wp-admin
			add_action( 'wp_ajax_fmwp_get_icons', [ $this->forms(), 'get_icons' ] );
			add_action( 'wp_ajax_fmwp_dismiss_notice', [ $this->notices(), 'dismiss_notice' ] );


			//front-end

			// default user profile actions
			add_action( 'wp_ajax_fmwp_profile_get_content', [ $this->profile(), 'get_tab_content' ] );
			add_action( 'wp_ajax_nopriv_fmwp_profile_get_content', [ $this->profile(), 'get_tab_content' ] );
			add_action( 'wp_ajax_fmwp_profile_topics', [ $this->profile(), 'get_profile_topics' ] );
			add_action( 'wp_ajax_nopriv_fmwp_profile_topics', [ $this->profile(), 'get_profile_topics' ] );
			add_action( 'wp_ajax_fmwp_profile_replies', [ $this->profile(), 'get_profile_replies' ] );
			add_action( 'wp_ajax_nopriv_fmwp_profile_replies', [ $this->profile(), 'get_profile_replies' ] );

			// forums list actions
			add_action( 'wp_ajax_fmwp_get_forums', [ $this->forum(), 'get_forums' ] );
			add_action( 'wp_ajax_nopriv_fmwp_get_forums', [ $this->forum(), 'get_forums' ] );
			add_action( 'wp_ajax_fmwp_lock_forum', [ $this->forum(), 'lock' ] );
			add_action( 'wp_ajax_fmwp_unlock_forum', [ $this->forum(), 'unlock' ] );
			add_action( 'wp_ajax_fmwp_trash_forum', [ $this->forum(), 'trash' ] );


			// topics list actions
			add_action( 'wp_ajax_fmwp_get_topics', [ $this->topic(), 'get_topics' ] );
			add_action( 'wp_ajax_nopriv_fmwp_get_topics', [ $this->topic(), 'get_topics' ] );

			add_action( 'wp_ajax_fmwp_create_topic', [ $this->topic(), 'create' ] );
			add_action( 'wp_ajax_fmwp_edit_topic', [ $this->topic(), 'edit' ] );

			add_action( 'wp_ajax_fmwp_get_topic', [ $this->topic(), 'get_topic' ] );
			add_action( 'wp_ajax_fmwp_pin_topic', [ $this->topic(), 'pin' ] );
			add_action( 'wp_ajax_fmwp_unpin_topic', [ $this->topic(), 'unpin' ] );
			add_action( 'wp_ajax_fmwp_lock_topic', [ $this->topic(), 'lock' ] );
			add_action( 'wp_ajax_fmwp_unlock_topic', [ $this->topic(), 'unlock' ] );
			add_action( 'wp_ajax_fmwp_trash_topic', [ $this->topic(), 'trash' ] );
			add_action( 'wp_ajax_fmwp_restore_topic', [ $this->topic(), 'restore' ] );
			add_action( 'wp_ajax_fmwp_delete_topic', [ $this->topic(), 'delete' ] );

			add_action( 'wp_ajax_fmwp_mark_spam_topic', [ $this->topic(), 'spam' ] );
			add_action( 'wp_ajax_fmwp_restore_spam_topic', [ $this->topic(), 'restore_spam' ] );
			add_action( 'wp_ajax_fmwp_report_topic', [ $this->topic(), 'report' ] );
			add_action( 'wp_ajax_fmwp_unreport_topic', [ $this->topic(), 'unreport' ] );
			add_action( 'wp_ajax_fmwp_clear_reports_topic', [ $this->topic(), 'clear_reports' ] );
			add_action( 'wp_ajax_fmwp_topic_build_preview', [ $this->topic(), 'build_preview' ] );

			### Function: Increment Topic Views
			if ( defined( 'WP_CACHE' ) && WP_CACHE && FMWP()->options()->get( 'ajax_increment_views' ) ) {
				add_action( 'wp_ajax_fmwp_topic_views', [ $this->topic(), 'increment_views' ] );
				add_action( 'wp_ajax_nopriv_fmwp_topic_views', [ $this->topic(), 'increment_views' ] );
			}

			// replies AJAX actions
			add_action( 'wp_ajax_fmwp_get_replies', [ $this->reply(), 'get_replies' ] );
			add_action( 'wp_ajax_nopriv_fmwp_get_replies', [ $this->reply(), 'get_replies' ] );
			add_action( 'wp_ajax_fmwp_get_child_replies', [ $this->reply(), 'get_child_replies' ] );
			add_action( 'wp_ajax_nopriv_fmwp_get_child_replies', [ $this->reply(), 'get_child_replies' ] );
			add_action( 'wp_ajax_fmwp_create_reply', [ $this->reply(), 'create' ] );
			add_action( 'wp_ajax_fmwp_get_reply', [ $this->reply(), 'get_reply' ] );
			add_action( 'wp_ajax_fmwp_edit_reply', [ $this->reply(), 'edit' ] );
			add_action( 'wp_ajax_fmwp_trash_reply', [ $this->reply(), 'trash' ] );
			add_action( 'wp_ajax_fmwp_restore_reply', [ $this->reply(), 'restore' ] );
			add_action( 'wp_ajax_fmwp_delete_reply', [ $this->reply(), 'delete' ] );
			add_action( 'wp_ajax_fmwp_mark_spam_reply', [ $this->reply(), 'spam' ] );
			add_action( 'wp_ajax_fmwp_restore_spam_reply', [ $this->reply(), 'restore_spam' ] );
			add_action( 'wp_ajax_fmwp_report_reply', [ $this->reply(), 'report' ] );
			add_action( 'wp_ajax_fmwp_unreport_reply', [ $this->reply(), 'unreport' ] );
			add_action( 'wp_ajax_fmwp_clear_reports_reply', [ $this->reply(), 'clear_reports' ] );
			add_action( 'wp_ajax_fmwp_reply_build_preview', [ $this->reply(), 'build_preview' ] );

			// user suggestions
			add_action( 'wp_ajax_fmwp_get_user_suggestions', [ $this->user(), 'get_suggestions' ] );

			// forum categories AJAX actions
			if ( FMWP()->options()->get( 'forum_categories' ) ) {
				add_action( 'wp_ajax_fmwp_get_forum_categories', [ $this->forum_category(), 'get_list' ] );
				add_action( 'wp_ajax_nopriv_fmwp_get_forum_categories', [ $this->forum_category(), 'get_list' ] );
			}
		}


		/**
		 * Create classes' instances where __construct isn't empty for hooks init
		 *
		 * @used-by \FMWP::includes()
		 */
		function includes() {
			FMWP()->admin()->metabox();
			FMWP()->admin()->columns();

			FMWP()->admin()->upgrade()->init_packages_ajax_handlers();
		}


		/**
		 * Check nonce
		 *
		 * @param bool|string $action
		 *
		 * @since 1.0
		 */
		function check_nonce( $action = false ) {
			$nonce = isset( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : '';
			$action = empty( $action ) ? 'fmwp-common-nonce' : $action;

			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_send_json_error( __( 'Wrong AJAX Nonce', 'forumwp' ) );
			}
		}


		/**
		 * @return Notices()
		 *
		 * @since 1.0
		 */
		function notices() {
			if ( empty( FMWP()->classes['fmwp\ajax\notices'] ) ) {
				FMWP()->classes['fmwp\ajax\notices'] = new Notices();
			}
			return FMWP()->classes['fmwp\ajax\notices'];
		}


		/**
		 * @return Forms()
		 *
		 * @since 1.0
		 */
		function forms() {
			if ( empty( FMWP()->classes['fmwp\ajax\forms'] ) ) {
				FMWP()->classes['fmwp\ajax\forms'] = new Forms();
			}
			return FMWP()->classes['fmwp\ajax\forms'];
		}


		/**
		 * @return Reply()
		 *
		 * @since 1.0
		 */
		function reply() {
			if ( empty( FMWP()->classes['fmwp\ajax\reply'] ) ) {
				FMWP()->classes['fmwp\ajax\reply'] = new Reply();
			}
			return FMWP()->classes['fmwp\ajax\reply'];
		}


		/**
		 * @return Topic()
		 *
		 * @since 1.0
		 */
		function topic() {
			if ( empty( FMWP()->classes['fmwp\ajax\topic'] ) ) {
				FMWP()->classes['fmwp\ajax\topic'] = new Topic();
			}
			return FMWP()->classes['fmwp\ajax\topic'];
		}


		/**
		 * @return Forum()
		 *
		 * @since 1.0
		 */
		function forum() {
			if ( empty( FMWP()->classes['fmwp\ajax\forum'] ) ) {
				FMWP()->classes['fmwp\ajax\forum'] = new Forum();
			}
			return FMWP()->classes['fmwp\ajax\forum'];
		}


		/**
		 * @return Profile()
		 *
		 * @since 1.0
		 */
		function profile() {
			if ( empty( FMWP()->classes['fmwp\ajax\profile'] ) ) {
				FMWP()->classes['fmwp\ajax\profile'] = new Profile();
			}
			return FMWP()->classes['fmwp\ajax\profile'];
		}


		/**
		 * @return User()
		 *
		 * @since 1.0
		 */
		function user() {
			if ( empty( FMWP()->classes['fmwp\ajax\user'] ) ) {
				FMWP()->classes['fmwp\ajax\user'] = new User();
			}
			return FMWP()->classes['fmwp\ajax\user'];
		}


		/**
		 * @return Forum_Category()
		 *
		 * @since 1.0
		 */
		function forum_category() {
			if ( empty( FMWP()->classes['fmwp\ajax\forum_category'] ) ) {
				FMWP()->classes['fmwp\ajax\forum_category'] = new Forum_Category();
			}
			return FMWP()->classes['fmwp\ajax\forum_category'];
		}

	}
}