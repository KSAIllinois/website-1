<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="fmwp-new-forum-wrapper" class="fmwp">
	<?php $new_forum = FMWP()->frontend()->forms( [
		'id'    => 'fmwp-create-forum',
	] );

	$fields = [
		[
			'type'      => 'text',
			'label'     =>  __( 'Name', 'forumwp' ),
			'id'        => 'title',
			'required'  => true,
		],
		[
			'type'      => 'textarea',
			'label'     =>  __( 'Description', 'forumwp' ),
			'id'        => 'content',
			'required'  => true,
		],
		[
			'type'      => 'select',
			'label'     =>  __( 'Visibility', 'forumwp' ),
			'id'        => 'visibility',
			'options'   => FMWP()->common()->forum()->visibilities,
		],
	];

	if ( FMWP()->options()->get( 'forum_categories' ) ) {
		$fields[] = [
			'type'  => 'text',
			'label' => __( 'Categories', 'forumwp' ),
			'id'    => 'categories',
		];
	}

	$fields = apply_filters( 'fmwp_new_forum_fields', $fields, $new_forum );

	$new_forum->set_data( [
		'id'        => 'fmwp-create-forum',
		'class'     => '',
		'prefix_id' => 'fmwp-forum',
		'fields'    => $fields,
		'hiddens'   => [
			'fmwp-action'   => 'create-forum',
			'nonce'         => wp_create_nonce( 'fmwp-create-forum' ),
		],
		'buttons'   => [
			'create'    => [
				'type'  => 'submit',
				'label' => __( 'Create', 'forumwp' ),
			],
		],
	] );

	$new_forum->display(); ?>
</div>