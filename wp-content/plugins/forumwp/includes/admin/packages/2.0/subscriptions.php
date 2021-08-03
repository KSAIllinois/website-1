<?php if ( ! defined( 'ABSPATH' ) ) exit;

$ids = get_users( [
	'fields'        => 'ids',
	'meta_key'      => 'fmwp_forum_subscriptions',
	'meta_compare'  => 'EXISTS',
] );

if ( ! empty( $ids ) ) {
	foreach ( $ids as $id ) {
		$forums = get_user_meta( $id, 'fmwp_forum_subscriptions', true );
		if ( ! empty( $forums ) ) {
			foreach ( $forums as $forum_id ) {
				$token = md5( $id . '-' . $forum_id . '-' . time() );
				update_user_meta( $id, 'fmwp_forum_subscription_token_' . $forum_id, $token );
			}
		}
	}
}

$ids = get_users( [
	'fields'        => 'ids',
	'meta_key'      => 'fmwp_topic_subscriptions',
	'meta_compare'  => 'EXISTS',
] );

if ( ! empty( $ids ) ) {
	foreach ( $ids as $id ) {
		$topics = get_user_meta( $id, 'fmwp_topic_subscriptions', true );
		if ( ! empty( $topics ) ) {
			foreach ( $topics as $topic_id ) {
				$token = md5( $id . '-' . $topic_id . '-' . time() );
				update_user_meta( $id, 'fmwp_topic_subscription_token_' . $topic_id, $token );
			}
		}
	}
}

FMWP()->common()->rewrite()->reset_rules();