<?php

/*
Plugin Name: WP-XRDS
Description: Inserts XRDS link into page headers
Version: 1.0
Author: Will Norris
Author URI: http://www.willnorris.com
*/

function insert_xrds_meta_tag()
{
	echo '<meta http-equiv="X-XRDS-Location" content="'.get_option('home').'/xrds.xml" />';
}

add_action('wp_head', 'insert_xrds_meta_tag');

?>
