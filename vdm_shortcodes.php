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
    $content = apply_filters( 'vdm_buy_content', $content, $event_id, $atts );
    return $content;
}

add_filter( 'vdm_buy_content', function( $content, $event_id, $atts ) {
	if( empty( $event_id ) ) {
		add_action( 'admin_notices', 'vdm_no_event_selected' );
		return;
	}
	return $content;
}, 10, 3 );

function vdm_no_event_selected() {
    ?>
    <div class="error notice">
        <p><?php _e( 'There has been an error. Bummer!', 'my_plugin_textdomain' ); ?></p>
    </div>
    <?php
}

add_shortcode('vdm_event_name', 'vdm_event_name');
function vdm_event_name( $atts = [], $content = null, $tag='') {
	$vdm_events = $GLOBALS['vdm_events'];
	if($vdm_events) {
		return $vdm_events[0]->event_name;
	}
}

add_shortcode('vdm_event_extra', 'vdm_event_extra');
function vdm_event_extra( $atts = [], $content = null, $tag='') {
	$vdm_events = $GLOBALS['vdm_events'];
	if($vdm_events) {
		return $vdm_events[0]->event_text;
	}
}

add_shortcode('vdm_event_description', 'vdm_event_description');
function vdm_event_description( $atts = [], $content = null, $tag='') {
	$vdm_events = $GLOBALS['vdm_events'];
	if($vdm_events) {
		return $vdm_events[0]->event_short_text;
	}
}

add_shortcode('vdm_event_dates', 'vdm_event_dates');
function vdm_event_dates( $atts = [], $content = null, $tag='') {
	$vdm_events = $GLOBALS['vdm_events'];
	if($vdm_events) {
		foreach($vdm_events as $allevent) {
			foreach($allevent->sub_events as $event) {
				if($event->event_status!='pub') continue;
				$event->event_date = date('d-m-Y',strtotime($event->event_date));
				$event->event_time = date('H:i',strtotime($event->event_time));				
				$datetimes.=$event->event_date .' - '.$event->event_time."<br>";
			}
		}
	}
	return $datetimes;
}

add_shortcode('vdm_event_duration', 'vdm_event_duration');
function vdm_event_duration($atts = [], $content = null, $tag='') {
    $vdm_events = $GLOBALS['vdm_events'];
    $durations = [];
    if($vdm_events) {
        foreach($vdm_events as $allevent) {
            foreach($allevent->sub_events as $event) {
                if($event->event_status!='pub') continue;

                // Ensure event_end is set and is not zero
                if(isset($event->event_end) && $event->event_end !== '00:00:00') {
                    $start = new DateTime($event->event_time);
                    $end = new DateTime($event->event_end);

                    // Calculates the difference between the start and end times
                    $duration = $start->diff($end);

                    // Formats the duration as a string
                    $durationStr = $duration->format('%h:%I');

                    // Appends the event date and duration to the associative array
                    // The duration string is used as the key to remove duplicates
                    $durations[$durationStr] = true;
                }
            }
        }
    }
    
    // Convert the array keys into a string separated by "<br>"
    return implode("<br>", array_keys($durations));
}

add_shortcode('vdm_event_location', 'vdm_event_location');
function vdm_event_location( $atts = [], $content = null, $tag='') {
	$vdm_events = $GLOBALS['vdm_events'];
	if($vdm_events) {
		foreach($vdm_events as $allevent) {
			foreach($allevent->sub_events as $event) {
				$location[]=$event->location_name;
			}
		}
	}
	if($location) {
		$location=array_unique($location);
		foreach($location as $loc) {
			$loc=$loc.", ";
		}
	}
	return rtrim($loc,', ');
}

add_shortcode('vdm_tickettypes', 'vdm_tickettypes');
function vdm_tickettypes( $atts = [], $content = null, $tag='') {
	$vdm_events = $GLOBALS['vdm_events'];
	$vdm_client_shortname = wp_strip_all_tags(get_option('vdm_client_shortname'));

	if($vdm_events) {
		foreach($vdm_events as $allevent) {
			foreach($allevent->sub_events as $event) {
			    $response = wp_remote_get( 'https://api.voordemensen.nl/v1/'.$vdm_client_shortname.'/tickettypes/'.$event->event_id );
				$body = wp_remote_retrieve_body( $response );
				$tickettypes = json_decode($body);
				foreach($tickettypes as $tickettype) {
					$prices.=$tickettype->discounted_price." (".$tickettype->discount_name."), ";
				}
				return rtrim($prices,', ');
			}
		}
	}
}

add_shortcode('vdm_cartbutton','vdm_cartbutton');
function vdm_cartbutton( $atts = [], $content = null, $tag='') {
	if($atts=='') {
		$atts=[];
		$atts['button'] = 'Cart';
	}
	if(!(isset($atts['button']))) {
		$atts['button'] = 'Cart';
	}
    $content = "<button onclick='javascript:vdm_order(\"cart\",\"".session_id()."\");'>".$atts['button']."</button>";
    $content = apply_filters( 'vdm_cart_content', $content, $atts );
    return $content;
}

add_filter( 'vdm_cart_content', function( $content, $atts ) {
	return $content;
}, 10, 3 );

add_shortcode('vdm_eventbuttons', 'vdm_eventbuttons');
function vdm_eventbuttons( $atts = [], $content = null, $tag='') {
	$vdm_events = $GLOBALS['vdm_events'];

	if($vdm_events) {
		foreach($vdm_events as $allevent) {
			foreach($allevent->sub_events as $event) {
				$event->event_date = date('d-m-Y',strtotime($event->event_date));
				$event->event_time = date('H:i',strtotime($event->event_time));
				if($event->event_status!='pub') continue;
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
	return "<span class='vdm_basketcounter'>$content</span>";
}
?>