<?php
/**
 * Fires only when the user clicks "Delete" on the Plugins screen —
 * never on simple deactivation. This is the correct, WP-recommended
 * place to clean up options so uninstalling leaves no trace.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'rtp_settings' );

// Multisite: clean up per-site if network-activated.
if ( is_multisite() ) {
	$sites = get_sites( array( 'fields' => 'ids' ) );
	foreach ( $sites as $site_id ) {
		switch_to_blog( $site_id );
		delete_option( 'rtp_settings' );
		restore_current_blog();
	}
}
