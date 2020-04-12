<?php
add_shortcode('vdm_buy', 'vdm_shortcode_buy');
function vdm_shortcode_buy( $atts = [], $content = null, $tag='') {
	$event_id = get_post_meta(get_the_ID(), '_vdm_meta_key', true);
	if($atts=='') {
		$atts=[];
		$atts['button'] = 'Koop nu';
	}
	if(!(isset($atts['button']))) {
		$atts['button'] = 'Koop nu';
	}
    $content = "<button onclick='javascript:vdm_order($event_id,\"".session_id()."\");'>".$atts['button']."</button>";
    return $content;
}

add_shortcode('vdm_eventbuttons', 'vdm_eventbuttons');
function vdm_eventbuttons( $atts = [], $content = null, $tag='') {
	$event_id = get_post_meta(get_the_ID(), '_vdm_meta_key', true);
	$vdm_client_shortname = wp_strip_all_tags(get_option('vdm_client_shortname'));
    $response = wp_remote_get( 'https://api.voordemensen.nl/v1/'.$vdm_client_shortname.'/events/'.$event_id );
	$body = wp_remote_retrieve_body( $response );
	$events = json_decode($body);

	if($events) {
		foreach($events as $allevent) {
			foreach($allevent->sub_events as $event) {
				$event->event_date = date('d-m-Y',strtotime($event->event_date));
				$event->event_time = date('H:i',strtotime($event->event_time));
				if($event->event_free>0) {
				    $content .= "<button id='btn$event->event_id' onclick='javascript:vdm_order($event->event_id,\"".session_id()."\");'>$event->event_date $event->event_time</button><br><br>";
				} else {
					$content .= "<button disabled style='pointer-events: none !important;filter: brightness(350%);' id='btn$event->event_id'>$event->event_date $event->event_time</button><br><br>";
				}
			}
		}
	}
    return $content;
}

add_shortcode('vdm_basketcounter', 'vdm_basketcounter');
function vdm_basketcounter( $atts = [], $content = null, $tag='') {
	$vdm_client_shortname = wp_strip_all_tags(get_option('vdm_client_shortname'));
    $response = wp_remote_get( 'https://tickets.voordemensen.nl/api/'.$vdm_client_shortname.'/cart/'.session_id());
	$body = wp_remote_retrieve_body( $response );
	$cart = json_decode($body);	
	if (is_object($cart) || is_array($cart)) {
		$content= count($cart)-1;
	} else {
		$content='n/a';
	}
	return $content;
}
?>