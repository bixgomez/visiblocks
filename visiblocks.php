<?php
/**
 * Plugin Name:       VisiBlocks
 * Plugin URI:        https://github.com/bixgomez/visiblocks
 * Description:       Makes the WordPress block editor's hidden editing affordances visible and discoverable.
 * Version:           0.2.2
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Richard Gilbert
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       visiblocks
 *
 * @package VisiBlocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 */
define( 'VISIBLOCKS_VERSION', '0.2.2' );

/**
 * Absolute path to the main plugin file.
 */
define( 'VISIBLOCKS_FILE', __FILE__ );

/**
 * Absolute path to the plugin directory (with trailing slash).
 */
define( 'VISIBLOCKS_DIR', plugin_dir_path( __FILE__ ) );

/**
 * URL to the plugin directory (with trailing slash).
 */
define( 'VISIBLOCKS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Option name where all VisiBlocks settings are stored (single array option).
 */
define( 'VISIBLOCKS_OPTION', 'visiblocks_settings' );

// Activation routine, registered outside the bootstrap because activation runs
// before the plugin is loaded normally.
require_once VISIBLOCKS_DIR . 'includes/activation.php';
register_activation_hook( __FILE__, 'visiblocks_activate' );

/**
 * Bootstrap the plugin.
 *
 * Feature modules are wired up here as they are built (see the friction-point
 * inventory / roadmap).
 *
 * @return void
 */
function visiblocks_bootstrap() {
	// Settings accessors and asset helpers, always available.
	require_once VISIBLOCKS_DIR . 'includes/settings.php';
	require_once VISIBLOCKS_DIR . 'includes/assets.php';

	// Admin-only UI + editor features (the block editor runs in wp-admin).
	if ( is_admin() ) {
		require_once VISIBLOCKS_DIR . 'includes/admin/settings-page.php';
		require_once VISIBLOCKS_DIR . 'includes/features/view-same-tab.php';
		require_once VISIBLOCKS_DIR . 'includes/features/snackbar-position.php';
		require_once VISIBLOCKS_DIR . 'includes/features/adjustable-sidebar.php';
		require_once VISIBLOCKS_DIR . 'includes/features/block-outlines.php';
		require_once VISIBLOCKS_DIR . 'includes/features/add-block-links.php';
		require_once VISIBLOCKS_DIR . 'includes/features/remove-block-button.php';
		require_once VISIBLOCKS_DIR . 'includes/features/edge-block-inserters.php';
		require_once VISIBLOCKS_DIR . 'includes/features/toolbar-delete-button.php';
	}
}
add_action( 'plugins_loaded', 'visiblocks_bootstrap' );
