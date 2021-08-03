<?php if ( ! defined( 'ABSPATH' ) ) exit;

$forum_id = $fmwp_forum['id'];
$forum = get_post( $forum_id );

if ( post_password_required( $forum ) ) {

	echo get_the_password_form( $forum );

} else {
	$unlogged_class = FMWP()->frontend()->shortcodes()->unlogged_class();

	$show_header = ( isset( $fmwp_forum['show_header'] ) && $fmwp_forum['show_header'] == 'yes' ) ? true : false;

	$visibility = get_post_meta( $forum_id, 'fmwp_visibility', true );

	setup_postdata( $forum_id );

	ob_start();

	if ( ! FMWP()->common()->forum()->is_locked( $forum_id ) ) {
		if ( is_user_logged_in() ) {
			if ( FMWP()->user()->can_create_topic( $forum_id ) ) {?>
				<input type="button" class="fmwp-create-topic" title="<?php esc_attr_e( 'New topic', 'forumwp' ) ?>" value="<?php esc_attr_e( 'New topic', 'forumwp' ) ?>" data-fmwp_forum_id="<?php echo esc_attr( $fmwp_forum['id'] ) ?>" />
			<?php } else {
				echo apply_filters( 'fmwp_create_topic_disabled_text', "&nbsp;", $forum_id );
			}
		} else { ?>
			<input type="button" class="<?php echo $unlogged_class ?>" title="<?php esc_attr_e( 'New topic', 'forumwp' ) ?>" value="<?php esc_attr_e( 'New topic', 'forumwp' ) ?>" data-fmwp_popup_title="<?php esc_attr_e( 'Login to create a topic', 'forumwp' ) ?>" />
		<?php }
	}

	$new_topic_button = ob_get_clean();

	do_action( 'fmwp_before_individual_forum' );
	?>

	<div class="fmwp fmwp-forum-wrapper<?php if ( FMWP()->common()->forum()->is_locked( $forum_id ) ) { ?> fmwp-forum-locked<?php } ?><?php if ( $forum->post_status == 'pending' ) { ?> fmwp-forum-pending<?php } ?><?php if ( ! empty( $unlogged_class ) ) { ?> fmwp-unlogged-data<?php } ?>">

		<?php if ( ! is_user_logged_in() && $visibility != 'public' ) {
			printf( __( 'Please <a href="javascript:void(0);" class="%s" title="Login to view" data-fmwp_popup_title="Login to view forum">login</a> to view this forum', 'forumwp' ), $unlogged_class );
		} else {

			if ( ! empty( $_GET['fmwp-msg'] ) ) {

				$msg = sanitize_key( $_GET['fmwp-msg'] );

				switch ( $msg ) {
					default:
						do_action( 'fmwp_forum_header_message', $forum, $msg );
						break;
				}

			} ?>

			<div class="fmwp-forum-head" data-fmwp_forum_id="<?php echo esc_attr( $fmwp_forum['id'] ) ?>">
				<?php if ( ! FMWP()->is_forum_page() || $show_header ) {
					FMWP()->get_template_part( 'single-forum-info', [
						'id'            => $forum_id,
						'show_header'   => $show_header,
					] );
				} ?>

				<div class="fmwp-forum-nav-bar fmwp-responsive fmwp-ui-m fmwp-ui-l fmwp-ui-xl">

					<?php echo $new_topic_button; ?>

					<div class="fmwp-forum-nav-bar-line">
					<span class="fmwp-forum-sort-wrapper">
						<label>
							<span><?php _e( 'Sort:', 'forumwp' ) ?>&nbsp;</span>
							<select class="fmwp-forum-sort">
								<?php foreach ( FMWP()->common()->topic()->sort_by as $key => $title ) { ?>
									<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $fmwp_forum['order'], $key ) ?>><?php echo $title ?></option>
								<?php } ?>
							</select>
						</label>
					</span>

						<?php do_action( 'fmwp_single_forum_after_first_nav_line', $forum, $fmwp_forum ); ?>

						<span class="fmwp-forum-search-bar">
						<label>
							<input type="text" value="" class="fmwp-forum-search-line" placeholder="<?php esc_attr_e( 'Search forum topics', 'forumwp' ) ?>" />
						</label>
						<input type="button" class="fmwp-search-topic" title="<?php esc_attr_e( 'Search in Forum', 'forumwp' ) ?>" value="<?php esc_attr_e( 'Search', 'forumwp' ) ?>" />
					</span>
					</div>
				</div>

				<div class="fmwp-forum-nav-bar-mobile fmwp-responsive fmwp-ui-s fmwp-ui-xs">
					<div class="fmwp-forum-nav-bar-line-mobile">

						<?php echo $new_topic_button; ?>

						<div class="fmwp-forum-nav-bar-subline-mobile">
						<span class="fmwp-forum-sort-wrapper">
							<label>
								<span><?php _e( 'Sort:', 'forumwp' ) ?>&nbsp;</span>
								<select class="fmwp-forum-sort">
									<?php foreach ( FMWP()->common()->topic()->sort_by as $key => $title ) { ?>
										<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $fmwp_forum['order'], $key ) ?>><?php echo $title ?></option>
									<?php } ?>
								</select>
							</label>
						</span>

							<?php do_action( 'fmwp_single_forum_after_first_nav_line', $forum, $fmwp_forum ); ?>

							<span class="fmwp-search-toggle" title="<?php esc_attr_e( 'Search', 'forumwp' ); ?>">
							<i class="fas fa-search"></i>
						</span>
						</div>
					</div>
					<div class="fmwp-forum-nav-bar-line-mobile fmwp-search-wrapper">
					<span class="fmwp-forum-search-bar">
						<label>
							<input type="text" value="" class="fmwp-forum-search-line" placeholder="<?php esc_attr_e( 'Search forum topics', 'forumwp' ) ?>" />
						</label>
						<input type="button" class="fmwp-search-topic" title="<?php esc_attr_e( 'Search in Forum', 'forumwp' ) ?>" value="<?php esc_attr_e( 'Search', 'forumwp' ) ?>" />
					</span>
					</div>
				</div>

			</div>

			<div class="fmwp-forum-content">
				<?php FMWP()->get_template_part( 'archive-topic-header' );

				$classes = apply_filters( 'fmwp_topics_wrapper_classes', '' ); ?>

				<div class="fmwp-topics-wrapper<?php echo esc_attr( $classes ) ?>"
					 data-fmwp_forum_id="<?php echo esc_attr( $fmwp_forum['id'] ) ?>"
					 data-order="<?php echo esc_attr( $fmwp_forum['order'] ) ?>">
				</div>
			</div>

			<div class="fmwp-forum-footer">
				<?php echo $new_topic_button; ?>
			</div>

		<?php } ?>
	</div>

	<div class="clear"></div>

	<?php do_action( 'fmwp_after_individual_forum_wrapper', $forum_id );

	//Topics' dropdown actions
	FMWP()->frontend()->shortcodes()->dropdown_menu( '.fmwp-topic-actions-dropdown', 'click' );

	wp_reset_postdata();
}