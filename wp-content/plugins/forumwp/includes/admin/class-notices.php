<?php
namespace fmwp\admin;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\admin\Notices' ) ) {


	/**
	 * Class Notices
	 *
	 * @package fmwp\admin
	 */
	class Notices {


		/**
		 * Notices list
		 *
		 * @var array
		 */
		var $list = [];


		/**
		 * Notices constructor.
		 */
		function __construct() {
			add_action( 'admin_init', [ &$this, 'create_list' ], 10 );
			add_action( 'admin_notices', [ &$this, 'render' ], 1 );
		}


		/**
		 *
		 * @since 1.0
		 */
		function create_list() {
			$this->install_core_page_notice();
			$this->old_customers();
			$this->need_upgrade();

			do_action( 'fmwp_admin_create_notices' );
		}


		/**
		 * Render all admin notices
		 *
		 * @since 2.0
		 */
		function render() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$admin_notices = $this->get_admin_notices();

			$hidden = get_option( 'fmwp_hidden_admin_notices', [] );

			uasort( $admin_notices, [ &$this, 'priority_sort' ] );

			foreach ( $admin_notices as $key => $admin_notice ) {
				if ( empty( $hidden ) || ! in_array( $key, $hidden ) ) {
					$this->display( $key );
				}
			}

			do_action( 'fmwp_admin_after_main_notices' );
		}


		/**
		 * @return array
		 */
		function get_admin_notices() {
			return $this->list;
		}


		/**
		 * @param $admin_notices
		 */
		function set_admin_notices( $admin_notices ) {
			$this->list = $admin_notices;
		}


		/**
		 * @param $a
		 * @param $b
		 *
		 * @return mixed
		 */
		function priority_sort( $a, $b ) {
			if ( $a['priority'] == $b['priority'] ) {
				return 0;
			}
			return ( $a['priority'] < $b['priority'] ) ? -1 : 1;
		}


		/**
		 * Add notice to FMWP notices array
		 *
		 * @param string $key
		 * @param array $data
		 * @param int $priority
		 *
		 * @uses Notices::get_admin_notices()
		 * @uses Notices::set_admin_notices()
		 *
		 * @since 2.0
		 */
		function add( $key, $data, $priority = 10 ) {
			$admin_notices = $this->get_admin_notices();

			if ( empty( $admin_notices[ $key ] ) ) {
				$admin_notices[ $key ] = array_merge( $data, [ 'priority' => $priority ] );
				$this->set_admin_notices( $admin_notices );
			}
		}


		/**
		 * Remove notice from FMWP notices array
		 *
		 * @param string $key
		 *
		 * @uses Notices::get_admin_notices()
		 * @uses Notices::set_admin_notices()
		 *
		 * @since 2.0
		 */
		function remove( $key ) {
			$admin_notices = $this->get_admin_notices();

			if ( ! empty( $admin_notices[ $key ] ) ) {
				unset( $admin_notices[ $key ] );
				$this->set_admin_notices( $admin_notices );
			}
		}


		/**
		 * Dismiss notices by key
		 *
		 * @param string $key
		 */
		function dismiss( $key ) {
			$hidden_notices = get_option( 'fmwp_hidden_admin_notices', [] );
			$hidden_notices[] = $key;
			update_option( 'fmwp_hidden_admin_notices', $hidden_notices );
		}


		/**
		 * Display single admin notice
		 *
		 * @param string $key
		 * @param bool $echo
		 *
		 * @uses Notices::get_admin_notices()
		 *
		 * @since 2.0
		 *
		 * @return string
		 */
		function display( $key, $echo = true ) {
			$admin_notices = $this->get_admin_notices();

			if ( empty( $admin_notices[ $key ] ) ) {
				return '';
			}

			$notice_data = $admin_notices[ $key ];

			$class = ! empty( $notice_data['class'] ) ? $notice_data['class'] : 'updated';
			if ( ! empty( $admin_notices[ $key ]['dismissible'] ) ) {
				$class .= ' is-dismissible';
			}

			$message = ! empty( $notice_data['message'] ) ? $notice_data['message'] : '';

			ob_start();

			printf( '<div class="fmwp-admin-notice notice %s" data-key="%s">%s</div>',
				esc_attr( $class ),
				esc_attr( $key ),
				$message
			);

			$notice = ob_get_clean();

			if ( $echo ) {
				echo $notice;
				return '';
			} else {
				return $notice;
			}
		}


		/**
		 * Regarding page setup
		 *
		 * @uses Notices::add()
		 *
		 * @since 1.0
		 */
		function install_core_page_notice() {
			if ( FMWP()->options()->are_pages_installed() || ! current_user_can( 'manage_options' ) ) {
				return;
			}

			ob_start(); ?>

			<p>
				<?php printf( __( 'To add forum functionality to your website %s needs to create several front-end pages (Forums, Topics, Profile, Registration & Login).', 'forumwp' ), fmwp_plugin_name ); ?>
			</p>
			<p>
				<a href="<?php echo esc_attr( add_query_arg( 'fmwp_adm_action', 'install_core_pages' ) ); ?>" class="button button-primary">
					<?php _e( 'Create Pages', 'forumwp' ) ?>
				</a>
				&nbsp;
				<a href="javascript:void(0);" class="button-secondary fmwp_secondary_dimiss">
					<?php _e( 'No thanks', 'forumwp' ) ?>
				</a>
			</p>

			<?php $message = ob_get_clean();

			$this->add( 'wrong_pages', [
				'class'         => 'updated',
				'message'       => $message,
				'dismissible'   => true
			], 20 );
		}


		/**
		 * Regarding old customers upgrade
		 *
		 * @uses Notices::add()
		 *
		 * @since 1.0
		 */
		function old_customers() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$first_activation = get_option( 'fmwp_first_activation_date', false );

			// {first activation date}
			if ( ! $first_activation || $first_activation <= 1614681468 ) {
				return;
			}

			ob_start(); ?>

			<p>
				<?php _e( 'ForumWP - We have made some changes to ForumWP and launched a free version of the plugin. If you are were using ForumWP prior to this update you will need to install <a href="https://forumwpplugin.com/addons/forumwp-pro/">ForumWP - Pro</a> as the basic modules are not available in the free version. In order to continue using the modules that extend the functionality of the free plugin, please install ForumWP - Pro. <a href="https://forumwpplugin.com/topic/changes-to-forumwp/">details</a>.', 'forumwp' ); ?>
			</p>

			<?php $message = ob_get_clean();

			$this->add( 'replace_plus', [
				'class'         => 'notice-warning',
				'message'       => $message,
				'dismissible'   => true,
			], 20 );
		}


		function need_upgrade() {
			if ( ! empty( FMWP()->admin()->upgrade()->necessary_packages ) ) {

				$url = add_query_arg( [ 'page' => 'fmwp_upgrade' ], admin_url( 'admin.php' ) );

				ob_start(); ?>

                <p>
					<?php printf( __( '<strong>%s version %s</strong> needs to be updated to work correctly.<br />It is necessary to update the structure of the database and options that are associated with <strong>%s %s</strong>.<br />Please visit "<a href="%s">Upgrade</a>" page and run the upgrade process.', 'forumwp' ), fmwp_plugin_name, fmwp_version, fmwp_plugin_name, fmwp_version, $url ); ?>
                </p>

                <p>
                    <a href="<?php echo esc_url( $url ) ?>" class="button button-primary"><?php _e( 'Upgrade Now', 'forumwp' ) ?></a>
                    &nbsp;
                </p>

				<?php $message = ob_get_clean();

				$this->add( 'upgrade', [
					'class'     => 'error',
					'message'   => $message,
				], 4 );
			} else {
				if ( isset( $_GET['fmwp-msg'] ) && 'updated' == sanitize_key( $_GET['fmwp-msg'] ) ) {
					if ( isset( $_GET['page'] ) && 'forumwp-settings' == sanitize_key( $_GET['page'] ) ) {
						$this->add( 'settings_upgrade', [
							'class'     => 'updated',
							'message'   => '<p>' . sprintf( __( '<strong>%s %s</strong> Successfully Upgraded', 'forumwp' ), fmwp_plugin_name, fmwp_version ) . '</p>',
						], 4 );
					}
				}
			}
		}
	}
}