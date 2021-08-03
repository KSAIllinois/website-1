<?php
namespace fmwp\ajax;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\ajax\Reply' ) ) {


	/**
	 * Class Reply
	 *
	 * @package fmwp\ajax
	 */
	class Reply extends Post{


		/**
		 * @var array
		 */
		var $post_status;



		var $sort_by;


		/**
		 * Reply constructor.
		 */
		function __construct() {
			parent::__construct();
		}


		/**
		 * @param \WP_Post $reply
		 * @param bool $with_parent
		 *
		 * @version 2.0
		 *
		 * @return array
		 */
		function response_data( $reply, $with_parent = false ) {
			if ( is_numeric( $reply ) ) {
				$reply = get_post( $reply );

				if ( empty( $reply ) || is_wp_error( $reply ) ) {
					return [];
				}
			}

			$total_child = FMWP()->common()->reply()->get_child_replies_count( $reply->ID );

			$child = FMWP()->common()->reply()->build_replies_avatars( $reply );
			if ( count( $child ) > 2 ) {
				$child = array_slice( $child, 0, 2 );
			}

			$author = get_userdata( $reply->post_author );

			$can_edit = false;
			if ( is_user_logged_in() ) {
				$can_edit = FMWP()->user()->can_edit_reply( get_current_user_id(), $reply );
			}

			$dropdown_actions = FMWP()->common()->reply()->actions_list( $reply );

			$author_url = FMWP()->user()->get_profile_link( $author->ID );

			$slug = FMWP()->user()->maybe_get_slug( $author->ID );

			$beauty_date = '';
			if ( ! FMWP()->common()->reply()->is_pending( $reply->ID ) ) {
				$beauty_date = ( ! empty( $reply->post_modified_gmt ) && $reply->post_modified_gmt !== '0000-00-00 00:00:00' ) ? human_time_diff( strtotime( $reply->post_modified_gmt ) ) : '';
			}

			$date = '';
			if ( ! FMWP()->common()->reply()->is_pending( $reply->ID ) ) {
				$date = date_i18n( FMWP()->datetime_format(), strtotime( $reply->post_modified ) );
			}

			$reply_args = [
				'reply_id'          => $reply->ID,
				'post_parent'       => $reply->post_parent,
				'content'           => nl2br( $reply->post_content ),
				'permalink'         => FMWP()->common()->reply()->get_link( $reply->ID ),
				'author'            => FMWP()->user()->display_name( $author ),
				'author_url'        => $author_url,
				'author_avatar'     => FMWP()->user()->get_avatar( $author->ID, 'inline', 60 ),
				'date'              => $date,
				'beauty_date'       => $beauty_date,
				'has_children'      => ( $total_child > 0 ),
				'is_pending'        => FMWP()->common()->reply()->is_pending( $reply->ID ),
				'is_reported'       => false,
				'answers'           => $child,
				'more_answers'      => count( $child ) > 2 ? true : false,
				'total_replies'     => $total_child,
				'mention'           => '@' . $slug,
				'is_trashed'        => FMWP()->common()->reply()->is_trashed( $reply->ID ),
				'can_edit'          => $can_edit,
				'author_tags'       => FMWP()->common()->reply()->get_author_tags( $reply ),
				'author_card'       => FMWP()->user()->generate_card( $author->ID ),
				'is_locked'         => FMWP()->common()->reply()->is_locked( $reply ),
				'is_author'         => ( is_user_logged_in() && $author->ID === get_current_user_id() ),
				'dropdown_actions'  => $dropdown_actions,
				'can_actions'       => count( $dropdown_actions ),
				'is_subsub'         => FMWP()->common()->reply()->is_subsub( $reply ),
				'is_spam'           => FMWP()->common()->reply()->is_spam( $reply ),
				'title'             => $reply->post_title,
			];

			//Reports data
			if ( is_user_logged_in() ) {
				if ( FMWP()->reports()->is_reported_by_user( $reply->ID, get_current_user_id() ) ) {
					$reply_args['is_reported'] = true;
				} elseif ( current_user_can( 'fmwp_see_reports' ) && FMWP()->reports()->is_reported( $reply->ID ) ) {
					$reply_args['is_reported'] = true;
				}
			}

			if ( $with_parent ) {
				if ( ! empty( $reply->post_parent ) ) {
					$parent_reply = get_post( $reply->post_parent );

					if ( ! empty( $parent_reply ) && ! is_wp_error( $parent_reply ) ) {
						$child = FMWP()->common()->reply()->build_replies_avatars( $parent_reply );
						if ( count( $child ) > 2 ) {
							$child = array_slice( $child, 0, 2 );
						}

						$total_child = FMWP()->common()->reply()->get_child_replies_count( $reply->post_parent );

						$reply_args['parent_data'] = [
							'has_children'  => true,
							'total_replies' => $total_child,
							'answers'       => $child,
							'more_answers'  => count( $child ) > 2 ? true : false,
						];
					}
				}
			}

			$reply_args = apply_filters( 'fmwp_ajax_response_reply_args', $reply_args, $reply );
			return $reply_args;
		}


		/**
		 * AJAX get replies
		 *
		 * @version 2.0
		 */
		function get_replies() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			$args = [
				'meta_query' => [
					'relation' => 'AND',
				],
			];

			if ( ! empty( $_REQUEST['topic_id'] ) ) {

				$topic_id = absint( $_REQUEST['topic_id'] );

				$topic = get_post( $topic_id );

				if ( ! empty( $topic ) && ! is_wp_error( $topic ) ) {
					$args['meta_query'] = array_merge( $args['meta_query'], [
						'topic' => [
							'key'   => 'fmwp_topic',
							'value' => $topic_id,
						]
					] );
				}
			}

			$orderby = 'date';
			$order = 'desc';
			if ( ! empty( $_POST['order'] ) ) {
				list( $orderby, $order ) = explode( '_', sanitize_text_field( $_POST['order'] ) );

				$args = apply_filters( 'fmwp_get_replies_args_by_order', $args, $orderby );
			}

			$args['orderby'] = [ $orderby => $order ];
			$args = apply_filters( 'fmwp_get_replies_sort_summary', $args, $orderby, $order );

			$page = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

			$search_by_anchor = false;
			if ( ! empty( $_POST['reply_id'] ) && 1 == $page && FMWP()->user()->can_view_reply( get_current_user_id(), absint( $_POST['reply_id'] ) ) ) {
				$reply = get_post( absint( $_POST['reply_id'] ) );
				if ( ! empty( $reply ) && ! is_wp_error( $reply ) ) {
					$search_by_anchor = true;
				}
			}

			if ( $search_by_anchor ) {
				global $wpdb;

				$scroll_to = $reply->ID;
				$expand_child = false;

				$left_join = '';
				$where = '';
				$topic_id = false;

				if ( ! empty( $_REQUEST['topic_id'] ) ) {
					$topic_id = absint( $_REQUEST['topic_id'] );
					$topic = get_post( $topic_id );

					if ( ! empty( $topic ) && ! is_wp_error( $topic ) ) {
						$left_join = "LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID";
						$where = ' ( pm.meta_key = \'fmwp_topic\' AND pm.meta_value = %d ) AND ';
					}
				}

				if ( $reply->post_parent != 0 ) {
					$reply = get_post( $reply->post_parent );

					$scroll_to = $reply->ID;
					$expand_child = true;

					if ( $reply->post_parent != 0 ) {
						$reply = get_post( $reply->post_parent );

						$scroll_to = $reply->ID;
					}
				}

				$above_count = 0;

				switch ( $orderby ) {
					default: {
						$above_count = apply_filters( 'fmwp_pre_reply_count_by_anchor', $above_count, $orderby, $order, $reply, $left_join, $where, $topic_id );
						break;
					}
					case 'date': {
						if ( $order == 'desc' ) {
							if ( $topic_id ) {
								$above_count = $wpdb->get_var( $wpdb->prepare(
									"SELECT COUNT(p.ID) 
									FROM {$wpdb->posts} p 
									{$left_join}
									WHERE p.post_parent = 0 AND
										  p.post_date >= %s AND
										  {$where}
										  p.ID <> %d",
									$reply->post_date,
									$topic_id,
									$reply->ID
								) );
							} else {
								$above_count = $wpdb->get_var( $wpdb->prepare(
									"SELECT COUNT(p.ID) 
									FROM {$wpdb->posts} p 
									WHERE p.post_parent = 0 AND
										  p.post_date >= %s AND
										  p.ID <> %d",
									$reply->post_date,
									$reply->ID
								) );
							}
						} else {
							if ( $topic_id ) {
								$above_count = $wpdb->get_var( $wpdb->prepare(
									"SELECT COUNT(p.ID) 
									FROM {$wpdb->posts} p 
									{$left_join}
									WHERE p.post_parent = 0 AND
										  p.post_date <= %s AND
										  {$where}
										  p.ID <> %d",
									$reply->post_date,
									$topic_id,
									$reply->ID
								) );
							} else {
								$above_count = $wpdb->get_var( $wpdb->prepare(
									"SELECT COUNT(p.ID) 
									FROM {$wpdb->posts} p 
									WHERE p.post_parent = 0 AND
										  p.post_date <= %s AND
										  p.ID <> %d",
									$reply->post_date,
									$reply->ID
								) );
							}
						}
						break;
					}
				}

				$next_page = floor( ( $above_count + 1 ) / FMWP()->options()->get_variable( 'replies_per_page' ) );
				$next_page = ( $next_page == 0 ) ? 1 : $next_page;

				$next_offset = ( $above_count + 1 ) - FMWP()->options()->get_variable( 'replies_per_page' ) * ( ( $above_count + 1 ) % $next_page );

				$args = array_merge( $args, [
					'post_type'         => 'fmwp_reply',
					'posts_per_page'    => $above_count + 1,
					'post_status'       => FMWP()->common()->reply()->post_status,
					'post_parent'       => 0,
					'paged'             => $page,
				] );

			} else {

				$args = array_merge( $args, [
					'post_type'         => 'fmwp_reply',
					'posts_per_page'    => FMWP()->options()->get_variable( 'replies_per_page' ),
					'post_status'       => FMWP()->common()->reply()->post_status,
					'post_parent'       => 0,
					'paged'             => $page,
				] );

				if ( ! empty( $_REQUEST['offset'] ) ) {
					$args['offset'] = (int) $_REQUEST['offset'];
					$args['posts_per_page'] = FMWP()->options()->get_variable( 'replies_per_page' ) - $args['offset'];
					$args['paged'] = 1;
				}
			}

			$args['suppress_filters'] = false;

			$args = apply_filters( 'fmwp_ajax_get_replies_args', $args, $topic_id );

			$replies = get_posts( $args );

			$response = [];
			if ( ! empty( $replies ) ) {
				foreach ( $replies as $reply ) {
					$response[ $reply->ID ] = $this->response_data( $reply );
				}
			}

			if ( $search_by_anchor ) {
				wp_send_json_success( [
					'replies'       => array_values( $response ),
					'scroll_to'     => $scroll_to,
					'expand_child'  => $expand_child,
					'next_page'     => $next_page,
					'next_offset'   => $next_offset,
				] );
			} else {
				wp_send_json_success( array_values( $response ) );
			}
		}


		/**
		 * AJAX getting child replies
		 *
		 * @version 2.0
		 */
		function get_child_replies() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_REQUEST['reply_id'] ) ) {
				wp_send_json_error( __( 'Invalid reply', 'forumwp' ) );
			}

			$reply_id = absint( $_REQUEST['reply_id'] );
			$reply = get_post( $reply_id );
			if ( empty( $reply ) || is_wp_error( $reply ) ) {
				wp_send_json_error( __( 'Invalid reply', 'forumwp' ) );
			}

			$search_by_anchor = false;
			if ( ! empty( $_POST['search_reply'] ) ) {
				$search_reply_id = absint( $_POST['search_reply'] );

				if ( FMWP()->user()->can_view_reply( get_current_user_id(), $search_reply_id ) ) {

					$search_reply = get_post( $search_reply_id );
					if ( ! empty( $search_reply ) && ! is_wp_error( $search_reply ) ) {
						$search_by_anchor = true;
					}
				}
			}

			$args = [
				'meta_query' => [
					'relation' => 'AND',
				],
			];

			$orderby = 'date';
			$order = 'desc';
			if ( ! empty( $_POST['order'] ) ) {
				list( $orderby, $order ) = explode( '_', sanitize_text_field( $_POST['order'] ) );

				$args = apply_filters( 'fmwp_get_child_replies_args_by_order', $args, $orderby );
			}

			$args['orderby'] = [ $orderby => $order ];
			$args = apply_filters( 'fmwp_get_child_replies_sort_summary', $args, $orderby, $order );

			$args = array_merge( $args, [
				'post_parent'       => $reply_id,
				'post_type'         => 'fmwp_reply',
				'posts_per_page'    => -1,
				'post_status'       => FMWP()->common()->reply()->post_status,
			] );

			$args['suppress_filters'] = false;

			$args = apply_filters( 'fmwp_ajax_get_sub_replies_args', $args, $reply_id );

			$replies = get_posts( $args );

			$response = [];
			if ( ! empty( $replies ) ) {
				foreach ( $replies as $reply ) {
					$response[ $reply->ID ] = $this->response_data( $reply );
				}
			}

			if ( $search_by_anchor ) {

				$scroll_to = $search_reply->ID;
				$expand_child = false;

				if ( ! in_array( $search_reply->ID, array_keys( $response ) ) ) {
					$scroll_to = $search_reply->post_parent;
					$expand_child = true;
				}

				wp_send_json_success( [
					'replies'       => array_values( $response ),
					'scroll_to'     => $scroll_to,
					'expand_child'  => $expand_child,
				] );
			} else {
				wp_send_json_success( array_values( $response ) );
			}
		}


		/**
		 * AJAX handler for Create Reply
		 *
		 * @version 2.0
		 */
		function create() {
			FMWP()->ajax()->check_nonce( 'fmwp-create-reply' );

			if ( empty( $_POST['fmwp-reply'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$reply_data = $_POST['fmwp-reply'];

			if ( empty( $reply_data['topic_id'] ) ) {
				wp_send_json_error( __( 'Empty Topic ID', 'forumwp' ) );
			}

			$topic_id = absint( $reply_data['topic_id'] );

			if ( ! FMWP()->user()->can_reply( $topic_id ) ) {
				$text = apply_filters( 'fmwp_reply_disabled_reply_text', __( 'You haven\'t capabilities to make this action', 'forumwp' ), $topic_id );
				wp_send_json_error( $text );
			}

			$forum_id = FMWP()->common()->topic()->get_forum_id( $topic_id );

			if ( empty( $forum_id ) ) {
				wp_send_json_error( __( 'Empty Forum ID', 'forumwp' ) );
			}

			$errors = [];
			if ( empty( $reply_data['content'] ) ) {
				$errors[] = [
					'field'     => 'wp-fmwpreplycontent-wrap',
					'message'   => __( 'Content is required', 'forumwp' ),
				];
			}

			if ( count( $errors ) ) {
				wp_send_json_error( [ 'errors' => $errors ] );
			}

			$last_reply_time = get_user_meta( get_current_user_id(), 'fmwp_latest_reply_date', true );
			$reply_delay = FMWP()->options()->get( 'reply_throttle' );
			if ( ! empty( $last_reply_time ) && $last_reply_time + $reply_delay > time() ) {
				wp_send_json_error( sprintf( __( 'You cannot leave replies faster than %s seconds', 'forumwp' ), $reply_delay ) );
			}

			$args = [
				'forum_id'  => $forum_id,
				'topic_id'  => $topic_id,
				'content'   => wp_kses_post( $reply_data['content'] ),
			];

			if ( ! empty( $reply_data['parent_id'] ) ) {
				$parent_id = absint( $reply_data['parent_id'] );
				$parent_reply = FMWP()->common()->reply()->exists( $parent_id );
				if ( ! empty( $parent_reply ) ) {
					$args['post_parent'] = $parent_id;
				}
			}

			$args = apply_filters( 'fmwp_ajax_create_reply_args', $args, $reply_data );

			$reply_id = FMWP()->common()->reply()->create( $args );
			$reply = get_post( $reply_id );

			wp_send_json_success( $this->response_data( $reply, true ) );
		}


		/**
		 * AJAX handler for get reply edit
		 *
		 * @version 2.0
		 */
		function get_reply() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['reply_id'] ) ) {
				wp_send_json_error( __( 'Invalid reply ID', 'forumwp' ) );
			}

			$reply_id = absint( $_POST['reply_id'] );
			$reply = get_post( $reply_id );
			if ( empty( $reply ) || is_wp_error( $reply ) ) {
				wp_send_json_error( __( 'Invalid reply', 'forumwp' ) );
			}

			if ( ! FMWP()->user()->can_edit_reply( get_current_user_id(), $reply ) ) {
				wp_send_json_error( __( 'You do not have the ability to edit this reply', 'forumwp' ) );
			}

			$original_content = get_post_meta( $reply->ID, 'fmwp_original_content', true );
			$original_content = empty( $original_content ) ? $reply->post_content : $original_content;

			$response = [
				'id'            => $reply->ID,
				'parent_id'     => $reply->post_parent,
				'orig_content'  => $original_content,
				'content'       => nl2br( $reply->post_content ),
			];

			$response = apply_filters( 'fmwp_ajax_get_reply_args', $response, $reply );

			wp_send_json_success( $response );
		}


		/**
		 * AJAX handler for Edit Reply
		 *
		 * @version 2.0
		 */
		function edit() {
			FMWP()->ajax()->check_nonce( 'fmwp-create-reply' );

			if ( empty( $_POST['fmwp-reply'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$reply_data = $_POST['fmwp-reply'];

			if ( empty( $reply_data['reply_id'] ) ) {
				wp_send_json_error( __( 'Reply ID is invalid', 'forumwp' ) );
			}

			$reply_id = absint( $reply_data['reply_id'] );
			$reply = get_post( $reply_id );
			if ( ! FMWP()->user()->can_edit_reply( get_current_user_id(), $reply ) ) {
				wp_send_json_error( __( 'You do not have the ability to edit this reply', 'forumwp' ) );
			}

			$errors = [];
			if ( empty( $reply_data['content'] ) ) {
				$errors[] = [
					'field'     => 'wp-fmwpreplycontent-wrap',
					'message'   => __( 'Content is required', 'forumwp' ),
				];
			}

			if ( count( $errors ) ) {
				wp_send_json_error( [ 'errors' => $errors ] );
			}

			$args = [
				'reply_id'  => $reply_id,
				'content'   => wp_kses_post( $reply_data['content'] ),
			];

			if ( ! FMWP()->common()->reply()->edit( $args ) ) {
				wp_send_json_error( __( 'Something is wrong with the data', 'forumwp' ) );
			} else {
				do_action( 'fmwp_reply_edited', $reply_id, $reply_data );
			}

			$reply = get_post( $reply_id );

			wp_send_json_success( $this->response_data( $reply ) );
		}


		/**
		 * AJAX handler for moving reply to the trash
		 *
		 * @version 2.0
		 */
		function trash() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['reply_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$reply_id = absint( $_POST['reply_id'] );
			$reply = get_post( $reply_id );

			if ( empty( $reply ) || is_wp_error( $reply ) ) {
				wp_send_json_error( __( 'Reply ID is invalid', 'forumwp' ) );
			}

			if ( ! FMWP()->user()->can_trash_reply( get_current_user_id(), $reply ) ) {
				wp_send_json_error( __( 'You do not have the ability to move this reply to trash', 'forumwp' ) );
			}

			FMWP()->common()->reply()->move_to_trash( $reply_id );

			$reply = get_post( $reply_id );

			wp_send_json_success( [
				'dropdown_actions' => FMWP()->common()->reply()->actions_list( $reply ),
			] );
		}


		/**
		 * AJAX handler for restore reply from trash
		 *
		 * @version 2.0
		 */
		function restore() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['reply_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$reply_id = absint( $_POST['reply_id'] );
			$reply = get_post( $reply_id );

			if ( empty( $reply ) || is_wp_error( $reply ) ) {
				wp_send_json_error( __( 'Reply ID is invalid', 'forumwp' ) );
			}

			if ( ! FMWP()->user()->can_restore_reply( get_current_user_id(), $reply ) ) {
				wp_send_json_error( __( 'You do not have the ability to restore this reply', 'forumwp' ) );
			}

			FMWP()->common()->reply()->restore( $reply_id );

			$reply = get_post( $reply_id );

			wp_send_json_success( [
				'dropdown_actions'  => FMWP()->common()->reply()->actions_list( $reply ),
				'status'            => $reply->post_status,
			] );
		}


		/**
		 * AJAX handler for deleting reply permanently
		 *
		 * @version 2.0
		 */
		function delete() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );
			if ( empty( $_POST['reply_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$reply_id = absint( $_POST['reply_id'] );
			$reply = get_post( $reply_id );

			if ( empty( $reply ) || is_wp_error( $reply ) ) {
				wp_send_json_error( __( 'Reply ID is invalid', 'forumwp' ) );
			}

			if ( ! FMWP()->user()->can_delete_reply( get_current_user_id(), $reply ) ) {
				wp_send_json_error( __( 'You do not have the ability to delete this reply', 'forumwp' ) );
			}

			$topic_id = FMWP()->common()->reply()->get_topic_id( $reply_id );

			$is_sub = FMWP()->common()->reply()->is_sub( $reply );
			$is_subsub = FMWP()->common()->reply()->is_subsub( $reply );

			if ( $is_subsub ) {
				$parent_reply_id = $reply->post_parent;
				$parent_reply = get_post( $parent_reply_id );
				$parent_parent_reply_id = $parent_reply->post_parent;
			} elseif ( $is_sub ) {
				$parent_reply_id = $reply->post_parent;
			}

			$sub_delete = FMWP()->options()->get( 'reply_delete' );
			$sub_delete = empty( $sub_delete ) ? 'sub_delete' : $sub_delete;

			$args = [];
			$args['order'] = ! empty( $_POST['order'] ) ? sanitize_key( $_POST['order'] ) : 'date_asc';

			$sub_replies = FMWP()->common()->reply()->delete( $reply_id, $args );

			$child_replies = [];
			if ( $sub_delete == 'change_level' ) {
				if ( ! empty( $sub_replies ) ) {
					foreach ( $sub_replies as $subreply_id ) {
						$reply = get_post( $subreply_id );
						$child_replies[] = $this->response_data( $reply );
					}
				}
			}

			$response = [
				'sub_delete'    => $sub_delete,
				'child_replies' => $child_replies,
				'statistic'     => [
					'replies'   => FMWP()->common()->topic()->get_statistics( $topic_id, 'replies' ),
				],
			];

			if ( $is_subsub ) {
				$parent_reply = get_post( $parent_reply_id );
				if ( ! empty( $parent_reply ) && ! is_wp_error( $parent_reply ) ) {
					$child = FMWP()->common()->reply()->build_replies_avatars( $parent_reply );
					if ( count( $child ) > 2 ) {
						$child = array_slice( $child, 0, 2 );
					}

					$response['parent_data'] = [
						'has_children'  => true,
						'total_replies' => FMWP()->common()->reply()->get_child_replies_count( $parent_reply_id ),
						'answers'       => $child,
						'more_answers'  => count( $child ) > 2 ? true : false,
					];
				}

				$parent_parent_reply = get_post( $parent_parent_reply_id );
				if ( ! empty( $parent_parent_reply ) && ! is_wp_error( $parent_parent_reply ) ) {
					$child = FMWP()->common()->reply()->build_replies_avatars( $parent_parent_reply );
					if ( count( $child ) > 2 ) {
						$child = array_slice( $child, 0, 2 );
					}

					$response['parent_parent_data'] = [
						'has_children'  => true,
						'total_replies' => FMWP()->common()->reply()->get_child_replies_count( $parent_parent_reply_id ),
						'answers'       => $child,
						'more_answers'  => count( $child ) > 2 ? true : false,
					];
				}
			} elseif ( $is_sub ) {
				$parent_reply = get_post( $parent_reply_id );
				if ( ! empty( $parent_reply ) && ! is_wp_error( $parent_reply ) ) {
					$child = FMWP()->common()->reply()->build_replies_avatars( $parent_reply );
					if ( count( $child ) > 2 ) {
						$child = array_slice( $child, 0, 2 );
					}

					$response['parent_data'] = [
						'has_children'  => true,
						'total_replies' => FMWP()->common()->reply()->get_child_replies_count( $parent_reply_id ),
						'answers'       => $child,
						'more_answers'  => count( $child ) > 2 ? true : false,
					];
				}
			}

			wp_send_json_success( $response );
		}


		/**
		 * AJAX handler for marking reply as spam
		 *
		 * @version 2.0
		 */
		function spam() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['reply_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$reply_id = absint( $_POST['reply_id'] );
			$reply = get_post( $reply_id );

			if ( empty( $reply ) || is_wp_error( $reply ) ) {
				wp_send_json_error( __( 'Reply ID is invalid', 'forumwp' ) );
			}

			if ( ! FMWP()->user()->can_spam_reply( get_current_user_id(), $reply ) ) {
				wp_send_json_error( __( 'You do not have the ability to mark this reply as spam', 'forumwp' ) );
			}

			FMWP()->common()->reply()->spam( $reply_id );

			$reply = get_post( $reply_id );

			wp_send_json_success( [
				'dropdown_actions' => FMWP()->common()->reply()->actions_list( $reply ),
			] );
		}


		/**
		 * AJAX handler for restore reply from spam
		 *
		 * @version 2.0
		 */
		function restore_spam() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['reply_id'] ) ) {
				wp_send_json_error( __( 'Invalid data', 'forumwp' ) );
			}

			$reply_id = absint( $_POST['reply_id'] );
			$reply = get_post( $reply_id );

			if ( empty( $reply ) || is_wp_error( $reply ) ) {
				wp_send_json_error( __( 'Reply ID is invalid', 'forumwp' ) );
			}

			if ( ! FMWP()->user()->can_restore_spam_reply( get_current_user_id(), $reply ) ) {
				wp_send_json_error( __( 'You do not have the ability to restore this reply from spam', 'forumwp' ) );
			}

			FMWP()->common()->reply()->restore_spam( $reply_id );

			$reply = get_post( $reply_id );

			wp_send_json_success( [
				'dropdown_actions' => FMWP()->common()->reply()->actions_list( $reply ),
			] );
		}


		/**
		 * AJAX handler for reporting reply
		 *
		 * @version 2.0
		 */
		function report() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['reply_id'] ) ) {
				wp_send_json_error( __( 'Empty Reply ID', 'forumwp' ) );
			}

			$reply_id = absint( $_POST['reply_id'] );
			$user_id = get_current_user_id();

			$report_id = FMWP()->reports()->add( $reply_id, $user_id );
			if ( empty( $report_id ) ) {
				wp_send_json_error( __( 'Something wrong with reports', 'forumwp' ) );
			}

			$reply = get_post( $reply_id );
			wp_send_json_success( [
				'dropdown_actions' => FMWP()->common()->reply()->actions_list( $reply ),
			] );
		}


		/**
		 * AJAX handler for un-reporting reply
		 *
		 * @version 2.0
		 */
		function unreport() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['reply_id'] ) ) {
				wp_send_json_error( __( 'Empty Reply ID', 'forumwp' ) );
			}

			$reply_id = absint( $_POST['reply_id'] );
			$removed = FMWP()->reports()->remove( $reply_id, get_current_user_id() );

			if ( ! $removed ) {
				wp_send_json_error( __( 'Security Issue', 'forumwp' ) );
			}

			$reply = get_post( $reply_id );
			wp_send_json_success( [
				'dropdown_actions' => FMWP()->common()->reply()->actions_list( $reply ),
			] );
		}


		/**
		 * AJAX handler for clear reply reports
		 *
		 * @version 2.0
		 */
		function clear_reports() {
			FMWP()->ajax()->check_nonce( 'fmwp-frontend-nonce' );

			if ( empty( $_POST['reply_id'] ) ) {
				wp_send_json_error( __( 'Empty Reply ID', 'forumwp' ) );
			}

			$reply_id = absint( $_POST['reply_id'] );

			$removed = FMWP()->reports()->clear( $reply_id );

			if ( $removed ) {
				$reply = get_post( $reply_id );
				wp_send_json_success( [
					'dropdown_actions' => FMWP()->common()->reply()->actions_list( $reply ),
				] );
			} else {
				wp_send_json_error( __( 'Security Issue', 'forumwp' ) );
			}
		}
	}
}