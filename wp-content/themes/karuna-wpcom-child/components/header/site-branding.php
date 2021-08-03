<div class="sticky-wrapper">
	<div class="site-branding-wrapper">
		<style>
			.site-branding-upper-stats{flex:0 0 100%;text-align:center;padding:10px 0 0;color:rgb(35,72,154);font-size:13px;}
			.sticking .site-branding-upper-stats{display:none;}
			@media (min-width: 800px) {
				.site-branding-upper-stats{text-align:right;}
			}
		</style>
		<div class="site-branding-upper-stats">
			<b>방문자 수</b> — 오늘 <?php echo do_shortcode('[jp_get_total_visitors period="day"]'); ?>명 | 일주일 <?php echo do_shortcode('[jp_get_total_visitors period="week"]'); ?>명 | 총 <?php echo do_shortcode('[jp_get_total_visitors period="lifetime"]'); ?>명
		</div>
	</div>
	<div class="site-branding-wrapper">
		<div class="site-branding">
      <style>
				//.sticking .custom-logo {max-height: 40px;}
				@media (min-width: 768px) {
          .custom-logo {max-height: 90px;}
          .sticking .custom-logo {max-height: 70px;}
					.sticking .site-branding {padding: 0.5em 0;}
          .site-title {font-size: 1.9rem;}
				}
			</style>
			<?php karuna_the_custom_logo(); ?>
			<?php
			if ( is_front_page() && is_home() ) : ?>
				<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
			<?php else : ?>
				<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
			<?php
			endif; ?>
		</div><!-- .site-branding -->

		<?php get_template_part( 'components/navigation/navigation', 'top' ); ?>

		<?php
			if ( function_exists( 'karuna_woocommerce_header_cart' ) ) {
				karuna_woocommerce_header_cart();
			}
		?>
	</div><!-- .site-branding-wrapper -->
</div><!-- .sticky-wrapper -->

