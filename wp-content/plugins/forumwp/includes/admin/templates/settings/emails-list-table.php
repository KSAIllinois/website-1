<?php if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/**
 * Class FMWP_Emails_List_Table
 */
class FMWP_Emails_List_Table extends WP_List_Table {


	/**
	 * @var string
	 */
	var $no_items_message = '';


	/**
	 * @var array
	 */
	var $sortable_columns = [];


	/**
	 * @var string
	 */
	var $default_sorting_field = '';


	/**
	 * @var array
	 */
	var $actions = [];


	/**
	 * @var array
	 */
	var $bulk_actions = [];


	/**
	 * @var array
	 */
	var $columns = [];


	/**
	 * FMWP_Emails_List_Table constructor.
	 *
	 * @param array $args
	 */
	function __construct( $args = [] ) {
		$args = wp_parse_args( $args, [
			'singular'  => __( 'item', 'forumwp' ),
			'plural'    => __( 'items', 'forumwp' ),
			'ajax'      => false
		] );

		$this->no_items_message = $args['plural'] . ' ' . __( 'not found.', 'forumwp' );

		parent::__construct( $args );
	}


	/**
	 * @param callable $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	function __call( $name, $arguments ) {
		return call_user_func_array( [ $this, $name ], $arguments );
	}


	/**
	 *
	 */
	function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = [];
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = [ $columns, $hidden, $sortable ];
	}


	/**
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	function column_default( $item, $column_name ) {
		if( isset( $item[ $column_name ] ) ) {
			return $item[ $column_name ];
		} else {
			return '';
		}
	}


	/**
	 *
	 */
	function no_items() {
		echo $this->no_items_message;
	}


	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	function set_sortable_columns( $args = [] ) {
		$return_args = [];
		foreach ( $args as $k => $val ) {
			if ( is_numeric( $k ) ) {
				$return_args[ $val ] = [ $val, $val == $this->default_sorting_field ];
			} elseif ( is_string( $k ) ) {
				$return_args[ $k ] = [ $val, $k == $this->default_sorting_field ];
			} else {
				continue;
			}
		}
		$this->sortable_columns = $return_args;
		return $this;
	}


	/**
	 * @return array
	 */
	function get_sortable_columns() {
		return $this->sortable_columns;
	}


	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	function set_columns( $args = [] ) {
		if ( count( $this->bulk_actions ) ) {
			$args = array_merge( [ 'cb' => '<input type="checkbox" />' ], $args );
		}
		$this->columns = $args;

		return $this;
	}


	/**
	 * @return array
	 */
	function get_columns() {
		return $this->columns;
	}


	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	function set_actions( $args = [] ) {
		$this->actions = $args;
		return $this;
	}


	/**
	 * @return array
	 */
	function get_actions() {
		return $this->actions;
	}


	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	function set_bulk_actions( $args = [] ) {
		$this->bulk_actions = $args;
		return $this;
	}


	/**
	 * @return array
	 */
	function get_bulk_actions() {
		return $this->bulk_actions;
	}


	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_email( $item ) {
		$active = FMWP()->options()->get( $item['key'] . '_on' );

		return '<span class="dashicons fmwp-notification-status ' . ( ! empty( $active ) ? 'fmwp-notification-is-active dashicons-yes' : 'dashicons-no-alt' ) . '"></span><a href="' . add_query_arg( [ 'email' => $item['key'] ] ) . '"><strong>'. $item['title'] . '</strong></a>';
	}


	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_recipients( $item ) {
		if ( $item['recipient'] == 'admin' ) {
			return FMWP()->options()->get( 'admin_email' );
		} else {
			return __( 'Member', 'forumwp' );
		}
	}


	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_configure( $item ) {
		return '<a class="button fmwp-email-configure" href="' . add_query_arg( [ 'email' => $item['key'] ] ) . '"><span class="dashicons dashicons-admin-generic"></span></a>';
	}


	/**
	 * @param array $attr
	 */
	function fmwp_set_pagination_args( $attr = [] ) {
		$this->set_pagination_args( $attr );
	}
}


$ListTable = new FMWP_Emails_List_Table( [
	'singular'  => __( 'Email Notification', 'forumwp' ),
	'plural'    => __( 'Email Notifications', 'forumwp' ),
	'ajax'      => false
] );

$per_page   = 20;
$paged      = $ListTable->get_pagenum();

$columns = apply_filters( 'fmwp_email_templates_columns', [
	'email'         => __( 'Email', 'forumwp' ),
	'recipients'    => __( 'Recipient(s)', 'forumwp' ),
	'configure'     => '',
] );

$ListTable->set_columns( $columns );

$emails = FMWP()->config()->get( 'email_notifications' );

$ListTable->prepare_items();
$ListTable->items = $emails;
$ListTable->fmwp_set_pagination_args( [ 'total_items' => count( $emails ), 'per_page' => $per_page ] ); ?>

<form action="" method="get" name="fmwp-settings-emails" id="fmwp-settings-emails">
	<input type="hidden" name="page" value="forumwp-settings" />
	<input type="hidden" name="tab" value="email" />
	<?php if ( ! empty( $_GET['section'] ) ) { ?>
		<input type="hidden" name="section" value="<?php echo esc_attr( $_GET['section'] ) ?>" />
	<?php }

	$ListTable->display(); ?>
</form>