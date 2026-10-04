<?php
/**
 * Feature: Add-block buttons at the edges.
 *
 * Real, working "+" buttons above the first and below the final top-level block.
 * No native inserter exists at either outer edge (which is why the cosmetic ghosts
 * are suppressed there), so these buttons perform the insert themselves.
 *
 * Both edges are one feature and one setting: they are the same control, and an
 * editor who wants one almost certainly wants the other.
 *
 * The JS runs in the outer editor document and reaches into the canvas; the CSS
 * styles elements inside the canvas, so it goes on `enqueue_block_assets`.
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
function visiblocks_edge_block_inserters_enabled() {
	return visiblocks_is_enabled() && visiblocks_get_setting( 'edge_block_inserters' );
}

/**
 * Enqueue the edge-inserter script.
 *
 * @return void
 */
function visiblocks_edge_block_inserters_enqueue_script() {
	if ( ! visiblocks_edge_block_inserters_enabled() ) {
		return;
	}

	visiblocks_enqueue_script(
		'visiblocks-edge-block-inserters',
		'assets/js/edge-block-inserters.js',
		array( 'wp-data', 'wp-blocks', 'wp-i18n' ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'visiblocks_edge_block_inserters_enqueue_script' );

/**
 * Enqueue the edge-inserter stylesheet into the canvas.
 *
 * @return void
 */
function visiblocks_edge_block_inserters_enqueue_style() {
	if ( ! is_admin() || ! visiblocks_edge_block_inserters_enabled() ) {
		return;
	}

	visiblocks_enqueue_style( 'visiblocks-edge-block-inserters', 'assets/css/edge-block-inserters.css' );
}
add_action( 'enqueue_block_assets', 'visiblocks_edge_block_inserters_enqueue_style' );
