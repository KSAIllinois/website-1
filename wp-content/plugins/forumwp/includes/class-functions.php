<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'FMWP_Functions' ) ) {


	/**
	 * Class FMWP_Functions
	 */
	class FMWP_Functions {


		/**
		 * @var bool CPU Links Structure
		 */
		var $is_permalinks = false;


		/**
		 * @var string
		 */
		var $templates_path = '';


		/**
		 * @var string
		 */
		var $theme_templates = '';


		/**
		 * FMWP_Functions constructor.
		 */
		function __construct() {
		}


		/**
		 * Get current URL anywhere
		 *
		 * @param bool $no_query_params
		 *
		 * @return string
		 */
		function get_current_url( $no_query_params = false ) {
			//use WP native function for fill $_SERVER variables by correct values
			wp_fix_server_vars();

			$page_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

			if ( $no_query_params == true ) {
				$page_url = strtok( $page_url, '?' );
			}

			return apply_filters( 'fmwp_get_current_page_url', $page_url );
		}


		/**
		 * Get datetime format from WP native option
		 *
		 * @param string $data
		 *
		 * @return string
		 */
		function datetime_format( $data = 'all' ) {
			switch ( $data ) {
				case 'date':
					$date = get_option( 'date_format' );
					break;
				case 'time':
					$date = get_option( 'time_format' );
					break;
				case 'all':
				default:
					$date = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
					break;
			}

			return $date;
		}


		/**
		 * @param $array
		 * @param $key
		 * @param $insert_array
		 *
		 * @return mixed
		 */
		function array_insert_before( $array, $key, $insert_array ) {
			$index = array_search( $key, array_keys( $array ) );
			if ( $index === false ) {
				return $array;
			}

			$array = array_slice( $array, 0, $index, true ) +
				   $insert_array +
				   array_slice( $array, $index, count( $array ) - 1, true );

			return $array;
		}


		/**
		 * @param $array
		 * @param $key
		 * @param $insert_array
		 *
		 * @return mixed
		 */
		function array_insert_after( $array, $key, $insert_array ) {
			$index = array_search( $key, array_keys( $array ) );
			if ( $index === false ) {
				return $array;
			}

			$array = array_slice( $array, 0, $index + 1, true ) +
				   $insert_array +
				   array_slice( $array, $index + 1, count( $array ) - 1, true );

			return $array;
		}


		/**
		 * What type of request is this?
		 *
		 * @param string $type String containing name of request type (ajax, frontend, cron or admin)
		 *
		 * @return bool
		 */
		public function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
				case 'ajax' :
					return defined( 'DOING_AJAX' );
				case 'cron' :
					return defined( 'DOING_CRON' );
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}

			return false;
		}


		/**
		 * Get Custom Post Types arguments
		 *
		 * @return array
		 */
		function get_cpt() {
			$cpt = [
				'fmwp_forum' => [
					'labels'              => [
						'name'                  => __( 'Forums', 'forumwp' ),
						'singular_name'         => __( 'Forum', 'forumwp' ),
						'menu_name'             => _x( 'Forums', 'Admin menu name', 'forumwp' ),
						'add_new'               => __( 'Add New Forum', 'forumwp' ),
						'add_new_item'          => __( 'Add New Forum', 'forumwp' ),
						'edit'                  => __( 'Edit', 'forumwp' ),
						'edit_item'             => __( 'Edit Forum', 'forumwp' ),
						'new_item'              => __( 'New Forum', 'forumwp' ),
						'view'                  => __( 'View Forum', 'forumwp' ),
						'view_item'             => __( 'View Forum', 'forumwp' ),
						'search_items'          => __( 'Search Forums', 'forumwp' ),
						'not_found'             => __( 'No Forums found', 'forumwp' ),
						'not_found_in_trash'    => __( 'No Forums found in trash', 'forumwp' ),
						'parent'                => __( 'Parent Forum', 'forumwp' ),
						'featured_image'        => __( 'Forum Image', 'forumwp' ),
						'set_featured_image'    => __( 'Set forum image', 'forumwp' ),
						'remove_featured_image' => __( 'Remove forum image', 'forumwp' ),
						'use_featured_image'    => __( 'Use as forum image', 'forumwp' ),
					],
					'description'         => __( 'This is where you can add new forums.', 'forumwp' ),
					'public'              => true,
					'menu_icon'           => 'dashicons-format-chat',
					'show_ui'             => true,
					'capability_type'     => 'fmwp_forum',
					'show_in_menu'        => false,
					'map_meta_cap'        => true,
					'capabilities'        => [ 'create_posts' => 'create_fmwp_forums', ],
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'hierarchical'        => false,
					'rewrite'             => [
						'slug'       => FMWP()->options()->get( 'forum_slug' ),
						'with_front' => false,
						'feeds'      => true,
					],
					'query_var'           => true,
					'supports'            => [ 'title', 'editor', 'thumbnail', 'author', 'excerpt' ],
					'has_archive'         => false,
					'show_in_nav_menus'   => false,
					'show_in_rest'        => true,
					'taxonomies'          => [ 'fmwp_forum_category' ],
				],
				'fmwp_topic' => [
					'labels'              => [
						'name'               => __( 'Topics', 'forumwp' ),
						'singular_name'      => __( 'Topic', 'forumwp' ),
						'menu_name'          => _x( 'Topics', 'Admin menu name', 'forumwp' ),
						'add_new'            => __( 'Add Topic', 'forumwp' ),
						'add_new_item'       => __( 'Add New Topic', 'forumwp' ),
						'edit'               => __( 'Edit', 'forumwp' ),
						'edit_item'          => __( 'Edit Topic', 'forumwp' ),
						'new_item'           => __( 'New Topic', 'forumwp' ),
						'view'               => __( 'View Topic', 'forumwp' ),
						'view_item'          => __( 'View Topic', 'forumwp' ),
						'search_items'       => __( 'Search Topics', 'forumwp' ),
						'not_found'          => __( 'No Topics found', 'forumwp' ),
						'not_found_in_trash' => __( 'No Topics found in trash', 'forumwp' ),
					],
					'public'              => true,
					'show_ui'             => true,
					'capability_type'     => 'fmwp_topic',
					'show_in_menu'        => false,
					'map_meta_cap'        => true,
					'capabilities'        => [ 'create_posts' => 'create_fmwp_topics' ],
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'hierarchical'        => false,
					'rewrite'             => [
						'slug'       => FMWP()->options()->get( 'topic_slug' ),
						'with_front' => false,
						'feeds'      => true,
					],
					'query_var'           => true,
					'supports'            => [ 'title', 'editor', 'author' ],
					'has_archive'         => false,
					'show_in_nav_menus'   => false,
					'show_in_rest'        => true,
					'taxonomies'          => [ 'fmwp_topic_tag' ],
				],
				'fmwp_reply' => [
					'labels'              => [
						'name'               => __( 'Replies', 'forumwp' ),
						'singular_name'      => __( 'Reply', 'forumwp' ),
						'menu_name'          => _x( 'Replies', 'Admin menu name', 'forumwp' ),
						'add_new'            => __( 'Add Reply', 'forumwp' ),
						'add_new_item'       => __( 'Add New Reply', 'forumwp' ),
						'edit'               => __( 'Edit', 'forumwp' ),
						'edit_item'          => __( 'Edit Reply', 'forumwp' ),
						'new_item'           => __( 'New Reply', 'forumwp' ),
						'view'               => __( 'View Reply', 'forumwp' ),
						'view_item'          => __( 'View Reply', 'forumwp' ),
						'search_items'       => __( 'Search Replies', 'forumwp' ),
						'not_found'          => __( 'No Replies found', 'forumwp' ),
						'not_found_in_trash' => __( 'No Replies found in trash', 'forumwp' ),
					],
					'public'              => true,
					'show_ui'             => true,
					'capability_type'     => [ 'fmwp_reply', 'fmwp_replies' ],
					'capabilities'        => [ 'create_posts' => 'create_fmwp_replies' ], //add this capability to remove an ability to create Reply via wp-admin
					'show_in_menu'        => false,
					'map_meta_cap'        => true,
					'publicly_queryable'  => false,
					'exclude_from_search' => false,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => [ 'title', 'editor', 'author' ],
					'has_archive'         => false,
					'show_in_nav_menus'   => false,
					'show_in_rest'        => true,
				],
			];

			if ( ! FMWP()->options()->get( 'forum_categories' ) ) {
				unset( $cpt['fmwp_forum']['taxonomies'] );
			}

			if ( ! FMWP()->options()->get( 'topic_tags' ) ) {
				unset( $cpt['fmwp_topic']['taxonomies'] );
			}

			return apply_filters( 'fmwp_cpt_list', $cpt );
		}


		/**
		 * Get Custom Taxonomies arguments
		 *
		 * @return array
		 */
		function get_taxonomies() {

			$forums_page_id = FMWP()->common()->get_preset_page_id( 'forums' );
			$topics_page_id = FMWP()->common()->get_preset_page_id( 'topics' );

			$forums_slug = '';
			if ( $forums_page_id ) {
				$forums_page = get_post( $forums_page_id );
				if ( ! empty( $forums_page ) && ! is_wp_error( $forums_page ) ) {
					$forums_slug = $forums_page->post_name . '/';
				}
			}

			$topics_slug = '';
			if ( $topics_page_id ) {
				$topics_page = get_post( $topics_page_id );
				if ( ! empty( $topics_page ) && ! is_wp_error( $topics_page ) ) {
					$topics_slug = $topics_page->post_name . '/';
				}
			}

			$taxonomies = [
				'fmwp_forum_category'   => [
					'post_types'    => [ 'fmwp_forum' ],
					'tax_args'      => [
						'labels'        => [
							'name'                       => __( 'Forum Categories', 'forumwp' ),
							'singular_name'              => __( 'Forum Category', 'forumwp' ),
							'menu_name'                  => _x( 'Forum Categories', 'Admin menu name', 'forumwp' ),
							'search_items'               => __( 'Search Forum Categories', 'forumwp' ),
							'all_items'                  => __( 'All Forum Categories', 'forumwp' ),
							'edit_item'                  => __( 'Edit Forum Category', 'forumwp' ),
							'update_item'                => __( 'Update Forum Category', 'forumwp' ),
							'add_new_item'               => __( 'Add New Forum Category', 'forumwp' ),
							'new_item_name'              => __( 'New Forum Category Name', 'forumwp' ),
							'popular_items'              => __( 'Popular Forum Categories', 'forumwp' ),
							'separate_items_with_commas' => __( 'Separate Forum Categories with commas', 'forumwp' ),
							'add_or_remove_items'        => __( 'Add or remove Forum Categories', 'forumwp' ),
							'choose_from_most_used'      => __( 'Choose from the most used Forum Categories', 'forumwp' ),
							'not_found'                  => __( 'No Forum Categories found', 'forumwp' ),
						],
						'hierarchical'  => true,
						'label'         => __( 'Forum Categories', 'forumwp' ),
						'show_ui'       => true,
						'show_in_menu'  => false,
						'query_var'     => true,
						'capabilities'  => [
							'manage_terms' => 'manage_fmwp_forum_categories',
							'edit_terms'   => 'edit_fmwp_forum_categories',
							'delete_terms' => 'delete_fmwp_forum_categories',
							'assign_terms' => 'edit_fmwp_forums',
						],
						'rewrite'       => [
							'slug'          => _x( $forums_slug . FMWP()->options()->get( 'forum_category_slug' ), 'slug', 'forumwp' ),
							'with_front'    => false,
						],
						'show_in_rest'  => true,
					],
				],
				'fmwp_topic_tag'        => [
					'post_types'    => [ 'fmwp_topic' ],
					'tax_args'      => [
						'labels'        => [
							'name'                       => __( 'Topic Tags', 'forumwp' ),
							'singular_name'              => __( 'Topic Tag', 'forumwp' ),
							'menu_name'                  => _x( 'Topic Tags', 'Admin menu name', 'forumwp' ),
							'search_items'               => __( 'Search Topic Tags', 'forumwp' ),
							'all_items'                  => __( 'All Topic Tags', 'forumwp' ),
							'edit_item'                  => __( 'Edit Topic Tag', 'forumwp' ),
							'update_item'                => __( 'Update Topic Tag', 'forumwp' ),
							'add_new_item'               => __( 'Add New Topic Tag', 'forumwp' ),
							'new_item_name'              => __( 'New Topic Tag Name', 'forumwp' ),
							'popular_items'              => __( 'Popular Topic Tags', 'forumwp' ),
							'separate_items_with_commas' => __( 'Separate Topic Tags with commas', 'forumwp' ),
							'add_or_remove_items'        => __( 'Add or remove Topic Tags', 'forumwp' ),
							'choose_from_most_used'      => __( 'Choose from the most used Topic tags', 'forumwp' ),
							'not_found'                  => __( 'No Topic Tags found', 'forumwp' ),
						],
						'hierarchical'  => false,
						'label'         => __( 'Topic Tags', 'forumwp' ),
						'show_ui'       => true,
						'show_in_menu'  => false,
						'query_var'     => true,
						'capabilities'  => [
							'manage_terms' => 'manage_fmwp_topic_tags',
							'edit_terms'   => 'edit_fmwp_topic_tags',
							'delete_terms' => 'delete_fmwp_topic_tags',
							'assign_terms' => 'edit_fmwp_topics',
						],
						'rewrite'       => [
							'slug'          => _x( $topics_slug . FMWP()->options()->get( 'topic_tag_slug' ), 'slug', 'forumwp' ),
							'with_front'    => false,
						],
						'show_in_rest'  => true,
					],
				],
			];

			if ( ! FMWP()->options()->get( 'forum_categories' ) ) {
				unset( $taxonomies['fmwp_forum_category'] );
			}

			if ( ! FMWP()->options()->get( 'topic_tags' ) ) {
				unset( $taxonomies['fmwp_topic_tag'] );
			}

			return apply_filters( 'fmwp_custom_taxonomies_list', $taxonomies );
		}


		/**
		 * Get FMWP Post Statuses
		 *
		 * @return array
		 */
		function get_post_statuses() {
			$order_statuses = apply_filters( 'fmwp_custom_post_statuses', [] );

			return $order_statuses;
		}


		/**
		 * @param $content
		 *
		 * @return mixed
		 */
		function parse_embed( $content ) {
			$arr_urls = wp_extract_urls( $content );
			$site_url = get_site_url( get_current_blog_id() );

			if ( ! empty( $arr_urls ) ) {
				foreach ( $arr_urls as $url ) {
					if ( false !== strpos( $url, $site_url ) ) {
						continue;
					}

					$has_oEmbed = wp_oembed_get( $url );
					if ( $has_oEmbed ) {

						// condition for URLs which are parsed by tinyMCE editor to avoid wrong HTML formatting (iframe into link's href attr)
						if ( false !== strpos( $content, '<a href="' . $url . '">' . $url . '</a>' ) ) {
							$content = str_replace( '<a href="' . $url . '">' . $url . '</a>', $has_oEmbed, $content );
						} else {
							$content = str_replace( $url, $has_oEmbed, $content );
						}

					}
				}
			}

			return $content;
		}


		/**
		 * @return bool
		 */
		function is_topic_page() {
			return is_singular( [ 'fmwp_topic' ] );
		}


		/**
		 * @return bool
		 */
		function is_forum_page() {
			return is_singular( [ 'fmwp_forum' ] );
		}


		/**
		 * @param int $size
		 * @param string $display
		 * @param bool $echo
		 *
		 * @return string
		 */
		function ajax_loader( $size, $display = 'absolute_center', $echo = true ) {
			$this->ajax_loader_styles( $size, $display );

			ob_start(); ?>

			<div class="fmwp-ajax-loading fmwp-ajax-<?php echo $size ?>"></div>

			<?php if ( $echo ) {
				ob_get_flush();
			} else {
				$content = ob_get_clean();
				return $content;
			}
			return '';
		}


		/**
		 * @param $size
		 * @param string $display
		 */
		function ajax_loader_styles( $size, $display = 'absolute_center' ) {
			if ( ! FMWP()->frontend()->shortcodes()->check_preloader_css( $size, $display ) ) {
				$border = round( $size * 0.08 );
				$font_size = round( $size * 0.7 );

				$style = '';
				if ( $display == 'absolute_center' ) {
					$style = 'position:absolute;left: calc(50% - ' . $font_size . 'px);
					top: calc(50% - ' . $font_size . 'px);';
				}

				$custom_css = '.fmwp-ajax-loading.fmwp-ajax-' . $size . ' {
					border-width:' . $border . 'px;
					font-size:' . $font_size . 'px;' . $style . '
					width:' . $size . 'px;
					height:' . $size . 'px;
				}';

				wp_add_inline_style( 'fmwp-common', $custom_css );
			}
		}


		/**
		 * @param $page
		 *
		 * @return bool
		 */
		function is_core_page( $page ) {
			global $post;

			if ( empty( $post ) ) {
				return false;
			}

			$preset_page_id = FMWP()->common()->get_preset_page_id( $page );
			if ( isset( $post->ID ) && ! empty( $preset_page_id ) && $post->ID == $preset_page_id ) {
				return true;
			}

			return false;
		}


		/**
		 * Get template path
		 *
		 * @param string $slug
		 * @param string $module
		 * @return string
		 */
		function get_template( $slug, $module = '' ) {
			if ( empty( $module ) ) {
				$file_list = $this->templates_path . "{$slug}.php";
				$theme_file = $this->theme_templates . "{$slug}.php";
			} else {
				$data = FMWP()->modules()->get_data( $module );
				if ( ! empty( $data['path'] ) ) {
					$file_list = $data['path'] . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . "{$slug}.php";
					$theme_file = $this->theme_templates . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . "{$slug}.php";
				}
			}

			if ( file_exists( $theme_file ) ) {
				$file_list = $theme_file;
			}

			return $file_list;
		}


		/**
		 * Load template
		 *
		 * @param string $slug
		 * @param string $module
		 * @param array $args
		 */
		function get_template_part( $slug, $args = [], $module = '' ) {
			global $wp_query;

			$query_title = $this->undash( sanitize_title( $slug ) );

			$wp_query->query_vars[ 'fmwp_' . $query_title ] = $args;

			$template = $this->get_template( $slug, $module );

			if ( file_exists( $template ) ) {
				load_template( $template, false );
			}
		}


		/**
		 * Undash string
		 *
		 * @param string $slug
		 *
		 * @return string
		 */
		function undash( $slug ) {
			$slug = str_replace( '-', '_', $slug );
			return $slug;
		}


		/**
		 * @param $tip
		 * @param bool $allow_html
		 * @param bool $echo
		 *
		 * @return false|string
		 */
		function helptip( $tip, $allow_html = false, $echo = true ) {

			wp_enqueue_script( 'fmwp-helptip' );
			wp_enqueue_style( 'fmwp-helptip' );

			if ( $allow_html ) {
				$tip = htmlspecialchars( wp_kses( html_entity_decode( $tip ), [
					'br'     => [],
					'em'     => [],
					'strong' => [],
					'small'  => [],
					'span'   => [],
					'ul'     => [],
					'li'     => [],
					'ol'     => [],
					'p'      => [],
				] ) );

			} else {
				$tip = esc_attr( $tip );
			}

			ob_start(); ?>

			<span class="fmwp-helptip dashicons dashicons-editor-help" title="<?php echo $tip ?>"></span>

			<?php if ( $echo ) {
				ob_get_flush();
				return '';
			} else {
				return ob_get_clean();
			}

		}


		/**
		 * @return array
		 */
		function get_breadcrumbs_data() {
			$arr = [
				[
					'url'   => get_home_url(),
					'title' => __( 'Home', 'forumwp' ),
				],
			];

			$forums_page_url = '#';
			$forums_page_id = FMWP()->common()->get_preset_page_id( 'forums' );
			if ( $forums_page_id ) {
				$forums_page_url = get_the_permalink( $forums_page_id );
			}

			$topics_page_url = '#';
			$topics_page_id = FMWP()->common()->get_preset_page_id( 'topics' );
			if ( $topics_page_id ) {
				$topics_page_url = get_the_permalink( $topics_page_id );
			}


			if ( $forums_page_id && is_page( $forums_page_id ) ) {

				$query = get_query_var( 'fmwp_archive_forum' );

				if ( $query && isset( $query['category'] ) && trim( $query['category'] ) != '' ) {
					// individual category page
					$cat_id = $query['category'];
					$cat = get_term_by( 'term_id', $cat_id, 'fmwp_forum_category' );

					$arr = array_merge( $arr, [
						[
							'url'   => $forums_page_url,
							'title' => __( 'Forums', 'forumwp' ),
						],
						[
							'url'   => '#',
							'title' => __( 'Categories', 'forumwp' ),
						],
						[
							'url'   => $forums_page_url . $cat->slug,
							'title' => $cat->name,
						],
                    ] );

				} else {
					// forums page
					$arr = array_merge( $arr, [
                        [
                            'url'   => $forums_page_url,
                            'title' => __( 'Forums', 'forumwp' ),
                        ],
                    ] );
				}

			} elseif ( $topics_page_id && is_page( $topics_page_id ) ) {

				$query = get_query_var( 'fmwp_archive_topic' );

				if ( $query && isset( $query['tag'] ) && trim( $query['tag'] ) != '' ) {
					// individual topic tag page
					$tag_id = $query['tag'];
					$tag = get_term_by( 'term_id', $tag_id , 'fmwp_topic_tag' );

					$arr = array_merge( $arr, [
						[
							'url'   => $topics_page_url,
							'title' => __( 'Topics', 'forumwp' ),
						],
						[
							'url'   => '#',
							'title' => __( 'Tags', 'forumwp' ),
						],
						[
							'url'   => $topics_page_url . $tag->slug,
							'title' => $tag->name,
						],
					] );

				} else {

					$arr = array_merge( $arr, [
						[
							'url'   => $topics_page_url,
							'title' => __( 'Topics', 'forumwp' ),
						],
					] );

				}

			} elseif ( is_singular( 'fmwp_forum' ) ) {

				$arr = array_merge( $arr, [
					[
						'url'   => $forums_page_url,
						'title' => __( 'Forums', 'forumwp' ),
					],
					[
						'url'   => get_permalink( get_the_id() ),
						'title' => get_the_title(),
					],
				] );

			} elseif ( is_singular( 'fmwp_topic' ) ) {

				$forum_id = FMWP()->common()->topic()->get_forum_id( get_the_id() );
				$forum = get_post( $forum_id );

				$arr = array_merge( $arr, [
					[
						'url'   => $forums_page_url,
						'title' => __( 'Forums', 'forumwp' ),
					],
					[
						'url'   => get_permalink( $forum_id ),
						'title' => $forum->post_title,
					],
					[
						'url'   => get_permalink( get_the_id() ),
						'title' => get_the_title(),
					],
				] );

			}

			return apply_filters( 'fmwp_breadcrumbs_data', $arr );
		}
	}
}