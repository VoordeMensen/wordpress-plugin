<?php
// add events metaboxes
function vdm_add_custom_box() {
    add_meta_box(
        'vdm_box_id',           // Unique ID
        'VoordeMensen',  // Box title
        'vdm_custom_box_html');
}
add_action('add_meta_boxes', 'vdm_add_custom_box');

function vdm_custom_box_html($post)
{
	$vdm_client_shortname = wp_strip_all_tags(get_option('vdm_client_shortname'));
	$event_id = get_post_meta($post->ID, '_vdm_meta_key', true);
    $response = wp_remote_get( 'https://api.voordemensen.nl/v1/'.$vdm_client_shortname.'/events' );
    $body = wp_remote_retrieve_body( $response );
    $vdm_events = json_decode($body);
    if($vdm_events) {
        ?>
        <label for="vdm_event_id">Evenement:</label>
        <select name="vdm_event_id" id="vdm_event_id" class="postbox">
            <option value=""><?php __('selecteer...','vdm')?></option>
            <?php
    			usort($vdm_events, function($a, $b) {return strcmp($a->event_name, $b->event_name);});
    			foreach($vdm_events as $event) {
    				if(isset($event->event_name)) {
    					echo "<option ";
    					if($event->event_id==$event_id) echo "SELECTED ";
    					echo "value=$event->event_id>$event->event_name</option>";
    				}
    			}
            ?>
        </select>
        <?php
    } else {
        echo __('Geen evenementen gevonden');
    }
}

// save metaboxes
function vdm_save_postdata($post_id)
{
    if (array_key_exists('vdm_event_id', $_POST)) {
        update_post_meta(
            $post_id,
            '_vdm_meta_key',
            $_POST['vdm_event_id']
        );
    }
}
add_action('save_post', 'vdm_save_postdata');