<?php if ( ! defined( 'ABSPATH' ) ) exit;

$current_tab = empty( $_GET['tab'] ) ? '' : urldecode( sanitize_key( $_GET['tab'] ) );
$current_subtab = empty( $_GET['section'] ) ? '' : urldecode( sanitize_key( $_GET['section'] ) ); ?>

<div id="fmwp-settings-wrap" class="wrap">
	<h2><?php printf( __( '%s - Settings', 'forumwp' ), fmwp_plugin_name ) ?></h2>

	<?php echo FMWP()->admin()->settings()->tabs_menu() . FMWP()->admin()->settings()->subtabs_menu( $current_tab );

	do_action( "fmwp_before_settings_{$current_tab}_{$current_subtab}_content" );

	if ( FMWP()->admin()->settings()->section_is_custom( $current_tab, $current_subtab ) ) {

		do_action( "fmwp_settings_page_{$current_tab}_{$current_subtab}_before_section" );

		$settings_section = FMWP()->admin()->settings()->display_section( $current_tab, $current_subtab );

		echo apply_filters( "fmwp_settings_section_{$current_tab}_{$current_subtab}_content", $settings_section );

	} else { ?>

		<form method="post" action="" name="fmwp-settings-form" id="fmwp-settings-form">
			<input type="hidden" value="save" name="fmwp-settings-action" />

			<?php do_action( "fmwp_settings_page_{$current_tab}_{$current_subtab}_before_section" );

			$settings_section = FMWP()->admin()->settings()->display_section( $current_tab, $current_subtab );

			echo apply_filters( "fmwp_settings_section_{$current_tab}_{$current_subtab}_content", $settings_section ); ?>

			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'forumwp' ) ?>" />
				<input type="hidden" name="__fmwpnonce" value="<?php echo wp_create_nonce( 'fmwp-settings-nonce' ); ?>" />
			</p>
		</form>

	<?php } ?>
</div>