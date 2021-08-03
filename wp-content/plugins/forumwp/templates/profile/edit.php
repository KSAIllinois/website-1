<?php if ( ! defined( 'ABSPATH' ) ) exit;

$user_slug = get_query_var( 'fmwp_user' );

$user_id = FMWP()->user()->get_user_by_permalink( urldecode( $user_slug ) );
if ( empty( $user_id ) ) {

	_e( 'Wrong user in query', 'forumwp' );

} else {
	$user = get_userdata( $user_id );

	if ( empty( $user ) || is_wp_error( $user ) ) {

		_e( 'Wrong user in query', 'forumwp' );

	} else {
		$user_login = ! empty( $_POST['user_login'] ) ? sanitize_user( $_POST['user_login'] ) : $user->user_login;
		$user_email = ! empty( $_POST['user_email'] ) ? sanitize_email( $_POST['user_email'] ) : $user->user_email;
		$first_name = ! empty( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : $user->first_name;
		$last_name = ! empty( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : $user->last_name;

		$url = ! empty( $_POST['user_url'] ) ? filter_var( $_POST['user_url'], FILTER_VALIDATE_URL ) : $user->user_url;
		$url = $url === false ? $user->user_url : $url;

		$user_description = ! empty( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : $user->description;

		FMWP()->get_template_part( 'profile/edit-form', [
			'user_id'       => $user->ID,
			'user_login'    => $user_login,
			'user_email'    => $user_email,
			'first_name'    => $first_name,
			'last_name'     => $last_name,
			'user_url'      => $url,
			'description'   => $user_description,
		] );
	}
}