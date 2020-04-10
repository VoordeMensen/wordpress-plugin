<?php
/**
 * Plugin Name:       VoordeMensen
 * Plugin URI:        https://voordemensen.nl
 * Description:       Verbind WordPress met het VoordeMensen kaartverkoopsysteem
 * Version:           1.0.0
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

add_action( "template_redirect", "vdm_load_loader" );

