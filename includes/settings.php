<?php
/**
 * Settings API Implementation
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Initialize Settings
 */
add_action( 'admin_init', 'cp_settings_init' );
function cp_settings_init() {

    /**
     * Enforce Intent Modal
     */
    register_setting( 'cp_settings_group', 'cp_enable_intent', [
        'type'              => 'boolean',
        'sanitize_callback' => function( $val ) {
            return $val ? 1 : 0;
        },
        'default'           => 1,
    ]);

    /**
     * Content Change Threshold (future enforcement)
     */
    register_setting( 'cp_settings_group', 'cp_content_threshold', [
        'type'              => 'integer',
        'sanitize_callback' => 'absint',
        'default'           => 0,
    ]);

    /**
     * Excluded Post Types
     */
    register_setting( 'cp_settings_group', 'cp_excluded_post_types', [
        'type'              => 'array',
        'sanitize_callback' => 'cp_sanitize_array',
        'default'           => [],
    ]);

    /**
     * Section
     */
    add_settings_section(
        'cp_main_section',
        __( 'Capture Configuration', 'changeproof' ),
        '__return_null',
        'cp-settings'
    );

    /**
     * Fields
     */
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
 * Field: Intent Enforcement
 */
function cp_field_intent_render() {
    $val = (int) get_option( 'cp_enable_intent', 1 );
    ?>
    <label>
        <input type="checkbox" name="cp_enable_intent" value="1" <?php checked( $val, 1 ); ?> />
        <?php _e( 'Require users to explain why they are making a change before saving.', 'changeproof' ); ?>
    </label>
    <?php
}

/**
 * Field: Threshold
 */
function cp_field_threshold_render() {
    $val = (int) get_option( 'cp_content_threshold', 0 );
    ?>
    <input type="number" name="cp_content_threshold" value="<?php echo esc_attr( $val ); ?>" class="small-text" min="0" />
    <p class="description">
        <?php _e( 'Minimum character difference required to log a change. Set to 0 to log every change.', 'changeproof' ); ?>
    </p>
    <?php
}

/**
 * Field: Excluded Post Types
 */
function cp_field_post_types_render() {
    $selected   = (array) get_option( 'cp_excluded_post_types', [] );
    $post_types = get_post_types( [ 'public' => true ], 'objects' );

    foreach ( $post_types as $type ) {
        if ( 'attachment' === $type->name ) continue;

        printf(
            '<label style="display:block;">
                <input type="checkbox" name="cp_excluded_post_types[]" value="%s" %s />
                %s
            </label>',
            esc_attr( $type->name ),
            checked( in_array( $type->name, $selected, true ), true, false ),
            esc_html( $type->label )
        );
    }
}

/**
 * Sanitize checkbox arrays
 */
function cp_sanitize_array( $input ) {
    if ( ! is_array( $input ) ) {
        return [];
    }
    return array_values( array_map( 'sanitize_text_field', $input ) );
}

/**
 * Render Settings Page
 */
function cp_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Changeproof Settings', 'changeproof' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'cp_settings_group' );
            do_settings_sections( 'cp-settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
