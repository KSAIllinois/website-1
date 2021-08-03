<?php if ( ! defined( 'ABSPATH' ) ) exit;

$type = isset( $fmwp_js_forums_list['type'] ) ? $fmwp_js_forums_list['type'] : '';
$actions = isset( $fmwp_js_forums_list['actions'] ) ? $fmwp_js_forums_list['actions'] : ''; ?>

<script type="text/html" id="tmpl-fmwp-forums-<?php echo ! empty( $type ) ? esc_attr( $type ) . '-' : ''; ?>list">
	<# if ( data.forums.length > 0 ) { #>
		<# _.each( data.forums, function( forum, key, list ) { #>
			<?php FMWP()->get_template_part( 'js/forum-row', [
				'item'      => 'forum',
				'actions'   => $actions,
			] ); ?>
		<# }); #>
	<# } #>
</script>