<?php
/**
 * VisiBlocks uninstall routine.
 *
 * Runs when the plugin is DELETED from the Plugins screen, not on deactivation,
 * so settings survive an ordinary deactivate/reactivate cycle.
 *
 * VisiBlocks stores everything in one option and creates no tables, no custom
 * post types, and no transients, so removing that option is the whole job.
 *
 * @package VisiBlocks
 */

// Exit unless WordPress is genuinely uninstalling this plugin.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * The option name is repeated here rather than read from VISIBLOCKS_OPTION.
 * WordPress loads uninstall.php on its own, without the main plugin file, so
 * the plugin's constants do not exist at this point. Keep this string in sync
 * with the VISIBLOCKS_OPTION definition in visiblocks.php.
 */
$visiblocks_option = 'visiblocks_settings';

if ( is_multisite() ) {
	// The option is per-site, so clear it on every site in the network.
	$visiblocks_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);

	foreach ( $visiblocks_site_ids as $visiblocks_site_id ) {
		switch_to_blog( $visiblocks_site_id );
		delete_option( $visiblocks_option );
		restore_current_blog();
	}
} else {
	delete_option( $visiblocks_option );
}
