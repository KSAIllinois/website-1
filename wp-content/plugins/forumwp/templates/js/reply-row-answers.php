<?php if ( ! defined( 'ABSPATH' ) ) exit;

$item = isset( $fmwp_js_reply_row_answers['item'] ) ? $fmwp_js_reply_row_answers['item'] : 'reply';
$active = isset( $fmwp_js_reply_row_answers['active'] ) ? $fmwp_js_reply_row_answers['active'] : false; ?>

<# if ( <?php echo $item; ?>.has_children ) { #>
	<# if ( <?php echo $item; ?>.answers.length > 0 ) { #>
		<span class="fmwp-reply-avatars">
			<a href="javascript:void(0);" title="<?php esc_attr_e( 'Show all replies', 'forumwp' ) ?>" class="fmwp-show-child-replies<?php if ( $active ) { ?> fmwp-replies-loaded<?php } ?>">
				<span class="fmwp-replies-count">{{{<?php echo $item; ?>.total_replies}}}</span>
				<# if ( <?php echo $item; ?>.total_replies == 1 ) { #>
					 <?php _e( 'reply', 'forumwp' ) ?>
				<# } else { #>
					 <?php _e( 'replies', 'forumwp' ) ?>
				<# } #>
			</a>&nbsp;&nbsp;
			<# _.each( <?php echo $item; ?>.answers, function( user, key, list ) { #>
				{{{user.avatar}}}
			<# }); #>
			<# if ( <?php echo $item; ?>.more_answers ) { #>
				<span class="fmwp-reply-more-answers">
					<i class="fas fa-ellipsis-h"></i>
				</span>
			<# } #>
		</span>
	<# } #>
<# } #>