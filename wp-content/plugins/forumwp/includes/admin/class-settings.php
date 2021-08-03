<?php
namespace fmwp\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\admin\Settings' ) ) {


	/**
	 * Class Settings
	 *
	 * @package fmwp\admin
	 */
	class Settings {


		/**
		 * @var array
		 */
		var $config;


		/**
		 * Settings constructor.
		 */
		function __construct() {
			add_action( 'forumwp_init', [ &$this, 'init' ] );

			add_action( 'current_screen', [ $this, 'conditional_includes' ] );
			add_action( 'admin_init', [ $this, 'permalinks_save' ] );

			add_action( 'fmwp_before_settings_email__content', [ $this, 'email_templates_list_table' ], 10 );
			add_filter( 'fmwp_section_fields', [ $this, 'email_template_fields' ], 10, 3 );
		}


		/**
		 * Include admin files conditionally.
		 *
		 * @since 1.0
		 */
		function conditional_includes() {
			$screen = get_current_screen();
			if ( ! $screen ) {
				return;
			}
			switch ( $screen->id ) {
				case 'options-permalink':
					add_settings_field(
						FMWP()->options()->get_key( 'forum_slug' ),
						__( 'Forum base', 'forumwp' ),
						[ $this, 'forum_slug_input' ],
						'permalink',
						'optional'
					);
					add_settings_field(
						FMWP()->options()->get_key( 'topic_slug' ),
						__( 'Topic base', 'forumwp' ),
						[ $this, 'topic_slug_input' ],
						'permalink',
						'optional'
					);

					if ( FMWP()->options()->get( 'forum_categories' ) ) {
						add_settings_field(
							FMWP()->options()->get_key( 'forum_category_slug' ),
							__( 'Forum Category base', 'forumwp' ),
							[ $this, 'forum_category_slug_input' ],
							'permalink',
							'optional'
						);
					}

					if ( FMWP()->options()->get( 'topic_tags' ) ) {
						add_settings_field(
							FMWP()->options()->get_key( 'topic_tag_slug' ),
							__( 'Topic Tag base', 'forumwp' ),
							[ $this, 'topic_tag_slug_input' ],
							'permalink',
							'optional'
						);
					}

					break;
			}
		}


		/**
		 * Show a slug input box for CPT Forum slug.
		 *
		 * @since 1.0
		 */
		function forum_slug_input() {
			$defaults = FMWP()->config()->get( 'defaults' ); ?>

			<input type="text" class="regular-text code"
				   name="<?php echo esc_attr( FMWP()->options()->get_key( 'forum_slug' ) ) ?>"
				   value="<?php echo esc_attr( FMWP()->options()->get( 'forum_slug' ) ); ?>"
				   placeholder="<?php echo esc_attr( $defaults['forum_slug'] ); ?>" />
			<?php
		}


		/**
		 * Show a slug input box for Topic CPT slug.
		 *
		 * @since 1.0
		 */
		function topic_slug_input() {
			$defaults = FMWP()->config()->get( 'defaults' ); ?>

			<input type="text" class="regular-text code"
				   name="<?php echo esc_attr( FMWP()->options()->get_key( 'topic_slug' ) ) ?>"
				   value="<?php echo esc_attr( FMWP()->options()->get( 'topic_slug' ) ); ?>"
				   placeholder="<?php echo esc_attr( $defaults['topic_slug'] ); ?>" />
			<?php
		}


		/**
		 * Show a slug input box for Forum Category slug.
		 *
		 * @since 1.0
		 */
		function forum_category_slug_input() {
			$defaults = FMWP()->config()->get( 'defaults' ); ?>

			<input type="text" class="regular-text code"
				   name="<?php echo esc_attr( FMWP()->options()->get_key( 'forum_category_slug' ) ) ?>"
				   value="<?php echo esc_attr( FMWP()->options()->get( 'forum_category_slug' ) ); ?>"
				   placeholder="<?php echo esc_attr( $defaults['forum_category_slug'] ); ?>" />
			<?php
		}


		/**
		 * Show a slug input box for Topic Tag slug.
		 *
		 * @since 1.0
		 */
		function topic_tag_slug_input() {
			$defaults = FMWP()->config()->get( 'defaults' ); ?>

			<input type="text" class="regular-text code"
				   name="<?php echo esc_attr( FMWP()->options()->get_key( 'topic_tag_slug' ) ) ?>"
				   value="<?php echo esc_attr( FMWP()->options()->get( 'topic_tag_slug' ) ); ?>"
				   placeholder="<?php echo esc_attr( $defaults['topic_tag_slug'] ); ?>" />
			<?php
		}


		/**
		 * Save permalinks handler
		 *
		 * @since 1.0
		 */
		function permalinks_save() {
			if ( ! isset( $_POST['permalink_structure'] ) ) {
				// We must not be saving permalinks.
				return;
			}

			$forum_base_key = FMWP()->options()->get_key( 'forum_slug' );
			$topic_base_key = FMWP()->options()->get_key( 'topic_slug' );

			$forum_base = isset( $_POST[ $forum_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $forum_base_key ] ) ) : '';
			$topic_base = isset( $_POST[ $topic_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $topic_base_key ] ) ) : '';

			FMWP()->options()->update( 'forum_slug', $forum_base );
			FMWP()->options()->update( 'topic_slug', $topic_base );

			if ( FMWP()->options()->get( 'forum_categories' ) ) {
				$forum_category_base_key = FMWP()->options()->get_key( 'forum_category_slug' );
				$forum_category_base = isset( $_POST[ $forum_category_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $forum_category_base_key ] ) ) : '';
				FMWP()->options()->update( 'forum_category_slug', $forum_category_base );
			}

			if ( FMWP()->options()->get( 'topic_tags' ) ) {
				$topic_tag_base_key = FMWP()->options()->get_key( 'topic_tag_slug' );
				$topic_tag_base = isset( $_POST[ $topic_tag_base_key ] ) ? sanitize_title_with_dashes( wp_unslash( $_POST[ $topic_tag_base_key ] ) ) : '';
				FMWP()->options()->update( 'topic_tag_slug', $topic_tag_base );
			}
		}


		/**
		 * Set FMWP Settings
		 */
		function init() {
			$pages = get_posts( [
				'post_type'         => 'page',
				'post_status'       => 'publish',
				'posts_per_page'    => -1,
				'fields'            => [ 'ID', 'post_title' ],
			] );

			$page_options = [ '' => __( '(None)', 'forumwp' ) ];
			if ( ! empty( $pages ) ) {
				foreach ( $pages as $page ) {
					$page_options[ $page->ID ] = $page->post_title;
				}
			}

			$general_pages_fields = [];
			foreach ( FMWP()->config()->get( 'core_pages' ) as $page_id => $page ) {
				$page_title = ! empty( $page['title'] ) ? $page['title'] : '';

				$general_pages_fields[] = [
					'id'            => $page_id . '_page',
					'type'          => 'select',
					'label'         => sprintf( __( '%s page', 'forumwp' ), $page_title ),
					'options'       => $page_options,
					'placeholder'   => __( 'Choose a page...', 'forumwp' ),
					'size'          => 'small'
				];
			}

			$topic_fields = [
				[
					'id'        => 'topic_tags',
					'type'      => 'checkbox',
					'label'     => __( 'Topic Tags', 'forumwp' ),
					'helptip'   => __( 'Enable tags for topics', 'forumwp' ),
				],
				[
					'id'    => 'topic_throttle',
					'type'  => 'number',
					'size'  => 'small',
					'label' => __( 'Time between new topics (seconds)', 'forumwp' ),
				],
				[
					'id'        => 'show_forum',
					'type'      => 'checkbox',
					'size'      => 'small',
					'label'     => __( 'Show forum title', 'forumwp' ),
					'helptip'   => __( 'Show forum title at individual topic page and at topics lists', 'forumwp' ),
				],
				[
					'id'        => 'default_topics_order',
					'type'      => 'select',
					'size'      => 'small',
					'options'   => FMWP()->common()->topic()->sort_by,
					'label'     => __( 'Default topics order', 'forumwp' ),
					'helptip'   => __( 'Default topics order on latest topics list', 'forumwp' ),
				],
			];

			$custom_templates = FMWP()->common()->topic()->get_templates( 'fmwp_topic' );
			if ( count( $custom_templates ) ) {
				$topic_fields[] = [
					'id'        => 'default_topic_template',
					'type'      => 'select',
					'label'     => __( 'Default topics template', 'forumwp' ),
					'options'   => array_merge( [
						''  => __( 'Default Template', 'forumwp' ),
					], $custom_templates ),
					'helptip'   => __( 'Default template for all topics at your site. You may set different for each topic in the topic\'s styling section', 'forumwp' ),
					'size'      => 'small',
				];
			} else {
				$topic_fields[] = [
					'id'        => 'default_topic_template',
					'type'      => 'hidden',
					'value'     => '',
				];
			}

			if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
				$topic_fields[] = [
					'id'        => 'ajax_increment_views',
					'type'      => 'checkbox',
					'label'     => __( 'Use AJAX To Update Views', 'forumwp' ),
					'helptip'   => __( 'There is an ability to count views via AJAX in some cases when WP cache is active', 'forumwp' ),
				];
			} else {
				$topic_fields[] = [
					'id'    => 'ajax_increment_views',
					'type'  => 'hidden',
					'value' => 0,
				];
			}

			$forums = get_posts( [
				'post_type'         => 'fmwp_forum',
				'posts_per_page'    => -1,
				'post_status'       => 'publish',
				'meta_query'        => [
					[
						'key'       => 'fmwp_visibility',
						'value'     => 'public',
						'compare'   => '=',
					],
				],
				'fields'            => [ 'ID', 'post_title' ],
			] );

			$forum_options = [ '' => __( 'None', 'forumwp' ) ];
			if ( ! empty( $forums ) && ! is_wp_error( $forums ) ) {
				foreach ( $forums as $forum ) {
					$forum_options[ $forum->ID ] = $forum->post_title;
				}
			}

			$forums_fields = [
				[
					'id'        => 'forum_categories',
					'type'      => 'checkbox',
					'label'     => __( 'Forum categories', 'forumwp' ),
					'helptip'   => __( 'Enable categories for forums', 'forumwp' ),
				],
				[
					'id'        => 'default_forum',
					'type'      => 'select',
					'size'      => 'medium',
					'options'   => $forum_options,
					'label'     => __( 'Default forum', 'forumwp' ),
					'helptip'   => __( 'When you create a topic from topics list it will be added to the default forum', 'forumwp' ),
				],
				[
					'id'        => 'default_forums_order',
					'type'      => 'select',
					'size'      => 'small',
					'options'   => [
						'date_desc'     => __( 'Newest to Oldest', 'forumwp' ),
						'date_asc'      => __( 'Oldest to Newest', 'forumwp' ),
						'order_desc'    => __( 'Most Priority', 'forumwp' ),
						'order_asc'     => __( 'Lower Priority', 'forumwp' ),
					],
					'label'     => __( 'Default forums order', 'forumwp' ),
					'helptip'   => __( 'Default forums order on latest forums list', 'forumwp' ),
				],
			];

			$custom_templates = FMWP()->common()->forum()->get_templates( 'fmwp_forum' );
			if ( count( $custom_templates ) ) {
				$forums_fields[] = [
					'id'        => 'default_forums_template',
					'type'      => 'select',
					'label'     => __( 'Default forums template', 'forumwp' ),
					'options'   => array_merge( [
						''  => __( 'Default Template', 'forumwp' ),
					], $custom_templates ),
					'helptip'   => __( 'Default template for all forums at your site. You may set different for each forum in the forum\'s styling section', 'forumwp' ),
					'size'      => 'small',
				];
			} else {
				$forums_fields[] = [
					'id'        => 'default_forums_template',
					'type'      => 'hidden',
					'value'     => '',
				];
			}

			$this->config = apply_filters( 'fmwp_settings', [
				'general'   => [
					'title'     => __( 'General', 'forumwp' ),
					'sections'  => [
						'pages'             => [
							'title'     => __( 'Pages', 'forumwp' ),
							'fields'    => $general_pages_fields,
						],
						'general_options'   => [
							'title'     => __( 'General Options', 'forumwp' ),
							'fields'    => [
								[
									'id'        => 'default_role',
									'type'      => 'select',
									'size'      => 'small',
									'label'     => __( 'Default Role', 'forumwp' ),
									'helptip'   => __( 'New members will get this forum role automatically', 'forumwp' ),
									'options'   => FMWP()->config()->get( 'custom_roles' ),
								],
								[
									'id'        => 'raw_html_enabled',
									'type'      => 'checkbox',
									'label'     => __( 'Enable raw HTML in topic/reply content', 'forumwp' ),
									'helptip'   => __( 'If enabled can be less secure. Please enable only if you plan to get an ability for users create topics and replies with HTML tags', 'forumwp' ),
								],
								[
									'id'        => 'breadcrumb_enabled',
									'type'      => 'checkbox',
									'label'     => __( 'Enable Breadcrumbs', 'forumwp' ),
									'helptip'   => __( 'If enabled, breadcrumbs will be displayed on ForumWP templates', 'forumwp' ),
								],
								[
									'id'        => 'login_redirect',
									'type'      => 'text',
									'size'      => 'small',
									'label'     => __( 'Login Redirect', 'forumwp' ),
									'helptip'   => __( 'If empty user will be redirected to the same page. This option can be rewritten via login shortcode "redirect" attribute', 'forumwp' ),
								],
								[
									'id'        => 'register_redirect',
									'type'      => 'text',
									'size'      => 'small',
									'label'     => __( 'Registration Redirect', 'forumwp' ),
									'helptip'   => __( 'If empty user will be redirected to the Profile page. This option can be rewritten via register shortcode "redirect" attribute', 'forumwp' ),
								],
								[
									'id'        => 'logout_redirect',
									'type'      => 'text',
									'size'      => 'small',
									'label'     => __( 'Logout Redirect', 'forumwp' ),
									'helptip'   => __( 'If empty user will be redirected to the Login page.', 'forumwp' ),
								],
							],
						],
						'forums'            => [
							'title'     => __( 'Forums', 'forumwp' ),
							'fields'    => $forums_fields
						],
						'topics'            => [
							'title'     => __( 'Topics', 'forumwp' ),
							'fields'    => $topic_fields
						],
						'replies'           => [
							'title'     => __( 'Replies', 'forumwp' ),
							'fields'    => [
								[
									'id'    => 'reply_throttle',
									'type'  => 'number',
									'size'  => 'small',
									'label' => __( 'Time between new replies', 'forumwp' ),
								],
								[
									'id'        => 'reply_delete',
									'type'      => 'select',
									'size'      => 'small',
									'label'     => __( 'Reply deletion: sub-reply action to take', 'forumwp' ),
									'options'   => [
										'sub_delete'     => __( 'Delete all sub replies', 'forumwp' ),
										'change_level'   => __( 'Change sub replies\' level', 'forumwp' ),
									],
									'helptip'   => __( 'When a reply to a topic is removed/deleted, what would you like to happen to replies to that reply (sub replies)?', 'forumwp' ),
								],
								[
									'id'        => 'reply_user_role',
									'type'      => 'checkbox',
									'label'     => __( 'Show user role tag on replies', 'forumwp' ),
									'helptip'   => __( 'If turned on the role of the user will appear to the right of their name on topic page', 'forumwp' ),
								],
							],
						],
					],
				],
				'email'     => [
					'title'     => __( 'Email', 'forumwp' ),
					'fields'    => [
						[
							'id'        => 'admin_email',
							'type'      => 'text',
							'label'     => __( 'Admin E-mail Address', 'forumwp' ),
							'helptip'   => __( 'e.g. admin@companyname.com','forumwp' ),
						],
						[
							'id'        => 'mail_from',
							'type'      => 'text',
							'label'     => __( 'Mail appears from','forumwp' ),
							'helptip'   => __( 'e.g. Site Name','forumwp' ),
						],
						[
							'id'        => 'mail_from_addr',
							'type'      => 'text',
							'label'     => __( 'Mail appears from address','forumwp' ),
							'helptip'   => __( 'e.g. admin@companyname.com','forumwp' ),
						],
					],
				],
			] );

			$module_plans = FMWP()->modules()->get_list();
			if ( ! empty( $module_plans ) ) {

				$modules_settings_fields = [];
				foreach ( $module_plans as $plan_key => $plan_data ) {
					if ( empty( $plan_data['modules'] ) ) {
						continue;
					}

					$modules_settings_fields[] = [
						'id'            => $plan_key . '_label',
						'type'          => 'separator',
						'value'         => sprintf( __( '%s modules', 'forumwp' ), $plan_data['title'] ),
						'without_label' => true,
					];

					foreach ( $plan_data['modules'] as $slug => $data ) {
						$slug = FMWP()->undash( $slug );

						$modules_settings_fields[] = [
							'id'        => 'module_' . $slug . '_on',
							'type'      => 'checkbox',
							'label'     => $data['title'],
							'helptip'   => $data['description'],
						];
					}
				}

				$sections = [
					'modules'   => [
						'title'     => __( 'Enabled Modules', 'forumwp' ),
						'fields'    => $modules_settings_fields,
					],
				];
				$sections = apply_filters( 'fmwp_modules_settings_sections', $sections );

				$this->config['modules'] = [
					'title'     => __( 'Modules', 'forumwp' ),
					'sections'  => $sections,
				];
			}

			$this->config['advanced'] = [
				'title'     => __( 'Advanced', 'forumwp' ),
				'fields'    => [
					[
						'id'        => 'disable-fa-styles',
						'type'      => 'checkbox',
						'label'     => __( 'Disable FontAwesome styles', 'forumwp' ),
						'helptip'   => __( 'To avoid duplicates if you have enqueued FontAwesome styles you could disable it.', 'forumwp' ),
					],
					[
						'id'        => 'uninstall-delete-settings',
						'type'      => 'checkbox',
						'label'     => __( 'Delete settings on uninstall', 'forumwp' ),
						'helptip'   => __( 'Once removed, this data cannot be restored.', 'forumwp' ),
					],
				],
			];
		}


		/**
		 * @param $current_tab
		 * @param $current_subtab
		 *
		 * @return bool
		 */
		function section_is_custom( $current_tab, $current_subtab ) {
			$custom_section = in_array( $current_tab, apply_filters( 'fmwp_settings_custom_tabs', [] ) )
							  || in_array( $current_subtab, apply_filters( 'fmwp_settings_custom_subtabs', [], $current_tab ) );
			return $custom_section;
		}


		/**
		 * Get settings section
		 *
		 * @param string $tab
		 * @param string $section
		 * @param bool $assoc Return Associated array
		 *
		 * @return bool|array
		 */
		function get_settings( $tab = '', $section = '', $assoc = false ) {
			if ( empty( $tab ) ) {
				$tabs = array_keys( $this->config );
				$tab = $tabs[0];
			}

			if ( ! isset( $this->config[ $tab ] ) ) {
				return false;
			}

			if ( ! empty( $section ) && empty( $this->config[ $tab ]['sections'] ) ) {
				return false;
			}

			if ( ! empty( $this->config[ $tab ]['sections'] ) ) {
				if ( empty( $section ) ) {
					$sections = array_keys( $this->config[ $tab ]['sections'] );
					$section = $sections[0];
				}

				if ( isset( $this->config[ $tab ]['sections'] ) && ! isset( $this->config[ $tab ]['sections'][ $section ] ) ) {
					return false;
				}

				$fields = $this->config[ $tab ]['sections'][ $section ]['fields'];
			} else {
				$fields = $this->config[ $tab ]['fields'];
			}

			$fields = apply_filters( 'fmwp_section_fields', $fields, $tab, $section );

			$assoc_fields = [];
			foreach ( $fields as &$data ) {
				if ( ! isset( $data['value'] ) ) {
					$data['value'] = FMWP()->options()->get( $data['id'] );
				}

				if ( $assoc ) {
					$assoc_fields[ $data['id'] ] = $data;
				}
			}

			return $assoc ? $assoc_fields : $fields;
		}


		/**
		 * Generate pages tabs
		 *
		 * @param string $page
		 *
		 * @return string
		 */
		function tabs_menu( $page = 'settings' ) {
			switch( $page ) {
				case 'settings': {
					$current_tab = empty( $_GET['tab'] ) ? '' : urldecode( sanitize_key( $_GET['tab'] ) );
					if ( empty( $current_tab ) ) {
						$all_tabs = array_keys( $this->config );
						$current_tab = $all_tabs[0];
					}

					$i = 0;
					$tabs = '';
					foreach ( $this->config as $slug => $tab ) {
						if ( empty( $tab['fields'] ) && empty( $tab['sections'] ) ) {
							continue;
						}

						$link_args = [
							'page' => 'forumwp-settings',
						];
						if ( ! empty( $i ) ) {
							$link_args['tab'] = $slug;
						}

						$tab_link = add_query_arg(
							$link_args,
							admin_url( 'admin.php' )
						);

						$active = ( $current_tab == $slug ) ? 'nav-tab-active' : '';
						$tabs .= sprintf( "<a href=\"%s\" class=\"nav-tab %s\">%s</a>",
							$tab_link,
							$active,
							$tab['title']
						);

						$i++;
					}
					break;
				}
				default: {

					$tabs = apply_filters( 'fmwp_generate_tabs_menu_' . $page, '' );
					break;

				}
			}

			return '<h2 class="nav-tab-wrapper fmwp-nav-tab-wrapper">' . $tabs . '</h2>';
		}


		/**
		 * Generate sub-tabs
		 *
		 * @param string $tab
		 *
		 * @return string
		 */
		function subtabs_menu( $tab = '' ) {
			if ( empty( $tab ) ) {
				$all_tabs = array_keys( $this->config );
				$tab = $all_tabs[0];
			}

			if ( empty( $this->config[ $tab ]['sections'] ) ) {
				return '';
			}

			$current_tab = empty( $_GET['tab'] ) ? '' : urldecode( sanitize_key( $_GET['tab'] ) );

			$current_subtab = empty( $_GET['section'] ) ? '' : urldecode( sanitize_key( $_GET['section'] ) );
			if ( empty( $current_subtab ) ) {
				$sections = array_keys( $this->config[ $tab ]['sections'] );
				$current_subtab = $sections[0];
			}

			$i = 0;
			$subtabs = '';
			foreach ( $this->config[ $tab ]['sections'] as $slug => $subtab ) {

				$custom_section = FMWP()->admin()->settings()->section_is_custom( $current_tab, $slug );

				if ( ! $custom_section && empty( $subtab['fields'] ) ) {
					continue;
				}

				$link_args = [
					'page' => 'forumwp-settings',
				];
				if ( ! empty( $current_tab ) ) {
					$link_args['tab'] = $current_tab;
				}
				if ( ! empty( $i ) ) {
					$link_args['section'] = $slug;
				}

				$tab_link = add_query_arg(
					$link_args,
					admin_url( 'admin.php' )
				);

				$active = ( $current_subtab == $slug ) ? 'current' : '';

				$subtabs .= sprintf( "<a href=\"%s\" class=\"%s\">%s</a> | ",
					$tab_link,
					$active,
					$subtab['title']
				);

				$i++;
			}

			return '<div><ul class="subsubsub">' . substr( $subtabs, 0, -3 ) . '</ul></div>';
		}


		/**
		 * Render settings section
		 *
		 * @param $current_tab
		 * @param $current_subtab
		 *
		 * @return false|string
		 */
		function display_section( $current_tab, $current_subtab ) {
			$fields = $this->get_settings( $current_tab, $current_subtab );

			if ( ! $fields ) {
				return '';
			}

			return FMWP()->admin()->forms( [
				'class'     => 'fmwp-options-' . $current_tab . '-' . $current_subtab . ' fmwp-third-column',
				'prefix_id' => 'fmwp_options',
				'fields'    => $fields,
			] )->display( false );
		}


		/**
		 * Display Email Notifications Templates List
		 */
		function email_templates_list_table() {
			$email_key = empty( $_GET['email'] ) ? '' : urldecode( sanitize_key( $_GET['email'] ) );
			$email_notifications = FMWP()->config()->get( 'email_notifications' );

			if ( empty( $email_key ) || empty( $email_notifications[ $email_key ] ) ) {
				include_once FMWP()->admin()->templates_path . 'settings' . DIRECTORY_SEPARATOR . 'emails-list-table.php';
			}
		}


		/**
		 * Edit email template fields
		 *
		 * @param array $fields
		 * @param string $tab
		 * @param string $section
		 *
		 * @return array
		 */
		function email_template_fields( $fields, $tab, $section ) {
			if ( 'email' !== $tab ) {
				return $fields;
			}

			$email_key = empty( $_GET['email'] ) ? '' : urldecode( sanitize_key( $_GET['email'] ) );
			$email_notifications = FMWP()->config()->get( 'email_notifications' );
			if ( empty( $email_key ) || empty( $email_notifications[ $email_key ] ) ) {
				return $fields;
			}

			$fields = apply_filters( 'fmwp_settings_email_section_fields', [
				[
					'id'            => 'fmwp_email_template',
					'type'          => 'hidden',
					'value'         => $email_key,
				],
				[
					'id'            => $email_key . '_on',
					'type'          => 'checkbox',
					'label'         => $email_notifications[ $email_key ]['title'],
					'helptip'       => $email_notifications[ $email_key ]['description'],
				],
				[
					'id'            => $email_key . '_sub',
					'type'          => 'text',
					'label'         => __( 'Subject Line', 'forumwp' ),
					'helptip'       => __( 'This is the subject line of the e-mail', 'forumwp' ),
					'conditional'   => [ $email_key . '_on', '=', 1 ],
				],
				[
					'id'            => $email_key,
					'type'          => 'email_template',
					'label'         => __( 'Message Body', 'forumwp' ),
					'helptip'       => __( 'This is the content of the e-mail', 'forumwp' ),
					'value'         => FMWP()->common()->mail()->get_template( $email_key ),
					'conditional'   => [ $email_key . '_on', '=', 1 ],
				],
			], $email_key );

			return $fields;
		}
	}
}