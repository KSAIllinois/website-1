<div class="top-bar">
	<div class="top-bar-wrapper">
		<?php $description = get_bloginfo( 'description', 'display' );

		if ( $description || is_customize_preview() ) : ?>
			<p class="site-description"><?php echo $description; /* WPCS: xss ok. */ ?></p>
		<?php endif; ?>

		<?php karuna_social_menu(); ?>
		<?php echo '<script>jQuery(".top-bar .jetpack-social-navigation .icon").hide(); jQuery(".top-bar .jetpack-social-navigation .screen-reader-text").removeClass()</script>' ?>
	</div><!-- .top-bar-wrapper -->
</div><!-- .top-bar -->