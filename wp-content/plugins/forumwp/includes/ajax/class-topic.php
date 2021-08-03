<?php
namespace fmwp\ajax;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\ajax\Topic' ) ) {


	/**
	 * Class Topic
	 *
	 * @package fmwp\ajax
	 */
	class Topic extends Post {


		/**
		 * Topic constructor.
		 */
		function __construct() {
			parent::__construct();
		}


		/**
		 * @param int|\WP_Post $topic
		 *
		 * @return array
		 */
		function response_data( $topic ) {
			if ( is_numeric( $topic ) ) {
				$topic = get_post( $topic );

				if ( empty( $topic ) || is_wp_error( $topic ) ) {
					return [];
				}
			}

			$author = get_userdata( $topic->post_author );

			$args = [
				'post_type'         => 'fmwp_reply',
				'posts_per_page'    => -1,
				'post_status'       => FMWP()->common()->reply()->post_status,
				'meta_key'          => 'fmwp_topic',
				'meta_value'        => $topic->ID,
				'orderby'           => 'date',
				'order'             => 'desc',
			];
			$args['suppress_filters'] = false;

			$args = apply_filters( 'fmwp_ajax_get_replies_args', $args, $topic->ID );

			$replies = get_posts( $args );

			$respondents = [];

			$first_response = null;
			foreach ( $replies as $reply ) {
				if ( $reply->post_author == $topic->post_author ) {
					continue;
				}

				if ( empty( $first_response ) ) {
					$first_response = $reply;
				}

				$respondents[] = $reply->post_author;
			}

			if ( post_password_required( $topic ) ) {
				$respondents_count = 0;
			} else {
				$respondents_count = count( array_unique( $respondents ) );
			}

			$dropdown_actions = FMWP()->common()->topic()->actions_list( $topic );

			$people = [];
			$people[] = [
				'id'        => $topic->post_author,
				'avatar'    => FMWP()->user()->get_avatar( $topic->post_author, 'inline', 60, [
					'class' => 'fmwp-tip-n',
					'title' => sprintf( __( 'Created by %s', 'forumwp' ), FMWP()->user()->display_name( $author ) ),
				] ),
				'url'       => FMWP()->user()->get_profile_link( $topic->post_author ),
			];

			if ( ! empty( $first_response ) && ! post_password_required( $topic ) ) {
				$last_reply_author = get_userdata( $first_response->post_author );

				if ( $people[0]['id'] !== $first_response->post_author ) {
					$people[] = [
						'id'        => $first_response->post_author,
						'avatar'    => FMWP()->user()->get_avatar( $first_response->post_author, 'inline', 60, [
							'class' => 'fmwp-tip-n',
							'title' => sprintf( __( 'Last reply by %s', 'forumwp' ), FMWP()->user()->display_name( $last_reply_author ) ),
						] ),
						'url'       => FMWP()->user()->get_profile_link( $first_response->post_author ),
					];
				}
			}

			$forum_id = FMWP()->common()->topic()->get_forum_id( $topic->ID );
			$forum = get_post( $forum_id );

			$last_upgrade = '';
			if ( ! FMWP()->common()->topic()->is_pending( $topic->ID ) ) {
				$last_upgrade = get_post_meta( $topic->ID, 'fmwp_last_update', true );
				$default_last_upgrade = ( ! empty( $topic->post_modified_gmt ) && $topic->post_modified_gmt !== '0000-00-00 00:00:00' ) ? human_time_diff( strtotime( $topic->post_modified_gmt ) ) : '';
				$last_upgrade = ! empty( $last_upgrade ) ? human_time_diff( $last_upgrade ) : $default_last_upgrade;
			}

			$topic_args = [
				'topic_id'          => $topic->ID,
				'title'             => $topic->post_title,
				'permalink'         => get_permalink( $topic->ID ),
				'author'            => FMWP()->user()->display_name( $author ),
				'author_url'        => FMWP()->user()->get_profile_link( $author->ID ),
				'author_avatar'     => FMWP()->user()->get_avatar( $author->ID, 'inline', 40 ),
				'author_card'       => FMWP()->user()->generate_card( $author->ID ),
				'last_upgrade'      => $last_upgrade,
				'replies'           => FMWP()->common()->topic()->get_statistics( $topic->ID, 'replies' ),
				'respondents_count' => $respondents_count,
				'views'             => FMWP()->common()->topic()->get_statistics( $topic->ID, 'views' ),
				'content'           => post_password_required( $topic ) ? '' : apply_filters( 'the_content', $topic->post_content, $topic->ID ),
				'dropdown_actions'  => $dropdown_actions,
				'can_edit'          => FMWP()->user()->can_edit_topic( get_current_user_id(), $topic ),
				'can_actions'       => count( $dropdown_actions ),
				'people'            => $people,
				'is_locked'         => FMWP()->common()->topic()->is_locked( $topic ),
				'is_reported'       => false,
				'is_trashed'        => FMWP()->common()->topic()->is_trashed( $topic->ID ),
				'is_pending'        => FMWP()->common()->topic()->is_pending( $topic->ID ),
				'is_pinned'         => FMWP()->common()->topic()->is_pinned( $topic->ID ),
				'is_announcement'   => FMWP()->common()->topic()->is_announcement( $topic->ID ),
				'is_global'         => FMWP()->common()->topic()->is_global( $topic->ID ),
				'is_author'         => ( is_user_logged_in() && $author->ID === get_current_user_id() ),
				'forum_title'       => $forum->post_title,
				'forum_url'         => get_permalink( $forum->ID ),
				'is_spam'           => FMWP()->common()->topic()->is_spam( $topic ),
			];

			//Reports data
			if ( is_user_logged_in() ) {
				if ( FMWP()->reports()->is_reported_by_user( $topic->ID, get_current_user_id() ) ) {
					$topic_args['is_reported'] = true;
				} elseif ( current_user_can( 'fmwp_see_reports' ) && FMWP()->reports()->is_reported( $topic->ID ) ) {
					$topic_args['is_reported'] = true;
				}
			}

			if ( FMWP()->options()->get( 'topic_tags' ) ) {
				$topic_tags = FMWP()->common()->topic()->get_tags( $topic->ID );

				$tags_data = [];
				foreach ( $topic_tags as $tag ) {
					$tags_data[] = [
						'href' => get_term_link( $tag->term_id, 'fmwp_topic_tag' ),
						'name' => $tag->name,
					];
				}
				$topic_args['tags'] = $tags_data;
			}

			if ( empty( $_REQUEST['forum_id'] ) ) {
				$forum_id = get_post_meta( $topic->ID, 'fmwp_forum', true );
				$forum = get_post( $forum_id );

				if ( ! empty( $forum ) && ! is_wp_error( $forum ) ) {
					$topic_args['forum'] = $forum->post_title;
					$topic_args['forum_permalink'] = get_permalink( $forum->ID );
				}
			}

			$topic_args = apply_filters( 'fmwp_ajax_response_topic_args', $topic_args, $topic );
			return $topic_args;
		}


		/**
		 * AJAX get topics
		 */
		function get_topics() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			$response = [];

			$args = [
				'post_type'         => 'fmwp_topic',
				'posts_per_page'    => FMWP()->options()->get_variable( 'topics_per_page' ),
				'paged'             => absint( $_REQUEST['page'] ),
			];

			$orderby = 'date';
			$order = 'desc';
			if ( ! empty( $_POST['order'] ) ) {
				list( $orderby, $order ) = explode( '_', sanitize_text_field( $_POST['order'] ) );

				switch ( $orderby ) {
					default:
						$args = apply_filters( 'fmwp_get_topics_args_by_order', $args, $orderby );
						break;
					case 'views':
						$args['meta_query'] = [
							'views' => [
								'key'     => 'fmwp_views',
								'compare' => 'EXISTS',
							],
						];
						break;
					case 'update':
						$args['meta_query'] = [
							'update' => [
								'key'     => 'fmwp_last_update',
								'compare' => 'EXISTS',
							],
						];
						break;
					case 'replies':
//						$args['meta_query'] = [
//							'replies' => [
//								'key'     => 'fmwp_count_replies',
//								'compare' => 'EXISTS',
//							],
//						];
						break;
				}
			}

			if ( ! empty( $_REQUEST['type'] ) ) {
				$args['meta_query']['relation'] = 'AND';
				$args['meta_query']['type'] = [
					'key'   => 'fmwp_type',
					'value' => sanitize_key( $_REQUEST['type'] ),
				];

				$args['orderby'] = [ $orderby => $order ];
			} else {
				$args['meta_query']['relation'] = 'AND';
				$args['meta_query']['type_order'] = [
					'key'       => 'fmwp_type_order',
					'compare'   => 'EXISTS',
				];

				$args['orderby'] = [ 'type_order' => 'ASC', $orderby => $order ];
			}

			$args = apply_filters( 'fmwp_get_topics_sort_summary', $args, $orderby, $order );

			$post_status = FMWP()->common()->topic()->post_status;
			if ( ! empty( $_REQUEST['status'] ) ) {

				$status_map = [
					'open'      => 'publish',
					'pending'   => 'pending',
					'trash'     => 'trash',
				];

				$status = sanitize_key( $_REQUEST['status'] );
				$post_status = isset( $status_map[ $status ] ) ? $status_map[ $status ] : $post_status;

			}

			$args['post_status'] = $post_status;

			if ( ! empty( $_REQUEST['search'] ) ) {
				$args['s'] = sanitize_text_field( $_REQUEST['search'] );
			}

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
				wp_send_json_success( $response );
			} else {
				foreach ( $forum_ids as $k => $forum_id ) {
					if ( post_password_required( $forum_id ) ) {
						unset( $forum_ids[ $k ] );
					}
				}

				$forum_ids = array_values( $forum_ids );

				if ( empty( $forum_ids ) ) {
					wp_send_json_success( $response );
				}
			}

			$args['meta_query']['relation'] = 'AND';

			if ( ! empty( $_REQUEST['forum_id'] ) ) {
				$forum_id = absint( $_REQUEST['forum_id'] );
				$forum = get_post( $forum_id );

				if ( ! empty( $forum ) && ! is_wp_error( $forum ) ) {
					$args['meta_query'][] = [
						'relation' => 'OR',
						[
							'key'   => 'fmwp_forum',
							'value' => $forum_id,
						],
						[
							'relation' => 'AND',
							[
								'key'       => 'fmwp_forum',
								'value'     => $forum_ids,
								'compare'   => 'IN',
							],
							[
								'key'   => 'fmwp_type',
								'value' => 'global',
							],
						],
					];
				}
			}

			$args['meta_query'][] = [
				'key'       => 'fmwp_forum',
				'value'     => $forum_ids,
				'compare'   => 'IN',
			];

			if ( FMWP()->options()->get( 'topic_tags' ) ) {
				if ( ! empty( $_POST['tag'] ) ) {
					$args['tax_query'] = [
						[
							'taxonomy'  => 'fmwp_topic_tag',
							'field'     => 'id',
							'terms'     => absint( $_POST['tag'] ),
						],
					];
				}
			}

			$args['suppress_filters'] = false;

			$args = apply_filters( 'fmwp_get_topics_arguments', $args );

			$topics = get_posts( $args );

			if ( ! empty( $topics ) ) {
				foreach ( $topics as $topic ) {
					$response[] = $this->response_data( $topic );
				}
			}

			wp_send_json_success( $response );
		}


		/**
		 * AJAX handler for Create Topic Form
		 *
		 */
		function create() {
			FMWP()->ajax()->check_nonce( 'fmwp-create-topic' );

			if ( empty( $_POST['fmwp-topic'] ) ) {
				wp_send_json_error( __( 'Invalid Data', 'forumwp' ) );
			}

			$request = $_POST['fmwp-topic'];

			if ( empty( $request['forum_id'] ) ) {
				wp_send_json_error( __( 'Empty Forum ID', 'forumwp' ) );
			}

			$forum_id = absint( $request['forum_id'] );
			if ( ! FMWP()->user()->can_create_topic( $forum_id ) ) {
				$text = apply_filters( 'fmwp_create_topic_disabled_text', __( 'You do not have the capability to perform this action', 'forumwp' ), $forum_id );
				wp_send_json_error( $text );
			}

			if ( FMWP()->common()->forum()->is_locked( $forum_id ) ) {
				wp_send_json_error( __( 'Sorry, but this forum is locked', 'forumwp' ) );
			}

			$errors = [];
			if ( empty( $request['title'] ) ) {
				$errors[] = [
					'field' => 'fmwp-topic-title',
					'message' => __( 'Title is required', 'forumwp' ),
				];
			}

			if ( empty( $request['content'] ) ) {
				$errors[] = [
					'field' => 'wp-fmwptopiccontent-wrap',
					'message' => __( 'Content is required', 'forumwp' ),
				];
			}

			if ( count( $errors ) ) {
				wp_send_json_error( [ 'errors' => $errors ] );
			}

			$last_topic_time = get_user_meta( get_current_user_id(), 'fmwp_latest_topic_date', true );
			$topic_delay = FMWP()->options()->get( 'topic_throttle' );
			if ( ! empty( $last_topic_time ) && $last_topic_time + $topic_delay > time() ) {
				wp_send_json_error( sprintf( __( 'You cannot create topics faster than %s seconds', 'forumwp' ), $topic_delay ) );
			}

			$args = [
				'forum_id'  => $forum_id,
				'title'     => sanitize_text_field( $request['title'] ),
				'content'   => wp_kses_post( $request['content'] ),
				'type'      => 'normal',
				'status'    => 'open',
			];

			if ( FMWP()->options()->get( 'topic_tags' ) ) {
				$args['tags'] = ! empty( $request['tags'] ) ? sanitize_text_field( $request['tags'] ) : [];
			}

			$args = apply_filters( 'fmwp_ajax_create_topic_args', $args, $request );

			$topic_id = FMWP()->common()->topic()->create( $args );

			$topic = get_post( $topic_id );

			$response = $this->response_data( $topic );

			wp_send_json_success( $response );
		}


		/**
		 * AJAX handler for Edit Topic Form
		 *
		 * @version 2.0
		 */
		function edit() {
			FMWP()->ajax()->check_nonce( 'fmwp-create-topic' );

			if ( empty( $_POST['fmwp-topic'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$topic_data = $_POST['fmwp-topic'];

			if ( empty( $topic_data['topic_id'] ) ) {
				wp_send_json_error( __( 'Topic ID is invalid', 'forumwp' ) );
			}

			$topic_id = absint( $topic_data['topic_id'] );

			$topic = get_post( $topic_id );
			if ( ! FMWP()->user()->can_edit_topic( get_current_user_id(), $topic ) ) {
				wp_send_json_error( __( 'You do not have the ability to edit this topic', 'forumwp' ) );
			}

			$errors = [];
			if ( empty( $topic_data['title'] ) ) {
				$errors[] = [
					'field'     => 'fmwp-topic-title',
					'message'   => __( 'Title is required', 'forumwp' ),
				];
			}

			if ( empty( $topic_data['content'] ) ) {
				$errors[] = [
					'field'     => 'wp-fmwptopiccontent-wrap',
					'message'   => __( 'Content is required', 'forumwp' )
				];
			}

			if ( count( $errors ) ) {
				wp_send_json_error( [ 'errors' => $errors ] );
			}

			$args = [
				'topic_id'  => $topic_id,
				'title'     => sanitize_text_field( $topic_data['title'] ),
				'content'   => wp_kses_post( $topic_data['content'] ),
			];

			if ( FMWP()->options()->get( 'topic_tags' ) ) {
				$args['tags'] = ! empty( $topic_data['tags'] ) ? sanitize_text_field( $topic_data['tags'] ) : [];
			}

			$args = apply_filters( 'fmwp_ajax_edit_topic_args', $args, $topic_data );

			if ( ! FMWP()->common()->topic()->edit( $args ) ) {
				wp_send_json_error( __( 'Something is wrong with the data', 'forumwp' ) );
			} else {
				do_action( 'fmwp_topic_edited', $topic_id, $topic_data );
			}

			$topic = get_post( $topic_id );

			$response = $this->response_data( $topic );

			wp_send_json_success( $response );
		}


		/**
		 * AJAX handler for get topic edit
		 *
		 * @version 2.0
		 */
		function get_topic() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['topic_id'] ) ) {
				wp_send_json_error( __( 'Invalid topic ID', 'forumwp' ) );
			}

			$topic_id = absint( $_POST['topic_id'] );

			$topic = get_post( $topic_id );
			if ( empty( $topic ) || is_wp_error( $topic ) ) {
				wp_send_json_error( __( 'Invalid topic', 'forumwp' ) );
			}

			if ( ! FMWP()->user()->can_edit_topic( get_current_user_id(), $topic ) ) {
				wp_send_json_error( __( 'You do not have the ability to edit this topic', 'forumwp' ) );
			}

			$original_content = get_post_meta( $topic->ID, 'fmwp_original_content', true );
			$original_content = empty( $original_content ) ? $topic->post_content : $original_content;

			$response = [
				'id'            => $topic->ID,
				'parent_id'     => $topic->post_parent,
				'orig_content'  => $original_content,
				'content'       => nl2br( $topic->post_content ),
				'title'         => $topic->post_title,
			];

			if ( FMWP()->options()->get( 'topic_tags' ) ) {
				$response['tags'] = FMWP()->common()->topic()->get_tags( $topic->ID, 'names' );
			}

			$response = apply_filters( 'fmwp_ajax_get_topic_args', $response, $topic );

			wp_send_json_success( $response );
		}


		/**
		 * AJAX handler for pinning a topic
		 *
		 * @version 2.0
		 */
		function pin() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['topic_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$topic_id = absint( $_POST['topic_id'] );
			$topic = get_post( $topic_id );

			if ( empty( $topic ) || is_wp_error( $topic ) ) {
				wp_send_json_error( __( 'Topic ID is invalid', 'forumwp' ) );
			}

			FMWP()->common()->topic()->pin( $topic );

			$topic = get_post( $topic_id );
			wp_send_json_success( [
				'dropdown_actions' => FMWP()->common()->topic()->actions_list( $topic ),
			] );
		}


		/**
		 * AJAX handler for unpinning a topic
		 *
		 * @version 2.0
		 */
		function unpin() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['topic_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$topic_id = absint( $_POST['topic_id'] );
			$topic = get_post( $topic_id );

			if ( empty( $topic ) || is_wp_error( $topic ) ) {
				wp_send_json_error( __( 'Topic ID is invalid', 'forumwp' ) );
			}

			FMWP()->common()->topic()->unpin( $topic );

			$topic = get_post( $topic_id );
			wp_send_json_success( [
				'dropdown_actions' => FMWP()->common()->topic()->actions_list( $topic ),
			] );
		}


		/**
		 * AJAX handler for locking a topic
		 *
		 * @version 2.0
		 */
		function lock() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['topic_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$topic_id = absint( $_POST['topic_id'] );
			$topic = get_post( $topic_id );

			if ( empty( $topic ) || is_wp_error( $topic ) ) {
				wp_send_json_error( __( 'Topic ID is invalid', 'forumwp' ) );
			}

			FMWP()->common()->topic()->lock( $topic_id );

			$topic = get_post( $topic_id );
			wp_send_json_success( [
				'dropdown_actions' => FMWP()->common()->topic()->actions_list( $topic ),
			] );
		}


		/**
		 * AJAX handler for unlocking a topic
		 *
		 * @version 2.0
		 */
		function unlock() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['topic_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$topic_id = absint( $_POST['topic_id'] );
			$topic = get_post( $topic_id );

			if ( empty( $topic ) || is_wp_error( $topic ) ) {
				wp_send_json_error( __( 'Topic ID is invalid', 'forumwp' ) );
			}

			FMWP()->common()->topic()->unlock( $topic_id );

			$topic = get_post( $topic_id );
			wp_send_json_success( [
				'dropdown_actions' => FMWP()->common()->topic()->actions_list( $topic ),
			] );
		}


		/**
		 * AJAX handler for moving a topic to the trash
		 *
		 * @version 2.0
		 */
		function trash() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['topic_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$topic_id = absint( $_POST['topic_id'] );
			$topic = get_post( $topic_id );

			if ( empty( $topic ) || is_wp_error( $topic ) ) {
				wp_send_json_error( __( 'Topic ID is invalid', 'forumwp' ) );
			}

			if ( ! FMWP()->user()->can_trash_topic( get_current_user_id(), $topic ) ) {
				wp_send_json_error( __( 'You do not have the ability to move this topic to trash', 'forumwp' ) );
			}

			FMWP()->common()->topic()->move_to_trash( $topic_id );

			$topic = get_post( $topic_id );
			wp_send_json_success( [
				'dropdown_actions' => FMWP()->common()->topic()->actions_list( $topic ),
			] );
		}


		/**
		 * AJAX handler for Restore Topic
		 *
		 */
		function restore() {
			FMWP()->check_ajax_nonce();

			if ( empty( $_POST['topic_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$topic = get_post( $_POST['topic_id'] );

			if ( empty( $topic ) || is_wp_error( $topic ) ) {
				wp_send_json_error( __( 'Topic ID is invalid', 'forumwp' ) );
			}

			if ( ! FMWP()->user()->can_restore_topic( get_current_user_id(), $topic ) ) {
				wp_send_json_error( __( 'You do not have the ability to restore this topic', 'forumwp' ) );
			}

			FMWP()->common()->topic()->restore( absint( $_POST['topic_id'] ) );

			$topic = get_post( absint( $_POST['topic_id'] ) );
			wp_send_json_success( [
				'status'            => $topic->post_status,
				'dropdown_actions'  => FMWP()->common()->topic()->actions_list( $topic ),
			] );
		}


		/**
		 * AJAX handler for Delete Reply
		 *
		 */
		function delete() {
			FMWP()->check_ajax_nonce();

			if ( empty( $_POST['topic_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$topic = get_post( absint( $_POST['topic_id'] ) );

			if ( empty( $topic ) || is_wp_error( $topic ) ) {
				wp_send_json_error( __( 'Topic ID is invalid', 'forumwp' ) );
			}

			if ( ! FMWP()->user()->can_delete_topic( get_current_user_id(), $topic ) ) {
				wp_send_json_error( __( 'You do not have the ability to delete this topic', 'forumwp' ) );
			}

			$forum_id = FMWP()->common()->topic()->get_forum_id( absint( $_POST['topic_id'] ) );
			$forum_link = get_permalink( $forum_id );

			FMWP()->common()->topic()->delete( absint( $_POST['topic_id'] ) );

			wp_send_json_success( [
				'message'   => __( 'Topic was deleted successfully. You will be redirected to the Forum page', 'forumwp' ),
				'statistic' => [ 'topics' => FMWP()->common()->forum()->get_statistics( $forum_id, 'topics' ) ],
				'redirect'  => $forum_link,
			] );
		}


		/**
		 * AJAX handler for marking a topic as spam
		 *
		 * @version 2.0
		 */
		function spam() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['topic_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$topic_id = absint( $_POST['topic_id'] );
			$topic = get_post( $topic_id );

			if ( empty( $topic ) || is_wp_error( $topic ) ) {
				wp_send_json_error( __( 'Topic ID is invalid', 'forumwp' ) );
			}

			if ( ! FMWP()->user()->can_spam_topic( get_current_user_id(), $topic ) ) {
				wp_send_json_error( __( 'You do not have the ability to mark this topic as spam', 'forumwp' ) );
			}

			FMWP()->common()->topic()->spam( $topic_id );

			$topic = get_post( $topic_id );

			wp_send_json_success( [
				'dropdown_actions' => FMWP()->common()->topic()->actions_list( $topic ),
			] );
		}


		/**
		 * AJAX handler for un-marking a topic as spam
		 *
		 * @version 2.0
		 */
		function restore_spam() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['topic_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$topic_id = absint( $_POST['topic_id'] );
			$topic = get_post( $topic_id );

			if ( empty( $topic ) || is_wp_error( $topic ) ) {
				wp_send_json_error( __( 'Topic ID is invalid', 'forumwp' ) );
			}

			if ( ! FMWP()->user()->can_restore_spam_topic( get_current_user_id(), $topic ) ) {
				wp_send_json_error( __( 'You do not have the ability to restore this topic from spam', 'forumwp' ) );
			}

			FMWP()->common()->topic()->restore_spam( $topic_id );

			$topic = get_post( $topic_id );

			wp_send_json_success( [
				'dropdown_actions' => FMWP()->common()->topic()->actions_list( $topic ),
			] );
		}


		/**
		 * AJAX handler for reporting a topic
		 *
		 * @version 2.0
		 */
		function report() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['topic_id'] ) ) {
				wp_send_json_error( __( 'Empty Topic ID', 'forumwp' ) );
			}

			$topic_id = absint( $_POST['topic_id'] );

			$report_id = FMWP()->reports()->add( $topic_id, get_current_user_id() );
			if ( ! empty( $report_id ) ) {
				$topic = get_post( $topic_id );

				wp_send_json_success( [
					'dropdown_actions' => FMWP()->common()->topic()->actions_list( $topic ),
				] );
			} else {
				wp_send_json_error( __( 'Something wrong with reports', 'forumwp' ) );
			}
		}


		/**
		 * AJAX handler for un-reporting a topic
		 *
		 * @version 2.0
		 */
		function unreport() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['topic_id'] ) ) {
				wp_send_json_error( __( 'Empty Topic ID', 'forumwp' ) );
			}

			$topic_id = absint( $_POST['topic_id'] );

			$removed = FMWP()->reports()->remove( $topic_id, get_current_user_id() );

			if ( $removed ) {
				$topic = get_post( $topic_id );
				wp_send_json_success( [
					'dropdown_actions' => FMWP()->common()->topic()->actions_list( $topic ),
				] );
			} else {
				wp_send_json_error( __( 'Security Issue', 'forumwp' ) );
			}
		}


		/**
		 * AJAX handler for clear topic reports
		 *
		 * @version 2.0
		 */
		function clear_reports() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['topic_id'] ) ) {
				wp_send_json_error( __( 'Empty Topic ID', 'forumwp' ) );
			}

			$topic_id = absint( $_POST['topic_id'] );

			$removed = FMWP()->reports()->clear( $topic_id );

			if ( $removed ) {
				$topic = get_post( $topic_id );
				wp_send_json_success( [
					'dropdown_actions' => FMWP()->common()->topic()->actions_list( $topic ),
				] );
			} else {
				wp_send_json_error( __( 'Security Issue', 'forumwp' ) );
			}
		}


		/**
		 *
		 */
		function increment_views() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_REQUEST['post_id'] ) ) {
				wp_send_json_error();
			}

			$post_id = absint( $_REQUEST['post_id'] );

			$views = [];
			if ( ! empty( $_COOKIE['fmwp_topic_views'] ) ) {
				$views = maybe_unserialize( $_COOKIE['fmwp_topic_views'] );
			}
			$views[] = $post_id;
			$views = array_unique( $views );

			setcookie( 'fmwp_topic_views', serialize( $views ), time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );

			$post = get_post( $post_id );
			if ( $post_id > 0 && $post->post_type == 'fmwp_topic' ) {
				if ( ! $post_views = get_post_meta( $post_id, 'fmwp_views', true ) ) {
					$post_views = 0;
				}

				$should_count = true;
				if ( ! empty( $_COOKIE['fmwp_topic_views'] ) ) {
					$views = maybe_unserialize( $_COOKIE['fmwp_topic_views'] );

					if ( is_array( $views ) && in_array( $post_id, $views ) ) {
						$should_count = false;
					}
				}

				if ( $should_count ) {
					update_post_meta( $post_id, 'fmwp_views', ( $post_views + 1 ) );
					wp_send_json_success( $post_views + 1 );
				}
			}

			wp_send_json_error();
		}
	}
}