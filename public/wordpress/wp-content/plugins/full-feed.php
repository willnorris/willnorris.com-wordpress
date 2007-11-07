<?php
/*
Plugin Name: Full Feed
Plugin URI: http://willnorris.com/projects/full-feed
Description: Display the full post content in syndication feeds, ignoring &lt;!--more--&gt; tags.
Version: 1.0
Author: Will Norris
Author URI: http://willnorris.com/
*/


if (!class_exists('FullFeed')) {
	class FullFeed {

		var $content;

		function register_filters() {
			add_filter('the_title_rss', array($this, 'save_content'), 5);
			add_filter('the_content', array($this, 'restore_content'), -999);
		}
		
		/**
		 * Store the post content if it contains a <!--more--> tag
		 */
		function save_content($input) {
			global $page, $pages;

			// reset content
			$this->content = null;

			if ( $page > count($pages) )
				$page = count($pages);
			$parts = get_extended($pages[$page-1]);

			// if content included a <!--more--> tag, save the full content
			if ($parts['extended'])
				$this->content = implode('', $parts);

			return $input;
		}

		/**
		 * Return the saved content if it exists
		 */
		function restore_content($content) {
			$content = $this->content ? $this->content : $content;
			//$this->content = null;
			return $content;
		}
	}
}

if (isset($wp_version)) {
	$fullfeed = new FullFeed();

	add_action('atom_head', array($fullfeed, 'register_filters'));
	add_action('rss2_head', array($fullfeed, 'register_filters'));
	add_action('rdf_header', array($fullfeed, 'register_filters'));
}

?>
