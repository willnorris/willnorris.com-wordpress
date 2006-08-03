<?php

/*

Plugin Name: Flickr Post
Version: 1.4
Plugin URI: http://mcnicks.org/wordpress/flickr-post/
Description: Automatically includes specially tagged Flickr photographs in WordPress posts. For a photo to appear, it must be tagged with the word 'wordpress' and with the post slug of the post. Inspired by Ramon Darrow's flickr-gallery plugin.
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



/* Include the configuration functions. */

include_once( "fp_config.php" );

/* Used to find the user_id that matches the given username. */

define( 'FP_PHOTO_URL', 'http://flickr.com/photos/' );

/* Used to issue REST requests against the Flickr web site. */

define( 'FP_REST_URL', 'http://flickr.com/services/rest/' );

/*
 * The Flickr API key for this plugin. Do not change this, and do not
 * reuse it elsewhere. If you need an API key to build a WordPress plugin,
 * you can have one assigned by following the instructions at:
 *
 *   http://flickr.com/services/api/misc.api_keys.html
 */

define( 'FP_API_KEY', '1244ae1e42c346543747aaec524cbe7a' );



/* Add the flickr-post filter to the content and excerpt actions. */

add_action( 'the_content', 'fp_add_photos', 0 );
add_action( 'the_excerpt', 'fp_add_photos', 0 );



/*
 * fp_add_photos
 *
 *  $content - contains the existing content of the post.
 *
 * This is the filter that adds the photos to the top of the content
 * of the post. Post contents and excerpts are filtered through this
 * function.
 */

function fp_add_photos ( $content ) {
  global $id; // The current post ID as defined by WordPress.

  // Get the Flickr user ID and the slug of the current post.

  $user_id = fp_get_user_id();
  $slug = fp_get_the_slug();
  
  // Get the photos.

  list( $ids, $uris, $titles ) = fp_get_photos( $user_id, $slug, $id );

  if ( ! $ids ) return $content;

  // Construct the HTML.

  $fp_photos = '<div class="flickr-post">';

  $class = get_option( 'fp_image_class' );

  foreach ( $ids as $id ) {

    $uri = $uris[$id];
    $title = $titles[$id];
    $thumbnail = url_cache( $uri.'_s.jpg' );
    $image = $uri.'_o.jpg';

    $fp_photos .= '<a href="'.$image.'" rel="bookmark" title="'.$title.'">';
    $fp_photos .= "<img";

    if ( $class )
      $fp_photos .= " class=\"$class\"";

    $fp_photos .= ' src="'.$thumbnail.'" alt="'.$title.'"/>';


    $fp_photos .= '</a>';
  }

  $fp_photos .= '</div>';

  return $fp_photos . $content;
}



/*
 * get_recent_flickr_photos
 *
 * Prints HTML containing thumbnail images of recent photos.
 */

function get_recent_flickr_photos () {

  // Get the user ID.

  $user_id = fp_get_user_id();

  // Get the number of recent photos to display from WordPress options.

  $number = get_option( 'fp_recent' );

  // Make the REST request for recent photos.

  $response = fp_rest_request( "flickr.people.getPublicPhotos",
   "user_id=$user_id&per_page=$number" );

  if ( ! $response ) return;

  $class = get_option( 'fp_image_class' );

  echo "<div class=\"flickr-post\">\n";
 
  foreach ( $response as $order => $tag ) {
    if ( $tag['tag'] == 'photo' ) {

      $ph_server = $tag['attributes']['server'];
      $ph_id = $tag['attributes']['id'];
      $ph_secret = $tag['attributes']['secret'];
      $ph_ispublic = $tag['attributes']['ispublic'];
      $ph_server = $tag['attributes']['server'];
      $ph_title = $tag['attributes']['title'];
    
      if ( $ph_ispublic ) {

        $url = "http://static.flickr.com/$ph_server/";
        $url .= $ph_id . "_" . $ph_secret;
      
        $thumbnail = url_cache( $url . "_s.jpg" );
        $image = $url . "_o.jpg";
      
        echo "<a href=\"$image\"";
        echo " rel=\"bookmark\" title=\"$ph_title\">";
        echo "<img";

        if ( $class )
          echo " class=\"$class\"";

        echo " src=\"$thumbnail\" alt=\"$ph_title\"/>";
        echo "</a>\n";
      }
    }
  }

  echo "</div>\n";

  return $fp_photos;
}



/* 
 * fp_get_user_id
 *
 * Returns the user ID associated with the predefined username
 */

function fp_get_user_id () {

  $username = get_option( 'fp_flickr_username' );

  $response = fp_rest_request( "flickr.urls.lookupUser",
   "url=" . FP_PHOTO_URL.$username );

  return $response['1']['attributes']['id'];
}



/*
 * fp_get_photos
 *
 *  $user_id - the ID of the user whose Flickr album we are displaying.
 *  $slug - the slug of the current post.
 *  $post_id - the ID of the current post.
 *
 * This function returns the front section of the URL for each photo
 * that matches the given user_id and slug. The partial URL can be suffixed
 * with the appropriate ending to refer to the actual thumbnail or full
 * photo.
 */

function fp_get_photos ( $user_id, $slug, $post_id ) {

  // Return if the user_id and slug are not specified.

  if ( ! $user_id || ! $slug ) return;

  // Make a REST request for the photos.

  $clean_slug = preg_replace( "/[^a-z\d]/", "", $slug ); 
  $tags = "wp$clean_slug,wp$post_id";

  $response = fp_rest_request( "flickr.photos.search",
   "user_id=$user_id&tags=$tags" );

  $ids = array();
  $titles = array();
  $urls = array();

  if ( ! $response ) return;
 
  foreach ( $response as $order => $tag ) {
    if ( $tag['tag'] == 'photo' ) {

      $ph_server = $tag['attributes']['server'];
      $ph_id = $tag['attributes']['id'];
      $ph_secret = $tag['attributes']['secret'];
      $ph_ispublic = $tag['attributes']['ispublic'];
      $ph_server = $tag['attributes']['server'];
      $ph_title = $tag['attributes']['title'];
    
      if ( $ph_ispublic ) {

        array_unshift( $ids, $ph_id );

        $urls[$ph_id] = "http://static.flickr.com/$ph_server/";
        $urls[$ph_id] .= $ph_id . "_" . $ph_secret;

        $titles[$ph_id] = $ph_title;
      }
    }
  }

  return array( $ids, $urls, $titles );
}



/*
 * fp_rest_request
 *
 *  $method - is the Flickr REST method which should be called.
 *  $args - specified the arguments that should be sent.
 *  $timeout - an optional timeout period for the cache.
 *
 * This function uses the url_cache xml_cache() function to fetch
 * the result of a Flickr REST request based on the specified method
 * and arguments. 
 */

function fp_rest_request ( $method, $args, $timeout = "" ) {

	// Set the timeout if necessary.

	if ( ! timeout )
		$timeout = get_option( 'fp_timeout' );

  // Work out the REST URL.

  $url = FP_REST_URL."?method=$method&api_key=".FP_API_KEY."&$args";

  // Issue the request and return the response.

  return xml_cache( $url, $timeout );
}



/*
 * fp_get_the_slug
 *
 * Returns the slug associated with the current post. This is
 * borrowed from the code for get_the_title in:
 *
 *   wp-includes/template-functions-post.php
 */

function fp_get_the_slug () {
  global $id, $wpdb;

  return $wpdb->get_var( "SELECT post_name FROM $wpdb->posts WHERE ID = $id" );
}

?>
