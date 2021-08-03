<?php if ( ! defined( 'ABSPATH' ) ) exit;

FMWP()->get_template_part( 'js/replies-list', [
	'show_footer'       => false,
	'show_reply_title'  => true,
] ); ?>

<div class="fmwp-replies-wrapper"></div>