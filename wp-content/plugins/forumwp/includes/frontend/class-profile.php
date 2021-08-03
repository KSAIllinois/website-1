<?php
namespace fmwp\frontend;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\frontend\Profile' ) ) {


	/**
	 * Class Profile
	 *
	 * @package fmwp\frontend
	 */
	class Profile {


		/**
		 * Profile constructor.
		 */
		function __construct() {
		}


		/**
		 * @param \WP_User $user
		 *
		 * @return array
		 */
		function get_edit_tab_data( $user ) {

			$data = [
				'id'            => $user->ID,
				'login'         => $user->user_login,
				'email'         => $user->user_email,
				'first_name'    => $user->first_name,
				'last_name'     => $user->last_name,
				'url'           => $user->user_url,
				'description'   => $user->description,
			];

			return $data;
		}


		/**
		 * Profile Tabs
		 *
		 * @return array
		 */
		function tabs_list() {
			$tabs = [
				'topics'    => [
					'title' => __( 'Topics', 'forumwp' ),
					'ajax'  => false,
				],
				'replies'   => [
					'title' => __( 'Replies', 'forumwp' ),
					'ajax'  => false,
				],
				'edit'      => [
					'title' => __( 'Edit Profile', 'forumwp' ),
					'ajax'  => true,
				],
			];

			$tabs = apply_filters( 'fmwp_profile_tabs', $tabs );
			return $tabs;
		}


		/**
		 * Profile Tabs
		 *
		 * @return array
		 */
		function subtabs_list() {
			$subtabs = [];

			$subtabs = apply_filters( 'fmwp_profile_subtabs', $subtabs );
			return $subtabs;
		}


		/**
		 * @param string $slug
		 * @param \WP_User $user
		 *
		 * @return bool
		 */
		function tab_visibility( $slug, $user ) {
			$visible = true;

			if ( $slug == 'edit' ) {
				if ( ! is_user_logged_in() || $user->ID != get_current_user_id() ) {
					$visible = false;
				}
			} elseif ( $slug == 'replies' ) {
				if ( ! user_can( $user->ID, 'fmwp_post_reply' ) ) {
					$visible = false;
				}
			} elseif ( $slug == 'topics' ) {
				if ( ! user_can( $user->ID, 'fmwp_post_topic' ) ) {
					$visible = false;
				}
			}

			$visible = apply_filters( 'fmwp_profile_tab_visible', $visible, $slug, $user );
			return $visible;
		}


		/**
		 * @param string $slug
		 * @param string $tab
		 * @param \WP_User $user
		 *
		 * @return bool
		 */
		function subtab_visibility( $slug, $tab, $user ) {
			$visible = true;

			$visible = apply_filters( 'fmwp_profile_subtab_visible', $visible, $slug, $tab, $user );
			return $visible;
		}


		/**
		 * Get Profile Tabs for User
		 *
		 * @param \WP_User $user
		 *
		 * @return array
		 */
		function get_profile_tabs( $user ) {
			$menu_items = [];

			$tabs = $this->tabs_list();
			foreach ( $tabs as $slug => $data ) {

				$visible = $this->tab_visibility( $slug, $user );
				if ( ! $visible ) {
					continue;
				}

				$menu_items[ $slug ] = [
					'title' => $data['title'],
					'link'  => FMWP()->user()->get_profile_link( $user->ID, $slug ),
					'ajax'  => $data['ajax'],
				];

				if ( ! empty( $data['module'] ) ) {
					$menu_items[ $slug ]['module'] = $data['module'];
				}
			}

			return $menu_items;
		}


		/**
		 * Get Profile Tabs for User
		 *
		 * @param \WP_User $user
		 * @param string $tab Profile Tab's slug
		 *
		 * @return array
		 */
		function get_profile_subtabs( $user, $tab ) {
			$menu_items = [];

			$subtabs = $this->subtabs_list();
			if ( empty( $subtabs[ $tab ] ) ) {
				return $menu_items;
			}

			foreach ( $subtabs[ $tab ] as $slug => $title ) {
				$visible = $this->subtab_visibility( $slug, $tab, $user );
				if ( ! $visible ) {
					continue;
				}

				$menu_items[ $slug ] = [
					'title' => $title,
					'link'  => FMWP()->user()->get_profile_link( $user->ID, $tab, $slug ),
				];
			}

			return $menu_items;
		}
	}
}