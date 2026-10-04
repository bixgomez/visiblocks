<?php
/**
 * Feature: Reposition the save snackbar.
 *
 * Moves the block editor's "saved" snackbar notice from the far bottom-left
 * corner up to the top-right, near the Save button.
 *
 * @package VisiBlocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the snackbar-position stylesheet in the block editor, if enabled.
 *
 * @return void
 */
function visiblocks_snackbar_position_enqueue() {
	if ( ! visiblocks_is_enabled() || ! visiblocks_get_setting( 'reposition_snackbar' ) ) {
		return;
	}

	visiblocks_enqueue_style( 'visiblocks-snackbar-position', 'assets/css/snackbar-position.css' );
}
add_action( 'enqueue_block_editor_assets', 'visiblocks_snackbar_position_enqueue' );
