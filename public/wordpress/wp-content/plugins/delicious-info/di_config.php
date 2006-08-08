<?php



// This is the default URLs for the del.icio.us APIs.

define( TAG_URL, 'http://del.icio.us/api/tags/get' );
define( RECENT_POST_URL, 'http://del.icio.us/api/posts/recent' );



/* Tell WordPress to add the configuration page */

add_action( 'admin_menu', 'di_config_page' );

/*
 * di_config_page
 *
 * Adds a configuration page to the plugins section of the admin
 * dashboard.
 */

function di_config_page () {
  
  add_submenu_page( 'plugins.php', 'Del.icio.us Info Configuration',
   'delicious-info', 1, __FILE__, 'di_configuration' );
}



/*
 * di_configuration
 *
 * This function prints out the PHP code that comprises the
 * configuration page.
 */

function di_configuration () {

  if ( ! function_exists( 'url_cache' ) && ! $_REQUEST['di_action'] )
    di_error( 'This plugin requires the <a href="http://mcnicks.org/wordpress/url-cache/">url-cache</a> plugin in order to function' );
 
  switch ( $_REQUEST['di_action'] ) {

  case 'set':

    if ( $error = di_save_settings() )
      di_error( $error );
    else
      di_message( "Settings saved" );

    break;

  case 'setapi':

    if ( $error = di_save_api_settings() )
      di_error( $error );
    else
      di_message( "API URLs saved" );

    break;
  }
    
  include( 'di_settings_template.php' );
}



/*
 * di_load_settings
 *
 * Saves the current settings from the WordPress options into
 * $_REQUEST parameters, so that they are available for the HTML
 * forms.
 */

function di_load_settings () {

  $_REQUEST['delicious_username'] = get_option( "di_delicious_username" );
  $_REQUEST['delicious_password'] = get_option( "di_delicious_password" );
  $_REQUEST['recent'] = get_option( "di_recent" );
  $_REQUEST['timeout'] = get_option( "di_timeout" );
  $_REQUEST['delicious_tag_url'] = get_option( "di_delicious_tag_url" );
  $_REQUEST['delicious_post_url'] = get_option( "di_delicious_post_url" );

  // Set defaults if no values have been entered yet.

  if ( ! $_REQUEST['recent'] ) $_REQUEST['recent'] = 10;
  if ( ! $_REQUEST['timeout'] ) $_REQUEST['timeout'] = 3600;
  if ( ! $_REQUEST['delicious_tag_url'] ) $_REQUEST['delicious_tag_url'] = TAG_URL;
  if ( ! $_REQUEST['delicious_post_url'] ) $_REQUEST['delicious_post_url'] = RECENT_POST_URL;
}



/*
 * di_save_settings
 *
 * Saves the current settings as WordPress options.
 */

function di_save_settings () {
  global $_REQUEST;

  update_option( "di_delicious_username", $_REQUEST['delicious_username'] );
  update_option( "di_delicious_password", $_REQUEST['delicious_password'] );
  update_option( "di_recent", $_REQUEST['recent'] );
  update_option( "di_timeout", $_REQUEST['timeout'] );
}



/*
 * di_save_api_settings
 *
 * Saves the delicious API URLs as WordPress options.
 */

function di_save_api_settings () {
  global $_REQUEST;

  update_option( "di_delicious_tag_url", $_REQUEST['delicious_tag_url'] );
  update_option( "di_delicious_post_url", $_REQUEST['delicious_post_url'] );
}



/*
 * di_message
 *
 * Prints the given message in an HTML div tag.
 */

function di_message ( $message = "" ) {

  if ( $message )
    echo "<div id=\"message\" class=\"updated fade\"><p>$message</p></div>\n";
}



/*
 * di_error
 *
 * Prints the given error message in an HTML div tag.
 */

function di_error ( $message = "" ) {

  if ( $message )
    echo "<div id=\"message\" class=\"error\"><p>$message</p></div>\n";
}



/*
 * di_form_action
 *
 * Returns the URL of the current page, to be used as the action parameter
 * in forms.
 */

function di_form_action () {

  if ( $page = $_REQUEST['page'] )
    return "?page=$page";
}

?>
