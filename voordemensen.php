<?php
/**
 * Plugin Name:       VoordeMensen
 * Plugin URI:        https://voordemensen.nl
 * Description:       Verbind WordPress met het VoordeMensen kaartverkoopsysteem
 * Version:           1.0.3
 * Author:            VoordeMensen
 * Author URI:        https://voordemensen.nl
 * License:           GPL v2 or later
 */

// include the vdm settings page
include('vdm_admin.php');

// add metaboxes
include('vdm_metaboxes.php');

// add shotcodes
include('vdm_shortcodes.php');

// add the vdm_loader
function vdm_load_loader() {
	$vdm_client_shortname = wp_strip_all_tags(get_option('vdm_client_shortname'));
	$vdm_options = get_option('vdm_options');
	if($vdm_options['vdm_loader_type']=='side') {
		wp_enqueue_script('vdm_loader','https://tickets.voordemensen.nl/'.$vdm_client_shortname.'/iframes/vdm_sideloader.js');
	} else {
		wp_enqueue_script('vdm_loader','https://tickets.voordemensen.nl/'.$vdm_client_shortname.'/iframes/vdm_loader.js');	
	}
}

// preload the event_data
function vdm_load_event() {
	global $events;
	$event_id = get_post_meta(get_the_ID(), '_vdm_meta_key', true);
	$vdm_client_shortname = wp_strip_all_tags(get_option('vdm_client_shortname'));
    $response = wp_remote_get( 'https://api.voordemensen.nl/v1/'.$vdm_client_shortname.'/events/'.$event_id );
	$body = wp_remote_retrieve_body( $response );
	$events = json_decode($body);
}

add_action( "template_redirect", "vdm_load_loader" );
add_action( "template_redirect", "vdm_load_event" );
add_filter( 'single_post_title', 'do_shortcode' );
add_filter( 'the_title', 'do_shortcode' );

// start a session, use this id for the vdm_cart_id
function register_session() {
    if (!session_id())
        session_start();
}
add_action('init', 'register_session', 1);

// register scripts
add_action('init', 'register_script');
function register_script(){
	wp_register_script( 'vdm_script', plugins_url('/js/vdm_script.js', __FILE__), array('jquery'), '2.5.1' );
}

add_action('wp_enqueue_scripts', 'enqueue_script');
function enqueue_script(){
	wp_enqueue_script('vdm_script');

}