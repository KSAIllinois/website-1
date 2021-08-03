<?php
namespace fmwp\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\admin\Columns' ) ) {


	/**
	 * Class Columns
	 *
	 * @package fmwp\admin
	 */
	class Columns {


		/**
		 * Columns constructor.
		 */
		function __construct() {
			add_filter( 'display_post_states', [ &$this, 'add_display_post_states' ], 10, 2 );

			add_filter( 'manage_edit-fmwp_forum_columns', [ &$this, 'forum_columns' ] );
			add_action( 'manage_fmwp_forum_posts_custom_column', [ &$this, 'forum_columns_content' ], 10, 3 );

			add_filter( 'manage_edit-fmwp_forum_category_columns', [ &$this, 'forum_category_columns' ] );
			add_action( 'manage_fmwp_forum_category_custom_column', [ &$this, 'forum_category_columns_content' ], 10, 3 );

			add_filter( 'manage_edit-fmwp_topic_tag_columns', [ &$this, 'topic_tag_columns' ] );
			add_action( 'manage_fmwp_topic_tag_custom_column', [ &$this, 'topic_tag_columns_content' ], 10, 3 );

			add_filter( 'manage_edit-fmwp_topic_columns', [ &$this, 'topic_columns' ] );
			add_action( 'manage_fmwp_topic_posts_custom_column', [ &$this, 'topic_columns_content' ], 10, 3 );

			add_action( 'pre_get_posts', [ &$this, 'remove_post_status_all_edit_flow' ], 10, 1 );

			add_action( 'pre_get_posts', [ &$this, 'reported_posts' ], 10, 1 );

			add_filter( 'views_edit-fmwp_reply', [ &$this, 'add_reported_reply_folder' ], 10, 1 );
			add_filter( 'views_edit-fmwp_topic', [ &$this, 'add_reported_topic_folder' ], 10, 1 );
			add_filter( 'post_row_actions', [ &$this, 'row_actions' ], 10, 2 );

			add_filter( 'views_edit-fmwp_forum', [ $this, 'add_locked_forum_folder' ], 10, 1 );
			add_filter( 'views_edit-fmwp_topic', [ $this, 'add_locked_topic_folder' ], 10, 1 );
			add_action( 'pre_get_posts', [ &$this, 'locked_posts' ], 10, 1 );

			add_filter( 'views_edit-fmwp_reply', [ $this, 'add_spam_reply_folder' ], 10, 1 );
			add_filter( 'views_edit-fmwp_topic', [ $this, 'add_spam_topic_folder' ], 10, 1 );
			add_action( 'pre_get_posts', [ &$this, 'spam_posts' ], 10, 1 );
		}


		function add_reported_topic_folder( $views ) {
			$reported_topics = FMWP()->reports()->get_all_reports_count( 'fmwp_topic' );
			$current = isset( $_GET['post_status'] ) && 'fmwp_reported' == $_GET['post_status'];
			if ( ! empty( $reported_topics ) ) {
				$views['fmwp_reported'] = '<a href="edit.php?post_type=fmwp_topic&post_status=fmwp_reported" ' . ( $current ? 'class="current"' : '' ) . '>' . __( 'Reported', 'forumwp' ) . ' <span class="count">(' . $reported_topics . ')</span></a>';
			}
			return $views;
		}


		function add_reported_reply_folder( $views ) {
			$reported_replies = FMWP()->reports()->get_all_reports_count( 'fmwp_reply' );
			$current = isset( $_GET['post_status'] ) && 'fmwp_reported' == $_GET['post_status'];
			if ( ! empty( $reported_replies ) ) {
				$views['fmwp_reported'] = '<a href="edit.php?post_type=fmwp_reply&post_status=fmwp_reported" ' . ( $current ? 'class="current"' : '' ) . '>' . __( 'Reported', 'forumwp' ) . ' <span class="count">(' . $reported_replies . ')</span></a>';
			}
			return $views;
		}


		/**
		 * @param array $views
		 *
		 * @return array
		 */
		function add_locked_forum_folder( $views ) {
			$locked_forums = get_posts( [
				'post_type'         => 'fmwp_forum',
				'posts_per_page'    => -1,
				'meta_query'        => [
					[
						'key'       => 'fmwp_locked',
						'value'     => true,
						'compare'   => '=',
					]
				],
				'fields'            => 'ids',
			] );

			if ( ! empty( $locked_forums ) && ! is_wp_error( $locked_forums ) ) {
				$locked_forums = count( $locked_forums );
			} else {
				return $views;
			}

			$current = isset( $_GET['post_status'] ) && 'fmwp_locked' == $_GET['post_status'];
			$views['fmwp_locked'] = '<a href="edit.php?post_type=fmwp_forum&post_status=fmwp_locked" ' . ( $current ? 'class="current"' : '' ) . '>' . __( 'Locked', 'forumwp' ) . ' <span class="count">(' . $locked_forums . ')</span></a>';

			return $views;
		}


		/**
		 * @param array $views
		 *
		 * @return array
		 */
		function add_locked_topic_folder( $views ) {
			$locked_topics = get_posts( [
				'post_type'         => 'fmwp_topic',
				'posts_per_page'    => -1,
				'meta_query'        => [
					[
						'key'       => 'fmwp_locked',
						'value'     => true,
						'compare'   => '=',
					]
				],
				'fields'            => 'ids',
			] );

			if ( ! empty( $locked_topics ) && ! is_wp_error( $locked_topics ) ) {
				$locked_topics = count( $locked_topics );
			} else {
				return $views;
			}

			$current = isset( $_GET['post_status'] ) && 'fmwp_locked' == $_GET['post_status'];
			$views['fmwp_locked'] = '<a href="edit.php?post_type=fmwp_topic&post_status=fmwp_locked" ' . ( $current ? 'class="current"' : '' ) . '>' . __( 'Locked', 'forumwp' ) . ' <span class="count">(' . $locked_topics . ')</span></a>';

			return $views;
		}


		/**
		 * @param array $views
		 *
		 * @return array
		 */
		function add_spam_reply_folder( $views ) {
			$spam_replies = get_posts( [
				'post_type'         => 'fmwp_reply',
				'posts_per_page'    => -1,
				'meta_query'        => [
					[
						'key'       => 'fmwp_spam',
						'value'     => true,
						'compare'   => '=',
					]
				],
				'fields'            => 'ids',
			] );

			if ( ! empty( $spam_replies ) && ! is_wp_error( $spam_replies ) ) {
				$spam_replies = count( $spam_replies );
			} else {
				return $views;
			}

			$current = isset( $_GET['post_status'] ) && 'fmwp_spam' == $_GET['post_status'];
			$views['fmwp_spam'] = '<a href="edit.php?post_type=fmwp_reply&post_status=fmwp_spam" ' . ( $current ? 'class="current"' : '' ) . '>' . __( 'Spam', 'forumwp' ) . ' <span class="count">(' . $spam_replies . ')</span></a>';

			return $views;
		}


		/**
		 * @param array $views
		 *
		 * @return array
		 */
		function add_spam_topic_folder( $views ) {
			$spam_topics = get_posts( [
				'post_type'         => 'fmwp_topic',
				'posts_per_page'    => -1,
				'meta_query'        => [
					[
						'key'       => 'fmwp_spam',
						'value'     => true,
						'compare'   => '=',
					]
				],
				'fields'            => 'ids',
			] );

			if ( ! empty( $spam_topics ) && ! is_wp_error( $spam_topics ) ) {
				$spam_topics = count( $spam_topics );
			} else {
				return $views;
			}

			$current = isset( $_GET['post_status'] ) && 'fmwp_spam' == $_GET['post_status'];
			$views['fmwp_spam'] = '<a href="edit.php?post_type=fmwp_topic&post_status=fmwp_spam" ' . ( $current ? 'class="current"' : '' ) . '>' . __( 'Spam', 'forumwp' ) . ' <span class="count">(' . $spam_topics . ')</span></a>';

			return $views;
		}


		/**
		 * @param $query
		 */
		function locked_posts( $query ) {
			global $pagenow;

			if ( ! $query->is_admin ) {
				return;
			}

			if ( $pagenow !== 'edit.php' ) {
				return;
			}

			if ( $query->query['post_type'] !== 'fmwp_forum' && $query->query['post_type'] !== 'fmwp_topic' ) {
				return;
			}

			if ( isset( $_GET['post_status'] ) && 'fmwp_locked' == $_GET['post_status'] ) {
				if ( ! isset( $query->query_vars['meta_query'] ) ) {
					$query->query_vars['meta_query'] = [];
					$query->query_vars['meta_query']['relation'] = 'AND';
				}

				if ( ! isset( $query->query['meta_query'] ) ) {
					$query->query['meta_query'] = [];
					$query->query['meta_query']['relation'] = 'AND';
				}

				$query->query_vars['meta_query'][] = [
					'key'       => 'fmwp_locked',
					'value'     => true,
					'compare'   => '=',
				];

				$query->query['meta_query'][] = [
					'key'       => 'fmwp_locked',
					'value'     => true,
					'compare'   => '=',
				];
			}
		}


		/**
		 * @param $query
		 */
		function spam_posts( $query ) {
			global $pagenow;

			if ( ! $query->is_admin ) {
				return;
			}

			if ( $pagenow !== 'edit.php' ) {
				return;
			}

			if ( $query->query['post_type'] !== 'fmwp_reply' && $query->query['post_type'] !== 'fmwp_topic' ) {
				return;
			}

			if ( isset( $_GET['post_status'] ) && 'fmwp_spam' == $_GET['post_status'] ) {
				if ( ! isset( $query->query_vars['meta_query'] ) ) {
					$query->query_vars['meta_query'] = [];
					$query->query_vars['meta_query']['relation'] = 'AND';
				}

				if ( ! isset( $query->query['meta_query'] ) ) {
					$query->query['meta_query'] = [];
					$query->query['meta_query']['relation'] = 'AND';
				}

				$query->query_vars['meta_query'][] = [
					'key'       => 'fmwp_spam',
					'value'     => true,
					'compare'   => '=',
				];

				$query->query['meta_query'][] = [
					'key'       => 'fmwp_spam',
					'value'     => true,
					'compare'   => '=',
				];
			}
		}


		/**
		 * @param array $actions
		 * @param $post
		 *
		 * @return array
		 */
		function row_actions( $actions, $post ) {
			if ( $post->post_type == 'fmwp_reply' || $post->post_type == 'fmwp_topic' ) {
				if ( FMWP()->reports()->is_reported( $post->ID ) ) {
					$url = add_query_arg( [ 'fmwp_adm_action' => 'clear_reports', 'post_id' => $post->ID, '_wpnonce' => wp_create_nonce( 'fmwp_clear_reports' . $post->ID ) ] );
					$confirm = 'return confirm("' . __( 'Are you sure?', 'forumwp' ) . '") ? true : false;';
					$actions['fmwp_clear_reports'] = '<a href="' . $url . '" onclick="' . esc_attr( $confirm ) . '">' . __( 'Clear Reports', 'forumwp' ) . '</a>';
				}
			}

			return $actions;
		}


		function remove_post_status_all_edit_flow( $query ) {
			global $pagenow;

			if ( ! $query->is_admin ) {
				return;
			}

			if ( $pagenow !== 'edit.php' ) {
				return;
			}

			if ( $query->query['post_type'] !== 'fmwp_reply' && $query->query['post_type'] !== 'fmwp_topic' ) {
				return;
			}

			if ( $query->query['post_status'] == 'all' || $query->query['post_status'] == '' ) {
				$query->query_vars['post_status'] = 'any';
				$query->query['post_status'] = 'any';
			}
		}


		function reported_posts( $query ) {
			global $pagenow;

			if ( ! $query->is_admin ) {
				return;
			}

			if ( $pagenow !== 'edit.php' ) {
				return;
			}

			if ( $query->query['post_type'] !== 'fmwp_reply' && $query->query['post_type'] !== 'fmwp_topic' ) {
				return;
			}

			if ( isset( $_GET['post_status'] ) && 'fmwp_reported' == $_GET['post_status'] ) {
				$query->query_vars['post_status'] = [ 'any', 'trash' ];
				$query->query['post_status'] = [ 'any', 'trash' ];

				$post_ids = FMWP()->reports()->get_post_id_reports( $query->query['post_type'] );

				$query->query_vars['post__in'] = $post_ids;
				$query->query['post__in'] = $post_ids;
			}
		}


		/**
		 * Add a post display state for special ForumWP pages in the page list table.
		 *
		 * @param array $post_states An array of post display states.
		 * @param \WP_Post $post The current post object.
		 *
		 * @return mixed
		 */
		public function add_display_post_states( $post_states, $post ) {
			if ( $post->post_type == 'page' ) {
				foreach ( FMWP()->config()->get( 'core_pages' ) as $page_key => $page_value ) {
					if ( FMWP()->options()->get( $page_key . '_page' ) == $post->ID ) {
						$post_states[ 'fmwp_page_' . $page_key ] = sprintf( 'ForumWP %s', $page_value['title'] );
					}
				}
			} elseif ( $post->post_type == 'fmwp_forum' ) {
				if ( FMWP()->common()->forum()->is_locked( $post ) && ( ! isset( $_GET['post_status'] ) || 'fmwp_locked' != $_GET['post_status'] ) ) {
					$post_states['fmwp_locked'] = __( 'Locked', 'forumwp' );
				}
			} elseif ( $post->post_type == 'fmwp_topic' ) {
				if ( FMWP()->common()->topic()->is_locked( $post ) && ( ! isset( $_GET['post_status'] ) || 'fmwp_locked' != $_GET['post_status'] ) ) {
					$post_states['fmwp_locked'] = __( 'Locked', 'forumwp' );
				}

				if ( FMWP()->common()->topic()->is_spam( $post ) && ( ! isset( $_GET['post_status'] ) || 'fmwp_spam' != $_GET['post_status'] ) ) {
					$post_states['fmwp_status'] = __( 'Spam', 'forumwp' );
				}

				if ( ! isset( $_GET['post_status'] ) || 'fmwp_reported' != $_GET['post_status'] ) {
					if ( FMWP()->reports()->is_reported( $post->ID ) ) {
						$post_states['fmwp_reported'] = __( 'Reported', 'forumwp' );
					}
				}
			} elseif ( $post->post_type == 'fmwp_reply' ) {
				if ( FMWP()->common()->reply()->is_spam( $post ) && ( ! isset( $_GET['post_status'] ) || 'fmwp_spam' != $_GET['post_status'] ) ) {
					$post_states['fmwp_status'] = __( 'Spam', 'forumwp' );
				}

				if ( ! isset( $_GET['post_status'] ) || 'fmwp_reported' != $_GET['post_status'] ) {
					if ( FMWP()->reports()->is_reported( $post->ID ) ) {
						$post_states['fmwp_reported'] = __( 'Reported', 'forumwp' );
					}
				}
			}

			return $post_states;
		}


		/**
		 * Custom columns for Forum
		 *
		 * @param array $columns
		 *
		 * @return array
		 */
		function forum_columns( $columns ) {
			$additional_columns = [
				'topics'        => __( 'Topics', 'forumwp' ),
				'replies'       => __( 'Replies', 'forumwp' ),
				'category'      => __( 'Category', 'forumwp' ),
				'status'        => __( 'Status', 'forumwp' ),
				'visibility'    => __( 'Visibility', 'forumwp' ),
			];

			if ( ! FMWP()->options()->get( 'forum_categories' ) ) {
				unset( $additional_columns['category'] );
			}

			return FMWP()->array_insert_before( $columns, 'author', $additional_columns );
		}


		/**
		 * Display custom columns for Forum
		 *
		 * @param string $column_name
		 * @param int $id
		 */
		function forum_columns_content( $column_name, $id ) {
			switch ( $column_name ) {
				case 'topics':
					echo FMWP()->common()->forum()->get_statistics( $id, 'topics' );
					break;
				case 'replies':
					echo FMWP()->common()->forum()->get_statistics( $id, 'posts' );
					break;
				case 'category':
					$terms = wp_get_post_terms(
						$id,
						'fmwp_forum_category',
						[
							'orderby'   => 'name',
							'order'     => 'ASC',
							'fields'    => 'names',
						]
					);

					if ( ! empty( $terms ) ) {
						echo implode( ',', $terms );
					}
					break;
				case 'status':
					$post = get_post( $id );

					if ( FMWP()->common()->forum()->is_locked( $post ) ) {
						$status = __( 'Locked', 'forumwp' );
					} else {
						$status_obj = get_post_status_object( $post->post_status );
						$status = ! empty( $status_obj->label ) ? $status_obj->label : $post->post_status;
					}

					echo $status;
					break;
				case 'visibility':
					$visibility = get_post_meta( $id, 'fmwp_visibility', true );
					$visibility = ! empty( FMWP()->common()->forum()->visibilities[ $visibility ] ) ? FMWP()->common()->forum()->visibilities[ $visibility ] : $visibility;
					echo $visibility;
					break;
			}
		}


		/**
		 * Custom columns for Forum
		 *
		 * @param array $columns
		 *
		 * @return array
		 */
		function forum_category_columns( $columns ) {
			$additional_columns = [
				'ID' => __( 'Category ID', 'forumwp' ),
			];

			return FMWP()->array_insert_after( $columns, 'slug', $additional_columns );
		}


		/**
		 * Display custom columns for Forum Category
		 *
		 * @param string $content
		 * @param string $column_name
		 * @param int $term_id
		 *
		 * @return mixed
		 */
		function forum_category_columns_content( $content, $column_name, $term_id ) {
			switch ( $column_name ) {
				case 'ID':
					$content = $term_id;
					break;
			}

			return $content;
		}


		/**
		 * Custom columns for Forum
		 *
		 * @param array $columns
		 *
		 * @return array
		 */
		function topic_tag_columns( $columns ) {
			$additional_columns = [
				'ID' => __( 'Tag ID', 'forumwp' ),
			];

			return FMWP()->array_insert_after( $columns, 'slug', $additional_columns );
		}


		/**
		 * Display custom columns for Forum Category
		 *
		 * @param string $content
		 * @param string $column_name
		 * @param int $term_id
		 *
		 * @return mixed
		 */
		function topic_tag_columns_content( $content, $column_name, $term_id ) {
			switch ( $column_name ) {
				case 'ID':
					$content = $term_id;
					break;
			}

			return $content;
		}


		/**
		 * Custom columns for Forum
		 *
		 * @param array $columns
		 *
		 * @return array
		 */
		function topic_columns( $columns ) {
			$additional_columns = [
				'forum'         => __( 'Forum', 'forumwp' ),
				'type'          => __( 'Type', 'forumwp' ),
				'topic_tags'    => __( 'Tags', 'forumwp' ),
				'status'        => __( 'Status', 'forumwp' ),
			];

			if ( ! FMWP()->options()->get( 'topic_tags' ) ) {
				unset( $additional_columns['topic_tags'] );
			}

			return FMWP()->array_insert_before( $columns, 'author', $additional_columns );
		}


		/**
		 * Display custom columns for Forum
		 *
		 * @param string $column_name
		 * @param int $id
		 */
		function topic_columns_content( $column_name, $id ) {
			switch ( $column_name ) {
				case 'forum':
					$forum_id = get_post_meta( $id, 'fmwp_forum', true );
					$forum = get_post( $forum_id );

					if ( ! empty( $forum ) && ! is_wp_error( $forum ) ) {
						echo $forum->post_title;
					}
					break;
				case 'type':
					$type = get_post_meta( $id, 'fmwp_type', true );
					$type = ! empty( FMWP()->common()->topic()->types[ $type ]['title'] ) ? FMWP()->common()->topic()->types[ $type ]['title'] : $type;
					echo $type;
					break;
				case 'status':
					$post = get_post( $id );
					$status = ! empty( FMWP()->common()->topic()->statuses[ $post->post_status ] ) ? FMWP()->common()->topic()->statuses[ $post->post_status ] : $post->post_status;
					echo $status;
					break;
				case 'topic_tags':
					$terms = FMWP()->common()->topic()->get_tags( $id, 'names' );

					if ( ! empty( $terms ) ) {
						echo implode( ',', $terms );
					}
					break;
			}
		}
	}
}