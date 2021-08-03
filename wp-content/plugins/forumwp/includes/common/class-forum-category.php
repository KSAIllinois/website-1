<?php
namespace fmwp\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\common\Forum_Category' ) ) {


	/**
	 * Class Forum
	 *
	 * @package fmwp\common
	 */
	class Forum_Category {


		/**
		 * Rewrite constructor.
		 */
		function __construct() {
		}


		/**
		 * @param $category_id
		 *
		 * @return int
		 */
		function get_replies_count( $category_id ) {
			$forums = $this->get_forums( $category_id );

			$count = 0;

			foreach ( $forums as $forum_id ) {
				$count += FMWP()->common()->forum()->get_statistics( $forum_id, 'posts' );
			}

			return $count;
		}


		/**
		 * @param $category_id
		 *
		 * @return int
		 */
		function get_topics_count( $category_id ) {
			$forums = $this->get_forums( $category_id );

			$count = 0;

			foreach ( $forums as $forum_id ) {
				$count += FMWP()->common()->forum()->get_statistics( $forum_id, 'topics' );
			}

			return $count;
		}


		/**
		 * @param int $category_id
		 * @param string $fields
		 *
		 * @return int[]|\WP_Post[]
		 */
		function get_forums( $category_id, $fields = 'ids' ) {
			$query_args = [
				'posts_per_page'    => -1,
				'post_type'         => 'fmwp_forum',
				'tax_query'         => [
					[
						'taxonomy'          => 'fmwp_forum_category',
						'field'             => 'term_id ',
						'terms'             => [ $category_id ],
						'include_children ' => false
					],
				],
				'fields'            => $fields,
				'suppress_filters'  => false,
			];

			if ( ! is_user_logged_in() || ! current_user_can( 'manage_fmwp_forums_all' ) ) {
				$query_args['meta_query'][] = [
					'key'       => 'fmwp_visibility',
					'value'     => 'public',
					'compare'   => '=',
				];
			}

			$query_args = apply_filters( 'fmwp_get_forums_arguments', $query_args );

			$forums = get_posts( $query_args );

			foreach ( $forums as $k => $forum_id ) {
				if ( post_password_required( $forum_id ) ) {
					unset( $forums[ $k ] );
				}
			}

			$forums = array_values( $forums );

			return $forums;
		}
	}
}