<?php

function willnorris_header() { 
	echo '
		<script type="text/javascript" src="'.get_option('siteurl').'/wp-content/themes/willnorris/willnorris.js"></script>';
}

function willnorris_stylesheet_uri($style) { 
	return get_stylesheet_directory_uri().'/lib/reset.css" />
	<link rel="stylesheet" type="text/css" href="'.get_stylesheet_directory_uri().'/lib/typography.css" />
	<link rel="stylesheet" type="text/css" href="'.$style;
}

function willnorris_footer() { 
?>
	<div id="copyright"> &copy; <?php echo date('Y'); ?>
		<address class="vcard author" id="hcard">
			<a class="url fn" href="http://willnorris.com/">Will Norris</a>
		</address>
	</div>
<?php
}

function willnorris_remove_title_prefix($title, $sep) {
	$prefix = " $sep ";
	if (strpos($title, $prefix) == 0) {
		$title = substr($title, strlen($prefix));
	}

	return $title;
}


function willnorris_bloginfo($output, $show) {
	global $willnorris_removed_title;

	if (is_singular() && $show == 'name') {
		if (empty($willnorris_removed_title)) {
			$willnorris_removed_title = 1;
			add_filter('wp_title', 'willnorris_remove_title_prefix', 5, 2);
			return '';
		}
	}

	return $output;
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

//add_action('wp_head', 'willnorris_header');
//add_action('stylesheet_uri', 'willnorris_stylesheet_uri' );
add_action('get_footer', 'willnorris_footer');
add_filter('bloginfo', 'willnorris_bloginfo', 5, 2);
add_action('wp', 'willnorris_fix_sharethis_head');
add_action('wp', 'willnorris_fix_quoter_head');


add_filter('comments_template', 'legacy_comments');

function legacy_comments($file) {
		if(!function_exists('wp_list_comments')) : // WP 2.7-only check
			$file = STYLESHEETPATH . '/legacy.comments.php';
		endif;

		return $file;
}

?>
