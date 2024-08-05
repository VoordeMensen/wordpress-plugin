<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

add_action("wp_loaded", "voordemensen_load_event");

add_shortcode('vdm_buy', 'voordemensen_shortcode_buy');
function voordemensen_shortcode_buy($atts = [], $content = null, $tag = '')
{
    if (is_admin()) {
        return;
    }

    // Sanitize and get the event ID from post meta
    $event_id = sanitize_text_field(get_post_meta(get_the_ID(), '_voordemensen_meta_key', true));

    // Ensure $atts is an array
    $atts = shortcode_atts([
        'button' => __('Koop nu', 'voordemensen')
    ], $atts, $tag);

    // Sanitize the button label
    $button_label = sanitize_text_field($atts['button']);

    // Create a unique ID for the button
    $button_id = 'vdm-buy-button-' . uniqid();

    // JavaScript function call will be handled by AJAX
    $onclick = sprintf("javascript:vdmOrderWithSession('%s', '%s');", esc_js($event_id), esc_js($button_id));

    // Create the button HTML, escaping all output
    $content = "<button id='" . esc_attr($button_id) . "' onclick='" . esc_attr($onclick) . "'>" . esc_html($button_label) . "</button>";

    // Apply filters to allow modification of the output by other plugins/themes
    $content = apply_filters('voordemensen_buy_content', $content, $event_id, $atts);

    return $content;
}

add_filter('voordemensen_buy_content', function ($content, $event_id, $atts) {
    if (empty($event_id)) {
        add_action('admin_notices', 'voordemensen_no_event_selected');
        return '';  // Return an empty string or perhaps an alternative message in the content
    }
    return $content;
}, 10, 3);

function voordemensen_no_event_selected()
{
    // Ensure that this notice only appears in the admin area to the appropriate users
    if (current_user_can('manage_options')) {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('Error: No event selected. Please choose an event to proceed.', 'voordemensen'); ?></p>
        </div>
        <?php
    }
}
add_shortcode('vdm_event_name', 'voordemensen_event_name');
function voordemensen_event_name($atts = [], $content = null, $tag = '')
{
    if (is_admin()) {
        return;
    }

    $voordemensen_events = $GLOBALS['voordemensen_events'];
    if ($voordemensen_events && isset($voordemensen_events[0]->event_name)) {
        return esc_html($voordemensen_events[0]->event_name);
    }
}

add_shortcode('vdm_event_extra', 'voordemensen_event_extra');
function voordemensen_event_extra($atts = [], $content = null, $tag = '')
{
    if (is_admin()) {
        return;
    }

    $voordemensen_events = $GLOBALS['voordemensen_events'];
    if ($voordemensen_events && isset($voordemensen_events[0]->event_text)) {
        return esc_html($voordemensen_events[0]->event_text);
    }
}

add_shortcode('vdm_event_description', 'voordemensen_event_description');
function voordemensen_event_description($atts = [], $content = null, $tag = '')
{
    if (is_admin()) {
        return;
    }
    $voordemensen_events = $GLOBALS['voordemensen_events'];
    if ($voordemensen_events && isset($voordemensen_events[0]->event_short_text)) {
        return esc_html($voordemensen_events[0]->event_short_text);
    }
}


add_shortcode('vdm_event_dates', 'voordemensen_event_dates');
function voordemensen_event_dates($atts = [], $content = null, $tag = '')
{
    if (is_admin()) {
        return;
    }

    $voordemensen_events = $GLOBALS['voordemensen_events'];
    $datetimes = [];

    if ($voordemensen_events) {
        foreach ($voordemensen_events as $allevent) {
            foreach ($allevent->sub_events as $event) {
                if ($event->event_status != 'pub') {
                    continue;
                }

                try {
                    // Create a DateTime object from the event date and time
                    $datetime = new DateTime($event->event_date . ' ' . $event->event_time);

                    // Store the DateTime object and its formatted string
                    $datetimes[] = [
                        'datetime' => $datetime,
                        'formatted' => $datetime->format('d-m-Y - H:i')
                    ];
                } catch (Exception $e) {
                    // Handle the exception if the date format is incorrect
                    continue;
                }
            }
        }

        // Sort the datetimes array using the DateTime objects
        usort($datetimes, function ($a, $b) {
            return $a['datetime'] <=> $b['datetime'];
        });

        // Extract the formatted strings
        $formatted_dates = array_column($datetimes, 'formatted');

        // Concatenate them with "<br>"
        $output = implode("<br>", array_map('esc_html', $formatted_dates));
    } else {
        $output = '';
    }

    return $output;
}



add_shortcode('vdm_event_duration', 'voordemensen_event_duration');
function voordemensen_event_duration($atts = [], $content = null, $tag = '')
{
    if (is_admin()) {
        return;
    }
    $voordemensen_events = $GLOBALS['voordemensen_events'];
    $durations = [];
    if ($voordemensen_events) {
        foreach ($voordemensen_events as $allevent) {
            foreach ($allevent->sub_events as $event) {
                if ($event->event_status != 'pub')
                    continue;

                // Ensure event_end is set and is not zero
                if (isset($event->event_end) && $event->event_end !== '00:00:00') {
                    try {
                        $start = new DateTime($event->event_time);
                        $end = new DateTime($event->event_end);

                        // Calculates the difference between the start and end times
                        $duration = $start->diff($end);

                        // Formats the duration as a string
                        $hours = $duration->h;
                        $minutes = $duration->i;
                        $durationStr = '';
                        if ($hours > 0) {
                            $durationStr .= $hours . ' h ';
                        }
                        if ($minutes > 0) {
                            $durationStr .= $minutes . ' min';
                        }

                        // Appends the event date and duration to the associative array
                        // The duration string is used as the key to remove duplicates
                        $durations[$durationStr] = true;
                    } catch (Exception $e) {
                        // Handle the exception if the date format is incorrect
                        continue; // Optionally log this error or handle it as required
                    }
                }
            }
        }
    }

    // Convert the array keys into a string separated by "<br>"
    // Escape each duration string for HTML output
    return implode("<br>", array_map('esc_html', array_keys($durations)));
}


add_shortcode('vdm_event_location', 'voordemensen_event_location');
function voordemensen_event_location($atts = [], $content = null, $tag = '')
{
    if (is_admin()) {
        return;
    }

    $voordemensen_events = $GLOBALS['voordemensen_events'];
    $locations = []; // Initialize an empty array to store locations

    if ($voordemensen_events) {
        foreach ($voordemensen_events as $allevent) {
            foreach ($allevent->sub_events as $event) {
                if (isset($event->location_name)) {
                    $locations[] = $event->location_name; // Collect location names
                }
            }
        }
    }

    if (!empty($locations)) {
        $locations = array_unique($locations); // Remove duplicates
        $locations = array_map('esc_html', $locations); // Escape HTML to prevent XSS attacks
        return implode(', ', $locations); // Join all locations with a comma and a space
    }

    return ''; // Return an empty string if no locations are found
}


add_shortcode('vdm_tickettypes', 'voordemensen_tickettypes');
function voordemensen_tickettypes($atts = [], $content = null, $tag = '')
{
    if (is_admin()) {
        return;
    }

    $voordemensen_events = $GLOBALS['voordemensen_events'];
    $prices = '';

    $options = get_option('voordemensen_options');
    $voordemensen_client_shortname = sanitize_text_field($options['voordemensen_client_shortname']);
    $voordemensen_client_domainname = sanitize_text_field($options['voordemensen_client_domainname'] ?? 'tickets.voordemensen.nl');

    if ($voordemensen_events) {
        foreach ($voordemensen_events as $allevent) {
            foreach ($allevent->sub_events as $event) {
                $response = wp_remote_get('https://api.voordemensen.nl/v1/' . $voordemensen_client_shortname . '/tickettypes/' . esc_attr($event->event_id));
                $body = wp_remote_retrieve_body($response);
                $tickettypes = json_decode($body, true); // Decode as an array

                if (is_array($tickettypes)) {
                    foreach ($tickettypes as $tickettype) {
                        if (isset($tickettype['discounted_price'], $tickettype['discount_name'])) {
                            $prices .= esc_html($tickettype['discounted_price']) . " (" . esc_html($tickettype['discount_name']) . "), ";
                        }
                    }
                }
                // Return early if we have prices to output
                if (!empty($prices)) {
                    return rtrim($prices, ', ');
                }
            }
        }
    }

    return ''; // Return empty string if no prices found
}

add_shortcode('vdm_cartbutton', 'voordemensen_cartbutton');
function voordemensen_cartbutton($atts = [], $content = null, $tag = '')
{
    if (is_admin()) {
        return;
    }

    // Set default attributes for the shortcode and merge with input
    $atts = shortcode_atts([
        'button' => 'Cart'  // Default button text
    ], $atts, $tag);

    // Sanitize the button label to ensure it's safe for display
    $button_label = esc_html($atts['button']);

    // Create a unique ID for the button
    $button_id = 'vdm-cart-button-' . uniqid();

    // JavaScript function call will be handled by AJAX
    $onclick = sprintf("javascript:vdmOrderWithSession('cart', '%s');", esc_js($button_id));

    // Create the button HTML, escaping all output
    $content = "<button id='" . esc_attr($button_id) . "' onclick='" . esc_attr($onclick) . "'>" . esc_html($button_label) . "</button>";

    // Apply a filter to allow overriding of the final content
    $content = apply_filters('voordemensen_cart_content', $content, $atts);

    return $content;
}

add_filter('voordemensen_cart_content', function ($content, $atts) {
    return $content; // This filter currently just returns the content but can be used to modify it
}, 10, 2);


add_shortcode('vdm_eventbuttons', 'voordemensen_eventbuttons');
function voordemensen_eventbuttons($atts = [], $content = null, $tag = '')
{
    if (is_admin()) {
        return;
    }

    $voordemensen_events = $GLOBALS['voordemensen_events'];
    $content = '';

    if ($voordemensen_events) {
        $tempEvents = [];
        $timezone = wp_timezone(); // Get WordPress timezone

        foreach ($voordemensen_events as $allevent) {
            foreach ($allevent->sub_events as $event) {
                if ($event->event_status != 'pub')
                    continue;
                try {
                    $datetime = new DateTime($event->event_date . ' ' . $event->event_time, $timezone);
                    $tempEvents[] = array(
                        'datetime' => $datetime,
                        'event' => $event
                    );
                } catch (Exception $e) {
                    // Handle invalid date/time
                    continue;
                }
            }
        }

        usort($tempEvents, function ($a, $b) {
            return $a['datetime'] <=> $b['datetime'];
        });

        foreach ($tempEvents as $tempEvent) {
            $event = $tempEvent['event'];
            $buttonDate = $tempEvent['datetime']->format('d-m-Y H:i');
            $button_id = 'vdm-event-button-' . uniqid();
            $eventId = esc_attr($event->event_id);

            if ($event->event_free > 0) {
                $content .= "<button id='{$button_id}' onclick='javascript:vdmOrderWithSession(\"{$eventId}\", \"{$button_id}\");'>{$buttonDate}</button><br><br>";
            } else {
                $content .= "<button disabled style='pointer-events: none !important; filter: brightness(350%);' id='{$button_id}'>{$buttonDate}</button><br><br>";
            }
        }
    }
    return $content;
}

add_shortcode('vdm_basketcounter', 'voordemensen_basketcounter');
function voordemensen_basketcounter($atts = [], $content = null, $tag = '')
{
    if (is_admin()) {
        return;
    }

    $content = "<span class='vdm_basketcounter'>...</span>";

    // Enqueue the script to handle the AJAX call
    wp_enqueue_script('custom-session', plugin_dir_url(__FILE__) . 'js/vdm_session.js', array('jquery'), null, true);
    wp_localize_script('custom-session', 'ajaxurl', admin_url('admin-ajax.php'));

    return $content;
}

add_action('wp_ajax_nopriv_voordemensen_fetch_session_id', 'voordemensen_fetch_session_id_ajax');
add_action('wp_ajax_voordemensen_fetch_session_id', 'voordemensen_fetch_session_id_ajax');

add_action('wp_ajax_nopriv_voordemensen_generate_nonce', 'voordemensen_generate_nonce_ajax');
add_action('wp_ajax_voordemensen_generate_nonce', 'voordemensen_generate_nonce_ajax');

function voordemensen_generate_nonce_ajax()
{
    wp_send_json_success(['nonce' => wp_create_nonce('voordemensen_session_nonce')]);
}

function voordemensen_fetch_session_id_ajax()
{

    check_ajax_referer('voordemensen_session_nonce', 'security');

    check_ajax_referer('voordemensen_session_nonce', 'security');

    if (!isset($_COOKIE['user_token'])) {
        $token = bin2hex(random_bytes(16));
        setcookie('user_token', $token, time() + (365 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
    } else {
        $token = sanitize_text_field($_COOKIE['user_token']);
    }

    // Fetch session ID from external server
    $options = get_option('voordemensen_options');
    $voordemensen_client_shortname = sanitize_text_field($options['voordemensen_client_shortname']);

    $response = wp_remote_get('https://tickets.voordemensen.nl/api/'. $voordemensen_client_shortname .'/get-session?token=' . $token);

    if (is_wp_error($response)) {
        wp_send_json_error('Error fetching session ID: ' . $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['session_id'])) {
        $session_id = sanitize_text_field($data['session_id']);

        // Set the cookie to expire in 7 days
        $cookie_name = 'voordemensen_session_id';
        $cookie_value = $session_id;
        $cookie_expiration = time() + (7 * 24 * 60 * 60); // 7 days

        // Set the cookie, ensure HTTPOnly flag is set for security
        setcookie($cookie_name, $cookie_value, $cookie_expiration, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

        wp_send_json_success(['session_id' => $session_id]);
    } else {
        wp_send_json_error('Session ID not found in response.');
    }
}

function voordemensen_get_session_id()
{
    if (isset($_COOKIE['voordemensen_session_id'])) {
        return sanitize_text_field($_COOKIE['voordemensen_session_id']);
    }

    // If the cookie is not set, return a placeholder or handle accordingly.
    return null;
}

function voordemensen_custom_session_script()
{
    wp_enqueue_script('custom-session', plugin_dir_url(__FILE__) . 'js/vdm_session.js', array('jquery'), null, true);
    wp_localize_script('custom-session', 'ajaxurl', admin_url('admin-ajax.php'));

    // Pass additional data to the script
    $options = get_option('voordemensen_options');
    $voordemensen_client_shortname = sanitize_text_field($options['voordemensen_client_shortname']);
    $voordemensen_client_domainname = sanitize_text_field($options['voordemensen_client_domainname'] ?? 'tickets.voordemensen.nl');

    wp_localize_script('custom-session', 'vdm_basketcounter_data', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('voordemensen_session_nonce'),
        'client_shortname' => $voordemensen_client_shortname,
        'domain_name' => $voordemensen_client_domainname
    ));
}
add_action('wp_enqueue_scripts', 'voordemensen_custom_session_script');

?>
