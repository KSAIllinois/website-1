<?php if ( ! defined( 'ABSPATH' ) ) exit;

$user_id = ! empty( $fmwp_user_replies['user_id'] ) ? $fmwp_user_replies['user_id'] : get_current_user_id();

FMWP()->get_template_part( 'js/replies-list', [
	'show_footer'       => false,
	'show_reply_title'  => true,
] ); ?>

<div class="fmwp-user-replies fmwp" data-user_id="<?php echo esc_attr( $user_id ) ?>">
	<div class="fmwp-replies-wrapper"></div>
</div>