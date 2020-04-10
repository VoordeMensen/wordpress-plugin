<?php
/**
 * @internal never define functions inside callbacks.
 * these functions could be run multiple times; this would result in a fatal error.
 */
 
/**
 * custom option and settings
 */
function vdm_settings_init() {
 // register a new setting for "vdm" page
 register_setting( 'vdm', 'vdm_options' );
 register_setting( 'vdm', 'vdm_client_shortname' );

 // register a new section in the "vdm" page
 add_settings_section(
 'vdm_section_developers',
 __( '', 'vdm' ),
 'vdm_section_developers_cb',
 'vdm'
 );

add_settings_field(
    'vdm_client_shortname',
    __( 'Klantnaam', 'vdm' ),
    'vdm_client_shortname_cb',
    'vdm',
    'vdm_section_developers'
);
 // register a new field in the "vdm_section_developers" section, inside the "vdm" page
 add_settings_field(
 'vdm_loader_type', // as of WP 4.6 this value is used only internally
 // use $args' label_for to populate the id inside the callback
 __( 'Loader type', 'vdm' ),
 'vdm_loader_type_cb',
 'vdm',
 'vdm_section_developers',
 [
 'label_for' => 'vdm_loader_type',
 'class' => 'vdm_row',
 'vdm_custom_data' => 'custom',
 ]
 );
}
 
/**
 * register our vdm_settings_init to the admin_init action hook
 */
add_action( 'admin_init', 'vdm_settings_init' );
 
/**
 * custom option and settings:
 * callback functions
 */
 
// developers section cb
 
// section callbacks can accept an $args parameter, which is an array.
// $args have the following keys defined: title, id, callback.
// the values are defined at the add_settings_section() function.
function vdm_section_developers_cb( $args ) {
 ?>
 <!-- <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Follow the white rabbit.', 'vdm' ); ?></p> -->
 <?php
}
 
 function vdm_client_shortname_cb( $args ) {
	 $setting = wp_strip_all_tags(get_option( 'vdm_client_shortname' ));
	?>
    <input type="text" name="vdm_client_shortname" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
    <?php	 

 }
// pill field cb
 
// field callbacks can accept an $args parameter, which is an array.
// $args is defined at the add_settings_field() function.
// wordpress has magic interaction with the following keys: label_for, class.
// the "label_for" key value is used for the "for" attribute of the <label>.
// the "class" key value is used for the "class" attribute of the <tr> containing the field.
// you can add custom key value pairs to be used inside your callbacks.
function vdm_loader_type_cb( $args ) {
 // get the value of the setting we've registered with register_setting()
 $options = get_option( 'vdm_options' );
 // output the field
 ?>
 <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
 data-custom="<?php echo esc_attr( $args['vdm_custom_data'] ); ?>"
 name="vdm_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
 >
 <option value="popup" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'popup', false ) ) : ( '' ); ?>>
 <?php esc_html_e( 'popup', 'vdm' ); ?>
 </option>
 <option value="side" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'side', false ) ) : ( '' ); ?>>
 <?php esc_html_e( 'side', 'vdm' ); ?>
 </option>
 </select>
 <p class="description">Kies de manier waarop de kaartverkoop op je site getoond wordt, met een popup-overlay of aan de zijkant van het scherm</p>
 <?php
}
 
/**
 * top level menu
 */
function vdm_options_page() {
 // add top level menu page
 add_menu_page(
 'VoordeMensen instellingen',
 'VoordeMensen',
 'manage_options',
 'vdm',
 'vdm_options_page_html',
 'dashicons-tickets'
 );
}
 
/**
 * register our vdm_options_page to the admin_menu action hook
 */
add_action( 'admin_menu', 'vdm_options_page' );
 
/**
 * top level menu:
 * callback functions
 */
function vdm_options_page_html() {
 // check user capabilities
 if ( ! current_user_can( 'manage_options' ) ) {
 return;
 }
 
 // add error/update messages
 
 // check if the user have submitted the settings
 // wordpress will add the "settings-updated" $_GET parameter to the url
 if ( isset( $_GET['settings-updated'] ) ) {
 // add settings saved message with the class of "updated"
 add_settings_error( 'vdm_messages', 'vdm_message', __( 'Opgeslagen', 'vdm' ), 'updated' );
 }
 
 // show error/update messages
 settings_errors( 'vdm_messages' );
 ?>
 <div class="wrap">
 <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
 <form action="options.php" method="post">
 <?php
 // output security fields for the registered setting "vdm"
 settings_fields( 'vdm' );
 // output setting sections and their fields
 // (sections are registered for "vdm", each field is registered to a specific section)
 do_settings_sections( 'vdm' );
 // output save settings button
 submit_button( __('Opslaan') );
 ?>
 </form>
 </div>
 <?php
}