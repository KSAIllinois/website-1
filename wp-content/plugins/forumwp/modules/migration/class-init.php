<?php
namespace fmwpm\migration;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwpm\migration\Init' ) ) {


	/**
	 * Class Init
	 *
	 * @package fmwpm\migration
	 */
	final class Init {


		/**
		 * @var string
		 */
		private $slug = 'migration';


		/**
		 * @var int
		 */
		private $posts_per_page = 15;


		/**
		 * @var array
		 */
		private $reply_nesting_flow = [];


		/**
		 * @var
		 */
		private static $instance;


		/**
		 * @return Init
		 */
		static public function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}


		/**
		 * Init constructor.
		 */
		function __construct() {
			if ( FMWP()->is_request( 'admin' ) ) {
				add_filter( 'fmwp_settings_custom_subtabs', [ &$this, 'add_custom_subtab' ], 10, 2 );
				add_filter( 'fmwp_modules_settings_sections', [ &$this, 'add_settings_section' ], 10, 1 );
				add_filter( 'fmwp_settings_section_modules_migration_content', [ &$this, 'add_settings_migration_section' ], 10, 1 );

				add_action( 'admin_enqueue_scripts', [ &$this, 'add_scripts' ] );
			}

			if ( FMWP()->is_request( 'ajax' ) ) {
				add_action( 'wp_ajax_fmwp_get_bbpress_forums_count', [ $this, 'get_forums_count' ] );
				add_action( 'wp_ajax_fmwp_get_bbpress_topics_count', [ $this, 'get_topics_count' ] );
				add_action( 'wp_ajax_fmwp_get_bbpress_topic_tags_count', [ $this, 'get_topic_tags_count' ] );
				add_action( 'wp_ajax_fmwp_get_bbpress_replies_count', [ $this, 'get_replies_count' ] );

				add_action( 'wp_ajax_fmwp_run_migration_forums_bbpress', [ $this, 'fmwp_run_migration_forums_bbpress' ] );
				add_action( 'wp_ajax_fmwp_run_migration_topics_bbpress', [ $this, 'fmwp_run_migration_topics_bbpress' ] );
				add_action( 'wp_ajax_fmwp_run_migration_topic_tags_bbpress', [ $this, 'fmwp_run_migration_topic_tags_bbpress' ] );

				add_action( 'wp_ajax_fmwp_run_migration_replies_bbpress', [ $this, 'fmwp_run_migration_replies_bbpress' ] );
				add_action( 'wp_ajax_fmwp_migration_finished', [ $this, 'fmwp_migration_finished' ] );
			}
		}


		function add_scripts() {
			if ( FMWP()->admin()->is_own_screen() ) {
				wp_enqueue_script( 'wp-i18n' );
			}
		}


		/**
		 * Initialize "Migration" subtab as custom
		 *
		 * @param $subtabs
		 * @param $tab
		 *
		 * @return array
		 */
		function add_custom_subtab( $subtabs, $tab ) {
			if ( $tab == 'modules' ) {
				$subtabs[] = 'migration';
			}

			return $subtabs;
		}


		/**
		 * Add module's settings section
		 *
		 * @param $sections
		 *
		 * @return mixed
		 */
		function add_settings_section( $sections ) {
			$data = FMWP()->modules()->get_data( $this->slug );

			$sections[ $this->slug ] = [
				'title'     => $data['title'],
				'fields'    => [],
			];

			return $sections;
		}


		/**
		 * Settings section content
		 *
		 * @param string $content
		 * @return string
		 */
		function add_settings_migration_section( $content ) {

			ob_start(); ?>

			<div class="fmwp-wrap">
				<h3><?php _e( 'Migration forums from bbPress', 'forumwp' ) ?></h3>
				<ul style="list-style: inside;">
					<li><?php _e( 'Create full site\'s backup.', 'forumwp' ) ?></li>
					<li><?php _e( 'Set maintenance mode (if you need)', 'forumwp' ) ?></li>
					<li><?php _e( 'You have nice Internet connection', 'forumwp' ) ?></li>
				</ul>
				<p><?php _e( 'After the click to "Run migration" button, the migration process will be started. All information will be displayed in "migration Log" field.', 'forumwp' ); ?></p>
				<p><?php _e( 'If the migration was successful, you will see a corresponding message. Otherwise, contact technical support if the migration failed.', 'forumwp' ); ?></p>
				<div id="migration_log" style="width: 100%;height:300px; overflow: auto;border: 1px solid #a1a1a1;margin: 0 0 10px 0;"></div>
				<div>
					<input type="button" id="run_migration" class="button button-primary" value="<?php esc_attr_e( 'Run migration', 'forumwp' ) ?>"/>
				</div>
			</div>

			<script type="text/javascript">
				var posts_pages;
				var current_page = 1;
				var posts_per_page = <?php echo $this->posts_per_page ?>;

				jQuery( document ).ready( function() {
					jQuery( '#run_migration' ).click( function() {
						jQuery(this).prop( 'disabled', true );

						fmwp_add_migration_log( wp.i18n.__( 'Migration Process Started...', 'forumwp' ) );
						fmwp_add_migration_log( wp.i18n.__( 'Get bbPress forums...', 'forumwp' ) );

						wp.ajax.send( 'fmwp_get_bbpress_forums_count', {
							data: {
								nonce: fmwp_admin_data.nonce
							},
							success: function( response ) {
								fmwp_add_migration_log( wp.i18n.__( 'There are ' + response.count + ' bbPress forums' , 'forumwp' ) );
								posts_pages = Math.ceil( response.count / posts_per_page );
								fmwp_forums_migration();
							},
							error: function() {
								fmwp_wrong_ajax();
							}
						} );
					});
				});


				function fmwp_forums_migration() {
					if ( current_page <= posts_pages ) {
						wp.ajax.send( 'fmwp_run_migration_forums_bbpress', {
							data: {
								page: current_page,
								nonce: fmwp_admin_data.nonce
							},
							success: function( response ) {
								fmwp_add_migration_log( response.message );
								current_page++;
								fmwp_forums_migration();
							},
							error: function() {
								fmwp_wrong_ajax();
							}
						} );
					} else {
						posts_pages = 0;
						current_page = 1;
						fmwp_topics_count();
					}
				}


				function fmwp_topics_count() {
					fmwp_add_migration_log( wp.i18n.__( 'Get bbPress topics...', 'forumwp' ) );

					wp.ajax.send( 'fmwp_get_bbpress_topics_count', {
						data: {
							nonce: fmwp_admin_data.nonce
						},
						success: function( response ) {
							fmwp_add_migration_log( wp.i18n.__( 'There are ' + response.count + ' bbPress topics' , 'forumwp' ) );
							posts_pages = Math.ceil( response.count / posts_per_page );
							fmwp_topics_migration();
						},
						error: function() {
							fmwp_wrong_ajax();
						}
					} );
				}


				function fmwp_topics_migration() {
					if ( current_page <= posts_pages ) {
						wp.ajax.send( 'fmwp_run_migration_topics_bbpress', {
							data: {
								page: current_page,
								nonce: fmwp_admin_data.nonce
							},
							success: function( response ) {
								fmwp_add_migration_log( response.message );
								current_page++;
								fmwp_topics_migration();
							},
							error: function() {
								fmwp_wrong_ajax();
							}
						} );
					} else {
						posts_pages = 0;
						current_page = 1;

						<?php if ( FMWP()->options()->get( 'topic_tags' ) ) { ?>
							fmwp_topic_tags_count();
						<?php } else { ?>
							fmwp_replies_count();
						<?php } ?>
					}
				}


				function fmwp_topic_tags_count() {
					fmwp_add_migration_log( wp.i18n.__( 'Get bbPress topic tags...', 'forumwp' ) );

					wp.ajax.send( 'fmwp_get_bbpress_topic_tags_count', {
						data: {
							nonce: fmwp_admin_data.nonce
						},
						success: function( response ) {
							fmwp_add_migration_log( wp.i18n.__( 'There are ' + response.count + ' bbPress topic tags' , 'forumwp' ) );
							posts_pages = Math.ceil( response.count / posts_per_page );
							fmwp_topic_tags_migration();
						},
						error: function() {
							fmwp_wrong_ajax();
						}
					} );
				}


				function fmwp_topic_tags_migration() {
					if ( current_page <= posts_pages ) {
						wp.ajax.send( 'fmwp_run_migration_topic_tags_bbpress', {
							data: {
								page: current_page,
								nonce: fmwp_admin_data.nonce
							},
							success: function( response ) {
								fmwp_add_migration_log( response.message );
								current_page++;
								fmwp_topic_tags_migration();
							},
							error: function() {
								fmwp_wrong_ajax();
							}
						} );
					} else {
						posts_pages = 0;
						current_page = 1;

						fmwp_replies_count();
					}
				}


				function fmwp_replies_count() {
					fmwp_add_migration_log( wp.i18n.__( 'Get bbPress replies...', 'forumwp' ) );

					wp.ajax.send( 'fmwp_get_bbpress_replies_count', {
						data: {
							nonce: fmwp_admin_data.nonce
						},
						success: function( response ) {
							fmwp_add_migration_log( wp.i18n.__( 'There are ' + response.count + ' bbPress replies' , 'forumwp' ) );
							posts_pages = Math.ceil( response.count / posts_per_page );
							fmwp_replies_migration();
						},
						error: function() {
							fmwp_wrong_ajax();
						}
					} );
				}


				function fmwp_replies_migration() {
					if ( current_page <= posts_pages ) {
						wp.ajax.send( 'fmwp_run_migration_replies_bbpress', {
							data: {
								page: current_page,
								nonce: fmwp_admin_data.nonce
							},
							success: function( response ) {
								fmwp_add_migration_log( response.message );
								current_page++;
								fmwp_replies_migration();
							},
							error: function() {
								fmwp_wrong_ajax();
							}
						} );
					} else {
						posts_pages = 0;
						current_page = 1;
						fmwp_finish_migration();
					}
				}


				function fmwp_finish_migration() {
					fmwp_add_migration_log( wp.i18n.__( 'Finishing migration...', 'forumwp' ) );

					wp.ajax.send( 'fmwp_migration_finished', {
						data: {
							nonce: fmwp_admin_data.nonce
						},
						success: function( response ) {
							fmwp_add_migration_log( response.message );

							window.location = '';
						},
						error: function() {
							fmwp_wrong_ajax();
						}
					} );
				}


				/**
				 *
				 * @param line
				 */
				function fmwp_add_migration_log( line ) {
					var log_field = jQuery( '#migration_log' );
					var previous_html = log_field.html();
					log_field.html( previous_html + line + '<br />' );
				}

				function fmwp_wrong_ajax() {
					fmwp_add_migration_log( wp.i18n.__( 'Wrong AJAX response...', 'forumwp' ) );
					fmwp_add_migration_log( wp.i18n.__( 'Your migration was crashed, please contact with support', 'forumwp' ) );
				}
			</script>

			<?php $content = ob_get_clean();
			return $content;
		}


		/**
		 * Get forums count
		 */
		function get_forums_count() {
			FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

			global $wpdb;
			$forums_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'forum'" );

			wp_send_json_success( [ 'count' => $forums_count ] );
		}


		/**
		 * AJAX response for forums migrations
		 *
		 */
		function fmwp_run_migration_forums_bbpress() {
			FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

			$current_page = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

			$forums = get_posts( [
				'post_type'         => 'forum',
				'posts_per_page'    => $this->posts_per_page,
				'post_status'       => 'any',
				'offset'            => $this->posts_per_page * ( $current_page - 1 ),
			] );

			add_filter( 'fmwp_forum_upgrade_last_update', '__return_false' );
			add_filter( 'fmwp_disable_email_notification_by_hook', '__return_true' );

			foreach ( $forums as $forum ) {
				$this->migrate_forum( $forum );
			}

			$from = ( $current_page * $this->posts_per_page ) - $this->posts_per_page + 1;
			$to = $current_page * $this->posts_per_page;

			wp_send_json_success( [ 'message' => sprintf( __( 'Forums from %d to %d are migrated', 'forumwp' ), $from, $to ) ] );
		}


		/**
		 * @param \WP_Post $forum
		 */
		function migrate_forum( $forum ) {

			$forum_status = 'publish';
			if ( get_post_meta( $forum->ID, '_bbp_status', true ) == 'closed' ) {
				$is_locked = true;
			} else {
				$is_locked = false;
			}

			$visibility = 'public';
			if ( $forum->post_status == 'private' ) {
				$visibility = 'private';
			} elseif ( $forum->post_status == 'hidden' ) {
				$visibility = 'hidden';
			}

			if ( get_post_meta( $forum->ID, '_bbp_forum_type', true ) == 'category' ) {

				if ( FMWP()->options()->get( 'forum_categories' ) ) {
					$this->migrate_current_forum_category( $forum );
				}

			} else {

				$new_data = [
					'post_type'         => 'fmwp_forum',
					'post_parent'       => 0,
					'post_title'        => $forum->post_title,
					'post_author'       => $forum->post_author,
					'post_content'      => $forum->post_content,
					'post_excerpt'      => $forum->post_excerpt,
					'post_status'       => $forum_status,
					'post_date'         => $forum->post_date,
					'post_date_gmt'     => $forum->post_date_gmt,
					'post_modified'     => $forum->post_modified,
					'post_modified_gmt' => $forum->post_modified_gmt,
					'meta_input'        => [
						'fmwp_locked'           => $is_locked,
						'fmwp_visibility'       => $visibility,
						'fmwp_order'            => $forum->menu_order,
						'fmwp_template'         => '',
						'fmwp_icon_bgcolor'     => '',
						'fmwp_icon_color'       => '',
						'fmwp_last_update'      => strtotime( $forum->post_modified ),
						'fmwp_bbpress_forum'    => $forum->ID,
					],
				];


				if ( FMWP()->options()->get( 'forum_categories' ) ) {
					if ( $forum->post_parent != 0 ) {
						$cat_id = $this->migrate_forum_category( $forum );
						$new_data['tax_input'] = [
							'fmwp_forum_category'   => [ $cat_id ],
						];
					}
				}

				$forum_id = wp_insert_post( $new_data );

				update_post_meta( $forum->ID, 'fmwp_migration_forum_id', $forum_id );
			}
		}


		/**
		 * @param \WP_Post $forum
		 *
		 * @return int
		 */
		function migrate_forum_category( $forum ) {
			$category_exists = get_post_meta( $forum->post_parent, 'fmwp_migration_cat_id', true );
			if ( ! empty( $category_exists ) ) {
				return $category_exists;
			}

			$parent_forum = get_post( $forum->post_parent );
			$insert_forum_cat = [
				'taxonomy'              => 'fmwp_forum_category',
				'cat_name'              => $parent_forum->post_title,
				'category_description'  => $parent_forum->post_content,
				'category_nicename'     => $parent_forum->post_name,
			];

			if ( $parent_forum->post_parent != 0 ) {
				$insert_forum_cat['category_parent'] = $this->migrate_forum_category( $parent_forum );
			}

			$cat_id = wp_insert_category( $insert_forum_cat );
			update_post_meta( $forum->post_parent, 'fmwp_migration_cat_id', $cat_id );
			return $cat_id;
		}


		/**
		 * @param \WP_Post $forum
		 *
		 * @return int
		 */
		function migrate_current_forum_category( $forum ) {
			$category_exists = get_post_meta( $forum->ID, 'fmwp_migration_cat_id', true );
			if ( ! empty( $category_exists ) ) {
				return $category_exists;
			}

			$insert_forum_cat = [
				'taxonomy'              => 'fmwp_forum_category',
				'cat_name'              => $forum->post_title,
				'category_description'  => $forum->post_content,
				'category_nicename'     => $forum->post_name,
			];

			if ( $forum->post_parent != 0 ) {
				$parent_forum = get_post( $forum->post_parent );
				$insert_forum_cat['category_parent'] = $this->migrate_current_forum_category( $parent_forum );
			}

			$cat_id = wp_insert_category( $insert_forum_cat );
			update_post_meta( $forum->post_parent, 'fmwp_migration_cat_id', $cat_id );
			return $cat_id;
		}


		/**
		 * Get topics count
		 */
		function get_topics_count() {
			FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

			global $wpdb;
			$topics_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'topic'" );

			wp_send_json_success( [ 'count' => $topics_count ] );
		}


		/**
		 *
		 */
		function fmwp_run_migration_topics_bbpress() {
			FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

			$current_page = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

			$topics = get_posts( [
				'post_type'         => 'topic',
				'posts_per_page'    => $this->posts_per_page,
				'post_status'       => 'any',
				'offset'            => $this->posts_per_page * ( $current_page - 1 ),
			] );

			add_filter( 'fmwp_topic_upgrade_last_update', '__return_false' );
			add_filter( 'fmwp_disable_email_notification_by_hook', '__return_true' );

			foreach ( $topics as $topic ) {
				$this->migrate_topic( $topic );
			}

			$from = ( $current_page * $this->posts_per_page ) - $this->posts_per_page + 1;
			$to = $current_page * $this->posts_per_page;

			wp_send_json_success( [ 'message' => sprintf( __( 'Topics from %d to %d are migrated', 'forumwp' ), $from, $to ) ] );
		}


		/**
		 * @param \WP_Post $topic
		 */
		function migrate_topic( $topic ) {

			$post_status = $topic->post_status;

			$is_spam = false;
			if ( $topic->post_status == 'spam' ) {
				$post_status = 'publish';
				$is_spam = true;
			}

			if ( $topic->post_status == 'closed' ) {
				$post_status = 'publish';
				$is_locked = true;
			} else {
				$is_locked = false;
			}

			$last_update = strtotime( get_post_meta( $topic->ID, '_bbp_last_active_time', true ) );

			$topic_type = 'normal';
			$is_global = false;
			$supersticky = get_option( '_bbp_super_sticky_topics' );
			if ( ! empty( $supersticky ) ) {
				if ( in_array( $topic->ID, $supersticky ) ) {
					$is_global = true;
					$topic_type = 'global';
				}
			}

			if ( ! $is_global ) {
				$sticky = get_post_meta( $topic->post_parent, '_bbp_sticky_topics', true );
				if ( ! empty( $sticky ) ) {
					if ( in_array( $topic->ID, $sticky ) ) {
						$topic_type = 'pinned';
					}
				}
			}

			$type_order = FMWP()->common()->get_type_order( $topic_type );

			$parent_forum = ! empty( $topic->post_parent ) ? get_post_meta( $topic->post_parent, 'fmwp_migration_forum_id', true ) :  FMWP()->options()->get( 'default_forum' );
			$parent_forum = ! empty( $parent_forum ) ? $parent_forum : FMWP()->options()->get( 'default_forum' );
			$parent_forum = ! empty( $parent_forum ) ? $parent_forum : 0;

			$new_data = [
				'post_type'         => 'fmwp_topic',
				'post_parent'       => 0,
				'post_title'        => $topic->post_title,
				'post_content'      => $topic->post_content,
				'post_author'       => $topic->post_author,
				'post_excerpt'      => $topic->post_excerpt,
				'post_status'       => $post_status,
				'post_date'         => $topic->post_date,
				'post_date_gmt'     => $topic->post_date_gmt,
				'post_modified'     => $topic->post_modified,
				'post_modified_gmt' => $topic->post_modified_gmt,
				'meta_input'        => [
					'fmwp_spam'             => $is_spam,
					'fmwp_locked'           => $is_locked,
					'fmwp_last_update'      => $last_update,
					'fmwp_views'            => 0,
					'fmwp_forum'            => $parent_forum,
					'fmwp_type'             => $topic_type,
					'fmwp_type_order'       => $type_order,
					'fmwp_original_content' => $topic->post_content,
					'fmwp_template'         => '',
				],
			];

			$fmwp_topic_id = wp_insert_post( $new_data, true );

			if ( FMWP()->options()->get( 'topic_tags' ) ) {
				$topic_tags = wp_get_post_terms( $topic->ID, 'topic-tag', [ 'fields' => 'all' ] );

				if ( ! empty( $topic_tags ) && ! is_wp_error( $topic_tags ) ) {
					foreach ( $topic_tags as $tag ) {
						$tag_id = get_term_meta( $tag->term_id, 'fmwp_migrated_tag_id', true );
						if ( empty( $tag_id ) ) {
							$new_tag = wp_insert_term( $tag->name, 'fmwp_topic_tag', [
								'description' => $tag->description,
								'parent'      => 0,
								'slug'        => $tag->slug,
							] );

							if ( ! is_wp_error( $new_tag ) && isset( $new_tag['term_id'] ) ) {
								$tag_id = $new_tag['term_id'];
								update_term_meta( $tag->term_id, 'fmwp_migrated_tag_id', $tag_id );
							}
						}

						if ( ! empty( $tag_id ) ) {
							wp_set_post_terms( $fmwp_topic_id, [ absint( $tag_id ) ], 'fmwp_topic_tag' );
						}
					}
				}
			}

			update_post_meta( $topic->ID, 'fmwp_migration_topic_id', $fmwp_topic_id );
		}


		/**
		 * Get topic tags count
		 */
		function get_topic_tags_count() {
			FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

			$terms_count = get_terms( [
				'taxonomy'      => 'topic-tag',
				'hide_empty'    => false,
				'count'         => true,
			] );

			$count = ! empty( $terms_count ) ? count( $terms_count ) : 0;

			wp_send_json_success( [ 'count' => $count ] );
		}


		/**
		 *
		 */
		function fmwp_run_migration_topic_tags_bbpress() {
			FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

			$current_page = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

			$topic_tags = get_terms( [
				'taxonomy'      => 'topic-tag',
				'hide_empty'    => false,
				'number'        => $this->posts_per_page,
				'offset'        => $this->posts_per_page * ( $current_page - 1 ),
			] );

			foreach ( $topic_tags as $topic_tag ) {
				$this->migrate_topic_tag( $topic_tag );
			}

			$from = ( $current_page * $this->posts_per_page ) - $this->posts_per_page + 1;
			$to = $current_page * $this->posts_per_page;

			wp_send_json_success( [ 'message' => sprintf( __( 'Topic tags from %d to %d are migrated', 'forumwp' ), $from, $to ) ] );
		}


		/**
		 * @param \WP_Term $tag
		 */
		function migrate_topic_tag( $tag ) {
			$tag_id = get_term_meta( $tag->term_id, 'fmwp_migrated_tag_id', true );
			if ( ! empty( $tag_id ) ) {
				return;
			}

			$new_tag = wp_insert_term( $tag->name, 'fmwp_topic_tag', [
				'description' => $tag->description,
				'parent'      => 0,
				'slug'        => $tag->slug,
			] );

			if ( ! is_wp_error( $new_tag ) && isset( $new_tag['term_id'] ) ) {
				update_term_meta( $tag->term_id, 'fmwp_migrated_tag_id', $new_tag['term_id'] );
			}
		}


		/**
		 * Get replies count
		 */
		function get_replies_count() {
			FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

			global $wpdb;
			$replies_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'reply'" );

			wp_send_json_success( [ 'count' => $replies_count ] );
		}


		/**
		 *
		 */
		function fmwp_run_migration_replies_bbpress() {
			FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

			$current_page = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

			$replies = get_posts( [
				'post_type'         => 'reply',
				'posts_per_page'    => $this->posts_per_page,
				'post_status'       => 'any,trash,spam',
				'offset'            => $this->posts_per_page * ( $current_page - 1 ),
			] );

			add_filter( 'fmwp_reply_upgrade_last_update', '__return_false' );
			add_filter( 'fmwp_disable_email_notification_by_hook', '__return_true' );

			foreach ( $replies as $reply ) {
				$this->migrate_reply( $reply );
			}

			$from = ( $current_page * $this->posts_per_page ) - $this->posts_per_page + 1;
			$to = $current_page * $this->posts_per_page;

			wp_send_json_success( [ 'message' => sprintf( __( 'Replies from %d to %d are migrated', 'forumwp' ), $from, $to ) ] );
		}


		/**
		 * @param $reply_id
		 *
		 * @return mixed
		 */
		function fill_parent_flow( $reply_id ) {
			$parent_reply_id = get_post_meta( $reply_id, '_bbp_reply_to', true );
			$this->reply_nesting_flow[] = $parent_reply_id;

			if ( ! in_array( '', $this->reply_nesting_flow ) ) {
				$parent_reply_id = $this->fill_parent_flow( $parent_reply_id );
			}

			return $parent_reply_id;
		}


		/**
		 * @param $reply_id
		 *
		 * @return int
		 */
		function get_parent_reply( $reply_id ) {
			$this->reply_nesting_flow = [];

			$this->fill_parent_flow( $reply_id );

			$this->reply_nesting_flow = array_reverse( $this->reply_nesting_flow );

			if ( isset( $this->reply_nesting_flow[2] ) ) {
				return $this->reply_nesting_flow[2];
			} elseif ( isset( $this->reply_nesting_flow[1] ) ) {
				return $this->reply_nesting_flow[1];
			} else {
				return 0;
			}
		}


		/**
		 * @param \WP_Post $reply
		 *
		 * @return int|mixed|\WP_Error
		 */
		function migrate_reply( $reply ) {
			$reply_exists = get_post_meta( $reply->ID, 'fmwp_reply_migration_id', true );
			if ( ! empty( $reply_exists ) ) {
				return $reply_exists;
			}

			$forum_id = get_post_meta( $reply->ID, '_bbp_forum_id', true );
			if ( empty( $forum_id ) || $forum_id == $reply->ID ) {
				return 0;
			}

			$forum_id = get_post_meta( $forum_id, 'fmwp_migration_forum_id', true );
			if ( empty( $forum_id ) ) {
				return 0;
			}

			$topic_id = get_post_meta( $reply->ID, '_bbp_topic_id', true );
			if ( empty( $topic_id ) || $topic_id == $reply->ID ) {
				return 0;
			}

			$topic_id = get_post_meta( $topic_id, 'fmwp_migration_topic_id', true );
			if ( empty( $topic_id ) ) {
				return 0;
			}

			$post_status = $reply->post_status;

			$is_spam = false;
			if ( $reply->post_status == 'spam' ) {
			    $is_spam = true;
				$post_status = 'publish';
			}

			$post_parent = 0;
			$post_parent_id = $this->get_parent_reply( $reply->ID );
			if ( ! empty( $post_parent_id ) ) {
				$reply_parent = get_post( $post_parent_id );

				if ( ! is_wp_error( $reply_parent ) && $reply_parent->post_type = 'reply' ) {
					$post_parent = $this->migrate_reply( $reply_parent );
				}
			}

			$topic_title = get_the_title( $topic_id );
			$title = sprintf( __( 'Reply To: %s', 'forumwp' ), $topic_title );

			$new_data = [
				'post_type'         => 'fmwp_reply',
				'post_parent'       => $post_parent,
				'post_title'        => $title,
				'post_content'      => $reply->post_content,
				'post_author'       => $reply->post_author,
				'post_excerpt'      => $reply->post_excerpt,
				'post_status'       => $post_status,
				'post_date'         => $reply->post_date,
				'post_date_gmt'     => $reply->post_date_gmt,
				'post_modified'     => $reply->post_modified,
				'post_modified_gmt' => $reply->post_modified_gmt,
				'meta_input'        => [
					'fmwp_spam'             => $is_spam,
					'fmwp_forum'            => $forum_id,
					'fmwp_topic'            => $topic_id,
					'fmwp_original_content' => $reply->post_content,
					'fmwp_last_update'      => strtotime( $reply->post_modified ),
				],
			];

			$reply_id = wp_insert_post( $new_data, true );

			update_post_meta( $reply->ID, 'fmwp_reply_migration_id', $reply_id );

			return $reply_id;
		}


		/**
		 * AJAX response for finishing migration
		 */
		function fmwp_migration_finished() {
			FMWP()->ajax()->check_nonce( 'fmwp-backend-nonce' );

			global $wpdb;

			$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'fmwp_migration_cat_id' OR meta_key = 'fmwp_reply_migration_id' OR meta_key = 'fmwp_migration_forum_id' OR meta_key = 'fmwp_migration_topic_id'" );

			wp_send_json_success( [ 'message' => __( 'Migration finished', 'forumwp' ) ] );
		}
	}
}