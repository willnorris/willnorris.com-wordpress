<?php

function willnorris_init() {
	register_sidebar(array(
		'name' => 'Front Page',
		'before_widget' => '<li id="%1$s" class="widgetcontainer %2$s">',
		'after_widget' => "</li>",
		'before_title' => "<h3 class=\"widgettitle\">",
		'after_title' => "</h3>\n",
	));
}

function willnorris_page_menu_args($args) {
	if (empty($args['exclude'])) {
		$args['exclude'] = '';
	} else {
		$args['exclude'] .= ',';
	}

	$page_list .= get_option('page_on_front');
	$args['exclude'] .= $page_list;
	$args['sort_column'] = 'menu_order, post_title';

    return $args;
}


function willnorris_header() { 
	echo '
		<script type="text/javascript" src="'.get_option('siteurl').'/wp-content/themes/willnorris/willnorris.js"></script>';
}

function willnorris_footer() { 
?>
	<div id="copyright"> &copy; <?php echo date('Y'); ?>
		<address class="vcard author">
			<a class="url fn" href="http://willnorris.com/">Will Norris</a>
		</address>
	</div>
<?php
}

function willnorris_fix_sharethis_head($wp) {
	if (function_exists('st_widget')) {
		if (is_single()) { 
			add_filter('the_content', create_function('$c', 'return $c."<p>".st_widget()."</p>";'));
		} else {
			remove_action('wp_head', 'st_widget_head');
		}
	}

	return $wp;
}

function willnorris_fix_quoter_head($wp) {
	if (!is_single() && !is_comments_popup()) { 
		remove_action('wp_head', 'quoter_head');
	}

	return $wp;
}


function contactlist_widget_init() {

	function contactlist_widget($args) {
		extract($args);
		echo $before_widget;

		$arguments = array(
			'category_before' => '', 
			'category_after' => '', 
			'title_before' => '<h3>',
			'title_after' => '</h3>',
		);  

		wp_list_bookmarks($arguments);

		echo $after_widget;
	}

	register_sidebar_widget('Contact List', 'contactlist_widget');
	register_widget_control('Contact List', 'contactlist_widget_control', 270, 270);
}

function willnorris_single_post_title($title) {
	$p = get_query_var('p');
	if ($p == get_option('page_on_front')) {
		$title = get_bloginfo('name') . ' | ' . get_bloginfo('description');
	}

	return $title;
}

function willnorris_actionstream_parse_page_token($content) {
	if (function_exists('actionstream_render')) {
		if(preg_match('/<!--actionstream(\((.*)\))?-->/',$content,$matches)) {
			$user = $matches[2];
			$content = preg_replace('/<!--actionstream(\((.*)\))?-->/',actionstream_render($user,50,false,false), $content);
		}
	}

	return $content;
}

add_filter('wp_redirect_status', create_function('$s', 'status_header($s); return $s;'));

//add_action('wp_head', 'willnorris_header');
add_action('init', 'willnorris_init', 11);
add_filter('wp_page_menu_args', 'willnorris_page_menu_args');
add_action('get_footer', 'willnorris_footer');
add_action('wp', 'willnorris_fix_sharethis_head');
add_action('wp', 'willnorris_fix_quoter_head');
add_action('widgets_init', 'contactlist_widget_init');

add_filter('single_post_title', 'willnorris_single_post_title');
remove_filter('the_content', 'diso_actionstream_parse_page_token');
add_filter('the_content', 'willnorris_actionstream_parse_page_token');

add_filter('extended_profile_first_name', create_function('$n', 'return "<span class=\"given-name\">William</span>";'));
remove_filter('get_avatar', 'ext_profile_avatar');

add_filter('avatar_size', create_function('$s', 'return 32;'));
add_filter('extended_profile_adr', create_function('$s', 'return preg_replace("/Current (Address)/", "\\\1", $s);'));
?>
