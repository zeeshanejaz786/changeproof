<?php
/**
 * Settings API Implementation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize Settings
 */
add_action( 'admin_init', 'cp_settings_init' );
function cp_settings_init() {
	// 1. Register Settings
	register_setting( 'cp_settings_group', 'cp_content_threshold', [
		'type'              => 'integer',
		'sanitize_callback' => 'absint',
		'default'           => 0,
	]);

	register_setting( 'cp_settings_group', 'cp_enable_intent', [
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '1',
	]);

	register_setting( 'cp_settings_group', 'cp_excluded_post_types', [
		'type'              => 'array',
		'sanitize_callback' => 'cp_sanitize_array',
		'default'           => [],
	]);

	// 2. Add Section
	add_settings_section(
		'cp_main_section',
		__( 'Capture Configuration', 'changeproof' ),
		null,
		'cp-settings'
	);

	// 3. Add Fields
	add_settings_field(
		'cp_enable_intent',
		__( 'Enforce Intent Modal', 'changeproof' ),
		'cp_field_intent_render',
		'cp-settings',
		'cp_main_section'
	);

	add_settings_field(
		'cp_content_threshold',
		__( 'Content Change Threshold', 'changeproof' ),
		'cp_field_threshold_render',
		'cp-settings',
		'cp_main_section'
	);

	add_settings_field(
		'cp_excluded_post_types',
		__( 'Excluded Post Types', 'changeproof' ),
		'cp_field_post_types_render',
		'cp-settings',
		'cp_main_section'
	);
}

/**
 * Field Render: Intent Enforcement
 */
function cp_field_intent_render() {
	$val = get_option( 'cp_enable_intent', '1' );
	?>
	<input type="checkbox" name="cp_enable_intent" value="1" <?php checked( $val, '1' ); ?> />
	<p class="description"><?php _e( 'If checked, users must provide a reason before saving posts.', 'changeproof' ); ?></p>
	<?php
}

/**
 * Field Render: Threshold
 */
function cp_field_threshold_render() {
	$val = get_option( 'cp_content_threshold', 0 );
	?>
	<input type="number" name="cp_content_threshold" value="<?php echo esc_attr( $val ); ?>" class="small-text" />
	<p class="description"><?php _e( 'Minimum character difference required to trigger a log. Set to 0 to log every change.', 'changeproof' ); ?></p>
	<?php
}

/**
 * Field Render: Excluded Post Types
 */
function cp_field_post_types_render() {
	$selected = (array) get_option( 'cp_excluded_post_types', [] );
	$post_types = get_post_types( [ 'public' => true ], 'objects' );

	foreach ( $post_types as $type ) {
		// Skip built-in attachments
		if ( 'attachment' === $type->name ) continue;
		
		printf(
			'<label style="display:block;"><input type="checkbox" name="cp_excluded_post_types[]" value="%s" %s /> %s</label>',
			esc_attr( $type->name ),
			checked( in_array( $type->name, $selected ), true, false ),
			esc_html( $type->label )
		);
	}
}

/**
 * Sanitize array of checkboxes
 */
function cp_sanitize_array( $input ) {
	if ( ! is_array( $input ) ) return [];
	return array_map( 'sanitize_text_field', $input );
}

/**
 * Render Settings Page
 */
function cp_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;
	?>
	<div class="wrap">
		<h1><?php _e( 'Changeproof Settings', 'changeproof' ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'cp_settings_group' );
			do_settings_sections( 'cp-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}