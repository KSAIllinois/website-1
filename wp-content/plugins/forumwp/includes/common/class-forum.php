<?php
namespace fmwp\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\common\Forum' ) ) {


	/**
	 * Class Forum
	 *
	 * @package fmwp\common
	 */
	class Forum extends Post {


		/**
		 * @var array
		 */
		var $statuses = [];


		/**
		 * @var array
		 */
		var $visibilities = [];


		/**
		 * @var array
		 */
		var $post_status;


		/**
		 * Forum constructor.
		 */
		function __construct() {
			parent::__construct();

			$this->visibilities = [
				'public'    => __( 'Public', 'forumwp' ),
				'private'   => __( 'Private', 'forumwp' ),
				'hidden'    => __( 'Hidden', 'forumwp' ),
			];

			add_action( 'init', [ &$this, 'init_statuses' ], 10 );

			add_action( 'save_post_fmwp_forum', [ &$this, 'save_post' ], 999997, 3 );

			add_filter( 'the_posts', [ &$this, 'filter_private_forums' ], 99, 2 );
		}


		/**
		 * Make invisible private and hidden forums
		 *
		 * @param $posts
		 * @param $query
		 *
		 * @return array
		 */
		function filter_private_forums( $posts, $query ) {
			if ( FMWP()->is_request( 'admin' ) && ! FMWP()->is_request( 'ajax' ) ) {
				return $posts;
			}

			//if empty
			if ( empty( $posts ) ) {
				return $posts;
			}

			if ( is_user_logged_in() && current_user_can( 'manage_fmwp_forums_all' ) ) {
				return $posts;
			}

			$filtered_posts = [];

			foreach ( $posts as $post ) {
				if ( $post->post_type != 'fmwp_forum' ) {
					$filtered_posts[] = $post;
					continue;
				}

				$visibility = get_post_meta( $post->ID, 'fmwp_visibility', true );
				if ( $visibility == 'public' ) {
					$filtered_posts[] = $post;
					continue;
				}
			}

			$posts = $filtered_posts;
			return $posts;
		}


		/**
		 * @param int $post_ID
		 * @param \WP_Post $post
		 * @param bool $update
		 */
		function save_post( $post_ID, $post, $update ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			$upgrade_last_update = apply_filters( 'fmwp_forum_upgrade_last_update', true, $post_ID );

			if ( $upgrade_last_update ) {
				update_post_meta( $post_ID, 'fmwp_last_update', time() );
			}
		}


		/**
		 * Set statuses
		 */
		function init_statuses() {
			$this->post_status = [ 'publish' ];

			if ( is_user_logged_in() ) {
				if ( current_user_can( 'manage_fmwp_forums_all' ) ) {
					$this->post_status[] = 'private';
					$this->post_status[] = 'pending';
				}
			}
		}


		/**
		 * @param \WP_Post $forum
		 * @param bool|int $user_id
		 *
		 * @return array
		 */
		function actions_list( $forum, $user_id = false ) {
			//Forum dropdown actions
			$items = [];

			if ( ! $user_id ) {
				if ( is_user_logged_in() ) {
					$user_id = get_current_user_id();
				} else {
					return $items;
				}
			}

			if ( user_can( $user_id, 'manage_fmwp_forums_all' ) ) {
				if ( $this->is_locked( $forum ) ) {
					$items = array_merge( $items, [
						'fmwp-unlock-forum' => __( 'Unlock forum', 'forumwp' ),
					] );
				} else {
					$items = array_merge( $items, [
						'fmwp-lock-forum'   => __( 'Lock forum', 'forumwp' ),
					] );
				}

				$items = array_merge( $items, [
					'fmwp-trash-forum'  => __( 'Move to trash', 'forumwp' ),
				] );
			}

			$items = array_unique( $items );

			return $items;
		}


		/**
		 * @param int $forum_id
		 *
		 * @return array|int[]|void|\WP_Post|\WP_Post[]|null
		 */
		function get_last_topic( $forum_id ) {
			if ( post_password_required( $forum_id ) ) {
				return;
			} else {
				$args = [
					'post_type'         => 'fmwp_topic',
					'posts_per_page'    => 1,
					'post_status'       => FMWP()->common()->topic()->post_status,
					'fields'            => 'ids',
					'orderby'           => 'date',
					'order'             => 'desc',
					'meta_query'        => [
						'relation' => 'AND',
						[
							'key'   => 'fmwp_forum',
							'value' => $forum_id,
						],
					],
				];

				if ( ! is_user_logged_in() || ! current_user_can( 'manage_fmwp_topics_all' ) ) {
					$args['meta_query'][] = [
						/* Exclude spam topic from latest topic query */
						'relation' => 'OR',
						[
							'key'       => 'fmwp_spam',
							'compare'   => 'NOT EXISTS',
						],
						[
							'key'       => 'fmwp_spam',
							'value'     => 1,
							'compare'   => '!=',
						],
					];
				}

				$args['suppress_filters'] = false;

				$args = apply_filters( 'fmwp_get_topics_arguments', $args );

				$topic = get_posts( $args );

				if ( ! empty( $topic ) && ! is_wp_error( $topic ) ) {
					$topic = get_post( $topic[0] );
				}

				return $topic;
			}
		}


		/**
		 * @param int $forum_id
		 * @param string $key
		 *
		 * @return int|array
		 */
		function get_statistics( $forum_id, $key = 'all' ) {
			$stats = [];

			switch ( $key ) {
				case 'topics':

					if ( post_password_required( $forum_id ) ) {
						$stats = 0;
					} else {
						$args = [
							'post_type'         => 'fmwp_topic',
							'posts_per_page'    => -1,
							'post_status'       => FMWP()->common()->topic()->post_status,
							'fields'            => 'ids',
							'meta_query'        => [
								'relation' => 'AND',
								[
									'key'   => 'fmwp_forum',
									'value' => $forum_id,
								],
							],
						];

						if ( ! is_user_logged_in() || ! current_user_can( 'manage_fmwp_topics_all' ) ) {
							$args['meta_query'][] = [
								/* Exclude spam topic from latest topic query */
								'relation' => 'OR',
								[
									'key'       => 'fmwp_spam',
									'compare'   => 'NOT EXISTS',
								],
								[
									'key'       => 'fmwp_spam',
									'value'     => 1,
									'compare'   => '!=',
								],
							];
						}

						$args['suppress_filters'] = false;

						$args = apply_filters( 'fmwp_get_topics_arguments', $args );

						$topics = get_posts( $args );

						$stats = ( ! empty( $topics ) && ! is_wp_error( $topics ) ) ? count( $topics ) : 0;
					}

					break;
				case 'posts':
					if ( post_password_required( $forum_id ) ) {
						$stats = 0;
					} else {
						$args = [
							'post_type'         => 'fmwp_topic',
							'posts_per_page'    => -1,
							'post_status'       => FMWP()->common()->topic()->post_status,
							'fields'            => 'ids',
							'meta_query'        => [
								'relation' => 'AND',
								[
									'key'   => 'fmwp_forum',
									'value' => $forum_id,
								],
							],
						];

						if ( ! is_user_logged_in() || ! current_user_can( 'manage_fmwp_topics_all' ) ) {
							$args['meta_query'][] = [
								/* Exclude spam topic from latest topic query */
								'relation' => 'OR',
								[
									'key'       => 'fmwp_spam',
									'compare'   => 'NOT EXISTS',
								],
								[
									'key'       => 'fmwp_spam',
									'value'     => 1,
									'compare'   => '!=',
								],
							];
						}

						$args['suppress_filters'] = false;

						$args = apply_filters( 'fmwp_get_topics_arguments', $args );

						$topics = get_posts( $args );

						$stats = 0;
						foreach ( $topics as $topic_id ) {
							$stats += FMWP()->common()->topic()->get_statistics( $topic_id, 'replies' );
						}
					}
					break;
				case 'all':
					$keys = [
						'topics',
						'all',
					];
					foreach ( $keys as $attr ) {
						$stats[ $attr ] = $this->get_statistics( $forum_id, $attr );
					}

					break;
			}

			return $stats;
		}


		/**
		 * @param int $forum_id
		 * @param bool|string $data
		 *
		 * @return array|\WP_Error
		 */
		function get_categories( $forum_id, $data = false ) {
			$args = [
				'orderby'   => 'name',
				'order'     => 'ASC',
			];
			if ( $data === 'names' ) {
				$args['fields'] = 'names';
			}

			$terms = wp_get_post_terms(
				$forum_id,
				'fmwp_forum_category',
				$args
			);

			return $terms;
		}


		/**
		 * Lock Forum handler
		 *
		 * @param int $forum_id
		 */
		function lock( $forum_id ) {
			$post = get_post( $forum_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( $this->is_locked( $post ) ) {
				return;
			}

			update_post_meta( $forum_id, 'fmwp_locked', true );

			do_action( 'fmwp_after_lock_forum', $forum_id );
		}


		/**
		 * @param int|\WP_Post $post
		 *
		 * @return bool
		 */
		function is_locked( $post ) {
			$locked = false;
			if ( is_numeric( $post ) ) {
				$post = get_post( $post );

				if ( empty( $post ) || is_wp_error( $post ) ) {
					return $locked;
				}
			}

			if ( isset( $post->post_type ) && $post->post_type == 'fmwp_forum' ) {
				$is_locked = get_post_meta( $post->ID, 'fmwp_locked', true );
				$locked = ! empty( $is_locked );
			}

			return $locked;
		}


		/**
		 * Unlock Forum handler
		 *
		 * @param int $forum_id
		 */
		function unlock( $forum_id ) {
			$post = get_post( $forum_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( ! $this->is_locked( $post ) ) {
				return;
			}

			update_post_meta( $forum_id, 'fmwp_locked', false );

			do_action( 'fmwp_after_unlock_forum', $forum_id );
		}


		/**
		 * Move to Trash Forum handler
		 *
		 * @param int $forum_id
		 */
		function move_to_trash( $forum_id ) {
			$post = get_post( $forum_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( $post->post_status === 'trash' ) {
				return;
			}

			wp_update_post( [
				'ID'            => $post->ID,
				'post_status'   => 'trash',
				'meta_input'    => [
					'fmwp_prev_status' => $post->post_status,
				],
			] );

			do_action( 'fmwp_after_trash_forum', $forum_id );
		}


		/**
		 * Create Forum
		 *
		 * @param array $data
		 *
		 * @return int
		 */
		function create( $data ) {
			$author = ! empty( $data['author_id'] ) ? $data['author_id'] : get_current_user_id();

			if ( ! in_array( $data['visibility'], array_keys( $this->visibilities ) ) ) {
				$data['visibility'] = 'public';
			}

			list( $orig_content, $post_content ) = $this->prepare_content( $data['content'], 'fmwp_forum' );

			$args = [
				'post_type'     => 'fmwp_forum',
				'post_status'   => 'publish',
				'post_title'    => $data['title'],
				'post_content'  => $post_content,
				'post_author'   => $author,
				'meta_input'    => [
					'fmwp_visibility'       => $data['visibility'],
					'fmwp_original_content' => $orig_content,
				],
			];

			$args = apply_filters( 'fmwp_create_forum_args', $args, $data );

			$forum_id = wp_insert_post( $args );

			if ( ! is_wp_error( $forum_id ) ) {
				if ( FMWP()->options()->get( 'forum_categories' ) ) {
					if ( ! empty( $data['categories'] ) ) {
						if ( ! is_array( $data['categories'] ) ) {
							$list = explode( ',', trim( $data['categories'], ', ' ) );
							$list = array_map( 'trim', $list );
							if ( is_array( $list ) ) {
								$data['categories'] = array_filter( $list );
							}
						} else {
							$data['categories'] = array_filter( $data['categories'] );
						}

						$ids = [];
						foreach ( $data['categories'] as $name ) {
							$name = sanitize_text_field( $name );
							$term = get_term_by( 'name', $name, 'fmwp_forum_category' );
							if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
								$ids[] = (int) $term->term_id;
							}
						}

						wp_set_post_terms( $forum_id, $ids, 'fmwp_forum_category' );
					}
				}

				do_action( 'fmwp_forum_create_completed', $forum_id, $data );
			}

			return $forum_id;
		}
	}
}