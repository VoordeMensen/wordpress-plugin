<?php
/**
 * Plugin Name:       VoordeMensen
 * Plugin URI:        https://github.com/VoordeMensen/wordpress-plugin
 * Description:       Verbind WordPress met het VoordeMensen kaartverkoopsysteem
 * Version:           2.0.14
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            VoordeMensen
 * Author URI:        https://voordemensen.nl
 * License:           GPL v2 or later
 */

 if (!defined('ABSPATH')) exit; // Exit if accessed directly

// include the vdm settings page
include('voordemensen_admin.php');

// add metaboxes
include('voordemensen_metaboxes.php');

// add shotcodes
include('voordemensen_shortcodes.php');

// add the voordemensen_loader
function voordemensen_load_loader() {
    $options = get_option('voordemensen_options');
    $voordemensen_client_shortname = sanitize_text_field($options['voordemensen_client_shortname']);
	$voordemensen_client_domainname = sanitize_text_field($options['voordemensen_client_domainname'] ?? 'tickets.voordemensen.nl');
	if($options['voordemensen_loader_type']=='side') {
		wp_enqueue_script('vdm_loader','https://'.$voordemensen_client_domainname.'/'.$voordemensen_client_shortname.'/iframes/vdm_sideloader.js',[], true, true);
	} else {
		wp_enqueue_script('vdm_loader','https://'.$voordemensen_client_domainname.'/'.$voordemensen_client_shortname.'/iframes/vdm_loader.js', [], true, true);	}
}

// preload the event_data
function voordemensen_load_event() {
	global $voordemensen_events;
	$event_id = get_post_meta(get_the_ID(), '_voordemensen_meta_key', true);
    $options = get_option('voordemensen_options');
    $voordemensen_client_shortname = sanitize_text_field($options['voordemensen_client_shortname']);
	if(!empty($event_id)) {
	    $response = wp_remote_get( 'https://api.voordemensen.nl/v1/'.$voordemensen_client_shortname.'/events/'.$event_id );
		$body = wp_remote_retrieve_body( $response );
		$voordemensen_events = json_decode($body);
	}
}

add_action( "template_redirect", "voordemensen_load_loader" );
add_action( "template_redirect", "voordemensen_load_event" );
add_filter( 'single_post_title', 'do_shortcode' );
add_filter( 'the_title', 'do_shortcode' );

// register scripts
add_action('init', 'voordemensen_register_script');
function voordemensen_register_script(){
	wp_register_script( 'vdm_script', plugins_url('/js/vdm_script.js', __FILE__), array('jquery'), '2.5.1', true );
}
add_action('admin_init', 'voordemensen_register_adminscript');
function voordemensen_register_adminscript() {
	wp_register_script( 'vdm_adminscript', plugins_url('/js/vdm_adminscript.js', __FILE__), array('jquery'), '2.5.1', true);
}

add_action('wp_enqueue_scripts', 'voordemensen_enqueue_script');
function voordemensen_enqueue_script(){
	wp_enqueue_script('vdm_script');

}

function voordemensen_admin_queue( $hook ) {
    global $post; 
    if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
		wp_enqueue_script('voordemensen_adminscript');
    }

    // Register and enqueue the external CSS file
    wp_register_style('voordemensen_admin_style', plugins_url('css/admin-style.css', __FILE__));
    wp_enqueue_style('voordemensen_admin_style');
}

add_action( 'admin_enqueue_scripts', 'voordemensen_admin_queue' );
