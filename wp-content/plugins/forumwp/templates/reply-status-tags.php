<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>


<span class="fmwp-reply-tags-wrapper">

	<?php foreach ( FMWP()->common()->reply()->status_tags() as $class => $data ) { ?>

		<span class="fmwp-reply-tag fmwp-reply-tag-<?php echo esc_attr( $class ) ?> fmwp-tip-n"
			  <?php if ( ! empty( $data['label'] ) ) { ?>title="<?php echo esc_attr( $data['label'] ) ?>"<?php } ?>>

			<?php echo $data['title'] ?>

		</span>

	<?php } ?>

</span>