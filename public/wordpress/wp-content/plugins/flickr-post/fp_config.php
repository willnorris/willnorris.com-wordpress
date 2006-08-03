<?php



/* Tell WordPress to add the configuration page */

add_action( 'admin_menu', 'fp_config_page' );

/*
 * fp_config_page
 *
 * Adds a configuration page to the plugins section of the admin
 * dashboard.
 */

function fp_config_page () {
  
  add_submenu_page( 'plugins.php', 'flickr-post configuration',
   'flickr-post', 1, __FILE__, 'fp_configuration' );
}



/*
 * fp_configuration
 *
 * This function prints out the PHP code that comprises the
 * configuration page.
 */

function fp_configuration () {

  if ( ! function_exists( 'url_cache' ) && ! $_REQUEST['fp_action'] )
    fp_error( 'This plugin requires the <a href="http://mcnicks.org/wordpress/url-cache/">url-cache</a> plugin in order to function' );
 
  if ( $_REQUEST['fp_action'] == "set" ) {

    if ( $error = fp_save_settings() )
      fp_error( $error );
    else
      fp_message( "Settings saved" );
  }
    
  include( 'fp_settings_template.php' );
}



/*
 * fp_load_settings
 *
 * Saves the current settings from the WordPress options into
 * $_REQUEST parameters, so that they are available for the HTML
 * forms.
 */

function fp_load_settings () {

  $_REQUEST['flickr_username'] = get_option( "fp_flickr_username" );
  $_REQUEST['timeout'] = get_option( "fp_timeout" );
  $_REQUEST['recent'] = get_option( "fp_recent" );
  $_REQUEST['image_class'] = get_option( "fp_image_class" );

  if ( ! $_REQUEST['timeout'] )
    $_REQUEST['timeout'] = 3600;

  if ( ! $_REQUEST['recent'] )
    $_REQUEST['recent'] = 10;
}



/*
 * fp_save_settings
 *
 * Saves the current settings as WordPress options.
 */

function fp_save_settings () {
  global $_REQUEST;

  update_option( "fp_flickr_username", $_REQUEST['flickr_username'] );
  update_option( "fp_timeout", $_REQUEST['timeout'] );
  update_option( "fp_recent", $_REQUEST['recent'] );
  update_option( "fp_image_class", $_REQUEST['image_class'] );
}



/*
 * fp_message
 *
 * Prints the given message in an HTML div tag.
 */

function fp_message ( $message = "" ) {

  if ( $message )
    echo "<div id=\"message\" class=\"updated fade\"><p>$message</p></div>\n";
}



/*
 * fp_error
 *
 * Prints the given error message in an HTML div tag.
 */

function fp_error ( $message = "" ) {

  if ( $message )
    echo "<div id=\"message\" class=\"error\"><p>$message</p></div>\n";
}



/*
 * fp_form_action
 *
 * Returns the URL of the current page, to be used as the action parameter
 * in forms.
 */

function fp_form_action () {

  if ( $page = $_REQUEST['page'] )
    return "?page=$page";
}

?>
