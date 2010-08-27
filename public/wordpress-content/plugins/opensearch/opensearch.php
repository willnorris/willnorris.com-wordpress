<?php
/*
Plugin Name: OpenSearch
Plugin URI: http://wordpress.org/extend/plugins/opensearch/
Description: Add OpenSearch discovery and querying to your WordPress site.
Version: 1.0
Author: Jeff Waugh
Author URI: http://bethesignal.org/
*/

/*
Copyright (C) Jeff Waugh <http://bethesignal.org/>

This program is free software; you can redistribute it and/or modify
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

define('OPENSEARCH_NS', 'http://a9.com/-/spec/opensearch/1.1/');
define('OPENSEARCH_TYPE', 'application/opensearchdescription+xml');

function opensearch_url() {
	$url = trailingslashit(get_bloginfo('url'));

	if ( get_option('permalink_structure') == '' ) {
		$url .= '?opensearch_osd=1';
	} else {
		$url .= 'osd.xml';
	}

	return $url;
}

function opensearch_head() {
	if ( get_option('permalink_structure') == '' ) {
		$osd_url = '?opensearch_osd=1';
	} else {
		$osd_url = 'osd.xml';
	}
?>
	<link rel="search" type="application/opensearchdescription+xml" href="<?php bloginfo('url'); ?>/<?php echo $osd_url; ?>" title="<?php bloginfo('name'); ?>" />
<?php }

function opensearch_query_vars($vars) {
	$vars[] = 'opensearch_osd';
	return $vars;
}

function opensearch_flush_rewrite_rules() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

function opensearch_rewrite_rules($wp_rewrite) {
	global $wp_rewrite;
	$rules = array(
		'osd.xml$' => $wp_rewrite->index . '?opensearch_osd=1'
	);
	$wp_rewrite->rules = $rules + $wp_rewrite->rules;
}

function opensearch_canonical($redirect_url, $requested_url) {
	if ( substr($requested_url, -7) == 'osd.xml' )
		return false;
	return $redirect_url;
}

function opensearch_osd() {
	global $wp_query;

	if ( ! empty($wp_query->query_vars['opensearch_osd']) ) {
		header('Content-Type: application/opensearchdescription+xml');
		echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		echo '<OpenSearchDescription xmlns="' . esc_attr(OPENSEARCH_NS) . '">' . "\n";

		$shortname = apply_filters('opensearch_shortname', get_bloginfo('name'));
		if ($shortname) echo '  <ShortName>' . htmlentities2($shortname) . '</ShortName>' . "\n";

		$longname = apply_filters('opensearch_longname', get_bloginfo('name'));
		if ($longname) echo '  <LongName>' . htmlentities2($longname) . '</LongName>' . "\n";

		$description = apply_filters('opensearch_description', 'Search &#x201C;' . get_bloginfo('name') . '&#x201D;');
		if ($description) echo '  <Description>' . htmlentities2($description) . '</Description>' . "\n";

		$contact = apply_filters('opensearch_contact', get_bloginfo('admin_email'));
		if ($contact) echo '  <Contact>' . htmlentities2($contact) . '</Contact>' . "\n";

		$urls = array(
			'html' => array( 'type' => 'text/html', 'template' => get_bloginfo('url') . '/?s={searchTerms}' ),
			'atom' => array( 'type' => 'text/atom+xml', 'template' => get_bloginfo('url') . '/?feed=atom&amp;s={searchTerms}' ),
			'rss' => array( 'type' => 'text/rss+xml', 'template' => get_bloginfo('url') . '/?feed=rss2&amp;s={searchTerms}' ),
		);
		$urls = apply_filters('opensearch_urls', $urls);

		foreach ($urls as $id => $attributes) {
			echo '  <Url';
			foreach ($attributes as $name => $value) {
				echo ' ' . $name . '="' . esc_attr($value) . '"';
			}
			echo '>' . "\n";

		}

		$favicon = (file_exists(ABSPATH . 'favicon.ico') || file_exists(dirname(ABSPATH) . '/favicon.ico' )) 
			? get_bloginfo('url') . '/favicon.ico' : '';
		$favicon = apply_filters('opensearch_favicon', $favicon);
		if ($favicon) echo '  <Image height="16" width="16" type="image/vnd.microsoft.icon">' . clean_url($favicon) . '</Image>' . "\n";

		$language = (WPLANG != '') ? get_bloginfo('language') : '';
		$language = apply_filters('opensearch_language', $language);
		if ($language) echo '  <Language>' . htmlentities2($langauge) . '</Language>' . "\n";

		$developer = apply_filters('opensearch_developer', 'WordPress OpenSearch Plugin');
		if ($language) echo '  <Developer>' . htmlentities2($developer) . '</Developer>' . "\n";

		$output_encoding = apply_filters('opensearch_output_encoding', get_bloginfo('charset'));
		if ($output_encoding) echo '  <OutputEncoding>' . htmlentities2($output_encoding) . '</OutputEncoding>' . "\n";

		$input_encoding = apply_filters('opensearch_input_encoding', get_bloginfo('charset'));
		if ($input_encoding) echo '  <InputEncoding>' . htmlentities2($input_encoding) . '</InputEncoding>' . "\n";

		do_action('opensearch_document');

		echo '</OpenSearchDescription>';

		exit;
	}
	return;
}

add_filter('init', 'opensearch_flush_rewrite_rules');
add_filter('generate_rewrite_rules', 'opensearch_rewrite_rules');
add_filter('query_vars', 'opensearch_query_vars');
add_filter('redirect_canonical', 'opensearch_canonical', 10, 2);

add_action('wp_head', 'opensearch_head');
add_action('template_redirect', 'opensearch_osd');


/* Add OpenSearch to search results */

/**
 * Adds OpenSearch elements to head of a feed.  This can be used for either RSS or ATOM.
 */
function opensearch_feed_head() {
	global $wp_query;

	$page = $wp_query->query_vars['paged'];
	if ( empty($page) ) $page = 1;
	if ( $page > $wp_query->max_num_pages ) $page = $wp_query->max_num_pages;

	$total_posts = $wp_query->found_posts;
	$posts_per_page = $wp_query->query_vars['posts_per_page'];

	$startIndex = (($page - 1) * $posts_per_page) + 1;
	
	echo '
	<opensearch:totalResults>' . $total_posts . '</opensearch:totalResults>
	<opensearch:startIndex>' . $startIndex . '</opensearch:startIndex>
	<opensearch:itemsPerPage>' . $posts_per_page . '</opensearch:itemsPerPage>
	<opensearch:Query role="request" searchTerms="' . esc_attr($wp_query->query_vars['s']) . '" startPage="' . esc_attr($page) . '" />

';
}

/**
 * Register action hooks for the specified feed type.  
 *
 * @param string $feed type of feed to register hooks for (ie. 'atom', 'rss2', etc)
 */
function opensearch_do_feed($feed) {
	//add_action( "${feed}_head", create_function('', 'echo "	<$tag rel=\"search\" type=\"OPENSEARCH_TYPE\" href=\"" . opensearch_url() . "\" />\n";'));

	if ( is_search() ) {
		add_action( "${feed}_ns", create_function('', 'echo "  xmlns:opensearch=\"" . OPENSEARCH_NS . "\"\n";') );
		add_action( "${feed}_head", 'opensearch_feed_head' );
	}
}

/*
function openasearch_feed_link($feed) {
	$tag = ($feed == 'atom') ? 'Link' : 'atom:Link';
	echo '	<' $tag . ' rel="search" type="' . OPENSEARCH_TYPE . '" href="' . opensearch_url() . '" />' . "\n";
}
 */

// register atom, rss, and rss2 feeds
add_action('do_feed_atom', create_function('', 'opensearch_do_feed("atom");') );
add_action('do_feed_rss2', create_function('', 'opensearch_do_feed("rss2");') );

// always include opensearch link in feeds
add_action( 'atom_head', create_function('', 'echo	\'<link rel="search" type="/>\';') );

