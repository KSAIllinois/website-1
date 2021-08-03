<?php if ( ! defined( 'ABSPATH' ) ) exit;

$user_id = isset( $fmwp_profile_edit_form['user_id'] ) ? $fmwp_profile_edit_form['user_id'] : '{{{data.id}}}';
$user_login = isset( $fmwp_profile_edit_form['user_login'] ) ? $fmwp_profile_edit_form['user_login'] : '{{{data.login}}}';
$user_email = isset( $fmwp_profile_edit_form['user_email'] ) ? $fmwp_profile_edit_form['user_email'] : '{{{data.email}}}';
$first_name = isset( $fmwp_profile_edit_form['first_name'] ) ? $fmwp_profile_edit_form['first_name'] : '{{{data.first_name}}}';
$last_name = isset( $fmwp_profile_edit_form['last_name'] ) ? $fmwp_profile_edit_form['last_name'] : '{{{data.last_name}}}';
$user_url = isset( $fmwp_profile_edit_form['user_url'] ) ? $fmwp_profile_edit_form['user_url'] : '{{{data.url}}}';
$description = isset( $fmwp_profile_edit_form['description'] ) ? $fmwp_profile_edit_form['description'] : '{{{data.description}}}'; ?>


<div id="fmwp-edit-profile-form-wrapper" class="fmwp">

	<?php $edit_profile = FMWP()->frontend()->forms( [
		'id'    => 'fmwp-edit-profile',
	] );

	$fields = [
		[
			'type'      => 'text',
			'label'     =>  __( 'Username', 'forumwp' ),
			'id'        => 'user_login',
			'value'     => $user_login,
			'disabled'  => true,
			'readonly'  => true,
		],
		[
			'type'      => 'email',
			'label'     =>  __( 'Email', 'forumwp' ),
			'id'        => 'user_email',
			'required'  => true,
			'value'     => $user_email,
		],
		[
			'type'  => 'text',
			'label' =>  __( 'First Name', 'forumwp' ),
			'id'    => 'first_name',
			'value' => $first_name,
		],
		[
			'type'  => 'text',
			'label' =>  __( 'Last Name', 'forumwp' ),
			'id'    => 'last_name',
			'value' => $last_name,
		],
		[
			'type'  => 'url',
			'label' =>  __( 'Website', 'forumwp' ),
			'id'    => 'user_url',
			'value' => $user_url,
		],
		[
			'type'  => 'textarea',
			'label' =>  __( 'Description', 'forumwp' ),
			'id'    => 'description',
			'value' => $description,
		],
	];

	$edit_profile->set_data( [
		'id'        => 'fmwp-edit-profile',
		'class'     => '',
		'prefix_id' => '',
		'fields'    => $fields,
		'hiddens'   => [
			'fmwp-action'   => 'edit-profile',
			'user_id'       => ! empty( $user_id ) ? $user_id : '',
			'nonce'         => wp_create_nonce( 'fmwp-edit-profile' ),
		],
		'buttons'   => [
			'update'    => [
				'type'  => 'submit',
				'label' => __( 'Update', 'forumwp' ),
			],
		],
	] );

	$edit_profile->display(); ?>
</div>