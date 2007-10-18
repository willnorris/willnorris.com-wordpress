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

//add_action('wp_head', 'willnorris_header');
add_action('get_footer', 'willnorris_footer');

?>
