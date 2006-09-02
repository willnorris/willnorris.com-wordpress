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
	//echo '<link rel="openid.server" href="http://www.livejournal.com/openid/server.bml" />';
	//echo '<link rel="openid.delegate" href="http://hugwill.livejournal.com/" />';
	echo '<link rel="openid.server" href="https://www.myopenid.com/server" />';
	echo '<link rel="openid.delegate" href="https://wnorris.myopenid.com/" />';
}

add_action('wp_head', 'insert_xrds_meta_tag');

?>
