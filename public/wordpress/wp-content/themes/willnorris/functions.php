<?php

function willnorris_header() { 
	echo '
		<script type="text/javascript" src="'.get_option('siteurl').'/wp-content/themes/willnorris/willnorris.js"></script>';
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
	error_log("$title => $sep");
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

//add_action('wp_head', 'willnorris_header');
add_action('get_footer', 'willnorris_footer');
add_filter('bloginfo', 'willnorris_bloginfo', 5, 2);

?>

