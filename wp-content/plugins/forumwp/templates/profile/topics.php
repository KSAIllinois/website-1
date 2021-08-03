<?php if ( ! defined( 'ABSPATH' ) ) exit;

FMWP()->get_template_part( 'js/topics-list', [
	'show_forum'    => ! empty( FMWP()->options()->get( 'show_forum' ) ) ? true : false,
] );

FMWP()->get_template_part( 'archive-topic-header' );

$classes = apply_filters( 'fmwp_topics_wrapper_classes', '' ); ?>

<div class="fmwp-topics-wrapper<?php echo esc_attr( $classes ) ?>"></div>