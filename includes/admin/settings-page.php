<?php
/**
 * VisiBlocks admin settings page (Settings -> VisiBlocks).
 *
 * @package VisiBlocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the VisiBlocks settings page under the Settings menu.
 *
 * @return void
 */
function visiblocks_register_settings_page() {
	add_options_page(
		__( 'VisiBlocks', 'visiblocks' ), // Page <title>.
		__( 'VisiBlocks', 'visiblocks' ), // Menu label.
		'manage_options',                 // Capability.
		'visiblocks',                     // Menu slug.
		'visiblocks_render_settings_page' // Render callback.
	);
}
add_action( 'admin_menu', 'visiblocks_register_settings_page' );

/**
 * Add a "Settings" link to the plugin's row on the Plugins screen.
 *
 * @param array $links Existing action links.
 * @return array Action links with the settings link first.
 */
function visiblocks_add_action_links( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'options-general.php?page=visiblocks' ) ),
		esc_html__( 'Settings', 'visiblocks' )
	);

	array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( VISIBLOCKS_FILE ), 'visiblocks_add_action_links' );

/**
 * Register the plugin's setting, sections, and fields.
 *
 * @return void
 */
function visiblocks_register_settings() {
	register_setting(
		'visiblocks_settings_group',
		VISIBLOCKS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'visiblocks_sanitize_settings',
			'default'           => array(),
		)
	);

	// General section, the master enable switch.
	add_settings_section(
		'visiblocks_section_general',
		__( 'General', 'visiblocks' ),
		'__return_false',
		'visiblocks'
	);

	add_settings_field(
		'visiblocks_field_enabled',
		__( 'Enable VisiBlocks', 'visiblocks' ),
		'visiblocks_render_checkbox_field',
		'visiblocks',
		'visiblocks_section_general',
		array(
			'key'         => 'enabled',
			'label_for'   => 'visiblocks_enabled',
			'label'       => __( 'Enable VisiBlocks editor enhancements', 'visiblocks' ),
			'description' => __( 'Master switch. Turn every VisiBlocks enhancement off at once without losing your individual toggle choices.', 'visiblocks' ),
		)
	);

	// View & Save section, quality-of-life editor tweaks.
	add_settings_section(
		'visiblocks_section_view',
		__( 'View &amp; Save', 'visiblocks' ),
		'visiblocks_render_view_section',
		'visiblocks'
	);

	add_settings_field(
		'visiblocks_field_view_same_tab',
		__( 'View in same tab', 'visiblocks' ),
		'visiblocks_render_checkbox_field',
		'visiblocks',
		'visiblocks_section_view',
		array(
			'key'         => 'view_same_tab',
			'label_for'   => 'visiblocks_view_same_tab',
			'label'       => __( 'Add a "View" button that opens the post in the same tab', 'visiblocks' ),
			'description' => __( 'Adds a same-tab View button next to the stock one (which keeps opening a new tab), so you stop piling up browser tabs while editing.', 'visiblocks' ),
		)
	);

	add_settings_field(
		'visiblocks_field_reposition_snackbar',
		__( 'Reposition save notice', 'visiblocks' ),
		'visiblocks_render_checkbox_field',
		'visiblocks',
		'visiblocks_section_view',
		array(
			'key'         => 'reposition_snackbar',
			'label_for'   => 'visiblocks_reposition_snackbar',
			'label'       => __( 'Move the "saved" notice up near the Save button', 'visiblocks' ),
			'description' => __( 'Relocates the block editor snackbar from the far bottom-left corner to the top-right, near where you clicked Save.', 'visiblocks' ),
		)
	);

	// Editor Layout section, controls that improve the editing workspace itself.
	add_settings_section(
		'visiblocks_section_editor_layout',
		__( 'Editor Layout', 'visiblocks' ),
		'visiblocks_render_editor_layout_section',
		'visiblocks'
	);

	add_settings_field(
		'visiblocks_field_adjustable_sidebar',
		__( 'Adjustable editor sidebar', 'visiblocks' ),
		'visiblocks_render_checkbox_field',
		'visiblocks',
		'visiblocks_section_editor_layout',
		array(
			'key'         => 'adjustable_sidebar',
			'label_for'   => 'visiblocks_adjustable_sidebar',
			'label'       => __( 'Make the editor sidebar wider and adjustable', 'visiblocks' ),
			'description' => __( 'Starts the settings sidebar at a roomier width and lets each editor resize it by dragging its left edge. The chosen width is saved as an editor preference.', 'visiblocks' ),
		)
	);

	// Discoverability section, affordance toggles land here as features are built.
	add_settings_section(
		'visiblocks_section_discoverability',
		__( 'Discoverability', 'visiblocks' ),
		'visiblocks_render_discoverability_section',
		'visiblocks'
	);

	add_settings_field(
		'visiblocks_field_block_outlines',
		__( 'Block outlines', 'visiblocks' ),
		'visiblocks_render_select_field',
		'visiblocks',
		'visiblocks_section_discoverability',
		array(
			'key'         => 'block_outlines',
			'label_for'   => 'visiblocks_block_outlines',
			'choices'     => array(
				'off'    => __( 'Off', 'visiblocks' ),
				'hover'  => __( 'On hover', 'visiblocks' ),
				'always' => __( 'Always', 'visiblocks' ),
			),
			'description' => __( 'Draw a dashed boundary around blocks so their edges are visible. "On hover" shows it only for the block under the cursor (and the selected block); "Always" outlines every block.', 'visiblocks' ),
		)
	);

	add_settings_field(
		'visiblocks_field_add_block_links',
		__( 'Add-block hints', 'visiblocks' ),
		'visiblocks_render_checkbox_field',
		'visiblocks',
		'visiblocks_section_discoverability',
		array(
			'key'         => 'add_block_links',
			'label_for'   => 'visiblocks_add_block_links',
			'label'       => __( 'Show a persistent "+" between blocks', 'visiblocks' ),
			'description' => __( 'Places a faint "+" hint at each block boundary so you can see where new blocks go. As you approach it, it steps aside and WordPress\'s own inserter takes over, so clicking works exactly like normal.', 'visiblocks' ),
		)
	);

	add_settings_field(
		'visiblocks_field_remove_block_button',
		__( 'Remove-block button', 'visiblocks' ),
		'visiblocks_render_checkbox_field',
		'visiblocks',
		'visiblocks_section_discoverability',
		array(
			'key'         => 'remove_block_button',
			'label_for'   => 'visiblocks_remove_block_button',
			'label'       => __( 'Add a "remove block" button beside the new-block "+"', 'visiblocks' ),
			'description' => __( 'Puts a matching minus button next to the "+" that appears on a new empty block, so an accidentally added block can be removed on the spot instead of hunting through the toolbar menu.', 'visiblocks' ),
		)
	);

	add_settings_field(
		'visiblocks_field_edge_block_inserters',
		__( 'Add-block buttons at the edges', 'visiblocks' ),
		'visiblocks_render_checkbox_field',
		'visiblocks',
		'visiblocks_section_discoverability',
		array(
			'key'         => 'edge_block_inserters',
			'label_for'   => 'visiblocks_edge_block_inserters',
			'label'       => __( 'Show a "+" above the first block and below the last', 'visiblocks' ),
			'description' => __( 'Adds working "add block" buttons at the top and bottom of the document, where WordPress offers none, so there is always an obvious place to start writing and to continue instead of hunting for the right spot to click.', 'visiblocks' ),
		)
	);

	add_settings_field(
		'visiblocks_field_toolbar_delete_button',
		__( 'Delete button in the toolbar', 'visiblocks' ),
		'visiblocks_render_checkbox_field',
		'visiblocks',
		'visiblocks_section_discoverability',
		array(
			'key'         => 'toolbar_delete_button',
			'label_for'   => 'visiblocks_toolbar_delete_button',
			'label'       => __( 'Show a "Delete" button in the block toolbar', 'visiblocks' ),
			'description' => __( 'Deleting a block is a common, deliberate action, but WordPress keeps it inside the toolbar\'s overflow ("three dots") menu. This adds it to the toolbar itself, in its own group just before that menu.', 'visiblocks' ),
		)
	);
}
add_action( 'admin_init', 'visiblocks_register_settings' );

/**
 * Sanitize the settings array on save.
 *
 * @param mixed $input Raw submitted value.
 * @return array Sanitized settings.
 */
function visiblocks_sanitize_settings( $input ) {
	$input = is_array( $input ) ? $input : array();

	$output = array();

	// Boolean toggles (checkbox present === on).
	$output['enabled']               = ! empty( $input['enabled'] );
	$output['view_same_tab']         = ! empty( $input['view_same_tab'] );
	$output['reposition_snackbar']   = ! empty( $input['reposition_snackbar'] );
	$output['adjustable_sidebar']    = ! empty( $input['adjustable_sidebar'] );
	$output['add_block_links']       = ! empty( $input['add_block_links'] );
	$output['remove_block_button']   = ! empty( $input['remove_block_button'] );
	$output['edge_block_inserters']  = ! empty( $input['edge_block_inserters'] );
	$output['toolbar_delete_button'] = ! empty( $input['toolbar_delete_button'] );

	// Block outlines mode (whitelist).
	$allowed_modes            = array( 'off', 'hover', 'always' );
	$mode                     = isset( $input['block_outlines'] ) ? $input['block_outlines'] : 'off';
	$output['block_outlines'] = in_array( $mode, $allowed_modes, true ) ? $mode : 'off';

	return $output;
}

/**
 * Render a boolean checkbox field.
 *
 * @param array $args {
 *     @type string $key         Setting key within the VisiBlocks option.
 *     @type string $label_for   Input id.
 *     @type string $label       Checkbox label.
 *     @type string $description Optional help text.
 * }
 * @return void
 */
function visiblocks_render_checkbox_field( $args ) {
	$key = isset( $args['key'] ) ? $args['key'] : '';

	if ( '' === $key ) {
		return;
	}

	$id          = isset( $args['label_for'] ) ? $args['label_for'] : 'visiblocks_' . $key;
	$label       = isset( $args['label'] ) ? $args['label'] : '';
	$description = isset( $args['description'] ) ? $args['description'] : '';
	?>
	<label for="<?php echo esc_attr( $id ); ?>">
		<input
			type="checkbox"
			id="<?php echo esc_attr( $id ); ?>"
			name="<?php echo esc_attr( VISIBLOCKS_OPTION ); ?>[<?php echo esc_attr( $key ); ?>]"
			value="1"
			<?php checked( (bool) visiblocks_get_setting( $key ) ); ?>
		/>
		<?php echo esc_html( $label ); ?>
	</label>
	<?php if ( '' !== $description ) : ?>
		<p class="description"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>
	<?php
}

/**
 * Render a select (dropdown) field.
 *
 * @param array $args {
 *     @type string $key         Setting key within the VisiBlocks option.
 *     @type string $label_for   Select id.
 *     @type array  $choices     value => label pairs.
 *     @type string $description Optional help text.
 * }
 * @return void
 */
function visiblocks_render_select_field( $args ) {
	$key = isset( $args['key'] ) ? $args['key'] : '';

	if ( '' === $key || empty( $args['choices'] ) || ! is_array( $args['choices'] ) ) {
		return;
	}

	$id          = isset( $args['label_for'] ) ? $args['label_for'] : 'visiblocks_' . $key;
	$description = isset( $args['description'] ) ? $args['description'] : '';
	$current     = (string) visiblocks_get_setting( $key );
	?>
	<select
		id="<?php echo esc_attr( $id ); ?>"
		name="<?php echo esc_attr( VISIBLOCKS_OPTION ); ?>[<?php echo esc_attr( $key ); ?>]"
	>
		<?php foreach ( $args['choices'] as $value => $label ) : ?>
			<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, (string) $value ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php if ( '' !== $description ) : ?>
		<p class="description"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>
	<?php
}

/**
 * Intro copy for the View & Save section.
 *
 * @return void
 */
function visiblocks_render_view_section() {
	echo '<p>' . esc_html__( 'Small quality-of-life tweaks around viewing and saving.', 'visiblocks' ) . '</p>';
}

/**
 * Intro copy for the Editor Layout section.
 *
 * @return void
 */
function visiblocks_render_editor_layout_section() {
	echo '<p>' . esc_html__( 'Give editors more control over the space around their content.', 'visiblocks' ) . '</p>';
}

/**
 * Intro copy for the Discoverability section.
 *
 * @return void
 */
function visiblocks_render_discoverability_section() {
	echo '<p>' . esc_html__( 'Editor affordance toggles will appear here.', 'visiblocks' ) . '</p>';
}

/**
 * Render the settings page.
 *
 * @return void
 */
function visiblocks_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<p><?php esc_html_e( "Make the block editor's hidden editing affordances visible and discoverable.", 'visiblocks' ); ?></p>

		<form action="options.php" method="post">
			<?php
			settings_fields( 'visiblocks_settings_group' );
			do_settings_sections( 'visiblocks' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
