/**
 * VisiBlocks: Add-block hints (native inserter at zero-gap boundaries).
 *
 * Every ghost "+" stands for WordPress's own between-block inserter. Core only
 * shows that inserter while the cursor is over the block list itself, i.e. over
 * the empty space between two block wrappers. When a theme puts its spacing
 * inside the blocks (padding) instead of between them, that space is zero pixels
 * wide, so core's inserter can never appear and the ghost has nothing to hand
 * off to.
 *
 * At those boundaries this script asks core to show its own inserter, with the
 * same public store action core's hover handler uses:
 *
 *   showInsertionPoint( rootClientId, index, { __unstableWithInserter: true } )
 *
 * The button, its hover state and the insertion are all core's. Boundaries with a
 * real gap are left to core's own handler.
 *
 * Core's handler (on the block list, bubble phase) calls hideInsertionPoint()
 * whenever the cursor moves over a block. React renders between event listeners,
 * so a hide followed by a show remounts the inserter on every mouse move, which
 * restarts its appear animation and it never becomes visible. This listener is on
 * the block list in the capture phase, and stops propagation while the cursor is
 * in a zero-gap band, so core's handler does not run for those moves. Outside the
 * bands, events are untouched.
 *
 * @package VisiBlocks
 */

( function () {
	'use strict';

	/*
	 * Gaps (px) below this are treated as unreachable for core's own hover
	 * handler, so this script takes over that boundary.
	 */
	var MIN_GAP = 8;

	/* Half-height (px) of the band around a boundary that shows the inserter. */
	var ZONE = 12;

	/* The canvas document and block list currently being watched. */
	var current = { doc: null, root: null };

	/* Insertion index this script last showed, or null. */
	var shownIndex = null;

	/**
	 * The document holding the blocks: the canvas iframe, or the main document
	 * for non-iframe editors.
	 *
	 * @return {Document|null}
	 */
	function getCanvasDoc() {
		var iframe = document.querySelector( 'iframe[name="editor-canvas"]' );
		if ( iframe && iframe.contentDocument && iframe.contentDocument.querySelector( '.is-root-container' ) ) {
			return iframe.contentDocument;
		}
		if ( document.querySelector( '.is-root-container' ) ) {
			return document;
		}
		return null;
	}

	/**
	 * Top-level blocks (direct children of the root container).
	 *
	 * @param {Element|null} root
	 * @return {Element[]}
	 */
	function topLevelBlocks( root ) {
		var out = [];
		if ( ! root ) {
			return out;
		}
		var kids = root.children;
		for ( var i = 0; i < kids.length; i++ ) {
			if ( kids[ i ].classList && kids[ i ].classList.contains( 'block-editor-block-list__block' ) ) {
				out.push( kids[ i ] );
			}
		}
		return out;
	}

	/**
	 * The block-editor store selectors and actions, or null if unavailable.
	 *
	 * @return {{select: Object, dispatch: Object}|null}
	 */
	function editorStore() {
		if ( ! window.wp || ! window.wp.data ) {
			return null;
		}
		var select = window.wp.data.select( 'core/block-editor' );
		var dispatch = window.wp.data.dispatch( 'core/block-editor' );
		if ( ! select || ! dispatch || ! dispatch.showInsertionPoint || ! dispatch.hideInsertionPoint ) {
			return null;
		}
		return { select: select, dispatch: dispatch };
	}

	/**
	 * Whether the block is the selected, untouched default block. Core offers no
	 * between-block inserter beside it, and add-block-links.js hides the ghosts
	 * there too.
	 *
	 * @param {Object} select
	 * @param {string} clientId
	 * @return {boolean}
	 */
	function isSelectedEmptyBlock( select, clientId ) {
		if ( ! clientId || clientId !== select.getSelectedBlockClientId() ) {
			return false;
		}
		if ( ! window.wp.blocks || ! window.wp.blocks.isUnmodifiedDefaultBlock ) {
			return false;
		}
		var block = select.getBlock( clientId );
		return !! block && window.wp.blocks.isUnmodifiedDefaultBlock( block );
	}

	/**
	 * Index to insert at for a zero-gap boundary under the cursor, or null.
	 *
	 * @param {MouseEvent} event
	 * @param {Object}     select
	 * @return {number|null}
	 */
	function boundaryIndexAt( event, select ) {
		var blocks = topLevelBlocks( current.root );

		for ( var i = 0; i < blocks.length - 1; i++ ) {
			var a = blocks[ i ].getBoundingClientRect();
			var b = blocks[ i + 1 ].getBoundingClientRect();

			if ( b.top - a.bottom >= MIN_GAP ) {
				continue;
			}

			var y = ( a.bottom + b.top ) / 2;
			if ( Math.abs( event.clientY - y ) > ZONE ) {
				continue;
			}
			if ( event.clientX < Math.max( a.left, b.left ) || event.clientX > Math.min( a.right, b.right ) ) {
				continue;
			}

			var prevId = blocks[ i ].getAttribute( 'data-block' );
			var nextId = blocks[ i + 1 ].getAttribute( 'data-block' );
			if ( ! nextId || isSelectedEmptyBlock( select, prevId ) || isSelectedEmptyBlock( select, nextId ) ) {
				return null;
			}

			return select.getBlockIndex( nextId );
		}

		return null;
	}

	/**
	 * Whether a between-block inserter's block picker is open. The picker lives
	 * inside the insertion point popover, so hiding the insertion point would
	 * close it. Core's own handler stands down in the same state.
	 *
	 * @return {boolean}
	 */
	function isInserterOpen() {
		var selector = '.block-editor-block-list__insertion-point-inserter .block-editor-inserter__toggle[aria-expanded="true"]';
		if ( document.querySelector( selector ) ) {
			return true;
		}
		return !! ( current.doc && current.doc !== document && current.doc.querySelector( selector ) );
	}

	/**
	 * Hide the inserter if this script showed it.
	 *
	 * @param {Object} store
	 */
	function release( store ) {
		if ( null === shownIndex ) {
			return;
		}
		shownIndex = null;
		store.dispatch.hideInsertionPoint();
	}

	/**
	 * @param {MouseEvent} event
	 */
	function onMouseMove( event ) {
		var store = editorStore();
		if ( ! store ) {
			return;
		}

		// Block picker open: leave the insertion point and the event alone.
		if ( isInserterOpen() ) {
			return;
		}

		// Dragging, multi-selecting, or a locked template: core shows no inserter.
		if ( event.buttons || store.select.isMultiSelecting() || store.select.getTemplateLock() ) {
			release( store );
			return;
		}

		var index = boundaryIndexAt( event, store.select );
		if ( null === index || index < 0 ) {
			release( store );
			return;
		}

		// Keep core's block-list handler from hiding the inserter on this move.
		event.stopPropagation();

		if ( index === shownIndex && store.select.isBlockInsertionPointVisible && store.select.isBlockInsertionPointVisible() ) {
			return;
		}

		shownIndex = index;
		store.dispatch.showInsertionPoint( undefined, index, { __unstableWithInserter: true } );
	}

	function detach() {
		if ( current.root ) {
			current.root.removeEventListener( 'mousemove', onMouseMove, true );
		}
		current.doc = null;
		current.root = null;
		shownIndex = null;
	}

	/**
	 * Attach to the current block list, re-attaching if the editor swapped the
	 * canvas or re-rendered the list.
	 */
	function ensure() {
		var doc = getCanvasDoc();
		var root = doc ? doc.querySelector( '.is-root-container' ) : null;
		if ( ! root || root === current.root ) {
			return;
		}
		detach();
		current.doc = doc;
		current.root = root;
		root.addEventListener( 'mousemove', onMouseMove, true );
	}

	function boot() {
		ensure();
		setInterval( ensure, 1000 );
	}

	if ( document.readyState !== 'loading' ) {
		boot();
	} else {
		document.addEventListener( 'DOMContentLoaded', boot );
	}
} )();
