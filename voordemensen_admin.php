<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Initializes settings for the plugin.
 */
function voordemensen_settings_init() {
    // Register settings with validation callback
    register_setting('vdm', 'voordemensen_options', 'voordemensen_validate_options');

    // Register a new section in the "vdm" page
    add_settings_section(
        'voordemensen_section_developers',
        '',
        'voordemensen_section_developers_cb',
        'vdm'
    );

    // Register a new field for client shortname in the "vdm" page
    add_settings_field(
        'voordemensen_client_shortname',
        __('Klantnaam', 'voordemensen'),
        'voordemensen_client_shortname_cb',
        'vdm',
        'voordemensen_section_developers'
    );

    // Register a new field for client domain name in the "vdm" page
    add_settings_field(
        'voordemensen_client_domainname',
        __('Domeinnaam', 'voordemensen'),
        'voordemensen_client_domainname_cb',
        'vdm',
        'voordemensen_section_developers'
    );

    // Register a new field for loader type in the "vdm" page
    add_settings_field(
        'voordemensen_loader_type',
        __('Loader Type', 'voordemensen'),
        'voordemensen_loader_type_cb',
        'vdm',
        'voordemensen_section_developers',
        [
            'label_for' => 'voordemensen_loader_type',
            'class' => 'voordemensen_row',
            'voordemensen_custom_data' => 'custom',
        ]
    );
}

/**
 * Validation and sanitization callback for the plugin options.
 */
function voordemensen_validate_options($input) {
    $errors = [];
    $options = get_option('voordemensen_options');

    // Validate and sanitize client shortname
    if (empty(trim($input['voordemensen_client_shortname']))) {
        $errors[] = 'Client shortname cannot be empty.';
    } else {
        $input['voordemensen_client_shortname'] = sanitize_text_field($input['voordemensen_client_shortname']);
    }

    // Validate and sanitize client domain name
    if (empty(trim($input['voordemensen_client_domainname']))) {
        $errors[] = 'Client domain name cannot be empty.';
    } else {
        $input['voordemensen_client_domainname'] = sanitize_text_field($input['voordemensen_client_domainname']);
    }

    // Sanitize loader type
    $input['voordemensen_loader_type'] = sanitize_text_field($input['voordemensen_loader_type']);

    // Display errors if any
    if ($errors) {
        foreach ($errors as $error) {
            add_settings_error('voordemensen_options', 'voordemensen_options_error', $error, 'error');
        }
        return $options; // Return the existing options if there are errors
    }

    return $input;
}

/**
 * Section callback function - could have explanatory text or HTML.
 */
function voordemensen_section_developers_cb($args) {
    echo '<p id="' . esc_attr($args['id']) . '">' . esc_html__('Verbind deze WordPress-installatie met VoordeMensen.', 'voordemensen') . '</p>';
}

/**
 * Field callback function for client shortname.
 */
function voordemensen_client_shortname_cb($args) {
    $options = get_option('voordemensen_options');
    $shortname = !empty($options['voordemensen_client_shortname']) ? esc_attr($options['voordemensen_client_shortname']) : '';
    echo '<input type="text" id="voordemensen_client_shortname" name="voordemensen_options[voordemensen_client_shortname]" value="' . esc_attr($shortname) . '">';
}

/**
 * Field callback function for client domain name.
 */
function voordemensen_client_domainname_cb($args) {
    $options = get_option('voordemensen_options');
    $domainName = !empty($options['voordemensen_client_domainname']) ? esc_attr($options['voordemensen_client_domainname']) : 'tickets.voordemensen.nl';
    echo '<input type="text" id="voordemensen_client_domainname" name="voordemensen_options[voordemensen_client_domainname]" value="' . esc_attr($domainName) . '">';
    echo '<p class="description">Vul je domeinnaam in als je tickets verkoopt onder je eigen tickets.domeinnaam.nl domein - als je niet weet wat dit is, laat het dan staan op tickets.voordemensen.nl</p>';
}


/**
 * Field callback function for loader type.
 */
function voordemensen_loader_type_cb($args) {
    $options = get_option('voordemensen_options');
    $loaderType = !empty($options['voordemensen_loader_type']) ? $options['voordemensen_loader_type'] : '';
    echo '<select id="' . esc_attr($args['label_for']) . '" name="voordemensen_options[' . esc_attr($args['label_for']) . ']">';
    echo '<option value="popup" ' . selected($loaderType, 'popup', false) . '>Popup</option>';
    echo '<option value="side" ' . selected($loaderType, 'side', false) . '>Side</option>';
    echo '</select>';
    echo '<p class="description">Kies de manier waarop de kaartverkoop op je site getoond wordt, met een popup-overlay of aan de zijkant van het scherm</p>';
}

/**
 * Register admin menu for the plugin settings page.
 */
function voordemensen_options_page() {
    add_menu_page(
        'VoordeMensen Settings',
        'VoordeMensen',
        'manage_options',
        'vdm',
        'voordemensen_options_page_html',
        'dashicons-tickets-alt'
    );
}

/**
 * Render the settings page for the plugin.
 */
function voordemensen_options_page_html() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Show error/update messages
    settings_errors('voordemensen_options');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            // Output security fields for the registered setting "vdm"
            settings_fields('vdm');
            // Output setting sections and their fields
            do_settings_sections('vdm');
            // Output save settings button
            submit_button(__('Wijzigingen opslaan', 'voordemensen'));
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'voordemensen_settings_init');
add_action('admin_menu', 'voordemensen_options_page');
