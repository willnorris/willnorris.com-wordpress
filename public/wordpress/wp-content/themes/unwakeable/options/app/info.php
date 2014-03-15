<?php

function k2info($show='') {
	echo get_k2info($show);
}

function get_k2info($show='') {
	global $current;

	switch ($show) {
		case 'version' :
    		$output = $current;
			break;
		case 'scheme' :
			$output = get_bloginfo('template_url') . '/styles/' . get_option('unwakeable_scheme');
			break;
	}
	return $output;
}


function k2_style_info() {
	$style_info = get_option('unwakeable_styleinfo');
	echo stripslashes($style_info);
}

function k2styleinfo_update() {
	$style_info = '';
	$data = k2styleinfo_parse( get_option('unwakeable_scheme') );

	if ('' != $data) {
		$style_info = get_option('unwakeable_styleinfo_format');
		$style_info = str_replace("%author%", $data['author'], $style_info);
		$style_info = str_replace("%site%", $data['site'], $style_info);
		$style_info = str_replace("%style%", $data['style'], $style_info);
		$style_info = str_replace("%stylelink%", $data['stylelink'], $style_info);
		$style_info = str_replace("%version%", $data['version'], $style_info);
		$style_info = str_replace("%comments%", $data['comments'], $style_info);
	}
	
	update_option('unwakeable_styleinfo', $style_info, '','');	
}

function k2styleinfo_demo() {
	$style_info = get_option('unwakeable_styleinfo_format');
	$data = k2styleinfo_parse( get_option('unwakeable_scheme') );

	if ('' != $data) {
		$style_info = str_replace("%style%", $data['style'], $style_info);
		$style_info = str_replace("%stylelink%", $data['stylelink'], $style_info);
		$style_info = str_replace("%author%", $data['author'], $style_info);
		$style_info = str_replace("%site%", $data['site'], $style_info);
		$style_info = str_replace("%version%", $data['version'], $style_info);
		$style_info = str_replace("%comments%", $data['comments'], $style_info);
	} else {
		$style_info = str_replace("%style%", __('Default'), $style_info);
		$style_info = str_replace("%author%", 'Michael Heilemann', $style_info);
		$style_info = str_replace("%site%", 'http://binarybonsai.com/', $style_info);
		$style_info = str_replace("%version%", '1.0', $style_info);
		$style_info = str_replace("%comments%", 'Loves you like a kitten.', $style_info);
		$style_info = str_replace("%stylelink%", 'http://getk2.com/', $style_info);
	}

	echo stripslashes($style_info);
}

function k2styleinfo_parse($style_file = '') {
	// if no style selected, exit
	if ( '' == $style_file ) {
		return;
	}

	$style_path = TEMPLATEPATH . '/styles/' . $style_file;
	if (!file_exists($style_path)) return;
	$style_data = implode( '', file( $style_path ) );
	
	// parse the data
	preg_match("|Author Name\s*:(.*)|i", $style_data, $author);
	preg_match("|Author Site\s*:(.*)|i", $style_data, $site);
	preg_match("|Style Name\s*:(.*)|i", $style_data, $style);
	preg_match("|Style URI\s*:(.*)|i", $style_data, $stylelink);
	preg_match("|Version\s*:(.*)|i", $style_data, $version);
	preg_match("|Comments\s*:(.*)|i", $style_data, $comments);

	return array('style' => trim($style[1]), 'stylelink' => trim($stylelink[1]), 'author' => trim($author[1]), 'site' => trim($site[1]), 'version' => trim($version[1]), 'comments' => trim($comments[1]));
}

function get_k2_ping_type($trackbacktxt = 'Trackback', $pingbacktxt = 'Pingback') {
	$type = get_comment_type();
	switch( $type ) {
		case 'trackback' :
			return $trackbacktxt;
			break;
		case 'pingback' :
			return $pingbacktxt;
			break;
	}
	return false;
}

function k2countpages($request) {
	global $wpdb;
	
	// get number of posts per page
	if (preg_match('/LIMIT \d+, (\d+)/', $request, $matches)) {
		$posts_per_page = $matches[1];
	} else {
		$posts_per_page = get_option('posts_per_page');
	}

	// modify the sql query
	$search = array('/\* FROM/', '/LIMIT \d+, \d+/');
	$replace = array('ID FROM', '');
	$request = preg_replace($search, $replace, $request);

	// get post count
	$post_count = count($wpdb->get_results($request));
	
	return ceil($post_count / $posts_per_page);
}

/* By Mark Jaquith, http://txfx.net */
function k2_nice_category($normal_separator = ', ', $penultimate_separator = ' and ') { 
	$categories = get_the_category(); 

	if (empty($categories)) { 
		_e('Uncategorized','k2_domain'); 
		return; 
	} 

	$thelist = ''; 
	$i = 1; 
	$n = count($categories); 

	foreach ($categories as $category) { 
		if (1 < $i and $i != $n) {
			$thelist .= $normal_separator;
		}

		if (1 < $i and $i == $n) {
			$thelist .= $penultimate_separator;
		}

		$thelist .= '<a href="' . get_category_link($category->cat_ID) . '" title="' . sprintf(__("View all posts in %s"), $category->cat_name) . '">'.$category->cat_name.'</a>'; 
		++$i; 
	} 
	return apply_filters('the_category', $thelist, $normal_separator);
}


function k2asides_filter($query) {
	$k2asidescategory = get_option('unwakeable_asidescategory');
	$k2asidesposition = get_option('unwakeable_asidesposition');

	if ( ($k2asidescategory != 0) and ($k2asidesposition == 1) and ($query->is_home) ) {
		$query->set('cat', '-'.$k2asidescategory);
	}

	return $query;
}

function k2_body_id() {
	if (get_option('permalink_structure') != '' and is_page()) {
		echo "id='" . get_query_var('name') . "'";
	}
}


// Semantic class functions from Sandbox 0.6.1 (http://www.plaintxt.org/themes/sandbox/)

// Template tag: echoes semantic classes in the <body>
function k2_body_class() {
	global $wp_query, $current_user;

	$c = array('wordpress', 'k2');

	k2_date_classes(time(), $c);

	is_home()       ? $c[] = 'home'       : null;
	is_archive()    ? $c[] = 'archive'    : null;
	is_date()       ? $c[] = 'date'       : null;
	is_search()     ? $c[] = 'search'     : null;
	is_paged()      ? $c[] = 'paged'      : null;
	is_attachment() ? $c[] = 'attachment' : null;
	is_404()        ? $c[] = 'four04'     : null; // CSS does not allow a digit as first character

	if ( is_single() ) {
		the_post();
		$c[] = 'single';
		if ( isset($wp_query->post->post_date) ) {
			k2_date_classes(mysql2date('U', $wp_query->post->post_date), $c, 's-');
		}
		foreach ( (array) get_the_category() as $cat ) {
			$c[] = 's-category-' . $cat->category_nicename;
		}
		$c[] = 's-author-' . get_the_author_login();
		rewind_posts();
	}

	else if ( is_author() ) {
		$author = $wp_query->get_queried_object();
		$c[] = 'author';
		$c[] = 'author-' . $author->user_nicename;
	}

	else if ( is_category() ) {
		$cat = $wp_query->get_queried_object();
		$c[] = 'category';
		$c[] = 'category-' . $cat->category_nicename;
	}

	else if ( is_page() ) {
		the_post();
		$c[] = 'page';
		$c[] = 'page-author-' . get_the_author_login();
		rewind_posts();
	}

	if ( $current_user->ID )
		$c[] = 'loggedin';

	echo join(' ', apply_filters('body_class',  $c));
}

// Template tag: echoes semantic classes in each post <div>
function k2_post_class( $post_count = 1, $post_asides = false ) {
	global $post;

	$c = array('hentry', "p$post_count", $post->post_type, $post->post_status);

	$c[] = 'author-' . get_the_author_login();

	foreach ( (array) get_the_category() as $cat ) {
		$c[] = 'category-' . $cat->category_nicename;
	}

	k2_date_classes(mysql2date('U', $post->post_date), $c);

	if ( $post_asides ) {
		$c[] = 'k2-asides';
	}

	if ( $post_count & 1 == 1 ) {
		$c[] = 'alt';
	}

	echo join(' ', apply_filters('post_class', $c));
}

// Template tag: echoes semantic classes for a comment <li>
function k2_comment_class( $comment_count = 1 ) {
	global $comment, $post;

	$c = array($comment->comment_type, "c$comment_count");

	if ( $comment->user_id > 0 ) {
		$user = get_userdata($comment->user_id);

		$c[] = "byuser commentauthor-$user->user_login";

		if ( $comment->user_id === $post->post_author ) {
			$c[] = 'bypostauthor';
		}
	}

	k2_date_classes(mysql2date('U', $comment->comment_date), $c, 'c-');

	if ( $comment_count & 1 == 1 ) {
		$c[] = 'alt';
	}
		
	if ( is_trackback() ) {
		$c[] = 'trackback';
	}

	echo join(' ', apply_filters('comment_class', $c));
}

// Adds four time- and date-based classes to an array
// with all times relative to GMT (sometimes called UTC)
function k2_date_classes($t, &$c, $p = '') {
	$t = $t + (get_settings('gmt_offset') * 3600);
	$c[] = $p . 'y' . gmdate('Y', $t); // Year
	$c[] = $p . 'm' . gmdate('m', $t); // Month
	$c[] = $p . 'd' . gmdate('d', $t); // Day
	$c[] = $p . 'h' . gmdate('h', $t); // Hour
}


// Filter to remove asides from the loop
add_filter('pre_get_posts', 'k2asides_filter');
?>
