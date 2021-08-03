<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="fmwp-popup-overlay"></div>
<div id="fmwp-login-popup-wrapper" class="fmwp-popup fmwp">
	<div class="fmwp-popup-topbar">
		<div class="fmwp-popup-header" data-default="<?php esc_attr_e( 'Login', 'forumwp' ) ?>"></div>
		<span class="fmwp-popup-close fmwp-tip-n" title="<?php esc_attr_e( 'Close', 'forumwp' ) ?>">
			<i class="fas fa-times"></i>
		</span>
	</div>

	<?php if ( version_compare( get_bloginfo( 'version' ),'5.4', '<' ) ) {
		echo do_shortcode( '[fmwp_login_form is_popup="1" /]' );
	} else {
		echo apply_shortcodes( '[fmwp_login_form is_popup="1" /]' );
	} ?>

	<span>
		<?php _e( 'Don\'t have an account?', 'forumwp' ) ?>
		<a href="<?php echo esc_attr( FMWP()->common()->get_preset_page_link( 'register' ) ) ?>">
			<?php _e( 'Sign up', 'forumwp' ) ?>
		</a>
	</span>

	<div class="clear"></div>
</div>