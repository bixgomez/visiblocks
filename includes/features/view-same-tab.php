<?php
/**
 * Feature: View in same tab.
 *
 * Adds a "View" button to the block editor header that opens the post in the
 * same tab, next to the stock button (which keeps opening a new tab). Also makes
 * the "saved" snackbar's View link open in the same tab.
 *
 * @package VisiBlocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the same-tab view script in the block editor, if enabled.
 *
 * @return void
 */
function visiblocks_view_same_tab_enqueue() {
	if ( ! visiblocks_is_enabled() || ! visiblocks_get_setting( 'view_same_tab' ) ) {
		return;
	}

	visiblocks_enqueue_script(
		'visiblocks-view-same-tab',
		'assets/js/view-same-tab.js',
		array( 'wp-dom-ready', 'wp-i18n' ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'visiblocks_view_same_tab_enqueue' );
