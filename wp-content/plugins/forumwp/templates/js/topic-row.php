<?php if ( ! defined( 'ABSPATH' ) ) exit;

$item = isset( $fmwp_js_topic_row['item'] ) ? $fmwp_js_topic_row['item'] : 'topic';
$actions = isset( $fmwp_js_topic_row['actions'] ) ? $fmwp_js_topic_row['actions'] : '';
$show_forum = isset( $fmwp_js_topic_row['show_forum'] ) ? $fmwp_js_topic_row['show_forum'] : true; ?>

<div class="fmwp-topic-row<# if ( <?php echo $item; ?>.is_trashed ) { #> fmwp-topic-trashed<# } #><# if ( <?php echo $item; ?>.is_spam ) { #> fmwp-topic-spam<# } #><# if ( <?php echo $item; ?>.is_pending ) { #> fmwp-topic-pending<# } #><# if ( <?php echo $item; ?>.is_reported ) { #> fmwp-topic-reported<# } #><# if ( <?php echo $item; ?>.is_locked ) { #> fmwp-topic-locked<# } #><# if ( <?php echo $item; ?>.is_pinned ) { #> fmwp-topic-pinned<# } #><# if ( <?php echo $item; ?>.is_announcement ) { #> fmwp-topic-announcement<# } #><# if ( <?php echo $item; ?>.is_global ) { #> fmwp-topic-global<# } #><?php if ( FMWP()->options()->get('topic_tags') ) { ?><# if ( <?php echo $item; ?>.tags.length > 0 ) { #> fmwp-topic-tagged<# } #><?php } ?><?php do_action( 'fmwp_js_template_topic_row_classes', $item ) ?>"
	 data-topic_id="{{{<?php echo $item; ?>.topic_id}}}"
	 data-is_author="<# if ( <?php echo $item; ?>.is_author ) { #>1<# } #>"
	 data-trashed="<# if ( <?php echo $item; ?>.is_trashed ) { #>1<# } #>"
	 data-locked="<# if ( <?php echo $item; ?>.is_locked ) { #>1<# } #>"
	 data-pinned="<# if ( <?php echo $item; ?>.is_pinned ) { #>1<# } #>">

	<div class="fmwp-topic-avatar fmwp-responsive fmwp-ui-xs">
		<a href="{{{<?php echo $item; ?>.author_url}}}" title="{{{<?php echo $item; ?>.author}}} <?php esc_attr_e( 'Profile', 'forumwp' ) ?>" data-fmwp_tooltip="{{<?php echo $item; ?>.author_card}}" data-fmwp_tooltip_id="fmwp-user-card-tooltip">
			{{{<?php echo $item; ?>.author_avatar}}}
		</a>
	</div>

	<div class="fmwp-topic-row-lines">
		<div class="fmwp-topic-row-line fmwp-topic-primary-data">
			<span class="fmwp-topic-title-line">
				<a href="{{{<?php echo $item; ?>.permalink}}}">
					<?php foreach ( FMWP()->common()->topic()->status_markers as $class => $data ) { ?>
						<span class="fmwp-topic-status-marker <?php echo esc_attr( $class ) ?> fmwp-tip-n"
							  title="<?php echo esc_attr( $data['title'] ) ?>">
							<i class="<?php echo esc_attr( $data['icon'] ) ?>"></i>
						</span>
					<?php } ?>
					<span class="fmwp-topic-title">
						{{{<?php echo $item; ?>.title}}}
					</span>
				</a>
			</span>
			<span class="fmwp-topic-tags-wrapper">
				<?php if ( FMWP()->options()->get( 'topic_tags' ) ) { ?>
					<# if ( <?php echo $item; ?>.tags.length > 0 ) { #>
						<# _.each( <?php echo $item; ?>.tags, function( tag, key, list ) { #>
							<span class="fmwp-topic-tag"><a href="{{{tag.href}}}">{{{tag.name}}}</a></span>
						<# }); #>
					<# } #>
				<?php }

				FMWP()->get_template_part( 'topic-status-tags' ); ?>
			</span>

			<?php if ( $show_forum ) { ?>
				<span class="fmwp-topic-forum">
					<strong><?php _e('Forum:', 'forumwp' ) ?></strong> <a href="{{{<?php echo $item; ?>.forum_url}}}">{{{<?php echo $item; ?>.forum_title}}}</a>
				</span>
			<?php } ?>
		</div>

		<div class="fmwp-topic-row-line fmwp-topic-statistics-data">
			<div class="fmwp-topic-replies fmwp-responsive fmwp-ui-s fmwp-ui-m fmwp-ui-l fmwp-ui-xl">
				<# _.each( <?php echo $item; ?>.people, function( user, key, list ) { #>
					<a href="{{{user.url}}}">{{{user.avatar}}}</a>
				<# }); #>
			</div>

			<div class="fmwp-topic-statistics-section">
				<div class="fmwp-topic-replies-count" title="{{{<?php echo $item; ?>.respondents_count}}} <?php esc_attr_e( 'people have replied', 'forumwp' ) ?>">
					<span class="fmwp-responsive fmwp-ui-xs">{{{<?php echo $item; ?>.replies}}} <?php esc_attr_e( 'replies', 'forumwp' ) ?></span>
					<span class="fmwp-responsive fmwp-ui-s fmwp-ui-m fmwp-ui-l fmwp-ui-xl">{{{<?php echo $item; ?>.replies}}}</span>
				</div>

				<?php do_action( 'fmwp_topic_row_stats', $item ); ?>

				<div class="fmwp-topic-views" title="<?php esc_attr_e( 'Views', 'forumwp' ) ?>">
					<span class="fmwp-responsive fmwp-ui-xs">{{{<?php echo $item; ?>.views}}} <?php esc_attr_e( 'views', 'forumwp' ) ?></span>
					<span class="fmwp-responsive fmwp-ui-s fmwp-ui-m fmwp-ui-l fmwp-ui-xl">{{{<?php echo $item; ?>.views}}}</span>
				</div>

				<div class="fmwp-topic-last-upgrade" title="<?php esc_attr_e( 'Last Updated', 'forumwp' ) ?>">
					{{{<?php echo $item; ?>.last_upgrade}}}
				</div>
			</div>
		</div>
	</div>

	<div class="fmwp-topic-actions">
		<?php if ( is_user_logged_in() ) {
			if ( $actions === 'edit' ) { ?>
				<# if ( Object.keys( <?php echo $item; ?>.dropdown_actions ).length > 0 ) { #>
					<div class="fmwp-topic-actions-dropdown">
						<i class="fas fa-angle-down" title="<?php esc_attr_e( 'More Actions', 'forumwp' ) ?>"></i>
						<div class="fmwp-dropdown" data-element=".fmwp-topic-actions-dropdown" data-trigger="click">
							<ul>
								<# _.each( <?php echo $item; ?>.dropdown_actions, function( title, key, list ) { #>
									<li><a href="javascript:void(0);" class="{{{key}}}">{{{title}}}</a></li>
								<# }); #>
							</ul>
						</div>
					</div>
				<# } #>
			<?php }

			do_action( 'fmwp_topic_row_actions', $item, $actions );
		} ?>
	</div>
</div>