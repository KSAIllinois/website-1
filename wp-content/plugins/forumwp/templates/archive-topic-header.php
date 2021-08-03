<?php if ( ! defined( 'ABSPATH' ) ) exit;

$stats_cols = [
	'people'    => __( 'People', 'forumwp' ),
	'replies'   => __( 'Replies', 'forumwp' ),
	'views'     => __( 'Views', 'forumwp' ),
	'updated'   => __( 'Updated', 'forumwp' ),
];
$stats_cols = apply_filters( 'fmwp_topics_header_columns', $stats_cols );

$classes = apply_filters( 'fmwp_topics_header_classes', '' ); ?>

<div class="fmwp-topics-wrapper-heading<?php echo esc_attr( $classes ) ?>">
	<span class="fmwp-topic-head-line fmwp-topic-col-topic">
		<?php _e( 'Topic', 'forumwp' ) ?>
	</span>
	<span class="fmwp-topic-head-line">
		<?php foreach ( $stats_cols as $key => $title ) { ?>
			<span class="fmwp-topic-col-<?php echo esc_attr( $key ) ?>"><?php echo $title ?></span>
		<?php } ?>
	</span>
</div>