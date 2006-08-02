<?php

/*

Plugin Name: URL Cache
Version: 1.3
Plugin URI: http://mcnicks.org/wordpress/url-cache/
Description: Given a URL, the url_cache() function will attempt to download the file it represents and return a URL pointing to this locally cached version.
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



/* The user agent to use for URL transfers */

$url_cache_ua = "url-cache 1.2 -- http://mcnicks.org/wordpress/url-cache/";



/*
 * url_cache
 *
 *  $url - the remote URL to cache
 *  $username, $password - optional authentication parameters
 *  $timeout - how old the cache can be before being considered stale.
 *
 * Attempts to access the given URL and download its contents to a
 * local cache, and then returns a URL to that locally cached copy.
 * If the download fails, the remote URL is returns so, from the 
 * calling function's point-of-view, the function is transparent.
 *
 */

function url_cache ( $url, $username = "", $password = "", $timeout = 3600 ) {

  // Return if no URL was given.

  if ( $url == "" ) return;

  // Return the local URL if the file is currently cached.

  if ( uc_is_cached( $url, $timeout ) )
    return uc_get_local_url( $url );

  // Attempt to cache the file locally.
    
  $contents = uc_get_contents( $url, $username, $password );

  if ( $contents )
    uc_cache_contents( $url, $contents );

  // If we reach this point, then an attempt has been made to
  // cache the file locally. We can check whether the local
  // file is valid to determine which URL to return.

  if ( uc_is_cached( $url, $timeout ) )
    return uc_get_local_url( $url );
  else
    return $url;
}
  


/*
 * content_cache
 *
 *  $url - the remote URL to cache
 *  $username, $password - optional authentication parameters
 *  $timeout - how old the cache can be before being considered stale.
 *
 * This operates like url_cache, except that it returns the content
 * of the URL itself rather than a URL to a locally cached version
 * of the content.
 */

function content_cache ( $url, $username = "", $password = "", $timeout = 3600 ) {

  // Return if no URL was given.

  if ( $url == "" ) return;

  // Return the local URL if the file is currently cached.

  if ( uc_is_cached( $url, $timeout ) )
    return uc_get_cached_contents( $url );

  // Attempt to get the contents of the URL.
    
  $contents = uc_get_contents( $url, $username, $password );

  // Cache the new contents if successful. If not, try to get
  // the old contents from the cache, even though they are out
  // of date.

  if ( $contents ) 
    uc_cache_contents( $url, $contents );
  else
    $contents = uc_get_cached_contents( $url );

  // If we reach this point, then an attempt has been made to
  // cache the file locally. Either way, we can simply return
  // the contents that we have already fetched.

  return $contents;
}



/*
 * xml_cache
 *
 *  $url - the remote URL to cache
 *  $username, $password - optional authentication parameters
 *  $timeout - how old the cache can be before being considered stale.
 *
 * This is a wrapper around content_cache that assumes the content of
 * the remote URL to be XML. After fetching the content, it converts
 * the XML into a tree structure and returns it.
 */

function xml_cache ( $url, $username = "", $password = "", $timeout = 3600 ) {

  // Get the contents of the URL as usual.

  $contents = content_cache( $url, $username, $password, $timeout );

  // Parse the contents as XML.

  $output = array();

  $parser = xml_parser_create();

  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
  xml_parse_into_struct($parser, $contents, $values, $tags);
  xml_parser_free($parser);

  return $values;
}



/*
 * uc_get_local_file
 *
 *  $url - the remote URL whose local cache file we want to locate.
 *
 * Returns the name of the local cache file associated with the given
 * remote URL. The file name is created by hashing the URL so it should
 * be unique.
 */

function uc_get_local_file ( $url ) {

  // Work out where the local file should be placed.

  $cache_dir = ABSPATH . "wp-content/cache/";

  // Calculate a hash of the remote URL and extract the file extension. 

  $hash = md5( $url );
  $extension = "bin";

  if ( preg_match( '/\.([A-Za-z]+)$/', $url, $matches ) )
    $extension = $matches[1];

  // Return the absolute path to the local file.

  return $cache_dir . "uc.$hash.$extension";
}



/*
 * uc_get_local_url
 *
 *  $url - the remote URL whose locally cached URL we want.
 *
 * Returns the locally cached URL associated with the given remote URL.
 */

function uc_get_local_url ( $url ) {

  // Work out the base of the local URL.

  $cache_url = get_option( 'siteurl' ) . "/wp-content/cache/";

  // Calculate a hash of the remote URL and extract the file extension. 

  $hash = md5( $url );
  $extension = "bin";

  if ( preg_match( '/\.([A-Za-z]+)$/', $url, $matches ) )
    $extension = $matches[1];

  // Return the local URL.

  return $cache_url . "uc.$hash.$extension";
}



/*
 * uc_is_cached
 *
 *  $url - the remote URL we are interested in.
 *  $timeout - how old the cache can be before being considered stale.
 *
 * Returns true if the remote URL is locally cached and if the cache
 * is still valid.
 */

function uc_is_cached ( $url, $timeout ) {

  $local_file = uc_get_local_file( $url );
  $expiry_time = @filemtime( $local_file ) + $timeout;

  return @file_exists( $local_file ) && $expiry_time > time();
}



/*
 * uc_cache_contents
 *
 *  $url - the remote URL to associate the cached contents with.
 *  $contents - the contents we wish to cache.
 *
 * Associates the contents with the URL in the cache. This assumes
 * that the given contents have already been fetched from the URL.
 */

function uc_cache_contents( $url, $contents ) {

  if ( $local = fopen( uc_get_local_file( $url ), "wb" ) ) {

    fwrite( $local, $contents );
    fclose( $local );
  }
}



/*
 * uc_get_contents
 *
 *  $url - the URL to fetch.
 *  $username, $password  - the authentication details to use.
 *
 *  Returns the contents of the given URL.
 */

function uc_get_contents ( $url, $username, $password ) {
  global $url_cache_ua;
 
  $handle = curl_init( $url );

  if ( $username && $password )
    curl_setopt( $handle, CURLOPT_USERPWD, "$username:$password" );

  curl_setopt( $handle, CURLOPT_RETURNTRANSFER, 1 );
  curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 1 );
  curl_setopt( $handle, CURLOPT_TIMEOUT, 4 );
  curl_setopt( $handle, CURLOPT_USERAGENT, $url_cache_ua );

  $buffer = curl_exec( $handle );

  curl_close( $handle );

  return $buffer;
}



/*
 * uc_get_cached_contents
 *
 *  $url - the URL we are interested in.
 *
 * Returns the content of the locally cached version of the given
 * URL.
 */

function uc_get_cached_contents ( $url ) {

  if ( $cache = @fopen( uc_get_local_file( $url ), "r" ) ) {

    $contents = "";

    while ( $part = @fread( $cache, 8192 ) )
      $contents .= $part;

    @fclose( $cache ); 

    return $contents;
  }
}



/* 
 * uc_get_rest_response
 *  - $method is the REST method to be cached
 *  - $slug is used to make the cached response more unique
 *  - (optional) $timeout is a specific timeout value to use
 *  - (optional) $any will return any available cached
 *    response, regardless of how stale it is.
 *
 * This function uses $method and $cache to make up a unique filename
 * which is used to store REST responses. If the file associated with
 * $request and $slug exists, its contents are returned. If the file
 * is stale, then nothing is returned, which should prompt a refresh.
 */

function uc_get_rest_response( $method, $slug, $timeout = 0, $any = 0 ) {
  global $cache_dir;

  // Return if the method and slug are not specified.

  if ( ! $method || ! $slug ) return;

  // Set the timeout to the default value if none has been specified.

  if ( ! $timeout )
    $timeout = 3600;

  // Work out the file name that the response should be cached in.

  $filename = $cache_dir."rest--$method--$slug";

  // Return if the file does not exist.

  if ( ! @file_exists( $filename ) ) return;

  // Check whether the cached response is stale, unless we have been
  // told to return anything that is available.

  if ( ! $any )
    if ( ( @filemtime( $filename ) + $timeout ) < ( time() ) )
      return;

  // Otherwise, open it and return the contents.

  $handle = @fopen( $filename, "r" );

  if ( $handle ) {

    $cached_response = "";

    while ( $part = @fread( $handle, 8192 ) ) {
      $cached_response .= $part;
    }

    @fclose( $handle );

    return $cached_response;
  }
}



/*
 * uc_cache_rest_response
 *  - $method is the REST method to be cached
 *  - $slug is used to make the cached response more unique
 *  - $response is the actual response that we should cache
 *
 * This function uses $method and $cache to make up a unique filename
 * which is used to store REST responses. When called, the function
 * writes the given response to that filename.
 */

function uc_cache_rest_response( $method, $slug, $response ) {
  global $cache_dir;

  // Return if the arguments are not specified.

  if ( ! $method || ! $slug  || ! $response ) return;

  // Work out the file name that the response should be cached in.

  $filename = $cache_dir."rest--$method--$slug";

  // Open it for writing and dump the response in.

  $handle = @fopen( $filename, "w+" );

  if ( $handle ) {

    @fwrite( $handle, $response );
    @fclose( $handle );
  }

  // That almost seemed too simple.
}



/*
 * uc_cache_value
 *  - $name the name of the value to cache
 *  - $value the value itself
 *
 * This function uses the standard caching functions above to cache
 * name/value pairs. At the moment this is a bit of a fudge, making
 * use of the special method, "value". */

function uc_cache_value( $name, $value ) {
  return uc_cache_rest_response( "value", $name, $value );
}



/*
 * uc_get_cached_value
 *  - $name the name of the value to cache
 *  - returns the cached value associated with $name
 *
 * This function uses the standard caching functions above to return
 * a previously cached value associated with a name/value pair. At the
 * moment this is a bit of a fudge, making use of the special method,
 * "value".
 */

function uc_get_value( $name, $timeout = 0 ) {
  return uc_get_rest_response( "value", $name, $timeout );
}

?>
