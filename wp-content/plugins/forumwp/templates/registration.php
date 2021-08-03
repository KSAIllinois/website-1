<?php if ( ! defined( 'ABSPATH' ) ) exit;

$user_login = ! empty( $_POST['user_login'] ) ? sanitize_user( $_POST['user_login'] ) : '';
$user_email = ! empty( $_POST['user_email'] ) ? sanitize_email( $_POST['user_email'] ) : '';
$first_name = ! empty( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
$last_name = ! empty( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : ''; ?>

<div id="fmwp-register-form-wrapper" class="fmwp">

	<?php $registration = FMWP()->frontend()->forms( [
		'id'    => 'fmwp-register',
	] );

	$fields = [
		[
			'type'      => 'text',
			'label'     =>  __( 'Username', 'forumwp' ),
			'id'        => 'user_login',
			'required'  => true,
			'value'     => $user_login,
		],
		[
			'type'      => 'email',
			'label'     =>  __( 'Email', 'forumwp' ),
			'id'        => 'user_email',
			'required'  => true,
			'value'     => $user_email,
		],
	];

	if ( 'hide' !== $fmwp_registration['first_name'] ) {
		$fields[] = [
			'type'  => 'text',
			'label' =>  __( 'First Name', 'forumwp' ),
			'id'    => 'first_name',
			'value' => $first_name,
		];
	}

	if ( 'hide' !== $fmwp_registration['last_name'] ) {
		$fields[] = [
			'type'  => 'text',
			'label' =>  __( 'Last Name', 'forumwp' ),
			'id'    => 'last_name',
			'value' => $last_name,
		];
	}

	$fields = array_merge( $fields, [
		[
			'type'      => 'password',
			'label'     => __( 'Password', 'forumwp' ),
			'id'        => 'user_pass',
			'required'  => true,
		],
		[
			'type'      => 'password',
			'label'     => __( 'Confirm Password', 'forumwp' ),
			'id'        => 'user_pass2',
			'required'  => true,
		],
	] );

	$registration->set_data( [
		'id'        => 'fmwp-register',
		'class'     => '',
		'prefix_id' => '',
		'fields'    => $fields,
		'hiddens'   => [
			'fmwp-action'   => 'registration',
			'redirect_to'   => ! empty( $fmwp_registration['redirect'] ) ? $fmwp_registration['redirect'] : '',
			'nonce'         => wp_create_nonce( 'fmwp-registration' ),
		],
		'buttons'   => [
			'signup'    => [
				'type'  => 'submit',
				'label' => __( 'Sign Up', 'forumwp' ),
			],
		],
	] );

	$registration->display(); ?>
</div>