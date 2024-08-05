=== VoordeMensen ===
Contributors: voordemensen
Donate link: https://voordemensen.nl
Tags: tickets, events, e-commerce
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 2.0.14
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Connect WordPress with the VoordeMensen ticketing system.

== Description ==

VoordeMensen is a WordPress plugin that connects your website with the VoordeMensen ticket sales system. It provides several features:

* Load the VoordeMensen loader script dynamically based on your site's settings 
* Preload event data from the VoordeMensen API (see 3rd party connection for more info)
* Include various metaboxes and shortcodes for enhanced functionality
* Start a user session for tracking the VoordeMensen cart ID (see 3rd party connection for more info)

== 3rd party connection == 

This plugin will fetch the VoordeMensen loader from [https://voordemensen.nl](https://voordemensen.nl) and event data from [https://api.voordemensen.nl](https://api.voordemensen.nl) and use it in your WordPress. This way your visitors will be able to buy tickets using VoordeMensen. 

Depending on the loader type you choose in the admin page either [vdm_sideloader.js](https://tickets.voordemensen.nl/demo/vdm_sideloader.js) or [vdm_loader.js](https://tickets.voordemensen.nl/demo/vdm_loader.js) will be loaded.

Additionaly this plugin will connect to [https://tickets.voordemensen.nl](https://tickets.voordemensen.nl) to obtain a unique session id. 

The VoordeMensen [privacy statement](https://voordemensen.nl/privacyverklaring/) applies.


= Features =

* Dynamically load the VoordeMensen loader script
* Preload event data based on the settings
* Custom metaboxes and shortcodes included
* User session management

= Requirements =

* A valid VoordeMensen account

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/voordemensen` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->VoordeMensen to configure the plugin.

== Frequently Asked Questions ==

= Where can I get an VoordeMensen account? =

You can create a VoordeMensen account at https://voordemensen.nl.

== Screenshots ==

1. VoordeMensen settings page.

== Changelog ==

= 2.0.14 = 
Fixed a caching issue, make the plugin more stable in a cached enviroment.
Improved sorting of event_dates and buttons shortcodes.


= 2.0.13 = 
Adding session management using session id's aquired from the VoordeMensen server, making the plugin compatible with caching plugins. 


= 2.0.12 =
Initial public release 

= 1.0.10 =
* Initial release

== Upgrade Notice ==

= 1.0.10 =
Initial release

== Use ==

[For complete instructions, visit VoordeMensen's support page](https://voordemensen.nl/support)
