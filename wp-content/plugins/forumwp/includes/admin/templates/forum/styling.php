<?php if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $post_id;

$templates = [
	''                          => __( 'Global settings', 'forumwp' ),
	'fmwp_individual_default'   => __( 'Default template', 'forumwp' ),
];

$custom_templates = FMWP()->common()->forum()->get_templates( $post );

$fields = [];
if ( count( $custom_templates ) ) {
	$fields[] = [
		'id'        => 'fmwp_template',
		'type'      => 'select',
		'label'     => __( 'Template', 'forumwp' ),
		'value'     => get_post_meta( $post_id, 'fmwp_template', true ),
		'options'   => array_merge( $templates, $custom_templates ),
	];
} else {
	$fields[] = [
		'id'    => 'fmwp_template',
		'type'  => 'hidden',
		'value' => '',
	];
}

$fields = array_merge( $fields, [
	[
		'id'    => 'fmwp_icon',
		'type'  => 'icon_select',
		'label' => __( 'Icon', 'forumwp' ),
		'value' => get_post_meta( $post_id, 'fmwp_icon', true ),
	],
	[
		'id'    => 'fmwp_icon_bgcolor',
		'type'  => 'color',
		'label' => __( 'Icon Background Color', 'forumwp' ),
		'value' => get_post_meta( $post_id, 'fmwp_icon_bgcolor', true ),
	],
	[
		'id'    => 'fmwp_icon_color',
		'type'  => 'color',
		'label' => __( 'Icon Color', 'forumwp' ),
		'value' => get_post_meta( $post_id, 'fmwp_icon_color', true ),
	],
] );
?>

<div class="fmwp-admin-metabox fmwp">

	<?php FMWP()->admin()->forms( [
		'class'     => 'fmwp-forum-styling fmwp-top-label',
		'prefix_id' => 'fmwp_metadata',
		'fields'    => $fields
	] )->display(); ?>

	<a href="https://docs.forumwpplugin.com/article/1475-creating-a-new-forum-in-wp-admin#forum-styling" target="_blank">
		<?php _e( 'Learn more about forum styling', 'forumwp' ) ?>
	</a>

	<div class="clear"></div>
</div>