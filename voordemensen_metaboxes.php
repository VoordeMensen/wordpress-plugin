<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

// add events metaboxes
function voordemensen_add_custom_box() {
    add_meta_box(
        'voordemensen_box_id',        // Unique ID
        'VoordeMensen',        // Box title
        'voordemensen_custom_box_html', // Content callback, must be callable
        null,                         // Admin page (or post type), 'null' uses current post type
        'advanced',                   // Context
        'high'                        // Priority
    );
}

add_action('add_meta_boxes', 'voordemensen_add_custom_box');

function voordemensen_custom_box_html($post) {
    $options = get_option('voordemensen_options');
    $voordemensen_client_shortname = sanitize_text_field($options['voordemensen_client_shortname']);
    $event_id = sanitize_text_field(get_post_meta($post->ID, '_voordemensen_meta_key', true));
    $response = wp_remote_get('https://api.voordemensen.nl/v1/' . sanitize_text_field($voordemensen_client_shortname) . '/events');
    $body = wp_remote_retrieve_body($response);
    $voordemensen_events = json_decode($body);

    if ($voordemensen_events && is_array($voordemensen_events)) {
        wp_nonce_field('voordemensen_save_event_id', 'voordemensen_event_nonce');

        echo '<label for="voordemensen_event_id">' . esc_html__('Evenement:', 'voordemensen') . '</label>';
        echo '<select name="voordemensen_event_id" id="voordemensen_event_id" class="postbox">';
        echo '<option value="">' . esc_html__('selecteer...', 'voordemensen') . '</option>';

        usort($voordemensen_events, function($a, $b) {
            return strcmp($a->event_name, $b->event_name);
        });

        foreach ($voordemensen_events as $event) {
            if (isset($event->event_name, $event->event_id)) {
                printf(
                    '<option value="%s"%s>%s</option>',
                    esc_attr($event->event_id),
                    selected($event->event_id, $event_id, false),
                    esc_html($event->event_id . ' | ' . $event->event_name)
                );
            }
        }
        echo '</select>';
    } else {
        echo esc_html__('Geen evenementen gevonden', 'voordemensen');
    }
}

// save metaboxes
function voordemensen_save_postdata($post_id) {
    // Check if our nonce is set.
    if (!isset($_POST['voordemensen_event_nonce'])) {
        return $post_id;
    }

    // Verify that the nonce is valid.
    $nonce = sanitize_text_field(wp_unslash($_POST['voordemensen_event_nonce']));
    if (!wp_verify_nonce($nonce, 'voordemensen_save_event_id')) {
        return $post_id;
    }

    // Check if this is an autosave routine. If it is, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions.
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    // Update the meta field in the database.
    if (array_key_exists('voordemensen_event_id', $_POST)) {
        update_post_meta(
            $post_id,
            '_voordemensen_meta_key',
            sanitize_text_field($_POST['voordemensen_event_id'])
        );
    }
}
add_action('save_post', 'voordemensen_save_postdata');