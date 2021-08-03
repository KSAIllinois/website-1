<?php if ( ! defined( 'ABSPATH' ) ) exit;

$user_login = get_query_var( 'fmwp_user' );
if ( empty( $user_login ) && current_user_can( 'manage_options' ) ) {
	$user_id = get_current_user_id();
} else {
	$user_id = FMWP()->user()->get_user_by_permalink( $user_login );
}

$user = get_user_by( 'ID', $user_id );

$active_tab = get_query_var( 'fmwp_profiletab' );
$active_tab = empty( $active_tab ) ? 'topics' : $active_tab;

$menu_items = FMWP()->frontend()->profile()->get_profile_tabs( $user ); ?>

<div class="fmwp-profile-mobile fmwp-responsive fmwp-ui-xs">

	<div class="fmwp-profile-general-info">

		<div class="fmwp-profile-info-line">
			<div class="fmwp-profile-avatar">
				<a href="<?php echo FMWP()->user()->get_profile_link( $user->ID ) ?>">
					<?php echo FMWP()->user()->get_avatar( $user->ID, 'inline', 300 ) ?>
				</a>
			</div>
			<div class="fmwp-profile-info-wrapper">
					<span class="fmwp-profile-info-subline">
						<span class="fmwp-profile-username"><?php echo FMWP()->user()->display_name( $user ) ?></span>
						<span class="fmwp-profile-user-stats fmwp-profile-user-top-info">
							<span>
								<?php $topics = FMWP()->user()->get_topics_count( $user->ID );
								printf( _n( '%s topic', '%s topics', $topics, 'forumwp' ), $topics ); ?>
							</span>
							<span>
								<?php $replies = FMWP()->user()->get_replies_count( $user->ID );
								printf( _n( '%s reply', '%s replies', $replies, 'forumwp' ), $replies ); ?>
							</span>
						</span>
					</span>
				<span class="fmwp-profile-info-subline fmwp-profile-user-top-info">
						<?php printf( __( 'Joined: %s', 'forumwp' ), date_i18n( FMWP()->datetime_format( 'date' ), strtotime( $user->user_registered ) ) ) ?>
					</span>
			</div>
		</div>

		<?php if ( ! empty( $user->user_url ) ) { ?>
			<div class="fmwp-profile-info-line fmwp-profile-user-top-info">
				<?php _e( 'Website:', 'forumwp' ) ?>&nbsp;<a href="<?php echo esc_attr( $user->user_url ) ?>"><?php echo $user->user_url ?></a>
			</div>
		<?php } ?>

		<?php if ( ! empty( $user->description ) ) { ?>
			<div class="fmwp-profile-info-line fmwp-profile-user-top-info">
				<?php echo nl2br( $user->description ) ?>
			</div>
		<?php } ?>
	</div>


	<nav>
		<ul class="fmwp-profile-menu">
			<?php foreach ( $menu_items as $tab => $item ) { ?>
				<li class="<?php if ( $tab == $active_tab ) { ?>fmwp-active-tab<?php } ?>">
					<a href="<?php echo esc_attr( $item['link'] ) ?>" class="fmwp-profile-mobile-tab-link" data-tab="<?php echo esc_attr( $tab ) ?>" data-ajax="<?php echo (int) $item['ajax'] ?>" title="<?php echo esc_attr( $item['title'] ) ?>">
						<?php echo $item['title'] ?>
					</a>
				</li>
			<?php } ?>
			<li class="fmwp-profile-menu-indicator"></li>
		</ul>
	</nav>


	<div class="fmwp-profile-scroll-content">
		<?php foreach ( $menu_items as $tab => $item ) {

			$module = isset( $item['module'] ) ? $item['module'] : '';

			$active_subtab = false;
			$submenu_items = FMWP()->frontend()->profile()->get_profile_subtabs( $user, $tab );
			if ( ! empty( $submenu_items ) ) {
				if ( $tab == $active_tab ) {
					$slug_array = array_keys( $submenu_items );
					$active_subtab = get_query_var( 'fmwp_profilesubtab' );
					$active_subtab = empty( $active_subtab ) ? $slug_array[0] : $active_subtab;
				}
			} ?>

			<div class="fmwp-profile-tab-content fmwp-profile-<?php echo esc_attr( $tab ) ?>-content <?php echo ( $item['ajax'] && $tab != $active_tab ) ? 'fmwp-profile-blank-content' : '' ?>" data-tab="<?php echo esc_attr( $tab ) ?>" data-ajax="<?php echo (int) $item['ajax'] ?>" <?php if ( ! empty( $active_subtab ) ) { ?>data-active_subtab="<?php echo esc_attr( $active_subtab ) ?>"<?php } ?>>

				<?php if ( ! empty( $submenu_items ) ) { ?>

					<ul class="fmwp-profile-submenu">
						<?php foreach ( $submenu_items as $subtab => $sub_item ) { ?>
							<li class="<?php if ( $subtab == $active_subtab ) { ?>fmwp-active-tab<?php } ?>">
								<a href="<?php echo esc_attr( $sub_item['link'] ) ?>" class="fmwp-profile-load-content-link fmwp-profile-subtab-link" data-tab="<?php echo esc_attr( $subtab ) ?>" title="<?php echo esc_attr( $sub_item['title'] ) ?>">
									<?php echo $sub_item['title'] ?>
								</a>
							</li>
						<?php } ?>
					</ul>

					<?php foreach ( $submenu_items as $subtab => $sub_item ) { ?>

						<div class="fmwp-profile-subtab-content fmwp-profile-<?php echo esc_attr( $subtab ) ?>-<?php echo esc_attr( $tab ) ?>-wrapper">

							<?php FMWP()->get_template_part( 'profile/' . $tab . '/' . $subtab, [], $module );

							FMWP()->ajax_loader( 50 ); ?>

						</div>

					<?php }
				} else {
					FMWP()->get_template_part( 'profile/' . $tab, [], $module );
				}

				if ( empty( $submenu_items ) ) {
					FMWP()->ajax_loader( 50 );
				} ?>
			</div>
		<?php } ?>
	</div>
</div>