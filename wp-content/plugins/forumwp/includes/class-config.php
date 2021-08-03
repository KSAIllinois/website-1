<?php
namespace fmwp;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\Config' ) ) {


	/**
	 * Class Config
	 *
	 * @package fmwp
	 */
	class Config {


		/**
		 * @var array
		 */
		var $defaults;


		/**
		 * @var
		 */
		var $custom_roles;


		/**
		 * @var
		 */
		var $all_caps;


		/**
		 * @var
		 */
		var $capabilities_map;


		/**
		 * @var
		 */
		var $core_pages;


		/**
		 * @var
		 */
		var $variables;


		/**
		 * @var
		 */
		var $email_notifications;


		/**
		 * Config constructor.
		 */
		function __construct() {
		}


		/**
		 * @param $key
		 *
		 * @return mixed
		 */
		function get( $key ) {
			call_user_func( [ &$this, 'init_' . $key ] );
			return $this->$key;
		}


		/**
		 *
		 */
		function init_defaults() {
			$this->defaults = apply_filters( 'fmwp_settings_defaults', [
				'default_role'                  => 'fmwp_participant',
				'login_redirect'                => '',
				'register_redirect'             => '',
				'logout_redirect'               => '',
				'forum_categories'              => true,
				'default_forum'                 => '',
				'default_forums_order'          => 'date_desc',
				'default_forums_template'       => '',
				'topic_tags'                    => true,
				'raw_html_enabled'              => false,
				'breadcrumb_enabled'            => false,
				'topic_throttle'                => 30,
				'show_forum'                    => true,
				'default_topics_order'          => 'date_desc',
				'default_topics_template'       => '',
				'ajax_increment_views'          => false,
				'reply_throttle'                => 10,
				'reply_delete'                  => 'sub_delete',
				'reply_user_role'               => false,
				'forum_slug'                    => 'forum',
				'topic_slug'                    => 'topic',
				'topic_tag_slug'                => 'topic-tag',
				'forum_category_slug'           => 'forum-category',
				'admin_email'                   => get_bloginfo( 'admin_email' ),
				'mail_from'                     => get_bloginfo( 'name' ),
				'mail_from_addr'                => get_bloginfo( 'admin_email' ),
				'mention_on'                    => true,
				'mention_sub'                   => '{author_name} has mentioned you in {topic_title}',

				'disable-fa-styles'             => false,
				'uninstall-delete-settings'     => false,
			] );
		}


		/**
		 *
		 */
		function init_custom_roles() {
			$this->custom_roles = apply_filters( 'fmwp_custom_roles_list', [
				'fmwp_manager'      => __( 'Forum Manager', 'forumwp' ),
				'fmwp_moderator'    => __( 'Moderator', 'forumwp' ),
				'fmwp_participant'  => __( 'Participant', 'forumwp' ),
				'fmwp_spectator'    => __( 'Spectator', 'forumwp' ),
			] );
		}


		/**
		 *
		 */
		function init_capabilities_map() {
			$this->capabilities_map = apply_filters( 'fmwp_roles_capabilities_list', [
				'administrator'     => [
					'manage_fmwp_forums',
					'manage_fmwp_forums_all',
					'manage_fmwp_topics',
					'manage_fmwp_topics_all',
					'manage_fmwp_replies',
					'manage_fmwp_replies_all',
					'fmwp_see_admin_menu',
					'fmwp_see_reports',
					'fmwp_remove_reports',

					'edit_fmwp_forums',
					'edit_others_fmwp_forums',
					'publish_fmwp_forums',
					'read_private_fmwp_forums',
					'delete_fmwp_forums',
					'delete_private_fmwp_forums',
					'delete_published_fmwp_forums',
					'delete_others_fmwp_forums',
					'edit_private_fmwp_forums',
					'edit_published_fmwp_forums',
					'create_fmwp_forums',

					'edit_fmwp_topics',
					'edit_others_fmwp_topics',
					'publish_fmwp_topics',
					'read_private_fmwp_topics',
					'delete_fmwp_topics',
					'delete_private_fmwp_topics',
					'delete_published_fmwp_topics',
					'delete_others_fmwp_topics',
					'edit_private_fmwp_topics',
					'edit_published_fmwp_topics',
					'create_fmwp_topics',

					'edit_fmwp_replies',
					'edit_others_fmwp_replies',
					'publish_fmwp_replies',
					'read_private_fmwp_replies',
					'delete_fmwp_replies',
					'delete_private_fmwp_replies',
					'delete_published_fmwp_replies',
					'delete_others_fmwp_replies',
					'edit_private_fmwp_replies',
					'edit_published_fmwp_replies',

					'manage_fmwp_topic_tags',
					'edit_fmwp_topic_tags',
					'delete_fmwp_topic_tags',

					'manage_fmwp_forum_categories',
					'edit_fmwp_forum_categories',
					'delete_fmwp_forum_categories',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',
					'fmwp_post_forum',

					'read'
				],
				'editor'            => [
					'fmwp_see_admin_menu',

					'manage_fmwp_forums',
					'manage_fmwp_forums_all',
					'manage_fmwp_topics',
					'manage_fmwp_topics_all',
					'manage_fmwp_replies',
					'manage_fmwp_replies_all',

					'create_fmwp_forums',
					'create_fmwp_topics',

					'publish_fmwp_forums',
					'edit_fmwp_forums',
					'edit_private_fmwp_forums',
					'edit_published_fmwp_forums',
					'edit_others_fmwp_forums',
					'delete_fmwp_forums',
					'delete_private_fmwp_forums',
					'delete_published_fmwp_forums',
					'delete_others_fmwp_forums',
					'read_private_fmwp_forums',

					'publish_fmwp_topics',
					'edit_fmwp_topics',
					'edit_private_fmwp_topics',
					'edit_published_fmwp_topics',
					'edit_others_fmwp_topics',
					'delete_fmwp_topics',
					'delete_private_fmwp_topics',
					'delete_published_fmwp_topics',
					'delete_others_fmwp_topics',
					'read_private_fmwp_topics',

					'publish_fmwp_replies',
					'edit_fmwp_replies',
					'edit_private_fmwp_replies',
					'edit_published_fmwp_replies',
					'edit_others_fmwp_replies',
					'delete_fmwp_replies',
					'delete_private_fmwp_replies',
					'delete_published_fmwp_replies',
					'delete_others_fmwp_replies',
					'read_private_fmwp_replies',

					'manage_fmwp_topic_tags',
					'manage_fmwp_forum_categories',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',

					'read',
				],
				'author'            => [
					'fmwp_see_admin_menu',

					'manage_fmwp_forums',
					'manage_fmwp_forums_own',
					'manage_fmwp_topics',
					'manage_fmwp_topics_own',
					'manage_fmwp_replies',
					'manage_fmwp_replies_own',

					'create_fmwp_forums',
					'create_fmwp_topics',

					'publish_fmwp_forums',
					'edit_fmwp_forums',
					'edit_published_fmwp_forums',
					'delete_fmwp_forums',
					'delete_published_fmwp_forums',

					'publish_fmwp_topics',
					'edit_fmwp_topics',
					'edit_published_fmwp_topics',
					'delete_fmwp_topics',
					'delete_published_fmwp_topics',

					'publish_fmwp_replies',
					'edit_fmwp_replies',
					'edit_published_fmwp_replies',
					'delete_fmwp_replies',
					'delete_published_fmwp_replies',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',
					'read',
				],
				'contributor'       => [
					'fmwp_see_admin_menu',

					'manage_fmwp_forums',
					'manage_fmwp_forums_own',
					'manage_fmwp_topics',
					'manage_fmwp_topics_own',
					'manage_fmwp_replies',
					'manage_fmwp_replies_own',

					'create_fmwp_forums',
					'create_fmwp_topics',

					'edit_fmwp_topics',
					'delete_fmwp_topics',
					'edit_fmwp_replies',
					'delete_fmwp_replies',
					'edit_fmwp_forums',
					'delete_fmwp_forums',
					'read',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',
				],
				'subscriber'        => [
					'read',

					'edit_fmwp_topics',
					'edit_fmwp_replies',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',
				],


				'fmwp_manager'      => [
					'manage_fmwp_forums',
					'manage_fmwp_forums_all',
					'manage_fmwp_topics',
					'manage_fmwp_topics_all',
					'manage_fmwp_replies',
					'manage_fmwp_replies_all',
					'fmwp_see_admin_menu',
					'fmwp_see_reports',
					'fmwp_remove_reports',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',
					'fmwp_post_forum',

					'edit_fmwp_forums',
					'edit_others_fmwp_forums',
					'publish_fmwp_forums',
					'read_private_fmwp_forums',
					'delete_fmwp_forums',
					'delete_private_fmwp_forums',
					'delete_published_fmwp_forums',
					'delete_others_fmwp_forums',
					'edit_private_fmwp_forums',
					'edit_published_fmwp_forums',
					'create_fmwp_forums',

					'edit_fmwp_topics',
					'edit_others_fmwp_topics',
					'publish_fmwp_topics',
					'read_private_fmwp_topics',
					'delete_fmwp_topics',
					'delete_private_fmwp_topics',
					'delete_published_fmwp_topics',
					'delete_others_fmwp_topics',
					'edit_private_fmwp_topics',
					'edit_published_fmwp_topics',
					'create_fmwp_topics',

					'edit_fmwp_replies',
					'edit_others_fmwp_replies',
					//'create_fmwp_replies', lock the creation of replies via wp-admin
					'publish_fmwp_replies',
					'read_private_fmwp_replies',
					'delete_fmwp_replies',
					'delete_private_fmwp_replies',
					'delete_published_fmwp_replies',
					'delete_others_fmwp_replies',
					'edit_private_fmwp_replies',
					'edit_published_fmwp_replies',

					'manage_fmwp_topic_tags',
					'edit_fmwp_topic_tags',
					'delete_fmwp_topic_tags',

					'manage_fmwp_forum_categories',
					'edit_fmwp_forum_categories',
					'delete_fmwp_forum_categories',

					'edit_posts',
					'read',
				],
				'fmwp_moderator'    => [
					'manage_fmwp_topics',
					'manage_fmwp_topics_all',
					'manage_fmwp_replies',
					'manage_fmwp_replies_all',

					'fmwp_see_admin_menu',
					'fmwp_see_reports',
					'fmwp_remove_reports',

					'edit_fmwp_topics',
					'edit_others_fmwp_topics',
					'publish_fmwp_topics',
					'read_private_fmwp_topics',
					'delete_fmwp_topics',
					'delete_private_fmwp_topics',
					'delete_published_fmwp_topics',
					'delete_others_fmwp_topics',
					'edit_private_fmwp_topics',
					'edit_published_fmwp_topics',
					'create_fmwp_topics',

					'edit_fmwp_replies',
					'edit_others_fmwp_replies',
					//'create_fmwp_replies', lock the creation of replies via wp-admin
					'publish_fmwp_replies',
					'read_private_fmwp_replies',
					'delete_fmwp_replies',
					'delete_private_fmwp_replies',
					'delete_published_fmwp_replies',
					'delete_others_fmwp_replies',
					'edit_private_fmwp_replies',
					'edit_published_fmwp_replies',

					'manage_fmwp_topic_tags',
					'edit_fmwp_topic_tags',
					'delete_fmwp_topic_tags',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',

					'edit_posts',
					'read',
				],
				'fmwp_participant'  => [
					'read',

//					'edit_posts',
//					'edit_published_fmwp_topics',

					'edit_fmwp_topics',
					'edit_fmwp_replies',

					'fmwp_post_reply',
					'fmwp_edit_own_reply',
					'fmwp_post_topic',
					'fmwp_edit_own_topic',
				],
				'fmwp_spectator'    => [
					'read',
				],
			] );
		}


		/**
		 *
		 */
		function init_all_caps() {
			$this->all_caps = apply_filters( 'fmwp_all_caps_list', [
				'manage_fmwp_forums',
				'manage_fmwp_forums_all',
				'manage_fmwp_forums_own',
				'manage_fmwp_topics',
				'manage_fmwp_topics_all',
				'manage_fmwp_topics_own',
				'manage_fmwp_replies',
				'manage_fmwp_replies_all',
				'manage_fmwp_replies_own',

				'fmwp_see_admin_menu',
				'fmwp_see_reports',
				'fmwp_remove_reports',

				'edit_fmwp_forums',
				'edit_others_fmwp_forums',
				'publish_fmwp_forums',
				'read_private_fmwp_forums',
				'delete_fmwp_forums',
				'delete_private_fmwp_forums',
				'delete_published_fmwp_forums',
				'delete_others_fmwp_forums',
				'edit_private_fmwp_forums',
				'edit_published_fmwp_forums',
				'create_fmwp_forums',

				'edit_fmwp_topics',
				'edit_others_fmwp_topics',
				'publish_fmwp_topics',
				'read_private_fmwp_topics',
				'delete_fmwp_topics',
				'delete_private_fmwp_topics',
				'delete_published_fmwp_topics',
				'delete_others_fmwp_topics',
				'edit_private_fmwp_topics',
				'edit_published_fmwp_topics',
				'create_fmwp_topics',

				'edit_fmwp_replies',
				'edit_others_fmwp_replies',
				'create_fmwp_replies',
				'publish_fmwp_replies',
				'read_private_fmwp_replies',
				'delete_fmwp_replies',
				'delete_private_fmwp_replies',
				'delete_published_fmwp_replies',
				'delete_others_fmwp_replies',
				'edit_private_fmwp_replies',
				'edit_published_fmwp_replies',

				'manage_fmwp_topic_tags',
				'edit_fmwp_topic_tags',
				'delete_fmwp_topic_tags',

				'manage_fmwp_forum_categories',
				'edit_fmwp_forum_categories',
				'delete_fmwp_forum_categories',

				'fmwp_post_reply',
				'fmwp_edit_own_reply',
				'fmwp_post_topic',
				'fmwp_edit_own_topic',
				'fmwp_post_forum',

				'read',
			] );
		}


		/**
		 *
		 */
		function init_core_pages() {
			$this->core_pages = apply_filters( 'fmwp_core_pages', [
				'login'     => [
					'title' => __( 'Login', 'forumwp' ),
				],
				'register'  => [
					'title' => __( 'Registration', 'forumwp' ),
				],
				'profile'   => [
					'title' => __( 'User Profile', 'forumwp' ),
				],
				'forums'    => [
					'title' => __( 'Forums', 'forumwp' ),
				],
				'topics'    => [
					'title' => __( 'Topics', 'forumwp' ),
				],
			] );
		}


		/**
		 *
		 */
		function init_variables() {
			$this->variables = apply_filters( 'fmwp_static_variables', [
				'forums_per_page'           => 20,
				'forum_categories_per_page' => 20,
				'topics_per_page'           => 20,
				'replies_per_page'          => 20,
			] );
		}


		/**
		 *
		 */
		function init_email_notifications() {
			$this->email_notifications = apply_filters( 'fmwp_email_notifications', [
				'mention'   => [
					'key'               => 'mention',
					'title'             => __( 'Mention', 'forumwp' ),
					'description'       => __( 'Whether to send the user an email when his was mentioned', 'forumwp' ),
					'recipient'         => 'user',
					'default_active'    => false,
				],
			] );
		}
	}
}