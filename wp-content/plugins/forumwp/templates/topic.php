<?php if ( ! defined( 'ABSPATH' ) ) exit;

$topic = get_post( $fmwp_topic['id'] );

if ( post_password_required( $topic ) ) {

	echo get_the_password_form( $topic );

} else {

	$forum_id = FMWP()->common()->topic()->get_forum_id( $fmwp_topic['id'] );
	$forum = get_post( $forum_id );

	$show_header = ( isset( $fmwp_topic['show_header'] ) && $fmwp_topic['show_header'] == 'yes' ) ? true : false;

	FMWP()->get_template_part( 'js/replies-list', [
		'actions' => 'edit',
		'topic_id' => $fmwp_topic['id'],
	] );

	FMWP()->get_template_part( 'js/single-reply-subreplies', [
		'item'      => 'data',
		'active'    => true,
	] );

	if ( FMWP()->options()->get( 'topic_tags' ) ) {
		FMWP()->get_template_part( 'js/single-topic-tags' );
	}

	$unlogged_class = FMWP()->frontend()->shortcodes()->unlogged_class();

	$visibility = get_post_meta( $forum_id, 'fmwp_visibility', true );

	$forum_link = get_permalink( $forum_id );

	$author = get_userdata( $topic->post_author );
	$author_link = FMWP()->user()->get_profile_link( $topic->post_author );

	setup_postdata( $fmwp_topic['id'] );

	//Topic dropdown actions
	$topic_dropdown_items = [];

	$replies_count = FMWP()->common()->topic()->get_statistics( $fmwp_topic['id'], 'replies' );

	$status_classes = '';
	if ( is_user_logged_in() || $visibility == 'public' ) {
		if ( FMWP()->common()->topic()->is_spam( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-spam';
		}

		if ( FMWP()->common()->topic()->is_trashed( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-trashed';
		}

		if ( FMWP()->common()->topic()->is_locked( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-locked';
		}

		if ( FMWP()->common()->topic()->is_pending( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-pending';
		}

		if ( FMWP()->common()->topic()->is_pinned( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-pinned';
		}

		if ( FMWP()->common()->topic()->is_announcement( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-announcement';
		}

		if ( FMWP()->common()->topic()->is_global( $fmwp_topic['id'] ) ) {
			$status_classes .= ' fmwp-topic-global';
		}

		if ( is_user_logged_in() ) {
			if ( FMWP()->reports()->is_reported_by_user( $fmwp_topic['id'], get_current_user_id() ) ) {
				$status_classes .= ' fmwp-topic-reported';
			} elseif ( current_user_can( 'fmwp_see_reports' ) && FMWP()->reports()->is_reported( $fmwp_topic['id'] ) ) {
				$status_classes .= ' fmwp-topic-reported';
			}
		}

		$status_classes = apply_filters( 'fmwp_topic_status_classes', $status_classes, $fmwp_topic );

		$topic_dropdown_items = FMWP()->common()->topic()->actions_list( $topic );
		foreach ( $topic_dropdown_items as $key => &$value ) {
			$value = '<a href="javascript:void(0);" class="' . esc_attr( $key ) . '">' . $value . '</a>';
		}
	}

	do_action( 'fmwp_before_individual_topic' ); ?>

	<div class="fmwp fmwp-topic-main-wrapper <?php echo esc_attr( $status_classes ) ?><?php if ( ! empty( $unlogged_class ) ) { ?> fmwp-unlogged-data<?php } ?>">
		<?php if ( ! is_user_logged_in() && $visibility != 'public' ) {

			printf( __( 'Please <a href="javascript:void(0);" class="%s" title="Login to view" data-fmwp_popup_title="Login to view topic">login</a> to view this topic', 'forumwp' ), $unlogged_class );

		} else {

			if ( ! empty( $_GET['fmwp-msg'] ) ) {

				$msg = sanitize_key( $_GET['fmwp-msg'] );

				switch ( $msg ) {
					default:
						do_action( 'fmwp_topic_header_message', $topic, $msg );
						break;
				}

			} ?>

			<div class="fmwp-topic-head">
				<?php if ( ! FMWP()->is_topic_page() || $show_header ) {
					if ( FMWP()->is_topic_page() && $show_header ) { ?>
						<h1><?php echo $topic->post_title ?></h1>
					<?php } else { ?>
						<h3><?php echo $topic->post_title ?></h3>
					<?php } ?>
				<?php } ?>
				<span class="fmwp-topic-info">
					<?php if ( ! empty( FMWP()->options()->get( 'show_forum' ) ) && ! empty( $forum ) ) { ?>
						<a href="<?php echo esc_attr( $forum_link ) ?>" title="<?php echo esc_attr( $forum->post_title ) ?>" class="fmwp-topic-forum-link">
							<?php echo $forum->post_title ?>
						</a>
					<?php } ?>
					<span class="fmwp-topic-stats">
						<?php $replies = FMWP()->common()->topic()->get_statistics( $fmwp_topic['id'], 'replies' );
						printf( _n( '<span id="fmwp-replies-total">%s</span> reply', '<span id="fmwp-replies-total">%s</span> replies', $replies, 'forumwp' ), $replies ); ?>
					</span>

					<?php do_action( 'fmwp_topic_stats', $fmwp_topic ); ?>

					<span class="fmwp-topic-stats">
						<?php $views = FMWP()->common()->topic()->get_statistics( $fmwp_topic['id'], 'views' );
						printf( _n( '<span id="fmwp-views-total">%s</span> view', '<span id="fmwp-views-total">%s</span> views', $views, 'forumwp' ), $views ); ?>
					</span>
					<?php if ( FMWP()->options()->get( 'topic_tags' ) ) { ?>
						<?php $topic_tags = FMWP()->common()->topic()->get_tags( $topic->ID );

						if ( count( $topic_tags ) ) { ?>
							<span class="fmwp-topic-stats fmwp-tags-stats">
								<?php _e( 'Tags:' ); ?>&nbsp;
								<span class="fmwp-topic-tags-list">
									<?php $i = 1; foreach ( $topic_tags as $tag ) { ?>
										<a href="<?php echo esc_attr( get_term_link( $tag->term_id, 'fmwp_topic_tag' ) ) ?>"><?php echo $tag->name ?></a><?php if ( $i < count( $topic_tags ) ) {?>,&nbsp;<?php }
										$i++;
									} ?>
								</span>
							</span>
						<?php }
					} ?>
				</span>
			</div>

			<div class="fmwp-topic-content">
				<div class="fmwp-topic-base" data-topic_id="<?php echo esc_attr( $fmwp_topic['id'] ) ?>"
					 data-trashed="<?php echo ( FMWP()->common()->topic()->is_trashed( $fmwp_topic['id'] ) ) ? 1 : 0 ?>"
					 data-locked="<?php echo ( FMWP()->common()->topic()->is_locked( $fmwp_topic['id'] ) ) ? 1 : 0 ?>"
					 data-pinned="<?php echo ( FMWP()->common()->topic()->is_pinned( $fmwp_topic['id'] ) ) ? 1 : 0 ?>">
					<div class="fmwp-topic-base-header<?php if ( !( is_user_logged_in() && count( $topic_dropdown_items ) > 0 ) ) { ?> fmwp-topic-no-actions<?php } ?>">
						<div class="fmwp-topic-avatar">
							<a href="<?php echo FMWP()->user()->get_profile_link( $topic->post_author ) ?>" data-fmwp_tooltip="<?php echo esc_attr( FMWP()->user()->generate_card( $topic->post_author ) ) ?>" data-fmwp_tooltip_id="fmwp-user-card-tooltip">
								<?php echo FMWP()->user()->get_avatar( $topic->post_author, 'inline', 60 ) ?>
							</a>
						</div>
						<div class="fmwp-topic-data">
							<div class="fmwp-topic-data-top">
								<span class="fmwp-topic-data-head">
									<span class="fmwp-topic-data-head-section">
										<a href="<?php echo esc_attr( $author_link ) ?>" title="<?php printf( esc_attr( '%s Profile', 'forumwp' ), FMWP()->user()->display_name( $author ) ) ?>">
											<?php foreach ( FMWP()->common()->topic()->status_markers as $class => $data ) { ?>
												<span class="fmwp-topic-status-marker <?php echo esc_attr( $class ) ?> fmwp-tip-n"
													  title="<?php echo esc_attr( $data['title'] ) ?>">
													<i class="<?php echo esc_attr( $data['icon'] ) ?>"></i>
												</span>
											<?php } ?>
											<?php echo FMWP()->user()->display_name( $author ) ?>
										</a>

										<?php $topic_author_tags = FMWP()->common()->topic()->get_author_tags( $topic );
										if ( count( $topic_author_tags ) ) { ?>
											<span class="fmwp-topic-author-tags-wrapper fmwp-responsive fmwp-ui-s fmwp-ui-m fmwp-ui-l fmwp-ui-xl">
												<?php foreach ( $topic_author_tags as $tag ) { ?>
													<span class="fmwp-topic-tag <?php echo ! empty( $tag['class'] ) ? esc_attr( $tag['class'] ) : '' ?>">
														<?php echo $tag['title'] ?>
													</span>
												<?php }

												FMWP()->get_template_part( 'topic-status-tags' ); ?>

											</span>
										<?php } ?>
									</span>

									<span class="fmwp-topic-subdata">
										<?php
										$last_upgrade = '';
										if ( ! FMWP()->common()->topic()->is_pending( $topic->ID ) ) {
											$last_upgrade = get_post_meta( $topic->ID, 'fmwp_last_update', true );
											$default_last_upgrade = ( ! empty( $topic->post_modified_gmt ) && $topic->post_modified_gmt !== '0000-00-00 00:00:00' ) ? human_time_diff( strtotime( $topic->post_modified_gmt ) ) : '';
											$last_upgrade = ! empty( $last_upgrade ) ? human_time_diff( $last_upgrade ) : $default_last_upgrade;
										}
										echo $last_upgrade;
										?>
									</span>
								</span>

								<?php if ( is_user_logged_in() && count( $topic_dropdown_items ) > 0 ) { ?>
									<span class="fmwp-topic-top-actions">
										<span class="fmwp-topic-top-actions-dropdown" title="<?php esc_attr_e( 'More Actions', 'forumwp' ) ?>">
											<i class="fas fa-angle-down"></i>
										</span>
									</span>

									<?php
									//Topic dropdown actions
									FMWP()->frontend()->shortcodes()->dropdown_menu( '.fmwp-topic-top-actions-dropdown', 'click', $topic_dropdown_items );
								} ?>
							</div>
							<div class="fmwp-topic-data-content">
								<?php echo $topic->post_content; ?>
							</div>
						</div>
					</div>
					<div class="fmwp-topic-base-footer">
						<div class="fmwp-topic-left-panel">
							<?php if ( is_user_logged_in() ) {
								if ( FMWP()->user()->can_reply( $topic->ID ) ) { ?>
									<input type="button" class="fmwp-write-reply" title="<?php esc_attr_e( 'Reply', 'forumwp' ) ?>" value="<?php esc_attr_e( 'Reply', 'forumwp' ) ?>" />
									<span class="fmwp-topic-closed-notice"><?php _e( 'This topic is closed to new replies', 'forumwp' ); ?></span>
								<?php } else {
									echo apply_filters( 'fmwp_reply_disabled_reply_text', '<span class="fmwp-topic-closed-notice">' . __( 'This topic is closed to new replies', 'forumwp' ) . '</span>', $topic->ID );
								}
							} else {
								if ( $topic->post_status == 'publish' ) { ?>
									<input type="button" class="<?php echo $unlogged_class ?>" title="<?php esc_attr_e( 'Reply', 'forumwp' ) ?>" value="<?php esc_attr_e( 'Reply', 'forumwp' ) ?>" data-fmwp_popup_title="<?php esc_attr_e( 'Login to reply to this topic', 'forumwp' ) ?>" />
								<?php }
							} ?>

							<span class="fmwp-topic-sort-wrapper fmwp-responsive fmwp-ui-xs<?php if ( $replies_count < 2 ) {?> fmwp-topic-hidden-sort<?php } ?>">
								<label>
									<span><?php _e( 'Sort:', 'forumwp' ) ?>&nbsp;</span>
									<select class="fmwp-topic-sort">
										<?php foreach ( FMWP()->common()->reply()->sort_by as $key => $title ) { ?>
											<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $fmwp_topic['order'], $key ) ?>><?php echo $title ?></option>
										<?php } ?>
									</select>
								</label>
							</span>
						</div>
						<div class="fmwp-topic-right-panel">

							<?php do_action( 'fmwp_topic_footer', $fmwp_topic['id'], $fmwp_topic ); ?>

							<span class="fmwp-topic-sort-wrapper fmwp-responsive fmwp-ui-s fmwp-ui-m fmwp-ui-l fmwp-ui-xl<?php if ( $replies_count < 2 ) {?> fmwp-topic-hidden-sort<?php } ?>">
								<label>
									<span><?php _e( 'Sort:', 'forumwp' ) ?>&nbsp;</span>
									<select class="fmwp-topic-sort">
										<?php foreach ( FMWP()->common()->reply()->sort_by as $key => $title ) { ?>
											<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $fmwp_topic['order'], $key ) ?>><?php echo $title ?></option>
										<?php } ?>
									</select>
								</label>
							</span>
						</div>
					</div>
				</div>

				<div class="clear"></div>
				<div class="fmwp-topic-wrapper" data-fmwp_topic_id="<?php echo esc_attr( $fmwp_topic['id'] ) ?>"
					 data-order="<?php echo esc_attr( $fmwp_topic['order'] ) ?>">
				</div>
			</div>

			<div class="fmwp-topic-footer">
				<?php if ( is_user_logged_in() ) {
					if ( FMWP()->user()->can_reply( $topic->ID ) ) { ?>
						<input type="button" class="fmwp-write-reply" title="<?php esc_attr_e( 'Reply', 'forumwp' ) ?>" value="<?php esc_attr_e( 'Reply', 'forumwp' ) ?>" />
						<span class="fmwp-topic-closed-notice"><?php _e( 'This topic is closed to new replies', 'forumwp' ); ?></span>
					<?php } else {
						echo apply_filters( 'fmwp_reply_disabled_reply_text', '<span class="fmwp-topic-closed-notice">' . __( 'This topic is closed to new replies', 'forumwp' ) . '</span>', $topic->ID );
					}
				} else {
					if ( $topic->post_status == 'publish' ) { ?>
						<input type="button" class="<?php echo $unlogged_class ?>" title="<?php esc_attr_e( 'Reply', 'forumwp' ) ?>" value="<?php esc_attr_e( 'Reply', 'forumwp' ) ?>" data-fmwp_popup_title="<?php esc_attr_e( 'Login to reply to this topic', 'forumwp' ) ?>" />
					<?php }
				} ?>
			</div>
		<?php } ?>
		<div class="clear"></div>
	</div>

	<div class="clear"></div>

	<?php
	//Reply dropdown actions
	FMWP()->frontend()->shortcodes()->dropdown_menu( '.fmwp-reply-top-actions-dropdown', 'click' );

	wp_reset_postdata();

}