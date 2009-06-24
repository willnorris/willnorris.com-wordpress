<?php


/**
 * Completely replace thematic generated description (it's pretty awful).
 */
function willnorris_thematic_description( $description ) {
	if ( is_home() || is_front_page() ) {
		$content = get_bloginfo('description');
	} else {
		add_filter('the_content', 'willnorris_trim_markdown_headings', -1);
		$content = get_the_excerpt();
		remove_filter('the_content', 'willnorris_trim_markdown_headings', -1);
	}

	$content = preg_replace('/\n/', ' ', trim($content));

	$description = "\t" . '<meta name="description" content="' . esc_attr( $content ) . '" />' . "\n\n";

	return $description;
}
add_filter('thematic_create_description', 'willnorris_thematic_description', 99);

function willnorris_trim_markdown_headings( $content ) {
	$content = preg_replace('/^#{1,6} .*$/m', '', $content);
	return $content;
}


function willnorris_thematic_doctitle($elements) {
	if ( array_key_exists('separator', $elements) ) {
		$elements['separator'] = '&#8212;';
	}

	if ( ! array_key_exists('site_name', $elements) ) {
		$elements[] = '&#8212;';
		$elements[] = get_bloginfo('name');
	}

	return $elements;
}
add_filter('thematic_doctitle', 'willnorris_thematic_doctitle');

