<?php
namespace fmwp\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\common\Topic' ) ) {


	/**
	 * Class Topic
	 *
	 * @package fmwp\common
	 */
	final class Topic extends Post {


		/**
		 * @var array
		 */
		var $statuses = [];


		/**
		 * @var array
		 */
		var $types = [];


		/**
		 * @var array
		 */
		var $status_markers = [];


		var $already_filtered = false;


		/**
		 * @var array
		 */
		var $sort_by = [];


		var $post_status = [];


		/**
		 * Rewrite constructor.
		 */
		function __construct() {
			parent::__construct();

			add_action( 'forumwp_init', [ &$this, 'init_variables' ], 9 );

			add_action( 'save_post_fmwp_topic', [ &$this, 'save_post' ], 999997, 3 );

			add_action( 'wp_head', [ &$this, 'views_increment' ] );
			add_action( 'template_redirect', [ &$this, 'cookies_for_views' ] );

			add_filter( 'the_posts', [ &$this, 'filter_topics_from_hidden_forums' ], 99, 2 );

			add_filter( 'posts_where' , [ &$this, 'filter_pending_for_author' ], 10, 2 );

			add_action( 'init', [ &$this, 'init_statuses' ], 10 );
		}


		/**
		 * Set statuses
		 */
		function init_statuses() {
			$this->post_status = [ 'publish' ];
			if ( is_user_logged_in() ) {
				$this->post_status[] = 'pending'; //pending can be visible for author
				if ( current_user_can( 'manage_fmwp_topics_all' ) ) {
					$this->post_status[] = 'private';
				}
			}
		}


		/**
		 * @param $where
		 * @param $wp_query
		 *
		 * @return mixed
		 */
		function filter_pending_for_author( $where, $wp_query ) {
			if ( isset( $wp_query->query['post_type'] ) && $wp_query->query['post_type'] == 'fmwp_topic' ) {
				if ( isset( $wp_query->query['post_status'] ) && ( 'pending' == $wp_query->query['post_status'] || ( is_array( $wp_query->query['post_status'] ) && in_array( 'pending', $wp_query->query['post_status'] ) ) ) ) {
					global $wpdb;
					if ( ! current_user_can( 'manage_fmwp_topics_all' ) ) {
						$where = str_replace( "{$wpdb->posts}.post_status = 'pending'", "( {$wpdb->posts}.post_status = 'pending' AND {$wpdb->posts}.post_author = '" . get_current_user_id() . "' )", $where );
					}
				}
			}

			return $where;
		}


		/**
		 * Make invisible topics from trashed forums
		 *
		 * @param $posts
		 * @param $query
		 *
		 * @return array
		 */
		function filter_topics_from_hidden_forums( $posts, $query ) {
			if ( FMWP()->is_request( 'admin' ) && ! FMWP()->is_request( 'ajax' ) ) {
				return $posts;
			}

			$filtered_posts = [];

			//if empty
			if ( empty( $posts ) ) {
				return $posts;
			}

			foreach ( $posts as $post ) {

				if ( $post->post_type != 'fmwp_topic' ) {
					$filtered_posts[] = $post;
					continue;
				}

				if ( ! current_user_can( 'manage_fmwp_topics_all' ) ) {
					if ( $this->is_spam( $post ) ) {
						continue;
					}
				}

				$forum_id = $this->get_forum_id( $post->ID );
				$forum = get_post( $forum_id );

				if ( in_array( $forum->post_status, [ 'private', 'pending' ] ) && current_user_can( 'manage_fmwp_forums_all' ) ) {
					$filtered_posts[] = $post;
					continue;
				} elseif ( $forum->post_status == 'publish' ) {
					if ( current_user_can( 'manage_fmwp_forums_all' ) ) {
						$filtered_posts[] = $post;
						continue;
					} else {
						$visibility = get_post_meta( $forum_id, 'fmwp_visibility', true );
						if ( $visibility == 'public' ) {
							$filtered_posts[] = $post;
							continue;
						}
					}
				}
			}
			$posts = $filtered_posts;
			return $posts;
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

			if ( $post->post_type == 'fmwp_topic' ) {
				$is_locked = get_post_meta( $post->ID, 'fmwp_locked', true );
				$locked = ! empty( $is_locked );
			}

			return $locked;
		}


		/**
		 * @param int|\WP_Post $post
		 *
		 * @return bool
		 */
		function is_spam( $post ) {
			$spam = false;
			if ( is_numeric( $post ) ) {
				$post = get_post( $post );

				if ( empty( $post ) || is_wp_error( $post ) ) {
					return $spam;
				}
			}

			if ( $post->post_type == 'fmwp_topic' ) {
				$is_spam = get_post_meta( $post->ID, 'fmwp_spam', true );
				$spam = ! empty( $is_spam );
			}

			return $spam;
		}


		/**
		 *
		 */
		function init_variables() {
			$this->statuses = apply_filters( 'fmwp_topic_statuses', [
				'publish'   => __( 'Open', 'forumwp' ),
				'pending'   => __( 'Pending', 'forumwp' ),
				'trash'     => __( 'Trash', 'forumwp' ),
			] );

			$this->types = [
				'normal'        => [
					'title' => __( 'Normal', 'forumwp' ),
					'order' => 4,
				],
				'pinned'        => [
					'title' => __( 'Pinned', 'forumwp' ),
					'order' => 2,
				],
				'announcement'  => [
					'title' => __( 'Announcement', 'forumwp' ),
					'order' => 3,
				],
				'global'        => [
					'title' => __( 'Global', 'forumwp' ),
					'order' => 1,
				],
			];


			$this->status_markers = apply_filters( 'fmwp_topic_status_markers', [
				'fmwp-topic-locked-marker'          => [
					'icon'  => 'fas fa-lock',
					'title' => __( 'Locked', 'forumwp' ),
				],
				'fmwp-topic-pinned-marker'          => [
					'icon'  => 'fas fa-thumbtack',
					'title' => __( 'Pinned', 'forumwp' ),
				],
				'fmwp-topic-announcement-marker'    => [
					'icon'  => 'fas fa-bullhorn',
					'title' => __( 'Announcement', 'forumwp' ),
				],
				'fmwp-topic-global-marker'          => [
					'icon'  => 'fas fa-globe-americas',
					'title' => __( 'Global', 'forumwp' ),
				],
			] );


			$this->sort_by = apply_filters( 'fmwp_topics_sorting', [
				'date_asc'      => __( 'Oldest to Newest', 'forumwp' ),
				'date_desc'     => __( 'Newest to Oldest', 'forumwp' ),
				'update_desc'   => __( 'Recently updated', 'forumwp' ),
				'views_desc'    => __( 'Most views', 'forumwp' ),
//				'replies_desc'  => __( 'Most replies', 'forumwp' ),
			] );
		}


		/**
		 *
		 */
		function views_increment() {
			if ( is_admin() ) {
				return;
			}

			if ( FMWP()->options()->get( 'ajax_increment_views' ) ) {
				return;
			}

			global $post;
			if ( is_int( $post ) ) {
				$post = get_post( $post );
			}
			if ( ! wp_is_post_revision( $post ) && ! is_preview() ) {
				if ( is_singular( 'fmwp_topic' ) ) {
					$id = (int) $post->ID;
					if ( ! $post_views = get_post_meta( $post->ID, 'fmwp_views', true ) ) {
						$post_views = 0;
					}

					$should_count = true;
					$bots = [
						'Google Bot'    => 'google',
						'MSN'           => 'msnbot',
						'Alex'          => 'ia_archiver',
						'Lycos'         => 'lycos',
						'Ask Jeeves'    => 'jeeves',
						'Altavista'     => 'scooter',
						'AllTheWeb'     => 'fast-webcrawler',
						'Inktomi'       => 'slurp@inktomi',
						'Turnitin.com'  => 'turnitinbot',
						'Technorati'    => 'technorati',
						'Yahoo'         => 'yahoo',
						'Findexa'       => 'findexa',
						'NextLinks'     => 'findlinks',
						'Gais'          => 'gaisbo',
						'WiseNut'       => 'zyborg',
						'WhoisSource'   => 'surveybot',
						'Bloglines'     => 'bloglines',
						'BlogSearch'    => 'blogsearch',
						'PubSub'        => 'pubsub',
						'Syndic8'       => 'syndic8',
						'RadioUserland' => 'userland',
						'Gigabot'       => 'gigabot',
						'Become.com'    => 'become.com',
						'Baidu'         => 'baiduspider',
						'so.com'        => '360spider',
						'Sogou'         => 'spider',
						'soso.com'      => 'sosospider',
						'Yandex'        => 'yandex',
					];
					$useragent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
					foreach ( $bots as $name => $lookfor ) {
						if ( ! empty( $useragent ) && ( false !== stripos( $useragent, $lookfor ) ) ) {
							$should_count = false;
							break;
						}
					}

					if ( ! empty( $_COOKIE['fmwp_topic_views'] ) ) {
						$views = maybe_unserialize( $_COOKIE['fmwp_topic_views'] );

						if ( is_array( $views ) && in_array( $id, $views ) ) {
							$should_count = false;
						}
					}

					if ( $should_count ) {
						update_post_meta( $id, 'fmwp_views', $post_views + 1 );
					}
				}
			}
		}


		/**
		 *
		 */
		function cookies_for_views() {
			if ( FMWP()->options()->get( 'ajax_increment_views' ) ) {
				return;
			}

			global $post;
			if ( is_int( $post ) ) {
				$post = get_post( $post );
			}
			if ( ! wp_is_post_revision( $post ) && ! is_preview() ) {
				if ( is_singular( 'fmwp_topic' ) ) {
					$id = (int) $post->ID;

					$views = [];
					if ( ! empty( $_COOKIE['fmwp_topic_views'] ) ) {
						$views = maybe_unserialize( $_COOKIE['fmwp_topic_views'] );
					}
					$views[] = $id;
					$views = array_unique( $views );

					setcookie( 'fmwp_topic_views', serialize( $views ), time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
				}
			}
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

			if ( $post->post_status == 'auto-draft' ) {
				return;
			}

			$upgrade_last_update = apply_filters( 'fmwp_topic_upgrade_last_update', true, $post_ID );

			if ( $upgrade_last_update ) {
				update_post_meta( $post_ID, 'fmwp_last_update', time() );
				$forum_id = $this->get_forum_id( $post_ID );
				if ( ! empty( $forum_id ) ) {
					update_post_meta( $forum_id, 'fmwp_last_update', time() );
				}
			}

			if ( $update ) {
				return;
			}

			update_user_meta( $post->post_author, 'fmwp_latest_topic_date', time() );

			update_post_meta( $post_ID, 'fmwp_views', 0 );
		}


		/**
		 * @param int|\WP_Post $topic
		 *
		 * @return bool|null
		 */
		function is_pinned( $topic ) {
			if ( is_numeric( $topic ) ) {
				$topic = get_post( $topic );

				if ( empty( $topic ) || is_wp_error( $topic ) ) {
					return null;
				}
			}

			$type = get_post_meta( $topic->ID, 'fmwp_type', true );
			$type = ! empty( $type ) ? $type : 'normal';

			return $type === 'pinned';
		}


		/**
		 * @param int|\WP_Post $topic
		 *
		 * @return bool|null
		 */
		function is_announcement( $topic ) {
			if ( is_numeric( $topic ) ) {
				$topic = get_post( $topic );

				if ( empty( $topic ) || is_wp_error( $topic ) ) {
					return null;
				}
			}

			$type = get_post_meta( $topic->ID, 'fmwp_type', true );
			$type = ! empty( $type ) ? $type : 'normal';

			return $type === 'announcement';
		}


		/**
		 * @param int|\WP_Post $topic
		 *
		 * @return bool|null
		 */
		function is_global( $topic ) {
			if ( is_numeric( $topic ) ) {
				$topic = get_post( $topic );

				if ( empty( $topic ) || is_wp_error( $topic ) ) {
					return null;
				}
			}

			$type = get_post_meta( $topic->ID, 'fmwp_type', true );
			$type = ! empty( $type ) ? $type : 'normal';

			return $type === 'global';
		}


		function status_tags() {
			$tags = [];
			if ( is_user_logged_in() ) {
				if ( current_user_can( 'manage_fmwp_topics_all' ) ) {
					$tags['trashed'] = __( 'Trashed', 'forumwp' );
					$tags['spam'] = __( 'Spam', 'forumwp' );
				}
				$tags['reported'] = __( 'Reported', 'forumwp' );
				$tags['pending'] = __( 'Pending', 'forumwp' );
			}

			$statuses = apply_filters( 'fmwp_topic_status_tags', $tags );

			return $statuses;
		}


		/**
		 *
		 * @param \WP_Post $topic
		 * @param int|bool $user_id
		 *
		 * @return array
		 */
		function actions_list( $topic, $user_id = false ) {
			//Topic dropdown actions
			$items = [];

			if ( ! $user_id ) {
				if ( is_user_logged_in() ) {
					$user_id = get_current_user_id();
				} else {
					return $items;
				}
			}

			if ( FMWP()->user()->can_edit_topic( $user_id, $topic ) ) {
				$items = array_merge( $items, [
					'fmwp-edit-topic'   => __( 'Edit topic', 'forumwp' ),
				] );
			}

			if ( FMWP()->user()->can_pin_topic( $user_id, $topic ) ) {
				$items = array_merge( $items, [
					'fmwp-pin-topic'    => __( 'Pin topic', 'forumwp' ),
				] );
			}

			if ( FMWP()->user()->can_unpin_topic( $user_id, $topic ) ) {
				$items = array_merge( $items, [
					'fmwp-unpin-topic'  => __( 'Unpin topic', 'forumwp' ),
				] );
			}

			if ( FMWP()->user()->can_lock_topic( $user_id, $topic ) ) {
				$items = array_merge( $items, [
					'fmwp-lock-topic'   => __( 'Lock topic', 'forumwp' ),
				] );
			}

			if ( FMWP()->user()->can_unlock_topic( $user_id, $topic ) ) {
				$items = array_merge( $items, [
					'fmwp-unlock-topic' => __( 'Unlock topic', 'forumwp' ),
				] );
			}

			if ( FMWP()->user()->can_trash_topic( $user_id, $topic ) ) {
				$items = array_merge( $items, [
					'fmwp-trash-topic'  => __( 'Move to trash', 'forumwp' ),
				] );
			}

			if ( FMWP()->user()->can_spam_topic( $user_id, $topic ) ) {
				$items = array_merge( $items, [
					'fmwp-mark-spam-topic' => __( 'Mark as spam', 'forumwp' ),
				] );
			}

			if ( FMWP()->user()->can_restore_spam_topic( $user_id, $topic ) ) {
				$items = array_merge( $items, [
					'fmwp-restore-spam-topic' => __( 'Isn\'t spam', 'forumwp' ),
				] );
			}

			if ( $user_id != $topic->post_author ) {
				if ( ! FMWP()->reports()->is_reported_by_user( $topic->ID, $user_id ) ) {
					$items = array_merge( $items, [
						'fmwp-report-topic' => __( 'Report topic', 'forumwp' ),
					] );
				} else {
					$items = array_merge( $items, [
						'fmwp-unreport-topic' => __( 'Un-report topic', 'forumwp' ),
					] );
				}
			}

			if ( FMWP()->reports()->is_reported( $topic->ID ) && FMWP()->user()->can_clear_reports( $user_id ) ) {
				$items = array_merge( $items, [
					'fmwp-clear-reports-topic' => __( 'Clear topic\'s reports', 'forumwp' ),
				] );
			}

			$items = apply_filters( 'fmwp_topic_dropdown_actions', $items, $user_id, $topic );

			$items = array_unique( $items );

			return $items;
		}


		/**
		 * @param array $data
		 *
		 * @return int
		 */
		function edit( $data ) {
			list( $orig_content, $post_content ) = $this->prepare_content( $data['content'], 'fmwp_topic' );

			$args = [
				'ID'            => $data['topic_id'],
				'post_title'    => $data['title'],
				'post_content'  => $post_content,
				'meta_input'    => [
					'fmwp_original_content' => $orig_content,
				],
			];

			$args = apply_filters( 'fmwp_edit_topic_args', $args );

			$topic_id = wp_update_post( $args );

			if ( ! is_wp_error( $topic_id ) ) {

				if ( FMWP()->options()->get( 'topic_tags' ) ) {
					if ( ! empty( $data['tags'] ) ) {

						$terms = $this->get_tags( $topic_id );

						$terms_ids = [];
						foreach ( $terms as $term ) {
							$terms_ids[] = $term->term_id;
						}

						wp_remove_object_terms( $topic_id, $terms_ids, 'fmwp_topic_tag' );

						if ( ! is_array( $data['tags'] ) ) {
							$list = explode( ',', trim( $data['tags'], ', ' ) );
							$list = array_map( 'trim', $list );
							if ( is_array( $list ) ) {
								$data['tags'] = array_filter( $list );
							}
						} else {
							$data['tags'] = array_filter( $data['tags'] );
						}

						$ids = [];
						foreach ( $data['tags'] as $name ) {
							$name = sanitize_text_field( $name );
							$term = get_term_by( 'name', $name, 'fmwp_topic_tag' );
							if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
								$ids[] = $term->term_id;
							} else {
								$term = wp_insert_term( $name, 'fmwp_topic_tag' );
								if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
									$ids[] = $term['term_id'];
								}
							}
						}

						wp_set_post_terms( $topic_id, $ids, 'fmwp_topic_tag' );
					} else {
						$terms = $this->get_tags( $topic_id );
						$terms_ids = [];
						foreach ( $terms as $term ) {
							$terms_ids[] = $term->term_id;
						}
						wp_remove_object_terms( $topic_id, $terms_ids, 'fmwp_topic_tag' );
					}
				}

				do_action( 'fmwp_topic_edit_completed', $topic_id, $data );
			}

			return $topic_id;
		}


		/**
		 * Create Topic
		 *
		 * @param array $data
		 *
		 * @return int
		 */
		function create( $data ) {
			$author = ! empty( $data['author_id'] ) ? $data['author_id'] : get_current_user_id();

			list( $orig_content, $post_content ) = $this->prepare_content( $data['content'], 'fmwp_topic' );

			$args = [
				'post_type'     => 'fmwp_topic',
				'post_status'   => 'publish',
				'post_title'    => $data['title'],
				'post_content'  => $post_content,
				'post_author'   => $author,
				'meta_input'    => [
					'fmwp_original_content' => $orig_content,
					'fmwp_forum'            => $data['forum_id'],
					'fmwp_type'             => $data['type'],
					'fmwp_type_order'       => $this->types[ $data['type'] ]['order'],
				],
			];

			$args = apply_filters( 'fmwp_create_topic_args', $args );

			$topic_id = wp_insert_post( $args );

			if ( ! is_wp_error( $topic_id ) ) {

				if ( ! empty( $data['tags'] ) ) {
					if ( ! is_array( $data['tags'] ) ) {
						$list = explode( ',', trim( $data['tags'], ', ' ) );
						$list = array_map( 'trim', $list );
						if ( is_array( $list ) ) {
							$data['tags'] = array_filter( $list );
						}
					} else {
						$data['tags'] = array_filter( $data['tags'] );
					}

					$ids = [];
					foreach ( $data['tags'] as $name ) {
						$name = sanitize_text_field( $name );
						$term = get_term_by( 'name', $name, 'fmwp_topic_tag' );
						if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
							$ids[] = $term->term_id;
						} else {
							$term = wp_insert_term( $name, 'fmwp_topic_tag' );
							if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
								$ids[] = $term['term_id'];
							}
						}
					}

					wp_set_post_terms( $topic_id, $ids, 'fmwp_topic_tag' );
				}

				do_action( 'fmwp_topic_create_completed', $topic_id, $data );
			}

			return $topic_id;
		}


		/**
		 * @param $topic_id
		 *
		 * @return bool|int
		 */
		function get_forum_id( $topic_id ) {
			$forum_id = get_post_meta( $topic_id, 'fmwp_forum', true );
			if ( empty( $forum_id ) ) {
				$forum_id = false;
			}
			return (int) $forum_id;
		}


		/**
		 * @param int $topic_id
		 * @param string $key
		 *
		 * @return int|array
		 */
		function get_statistics( $topic_id, $key = 'all' ) {
			$stats = [];

			switch ( $key ) {
				default:
					$stats = apply_filters( 'fmwp_calculate_topic_stats', 0, $topic_id, $key );
					break;
				case 'replies':
					if ( post_password_required( $topic_id ) ) {
						$stats = 0;
					} else {
						$args = [
							'post_type'         => 'fmwp_reply',
							'posts_per_page'    => -1,
							'post_status'       => FMWP()->common()->reply()->post_status,
							'meta_key'          => 'fmwp_topic',
							'meta_value'        => $topic_id,
							'fields'            => 'ids',
						];

						$args['suppress_filters'] = false;

						$args = apply_filters( 'fmwp_ajax_get_replies_args', $args, $topic_id );

						$replies = get_posts( $args );

						$stats = ( ! empty( $replies ) && ! is_wp_error( $replies ) ) ? count( $replies ) : 0;
					}
					break;
				case 'views':
					if ( post_password_required( $topic_id ) ) {
						$stats = 0;
					} else {
						$views = get_post_meta( $topic_id, 'fmwp_views', true );
						if ( empty( $views ) ) {
							$views = 0;
						}

						$stats = $views;
					}
					break;
				case 'all':
					$keys = apply_filters( 'fmwp_topic_statistic_keys', [
						'replies',
						'views',
					] );
					foreach ( $keys as $attr ) {
						$stats[ $attr ] = $this->get_statistics( $topic_id, $attr );
					}

					break;
			}

			return $stats;
		}


		/**
		 * Spam Topic handler
		 *
		 * @param $topic_id
		 */
		function spam( $topic_id ) {
			$post = get_post( $topic_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( $this->is_spam( $post ) ) {
				return;
			}

			update_post_meta( $post->ID, 'fmwp_spam', true );

			do_action( 'fmwp_after_spam_topic', $topic_id );
		}


		/**
		 * Restore from Trash Topic handler
		 *
		 * @param $topic_id
		 */
		function restore_spam( $topic_id ) {
			$post = get_post( $topic_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( ! $this->is_spam( $post ) ) {
				return;
			}

			update_post_meta( $post->ID, 'fmwp_spam', false );

			do_action( 'fmwp_after_restore_spam_topic', $topic_id );
		}


		/**
		 * Delete Topic handler
		 *
		 * @param $topic_id
		 */
		function move_to_trash( $topic_id ) {
			$post = get_post( $topic_id );

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

			do_action( 'fmwp_after_trash_topic', $topic_id );
		}


		/**
		 * Restore from Trash Topic handler
		 *
		 * @param $topic_id
		 */
		function restore( $topic_id ) {
			$post = get_post( $topic_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( $post->post_status !== 'trash' ) {
				return;
			}

			$prev_status = get_post_meta( $post->ID, 'fmwp_prev_status', true );
			if ( empty( $prev_status ) ) {
				$prev_status = 'publish';
			}

			wp_update_post( [
				'ID'            => $post->ID,
				'post_status'   => $prev_status,
			] );

			delete_post_meta( $post->ID, 'fmwp_prev_status' );

			do_action( 'fmwp_after_restore_topic', $topic_id );
		}


		/**
		 * @param int $user_id
		 * @param array $args
		 *
		 * @return array
		 */
		function get_topics_by_author( $user_id, $args = [] ) {
			$query_args = [
				'post_type'         => 'fmwp_forum',
				'post_status'       => FMWP()->common()->forum()->post_status,
				'posts_per_page'    => -1,
				'fields'            => 'ids',
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

			$forum_ids = get_posts( $query_args );

			if ( empty( $forum_ids ) ) {
				return [];
			} else {
				foreach ( $forum_ids as $k => $forum_id ) {
					if ( post_password_required( $forum_id ) ) {
						unset( $forum_ids[ $k ] );
					}
				}

				$forum_ids = array_values( $forum_ids );

				if ( empty( $forum_ids ) ) {
					return [];
				}
			}

			$args = array_merge( [
				'post_type'         => 'fmwp_topic',
				'posts_per_page'    => -1,
				'post_status'       => FMWP()->common()->topic()->post_status,
				'author'            => $user_id,
				'order'             => 'desc',
				'meta_query' => [
					[
						'key'       => 'fmwp_forum',
						'value'     => $forum_ids,
						'compare'   => 'IN',
					],
				],
				'suppress_filters'  => false,
			], $args );

			$args = apply_filters( 'fmwp_get_topics_arguments', $args );

			$topics = get_posts( $args );

			if ( empty( $topics ) || is_wp_error( $topics ) ) {
				$topics = [];
			}

			return $topics;
		}


		/**
		 * @param \WP_Post $topic
		 *
		 * @return array
		 */
		function get_author_tags( $topic ) {
			$tags = [];

			if ( FMWP()->options()->get( 'reply_user_role' ) ) {
				global $wp_roles;
				$user = get_userdata( $topic->post_author );
				$user_roles = FMWP()->user()->get_roles( $user );

				if ( ! empty( $user_roles ) ) {
					foreach ( $user_roles as $role ) {
						$name = translate_user_role( $wp_roles->roles[ $role ]['name'] );
						$tags[] = [
							'title' => $name,
						];
					}
				}
			}

			return $tags;
		}


		/**
		 * @param $id
		 * @param bool $data
		 *
		 * @return array|\WP_Error
		 */
		function get_tags( $id, $data = false ) {

			$args = [
				'orderby'   => 'name',
				'order'     => 'ASC',
			];
			if ( $data === 'names' ) {
				$args['fields'] = 'names';
			}

			$terms = wp_get_post_terms(
				$id,
				'fmwp_topic_tag',
				$args
			);

			if ( empty( $data ) ) {
				if ( count( $terms ) ) {
					foreach ( $terms as $k => $tag ) {
						$terms[ $k ]->permalink = get_term_link( $tag->term_id, 'fmwp_topic_tag' );
					}
				}
			}

			return $terms;
		}


		/**
		 * @param $topic
		 * @return bool
		 */
		function is_reply_button_hidden( $topic ) {
			$hidden = false;

			$unlogged_class = FMWP()->frontend()->shortcodes()->unlogged_class();

			if ( $topic->post_status == 'publish' ) {

				if ( is_user_logged_in() ) {
					if ( FMWP()->user()->can_reply( $topic->ID ) ) { ?>
						<input type="button" class="fmwp-write-reply" title="<?php esc_attr_e( 'Reply', 'forumwp' ) ?>" value="<?php esc_attr_e( 'Reply', 'forumwp' ) ?>" />
					<?php } else {
						echo apply_filters( 'fmwp_reply_disabled_reply_text', '', $topic->ID );
					}
				} else { ?>
					<input type="button" class="<?php echo $unlogged_class ?>" title="<?php esc_attr_e( 'Reply', 'forumwp' ) ?>" value="<?php esc_attr_e( 'Reply', 'forumwp' ) ?>" data-fmwp_popup_title="<?php esc_attr_e( 'Login to reply to this topic', 'forumwp' ) ?>" />
				<?php }

			} else {

				_e( 'This topic is closed to new replies', 'forumwp' );

			}

			return $hidden;
		}


		/**
		 * @param \WP_Post $topic
		 */
		function pin( $topic ) {
			update_post_meta( $topic->ID, 'fmwp_type', 'pinned' );
			update_post_meta( $topic->ID, 'fmwp_type_order', $this->types['pinned']['order'] );
		}


		/**
		 * @param \WP_Post $topic
		 */
		function unpin( $topic ) {
			update_post_meta( $topic->ID, 'fmwp_type', 'normal' );
			update_post_meta( $topic->ID, 'fmwp_type_order', $this->types['normal']['order'] );
		}


		/**
		 * @param int $topic_id
		 */
		function lock( $topic_id ) {
			$post = get_post( $topic_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( $this->is_locked( $post ) ) {
				return;
			}

			update_post_meta( $topic_id, 'fmwp_locked', true );

			do_action( 'fmwp_after_lock_topic', $topic_id );
		}


		/**
		 * @param int $topic_id
		 */
		function unlock( $topic_id ) {
			$post = get_post( $topic_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return;
			}

			if ( ! $this->is_locked( $post ) ) {
				return;
			}

			update_post_meta( $topic_id, 'fmwp_locked', false );

			do_action( 'fmwp_after_unlock_topic', $topic_id );
		}


		/**
		 * Delete Topic handler
		 *
		 * @param $topic_id
		 */
		function delete( $topic_id ) {
			$topic = get_post( $topic_id );

			do_action( 'fmwp_before_delete_topic', $topic_id, $topic );

			if ( ! empty( $topic ) && ! is_wp_error( $topic ) ) {
				wp_delete_post( $topic_id );
				do_action( 'fmwp_after_delete_topic', $topic_id );
			}
		}
	}
}