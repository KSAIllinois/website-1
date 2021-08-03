<?php
namespace fmwp\common;


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\common\Reports' ) ) {


	/**
	 * Class Reports
	 * @package fmwp\common
	 */
	class Reports {


		/**
		 * Reports constructor.
		 */
		function __construct() {
		}


		/**
		 * @param int $post_id
		 *
		 * @return string|null
		 */
		function get_count( $post_id ) {
			global $wpdb;

			$count = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT( id ) 
				FROM {$wpdb->prefix}fmwp_reports
				WHERE post_id = %d",
				$post_id
			) );

			$count = ! empty( $count ) ? (int) $count : 0;
			return $count;
		}


		function get_post_id_reports( $post_type = false ) {
			global $wpdb;

			if ( ! $post_type ) {
				$post_ids = $wpdb->get_col(
					"SELECT DISTINCT post_id
					FROM {$wpdb->prefix}fmwp_reports"
				);
			} else {
				$post_ids = $wpdb->get_col( $wpdb->prepare(
					"SELECT DISTINCT post_id 
					FROM {$wpdb->prefix}fmwp_reports r
					LEFT JOIN {$wpdb->posts} p ON p.ID = r.post_id
					WHERE p.post_type = %s",
					$post_type
				) );
			}

			$post_ids = ! empty( $post_ids ) ? $post_ids : [];
			return $post_ids;
		}


		function get_all_reports_count( $post_type = false ) {
			global $wpdb;

			if ( ! $post_type ) {
				$count = $wpdb->get_var(
					"SELECT COUNT( DISTINCT post_id ) 
					FROM {$wpdb->prefix}fmwp_reports"
				);
			} else {
				$count = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT( DISTINCT post_id ) 
					FROM {$wpdb->prefix}fmwp_reports r
					LEFT JOIN {$wpdb->posts} p ON p.ID = r.post_id
					WHERE p.post_type = %s",
					$post_type
				) );
			}

			$count = ! empty( $count ) ? (int) $count : 0;
			return $count;
		}


		/**
		 * @param int $post_id
		 *
		 * @return array
		 */
		function get_list( $post_id ) {
			global $wpdb;

			$reports = $wpdb->get_results( $wpdb->prepare(
				"SELECT * 
				FROM {$wpdb->prefix}fmwp_reports
				WHERE post_id = %d",
				$post_id
			), ARRAY_A );

			$reports = ! empty( $reports ) ? $reports : [];
			return $reports;
		}


		/**
		 * @param int $user_id
		 *
		 * @return array
		 */
		function get_by_user( $user_id ) {
			global $wpdb;

			$reports = $wpdb->get_results( $wpdb->prepare(
				"SELECT * 
				FROM {$wpdb->prefix}fmwp_reports
				WHERE user_id = %d",
				$user_id
			), ARRAY_A );

			$reports = ! empty( $reports ) ? $reports : [];
			return $reports;
		}


		/**
		 * @param int $post_id
		 *
		 * @return bool
		 */
		function is_reported( $post_id ) {
			$reports_count = $this->get_count( $post_id );

			return $reports_count > 0;
		}


		/**
		 * @param int $post_id
		 * @param int $user_id
		 *
		 * @return bool
		 */
		function is_reported_by_user( $post_id, $user_id ) {
			global $wpdb;

			$report = $wpdb->get_var( $wpdb->prepare(
				"SELECT id 
				FROM {$wpdb->prefix}fmwp_reports
				WHERE post_id = %d AND 
				      user_id = %d",
				$post_id,
				$user_id
			) );

			return ! empty( $report );
		}


		/**
		 * Add post report
		 *
		 * @param int $post_id
		 * @param int $user_id
		 *
		 * @return int
		 */
		function add( $post_id, $user_id ) {
			global $wpdb;

			$wpdb->insert(
				"{$wpdb->prefix}fmwp_reports",
				[
					'post_id'       => $post_id,
					'user_id'       => $user_id,
					'creation_date' => time(),
				],
				[
					'%d',
					'%d',
					'%d',
				]
			);

			return $wpdb->insert_id;
		}


		/**
		 * Remove post report
		 *
		 * @param int $post_id
		 * @param int $user_id
		 *
		 * @return bool
		 */
		function remove( $post_id, $user_id ) {
			global $wpdb;

			if ( ! FMWP()->user()->can_unreport( $user_id, $post_id ) ) {
				return false;
			}

			$deleted = $wpdb->delete(
				"{$wpdb->prefix}fmwp_reports",
				[
					'post_id'   => $post_id,
					'user_id'   => $user_id,
				],
				[
					'%d',
					'%d',
				]
			);

			return ! empty( $deleted );
		}


		/**
		 * Remove post report
		 *
		 * @param int $post_id
		 *
		 * @return bool
		 */
		function clear( $post_id ) {
			global $wpdb;

			if ( FMWP()->user()->can_clear_reports() ) {
				$deleted = $wpdb->delete(
					"{$wpdb->prefix}fmwp_reports",
					[
						'post_id'   => $post_id,
					],
					[
						'%d',
					]
				);
			}

			return ! empty( $deleted );
		}
	}
}