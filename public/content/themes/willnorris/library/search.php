<?php

add_filter('search_field_value', create_function('$v', 'return "Search...";'));

/**
 * Display stylized search field for Safari users.
 */
function willnorris_safari_search_form($form) {
	global $is_safari;

	if ($is_safari) {
		$domain = preg_replace('|https?://(.+)(/.*)?|', '\1', get_bloginfo('url') );
		$form = preg_replace('/id="s" [^>]+ value="(.*?)"/', '\0 placeholder="\1" autosave="' . $domain . '" results="10"', $form);
		$form = preg_replace('/type="text"/', 'type="search"', $form);

		$form = preg_replace('/<input [^>]* type="submit" [^>]*>/', '', $form);
	}

	return $form;
}
add_filter('thematic_search_form', 'willnorris_safari_search_form');

