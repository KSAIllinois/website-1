<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<script type="text/html" id="tmpl-fmwp-parent-reply">
	<?php FMWP()->get_template_part( 'js/reply-row', [
		'item'          => 'data',
		'actions'       => 'edit',
		'show_footer'   => true,
		'topic_id'      => ! empty( $fmwp_js_single_reply['topic_id'] ) ? $fmwp_js_single_reply['topic_id'] : false,
	] ); ?>
</script>