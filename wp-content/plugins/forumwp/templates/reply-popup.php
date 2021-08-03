<?php if ( ! defined( 'ABSPATH' ) ) exit;

$topic_id = ! empty( $fmwp_reply_popup['topic_id'] ) ? $fmwp_reply_popup['topic_id'] : false;

FMWP()->get_template_part( 'js/single-reply', [ 'topic_id' => $topic_id ] );

ob_start();

do_action( 'fmwp_reply_popup_actions' ); ?>

<input type="button" class="fmwp-reply-popup-discard" title="<?php esc_attr_e( 'Back to topic', 'forumwp' ) ?>" value="<?php _e( 'Discard', 'forumwp' ) ?>" />
<span style="position: relative;">
	<input type="button" class="fmwp-reply-popup-submit" title="<?php esc_attr_e( 'Submit Reply', 'forumwp' ) ?>" value="<?php _e( 'Submit Reply', 'forumwp' ) ?>" />
	<?php FMWP()->ajax_loader( 25 ); ?>
</span>

<?php $buttons = ob_get_clean(); ?>

<div id="fmwp-reply-popup-wrapper" class="fmwp fmwp-post-popup-wrapper">
	<span class="fmwp-post-popup-toolbar">
		<span class="fmwp-post-popup-action-fullsize">
			<i class="fas fa-expand-arrows-alt"></i>
			<i class="fas fa-compress-arrows-alt"></i>
		</span>
	</span>

	<form action="" method="post" name="fmwp-create-reply">
		<span id="fmwp-reply-popup-head" class="fmwp-post-popup-header">
			<span class="fmwp-post-popup-header-section">
				<span id="fmwp-reply-popup-avatar">
					<?php echo FMWP()->user()->get_avatar( get_current_user_id() ); ?>
				</span>

				<span id="fmwp-reply-popup-quote">
					<i class="fas fa-reply"></i>
					<span><?php _e( 'Replying to:', 'forumwp' )?> "<?php the_title() ?>"</span>
				</span>
			</span>
			<span class="fmwp-post-popup-header-section fmwp-post-popup-actions fmwp-responsive fmwp-ui-m fmwp-ui-l fmwp-ui-xl">
				<?php echo $buttons; ?>
			</span>
		</span>
		<div class="clear"></div>

		<input type="hidden" name="fmwp-action" value="create-reply" />
		<input type="hidden" name="fmwp-reply[reply_id]" value="" />
		<input type="hidden" name="fmwp-reply[topic_id]" value="<?php echo esc_attr( get_the_ID() ) ?>" />
		<input type="hidden" name="fmwp-reply[parent_id]" value="" />
		<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'fmwp-create-reply' ) ) ?>" />

		<div id="fmwp-reply-popup-editors">
			<div id="fmwp-reply-popup-editor" data-editor-id="fmwpreplycontent">
				<label>
					<?php FMWP()->common()->render_editor( 'reply' ); ?>
				</label>
			</div>
			<span id="fmwp-reply-popup-preview-action" data-show_label="<?php esc_attr_e( 'Show preview', 'forumwp' ) ?>" data-hide_label="<?php esc_attr_e( 'Hide preview', 'forumwp' ) ?>">
				<?php _e( 'Hide preview', 'forumwp' ) ?>
			</span>
			<div id="fmwp-reply-popup-editor-preview">
				<div id="fmwpreplycontent-preview"></div>
			</div>
		</div>

		<span class="fmwp-post-popup-actions-bottom fmwp-responsive fmwp-ui-xs fmwp-ui-s">
			<?php echo $buttons; ?>
		</span>
	</form>
	<div class="clear"></div>
</div>