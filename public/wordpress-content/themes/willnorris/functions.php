<?php

require_once dirname(__FILE__) . '/library/search.php';
require_once dirname(__FILE__) . '/library/widgets.php';



function willnorris_page_menu_args($args) {
	$args['depth'] = 1;
	return $args;
}
add_filter('wp_page_menu_args', 'willnorris_page_menu_args');

function willnorris_list_pages_exludes($pages) {
	if (get_option('show_on_front') == 'page') {
		$pages[] = get_option('page_on_front');
	}

	return $pages;
}
add_filter('wp_list_pages_excludes', 'willnorris_list_pages_exludes');


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
add_action('get_footer', 'willnorris_footer');
add_action('wp', 'willnorris_fix_sharethis_head');
add_action('wp', 'willnorris_fix_quoter_head');

add_filter('single_post_title', 'willnorris_single_post_title');
remove_filter('the_content', 'diso_actionstream_parse_page_token');
add_filter('the_content', 'willnorris_actionstream_parse_page_token');

add_filter('extended_profile_first_name', create_function('$n', 'return "<span class=\"given-name\">William</span>";'));
remove_filter('get_avatar', 'ext_profile_avatar');

add_filter('avatar_size', create_function('$s', 'return 32;'));
add_filter('extended_profile_adr', create_function('$s', 'return preg_replace("/Current (Address)/", "\\\1", $s);'));



function willnorris_thematic_description($content) {
	if ( is_home() || is_front_page() ) {
		$content = "\t" . '<meta name="description" content="' . get_bloginfo('description') . '" />' . "\n\n";
	}
	return $content;
}
//add_filter('thematic_create_description', 'willnorris_thematic_description');

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

// don't import hcard on account creation (I've had problems with this in the past)
add_filter('init', create_function('', 'remove_action("user_register", "ext_profile_hcard_import");'));







function willnorris_postfooter_postcategory($postcategory) {
	if ( is_attachment() ) {
		$postcategory = '';
	}
	return $postcategory;
}
add_filter('thematic_postfooter_postcategory', 'willnorris_postfooter_postcategory');

/**
 * Attachments don't need comments
 */
function willnorris_comments_open($open, $post_id) {
	if ( is_attachment($post_id) ) {
		$open = false;
	}
	return $open;
}
add_filter('comments_open', 'willnorris_comments_open', 10, 2);


function willnorris_get_attachment_link($link, $id, $size, $permalink, $icon, $text) {
	$post = get_post($id);

	if ( $post->post_excerpt && empty($text) ) {

		if ( ( is_int($size) && $size != 0 ) or ( is_string($size) && $size != 'none' ) or $size != false ) {
			$link_text = wp_get_attachment_image($id, $size, $icon);
		}
		if( trim($link_text) != '' ) return $link;

		$link_text = $post->post_excerpt;

		if ( $permalink ) {
			$url = get_attachment_link($_post->ID);
		} else {
			$url = wp_get_attachment_url($post->ID);
		}

		$post_title = esc_attr($post->post_title);

		$link = "<a href='$url' title='$post_title'>$link_text</a>";
	}

	return $link;
}
add_filter('wp_get_attachment_link', 'willnorris_get_attachment_link', 10, 6);


function willnorris_profile_email($email, $user_id) {
	$userdata = get_userdata($user_id);

	$email = '<dt>Email:</dt> <dd><a class="email" href="mailto:' . esc_attr(antispambot("$userdata->user_email")) . '">' . antispambot($userdata->user_email) . '</a><br />
		<a href="' . get_bloginfo('url') . '/about/pgp">My PGP Key</a></dd>';
	$email .= '
	<dt>IRC:</dt>
	<dd><a href="irc://irc.freenode.net/willnorris,isnick">willnorris@freenode</a></dd>';

	return $email;
}
add_action('extended_profile_email', 'willnorris_profile_email', 10, 2);

function willnorris_profile_jabber($jabber, $user_id) {
	$userdata = get_userdata($user_id);
	return '<dt>Jabber:</dt> <dd><a class="url" href="xmpp:' . esc_attr(antispambot("$userdata->jabber")) . '">' . antispambot($userdata->jabber) . '</a></dd>';
}
add_action('extended_profile_jabber', 'willnorris_profile_jabber', 10, 2);
