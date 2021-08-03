<?php if ( ! defined( 'ABSPATH' ) ) exit;

$item = isset( $fmwp_js_forum_row['item'] ) ? $fmwp_js_forum_row['item'] : 'forum';
$actions = isset( $fmwp_js_forum_row['actions'] ) ? $fmwp_js_forum_row['actions'] : ''; ?>

<div class="fmwp-forum-row<# if ( <?php echo $item; ?>.is_locked ) { #> fmwp-forum-locked<# } #><# if ( Object.keys( <?php echo $item; ?>.dropdown_actions ).length === 0 ) { #> fmwp-forum-no-actions<# } #>" data-forum_id="{{{<?php echo $item; ?>.forum_id}}}">
	<div class="fmwp-forum-row-lines">

		<div class="fmwp-forum-row-line fmwp-forum-primary-data">

			<# if ( <?php echo $item; ?>.thumbnail ) { #>
				<a href="{{{<?php echo $item; ?>.permalink}}}" title="{{{<?php echo $item; ?>.title}}}" class="fmwp-forum-avatar-link">
					<span class="fmwp-forum-avatar">{{{<?php echo $item; ?>.thumbnail}}}</span>
				</a>
			<# } else if ( <?php echo $item; ?>.icon ) { #>
				<a href="{{{<?php echo $item; ?>.permalink}}}" title="{{{<?php echo $item; ?>.title}}}" class="fmwp-forum-avatar-link">
					<span class="fmwp-forum-avatar fmwp-forum-icon" style="color: {{{<?php echo $item; ?>.icon_color}}}; background-color: {{{<?php echo $item; ?>.icon_bgcolor}}};">
						<i class="{{{<?php echo $item; ?>.icon}}}"></i>
					</span>
				</a>
			<# } #>

			<div class="fmwp-forum-data<# if ( ! <?php echo $item; ?>.thumbnail && ! <?php echo $item; ?>.icon ) { #> fmwp-forum-fullwidth-data<# } #>">

				<span class="fmwp-forum-first-line">
					<span class="fmwp-forum-title-line">
						<a href="{{{<?php echo $item; ?>.permalink}}}">
							<span class="fmwp-forum-status-marker fmwp-forum-locked-marker fmwp-tip-n" title="<?php esc_attr_e( 'Locked', 'forumwp' ) ?>">
								<i class="fas fa-lock"></i>
							</span>
							<span class="fmwp-forum-title">
								{{{<?php echo $item; ?>.title}}}
							</span>
						</a>
					</span>
					<span class="fmwp-forum-categories-wrapper">
						<?php if ( FMWP()->options()->get( 'forum_categories' ) ) { ?>
							<# if ( <?php echo $item; ?>.categories.length > 0 ) { #>
								<# _.each( <?php echo $item; ?>.categories, function( category, key, list ) { #>
									<span class="fmwp-forum-category"><a href="{{{category.href}}}">{{{category.name}}}</a></span>
								<# }); #>
							<# } #>
						<?php } ?>
					</span>
				</span>

				<div class="fmwp-forum-description">{{{<?php echo $item; ?>.strip_content}}}</div>

				<# if ( <?php echo $item; ?>.latest_topic ) { #>
					<div class="fmwp-forum-latest-topic">
						<strong><?php _e('Latest topic:', 'forumwp' ) ?></strong> <a href="{{{<?php echo $item; ?>.latest_topic_url}}}">{{{<?php echo $item; ?>.latest_topic}}}</a>
					</div>
				<# } #>
			</div>
		</div>

		<div class="fmwp-forum-row-line fmwp-forum-statistics-data<# if ( ! <?php echo $item; ?>.thumbnail && ! <?php echo $item; ?>.icon ) { #> fmwp-forum-fullwidth-data<# } #>">
			<div class="fmwp-forum-topics" title="{{{<?php echo $item; ?>.topics}}} <?php esc_attr_e( 'topics', 'forumwp' ) ?>">
				<span class="fmwp-responsive fmwp-ui-xs">{{{<?php echo $item; ?>.topics}}} <?php esc_attr_e( 'topics', 'forumwp' ) ?></span>
				<span class="fmwp-responsive fmwp-ui-s fmwp-ui-m fmwp-ui-l fmwp-ui-xl">{{{<?php echo $item; ?>.topics}}}</span>
			</div>
			<div class="fmwp-forum-replies-count" title="{{{<?php echo $item; ?>.replies}}} <?php esc_attr_e( 'people have replied', 'forumwp' ) ?>">
				<span class="fmwp-responsive fmwp-ui-xs">{{{<?php echo $item; ?>.replies}}} <?php esc_attr_e( 'replies', 'forumwp' ) ?></span>
				<span class="fmwp-responsive fmwp-ui-s fmwp-ui-m fmwp-ui-l fmwp-ui-xl">{{{<?php echo $item; ?>.replies}}}</span>
			</div>

			<div class="fmwp-forum-last-upgrade" title="<?php esc_attr_e( 'Last Updated', 'forumwp' ) ?>">
				{{{<?php echo $item; ?>.last_upgrade}}}
			</div>
		</div>
	</div>

	<div class="fmwp-forum-actions">
		<?php if ( is_user_logged_in() ) {
			if ( $actions === 'edit' ) { ?>
				<# if ( Object.keys( <?php echo $item; ?>.dropdown_actions ).length > 0 ) { #>
					<span class="fmwp-forum-actions-dropdown" title="<?php esc_attr_e( 'More Actions', 'forumwp' ) ?>">
						<i class="fas fa-angle-down"></i>
						<div class="fmwp-dropdown" data-element=".fmwp-forum-actions-dropdown" data-trigger="click">
							<ul>
								<# _.each( <?php echo $item; ?>.dropdown_actions, function( title, key, list ) { #>
									<li><a href="javascript:void(0);" class="{{{key}}}">{{{title}}}</a></li>
								<# }); #>
							</ul>
						</div>
					</span>
				<# } #>
			<?php }

			do_action( 'fmwp_forum_row_actions', $item, $actions );
		} ?>
	</div>
</div>