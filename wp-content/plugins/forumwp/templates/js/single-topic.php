<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<script type="text/html" id="tmpl-fmwp-topic">
	<?php FMWP()->get_template_part( 'js/topic-row', [
		'item'          => 'data',
		'actions'       => 'edit',
		'show_forum'    => isset( $fmwp_js_single_topic['show_forum'] ) ? $fmwp_js_single_topic['show_forum'] : true,
	] ); ?>
</script>