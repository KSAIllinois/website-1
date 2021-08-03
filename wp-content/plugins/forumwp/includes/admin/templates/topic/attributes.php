<?php if ( ! defined( 'ABSPATH' ) ) exit;

global $post_id, $post;

$forums = get_posts( [
	'post_type'         => 'fmwp_forum',
	'post_status'       => [ 'any', 'trash' ],
	'posts_per_page'    => -1,
	'fields'            => [ 'ID', 'post_title' ],
	'meta_query'        => [
        'relation'  => 'OR',
		[
			'key'       => 'fmwp_locked',
			'compare'   => 'NOT EXISTS',
		],
		[
			'key'       => 'fmwp_locked',
			'value'     => true,
			'compare'   => '!=',
		]
	],
	'orderby'           => 'post_title',
	'order'             => 'ASC',
] );

$forum_options = [];
$forums = ( ! empty( $forums ) && ! is_wp_error( $forums ) ) ? $forums : [];
foreach ( $forums as $forum ) {
	$forum_options[ $forum->ID ] = $forum->post_title;
}

$types = [];
foreach ( FMWP()->common()->topic()->types as $value => $type ) {
	$types[ $value ] = $type['title'];
}


$fields = [
	[
		'id'        => 'status_changed',
		'type'      => 'hidden',
		'value'     => '',
		'data'      => [
			'post-status'   => $post->post_status,
		],
	],
	[
		'id'        => 'fmwp_type',
		'type'      => 'select',
		'label'     => __( 'Type', 'forumwp' ),
		'options'   => $types,
		'value'     => get_post_meta( $post->ID, 'fmwp_type', true ),
	],
	[
		'id'        => 'fmwp_locked',
		'type'      => 'select',
		'label'     => __( 'Is Locked?', 'forumwp' ),
		'options'   => [
			'0' => __( 'No', 'forumwp' ),
			'1' => __( 'Yes', 'forumwp' ),
		],
		'value'     => get_post_meta( $post->ID, 'fmwp_locked', true ),
	],
    [
		'id'        => 'fmwp_spam',
		'type'      => 'select',
		'label'     => __( 'Is Spam?', 'forumwp' ),
		'options'   => [
			'0' => __( 'No', 'forumwp' ),
			'1' => __( 'Yes', 'forumwp' ),
		],
		'value'     => get_post_meta( $post->ID, 'fmwp_spam', true ),
	],
	[
		'id'        => 'fmwp_forum',
		'type'      => 'select',
		'label'     => __( 'Forum', 'forumwp' ),
		'options'   => $forum_options,
		'value'     => get_post_meta( $post->ID, 'fmwp_forum', true ),
	],
];

$fields = apply_filters( 'fmwp_topic_admin_settings_fields', $fields, $post->ID ); ?>

<div class="fmwp-admin-metabox fmwp">

	<?php FMWP()->admin()->forms( [
		'class'     => 'fmwp-topic-attributes fmwp-top-label',
		'prefix_id' => 'fmwp_metadata',
		'fields'    => $fields,
	] )->display(); ?>

	<div class="clear"></div>
</div>