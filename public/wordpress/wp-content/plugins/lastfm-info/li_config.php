<?php



/* Tell WordPress to add the configuration page */

add_action( 'admin_menu', 'li_config_page' );



/*
 * li_config_page
 *
 * Adds a configuration page to the plugins section of the admin
 * dashboard.
 */

function li_config_page () {
  
  add_submenu_page( 'plugins.php', 'Last.fm Info Configuration',
   'lastfm-info', 1, __FILE__, 'li_configuration' );
}



/*
 * li_configuration
 *
 * This function prints out the PHP code that comprises the
 * configuration page.
 */

function li_configuration () {

  if ( ! function_exists( 'url_cache' ) && ! $_REQUEST['li_action'] )
    li_error( 'This plugin requires the <a href="http://mcnicks.org/wordpress/url-cache/">url-cache</a> plugin in order to function' );
 
  if ( $_REQUEST['li_action'] == "set" ) {

    if ( $error = li_save_settings() )
      li_error( $error );
    else
      li_message( "Settings saved" );
  }
    
  include( 'li_settings_template.php' );
}



/*
 * li_load_settings
 *
 * Saves the current settings from the WordPress options into
 * $_REQUEST parameters, so that they are available for the HTML
 * forms.
 */

function li_load_settings () {

  $_REQUEST['lastfm_username'] = get_option( "li_lastfm_username" );
  $_REQUEST['tracks'] = get_option( "li_tracks" );
  $_REQUEST['timeout'] = get_option( "li_timeout" );

  if ( ! $_REQUEST['tracks'] ) $_REQUEST['tracks'] = 10;
  if ( ! $_REQUEST['timeout'] ) $_REQUEST['timeout'] = 600;
}



/*
 * li_save_settings
 *
 * Saves the current settings as WordPress options.
 */

function li_save_settings () {
  global $_REQUEST;

  update_option( "li_lastfm_username", $_REQUEST['lastfm_username'] );
  update_option( "li_tracks", $_REQUEST['tracks'] );
  update_option( "li_timeout", $_REQUEST['timeout'] );
}



/*
 * li_message
 *
 * Prints the given message in an HTML div tag.
 */

function li_message ( $message = "" ) {

  if ( $message )
    echo "<div id=\"message\" class=\"updated fade\"><p>$message</p></div>\n";
}



/*
 * li_error
 *
 * Prints the given error message in an HTML div tag.
 */

function li_error ( $message = "" ) {

  if ( $message )
    echo "<div id=\"message\" class=\"error\"><p>$message</p></div>\n";
}



/*
 * li_form_action
 *
 * Returns the URL of the current page, to be used as the action parameter
 * in forms.
 */

function li_form_action () {

  if ( $page = $_REQUEST['page'] )
    return "?page=$page";
}

?>
