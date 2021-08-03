<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'FMWP' ) ) {


	/**
	 * Main FMWP Class
	 *
	 *
	 * @method FMWP_Pro Pro()
	 *
	 *
	 * @class FMWP
	 *
	 * @version 2.0
	 */
	final class FMWP extends FMWP_Functions {


		/**
		 * @var FMWP the single instance of the class
		 */
		protected static $instance = null;


		/**
		 * @var array all plugin's classes
		 */
		public $classes = [];


		/**
		 * Main FMWP Instance
		 *
		 * Ensures only one instance of FMWP is loaded or can be loaded.
		 *
		 * @since 1.0
		 * @static
		 * @see FMWP()
		 * @return FMWP - Main instance
		 */
		static public function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->_fmwp_construct();
			}

			return self::$instance;
		}


		/**
		 * Create plugin classes - not sure if it needs!!!!!!!!!!!!!!!
		 *
		 * @since 1.0
		 * @see FMWP()
		 *
		 * @param $name
		 * @param array $params
		 * @return mixed
		 */
		public function __call( $name, array $params ) {

			if ( empty( $this->classes[ $name ] ) ) {
				$this->classes[ $name ] = apply_filters( 'fmwp_call_object_' . $name, false );
			}

			return $this->classes[ $name ];

		}


		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'forumwp' ), '1.0' );
		}


		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'forumwp' ), '1.0' );
		}


		/**
		 * FMWP constructor.
		 *
		 * @since 1.0
		 */
		function __construct() {
			parent::__construct();
		}


		/**
		 * FMWP pseudo-constructor.
		 *
		 * @since 1.0
		 */
		function _fmwp_construct() {
			//register autoloader for include FMWP classes
			spl_autoload_register( [ $this, 'fmwp__autoloader' ] );

			if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
				$this->includes();
			}
		}


		/**
		 * Function for add classes to $this->classes
		 * for run using FMWP()
		 *
		 * @since 1.0
		 *
		 * @param string $class_name
		 * @param bool $instance
		 */
		public function set_class( $class_name, $instance = false ) {
			if ( empty( $this->classes[ $class_name ] ) ) {
				$class = 'FMWP_' . $class_name;
				$this->classes[ $class_name ] = $instance ? $class::instance() : new $class;
			}
		}


		/**
		 * Autoload FMWP classes handler
		 *
		 * @since 1.0
		 *
		 * @param $class
		 */
		function fmwp__autoloader( $class ) {
			if ( strpos( $class, 'fmwp' ) === 0 ) {

				$array = explode( '\\', strtolower( $class ) );
				$array[ count( $array ) - 1 ] = 'class-'. end( $array );

				if ( strpos( $class, 'fmwpm' ) === 0 ) {
					// module namespace
					$module_slug = str_replace( '_', '-', $array[1] );
					$module_data = $this->modules()->get_data( $module_slug );

					if ( ! empty( $module_data['path'] ) ) {
						$full_path = $module_data['path'] . DIRECTORY_SEPARATOR;

						unset( $array[0], $array[1] );
						$path = implode( DIRECTORY_SEPARATOR, $array );
						$path = str_replace( '_', '-', $path );
						$full_path .= $path . '.php';
					}
				} elseif ( strpos( $class, 'fmwp\\' ) === 0 ) {
					// regular core namespace
					$class = implode( '\\', $array );
					$path = str_replace( [ 'fmwp\\', '_', '\\' ], [ DIRECTORY_SEPARATOR, '-', DIRECTORY_SEPARATOR ], $class );
					$full_path =  fmwp_path . 'includes' . $path . '.php';
				}

				if ( isset( $full_path ) && file_exists( $full_path ) ) {
					include_once $full_path;
				}
			}
		}


		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @since 1.0
		 * @version 2.0
		 *
		 * @return void
		 */
		public function includes() {
			$this->modules();
			$this->common()->includes();

			if ( $this->is_request( 'ajax' ) ) {
				$this->ajax()->includes();
			} elseif ( $this->is_request( 'admin' ) ) {
				$this->admin()->includes();
			} elseif ( $this->is_request( 'frontend' ) ) {
				$this->frontend()->includes();
			}
		}


		/**
		 * @param string $class
		 *
		 * @since 1.0
		 *
		 * @return mixed
		 */
		function call_class( $class ) {
			$key = strtolower( $class );

			if ( empty( $this->classes[ $key ] ) ) {
				$this->classes[ $key ] = new $class;
			}

			return $this->classes[ $key ];
		}


		/**
		 * @since 1.0
		 *
		 * @return fmwp\Config
		 */
		function config() {
			if ( empty( $this->classes['fmwp\config'] ) ) {
				$this->classes['fmwp\config'] = new fmwp\Config();
			}

			return $this->classes['fmwp\config'];
		}


		/**
		 * @since 1.0
		 *
		 * @return fmwp\Modules
		 */
		function modules() {
			if ( empty( $this->classes['fmwp\modules'] ) ) {
				$this->classes['fmwp\modules'] = new fmwp\Modules();
			}

			return $this->classes['fmwp\modules'];
		}


		/**
		 * Get addons API
		 *
		 * @since 1.0
		 *
		 * @param $slug
		 *
		 * @return mixed
		 */
		function module( $slug ) {

			$data = $this->modules()->get_data( $slug );
			if ( ! empty( $data['path'] ) ) {
				$slug = $this->undash( $slug );

				$class = "fmwpm\\{$slug}\\Init";

				if ( empty( $this->classes[ strtolower( $class ) ] ) ) {
					/**
					 * @var $class fmwpm\private_replies\Init
					 * @var $class fmwpm\votes\Init
					 */
					$this->classes[ strtolower( $class ) ] = $class::instance();
				}

				return $this->classes[ strtolower( $class ) ];
			} else {
				return false;
			}
		}


		/**
		 * Getting the Install class instance
		 *
		 * @since 1.0
		 *
		 * @return fmwp\common\Install()
		 */
		function install() {
			if ( empty( $this->classes['fmwp\common\install'] ) ) {
				$this->classes['fmwp\common\install'] = new fmwp\common\Install();
			}
			return $this->classes['fmwp\common\install'];
		}


		/**
		 * Getting the Common class instance
		 *
		 * @since 1.0
		 *
		 * @return fmwp\common\Common()
		 */
		function common() {
			if ( empty( $this->classes['fmwp\common\common'] ) ) {
				$this->classes['fmwp\common\common'] = new fmwp\common\Common();
			}
			return $this->classes['fmwp\common\common'];
		}


		/**
		 * Getting the AJAX class instance
		 *
		 * @since 1.0
		 *
		 * @return fmwp\ajax\Common()
		 */
		function ajax() {
			if ( empty( $this->classes['fmwp\ajax\common'] ) ) {
				$this->classes['fmwp\ajax\common'] = new fmwp\ajax\Common();
			}
			return $this->classes['fmwp\ajax\common'];
		}


		/**
		 * Getting the Frontend class instance
		 *
		 * @since 1.0
		 *
		 * @return fmwp\frontend\Common()
		 */
		function frontend() {
			if ( empty( $this->classes['fmwp\frontend\common'] ) ) {
				$this->classes['fmwp\frontend\common'] = new fmwp\frontend\Common();
			}
			return $this->classes['fmwp\frontend\common'];
		}


		/**
		 * Getting the Admin class instance
		 *
		 * @since 1.0
		 *
		 * @return fmwp\admin\Common()
		 */
		function admin() {
			if ( empty( $this->classes['fmwp\admin\common'] ) ) {
				$this->classes['fmwp\admin\common'] = new fmwp\admin\Common();
			}
			return $this->classes['fmwp\admin\common'];
		}


		/**
		 * Function duplicate to avoid long FMWP()->common()->options()->method
		 * @since 1.0
		 * @version 2.0
		 *
		 * @return fmwp\common\Options()
		 */
		function options() {
			return $this->common()->options();
		}


		/**
		 * @since 1.0
		 *
		 * @return fmwp\common\Reports
		 */
		function reports() {
			if ( empty( $this->classes['fmwp\common\reports'] ) ) {
				$this->classes['fmwp\common\reports'] = new fmwp\common\Reports();
			}

			return $this->classes['fmwp\common\reports'];
		}


		/**
		 * @since 1.0
		 *
		 * @return fmwp\common\User
		 */
		function user() {
			if ( empty( $this->classes['fmwp\common\user'] ) ) {
				$this->classes['fmwp\common\user'] = new fmwp\common\User();
			}

			return $this->classes['fmwp\common\user'];
		}


		/**
		 * @since 1.0
		 *
		 * @deprecated 2.0
		 */
		function shortcodes() {
			return $this->frontend()->shortcodes();
		}


		/**
		 * @since 1.0
		 *
		 * @deprecated 2.0
		 */
		function forum() {
			return $this->common()->forum();
		}


		/**
		 * @since 1.0
		 *
		 * @deprecated 2.0
		 */
		function topic() {
			return $this->common()->topic();
		}


		/**
		 * @since 1.0
		 *
		 * @deprecated 2.0
		 */
		function reply() {
			return $this->common()->reply();
		}
	}
}


/**
 * Function for calling FMWP methods and variables
 *
 * @since 1.0
 *
 * @return FMWP
 */
function FMWP() {
	return FMWP::instance();
}