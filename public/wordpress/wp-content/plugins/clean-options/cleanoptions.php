<?php
/*
Plugin Name: Clean Options
Plugin URI: http://www.mittineague.com/dev/co.php
Description: finds orphaned options and allows for their removal from the wp_options table
Version: Beta 0.9.7
Author: Mittineague
Author URI: http://www.mittineague.com
*/

/*
* Change Log
* 
* ver. Beta 0.9.7 06-Aug-2008
* - provided for time limit increase
* - updated the $known_ok array (for WordPress 2.6)
* - added test for empty option_name field [autoload != yes block]
* 
* ver. Beta 0.9.6 19-Oct-2007
* - added test for empty option_name field [autoload = yes block]
* - tweaked error handling
*
* ver. Beta 0.9.5 18-Oct-2007
* - updated the $known_ok array
* - scoped $cur_wp_ver
* - removed $wpdb->hide_errors() from get_all_no_autoload_options()
* - changed WP_Error obj syntax
* - removed global $wp_queries from get_all_no_autoload_options()
* - replaced get_alloptions() with get_all_yes_autoload_options()
* 
* ver. Beta 0.9.4 06-Oct-2007
* - added WordPress ver. 2.3 compatibility
* - tweaked error handling
*
* ver. Beta 0.9.3 06-Jul-2007
* - updated/improved WP core options array
* - provided for memory limit increase
* - optimized memory usage
* 
* ver. Beta 0.9.2 25-Apr-2007
* - improved protection against accidental removal of WP core options
* - - expanded the $known_ok array
* - - test for non-default install prefix user_roles option
* - - added backup suggestion
* 
* ver. Beta 0.9.1 24-Apr-2007
* - changed a 'hard-coded' wp_ to $wpdb->
*
* ver. Beta 0.9.0 22-Apr-2007
* - added get_all "rss_" options
* - changed str_replace() to wordwrap()
*
* ver. Beta 0.7.1 17-Apr-2007
* - added "Further Information" section
*
* ver. Beta 0.7.0 13-Apr-2007
*/

/*
/--------------------------------------------------------------------\
|                                                                    |
| License: GPL                                                       |
|                                                                    |
| Clean Options Plugin - allows removal of orphaned options          |
| Copyright (C) 2007, Mittineague, www.mittineague.com               |
| All rights reserved.                                               |
|                                                                    |
| This program is free software; you can redistribute it and/or      |
| modify it under the terms of the GNU General Public License        |
| as published by the Free Software Foundation; either version 2     |
| of the License, or (at your option) any later version.             |
|                                                                    |
| This program is distributed in the hope that it will be useful,    |
| but WITHOUT ANY WARRANTY; without even the implied warranty of     |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
| GNU General Public License for more details.                       |
|                                                                    |
| You should have received a copy of the GNU General Public License  |
| along with this program; if not, write to the                      |
| Free Software Foundation, Inc.                                     |
| 51 Franklin Street, Fifth Floor                                    |
| Boston, MA  02110-1301, USA                                        |   
|                                                                    |
\--------------------------------------------------------------------/
*/

function mitt_add_co_page()
{
	if ( function_exists('add_management_page') )
	{
		add_management_page('Clean Options', 'CleanOptions', 8, basename(__FILE__), 'mitt_co_page');
	}
	$cononce = md5('cleanoptions');
}

function mitt_co_css()
{
?>
<style type="text/css">
#or_table {
border: 1px solid #000;
}
#or_table th {
border-bottom: 1px solid #777;
}
#or_table td {
padding: .1em;
border-right: 1px solid #bbb;
border-bottom: 1px solid #bbb;
}
</style>
<?php
}

function mitt_co_page()
{
	global $cononce;

/* hopefully 32M is enough memory, (16M should be plenty)
* if memory limit is not at least 32M, ini_set() it
* 16M = 16777216 bytes
* 32M = 33554432 bytes
* 64M = 67108864 bytes
*/
$co_mem_lim = ini_get('memory_limit');
function co_return_bytes($val)
{
	$val = trim($val);
	$last = strtolower( $val { strlen($val)-1 } );
	switch($last)
	{
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}
	return $val;
}
$mem_lim_as_bytes = co_return_bytes($co_mem_lim);
if ( $mem_lim_as_bytes < 33554432 )
{
	ini_set('memory_limit', '32M');
}

/* similarly, attempt to increase time limit
* - will not work if PHP is in safe mode
*/
$co_time_lim = ini_get('max_execution_time');
if ( $co_time_lim < 60 )
{
	set_time_limit(60);
}

/* get blog version */
$cur_wp_ver = get_bloginfo('version');
$cur_wp_ver = substr( $cur_wp_ver, 0, 3 );

/* Find Orphans Section */

	if ( isset($_POST['find_orphans']) )
	{
		check_admin_referer('clean-options-find-orphans_' . $cononce);
?>
		<div class="wrap">
		<h2>Clean Options</h2>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<?php
		$errs = new WP_Error();

		function add_wp_error($dir_name)
		{
			if ( is_object ($errs) )
			{
				$errs->add('File System Error', 'Could not open folder ' . $dir_name);
			}
			else
			{
				echo '<strong>WARNING !! ERROR MESSAGE !!</strong><br />';
				echo 'The ' . $dir_name  . ' folder could not be opened.<br />';
			}
		}

		function searchdir($dir)
		{
			$file_list = '';
			$stack[] = $dir;
			while ($stack)
			{
				$current_dir = array_pop($stack);
				if ( $dh = opendir($current_dir) )
				{
					while ( ($file = readdir($dh)) !== false )
					{
						if ( ($file !== '.') && ($file !== '..') )
						{
							$current_file = "{$current_dir}/{$file}";
							$get_ext = pathinfo($current_file);
							if ( is_file($current_file) && ($get_ext['extension'] == 'php') )
							{
								$file_list[] = "{$current_dir}/{$file}";
							}
							elseif ( is_dir($current_file) )
							{
								$stack[] = $current_file;
							}
						}
					}
					closedir($dh);
				}
				else
				{
					add_wp_error($current_dir);
				}
			}
			return $file_list;
		}

		$root_dir = '../';
		$out = searchdir($root_dir);
		$pattern = "#get_(?:option|settings)[ ]?\([ ]?[\'\"]([-\w]+)[\'\"][ ]?\)#";
		$temp_arr = array();

		foreach ( $out as $found_file )
		{
			if ( !$handle = fopen($found_file, 'r') )
			{
				$errs->add('File System Error', 'Could not open file ' . $found_file);
			}
			else
			{
				$fs = filesize($found_file);
				if ( $fs == 0 )
				{
					$fs = 1;
				}
				$file_data = fread($handle, $fs);
				preg_match_all($pattern, $file_data, $matches_arr);
				fclose($handle);
				clearstatcache();
				$temp_arr = array_unique ( array_merge ($temp_arr, $matches_arr[1]) );
			}			
		}

		$temp_file_options_arr = array();
		foreach ( $temp_arr as $opt_name )
		{
			$temp_file_options_arr[] = $opt_name;
		}
		unset($temp_arr);

/* Search Options Table for Existing option values */
/* autoload = yes block */

		function get_all_yes_autoload_options()
		{
			global $wpdb, $errs;
			$yes_options = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE autoload = 'yes'");
			foreach ( $yes_options as $yes_option )
			{
				if ( empty ($yes_option->option_name) )
				{
					if ( is_object ($errs) )
					{
						$errs->add('Empty Value', 'autoload yes Option with No Name with the value: ' . wp_specialchars($yes_option->option_value) );
					}
					else
					{
						echo '<strong>WARNING !! ERROR MESSAGE !!</strong><br />';
						echo 'There is an autoload yes Option with No Name with the value: ' . wp_specialchars($yes_option->option_value) . '<br />';
					}
				}
				else
				{
					$yes_value = maybe_unserialize($yes_option->option_value);
					$all_yes_options->{$yes_option->option_name} = apply_filters('pre_option_' . $yes_option->option_name, $yes_value);
				}
			}
			return apply_filters('all_options', $all_yes_options);
		}

		$opt_arr = get_all_yes_autoload_options();

		$temp_table_options_arr = array();
		foreach ( $opt_arr as $key => $value )
		{
			$temp_table_options_arr[] =  $key;
		}

		if ( !empty($errs->errors) )
		{
			echo "<strong>WARNING !! ERROR MESSAGE !!</strong><br />";
			foreach ( $errs->get_error_messages() as $msg )
			{
				echo $msg . "<br />";
			}
		}

		echo "<p>The following Options appear to be orphans.<br />Non-selectable Options are known to have been created from files present during upgrade or backup, or are legitimate options that do not &#34;fit&#34; the search for get_option or get_settings. If you wish to remove them by other means, do so at your own risk.</p>";

		$orphans = array_diff($temp_table_options_arr, $temp_file_options_arr);
		unset($temp_table_options_arr, $temp_file_options_arr);
		natcasesort($orphans);
/*
* Options used by files in WordPress "core" should have been found
* and put into the $temp_file_options_arr array. But just in case,
* they're in the $known_ok array too. A bit of code bloat perhaps, but better
* than making it too easy to remove something that may break the blog.
* these options are found in wp-admin/includes/schema.php, and
* wp-content/plugins/akismet/akismet.php~, wp-content/themes/default/functions.php^,
* wp-cron.php#, wp-includes/cron.php@,
* wp-includes/rewrite.php!, wp-admin/options-reading.php*
*/
		$known_ok = array('active_plugins',			//2.6
				'admin_email',				//2.6
				'advanced_edit',			//2.6
				'akismet_discard_month',		//2.6~
				'akismet_spam_count',			//2.6~
				'blog_charset',				//2.6
				'blogdescription',			//2.6
				'blogname',				//2.6
				'category_base',			//2.6
				'comment_max_links',			//2.6
				'comment_moderation',			//2.6
				'comments_notify',			//2.6
				'cron',					//2.6@
				'date_format',				//2.6
				'default_category',			//2.6
				'default_comment_status',		//2.6
				'default_ping_status',			//2.6
				'default_pingback_flag',		//2.6
				'default_post_edit_rows',		//2.6
				'doing_cron',				//2.6#
				'gmt_offset',				//2.6
				'gzipcompression',			//2.6
				'hack_file',				//2.6
				'home',					//2.6
				'kubrick_header_color',			//2.6^
				'kubrick_header_display',		//2.6^
				'kubrick_header_image',			//2.6^
				'links_recently_updated_append',	//2.6
				'links_recently_updated_prepend',	//2.6
				'links_recently_updated_time',		//2.6
				'links_updated_date_format',		//2.6
				'mailserver_login',			//2.6
				'mailserver_pass',			//2.6
				'mailserver_port',			//2.6
				'mailserver_url',			//2.6
				'moderation_keys',			//2.6
				'moderation_notify',			//2.6
				'page_attachment_uris',			//2.6!
				'page_for_posts',			//2.6*
				'page_on_front',			//2.6*
				'permalink_structure',			//2.6
				'ping_sites',				//2.6
				'posts_per_page',			//2.6
				'posts_per_rss',			//2.6
				'require_name_email',			//2.6
				'rewrite_rules',			//2.6!
				'rss_excerpt_length',			//2.6
				'rss_use_excerpt',			//2.6
				'show_on_front',			//2.6*
				'siteurl',				//2.6
				'start_of_week',			//2.6
				'time_format',				//2.6
				'use_balanceTags',			//2.6
				'use_smilies',				//2.6
				'users_can_register',			//2.6
				'what_to_show',				//2.6
				'widget_akismet',			//2.6~
				'wordpress_api_key',			//2.6~
				);
		if ( $cur_wp_ver < '2.6' )
		{
			$ver_2_5_arr = array('avatar_rating',		//2.5
				'medium_size_w',			//2.5
				'medium_size_h',			//2.5
				'show_avatars',				//2.5
				'thumbnail_crop',			//2.5
				'thumbnail_size_h',			//2.5
				'thumbnail_size_w',			//2.5
				'upload_url_path',			//2.5
				);
			$known_ok += $ver_2_5_arr;
		}
		if ( $cur_wp_ver < '2.5' )
		{
			$ver_2_3_arr = array('xvalid_options');		//2.3 during backup
			$known_ok += $ver_2_3_arr;
		}
		if ( $cur_wp_ver < '2.3' )
		{
			$ver_2_2_arr = array('tag_base');		//2.2
			$known_ok += $ver_2_2_arr;
		}
		if ( $cur_wp_ver < '2.2' )
		{
			$ver_2_1_arr = array('import-blogger',		//2.1.3
				'blog_public',				//2.1
				'default_link_category',		//2.1
				'show_on_front',			//2.1
				);
			$known_ok += $ver_2_1_arr;
		}
		if ( $cur_wp_ver < '2.1' )
		{
			$ver_2_0_arr = array('secret',			//2.0.3
				'upload_path',				//2.0.1
				'uploads_use_yearmonth_folders',	//2.0.1
				'db_version',				//2.0
				'default_role',				//2.0
				);
			$known_ok += $ver_2_0_arr;
		}
		if ( $cur_wp_ver < '2.0' )
		{
			$ver_1_5_arr = array('use_trackback',		//1.5.1
				'blacklist_keys',			//1.5
				'comment_registration',			//1.5
				'comment_whitelist',			//1.5
				'default_email_category',		//1.5
				'html_type',				//1.5
				'page_uris',				//1.5
				'recently_edited',			//1.5
				'rss_language',				//1.5
				'stylesheet',				//1.5
				'template',				//1.5
				'use_linksupdate',			//1.5
				);
			$known_ok += $ver_1_5_arr;
		}

		foreach ( $orphans as $opt_val )
		{
/*
* the wp_user_roles option constructed from the capabilities class may not have
* the default install prefix. Test for it here while testing for the "known ok" array.
*/ 
			if ( ( in_array($opt_val, $known_ok) ) || ( strpos($opt_val, 'user_roles') !== FALSE ) )
			{
				echo "-&nbsp;&nbsp;" . $opt_val . "<br />";
			}
			else
			{
				echo "<input name='prune_opt[]' type='checkbox' value='" . $opt_val . "' />";
				echo "&nbsp;" . $opt_val . "<br />";
			}
		}
		if ( empty($orphans) )
		{
			echo "No Orphaned Options<br />";
		}

/* autoload != yes block, get RSS cache options */

		function get_all_no_autoload_options()
		{
			global $wpdb, $errs;
			$no_options = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE autoload != 'yes'");

			foreach ( $no_options as $no_option )
			{
				if ( empty ($no_option->option_name) )
				{
					if ( is_object ($errs) )
					{
						$errs->add('Empty Value', 'autoload not equal to yes Option with No Name with the value: ' . wp_specialchars($no_option->option_value) );
					}
					else
					{
						echo '<strong>WARNING !! ERROR MESSAGE !!</strong><br />';
						echo 'There is an autoload not equal to yes Option with No Name with the value: ' . wp_specialchars($no_option->option_value) . '<br />';
					}
				}
				else
				{
					$no_value = maybe_unserialize($no_option->option_value);
					$all_no_options->{$no_option->option_name} = apply_filters('pre_option_' . $no_option->option_name, $no_value);
				}
			}
			return apply_filters('all_options', $all_no_options);
		}

		function read_rss_ts($rss_opt_val)
		{
			$mtime = get_option($rss_opt_val);
			$age = time() - $mtime;
			$days = floor($age/86400);
			return $days;
		}

		$temp_rss_opt_arr = get_all_no_autoload_options();
		$rss_opt_arr = array();
		$rss_ts_arr = array();
		$ts_regex = '/^(?:rss_)[a-f0-9]+(?:_ts)$/';
		$rss_regex = '/^(?:rss_)[a-f0-9]+$/';
		foreach ( $temp_rss_opt_arr as $key => $value )
		{
			if ( preg_match($ts_regex, $key) )
			{
				$rss_ts_arr[] = read_rss_ts($key);
				$rss_opt_arr[] =  $key;
			}
			else if ( preg_match($rss_regex, $key) )
			{
				$rss_opt_arr[] =  $key;
			}
		}
		unset($temp_rss_opt_arr);
		natcasesort($rss_ts_arr);
		$num_rss_days = count($rss_ts_arr);
		$ok_rss_date = '100';
		if ( $num_rss_days >= '15' )
		{
			$ok_rss_date = $rss_ts_arr['14'];
		}

		echo "<p>The following contains <strong>ALL</strong> of the &#34;RSS&#34; Options added to the wp_options table from the blog's dashboard page and other files that parse RSS feeds and cache the results.<br />In each pair, the upper option is the cached feed and the lower is the option's timestamp.<br />The timestamps of the newer Options that are more likely to be current have no checkbox, but begin with &#34;-&#34; and end with &#34;<em># days old</em>&#34; in italics.<br />The timestamps of older options can be selected and end with &#34;<strong># days old</strong>&#34; in bold.<br />Please only remove the older options in which <strong>BOTH</strong> options of the pair can be selected.</p>";

		natcasesort($rss_opt_arr);

		foreach ( $rss_opt_arr as $rss_opt_val )
		{
			if ( preg_match($ts_regex, $rss_opt_val) )
			{
				$rss_opt_date = read_rss_ts($rss_opt_val);
				if ( $rss_opt_date > $ok_rss_date )
				{
					echo "<input name='prune_opt[]' type='checkbox' value='" . $rss_opt_val . "' />";
					echo "&nbsp;" . $rss_opt_val . "&nbsp;&nbsp;&nbsp;<strong>" . $rss_opt_date . " days old</strong><br /><br />";
				}
				else if ( $rss_opt_date <= $ok_rss_date )
				{
					echo "-&nbsp;&nbsp;" . $rss_opt_val . "&nbsp;&nbsp;&nbsp;<em>" . $rss_opt_date . " days old</em><br /><br />";
				}
				$rss_opt_date = '';
			}
			else if ( preg_match($rss_regex, $rss_opt_val) )
			{
				echo "<input name='prune_opt[]' type='checkbox' value='" . $rss_opt_val . "' />";
				echo "&nbsp;" . $rss_opt_val . "<br />";
			}
		}
		if ( empty($rss_opt_arr) )
		{
			echo "No &#34;RSS_&#34; Options<br />";
		}

		if ( function_exists('wp_nonce_field') )
			wp_nonce_field('clean-options-pre-remove-orphans_' . $cononce);
?>
		<div class="submit">
			<input type="submit" name="pre_remove_orphans" value="View Selected Options Information" />
		</div>
		</form>
		</div>
<?php
	}

/* Pre Remove Orphans Section */

	else if (isset($_POST['pre_remove_orphans']))
	{
		check_admin_referer('clean-options-pre-remove-orphans_' . $cononce);
?>
		<div class="wrap">
		<h2>Clean Options</h2>
		<p>*Note* spaces have been added after every 10th character of the option_name and every 20th character of the option_value to preserve page layout.<br />Not all options have values and / or descriptions.</p><p><strong>Please review this information very carefully and only remove Options that you know for certain have been orphaned or deprecated.</strong></p><p><strong>It is strongly suggested that you BACKUP your database before removing any options.</strong><p>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<?php
		if ( ( isset($_POST['prune_opt']) ) && ( is_array($_POST['prune_opt']) ) )
		{
			global $wpdb;

			$prune_opt_arr = $_POST['prune_opt'];
			if ( $cur_wp_ver < '2.3' )
			{
				$prune_query = "SELECT option_name, option_value, option_description FROM $wpdb->options WHERE ";
			}
			else if ( $cur_wp_ver > '2.2' )
			{
				$prune_query = "SELECT option_name, option_value FROM $wpdb->options WHERE ";
			}
			$prune_wheres = '';
			foreach ( $prune_opt_arr as $pruneopt )
			{
				if ( !empty($pruneopt) )
				{
					$prune_wheres .= "option_name = '" . $pruneopt . "' OR ";
				}
			}
			$prune_wheres = rtrim($prune_wheres, ' OR ');
			$prune_query .= $prune_wheres;
			$q_results = $wpdb->get_results($prune_query, ARRAY_A);
			echo "<table id='or_table'>";
			if ( $cur_wp_ver < '2.3' )
			{
				echo "<tr><th>option_name</th><th>option_value</th><th>option_description</th></tr>";
			}
			else if ( $cur_wp_ver > '2.2' )
			{
				echo "<tr><th>option_name</th><th>option_value</th></tr>";
			}
			foreach ( $q_results as $q_val )
			{
				$opt_name = $q_val['option_name'];
				$opt_name = wordwrap($opt_name, 10, ' ', 1);
				echo "<tr><td>" . $opt_name . "</td>";
				$opt_val = htmlentities( print_r($q_val['option_value'], TRUE) );
				$opt_val = wordwrap($opt_val, 20, ' ', 1);
				echo "<td>" . $opt_val . "</td>";

				if ( $cur_wp_ver < '2.3' )
				{
					$opt_desc = print_r($q_val['option_description'], TRUE);
					echo "<td>" . $opt_desc . "</td></tr>";
				}
			}
			echo "</table>";
		}
		if ( empty($_POST['prune_opt']) )
		{
			echo "No Orphaned Options where selected<br />";
		}

		$opt_arr_str = ( empty($prune_opt_arr) ) ? '' : implode('#', $prune_opt_arr);

		if ( function_exists('wp_nonce_field') )
			wp_nonce_field('clean-options-do-remove-orphans_' . $cononce);
?>
<br />
		<input type="hidden" name="orphan_opts" value="<?php echo $opt_arr_str; ?>" />
		<input type="radio" name="confirm_rem" value="1" /> Yes, Remove ALL of these options from the wp_options table.<br />
		<input type="radio" name="confirm_rem" value="0" checked="checked" /> No, Don't remove them, return to the first screen.<br />
		<div class="submit">
			<input type="submit" name="do_remove_orphans" value="Submit" />
		</div>
		</form>
		</div>
<?php
	}

/* Do Remove Orphans Section */

	else if ( isset($_POST['do_remove_orphans']) )
	{
		check_admin_referer('clean-options-do-remove-orphans_' . $cononce);

		if ( ( isset($_POST['confirm_rem']) ) && ($_POST['confirm_rem'] == "1") )
		{
			if ( !empty($_POST['orphan_opts']) )
			{
?>
				<div id="message" class="updated fade">
				<p><strong>Removed Options:</strong><br />
<?php
				$orphan_opt_arr = explode("#", $_POST['orphan_opts']);
				foreach ( $orphan_opt_arr as $orphanopt )
				{
					delete_option($orphanopt);
					echo $orphanopt . "<br />";
				}
?>
				</p>
				</div>
<?php
			}
		}
?>
		<div class="wrap">
		<h2>Clean Options</h2>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<p>Listed Options are those that are found in the wp_options table but are not referenced by "get_option" or "get_settings" by any of the PHP files located within your blog directory. If you have deactivated plugins and / or non-used themes in your directory, the associated options will not be considered orphaned until the files are removed.<br />Every "rss_hash" option in the wp_options table will be shown, including current ones.</p>
<?php
/* find out how many rows are in the options table */
		global $wpdb;
		$co_opt_tbl_len = $wpdb->get_results("SELECT COUNT(*) FROM $wpdb->options", ARRAY_A);
?>
		<p>The Options table currently has <b><?php echo $co_opt_tbl_len[0]['COUNT(*)']; ?></b> rows.</p>
<?php
		if ( function_exists('wp_nonce_field') )
			wp_nonce_field('clean-options-find-orphans_' . $cononce);
?>
		<div class="submit">
			<input type="submit" name="find_orphans" value="Find Orphaned Options" />
		</div>
		</form>
		</div>
<?php
	}

/* Intitial View Section */

	else
	{
?>
		<div class="wrap">
		<h2>Clean Options</h2>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<p>Listed Options are those that are found in the wp_options table but are not referenced by "get_option" or "get_settings" by any of the PHP files located within your blog directory. If you have deactivated plugins and / or non-used themes in your directory, the associated options will not be considered orphaned until the files are removed.<br />Every "rss_hash" option in the wp_options table will be shown, including current ones.</p>
<?php
/* find out how many rows are in the options table */
		global $wpdb;
		$co_opt_tbl_len = $wpdb->get_results("SELECT COUNT(*) FROM $wpdb->options", ARRAY_A);
?>
		<p>The Options table currently has <b><?php echo $co_opt_tbl_len[0]['COUNT(*)']; ?></b> rows.</p>
<?php
		if ( function_exists('wp_nonce_field') )
			wp_nonce_field('clean-options-find-orphans_' . $cononce);
?>
		<div class="submit">
			<input type="submit" name="find_orphans" value="Find Orphaned Options" />
		</div>
		</form>
		</div>
<?php
	}
?>

<!-- /* FURTHER INFORMATION SECTION */ -->

	<div class="wrap">
	<h2>Further Information</h2>
	<p>WANTED - Bug Reports<br />
	If you find any problems please let me know.</p>
	<p>For more information, the latest version, etc. please visit <a href='http://www.mittineague.com/dev/co.php'>http://www.mittineague.com/dev/co.php</a></p>
	<p>Questions? For support, please visit <a href='http://www.mittineague.com/forums/viewtopic.php?t=101'>http://www.mittineague.com/forums/viewtopic.php?t=101</a> (registration required to post)</p>
	<p>For comments / suggestions, please visit <a href='http://www.mittineague.com/blog/2007/04/clean-options-plugin/'>http://www.mittineague.com/blog/2007/04/clean-options-plugin/</a></p>
	</div>
<?php
}

if ( function_exists('add_action') )
{
	add_action('admin_head', 'mitt_co_css');
	add_action('admin_menu', 'mitt_add_co_page');
}
?>