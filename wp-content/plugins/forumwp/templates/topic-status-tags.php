<?php if ( ! defined( 'ABSPATH' ) ) exit;


foreach ( FMWP()->common()->topic()->status_tags() as $class => $label ) { ?>

	<span class="fmwp-topic-tag fmwp-topic-status-tag fmwp-topic-tag-<?php echo esc_attr( $class ) ?>">

		<?php echo $label ?>

	</span>

<?php }