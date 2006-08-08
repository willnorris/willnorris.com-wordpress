<?php

/*

Plugin Name: Del.icio.us Info
Version: 1.1
Plugin URI: http://mcnicks.org/wordpress/delicious-info/
Description: Defines functions that display cached versions of del.icio.us tags and recently posted links.
Author: David McNicol
Author URI: http://mcnicks.org/

Copyright (c) 2005
Released under the GPL license
http://www.gnu.org/licenses/gpl.txt

This file is part of WordPress.
WordPress is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/



// Include the configuration functions.

include_once( 'di_config.php' );



/*
 * delicious_tagcloud
 *
 * prints a tagcloud of del.icio.us links.
 */

function delicious_tagcloud () {

  // Fetch the username, password and API URL from WordPress options.

  $username = get_option( 'di_delicious_username' );
  $password = get_option( 'di_delicious_password' );
  $tag_url = get_option( 'di_delicious_tag_url' );
  $timeout = get_option( 'di_timeout' );
  
  if ( ! $username || ! $password )
    return;

  // Get the tag information from del.icio.us.

  $xml = xml_cache( $tag_url, $username, $password, $timeout );

  // Get them into an array.

  $tags = array();
  $min = 1000000; // Bit of an assumption but, hey, whatever.
  $max = 0;
  
  foreach ( $xml as $element ) {

    if ( $element['tag'] == 'tag' ) {
      
      $tag = $element['attributes']['tag'];
      $count = $element['attributes']['count'];

      if ( $count < $min ) $min = $count;
      if ( $count > $max ) $max = $count;

      $tags[$tag] = $count;
    }
  } 

  // Work out the ratio of font sizes.

  $scope = $max - $min;

  // Print out the del.icio.us tags as links.

  echo "<div class=\"tagcloud\">\n";

  foreach ( $tags as $tag => $count ) {

    $font_size = (( $count - $min ) * 100 / $scope ) + 100;
    
    echo "<a href=\"http://del.icio.us/$username/$tag\"";
    echo " style=\"font-size: $font_size%;\">$tag</a>\n";
  }

  echo "</div>\n";
}



/*
 * delicious_recent_links
 *
 * Prints the most recently added links.
 */

function delicious_recent_links ( $before = "<li>", $after = "</li>", $dateformat = "j F Y \a\\t G:i" ) {

  // Fetch the username, password and API URL from WordPress options.

  $username = get_option( 'di_delicious_username' );
  $password = get_option( 'di_delicious_password' );
  $post_url = get_option( 'di_delicious_post_url' );
  $timeout = get_option( 'di_timeout' );
  $count = get_option( 'di_recent' );
  
  if ( ! $username || ! $password )
    return;

  // Get the recent post information from del.icio.us.

  $post_url .= "?count=$count";

  $xml = xml_cache( $post_url, $username, $password, $timeout );

  // Print them out.

  foreach ( $xml as $element ) {

    if ( $element['tag'] == 'post' ) {
      
      $href = $element['attributes']['href'];
      $desc = $element['attributes']['description'];
      $time = $element['attributes']['time'];

      echo $before;
      echo "<a href=\"$href\">$desc</a> ";
      echo "<span class=\"more\">linked ";

      if ( function_exists( "time_since" ) )
        echo time_since( abs( strtotime( $time ) ) ) . " ago";
      else
        echo "on " . date( $dateformat, abs( strtotime( $time ) ) );

      echo "</span>";
      echo $after;
      echo "\n";
    }
  }
}

?>
