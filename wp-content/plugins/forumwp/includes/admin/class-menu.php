<?php
namespace fmwp\admin;


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\admin\Menu' ) ) {


	/**
	 * Class Menu
	 *
	 * @package fmwp\common
	 */
	class Menu {


		/**
		 * Menu constructor.
		 */
		function __construct() {
			add_action( 'admin_menu',  [ &$this, 'menu' ] );

			add_filter( 'admin_body_class', [ &$this, 'selected_menu' ], 10, 1 );
			add_filter( 'submenu_file', [ &$this, 'wp_admin_submenu_filter' ] );

			add_action( 'admin_init', [ &$this, 'wrong_settings' ], 9999 );
		}


		/**
		 * Creates the plugin's menu
		 *
		 * @since 1.0
		 */
		function menu() {
			$capability = current_user_can( 'fmwp_see_admin_menu' ) ? 'fmwp_see_admin_menu' : 'manage_options';
			add_menu_page( __( 'Forums', 'forumwp' ), __( 'Forums', 'forumwp' ), $capability, 'forumwp', '', 'dashicons-format-chat', 40 );

			$manage_forums_cap = current_user_can( 'manage_fmwp_forums' ) ? 'manage_fmwp_forums' : 'manage_options';
			$manage_topics_cap = current_user_can( 'manage_fmwp_topics' ) ? 'manage_fmwp_topics' : 'manage_options';
			$manage_replies_cap = current_user_can( 'manage_fmwp_replies' ) ? 'manage_fmwp_replies' : 'manage_options';

			$category_capability = current_user_can( 'manage_fmwp_forum_categories' ) ? 'manage_fmwp_forum_categories' : 'manage_options';
			$tags_capability = current_user_can( 'manage_fmwp_topic_tags' ) ? 'manage_fmwp_topic_tags' : 'manage_options';

			add_submenu_page( 'forumwp', __( 'Dashboard', 'forumwp' ), __( 'Dashboard', 'forumwp' ), $capability, 'forumwp', '' );

			add_submenu_page( 'forumwp', __( 'Forums', 'forumwp' ), __( 'Forums', 'forumwp' ), $manage_forums_cap, 'edit.php?post_type=fmwp_forum' );
			if ( FMWP()->options()->get( 'forum_categories' ) ) {
				add_submenu_page( 'forumwp', __( 'Forum Categories', 'forumwp' ), __( 'Forum Categories', 'forumwp' ), $category_capability, 'edit-tags.php?taxonomy=fmwp_forum_category&post_type=fmwp_forum' );
			}


			add_submenu_page( 'forumwp', __( 'Topics', 'forumwp' ), __( 'Topics', 'forumwp' ), $manage_topics_cap, 'edit.php?post_type=fmwp_topic' );
			if ( FMWP()->options()->get( 'topic_tags' ) ) {
				add_submenu_page( 'forumwp', __( 'Topic Tags', 'forumwp' ), __( 'Topic Tags', 'forumwp' ), $tags_capability, 'edit-tags.php?taxonomy=fmwp_topic_tag&post_type=fmwp_topic' );
			}

			add_submenu_page( 'forumwp', __( 'Replies', 'forumwp' ), __( 'Replies', 'forumwp' ), $manage_replies_cap, 'edit.php?post_type=fmwp_reply' );

			add_submenu_page( 'forumwp', __( 'Settings', 'forumwp' ), __( 'Settings', 'forumwp' ), 'manage_options', 'forumwp-settings', [ &$this, 'settings' ] );
		}


		/**
		 * Hide first submenu and replace to Forums
		 *
		 * @param string $submenu_file
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		function wp_admin_submenu_filter( $submenu_file ) {
			global $plugin_page;

			$hidden_submenus = [
				'forumwp',
			];

			// Select another submenu item to highlight (optional).
			if ( $plugin_page && isset( $hidden_submenus[ $plugin_page ] ) ) {
				$submenu_file = 'edit.php?post_type=fmwp_forum';
			}

			// Hide the submenu.
			foreach ( $hidden_submenus as $submenu ) {
				remove_submenu_page( 'forumwp', $submenu );
			}

			return $submenu_file;
		}


		/**
		 * Made selected ForumWP menu on Add/Edit CPT and Term Taxonomies
		 *
		 * @param string $classes
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		function selected_menu( $classes ) {
			global $submenu, $pagenow;

			if ( isset( $submenu['forumwp'] ) ) {
				if ( isset( $_GET['post_type'] ) && ( 'fmwp_forum' == $_GET['post_type'] || 'fmwp_topic' == $_GET['post_type'] || 'fmwp_reply' == $_GET['post_type'] ) ) {
					add_filter( 'parent_file', [ &$this, 'change_parent_file' ], 200, 1 );
				}

				if ( 'post.php' == $pagenow && ( isset( $_GET['post'] ) && ( 'fmwp_forum' == get_post_type( $_GET['post'] ) || 'fmwp_topic' == get_post_type( $_GET['post'] ) || 'fmwp_reply' == get_post_type( $_GET['post'] ) ) ) ) {
					add_filter( 'parent_file', [ &$this, 'change_parent_file' ], 200, 1 );
				}

				add_filter( 'submenu_file', [ &$this, 'change_submenu_file' ], 200, 2 );
			}

			return $classes;
		}


		/**
		 * Return admin submenu variable for display pages
		 *
		 * @param string $parent_file
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		function change_parent_file( $parent_file ) {
			global $pagenow;

			if ( 'edit-tags.php' !== $pagenow && 'term.php' !== $pagenow && 'post-new.php' !== $pagenow ) {
				$pagenow = 'admin.php';
			}

			$parent_file = 'forumwp';

			return $parent_file;
		}


		/**
		 * Return admin submenu variable for display pages
		 *
		 * @param string $submenu_file
		 * @param string $parent_file
		 *
		 * @return string
		 *
		 * @since 1.0
		 */
		function change_submenu_file( $submenu_file, $parent_file ) {
			global $pagenow;

			if ( 'edit-tags.php' == $pagenow || 'term.php' == $pagenow || 'post-new.php' == $pagenow ) {
				if ( $parent_file == 'forumwp' ) {
					if ( isset( $_GET['post_type'] ) && ( 'fmwp_forum' == $_GET['post_type'] || 'fmwp_topic' == $_GET['post_type'] ) &&
					     isset( $_GET['taxonomy'] ) && ( 'fmwp_forum_category' == $_GET['taxonomy'] || 'fmwp_topic_tag' == $_GET['taxonomy'] ) ) {
						$submenu_file = 'edit-tags.php?taxonomy=' . sanitize_key( $_GET['taxonomy'] ) . '&post_type=' . sanitize_key( $_GET['post_type'] );
					} elseif ( 'post-new.php' == $pagenow && isset( $_GET['post_type'] ) && ( 'fmwp_forum' == $_GET['post_type'] || 'fmwp_topic' == $_GET['post_type'] || 'fmwp_reply' == $_GET['post_type'] ) ) {
						$submenu_file = 'edit.php?post_type=' . sanitize_key( $_GET['post_type'] );
					}

					$pagenow = 'admin.php';
				}
			}

			return $submenu_file;
		}


		/**
		 * Settings page callback
		 *
		 * @since 1.0
		 */
		function settings() {
			include_once FMWP()->admin()->templates_path . 'settings' . DIRECTORY_SEPARATOR . 'settings.php';
		}


		/**
		 * Handle redirect if wrong settings tab is open
		 *
		 * @since 2.0
		 */
		function wrong_settings() {
			global $pagenow;

			if ( 'admin.php' == $pagenow && isset( $_GET['page'] ) && 'forumwp-settings' == $_GET['page'] ) {
				$current_tab = empty( $_GET['tab'] ) ? '' : sanitize_key( urldecode( $_GET['tab'] ) );
				$current_subtab = empty( $_GET['section'] ) ? '' : sanitize_key( urldecode( $_GET['section'] ) );

				$settings_struct = FMWP()->admin()->settings()->get_settings( $current_tab, $current_subtab );
				$custom_section = FMWP()->admin()->settings()->section_is_custom( $current_tab, $current_subtab );

				if ( ! $custom_section && empty( $settings_struct ) ) {
					wp_redirect( add_query_arg( [ 'page' => 'forumwp-settings' ], admin_url( 'admin.php' ) ) );
					exit;
				} else {
					//remove extra query arg for Email list table
					$email_key = empty( $_GET['email'] ) ? '' : sanitize_key( urldecode( $_GET['email'] ) );
					$email_notifications = FMWP()->config()->get( 'email_notifications' );

					if ( empty( $email_key ) || empty( $email_notifications[ $email_key ] ) ) {
						if ( ! empty( $_GET['_wp_http_referer'] ) ) {
							wp_redirect( remove_query_arg( [ '_wp_http_referer', '_wpnonce' ], wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
							exit;
						}
					}
				}
			}
		}
	}
}