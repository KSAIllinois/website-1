<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="fmwp-user-card-wrapper">
	<div class="fmwp-user-card-avatar">
		<a href="<?php echo FMWP()->user()->get_profile_link( $fmwp_user_card->ID ) ?>">
			<?php echo FMWP()->user()->get_avatar( $fmwp_user_card->ID, 'inline', 60 ); ?>
		</a>
	</div>
	<div class="fmwp-user-card-content">
		<div class="fmwp-user-card-name">
			<a href="<?php echo FMWP()->user()->get_profile_link( $fmwp_user_card->ID ) ?>" title="<?php esc_attr_e( 'User Profile', 'forumwp' ) ?>">
				<?php echo FMWP()->user()->display_name( $fmwp_user_card ) ?>
			</a>
		</div>
		<?php if ( FMWP()->options()->get( 'reply_user_role' ) ) { ?>
			<div class="fmwp-user-card-role">
				<?php global $wp_roles;
				$user_roles = FMWP()->user()->get_roles( $fmwp_user_card );

				$tags = [];
				if ( ! empty( $user_roles ) ) {
					foreach ( $user_roles as $role ) {
						$name = translate_user_role( $wp_roles->roles[ $role ]['name'] );
						$tags[] = [
							'title' => $name,
						];
					}
				}
				if ( count( $tags ) ) { ?>
					<span class="fmwp-user-card-tags">
						<?php foreach ( $tags as $tag ) { ?>
							<span class="fmwp-user-card-tag <?php echo ! empty( $tag['class'] ) ? esc_attr( $tag['class'] ) : '' ?>">
								<?php echo $tag['title'] ?>
							</span>
						<?php } ?>
					</span>
				<?php } ?>
			</div>
		<?php } ?>

		<div class="fmwp-user-card-description">
			<?php echo nl2br( $fmwp_user_card->description ); ?>
		</div>

		<span class="fmwp-user-card-stats">
			<span>
				<a href="<?php echo FMWP()->user()->get_profile_link( $fmwp_user_card->ID, 'topics' ) ?>"
                   title="<?php esc_attr_e( 'User Topics', 'forumwp' ) ?>">
				<?php $topics = FMWP()->user()->get_topics_count( $fmwp_user_card->ID );

				printf( _n( '%s topic', '%s topics', $topics, 'forumwp' ), $topics ); ?>
				</a>
			</span>
			<span>
				<a href="<?php echo FMWP()->user()->get_profile_link( $fmwp_user_card->ID, 'replies' ) ?>"
                   title="<?php esc_attr_e( 'User Replies', 'forumwp' ) ?>">
					<?php $replies = FMWP()->user()->get_replies_count( $fmwp_user_card->ID );

					printf( _n( '%s reply', '%s replies', $replies, 'forumwp' ), $replies ); ?>
				</a>
			</span>
		</span>
	</div>
</div>