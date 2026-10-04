<?php
/**
 * Feature: Add-block hints.
 *
 * A persistent "ghost +" between top-level blocks that hands off to WordPress's
 * own inserter as the cursor approaches.
 *
 * - CSS (the ghost's look) styles elements in the WP 6.3+ iframe canvas, so it
 *   loads on `enqueue_block_assets`.
 * - JS measures the theme's variable block gaps and writes positioning/visibility
 *   custom properties; it runs in the editor's outer document (it reaches into
 *   the canvas iframe itself), so it loads on `enqueue_block_editor_assets`.
 *
 * @package VisiBlocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether this feature should load.
 *
 * @return bool
 */
function visiblocks_add_block_links_enabled() {
	return visiblocks_is_enabled() && visiblocks_get_setting( 'add_block_links' );
}

/**
 * Enqueue the add-block-hints canvas stylesheet, if enabled.
 *
 * @return void
 */
function visiblocks_add_block_links_enqueue_style() {
	// Editor canvas only (this hook also fires on the front end).
	if ( ! is_admin() || ! visiblocks_add_block_links_enabled() ) {
		return;
	}

	visiblocks_enqueue_style( 'visiblocks-add-block-links', 'assets/css/add-block-links.css' );
}
add_action( 'enqueue_block_assets', 'visiblocks_add_block_links_enqueue_style' );

/**
 * Enqueue the add-block-hints measuring script, if enabled.
 *
 * @return void
 */
function visiblocks_add_block_links_enqueue_script() {
	if ( ! visiblocks_add_block_links_enabled() ) {
		return;
	}

	visiblocks_enqueue_script(
		'visiblocks-add-block-links',
		'assets/js/add-block-links.js',
		// Needed to ask the editor whether the selected block is an untouched
		// empty one, which decides whether the adjacent ghosts are honest.
		array( 'wp-data', 'wp-blocks' )
	);
}
add_action( 'enqueue_block_editor_assets', 'visiblocks_add_block_links_enqueue_script' );
