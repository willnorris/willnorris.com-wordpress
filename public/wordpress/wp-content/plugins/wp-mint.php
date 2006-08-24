<?php

/*
Plugin Name: WP-Mint
Description: Inserts <a href="http://haveamint.com">Mint</a> link into page headers
Version: 1.0
Author: Will Norris
Author URI: http://www.willnorris.com
*/

function insert_mint_script_tag()
{
	echo '<script src="/mint/?js" type="text/javascript"></script>';
}

add_action('wp_head', 'insert_mint_script_tag');

?>
