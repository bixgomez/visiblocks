<?php
/**
 * VisiBlocks settings accessors.
 *
 * Data-layer helpers for reading plugin settings. Always loaded (not admin-only)
 * so feature modules can consult settings in the editor and on the front end.
 *
 * @package VisiBlocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default settings values.
 *
 * The single source of truth for setting keys and their defaults. Every new
 * toggle should add its key/default here.
 *
 * VisiBlocks ships with every enhancement on, so a fresh install immediately
 * shows what the plugin does. Editors turn off whatever they do not want.
 *
 * @return array
 */
function visiblocks_default_settings() {
	return array(
		'enabled'               => true,     // Master switch.
		'view_same_tab'         => true,     // Same-tab "View" button in the editor header.
		'reposition_snackbar'   => true,     // Move the "saved" notice up near the Save button.
		'adjustable_sidebar'    => true,     // Wider editor sidebar with an editor-adjustable width.
		'block_outlines'        => 'always', // Block boundary outlines: 'off' | 'hover' | 'always'.
		'add_block_links'       => true,     // Persistent "ghost +" hint between top-level blocks.
		'remove_block_button'   => true,     // "Minus" button beside the empty-block inserter.
		'edge_block_inserters'  => true,     // Real "+" buttons above the first and below the last block.
		'toolbar_delete_button' => true,     // Delete control in the block toolbar, out of the overflow menu.
	);
}

/**
 * Get all settings, merged over defaults.
 *
 * @return array
 */
function visiblocks_get_settings() {
	$stored = get_option( VISIBLOCKS_OPTION, array() );

	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	return wp_parse_args( $stored, visiblocks_default_settings() );
}

/**
 * Get a single setting value.
 *
 * @param string $key Setting key.
 * @return mixed Setting value, or null if the key is unknown.
 */
function visiblocks_get_setting( $key ) {
	$settings = visiblocks_get_settings();

	return array_key_exists( $key, $settings ) ? $settings[ $key ] : null;
}

/**
 * Whether VisiBlocks's enhancements are globally enabled (master switch).
 *
 * Feature modules should check this before doing any work.
 *
 * @return bool
 */
function visiblocks_is_enabled() {
	return (bool) visiblocks_get_setting( 'enabled' );
}
