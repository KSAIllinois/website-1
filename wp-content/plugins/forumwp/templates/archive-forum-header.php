<?php if ( ! defined( 'ABSPATH' ) ) exit;

$stats_cols = [
	'topics'    => __( 'Topics', 'forumwp' ),
	'replies'   => __( 'Replies', 'forumwp' ),
	'updated'   => __( 'Updated', 'forumwp' ),
];
$stats_cols = apply_filters( 'fmwp_forums_header_columns', $stats_cols, $fmwp_archive_forum_header ); ?>

<div class="fmwp-forums-wrapper-heading">
	<span class="fmwp-forum-head-line fmwp-forum-col-forum">
		<?php echo apply_filters( 'fmwp_forums_header_forum_column_name', __( 'Forum', 'forumwp' ), $fmwp_archive_forum_header ); ?>
	</span>
	<span class="fmwp-forum-head-line">
		<?php foreach ( $stats_cols as $key => $title ) { ?>
			<span class="fmwp-forum-col-<?php echo esc_attr( $key ) ?>"><?php echo $title ?></span>
		<?php } ?>
	</span>
</div>