<?php
/**
 *	Uninstall
 *
 *	Deletes all the plugin data
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

function dd_recaptcha_uninstall_plugin() {
	global $wpdb;

	$post_id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_type = 'ddr-post' LIMIT 1" );

	if ( $post_id ) {
		// There may have too many post meta. delete them first in one query.
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE post_id = %d", $post_id ) );
		
		wp_delete_post( $post_id, true );
	}
}

dd_recaptcha_uninstall_plugin();

